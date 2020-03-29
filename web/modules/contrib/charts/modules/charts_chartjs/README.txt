Using Composer and wikimedia/composer-merge-plugin (recommended)
========================================

Within your Drupal root's composer.json file:

  1. Ensure that you have the 'wikimedia/composer-merge-plugin` package installed.

  2. Ensure that you have the `oomphinc/composer-installers-extender` package
     installed.

  3. Add an "installer-types" section in the "extra" of your project composer.json
     file, make sure you have "bower-asset" and "npm-asset" listed.
     For example:
       "installer-types": [
           "bower-asset",
           "npm-asset"
       ],

  4. In the "installer-paths" section in the "extra" of your project composer.json
     file, ensure you have an the types drupal-library, bower-asset, and npm-asset.
     For example:
       "web/libraries/{$name}": [
           "type:drupal-library",
           "type:bower-asset",
           "type:npm-asset"
       ],

  5. Add a "merge-plugin" section in the "extra" of your project composer.json
     file, so that the composer.json file of the sub-modules you want is included.
     For example:
       "merge-plugin": {
           "include": [
               "web/modules/contrib/charts/modules/charts_chartjs/composer.json"
           ]
       },
     (please note the include path, which includes "web" and "contrib"...this may be different for you).

  6. Run the 'composer require --prefer-dist chartjs/chartjs:2.7.2'

  7. If this has worked, you will see a file in '/web/libraries/chartjs/Chart.bundle.js'


Installation using Composer (less recommended)
========================================

If you use Composer to manage dependencies, edit "/composer.json" as follows.

  1. Run "composer require --prefer-dist composer/installers" to ensure that you
     have the "composer/installers" package installed. This package facilitates
     the installation of packages into directories other than "/vendor" (e.g.
     "/libraries") using Composer.

  2. Add the following to the "installer-paths" section of "composer.json":

"libraries/{$name}": ["type:drupal-library"],

  3. Add the following to the "repositories" section of "composer.json":

        {
            "type": "package",
            "package": {
                "name": "chartjs/chartjs",
                "version": "v2.7.2",
                "type": "drupal-library",
                "extra": {
                    "installer-name": "chartjs"
                },
                "dist": {
                    "url": "https://www.chartjs.org/dist/2.7.2/Chart.bundle.js",
                    "type": "file"
                }
            }
        }

4. Run "composer require --prefer-dist chartjs/chartjs:2.7.2"
- you should find that new directories have been created under "/libraries"


Please note that there is a Chart.js issue with mixed charts that
start with a line instead of a bar, for example:

https://github.com/chartjs/Chart.js/issues/5701