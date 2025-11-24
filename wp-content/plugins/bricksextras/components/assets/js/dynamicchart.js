function xDynamicChart() {

    function updateChartColors(chart, propertiesToUpdate, config, queryLoop) {
 
        function getCSSVariable(variableName) {
            const value = getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
            return value;
        }
  
        // Process dataPointColorsRaw separately first
        if (config.dataPointColorsRaw && Array.isArray(config.dataPointColorsRaw)) {
            const colors = config.dataPointColorsRaw.map(item => {
                // Handle CSS variables from color picker
                if (item.color?.raw?.startsWith('var')) {
                    const variableName = item.color.raw.replace(/^var\(([^)]+)\)$/, '$1');
                    return getCSSVariable(variableName) || '#222';
                }
                // Handle hex colors from color picker
                if (item.color?.hex) {
                    return item.color.hex;
                }
                // Handle direct color values (from dynamic data)
                if (typeof item === 'string') {
                    return item;
                }
                return '#222';
            });
        
            if (chart.data.datasets && chart.data.datasets.length > 0) {
                chart.data.datasets.forEach((dataset, index) => {
                    if (config.config.loopDataPoints) {
                        dataset.backgroundColor = colors;
                        dataset.borderColor = colors;
                        if (config.config.chartType === 'line') {
                            dataset.pointBackgroundColor = colors;
                            dataset.pointBorderColor = colors;
                        }
                    }
                });
            }
        }
 
        // Process dataSetColorsRaw if it exists
        if (config.dataSetColorsRaw && Array.isArray(config.dataSetColorsRaw)) {
            const colors = config.dataSetColorsRaw.map(item => {
                // Handle CSS variables from color picker
                if (item.color?.raw?.startsWith('var')) {
                    const variableName = item.color.raw.replace(/^var\(([^)]+)\)$/, '$1');
                    const color = getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
                    return color;
                }
                // Handle hex colors from color picker
                if (item.color?.hex) {
                    return item.color.hex;
                }
                // Handle direct color values (from dynamic data)
                if (typeof item === 'string') {
                    return item;
                }
                return '#222';
            });
 
            if (chart.data.datasets && chart.data.datasets.length > 0 && queryLoop) {
                chart.data.datasets.forEach((dataset, index) => {
                    const color = colors[index % colors.length];
                    dataset.backgroundColor = color;
                    dataset.borderColor = color;
                });
            } else {
            }
        } else {
        }
 
        // Process backgroundColorArrayRaw if it exists
        if (config.backgroundColorArrayRaw && Array.isArray(config.backgroundColorArrayRaw)) {
            const colors = config.backgroundColorArrayRaw.map(item => {
                // Handle CSS variables from color picker
                if (item.color?.raw?.startsWith('var')) {
                    const variableName = item.color.raw.replace(/^var\(([^)]+)\)$/, '$1');
                    return getCSSVariable(variableName) || '#222';
                }
                // Handle hex colors from color picker
                if (item.color?.hex) {
                    return item.color.hex;
                }
                // Handle direct color values (from dynamic data)
                if (typeof item === 'string') {
                    return item;
                }
                return '#222';
            });
        
            if (chart.data.datasets && chart.data.datasets.length > 0) {
                chart.data.datasets.forEach((dataset, index) => {
                    if (!queryLoop && !config.config.loopDataPoints) {
                        dataset.backgroundColor = colors;
                    }
                });
            }
        }
 
        // 2. Enhanced updateChartProperty function
        function updateChartProperty(obj, property) {
            // Map properties to config values
            const propertyMap = {
                'scales.x.ticks.color': 'xAxisTicksColor',
                'scales.y.ticks.color': 'yAxisTicksColor',
                'scales.x.title.color': 'xAxisTitleColor',
                'scales.y.title.color': 'yAxisTitleColor',
                'scales.x.grid.borderColor': 'xBorderColor',
                'scales.y.grid.borderColor': 'yBorderColor',
                'scales.x.grid.color': 'xGridColor',
                'scales.y.grid.color': 'yGridColor',
                'datasets.backgroundColor': 'backgroundColorArrayRaw',
                'plugins.tooltip.backgroundColor': 'tooltipBackground',
                'plugins.tooltip.titleColor': 'tooltipColor',
                'plugins.tooltip.bodyColor': 'tooltipColor',
                'plugins.tooltip.borderColor': 'tooltipBorderColor',
                'plugins.datalabels.color': 'dataLabelsColor',
                'plugins.datalabels.backgroundColor': 'dataLabelsBackgroundColor',
                'plugins.datalabels.borderColor': 'dataLabelsBorderColor',
                'plugins.legend.labels.color': 'legendColor'
            };
 
            const configKey = propertyMap[property];
            
            // Special handling for backgroundColorArrayRaw
            if (configKey === 'backgroundColorArrayRaw' && config.config[configKey]) {
                const colors = config.config[configKey].map(item => {
                    if (item.color?.raw?.startsWith('var')) {
                        const variableName = item.color.raw.replace(/^var\(([^)]+)\)$/, '$1');
                        return getCSSVariable(variableName) || '#222';
                    }
                    return item.color?.hex || '#222';
                });
                
                if (chart.data.datasets && chart.data.datasets.length > 0) {
                    chart.data.datasets.forEach((dataset, index) => {
                        const color = colors[index % colors.length];
                        dataset.backgroundColor = color;
                    });
                }
                return;
            }
 
            // Regular property handling
            if (!configKey || !config?.config?.[configKey]?.raw) {
                return;
            }
        
            // Extract CSS variable name
            const rawValue = config.config[configKey].raw;
            if (!rawValue.startsWith('var')) {
                return; // Skip if not a CSS variable
            }
 
            const variableName = rawValue.replace(/^var\(([^)]+)\)$/, '$1');
            const colorValue = getCSSVariable(variableName) || '#222';
        
            // Safely update nested properties
            const path = property.split('.');
            let target = obj;
            
            // First check if the entire path exists
            try {
                for (const p of path.slice(0, -1)) {
                    if (!target[p]) {
                        return; // Don't modify if path doesn't exist
                    }
                    target = target[p];
                }
        
                const finalProp = path[path.length - 1];
                target[finalProp] = colorValue;
            } catch (error) {
            }
        }
  
        propertiesToUpdate.forEach(property => {
            updateChartProperty(chart.config.options, property);
            updateChartProperty(chart.data, property);
        });
  
        chart.update(); // Force chart re-render
    }
 
    function getColorFromBricks(colorSetting) {
 
        let color
 
        if (!colorSetting) {
            return '';
        }
 
        if (  colorSetting.color !== undefined )  {
 
            if ( colorSetting.color.raw && colorSetting.color.raw.startsWith("var") ) {
                color = getComputedStyle(document.documentElement).getPropertyValue( colorSetting.color.raw.slice(4, -1) ).trim()
            } 
            else {
                color = colorSetting.color.hex
            }
 
        } else if ( colorSetting.startsWith("var") ) {
 
            color = getComputedStyle(document.documentElement).getPropertyValue( colorSetting.slice(4, -1) ).trim()
        }
        
        else {
            color = colorSetting;
        }
 
        return color;
    }
 
    function getColor(colorSetting, defaultColor) {
 
        if ( colorSetting.rgb ) {
            color = colorSetting.rgb
        }
        else if ( colorSetting.hex ) {
            color = colorSetting.hex
        }
        else if ( colorSetting.raw ) {
            if (colorSetting.raw.startsWith("var")) {
                color = getComputedStyle(document.documentElement).getPropertyValue( colorSetting.raw.slice(4, -1) ).trim()
            } else {
                color = colorSetting.raw
            }
        }
        else {
            color = defaultColor
        }
 
        return color;
 
 
    }
 
    const extrasChart = function ( container ) {
        
        container.querySelectorAll('.brxe-xdynamicchart').forEach((dynamicChart) => {
 
            const table = dynamicChart.querySelector('table')
 
            if (table) {
 
            const config = table && table.getAttribute('data-x-chart') ? JSON.parse(table.getAttribute('data-x-chart')) : {}
 
            let lablesArray = [];
            let dataSetArray = [];
            let dataSetColors = [];
            let dataSetColorsRaw = [];
            let dataPointColors = [];
            let dataPointColorsRaw = [];
            let queryLoop = false;
            let tickValue;
            let backgroundColorArray = [];
            let backgroundColorArrayRaw = []
 
            const headerLabels = table.querySelectorAll('th');
 
            headerLabels.forEach(function(headerLabel) {
                lablesArray.push( headerLabel.innerHTML )
            })
 
            /* dataset colors */
 
            if (!config.loopDataPoints) {
                if ( dynamicChart.querySelectorAll('td.legend[data-set-color]').length > 0 ) {
 
                    const dataSetStyles = dynamicChart.querySelectorAll('td.legend')
 
                    if ( dataSetStyles.length > 0 ) {
                        queryLoop = true;
 
                        dataSetStyles.forEach((dataSetStyle) => {
                            if ( dataSetStyle.getAttribute('data-set-color') !== undefined )  {
                                //dataSetColors.push( dataSetStyle.getAttribute('data-set-color') )
                                dataSetColors.push( getColorFromBricks( dataSetStyle.getAttribute('data-set-color') ) )
                                dataSetColorsRaw.push( dataSetStyle.getAttribute('data-set-color') )
                            }
                        })
                    }
 
                } else {
 
                    let dataSetStyles = dynamicChart.querySelectorAll('li[data-x-chart-style]')
 
                    if ( dataSetStyles.length > 0 ) {
                        queryLoop = true;
 
                        dataSetStyles.forEach((dataSetStyle,index) => {
                            if ( JSON.parse(dataSetStyle.getAttribute('data-x-chart-style')).color !== undefined )  {
                               // dataSetColors.push( JSON.parse(dataSetStyle.getAttribute('data-x-chart-style')).color.hex )
                                dataSetColors.push( getColorFromBricks( JSON.parse(dataSetStyle.getAttribute('data-x-chart-style')) ) )
                                dataSetColorsRaw.push( JSON.parse(dataSetStyle.getAttribute('data-x-chart-style')) )
 
                            }
                        })
                    }
                }
            } else {
 
                if ( dynamicChart.querySelectorAll('td[data-point-color]').length > 0 ) {
 
                    const dataPointStyles = dynamicChart.querySelectorAll('td[data-point-color]')
 
                    if ( dataPointStyles.length > 0 ) {
 
                        dataPointStyles.forEach((dataPointStyle) => {
                            if ( dataPointStyle.getAttribute('data-point-color') !== undefined )  {
 
                                dataPointColors.push( getColorFromBricks( dataPointStyle.getAttribute('data-point-color') ) )
                                dataPointColorsRaw.push( dataPointStyle.getAttribute('data-point-color') )
                            }
                        })
                    }
 
                } else {
 
                    let dataSetStyles = dynamicChart.querySelectorAll('li[data-x-chart-style]')
 
                    if ( dataSetStyles.length > 0 ) {
 
                        dataSetStyles.forEach((dataSetStyle,index) => {
                            if ( JSON.parse(dataSetStyle.getAttribute('data-x-chart-style')).color !== undefined )  {
                                dataPointColors.push( getColorFromBricks( JSON.parse(dataSetStyle.getAttribute('data-x-chart-style') ) ) )
                                dataPointColorsRaw.push( JSON.parse(dataSetStyle.getAttribute('data-x-chart-style') ) )
                            }
                            
                        })
                    }
 
                }
 
            }
 
            const tableRows = table.querySelectorAll('tbody tr')
 
            if ( tableRows.length ) {
                tableRows.forEach((tableRow,index) => {
 
                    const tableRowDatas = tableRow.querySelectorAll('td:not(.legend)');
                    const tableRowLegend = tableRow.querySelector('td.legend');
                    
                    var tableDataArray = [];
 
                    tableRowDatas.forEach((tableRowData) => {
                        tableDataArray.push( tableRowData.innerHTML )
                        if (!queryLoop && null != tableRowData.getAttribute('data-x-chart-style')) {
 
                            backgroundColorArray.push( getColorFromBricks( JSON.parse(tableRowData.getAttribute('data-x-chart-style') ) ) )
                            backgroundColorArrayRaw.push( JSON.parse(tableRowData.getAttribute('data-x-chart-style') ) )
                            
                        }
                    })
 
                    dataSetArray.push(
                        {
                            label: tableRowLegend ? tableRowLegend.innerHTML : '',
                            data: tableDataArray,
                            backgroundColor: queryLoop ? dataSetColors[index] : config.loopDataPoints ? dataPointColors : backgroundColorArray,
                            borderColor: queryLoop ? dataSetColors[index] : 'line' === config.chartType ? getColor( config.lineColor, '#44889c' ) : getColor( config.barBorderColor, '#44889c' ),
                            
                        }
                    )
 
                })
 
            }
 
            if ( dynamicChart.querySelector('table') && !document.querySelector('body > .brx-body.iframe') ) {
                dynamicChart.querySelector('table').remove();
              }  
 
              if ( dynamicChart.querySelector('.x-dynamic-chart_list') ) {
               dynamicChart.querySelector('.x-dynamic-chart_list').remove();
              }
 
            if ( dynamicChart.querySelector('canvas') ) {
                dynamicChart.querySelector('canvas').remove();
            }
 
            if ( ! tableRows.length && !document.querySelector('body > .brx-body.iframe') ) {
                return;
            }
 
             // Helper function to format typography settings for Chart.js
             function formatTypography(typography) {
                if (!typography || Array.isArray(typography)) return {};
                
                // Ensure we're getting valid values and handle font-family specially
                const formattedFont = {
                    family: typography['font-family'] ? String(typography['font-family']) : undefined,
                    weight: typography['font-weight'] ? String(typography['font-weight']) : undefined,
                    style: typography['font-style'] || undefined,
                    lineHeight: typography['line-height'] ? parseFloat(typography['line-height']) : undefined,
                    size: typography['font-size'] ? parseInt(typography['font-size']) : undefined
                };
 
                // Remove undefined values
                Object.keys(formattedFont).forEach(key => 
                    formattedFont[key] === undefined && delete formattedFont[key]
                );
 
                return formattedFont;
            }
 
            let scaleX = {
                display: 'false' != config.xAxisDisplay,
                //stacked: true,
                title: {
                  display: true,
                  text: config.xAxisTitle ? config.xAxisTitle : '',
                  //color: config.xAxisTitleColor.rgb ? config.xAxisTitleColor.rgb : ( config.xAxisTitleColor.hex ? config.xAxisTitleColor.hex : '#222' ),
                  color: getColor( config.xAxisTitleColor, '#222' ),
                  font: formatTypography(config.xAxisFont || defaultTypography)
                },
                ticks: {
                    //color: config.xAxisTicksColor.rgb ? config.xAxisTicksColor.rgb : ( config.xAxisTicksColor.hex ? config.xAxisTicksColor.hex : '#222' ),
                    color: getColor( config.xAxisTicksColor, '#222' ),
                    font: formatTypography(config.xAxisTicksFont || defaultTypography)
                },
                grid: {
                    drawBorder: true,
                    //borderColor: config.xBorderColor.rgb ? config.xBorderColor.rgb : ( config.xBorderColor.hex ? config.xBorderColor.hex : '#222' ),
                    borderColor: getColor( config.xBorderColor, '#222' ),
                    lineWidth: 1,
                    //color: config.xGridColor.rgb ? config.xGridColor.rgb : ( config.xGridColor.hex ? config.xGridColor.hex : '#eee' )
                    color: getColor( config.xGridColor, '#eee' )
                },
                drawTicks: false,
              };
 
              let scaleY = {
                display: 'false' != config.yAxisDisplay,
                title: {
                  display: true,
                  text: config.yAxisTitle ? config.yAxisTitle : '',
                  //color: config.yAxisTitleColor.rgb ? config.yAxisTitleColor.rgb : ( config.yAxisTitleColor.hex ? config.yAxisTitleColor.hex : '#222' ),
                  color: getColor( config.yAxisTitleColor, '#222' ),
                  font: formatTypography(config.yAxisFont || defaultTypography)
                },
                ticks: {
                    //color: config.yAxisTicksColor.rgb ? config.yAxisTicksColor.rgb : ( config.yAxisTicksColor.hex ? config.yAxisTicksColor.hex : '#222' ),
                    color: getColor( config.yAxisTicksColor, '#222' ),
                    font: formatTypography(config.yAxisTicksFont || defaultTypography)
                },
                grid: {
                    drawBorder: true,
                    //borderColor: config.yBorderColor.rgb ? config.yBorderColor.rgb : ( config.yBorderColor.hex ? config.yBorderColor.hex : '#222' ),
                    borderColor: getColor( config.yBorderColor, '#222' ),
                    lineWidth: 1,
                    //color: config.yGridColor.rgb ? config.yGridColor.rgb : ( config.yGridColor.hex ? config.yGridColor.hex : '#eee' )
                    color: getColor( config.yGridColor, '#eee' )
                },
              };
 
              let yAxisOptions = {
                type: config.yAxisType,
                beginAtZero: 'true' == config.beginAtZero,
                suggestedMin: config.suggestedMin,
                suggestedMax: config.suggestedMax
             };
 
             if ('after' === config.xAxisUnitPosition) {
                tickValue = value => `${value}` + config.xAxisUnits
             } else {
                tickValue = value => config.xAxisUnits + `${value}`
             }
 
 
            if ( 'vertical' === config.chartDirection ) {
                scaleY = Object.assign({}, scaleY, yAxisOptions);
                scaleY['ticks']['callback'] = tickValue
            } else {
               scaleX = Object.assign({}, scaleX, yAxisOptions);
               scaleX['ticks']['callback'] = tickValue
            }
 
            if ( 'true' === xChart.RTL ) {
                scaleX['reverse'] = true
            }
 
            if ( 'true' === xChart.RTL ) {
                scaleY['position'] = 'right'
            }
 
            var canvas = document.createElement('canvas');
                canvas.setAttribute('aria-label', config.ariaLabel);
                canvas.setAttribute('role', 'img');
                dynamicChart.appendChild(canvas) 
 
                var ctx = canvas.getContext('2d');

                // Parse aspect ratio - convert "3/1" string to number
                let aspectRatio = config.chartType === 'doughnut' ? 1 : 2; // default: 1/1 for doughnut, 2/1 for others
                if (config.aspectRatio) {
                    if (typeof config.aspectRatio === 'string' && config.aspectRatio.includes('/')) {
                        const parts = config.aspectRatio.split('/');
                        aspectRatio = parseFloat(parts[0]) / parseFloat(parts[1]);
                    } else {
                        aspectRatio = parseFloat(config.aspectRatio);
                    }
                } 
 
                let chartConfig = {
                    type: config.chartType,
                    data: {
                        labels: lablesArray,
                        datasets: dataSetArray,
                    },
                    resizeDelay: 250,
                    options: { 
                      aspectRatio: aspectRatio,  
                      font: formatTypography(config.chartTypography),
                      indexAxis: 'vertical' !== config.chartDirection ? 'y' : 'x',
                      events: 'click' != config.events ? ['mousemove', 'mouseout', 'click', 'touchstart', 'touchmove'] : ['click', 'mouseout'],
                      elements: {
                          line: {
                              borderColor: getColor( config.lineColor, '#44889c' ),
                              borderWidth: config.lineWidth ? config.lineWidth : 2,
                              tension: config.tension,
                          },
                          point: {
                              radius: config.linePointRadius,
                              pointBorderWidth: config.linePointBorderWidth ? config.linePointBorderWidth : 1,
                              borderWidth: 0,
                          },
                          bar: {
                              borderColor: getColor( config.barBorderColor, '#44889c' ),
                              borderRadius: config.barBorderRadius,
                              borderWidth: config.barBorderWidth ? config.barBorderWidth : 0,
                          },
                          arc: {
                              borderWidth: config.pieBorderWidth ? config.pieBorderWidth : 0,
                          },
                      },
                      spanGaps: true,
                      scales: {
                          x: scaleX,
                          y: scaleY
                      },
                      interaction: {
                          mode: 'index',
                          intersect: false,
                      },
                      animations: {
                          tension: {
                            duration: 0,
                          },
                          radius: {
                              duration: 0,
                          },
                          borderWidth: {
                              duration: 0,
                          },
                          x: {
                              duration: 0,
                          },
                          y: {
                              duration: 0,
                          },
                        },
                      plugins: {
                          legend: {
                              display: 'false' != config.legendDisplay,
                              position: config.legendPosition ? config.legendPosition : 'top',
                              align: config.legendAlign ? config.legendAlign : 'center',
                              labels: {
                                  boxWidth: config.legendBoxWidth ? config.legendBoxWidth : 15,
                                  boxHeight: config.legendBoxHeight ? config.legendBoxHeight : 12,
                                  padding: config.legendPadding ? config.legendPadding : 10,
                                  color: getColor( config.legendColor, '#666' ),
                                  font: function(context) {
                                    // Start with default typography
                                    const defaultFont = formatTypography(config.chartTypography);
                                    // Merge with specific legend font if available
                                    const legendFont = formatTypography(config.legendFont);
                                    
                                    return {
                                        ...defaultFont,
                                        ...legendFont
                                    };
                                },
                              },
                          },
                          tooltip: {
                             enabled: 'false' != config.tooltipDisplay,
                             padding: 20,
                             backgroundColor: getColor( config.tooltipBackground, '#fff' ),
                             titleColor: getColor( config.tooltipColor, '#111' ),
                             bodyColor: getColor( config.tooltipColor, '#222' ),
                             borderColor: getColor( config.tooltipBorderColor, '#222' ),
                             borderWidth: config.tooltipBorderWidth ? config.tooltipBorderWidth : 1,
                             caretSize: config.tooltipCaretSize ? config.tooltipCaretSize : 5,
                             caretPadding: 10,
                             cornerRadius: 10,
                             displayColors: false,
                                titleFont: {
                                    ...formatTypography(config.chartTypography),
                                    ...formatTypography(config.tooltipTitleFont)
                                },
                                bodyFont: {
                                    ...formatTypography(config.chartTypography),
                                    ...formatTypography(config.tooltipBodyFont)
                                },
                             callbacks: {
                                  label: function(context) {
                                      let label = context.dataset.label || '';
                                      
                                      if (label) {
                                          label += ': ';
                                      }
  
                                      let parsedContext;
  
                                      if ( 'doughnut' === config.chartType ) {
                                          parsedContext = context.parsed
                                      } else if ( 'vertical' === config.chartDirection ) {
                                          parsedContext = context.parsed.y
                                      }  else {
                                          parsedContext = context.parsed.x
                                      } 
  
                                      if (context.parsed !== null) {
                                          if ('after' == config.xAxisUnitPosition) {
                                              label += parsedContext + config.xAxisUnits;
                                          } else {
                                              label += config.xAxisUnits + parsedContext;
                                          }
                                      }
                                      
                                      return label;
                                  }
                              }
                          },
                      },
                      rotation: config.rotation,
                      circumference: config.circumference,
                      cutout: config.pieCutOut ? config.pieCutOut.toString() + '%' : 0,
                      layout: {
                        padding: {
                            top: config.spacingTop ? config.spacingTop : 0,
                            right: config.spacingRight ? config.spacingRight : 0,
                            bottom: config.spacingBottom ? config.spacingBottom : 0,
                            left: config.spacingLeft ? config.spacingLeft : 0
                        }
                     }
                    }
                };
 
                if ( config.dataLabelsDisplay ) {
 
                    chartConfig.plugins = [ChartDataLabels]
 
                    chartConfig.options.plugins.datalabels = {
                        display: true,
                        backgroundColor: function(context) {
                            return getColor( config.dataLabelsBackgroundColor, context.dataset.backgroundColor )
                        },
                        borderRadius: 4,
                        color: getColor( config.dataLabelsColor, '#ffffff' ),
                        anchor: config.dataLabelsAnchor,
                        align: config.dataLabelsAlign,
                        offset: config.dataLabelsOffset,
                        padding: config.dataLabelsPadding ? config.dataLabelsPadding : '1em',
                        borderColor: function(context) {
                            return getColor( config.dataLabelsBorderColor, context.dataset.backgroundColor )
                        },
                        borderWidth: config.dataLabelsBorderWidth,
                        borderRadius: config.dataLabelsBorderRadius,
                        font: {
                            family: config.dataLabelsFontFamily['font-family'] ? config.dataLabelsFontFamily['font-family'] : 'inherit',
                            style: config.dataLabelsFontFamily['font-style'] ? config.dataLabelsFontFamily['font-style'] : 'inherit',
                            lineHeight: config.dataLabelsFontFamily['line-height'] ? config.dataLabelsFontFamily['line-height'] : 'inherit',
                            size: config.dataLabelsFontSize ? config.dataLabelsFontSize : '12px',
                            weight: config.dataLabelsFontWeight ? config.dataLabelsFontWeight : 'normal'
                        },
                        textAlign: "center",
                        rotation: config.dataLabelsRotation,
                        formatter: (value, context) => {
                            return 'after' === config.xAxisUnitPosition ? value + config.xAxisUnits : config.xAxisUnits + value
                        },
                      }
                }
 
                var myChart = new Chart(ctx, chartConfig );
 
                if ( window.xChart && window.xChart.Config ){ 
 
                    window.xChart.Instances[dynamicChart.dataset.xId] = myChart;
                    window.xChart.Config[dynamicChart.dataset.xId] = {
                        config,
                        dataPointColorsRaw,
                        backgroundColorArrayRaw,
                        dataSetColorsRaw,
                        queryLoop
                    }
                }
 
            }
 
            else {
 
                if ( window.xChart && window.xChart.Config ){
                
                    const chartInstance = window.xChart.Instances[dynamicChart.dataset.xId];
                    const chartConfig = window.xChart.Config[dynamicChart.dataset.xId];
 
                    // 1. Create mapping of properties to their config keys
                    const propertyConfigMap = {
                        'scales.x.ticks.color': 'xAxisTicksColor',
                        'scales.y.ticks.color': 'yAxisTicksColor',
                        'scales.x.title.color': 'xAxisTitleColor',
                        'scales.y.title.color': 'yAxisTitleColor',
                        'scales.x.grid.borderColor': 'xBorderColor',
                        'scales.y.grid.borderColor': 'yBorderColor',
                        'scales.x.grid.color': 'xGridColor',
                        'scales.y.grid.color': 'yGridColor',
                        'datasets.backgroundColor': 'backgroundColorArrayRaw',
                        'datasets.borderColor': 'borderColorArrayRaw',
                        'plugins.tooltip.backgroundColor': 'tooltipBackground',
                        'plugins.tooltip.titleColor': 'tooltipColor',
                        'plugins.tooltip.bodyColor': 'tooltipColor',
                        'plugins.tooltip.borderColor': 'tooltipBorderColor',
                        'plugins.datalabels.color': 'dataLabelsColor',
                        'plugins.datalabels.backgroundColor': 'dataLabelsBackgroundColor',
                        'plugins.datalabels.borderColor': 'dataLabelsBorderColor',
                        'plugins.legend.labels.color': 'legendColor'
                    };
 
                    // Generate propertiesToUpdate array dynamically
                    const propertiesToUpdate = Object.entries(propertyConfigMap)
                        .filter(([_, configKey]) => {
                            // Check if the config value exists and uses a CSS variable
                            const configValue = chartConfig.config?.[configKey];
                            return configValue?.raw?.startsWith('var');
                        })
                        .map(([property]) => property);
 
                    updateChartColors(chartInstance, propertiesToUpdate, chartConfig, chartConfig.queryLoop);
 
                }
 
            }
           
        })
 
    }
 
    setTimeout(() => {
        extrasChart(document)
    }, 30)

    const xDynamicChartAJAX = xExtrasRegisterAJAXHandler('doExtrasChart');
 
    window.doExtrasChart = extrasChart;
 
 }
 
 document.addEventListener("DOMContentLoaded",function(e){
    bricksIsFrontend&&xDynamicChart()
 });