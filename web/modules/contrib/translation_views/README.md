CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Provides fields and filters for views to display information about each row, the
**source** translation, in another **target** language. The user can also add
translation operation links to decide if the user wants to add/edit the content
in the target language.

Configurations for a demo view are imported on installation, named `Content
translation jobs` which can be found at path `/translate/content`. If the user
wants to get the view for other translatable entities, the user should build it
themselves using our fields/filters.

**Notice**

This module provides view's fields/filters only for translatable
entity types, this means that until the user has enabled content translation
support for an entity they'll not have any extra fields/filters. The content
translations can be enabled at path `admin/config/regional/content-language.


FEATURES
--------

**Target translation**

 * Target language: `field`,`filter`. Should in most cases be an exposed filter.
   Also, there is an extra filter option: *Remove rows where language equals
   target language

These fields/filters will display information about each row in the selected
target language:
 * Translation outdated: `field`,`filter`
 * Translation status: `field`,`filter`
 * Target language equals default language: `field`,`filter`: Checks if the
   target language is the same as the original language of the node.
 * Translation changed time: `field`
 * Source translation of target language equals row language: `field`,`filter`:
   Checks if the source translation of the row is the same as the target
   language
 * Translation operations: `field`
 * Translation moderation state: `field`

**Source translation**

 * Translation counter: `field`,`filter`: count translations of original
   language. The user can also configure the field to include original language
   in the count.


REQUIREMENTS
------------

 * PHP 7+

This module requires no modules outside of Drupal core.


RECOMMENDED MODULES
-------------------

The user can limit the target language filter list to the users registered
translation skill when using
 * [Translators](https://www.drupal.org/project/translators)


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit:
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

No configuration is needed.

### Demo view: Content translation jobs:
Upon installation, a new view is added named `Content translation jobs` that
demonstrate how the fields/filters can be used to create lists of translation
jobs. Available at path `/translate/content`.

If this didn't happen on installation, then navigate to
`admin/config/development/configuration/single/import` and manually import
config from the file located in the module folder at:
`config/optional/views.view.content_translations.yml`.

**Fields**

The view display:
 * Title for each node in the view
 * The node language
 * The target language
 * The translation status of target language.
 * The time the content was last changed in target language
 * Translation operations to add/edit translation in target language

**Filters**

Exposed filters:
 * Source language (the language of each node in the view)
 * Target language
 * Outdated status in target language
 * Translation status in target language

Hidden filters:
 * Source content outdated must be false: Make sure you do not translate from
   outdated nodes.
 * Target language equals default language must be false: As there is no use to
   check the status of original language as a target language.
 * The target language must be untranslated or the same as the source language
   of translation. This makes sure that once there is a translation in the
   target language it will only be displayed if the source language is the
   correct source language. If the target language is not translated you can use
   different language as source translation.


MAINTAINERS
-----------

Developed by [vlad.dancer](https://drupal.org/u/vladdancer)  
Designed by [matsbla](https://drupal.org/u/matsbla)
