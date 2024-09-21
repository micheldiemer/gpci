import pluginJs from "@eslint/js";
import globals from "globals";

export default [
  { files: ["**/*.js"], languageOptions: { sourceType: "commonjs" } },
  {
    languageOptions: {
      globals: {
        $: "readonly",
        angular: "readonly",
        webApp: "writable",
        moment: "readonly",
        ...globals.browser,
      },
    },
    rules: {
      "no-undef": "error",
      "no-var": "error",
    },
  },

  pluginJs.configs.recommended,
];
