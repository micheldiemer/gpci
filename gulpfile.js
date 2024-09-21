/* eslint-disable no-octal */
const gulp = require("gulp");
const runSequence = require("gulp4-run-sequence");
const inject = require("gulp-inject");
const concat = require("gulp-concat");
const terser = require("terser");
const gulpTerser = require("gulp-terser");
const series = require("stream-series");
const replace = require("gulp-replace");
const parseArgs = require("minimist");
const count = require("gulp-count");
const pipeline = require("stream/promises").pipeline;
const rename = require("gulp-rename");
const clean = require("gulp-clean");

const isElectron = () =>
  parseArgs(process.argv.slice(2), { boolean: ["electron"] }).electron;
const destF = () =>
  isElectron() ? "./preprod/electronApp" : "./preprod/webApp";

gulp.task("webApp", function () {
  //Partie webApp
  return Promise.resolve(
    runSequence(
      "clean",
      [
        "_backend_html",
        "_backend_img",
        "_backend_php",
        "_css",
        "_html",
        "_js",
      ],
      "_tmp_cleanup",
      function () {
        console.log("task webApp done");
      }
    )
  );
});

gulp.task("_backend_php", async function () {
  gulp
    .src([
      "./gpci/backend/**/*.php",
      "./gpci/backend/.htaccess",
      "!./gpci/backend/settings.*.php",
    ])
    .pipe(gulp.dest(destF() + "/backend", { mode: 0604 }));
});

gulp.task("_css", async function () {
  gulp
    .src("./gpci/css/**/*.css")
    .pipe(gulp.dest(destF() + "/css", { mode: 0604 }));
});


gulp.task("_backend_img", async function () {
  gulp
    .src([
      "./gpci/backend/img/*.png",
      "./gpci/backend/img/*.jpg",
      "./gpci/backend/img/*.jpeg",
      "./gpci/backend/img/*.gif",
      "./gpci/backend/img/*.webp",
    ])
    .pipe(gulp.dest(destF() + "/backend/img", { mode: 0604 }));
});

gulp.task("_backend_html", async function () {
  gulp
    .src(["./gpci/backend/**/*.html"])
    .pipe(gulp.dest(destF() + "/backend", { mode: 0604 }));
});

gulp.task("_html", async function () {
  gulp
    .src(["./gpci/**/*.html", "!./gpci/backend/**"])
    .pipe(gulp.dest(destF(), { mode: 0604 }));
});

gulp.task("_tmp_cleanup", async function () {
  await pipeline(
    gulp.src([destF() + "/tmp"], { read: false, allowEmpty: true }),
    clean({ force: true })
  );
});

gulp.task("_js", async function () {
  await pipeline(
    gulp.src(["./gpci/scripts/**/*.js", "!./gpci/app*.js"]),
    concat("concat.js"),
    gulp.dest(destF() + "/tmp"),
    rename("app.js"),
    gulpTerser({ compress: true, mangle: true, ecma: 2015 }, terser.minify),
    gulp.dest(destF(), { mode: 0604 })
  );
});

gulp.task("clean", async function () {
  await pipeline(
    gulp.src([destF() + "/*", destF() + "/.*"], {
      read: false,
      allowEmpty: true,
    }),
    clean({ force: true })
  );
});

gulp.task("default", gulp.series("webApp"));

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
