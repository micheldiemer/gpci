webApp.controller(
  "PlanSallesController",
  function ($scope, $uibModal, sallesService) {
    $scope.salles = [];

    sallesService.getList().then(function (data) {
      $scope.salles = data;
      $scope.sallesView = [].concat($scope.salles);
    });

    $scope.open = function (salle) {
      //Il faut mettre l'idList dans $scope pour qu'il soit accessible dans resolve:
      $scope.salle = salle;

      const modalDetails = $uibModal.open({
        animation: true,
        templateUrl: "modals/sallesDetails.html",
        controller: "SalleDetails",
        size: "md",
        resolve: {
          salle: function () {
            return $scope.salle;
          },
        },
      });

      modalDetails.result.then(function (salleP) {
        if (salleP.toDelete) {
          $scope.remove(salleP);
        } else {
          sallesService.save(salleP).then(function () {
            updateTable();
          });
        }
      });
    };

    $scope.remove = function (salle) {
      sallesService.remove(salle).then(function () {
        updateTable();
      });
    };

    function updateTable() {
      sallesService.updateList().then(function () {
        sallesService.getList().then(function (salles) {
          $scope.salles = salles;
          $scope.sallesView = [].concat($scope.salles);
        });
      });
    }
  }
);
