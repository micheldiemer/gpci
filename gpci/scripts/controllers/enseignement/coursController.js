webApp.controller(
  "EnsCoursController",
  function ($scope, Session, ensCours, Restangular, BASE_URL) {
    $scope.id = Session.id;
    $scope.BASE_URL = BASE_URL;
    $scope.cours = [];

    updateTable();

    function updateTable() {
      ensCours.getCours().then(function (cours) {
        angular.forEach(cours, function (element) {
          element.periode =
            moment(element.start).hour() == 8 ? "matin" : "après-midi";
          element.date = moment(element.start).format("DD-MM-YYYY");
        });
        Restangular.copy(cours, $scope.cours);
        $scope.coursView = [].concat($scope.coursr, BASE_URL);
      });
    }
  }
);
