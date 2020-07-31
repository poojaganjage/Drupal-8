(function ($) {
  'use strict';

  let updateLogs = function () {
    // Check status.
    if ($('.field--name-status .field--item').html() === 'applied') {
      return;
    }

    $.get(window.location.pathname + '/logs', function (json) {
      $('.field--name-plan-log')
        .replaceWith(json.planLog);

      $('.field--name-apply-log')
        .replaceWith(json.applyLog);
    });
  };

  let interval = drupalSettings.terraform.terraform_js_refresh_interval || 10;
  setInterval(function () {
    updateLogs();
  }, interval * 1000);

}(jQuery));
