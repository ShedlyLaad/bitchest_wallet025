module.exports = {
    root: true,
    env: {
      browser: true,
      node: true,
      es2021: true
    },
    extends: [
      'plugin:vue/vue3-recommended',
      'plugin:@typescript-eslint/recommended',
      'eslint:recommended'
    ],
    parser: 'vue-eslint-parser',
    parserOptions: {
      parser: '@typescript-eslint/parser',
      ecmaVersion: 2020,
      sourceType: 'module'
    },
    plugins: [
      'vue',
      '@typescript-eslint',
      'tailwindcss'
    ],
    ignorePatterns: ['dist', 'node_modules'],
    rules: {
      // TypeScript unused vars: autorise les variables commençant par _
      '@typescript-eslint/no-unused-vars': ['warn', { "argsIgnorePattern": '^_' }],
      // règles Vue utiles
      'vue/script-setup-uses-vars': 'error',
      'vue/no-deprecated-slot-attribute': 'off',
      // règles Tailwind (optionnel si tu as eslint-plugin-tailwindcss)
      // 'tailwindcss/classnames-order': 'warn'
    }
  }