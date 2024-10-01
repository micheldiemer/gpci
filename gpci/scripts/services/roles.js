webApp.factory("serviceRoles", function (Restangular) {
  return {
    getRoles: function () {
      console.debug(
        'webApp.factory("serviceRoles"',
        Restangular.all("roles").getList()
      );
      return Restangular.all("roles").getList();
    },
  };
});
