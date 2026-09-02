import nodeResolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import babel from '@rollup/plugin-babel';
import replace from '@rollup/plugin-replace';
import terser from '@rollup/plugin-terser';

const stripUseClient = () => ({
  name: 'strip-use-client',
  transform(code) {
    if (code.startsWith("'use client';") || code.startsWith('"use client";')) {
      return {
        code: code.replace(/^['"]use client['"];\s*/, ''),
        map: null,
      };
    }

    return null;
  },
});

export default {
  input: 'src/react/app.js',
  output: {
    dir: 'assets/js',
    format: 'iife',
    name: 'library',
    compact: true,
    sourcemap: false,
    globals: {
      react: 'React',
      'react-dom': 'ReactDOM',
      'react-dom/client': 'ReactDOM',
    },
  },
  plugins: [
    stripUseClient(),
    nodeResolve({
      browser: true,
      extensions: ['.js', '.jsx', '.ts', '.tsx'],
      exportConditions: ['browser', 'module', 'import', 'default'],
      mainFields: ['browser', 'module', 'jsnext:main', 'main'],
    }),
    babel({
      babelHelpers: 'bundled',
      presets: ['@babel/preset-react'],
      extensions: ['.js', '.jsx'],
      exclude: /node_modules/,
    }),
    commonjs(),
    replace({
      preventAssignment: false,
      'process.env.NODE_ENV': '"development"'
    }),
    terser()
  ],
  external: [
    'react',
    'react-dom',
    'react-dom/client',
  ]
};
