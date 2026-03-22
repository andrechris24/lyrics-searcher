import js from "@eslint/js";
import globals from "globals";
import jquery from "eslint-plugin-jquery";
import { defineConfig } from "eslint/config";
import sweetAlert2EslintConfig from '@sweetalert2/eslint-config'

export default defineConfig([
  ...sweetAlert2EslintConfig,
  { files: ["**/*.{js,mjs,cjs}"], plugins: { js, jquery }, extends: ["js/recommended"], languageOptions: { globals: globals.browser } },
  { files: ["**/*.js"], languageOptions: { sourceType: "script", globals: {...globals.jquery} } },
]);
