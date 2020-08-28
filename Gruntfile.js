/**
 * Grunt Tasks JavaScript.
 *
 * @package    Responsive_Add_Ons
 * @subpackage Responsive_Add_Ons
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
						'!release/**',
						'!build/**',
						'!bin/**',
						'!tests/**',
						'!.git/**',
						'!Gruntfile.js',
						'!package.json',
						'!package-lock.json',
						'!codeception.dist.yml',
						'!.env.testing',
						'!.gitignore',
						'!.gitmodules',
						'!.gitattributes',
						'!composer.lock',
						'!composer.json',
						'!phpunit.xml.dist',
						'!.travis.xml',
						'!.distignore',
						'!.editorconfig',
						'!.phpcs.xml.dist',
						'!README.md',
						'!config/**',
						'!scripts/**',
					],
					dest: 'release/<%= pkg.version %>/'
				}
			},
			compress: {
				build: {
					options: {
						mode: 'zip',
						archive: './release/responsive-add-ons.zip'
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
					src: ['*.php', '**/*.php', '!\.git/**/*', '!bin/**/*', '!node_modules/**/*', '!config/**/*', '!tests/**/*', '!scripts/**/*', '!vendor/**/*', '!includes/libraries/acf/**/*', '!includes/importers/wxr-importer/*']
				}
			},

			wp_readme_to_markdown: {
				your_target: {
					files: {
						'README.md': 'readme.txt'
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
		}
	);

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.registerTask( 'default', ['readme', 'build'] );
	grunt.registerTask( 'i18n', ['addtextdomain', 'makepot'] );
	grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );
	grunt.registerTask( 'build', ['clean:build', 'copy:build', 'compress:build'] );

	grunt.util.linefeed = '\n';

};
