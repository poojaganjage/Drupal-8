<?php

namespace Drupal\cloud\Service;

/**
 * Ansi string element renderer service.
 */
class AnsiStringRenderer implements AnsiStringRendererInterface {

  /**
   * {@inheritdoc}
   */
  public function render($value) : array {
    return [
      '#markup' => '<pre class = "ansi-block">' . $this->convertAnsiToHtml($value) . '</pre>',
      '#attached' => [
        'library' => ['cloud/cloud_ansi_color'],
      ],
    ];
  }

  /**
   * Convert ANSI sequences to HTML.
   *
   * @param string $text
   *   The text contains ANSI sequences.
   *
   * @return string
   *   The html for the text.
   */
  private function convertAnsiToHtml($text) {
    $html = preg_replace_callback('/\e\[([0-9]{0,2}(;[0-9]{1,2})*)m/U', [$this, 'getAnsiHtmlCallback'], $text);
    return "<span>$html</span>";
  }

  /**
   * Convert an ANSI sequence to a html.
   *
   * @param array $matches
   *   The matches of preg_replace_callback.
   *
   * @return string
   *   The html for an ANSI sequence
   */
  private function getAnsiHtmlCallback(array $matches) {
    $string = $matches[0];
    $t = '0';
    $f = '37';
    $b = '40';

    $string = str_replace(['[', 'm'], '', $string);
    $codes = explode(';', $string);

    foreach ($codes as $code) {
      $code = (int) $code;
      if ($code == 0 || $code == 1) {
        $t = $code;
      }
      elseif ($code > 29 && $code < 40) {
        $f = $code;
      }
      elseif ($code > 39 && $code < 50) {
        $b = $code;
      }
      elseif ($code > 49 && $code < 60) {
        $t = 1;
        $f = $code - 20;
      }
      elseif ($code > 59 && $code < 70) {
        $b = $code - 20;
      }
    }

    $class = "ansi t$t f$f b$b";
    return '</span><span class="' . $class . '">';
  }

}
