<?php

namespace Drupal\cloud\Service;

use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\DependencyInjection\ClassResolver;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\cloud\Service\Util\EntityLinkHtmlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Entity link element renderer service.
 */
class EntityLinkRenderer implements EntityLinkRendererInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * The class resolver service.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolver
   */
  private $classResolver;

  /**
   * An entity type manager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Request service.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  private $request;

  /**
   * Constructs a new EntityLinkRenderer object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\DependencyInjection\ClassResolver $class_resolver
   *   The class resolver service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager instance.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    ClassResolver $class_resolver,
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $request_stack) {

    $this->routeMatch = $route_match;
    $this->classResolver = $class_resolver;
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * Render entity link for view.
   *
   * @param string $value
   *   The value of entity.
   * @param string $target_type
   *   The type of target entity.
   * @param string $field_name
   *   The field name of target entity.
   * @param array $query
   *   The query parameters.
   * @param string $alt_text
   *   Optional alternative text to display.
   * @param string $html_generator_class
   *   Html generator class.
   * @param string $cloud_context
   *   The cloud context.
   * @param string $extra_route_parameter
   *   The extra route parameter.
   * @param string $extra_route_parameter_entity_method
   *   The entity method for extra route parameter.
   *
   * @return array
   *   The build array of entity link element for views.
   */
  public function renderViewElement(
    $value,
    $target_type,
    $field_name,
    array $query = [],
    $alt_text = '',
    $html_generator_class = '',
    $cloud_context = '',
    $extra_route_parameter = '',
    $extra_route_parameter_entity_method = ''
  ) : array {

    if (empty($cloud_context)) {
      $cloud_context = $this->routeMatch->getParameter('cloud_context');
    }

    if ($this->routeMatch->getRouteName() === 'views.ajax') {
      // When the request is from ajax, get the cloud context from referer.
      global $base_url;
      // Get the referer url.
      $referer = $this->request->headers->get('referer');
      if (!empty($referer)) {
        // Get the alias or the referer.
        $alias = substr($referer, strlen($base_url));
        $url = Url::fromUri("internal:$alias");
        $params = $url->getRouteParameters();
        $cloud_context = !empty($params['cloud_context']) ? $params['cloud_context'] : NULL;
      }
    }

    if (is_array($value)) {
      $values = $value;
    }
    else {
      $values = [$value];
    }

    foreach ($values ?: [] as $item) {
      $entity_ids = $this->entityTypeManager
        ->getStorage($target_type)
        ->getQuery()
        ->condition('cloud_context', $cloud_context)
        ->condition($field_name, $item)
        ->execute();

      if (empty($entity_ids)) {
        $htmls[] = $item;
      }
      else {
        $entity = $this->entityTypeManager
          ->getStorage($target_type)->load(reset($entity_ids));

        $name = !empty($entity) && $entity->hasField('name') && $entity->getName() !== $item
          ? $entity->getName()
          : '';

        if (empty($html_generator_class)) {
          $html_generator_class = EntityLinkHtmlGenerator::class;
        }

        $generator = $this->classResolver->getInstanceFromDefinition($html_generator_class);
        $route_parameters = [
          'cloud_context' => $cloud_context,
          $target_type => array_values($entity_ids)[0],
        ];
        if (!empty($extra_route_parameter) && !empty($extra_route_parameter_entity_method)) {
          if (method_exists($entity, $extra_route_parameter_entity_method)) {
            $route_parameters[$extra_route_parameter] = $entity->$extra_route_parameter_entity_method();
          }
        }
        $html = $generator->generate(
          Url::fromRoute(
            "entity.$target_type.canonical", $route_parameters, [
              'query' => $query,
            ]
          ),
          $item,
          $name,
          $alt_text
        );
        $htmls[] = $html;
      }
    }

    return [
      '#markup' => implode(', ', $htmls),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function renderFormElements(
    $value,
    $target_type,
    $field_name,
    array $options,
    $alt_text = '',
    $html_generator_class = '') {

    return $this->renderViewElement(
      $value,
      $target_type,
      $field_name,
      $options,
      $alt_text,
      $html_generator_class)
      + $options
      + ['#type' => 'item'];
  }

}
