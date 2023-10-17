# Documentation GPCI

## Backend

### model

La table matiere doit exister dans la base de données: matieres(id,nom,code)

'''php
// fichier model.php
class Matieres extends Model {
    public $timestamps = false;

    public function user() {
        return $this->belongsToMany('Users', 'users_matieres', 'id_Matieres', 'id_Users');
    }
    public function cours() {
        return $this->hasMany('Cours', 'id_Matieres');
    }
}
'''

### route

'''php
// fichier planificateur.php
$app->get('/plan/matiere', $authenticateWithRole('planificateur'), function() use ($app) {
    $matiere = Matieres::with('user')->get();
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setBody($matiere->toJson());
});
'''

### test de la route

http://192.168.1.38/gpci/backend/plan/matiere 
Resulta:

'''json
[{"id":1,"nom":"Math","code":"001","user":[{"id":12,"login":"pdupont","password":"f102cae750dc2425cdca4d8c32b34f954e97b1ff","firstName":"pierre","lastName":"dupont","email":"pierred@example.org","enabled":"1","connected":"0","hash":"EDAGZRYZERDX","token":"4869617866511731574ad50.14172966","tokenCDate":null,"theme":"","pivot":{"id_Matieres":"1","id_Users":"12"}}]},{"id":2,"nom":"Fran\u00e7ais","code":"002","user":[]},{"id":3,"nom":"Physics","code":"003","user":[{"id":12,"login":"pdupont","password":"f102cae750dc2425cdca4d8c32b34f954e97b1ff","firstName":"pierre","lastName":"dupont","email":"pierred@example.org","enabled":"1","connected":"0","hash":"EDAGZRYZERDX","token":"4869617866511731574ad50.14172966","tokenCDate":null,"theme":"","pivot":{"id_Matieres":"3","id_Users":"12"}}]}]
'''

## Front-end

### La vue

'''html 
<!-- fichier views/planification/matieres.html-->
        <tbody>
        <tr ng-repeat="matiere in matieresView">
            <td>{{ matiere.code }}</td>
            <td>{{ matiere.nom }}</td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-default" ng-click="open(matiere)">Editer</button>
                    <button type="button" class="btn btn-default" ng-click="remove(matiere)">
                        <i class="fa fa-remove"></i>
                    </button>
                </div>
            </td>
        </tr>
        </tbody>
'''

### Le service

'''javascript
// fichier service/planification/matieres.js
webApp.factory("matieresService",
    function($q, notifService, Restangular) {

        var list = [];

        function updateList() {
            return $q(function(resolve, reject) {
                //TO DO Lancer toastr chargement
                Restangular.all("plan/matiere").getList().then(function(data) {
                    list = [].concat(data);
                    //TO DO SUCCESS TOASTR
                    resolve();
                }, function() {
                    //TO DO ERROR TOASTR
                    reject();
                });
            });
        };
'''

### La route 

'''javascript
// fihcier routing.js
$stateProvider
 .state("planification.matieres", {
            url: "/matieres",
            templateUrl: "views/planification/matieres.html",
            controller: "PlanMatieresController",
            data: {
                authorizedRoles: [USERS_ROLES.planificateur]
            }
        })
'''

### Le controller 

'''javascript 
//fihcier scripts/controllers/planification/matieres/matieres.js
webApp.controller("PlanMatieresController",
	function($scope, $uibModal, matieresService){
        
		$scope.matieres = [];

	    matieresService.getList().then(function(data) {
	        $scope.matieres = data;
	        $scope.matieresView = [].concat($scope.matieres);
	    });
'''

### Initialization du service

'''javascript
//fihcier scripts/service/initializers.js
webApp.factory("initializers", function(matieresService, classesService, enseignantsService) {

    function planification() {
        matieresService.updateList();
        classesService.updateList();
        enseignantsService.updateList();
    }

    return {
        planification: planification
    }
});

'''

