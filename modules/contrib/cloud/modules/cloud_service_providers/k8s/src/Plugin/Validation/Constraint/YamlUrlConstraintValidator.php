<?php

namespace Drupal\k8s\Plugin\Validation\Constraint;

use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates YAML URL fields.
 */
class YamlUrlConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {

    // Only perform validations for K8s bundles.
    if ($entity->bundle() === 'k8s') {
      $yaml_url = $entity->get('field_yaml_url')->uri;
      $detail = $entity->get('field_detail')->value;
      $source_type = $entity->get('field_source_type')->value;

      if ($source_type === 'git') {
        if (empty($yaml_url)) {
          $this->context
            ->buildViolation($constraint->requiredGitUrl)
            ->atPath('field_yaml_url')
            ->addViolation();
        }
        elseif (strpos($yaml_url, '.git') !== strlen($yaml_url) - 4) {
          $this->context
            ->buildViolation($constraint->invalidGitUrl)
            ->atPath('field_yaml_url')
            ->addViolation();
        }
        else {
          $client = \Drupal::httpClient();
          try {
            $client->get($yaml_url);
          }
          catch (RequestException $e) {
            $this->context
              ->buildViolation($constraint->unreachableGitUrl)
              ->atPath('field_yaml_url')
              ->addViolation();
          }
        }
      }
      else {
        if (empty($yaml_url) && empty($detail)) {
          $this->context
            ->buildViolation($constraint->requiredYamlUrlOrDetail)
            ->atPath('field_yaml_url')
            ->addViolation();
        }
        elseif (!empty($yaml_url) && !empty($detail)) {
          $this->context
            ->buildViolation($constraint->prohibitYamlUrlAndDetail)
            ->atPath('field_yaml_url')
            ->addViolation();
        }
        elseif (!empty($yaml_url)) {
          $content = file_get_contents($yaml_url);
          if (empty($content)) {
            $this->context
              ->buildViolation($constraint->invalidYamlUrl)
              ->atPath('field_yaml_url')
              ->addViolation();
          }
          else {
            $templates = k8s_supported_cloud_server_templates();
            try {
              $yamls = k8s_decode_multiple_doc_yaml($content);
              foreach ($yamls ?: [] as $yaml) {
                if (!is_array($yaml)) {
                  $this->context
                    ->buildViolation($constraint->invalidYamlFormat)
                    ->atPath('field_yaml_url')
                    ->addViolation();
                  return;
                }

                if (empty($yaml['kind'])) {
                  $this->context
                    ->buildViolation($constraint->noKindFound)
                    ->atPath('field_yaml_url')
                    ->addViolation();
                  return;
                }

                $kind = $yaml['kind'];
                $object = array_search($kind, $templates);
                if (empty($object)) {
                  $this->context
                    ->buildViolation($constraint->unsupportedObjectType, ['%kind' => $kind])
                    ->atPath('field_yaml_url')
                    ->addViolation();
                  return;
                }
              }
            }
            catch (\Exception $e) {
              $this->context
                ->buildViolation($constraint->invalidYaml . $e->getMessage())
                ->atPath('field_yaml_url')
                ->addViolation();
            }
          }
        }
      }
    }
  }

}
