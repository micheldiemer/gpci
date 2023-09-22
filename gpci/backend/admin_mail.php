<?php


$app->get('/admin/specialmail', function() use ($app, $mailer) {
    try{
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

        $template = file_get_contents("templates/new_user.html", FILE_TEXT);
        // ajout des valeur des variables dans le template
        foreach($list_var as $cle => $valeur) {
            $template = str_replace('{{ '.$cle.' }}', $valeur, $template);
        }     
        // ajout du header pour responsive, css ...
        $template = file_get_contents("templates/header.html", FILE_TEXT) . $template; 
        // creation du mail
        $message = Swift_Message::newInstance('Création de votre compte GPCI')
            ->setFrom(array('ifide@ifide.net' => 'IFIDE SupFormation'))
            ->setTo(array($data['email'] => $data['firstName'] + '' + $data['lastName']))
            ->setBcc('supformation@ifide.net')
            ->setBody($template, "text/html")
            ->setContentType("text/html");

        // envoie
        try {
            $results = $mailer->send($message);
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->setBody('ok');
        }catch(Exception $e) {
            $results = $e;
        }

        // Print the results, 1 = message sent!
        $app->response->setBody($results);
    } catch(Exception $e) {
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody($e);
    }
});

?>
