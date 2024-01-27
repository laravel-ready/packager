{
    "name": "{{ $PACKAGE_SLUG }}",
    "version": "1.0.0",
    "description": "",
    "main": "tailwind.config.js",
    "scripts": {
        "dev": "npm run development",
        "development": "mix",
        "watch": "mix watch",
        "watch-poll": "mix watch -- --watch-options-poll=1000",
        "hot": "mix watch --hot",
        "prod": "npm run production",
        "production": "mix --production"
    },
    "author": "",
    "license": "ISC",
    "devDependencies": {
        "autoprefixer": "^10.4.0",
        "cross-env": "^7.0.3",
        "laravel-mix": "^6.0.39",
        "laravel-mix-purgecss": "^6.0.0",
        "lodash": "^4.17.21",
        "postcss": "^8.4.4",
        "postcss-import": "^14.0.2",
        "resolve-url-loader": "^4.0.0",
        "sass": "^1.45.0",
        "sass-loader": "^12.4.0",
        "string-replace-loader": "^3.1.0",
        "tailwindcss": "^3.0.1",
        "webpack": "^5.65.0"
    },
    "dependencies": {}
}
