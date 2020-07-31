(function ($) {
  'use strict';

  let field_openstack_subnet_default_values = drupalSettings.openstack.field_openstack_subnet_default_values;
  $('select[name=field_openstack_subnet] option').each(function () {
    if (!field_openstack_subnet_default_values.includes($(this).val())) {
      $(this).remove();
    }
  });

  let field_openstack_security_group_default_values = drupalSettings.openstack.field_openstack_security_group_default_values;
  $("select[name='field_openstack_security_group[]'] option").each(function () {
    if (!field_openstack_security_group_default_values.includes($(this).val())) {
      $(this).remove();
    }
  });

  // Remove "- Select a value -" option if there is only one SSH key.
  let field_openstack_ssh_key_options = $('select[name=field_openstack_ssh_key] option');
  if (field_openstack_ssh_key_options.get().length ===  2) {
    field_openstack_ssh_key_options.each(function () {
      if ($(this).val() === '_none') {
        $(this).remove();
      }
    });
  }

  // Field image ID.
  $('#edit-field-openstack-image-id').select2({width: '100%'});
})(jQuery);
