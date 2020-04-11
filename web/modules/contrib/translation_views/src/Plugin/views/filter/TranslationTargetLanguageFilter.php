<?php

namespace Drupal\translation_views\Plugin\views\filter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\translation_views\TranslationViewsTargetLanguage as TargetLanguage;
use Drupal\views\Plugin\views\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides filtering by translation target language.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("translation_views_target_language")
 */
class TranslationTargetLanguageFilter extends FilterPluginBase implements ContainerFactoryPluginInterface {
  use TargetLanguage;

  /**
   * Flag about module 'translators_content' existence.
   *
   * @var bool
   */
  protected $translators_content = FALSE;
  /**
   * Translators skills service.
   *
   * @var \Drupal\translators\Services\TranslatorsSkills|null
   */
  protected $translatorSkills = NULL;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('language_manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, ModuleHandlerInterface $handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->translators_content = $handler->moduleExists('translators_content');
    if ($this->translators_content && \Drupal::hasService('translators.skills')) {
      $this->translatorSkills = \Drupal::service('translators.skills');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);

    $form['expose']['multiple'] = [
      '#type' => 'hidden',
      '#value' => FALSE,
    ];
    $form['expose']['required'] = [
      '#type' => 'hidden',
      '#value' => TRUE,
    ];

    if ($this->translators_content) {
      // We need to force this option to allow users to use only the languages,
      // specified as the user's translation skills.
      $form['expose']['reduce']['#default_value'] = TRUE;
      $form['expose']['reduce']['#disabled'] = TRUE;
    }

    $form['expose']['identifier'] = [
      '#type' => 'hidden',
      '#value' => static::$targetExposedKey,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['expose']['contains']['label'] = [
      'default' => $this->t('Target language'),
    ];

    $options['expose']['contains']['identifier'] = [
      'default' => static::$targetExposedKey,
    ];

    $options['value']['default'] = '';
    $options['remove']['default'] = TRUE;
    $options['exposed']['default'] = TRUE;

    if ($this->translators_content) {
      $options['limit'] = ['default' => FALSE];
      $options['column'] = ['default' => ['source' => '', 'target' => 'target']];
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    parent::buildExposedForm($form, $form_state);
    $field =& $form[$this->field];
    // Avoide validation error when list of allowed languages changes.
    if (isset($field['#value']) && !isset($field['#options'][$field['#value']])) {
      $field['#value'] = $field['#default_value'] = '';
    }
    // Show empty registered skills message inside this window.
    if ($this->translators_content
      && $this->options['limit']
      && empty($this->translatorSkills->getAllLangcodes())) {
        $field['#options'] = ['All' => $this->t('- Any -')];
        $field['#value'] = $field['#default_value'] = 'All';
        $this->translatorSkills->showMissingTranslationSkillsWarning();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['remove'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove rows where language equals target language.'),
      '#default_value' => $this->options['remove'],
      '#weight' => -50,
    ];
    // Build values list independently in order to see all the options,
    $form['value']['#options'] = $this->listLanguages(
      LanguageInterface::STATE_CONFIGURABLE
      | LanguageInterface::STATE_SITE_DEFAULT
      | PluginBase::INCLUDE_NEGOTIATED
    );
    if ($this->translators_content) {
      $end = $form['clear_markup_end'];
      unset($form['clear_markup_end']);
      $form['limit'] = [
        '#type'          => 'checkbox',
        '#title'         => $this->t('Limit target languages by translation skills'),
        '#required'      => FALSE,
        '#default_value' => $this->options['limit'],
      ];
      $form['column'] = [
        '#type'          => 'checkboxes',
        '#options'       => [
          'source' => $this->t('Source languages'),
          'target'   => $this->t('Target languages'),
        ],
        '#title'         => $this->t('Translation skill'),
        '#required'      => TRUE,
        '#default_value' => $this->options['column'],
        '#states' => [
          'visible' => [
            'input[name="options[limit]"]' => ['checked' => TRUE],
          ],
        ],
      ];
      $form['clear_markup_end'] = $end;
      $form['value']['#prefix'] = '<div class="views-group-box views-right-60">';
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    if (!empty($this->options['exposed'])) {
      $language_options = $this->buildLanguageOptions();
      $identifier = $this->options['expose']['identifier'];
      $user_input = $form_state->getUserInput();
      $target_langcode = isset($user_input[$identifier]) ? $user_input[$identifier] : $this->value;
      $valid_langcode = $this->getValidLangcode($target_langcode, $language_options);
      $this->setExposedValue($identifier, $valid_langcode, $form_state);
    }

    $this->always_required = TRUE;

    $form['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Default target language'),
      '#options' => $this->buildLanguageOptions(),
      '#multiple' => FALSE,
      '#required' => TRUE,
      '#default_value' => $this->value,
    ];

    $form['expose']['identifier'] = [
      '#type' => 'hidden',
      '#value' => static::$targetExposedKey,
    ];
  }

  /**
   * Provide options for langcode dropdown.
   *
   * @return array
   *   Available options based on configurable languages.
   */
  protected function buildLanguageOptions() {
    $options = [];
    if ($this->translators_content && $this->options['limit']) {
      $translators_languages = $this->translatorSkills->getTranslationSkills();
      // Handle column options.
      foreach ($this->options['column'] as $name => $column) {
        if (!empty($column)) {
          foreach ($translators_languages as $langs) {
            $this->processColumnOption($langs, $name, $options);
          }
        }
      }
    }
    else {
      $options = $this->listLanguages(LanguageInterface::STATE_CONFIGURABLE);
    }
    return $options;
  }

  /**
   * Get a valid langcode from language options.
   *
   * @param string $target_langcode
   *   Target language identifier.
   * @param array $language_options
   *   Available language options.
   *
   * @return string|null
   *   A valid langcode, or NULL if no valid langcodes exist.
   */
  protected function getValidLangcode($target_langcode, array $language_options) {
    if ($target_langcode === '***LANGUAGE_site_default***') {
      $target_langcode = $this->languageManager->getDefaultLanguage()->getId();
    }
    elseif ($target_langcode === '***LANGUAGE_language_interface***') {
      $target_langcode = $this->languageManager->getCurrentLanguage()->getId();
    }
    if (!isset($language_options[$target_langcode]) && !empty($language_options)) {
      return array_keys($language_options)[0];
    }
    elseif (empty($language_options)) {
      return NULL;
    }
    return $target_langcode;
  }

  /**
   * Process column options.
   *
   * @param array $languages
   *   Languages array.
   * @param string $column
   *   Column name.
   */
  protected function processColumnOption(array $languages, $column, &$options) {
    $key = "language_$column";
    if (isset($languages[$key])) {
      $key = $languages[$key];
      $options[$key] = $this->languageManager
        ->getLanguage($key)
        ->getName();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function listLanguages($flags = LanguageInterface::STATE_ALL, array $current_values = NULL) {
    return array_map(function ($language) {
      return (string) $language;
    }, parent::listLanguages($flags, $current_values));
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if ($this->options['remove']) {
      $this->query->addWhere(
        $this->options['group'],
        $this->view->storage->get('base_table') . '.langcode',
        '***TRANSLATION_VIEWS_TARGET_LANG***',
        '<>'
      );
    }

    if (isset($this->options['limit']) && $this->options['limit']) {
      $table_alias = $this->ensureMyTable();

      $this->query->addWhereExpression(
        $this->options['group'],
        "FIND_IN_SET(:langcode_limit, $table_alias.langs) >= '0' AND :langcode_limit != ''",
        [
          ':langcode_limit' => '***TRANSLATION_VIEWS_TARGET_LIMIT_LANG***',
        ]
      );
    }
  }

  /**
   * Special setter for exposed value in views.
   */
  protected function setExposedValue($identifier, $value, FormStateInterface $form_state) {
    $user_input = $form_state->getUserInput();
    $user_input[$identifier] = $value;

    $form_state->setUserInput($user_input);
    $this->view->setExposedInput($user_input);
  }

}
