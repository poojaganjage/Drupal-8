<?php

/**
 * Provides cmis module Implementation.
 *
 * @category Module
 *
 * @package Contrib
 *
 * @author Display Name <username@example.com>
 *
 * @license https://www.drupal.org/ Drupal
 *
 * @version "GIT: <1001>"
 *
 * @link https://www.drupal.org/
 */

declare(strict_types = 1);

namespace Drupal\cmis\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'cmis_field_link' formatter.
 *
 * @FieldFormatter(
 *   id = "cmis_field_link",
 *   label = @Translation("Cmis field link"),
 *   field_types = {
 *     "cmis_field"
 *   }
 * )
 */

/**
 * Class CmisFieldLink.
 *
 * @category Module
 *
 * @package Drupal\cmis\Plugin\Field\FieldFormatter
 *
 * @author Display Name <username@example.com>
 *
 * @license https://www.drupal.org/ Drupal
 *
 * @version "Release: 8"
 *
 * @link https://www.drupal.org/
 */
class CmisFieldLink extends FormatterBase
{

    /**
     * View Elements.
     *
     * @param FieldItemListInterface $items    The field item list interface.
     * @param string                 $langcode The langiage code.
     *
     * @return array
     *   The textual output generated.
     */
    public function viewElements(FieldItemListInterface $items, $langcode)
    {
        $elements = [];

        foreach ($items as $delta => $item) {
            $elements[$delta] = $this->viewValue($item);
        }

        return $elements;
    }

    /**
     * Generate the output appropriate for one field item.
     *
     * @param \Drupal\Core\Field\FieldItemInterface $item One field item.
     *
     * @return array
     *   The textual output generated.
     */
    protected function viewValue(FieldItemInterface $item)
    {
        $url = Url::fromUserInput($item->get('path')->getValue());
        if (empty($url)) {
            return [];
        }
        $path = Link::fromTextAndUrl($item->get('title')->getValue(), $url)
        ->toRenderable();

        return $path;
    }

}
