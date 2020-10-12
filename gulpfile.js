'use strict';

var gulp = require('gulp'),
    csso = require('gulp-csso'),
    ignore = require('gulp-ignore'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify'),
    pump = require('pump');

// Configuration
const scripts = [
    'system/modules/isotope/assets/js/*.js',
    '!system/modules/isotope/assets/js/*.min.js'
];
const styles = [
    'system/modules/isotope/assets/css/isotope.css',
    '!system/modules/isotope/assets/css/*.min.css',
    'system/modules/isotope_reports/assets/*.css',
    '!system/modules/isotope_reports/assets/*.min.css'
];

gulp.task('scripts', function (cb) {
    pump(
        [
            gulp.src(scripts),
            ignore.exclude('*.min.js'),
            uglify(),
            rename({
                suffix: '.min'
            }),
            gulp.dest('system/modules/isotope/assets/js')
        ],
        cb
    );
});

gulp.task('styles', function (cb) {
    pump(
        [
            gulp.src(styles),
            ignore.exclude('*.min.css'),
            csso({
                comments: false,
                restructure: false
            }),
            rename({
                suffix: '.min'
            }),
            gulp.dest('core-bundle/src/Resources/contao/themes/flexible')
        ],
        cb
    );
});

gulp.task('watch', function () {
    gulp.watch(
        scripts,
        gulp.series('scripts')
    );

    gulp.watch(
        styles,
        gulp.series('styles')
    );
});

gulp.task('default', gulp.parallel('scripts', 'styles'));
