const path = require('path');
const webpack = require('webpack');

module.exports = (env) => {
    let deployment_type = env.type;
    return {
        mode: 'production',
        entry: {
            'jquery.min': require.resolve('jquery'),
            'jquery-ui.min': require.resolve('jquery-ui'),
            'tilt.jquery': require.resolve('tilt.js/dest/tilt.jquery.min.js'),
            'datatables.min': [
                path.resolve(__dirname, '../node_modules/jszip/dist/jszip.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-autofill-bs4/js/autoFill.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-colreorder-bs4/js/colReorder.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-fixedcolumns-bs4/js/fixedColumns.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-fixedheader-bs4/js/fixedHeader.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-keytable-bs4/js/keyTable.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-buttons/js/buttons.html5.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-buttons/js/dataTables.buttons.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-rowgroup-bs4/js/rowGroup.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-rowreorder-bs4/js/rowReorder.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-scroller-bs4/js/scroller.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js'),
            ],
            'datatables_v1.min': [
                path.resolve(__dirname, '../node_modules/jszip/dist/jszip.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-autofill-bs4/js/autoFill.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-colreorder-bs4/js/colReorder.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-fixedcolumns-bs4/js/fixedColumns.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-fixedheader-bs4/js/fixedHeader.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-keytable-bs4/js/keyTable.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-buttons/js/buttons.html5.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-buttons/js/dataTables.buttons.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-rowgroup-bs4/js/rowGroup.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-rowreorder-bs4/js/rowReorder.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-scroller-bs4/js/scroller.bootstrap4.min.js'),
                path.resolve(__dirname, '../node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js'),
            ],
            'Editor': require.resolve('@datatables.net/editor/js/dataTables.editor.min.js'),
            'cryptoJS': require.resolve('crypto-js'),
            'carbon.min': require.resolve('carbon-components/umd/index.js'),
            'clipboard.min': require.resolve('clipboard'),
            'bootstrap.bundle': require.resolve('bootstrap/dist/js/bootstrap.bundle.min.js'),
            'particles': require.resolve('particles.js/particles.js'),
            'popper.min': require.resolve('@popperjs/core/dist/umd/popper.js'),
            'd3.min': require.resolve('d3').replace('src/index.js', 'dist/d3.min.js'),
            'bootstrap-datepicker': require.resolve('bootstrap-datepicker/dist/js/bootstrap-datepicker.js'),
            'select2.full': require.resolve('select2/dist/js/select2.full.js'),
            'handsontable.full.min': require.resolve('handsontable/dist/handsontable.full.min.js'),
            'ion.rangeSlider': require.resolve('ion-rangeslider/js/ion.rangeSlider.js'),
            'highstock':require.resolve('highcharts/highstock.js'),
            'highmaps':require.resolve('highcharts/highmaps.js'),
            'heatmap':require.resolve('highcharts/modules/heatmap.js'),
            'exporting':[
                path.resolve(__dirname, '../node_modules/highcharts/modules/offline-exporting.js'),
                path.resolve(__dirname, '../node_modules/highcharts/modules/exporting.js')
            ],
            'highmaps-exporting':require.resolve('highcharts/modules/exporting.js'),
            'export-data':require.resolve('highcharts/modules/export-data.js'),
            'highmaps-export-data':require.resolve('highcharts/modules/export-data.js'),
            'highmaps-pattern-fill':require.resolve('highcharts/modules/pattern-fill.js'),
            'highmaps-map':require.resolve('highcharts/modules/map.js'),
            'highmaps-data':require.resolve('highcharts/modules/data.js'),
            'highmaps-drilldown':require.resolve('highcharts/modules/drilldown.js'),
            'no-data-to-display':require.resolve('highcharts/modules/no-data-to-display.js'),
            'highcharts-more':require.resolve('highcharts/highcharts-more.js'),
            'accessibility':require.resolve('highcharts/modules/accessibility.js'),
            'treemap':require.resolve('highcharts/modules/treemap.js'),
            'highmaps-accessibility':require.resolve('highcharts/modules/accessibility.js'),
            'go':require.resolve('gojs'),
            'sankey': require.resolve('highcharts/modules/sankey.js'),
            'sanitize-html':require.resolve('sanitize-html/index.js'),
            'tabulator-tables.min': require.resolve('tabulator-tables/dist/js/tabulator.min.js'),
            'Sortable_1_15_0.min':require.resolve('sortablejs/Sortable.min.js'),
            'mapbox-gl': require.resolve('mapbox-gl/dist/mapbox-gl.js'),
            'anychart-base.min': require.resolve('anychart/dist/js/anychart-base.min.js'),
            'anychart-surface.min': require.resolve('anychart/dist/js/anychart-surface.min.js'),
            'anychart-ui.min': require.resolve('anychart/dist/js/anychart-ui.min.js'),
            'anychart-exports.min': require.resolve('anychart/dist/js/anychart-exports.min.js'),
            'histogram-bellcurve':require.resolve('highcharts/modules/histogram-bellcurve.js'),
            'pptxgen.bundle': require.resolve('pptxgenjs'),
            'jszip.min': require.resolve('jszip/dist/jszip.min.js'),
            'indicators':require.resolve('highcharts/indicators/indicators.js'),
            'trendline':require.resolve('highcharts/indicators/trendline.js'),
            'html2pdf': [
                path.resolve(__dirname, '../node_modules/html2pdf.js/dist/html2pdf.js'),
                path.resolve(__dirname, '../node_modules/html2canvas/dist/html2canvas.min.js'),
                path.resolve(__dirname, '../node_modules/es6-promise/dist/es6-promise.auto.min.js'),
            ],
            'xlsx.full.min': require.resolve('xlsx/xlsx.js'),
            'js.color.gradient': require.resolve('javascript-color-gradient/src/index.js')
        },
        output: {
            filename: '[name].js',
            path: path.resolve(__dirname, deployment_type === "production" ? '../dist/assets' : 'js/dist'),
        },
        externals: {
            jquery: 'jQuery',
        },
        module: {
            // If there are any dependencies that need to be loaded in a special way
            // (ie they add global variables), they should be specified here
            rules: [
                {                                                     // Example for bundling jQuery so that it is globally accessible
                    test: require.resolve('jquery'),                  // Require library
                    loader: 'expose-loader',                          // Load library to be globally available
                    options: {
                        exposes: ['$', 'jQuery', 'jquery', 'window.jQuery']     // Expose the library in a global variable (must be the same name as if imported via <script> tags)
                    }
                },
                {
                    test: require.resolve('clipboard'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['ClipboardJS', 'window.ClipboardJS']
                    }
                },
                {
                    test: require.resolve('crypto-js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['CryptoJS', 'window.CryptoJS']
                    }
                },
                {
                    test: require.resolve('carbon-components'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['CarbonComponents', 'window.CarbonComponents']
                    }
                },
                {
                    test: require.resolve('handsontable/dist/handsontable.full.min.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['Handsontable']
                    }
                },
                {
                    test: require.resolve('highcharts/highstock.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['Highcharts']
                    }
                },
                {
                    test: require.resolve('highcharts/modules/heatmap.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['heatmap']
                    }
                },
                {
                    test: require.resolve('highcharts/highcharts-more.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['highchartsMore']
                    }
                },
                {
                    test: require.resolve('d3'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['d3', 'window.d3']
                    }
                },
                {
                    test: require.resolve('highcharts/modules/sankey.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['highchartsSankey', 'sankey']
                    }
                },
                {
                    test: require.resolve('highcharts/modules/data.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['highchartsData', 'data']
                    }
                },
                {
                    test: require.resolve('highcharts/modules/drilldown.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['highchartsDrilldown', 'drilldown']
                    }
                },
                {
                    test: require.resolve('highcharts/modules/exporting.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['highchartsExport']
                    }
                },
                {
                    test: require.resolve('highcharts/modules/offline-exporting.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['highchartsOfflineExport']
                    }
                },
                {
                    test: require.resolve('sanitize-html/index.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['sanitizeHtml']
                    }
                },
                {
                    test: require.resolve('tabulator-tables/dist/js/tabulator.min.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['Tabulator']
                    }
                },
                {
                    test: require.resolve('sortablejs/Sortable.min.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['Sortable']
                    }
                },
                {
                    test: require.resolve('mapbox-gl'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['mapboxgl', 'window.mapboxgl']
                    }
                },
                {
                    test: require.resolve('gojs'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['TableLayout']
                    }
                },
                {
                    test: require.resolve('anychart/dist/js/anychart-base.min.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['anychart']
                    }
                },
                {
                    test: require.resolve('highcharts/modules/histogram-bellcurve.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['histogram_bellcurve']
                    }
                },
                {
                    test: require.resolve('pptxgenjs'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['PptxGenJS']
                    }
                },
                {
                    test: require.resolve('jszip/dist/jszip.min.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['JSZip']
                    }
                },
                {
                    test: require.resolve('highcharts/indicators/indicators.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['indicators']
                    }
                },
                {
                    test: require.resolve('highcharts//indicators/trendline.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['trendline']
                    }
                },
                {
                    test: require.resolve('@datatables.net/editor/js/dataTables.editor.min.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['Editor']
                    }
                },
                {
                    test: require.resolve('html2pdf.js/dist/html2pdf.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['html2pdf']
                    }
                },
                {
                    test: require.resolve('highcharts/modules/no-data-to-display.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['highchartsNoData']
                    }
                },
                {
                	test: require.resolve('xlsx/xlsx.js'),
                	loader: 'expose-loader',
                	options: {
                    		exposes: {
                            globalName: 'XLSX',
                            override: true
				        }
                	}
            	},
                {
                	test: require.resolve('javascript-color-gradient/src/index.js'),
                	loader: 'expose-loader',
                	options: {
                    		exposes: {
                            globalName: 'GradientColor',
                            override: true
				        }
                	}
            	},
                {
                    test: require.resolve('highcharts/modules/treemap.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['treemap']
                    }
                },
                {
                    test: require.resolve('bootstrap/dist/js/bootstrap.bundle.min.js'),
                    loader: 'expose-loader',
                    options: {
                        exposes: ['bootstrap']
                    }
                }
            ],
        },
    }
};
