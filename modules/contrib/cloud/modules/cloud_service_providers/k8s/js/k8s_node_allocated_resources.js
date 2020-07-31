(function ($, drupalSettings) {

  'use strict';

  if (!drupalSettings.k8s || !drupalSettings.k8s.metrics_enabled) {
    $('#k8s_node_allocated_resources').parent().parent().hide();
    return;
  }

  const FONT_FAMILY = 'Lucida Grande, -apple-system, BlinkMacSystemFont';
  const FONT_SIZE_RSC_ALLOCATION_RATIO = 19;
  const FONT_SIZE_RSC_USAGE = 15;
  const FONT_SIZE_TITLE = 19;
  const WIDTH = 250;
  const HEIGHT = 250;
  const TITLE_HEIGHT = 20;
  const RING_WIDTH = 50;
  const RADIUS = Math.min(WIDTH, HEIGHT) / 2;
  const FOREGROUND_RING_WIDTH = 30;

  // Create a dummy <span>...</span> to measure the length (pixel) of a string.
  $('<span/>', {id: 'rsc_allocation_ratio_width_check', appendTo: 'body'})
      .css('font-size', FONT_SIZE_RSC_ALLOCATION_RATIO)
      .css('visibility', 'hidden')
      .css('position', 'absolute')
      .css('white-space', 'nowrap');

  $('<span/>', {id: 'rsc_usage_width_check', appendTo: 'body'})
      .css('font-size', FONT_SIZE_RSC_USAGE)
      .css('visibility', 'hidden')
      .css('position', 'absolute')
      .css('white-space', 'nowrap');

  $('<span/>', {id: 'title_width_check', appendTo: 'body'})
      .css('font-size', FONT_SIZE_TITLE)
      .css('visibility', 'hidden')
      .css('position', 'absolute')
      .css('white-space', 'nowrap');

  let pie = d3.pie()
      .sort(null).value(function (d) {
        return d.value;
      });

  let arc1 = d3.arc()
      .innerRadius(RADIUS - RING_WIDTH)
      .outerRadius(RADIUS);

  let arc = d3.arc()
      .innerRadius(RADIUS - RING_WIDTH - FOREGROUND_RING_WIDTH)
      .outerRadius(RADIUS - FOREGROUND_RING_WIDTH);

  function createSvg(data) {
    return d3.select(data.selector)
        .attr('align', 'center')
        .append('svg')
        .attr('class', data.class)
        .attr('width', WIDTH)
        .attr('height', HEIGHT + TITLE_HEIGHT)
        .style('display', 'inline');
  }

  // Create a tooltip.
  let tooltip = d3.select('body')
      .append('div')
      .style('opacity', 1)
      .attr('class', 'tooltip')
      .style('background-color', 'white')
      .style('border', 'solid')
      .style('border-width', '2px')
      .style('border-radius', '5px')
      .style('padding', '5px')
      .style('display', 'none');

  // Three function that change the tooltip when user hover / move / leave a cell.
  let mouseover = function (pie) {
    tooltip.style('display', 'block');

    d3.select(this)
        .style('stroke', pie.data.color)
        .style('stroke-width', 5)
        .style('opacity', 1);
  };

  let mousemove = function (pie) {
    tooltip.html('<strong>' + pie.value + ' ' + pie.data.suffix + '</strong>')
        .style('left', (d3.event.pageX + 15) + 'px')
        .style('top', (d3.event.pageY - 40) + 'px');
  };

  let mouseleave = function (pie) {
    tooltip.style('display', 'none');
    d3.select(this)
        .style('stroke', 'none')
        .style('opacity', 0.8);
  };

  let drawLegend = function () {
    let width = 600;
    let height = 50;
    let padding = 20;
    let xpadding = 90;
    let number = 20;
    let svg = d3.select('#k8s_node_allocated_resources')
        .append('svg')
        .style('max-width', width)
        .attr("preserveAspectRatio", "xMidYMid")
        .attr('viewBox', 0 + ' ' + 0 + ' ' + width + ' ' + height);

    svg.append('text')
        .attr('x', 10)
        .attr('y', 25)
        .style('font-family', FONT_FAMILY)
        .style('font-size', 16)
        .style('font-weight', 900)
        .text('Legend');

    let color = d3.interpolateSpectral;
    let defs = svg.append('defs')
        .append('linearGradient')
        .attr('id', 'legendGradient');

    for (let i = 0; i <= number; i++) {
      defs.append('stop')
          .attr('offset', (i * 100 / number) + '%')
          .attr('stop-color', color((number - i) / number));
    }

    svg.append('rect')
        .attr('x', xpadding)
        .attr('y', 0)
        .attr('width', width - 2 * xpadding)
        .attr('height', height - padding)
        .attr('fill', 'url(#legendGradient)');

    let xScale = d3.scaleLinear()
        .range([0, width - 2 * xpadding])
        .domain([0, 1.0]);

    let axis = svg.append('g')
        .attr('transform', 'translate(' + xpadding + ',' + (height - padding) + ')')
        .call(
          d3.axisBottom(xScale)
              .tickFormat(d3.format('.0%'))
        );
    axis.select('path')
        .attr('opacity', 0.0);
  };

  function render(data) {
    let svg = d3.select('svg.' + data.class);

    // The doughnut pie chart.
    svg.selectAll('.foreground')
        .data(pie(data.pieChart))
        .enter()
        .append('path')
        .attr('class', 'foreground')
        .attr('transform', 'translate(' + WIDTH / 2 + ',' + HEIGHT / 2 + ')')
        .attr('fill', function (d, i) {
          return d.data.color;
        })
        .on('mouseover', mouseover)
        .on('mousemove', mousemove)
        .on('mouseleave', mouseleave)
        .attr('d', arc)
        // Start transition.
        .transition()
        // Animate every one second.
        .duration(1000)
        // Change animation in the specified range.
        .attrTween('d', function (d) {
          let interpolate = d3.interpolate(
            // The start angle in each pie chart.
            {startAngle: 0, endAngle: 0},
            // The end angle of the each pie chart.
            {startAngle: d.startAngle, endAngle: d.endAngle}
          );
          return function (t) {
            // Process based on the time.
            return arc(interpolate(t));
          };
        });

    let rsc_usage_width_check_px = $('#rsc_usage_width_check')
        .text(data.resourceUsage).get(0).offsetWidth;
    let rsc_usage_height_check_px = $('#rsc_usage_width_check')
        .text(data.resourceUsage).get(0).offsetHeight;

    let rsc_allocation_ratio_width_check_px = $('#rsc_allocation_ratio_width_check')
        .text(data.resourceAllocationRatio).get(0).offsetWidth;
    let rsc_allocation_ratio_height_check_px = $('#rsc_allocation_ratio_width_check')
        .text(data.resourceAllocationRatio).get(0).offsetHeight;

    let title_width_check_px = $('#title_width_check')
        .text(data.title).get(0).offsetWidth;
    let title_height_check_px = $('#title_width_check')
        .text(data.title).get(0).offsetHeight;

    // Remove the <span/> element.
    $('#rsc_usage_width_check').empty();
    $('#rsc_allocation_ratio_width_check').empty();

    svg.selectAll('.allocation_ratio')
        .remove();

    svg.append('text')
        .attr('class', 'allocation_ratio')
        .attr('x', WIDTH / 2 + rsc_allocation_ratio_width_check_px / -2)
        .attr('y', HEIGHT / 2 + rsc_allocation_ratio_height_check_px / 2)
        // Or, we can locate a text like this:
        // .attr('transform', 'translate(' + (width / 2 + width_check_px / -2) + ',' + (height / 2 + height_check_px / 2) + ')')
        .style('font-family', FONT_FAMILY)
        .style('font-size', FONT_SIZE_RSC_ALLOCATION_RATIO)
        .style('font-weight', 900)
        .style('fill', d3.rgb(data.pieChart[0].color).darker(1))
        .text(data.resourceAllocationRatio);

    svg.selectAll('.resource_usage')
        .remove();

    svg.append('text')
        .attr('class', 'resource_usage')
        .attr('x', WIDTH / 2 + rsc_usage_width_check_px / -2)
        .attr('y', HEIGHT - rsc_usage_height_check_px * 2.5)
        .style('font-family', FONT_FAMILY)
        .style('font-size', FONT_SIZE_RSC_USAGE)
        .style('fill', d3.schemeSet3[8] + data.pieChart[0].color)
        .text(data.resourceUsage);

    // Add title.
    svg.selectAll('.chart_title')
        .remove();

    svg.append('text')
        .attr('class', '.chart_title')
        .attr('x', WIDTH / 2 + title_width_check_px / -2)
        .attr('y', HEIGHT - title_height_check_px + TITLE_HEIGHT)
        .style('font-family', FONT_FAMILY)
        .style('font-size', FONT_SIZE_TITLE)
        .style('font-weight', 900)
        .style('fill', d3.rgb(data.pieChart[0].color).darker(1))
        .text(data.title);
  }

  const selectors = [{
    selector: '#k8s_node_allocated_resources',
    class: 'k8s_node_cpu_usage'
  }, {
    selector: '#k8s_node_allocated_resources',
    class: 'k8s_node_memory_usage'
  }, {
    selector: '#k8s_node_allocated_resources',
    class: 'k8s_node_pods_allocation'
  }];

  let svgs = [];
  selectors.forEach(function (selector) {
    svgs.push(createSvg(selector));
  });

  let updateNodeAllocatedResources = function () {
    d3.json(drupalSettings.k8s.resource_url).then(function (nodes) {
      let cpu_capacity = 0;
      let cpu_request = 0;
      let memory_capacity = 0;
      let memory_request = 0;
      let pods_capacity = 0;
      let pods_allocation = 0;

      nodes.forEach(function (node) {
        cpu_capacity += parseFloat(node.cpuCapacity);
        cpu_request += parseFloat(node.cpuRequest);
        memory_capacity += parseFloat(node.memoryCapacity);
        memory_request += parseFloat(node.memoryRequest);
        pods_capacity += parseInt(node.podsCapacity);
        pods_allocation += parseInt(node.podsAllocation);
      });

      // CPU usage doughnut chart.
      let cpu_request_ratio = cpu_request / cpu_capacity;
      let cpu_request_rounded = cpu_request.toFixed(2);
      render({
        svg: svgs.pop(),
        selector: '#k8s_node_allocated_resources',
        class: 'k8s_node_cpu_usage',
        pieChart: [{
          color: d3.interpolateSpectral(1 - cpu_request_ratio),
          value: cpu_request_rounded,
          suffix: 'Cores'
        }, {
          color: d3.interpolateSpectral(1 - cpu_request_ratio - 0.15),
          value: (cpu_capacity - cpu_request_rounded).toFixed(2),
          suffix: 'Cores'
        }],
        resourceUsage: cpu_request_rounded + ' / ' +
            cpu_capacity + ' Cores',
        resourceAllocationRatio: (cpu_request_ratio * 100).toFixed(1) + '%',
        title: 'CPU Core Usage'
      });

      // Memory usage doughnut chart.
      let memory_request_ratio = memory_request / memory_capacity;
      let memory_request_rounded = (memory_request / 1024 / 1024 / 1024).toFixed(2);
      let memory_capacity_rounded = (memory_capacity / 1024 / 1024 / 1024).toFixed(2);
      render({
        svg: svgs.pop(),
        selector: '#k8s_node_allocated_resources',
        class: 'k8s_node_memory_usage',
        pieChart: [{
          color: d3.interpolateSpectral(1 - memory_request_ratio),
          value: memory_request_rounded,
          suffix: 'GiB'
        }, {
          color: d3.interpolateSpectral(1 - memory_request_ratio - 0.15),
          value: (memory_capacity_rounded - memory_request_rounded).toFixed(2),
          suffix: 'GiB'
        }],
        resourceUsage:
            memory_request_rounded + ' / ' +
            memory_capacity_rounded + ' GiB',
        resourceAllocationRatio: (memory_request_ratio * 100).toFixed(1) + '%',
        title: 'Memory Usage'
      });

      // Pods allocation doughnut chart.
      let pods_allocation_ratio = pods_allocation / pods_capacity;
      render({
        svg: svgs.pop(),
        selector: '#k8s_node_allocated_resources',
        class: 'k8s_node_pods_allocation',
        pieChart: [{
          color: d3.interpolateSpectral(1 - pods_allocation_ratio),
          value: pods_allocation,
          suffix: 'Pods'
        }, {
          color: d3.interpolateSpectral(1 - pods_allocation_ratio - 0.15),
          value: pods_capacity - pods_allocation,
          suffix: 'Pods'
        }],
        resourceUsage: pods_allocation + ' / ' + pods_capacity + ' Pods',
        resourceAllocationRatio: (pods_allocation_ratio * 100).toFixed(1) + '%',
        title: 'Pods Allocation'
      });
    });
  };

  updateNodeAllocatedResources();
  drawLegend();

  d3.select('body')
      .style('position', 'static');

  let interval = drupalSettings.k8s.k8s_js_refresh_interval || 10;
  setInterval(function () {
    updateNodeAllocatedResources();
  }, interval * 1000);

}(jQuery, drupalSettings));
