/* eslint-disable import/no-extraneous-dependencies, global-require */
const path = require('path');

module.exports = (paths, development) => {
  const root = path.resolve(paths.root, 'app/lib/gutenberg');

  function camelCaseDash(string) {
    return string.replace(/-([a-z])/g, (match, letter) => letter.toUpperCase());
  }

  const entryPointNames = [
    'components',
    'editor',
    'utils',
    'edit-post',
    'core-blocks',
    'nux',
  ];

  const gutenbergPackages = [
    'a11y',
    'api-fetch',
    'autop',
    'blob',
    'blocks',
    'block-serialization-spec-parser',
    'compose',
    'core-data',
    'data',
    'date',
    'deprecated',
    'dom',
    'dom-ready',
    'element',
    'hooks',
    'html-entities',
    'i18n',
    'is-shallow-equal',
    'keycodes',
    'plugins',
    'shortcode',
    'viewport',
  ];

  const externals = [];

  [
    ...entryPointNames,
    ...gutenbergPackages,
  ].forEach((name) => {
    externals[`@wordpress/${name}`] = {
      window: ['et_gb', 'wp', camelCaseDash(name)],
    };
  });

  return {
    path: root,
    sassLoaderOptions: {
      sourceMap: development,
      data: '@import "colors"; @import "breakpoints"; @import "variables"; @import "mixins"; @import "animations";@import "z-index";',
      includePaths: [path.resolve(root, 'edit-post/assets/stylesheets')],
    },
    alias: {
      '@gutenberg': root,
      tinymce: 'tinymce',
    },
    externals,
  };
};
