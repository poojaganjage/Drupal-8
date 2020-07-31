<?php

namespace Drupal\aws_cloud\Form\Ec2;

use Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImageImportForm.
 *
 * Responsible for image importing.
 *
 * @package Drupal\aws_cloud\Form\Ec2
 */
class ImageImportForm extends FormBase {

  /**
   * The AWS Cloud EC2 Service.
   *
   * @var \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface
   */
  protected $ec2Service;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * AwsDeleteForm constructor.
   *
   * @param \Drupal\aws_cloud\Service\Ec2\Ec2ServiceInterface $ec2_service
   *   The AWS Cloud EC2 Service.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The Messenger service.
   */
  public function __construct(Ec2ServiceInterface $ec2_service, Messenger $messenger) {
    $this->ec2Service = $ec2_service;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('aws_cloud.ec2'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'image_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cloud_context = '') {
    $form['import_images'] = [
      '#type' => 'details',
      '#title' => $this->t('Images'),
      '#open' => TRUE,
    ];

    $form['import_images']['markup'] = [
      '#markup' => $this->t('Use this form to import images into the system.  Only one field is needed for searching.  The import process can return a very large set of images.  Please try to be specific in your search.'),
    ];
    $form['import_images']['owners'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Owners'),
      '#description' => $this->t('Comma separated list of owners.  For example "self, amazon".  Specifying amazon will bring back around 4000 images, which is a rather large set of images.'),
    ];

    $form['import_images']['image_ids'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Image IDs'),
      '#description' => $this->t('Comma separated list of image IDs.'),
    ];

    $form['import_images']['name'] = [
      '#type' => 'select',
      '#title' => $this->t('Search for images by AMI name'),
    ];

    $current_year_month = date('Ym');
    $markup = <<< EOD
<div>
You can use wildcards with the filter values. An asterisk (*) matches zero or more characters, and a question mark (?) matches exactly one character.<br/>
<br/>
Example: Find the current Amazon Linux 2 AMI:<br/>
amzn2-ami-hvm-2.0.????????-x86_64-gp2<br/>
<br/>
Example: Find the current Ubuntu Server 18.04 LTS AMI:<br/>
*ssd/ubuntu*18.04*$current_year_month*
</div>
EOD;

    $form['import_images']['examples'] = [
      '#type' => 'item',
      '#title' => $this->t('Examples of AMI name'),
      '#markup' => $markup,
    ];

    $form['cloud_context'] = [
      '#type' => 'value',
      '#value' => $cloud_context,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Import')];
    $form['#attached']['library'][] = 'aws_cloud/aws_cloud_image_import';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_state->clearErrors();

    $owners = trim($form_state->getValue('owners'));
    $image_ids = trim($form_state->getValue('image_ids'));
    $names = trim($form_state->getValue('name'));
    if (empty($owners) && empty($image_ids) && empty($names)) {
      $form_state->setError($form, $this->t('Please input at least one of Owners, Images IDs and AMI Name.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Build the Params array for importImages.
    $params = [];
    $owners = trim($form_state->getValue('owners'));
    if (!empty($owners)) {
      $params['Owners'] = explode(',', $owners);
    }
    $image_ids = trim($form_state->getValue('image_ids'));
    if (!empty($image_ids)) {
      $params['ImageIds'] = explode(',', $image_ids);
    }

    $names = trim($form_state->getValue('name'));
    if (!empty($names)) {
      $params['Filters'] = [
        [
          'Name' => 'name',
          'Values' => [$names],
        ],
      ];
    }

    $cloud_context = $form_state->getValue('cloud_context');

    if (count($params)) {
      $this->ec2Service->setCloudContext($cloud_context);
      if (($image_count = $this->ec2Service->updateImages($params, FALSE)) !== FALSE) {
        $this->messenger->addStatus($this->t('Imported @count images', ['@count' => $image_count]));
      }
    }

    return $form_state->setRedirect('view.aws_cloud_image.list', [
      'cloud_context' => $cloud_context,
    ]);
  }

}
