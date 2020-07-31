(function ($) {
  'use strict';

  if (!drupalSettings.k8s || !drupalSettings.k8s.metrics_enabled) {
    $('#k8s_entity_metrics').parent().parent().hide();
    return;
  }

  let color = Chart.helpers.color;
  let formatMemory = function (value) {
    let mb = value / 1024 / 1024;
    mb = Math.round(mb);

    let gb = mb / 1024;
    gb = Math.round(gb * 100) / 100;

    if (gb >= 1) {
      return gb + ' Gi';
    }
    else {
      return mb + ' Mi';
    }
  };

  let updateCharts = function () {
    $.get(window.location.pathname + '/metrics', function (json) {
      let cpu_data = [];
      let memory_data = [];
      $.each(json, function () {
        cpu_data.push({
          t: this.timestamp * 1000,
          y: this.cpu
        });

        memory_data.push({
          t: this.timestamp * 1000,
          y: this.memory
        });
      });

      cpu_chart.data.datasets[0].data = cpu_data;
      cpu_chart.update();

      memory_chart.data.datasets[0].data = memory_data;
      memory_chart.update();
    });
  };

  // Build CPU chart.
  $('#k8s_entity_metrics').append('<div class="col-sm-6"><canvas id="cpu_chart"></canvas></div>');
  let cpu_ctx = document.getElementById('cpu_chart').getContext('2d');
  let cpu_cfg = {
    type: 'bar',
    data: {
      datasets: [{
        label: 'CPU Usage',
        backgroundColor: color('green').alpha(0.5).rgbString(),
        borderColor: 'green',
        data: [],
        type: 'line',
        pointRadius: 0,
        fill: false,
        lineTension: 0,
        borderWidth: 2
      }]
    },
    options: {
      scales: {
        xAxes: [{
          type: 'time',
          distribution: 'linear',
          bounds: 'ticks',
          ticks: {
            source: 'ticks',
            autoSkip: false
          }
        }],
        yAxes: [{
          scaleLabel: {
            display: true,
            labelString: 'CPU (Cores)'
          }
        }]
      },
      tooltips: {
        intersect: false,
        mode: 'index',
        callbacks: {
          label: function (tooltipItem, myData) {
            let label = myData.datasets[tooltipItem.datasetIndex].label || '';
            if (label) {
              label += ': ';
            }
            label += parseFloat(tooltipItem.value).toFixed(2);
            return label;
          }
        }
      }
    }
  };

  let cpu_chart = new Chart(cpu_ctx, cpu_cfg);

  // Build Memory chart.
  $('#k8s_entity_metrics').append('<div class="col-sm-6"><canvas id="memory_chart"></canvas></div>');
  let memory_ctx = document.getElementById('memory_chart').getContext('2d');
  let memory_cfg = {
    type: 'bar',
    data: {
      datasets: [{
        label: 'Memory Usage',
        backgroundColor: color('blue').alpha(0.5).rgbString(),
        borderColor: 'blue',
        data: [],
        type: 'line',
        pointRadius: 0,
        fill: false,
        lineTension: 0,
        borderWidth: 2
      }]
    },
    options: {
      scales: {
        xAxes: [{
          type: 'time',
          distribution: 'linear',
          bounds: 'ticks',
          ticks: {
            source: 'ticks',
            autoSkip: false
          }
        }],
        yAxes: [{
          scaleLabel: {
            display: true,
            labelString: 'Memory (Bytes)'
          },
          ticks: {
            callback: function (value, index, values) {
              return formatMemory(value);
            }
          }
        }]
      },
      tooltips: {
        intersect: false,
        mode: 'index',
        callbacks: {
          label: function (tooltipItem, myData) {
            let label = myData.datasets[tooltipItem.datasetIndex].label || '';
            if (label) {
              label += ': ';
            }
            label += formatMemory(tooltipItem.value);
            return label;
          }
        }
      }
    }
  };

  let memory_chart = new Chart(memory_ctx, memory_cfg);

  updateCharts();

  let interval = drupalSettings.k8s.k8s_js_refresh_interval || 10;
  setInterval(function () {
    updateCharts();
  }, interval * 1000);

})(jQuery);
