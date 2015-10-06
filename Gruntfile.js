
module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        watch: {
            css: {
                files: ['sass/*.scss'],
                tasks: ['sass'],
                options: {
                    spawn: false,
                    livereload: true
                }
            },
            scripts: {
                files: ['js/src/*.js'],
                tasks: ['jshint', 'concat'],
                options: {
                    spawn: false,
                    livereload: true
                }
            },
            templates: {
                files: ['js/src/templates/*.tpl'],
                tasks: ['jst', 'jshint', 'concat'],
                options: {
                    spawn: false,
                    livereload: true
                }
            }
        },
        concat: {
            options: {
                // define a string to put between each file in the concatenated output
                separator: ';'
            },
            dist: {
                // the files to concatenate
                src: ['js/src/vendor/soundmanager2/script/soundmanager2.js', 
                    'js/src/vendor/underscore.js', 'js/src/vendor/backbone.js',
                    'js/src/vendor/jquery.finger.js',
                    'js/src/templates/compiled.js', 'js/src/stereo.js', 'js/src/stereo-history.js'],
                // the location of the resulting JS file
                dest: 'js/<%= pkg.name %>.js'
            }
        },
        uglify: {
            options: {
                // the banner is inserted at the top of the output
                banner: '/* <%= pkg.name %>\n' +
                    '* <%= pkg.homepage %> \n' +
                    '* Copyright (c) <%= grunt.template.today("yyyy") %>\n' +
                    '* <%= pkg.author.name %>, <%= pkg.author.email %>\n' +
                    '* <%= pkg.name %> may be freely distributed under the MIT license.\n*/\n'
            },
            dist: {
                files: {
                    'js/<%= pkg.name %>.min.js': ['<%= concat.dist.dest %>']
                }
            }
        },
        qunit: {
            files: ['test/*.html']
        },
        jshint: {
            files: ['Gruntfile.js', 'js/src/*.js', 'test/*.js'],
            options: {
                // options here to override JSHint defaults
                /*
                globals: {
                    jQuery: true,
                    console: true,

                }
                */
            }
        },
        jst: {
            compile: {
                options: {
                    namespace: "Stereo.Tmpl",
                    processName: function (filename) {
                        return filename.split('/').pop().split('.')[0];
                    }
                },
                files: {
                    "js/src/templates/compiled.js": ["js/src/templates/*.tpl"]
                }
            }
        },
        copy: {
            main: {
                files: [
                    {expand: true, cwd: "js/src/vendor/soundmanager2/swf", src: ["*.swf", "!*debug*"], dest:"js/swf/"}
                ]
            }
        },
        sass: {
            options: {
                includePaths: require('node-bourbon').includePaths,
                sourceMap: true
            },
            dist: {
                files: {
                    'css/stereo.css': 'sass/stereo.scss'
                }
            }
        }

    });

    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-qunit');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-jst');

    grunt.registerTask('test', ['jst', 'jshint', 'qunit']);
    grunt.registerTask('default', ['sass', 'jst', 'jshint',  'concat', 'uglify', 'copy']);
    grunt.registerTask('release', ['sass', 'jst', 'jshint',  'concat', 'uglify', 'copy']);

};
