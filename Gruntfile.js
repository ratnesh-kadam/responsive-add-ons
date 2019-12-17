/**
 * Grunt Tasks JavaScript.
 *
 * @package    Responsive Ready Sites Importer
 * @author     CyberChimps <https://www.cyberchimps.com>
 */

module.exports = function (grunt) {

	'use strict';

	// Project configuration.
	grunt.initConfig(
		{

			pkg: grunt.file.readJSON( 'package.json' ),
			clean: {
				build: ['release/<%= pkg.version %>']
			},
			copy: {
				build: {
					options: {
						mode: true,
						expand: true,
					},
					src: [
						'**',
						'!node_modules/**',
						'!vendor/**',
						'!config/**',
						'!release/**',
						'!build/**',
						'!.git/**',
						'!Gruntfile.js',
						'!package.json',
						'!package-lock.json',
						'!.gitignore',
						'!.gitmodules',
						'!composer.lock',
						'!composer.json',
						'!.env.testing',
						'!tests/**',
						'!scripts/**',
						'!codeception.dist.yml'
					],
					dest: 'release/<%= pkg.version %>/'
				}
			},
			compress: {
				build: {
					options: {
						mode: 'zip',
						archive: './release/<%= pkg.name %>.<%= pkg.version %>.zip'
					},
					expand: true,
					cwd: 'release/<%= pkg.version %>/',
					src: ['**/*'],
					dest: '<%= pkg.name %>'
				}
			},

			addtextdomain: {
				options: {
					textdomain: 'responsive-addons',
				},
				update_all_domains: {
					options: {
						updateDomains: true
					},
					src: ['*.php', '**/*.php', '!\.git/**/*', '!bin/**/*', '!node_modules/**/*', '!config/**/*', '!tests/**/*', '!scripts/**/*', '!vendor/**/*', '!includes/libraries/acf/**/*', '!includer/importers/wxr-importer/*']
				}
			},

			wp_readme_to_markdown: {
				your_target: {
					files: {
						'README.md': 'README.txt'
					}
				},
			},

			makepot: {
				target: {
					options: {
						domainPath: '/languages',
						exclude: ['\.git/*', 'bin/*', 'node_modules/*', '!config/**/*', '!tests/**/*', '!scripts/**', '!vendor/**/*', '!includes/libraries/acf/**/*'],
						mainFile: 'responsive-add-ons.php',
						potFilename: 'responsive-addons.pot',
						potHeaders: {
							poedit: true,
							'x-poedit-keywordslist': true
						},
						type: 'wp-plugin',
						updateTimestamp: true
					}
				}
			},
			uglify: {
				options: {

				},
				admin: {
					files: [{
						expand: true,
						cwd: 'release/<%= pkg.version %>/assets/js/admin/',
						src: [
							'*.js',
							'!*.min.js'
						],
						dest: 'release/<%= pkg.version %>/assets/js/admin/',
						ext: '.min.js'
					}]
				},
				frontend: {
					files: [{
						expand: true,
						cwd: 'release/<%= pkg.version %>/assets/js/',
						src: [
							'*.js',
							'!*.min.js'
						],
						dest: 'release/<%= pkg.version %>/assets/js/',
						ext: '.min.js'
					}]
				},
			},
			cssmin: {
				options: {

				},
				admin: {
					files: [{
						expand: true,
						cwd: 'release/<%= pkg.version %>/assets/css/admin/',
						src: [
							'*.css',
							'!*.min.css'
						],
						dest: 'release/<%= pkg.version %>/assets/css/admin/',
						ext: '.min.css'
					}]
				},
				frontend: {
					files: [{
						expand: true,
						cwd: 'release/<%= pkg.version %>/assets/css/',
						src: [
							'*.css',
							'!*.min.css'
						],
						dest: 'release/<%= pkg.version %>/assets/css/',
						ext: '.min.css'
					}]
				},
			},
		}
	);

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.registerTask( 'default', ['i18n'] );
	grunt.registerTask( 'i18n', ['addtextdomain', 'makepot'] );
	grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );
	grunt.registerTask( 'build', ['clean:build', 'copy:build', 'uglify:admin', 'uglify:frontend', 'cssmin:admin', 'cssmin:frontend', 'compress:build'] );

	grunt.util.linefeed = '\n';

};
