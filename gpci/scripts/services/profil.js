webApp.factory("profilService", function ($q, Restangular) {
  return {
    changePassword: function (passwords) {
      return $q(function (resolve, reject) {
        Restangular.one("changePassword")
          .doPOST(passwords)
          .then(
            function () {
              resolve("ok");
            },
            function () {
              reject("Il y a eu un probl�me sur le serveur");
            }
          );
      });
    },
    changeEmail: function (emails) {
      return $q(function (resolve, reject) {
        Restangular.one("changeEmail")
          .doPOST(emails)
          .then(
            function () {
              resolve("ok");
            },
            function () {
              reject("Il y a eu un probl�me sur le serveur");
            }
          );
      });
    },
  };
});
