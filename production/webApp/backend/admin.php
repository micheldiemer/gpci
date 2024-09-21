<?php

/**
 * \file        admin.php
 * \author      SIO-SLAM 2014-2016
 * \version     1.0
 * \date        12/04/2015
 * \brief       administrator routes
 *
 * \details     this file contains all the routes for administrator role
 */

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

$app->get('/admin/personnes', function ($request, $response, array $args) use ($authenticateWithRole) {

    $authenticateWithRole('administrateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }

    /// Looking for all the users (even the one who aren't enabled) and the corresponding role
    $users = Users::with('roles')->select('id', 'login', 'firstName', 'lastName', 'email', 'enabled', 'connected')->get();
    /// Sending them as JSON then it is readable by AngularJS
    return $response->withJson($users);
});

$app->get('/admin/personnes/{id}', function ($request, $response, array $args) use ($authenticateWithRole) {

    $authenticateWithRole('administrateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    $personne = Users::where('id', $id)->with('roles')->select('id', 'login', 'firstName', 'lastName', 'email', 'enabled')->firstOrFail();
    return $response->withJson($personne);
});

$app->post('/admin/personnes', function ($request, $response, array $args) use ($authenticateWithRole) {

    $authenticateWithRole('administrateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $json = $request->getBody();
        $data = json_decode($json, true);
        $token = uniqid(rand(), true);
        $username = strtolower($data['firstName'][0] . $data['lastName']);
        if (Users::where('login', $username)->exists()) {
            for ($i = 1; $i < 100; $i++) {
                $username_adjusted = $username . strval($i);
                if (Users::where('login', $username_adjusted)->exists())
                    continue;
                else {
                    $username = $username_adjusted;
                    break;
                }
            }
        }
        $personne = new Users;
        $personne->login = $username;
        $personne->password = '';
        $personne->firstName = $data['firstName'];
        $personne->lastName = $data['lastName'];
        $personne->email = $data['email'];
        $personne->token = $token;
        $personne->enabled = 0;
        $personne->connected = 0;
        $personne->save();

        //sync roles
        $newRoles = [];
        foreach ($data['roles'] as $role) {
            array_push($newRoles, $role['id']);
        }

        $personne->roles()->sync($newRoles);

        // Liste variable a utilisé dans le templat
        $list_var = array(
            'user_id' => $personne->id,
            'user_token' => $personne->token,
            'user_firstname' => $personne->firstName,
            'user_lastname' => $personne->lastName,
            'user_login' => $personne->login,

        );

        $template = file_get_contents("templates/new_user.html");
        // ajout des valeur des variables dans le template
        foreach ($list_var as $cle => $valeur) {
            $template = str_replace('{{ ' . $cle . ' }}', $valeur, $template);
        }
        // ajout du header pour responsive, css ...
        $template = file_get_contents("templates/header.html") . $template;
        // creation du mail
        global $mailer;
        global $smtpSettings;
        $message = (new Email())
            ->from(new Address($smtpSettings['MAIL_FROM'][0], $smtpSettings['MAIL_FROM'][1]))
            ->subject('Création de votre compte GPCI')
            ->to(new Address($data['email'], $data['firstName'] + '' + $data['lastName']))
            ->html($template);

        // envoie
        try {
            $results = $mailer->send($message);
        } catch (Exception $e) {
            $results = $e;
        }

        // Print the results, 1 = message sent!
        ($response->getBody())->write($results);
        return $response;
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});

$app->put('/admin/personnes/{id}', function ($request, $response, array $args) use ($authenticateWithRole) {

    $authenticateWithRole('administrateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    try {
        $json = $request->getBody();
        $data = json_decode($json, true);
        $personne = Users::where('id', $id)->with('roles')->firstOrFail();
        $personne->firstName = $data['firstName'];
        $personne->lastName = $data['lastName'];
        $personne->email = $data['email'];
        $personne->enabled = $data['enabled'];
        if ($personne->id == $_SESSION['id'])
            $personne->enabled = true;
        $personne->save();

        //sync roles
        $newRoles = [];
        foreach ($data['roles'] as $role) {
            array_push($newRoles, $role['id']);
        }

        $personne->roles()->sync($newRoles);

        return $response->setBody->write('1');
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});

$app->delete('/admin/personnes/{id}', function ($request, $response, array $args) use ($authenticateWithRole) {

    $authenticateWithRole('administrateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $id = $args['id'];
    try {
        $personne = Users::where('id', $id)->with('roles')->firstOrFail();
        $personne->enabled = false;
        $personne->save();
        ($response->getBody())->write('1');
        return $response;
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});
