<?php

namespace Drupal\popup_entity\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\popup_entity\PopupInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the PopupEntity entity.
 *
 * @ingroup popup_entity
 *
 * This is the main definition of the entity type. From it, an EntityType object
 * is derived. The most important properties in this example are listed below.
 *
 * id: The unique identifier of this entity type. It follows the pattern
 * 'moduleName_xyz' to avoid naming conflicts.
 *
 * label: Human readable name of the entity type.
 *
 * handlers: Handler classes are used for different tasks. You can use
 * standard handlers provided by Drupal or build your own, most probably derived
 * from the ones provided by Drupal. In detail:
 *
 * - view_builder: we use the standard controller to view an instance. It is
 *   called when a route lists an '_entity_view' default for the entity type.
 *   You can see this in the entity.popup_entity_popup.canonical
 *   route in the popup_entity.routing.yml file. The view can be
 *   manipulated by using the standard Drupal tools in the settings.
 *
 * - list_builder: We derive our own list builder class from EntityListBuilder
 *   to control the presentation. If there is a view available for this entity
 *   from the views module, it overrides the list builder if the "collection"
 *   key in the links array in the Entity annotation below is changed to the
 *   path of the View. In this case the entity collection route will give the
 *   view path.
 *
 * - form: We derive our own forms to add functionality like additional fields,
 *   redirects etc. These forms are used when the route specifies an
 *   '_entity_form' or '_entity_create_access' for the entity type. Depending on
 *   the suffix (.add/.default/.delete) of the '_entity_form' default in the
 *   route, the form specified in the annotation is used. The suffix then also
 *   becomes the $operation parameter to the access handler. We use the
 *   '.default' suffix for all operations that are not 'delete'.
 *
 * - access: Our own access controller, where we determine access rights based
 *   on permissions.
 *
 * More properties:
 *
 *  - base_table: Define the name of the table used to store the data. Make sure
 *    it is unique. The schema is automatically determined from the
 *    BaseFieldDefinitions below. The table is automatically created during
 *    installation.
 *
 *  - fieldable: Can additional fields be attached to the entity via the GUI?
 *    Can the user add fields, like they would to a node?
 *
 *  - entity_keys: How to access the fields. Specify fields from
 *    baseFieldDefinitions() which can be used as keys.
 *
 *  - links: Provide links to do standard tasks. The 'edit-form' and
 *    'delete-form' links are added to the list built by the
 *    entityListController. They will show up as action buttons in an additional
 *    column.
 *
 * There are many more properties to be used in an entity type definition. For
 * a complete overview, please refer to the '\Drupal\Core\Entity\EntityType'
 * class definition.
 *
 * The following construct is the actual definition of the entity type which
 * is read and cached. Don't forget to clear cache after changes.
 *
 * @ContentEntityType(
 *   id = "popup_entity_popup",
 *   label = @Translation("Popup"),
 *   translatable = TRUE,
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\popup_entity\Entity\Controller\PopupListBuilder",
 *     "form" = {
 *       "default" = "Drupal\popup_entity\Form\PopupForm",
 *       "delete" = "Drupal\popup_entity\Form\PopupDeleteForm",
 *     },
 *     "access" = "Drupal\popup_entity\PopupAccessControlHandler",
 *   },
 *   list_cache_contexts = { "user" },
 *   base_table = "popup",
 *   data_table = "popup_entity_popup_field_data",
 *   admin_permission = "administer popup entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/popup_entity_popup/{popup_entity_popup}",
 *     "edit-form" = "/popup_entity_popup/{popup_entity_popup}/edit",
 *     "delete-form" = "/popup/{popup_entity_popup}/delete",
 *     "collection" = "/popup_entity_popup/list"
 *   },
 *   field_ui_base_route = "popup_entity.popup_settings",
 * )
 *
 * The 'links' above are defined by their path. For core to find the
 * corresponding route, the route name must follow the correct pattern:
 *
 * entity.<entity_type>.<link_name>
 *
 * Example: 'entity.popup_entity_popup.canonical'.
 *
 * See the routing file at popup_entity.routing.yml for the
 * corresponding implementation.
 *
 * The Popup class defines methods and fields for the popup entity.
 *
 * Being derived from the ContentEntityBase class, we can override the methods
 * we want. In our case we want to provide access to the standard fields about
 * creation and changed time stamps.
 *
 * Our interface (see PopupInterface) also exposes the EntityOwnerInterface.
 * This allows us to provide methods for setting and providing ownership
 * information.
 *
 * The most important part is the definitions of the field properties for this
 * entity type. These are of the same type as fields added through the GUI, but
 * they can by changed in code. In the definition we can define if the user with
 * the rights privileges can influence the presentation (view, edit) of each
 * field.
 *
 * The class also uses the EntityChangedTrait trait which allows it to record
 * timestamps of save operations.
 */
class Popup extends ContentEntityBase implements PopupInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * Gets the label of the entity.
   *
   * @return string|null
   *   The label of the entity, or NULL if there is no label defined.
   */
  public function label(){
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behaviour of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Popup entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Popup entity.'))
      ->setReadOnly(TRUE);


    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The name of author.'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 2,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => 'Enter here user name...',
        ],
      ])
      ->setSetting('handler', 'default');


    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('Administrative title'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      // Set no default value.
      ->setDefaultValue(NULL)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);


    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The language code of PopupEntity entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));


    $config = \Drupal::config('system.theme');
    $default_theme = $config->get('default');

    $breakpoints_list = array();
    $breakpoint = \Drupal::service('breakpoint.manager')->getBreakpointsByGroup($default_theme);
    foreach ($breakpoint as $breakpoint_name => $breakpoint_item){
      /* @var $breakpoint_item \Drupal\breakpoint\Breakpoint */
      $breakpoints_list[$breakpoint_name] = $breakpoint_item->getLabel()->render();
      $mediaquery = $breakpoint_item->getMediaQuery();
      if(!empty($mediaquery)){
        $breakpoints_list[$breakpoint_name] .= ' ['.$mediaquery.']';
      }
    }
    $fields['breakpoints'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Breakpoints'))
      ->setDescription(t('Empty = show in all breakpoints.'))
      ->setCardinality(-1)
      ->setSetting('allowed_values', $breakpoints_list)
      ->setDisplayOptions('form', array(
        'type' => 'options_buttons',
        'weight' => 6
      ))
      ->setDefaultValue(NULL);

    $fields['width'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Width'))
      ->setDescription(t('Pop width in %. 0 = auto.'))
      ->setDisplayOptions('form', array(
        'weight' => 6
      ))
      ->setDefaultValue(0);

    $fields['height'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Height'))
      ->setDescription(t('Pop height in %. 0 = auto.'))
      ->setDisplayOptions('form', array(
        'weight' => 7
      ))
      ->setDefaultValue(0);

    $fields['position_x'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Horizontal'))
      ->setSettings([
        'allowed_values' => [
          'left' => t('Left'),
          'center' => t('Center'),
          'right' => t('Right'),
        ],
      ])
      ->setDisplayOptions('form', array(
        'weight' => 8
      ))
      ->setDefaultValue('center');

    $fields['position_y'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Vertical'))
      ->setSettings([
        'allowed_values' => [
          'top' => t('Top'),
          'middle' => t('Middle'),
          'bottom' => t('Bottom'),
        ],
      ])
      ->setDisplayOptions('form', array(
        'weight' => 9
      ))
      ->setDefaultValue('middle');

    $fields['open_delay'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Auto open delay'))
      ->setDescription(t('Time in milliseconds that the popup will wait before opening'))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 10
      ))
      ->setDefaultValue(0);

    $fields['times_to_show'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Times to close'))
      ->setDescription(t('Number of times that you have to close to stop showing the popup.'))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 11
      ))
      ->setDefaultValue(1);

    $fields['cookies_expiration'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Cookies expiration'))
      ->setDescription(t('Time in minutes for expiration. 1d=1440min, 1week=10080, 1month=43200'))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'min' => 1,
        'weight' => 11
      ))
      ->setDefaultValue(10080); // 1week

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Active'))
      ->setDescription(t('This popup is published and active'))
      ->setDisplayOptions('form', array(
        'weight' => 12
      ))
      ->setDefaultValue(TRUE);


    $fields['visibility'] = BaseFieldDefinition::create('entity_content_visibility')
      ->setLabel(t('Visibility'))
      ->setDisplayOptions('form', array(
          'type' => 'entity_content_visibility',
          'weight' => 99,
        )
      );

    return $fields;
  }

}
