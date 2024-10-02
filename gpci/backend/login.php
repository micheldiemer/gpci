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
        $token = uniqid(rand(), true);

        $session = isset($_SESSION['id']);
        $hasData = isset($data) && isset($data['name']) && isset($data['password']) && $data['name'] != '' && $data['password'] != '';
        $user_obj = null;

        if (!$hasData && !$session) {
            return $response->withJson('XX')->withStatus(401);
        } else if ($hasData && $session) { ///< if the user is already logged in, we need to disconnect him before connecting another user
            $userTemp = Users::where('id', $_SESSION['id'])->with('roles')->first();
            if (is_null($userTemp) || $userTemp->id != $_SESSION['id']) {
                unset($_SESSION['id']);
                unset($_SESSION['token']);
                session_destroy();
                session_start();
                $session = false;
            }
        }
        if ($hasData && !$session) { ///< if the user isn't logged in, this test will match the user's data corresponding to the user's id
            $userTemp = Users::where('login', $data['name'])->first();

            if (is_null($userTemp)) {
                return $response->withJson('Login ou mot de passe incorrect')->withStatus(401);
            }

            $password = sha1($userTemp->hash . sha1($data['password']));
            $user_obj = Users::whereRaw('login = ? and password = ?', [$data['name'], $password])->with('roles')->first();

            if (is_null($user_obj)) {
                return $response->withJson('Login ou mot de passe incorrect')->withStatus(401);
            }

            $_SESSION['id'] = $user_obj->id;
            $_SESSION['token'] = $token;
        } else {    ///< if the user is already logged in, the previous assignement is already done, we can skip it
            $id = $_SESSION['id'];
            $user_obj = Users::where('id', $id)->with('roles')->firstOrFail();
        }

        if (is_null($user_obj)) {
            return $response->withJson('Login ou mot de passe incorrect')->withStatus(401);
        }


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
            "token" => $token,
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
