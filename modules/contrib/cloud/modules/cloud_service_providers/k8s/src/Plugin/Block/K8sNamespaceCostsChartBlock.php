<?php

namespace Drupal\k8s\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\k8s\Controller\K8sNamespaceCostsChartController;

/**
 * Provides a block of the costs chart.
 *
 * @Block(
 *   id = "k8s_namespace_costs_chart",
 *   admin_label = @Translation("K8s Namespace Costs Chart"),
 *   category = @Translation("K8s")
 * )
 */
class K8sNamespaceCostsChartBlock extends K8sBaseBlock {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'display_view_k8s_namespace_list_only' => 1,
      'aws_cloud_chart_period' => 14,
      'aws_cloud_chart_ec2_cost_type' => 'ri_one_year',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $this->messenger->addWarning($this->t('The graph results may vary depending on the number of the setting of <a href=":url">Database log messages to keep</a>.', [':url' => $this->urlGenerator->generate('system.logging_settings')]));
    $controller = $this->classResolver->getInstanceFromDefinition(K8sNamespaceCostsChartController::class);

    $form['display_view_k8s_namespace_list_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display this block in K8s Namespace list page and K8s Project Content page only'),
      '#default_value' => $this->configuration['display_view_k8s_namespace_list_only'],
    ];

    $form['aws_cloud_chart_period'] = [
      '#type' => 'select',
      '#title' => $this->t('AWS Cloud Chart Period'),
      '#options' => $controller->getEc2ChartPeriod(),
      '#default_value' => $this->configuration['aws_cloud_chart_period'],
    ];

    $form['aws_cloud_chart_ec2_cost_type'] = [
      '#type' => 'select',
      '#title' => $this->t('AWS Cloud Chart EC2 Cost Type'),
      '#options' => $controller->getEc2CostTypes(),
      '#default_value' => $this->configuration['aws_cloud_chart_ec2_cost_type'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['display_view_k8s_namespace_list_only']
      = $form_state->getValue('display_view_k8s_namespace_list_only');
    $this->configuration['aws_cloud_chart_period']
      = $form_state->getValue('aws_cloud_chart_period');
    $this->configuration['aws_cloud_chart_ec2_cost_type']
      = $form_state->getValue('aws_cloud_chart_ec2_cost_type');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $route_name = $this->routeMatch->getRouteName();
    $cloud_context = $this->routeMatch->getParameter('cloud_context');
    $cloud_project = $this->routeMatch->getParameter('cloud_project');

    // If display_view_k8s_namespace_list_only is checked, do nothing.
    if ($route_name !== 'view.k8s_namespace.list'
      && $route_name !== 'entity.cloud_project.canonical'
      && $this->configuration['display_view_k8s_namespace_list_only']) {
      return [];
    }

    if ($this->isK8sEmpty() === TRUE) {
      return [];
    }

    $k8s_namespace_costs_cost_types_json_url = Url::fromRoute(
      'entity.k8s_namespace.cost_types',
      [
        'json' => 'json',
      ]
    )->toString();

    $k8s_namespace_costs_chart_periods_json_url = Url::fromRoute(
      'entity.k8s_namespace.all_chart_periods',
      [
        'json' => 'json',
      ]
    )->toString();

    // Use a different chart_periods endpoint
    // if cloud_context or cloud_project is present.
    if (!empty($cloud_project)) {
      $k8s_namespace_costs_chart_periods_json_url = Url::fromRoute(
      'entity.k8s_namespace.cloud_project.chart_periods',
        [
          'cloud_context' => $cloud_context,
          'cloud_project' => $cloud_project->id(),
          'json' => 'json',
        ]
      )->toString();
    }
    elseif (!empty($cloud_context)) {
      $k8s_namespace_costs_chart_periods_json_url = Url::fromRoute(
        'entity.k8s_namespace.chart_periods',
        [
          'cloud_context' => $cloud_context,
          'json' => 'json',
        ]
      )->toString();
    }

    $build = [];
    $fieldset_defs = [
      [
        'name' => 'k8s_namespace_costs',
        'title' => t('Namespace Costs Chart'),
        'open' => TRUE,
        'fields' => [
          'k8s_namespace_costs_chart',
        ],
      ],
    ];

    if (!empty($cloud_project)) {
      $json_url = Url::fromRoute(
      'entity.k8s_namespace.cloud_project.costs',
        [
          'cloud_context' => $cloud_context,
          'cloud_project' => $cloud_project->id(),
        ]
      )->toString();
    }
    elseif (!empty($cloud_context)) {
      if ($route_name === 'entity.k8s_namespace.canonical') {
        $k8s_namespace = $this->routeMatch->getParameter('k8s_namespace');
        $json_url = Url::fromRoute(
          'entity.k8s_namespace.cost',
          [
            'cloud_context' => $cloud_context,
            'k8s_namespace' => $k8s_namespace->id(),
          ]
        )->toString();
      }
      else {
        $json_url = Url::fromRoute(
          'entity.k8s_namespace.costs',
          [
            'cloud_context' => $cloud_context,
          ]
        )->toString();
      }
    }
    else {
      $json_url = Url::fromRoute('entity.k8s_namespace.all_costs')->toString();
    }

    $build = [];

    $build['k8s_namespace_costs_chart'] = [
      '#markup' => '<div id="k8s_namespace_costs_chart"></div>',
      '#attached' => [
        'library' => [
          'k8s/k8s_namespace_costs_chart',
        ],
        'drupalSettings' => [
          'k8s' => [
            'k8s_namespace_costs_chart_json_url' => $json_url,
            'k8s_namespace_costs_cost_types_json_url' => $k8s_namespace_costs_cost_types_json_url,
            'k8s_namespace_costs_chart_periods_json_url' => $k8s_namespace_costs_chart_periods_json_url,
            'k8s_namespace_costs_chart_ec2_cost_type' => $this->configuration['aws_cloud_chart_ec2_cost_type'],
            'k8s_namespace_costs_chart_period' => $this->configuration['aws_cloud_chart_period'],
          ],
        ],
      ],
    ];

    $this->cloudService->reorderForm($build, $fieldset_defs);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    $this->killSwitch->trigger();
    // BigPipe/#cache/max-age is breaking my block javascript
    // https://www.drupal.org/forum/support/module-development-and-code-questions/2016-07-17/bigpipecachemax-age-is-breaking-my
    // "a slight delay of a second or two before the charts are built.
    // That seems to work, though it is a janky solution.".
    return $this->moduleHandler->moduleExists('big_pipe') ? 1 : 0;
  }

}
