<?php

namespace Drupal\cloud\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'project_link_formatter' formatter.
 *
 * This formatter links a cloud service provider name to the list of server
 * templates.
 *
 * @FieldFormatter(
 *   id = "project_link_formatter",
 *   label = @Translation("Cloud Project Link"),
 *   field_types = {
 *     "string",
 *     "uri",
 *   }
 * )
 */
class ProjectLinkFormatter extends ServerTemplateLinkFormatter {

}
