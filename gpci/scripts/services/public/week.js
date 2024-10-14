webApp.factory("weekService", function ($q, notifService, Restangular) {
  let list = [];

  function updateList(year) {
    return $q(function (resolve, reject) {
      //TO DO Lancer toastr chargement
      Restangular.one("plan/weeks")
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
        .catch(function (err) {
          console.error("plan/weeks erreur ", err);
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
