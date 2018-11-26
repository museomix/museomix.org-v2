/* eslint-disable import/no-extraneous-dependencies, global-require */
const path = require('path');
const merge = require('webpack-merge');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const DirectoryNamedWebpackPlugin = require('directory-named-webpack-plugin');
const selectorPrefixer = require('postcss-prefix-selector');

module.exports = (development, customLoader = false) => {
  const paths = {
    root: path.join(__dirname, '../'),
    build: path.join(__dirname, '../build/'),
  };

  const entry = {
    bundle: path.join(paths.root, 'app/app.jsx'),
    gutenberg: path.join(paths.root, 'gutenberg/controller.js'),
  };

  const gutenberg = require('./gutenberg.js')(paths, development);
  const cssLoaders = options => [
    // If a custom loader is provided, use that instead of 'style-loader'
    customLoader || {
      loader: 'style-loader',
      options: {
        sourceMap: development,
      },
    },
    {
      loader: 'css-loader',
      options: {
        url: false,
        importLoaders: 2,
        sourceMap: development,
      },
    },
    {
      loader: 'postcss-loader',
      options: {
        ident: 'postcss',
        sourceMap: development,
        plugins: [
          require('autoprefixer'),
          selectorPrefixer({
            prefix: '.et-db #et-boc',
            // Exclude selectors which start with "html" or "body" or already contain the prefix.
            exclude: [
              /^html($|\s?)/,
              /^body($|\s?)/,
              /\.et-db #et-boc/,
              /^:export/,
              /^\.gutenberg__editor($|\s?)/,
            ],
          }),
        ],
      },
    },
    {
      loader: 'sass-loader',
      options,
    },
  ];

  return {
    entry,
    output: {
      path: paths.build,
      filename: '[name].js',
      chunkFilename: development ? 'bundle.[name].js' : 'bundle.[name].[chunkhash:8].js',
      library: ['ET_BUNDLES', '[name]'],
      libraryTarget: 'window',
    },
    externals: merge(
      gutenberg.externals,
      {
        jquery: 'jQuery',
        underscore: '_',
        react: 'React',
        'react-dom': 'ReactDOM',
      },
    ),
    resolve: {
      extensions: ['.js', '.jsx'],
      alias: merge(
        gutenberg.alias,
        {
          lodash$: path.resolve(paths.root, 'app/lib/lodash.js'),
          lodash: path.resolve(paths.root, 'node_modules/lodash'),
          'prop-types': path.resolve(paths.root, 'node_modules/prop-types'),
          'performance-now': path.resolve(paths.root, 'node_modules/performance-now'),
          'core-ui': path.resolve(paths.root, '../../../core/ui'),
          gutenberg: path.resolve(paths.root, 'gutenberg'),
        },
      ),
      modules: [path.resolve(paths.root, 'node_modules')],
      plugins: [
        new DirectoryNamedWebpackPlugin(true),
      ],
    },
    module: {
      rules: [
        {
          test: /\.jsx?$/,
          exclude: new RegExp(`(node_modules|bower_components)(?!(\\${path.sep}file-type))`),
          use: [
            {
              loader: 'babel-loader',
              options: {
                compact: false,
                presets: [
                  [require('babel-preset-env'), {
                    modules: false,
                    targets: { browser: ['last 2 versions', '> 5%'] },
                  }],
                  require('babel-preset-react'),
                ],
                plugins: [
                  development ? require('react-hot-loader/babel') : false,
                  require('babel-plugin-transform-object-rest-spread'),
                  require('babel-plugin-transform-class-properties'),
                  require('babel-plugin-syntax-dynamic-import'),
                ].filter(plugin => plugin !== false),
                cacheDirectory: false,
              },
            },
          ],
        },
        {
          // CSS loaders for our own code
          test: /\.s?css$/i,
          exclude: gutenberg.path,
          use: cssLoaders({
            sourceMap: development,
          }),
        },
        {
          // CSS loaders for gutenberg code
          test: /\.s?css$/i,
          include: gutenberg.path,
          use: cssLoaders(gutenberg.sassLoaderOptions),
        },
      ],
    },
    optimization: {
      splitChunks: {
        cacheGroups: {
          // Enable this so create chunks including common code shared between different entry points, eg lodash
          // commons: {
          //   name: 'commons',
          //   chunks: 'initial',
          //   minChunks: 2,
          // },
          vendors: false,
        },
      },
    },
    plugins: [
      new CleanWebpackPlugin([
        'build',
        'bundle*.js',
        'bundle*.map',
        'assets/css/style.css',
      ], {
        root: paths.root,
        verbose: true,
      }),
    ],
  };
};
