Extra Field module
------------------
Provides a plugin type for extra fields in content entity view.

This module allows developers to add custom fields by simply providing a plugin.
Site builders can use these fields in entity view modes as normally. Extra
fields do not store data and do not have a field widget. Extra fields typically
combine existing entity data and format it for display.

Examples
--------
The Extra Field Example module (modules/extra_field_example) contains ready to
use plugins. You can copy an example over to you (custom) module and modify it
to suit your needs. Make sure the Display plugin resides in the directory
[module name]/src/Plugin/ExtraField/Display.

API
---
Extra fields uses hook_entity_extra_field_info() to declare fields per entity
type and bundle. Plugins can be configured (with annotation) per entity type and
per bundle.

The object of the entity being viewed is available to the extra field plugin.
The plugin returns a renderable array which is added back in to the entity view
in hook_entity_view().

As usual with plugins, an alter hook is available. See extra_field.api.php for
documentation of hook_extra_field_display_info_alter().

Plugins
-------
Plugins of type "ExtraFieldDisplay" can be used to provide extra field Displays.
Plugin examples can be found in the included extra_field_example module.

ExtraFieldDisplay annotation should at least contain:
```
 * @ExtraFieldDisplay(
 *   id = "plugin_id",
 *   label = @Translation("Field name"),
 *   bundles = {
 *     "entity_type.bundle_name"
 *   }
 * )
```

To define a plugin for all bundles of a given entity type, use the '*' wildcard:
```
 *   bundles = {
 *     "entity_type.*"
 *   }
```

Other annotation options:
```
 *   weight = 10,
 *   visible = true
```

Plugin base classes
-------------------
Different bases classes are provided each containing different tools. The extra
field plugin must at least extend the ExtraFieldDisplayInterface.

ExtraFieldDisplayBase
  When using this base class, all output formatting has to take place in the
  plugin. No HTML wrappers are provided around the plugin output.

ExtraFieldDisplayFormattedBase
  When using this base class, the field output will be wrapped with field html
  wrappers. The field template can be used to override the html output as usual.
