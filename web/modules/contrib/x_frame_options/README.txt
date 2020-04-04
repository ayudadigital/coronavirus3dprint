CONTENTS OF THIS FILE
---------------------
   
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

The X-Frame-Options HTTP response header can be used to indicate whether or
not a browser should be allowed to render a page in a <frame>, <iframe> or
<object>. Sites can use this to avoid clickjacking attacks, by ensuring that
their content is not embedded into other sites. By default Drupal is
configured to not rendering the site from other sites.

The X-Frame-Options HTTP response header accepts the following directives:
1. DENY
2. SAMEORIGIN
3. ALLOW-FROM uri (Currently [2018-10-26] not accepted by chrome nor Safari)

This module will allow users to select which directive the site will have in
the response header. With the 'ALLOW-FROM' directive users will be asked to
configure which uri they want to allow to render the site from.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/x_frame_options

 * To submit bug reports and feature suggestions, or to track changes:
   https://drupal.org/project/issues/x_frame_options


REQUIREMENTS
------------

This module only requires a Drupal 8 installed.


INSTALLATION
------------

Install as you would normally install a contributed Drupal module. Visit:
https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
for further information.


CONFIGURATION
-------------

Go to Configuration » System » X-frame-options header 
(/admin/config/system/x_frame_options_configuration/settings) and select the
directive you want to use and if asked type the uri you will allow to render
your site from.


MAINTAINERS
-----------

Current maintainers:
 * Efrain Herrera (efrainh) - https://www.drupal.org/u/efrainh
