import js from '@eslint/js'
import vue from 'eslint-plugin-vue'
import globals from "globals";

export default [
  js.configs.recommended,
  ...vue.configs['flat/essential'],
  {
    files: ['resources/**/*.{js,vue}'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
        process: 'readonly',
        import: 'readonly',
        route: 'readonly',
        axios: 'readonly'
      },
    },
    rules: {
      'vue/multi-word-component-names': 'off',
      'no-console': 'off',
      'vue/multi-word-component-names': 'off',
      'vue/no-v-html': 'warn',
      'vue/require-default-prop': 'off',
      'vue/require-explicit-emits': 'error',
      'no-debugger': 'off',
      'no-unused-vars': 'warn'
    }
  }
]
