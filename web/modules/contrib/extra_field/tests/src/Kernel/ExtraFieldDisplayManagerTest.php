<?php

namespace Drupal\Tests\extra_field\Kernel;

use Drupal\KernelTests\KernelTestBase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @coversDefaultClass \Drupal\extra_field\Plugin\ExtraFieldDisplayManager
 *
 * @group extra_field
 */
class ExtraFieldDisplayManagerTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['extra_field'];

  /**
   * The plugin manager under test.
   *
   * @var \Drupal\extra_field\Plugin\ExtraFieldDisplayManager|PHPUnit_Framework_MockObject_MockObject
   */
  protected $displayManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();

    $namespaces = $this->container->get('container.namespaces');
    $cache_backend = $this->container->get('cache.discovery');
    $module_handler = $this->container->get('module_handler');
    $entity_type_manager = $this->container->get('entity_type.manager');
    $this->displayManager = $this->getMock('Drupal\extra_field\Plugin\ExtraFieldDisplayManager', [
      'getDefinitions',
      'allEntityBundles',
    ], [$namespaces, $cache_backend, $module_handler, $entity_type_manager]);
  }

  /**
   * Prepare ::getDefinitions to return the right values.
   *
   * @param array $definitions
   *   The plugin definitions to return.
   */
  protected function prepareDefinitions(array $definitions) {

    $this->displayManager->expects($this->any())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));
  }

  /**
   * Prepare ::allEntityBundles to return the right values.
   *
   * @param array $bundlesMap
   *   Array of bundle names.
   */
  protected function prepareEntityBundles(array $bundlesMap) {

    $this->displayManager->expects($this->any())
      ->method('allEntityBundles')
      ->will($this->returnValueMap($bundlesMap));
  }

  /**
   * @covers ::fieldInfo
   *
   * @dataProvider fieldInfoProvider
   *
   * @param array $definitions
   *   Plugin definitions as returned by ::getDefinitions.
   * @param array $bundles
   *   Entity bundles as returned by ::allEntityBundles.
   * @param array $results
   *   Field info as returned by ::fieldInfo.
   */
  public function testFieldInfo(array $definitions, array $bundles, array $results) {

    $this->prepareDefinitions($definitions);
    $this->prepareEntityBundles($bundles);

    $this->assertSame(count($this->displayManager->getDefinitions()), count($definitions));
    $this->assertSame($this->displayManager->fieldInfo(), $results);
  }

  public function fieldInfoProvider() {

    $info[] = [
      // Definitions.
      [
        'test' => [
          'id' => 'test',
          'bundles' => ['node.article'],
          'label' => 'test display node article',
          'weight' => 0,
          'visible' => FALSE,
        ],
      ],
      // Bundles.
      [],
      // Results.
      [
        'node' => [
          'article' => [
            'display' => [
              'extra_field_test' => [
                'label' => 'test display node article',
                'weight' => 0,
                'visible' => FALSE,
              ],
            ],
          ],
        ],
      ],
    ];

    $info[] = [
      // Definitions.
      [
        'test' => [
          'id' => 'test',
          'bundles' => ['node.article'],
          'label' => 'test display node article',
          'weight' => 88,
          'visible' => TRUE,
        ],
      ],
      // Bundles.
      [],
      // Results.
      [
        'node' => [
          'article' => [
            'display' => [
              'extra_field_test' => [
                'label' => 'test display node article',
                'weight' => 88,
                'visible' => TRUE,
              ],
            ],
          ],
        ],
      ],
    ];

    $info[] = [
      // Definitions.
      [
        'test1' => [
          'id' => 'test1',
          'bundles' => [
            'node.*',
            'come.*',
          ],
          'label' => 'test display 1',
          'weight' => 0,
          'visible' => FALSE,
        ],
        'test2' => [
          'id' => 'test2',
          'bundles' => [
            'node.article',
          ],
          'label' => 'test display 2',
          'weight' => 2,
          'visible' => TRUE,
        ],
      ],
      // Bundles.
      [
        ['node', ['article', 'story', 'blog']],
        ['come', ['rain', 'shine']],
      ],
      // Results.
      [
        'node' => [
          'article' => [
            'display' => [
              'extra_field_test1' => [
                'label' => 'test display 1',
                'weight' => 0,
                'visible' => FALSE,
              ],
              'extra_field_test2' => [
                'label' => 'test display 2',
                'weight' => 2,
                'visible' => TRUE,
              ],
            ],
          ],
          'story' => [
            'display' => [
              'extra_field_test1' => [
                'label' => 'test display 1',
                'weight' => 0,
                'visible' => FALSE,
              ],
            ],
          ],
          'blog' => [
            'display' => [
              'extra_field_test1' => [
                'label' => 'test display 1',
                'weight' => 0,
                'visible' => FALSE,
              ],
            ],
          ],
        ],
        'come' => [
          'rain' => [
            'display' => [
              'extra_field_test1' => [
                'label' => 'test display 1',
                'weight' => 0,
                'visible' => FALSE,
              ],
            ],
          ],
          'shine' => [
            'display' => [
              'extra_field_test1' => [
                'label' => 'test display 1',
                'weight' => 0,
                'visible' => FALSE,
              ],
            ],
          ],
        ],
      ],
    ];

    return $info;
  }

}
