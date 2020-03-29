<?php

namespace Drupal\entity_content_visibility\Plugin\Field\FieldWidget;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FieldWidget(
 *   id = "entity_content_visibility",
 *   field_types = {
 *     "entity_content_visibility"
 *   },
 * )
 */
class EntityContentVisibilityWidget extends WidgetBase implements ContainerFactoryPluginInterface {
  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.condition'),
      $container->get('context.repository')
    );
  }

  /** @var ConditionManager */
  private $condition_manager;

  /** @var ContextRepositoryInterface  */
  private $context_repository;

  /**
   * @inheritDoc
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ConditionManager $condition_manager, ContextRepositoryInterface $context_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->condition_manager = $condition_manager;
    $this->context_repository = $context_repository;
  }

  /**
   * @inheritDoc
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = unserialize($items[$delta]->value);

    $element['visibility_tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Visibility'),
      '#parents' => ['visibility_tabs'],
    ];

    $contexts = $this->context_repository->getAvailableContexts();
    $form_state->setTemporaryValue('gathered_contexts', $contexts);

    foreach ($this->condition_manager->getDefinitionsForContexts($contexts) as $condition_id => $definition) {
      if ($condition_id === 'current_theme') {
        continue;
      }

      /** @var ConditionInterface $condition */
      $condition = $this->condition_manager->createInstance($condition_id);
      $condition_configuration = isset($value[$condition_id])? $value[$condition_id]: $condition->defaultConfiguration();
      $condition->setConfiguration($condition_configuration);

      $condition_form = $condition->buildConfigurationForm([], $form_state);
      $condition_form['#type'] = 'details';
      $condition_form['#title'] = $condition->getPluginDefinition()['label'];
      $condition_form['#group'] = 'visibility_tabs';

      $element[$condition_id] = $condition_form;
    }

    return $element;
  }

  /**
   * @inheritDoc
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      unset($value['_original_delta']);

      foreach ($value as $condition_id => $condition_configuration) {

        /** @var ConditionInterface $condition */
        $condition = $this->condition_manager->createInstance($condition_id);
        $condition->setConfiguration($condition_configuration);

        $field_name = $this->fieldDefinition->getName();
        $subform = $form[$field_name]['widget'][$delta][$condition_id];
        $subform_state = SubformState::createForSubform($subform, $form, $form_state);
        $condition->submitConfigurationForm($subform, $subform_state);

        $comparable_configuration = $condition->getConfiguration();
        unset($comparable_configuration['id']);
        unset($comparable_configuration['context_mapping']);
        if ($comparable_configuration != $condition->defaultConfiguration()) {
          $value[$condition_id] = $condition->getConfiguration();
        } else {
          unset($value[$condition_id]);
        }
      }


      if (!empty($value)) {
        $values[$delta] = serialize($value);
      } else {
        unset($values[$delta]);
      }
    }

    return $values;
  }


}
