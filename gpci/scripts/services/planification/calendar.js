webApp.factory(
  "planCalendarService",
  function (uiCalendarConfig, $uibModal, coursService, enseignantsService) {
    const eventRender = function (event, element, view) {
      if (view.type === "agendaWeek") {
        if (
          element.hasClass("coursContainer") &&
          moment(event.start).isAfter(moment().startOf("day"))
        ) {
          element.find(".fc-bg").append("<div>" + event.description + "</div>");
          element.addClass("clickable");
        }
        if (element.hasClass("coursEvent")) {
          element.find(".fc-content").append("<p>" + event.enseignant + "</p>");
          element.find(".fc-content").append("<p>" + event.classes + "</p>");
          element.find(".fc-content").append("<p>" + event.salle + "</p>");
        }
      } else {
        if (element.hasClass("coursContainer")) {
          element.css("display", "none");
        }
      }
      if (event.assignationSent == 0) {
        element.css("backgroundColor", "#FF851B");
      }
    };

    const eventClick = function (event, jsEvent, view) {
      if (
        view.type === "agendaWeek" &&
        moment(event.start).isAfter(moment().startOf("day"))
      ) {
        //openEventDetails(event);
        const modalDetails = $uibModal.open({
          animation: true,
          templateUrl: "modals/coursDetails.html",
          controller: "CoursDetails",
          size: "md",
          resolve: {
            event: function () {
              return event;
            },
          },
        });

        modalDetails.result.then(function (coursP) {
          if (coursP.toDelete) {
            coursService.remove(coursP).then(function () {
              enseignantsService.updateList();
              uiCalendarConfig.calendars.planCalendar.fullCalendar(
                "removeEventSource",
                events
              );
              uiCalendarConfig.calendars.planCalendar.fullCalendar(
                "addEventSource",
                events
              );
            });
          } else {
            coursService.save(coursP).then(function () {
              enseignantsService.updateList();
              uiCalendarConfig.calendars.planCalendar.fullCalendar(
                "removeEventSource",
                events
              );
              uiCalendarConfig.calendars.planCalendar.fullCalendar(
                "addEventSource",
                events
              );
            });
          }
        });
      }
    };

    const sendAssignations = function () {
      let view =
        uiCalendarConfig.calendars.planCalendar.fullCalendar("getView");
      coursService
        .sendAssignations(
          view.intervalStart.format(),
          view.intervalEnd.format()
        )
        .then(function () {
          uiCalendarConfig.calendars.planCalendar.fullCalendar(
            "removeEventSource",
            events
          );
          uiCalendarConfig.calendars.planCalendar.fullCalendar(
            "addEventSource",
            events
          );
        });
    };

    /* Configuration du calendrier */
    const config = {
      calendar: {
        height: 540,
        editable: false,
        customButtons: {
          sendMail: {
            text: "Envoyer mails d'assignations",
            click: sendAssignations,
          },
        },
        header: {
          left: "title, sendMail",
          center: "month,agendaWeek",
          right: "today prev,next",
        },
        weekends: false,
        weekNumbers: true,
        eventRender: eventRender,
        eventClick: eventClick,
        minTime: "08:00:00",
        maxTime: "17:00:00",
        defaultView: "agendaWeek",
        dayNames: [
          "Dimanche",
          "Lundi",
          "Mardi",
          "Mercredi",
          "Jeudi",
          "Vendredi",
          "Samedi",
        ],
        dayNamesShort: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
        monthNames: [
          "Janvier",
          "Février",
          "Mars",
          "Avril",
          "Mai",
          "Juin",
          "Juillet",
          "Août",
          "Septembre",
          "Octobre",
          "Novembre",
          "Décembre",
        ],
      },
    };

    //Données du calendrier

    const events = {
      url: BASE_URL + "/plan/cours",
      color: "green",
      className: "coursEvent",
      eventDataTransform: function (rawEventData) {
        return {
          id: rawEventData.id,
          title: rawEventData.matiere.nom,
          enseignant:
            rawEventData.user != undefined
              ? rawEventData.user.firstName + " " + rawEventData.user.lastName
              : "",
          classes: printClasses(rawEventData.classes),
          start: rawEventData.start,
          end: rawEventData.end,
          assignationSent: rawEventData.assignationSent,
          salle: rawEventData.salle.nom,
        };
      },
    };

    function printClasses(classes) {
      let result = "";
      classes.forEach(function (classe) {
        result += classe.nom + " ";
      });
      return result;
    }
    //Fond clickable pour ajouter les cours
    const backgroundEvent = [
      {
        start: "8:00",
        end: "12:00",
        dow: [1, 2, 3, 4, 5],
        className: "coursContainer",
        description: "Ajouter un cours",
      },
      {
        start: "13:00",
        end: "17:00",
        dow: [1, 2, 3, 4, 5],
        className: "coursContainer",
        description: "Ajouter un cours",
      },
    ];

    const eventsGoogle = {
      googleCalendarId: "fr.french#holiday@group.v.calendar.google.com",
      googleCalendarApiKey: "AIzaSyAbOYkIfOWcqCnHEs_Mlf0JuT0HJ8TVq1M",
      className: "gcal-event",
      currentTimezone: "Europe/Paris",
    };

    /* Arrays de avec données de base du calendrier (au chargement de la page) */
    const feed = [events, eventsGoogle, backgroundEvent];

    return {
      config: config,
      feed: feed,
    };
  }
);
