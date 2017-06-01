'use strict';

// dependencies
const gulp = require('gulp');
const less = require('gulp-less');
const postcss = require('gulp-postcss');
const concat = require('gulp-concat');
const autoprefixer = require('autoprefixer');
const cleanCSS = require('gulp-clean-css');
const babelify = require('babelify');
const browserify = require('browserify');
const buffer = require('vinyl-buffer');
const source = require('vinyl-source-stream');
const sourcemaps = require('gulp-sourcemaps');

// legacy scripts TODO: remove when not needed anymore
gulp.task('legacy', function() {
    return gulp.src('./app/Resources/public/scripts/legacy/**/*.*')
        .pipe(sourcemaps.init())
        .pipe(concat('legacy.js'))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./web/scripts/legacy'));
});

// helpers
const nodeModulePath = "./node_modules";
const resourcesPath = "./app/Resources/public";
const javascriptLibsPath = `${resourcesPath}/scripts/libs`;

// Scripts (ES6)
gulp.task('babelify', function () {

    // transpile es6 files
    let bundler = browserify('./app/Resources/public/scripts/main.js', {debug: true}).transform(babelify);

    bundler.bundle()
        .on('error', function (err) {
            console.error(err);
            this.emit('end');
        })
        .pipe(source('app.js')) //fichier de destination
        .pipe(buffer())
        .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./web/scripts'));
});

// Scripts (prod)
gulp.task('scripts_prod', ['babelify'], function() {
    // concat custom code with libs
    return gulp.src([
        `${nodeModulePath}/bootstrap/dist/js/bootstrap.min.js`,
        `${javascriptLibsPath}/jquery-ui-slider.min.js`,
    ])
        .pipe(concat('libraries.js'))
        .pipe(gulp.dest('./web/scripts'));
});

// Scripts (dev)
gulp.task('scripts_dev', ['babelify'], function() {
    // concat custom code with libs
    return gulp.src([
        `${nodeModulePath}/bootstrap/dist/js/bootstrap.js`,
        `${javascriptLibsPath}/jquery-ui-slider.js`,
    ])
        .pipe(sourcemaps.init())
        .pipe(concat('libraries.js'))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./web/scripts'));
});

// Style (less)
gulp.task('style', function () {
    return gulp.src('./app/Resources/public/style/main.less')
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

// Favicon (move)
gulp.task('favicon', function () {
    return gulp.src('./app/Resources/public/images/favicon.ico')
        .pipe(gulp.dest('./web'));
});

// Images (move)
gulp.task('images', function () {
    return gulp.src('./app/Resources/public/images/**/*.*')
        .pipe(gulp.dest('./web/images'));
});

// Fonts (move)
gulp.task('fonts', function () {
    return gulp.src([
        './app/Resources/public/fonts/**/*.*',
        `${nodeModulePath}/font-awesome/fonts/**/*`,
        `${nodeModulePath}/bootstrap/fonts/**/*`,
    ]).pipe(gulp.dest('./web/fonts'));
});

// Watchers
gulp.task('watch', function () {
    gulp.watch('./app/Resources/public/scripts/**/*.*', ['scripts_dev']);
    gulp.watch('./app/Resources/public/style/**/*.*', ['style']);
    gulp.watch('./app/Resources/public/fonts/**/*.*', ['fonts']);
    gulp.watch('./app/Resources/public/images/**/*.*', ['images']);
});

// default task (with watcher)
gulp.task('default', ['dev', 'watch']);

// common tasks, run both by dev and prod tasks
gulp.task('common', ['style', 'jquery', 'images', 'fonts', 'legacy']);

// dev tasks (with watcher and Vue.js dev version)
gulp.task('dev', ['scripts_dev', 'common']);

// prod tasks (without watch task)
gulp.task('deploy', ['scripts_prod', 'common']);
