(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.MatomoReportsBehavior = {
    attach: function (context, settings) {
      // can access setting from 'drupalSettings';
        var pk_url = drupalSettings.matomo_reports.matomoJS.url;
        var query_string = drupalSettings.matomo_reports.matomoJS.query_string + '&jsoncallback=?';
        console.log(query_string);
        var header = "<table class='sticky-enabled sticky-table'><tbody></tbody></table>";
        // Add the table and show "Loading data..." status message for long running requests.
        $("#matomopageviews").html(header);
        $("#matomopageviews > table > tbody").html("<tr><td>" + Drupal.t('Loading data...') + "</td></tr>");
        // Get data from remote Matomo server.
        $.getJSON(pk_url + 'index.php?' + query_string, function(data){
          var item = '';
          $.each(data, function(key, val) {
            item = val;
          });
          var pk_content = "";
          if (item != '') {
            if (item.nb_visits) {
              pk_content += "<tr><td>" + Drupal.t('Visits') + "</td>";
              pk_content += "<td>" + item.nb_visits + "</td></tr>" ;
            }
            if (item.nb_hits) {
              pk_content += "<tr><td>" + Drupal.t('Page views') + "</td>";
              pk_content += "<td>" + item.nb_hits + "</td></tr>" ;
            }
          }
          // Push data into table and replace "Loading data..." status message.
          if (pk_content) {
            $("#matomopageviews > table > tbody").html(pk_content);
          }
          else {
            $("#matomopageviews > table > tbody > tr > td").html(Drupal.t('No data available.'));
          }
        });
    }
  };
})(jQuery, Drupal, drupalSettings);