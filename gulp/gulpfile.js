var del = require('del');
var csso = require('gulp-csso');
var concat = require('gulp-concat');
var gulp = require('gulp');
var htmlmin = require('gulp-htmlmin');
var runSequence = require('run-sequence');
var terser = require('gulp-terser');
var babel = require('gulp-babel');
var path = require('path');
var Transform = require('stream').Transform;

var STYLE_ROOT = path.resolve(__dirname, '../server/component/style');

// Sort the files before they are concatenated. gulp.src gives no order (it depends on the file system and the
// timing of the folder reads), so without this the bundles differ between Windows, Linux and Mac. Order: the
// shared folder (style/css or style/js) first, then the style folders; case-insensitive by path.
function sortFiles() {
    var files = [];
    var key = function (file) {
        var rel = path.relative(STYLE_ROOT, file.path).split(path.sep).join('/').toLowerCase();
        var shared = rel.split('/').length === 2; // js/x.js (shared) vs button/js/button.js (a style)
        return (shared ? '0' : '1') + rel;
    };
    return new Transform({
        objectMode: true,
        transform: function (file, enc, done) {
            files.push(file);
            done();
        },
        flush: function (done) {
            var self = this;
            files.sort(function (a, b) {
                var ka = key(a), kb = key(b);
                return ka < kb ? -1 : (ka > kb ? 1 : 0);
            }).forEach(function (file) {
                self.push(file);
            });
            done();
        }
    });
}

// Gulp task to minify CSS files
gulp.task('styles', function () {
    return gulp.src(['../server/component/style/css/*.css',
        '../server/component/style/**/css/*.css'])
    // Minify the file
    .pipe(csso())
    // A fixed order on every machine
    .pipe(sortFiles())
    // Concat
    .pipe(concat('styles.min.css'))
    // Output
    .pipe(gulp.dest('../css/ext'))
});

// Gulp task to minify JavaScript files
gulp.task('scripts', function() {
    return gulp.src(['../server/component/style/js/*.js',
        '../server/component/style/**/js/*.js'])
        .pipe(babel({
            presets: ['@babel/preset-env']
        }))
    // Minify the file
    .pipe(terser())
    // A fixed order on every machine
    .pipe(sortFiles())
    // Concat
    .pipe(concat('styles.min.js'))
    // Output
    .pipe(gulp.dest('../js/ext'))
});

// Clean output directory
gulp.task('clean', () => del(['dist']));

// Gulp task to minify all files
gulp.task('default', gulp.series('clean', 'styles', 'scripts', function (done) {
    done();
}));
