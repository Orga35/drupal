grunt.initConfig({
  compass: {                  // Task 
    dist: {                   // Target 
      options: {              // Target options 
        sassDir: 'sass',
        cssDir: 'css',
        environment: 'production'
      }
    },
    dev: {                    // Another target 
      options: {
        sassDir: 'sass',
        cssDir: 'css'
      }
    }
  },
  concat: {
    options: {
      separator: ';',
    },
    dist: {
      src: ['src/intro.js', 'src/project.js', 'src/outro.js'],
      dest: 'dist/built.js',
    },
  },
  watch: {
    scripts: {
      files: ['**/*.js'],
      tasks: ['concat'],
      options: {
        spawn: false,
      },
    },
  },
});
 
grunt.loadNpmTasks('grunt-contrib-compass', 'grunt-contrib-concat', 'grunt-contrib-watch');