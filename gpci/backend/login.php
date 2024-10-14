<?php

/**
 * \file        login.php
 * \author      SIO-SLAM 2014-2016
 * \version     1.0
 * \date        12/04/2015
 * \brief       login
 *
 * \details     this file contains the login treatment
 */

$app->post('/login', function ($request, $response, array $args) {;
    try {
        $json = $request->getBody(); ///< getBody get the request sent by the log in form
        $data = json_decode($json, true);

        $sid = isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
        $hasData = isset($data) && isset($data['name']) && isset($data['password']) && $data['name'] != '' && $data['password'] != '';
        $user_obj = null && is_string($data['name']) && is_string($data['password']);

        if (!$hasData && !$sid) {
            return $response->withJson('Données manquantes ou incorrectes')->withStatus(401);
        };

        if ($hasData) {
            if ($sid) {
                unset($_SESSION['id']);
                unset($_SESSION['token']);
                $user_obj = null;
                session_destroy();
                session_start();
                $sid = 0;
            }

            $user_obj = Users::where('login', $data['name'])->with('roles')->first();

            if (!is_null($user_obj)) {
                $password = sha1($user_obj->hash . sha1($data['password']));

                // if ($password != $user_obj->password) {
                //     if ($data['name'] == '') {
                //         $user_obj->password = sha1($user_obj->hash . sha1($data['password']));
                //         $user_obj->save();
                //     }
                // }

                if ($password != $user_obj->password) {
                    return $response->withJson('Login ou mot de passe incorrect')->withStatus(401);
                }
            }
        }
        if ($sid) {
            $user_obj = Users::where('id', $sid)->with('roles')->first();
        }

        if (is_null($user_obj)) {
            return $response->withJson('Login incorrect', 401);
        }

        if ($user_obj->enabled == 0) {
            return $response->withJson('Compte désactivé', 401);
        }

        $_SESSION['id'] = $user_obj->id;
        $_SESSION['token'] = uniqid(rand(), true);

        $user_home = "";
        $user_obj->connected = true;
        $user_obj->save();  ///< to keep the online status in the database
        $role_priority = 0;
        $role_array = [];
        foreach ($user_obj->roles as $role) {
            array_push($role_array, $role['role']);
            if ($role['priority'] < $role_priority || $role_priority == 0) {
                $role_priority = $role['priority'];
                $user_home = $role['home'];
            }
        }

        $userInfo = array( ///< stock all the user's information's in the user_json variable
            "name" => $user_obj->login,
            "firstName" => $user_obj->firstName,
            "lastName" => $user_obj->lastName,
            "roles" => $role_array,
            "token" => $_SESSION['token'],
            "home" => $user_home,
            "id" => $user_obj->id,
            "email" => $user_obj->email,
            "theme" => $user_obj->theme
        );
        return $response->withJson($userInfo);
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(500);
    }
});

//Un logout, car si on ne détruit pas la session, on ne peut plus que se logger qu'avec le dernier compte connecté
//TO DO : Revoir le fonctionnement d'une session PHP
$app->post('/logout', function ($request, $response, array $args) {
    try {
        if (isset($_SESSION['id'])) {
            $user_obj = Users::where('id', $_SESSION['id'])->first();
            if ($user_obj) {
                $user_obj->update(["connected" => 0]);
            }
        }
        session_destroy();
        return $response->withStatus(204);
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});
