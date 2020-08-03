const gulp = require("gulp");
const sass = require("gulp-sass");
const uglify = require('gulp-uglify');
const autoprefixer = require("gulp-autoprefixer");
const sourcemaps = require("gulp-sourcemaps");

function scss() {
  return gulp.src("sass/**/*.scss")
    .pipe(sourcemaps.init())
    .pipe(sass({outputStyle: "compressed"}).on("error", sass.logError))
    .pipe(autoprefixer("last 5 versions"))
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest("dist/css"));
}
exports.scss = scss;

function javascript() {
  return gulp.src(['scripts/*.js'])
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('dist/js'));
}
exports.javascript = javascript;

function watch (){
  gulp.watch("sass/**/*.scss", scss);
  gulp.watch("scripts/*.js", javascript);
}
exports.watch = watch;

exports.styles = gulp.series(scss, javascript);

exports.default = gulp.series(scss, javascript);;

