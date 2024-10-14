<?php

/**
 * \file        index.php
 * \author      SIO-SLAM 2014-2016
 * \version     1.1
 * \date        11/19/2015
 * \brief       backend index
 *
 * \details     this file contains the includes for the backend
 */

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Factory\AppFactory;

session_start();

require __DIR__ . '/vendor/autoload.php';
$app = AppFactory::create();
$app->setBasePath('/gpci/backend');
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);



require_once file_exists('settings.prod.php') ? 'settings.prod.php' : 'settings.git.php';
require_once 'model.php';
require_once 'functions.php';
require_once 'mailing.php';
require_once 'login.php';
require_once 'planificateur.php';
require_once 'enseignant.php';
require_once 'admin.php';
require_once 'profil.php';
require_once 'icalGenerator.php';
require_once 'public.php';
require_once 'test.php';
if ($profiler) require_once 'profiler.php';

$app->get('/', function (Request $request, Response $response, array $args) {
    return $response->withJson('GPCI');
});

$app->get('/roles', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('administrateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $roles = Roles::get();

    // return $response->withJson([$_SESSION, $roles]);
    return $response->withJson($roles);
});

$app->get('/matieres', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    $response = $authenticateWithRole('planificateur', $response);
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $matieres = Matieres::get();

    return $response->withJson($matieres);
});

// //Partie de l'API accessible sans identification

$app->get('/activation/{id}/token/{token}', function ($request, $response, array $args) {
    $id = $args['id'];
    $token = $args['token'];
    $user = Users::find($id)->where('token', $token)->first();
    if ($user) {
        $user->enabled = 1;
        $user->save();
        return $response->withJson(1, 200);
    } else {
        return $response->withJson(['message' => "Erreur de token"], 400);
    }
});

// //Cours
$app->get('/public/cours', function ($request, $response, array $args) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $cours_obj = Cours::with('user', 'matiere', 'classes', 'salle')
        ->where('start', '>=', $start)
        ->where('start', '<=', $end)
        ->where('assignationSent', 1)
        ->get();
    return $response->withJson($cours_obj);
});

$app->post('/set_firstpassword', function ($request, $response, array $args) {

    $json = $request->getBody();
    $data = json_decode($json, true);

    if (!$data || !isset($data['id']) || !isset($data['password']) || !isset($data['token']) || !isset($data['password_confirm'])) {
        return $response->withStatus(400);
    }

    $user = Users::find($data['id'])->where('token', $data['token'])->first();
    if (is_null($user)) {
        return $response->withJson('Utilisateur non trouvé', 401);
    }

    // if ($user->password !== '') {
    //     return $response->withJson('Le mot de passe a déjà été défini. Veuillez contacter l’administrateur du site', 403);
    // }

    if ($user->enabled == 0) {
        return $response->withJson('Utilisateur non activé', 403);
    }

    if ($data['password'] !== $data['password_confirm']) {
        return $response->withStatus(400);
    }

    $hash = uniqid(rand(), true);
    $user->hash = $hash;
    $user->password = sha1($hash . sha1($data['password']));
    $user->save();

    return $response->withJson(1, 200);
});

$app->post('/theme', function (Request $request, Response $response, array $args) use ($authenticateWithRole) {

    foreach (['administrateur', 'planificateur', 'enseignant'] as $role) {
        $response = $authenticateWithRole($role, $response);
        if ($response->getStatusCode() == 200) break;
    }
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    try {
        $user = Users::where('id', $_SESSION['id'])->first();
        $json = $request->getBody();
        $data = json_decode($json, true);
        $user->theme = $data["theme"];
        $user->save();

        return $response->withJson(1, 200);
    } catch (Exception $e) {
        return $response->withJson(['message' => "Erreur " . $e->getCode() . ' ' . $e->getMessage()], 500);
    }
});

$app->run();


function jsonGetError()
{
    $errNum = json_last_error();
    switch ($errNum) {
        case JSON_ERROR_NONE:
            echo ' - Aucune erreur';
            break;
        case JSON_ERROR_DEPTH:
            echo ' - Profondeur maximale atteinte';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Inadéquation des modes ou underflow';
            break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Erreur lors du contrôle des caractères';
            break;
        case JSON_ERROR_SYNTAX:
            echo ' - Erreur de syntaxe ; JSON malformé';
            break;
        case JSON_ERROR_UTF8:
            echo ' - Caractères UTF-8 malformés, probablement une erreur d\'encodage';
            break;
        default:
            echo ' - Erreur inconnue : ' . $errNum;
            break;
    }
}
