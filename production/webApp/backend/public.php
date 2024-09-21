<?php

use Dompdf\Dompdf;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response;

$app->get('/semaine/{year}/{week}/{classe}', function (Request $request, Response $response, array $args) {
    $year = $args['year'];
    $week = $args['week'];
    $classe = $args['classe'];
    $uploadedFiles = $_FILES;
    $directory = __DIR__ . '/uploads';
    $classe = Classes::where('id', $classe)->firstOrFail();

    $filename = $directory . "/" . htmlspecialchars($year) . "-" . htmlspecialchars($week) . "-" . htmlspecialchars($classe->nom) . ".pdf";
    // Handle single file upload
    if (file_exists($filename)) {

        return $response->withFileDownload($filename, htmlspecialchars($classe->nom) . "_semaine_" . htmlspecialchars($week) . ".pdf");
    } else {
        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'landscape');
        $date = getDateList($week, $year);
        $cours_am = array();
        $cours_pm = array();
        $count = 0;
        foreach ($date as $day) {
            $cours_am[$count] = Cours::with('user', 'matiere')->whereRaw('(start >= ? AND end <= ?) and assignationSent = 1', [$day . " 08:00:00", $day . " 12:00:00"])->whereHas('classes', function ($q) use ($classe) {
                $q->where('id', $classe['id']);
            })->first();
            $cours_pm[$count] = Cours::with('user', 'matiere')->whereRaw('(start >= ? AND end <= ?) and assignationSent = 1', [$day . " 13:00:00", $day . " 17:00:00"])->whereHas('classes', function ($q) use ($classe) {
                $q->where('id', $classe['id']);
            })->first();

            // cours / salle non renseignée : valeur par défaut id=0 nom=''
            if (isset($cours_am[$count]) && !(isset($cours_am[$count]->salle))) {
                $cours_am[$count]->salle = (object)array('id' => 0, 'nom' => '');
            }
            if (isset($cours_pm[$count]) && !(isset($cours_pm[$count]->salle))) {
                $cours_pm[$count]->salle = (object)array('id' => 0, 'nom' => '');
            }
            $count += 1;
        }
        $date_name = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $template = "templates/week.php";
        ob_start();
        include $template;
        $template = ob_get_clean();
        $dompdf->loadHtml($template);

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream($classe->nom . "_semaine_" . $week);
        return;
    }
});

$app->get('/plan/years/{current_next}', function (Request $request, Response $response, array $args) {
    $current_next = $args['current_next'];
    if ($current_next == 'current') {
        if (date('Y-m-d', strtotime("now")) >= date('Y-m-d', strtotime("first day of august"))) {
            $year = date("Y", strtotime("now")) . "/" . date("Y", strtotime("next year"));
        } else {
            $year = date("Y", strtotime("last year")) . "/" . date("Y", strtotime("now"));
        }
    } else {
        if (date('Y-m-d', strtotime("now")) >= date('Y-m-d', strtotime("first day of august"))) {
            $year = date("Y", strtotime("next year")) . "/" . date("Y", strtotime("+2 year"));
        } else {
            $year = date("Y", strtotime("now")) . "/" . date("Y", strtotime("next year"));
        }
    }
    $year = array("year" => $year);
    return $response->withJson($year);
});

$app->get('/plan/weeks/{current_next}', function (Request $request, Response $response, array $args) {
    $current_next = $args['current_next'];
    $date = getStartEndByYear($current_next);
    $cours = Cours::whereRaw('(start >= ? AND end <= ?)', [$date['start'], $date['end']])->orderBy('start', 'ASC')->get();
    $weeks = array();
    $week_list = array();
    foreach ($cours as $cour) {
        $classe_list = array();
        $date = new DateTime($cour->start);
        $week = $date->format("W");
        $year = $date->format("Y");
        $date = getStartAndEndDate($week, $year);
        $classes = Classes::whereHas('cours', function ($q) use ($date) {
            $q->whereRaw('(start >= ? AND end <= ?)', [$date[0], $date[1]]);
        })->get();
        foreach ($classes as $classe) {
            array_push($classe_list, $classe->id);
        }
        if (!in_array($week, $week_list)) {
            array_push($weeks, array(
                "number" => $week,
                "year" => $year,
                "classes" => $classe_list
            ));
            array_push($week_list, $week);
        }
    }
    return $response->withJson($weeks);
});

$app->get('/plan/current_next_classe/{current_next}', function ($request, $response, array $args) {
    $current_next = $args['current_next'];
    $date = getStartEndByYear($current_next);
    $classes = Classes::with('user')->whereRaw('(start >= ? and start <= ?) or (end >= ? and end <= ? )', [$date['start'], $date['end'], $date['start'], $date['end']])->get();

    $response = $response->withJson('');
    ($response->getBody())->write($classes->toJson());
    return $response;
});

//Lien Ical public pour les applications calendriers des professeurs
$app->get('/ical/classe/{classeId}',  function ($request, $response, array $args) {
    $classeId = $args['classeId'];
    try {
        $cours = Classes::where('id', $classeId)->with('cours')->get();
        $events = [];

        $i = 0;
        foreach ($cours[0]['cours'] as $cour) {
            $courDetail = Cours::where('id', $cour->id)->with('matiere', 'salle')->get();
            $start = new dateTime($cour->start);
            $end = new dateTime($cour->end);

            $eventParams = array(
                'start' => $start,
                'end' => $end,
                'summary' => 'Cours ' . ' de ' . $courDetail[0]->matiere->nom . ' - ' . $courDetail[0]->salle->nom
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
