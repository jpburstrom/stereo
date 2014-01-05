module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        watch: {
            scripts: {
                files: ['src/*.js'],
                tasks: ['jshint', 'concat'],
                options: {
                    spawn: false,
                    livereload: true
                }
            },
            templates: {
                files: ['src/templates/*.tpl'],
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
                src: ['src/vendor/soundmanager2/script/soundmanager2.js', 'src/templates/compiled.js', 'src/stereo.js', 'src/stereo-history.js'],
                // the location of the resulting JS file
                dest: '<%= pkg.name %>.js'
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
                    '<%= pkg.name %>.min.js': ['<%= concat.dist.dest %>']
                }
            }
        },
        qunit: {
            files: ['test/*.html']
        },
        jshint: {
            files: ['Gruntfile.js', 'src/*.js', 'test/*.js'],
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
                    "src/templates/compiled.js": ["src/templates/*.tpl"]
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-qunit');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-jst');

    grunt.registerTask('test', ['jst', 'jshint', 'qunit']);
    grunt.registerTask('default', ['jst', 'jshint',  'concat', 'uglify']);

};
