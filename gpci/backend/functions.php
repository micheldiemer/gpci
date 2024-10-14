<?php

/**
 * \file        function.php
 * \author      SIO-SLAM 2014-2016
 * \version     1.0
 * \date        12/04/2015
 * \brief       functions and variables used in the backend
 *
 * \details     this file contains all the functions and some global variables for the backend
 */



function getDateList($week, $year)
{
    $date = array();
    for ($day = 1; $day <= 5; $day++) {
        array_push($date, date('Y-m-d', strtotime($year . "W" . $week . $day)));
    }
    return $date;
}


function getStartAndEndDate($week, $year): array
{
    $date = new DateTime();
    $date->setISODate($year, $week);
    $return[0] = $date->format('Y-m-d');
    $return[1] = $date->add(new DateInterval('P6D'))->format('Y-m-d');
    return $return;
}

function getStartEndByYear($current_next): array
{
    if ($current_next == 'current') {
        if (date('Y-m-d', strtotime("now")) >= date('Y-m-d', strtotime("first day of august"))) {
            $date['start'] = date('Y-m-d', strtotime("first day of august"));
            $date['end'] = date('Y-m-d', strtotime("last day of july next year"));
        } else {
            $date['start'] = date('Y-m-d', strtotime("first day of august last year"));
            $date['end'] = date('Y-m-d', strtotime("last day of july"));
        }
    } else {
        if (date('Y-m-d', strtotime("now")) >= date('Y-m-d', strtotime("first day of august"))) {
            $date['start'] = date('Y-m-d', strtotime("first day of august next year"));
            $date['end'] = date('Y-m-d', strtotime("last day of july +2 years"));
        } else {
            $date['start'] = date('Y-m-d', strtotime("first day of august"));
            $date['end'] = date('Y-m-d', strtotime("last day of july next year"));
        }
    }

    $date['start'] = DateTimeImmutable::createFromFormat('Y-m-d', $date['start']);
    $date['start']->setTime(7, 0, 0);
    $date['end'] = DateTimeImmutable::createFromFormat('Y-m-d', $date['end']);
    $date['end']->setTime(19, 0, 0);

    return $date;
}

/**
 * 				\b function
 * \brief 		Get the role the user must have and save it in #$authenticateWithRole variable
 * \param[in] 	$role_required is a string wich is the title of the role the user must have
 * \return 		a boolean with the state \a  True or an error code
 */

function generatePassword()
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < 8; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}

$authenticateWithRole = function ($role_required, $response) {
    if (!isset($_SESSION['id'])) {    ///< If the session ID isn't recognized, error 401 is sent
        return $response->withStatus(401);
    } else {                        ///< In this case, we check if the user id is correctly associated with his role, if not, error 401 is sent
        $id = $_SESSION['id'];
        $role_db = Roles::where('role', $role_required)->firstOrFail();
        $user = Users::where('id', $id)->with('roles')->firstOrFail();    ///<	Matching the current role to the users id
        foreach ($user->roles as $role) {
            if ($role->priority <=  $role_db->priority)
                return $response->withStatus(200);
        }

        return $response->withStatus(401);
    };
};


function uploadFileName($year, $week, $class): array
{
    $directory = __DIR__ . '/uploads';
    $dbClasse =  Classes::find($class);
    $cf = preg_replace("/[^A-Za-z0-9\- ]/", '_', $dbClasse->nom);
    $fileName = $year . "-" . $week . "-" . $cf . ".pdf";

    return ["$directory/$fileName", $directory, $fileName];
}
