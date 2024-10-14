webApp.controller(
  "ActivationController",
  function ($scope, serviceActivation, id, token) {
    $scope.user = {};
    $scope.user.id = id;
    $scope.token = token;
    $scope.activation = {};

    serviceActivation.Activation(id, token).then(
      function (response) {
        if (response === 1) {
          $scope.activation.message = "Votre compte a bien été activé";
        } else {
          $scope.activation.message =
            "Il y a eu un problème lors de l'activation";
          console.error(response);
        }
      },
      function () {
        $scope.activation.message =
          "Il y a eu un problème lors de l'activation";
      }
    );

    $scope.submit = function (user) {
      if (user.password !== user.password_confirm) {
        $scope.form.message = "Les mots de passe ne correspondent pas";
        return;
      }
      if (user.password.length < 8) {
        $scope.form.message =
          "Le mot de passe doit contenir au moins 8 caractères";
        return;
      }
      user.token = $scope.token;
      serviceActivation
        .SetFirstPassword(user)
        .then(function (response) {
          console.log("SetFirstPassword", response);
          if (response === 1) {
            $scope.creationMdp = true;
            $scope.form.message =
              "Le mot de passe a été créé correctement, vous pouvez vous connectez avec le lien en haut à droite";
          } else {
            $scope.form.message =
              "Il y a eu un problème lors de la création du mot de passe";
            console.error(response);
          }
        })
        .catch(function (err) {
          $scope.form.message =
            "Il y a eu un problème lors de la création du mot de passe";
          console.error(err);
        });
    };
  }
);
