(function ($) {
  'use strict';

  let generateChart = function(bindTo) {
    return c3.generate({
      bindto: bindTo,
      data: {
        x: 'x',
        xFormat: '%Y-%m-%dT%H:%M:%S+00:00',
        xLocaltime: false,
        columns: []
      },
      axis: {
        x: {
          type: 'timeseries',
          tick: {
            format: '%m-%d %H:%M:%S'
          }
        }
      }
    });
  };

  let updateCharts = function () {
    $.get('metrics', function (json) {
      cpu_chart.load({
        columns: [
          ['x'].concat(json.cpu.timestamps),
          ['cpu utilization'].concat(json.cpu.values),
        ],
      });

      network_chart.load({
        columns: [
          ['x'].concat(json.network_in.timestamps),
          ['network in'].concat(json.network_in.values),
          ['network out'].concat(json.network_out.values),
        ],
      });

      disk_chart.load({
        columns: [
          ['x'].concat(json.disk_read.timestamps),
          ['disk read'].concat(json.disk_read.values),
          ['disk write'].concat(json.disk_write.values),
        ],
      });

      disk_operation_chart.load({
        columns: [
          ['x'].concat(json.disk_read_operation.timestamps),
          ['disk read operation'].concat(json.disk_read_operation.values),
          ['disk write operation'].concat(json.disk_write_operation.values),
        ],
      });
    });
  };

  let cpu_chart = generateChart('#edit-cpu-chart');
  let network_chart = generateChart('#edit-network-chart');
  let disk_chart = generateChart('#edit-disk-chart');
  let disk_operation_chart = generateChart('#edit-disk-operation-chart');

  updateCharts();

  let interval = drupalSettings.aws_cloud_monitor_refresh_interval || 10;
  setInterval(function () {
    updateCharts();
  }, interval * 1000);

})(jQuery);
