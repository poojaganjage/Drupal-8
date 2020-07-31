<?php

namespace Drupal\aws_cloud\Entity\Ec2;

use Drupal\cloud\Entity\CloudContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the KeyPair entity.
 *
 * @ingroup aws_cloud
 *
 * @ContentEntityType(
 *   id = "aws_cloud_key_pair",
 *   id_plural = "aws_cloud_key_pairs",
 *   label = @Translation("Key Pair"),
 *   label_collection = @Translation("Key Pairs"),
 *   label_singular = @Translation("Key Pair"),
 *   label_plural = @Translation("Key Pairs"),
 *   handlers = {
 *     "view_builder" = "Drupal\aws_cloud\Entity\Ec2\KeyPairViewBuilder",
 *     "list_builder" = "Drupal\cloud\Controller\CloudContentListBuilder",
 *     "views_data"   = "Drupal\aws_cloud\Entity\Ec2\KeyPairViewsData",
 *     "form" = {
 *       "default"                 = "Drupal\aws_cloud\Form\Ec2\KeyPairEditForm",
 *       "add"                     = "Drupal\aws_cloud\Form\Ec2\KeyPairCreateForm",
 *       "edit"                    = "Drupal\aws_cloud\Form\Ec2\KeyPairEditForm",
 *       "import"                  = "Drupal\aws_cloud\Form\Ec2\KeyPairImportForm",
 *       "delete"                  = "Drupal\aws_cloud\Form\Ec2\KeyPairDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\aws_cloud\Form\Ec2\KeyPairDeleteMultipleForm",
 *     },
 *     "access"       = "Drupal\aws_cloud\Controller\Ec2\KeyPairAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "aws_cloud_key_pair",
 *   admin_permission = "administer aws cloud key pair",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id"    = "id",
 *     "label" = "key_pair_name",
 *     "uuid"  = "uuid",
 *   },
 *   links = {
 *     "canonical"            = "/clouds/aws_cloud/{cloud_context}/key_pair/{aws_cloud_key_pair}",
 *     "edit-form"            = "/clouds/aws_cloud/{cloud_context}/key_pair/{aws_cloud_key_pair}/edit",
 *     "delete-form"          = "/clouds/aws_cloud/{cloud_context}/key_pair/{aws_cloud_key_pair}/delete",
 *     "collection"           = "/clouds/aws_cloud/{cloud_context}/key_pair",
 *     "delete-multiple-form" = "/clouds/aws_cloud/{cloud_context}/key_pair/delete_multiple",
 *   },
 *   field_ui_base_route = "aws_cloud_key_pair.settings"
 * )
 */
class KeyPair extends CloudContentEntityBase implements KeyPairInterface {

  /**
   * {@inheritdoc}
   */
  public function getKeyPairName() {
    return $this->get('key_pair_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getKeyFingerprint() {
    return $this->get('key_fingerprint')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setKeyFingerprint($key_fingerprint = '') {
    return $this->set('key_fingerprint', $key_fingerprint);
  }

  /**
   * {@inheritdoc}
   */
  public function getKeyMaterial() {
    return $this->get('key_material')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setKeyMaterial($key_material = '') {
    return $this->set('key_material', $key_material);
  }

  /**
   * {@inheritdoc}
   */
  public function getRefreshed() {
    return $this->get('refreshed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRefreshed($time) {
    return $this->set('refreshed', $time);
  }

  /**
   * Helper function that returns the file location of the key file.
   *
   * @return bool|string
   *   The key file location.
   */
  public function getKeyFileLocation() {
    $file = FALSE;
    // See also: https://www.drupal.org/node/2913224.
    $tmp_dir = \Drupal::service('file_system')->getTempDirectory();
    if (file_exists("$tmp_dir/{$this->getKeyPairName()}.pem")) {
      $file = "$tmp_dir/{$this->getKeyPairName()}.pem";
    }
    return $file;
  }

  /**
   * Helper function that returns the file location.
   *
   * The file location starts with stream wrapper URI.
   *
   * @return string
   *   The key file name.
   */
  public function getKeyFileName() {
    return 'temporary://' . $this->getKeyPairName() . '.pem';
  }

  /**
   * Helper function to save private key to temporary file system.
   *
   * @param string $key
   *   String of the private key.
   */
  public function saveKeyFile($key) {
    if (!empty($key)) {
      // See also: https://www.drupal.org/node/2913224.
      \Drupal::service('file_system')->saveData($key, $this->getKeyFileName());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Ec2ServiceKeyPair entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Ec2ServiceKeyPair entity.'))
      ->setReadOnly(TRUE);

    $fields['cloud_context'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('Cloud Service Provider ID'))
      ->setDescription(t('A unique ID for the cloud service provider.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['key_pair_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Key Pair Name'))
      ->setDescription(t('The user-supplied key pair name, which is used to connect to an instance.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['key_fingerprint'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Fingerprint'))
      ->setDescription(t('The unique fingerprint of the key pair, which can be used to confirm its authenticity.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setReadOnly(TRUE);

    $fields['key_material'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Key Material'))
      ->setDescription(t('The key pair material (a private key)'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 5120,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
        'weight' => -5,
        'settings' => [
          'date_format' => 'short',
        ],
      ]);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['refreshed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Refreshed'))
      ->setDescription(t('The time that the entity was last refreshed.'));

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the Ec2ServiceKeyPair entity author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'match_limit' => 10,
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
