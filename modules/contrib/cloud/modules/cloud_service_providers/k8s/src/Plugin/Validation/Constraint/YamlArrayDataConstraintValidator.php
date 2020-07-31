<?php

namespace Drupal\k8s\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates yaml data.
 */
class YamlArrayDataConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items ?: [] as $item) {
      try {
        $yamls = k8s_decode_multiple_doc_yaml($item->value);
        foreach ($yamls ?: [] as $yaml) {
          if (!is_array($yaml)) {
            $this->context->addViolation($constraint->invalidYamlArray);
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
