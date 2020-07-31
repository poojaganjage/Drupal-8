(function ($) {
  'use strict';

  // The interval to update a view's content.
  let refresh_in_sec = drupalSettings.aws_cloud_view_refresh_interval || 10;

  // The function to get a view's content in an Ajax way.
  let auto_update = function () {

    // The url of the callback for a view.
    let url = window.location.pathname + '/callback';

    // The query string of current request.
    // It is necessary because query parameters of a view, such as sorting and
    // paging, should be passed to callback in order to get the content with the
    // same sorting and paging as the initial view's content.
    let query_str = window.location.search;

    // Update .view-content element, which is the content of view, excluding
    // exposed filter and pager.
    $.get(url + query_str, function (data) {
      let form_token = $('.view-content input[name=form_token]').val();
      let checkbox_ids = $('.view-content .form-checkbox:checked').map(function () {
        return this.id;
      }).get();

      $('.views-element-container .view-content')
        .replaceWith($(data).find('.view-content'));

      // Restore form token.
      // If form token changes, the form validation will fail.
      if (form_token) {
        $('.view-content input[name=form_token]').val(form_token);
      }

      // Fix for the theme Bartik, which need to initialize drop buttons.
      if (Drupal.behaviors.dropButton) {
        Drupal.behaviors.dropButton.attach(document, drupalSettings);
      }

      if (Drupal.behaviors.tableSelect) {
        Drupal.behaviors.tableSelect.attach(document, drupalSettings);
      }

      // Restore checkbox selection.
      $.each(checkbox_ids, function (i, val) {
        if (val) {
          $('#' + val).prop('checked', true);
        }
        else {
          $('.select-all .form-checkbox').prop('checked', true);
        }
      });
    });
  };

  // Update a view's content every "refresh_in_sec" seconds.
  setInterval(auto_update, refresh_in_sec * 1000);
})(jQuery);
