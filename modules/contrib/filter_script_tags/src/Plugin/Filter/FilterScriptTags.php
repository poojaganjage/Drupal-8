<?php

namespace Drupal\filter_script_tags\Plugin\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Component\Utility\Html;
use Drupal\filter\Annotation\Filter;


/**
 * Provides a filter to remove script tags with 
 *
 * @Filter(
 *   id = "filter_script_tags",
 *   title = @Translation("Filter Script Tags"),
 *   description = @Translation("Remove external script tags from displaying according to hostnames. This is especially useful for sites that allow full HTML inputs."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   settings = {
 *     "filter_script_tags_whitelist" = "",
 *   }
 * )
 */
class FilterScriptTags extends FilterBase
{

    /**
     * {@inheritdoc}
     */
    public function settingsForm(array $form, FormStateInterface $form_state)
    {
        $form['filter_script_tags_whitelist'] = [
            '#type' => 'textarea',
            '#title' => t('Whitelist'),
            '#description' => t('Only scripts from these domains are allowed. (One domain per line)'),
            '#default_value' => isset($this->settings['filter_script_tags_whitelist']) ? $this->settings['filter_script_tags_whitelist'] : '',
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function tips($long = FALSE)
    {
        return $this->t('Removes unsafe external script tags and removes empty tags from appearing on the page');
    }

    /**
     * {@inheritdoc}
     */
    public function process($text, $langcode)
    {
        
	    $text = $this->scriptFilterScript($text, $this->scriptFilterMapRegex($this->settings['filter_script_tags_whitelist']));
	    //** Return if string not given or empty.
	    if (!is_string ($text) || trim ($text) == '')
	      return new FilterProcessResult($text);

	    //** Recursive empty HTML tags.
	    $text = preg_replace (
	      //** Pattern to match empty tags.
	      '/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU',
	      //** Replace with nothing.
	      '',
	      //** Source string
	      $text
	    );

 
        return new FilterProcessResult($text);
    }

    /**
     * Helper function to scriptfilter_process_callback().
     *
     * @param string $whitelist
     *   String of domains (with wildcard) to work with.
     *
     * @return array
     *   Array of regular expression to match the domains (with wildcard).
     */
    private function scriptFilterMapRegex(string $whitelist)
    {
        $whitelist = trim($whitelist);
        $whitelists = !empty($whitelist) ? preg_split("/([ \t]*(\n|\r\n)+[ \t]*)+/", $whitelist) : [];

        return array_map(function ($host) {
            $host = preg_quote($host, NULL);
            $host = preg_replace('/\\\\\*/', '.*?', $host);
            $host = preg_replace('#/#', '\/', $host);
            return '/' . $host . '/';
        }, $whitelists);
    }

    /**
     * Helper function to scriptfilter_whitelist_value_callback().
     *
     * Removes all external scripts with src host not within whitelist.
     * Removes anything within <script> tags.
     * Removes anything that is not a <script> tag.
     * 
     *
     * @param string $string
     *   HTML content to filter from.
     * @param array $whitelist_regex
     *   Array of regular expressions to match whitelist domains.
     *
     * @return string
     *   Sanitized HTML
     */
    private function scriptFilterScript(string $string, array $whitelist_regex = [])
    {

        // Load the whole text string into a DOM object so we can properly
        // extract the script src. Using regex is way too messy and difficult
        // to ensure consistency.
        $html_input = Html::load($string);
        $to_remove = [];

        // Set flag to indicate whether we have modified $html_input and therefore need to return it.
        $send_new_html = false;
        
        $body = $html_input->getElementsByTagName('body');
        $body = $body[0];

        foreach ($body->childNodes as $element) {

                // Remove anything that is not a script tag.
                if ($element->nodeName !== "script") {
                    $to_remove[] = $element;
                    $send_new_html = true;
                }

                else {
                    // Remove the script if its src is not valid or is not whitelisted.
                    if ($element->hasAttribute('src')) {
                        $src = $element->getAttribute('src');
                        $host = parse_url($src, PHP_URL_HOST);
                        if (empty($host) || !$this->scriptFilterArrayMatch($host, $whitelist_regex)) {
                            $to_remove[] = $element;
                            $send_new_html = true;
                        }
                    }
    
                    // Remove any code (or anything at all) within the script tag.
                    while ($element->firstChild) {
                        $element->removeChild($element->firstChild);
                        $send_new_html = true;
                    }
                }
        }

        foreach ($to_remove as $element) {
            $element->parentNode->removeChild($element);
        }

        // If we have modified $html_input, we need to return it.
        if ($send_new_html) {
            $html = Html::serialize($html_input);
            return $html;
        }

        return $string;
    }

    /**
     * Helper function to scriptFilterScript().
     *
     * @param string $host
     *   Domain name from script tag.
     * @param array $regex_list
     *   Array of domain name of whitelist.
     *
     * @return bool
     *   Is matched.
     */
    private function scriptFilterArrayMatch(string $host, array $regex_list)
    {
        foreach ($regex_list as $regex) {
            if (preg_match($regex, $host)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
