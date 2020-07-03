/**
 * @file
 * Js file to make the tables responsive.
 */

(function ($, Drupal) {
  Drupal.behaviors.myBehavior = {
    attach: function (context, settings) {
      maxWidth = drupalSettings.simpleResponsiveTable.maxWidth;
      // Table responsive.
      if ($(window).width() <= maxWidth) {
        modulePath = drupalSettings.simpleResponsiveTable.modulePath;
        cssFilePath = '/' + modulePath + '/css/simple-responsive-table.css';
        // Load css file dynamically only for small screens.
        $("<link/>", {
           rel: "stylesheet",
           type: "text/css",
           href: cssFilePath
        }).appendTo("head");
        // Add data title for each table row td.
        $(context).find('table').once('processTable').each(function () {
          headers = [];
          $(this).find('tr th').each(function () {
            header = $(this).text();
            // Handle sort headers.
            header = header.replace('Sort descending', '');
            header = header.replace('Sort ascending', '');
            headers.push(header.trim());
          });
          $(this).find('tr').each(function () {
            $(this).find('td').each(function (index) {
              $(this).attr('data-title', headers[index]);
              if ($(this).text().trim() == '') {
                $(this).html('<div class="simple-responsive-table-empty-row-data"></div>');
              }
            });
          });
        });
      }
    }
  };
})(jQuery, Drupal);
