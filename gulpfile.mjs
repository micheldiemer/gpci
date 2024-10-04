import gulp from "gulp";
import concat from "gulp-concat";
import { createRequire } from "node:module";
import { minify } from "terser";
const require = createRequire(import.meta.url);
// const uglify = require("gulp-uglify");
import clean from "gulp-clean";
import debug from "gulp-debug";
import { pipeline } from "stream/promises";
const parseArgs = require("minimist");
const exec = require("gulp-exec");
const gulpTerser = require("gulp-terser");
const replace = require("gulp-replace");
const rename = require("gulp-rename");
const terser = { minify };
const isElectron = () =>
  parseArgs(process.argv.slice(2), { boolean: ["electron"] }).electron;
const destF = () => (isElectron() ? "./preprod/electronApp" : "./preprod/gpci");
const DEV_URL_REGEX = /(webApp.constant.*BASE_URL.*)http.*(['"`])/;
const PROD_BACKEND_URL = "https://intranet.ifide.net/gpci/backend";

gulp.task("_backend_php", async function () {
  gulp
    .src([
      "./gpci/backend/**/*.php",
      "./gpci/backend/.htaccess",
      "!./gpci/backend/vendor",
      "!./gpci/backend/settings.*.php",
    ])
    .pipe(gulp.dest(destF() + "/backend"));
});

gulp.task("_css", async function () {
  gulp.src("./gpci/css/**/*.css").pipe(gulp.dest(destF() + "/css"));
});

gulp.task("__mk_uploads", async function () {
  gulp.src("gulpfile.mjs", { read: false }).pipe(
    exec(
      'mkdir -m 777 -pv "' + destF() + '/backend/uploads"',
      function (err, stdout, stderr) {
        console.log(stdout);
        console.log(stderr);
      }
    )
  );
});

gulp.task("__test", async function () {
  gulp.src(["./gpci/favicon*", "./gpci/favicon*/**"]).pipe(debug());
});

gulp.task("__copyfiles", async function () {
  gulp
    .src([
      "./gpci/backend/vendor/**/*",
      "./gpci/favicon*",
      "./gpci/favicon*/**",
      "./gpci/backend/img/**",
      "./gpci/img/**",
    ])
    .pipe(
      exec((file, d = file.isDirectory()) =>
        !d
          ? `echo install -Dv ${file.path} ` +
            file.path
              .replace("/gpci/", "/" + destF() + "/")
              .replace("/./", "/") +
            " >> liste.sh"
          : "exit"
      )
    )
    .pipe(
      exec((file, d = file.isDirectory()) =>
        !d
          ? `install -Dv ${file.path} ` +
            file.path
              .replace("/gpci/", "/" + destF() + "/")
              .replace("/./", "/") +
            " >> liste.txt"
          : "exit"
      )
    )
    // .pipe(
    //   exec(
    //     (file) =>
    //       `echo install -Dv ${file.path} ` +
    //       file.path.replace("/gpci/", "/" + destF() + "/").replace("/./", "/")
    //   )
    // )
    .pipe(exec.reporter());
});

gulp.task("_favicon", async function () {
  gulp.src("./gpci/favicon/**").pipe(gulp.dest(destF() + "/favicon"));
});

gulp.task("_backend_html", async function () {
  gulp.src(["./gpci/backend/**/*.html"]).pipe(gulp.dest(destF() + "/backend"));
});

gulp.task("_html", async function () {
  gulp
    .src(["./gpci/**/*.html", "./gpci/favicon.ico", "!./gpci/backend/**"])
    .pipe(gulp.dest(destF()));
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
    // gulp.dest(destF() + "/app.js")
    rename("app.js"),
    replace(DEV_URL_REGEX, `$1${PROD_BACKEND_URL}$2`),
    gulpTerser({ compress: false, mangle: false, ecma: 2015 }, terser.minify),
    // uglify(),
    gulp.dest(destF())
  );
});

gulp.task("clean", async function () {
  await pipeline(
    gulp.src([destF() + "/", "./liste.sh"], {
      read: false,
      allowEmpty: true,
    }),
    clean({ force: true })
  );
});

gulp.task(
  "webApp",
  gulp.series(
    "clean",
    gulp.parallel(
      "_backend_html",
      "_backend_php",
      "__copyfiles",
      "_css",
      "_html",
      "_js"
    ),
    "_tmp_cleanup"
  )
);

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
