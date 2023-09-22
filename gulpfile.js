const gulp = require("gulp"),
    inject = require("gulp-inject"),
    concat = require("gulp-concat"),
    uglify = require("gulp-uglify"),
    series = require("stream-series"),
    replace = require("gulp-replace");

gulp.task("injectScripts", function() {
    var base = gulp.src(["./gpci/scripts/*.js"], {read: false});
    var services = gulp.src(["./gpci/scripts/services/**/*.js"], {read: false});
    var directives = gulp.src(['./gpci/scripts/directives/*.js'], { read: false });
    var filters = gulp.src(['./gpci/scripts/filters/*.js'], { read: false });
    var controllers = gulp.src(['./gpci/scripts/controllers/**/*.js'], { read: false });

    gulp
      .src('./gpci/index.html')
      .pipe(inject(series(base, services, directives, filters, controllers), { relative: true }))
      .pipe(gulp.dest('./gpci'));
});

gulp.task("webApp", function () {
  //Partie webApp
  gulp.src('./gpci/scripts/**/*.js')
    .pipe(uglify(concat('app.js')))
    .pipe(gulp.dest('./production/webApp'));
  gulp.src('./gpci/views/**/*.html')
    .pipe(gulp.dest('./production/webApp/app/views/'));
  gulp.src('./gpci/modals/**/*.html')
    .pipe(gulp.dest('./production/webApp/app/modals/'));
  gulp.src('./gpci/css/**/*.css')
    .pipe(gulp.dest('./production/css'));
  gulp.src(['./gpci/backend/**/*.php', '!settings.*.php'])
    .pipe(gulp.dest('./production/webApp/backend'));
  gulp.src('./gpci/backend/**/*.html')
    .pipe(gulp.dest('./production/webApp/backend'));
  gulp.src('./gpci/backend/.htaccess')
    .pipe(gulp.dest('./production/webApp/backend/.htaccess'));
});

gulp.task('electronApp', function () {
  //Partie electron
  gulp
    .src('./gpci/scripts/**/*.js')
    .pipe(uglify(concat('app.js')))
    .pipe(gulp.dest('./production/electronApp'));

  gulp.src('./production/electronApp/app.js')
    .pipe(replace('./backend/', 'https://intranet.ifide.net/gpci/backend/'))
    .pipe(gulp.dest('./production/electronApp/app/'));

  gulp.src('./gpci/views/**/*.html')
    .pipe(gulp.dest('./production/electronApp/app/views/'));

  gulp.src('./gpci/modals/**/*.html')
    .pipe(gulp.dest('./production/electronApp/app/modals/'));

  gulp.src('./gpci/css/**/*.css')
    .pipe(gulp.dest('./production/electronApp/css'));
});