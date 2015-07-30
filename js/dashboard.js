/*
 FusionCharts JavaScript Library
 Copyright FusionCharts Technologies LLP
 License Information at <http://www.fusioncharts.com/license>
*/

/*
 * This is the controller file for the dashboard. It reads data from the data.js
 * file and prepares the chart objects.
 *
 * More documentation on this can be found at
 * http://docs.fusioncharts.com/FusionCharts.html
 */

FusionCharts.ready(function() {

    // Global / General Config
    var App,
        dom,
        chartDataSource,
        dashboards,
        viewHelpers,
        eventStates,
        chartConfig,
        startYear = '2014',
        currentYear = '2014',
        DOCUMENT = document,
        ChartsInModal = {},
        activeModalChart = [],
        ieVersion = getIEVersion(),
        ie6 = isIE6();

    //All chart configurations are stored in this variable
    chartConfig = {
        //Chart Configurations for summary tab begins
        //Yearly sales comparison chart configuration
        yearlySalesSummary: {
            type: 'MSCombiDY2D',
            id: 'yearlySalesSummary',
            width: '473',
            height: '340',
            dataFormat: 'json',
            renderAt: 'yearly-sales-chart',
            dataSource: {
                chart: {
                    xAxisName: 'Year',
                    theme: 'management',
                    numberPrefix: '$',
                    pyAxisName: 'Revenue (US $ in thousands)',
                    sYaxisName: 'Units sold (in thousands)'
                },
                categories: [{
                    category: [{
                        label: '2012'
                    }, {
                        label: '2013'
                    }, {
                        label: '2014'
                    }]
                }],
                dataset: [{
                    seriesName: 'Revenue',
                    renderAs: 'column',
                    showValues: '0',
                    data: [{
                        value: '682796'
                    }, {
                        value: '1248078'
                    }, {
                        value: '1160327'
                    }]
                }, {
                    seriesName: 'Units Sold',
                    parentYAxis: 'S',
                    renderAs: 'line',
                    showValues: '0',
                    data: [{
                        value: '25007'
                    }, {
                        value: '43710'
                    }, {
                        value: '38616'
                    }]
                }]
            }
        },
        //Top sales performers chart configuration
        topSalesPerformersSummary: {
            type: 'MSCombiDY2D',
            id: 'topSalesPerformersSummary',
            width: '473',
            height: '340',
            dataFormat: 'json',
            renderAt: 'top-sales-performers-chart',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Performers',
                    pYAxisName: 'Sales (US $ in thousands)',
                    sYAxisName: 'Units sold (in thousands)',
                    theme: 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Sales',
                    renderAs: 'column',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Units Sold',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    data: []
                }]
            }
        },
        //Top products chart configuration
        topProductsSummary: {
            type: 'MSCombiDY2D',
            id: 'topProductsSummary',
            width: '968',
            height: '340',
            dataFormat: 'json',
            renderAt: 'top-products-chart',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Products',
                    pYAxisName: 'Price (US $ in thousands)',
                    sYAxisName: 'Units Sold',
                    theme: 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Price',
                    renderAs: 'column',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Units Sold',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    data: []
                }]
            }
        },
        //Top countries by revenue chart configuration
        topRevenuesCountriesSummary: {
            type: 'MSCombiDY2D',
            id: 'topRevenuesCountriesSummary',
            width: '473',
            height: '340',
            dataFormat: 'json',
            renderAt: 'top-revenues-countries-chart',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Countries',
                    pYAxisName: 'Sales (US $ in thousands)',
                    sYAxisName: 'Units sold (in thousands)',
                    theme: 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Sales',
                    renderAs: 'column',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Units Sold',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    data: []
                }]
            }
        },
        //Top cities by revenue chart configuration
        topRevenuesCitiesSummary: {
            type: 'bar2d',
            id: 'topRevenuesCitiesSummary',
            width: '473',
            height: '340',
            dataFormat: 'json',
            renderAt: 'top-revenues-cities-chart',
            dataSource: {
                chart: {
                    yAxisName: 'Total Sales (US $ in thousands)',
                    theme: 'management'
                },
                data: []
            }
        },
        //Chart Configurations for summary tab ends
        //Chart Configurations for sales tab begins
        //Top categories by sales chart configuration
        topCategoriesSalesTab: {
            type: 'bar2d',
            id: 'topCategoriesSalesTab',
            width: '473',
            height: '340',
            dataFormat: 'json',
            renderAt: 'top-categories-sales-tab-chart',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Product Categories',
                    YAxisName: 'Revenue (US $ in thousands)',
                    theme: 'management'
                },
                data: []
            }
        },
        //Top performers by sales chart configuration
        topPerformersSalesTab: {
            type: 'column2D',
            id: 'topPerformersSalesTab',
            width: '473',
            height: '340',
            dataFormat: 'json',
            renderAt: 'top-performers-sales-tab-chart',
            dataSource: {
                chart: {
                    xAxisName: 'Performers',
                    yAxisName: 'Revenue (US $ in thousands)',
                    theme: 'management'
                },
                data: []
            }
        },
        //Monthly sales chart configuration
        topMonthlySalesTab: {
            type: 'MSCombiDY2D',
            id: 'topMonthlySalesTab',
            width: '968',
            height: '340',
            dataFormat: 'json',
            renderAt: 'top-monthly-sales-tab-chart',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Month',
                    pYAxisName: 'Sales (US $ in thousands)',
                    sYAxisName: 'Units Sold (in thousands)',
                    theme: 'management',
                    lineThickness: '2'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Beverages',
                    renderAs: 'line',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Condiments',
                    renderAs: 'line',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Confections',
                    renderAs: 'line',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Dairy Products',
                    renderAs: 'line',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Grains / Cereals',
                    renderAs: 'line',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Meat / Poultry',
                    renderAs: 'line',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Produce',
                    renderAs: 'line',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Seafood',
                    renderAs: 'line',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Total Units Sold',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    color: '#052829',
                    dashed: '1',
                    data: []
                }]
            }
        },
        //Productwise Sales chart configuration [ Drill down from Top categories by sales chart ]
        categoryWiseSales: {
            type: 'MSCombiDY2D',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Products',
                    pYAxisName: 'Revenue (US $ in thousands)',
                    sYAxisName: 'Units Sold',
                    theme: 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Revenue',
                    renderAs: 'column',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Units Sold',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    data: []
                }]
            }
        },
        //Sales performance by Sales person chart configuration [ Drill down from Top performers by sales chart ]
        singleSalePerformerSalesTab: {
            type: 'MSCombiDY2D',
            id: 'singleSalePerformerSalesTab',
            width: '473',
            height: '340',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Year',
                    pYAxisName: 'Amount (US $ in thousands)',
                    sYAxisName: 'No. of orders',
                    theme: 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Amount',
                    renderAs: 'column',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'No. of orders',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    data: []
                }]
            }
        },
        //Chart Configurations for sales tab ends
        //Chart Configurations for Geogrpahy tab begins
        //North America sales chart configuration
        NASales: {
            type: 'northamerica',
            renderAt: 'north-america-sales-chart',
            id: 'NASales',
            width: '100%',
            height: '400',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    theme: 'management'
                },
                colorrange: {
                    minvalue: '1000',
                    code: 'F5F5F5',
                    color: [{
                        minvalue: '1000',
                        maxvalue: '25000',
                        displayvalue: 'Poor',
                        code: 'e11800'
                    }, {
                        minvalue: '25000',
                        maxvalue: '45000',
                        displayvalue: 'Average',
                        code: 'eed800'
                    }, {
                        minvalue: '45000',
                        maxvalue: '235000',
                        displayvalue: 'Good',
                        code: '99cf1b'
                    }]
                },
                data: []
            }
        },
        //South America sales chart configuration
        SASales: {
            type: 'southamerica',
            renderAt: 'south-america-sales-chart',
            id: 'SASales',
            width: '100%',
            height: '400',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    theme: 'management'
                },
                colorrange: {
                    minvalue: '1000',
                    code: 'F5F5F5',
                    color: [{
                        minvalue: '1000',
                        maxvalue: '25000',
                        displayvalue: 'Poor',
                        code: 'dd0017'
                    }, {
                        minvalue: '25000',
                        maxvalue: '45000',
                        displayvalue: 'Average',
                        code: 'f8e71c'
                    }, {
                        minvalue: '45000',
                        maxvalue: '235000',
                        displayvalue: 'Good',
                        code: '75ca40'
                    }]
                },
                data: []
            }
        },
        //Europe sales chart configuration
        europeSales: {
            type: 'europe',
            renderAt: 'europe-sales-chart',
            id: 'europeSales',
            width: '100%',
            height: '400',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    theme: 'management'
                },
                colorrange: {
                    color: [{
                        minvalue: '1000',
                        maxvalue: '25000',
                        displayvalue: 'Poor',
                        code: 'dd0017'
                    }, {
                        minvalue: '25000',
                        maxvalue: '45000',
                        displayvalue: 'Average',
                        code: 'f8e71c'
                    }, {
                        minvalue: '45000',
                        maxvalue: '235000',
                        displayvalue: 'Good',
                        code: '75ca40'
                    }]
                },
                data: []
            }
        },
        //Top cities sales chart configuration [ Drill down from Map ]
        topCitiesSales: {
            type: 'MSCombiDY2D',
            id: 'topCitiesSales',
            width: '473',
            height: '340',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'City',
                    pYAxisName: 'Sales (US $ in thousands)',
                    sYAxisName: 'Units sold (in thousands)',
                    theme: 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Sales',
                    renderAs: 'column',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Units Sold',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    data: []
                }]
            }
        },
        //Top customers chart configuration [ Drill down from Map ]
        topCitiesCustomers: {
            type: 'MSCombiDY2D',
            id: 'topCitiesCustomers',
            width: '473',
            height: '340',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Customer',
                    pYAxisName: 'Sales (US $ in thousands)',
                    sYAxisName: 'Units sold (in thousands)',
                    theme: 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Sales',
                    renderAs: 'column',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Units Sold',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    data: []
                }]
            }
        },
        //Chart Configurations for Geogrpahy tab ends
        //Chart Configurations for KPI tab begins
        //Product categories by inventory chart configuration
        inventoryByProductCategories: {
            type: 'MSCombiDY2D',
            id: 'inventoryByProductCategories',
            width: '473',
            height: '340',
            dataFormat: 'json',
            renderAt: 'inventory-by-product-categories-chart',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Product Category',
                    pYAxisName: 'Cost of Inventory',
                    sYAxisName: 'Units in Inventory',
                    theme: 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Cost of Inventory',
                    renderAs: 'column',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Units in Inventory',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    data: []
                }]
            }
        },
        //Cost of goods sold chart configuration
        costOfGoodsSold: {
            type: 'stackedcolumn2d',
            id: 'costOfGoodsSold',
            renderAt: 'cost-of-goods-sold-chart',
            width: '473',
            height: '340',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    'xAxisName': 'Month',
                    'yAxisName': 'In Percentage',
                    'numberPrefix': '$',
                    'stack100Percent': '1',
                    'showPercentValues': '0',
                    'theme': 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Purchase',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Labour',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Lease',
                    showValues: '0',
                    data: []
                }]
            }
        },
        //Average shipping time chart configuration
        averageShippingTime: {
            type: 'bar2d',
            id: 'averageShippingTime',
            width: '473',
            height: '340',
            dataFormat: 'json',
            renderAt: 'average-shipping-time-chart',
            dataSource: {
                chart: {
                    yAxisName: 'Time (in days)',
                    theme: 'management'
                },
                data: []
            }
        },
        //Customer satisfaction chart configuration
        customerSatisfaction: {
            type: 'pie2d',
            id: 'customerSatisfaction',
            renderAt: 'customer-satisfaction-chart',
            width: '473',
            height: '340',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    showPercentValues: '1',
                    theme: 'management'
                },
                data: []
            }
        },
        //Inventory by products [ Drill down from Product categories by inventory chart ]
        categoryWiseInventory: {
            type: 'MSCombiDY2D',
            dataFormat: 'json',
            dataSource: {
                chart: {
                    numberPrefix: '$',
                    xAxisName: 'Products',
                    pYAxisName: 'Cost of Inventory (US $ in thousands)',
                    sYAxisName: 'Units in Inventory',
                    theme: 'management'
                },
                categories: [{
                    category: []
                }],
                dataset: [{
                    seriesName: 'Cost of Inventory',
                    renderAs: 'column',
                    showValues: '0',
                    data: []
                }, {
                    seriesName: 'Units in Inventory',
                    renderAs: 'line',
                    showValues: '0',
                    parentYAxis: 'S',
                    data: []
                }]
            }
        }
        //Chart Configurations for KPI tab ends
    };

    // Employee IDs
    var employeeDetails = managementData.employeeDetails;

    // Application specific methods.
    App = {
        // Handles application initialization.
        //Any code that you need to execute before the all the dashboards are executed can be specified here.
        init: function(callback) {
            callback();
        }
    }

    // Setters that are used to set chart specific datasource attributes.
    chartDataSource = {
        // Sets the data object for a given chart datasource
        setData: function(datasource, data, _options) {
            var options = typeof _options === 'undefined' ? false : _options,
                i,
                dataLength,
                formatted,
                parentValue,
                parentTitle;

            datasource.data = [];
            dataLength = data.length;
            for (i = 0; i < dataLength; i++) {
                formatted = data[i].data;
                if (typeof formatted.tooltip !== 'undefined') {

                    parentTitle = formatted['label'];

                    if (options.tooltipFormatter) {
                        parentValue = options.tooltipFormatter(formatted['value']);
                    } else {
                        parentValue = formatted['value'];
                    }

                    formatted['tooltext'] = DataHelpers.formatToolText({
                        title: parentTitle,
                        value: parentValue
                    }, formatted.tooltip);
                    delete formatted.tooltip;
                }

                datasource.data.push(formatted);
            }
        },
        // Sets the caption of a chart
        setCaption: function(datasource, caption) {
            datasource.chart.caption = caption;
        },
        // Set categories for a chart
        setCategories: function(datasource, categories) {
            datasource.categories[0].category = categories;
        },
        // Set an entire dataset for a chart particularly for a multi-series chart
        setDataSet: function(datasource, dataset) {
            datasource.dataset = dataset;
        },
        // Set the data object for a given dataset
        setDataSetData: function(datasource, data) {
            var i, datasetLength = datasource.dataset.length;
            for (i = 0; i < datasetLength; i++) {
                datasource.dataset[i].data = data[i].data;
            }
        }
    };

    // Handles dom querying.
    dom = {
        // Get the value of a specific dom element by id.
        getElementValue: function(id) {
            var $element = DOCUMENT.getElementById(id);
            return $element.value;
        },
        // Get dom element by id.
        getById: function(id) {
            return DOCUMENT.getElementById(id);
        },
        // Get value of current dom element in scope.
        queryCurrentValue: function(id, obj) {
            // IE fix, because IE doesn't recognize `this` well enough.
            if (obj === window) {
                return dom.getElementValue(id);
            }

            return obj.value;
        }
    };
    // Set of helper methods that handles presentation.
    viewHelpers = {
        // Adds active class for the sidebar list element to specify the active dashboard.
        addActiveLink: function(id) {
            var $link = dom.getById(id);
            $link.className = 'active';
        },
        // Remove the active class name for a tab.
        removeActiveLink: function(id) {
            var $link = dom.getById(id);
            $link.className = '';
        },
        // Present the modal on the screen.
        showModal: function(id, title, _props, callback) {
            var props, chart, i, select, topLevelFilter, charts, selectLength, chartsLength,
                $modalWrapper = dom.getById('modalWrapper'),
                $modal = dom.getById('modal'),
                $title = dom.getById('modal-title');

            props = _props;
            $title.innerHTML = title;
            props.id = 'modal-inner';
            props.renderAt = 'modal';
            props.width = '1068';
            props.height = '500';

            ChartsInModal[id] = {
                props: props,
                chart: new FusionCharts(props)
            };

            activeModalChart.push(ChartsInModal[id]['chart']);
            //For IE6, the drop down boxes' visibility set to hidden
            if (isIE6()) {
                select = DOCUMENT.getElementsByTagName('select');
                topLevelFilter = dom.getById('top-level-select-filter');
                selectLength = select.length;
                for (i = 0; i < selectLength; i++) {
                    select[i].style.visibility = 'hidden';
                }
                topLevelFilter.style.visibility = 'hidden';
            }


            setTimeout(function() {
                callback(ChartsInModal[id]['chart']);
            }, 50);

            charts = getElementsByClassName('fusioncharts-container');
            chartsLength = charts.length;
            for (i = 0; i < chartsLength; i++) {
                charts[i].style.position = '';
                charts[i].children[0].style.position = '';
            }
            $modalWrapper.style.display = 'block';
            $modalWrapper.style.zIndex = '2';

            eventStates.modalShown = true;
        }
    };
    //Helper Methods for filtering data etc.
    DataHelpers = {

        // Convert a string to a slug.
        // For eg.: Meat / Poultry becomes meat_poultry
        slugize: function(_name) {
            var i, name = _name.toLowerCase().split(" ").join("/").split("/"),
                nameLength,
                label = [];

            nameLength = name.length;
            for (i = 0; i < nameLength; i++) {
                if (name[i].trim()) {
                    label.push(name[i]);
                }
            }

            return label.join('_').trim();
        },
        // Filter / Limit the number of category objects
        // i.e., if you pass 10 categories and limit it to a certain number like 5, it will return 5 categories
        numberFilterCategories: function(total, config) {
            var i, totalInt, data = [];
            if (total === 'all') {
                data = config['all'];
            } else {
                totalInt = parseInt(total);
                for (i = 0; i < totalInt; i++) {
                    data.push(config['all'][i]);
                }
            }

            return data;
        },
        // Filter / Limit the dataset.
        numberFilterDataSet: function(total, _dataset) {
            var i, totalInt, dataset = _dataset['all'],
                series1_data = {
                    data: []
                },
                series2_data = {
                    data: []
                };

            if (total === 'all') {
                series1_data = dataset[0];
                series2_data = dataset[1];
            } else {
                totalInt = parseInt(total);
                for (i = 0; i < totalInt; i++) {
                    series1_data.data.push(dataset[0].data[i]);
                    series2_data.data.push(dataset[1].data[i]);
                }
            }

            return [series1_data, series2_data];
        },
        // Filter / Limit the data object for a chart
        numberFilterData: function(total, _data) {
            var i, totalInt, data = _data['all'],
                result = [];

            if (total === 'all') {
                result = data;
            } else {
                totalInt = parseInt(total);
                for (i = 0; i < totalInt; i++) {
                    result.push(data[i]);
                }
            }

            return result;
        },
        // Custom ToolText format for all the charts.
        formatToolText: function(parent, obj) {
            var key, html = [],
                result;

            for (key in obj) {
                html.push(key + ' - ' + obj[key]);
            }

            result = parent.title + ' - ' + parent.value + 
                    '<div class="tooltip-border"></div><div class="tooltip-content">' + html.join('<br>') + '</div>';

            return result;
        },
        // ToolTip formatting for a particluar chart.
        tooltipFormatters: {
            averageShippingTimeChart: function(data) {
                return data + ' days';
            }
        }
    };
    //Consists of all dashboards like Summary, Sales, Geography and KPI
    dashboards = {
        // An item is a single dashboard.
        items: {},
        // Current active / shown dashboard.
        active: null,
        // Current year for which the data should be shown
        currentYear: null,
        // Add a single dashboard to the items object.
        add: function(id, callback) {
            this.items[id] = {
                rendered: false,
                callback: callback,
                currentYear: currentYear
            };
        },
        // Run / Execute the callback of a particular dashboard.
        run: function(id) {
            var self = this;
            FusionCharts.ready(function() {
                dom.getById(id).className = '';
                self.items[id].rendered = true;
                self.items[id].callback();
                viewHelpers.addActiveLink(id + '-' + 'link');
                self.active = id;
                self.items[id].currentYear = currentYear;
            });
        },
        // Show a dashboard. This also toggles the active dashboard.
        show: function(id) {
            var self = this,
                $element,
                $activeElement,
                $link,
                redraw = false;

            if (this.items[this.active].rendered) {
                if (currentYear != this.items[id].currentYear) {
                    redraw = true;
                    this.items[id].currentYear = currentYear;
                }
            }

            for (var key in this.items) {
                $element = dom.getById(key);
                viewHelpers.addActiveLink(key + '-' + 'link');
                $link = dom.getById(key + '-' + 'link');

                if (key !== id) {
                    $element.style.display = 'none';
                    $link.className = '';
                } else {
                    this.active = key;
                    $activeElement = $element;
                }
            }

            $activeElement.style.display = 'block';
            if (!this.items[this.active].rendered) {
                this.run(this.active);
            }
            if (redraw) {
                this.redraw(id);
            }
        },
        // Redraw all the charts of a particular dashboard.
        redraw: function(id) {
            FusionCharts.ready(function() {
                var ids = getEventIds(id);
                eventListeners.trigger('change', function(e) {
                    eventHandlers.trigger.topLevelYearFilter(ids, {
                        trigger: e,
                        year: currentYear,
                        eventName: 'change'
                    });
                });
            });
        }
    };
    //Setter and getter for modal
    eventStates = {
        // Flag that represents whether a modal is shown.
        modalShown: false,
        // Check if a modal is opened.
        isModalOpened: function() {
            return this.modalShown;
        },
        // Check if a modal is closed.
        isModalClosed: function() {
            return !this.isModalOpened();
        }
    };

    //Handles adding of events and triggering.
    eventListeners = {
        // Add an event for a specific dom id / object.
        add: function(id, eventName, callback, _isObject) {
            var isObject = typeof _isObject === 'undefined' ? false : _isObject,
                $element;

            if (isObject) {
                $element = id;
            } else {
                $element = DOCUMENT.getElementById(id);
            }

            if ($element.addEventListener) {
                $element.addEventListener(eventName, callback);
            } else if ($element.attachEvent) {
                $element.attachEvent('on' + eventName, callback);
            }
        },
        // Trigger / Fire an event.
        trigger: function(eventType, callback) {
            var mouseEvent;

            if (DOCUMENT.createEvent) {
                mouseEvent = DOCUMENT.createEvent('MouseEvent');

                mouseEvent.initEvent(eventType, true, true);
                callback(mouseEvent);
            } else if (DOCUMENT.createEventObject) {
                mouseEvent = DOCUMENT.createEventObject();
                callback(mouseEvent);
            }
        }
    };

    //Callbacks for a particluar event.
    eventHandlers = {
        // Trigger callback.
        trigger: {

            // Trigger all filters for the current active dashboard.
            topLevelYearFilter: function(ids, options) {
                var i, idLength, $element;
                idLength = ids.length;
                for (i = 0; i < idLength; i++) {
                    $element = dom.getById(ids[i]);
                    $element.value = options.year;

                    if ($element.dispatchEvent) {
                        $element.dispatchEvent(options.trigger);
                    } else if ($element.fireEvent) {
                        $element.fireEvent('on' + options.eventName, options.trigger);
                    }
                }
            }
        },
        // Stop propogation
        stopPropagation: function(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            } else if (e.returnValue) {
                e.returnValue = false;
            }
            if (window.event) {
                if (window.event.cancelBubble !== 'undefined') {
                    window.event.cancelBubble = true;
                }
            }
        }
    };

    //Function which inserts rows on the Geography tab depending upon the year selected
    function insertTable(tableId, tableClass, id, year) {
        var i, contents = "<table class='" + tableClass + "' id='" + tableId + "'>",
            displayvalue = "",
            value = 0,
            fontColor = "",
            currentYear = typeof year === 'undefined' ? '2014' : year;
        for (i = 0; i < managementData[id + 'Data'][currentYear].length; i++) {
            displayvalue = managementData[id + 'Data'][currentYear][i].data.displayvalue;
            value = managementData[id + 'Data'][currentYear][i].data.value;
            fontColor = getValueColor(id, value);
            //fontColor = "#000";
            formatedValue = FusionCharts.formatNumber(+value, {
                formatNumberScale: '0',
                numberPrefix: '$'
            });
            if (i % 2 == 0)
                contents += '<tr>';
            else
                contents += '<tr class="even-row">';
            contents += '<td><div class="iMap ' + displayvalue + '"></div></td>';
            contents += '<td>' + displayvalue + '</td>';
            contents += '<td><p class="mapCountryValue" style="background-color: #' +
                fontColor + '">' + formatedValue + '</p></td>';
            contents += '</tr>';
        }
        contents += '</table>';
        return contents;

    }

    //Function which determines the color of the value in geogrpahy table
    function getValueColor(id, value) {
        var i, limitArrLength, limitArr = chartConfig[id].dataSource.colorrange.color,
            color = '';
        limitArrLength = limitArr.length;
        for (i = 0; i < limitArrLength; i++) {

            if (value >= +limitArr[i].minvalue && value <= +limitArr[i].maxvalue) {
                color = limitArr[i].code;
                break;
            }
        }

        return color;

    }

    //Loads map data into the table for specific year
    function loadMapData(year) {
        var NATableHoder = dom.getById('NATableHolder'),
            SATableHoder = dom.getById('SATableHolder'),
            europeTableHoder = dom.getById('europeTableHolder'),
            currentYear = typeof year === 'undefined' ? '2014' : year;

        NATableHoder.innerHTML = insertTable('NAMapTable', 'mapTable', 'NASales', currentYear);
        SATableHoder.innerHTML = insertTable('SAMapTable', 'mapTable', 'SASales', currentYear);
        europeTableHoder.innerHTML = insertTable('europeMapTable', 'mapTable', 'europeSales', currentYear);

    }

    // Global Event Listeners
    // Event listener for closing the modal.
    eventListeners.add('close-modal', 'click', function(e) {
        var i, chartTitle = dom.getById('modal-title'),
            modalWrapper = dom.getById('modalWrapper'),
            geographyTitle = dom.getById('geography-title-holder');

        if (activeModalChart) {
            modalChartLength = activeModalChart.length;
            for (i = 0; i < modalChartLength; i++)
                activeModalChart[i].dispose();
            activeModalChart = [];
        }

        chartTitle.style.display = 'block';
        if (geographyTitle) {
            geographyTitle.parentNode.removeChild(geographyTitle);
        }

        modalWrapper.style.display = 'none';
        modalWrapper.style.zIndex = 0;

        eventStates.modalShown = false;

        if (isIE6()) {
            var i, topLevelFilter = dom.getById('top-level-select-filter'),
                select = DOCUMENT.getElementsByTagName('select'),
                selectLength;
            selectLength = select.length;
            for (i = 0; i < selectLength; i++) {
                select[i].style.visibility = '';
            }
            topLevelFilter.style.visibility = '';
        }
    });

    // Event listener for the top level year filter.
    eventListeners.add('global_year_filter', 'change', function() {
        var year = dom.queryCurrentValue('global_year_filter', this);
        currentYear = year;

        dashboards.items[dashboards.active].currentYear = currentYear;
        eventListeners.trigger('change', function(e) {
            eventHandlers.trigger.topLevelYearFilter(getEventIds(dashboards.active), {
                trigger: e,
                year: currentYear,
                eventName: 'change'
            });
        });

    });

    // Event listener that closes the modal on click anywhere outside the modal.
    eventListeners.add('modalWrapper', 'click', function(e) {
        var $closeModal;
        if (!ie6) {
            eventHandlers.stopPropagation(e);
            $closeModal = dom.getById('close-modal');

            if (eventStates.isModalOpened()) {
                eventListeners.trigger('click', function(e) {
                    if ($closeModal.dispatchEvent) {
                        $closeModal.dispatchEvent(e);
                    } else if ($closeModal.fireEvent) {
                        $closeModal.fireEvent('onclick', e);
                    }
                });
            }
        }
    });

    // Event listener to override the modal wrapper's event click.
    eventListeners.add('modal', 'click', eventHandlers.stopPropagation);

    // Event listener to override the modal wrapper's event click.
    eventListeners.add('modal-title', 'click', eventHandlers.stopPropagation);

    // Summary dashboards / dashboards Tab 1
    dashboards.add('summary', function() {
        // Config for Yearly Sales Chart
        var yearlySalesChart,
            yearlySalesChartConfig = chartConfig.yearlySalesSummary;

        // Config for Top Sales Performers Chart
        var topSalesPerformers,
            topSalesPerformersChartConfig = chartConfig.topSalesPerformersSummary,
            topSalesPerformersSummaryCategories = managementData.topSalesPerformersSummaryCategories,
            topSalesPerformersSummaryData = managementData.topSalesPerformersSummaryData,
            salesByCategoryChartConfig = managementData.salesByCategory;

        // Config for Top Products Chart
        var topProductsSummaryChart,
            topProductsSummaryChartConfig = chartConfig.topProductsSummary,
            topProductsSummaryCategories = managementData.topProductsSummaryCategories,
            topProductsSummaryData = managementData.topProductsSummaryData;

        // Config for Top Revenues By Country Chart
        var topRevenuesCountriesSummaryChart,
            topRevenuesCountriesSummaryChartConfig = chartConfig.topRevenuesCountriesSummary,
            topRevenuesCountriesSummaryCategories = managementData.topRevenuesCountriesSummaryCategories,
            topRevenuesCountriesSummaryData = managementData.topRevenuesCountriesSummaryData;

        // Config for Top Revenues By Cities Chart
        var topRevenuesCitiesSummaryChart,
            topRevenuesCitiesSummaryChartConfig = chartConfig.topRevenuesCitiesSummary,
            topRevenuesCitiesSummaryData = managementData.topRevenuesCitiesSummaryData;


        // Yearly Sales Chart
        yearlySalesChart = new FusionCharts(yearlySalesChartConfig);
        yearlySalesChart.render();


        // Top Sales Performers Chart
        chartDataSource.setCategories(topSalesPerformersChartConfig.dataSource,
            DataHelpers.numberFilterCategories(3, topSalesPerformersSummaryCategories[startYear]));

        chartDataSource.setDataSetData(topSalesPerformersChartConfig.dataSource,
            DataHelpers.numberFilterDataSet(3, topSalesPerformersSummaryData[startYear]));

        topSalesPerformersChart = new FusionCharts(topSalesPerformersChartConfig);
        topSalesPerformersChart.render();

        // Top Products Chart
        chartDataSource.setCategories(topProductsSummaryChartConfig.dataSource,
            DataHelpers.numberFilterCategories(5, topProductsSummaryCategories[startYear]));

        chartDataSource.setDataSetData(topProductsSummaryChartConfig.dataSource,
            DataHelpers.numberFilterDataSet(5, topProductsSummaryData[startYear]));

        topProductsSummaryChart = new FusionCharts(topProductsSummaryChartConfig);
        topProductsSummaryChart.render();

        // Top Revenues Countries Chart
        chartDataSource.setCategories(topRevenuesCountriesSummaryChartConfig.dataSource,
            DataHelpers.numberFilterCategories(5, topRevenuesCountriesSummaryCategories[currentYear]));

        chartDataSource.setDataSetData(topRevenuesCountriesSummaryChartConfig.dataSource,
            DataHelpers.numberFilterDataSet(5, topRevenuesCountriesSummaryData[startYear]));

        topRevenuesCountriesSummaryChart = new FusionCharts(topRevenuesCountriesSummaryChartConfig);
        topRevenuesCountriesSummaryChart.render();

        // Top Revenues Cities Chart
        chartDataSource.setData(topRevenuesCitiesSummaryChartConfig.dataSource,
            DataHelpers.numberFilterData(5, topRevenuesCitiesSummaryData[startYear]));

        topRevenuesCitiesSummaryChart = new FusionCharts(topRevenuesCitiesSummaryChartConfig);
        topRevenuesCitiesSummaryChart.render();

        /**
         * Event Listeners for top sales performers chart.
         */

        // Year filter.
        eventListeners.add('top_sales_performers_summary_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('top_sales_performers_summary_year_filter', this),
                numberOfEmployees = dom.getElementValue('top_sales_performers_summary_number_filter');
            chartDataSource.setCategories(topSalesPerformersChartConfig.dataSource,
                DataHelpers.numberFilterCategories(numberOfEmployees, topSalesPerformersSummaryCategories[year]));

            chartDataSource.setDataSetData(topSalesPerformersChartConfig.dataSource,
                DataHelpers.numberFilterDataSet(numberOfEmployees, topSalesPerformersSummaryData[year]));
            topSalesPerformersChart.setJSONData(topSalesPerformersChartConfig.dataSource);
        });

        // Number filter.
        eventListeners.add('top_sales_performers_summary_number_filter', 'change', function() {
            var numberOfEmployees = dom.getElementValue('top_sales_performers_summary_number_filter', this),
                year = dom.getElementValue('top_sales_performers_summary_year_filter');

            chartDataSource.setCategories(topSalesPerformersChartConfig.dataSource,
                DataHelpers.numberFilterCategories(numberOfEmployees, topSalesPerformersSummaryCategories[year]));

            chartDataSource.setDataSetData(topSalesPerformersChartConfig.dataSource,
                DataHelpers.numberFilterDataSet(numberOfEmployees, topSalesPerformersSummaryData[year]));

            topSalesPerformersChart.setJSONData(topSalesPerformersChartConfig.dataSource);
        });

        /**
         * Event Listeners for top products chart.
         */

        // Year filter.
        eventListeners.add('top_products_summary_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('top_products_summary_year_filter', this),
                numberOfProducts = dom.getElementValue('top_products_summary_number_filter');

            chartDataSource.setCategories(topProductsSummaryChartConfig.dataSource,
                DataHelpers.numberFilterCategories(numberOfProducts, topProductsSummaryCategories[year]));

            chartDataSource.setDataSetData(topProductsSummaryChartConfig.dataSource,
                DataHelpers.numberFilterDataSet(numberOfProducts, topProductsSummaryData[year]));

            topProductsSummaryChart.setJSONData(topProductsSummaryChartConfig.dataSource);
        });

        // Number filter.
        eventListeners.add('top_products_summary_number_filter', 'change', function() {
            var numberOfProducts = dom.queryCurrentValue('top_products_summary_number_filter', this),
                year = dom.getElementValue('top_products_summary_year_filter');

            chartDataSource.setCategories(topProductsSummaryChartConfig.dataSource,
                DataHelpers.numberFilterCategories(numberOfProducts, topProductsSummaryCategories[year]));

            chartDataSource.setDataSetData(topProductsSummaryChartConfig.dataSource,
                DataHelpers.numberFilterDataSet(numberOfProducts, topProductsSummaryData[year]));

            topProductsSummaryChart.setJSONData(topProductsSummaryChartConfig.dataSource);
        });

        /**
         * Event Listeners for top countries by revenue chart.
         */

        // Year filter.
        eventListeners.add('top_revenues_country_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('top_revenues_country_year_filter', this),
                numberOfCountires = parseInt(dom.getElementValue('top_revenues_country_number_filter'));

            chartDataSource.setCategories(topRevenuesCountriesSummaryChartConfig.dataSource,
                DataHelpers.numberFilterCategories(numberOfCountires,
                    topRevenuesCountriesSummaryCategories['2014']));

            chartDataSource.setDataSetData(topRevenuesCountriesSummaryChartConfig.dataSource,
                DataHelpers.numberFilterDataSet(numberOfCountires, topRevenuesCountriesSummaryData[year]));

            topRevenuesCountriesSummaryChart.setJSONData(topRevenuesCountriesSummaryChartConfig.dataSource);
        });

        // Number filter.
        eventListeners.add('top_revenues_country_number_filter', 'change', function() {
            var numberOfCountires = dom.queryCurrentValue('top_revenues_country_number_filter', this),
                year = parseInt(dom.getElementValue('top_revenues_country_year_filter'));

            chartDataSource.setCategories(topRevenuesCountriesSummaryChartConfig.dataSource,
                DataHelpers.numberFilterCategories(numberOfCountires,
                    topRevenuesCountriesSummaryCategories['2014']));

            chartDataSource.setDataSetData(topRevenuesCountriesSummaryChartConfig.dataSource,
                DataHelpers.numberFilterDataSet(numberOfCountires, topRevenuesCountriesSummaryData[year]));

            topRevenuesCountriesSummaryChart.setJSONData(topRevenuesCountriesSummaryChartConfig.dataSource);
        });

        /**
         * Event Listeners for top cities by revenue chart.
         */

        // Year filter.
        eventListeners.add('top_revenues_cities_summary_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('top_revenues_cities_summary_year_filter', this),
                numberOfCities = dom.getElementValue('top_revenues_cities_summary_number_filter');

            chartDataSource.setData(topRevenuesCitiesSummaryChartConfig.dataSource,
                DataHelpers.numberFilterData(numberOfCities, topRevenuesCitiesSummaryData[year]));

            topRevenuesCitiesSummaryChart.setJSONData(topRevenuesCitiesSummaryChartConfig.dataSource);
        });

        // Number filter.
        eventListeners.add('top_revenues_cities_summary_number_filter', 'change', function() {
            var numberOfCities = dom.queryCurrentValue('top_revenues_cities_summary_number_filter', this),
                year = dom.getElementValue('top_revenues_cities_summary_year_filter');

            chartDataSource.setData(topRevenuesCitiesSummaryChartConfig.dataSource,
                DataHelpers.numberFilterData(numberOfCities, topRevenuesCitiesSummaryData[year]));

            topRevenuesCitiesSummaryChart.setJSONData(topRevenuesCitiesSummaryChartConfig.dataSource);
        });

    });

    // Sales dashboards / dashboards Tab 2
    dashboards.add('sales', function() {

        // Config for Top Categories Chart in Sales Tab
        var topCategoriesSalesTabChart,
            topCategoriesSalesTabChartConfig = chartConfig.topCategoriesSalesTab,
            topCategoriesSalesTabCategories = managementData.topCategoriesSalesTabCategories,
            topCategoriesSalesTabData = managementData.topCategoriesSalesTabData;

        // Config for Top Sales Performers Charts in Sales Tab
        var topPerformersSalesTabChart,
            topPerformersSalesTabChartConfig = chartConfig.topPerformersSalesTab,
            topPerformersSalesTabData = managementData.topPerformersSalesTabData;

        // Config for Monthly category wise Sales
        var topMonthlySalesTabChart,
            topMonthlySalesTabChartConfig = chartConfig.topMonthlySalesTab,
            topMonthlySalesData = managementData.topMonthlySalesTabData,
            topMonthlySalesTabCategories = managementData.topMonthlySalesTabCategories;

        // Config for Product Wise Sales Chart after drill down from 'Top Categories by sales' chart
        var categoryWiseSalesChart,
            categoryWiseSalesChartConfig = chartConfig.categoryWiseSales,
            categoryWiseSalesCategories = managementData.productWiseSalesCategories,
            categoryWiseSalesData = managementData.productWiseSalesData;

        // Config for Sales performers Chart after drill down from 'Top performers by sales' chart
        var singleSalePerformerSalesTabChart,
            singleSalePerformerSalesTabConfig = chartConfig.singleSalePerformerSalesTab,
            singleSalePerformerSalesTabCategories = managementData.singleSalePerformerSalesTabCategories,
            singleSalePerformerSalesTabData = managementData.singleSalePerformerSalesTabData;

        // Top Sales Categories Chart
        chartDataSource.setData(topCategoriesSalesTabChartConfig.dataSource, topCategoriesSalesTabData[currentYear]);
        topCategoriesSalesChart = new FusionCharts(topCategoriesSalesTabChartConfig);
        topCategoriesSalesChart.render();

        // Top Sales Performers Chart
        chartDataSource.setData(topPerformersSalesTabChartConfig.dataSource, topPerformersSalesTabData[currentYear]);
        topPerformersSalesTabChart = new FusionCharts(topPerformersSalesTabChartConfig);
        topPerformersSalesTabChart.render();

        // Sales Details of Individual Performer Chart
        chartDataSource.setCategories(singleSalePerformerSalesTabConfig.dataSource,
            singleSalePerformerSalesTabCategories);

        // Monthly Categories Chart
        chartDataSource.setCategories(topMonthlySalesTabChartConfig.dataSource, topMonthlySalesTabCategories);
        chartDataSource.setDataSetData(topMonthlySalesTabChartConfig.dataSource, topMonthlySalesData[currentYear]);
        topMonthlySalesTabChart = new FusionCharts(topMonthlySalesTabChartConfig);
        topMonthlySalesTabChart.render();

        /**
         * Event listeners for top performers chart.
         */

        // Drilldown that opens in a modal
        eventListeners.add(topPerformersSalesTabChart, 'dataplotClick', function() {
            var employeeSlug = DataHelpers.slugize(arguments[1]['categoryLabel']);
            chartDataSource.setDataSetData(singleSalePerformerSalesTabConfig.dataSource,
                singleSalePerformerSalesTabData[employeeSlug]);

            viewHelpers.showModal('singleSalePerformerSalesTabChart', 'Sales Details of ' +
                employeeDetails[employeeSlug]['name'], singleSalePerformerSalesTabConfig,
                function(chart) {
                    chart.render();
                });
        }, true);

        /**
         * Event listeners for top monthly sales chart.
         */

        // Year filter.
        eventListeners.add('top_monthly_sales_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('top_monthly_sales_year_filter', this);

            chartDataSource.setDataSetData(topMonthlySalesTabChartConfig.dataSource, topMonthlySalesData[year]);
            topMonthlySalesTabChart.setJSONData(topMonthlySalesTabChartConfig.dataSource);
        });

        // Number filter.
        eventListeners.add('top_categories_sales_tab_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('top_categories_sales_tab_year_filter', this);

            chartDataSource.setData(topCategoriesSalesTabChartConfig.dataSource, topCategoriesSalesTabData[year]);
            topCategoriesSalesChart.setJSONData(topCategoriesSalesTabChartConfig.dataSource);
        });

        // Event listeners for top performers chart in sales dashboard.
        eventListeners.add('top_performers_sales_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('top_performers_sales_year_filter', this);

            chartDataSource.setData(topPerformersSalesTabChartConfig.dataSource, topPerformersSalesTabData[year]);
            topPerformersSalesTabChart.setJSONData(topPerformersSalesTabChartConfig.dataSource);
        });

        /**
         * Event listeners for top categories by sales chart.
         */

        // Drilldown that shows in a modal
        eventListeners.add(topCategoriesSalesChart, 'dataplotClick', function() {
            var year = dom.getById('top_categories_sales_tab_year_filter').value,
                label = DataHelpers.slugize(arguments[1].categoryLabel);

            chartDataSource.setCategories(categoryWiseSalesChartConfig.dataSource,
                categoryWiseSalesCategories[year][label]);

            chartDataSource.setDataSetData(categoryWiseSalesChartConfig.dataSource,
                categoryWiseSalesData[year][label]);

            viewHelpers.showModal('categoryWiseSalesChart', arguments[1].categoryLabel +
                ' Sales', categoryWiseSalesChartConfig,
                function(chart) {
                    chart.render();
                });
        }, true);
    });

    // Geography dashboards / dashboards Tab 3
    dashboards.add('geography', function() {

        //Config for North America Sales
        var northAmericaSalesChart,
            northAmericaSalesChartConfig = chartConfig.NASales,
            northAmericaSalesChartData = managementData.NASalesData;

        //Config for South America Sales
        var southAmericaSalesChart,
            southAmericaSalesChartConfig = chartConfig.SASales,
            southAmericaSalesChartData = managementData.SASalesData;

        //Config for Europe Sales
        var europeSalesChart,
            europeSalesChartConfig = chartConfig.europeSales,
            europeSalesChartData = managementData.europeSalesData;

        //Function to display the country level data in a modal
        function showCountryLevelDataFromMap() {
            if (arguments[1].value) {
                var year = dom.getById(this.getMapName() + '_year_filter').value,
                    currentCountry = arguments[1].label,
                    label = arguments[1].shortLabel,
                    topCitiesSalesChartConfig = chartConfig.topCitiesSales,
                    topCitiesSalesChartCategories = managementData['topCitiesSales' +
                        label + 'Categories'],
                    topCitiesSalesChartData = managementData['topCitiesSales' +
                        label + 'Data'],
                    topCitiesCustomersChartConfig = chartConfig.topCitiesCustomers,
                    topCitiesCustomersCategories = managementData['topCitiesCustomers' +
                        label + 'Categories'],
                    topCitiesCustomersChartData = managementData['topCitiesCustomers' +
                        label + 'Data'];

                chartDataSource.setCategories(topCitiesSalesChartConfig.dataSource,
                    topCitiesSalesChartCategories[year].all);

                chartDataSource.setDataSetData(topCitiesSalesChartConfig.dataSource,
                    topCitiesSalesChartData[year].all);

                chartDataSource.setCategories(topCitiesCustomersChartConfig.dataSource,
                    topCitiesCustomersCategories[year].all);

                chartDataSource.setDataSetData(topCitiesCustomersChartConfig.dataSource,
                    topCitiesCustomersChartData[year].all);

                viewHelpers.showModal('topCitiesSales' + label, '', topCitiesSalesChartConfig, function(chart) {
                    var modalWrapper, modalDiv, titleHolder, modalFirstHolder, modalFirstHeading, modalFirstChart,
                        modaSecondHolder, modalSecondChart, modalSecondHeading, topCitiesCustomersChart,
                        props, chartTitle;

                    modalWrapper = dom.getById('modalWrapper');

                    modalDiv = dom.getById('modal');
                    chartTitle = dom.getById('modal-title');
                    chartTitle.style.display = 'none';
                    modalDiv.innerHTML = '';

                    titleHolder = DOCUMENT.createElement('div');
                    titleHolder.id = 'geography-title-holder';

                    modalFirstHolder = DOCUMENT.createElement('div');
                    modalFirstHolder.className = 'pull-left chart-category';
                    modalFirstHolder.id = 'modal-holder-1';

                    modalSecondHolder = DOCUMENT.createElement('div');
                    modalSecondHolder.className = 'pull-right chart-category no-border';
                    modalSecondHolder.id = 'modal-holder-2';

                    modalFirstHeading = DOCUMENT.createElement('h3');
                    modalFirstHeading.innerHTML = 'Top Sales in ' + currentCountry + ' for ' + year;
                    titleHolder.appendChild(modalFirstHeading);

                    modalFirstChart = DOCUMENT.createElement('div');
                    modalFirstChart.id = 'modal-1';
                    modalFirstHolder.appendChild(modalFirstChart);

                    modalSecondHeading = DOCUMENT.createElement('h3');
                    modalSecondHeading.innerHTML = 'Top Customers in ' + currentCountry + ' for ' + year;
                    titleHolder.appendChild(modalSecondHeading);

                    modalSecondChart = DOCUMENT.createElement('div');
                    modalSecondChart.id = 'modal-2';
                    modalSecondChart.className = 'pull-left'
                    modalSecondHolder.appendChild(modalSecondChart);

                    modalWrapper.appendChild(titleHolder);

                    modalDiv.appendChild(modalFirstHolder);
                    modalDiv.appendChild(modalSecondHolder);

                    //chart.renderAt = 'modal-1';
                    chart.width = '474';
                    chart.render('modal-1');


                    props = topCitiesCustomersChartConfig;
                    props.id = 'modal-inner-2';
                    props.renderAt = 'modal-2';
                    props.width = '484';
                    props.height = '500';
                    topCitiesCustomersChart = new FusionCharts(props);
                    topCitiesCustomersChart.render('modal-2');
                    activeModalChart.push(topCitiesCustomersChart);
                });
            }
        }

        //North America Sales Chart
        chartDataSource.setData(northAmericaSalesChartConfig.dataSource, northAmericaSalesChartData[currentYear]);
        northAmericaSalesChart = new FusionCharts(northAmericaSalesChartConfig);
        northAmericaSalesChart.render();

        //South America Sales Chart
        chartDataSource.setData(southAmericaSalesChartConfig.dataSource, southAmericaSalesChartData[currentYear]);
        southAmericaSalesChart = new FusionCharts(southAmericaSalesChartConfig);
        southAmericaSalesChart.render();

        //Europe Sales Chart
        chartDataSource.setData(europeSalesChartConfig.dataSource, europeSalesChartData[currentYear]);
        europeSalesChart = new FusionCharts(europeSalesChartConfig);
        europeSalesChart.render();

        //Event Listeners for North America sales chart
        eventListeners.add('northamerica_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('northamerica_year_filter', this),
                NATableHoder = dom.getById('NATableHolder');

            chartDataSource.setData(northAmericaSalesChartConfig.dataSource, northAmericaSalesChartData[year]);
            northAmericaSalesChart.setJSONData(northAmericaSalesChartConfig.dataSource);
            NATableHoder.innerHTML = insertTable('NAMapTable', 'mapTable', 'NASales', year);
        });

        //Event Listeners for South America sales chart
        eventListeners.add('southamerica_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('southamerica_year_filter', this),
                SATableHoder = dom.getById('SATableHolder');

            chartDataSource.setData(southAmericaSalesChartConfig.dataSource, southAmericaSalesChartData[year]);
            southAmericaSalesChart.setJSONData(southAmericaSalesChartConfig.dataSource);
            SATableHoder.innerHTML = insertTable('SAMapTable', 'mapTable', 'SASales', year);
        });

        //Event Listeners for Europe sales chart
        eventListeners.add('europe_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('europe_year_filter', this),
                europeTableHoder = dom.getById('europeTableHolder');

            chartDataSource.setData(europeSalesChartConfig.dataSource, europeSalesChartData[year]);
            europeSalesChart.setJSONData(europeSalesChartConfig.dataSource);
            europeTableHoder.innerHTML = insertTable('EUMapTable', 'mapTable', 'europeSales', year);
        });

        //Event Listeners for North America sales chart - Drill down that shows in a modal
        eventListeners.add(northAmericaSalesChart, 'entityClick',
            showCountryLevelDataFromMap, true);

        //Event Listeners for South America sales chart - Drill down that shows in a modal
        eventListeners.add(southAmericaSalesChart, 'entityClick',
            showCountryLevelDataFromMap, true);

        //Event Listeners for Europe sales chart - Drill down that shows in a modal
        eventListeners.add(europeSalesChart, 'entityClick',
            showCountryLevelDataFromMap, true);

        //Load inital set of data in the table
        loadMapData();

    });

    // KPI dashboards / dashboards Tab 4
    dashboards.add('kpi', function() {

        // Config for Inventory by product categories Chart
        var inventoryByProductCategoriesChart,
            inventoryByProductCategoriesChartConfig = chartConfig.inventoryByProductCategories,
            inventoryByProductCategoriesCategories = managementData.inventoryByProductCategoriesCategories,
            inventoryByProductCategoriesData = managementData.inventoryByProductCategoriesData;

        // Config for Cost of goods gold Chart
        var costOfGoodsSoldChart,
            costOfGoodsSoldChartConfig = chartConfig.costOfGoodsSold,
            costOfGoodsSoldCategories = managementData.costOfGoodsSoldCategories,
            costOfGoodsSoldData = managementData.costOfGoodsSoldData;

        // Config for Average shipping time Chart
        var averageShippingTimeChart,
            averageShippingTimeChartConfig = chartConfig.averageShippingTime,
            averageShippingTimeData = managementData.averageShippingTimeData;

        // Config for Customer satisfaction Chart
        var customerSatisfactionChart,
            customerSatisfactionChartConfig = chartConfig.customerSatisfaction,
            customerSatisfactionData = managementData.customerSatisfactionData;

        //Config for inventory by products after drill down from 'Inventory By Product Categories' chart
        var categoryWiseInventoryChart,
            categoryWiseInventoryChartConfig = chartConfig.categoryWiseInventory,
            categoryWiseInventoryCategories = managementData.productWiseInventoryCategories,
            categoryWiseInventoryData = managementData.productWiseInventoryData;

        // Inventory By Product Categories Chart
        chartDataSource.setCategories(inventoryByProductCategoriesChartConfig.dataSource,
            inventoryByProductCategoriesCategories);

        chartDataSource.setDataSetData(inventoryByProductCategoriesChartConfig.dataSource,
            inventoryByProductCategoriesData);

        inventoryByProductCategoriesChart = new FusionCharts(inventoryByProductCategoriesChartConfig);
        inventoryByProductCategoriesChart.render();

        // Average Shipping Chart
        chartDataSource.setData(averageShippingTimeChartConfig.dataSource, averageShippingTimeData[currentYear], {
            tooltipFormatter: DataHelpers.tooltipFormatters.averageShippingTimeChart
        });
        averageShippingTimeChart = new FusionCharts(averageShippingTimeChartConfig);
        averageShippingTimeChart.render();

        // Cost of Goods Sold Chart
        chartDataSource.setCategories(costOfGoodsSoldChartConfig.dataSource, costOfGoodsSoldCategories);
        chartDataSource.setDataSetData(costOfGoodsSoldChartConfig.dataSource, costOfGoodsSoldData[currentYear]);
        costOfGoodsSoldChart = new FusionCharts(costOfGoodsSoldChartConfig);
        costOfGoodsSoldChart.render();

        // Cusotmer Satisfaction Chart
        chartDataSource.setData(customerSatisfactionChartConfig.dataSource, customerSatisfactionData[currentYear]);
        customerSatisfactionChart = new FusionCharts(customerSatisfactionChartConfig);
        customerSatisfactionChart.render();

        /**
         * Year filter for inventory by categories chart.
         */
        eventListeners.add('inventory_by_categories_year_filter', 'change', function() {
            chartDataSource.setData(inventoryByProductCategoriesChartConfig.dataSource,
                inventoryByProductCategoriesData);

            inventoryByProductCategoriesChart.setJSONData(inventoryByProductCategoriesChartConfig.dataSource);
        });

        /**
         * Year filter for cost of goods sold chart.
         */
        eventListeners.add('cost_of_goods_sold_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('cost_of_goods_sold_year_filter', this);

            chartDataSource.setCategories(costOfGoodsSoldChartConfig.dataSource, costOfGoodsSoldCategories);
            chartDataSource.setDataSetData(costOfGoodsSoldChartConfig.dataSource, costOfGoodsSoldData[year]);

            costOfGoodsSoldChart.setJSONData(costOfGoodsSoldChartConfig.dataSource);
        });

        /**
         * Year filter for average shipping time chart.
         */
        eventListeners.add('average_shipping_time_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('average_shipping_time_year_filter', this);

            chartDataSource.setData(averageShippingTimeChartConfig.dataSource, averageShippingTimeData[year], {
                tooltipFormatter: DataHelpers.tooltipFormatters.averageShippingTimeChart
            });

            averageShippingTimeChart.setJSONData(averageShippingTimeChartConfig.dataSource);
        });

        /**
         * Year filter for custom satisfaction chart.
         */
        eventListeners.add('customer_satisfaction_year_filter', 'change', function() {
            var year = dom.queryCurrentValue('customer_satisfaction_year_filter', this);

            chartDataSource.setData(customerSatisfactionChartConfig.dataSource, customerSatisfactionData[year]);
            customerSatisfactionChart.setJSONData(customerSatisfactionChartConfig.dataSource);
        });

        /**
         * Event listeners for inventory by categories chart.
         */

        // Drilldown that shows in a modal
        eventListeners.add(inventoryByProductCategoriesChart, 'dataplotClick', function() {
            var year = dom.getById('inventory_by_categories_year_filter').value,
                label = DataHelpers.slugize(arguments[1].categoryLabel);

            chartDataSource.setCategories(categoryWiseInventoryChartConfig.dataSource,
                categoryWiseInventoryCategories[year][label]);
            chartDataSource.setDataSetData(categoryWiseInventoryChartConfig.dataSource,
                categoryWiseInventoryData[year][label]);
            viewHelpers.showModal('categoryWiseInventoryChart', arguments[1].categoryLabel +
                ' - Inventory by products', categoryWiseInventoryChartConfig,
                function(chart) {
                    chart.render();
                });
        }, true);
    });

    // Private Methods
    // Get all chart dom ids for a dashboard tab.
    var getEventIds = function(id) {
        var ids = {
            summary: ['top_sales_performers_summary_year_filter', 'top_revenues_country_year_filter',
                'top_products_summary_year_filter', 'top_revenues_cities_summary_year_filter'
            ],
            sales: ['top_categories_sales_tab_year_filter', 'top_performers_sales_year_filter',
                'top_monthly_sales_year_filter'
            ],
            geography: ['northamerica_year_filter', 'southamerica_year_filter', 'europe_year_filter'],
            kpi: ['inventory_by_categories_year_filter', 'cost_of_goods_sold_year_filter',
                'average_shipping_time_year_filter', 'customer_satisfaction_year_filter'
            ]
        };

        return ids[id];
    };

    // Polyfill for string trim
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, "");
    };

    //// Polyfill for index of array
    var indexOfArray = function(list, value) {
        var i, listLength;
        if (Array.prototype.indexOf) {
            return list.indexOf(value);
        } else {
            listLength = list.length;
            for (i = 0; i < listLength; i++) {
                if (list[i] === value) {
                    return i;
                }
            }
        }

        return -1;
    };

    // Polyfill for get elements by class name
    var getElementsByClassName = function(tag, className) {
        var i, result,
            elements, elementLength;

        if (DOCUMENT.getElementsByClassName) {
            result = DOCUMENT.getElementsByClassName('fusioncharts-container');
        } else {
            result = [];
            elements = DOCUMENT.getElementsByTagName(tag);
            elementLength = elements.length;
            for (i = 0; i < elementLength; i++) {
                if (elements[i].className === className) {
                    result.push(elements[i]);
                }
            }
        }

        return result;
    };

    // Check if IE6
    function isIE6() {
        return (ieVersion > 0 && ieVersion < 7) ? true : false;
    }

    // Check if IE is less than version 8
    function isLessThan8() {
        return (ieVersion > 0 && ieVersion <= 8) ? true : false;
    }

    // Get current version of IE
    function getIEVersion() {
        var pattern = /MSIE (\d+\.\d+);/;

        if (pattern.test(window.navigator.userAgent)) {
            return new Number(RegExp.$1);
        }

        return 0;
    }

    // Handle permalink of tabs switching.
    var urlHandler = function() {
        var tab,
            availableTabs,
            pattern = /#([^#]+)/;
        tab = DOCUMENT.URL ? DOCUMENT.URL.match(pattern) : window.location.match(pattern);
        availableTabs = ['summary', 'sales', 'geography', 'kpi'];

        if (tab) {
            if (indexOfArray(availableTabs, tab[1].trim()) === -1) {
                dashboards.run('summary');
            } else {
                dashboards.run(tab[1]);
            }
        } else {
            dashboards.run('summary');
        }
    };

    /**
     * Main App Initializtion
     */
    App.init(function() {

        // Event Listeners for summary link
        eventListeners.add('summary-link', 'click', function(e) {
            dashboards.show('summary');
        });

        // Event Listeners for sales link
        eventListeners.add('sales-link', 'click', function(e) {
            dashboards.show('sales');
        });

        // Event Listeners for geography link
        eventListeners.add('geography-link', 'click', function(e) {
            dashboards.show('geography');
        });

        // Event Listeners for kpi link
        eventListeners.add('kpi-link', 'click', function(e) {
            dashboards.show('kpi');
        });

        urlHandler();

    });

});