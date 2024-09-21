const gulp = require("gulp"),
  deletefile = require("gulp-delete-file"),
  runSequence = require("gulp4-run-sequence"),
  inject = require("gulp-inject"),
  concat = require("gulp-concat"),
  uglify = require("gulp-uglify-es").default,
  series = require("stream-series"),
  replace = require("gulp-replace"),
  parseArgs = require("minimist"),
  count = require("gulp-count");

// gulp.task("injectScripts", function () {
//   var base = gulp.src(["./gpci/scripts/*.js"], { read: false });
//   var services = gulp.src(["./gpci/scripts/services/**/*.js"], { read: false });
//   var directives = gulp.src(["./gpci/scripts/directives/*.js"], {
//     read: false,
//   });
//   var filters = gulp.src(["./gpci/scripts/filters/*.js"], { read: false });
//   var controllers = gulp.src(["./gpci/scripts/controllers/**/*.js"], {
//     read: false,
//   });

//   gulp
//     .src("./gpci/index.html")
//     .pipe(
//       inject(series(base, services, directives, filters, controllers), {
//         relative: true,
//       })
//     )
//     .pipe(gulp.dest("./gpci"));
// });

gulp.task("webApp", function () {
  //Partie webApp
  return Promise.resolve(
    runSequence(
      "clean",
      [
        "_backend_css",
        "_backend_htaccess",
        "_backend_html",
        "_backend_img",
        "_backend_php",
        "_css",
        "_html_modals",
        "_html_views",
        "_img",
        "_js",
      ],
      function () {
        console.log("done");
      }
    )
  );
});

gulp.task("_backend_php", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp
    .src(["./gpci/backend/**/*.php", "!settings.*.php"])
    .pipe(gulp.dest(dest + "/backend"));
});

gulp.task("_backend_htaccess", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp
    .src("./gpci/backend/.htaccess")
    .pipe(gulp.dest(dest + "backend/.htaccess"));
});

gulp.task("_css", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp.src("./gpci/css/**/*.css").pipe(gulp.dest(dest + "/css"));
});

gulp.task("_img", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp
    .src([
      "./gpci/img/**/*.png",
      "./gpci/img/**/*.jpg",
      "./gpci/img/**/*.jpeg",
      "./gpci/img/**/*.gif",
      "./gpci/img/**/*.webp",
    ])
    .pipe(gulp.dest(dest + "/img"));
});

gulp.task("_backend_img", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp
    .src([
      "./gpci/img/**/*.png",
      "./gpci/img/**/*.jpg",
      "./gpci/img/**/*.jpeg",
      "./gpci/img/**/*.gif",
      "./gpci/img/**/*.webp",
    ])
    .pipe(gulp.dest(dest + "/backend/img"));
});

gulp.task("_backend_css", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp.src(["./gpci/backend/**/*.css"]).pipe(gulp.dest(dest + "/backend/css"));
});

gulp.task("_backend_html", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp.src(["./gpci/backend/**/*.html"]).pipe(gulp.dest(dest + "/backend"));
});

gulp.task("_html_modals", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp.src("./gpci/modals/**/*.html").pipe(gulp.dest(dest + "/app/modals/"));
});

gulp.task("_html_views", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp.src("./gpci/views/**/*.html").pipe(gulp.dest(dest + "/app/views/"));
});

gulp.task("_js", : q
  async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  gulp
    .src(["./gpci/scripts/**/routing.js", ["!./gpci/app*.js"]])

    .pipe(concat("app.js").on("error", console.error))
    .pipe(gulp.dest(dest + "/xx"));

  // .pipe(uglify(concat("app.js").on("error", console.error)))
  // .pipe(gulp.dest(dest));
});

gulp.task("clean", async function () {
  const dest = parseArgs("electron")
    ? "./preprod/electronApp"
    : "./preprod/webApp";
  deletefile(dest);
});

// gulp.task("electronApp", function () {
//   //Partie electron
//   gulp
//     .src("./gpci/scripts/**/*.js")
//     .pipe(uglify(concat("app.js")))
//     .pipe(gulp.dest("./preprod/electronApp"));

//   gulp
//     .src("./preprod/electronApp/app.js")
//     .pipe(replace("./backend/", "https://intranet.ifide.net/gpci/backend/"))
//     .pipe(gulp.dest("./preprod/electronApp/app/"));

//   gulp
//     .src("./gpci/views/**/*.html")
//     .pipe(gulp.dest("./preprod/electronApp/app/views/"));

//   gulp
//     .src("./gpci/modals/**/*.html")
//     .pipe(gulp.dest("./preprod/electronApp/app/modals/"));

//   gulp.src("./gpci/css/**/*.css").pipe(gulp.dest("./preprod/electronApp/css"));
// });
