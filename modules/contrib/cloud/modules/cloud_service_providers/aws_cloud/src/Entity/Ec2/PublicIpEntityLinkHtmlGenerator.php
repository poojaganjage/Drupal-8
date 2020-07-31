<?php

namespace Drupal\aws_cloud\Entity\Ec2;

use Drupal\cloud\Service\Util\EntityLinkHtmlGenerator;
use Drupal\Core\Url;

/**
 * Html generator for public_ip field.
 */
class PublicIpEntityLinkHtmlGenerator extends EntityLinkHtmlGenerator {

  /**
   * Render entity link for view.
   *
   * @param \Drupal\Core\Url $url
   *   The URL object.
   * @param int $id
   *   The entity ID.
   * @param string $name
   *   The field name of target entity.
   * @param string $alt_text
   *   Optional alternative text to display.
   *
   * @return string
   *   The HTML link string.
   */
  public function generate(Url $url, $id, $name = '', $alt_text = ''): string {
    if (!empty($name) && $name !== $id) {
      $html = $this->linkGenerator->generate($name, $url);
      $html = "$id ($html)";
    }
    else {
      $html = $this->linkGenerator->generate($id, $url);
    }

    return $html;
  }

}
