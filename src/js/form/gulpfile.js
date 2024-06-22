const gulp = require('gulp');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify-es').default;

const watch = () => {
    gulp.watch('src/*.js', concatJs);
}

const concatJs = () => {
    return gulp.src('src/*.js')
    .pipe(concat('sysaForm.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('dist/'));
}

exports.mainJs = concatJs;
exports.watch = watch;
console.log(uglify);
gulp.task('default', gulp.parallel(watch));