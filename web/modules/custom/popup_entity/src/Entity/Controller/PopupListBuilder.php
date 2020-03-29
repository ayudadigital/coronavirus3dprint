<?php

namespace Drupal\popup_entity\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for popup_entity entity.
 *
 * @ingroup popup_entity
 */
class PopupListBuilder extends EntityListBuilder {

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('url_generator')
    );
  }

  /**
   * Constructs a new PopupListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the popup list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['status'] = $this->t('Active/Published');
    $header['language'] = $this->t('Original Language');
    $header['translations'] = $this->t('Translations');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\popup_entity\Entity\Popup */
    $row['id'] = $entity->id();
    $row['title'] = $entity->get('title')->value;
    $row['status'] = !empty($entity->get('status')->value) ?
      $this->t('Yes') : $this->t('No');

    $row['language'] = $entity->language()->getName();

    $translations = $entity->getTranslationLanguages(FALSE);
    $lang_translations = array();
    foreach ($translations as $key => $translation){
      /* @var $translation \Drupal\Core\Language\Language */
      $lang_translations[] = $translation->getName();
    }
    $row['translations'] = implode(', ', $lang_translations);

    return $row + parent::buildRow($entity);
  }

}
