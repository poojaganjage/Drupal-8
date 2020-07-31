(function ($, drupalSettings) {
  'use strict';

  let setGeolocation = function() {

    if (!drupalSettings.cloud || !drupalSettings.cloud.geocoder_url) {
      return;
    }
    let country = $('#edit-field-location-country').val();
    let city = $('#edit-field-location-city-0-value').val();
    if (country === '_none' || city.trim().length === 0) {
      return;
    }
    let url = drupalSettings.cloud.geocoder_url;

    url = url.replace('country', country);
    url = url.replace('city', city);

    $.ajax({
      async: false,
      url: url,
      dataType: 'json',
      success: function success(data) {
        if (data && data.latitude && data.longitude) {
          let step = $('#edit-field-location-latitude-0-value').attr('step');
          if (step) {
            step = Number.parseFloat(step);
          }
          else {
            step = 0.000001;
          }
          step = 1 / step;
          let latitude = Math.round(data.latitude * step) / step;
          let longitude = Math.round(data.longitude * step) / step;
          $('#edit-field-location-latitude-0-value').val(latitude);
          $('#edit-field-location-longitude-0-value').val(longitude);
        }
      }
    });
  };

  $('#edit-field-location-country').change(function() {
    setGeolocation();
  });

  $('#edit-field-location-country').blur(function() {
    setGeolocation();
  });

  $('#edit-field-location-city-0-value').change(function() {
    setGeolocation();
  });

  $('#edit-field-location-city-0-value').blur(function() {
    setGeolocation();
  });

  $('form').submit(function() {
    setGeolocation();
  });

}(jQuery, drupalSettings));
