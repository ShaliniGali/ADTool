module.exports = {
    "env": {
        "browser": true,
        "es2021": true
    },
    "extends": [
        "eslint:recommended",
        "plugin:@typescript-eslint/recommended"
    ],
    "parser": "@typescript-eslint/parser",
    "parserOptions": {
        "ecmaVersion": "latest",
        "sourceType": "module"
    },
    "plugins": [
        "@typescript-eslint"
    ],
    "rules": {
        "no-console": "off", 
        "require-atomic-updates": "off", 
        "no-undef": "off", 
        "no-unused-vars": "off",
        "@typescript-eslint/no-unused-vars": "off",
        "@typescript-eslint/no-this-alias": "off",
        "@typescript-eslint/no-empty-function": "off",
        "@typescript-eslint/no-extra-semi": "off",
	"no-const-assign": "off",
        "no-mixed-spaces-and-tabs": "off",
        "no-dupe-keys": "off",
        "no-empty": "off",
        "no-useless-escape": "off",
	"no-dupe-else-if":"off",
        "no-prototype-builtins": "off",
        "no-redeclare": "off",
        "no-inner-declarations": "off",
        "no-cond-assign": "off",
	"@typescript-eslint/no-var-requires": "off"
    }
}

