webApp.controller(
  "PlanAnneeController",
  function ($scope, $uibModal, $http, Session, weekService, BASE_URL, $state) {
    $scope.current_next = [];
    $scope.BASE_URL = BASE_URL;
    $scope.isPlanif = Session.isPlanif && $state.current.name != "annee";
    weekService.getList("current").then(function (current_next) {
      $scope.current_next = current_next;
      $scope.current_next = [].concat($scope.current_next);

      if (localStorage.getItem("SCROLL")) {
        let nbInterval = 10;
        const intervalID = setInterval(() => {
          const scroll = JSON.parse(localStorage.getItem("SCROLL"));
          if (
            window.innerHeight >= scroll.top &&
            window.innerWidth >= scroll.left
          ) {
            scroll.behavior = "instant";
            window.scrollTo(scroll);
            localStorage.removeItem("SCROLL");
            clearInterval(intervalID);
            nbInterval--;
            if (nbInterval == 0) clearInterval(intervalID);
          }
        }, 300);
      }
    });

    $http({
      method: "GET",
      url: BASE_URL + "/plan/years/current",
    }).then(
      function successCallback(response) {
        $scope.year = response.data.year;
      },
      function errorCallback(response) {}
    );

    $http({
      method: "GET",
      url: BASE_URL + "/plan/years/next",
    }).then(
      function successCallback(response) {
        $scope.nextyear = response.data.year;
      },
      function errorCallback(response) {}
    );

    $scope.onSave = function (year, weekNumber, classeId, classeNom) {
      localStorage.setItem(
        "SCROLL",
        JSON.stringify({ top: window.scrollY, left: window.scrollX })
      );
      const modalInstance = $uibModal.open({
        templateUrl: "modals/anneeDetails.html",
        controller: "ModalInstanceCtrl",

        resolve: {
          year: function () {
            return year;
          },
          weekNumber: function () {
            return weekNumber;
          },
          classeId: function () {
            return classeId;
          },
          classeNom: function () {
            return classeNom;
          },
        },
      });

      modalInstance.closed.then(function (result) {
        $state.go("planification.annee", {}, { reload: true });
      });
    };
  }
);

webApp.controller(
  "ModalInstanceCtrl",
  function (
    $scope,
    $uibModalInstance,
    Restangular,
    year,
    weekNumber,
    classeId,
    classeNom
  ) {
    $scope.fileExistsMessage = ""; // Message to display if file exists

    // Function to check if file exists
    function checkFileExists() {
      return Restangular.one(
        "upload/" + year + "/" + weekNumber + "/" + classeId
      )
        .get()
        .then(function (response) {
          return {
            exists: response.exists,
            filename: response.fileDirectory,
          };
        })
        .catch(function (error) {
          console.error("Error checking file existence:", error);
          return {
            exists: false,
            filename: "",
          };
        });
    }

    // Execute checkFileExists when modal opens
    $uibModalInstance.opened.then(function () {
      checkFileExists().then(function (result) {
        if (document.getElementById("weekClasse"))
          document.getElementById("weekClasse").innerHTML =
            classeNom + " - Semaine " + weekNumber;
        $scope.exists = result.exists;
        if (result.exists) {
          document.getElementById("message").innerHTML =
            "Un document existe déjà: " + result.filename;
          document.getElementById("saveButton").disabled = true;
          document.getElementsByClassName("selectFile")[0].disabled = true;
          document.getElementById("removeButton").disabled = false;
        } else {
          document.getElementById("message").innerHTML = "";
          document.getElementById("saveButton").disabled = true;
          document.getElementsByClassName("selectFile")[0].disabled = false;
          document.getElementById("removeButton").disabled = true;
        }
      });
    });

    $scope.save = function () {
      if (document.getElementsByClassName("selectFile")[0].files.length == 0) {
        $uibModalInstance.dismiss("save");
        return;
      }
      const File = document.getElementsByClassName("selectFile")[0].files[0];
      const formData = new FormData();
      formData.append("file", File);

      Restangular.one("upload/" + year + "/" + weekNumber + "/" + classeId)
        .withHttpConfig({ transformRequest: angular.identity })
        .customPOST(formData, "", undefined, { "Content-Type": undefined })
        .then(function (response) {
          if (response.error) {
            document.getElementById("message").innerHTML = response.msg;
          } else {
            document.getElementById("message").innerHTML =
              "Document enregistré.";

            $uibModalInstance.close($scope.uploadFile);
          }
        })
        .catch(function (error) {
          console.error("Error uploading file", error);
          document.getElementById("message").innerHTML = "Erreur.";
        });
    };

    $scope.remove = function () {
      checkFileExists().then(function (result) {
        $scope.exists = result.exists;
        if (result.exists) {
          Restangular.one("upload/" + year + "/" + weekNumber + "/" + classeId)
            .remove()
            .then(function (response) {
              if (response.deleted) {
                document.getElementById("message").innerHTML =
                  "Document supprimé.";
                document.getElementById("saveButton").disabled = true;
                document.getElementsByClassName(
                  "selectFile"
                )[0].disabled = false;
                document.getElementById("removeButton").disabled = true;
                $uibModalInstance.close("Document supprimé.");
              } else {
                console.error("Error deleting file:", response);
                document.getElementById("message").innerHTML = "Erreur.";
              }
              return;
            })
            .catch(function (error) {
              console.error("Error checking file existence:", error);
              document.getElementById("message").innerHTML = "Erreur.";
              return;
            });
        } else {
          document.getElementById("message").innerHTML =
            "Aucun document trouvé.";
          document.getElementById("removeButton").disabled = true;
        }
      });
    };

    $scope.cancel = function () {
      $uibModalInstance.dismiss("close");
    };
  }
);

webApp.directive("fileModel", [
  "$parse",
  function ($parse) {
    return {
      restrict: "A",
      link: function (scope, element, attrs) {
        const model = $parse(attrs.fileModel);
        const modelSetter = model.assign;

        element.bind("change", function () {
          scope.$apply(function () {
            modelSetter(scope, element[0].files[0]);
            document.getElementById("saveButton").disabled = false;
          });
        });
      },
    };
  },
]);

function uploadFile() {
  const scope = angular.element(document.getElementById("uploadForm")).scope();
  scope.uploadFile();
}
