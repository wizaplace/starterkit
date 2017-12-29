'use strict';

// dependencies
const gulp = require('gulp');
const less = require('gulp-less');
const postcss = require('gulp-postcss');
const clean = require('gulp-clean');
const concat = require('gulp-concat');
const autoprefixer = require('autoprefixer');
const cleanCSS = require('gulp-clean-css');
const sourcemaps = require('gulp-sourcemaps');
const imagemin = require('gulp-imagemin');
const browserSync = require('browser-sync').create();
const gulpStylelint = require('gulp-stylelint');
const babelify = require('babelify');
const browserify = require('browserify');
const buffer = require('vinyl-buffer');
const source = require('vinyl-source-stream');

// helpers
const nodeModulePath = "./node_modules";

// clean generated assets folders
gulp.task('clean', function() {
    return gulp.src([
            './web/fonts',
            './web/images',
            './web/scripts',
            './web/style',
        ],
        { read: false })
        .pipe(clean());
});

//ES6+ (prod and dev)
gulp.task('babelify', function () {
    let bundler = browserify('./src/AppBundle/Resources/public/scripts/scoped/header.js', {debug: true}).transform(babelify);

    bundler.bundle()
        .on('error', function (err) {
            console.error(err);
            this.emit('end');
        })
        .pipe(source('header.js')) // destination filename
        .pipe(buffer())
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./web/scripts')); // destination folder
});

// scripts (prod)
gulp.task('scripts_prod', ['babelify'], function() {
    return gulp.src([
        `${nodeModulePath}/bootstrap/dist/js/bootstrap.min.js`,
        `${nodeModulePath}/vue/dist/vue.min.js`,
        `${nodeModulePath}/moment/min/moment.min.js`,
        `${nodeModulePath}/lodash/lodash.min.js`,
        `${nodeModulePath}/cookieconsent/build/cookieconsent.min.js`,
        `${nodeModulePath}/slick-carousel/slick/slick.min.js`,
        './src/AppBundle/Resources/public/scripts/**/*.*',
        '!./src/AppBundle/Resources/public/scripts/scoped/**/*.*',
    ])
        .pipe(concat('app.js'))
        .pipe(gulp.dest('./web/scripts'));
});

// scripts (dev)
gulp.task('scripts_dev', ['babelify'], function() {
    return gulp.src([
        `${nodeModulePath}/bootstrap/dist/js/bootstrap.min.js`,
        `${nodeModulePath}/vue/dist/vue.js`, // not minified to be used with chrome plugin (vuejs-devtools)
        `${nodeModulePath}/moment/min/moment.min.js`,
        `${nodeModulePath}/lodash/lodash.min.js`,
        `${nodeModulePath}/cookieconsent/build/cookieconsent.min.js`,
        `${nodeModulePath}/slick-carousel/slick/slick.min.js`,
        './src/AppBundle/Resources/public/scripts/**/*.*',
        '!./src/AppBundle/Resources/public/scripts/scoped/**/*.*',
    ])
        .pipe(sourcemaps.init())
        .pipe(concat('app.js'))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./web/scripts'));
});

// style (less)
gulp.task('style', function () {
    return gulp.src('./src/AppBundle/Resources/public/style/main.less')
        .pipe(sourcemaps.init())
        .pipe(less())
        .pipe(concat('app.css'))
        .pipe(postcss([ autoprefixer() ]))
        .pipe(cleanCSS())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./web/style'));
});

// JQuery (move)
gulp.task('jquery', function() {
    return gulp.src(`${nodeModulePath}/jquery/dist/jquery.min.js`)
        .pipe(gulp.dest('./web/scripts'));
});

// images (move)
gulp.task('images', function () {
    return gulp.src([
        './src/AppBundle/Resources/public/images/**/*.*',
        `${nodeModulePath}/slick-carousel/slick/ajax-loader.gif`,
    ])
        .pipe(imagemin())
        .pipe(gulp.dest('./web/images'));
});

// fonts (move)
gulp.task('fonts', function () {
    return gulp.src([
        './src/AppBundle/Resources/public/fonts/**/*.*',
        `${nodeModulePath}/font-awesome/fonts/**/*`,
        `${nodeModulePath}/bootstrap/fonts/**/*`,
        `${nodeModulePath}/slick-carousel/slick/fonts/**/*`,
    ]).pipe(gulp.dest('./web/fonts'));
});

// serve (for live reload) and watch assets changes
gulp.task('server', function() {
    browserSync.init({
        proxy: "demo.loc",
        startPath: "/app_dev.php",
        notify: false
    });

    gulp.watch('./src/AppBundle/Resources/public/scripts/**/*.*', ['scripts_dev', 'browser-reload']);
    gulp.watch('./src/AppBundle/Resources/public/style/**/*.*', ['lint-css', 'style', 'browser-reload']);
    gulp.watch('./src/AppBundle/Resources/public/fonts/**/*.*', ['fonts', 'browser-reload']);
    gulp.watch('./src/AppBundle/Resources/public/images/**/*.*', ['images', 'browser-reload']);
    gulp.watch('./src/AppBundle/Resources/views/**/*.*', ['browser-reload']);
});

gulp.task('browser-reload', function() {
    browserSync.reload();
});

gulp.task('lint-css', function lintCssTask() {
    return gulp
        .src('src/AppBundle/Resources/public/style/**/*.less')
        .pipe(gulpStylelint({
            reporters: [
                {formatter: 'string', console: true}
            ]
        }))
    ;
});

// tasks
// =====

// default task (with watcher)
gulp.task('default', ['dev']);

// common tasks, run both by dev and prod tasks
gulp.task('common', ['style', 'jquery', 'images', 'fonts']);

// dev tasks (with watcher, css linter and Vue.js dev version)
gulp.task('dev', ['scripts_dev', 'common', 'lint-css', 'server']);

// prod tasks (without watch task)
gulp.task('deploy', ['scripts_prod', 'common']);
