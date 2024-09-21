<?php

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

$app->get('/admin/specialmail', function ($request, $response, array $args) {
    global $mailer;
    global $smtpSettings;
    try {
        $personne = Users::where('login', 'tboos')->with('roles')->select('id', 'login', 'firstName', 'lastName', 'email', 'token', 'enabled')->firstOrFail();

        // Liste variable a utilisé dans le templat
        $list_var = array(
            'user_id' => $personne->id,
            'user_token' => $personne->token,
            'user_firstname' => $personne->firstName,
            'user_lastname' => $personne->lastName,
            'user_login' => $personne->login,

        );
        $data = $personne;

        $template = file_get_contents("templates/new_user.html");
        // ajout des valeur des variables dans le template
        foreach ($list_var as $cle => $valeur) {
            $template = str_replace('{{ ' . $cle . ' }}', $valeur, $template);
        }
        // ajout du header pour responsive, css ...
        $template = file_get_contents("templates/header.html") . $template;
        // creation du mail
        $message =
            $message = (new Email())
            ->from(new Address($smtpSettings['MAIL_FROM'][0], $smtpSettings['MAIL_FROM'][1]))
            ->subject('Création de votre compte GPCI')
            ->to(new Address($data['email'], $data['firstName'] + '' + $data['lastName']))
            ->bcc(new Address($smtpSettings['MAIL_BCC'][0], $smtpSettings['MAIL_BCC'][1]))
            ->html($template);
        // envoie
        try {
            $results = $mailer->send($message);
            return $response->withJson(1);
        } catch (Exception $e) {
            return $response->withJson($e);
        }
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});
