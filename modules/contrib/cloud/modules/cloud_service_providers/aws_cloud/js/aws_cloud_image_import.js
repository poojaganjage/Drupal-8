(function ($) {
  'use strict';

  $('#edit-name').select2({
    ajax: {
      url: 'search',
      dataType: 'json',
      type: 'GET',
      delay: 1000,
      data: function (params) {
        return {
          q: params.term
        };
      },
      processResults: function (data) {
        let res = data.map(function (item) {
          return {id: item.id, text: item.name};
        });
        return {
          results: res
        };
      }
    },
    minimumInputLength: 4,
    placeholder: 'Search for images'
  });

})(jQuery);
