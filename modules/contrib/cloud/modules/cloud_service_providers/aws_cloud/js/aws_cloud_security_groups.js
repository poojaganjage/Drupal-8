(function ($, Drupal) {
  'use strict';

  Drupal.SecurityGroup = Drupal.SecurityGroup || {};

  Drupal.SecurityGroup.autoPopulate = {
    populateAllPorts: function (protocol, field, key) {
      if (protocol === '-1') {
        $('.form-item-' + field + '-' + key + '-from-port input').val('0');
        $('.form-item-' + field + '-' + key + '-to-port input').val('65535');
      }
    }
  };

  Drupal.SecurityGroup.showHide = {
    hideRow: function (field, row_count, table_id) {
      let from_port = $('.' + table_id + ' tr.row-' + row_count + ' .form-item-' + field + '-' + row_count + '-from-port input').val();
      let to_port = $('.' + table_id + ' tr.row-' + row_count + ' .form-item-' + field + '-' + row_count + '-to-port input').val();

      // If these fields are blank, they qualify as an empty row. Remove them
      if (from_port !== '' && to_port !== '') {
        $('.' + table_id + ' tr.row-' + row_count + ' .form-item-' + field + '-' + row_count + '-from-port input').val('');
        $('.' + table_id + ' tr.row-' + row_count + ' .form-item-' + field + '-' + row_count + '-to-port input').val('');
        $('.' + table_id + ' tr.row-' + row_count + ' .form-item-' + field + '-' + row_count + '-cidr-ip input').val('');
        $('.' + table_id + ' tr.row-' + row_count + ' .form-item-' + field + '-' + row_count + '-cidr-ip-v6 input').val('');
        $('.' + table_id + ' tr.row-' + row_count + ' .form-item-' + field + '-' + row_count + '-group-id input').val('');
        $('.' + table_id + ' tr.row-' + row_count + ' .form-item-' + field + '-' + row_count + '-prefix-list-id input').val('');
        $('.' + table_id + ' tr.row-' + row_count).addClass('hide');
        // Display a message telling user to save the page before the rule change is applied.
        if (!$('.messages--warning').length) {
          $('<div class="messages messages--warning" role="alert" style=""><abbr class="warning">*</abbr> Click "Save" to apply rule changes.</div>').prependTo('#edit-rules .details-wrapper');
        }
      }
    },
  };

  Drupal.behaviors.securityPermissions = {
    attach: function (context, settings) {
      $.each($('.field--name-ip-permission .ip-protocol-select', context), function (k, el) {
        $(el).change(function () {
          // Populate from-to ports for "all traffic" option.
          Drupal.SecurityGroup.autoPopulate.populateAllPorts($(this).val(), 'ip-permission', k);
        });
      });

      $.each($('.field--name-outbound-permission .ip-protocol-select', context), function (k, el) {
        $(el).change(function () {
          // Populate from-to ports for "all traffic" option.
          Drupal.SecurityGroup.autoPopulate.populateAllPorts($(this).val(), 'outbound-permission', k);
        });
      });

      // When the link is clicked, clear out the to and from port.
      // Hide the row, and add a message for the user.
      $.each($('.ip-permission-values .remove-rule', context), function (k, el) {
        $(el).click(function () {
          let row_count = $(this).attr('data-row');
          let table_id = $(this).attr('data-table-id');
          Drupal.SecurityGroup.showHide.hideRow('ip-permission', row_count, table_id);
        });
      });

      $.each($('.outbound-permission-values .remove-rule', context), function (k, el) {
        $(el).click(function () {
          let row_count = $(this).attr('data-row');
          let table_id = $(this).attr('data-table-id');
          Drupal.SecurityGroup.showHide.hideRow('outbound-permission', row_count, table_id);
        });
      });

    }
  };
})(jQuery, Drupal);
