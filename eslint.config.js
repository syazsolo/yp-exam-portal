import js from "@eslint/js";
import prettier from "eslint-config-prettier";
import globals from "globals";
import vue from "eslint-plugin-vue";

export default [
    {
        ignores: ["node_modules/**", "public/build/**"],
    },
    js.configs.recommended,
    ...vue.configs["flat/essential"],
    prettier,
    {
        files: ["resources/js/**/*.{js,vue}"],
        languageOptions: {
            ecmaVersion: "latest",
            sourceType: "module",
            globals: {
                ...globals.browser,
                route: "readonly",
            },
        },
        rules: {
            "vue/multi-word-component-names": "off",
            "vue/require-default-prop": "off",
            "vue/require-prop-types": "off",
        },
    },
];
