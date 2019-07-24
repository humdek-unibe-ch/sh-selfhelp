var del = require('del');
var csso = require('gulp-csso');
var concat = require('gulp-concat');
var gulp = require('gulp');
var htmlmin = require('gulp-htmlmin');
var runSequence = require('run-sequence');
var terser = require('gulp-terser');

// Gulp task to minify CSS files
gulp.task('styles', function () {
  return gulp.src(['../server/component/style/style.css',
        '../server/component/style/**/css/*.css'])
    // Minify the file
    .pipe(csso())
    // Concat
    .pipe(concat('styles.min.css'))
    // Output
    .pipe(gulp.dest('../css/ext'))
});

// Gulp task to minify JavaScript files
gulp.task('scripts', function() {
  return gulp.src('../server/component/style/**/js/*.js')
    // Minify the file
    .pipe(terser())
    // Concat
    .pipe(concat('styles.min.js'))
    // Output
    .pipe(gulp.dest('../js/ext'))
});

// Clean output directory
gulp.task('clean', () => del(['dist']));

// Gulp task to minify all files
gulp.task('default', ['clean'], function () {
  runSequence(
    'styles',
    'scripts'
  );
});
