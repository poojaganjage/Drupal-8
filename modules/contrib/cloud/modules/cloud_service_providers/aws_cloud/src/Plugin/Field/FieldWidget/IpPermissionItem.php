<?php

namespace Drupal\aws_cloud\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'ip_permission_item' widget.
 *
 * @FieldWidget(
 *   id = "ip_permission_item",
 *   label = @Translation("AWS IP permission"),
 *   field_types = {
 *     "ip_permission"
 *   }
 * )
 */
class IpPermissionItem extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /* @var \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $parent */
    $parent = $items->getParent();
    $security_group = $parent->getValue();

    $protocols = [
      'tcp' => $this->t('TCP'),
      'udp' => $this->t('UDP'),
      'icmp' => $this->t('ICMP'),
    ];

    $source = [
      'ip4' => 'IP',
      'group' => 'Group',
    ];
    $field_name = $this->fieldDefinition->getName();

    // Add IPv6 protocols if the group is a vpc.
    if (!empty($security_group->getVpcId())) {
      $protocols = [
        '-1' => $this->t('All traffic'),
        'tcp' => $this->t('TCP'),
        'udp' => $this->t('UDP'),
        'icmp' => $this->t('ICMP'),
        'icmpv6' => $this->t('ICMPv6'),
      ];

      $source = [
        'ip4' => 'IP',
        'ip6' => 'IPv6',
        'group' => 'Group',
        'prefix' => 'Prefix List Id',
      ];
    }

    $element['ip_protocol'] = [
      '#type' => 'select',
      '#title' => $this->t('IP Protocol'),
      '#options' => $protocols,
      '#attributes' => [
        'class' => [
          'ip-protocol-select',
        ],
      ],
      '#default_value' => $items[$delta]->ip_protocol ?? 'tcp',
    ];

    $element['from_port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From Port'),
      '#size' => 5,
      '#default_value' => $items[$delta]->from_port ?? NULL,
      '#maxlength' => 5,
      '#placeholder' => 0,
    ];
    $element['to_port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('To Port'),
      '#size' => 5,
      '#default_value' => $items[$delta]->to_port ?? NULL,
      '#maxlength' => 5,
      '#placeholder' => 65535,
    ];
    $element['source'] = [
      '#type' => 'select',
      '#title' => $this->t('Source'),
      '#options' => $source,
      '#attributes' => [
        'class' => [
          'ip-permission-select',
          'ip-type-select',
        ],
      ],
      '#default_value' => $items[$delta]->source ?? NULL,
    ];
    $element['cidr_ip'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CIDR IP'),
      '#size' => 20,
      '#default_value' => $items[$delta]->cidr_ip ?? NULL,
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#placeholder' => '0.0.0.0/0',
      '#states' => [
        'visible' => [
          'select[name="' . $field_name . '[' . $delta . ']' . '[source]"]' => ['value' => 'ip4'],
        ],
      ],
    ];
    $element['cidr_ip_v6'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CIDR IPv6'),
      '#size' => 50,
      '#default_value' => $items[$delta]->cidr_ip_v6 ?? NULL,
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#states' => [
        'visible' => [
          'select[name="' . $field_name . '[' . $delta . ']' . '[source]"]' => ['value' => 'ip6'],
        ],
      ],
    ];
    if (!empty($security_group->getVpcId())) {
      $element['prefix_list_id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Prefix List ID'),
        '#size' => 20,
        '#default_value' => $items[$delta]->prefix_list_id ?? NULL,
        '#maxlength' => $this->getFieldSetting('max_length'),
        '#states' => [
          'visible' => [
            'select[name="' . $field_name . '[' . $delta . ']' . '[source]"]' => ['value' => 'prefix'],
          ],
        ],
      ];
    }
    $element['group_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Group ID'),
      '#default_value' => $items[$delta]->group_id ?? NULL,
      '#size' => 20,
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#states' => [
        'visible' => [
          'select[name="' . $field_name . '[' . $delta . ']' . '[source]"]' => ['value' => 'group'],
        ],
      ],
    ];
    if (isset($items[$delta]->group_name)) {
      $element['group_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Group Name'),
        '#default_value' => $items[$delta]->group_name ?? NULL,
        '#size' => 20,
        '#maxlength' => $this->getFieldSetting('max_length'),
        '#disabled' => TRUE,
        '#states' => [
          'visible' => [
            'select[name="' . $field_name . '[' . $delta . ']' . '[source]"]' => ['value' => 'group'],
          ],
        ],
      ];
    }
    $element['peering_status'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Peering Status'),
      '#default_value' => $items[$delta]->peering_status ?? NULL,
      '#size' => 20,
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#states' => [
        'visible' => [
          'select[name="' . $field_name . '[' . $delta . ']' . '[source]"]' => ['value' => 'group'],
        ],
      ],
    ];
    $element['user_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Group User ID'),
      '#default_value' => $items[$delta]->user_id ?? NULL,
      '#size' => 20,
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#states' => [
        'visible' => [
          'select[name="' . $field_name . '[' . $delta . ']' . '[source]"]' => ['value' => 'group'],
        ],
      ],
    ];
    $element['vpc_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('VPC ID'),
      '#default_value' => $items[$delta]->vpc_id ?? NULL,
      '#size' => 20,
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#states' => [
        'visible' => [
          'select[name="' . $field_name . '[' . $delta . ']' . '[source]"]' => ['value' => 'group'],
        ],
      ],
    ];
    $element['peering_connection_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Peering Connection ID'),
      '#default_value' => $items[$delta]->peering_connection_id ?? NULL,
      '#size' => 20,
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#states' => [
        'visible' => [
          'select[name="' . $field_name . '[' . $delta . ']' . '[source]"]' => ['value' => 'group'],
        ],
      ],
    ];
    return $element;
  }

}
