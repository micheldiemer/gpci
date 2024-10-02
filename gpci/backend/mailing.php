<?php

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

// Create Transport
$MAILER_DSN = 'smtp://' . $smtpSettings['MAIL_USERNAME'] . ':' . $smtpSettings['MAIL_PASSWORD'] . '@' . $smtpSettings['MAIL_HOST'] . ':' .  $smtpSettings['MAIL_PORT'];

$transport =
    Transport::fromDsn($MAILER_DSN);


// Create Mailer with our Transport.
$mailer = new Mailer($transport);

function mailAssignationCours($cours, $mailer)
{

    // Conversion date pour extraire Date de Heure séparément
    $dt_start = new DateTime($cours->start);
    $dt_end = new DateTime($cours->end);

    // Extraction date et heure
    $date = $dt_start->format('d/m/Y');
    $time_start = $dt_start->format('H:i:s');
    $time_end = $dt_end->format('H:i:s');

    $liste_classe = '';
    $i = 0;
    $len = count($cours->classes);
    // Création liste de classe pour template
    foreach ($cours->classes as $classe) {
        if ($len <= 1) {
            $liste_classe .= 'la classe ';
        } else {
            $liste_classe .= 'les classes ';
        }
        $liste_classe .= $classe['nom'];
        if ($i != $len - 1) {
            $liste_classe .= ',';
        }
        $i++;
    }
    $matiere = Matieres::where('id', $cours->id_Matieres)->firstOrFail();

    // Liste variable a utilisé dans le template
    $list_var = array(
        'user_firstname' => $cours->user->firstName,
        'user_lastname' => $cours->user->lastName,
        'cours_date' => $date,
        'cours_start' => $time_start,
        'cours_end' => $time_end,
        'classe' => $liste_classe,
        'matiere' => $matiere['nom'],
        'BASE_URL' => BASE_URL,
    );

    $template = file_get_contents("templates/assignation_cours.html");

    // ajout des valeur des variables dans le template
    foreach ($list_var as $cle => $valeur) {
        $template = str_replace('{{ ' . $cle . ' }}', $valeur, $template);
    }

    // ajout du header pour responsive, css ...
    $template = file_get_contents("templates/header.html") . $template;
    global $smtpSettings;

    // creation du mail
    $message = (new Email())
        ->from(new Address($smtpSettings['MAIL_FROM'][0], $smtpSettings['MAIL_FROM'][1]))
        ->subject('Assignation d\'un cours à IFIDE SupFormation')
        ->to(new Address($cours->user->email, $cours->user->firstName + '' + $cours->user->lastName))
        ->html($template);

    // envoie
    $results = $mailer->send($message);
}

function mailAnnulationCours($cours, $mailer)
{

    // Conversion date pour extraire Date de Heure séparément
    $dt_start = $cours->start;
    $dt_end = $cours->end;

    // Extraction date et heure
    $date = $dt_start->format('d/m/Y');
    $time_start = $dt_start->format('H:i:s');
    $time_end = $dt_end->format('H:i:s');

    // Liste variable a utilisé dans le template
    $list_var = array(
        'user_firstname' => $cours->user->firstName,
        'user_lastname' => $cours->user->lastName,
        'cours_date' => $date,
        'cours_start' => $time_start,
        'cours_end' => $time_end,
        'BASE_URL' => BASE_URL,
    );

    $template = file_get_contents("templates/suppression_cours.html");

    // ajout des valeur des variables dans le template
    foreach ($list_var as $cle => $valeur) {
        $template = str_replace('{{ ' . $cle . ' }}', $valeur, $template);
    }

    // ajout du header pour responsive, css ...
    $template = file_get_contents("templates/header.html") . $template;

    // creation du mail
    global $smtpSettings;
    $message = (new Email())
        ->from(new Address($smtpSettings['MAIL_FROM'][0], $smtpSettings['MAIL_FROM'][1]))
        ->subject('Suppression d\'un de vos cours à IFIDE SupFormation')
        ->to(new Address($cours->user->email, $cours->user->firstName . ' ' . $cours->user->lastName))
        ->html($template);

    // envoie
    $results = $mailer->send($message);
}
