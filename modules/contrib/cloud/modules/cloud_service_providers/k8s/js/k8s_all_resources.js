(function ($) {
  'use strict';

  let cloud_context_namespaces = drupalSettings.k8s.cloud_context_namespaces;
  let updateNamespaceOptions = function (cloud_context) {
    $('#edit-namespace option').each(function () {
      let namespace = $(this).val();

      // If the cloud context is any.
      if (!cloud_context) {
        $(this).show();
        return;
      }

      // If the namespace is any.
      if (!namespace) {
        $(this).show();
        return;
      }

      // If the cloud context doesn't has any namespace,
      // or the namespace doesn't below to the cloud context.
      if (!cloud_context_namespaces[cloud_context]
        || !cloud_context_namespaces[cloud_context][namespace]) {

        $(this).hide();
        if ($(this).prop('selected')) {
          $(this).prop('selected', '');
        }
      } else {
        $(this).show();
      }
    });
  };

  updateNamespaceOptions($('#edit-cloud-context').val());
  $('#edit-cloud-context').change(function () {
    updateNamespaceOptions($(this).val());
  });
})(jQuery);
