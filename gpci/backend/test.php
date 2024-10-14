<?php

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    return $response->withJson("Hello, $name");
});

$app->get('/test', function (Request $request, Response $response, array $args) {
    return $response->withJson("test");
});


$app->get('/fakelogin/{name}', function ($request, $response, array $args) {
    try {
        $name = $args['name'];

        // $json = $request->getBody(); ///< getBody get the request sent by the log in form
        $data['name']  = $name;
        $token = uniqid(rand(), true);
        if (isset($data)) {
            unset($_SESSION['id']);
            unset($_SESSION['token']);
            if (!isset($_SESSION['id'])) { ///< if the user isn't logged in, this test will match the user's data corresponding to the user's id
                $userTemp = Users::where('login', $data['name'])->firstOrFail();
                // $password = sha1($userTemp->hash . sha1($data['password']));
                // $user_obj = Users::whereRaw('login = ? and password = ?', [$data['name'], $password])->with('roles')->firstOrFail();
                $user_obj = Users::whereRaw('login = ? ', [$data['name']])->with('roles')->firstOrFail();
                $_SESSION['id'] = $user_obj->id;
                $_SESSION['token'] = $token;
            } else {    ///< if the user is already logged in, the previous assignement is already done, we can skip it
                $id = $_SESSION['id'];
                $user_obj = Users::where('id', $id)->with('roles')->firstOrFail();
            }

            if ($user_obj) {
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
                    "token" => $token,
                    "home" => $user_home,
                    "id" => $user_obj->id,
                    "email" => $user_obj->email,
                    "theme" => $user_obj->theme
                );
                return $response->withJson($userInfo);
            }
            ///< The last lines are the errors cases
        } else {
            return $response->withStatus(400);
        }
    } catch (Exception $e) {
        return $response->withJson($e)->withStatus(400);
    }
});
