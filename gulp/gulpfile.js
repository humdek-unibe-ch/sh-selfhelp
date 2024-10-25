var del = require('del');
var csso = require('gulp-csso');
var concat = require('gulp-concat');
var gulp = require('gulp');
var htmlmin = require('gulp-htmlmin');
var runSequence = require('run-sequence');
var terser = require('gulp-terser');
var babel = require('gulp-babel');
var replace = require('gulp-replace');
const { src, dest } = require('gulp');


// Gulp task to minify CSS files
gulp.task('styles', function () {
    return gulp.src(['../server/component/style/css/*.css',
        '../server/component/style/**/css/*.css'])
        // Minify the file
        .pipe(csso())
        // Concat
        .pipe(concat('styles.min.css'))
        // Output
        .pipe(gulp.dest('../css/ext'))
});

// Gulp task to minify JavaScript files
gulp.task('scripts', function () {
    return gulp.src(['../server/component/style/js/*.js',
        '../server/component/style/**/js/*.js'])
        .pipe(babel({
            presets: ['@babel/preset-env']
        }))
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
gulp.task('default', gulp.series('clean', 'styles', 'scripts', function (done) {
    done();
}));


/************ BOOTSTRAP MIGRATION from 4.6 to 5.3 */
/** Based on https://github.com/coliff/bootstrap-5-migrate-tool */

/**
 * Options that may be set via cli flags \
 * For example: \
 * `npx gulp migrate  --src "./src-dir" --overwrite --verbose` */
const DEFAULT_OPTIONS = {
    /** string that will be passed to the gulp {@link src} function */
    src: '../server/component',
    /** string that will be passed to the gulp {@link dest} function */
    dest: `./`,
    /** overwrite the existing files in place. **Cannot be used with --dest flag** */
    overwrite: true,
    /** print the path of each generated / modified file to the console */
    verbose: true,
    /** Default glob for files to search in. Default: Search all folder and files recursively */
    defaultFileGlob: '**/*.{html,js,php,sql,css}',
};

// Arrays for class name replacements and data attribute replacements
const classReplacements = [
    { old: 'badge-info', new: 'text-bg-info' },
    { old: 'badge-pill', new: 'rounded-pill' },
    { old: 'badge-primary', new: 'text-bg-primary' },
    { old: 'badge-secondary', new: 'text-bg-secondary' },
    { old: 'badge-success', new: 'text-bg-success' },
    { old: 'badge-warning', new: 'text-bg-warning' },
    { old: 'badge-danger', new: 'text-bg-danger' },
    { old: 'badge-dark', new: 'text-bg-dark' },
    { old: 'badge-light', new: 'text-bg-light' },
    { old: 'border-left', new: 'border-start' },
    { old: 'border-right', new: 'border-end' },
    { old: 'float-left', new: 'float-start' },
    { old: 'float-right', new: 'float-end' },
    { old: 'ml-', new: 'ms-' },
    { old: 'mr-', new: 'me-' },
    { old: 'pl-', new: 'ps-' },
    { old: 'pr-', new: 'pe-' },
    { old: 'custom-control-input', new: 'form-check-input' },
    { old: 'custom-control-label', new: 'form-check-label' },
    { old: 'custom-control custom-checkbox', new: 'form-check' },
    { old: 'custom-control custom-radio', new: 'form-check' },
    { old: 'custom-control custom-switch', new: 'form-check form-switch' },
    { old: 'custom-file-input', new: 'form-control' },
    { old: 'custom-file-label', new: 'form-label' },
    { old: 'custom-range', new: 'form-range' },
    { old: 'custom-select-sm', new: 'form-select-sm' },
    { old: 'custom-select-lg', new: 'form-select-lg' },
    { old: 'custom-select', new: 'form-select' },
    { old: 'font-weight-bold', new: 'fw-bold' },
    { old: 'font-weight-normal', new: 'fw-normal' },
    { old: 'font-weight-light', new: 'fw-light' },
    { old: 'font-weight-bolder', new: 'fw-bolder' },
    { old: 'font-weight-lighter', new: 'fw-lighter' },
    { old: 'font-italic', new: 'fst-italic' },
    { old: 'sr-only', new: 'visually-hidden' },
    { old: 'sr-only-focusable', new: 'visually-hidden-focusable' },
    { old: 'text-muted', new: 'text-body-secondary' },
    { old: 'text-left', new: 'text-start' },
    { old: 'text-right', new: 'text-end' },
    { old: 'text-sm-left', new: 'text-sm-start' },
    { old: 'text-sm-right', new: 'text-sm-end' },
    { old: 'text-md-left', new: 'text-md-start' },
    { old: 'text-md-right', new: 'text-md-end' },
    { old: 'text-lg-left', new: 'text-lg-start' },
    { old: 'text-lg-right', new: 'text-lg-end' },
    { old: 'text-xl-left', new: 'text-xl-start' },
    { old: 'text-xl-right', new: 'text-xl-end' },
    { old: 'text-monospace', new: 'font-monospace' },
    { old: 'no-gutters', new: 'g-0' },
    { old: 'pre-scrollable', new: 'overflow-y-scroll' },
    { old: 'embed-responsive-item', new: '' },
    { old: 'embed-responsive-16by9', new: 'ratio-16x9' },
    { old: 'embed-responsive-1by1', new: 'ratio-1x1' },
    { old: 'embed-responsive-21by9', new: 'ratio-21x9' },
    { old: 'embed-responsive-4by3', new: 'ratio-4x3' },
    { old: 'embed-responsive', new: 'ratio' },
    { old: 'rounded-left', new: 'rounded-start' },
    { old: 'rounded-right', new: 'rounded-end' },
    { old: 'rounded-sm', new: 'rounded-1' },
    { old: 'rounded-lg', new: 'rounded-3' },
    { old: 'close', new: 'btn-close' },
    { old: 'form-control-file', new: 'form-control' },
    { old: 'form-control-range', new: 'form-range' },
    { old: 'form-group', new: 'mb-3' },
    { old: 'form-inline', new: 'd-flex align-items-center' },
    { old: 'form-row', new: 'row' },
    { old: 'jumbotron-fluid', new: 'rounded-0 px-0' },
    { old: 'jumbotron', new: 'card card-header mb-4 rounded-2 py-5 px-3' },
    { old: 'media-body', new: 'flex-grow-1' },
    { old: 'media', new: 'd-flex' },
    { old: 'dropdown-menu-left', new: 'dropdown-menu-start' },
    { old: 'dropdown-menu-right', new: 'dropdown-menu-end' },
    { old: 'dropdown-menu-sm-left', new: 'dropdown-menu-sm-start' },
    { old: 'dropdown-menu-sm-right', new: 'dropdown-menu-sm-end' },
    { old: 'dropdown-menu-md-left', new: 'dropdown-menu-md-start' },
    { old: 'dropdown-menu-md-right', new: 'dropdown-menu-md-end' },
    { old: 'dropdown-menu-lg-left', new: 'dropdown-menu-lg-start' },
    { old: 'dropdown-menu-lg-right', new: 'dropdown-menu-lg-end' },
    { old: 'dropdown-menu-xl-left', new: 'dropdown-menu-xl-start' },
    { old: 'dropdown-menu-xl-right', new: 'dropdown-menu-xl-end' },
    { old: 'dropleft', new: 'dropstart' },
    { old: 'dropright', new: 'dropend' },
];


const dataAttributeReplacements = [
    'animation', 'autohide', 'backdrop', 'boundary', 'container', 'content',
    'custom-class', 'delay', 'dismiss', 'display', 'html', 'interval', 'keyboard',
    'method', 'offset', 'pause', 'placement', 'popper-config', 'reference', 'ride',
    'selector', 'slide', 'target', 'template', 'title', 'toggle', 'touch', 'trigger',
    'wrap'
];

const cssVarReplacements = [
    { old: '--success', new: '--bs-success' },
    { old: '--primary', new: '--bs-primary' },
    { old: '--secondary', new: '--bs-secondary' },
    { old: '--warning', new: '--bs-warning' },
    { old: '--danger', new: '--bs-danger' },
    { old: '--info', new: '--bs-info' },
];

const cdnLinks = [
    {
        regex: /<link href=["']https:\/\/cdnjs\.cloudflare\.com\/ajax\/libs\/bootstrap\/4\.\d+\.\d+\/dist\/css\/bootstrap(\.min)?\.css["'] rel=["']stylesheet["'] ?\/?>/g,
        replacement: '<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">'
    },
    {
        regex: /<script src=["']https:\/\/cdn\.cloudflare\.com\/ajax\/libs\/bootstrap\/4\.\d+\.\d+\/dist\/js\/bootstrap(\.min)?\.js["']>/g,
        replacement: '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js">'
    },
    // Add more CDN link replacements as needed
];

async function migrate(cb) {
    const options = parseArgs();

    let dataAttrChanged = 0;
    let CDNLinksChanged = 0;
    let cssClassChanged = 0;
    let cssPHPClassChanged = 0;

    // Create the source stream
    let stream = src([`${options.src}/${options.defaultFileGlob}`], { base: options.src });

    // Apply CDN link replacements
    cdnLinks.forEach(cdnLink => {
        stream = stream.pipe(
            replace(cdnLink.regex, () => {
                CDNLinksChanged++;
                return cdnLink.replacement;
            })
        );
    });

    // Apply class replacements
    classReplacements.forEach(({ old, new: newClass }) => {
        console.log(old, newClass);
        const regex = new RegExp(`(class\\s*=\\s*['"][^'"]*\\b${old}\\b[^'"]*['"])`, 'g');
        stream = stream.pipe(
            replace(regex, (match) => {
                cssClassChanged++;
                return match.replace(new RegExp(`\\b${old}\\b`, 'g'), newClass);
            })
        );
    });

    // Apply css replacements in php arrays
    classReplacements.forEach(({ old, new: newClass }) => {
        const regex = new RegExp(
            `("css"\\s*=>\\s*['"][^'"]*\\b${old}\\b[^'"]*['"]|'css'\\s*=>\\s*['"][^'"]*\\b${old}\\b[^'"]*['"])`, 'g');
        stream = stream.pipe(
            replace(regex, (match) => {
                cssPHPClassChanged++;
                return match.replace(new RegExp(`\\b${old}\\b`, 'g'), newClass);
            })
        );
    });

    // Apply data attribute replacements
    dataAttributeReplacements.forEach(attr => {
        const regex = new RegExp(`\\sdata-${attr}=`, 'g');
        stream = stream.pipe(
            replace(regex, () => {
                dataAttrChanged++;
                return ` data-bs-${attr}=`;
            })
        );
    });

    // Apply CSS variable replacements
    cssVarReplacements.forEach(({ old, new: newVar }) => {
        const regex = new RegExp(`var\\(${old}\\)`, 'g');
        stream = stream.pipe(
            replace(regex, match => {
                cssClassChanged++;
                return match.replace(old, newVar);
            })
        );
    });

    // Write changes to the same source directory
    stream
        .pipe(dest(options.src))
        .on('data', (data) => {
            if (options.verbose) {
                console.log(`Wrote file: ${data.path}`);
            }
        })
        .on('end', function () {
            console.log(`Completed! Changed ${cssClassChanged} CSS class names, Changed ${cssPHPClassChanged} CSS PHP array class names, ${dataAttrChanged} data-attributes, and ${CDNLinksChanged} CDN links.`);
            cb();
        });
}



/** parses cli args array and return an options object */
function parseArgs() {
    const options = Object.assign({}, DEFAULT_OPTIONS);

    const argv = process.argv;
    argv.forEach((flag, i) => {
        const value = argv[i + 1];
        switch (flag) {
            case '--src': {
                options.src = value;
                break;
            }
            case '--dest': {
                options.dest = value;
                break;
            }
            case '--glob': {
                options.defaultFileGlob = value;
                break;
            }
            case '--overwrite': {
                options.overwrite = true;
                options.dest = './';
                if (argv.includes('--dest')) {
                    throw new Error('Cannot use --overwrite and --dest options together.');
                }
                break;
            }
            case '--verbose': {
                options.verbose = true;
                break;
            }

            default:
                break;
        }
    });
    return options;
}

/**
 * Migrate Bootstrap 4.x to Bootstrap 5.x task
 */
gulp.task('migrate', function (cb) {
    return migrate(cb); // Uses the migrate function defined earlier
});

