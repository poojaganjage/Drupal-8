(function ($) {
  'use strict';

  if (!window.location.hash) {
    return;
  }

  let instance_type = window.location.hash.substr(1);
  let instance_type_underscore = instance_type.replace('.', '_');

  // Find the column with instance type.
  let $td = $('tr.' + instance_type_underscore + ' td:first');
  if (!$td.html()) {
    return;
  }

  // Highlight the row.
  $('tr.' + instance_type_underscore).addClass('highlight');

  // Calculate position.
  let navbar_top = $('header').offset().top;
  let header_height = $('table.aws_cloud_instance_type_prices thead').height();
  let top = $td.offset().top - header_height - navbar_top;
  setTimeout(function () {
    $(window).scrollTop(top);
  }, 500);
})(jQuery);
