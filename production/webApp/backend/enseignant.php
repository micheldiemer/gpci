<?php

/**
 * \file        enseignant.php
 * \author      SIO-SLAM 2014-2016
 * \version     1.0
 * \date        12/04/2015
 * \brief       "enseignant" routes
 *
 * \details     this file contains all the routes for "enseignant" role
 */


//Cours
$app->get('/ens/cours', function ($request, $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('enseignant', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $cours_obj = Cours::with('user', 'matiere', 'classes', 'salle')->whereRaw('DATE(start) >= CURDATE() and id_Users = ? and assignationSent = 1', [$_SESSION['id']])->get();
    return $response->withJson($cours_obj);
});

$app->get('/ens/cours/{id}', function ($request, $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('enseignant', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $cours_obj = Cours::with('matiere', 'classes')->select('id', 'start', 'end')->where('id', $id)->get();
    $cours = array();
    foreach ($cours_obj as $cour) {
        array_push(
            $cours,
            "cours",
            $cour,
            "matiere",
            $cour->matiere,
            "classes",
            $cour->classes
        );
    }
    return $response->withJson($cours);
});

//Indisponibilités
$app->get('/ens/indispo', function ($request, $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('enseignant', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    //Suppression des indispos dans le passé à chaque requete ( à gérer autrement plus tard)
    Indisponibilite::whereRaw('DATE(end) < CURDATE() and id_Users = ?', [$_SESSION['id']])->delete();

    $indispos = Indisponibilite::where('id_Users', $_SESSION['id'])->get();
    return $response->withJson($indispos);
});

$app->post('/ens/indispo', function ($request, $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('enseignant', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $json = $request->getBody();
    $data = json_decode($json, true);
    $indispo = new Indisponibilite;
    $indispo->start = new DateTime($data['start']);
    $indispo->end = new DateTime($data['end']);
    $indispo->id_Users = $_SESSION['id'];

    $duration = date_diff($indispo['start'], $indispo['end'], true);

    //Si indispo d'une journée -> suppression de l'indispo précédente
    if ($duration->d < 2) {
        Indisponibilite::whereRaw('DATE(start) = DATE(?) and id_Users = ?', [$data['start'], $_SESSION['id']])->delete();
        //$oldIndispo = Indisponibilite::where([
        //                                       ['id_Users',$data['user']['id']],
        //                                       ['DATE(start)',$data['start']->format('Y-m-d')]])->firstOrFail();

        //Si indispo de plus d'une journée -> période
    } else {
        //TO DO : Rajouter cas collision entre deux périodes
        Indisponibilite::whereRaw('DATE(start) BETWEEN DATE(?) and DATE(?) and id_Users = ?', [$data['start'], $data['end'], $_SESSION['id']])->delete();
    }

    $indispo->save();

    ($response->getBody())->write('1');
    return $response;
});

$app->delete('/ens/indispo/{id}', function ($request, $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('enseignant', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $id = $args['id'];
        $indispo = Indisponibilite::where('id_Users', $_SESSION['id'])->where('id', $id)->delete();

        ($response->getBody())->write('1');
        return $response;
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});


//Lien Ical public pour les applications calendriers des professeurs
$app->get('/ical/{id}', function ($request, $response, array $args) {
    try {
        $id = $args['id'];
        $cours = Cours::with('user', 'matiere', 'classes')->whereRaw('DATE(start) >= DATE_SUB(NOW(),INTERVAL 1 YEAR) and id_Users = ? and assignationSent = 1', [$id])->get();
        $events = [];

        $i = 0;
        foreach ($cours as $cour) {
            //Pansement pour problème timezone
            $start = new dateTime($cour->start);
            $end = new dateTime($cour->end);
            $strClasses = '';
            foreach ($cour->classes as $classe) {
                $strClasses .= $classe['nom'] . ' ';
            }
            $strClasses = trim($strClasses);
            $eventParams = array(
                'start' => $start,
                'end' => $end,
                'summary' => 'Cours ' . $strClasses . ' - ' . $cour->matiere->nom
            );
            $events[$i] = new CalendarEvent($eventParams);
            $i++;
        }

        $calParams = array(
            'events' => $events
        );

        $calendar = new Calendar($calParams);
        $calendar->generateDownload();
    } catch (Exception $e) {

        ($response->getBody())->write($e);
        return $response->withStatus(400);
    }
});
