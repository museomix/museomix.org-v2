import { terser } from 'rollup-plugin-terser';
import multiEntry from 'rollup-plugin-multi-entry';
import buble from 'rollup-plugin-buble';

export default [{
    input: [
        'assets/js/src/event-manager.js',
        'assets/js/src/front.js',
        'assets/js/src/front-facets.js'
    ],
    output: {
        file: 'assets/js/dist/front.min.js',
        format: 'iife'
    },
    watch: {
        include: 'assets/js/src/**'
    },
    plugins: [
        multiEntry(),
        terser()
    ]
},
{
    input: 'assets/js/src/admin.js',
    output: {
        file: 'assets/js/dist/admin.min.js',
        format: 'iife'
    },
    watch: {
        include: 'assets/js/src/admin.js'
    },
    plugins: [
        buble()
    ]
}]
