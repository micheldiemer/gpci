import pluginJs from "@eslint/js";
import globals from "globals";

export default [
  { files: ["**/*.js"], languageOptions: { sourceType: "commonjs" } },
  pluginJs.configs.recommended,
  {
    languageOptions: {
      globals: {
        $: "readonly",
        angular: "readonly",
        webApp: "writable",
        moment: "readonly",
        BASE_URL: "writable",
        process: "readonly",
        ...globals.browser,
      },
    },
    rules: {
      "no-undef": "error",
      "no-var": "error",
      "no-unused-vars": "off",
    },
  },
];
