webApp.controller(
  "PlanCalendar",
  function ($scope, planCalendarService, BASE_URL) {
    $scope.config = planCalendarService.config;
    $scope.feed = planCalendarService.feed;
  }
);
