try {
  function includeJs(jsFilePath) {
    $("body").append($("<script></script>").attr("src", jsFilePath));
  }

  includeJs("scripts/app.js");
  includeJs("scripts/controllers/activation.js");
  includeJs("scripts/controllers/administration/Details.js");
  includeJs("scripts/controllers/administration/List.js");
  includeJs("scripts/controllers/enseignement/calendarController.js");
  includeJs("scripts/controllers/enseignement/coursController.js");
  includeJs("scripts/controllers/enseignement/indisposController.js");
  includeJs("scripts/controllers/enseignement/periodeModal.js");
  includeJs("scripts/controllers/login.js");
  includeJs("scripts/controllers/mainController.js");
  includeJs("scripts/controllers/planification/calendar/annee.js");
  includeJs("scripts/controllers/planification/calendar/anneeDetails.js");
  includeJs("scripts/controllers/planification/calendar/calendar.js");
  includeJs("scripts/controllers/planification/calendar/calendarCours.js");
  includeJs("scripts/controllers/planification/classes/classes.js");
  includeJs("scripts/controllers/planification/classes/classesDetails.js");
  includeJs("scripts/controllers/planification/enseignants/enseignants.js");
  includeJs(
    "scripts/controllers/planification/enseignants/enseignantsMatieres.js"
  );

  includeJs("scripts/controllers/planification/matieres/matieres.js");
  includeJs("scripts/controllers/planification/matieres/matieresDetails.js");
  includeJs("scripts/controllers/planification/planification.js");
  includeJs("scripts/controllers/planification/salles/salles.js");
  includeJs("scripts/controllers/planification/salles/sallesDetails.js");
  includeJs("scripts/controllers/profil.js");
  includeJs("scripts/controllers/public/annee.js");
  includeJs("scripts/filters/unique.js");
  includeJs("scripts/fonctionGlobale.js");
  includeJs("scripts/routing.js");
  includeJs("scripts/services/activation.js");
  includeJs("scripts/services/administration/personnes.js");
  includeJs("scripts/services/enseignement/calendar.js");
  includeJs("scripts/services/enseignement/cours.js");
  includeJs("scripts/services/enseignement/indispos.js");
  includeJs("scripts/services/helper.js");
  includeJs("scripts/services/initializers.js");
  includeJs("scripts/services/login/authentification.js");
  includeJs("scripts/services/login/intercepteurHttp.js");
  includeJs("scripts/services/matieres.js");
  includeJs("scripts/services/notifications.js");
  includeJs("scripts/services/planification/calendar.js");
  includeJs("scripts/services/planification/classes.js");
  includeJs("scripts/services/planification/cours.js");
  includeJs("scripts/services/planification/enseignants.js");
  includeJs("scripts/services/planification/matieres.js");
  includeJs("scripts/services/planification/salles.js");
  includeJs("scripts/services/profil.js");
  includeJs("scripts/services/public/week.js");
  includeJs("scripts/services/roles.js");
  includeJs("scripts/services/session.js");
  includeJs("scripts/services/theme.js");
} catch (error) {
  alert("error");
  console.error(error);
  // expected output: ReferenceError: nonExistentFunction is not defined
  // Note - error messages will vary depending on browser
}
