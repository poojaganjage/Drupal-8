(function ($, drupalSettings) {

  'use strict';

  if (!drupalSettings.k8s || !drupalSettings.k8s.metrics_enabled) {
    $('#k8s_node_heatmap').parent().parent().hide();
    return;
  }

  const FONT_FAMILY = 'Lucida Grande, -apple-system, BlinkMacSystemFont';
  const FONT_SIZE_AXIS_LABEL = 13;
  const RECT_PX = 50;
  const MAX_Y_AXIS_LABEL_STR_LENGTH = 30;
  const MAX_WIDTH = 750;

  // Read the data.
  let updateNodeHeatmap = function (pods_count) {
    d3.json(drupalSettings.k8s.resource_url).then(function (nodes) {

      let pods = [];
      let index = 0;
      let rows = 1;
      let node_name;
      let node_index = 1;

      d3.select('#k8s_node_heatmap')
        .append('svg')
        .style('width', '100%')
        .style('max-width', MAX_WIDTH + 'px');
      let offset_width = $('#k8s_node_heatmap svg').width();

      let pods_auto_count = Math.floor(offset_width / RECT_PX);
      if (!pods_count) {
        pods_count = pods_auto_count;
      }

      nodes.forEach(function (node) {

        if (node_name !== node.name) {
          node_name = node.name;
          node_index++;
        }

        for (let i = 0; i < node.podsCapacity; i++) {
          index++;
          if (index === parseInt(pods_count) + 1) {
            rows++;
            index = 1;
          }
          let pod_name = i < node.podsAllocation && node.pods[i]
            ? node.pods[i].name
            : '';

          let cpu_usage = i < node.podsAllocation && node.pods[i]
            // @TODO: "* 100 * 3" is an adjustment. Change the unit for the normal use.
            ? node.pods[i].cpuUsage * 100 * 3 + 20
            : 0;

          cpu_usage = Math.round(cpu_usage * 100) / 100;

          let memory_usage = i < node.podsAllocation && node.pods[i]
            ? Math.floor(node.pods[i].memoryUsage / 1024 / 1024)
            : '0';

          pods.push({
            index: index,
            node_index: node_index,
            row: rows,
            nodeName: node.name,
            name: pod_name,
            cpuUsage: cpu_usage,
            memoryUsage: memory_usage
          });
        }
      });

      let max_pods_capacity = index;
      if (rows > 1) {
        max_pods_capacity = parseInt(pods_count);
      }

      // Set the dimensions and margins of the graph.
      let pods_allocation_scale
        = [...Array(max_pods_capacity).keys()].map(i => ++i);

      let margin = {top: 0, right: 15, bottom: 20, left: 15};
      let width = max_pods_capacity * RECT_PX;
      let height = rows * RECT_PX;
      height = height < RECT_PX
        ? RECT_PX - margin.top / 4 - margin.bottom / 4
        : height;

      // * Important * Initialization the SVG object.  If you remove this code,
      // the SVG object will be duplicated repeatedly.
      $('#k8s_node_heatmap').empty();
      $('#k8s_node_heatmap').css('position', 'relative');
      $('#k8s_node_heatmap').css('text-align', 'center');
      $('#k8s_node_heatmap').append('<div id="pods_count_option">');
      // Add select list for pods count.
      $('#pods_count_option').css('font-size', 'x-small');
      $('#pods_count_option').css('text-align', 'right');
      $('#pods_count_option').append('<label for="pods_count">' + Drupal.t('Pods Count of Row') + '</label>');
      $('#pods_count_option').append('<select id="pods_count">');
      $('#pods_count').append('<option value="' + pods_auto_count + '">Auto</option>');
      $('#pods_count').append('<option value=10>10</option>');
      $('#pods_count').append('<option value=15>15</option>');
      $('#pods_count').append('<option value=20>20</option>');
      $('#pods_count').append('<option value=25>25</option>');
      $('#pods_count').append('<option value=50>50</option>');
      $('#pods_count').append('<option value=100>100</option>');
      $('#pods_count').val(pods_count);
      $('#pods_count option').css('font-size', 'x-small');
      $('#pods_count').change(function() {
         updateNodeHeatmap($(this).val());
       });

      // Append the svg object to the body of the page.
      let svg = d3.select('#k8s_node_heatmap')
        .append('svg')
        .attr('class', 'node_heatmap')
        .attr("viewBox", "0 0 " + (width + margin.left + margin.right) + " " + (height + margin.top + margin.bottom))
        .attr("preserveAspectRatio", "xMidYMid")
        .style('max-width', MAX_WIDTH + 'px')
        .append('g')
        .style('font-family', FONT_FAMILY)
        .attr('transform',
          'translate(' + margin.left + ', ' + margin.top + ')');

      // Build X scales and axis:
      let x = d3.scaleBand()
        .range([0, width])
        .domain(pods_allocation_scale)
        .padding(0.1);

      svg.append('g')
        .style('font-size', FONT_SIZE_AXIS_LABEL)
        .style('font-family', FONT_FAMILY)
        .attr('transform', 'translate(0,' + height + ')')
        .call(d3.axisBottom(x).tickSize(0))
        .select('.domain').remove();

      let y_axis_scale
        = [...Array(rows).keys()].map(i => ++i);
      y_axis_scale = y_axis_scale.reverse();

      // Build Y scales and axis:
      let y = d3.scaleBand()
        .range([height, 0])
        .domain(y_axis_scale)
        .padding(0.1);

      // Build color scale.
      let my_color_green = d3.scaleSequential(d3.interpolateGreens).domain([1, 100]);
      let my_color_blue = d3.scaleSequential(d3.interpolateBlues).domain([1, 100]);

      // Create a tooltip.
      let tooltip = d3.select('#k8s_node_heatmap')
        .append('div')
        .style('opacity', 0)
        .attr('class', 'tooltip')
        .style('background-color', 'white')
        .style('border', 'solid')
        .style('border-width', '2px')
        .style('border-radius', '5px')
        .style('padding', '5px');

      // Three function that change the tooltip when user hover / move / leave a cell.
      let mouseover = function (pod) {
        tooltip.style('opacity', 1);
        d3.select(this)
          .style('stroke', '#cc6600')
          .style('opacity', 1);
      };

      let mousemove = function (pod) {
        let client_rect = this.getBoundingClientRect();
        let div = document.getElementById("k8s_node_heatmap");
        let div_rect = div.getBoundingClientRect();
        tooltip
          .html('<strong>Node: ' + pod.nodeName + '<br/>Pod: ' + pod.name + '</strong><br />'
            + 'CPU: ' + pod.cpuUsage + ' %<br />'
            + 'Memory: ' + pod.memoryUsage + ' MiB')
          .style('display', 'block')
          .style('left', (client_rect.left - div_rect.left - client_rect.width / 2) + 'px')
          .style('top', (client_rect.top -div_rect.top + client_rect.height)+ 'px')
      };

      let mouseleave = function (pod) {
        tooltip.style('display', 'none');
        d3.select(this)
          .style('stroke', 'none')
          .style('opacity', 0.8);
      };

      // Add the squares.
      svg.selectAll('svg.node_heatmap')
        .exit()
        .remove()
        .data(pods, function (pod) {
          return pod.row + ':' + pod.index;
        })
        .enter()
        .append('rect')
        .attr('x', function (pod) {
          return x(pod.index);
        })
        .attr('y', function (pod) {
          return y(pod.row);
        })
        .attr('rx', 5)
        .attr('ry', 5)
        .attr('width', x.bandwidth())
        .attr('height', y.bandwidth())
        // 20: Adjust color.
        .style('fill', function (pod) {
          if (pod.node_index % 2 === 0) {
            return my_color_green(pod.cpuUsage + 20);
          }
          else {
            return my_color_blue(pod.cpuUsage + 20);
          }
        })
        .style('stroke-width', 4)
        .style('stroke', 'none')
        .style('opacity', 0.8)
        .on('mouseover', mouseover)
        .on('mousemove', mousemove)
        .on('mouseleave', mouseleave);
    });
  };

  updateNodeHeatmap();

  let interval = drupalSettings.k8s.k8s_js_refresh_interval || 10;
  setInterval(function () {
    let pods_count = $('#pods_count').val();
    updateNodeHeatmap(pods_count);
  }, interval * 1000);

}(jQuery, drupalSettings));
