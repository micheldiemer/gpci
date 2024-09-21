webApp.factory(
  "initializers",
  function (
    matieresService,
    classesService,
    enseignantsService,
    sallesService
  ) {
    function planification() {
      matieresService.updateList();
      classesService.updateList();
      enseignantsService.updateList();
      sallesService.updateList();
    }

    return {
      planification: planification,
    };
  }
);
