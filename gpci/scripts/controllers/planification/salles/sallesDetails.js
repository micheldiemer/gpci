webApp.controller("SalleDetails",
	function($scope, $timeout, $uibModalInstance,  sallesService, salle){
		
	    $scope.salle = {};
	    $scope.creation = salle ? false : true;
        
        if(!$scope.creation){
            sallesService.getOne(salle.id).then(function (data) {
                $scope.salle = data;
            });
        }
        else {
            $scope.salle = sallesService.getNew();
        }
        
		$scope.save = function () {
		    $scope.salle.toDelete = false;
            $uibModalInstance.close($scope.salle);
		};
        
        $scope.remove = function () {
            $scope.salle.toDelete = true;
            $uibModalInstance.close($scope.salle);
		};
        
		$scope.cancel = function () {
			$uibModalInstance.dismiss("Annuler");
		};        
	});