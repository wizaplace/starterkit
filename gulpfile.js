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

// scripts (prod)
gulp.task('scripts_prod', function() {
    return gulp.src([
        `${nodeModulePath}/bootstrap/dist/js/bootstrap.min.js`,
        `${nodeModulePath}/vue/dist/vue.min.js`,
        `${nodeModulePath}/moment/min/moment.min.js`,
        `${nodeModulePath}/lodash/lodash.min.js`,
        `${nodeModulePath}/coockieconsent/build/cookieconsent.min.js`,
        './app/Resources/public/scripts/**/*.*',
    ])
        .pipe(concat('app.js'))
        .pipe(gulp.dest('./web/scripts'));
});

// scripts (dev)
gulp.task('scripts_dev', function() {
    return gulp.src([
        `${nodeModulePath}/bootstrap/dist/js/bootstrap.min.js`,
        `${nodeModulePath}/vue/dist/vue.js`,
        `${nodeModulePath}/moment/min/moment.min.js`,
        `${nodeModulePath}/lodash/lodash.min.js`,
        `${nodeModulePath}/cookieconsent/build/cookieconsent.min.js`,
        './app/Resources/public/scripts/**/*.*',
    ])
        .pipe(sourcemaps.init())
        .pipe(concat('app.js'))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./web/scripts'));
});

// style (less)
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

// images (move)
gulp.task('images', function () {
    return gulp.src('./app/Resources/public/images/**/*.*')
        .pipe(imagemin())
        .pipe(gulp.dest('./web/images'));
});

// fonts (move)
gulp.task('fonts', function () {
    return gulp.src([
        './app/Resources/public/fonts/**/*.*',
        `${nodeModulePath}/font-awesome/fonts/**/*`,
        `${nodeModulePath}/bootstrap/fonts/**/*`,
    ]).pipe(gulp.dest('./web/fonts'));
});

// watchers
gulp.task('watch', function () {
    gulp.watch('./app/Resources/public/scripts/**/*.*', ['scripts_dev']);
    gulp.watch('./app/Resources/public/style/**/*.*', ['style']);
    gulp.watch('./app/Resources/public/fonts/**/*.*', ['fonts']);
    gulp.watch('./app/Resources/public/images/**/*.*', ['images']);
});

// default task (with watcher)
gulp.task('default', ['dev', 'watch']);

// common tasks, run both by dev and prod tasks
gulp.task('common', ['style', 'jquery', 'images', 'fonts']);

// dev tasks (with watcher and Vue.js dev version)
gulp.task('dev', ['scripts_dev', 'common']);

// prod tasks (without watch task)
gulp.task('deploy', ['scripts_prod', 'common']);
