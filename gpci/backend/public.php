<?php

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response;

$app->get('/semaine/{year}/{week}/{classe}', function (Request $request, Response $response, array $args) {
    try {
        $year = $args['year'];
        $week = $args['week'];
        $classeId = intval($args['classe']);
        $fileinfo = uploadFileName($year, $week, $classeId);
        $classe = Classes::find($classeId);
        // Handle single file upload
        if (file_exists($fileinfo[0])) {
            return $response->withFile($fileinfo[0], $fileinfo[2])->withHeader('Content-Disposition', 'inline');
        } else {
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
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
            $dompdf->stream($classe->nom . "_semaine_" . $week, ["Attachment" => 0]);
            return $response->withStatus(200);
        }
    } catch (\Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage(), 'exception' => $e], 500);
    }
});

$app->get('/plan/years/{current_next}', function (Request $request, Response $response, array $args) {
    try {
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
    } catch (\Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage(), 'exception' => $e], 500);
    }
});

$app->get('/plan/weeks', function (Request $request, Response $response, array $args) {

    $current_next = [];
    try {
        $i = 0;
        foreach (['current', 'next'] as $cn) {
            $date = getStartEndByYear($cn);
            $dateStartEnd = $date;

            // $classes =
            //     Classes::whereRaw('(start >= ? and start <= ?) or (end >= ? and end <= ? )', [$date['start'], $date['end'], $date['start'], $date['end']])->orderBy('nom')->get()->toArray();


            $interval = new DateInterval('P1W');
            $period   = new DatePeriod($date['start'], $interval, $date['end']);

            $dbweeks = DB::table('classes')
                ->selectRaw('classes.id, classes.nom, classes.start as classeStart, classes.end as classeEnd, count(cours.id) as nbCours, year(cours.start) as year, lpad(week(cours.start,3),2,"0") as week, min(cours.start) as firstCours, max(cours.end) as lastCours')
                ->join('cours_classes', 'classes.id', '=', 'cours_classes.id_Classes')
                ->join('cours', 'cours.id', '=', 'cours_classes.id_Cours')
                // ->whereRaw('(classes.start >= ? and classes.start <= ?) or (classes.end >= ? and classes.end <= ? )', [$date['start'], $date['end'], $date['start'], $date['end']])
                ->where('cours.start', '>=', $date['start'])
                ->where('cours.end', '<=', $date['end'])
                ->orderByRaw('year asc, week asc, classes.nom asc')
                ->groupBy('classes.id', 'week')
                ->get();



            $tabClasses = [];
            $weeks = [];
            $periodFirstCours = $date['end'];
            $periodLastCours = $date['start'];

            foreach ($dbweeks as $dbweek) {
                if ($dbweek->firstCours < $periodFirstCours) {
                    $periodFirstCours = DateTimeImmutable::createFromFormat('Y-m-d h:i:s', $dbweek->firstCours);
                }
                if ($dbweek->lastCours > $periodLastCours) {
                    $periodLastCours = DateTimeImmutable::createFromFormat('Y-m-d h:i:s', $dbweek->lastCours);
                }
                $week_id = "$dbweek->year-$dbweek->week";
                $classe_id = $dbweek->id;
                $classe_nom = $dbweek->nom;
                $classe_nbCours = $dbweek->nbCours;
                if (isset($tabClasses[$classe_id])) {
                    $tabClasses[$classe_id]['nbCours'] += $classe_nbCours;
                } else {
                    $tabClasses[$classe_id] = [
                        'id' => $classe_id,
                        'nom' => $classe_nom,
                        'nbCours' => $classe_nbCours,
                        'firstCours' => $dbweek->firstCours,
                        'lastCours' => $dbweek->lastCours,
                        'classeStart' => $dbweek->classeStart,
                        'classeEnd' => $dbweek->classeEnd,
                        'hasPDF' => false,
                        'weeks' => []
                    ];
                };
                $tabClasses[$classe_id]['weeks'][$week_id] ??= 0;
                $tabClasses[$classe_id]['weeks'][$week_id] += $classe_nbCours;
            }

            uasort($tabClasses, function ($a, $b) {
                return $a['nom'] <=> $b['nom'];
            });

            $j = 0;
            foreach ($period as $dateP) {
                $date = DateTimeImmutable::createFromInterface($dateP)->modify('previous monday');
                $week_id = $date->format('Y-W');

                $weeks[$j] =
                    [
                        'week_id' => $week_id,
                        'year' => $date->format('Y'),
                        'week' => $date->format('W'),
                        'number' => $date->format('W'),
                        'firstDay' => $date->format('d/m'),
                        'lastDay' => (DateTime::createFromImmutable($date))->add(new DateInterval('P5D'))->format('d/m'),
                        'nbCours' => 0,
                        'hasPDF' => false,
                        'classes' => []
                    ];


                foreach ($tabClasses as $classe) {
                    $filename = uploadFileName($weeks[$j]['year'], $weeks[$j]['week'], $classe['id'])[0];

                    $hasPdf = file_exists($filename);

                    $weeks[$j]['classes'][] = [
                        'id' => $classe['id'],
                        'nom' => $classe['nom'],
                        'nbCours' => $tabClasses[$classe['id']]['weeks'],
                        'hasPDF' => $hasPdf,
                        'nbCours' => $tabClasses[$classe['id']]['weeks'][$week_id] ?? 0,
                    ];

                    if ($hasPdf) {
                        $tabClasses[$classe['id']]['hasPDF'] = true;
                        $weeks[$j]['hasPDF'] = true;
                    }
                    $weeks[$j]['nbCours'] += $tabClasses[$classe['id']]['weeks'][$week_id] ?? 0;
                };

                $j++;
            };


            $years  = [$dateStartEnd['start']->format('Y'), $dateStartEnd['end']->format('Y')];

            $current_next[$i] = [
                'name' => $cn,
                'startPeriod' => $dateStartEnd['start'],
                'endPeriod' => $dateStartEnd['end'],
                'year' => (($years[0] == $years[1]) ? $years[0] : $years[0] . '/' . $years[1]),
                'firstCours' => $periodFirstCours,
                'lastCours' => $periodLastCours,
                'firstWeek' => $periodFirstCours->format('Y-W'),
                'lastWeek' => $periodLastCours->format('Y-W'),
                'weeks' => $weeks,
                'classes' => array_values($tabClasses),
            ];

            $i++;
        }

        return $response->withJson($current_next);
    } catch (\Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage(), 'exception' => $e], 500);
    }
});

$app->get('/plan/current_next_classe/{current_next}', function ($request, $response, array $args) {
    try {
        $current_next = $args['current_next'];
        $date = getStartEndByYear($current_next);
        $classes = Classes::whereRaw('(start >= ? and start <= ?) or (end >= ? and end <= ? )', [$date['start']->format('Y-m-d'), $date['end']->format('Y-m-d'), $date['start']->format('Y-m-d'), $date['end']->format('Y-m-d')])->orderBy('nom')->get();

        foreach ($classes as $classe) {
            $classe['nbCours'] = $classe->nbCours();
        }

        return $response->withJson($classes, 200);
    } catch (\Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage(), 'exception' => $e], 500);
    }
});

//Lien Ical public pour les applications calendriers des professeurs
$app->get('/ical/classe/{classeId}',  function ($request, $response, array $args) {
    $classeId = $args['classeId'];
    try {
        $classe = Classes::where('id', $classeId)->firstOrFail();
        $cours = Classes::where('id', $classeId)->with('cours')->get();
        $events = [];


        $i = 0;
        foreach ($cours[0]['cours'] as $cour) {
            // $coursDetail = Cours::where('id', $cour->id)->with('matiere', 'salle')->get();
            // $start = new dateTime($classe->start);
            // $end = new dateTime($classe->end);



            if ($cour->end < $classe->start || $cour->start > $classe->end) {
                continue;
            }

            $eventParams = array(
                'start' => new DateTime($cour->start),
                'end' => new DateTime($cour->end),
                'summary' => 'Cours ' . ' de ' . $cour->matiere->nom . ' - ' . $cour->salle->nom
            );


            $events[$i] = new CalendarEvent($eventParams);
            $i++;
        }



        $calParams = array(
            'events' => $events
        );

        $calendar = new Calendar($calParams);
        $calendar->generateDownload();
        return $response->withStatus(200);
    } catch (\Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage() . ' ' . $classe, 'exception' => $e], 500);
    }
});
