<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\cloud\Form\CloudContentForm;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Service\EntityLinkRendererInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Plugin\CachedDiscoveryClearerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Render\Renderer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The base form class for the AWS Cloud content form.
 */
class AwsCloudContentForm extends CloudContentForm {

  /**
   * The AWS Cloud EC2 Service.
   *
   * @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface
   */
  protected $ec2Service;

  /**
   * Entity link renderer object.
   *
   * @var \Drupal\cloud\Service\EntityLinkRendererInterface
   */
  protected $entityLinkRenderer;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * A plugin cache clear instance.
   *
   * @var \Drupal\Core\Plugin\CachedDiscoveryClearerInterface
   */
  protected $pluginCacheClearer;

  /**
   * A cache backend interface instance.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheRender;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * General renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * AwsCloudContentForm constructor.
   *
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   The AWS Cloud or OpenStack EC2 Service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The Messenger Service.
   * @param \Drupal\cloud\Service\EntityLinkRendererInterface $entity_link_renderer
   *   The entity link render service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The Entity Type Manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheRender
   *   A cache backend interface instance.
   * @param \Drupal\Core\Plugin\CachedDiscoveryClearerInterface $plugin_cache_clearer
   *   A plugin cache clear instance.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The general renderer.
   */
  public function __construct(Ec2ServiceInterface $ec2_service,
                              EntityRepositoryInterface $entity_repository,
                              EntityTypeBundleInfoInterface $entity_type_bundle_info,
                              TimeInterface $time,
                              Messenger $messenger,
                              EntityLinkRendererInterface $entity_link_renderer,
                              EntityTypeManager $entity_type_manager,
                              CacheBackendInterface $cacheRender,
                              CachedDiscoveryClearerInterface $plugin_cache_clearer,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              AccountInterface $current_user,
                              RouteMatchInterface $route_match,
                              DateFormatterInterface $date_formatter,
                              Renderer $renderer) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time, $messenger);
    $this->ec2Service = $ec2_service;
    $this->entityLinkRenderer = $entity_link_renderer;
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheRender = $cacheRender;
    $this->pluginCacheClearer = $plugin_cache_clearer;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->currentUser = $current_user;
    $this->routeMatch = $route_match;
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('aws_cloud.ec2'),
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('messenger'),
      $container->get('entity.link_renderer'),
      $container->get('entity_type.manager'),
      $container->get('cache.render'),
      $container->get('plugin.cache_clearer'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Copy values of the form elements whose type are item to entity.
    // If not, the properties corresponding to the form elements
    // will be saved as NULL.
    $this->copyFormItemValues($form);

    $this->trimTextfields($form, $form_state);

    parent::save($form, $form_state);
  }

  /**
   * Copy values from #type=item elements to its original element type.
   *
   * @param array $form
   *   The form array.
   */
  protected function copyFormItemValues(array $form) {
    $original_entity = $this->entityTypeManager
      ->getStorage($this->entity->getEntityTypeId())
      ->load($this->entity->id());

    $item_field_names = [];
    foreach ($form ?: [] as $name => $item) {
      if (!is_array($item)) {
        continue;
      }

      if (isset($item['#type'])
        && $item['#type'] === 'item'
        && (!isset($item['#not_field']) || $item['#not_field'] === FALSE)
      ) {
        $item_field_names[] = $name;
      }

      if (isset($item['#type']) && $item['#type'] === 'details') {
        foreach ($item ?: [] as $sub_item_name => $sub_item) {
          if (is_array($sub_item)
            && isset($sub_item['#type'])
            && $sub_item['#type'] === 'item'
            && (!isset($sub_item['#not_field']) || $sub_item['#not_field'] === FALSE)
          ) {
            $item_field_names[] = $sub_item_name;
          }
        }
      }
    }

    foreach ($item_field_names ?: [] as $item_field_name) {
      // Support multi-valued item fields.
      $values = !empty($original_entity) ? $original_entity->get($item_field_name)->getValue() : [];
      if (!empty($values) && count($values) > 1) {
        $item_field_values = [];
        foreach ($values ?: [] as $value) {
          $item_field_values[] = $value['value'];
        }
        $this->entity->set($item_field_name, $item_field_values);
      }
      else {
        $item_field_value = !empty($original_entity) ? $original_entity->get($item_field_name)->value : [];
        if (!empty($item_field_value)) {
          $this->entity->set($item_field_name, $item_field_value);
        }
      }
    }
  }

  /**
   * Trim white spaces in the values of textfields.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  protected function trimTextfields(array $form, FormStateInterface $form_state) {
    $field_names = [];
    foreach ($form ?: [] as $name => $item) {
      if (!is_array($item)) {
        continue;
      }

      if (isset($item['#type'])
        && $item['#type'] === 'textfield'
      ) {
        $field_names[] = $name;
      }

      if (isset($item['#type']) && $item['#type'] === 'details') {
        foreach ($item ?: [] as $sub_item_name => $sub_item) {
          if (is_array($sub_item)
            && isset($sub_item['#type'])
            && $sub_item['#type'] === 'textfield'
          ) {
            $field_names[] = $sub_item_name;
          }
        }
      }
    }

    foreach ($field_names ?: [] as $field_name) {
      $value = $form_state->getValue($field_name);
      if ($value === NULL) {
        continue;
      }

      $value = trim($value);
      $form_state->setValue($field_name, $value);
      $this->entity->set($field_name, $value);
    }
  }

  /**
   * Add the build array of fieldset others.
   *
   * @param array &$form
   *   The form array.
   * @param int $weight
   *   The weight of the fieldset.  The parameter's default value is 1
   *   to put the "Others" fieldset in between the main items and button(s)
   *   (e.g. "Save") if the parameter is omitted since 0 is the default value
   *   of the #weight attribute.
   * @param string $cloud_context
   *   The cloud context.
   */
  protected function addOthersFieldset(array &$form, $weight = 1, $cloud_context = '') {

    parent::addOthersFieldset($form, $weight, $cloud_context);
    unset($form['uid']);
  }

  /**
   * Helper function to get title translatable string of a item.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $t
   *   The translatable string.
   *
   * @return string
   *   The string of title.
   */
  protected function getItemTitle(TranslatableMarkup $t) {
    return $t->render() . ': ';
  }

  /**
   * Set the uid tag for different entities.
   *
   * @param string $resource_id
   *   The resource ID.  For example, instance_id, volume_id.
   * @param string $key
   *   The key to store this value.
   * @param string $uid
   *   The drupal user ID.
   */
  protected function setUidInAws($resource_id, $key, $uid) {
    $this->setTagsInAws($resource_id, [$key => $uid]);
  }

  /**
   * Set the tags for the entity.
   *
   * @param string $resource_id
   *   The resource ID.  For example, instance_id, volume_id.
   * @param array $tag_map
   *   The map of tags.
   */
  protected function setTagsInAws($resource_id, array $tag_map) {
    $this->ec2Service->setCloudContext($this->entity->getCloudContext());
    $tags = [];
    foreach ($tag_map ?: [] as $key => $value) {
      $tags[] = [
        'Key' => $key,
        'Value' => $value,
      ];
    }

    // Create Tags with different parameters for AWS and OpenStack.
    if (preg_match('[^aws_cloud]', $this->entity->getEntityTypeId()) === 1) {
      $this->ec2Service->createTags([
        'Resources' => [$resource_id],
        'Tags' => $tags,
      ]);
    }
    else {
      $this->ec2Service->createTags([
        'Resources' => [$resource_id],
        'Tags' => [
          ['Key' => 'Name', 'Value' => $this->entity->getName()],
        ],
      ]);
    }
  }

}
