<?php

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
        $cours = Cours::with('user')->whereRaw("start >= ? AND end <= ?", [$start, $end])->get();


        foreach ($cours as $cour) {
            if (!empty($cour->user) and $cour->assignationSent == 0) {
                mailAssignationCours($cour, $mailer);
                $cour->assignationSent = 1;
                $cour->save();
            }
        }
    } catch (Exception $e) {
        return $response->getBody()->write($e)->withStatus(400);
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


$app->post('/plan/cours/', function ($request, $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('enseignant', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $json = $request->getBody();
        $data = json_decode($json, true);

        $cours = new Cours;
        $cours->start = $data['start'];
        $cours->end = $data['end'];
        $cours->id_Matieres = $data['id_Matieres'];
        $cours->id_Salles = $data['id_Salles'];
        $cours_user = [];
        $indispo = [];
        if (!empty($data['id_Users'])) {
            $cours->id_Users = $data['id_Users'];
            // Verification si l'enseignant na aucun autre cours à la même date
            $coursExists = Cours::whereRaw('(start >= ? AND end <= ?) and id_Users = ?', [$data['start'], $data['end'], $data['id_Users']])->count();
            // Verification s'il n'est pas indisponible
            $indispoExists = Indisponibilite::whereRaw('(start >= ? AND end <= ?) and id_Users = ?', [$data['start'], $data['end'], $data['id_Users']])->count();

            if ($coursExists == 0  && $indispoExists == 0) {
                saveCours($cours, $data['classes']);
            } else {
                ($response->getBody())->write(array("message" => "Cet enseignant est indisponible!"));
                return $response->withStatus(400);
            }
        } else {
            saveCours($cours, $data['classes']);
        }
    } catch (Exception $e) {
        ($response->getBody())->write($e);
        return $response->withStatus(400);
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

        $cours->start = $data['start'];
        $cours->end = $data['end'];
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
                saveCours($cours, $data['classes']);
            } else {
                return $response->withJson(array("message" => "Cet enseignant est indisponible!"))->withStatus(400);
            }
        } else {
            saveCours($cours, $data['classes']);
        }
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

function saveCours($cours, $classes)
{
    $cours->save();
    $newClasses = [];
    foreach ($classes as $classe) {
        array_push($newClasses, $classe['id']);
    }
    // Sauvegarde classe database
    $cours->classes()->sync($newClasses);
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
        ($response->getBody())->write('1');
        return $response;
    } catch (Exception $e) {
        ($response->getBody())->write($e);
        return $response->withStatus(400);
    }
});

//Matières
$app->get('/plan/matiere', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $matiere = Matieres::with('user')->orderBy('nom')->get();

    $response = $response->withJson('');
    ($response->getBody())->write($matiere->toJson());
    return $response;
});

$app->get('/plan/matiere/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $matiere = Matieres::where('id', $id)->first();
    $response = $response->withJson('');
    ($response->getBody())->write($matiere->toJson());
    return $response;
});

$app->post('/plan/matiere/', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $json = $request->getBody();
        $data = json_decode($json, true);

        $matiere = new Matieres();
        $matiere->nom = $data['nom'];
        $matiere->code = $data['code'];
        $matiere->save();
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

    $response = $response->withJson('');
    ($response->getBody())->write($users->toJson());
    return $response;
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

    $response = $response->withJson('');
    ($response->getBody())->write($personne->toJson());
    return $response;
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

    $response = $response->withJson('');
    ($response->getBody())->write($classes->toJson());
    return $response;
});

$app->get('/plan/classe/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $classe = Classes::where("id", $id)->with('user')->first();

    $response = $response->withJson('');
    ($response->getBody())->write($classe->toJson());
    return $response;
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
        $classe->start = $data['start'];
        $classe->end = $data['end'];
        $classe->id_Users = $data['id_Users'];
        $classe->save();

        ($response->getBody())->write('1');
        return $response;
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

        $classe = new Classes;
        $classe->nom = $data['nom'];
        $classe->start = $data['start'];
        $classe->end = $data['end'];
        $classe->id_Users = $data['id_Users'];
        $classe->save();
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

    $response = $response->withJson('');
    ($response->getBody())->write($salles->toJson());
    return $response;
});

$app->get('/plan/salle/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $salle = Salles::where("id", $id)->first();

    $response = $response->withJson('');
    ($response->getBody())->write($salle->toJson());
    return $response;
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
        return $response->withJson('1')->withStatus(201);
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

        ($response->getBody())->write('1');
        return $response;
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

$app->delete('/plan/salle/{id}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $id = $args['id'];
        salles::find($id)->delete();
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});

$app->get('/upload/{year}/{week}/{classe}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $year = $args['year'];
    $week = $args['week'];
    $classe = $args['classe'];
    $uploadedFiles = $_FILES;
    $directory = __DIR__ . '/uploads';
    $filename = $directory . "/" . $year . "-" . $week . "-" . $classe . ".pdf";
    // Handle single file upload
    if (file_exists($filename)) {
        return $response->withJson(['exists' => true, 'fileDirectory' => $year . "-" . $week . "-" . $classe . ".pdf"]);
    } else {
        return $response->withJson(['exists' => false, 'fileDirectory' => null]);
    }
});

$app->post('/upload/{year}/{week}/{classe}', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $year = $args['year'];
    $week = $args['week'];
    $classe = $args['classe'];
    $fileExt = explode('.', $_FILES['file']["tmp_name"]);
    if (!array_pop($fileExt) == 'pdf') {

        return $response->withJson(['error' => 'Failed to upload file', 'msg' => "Format non autorisé"])->withStatus(400);
    }


    $uploadedFiles = $_FILES;
    $directory = __DIR__ . '/uploads';
    $filename = $directory . "/" . $year . "-" . $week . "-" . $classe . ".pdf";
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
    $year = $args['year'];
    $week = $args['week'];
    $classe = $args['classe'];
    $directory = __DIR__ . '/uploads';
    $filename = $directory . "/" . $year . "-" . $week . "-" . $classe . ".pdf";

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
