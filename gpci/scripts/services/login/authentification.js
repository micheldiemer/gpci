webApp.factory(
  "Authentification",
  function ($rootScope, $window, Session, AUTH_EVENTS, Restangular) {
    let authService = {};

    //la fonction login
    authService.login = function (user, success, error) {
      Restangular.all("login")
        .post(user)
        .then(
          function (data) {
            if (data) {
              const user = data;
              //Stockage des données utilisateurs dans le navigateur
              try {
                $window.sessionStorage["userData"] = JSON.stringify(user);
                Session.create(user);

                //Déclencher l'evenement loginSuccess
                $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                //lancer la fonction succès ( 2eme paramètre)
                success(user);
              } catch (error) {
                console.error(
                  "authentification.js JSON sessionStorage error",
                  error
                );
              }
            }
          },
          function () {
            $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
            error();
          }
        );
    };

    //verification si user est connecté
    authService.isAuthenticated = function () {
      return !!Session.user; // le double point d'exclamation force un booléan
    };

    //verification si user est autorisé
    authService.isAuthorized = function (authorizedRoles) {
      if (authorizedRoles === "any") return true;
      if (!authService.isAuthenticated()) return false;

      if (!angular.isArray(authorizedRoles)) {
        authorizedRoles = [authorizedRoles];
      }
      for (let i = 0; i < Session.roles.length; i++) {
        let role = Session.roles[i];
        if (authorizedRoles.indexOf(role) !== -1) return true;
      }
      return false;
    };

    //logout de l'utilisateur, destruction de la session ( javascript + naviguateur)
    authService.logout = function () {
      Restangular.one("logout").doPOST();
      Session.destroy();
      $window.sessionStorage.removeItem("userData");
      $window.location.reload();
      //$rootScope.$broadcast(AUTH_EVENTS.logoutSuccess);
    };

    return authService;
  }
);
