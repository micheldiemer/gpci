webApp.factory("enseignantsService", function ($q, notifService, Restangular) {
  let list = [];

  function updateList() {
    return $q(function (resolve, reject) {
      //TO DO Lancer toastr chargement
      Restangular.all("plan/enseignant")
        .getList()
        .then(
          function (data) {
            angular.forEach(data, function (element) {
              element.fullName = element.firstName + " " + element.lastName;
            });
            list = [].concat(data);
            //TO DO SUCCESS TOASTR
            resolve();
          },
          function () {
            //TO DO ERROR TOASTR
            reject();
          }
        )
        .catch(() => {
          console.error("plan/enseignant all erreur");
        });
    });
  }

  function getList() {
    return $q(function (resolve, reject) {
      if (list && list.length > 0) {
        resolve(list);
      } else {
        updateList().then(function () {
          resolve(list);
        });
      }
    });
  }

  function getOne(id) {
    return $q(function (resolve, reject) {
      //TO DO Lancer toastr chargement
      Restangular.one("plan/enseignant", id)
        .get()
        .then(
          function (data) {
            //TO DO SUCCESS TOASTR
            resolve(data);
          },
          function () {
            //TO DO ERROR TOASTR
            reject();
          }
        )
        .catch(() => {
          console.error("plan/enseignant id errreur");
        });
    });
  }

  function save(enseignant) {
    return $q(function (resolve, reject) {
      notifService.saving();
      enseignant.save().then(
        function () {
          notifService.saved();
          resolve();
        },
        function (response) {
          notifService.error(response.data.message);
          reject();
        }
      );
    });
  }

  return {
    updateList: updateList,
    getList: getList,
    getOne: getOne,
    save: save,
  };
});
