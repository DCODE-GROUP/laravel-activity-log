module.exports = {
  "root": true,
  "parser": "vue-eslint-parser",
  "parserOptions": {
    "parser": "@babel/eslint-parser",
    "sourceType": "module",
    "ecmaVersion": 2018,
    "ecmaFeatures": {
      "globalReturn": false,
      "impliedStrict": false,
      "jsx": true
    },
    "requireConfigFile": false,
  },
  "settings": {
    "import/resolver": {
      "node": {
        "extensions": [".js", ".jsx", ".ts", ".tsx", ".json", ".vue", ".scss"]
      }
    }
  },
  "extends": [
    "eslint:recommended",
    "plugin:prettier/recommended",
    "plugin:vue/essential",
  ],
  "env": {
    "browser": true,
    "es6": true,
    "jest": true,
    "node": true
  },
  "plugins": ["jsx-a11y", "prettier", "@typescript-eslint", "jsx", "flow"],
  "rules": {
    "semi": 0,
    "max-len": [2, 120, 2],
    "no-console": "warn",
    "no-prototype-builtins": "warn",
    "jsx-a11y/label-has-associated-control": "off",
    "prettier/prettier": ["error"],
    "prefer-promise-reject-errors": "off",
    "camelcase": "off",
    "vue/no-v-for-template-key": "off",
    "@typescript-eslint/no-namespace": "off",
    "@typescript-eslint/ban-ts-comment": "off",
    "@typescript-eslint/no-var-requires": "off",
  }
};
