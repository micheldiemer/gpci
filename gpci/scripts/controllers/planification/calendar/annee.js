webApp.controller("PlanAnnee", function ($scope, $uibModal, $http, classesService, weekService) {
    $scope.current_classes = [];
    $scope.next_classes = [];
    
    classesService.getCurrentNextList('current').then(function (classes) {
        $scope.current_classes = classes;
        $scope.classesView = [].concat($scope.current_classes);
    });

    classesService.getCurrentNextList('next').then(function (classes) {
        $scope.next_classes = classes;
        $scope.next_classesView = [].concat($scope.next_classes);
    });

    $scope.year = "";
    $scope.nextyear = "";
    $scope.week = [];
    
    weekService.getList('current').then(function (week) {
        $scope.week = week;
        $scope.weekView = [].concat($scope.week);
    });

    $scope.nextweek = [];
    weekService.getList('next').then(function(week) {
        $scope.nextweek = week;
        $scope.nextweekView = [].concat($scope.nextweek);
    });

    $http({
        method: 'GET',
        url: 'backend/plan/years/current'
    }).then(function successCallback(response) {
        $scope.year = response.data.year;
    }, function errorCallback(response) {});

    $http({
        method: 'GET',
        url: 'backend/plan/years/next'
    }).then(function successCallback(response) {
        $scope.nextyear = response.data.year;
    }, function errorCallback(response) {});

    $scope.onSave = function (year, weekNumber, classeId) {
        var modalInstance = $uibModal.open({
            templateUrl: 'modals/anneeDetails.html',
            controller: 'ModalInstanceCtrl',
            resolve: {
                year: function () {
                    return year;
                },
                weekNumber: function () {
                    return weekNumber;
                },
                classeId: function () {
                    return classeId;
                }
            }
        });
    };
    
});

webApp.controller('ModalInstanceCtrl', function ($scope, $uibModalInstance, Restangular, year, weekNumber, classeId) {

    $scope.fileExistsMessage = ''; // Message to display if file exists

    // Function to check if file exists
    function checkFileExists() {
        return Restangular.one('upload/' + year + '/' + weekNumber + '/' + classeId)
            .get()
            .then(function(response) {
                return {
                    exists: response.exists,
                    filename: response.fileDirectory // Assuming your response has 'filename'
                };
            })
            .catch(function(error) {
                console.error('Error checking file existence:', error);
                return {
                    exists: false,
                    filename: ''
                };
            });
    }

    // Execute checkFileExists when modal opens
    $uibModalInstance.opened.then(function() {
        checkFileExists().then(function(result) {
            console.log(result);
            $scope.exists = result.exists
            if (result.exists) {
                document.getElementById('message').innerHTML = 'Un document existe déjà: ' + result.filename;;
                document.getElementById('saveButton').disabled = true;
                document.getElementsByClassName('selectFile')[0].disabled = true;
                document.getElementById("removeButton").disabled = false;
            } else {
                document.getElementById('message').innerHTML = '';
                document.getElementById('saveButton').disabled = false;
                document.getElementsByClassName('selectFile')[0].disabled = false;
                document.getElementById("removeButton").disabled = true;
            }
        });
    });

    $scope.save = function () {
        if (document.getElementsByClassName('selectFile')[0].files.length == 0) {
            $uibModalInstance.dismiss('save');
            return;
        }
        var File = document.getElementsByClassName('selectFile')[0].files[0];
        var formData = new FormData();
        formData.append('file', File);
        console.log(File);

        Restangular.one('upload/' + year + '/' + weekNumber + '/' + classeId)
                    .withHttpConfig({ transformRequest: angular.identity })
                    .customPOST(formData, '', undefined, { 'Content-Type': undefined })
                    .then(function (response) {
                        if (response.error) {
                            document.getElementById('message').innerHTML = response.msg;
                        } else {
                            document.getElementById('message').innerHTML = "Document enregistré.";
                            $uibModalInstance.close($scope.uploadFile);
                        }
                        
                    })
                    .catch(function (error) {
                        console.error('Error uploading file', error);
                        document.getElementById('message').innerHTML = "Erreur.";
                    });
    };

    $scope.remove = function () {
        checkFileExists().then(function(result) {
            console.log(result);
            $scope.exists = result.exists
            if (result.exists) {
                Restangular.one('upload/' + year + '/' + weekNumber + '/' + classeId)
                .remove()
                .then(function(response) {
                    if (response.deleted) {
                        document.getElementById('message').innerHTML = 'Document supprimée.';
                        document.getElementById('saveButton').disabled = false;
                        document.getElementsByClassName('selectFile')[0].disabled = false;
                        document.getElementById("removeButton").disabled = true;
                    } else {
                        document.getElementById('message').innerHTML = 'Erreur.';
                    }
                    return;
                })
                .catch(function(error) {
                    console.error('Error checking file existence:', error);
                    document.getElementById('message').innerHTML = 'Erreur.';
                    return;
                });
            } else {
                document.getElementById('message').innerHTML = 'Aucun document trouvé.';
                document.getElementById("removeButton").disabled = true;
            }
        });
    };

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
});



webApp.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;

            element.bind('change', function () {
                scope.$apply(function () {
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);

function uploadFile() {
    var scope = angular.element(document.getElementById('uploadForm')).scope();
    scope.uploadFile();
}
