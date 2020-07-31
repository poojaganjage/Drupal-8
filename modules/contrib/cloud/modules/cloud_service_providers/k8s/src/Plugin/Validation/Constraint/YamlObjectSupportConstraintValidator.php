<?php

namespace Drupal\k8s\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validates the "kind" element.
 */
class YamlObjectSupportConstraintValidator extends YamlArrayDataConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $templates = k8s_supported_cloud_server_templates();

    foreach ($items ?: [] as $item) {
      try {
        $yamls = k8s_decode_multiple_doc_yaml($item->value);
        foreach ($yamls ?: [] as $yaml) {
          $kind = $yaml['kind'];
          if (isset($kind)) {
            $object = array_search($kind, $templates);
            if ($object === FALSE) {
              $this->context->addViolation($constraint->unsupportedObjectType);
              return;
            }
          }
          else {
            $this->context->addViolation($constraint->noObjectFound);
            return;
          }
        }
      }
      catch (\Exception $e) {
        $this->context->addViolation($constraint->invalidYaml . $e->getMessage());
        break;
      }
    }
  }

}
