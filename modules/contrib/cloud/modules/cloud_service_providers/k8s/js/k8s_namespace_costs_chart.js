(function ($) {
  'use strict';

  let color = d3.interpolateSpectral;
  if (!drupalSettings.k8s || !drupalSettings.k8s.k8s_namespace_costs_chart_json_url) {
    return;
  }

  let cost_type = null;
  let chart_period = null;
  if (drupalSettings.k8s.k8s_namespace_costs_chart_ec2_cost_type) {
    cost_type = drupalSettings.k8s.k8s_namespace_costs_chart_ec2_cost_type;
  }
  if (drupalSettings.k8s.k8s_namespace_costs_chart_period) {
    chart_period = drupalSettings.k8s.k8s_namespace_costs_chart_period;
  }
  else {
    chart_period = 1;
  }
  $('#k8s_namespace_costs_chart').append('<div id="chart_options">');
  $('#chart_options').css('font-size', 'x-small');
  $('#chart_options').css('text-align', 'right');
  $('#chart_options').append('<span>');
  $('#chart_options').append('<span>');
  if (drupalSettings.k8s.k8s_namespace_costs_cost_types_json_url) {
    let cost_types_json_url = drupalSettings.k8s.k8s_namespace_costs_cost_types_json_url;
    $.get(cost_types_json_url, function (json) {
      $('#chart_options > span:first-child').append('<label for="cost_type">' + Drupal.t('Cost Type') + '</label>');
      $('#chart_options > span:first-child').append('<select id="cost_type">');
      $.each(json, function (index, value) {
        $('#cost_type').append('<option value="' + index + '">' + value + '</option>');
      });
      $('#cost_type option').css('font-size', 'x-small');
      if (cost_type) {
        $('#cost_type').val(cost_type);
      }
      $('#cost_type').change(function() {
        updateChart();
      });
    });
  }
  if (drupalSettings.k8s.k8s_namespace_costs_chart_periods_json_url) {
    let chart_periods_json_url = drupalSettings.k8s.k8s_namespace_costs_chart_periods_json_url;
    $.get(chart_periods_json_url, function (json) {
      $('#chart_options > span:last-child').append('<label for="chart_period">' + Drupal.t('Chart Period') + '</label>');
      $('#chart_options > span:last-child label').css('margin-left', '10px');
      $('#chart_options > span:last-child').append('<select id="chart_period">');
      $.each(json, function (index, value) {
        $('#chart_period').append('<option value="' + index + '">' + value + '</option>');
      });
      $('#chart_period option').css('font-size', 'x-small');

      if (chart_period && $('select#chart_period option[value="' + chart_period + '"]').length > 0) {
        $('#chart_period').val(chart_period);
      }
      else if ($('select#chart_period option[value="1"]').length > 0) {
        $('#chart_period').val(1);
      }
      $('#chart_period').change(function() {
       updateChart();
      });
    });
  }

  let json_url = drupalSettings.k8s.k8s_namespace_costs_chart_json_url;

  let initChart = function () {
    let init_json_url = json_url + '?' + 'cost_type=' + cost_type + '&period=' + chart_period;
    d3.json(init_json_url).then(function (json) {
      cost_chart.data.datasets = [];
      let count = json.length;
      let idx = 1;
      $.each(json, function () {
        let color = d3.interpolateRainbow(idx / count);
        let rgb = d3.rgb(color);
        rgb.opacity = 0.5;
        let namespace = this.namespace;
        let cost_data = [];
        $.each(this.costs, function () {
          cost_data.push({
            t: this.timestamp * 1000,
            y: this.cost
          });
        });
        cost_chart.data.datasets.push({
          label: namespace,
          backgroundColor: rgb.brighter(1),
          borderColor: color,
          data: cost_data,
          type: 'line',
          pointRadius: 0,
          fill: false,
          lineTension: 0,
          borderWidth: 2
        });
        idx++;
      });
      cost_chart.update();
    });
  };

  let updateChart = function () {
    let update_json_url = json_url + '?' + 'cost_type=' + $('#cost_type').val() + '&period=' + $('#chart_period').val();
    d3.json(update_json_url).then(function (json) {
      let idx = 0;
      $.each(json, function () {
        let cost_data = [];
        $.each(this.costs, function () {
          cost_data.push({
            t: this.timestamp * 1000,
            y: this.cost
          });
        });
        cost_chart.data.datasets[idx].data = cost_data;
        idx++;
      });
      cost_chart.update();
    });
  };

  // Build CPU chart.
  $('#k8s_namespace_costs_chart').append('<div class="col-sm-9" style="float: none; margin: 0 auto"><canvas id="cost_chart"></canvas></div>');
  let cost_ctx = document.getElementById('cost_chart').getContext('2d');
  let cost_cfg = {
    type: 'bar',
    options: {
      scales: {
        xAxes: [{
          type: 'time',
          time: {
            unit: 'day',
            displayFormats: {
              hour: 'MMM D hA'
            }
          },
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
            labelString: 'Cost ($)'
          }
        }]
      },
      tooltips: {
        intersect: false,
        mode: 'nearest',
        callbacks: {
          label: function (tooltipItem, myData) {
            let label = myData.datasets[tooltipItem.datasetIndex].label || '';
            if (label) {
              label += ': ';
            }
            label += '$';
            label += parseFloat(tooltipItem.value).toFixed(2);
            return label;
          }
        }
      }
    }
  };

  let cost_chart = new Chart(cost_ctx, cost_cfg);

  initChart();

  let interval = drupalSettings.k8s.k8s_js_refresh_interval || 10;
  setInterval(function () {
    updateChart();
  }, interval * 1000);

})(jQuery);
