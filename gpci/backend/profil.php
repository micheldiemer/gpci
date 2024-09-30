<?php
$app->post('/changePassword', function ($request, $response, array $args) use ($authenticateWithRole) {

    foreach(['administrateur','planificateur','enseignant'] as $role) {
      $response = $authenticateWithRole($role, $response);
      if ($response->getStatusCode() == 200) break;
    }
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $json = $request->getBody();
    $data = json_decode($json, true);
    if ($data['newPassword'] == $data['newPasswordConfirm']) {
        $hash = uniqid(rand(), true);
        $newPassword = sha1($hash . sha1($data['newPassword']));
        Users::where('id', $_SESSION['id'])->update(['password' => $newPassword, 'hash' => $hash]);
        return $response->withStatus(200);
    } else {
        return $response->withStatus(400);
    }
});

$app->post('/changeEmail', function ($request, $response, array $args) use ($authenticateWithRole) {

    foreach(['administrateur','planificateur','enseignant'] as $role) {
      $response = $authenticateWithRole($role, $response);
      if ($response->getStatusCode() == 200) break;
    }
    if ($response->getStatusCode() !== 200) {
        return $response;
    }
    $json = $request->getBody();
    $data = json_decode($json, true);
    if ($data['newEmail'] == $data['newEmailConfirm']) {
        Users::where('id', $_SESSION['id'])->update(['email' => $data['newEmail']]);
        return $response->withStatus(200);
    } else {
        return $response->withStatus(400);
    }
});
