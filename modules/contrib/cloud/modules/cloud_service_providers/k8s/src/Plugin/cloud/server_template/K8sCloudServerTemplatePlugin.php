<?php

namespace Drupal\k8s\Plugin\cloud\server_template;

use Drupal\cloud\Entity\CloudServerTemplateInterface;
use Drupal\cloud\Plugin\cloud\CloudPluginBase;
use Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface;
use Drupal\cloud\Plugin\cloud\server_template\CloudServerTemplatePluginInterface;
use Drupal\cloud\Service\CloudServiceInterface;
use Drupal\cloud\Service\EntityLinkRendererInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\Exception\NotRegularDirectoryException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\k8s\Entity\K8sEntityBase;
use Drupal\k8s\Service\K8sServiceException;
use Drupal\k8s\Service\K8sServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * K8s Cloud server template plugin.
 */
class K8sCloudServerTemplatePlugin extends CloudPluginBase implements CloudServerTemplatePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The K8s Service.
   *
   * @var \Drupal\k8s\Service\K8sServiceInterface
   */
  protected $k8sService;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * Current login user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cloud service provider plugin manager (CloudConfigPluginManager).
   *
   * @var \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface
   */
  protected $cloudConfigPluginManager;

  /**
   * Entity link renderer object.
   *
   * @var \Drupal\cloud\Service\EntityLinkRendererInterface
   */
  protected $entityLinkRenderer;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The Cloud service.
   *
   * @var \Drupal\cloud\Service\CloudServiceInterface
   */
  protected $cloudService;

  /**
   * K8sCloudServerTemplatePlugin constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The uuid service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current login user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\cloud\Plugin\cloud\config\CloudConfigPluginManagerInterface $cloud_config_plugin_manager
   *   The cloud service provider plugin manager (CloudConfigPluginManager).
   * @param \Drupal\cloud\Service\EntityLinkRendererInterface $entity_link_renderer
   *   The entity link render service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\cloud\Service\CloudServiceInterface $cloud_service
   *   The Cloud service.
   * @param \Drupal\k8s\Service\K8sServiceInterface $k8s_service
   *   The K8s Service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager,
                              UuidInterface $uuid_service,
                              AccountProxyInterface $current_user,
                              ConfigFactoryInterface $config_factory,
                              CloudConfigPluginManagerInterface $cloud_config_plugin_manager,
                              EntityLinkRendererInterface $entity_link_renderer,
                              FileSystemInterface $file_system,
                              CloudServiceInterface $cloud_service,
                              K8sServiceInterface $k8s_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->uuidService = $uuid_service;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
    $this->cloudConfigPluginManager = $cloud_config_plugin_manager;
    $this->entityLinkRenderer = $entity_link_renderer;
    $this->fileSystem = $file_system;
    $this->cloudService = $cloud_service;
    $this->k8sService = $k8s_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('uuid'),
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('plugin.manager.cloud_config_plugin'),
      $container->get('entity.link_renderer'),
      $container->get('file_system'),
      $container->get('cloud'),
      $container->get('k8s')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundleName() {
    return $this->pluginDefinition['entity_bundle'];
  }

  /**
   * {@inheritdoc}
   */
  public function launch(CloudServerTemplateInterface $cloud_server_template, FormStateInterface $form_state = NULL) {
    $k8s_obj = $cloud_server_template->get('field_object')->value;
    $source_type = $cloud_server_template->get('field_source_type')->value;
    $delete_resources = $form_state->getValue('field_delete_all_resources');

    if (!empty($delete_resources)) {
      $resources = $cloud_server_template->get('field_launch_resources')->getValue();
      if (!empty($resources)) {
        $entities = [];
        foreach ($resources ?: [] as $resource) {
          $type = $resource['item_key'];
          $id = $resource['item_value'];
          $entity = \Drupal::entityTypeManager()
            ->getStorage($type)
            ->load($id);
          if (!empty($entity)) {
            $entities[] = $entity;
          }
        }
        if (!empty($entities)) {
          $this->k8sService->deleteResourcesWithEntities($entities);
        }
        $cloud_server_template->get('field_launch_resources')->setValue(NULL);
      }
    }

    $route = [
      'route_name' => 'entity.cloud_server_template.canonical',
      'params' => [
        'cloud_server_template' => $cloud_server_template->id(),
        'cloud_context' => $cloud_server_template->getCloudContext(),
      ],
    ];

    $this->k8sService->setCloudContext($cloud_server_template->getCloudContext());

    if ($source_type === 'git') {
      $this->launchByGitYamlFiles($cloud_server_template, $form_state);
      return $route;
    }

    $object_types = k8s_supported_cloud_server_templates();
    if (empty($object_types[$k8s_obj])) {
      $this->messenger->addError($this->t('@object launch not supported.', ['@object' => $k8s_obj]));
      return $route;
    }

    return $this->launchK8sResources($cloud_server_template);
  }

  /**
   * {@inheritdoc}
   */
  public function buildListHeader() {
    return [
      [
        'data' => $this->t('Namespace'),
        'specifier' => 'field_namespace',
        'field' => 'field_namespace',
      ],
      [
        'data' => $this->t('Object'),
        'specifier' => 'field_object',
        'field' => 'field_object',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildListRow(CloudServerTemplateInterface $entity) {
    $row['field_namespace'] = [
      'data' => $this->entityLinkRenderer->renderViewElement(
        $entity->get('field_namespace')->value,
        'k8s_namespace',
        'name',
        []
      ),
    ];
    $row['field_object'] = [
      'data' => $this->renderField($entity, 'field_object'),
    ];
    return $row;
  }

  /**
   * Build the launch form for K8s.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state if launch is called from a form.
   */
  public function buildLaunchForm(array &$form, FormStateInterface $form_state) {
    $form_object = $form_state->getFormObject();
    $entity = $form_object->getEntity();
    $source_type = $entity->get('field_source_type')->value;

    try {
      $form['#parents'] = [];

      $form_display = $this->entityTypeManager
        ->getStorage('entity_form_display')
        ->load('cloud_server_template.k8s.default');
      $widget_cloud_context = $form_display->getRenderer('cloud_context');
      $items_cloud_context = $entity->get('cloud_context');
      $items_cloud_context->filterEmptyItems();
      $form['cloud_context'] = $widget_cloud_context->form($items_cloud_context, $form, $form_state);
      $form['cloud_context']['widget'][0]['value']['#title'] = t('K8s Cluster');
      $form['cloud_context']['widget'][0]['value']['#type'] = 'select';
      $form['cloud_context']['widget'][0]['value']['#options'] = k8s_cluster_allowed_values();
      $form['cloud_context']['widget'][0]['value']['#ajax'] = [
        'callback' => 'k8s_ajax_callback_get_fields',
      ];
      unset($form['cloud_context']['widget'][0]['value']['#size']);
      unset($form['cloud_context']['widget'][0]['value']['#description']);

      $widget_namespace = $form_display->getRenderer('field_namespace');
      $items_namespace = $entity->get('field_namespace');
      $items_namespace->filterEmptyItems();
      $form['field_namespace'] = $widget_namespace->form($items_namespace, $form, $form_state);

      $resources = $entity->get('field_launch_resources')->getValue();
      if (!empty($resources)) {
        k8s_create_resources_message($form, $resources);
      }
    }
    catch (\Exception $e) {
      $this->handleException($e);
    }

    $fieldsets_def = [
      [
        'name' => 'k8s',
        'title' => t('K8s'),
        'open' => TRUE,
        'fields' => [
          'cloud_context',
          'field_namespace',
        ],
      ],
    ];

    if ($source_type === 'git') {

      $git_url_value = $entity->get('field_yaml_url')->getValue();
      $git_url = $git_url_value[0]['uri'];
      $git_paths = $entity->get('field_git_path')->getValue();
      $git_username = $entity->get('field_git_username')->value;
      $git_password = $entity->get('field_git_password')->value;

      $tmp_dir = $this->fileSystem->getTempDirectory();
      $name = Html::getId($entity->getName() . ' ' . $entity->id());
      $dir_name = $this->fileSystem->getDestinationFilename($tmp_dir . "/$name", FileSystemInterface::EXISTS_RENAME);

      // Make the temporary working directory.
      $result = $this->fileSystem->mkdir($dir_name);

      if (!$result) {
        $this->messenger->addError($this->t("Unable to create the working directory."));
      }
      else {
        if ($git_username) {
          $account = rawurlencode($git_username);
          if ($git_password) {
            $account .= ":" . rawurlencode($git_password);
          }
          $git_url = str_replace("://", "://$account@", $git_url);
        }
        $output = [];
        $return_var = 0;
        exec("git clone $git_url $dir_name", $output, $return_var);
        if ($return_var !== 0) {
          $this->messenger->addError($this->t("Unable to clone the git repository."));
        }
        else {
          $match = '/.*/';
          $files = [];
          try {
            foreach ($git_paths ?: [] as $path) {
              $files = array_merge($files, $this->fileSystem->scanDirectory($dir_name . $path['value'], $match));
            }
          }
          catch (NotRegularDirectoryException $e) {
            $this->messenger->addError($this->t("Git Resource Path might not be correct."));
          }

          if (!empty($files)) {
            usort($files, static function ($file1, $file2) {
              $dir1 = dirname($file1->uri);
              $dir2 = dirname($file2->uri);

              if ($dir1 === $dir2) {
                return $file1 > $file2;
              }
              elseif ($dir1 > $dir2) {
                return 1;
              }
              else {
                return -1;
              }
            });

            $templates = k8s_supported_cloud_server_templates();
            $extensions = $this->configFactory->get('k8s.settings')
              ->get('k8s_yaml_file_extensions');
            $regex = '/\.(' . preg_replace('/ +/', '|', preg_quote($extensions)) . ')$/i';

            foreach ($files ?: [] as $file) {
              $message = NULL;
              $validated = FALSE;
              $file_contents = file_get_contents($file->uri);
              if (!$file_contents) {
                $message = $this->t("Unable to get contents from the file [%file].", ["%file" => $file->filename]);
              }
              else {
                if (!preg_match($regex, $file->filename)) {
                  continue;
                }
                else {
                  $yamls = k8s_decode_multiple_doc_yaml($file_contents);

                  // Validate yamls.
                  foreach ($yamls as $yaml) {
                    if (!is_array($yaml)) {
                      $message = $this->t("The file [%file] isn't YAML format.", ['%file' => $file->filename]);
                      break;
                    }

                    if (!isset($yaml['kind'])) {
                      $message = $this->t("No 'Kind' element found in the file [%file].", ['%file' => $file->filename]);
                      break;
                    }

                    $kind = $yaml['kind'];
                    $object = array_search($kind, $templates);
                    if ($object === FALSE) {
                      $message = $this->t("Unsupported 'Kind' element in the file [%file].", ['%file' => $file->filename]);
                      break;
                    }

                    $validated = TRUE;
                  }
                }

                $directory = str_replace($dir_name, '', dirname($file->uri));

                if (!isset($form['yaml_files'][$directory])) {
                  $form['yaml_files'][$directory] = [
                    '#type' => 'details',
                    '#title' => $directory,
                    '#open' => TRUE,
                    'files' => [],
                  ];
                }
                $form['yaml_files'][$directory]['files'][] = [
                  '#type' => 'details',
                  '#title' => $file->filename,
                  '#open' => !$validated,
                ];
                $idx = count($form['yaml_files'][$directory]['files']) - 1;
                if (!$validated && !empty($message)) {
                  $form['yaml_files'][$directory]['files'][$idx]['message'] = [
                    '#theme' => 'status_messages',
                    '#message_list' => ['error' => [$message]],
                  ];
                  $form['yaml_files'][$directory]['files'][$idx]['#attributes'] = ['class' => ['error', 'has-error']];
                  $form['yaml_files'][$directory]['files'][$idx]["launch_resource_$idx"]['#attributes'] = ['class' => ['error']];
                }
                $form['yaml_files'][$directory]['files'][$idx]["launch_resource_$idx"] = [
                  '#type' => 'checkbox',
                  '#title' => t('Launch this resource'),
                  '#default_value' => $validated,
                ];
                if (!$validated && !empty($message)) {
                  $form['yaml_files'][$directory]['files'][$idx]["launch_resource_$idx"]['#attributes'] = ['class' => ['error']];
                }
                $form['yaml_files'][$directory]['files'][$idx]["yaml_file_name_$idx"] = [
                  '#type' => 'hidden',
                  '#default_value' => $file->filename,
                ];
                $form['yaml_files'][$directory]['files'][$idx]["yaml_file_content_$idx"] = [
                  '#type' => 'textarea',
                  '#default_value' => $file_contents,
                  '#attributes' => ['readonly' => 'readonly'],
                ];
              }
            }
          }
        }
      }

      $fieldsets_def[] = [
        'name' => 'launch_resources',
        'title' => t('Launch Resources'),
        'open' => TRUE,
        'fields' => [
          'yaml_files',
        ],
      ];

      $this->fileSystem->deleteRecursive($dir_name);
    }

    $resources = $entity->get('field_launch_resources')->getValue();
    if (!empty($resources)) {
      $form['field_delete_all_resources'] = [
        '#type' => 'checkbox',
        '#title' => t('Delete following resources'),
        '#default_value' => FALSE,
      ];

      $fieldsets_def[] = [
        'name' => 'delete_resources',
        'title' => t('Delete Resources'),
        'open' => TRUE,
        'fields' => [
          'field_delete_all_resources',
          'confirm_message',
        ],
      ];
    }
    $this->cloudService->reorderForm($form, $fieldsets_def);
    $form['description']['#weight'] = count($fieldsets_def) + 1;

  }

  /**
   * Render a server template entity field.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $entity
   *   The server template entity.
   * @param string $field_name
   *   The field to render.
   * @param string $view
   *   The view to render.
   *
   * @return array
   *   A fully loaded render array for that field or empty array.
   */
  private function renderField(CloudServerTemplateInterface $entity, $field_name, $view = 'default') {
    $field = [];
    if ($entity->hasField($field_name)) {
      $field = $entity->get($field_name)->view($view);
      // Hide the label.
      $field['#label_display'] = 'hidden';
    }
    return $field;
  }

  /**
   * Launch a K8s Resource from a server template.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $cloud_server_template
   *   Cloud server template interface.
   *
   * @return array
   *   The route to redirect after launch.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *   Thrown when unable to launch deployment.
   */
  private function launchK8sResources(CloudServerTemplateInterface $cloud_server_template) {
    $object_types = k8s_supported_cloud_server_templates();
    $yamls = k8s_decode_multiple_doc_yaml($cloud_server_template->get('field_detail')->value);
    foreach ($yamls ?: [] as $yaml) {
      $yaml['metadata']['annotations'][K8sEntityBase::ANNOTATION_LAUNCHED_APPLICATION_ID] = $cloud_server_template->id();
      $kind = $yaml['kind'];
      $object_type = array_search($kind, $object_types);
      $entity_type_id = "k8s_$object_type";

      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $id_plural = $entity_type->get('id_plural');
      $namespaceable = $entity_type->get('namespaceable');
      if ($namespaceable === NULL) {
        $namespaceable = TRUE;
      }

      // Get the name of method createXXXX.
      $short_label = ucwords(str_replace('_', ' ', $object_type));
      $name_camel = str_replace(' ', '', $short_label);
      $create_method_name = "create{$name_camel}";

      // Get the name of method updateXXXXs.
      $short_name = '';
      if (!empty($id_plural)) {
        $short_name = substr($id_plural, strlen('k8s_'));
      }
      $name_plural_camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $short_name)));
      $update_method_name = "update${name_plural_camel}";

      try {
        if ($namespaceable) {
          $result = $this->k8sService->$create_method_name(
            $cloud_server_template->get('field_namespace')->value,
            $yaml
          );
        }
        else {
          $result = $this->k8sService->$create_method_name($yaml);
        }

        $this->k8sService->$update_method_name([
          'metadata.name' => $result['metadata']['name'],
        ], FALSE);

        // Update creation_yaml field of entity.
        $entities = $this->entityTypeManager->getStorage($entity_type_id)->loadByProperties(
          [
            'cloud_context' => $cloud_server_template->getCloudContext(),
            'name' => $result['metadata']['name'],
          ]
        );
        if (!empty($entities)) {
          $entity = reset($entities);

          // Update creation_yaml.
          if (method_exists($entity, 'setCreationYaml')) {
            unset($yaml['metadata']['annotations'][K8sEntityBase::ANNOTATION_LAUNCHED_APPLICATION_ID]);
            $entity->setCreationYaml(Yaml::encode($yaml));
            $entity->save();
          }

          $this->messenger->addStatus($this->t('The @type <a href=":url">%label</a> has been launched.', [
            '@type' => $short_label,
            ':url' => $entity->toUrl('canonical')->toString(),
            '%label' => $result['metadata']['name'],
          ]));

          $this->logger('k8s')->notice('@type: created %label.', [
            '@type' => $short_label,
            '%label' => $result['metadata']['name'],
            'link' => $entity->toLink($this->t('View'))->toString(),
          ]);

          $cloud_server_template->get('field_launch_resources')->appendItem(['item_key' => $entity->getEntityTypeId(), 'item_value' => $entity->id()]);

          // Identify whether called by 'launchByGitYamlFiles'.
          // Parameters are 2 when called by the function.
          // And the cloud server template shouldn't be saved.
          if (count(func_get_args()) === 1) {
            $cloud_server_template->validate();
            $cloud_server_template->save();
          }
        }
        else {
          $this->messenger->addStatus($this->t('The @type %label has been launched.', [
            '@type' => $short_label,
            '%label' => $result['metadata']['name'],
          ]));

          $this->logger('k8s')->notice('@type: launched %label.', [
            '@type' => $short_label,
            '%label' => $result['metadata']['name'],
          ]);
        }

      }
      catch (K8sServiceException
      | EntityStorageException
      | EntityMalformedException $e) {

        $this->processOperationErrorStatus($cloud_server_template, 'launched');

        $route = [
          'route_name' => 'entity.cloud_server_template.canonical',
          'params' => [
            'cloud_server_template' => $cloud_server_template->id(),
            'cloud_context' => $cloud_server_template->getCloudContext(),
          ],
        ];

        return $route;
      }
    }

    if (count($yamls) === 1) {
      $route = [
        'route_name' => "view.$entity_type_id.list",
        'params' => ['cloud_context' => $cloud_server_template->getCloudContext()],
      ];
    }
    else {
      $route = [
        'route_name' => 'entity.cloud_server_template.canonical',
        'params' => [
          'cloud_server_template' => $cloud_server_template->id(),
          'cloud_context' => $cloud_server_template->getCloudContext(),
        ],
      ];
    }

    return $route;
  }

  /**
   * Launch by YAML files in git repository.
   *
   * @param \Drupal\cloud\Entity\CloudServerTemplateInterface $cloud_server_template
   *   Cloud server template interface.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state if launch is called from a form.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  private function launchByGitYamlFiles(CloudServerTemplateInterface $cloud_server_template, FormStateInterface $form_state = NULL) {
    $values = $form_state->getValues();

    $launch_resources = [];
    $yaml_file_contents = [];
    $yaml_file_names = [];
    $pattern = '/\d+/';
    foreach ($values as $key => $value) {
      if (strpos($key, 'launch_resource') !== FALSE) {
        $match = [];
        preg_match($pattern, $key, $match);
        if (!empty($match)) {
          $idx = $match[0];
          $launch_resources[$idx] = $value;
        }
      }
      if (strpos($key, 'yaml_file_content') !== FALSE) {
        $match = [];
        preg_match($pattern, $key, $match);
        if (!empty($match)) {
          $idx = $match[0];
          $yaml_file_contents[$idx] = $value;
        }
      }
      if (strpos($key, 'yaml_file_name') !== FALSE) {
        $match = [];
        preg_match($pattern, $key, $match);
        if (!empty($match)) {
          $idx = $match[0];
          $yaml_file_names[$idx] = $value;
        }
      }
    }

    $objects = [];
    if (!empty($yaml_file_contents) && !empty($launch_resources) && !empty($yaml_file_names)) {
      $templates = k8s_supported_cloud_server_templates();
      foreach ($yaml_file_contents as $idx => $file_contents) {
        if (empty($launch_resources[$idx])) {
          continue;
        }
        try {
          $yamls = k8s_decode_multiple_doc_yaml($file_contents);
          $validated = FALSE;
          $object = NULL;

          // Validate yamls.
          foreach ($yamls as $yaml) {
            if (!is_array($yaml)) {
              $this->messenger->addError($this->t("The file [%file] isn't YAML format.", ['%file' => $yaml_file_names[$idx]]));
              break;
            }

            if (!isset($yaml['kind'])) {
              $this->messenger->addError($this->t("No 'Kind' element found in the file [%file].", ['%file' => $yaml_file_names[$idx]]));
              break;
            }

            $kind = $yaml['kind'];
            $object = array_search($kind, $templates);
            if ($object === FALSE) {
              $this->messenger->addError($this->t("Unsupported 'Kind' element in the file [%file].", ['%file' => $yaml_file_names[$idx]]));
              break;
            }

            $validated = TRUE;
          }

          if ($validated) {
            $cloud_server_template->get('field_detail')->setValue($file_contents);
            $errors_before = count($this->messenger->messagesByType($this->messenger::TYPE_ERROR));
            // Put an extra 2nd param to identify called by this function.
            $this->launchK8sResources($cloud_server_template, TRUE);
            $errors_after = count($this->messenger->messagesByType($this->messenger::TYPE_ERROR));
            if ($errors_before === $errors_after) {
              if (count($yamls) === 1) {
                $objects[] = $object;
              }
              else {
                $objects[] = 'mixed';
              }
            }
          }
        }
        catch (\Exception $e) {
          $this->messenger->addError($this->t("Invalid Yaml format in the file [%file]:%message", [
            '%file' => $yaml_file_names[$idx],
            '%message' => $e->getMessage(),
          ]));
        }
      }

      $cloud_server_template->get('field_object')->setValue($objects);
      $cloud_server_template->get('field_detail')->setValue(NULL);
      $cloud_server_template->validate();
      $cloud_server_template->save();
    }
  }

}
