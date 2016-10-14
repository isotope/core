'use strict';

const gulp = require('gulp');
const rename = require('gulp-rename');
const gutil = require('gulp-util');
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');

const production = !!gutil.env.prod;

// Configuration
const scripts = [
    'system/modules/isotope/assets/js/*.js',
    '!system/modules/isotope/assets/js/*.min.js'
];
const styles = [
    'system/modules/isotope/assets/css/*.css',
    '!system/modules/isotope/assets/css/*.min.css',
    'system/modules/isotope_reports/assets/*.css',
    '!system/modules/isotope_reports/assets/*.min.css'
];

// Build scripts
gulp.task('scripts', function () {
    return gulp.src(scripts, {base: './'})
        .pipe(production ? uglify() : gutil.noop())
        .pipe(rename(function (path) {
            path.extname = '.min' + path.extname;
        }))
        .pipe(gulp.dest('./'));
});

// Build styles
gulp.task('styles', function () {
    return gulp.src(styles, {base: './'})
        .pipe(production ? cleanCSS({'restructuring': false, 'processImport': false}) : gutil.noop())
        .pipe(rename(function (path) {
            path.extname = '.min' + path.extname;
        }))
        .pipe(gulp.dest('./'));
});

// Build by default
gulp.task('default', ['styles', 'scripts']);