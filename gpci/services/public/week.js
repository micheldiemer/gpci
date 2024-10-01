webApp.factory("weekService", function ($q, notifService, Restangular) {
  let list = [];

  function updateList(year) {
    return $q(function (resolve, reject) {
      //TO DO Lancer toastr chargement
      Restangular.one("plan/weeks", year)
        .getList()
        .then(
          function (data) {
            list = [].concat(data.plain());
            //TO DO SUCCESS TOASTR
            resolve();
          },
          function () {
            //TO DO ERROR TOASTR
            reject();
          }
        )
        .catch(function () {
          console.error("plan/weeks erreur");
        });
    });
  }

  function getList(year) {
    return $q(function (resolve, reject) {
      updateList(year).then(function () {
        resolve(list);
      });
    });
  }

  return {
    updateList: updateList,

    getList: getList,
  };
});
