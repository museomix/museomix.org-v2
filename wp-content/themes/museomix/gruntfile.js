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

 		},

        /*
		// Compilation de tous les fichiers scss se trouvant dans le dossier sass
        */
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

	// Enregistrement des tâches 
	grunt.registerTask('default', ['watch']); // "default" = "grunt" en ligne de commande directement dans le dossier du thème
	grunt.registerTask('compil', ['sass', 'postcss']); // Pour lancer cette tâche il faut écire "grunt compil". Tâche pour lancer une compil rapide sans watch

};