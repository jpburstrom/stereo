module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        concat: {
            options: {
                // define a string to put between each file in the concatenated output
                separator: ';'
            },
            dist: {
                // the files to concatenate
                src: ['src/stereo.js', 'src/templates/compiled.js'],
                // the location of the resulting JS file
                dest: 'dist/<%= pkg.name %>.js'
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
                    'dist/<%= pkg.name %>.min.js': ['<%= concat.dist.dest %>']
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
    grunt.registerTask('default', ['jst', 'jshint', 'qunit', 'concat', 'uglify']);

};
