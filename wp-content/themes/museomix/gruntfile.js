module.exports = function(grunt){

	require('time-grunt')(grunt);
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

 		watch: {
 			css: {
 				files: './*.scss',
 				tasks: ['sass', 'postcss'],
 		        options: {
 		            spawn: false
 		        }
 			},
			png: {
 				files: 'sprites/*.png',
 				tasks: ['sprite'],
				options: {
						spawn: false
				}
 			},
			php: {
 				files: '*.php',
 				tasks: ['makepot'],
				options: {
						spawn: false
				}
 			},

 		},

        /*
		// Compilation de tous les fichiers scss se trouvant dans le dossier sass
        */
		sprite:{
		  all: {
			src: 'sprites/*.png',
			dest: 'sprites.png',
			destCss: 'sprites.css',
			cssTemplate: 'assets/handlebars/handlebarsStr.css.handlebars'
		  }
		},
		makepot: {
        target: {
            options: {
                domainPath: 'languages',                   // Where to save the POT file.
                                // Headers to add to the generated POT file.
                type: 'wp-theme',                // Type of project (wp-plugin or wp-theme).
                updateTimestamp: true,            // Whether the POT-Creation-Date should be updated without other changes.
                updatePoFiles: false              // Whether to update PO files in the same directory as the POT file.
            }
        }
    },
 		sass: {
 	      	dist: {
		        files: [{
					"expand": true,
					"cwd": "./",
					"src": ["*.scss"],
					"dest": "./",
					"ext": ".css"
	 	        }]
 	      	}
 	    },

 		/*
		//	Tâche qui passe après la compilation sass pour :
		//	-	génération des sourcempas
		//	-	ajouts des fallbacks aux unités rem
		// 	-	préfixe automatiquement ajouté si nécessaire en fonction du nombre de versions à supporter
		//	-	minification css
 		*/
 		postcss: {
 		    options: {
 		      map: true,
 		      processors: [
 		        require('pixrem')(), // add fallbacks for rem units
 		        require('autoprefixer')({browsers: 'last 3 versions'}), // add vendor prefixes
 		        require('cssnano')() // minify the result
 		      ]
 		    },
 		    dist: {
 		      files: [{
 		        "expand": true,
 		        "cwd": "./",
 		        "src": ["*.css"],
 		        "dest": "./",
 		        "ext": ".css"
 		      }]
 		     }
 		},


     });

	// Chargement des plugins
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-postcss');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-spritesmith');
	grunt.loadNpmTasks( 'grunt-wp-i18n' );


	// Enregistrement des tâches
	grunt.registerTask('default', ['watch']); // "default" = "grunt" en ligne de commande directement dans le dossier du thème
	grunt.registerTask('compil', ['sass', 'postcss', 'sprite']); // Pour lancer cette tâche il faut écire "grunt compil". Tâche pour lancer une compil rapide sans watch
	grunt.task.run('compil');
	grunt.task.run('sprite');

};
