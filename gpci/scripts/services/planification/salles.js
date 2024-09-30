webApp.factory("sallesService", function ($q, notifService, Restangular) {
  let list = [];

  function updateList() {
    return $q(function (resolve, reject) {
      //TO DO Lancer toastr chargement
      Restangular.all("plan/salle")
        .getList()
        .then(
          function (data) {
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
          console.error("plan/salle erreur");
        });
    });
  }

  function getList() {
    return $q(function (resolve, reject) {
      if (list) {
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
      Restangular.one("plan/salle", id)
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
          console.error("plan/salle erreur");
        });
    });
  }

  function getNew() {
    return Restangular.one("plan/salle");
  }

  function save(salle) {
    return $q(function (resolve, reject) {
      notifService.saving();
      salle.save().then(
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

  function remove(salle) {
    return $q(function (resolve, reject) {
      notifService.deleting();
      salle.remove().then(
        function () {
          notifService.deleted();
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

    getNew: getNew,

    save: save,

    remove: remove,
  };
});
