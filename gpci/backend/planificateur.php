<?php
const SALLE_DEFAUT = 99;
/**
 * \file        planificateur.php
 * \author      SIO-SLAM 2014-2016
 * \version     1.0
 * \date        12/04/2015
 * \brief       "planificateur" routes
 *
 * \details     this file contains all the routes for "planificateur" role
 */

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response;

//Message d'assignation à tous les enseignants dans une fourchette de temps
$app->get('/plan/cours/assignation', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    global $mailer;
    try {
        $start = $_GET['start'];
        $end = $_GET['end'];
        $cours = Cours::with('user')->whereRaw("assignationSent = 0 AND start >= ? AND end <= ?", [$start, $end])->get();


        foreach ($cours as $unCours) {
            if (!empty($unCours->user) and $unCours->assignationSent == 0) {
                mailAssignationCours($unCours, $mailer);
                $unCours->assignationSent = 1;
                $unCours->save();
            }
        }
        return $response->withJson('1');
    } catch (Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage()], 500);
    }
});

//Cours
$app->get('/plan/cours', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $start = $_GET['start'];
    $end = $_GET['end'];
    $cours_obj = Cours::with('user', 'matiere', 'classes', 'salle')->where(function ($q) use ($start) {
        $q->where('start', '>=', $start);
    })->where(function ($q) use ($end) {
        $q->where('start', '<=', $end);
    })->get();
    return $response->withJson($cours_obj);
});


$app->get('/plan/cours/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $cours_obj = Cours::with('user', 'matiere', 'classes', 'salle')->where('id', $id)->firstOrFail();
    return $response->withJson($cours_obj);
});

$app->get('/plan/cours/classe/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $classe = Classes::where('id', $id)->with('cours')->get();;
    $cours = Cours::whereHas('classes', function ($q) use ($id) {
        $q->where('id', $id);
    })->get();;
    return $response->withJson($cours);
});


$app->post('/plan/cours', function ($request, $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }

    try {
        $json = $request->getBody();
        $data = json_decode($json, true);

        $cours = new Cours;
        $cours->start =
            DateTime::createFromFormat('Y-m-d\TH:i:s', $data['start']);
        $cours->end =
            DateTime::createFromFormat('Y-m-d\TH:i:s', $data['end']);
        $cours->id_Matieres = $data['id_Matieres'];
        $cours->id_Salles = $data['id_Salles'] ?? SALLE_DEFAUT;
        $cours->assignationSent = 0;

        $cours_user = [];
        $indispo = [];
        if (!empty($data['id_Users'])) {
            $cours->id_Users = $data['id_Users'];
            // Verification si l'enseignant na aucun autre cours à la même date
            $coursExists = Cours::whereRaw('(start >= ? AND end <= ?) and id_Users = ?', [$data['start'], $data['end'], $data['id_Users']])->count();
            // Verification s'il n'est pas indisponible
            $indispoExists = Indisponibilite::whereRaw('(start >= ? AND end <= ?) and id_Users = ?', [$data['start'], $data['end'], $data['id_Users']])->count();

            if ($coursExists == 0  && $indispoExists == 0) {
                return saveCours($response, $cours, $data['classes']);
            } else {
                return $response->withJson(["message" => "Cet enseignant est indisponible!"], 400);
            }
        } else {
            return saveCours($response, $cours, $data['classes']);
        }
    } catch (Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage()], 500);
    }
});

$app->put('/plan/cours/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    global $mailer;
    $id = $args['id'];

    try {
        $json = $request->getBody();
        $data = json_decode($json, true);

        $cours = Cours::where('id', $id)->firstOrFail();
        //Verication si cours a déjà été assigné, message d'annulation si oui
        if ($cours->assignationSent == 1) {
            if ($data['id_Users'] != $cours->id_Users) {
                mailAnnulationCours($cours, $mailer);
                $cours->assignationSent = 0;
            }
        }

        // $cours->start =
        //     DateTime::createFromFormat('Y-m-d\TH:i:s', $data['start']);
        // $cours->end =
        //     DateTime::createFromFormat('Y-m-d\TH:i:s', $data['end']);
        $cours->id_Matieres = $data['id_Matieres'];
        $cours->id_Salles = $data['id_Salles'];
        $cours_user = [];
        $indispo = [];

        if (!empty($data['id_Users'])) {

            $cours->id_Users = $data['id_Users'];
            // Verification si l'enseignant na aucun autre cours à la même date
            $coursExists = Cours::whereRaw('(start >= ? AND end <= ?) and id_Users = ? and id != ?', [$data['start'], $data['end'], $data['id_Users'], $id])->count();
            // Verification s'il n'est pas indisponible
            $indispoExists = Indisponibilite::whereRaw('(start >= ? AND end <= ?) and id_Users = ?', [$data['start'], $data['end'], $data['id_Users']])->count();

            if ($coursExists == 0  && $indispoExists == 0) {
                return saveCours($response, $cours, $data['classes'], false);
            } else {
                return $response->withJson(array("message" => "Cet enseignant est indisponible!"))->withStatus(400);
            }
        } else {
            return saveCours($response, $cours, $data['classes'], false);
        }
    } catch (Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage()], 500);
    }
});

function saveCours($response, $cours, $classes, $doCreate = true)
{

    $newClasses = [];
    foreach ($classes as $classe) {
        if (!is_null($classe) && isset($classe['id']))
            array_push($newClasses, $classe['id']);
    }

    if ($doCreate) {
        if (count($newClasses) == 0) {
            return $response->withJson(array("message" => "Veuillez sélectionner au moins une classe!"))->withStatus(400);
        };

        $cours = Cours::create([
            'start' => $cours->start,
            'end' => $cours->end,
            'id_Matieres' => $cours->id_Matieres,
            'id_Salles' => $cours->id_Salles,
        ]);
    } else {
        $cours->save();
    }


    if (is_null($cours)) {
        return $response->withJson(['message' => 'Erreur DB création cours'])->withStatus(500);
    }


    if ($doCreate || count($newClasses) > 0)
        // Sauvegarde classe database
        $cours->classes()->sync($newClasses);

    return $response->withJson('1', 200);
}

$app->delete('/plan/cours/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    global $mailer;
    $id = $args['id'];
    try {
        $cours = Cours::with('user', 'matiere', 'classes', 'salle')->where('id', $id)->firstOrFail();
        if ($cours->assignationSent == 1) {
            mailAnnulationCours($cours, $mailer);
        }
        $cours->classes()->detach();
        $cours->delete();

        return $response->withJson(1, 200);
    } catch (Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage()], 500);
    }
});

//Matières
$app->get('/plan/matiere', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $matiere = Matieres::with('user')->orderBy('nom')->get();

    return $response->withJson($matiere, 200);
});

$app->get('/plan/matiere/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $matiere = Matieres::where('id', $id)->first();
    return $response->withJson($matiere, 200);
});

$app->post('/plan/matiere', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $json = $request->getBody();
        $data = json_decode($json, true);

        $matiere = Matieres::create(['nom' => $data['nom'], 'code' => $data['code']]);
        return is_null($matiere)
            ? $response->withJson('Matière non créée', 500)
            : $response->withJson('1')->withStatus(201);
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

$app->put('/plan/matiere/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $id = $args['id'];
        $matiere = Matieres::where('id', $id)->firstOrFail();
        $json = $request->getBody();
        $data = json_decode($json, true);

        $matiere->nom = $data['nom'];
        $matiere->code = $data['code'];
        $matiere->save();
        return $response->withJson(1);
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

$app->delete('/plan/matiere/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $id = $args['id'];
        $cours = Matieres::where('id', $id)->has('cours')->count();
        if ($cours == 0) {
            Matieres::find($id)->delete();
        } else {

            return $response
                ->withJson(array("message" => "Vous ne pouvez pas supprimer une matiere avec des cours!"))
                ->withStatus(400);
        }
        return $response->withJson('1');
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

//Enseignants
$app->get('/plan/enseignant', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $users = Users::whereHas('roles', function ($q) {
        $q->where('role', 'enseignant');
    })
        ->where("enabled", 1)
        ->with('matieres', 'indisponibilite', 'cours')
        ->select('id', 'firstName', 'lastName')
        ->orderBy('lastName')
        ->get();

    return $response->withJson($users, 200);
});

$app->get('/plan/enseignant/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $personne = Users::where('id', $id)
        ->where('enabled', 1)
        ->with('matieres')
        ->select('id', 'firstName', 'lastName')
        ->first();

    return $response->withJson($personne, 200);
});

$app->put('/plan/enseignant/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $id = $args['id'];
        $json = $request->getBody();
        $data = json_decode($json, true);
        $personne = Users::where('id', $id)->with('matieres')->firstOrFail();
        //sync matieres
        $newMatieres = [];
        foreach ($data['matieres'] as $matiere) {
            array_push($newMatieres, $matiere['id']);
        }
        $personne->matieres()->sync($newMatieres);
        return $response->withJson('1');
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

//Classes

$app->get('/plan/classe', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {


    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }

    if (date('Y-m-d', strtotime("now")) >= date('Y-m-d', strtotime("first day of september"))) {
        $start = date('Y-m-d', strtotime("first day of september"));
    } else {
        $start = date('Y-m-d', strtotime("first day of september last year"));
    }
    $classes = Classes::with('user')->whereRaw('(end >= ?)', [$start])->get();

    return $response->withJson($classes, 200);
});

$app->get('/plan/classe/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $classe = Classes::where("id", $id)->with('user')->first();

    return $response->withJson($classe, 200);
});

$app->put('/plan/classe/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $id = $args['id'];
        $json = $request->getBody();
        $data = json_decode($json, true);
        $classe = Classes::where('id', $id)->firstOrFail();

        $classe->nom = $data['nom'];
        $classe->start =
            DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $data['start']);
        $classe->end =
            DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $data['end']);
        $classe->id_Users = $data['id_Users'];

        if ($classe->start === false || $classe->end === false) {
            return $response->withJson(['message' => 'Dates incorrectes format attendu YYYY-MM-DDTHH:mm:ss.000Z trouvé ' . htmlspecialchars($data['start'])])->withStatus(400);
        }

        $classe->save();

        return $response->withJson('1', 200);
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

$app->post('/plan/classe', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $json = $request->getBody();
        $data = json_decode($json, true);
        $userStart = $data['start'];
        $userEnd = $data['end'];

        $data['start'] = DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $userStart);
        $data['end'] = DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $userEnd);

        if ($data['start'] === false || $data['end'] === false) {
            return $response->withJson(['message' => 'Dates incorrectes format attendu YYYY-MM-DDTHH:mm:ss.000Z / fourni ' . htmlspecialchars($userStart)], 400);
        }

        $classe = Classes::create(['nom' => $data['nom'], 'start' => $data['start'], 'end' => $data['end'], 'id_Users' => $data['id_Users']]);

        return is_null($classe)
            ? $response->withJson('Classe non créée')->withStatus(500)
            : $response->withJson('1');
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

$app->delete('/plan/classe/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $id = $args['id'];
        $nbCours = Classes::where("id", $id)->has('cours')->count();
        if ($nbCours == 0) {
            Classes::find($id)->delete();
        } else {

            return $response
                ->withJson(array("message" => "Vous ne pouvez pas supprimer une classe avec des cours!"))
                ->withStatus(400);
        }
        return $response->withJson('1');
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

// Salles

$app->get('/plan/salle', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $salles = Salles::all();

    return $response->withJson($salles, 200);
});

$app->get('/plan/salle/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $salle = Salles::where("id", $id)->first();

    return $response->withJson($salle, 200);
});

$app->post('/plan/salle', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $json = $request->getBody();
        $data = json_decode($json, true);

        if (!isset($data['nom'])) {
            return $response->withJson(array("message" => "Nom de la salle manquant!"))->withStatus(400);
        }

        $salle = Salles::where('nom', $data['nom'])->first();
        if ($salle) {
            return $response->withJson(array("message" => "Salle déjà existante!"))->withStatus(400);
        };

        $salle = Salles::create(['nom' => $data['nom']]);
        return is_null($salle)
            ? $response->withJson('Salle non créée')->withStatus(500)
            : $response->withJson('1', 201);
    } catch (Exception $e) {
        return $response->withJson(strval($e), 500);
    }
});

$app->put('/plan/salle/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $id = $args['id'];
        $salle = Salles::where('id', $id)->firstOrFail();
        $json = $request->getBody();
        $data = json_decode($json, true);

        $salle->nom = $data['nom'];
        $salle->save();

        return $response->withJson(1, 200);
    } catch (Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage()], 500);
    }
});

$app->delete('/plan/salle/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $id = $args['id'];
        $salle = Salles::where('id', $id)->firstOrFail();

        if (count($salle->cours) > 0) {
            return $response->withJson(array("message" => 'Vous ne pouvez pas supprimer une salle avec des cours !'))->withStatus(400);
        }

        $salle->delete();
        return $response->withJson('1');
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

$app->get('/upload/{year}/{week}/{classe}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }

    $local = uploadFileName($args['year'] ?? '', $args['week'] ?? '', intval($args['classe']));
    $filename = $local[0];

    // Handle single file upload
    if (file_exists($filename)) {
        return $response->withJson(['exists' => true, 'fileDirectory' => $local[2]]);
    } else {
        return $response->withJson(['exists' => false, 'fileDirectory' => null]);
    }
});

$app->post('/upload/{year}/{week}/{classe}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }

    $filename = uploadFileName($args['year'] ?? '', $args['week'] ?? '', intval($args['classe']))[0];

    if (mime_content_type($_FILES['file']["tmp_name"]) !== 'application/pdf') {
        return $response->withJson(['error' => 'Failed to upload file', 'msg' => "Format non autorisé"])->withStatus(400);
    }


    $uploadedFiles = $_FILES;

    // Handle single file upload
    $err = "test file";
    if (isset($uploadedFiles['file'])) {
        $uploadedFile = $uploadedFiles['file']["tmp_name"];
        $err = "nom fichier $uploadedFile $filename";
        if (move_uploaded_file($uploadedFile, $filename)) {
            return $response->withJson(['message' => 'File uploaded successfully']);
        }
    }

    return $response->withJson(['error' => 'Failed to upload file', 'msg' => $err]);
});

$app->delete('/upload/{year}/{week}/{classe}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $uploadedFiles = $_FILES;
    $filename = uploadFileName($args['year'] ?? '', $args['week'] ?? '', $args['classe'] ?? '')[0];

    if (file_exists($filename)) {
        if (unlink($filename)) {
            return $response->withJson(['deleted' => true]);
        } else {
            return $response->withJson(['exists' => true]);
        }
    } else {
        return $response->withJson(['exists' => false]);
    }
});
