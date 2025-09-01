"use strict";
let textColor = 'var(--cds-text-02, #525252)';
let pieChart = 'var(--cds-pie-01)'
let resource_category_chart;
if (!window._rb) window._rb = {};

let current_page = {
    'ZBT': 'zbt_summary',
    'Issue': 'issue'
}

function onReady() {
    if (page == 'ZBT' || page == 'Issue') {
        // Show the breadcrumb instead of hiding it
        $(`#${page}-summary-breadcrumb`).attr("hidden",false);
        Highcharts.chart('pie-1', {
            chart: {
                backgroundColor: 'transparent',
                type: 'pie',
                options3d: {
                    enabled: true,
                    alpha: 45,
                    beta: 0
                }
            },
            title: {
                text: `${page}s by Capability Sponsor`,
                style: {
                    color: textColor,
                    fontWeight: 'bolder'
                }
            },
            // accessibility: {
            //     point: {
            //         valueSuffix: '%'
            //     }
            // },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y:.1f}</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    depth: 35,
                    dataLabels: {
                        // enabled: false,
                        distance: 5,
                        format: '{point.name}, {point.y:.0f}',
                        style: {
                            fontSize: "14px",
                            color: pieChart,
                            textOutline: '0'
                        }
                    }
                },
                series: {
                    cursor: 'pointer',
                    events: {
                        click: function (event) {
                            redirectToPage(
                                current_page[page], 
                                'event_summary_overall', 
                                `cap-sponsor[]=${encodeURIComponent(event.point.name)}`
                            );
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Share',
                colorByPoint: true,
                data: cap_sponsor_count
            }],
            credits:{
                enabled:false
            },
            exporting: {
                enabled: false
            }
        });
        Highcharts.chart('pie-2', {
            chart: {
                backgroundColor: 'transparent',
                type: 'pie',
                options3d: {
                    enabled: true,
                    alpha: 45,
                    beta: 0
                }
            },
            title: {
                text: `${page}s - Dollars (Thousands)`,
                style: {
                    color: textColor,
                    fontWeight: 'bolder'
                },
            },
            accessibility: {
                point: {
                    valueSuffix: '$M'
                }
            },
            tooltip: {
                pointFormat: 'Total Dollars: <b>${point.y:.1f}</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    depth: 35,
                    dataLabels: {
                        // enabled: false,
                        distance: 5,
                        format: '{point.name}, ${point.y:.0f}',
                        style: {
                            fontSize: "14px",
                            color: pieChart,
                            textOutline: '0'
                        }
                    }
                },
                series: {
                    cursor: 'pointer',
                    events: {
                        click: function (event) {
                            redirectToPage(
                                current_page[page], 
                                'event_summary_overall',
                                `cap-sponsor[]=${encodeURIComponent(event.point.name)}`
                            );
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: `${page}s`,
                colorByPoint: true,
                data: cap_sponsor_dollar
            }],
            credits:{
                enabled:false
            },
            exporting: {
                enabled: false
            }
        });

        resource_category_chart = Highcharts.chart('chart-1', {
            chart: {
                backgroundColor: 'transparent',
                type: 'area'
            },
            title: {
                text: 'Dollars Moved by Resource Category',
                style: {
                    color: textColor,
                    fontWeight: 'bolder'
                }
            },
            xAxis: {
                labels: {
                    style: {
                        color: textColor,
                        fontSize: '13px'
                    }
                },
                categories: dollars_move_fiscal_years
            },
            yAxis: {
                title: {
                    text: 'Dollars (Thousands)',
                    style: {
                        color: textColor,
                        fontSize: '13px'
                    }
                },
                labels: {
                    style: {
                        color: textColor,
                        fontSize: '13px'
                    }
                }
            },
            tooltip: {
                shared: true,
                headerFormat: '<span style="font-size:12px"><b>{point.key}</b></span><br>',
                formatter: function () {
                    let tooltipContent = '<b>' + this.x + '</b><br/>';
                    this.points.forEach(function (point) {
                        const seriesName = point.series.name;
                        const formattedValue = Highcharts.numberFormat(point.y, 0, '', '');
                        tooltipContent += '<span style="color:' + point.color + '">\u25CF</span> ' + seriesName + ': ' + formattedValue + '<br/>';
                    });

                    return tooltipContent;
                }
            },
            plotOptions: {
                area: {
                    stacking: 'normal',
                    lineColor: '#666666',
                    lineWidth: 1,
                    marker: {
                        lineWidth: 1,
                        lineColor: '#666666'
                    }
                }
            },
            series: dollars_move_series_data,
            credits:{
                enabled:false
            },
            exporting: {
                enabled: false
            },
            legend: {
                itemStyle: {
                    color: textColor
                }
            }
        });


        Highcharts.chart('chart-2', {
            chart: {
                backgroundColor: 'transparent',
                type: 'column'
            },
            title: {
                text: `${page}s by Capability Sponsor (Approve/Reject)`,
                style: {
                    color: textColor,
                    fontWeight: 'bolder'
                }
            },
            xAxis: {
                categories: cap_sponsor_approve_reject_categories,
                labels: {
                    style: {
                        color: textColor,
                        fontSize: '13px'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Count',
                    style: {
                        color: textColor,
                        fontSize: '13px'
                    }
                },
                stackLabels: {
                    enabled: true
                },
                labels: {
                    style: {
                        color: textColor,
                        fontSize: '13px'
                    }
                }
            },
            tooltip: {
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true
                    }
                },
                series: {
                    cursor: 'pointer',
                    events: {
                        click: function (event) {
                            redirectToPage(
                                current_page[page], 
                                'event_summary_overall', 
                                `cap-sponsor[]=${encodeURIComponent(event.point.category)}&
                                ad-consensus=${encodeURIComponent(event.point.series.name)}`
                            );
                        }
                    }
                }
            },
            series: cap_sponsor_approve_reject_series_data,
            credits:{
                enabled:false
            },
            exporting: {
                enabled: false
            },
            legend: {
                itemStyle: {
                    color: textColor
                }
            }
        });
    }  
    
    function redirectToPage(currentPage, page, params) {
        switch (page) {
            case 'event_summary_overall':
                window.location.href = `/socom/${currentPage}/${page}?` + params;
                break;
            default:
                break;
        }
    }

    function update_sand_chart(chart) {
        $.post("/socom/get_dollars_moved_resource_category", {
            rhombus_token:rhombuscookie(),
            filter: chart
        }, function(data) {
            const categories = data?.dollars_move_fiscal_years;
            const series_data = data?.dollars_move_series_data
            resource_category_chart.xAxis[0].update({
                categories
            })
            while (resource_category_chart.series.length > 0) {
                resource_category_chart.series[0].remove(true);
            }
            
            series_data.forEach(function(series) {
                resource_category_chart.addSeries(series);
            });
        });
    }
};

$(onReady) 

window._rb = {
    onReady
}
