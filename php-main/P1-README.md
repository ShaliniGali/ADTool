# NPM commands

```
npm install
npm run test:unit
npm run lint
npm run build
```

After running the `build` script all sources will be generated under `./dist`
```
dist/
  assets      contains js, css, webfonts, images from the assets folder
  static      contains compile react sso tile app
```

## Dockerfile

The `dist` folder and `assets/js/index.js` should be copied to `/home/node/code`. Then running `node index.js` will create a webserver to provide S3 features and statically serve the folders `assets` and `static`



### TODOs:
- Add aditional test to increase coverage
- Review if old tiles page js files are still needed 
- Verify if PHP js_comporess and css_press features still work
- Double check if `dist/assets/js` folder includes all the files. If anything is missing update npm script `copy-to-dist` in `assets/package.json` 
- Why is this folder excluded for js test `assets/js/NIPR/*`
- If `dist` filder is not copied to the exact location in docker container, update index.js to server the correct folders.
- Update Dockerfile or create new one for final build on P1
- Update python script for copying files



### Python script
```
assets                    -> /assets
sso-tile-app              -> /sso-tile-app
package.json              -> /package.json  
Dockerfile.js             -> /Dockerfile
sso-tile-app/controllers  -> /controllers
sso-tile-app/index.js     -> /index.js
babel.config.js           -> /babel.config.js
```