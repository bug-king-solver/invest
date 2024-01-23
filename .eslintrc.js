module.exports = {
    env: {
        node: true
    },
    extends: ["plugin:prettier/recommended", "eslint:recommended", "prettier"],
    rules: {
        "prettier/prettier": [
            "error",
            {
                useTabs: false,
                tabWidth: 4,
                singleQuote: true,
                "object-curly-spacing": ["error", "always"],
                "array-bracket-spacing": ["error", "always"],
                "computed-property-spacing": ["error", "always"]
            }
        ],
        "comma-dangle": [
            "error",
            {
                arrays: "never",
                objects: "never",
                imports: "never",
                exports: "never",
                functions: "ignore"
            }
        ]
        // override/add rules settings here, such as:
        // 'vue/no-unused-vars': 'error'
    }
};
