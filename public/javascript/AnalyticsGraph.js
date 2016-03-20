function AnalyticsGraph(config, overwriteConfig) {
    this.readConfig(config, overwriteConfig);
    var graphPainted = false;

    if (this.day) {
        this.displayDayGraph();
        graphPainted = true;
    }

    if (!graphPainted && this.month) {
        this.displayMonthGraph();
        graphPainted = true;
    }

    if (!graphPainted && this.year) {
        this.displayYearGraph();
        graphPainted = true;
    }

    this.printNavigation();
}

AnalyticsGraph.prototype.readConfig = function (config, overwriteConfig) {

    if (overwriteConfig) {
        $.each(overwriteConfig, function (key, value) {
            config[key] = value;
        });
    }

    if (
            !config.domain
            || !config.containerDivId
            || !config.src
            ) {
        throw "required parameters: domain, containerDivId, src"
    }

    this.domain = config.domain;
    this.containerDivId = config.containerDivId;
    this.src = config.src;

    this.year = config.year || new Date().getFullYear();
    this.month = config.month || false;
    this.day = config.day || false;

    this.months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

}

AnalyticsGraph.prototype.getConfigAsJsonString = function () {
    var config = "{" +
            "domain:'" + this.domain + "'," +
            "containerDivId:'" + this.containerDivId + "'," +
            "src:'" + this.src + "'," +
            "year:'" + this.year + "',";
    if (this.month) {
        config += "month:'" + this.month + "',";
    }
    if (this.day) {
        config += "day:'" + this.day + "',";
    }
    config += "}";
    return config;
}

AnalyticsGraph.prototype.displayYearGraph = function () {
    self = this;
    var jsonUrl = self.src + self.domain + "/" + self.year + "/";
    var graphData = new Array();
    yAxisMax = 500;

    var config = self.getConfigAsJsonString();

    $.getJSON(jsonUrl, function (data) {
        console.log(data);
        $.each(data, function (key, value) {
            temp = new Object();
            temp["y"] = value * 1;
            temp["url"] = "javascript:var tmp = new AnalyticsGraph(" +
                    config + ", {month:'" + key + "'});";
            graphData.push(temp);
        });
        xAxisUnits = self.months;
        self.printLineGraph(graphData, xAxisUnits, yAxisMax);
    });
}

AnalyticsGraph.prototype.displayMonthGraph = function () {
    self = this;
    var jsonUrl = self.src + self.domain + "/" + self.year + "/" + self.month + "/";
    var xAxisUnits = new Array();
    var graphData = new Array();
    yAxisMax = 50;

    var config = self.getConfigAsJsonString();

    $.getJSON(jsonUrl, function (data) {
        $.each(data, function (key, value) {
            xAxisUnits.push(key);
            temp = new Object();
            temp["y"] = value * 1;
            temp["url"] = "javascript:var tmp = new AnalyticsGraph(" +
                    config + ", {day:'" + key + "'});";
            graphData.push(temp);
        });

        self.printLineGraph(graphData, xAxisUnits, yAxisMax);
    });
}

AnalyticsGraph.prototype.displayDayGraph = function () {
    self = this;
    var jsonUrl = self.src + self.domain + "/" + self.year + "/" + self.month + "/" + self.day + "/";
    var graphData = new Array();

    $.getJSON(jsonUrl, function (data) {
        var totalCount = 0;
        var itemsCount = 0;
        $.each(data, function (key, value) {
            totalCount += value * 1;
            itemsCount++;
        });

        var bundleLittleItems = false;
        //TODO find good balance
        if (itemsCount > 7) {
            bundleLittleItems = true;
        }

        var littleItems = "";
        var littleItemsCount = 0;
        $.each(data, function (key, value) {

            if (bundleLittleItems && value * 1 === 1) {
                littleItems += key + "<br/>";
                littleItemsCount += 1;
            } else {
                temp = new Object();
                temp["y"] = value * 1;
                temp["info"] = (value * 1 === 1) ? "1 visit" : (value * 1) + " visits" ;
                temp["name"] = key;
                graphData.push(temp);
            }
        });

        if (bundleLittleItems) {
            graphData.push({y: littleItemsCount, info: littleItems, name: "misc"});
        }
        self.printPieGraph(graphData);
    });
}


AnalyticsGraph.prototype.printLineGraph = function (graphData, xAxisUnits, yAxisMax) {
    self = this;
    if (self.userAddon) {
        unit = "visitors";
    } else {
        unit = "unique page calls";
    }
    $(function () {
        $('#' + self.containerDivId).highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: '',
                x: -20 //center
            },
            subtitle: {
                text: '',
                x: -20
            },
            xAxis: {
                categories: xAxisUnits
            },
            yAxis: {
                title: {
                    text: unit
                },
                min: 0,
                max: yAxisMax,
                plotLines: [{
                        color: '#808080'
                    }]
            },
            plotOptions: {
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function () {
                                location.href = this.options.url;
                            }
                        }
                    }
                }
            },
            tooltip: {
                valueSuffix: ' ' + unit
            },
            series: [{
                    name: self.domain,
                    data: graphData
                }]
        });
    });
}

AnalyticsGraph.prototype.printPieGraph = function (graphData) {
    $(function () {
        $('#' + self.containerDivId).highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: ''
            },
            tooltip: {
                pointFormat: '{point.info}',
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{y}x {point.name}</b>',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        }
                    }
                }
            },
            series: [{
                    name: "Brands",
                    colorByPoint: true,
                    data: graphData
                }]
        });
    });
}

AnalyticsGraph.prototype.printNavigation = function () {
    self = this;
    if ($("#" + self.containerDivId + "-navigation").length === 0) {
        var $chart = $("#" + self.containerDivId);
        $('<div id="' + self.containerDivId + '-navigation"></div>').insertBefore($chart).width($chart.width());
    }

    $navigation = $("#" + self.containerDivId + '-navigation');
    var config = self.getConfigAsJsonString();

    var fullNavigation = "";
    
    
    
    fullNavigation = '<style>' + 
            '#' + self.containerDivId + '-navigation p ' + 
            '{font-family: "Lucida Grande","Lucida Sans Unicode",Arial,Helvetica,sans-serif;font-size: 16px;text-align:center;} ' +
            '#' + self.containerDivId + '-navigation p a ' + 
            '{text-decoration: none; color: #7cb5ec} ' +
            '#' + self.containerDivId + '-navigation p.days ' + 
            '{font-size: 12px;} ' +
            '</style>'; 

    var yearNavigation = '<p class="years">';
    yearNavigation += '<a href="javascript:var tmp = new AnalyticsGraph(' + config + ", {year:'" + (self.year * 1 - 1) + "',month:false,day:false});" + '">' + (self.year * 1 - 1) + '</a> ';
    yearNavigation += self.year;
    if (new Date().getFullYear() !== self.year * 1) {
        yearNavigation += ' <a href="javascript:var tmp = new AnalyticsGraph(' + config + ", {year:'" + (self.year * 1 + 1) + "',month:false,day:false});" + '">' + (self.year * 1 + 1) + '</a>';
    }
    fullNavigation += yearNavigation;

    if (self.month) {
        var monthNavigation = '<p class="months">';
        var monthNumber = 0;
        $.each(self.months, function (key, monthName) {
            monthNumber++;

            if (self.month * 1 === monthNumber) {
                monthNavigation += " " + monthName;
            } else {
                monthNavigation += ' <a href="javascript:var tmp = new AnalyticsGraph(' + config + ", {month:" + monthNumber + ",day:false});" + '">' + monthName + '</a>';
            }
        });
        monthNavigation += "</p>";
        fullNavigation += monthNavigation;
    }

    if (self.day) {
        var daysInMonth = new Date(self.year, self.month, 0).getDate();
        var dayNavigation = '<p class="days">';
        for (var dayNumber = 1; dayNumber <= daysInMonth; dayNumber++) {
            console.log(self.day * 1 + " "+  dayNumber);
            if (self.day * 1 === dayNumber) {
                dayNavigation += " " + (dayNumber < 10 ? "0" : "") + dayNumber;
            } else {
                dayNavigation += ' <a href="javascript:var tmp = new AnalyticsGraph(' + config + ", {day:" + dayNumber + "});" + '">' + (dayNumber < 10 ? "0" : "") + dayNumber + '</a>';
            }
        }


        dayNavigation += "</p>";
        fullNavigation += dayNavigation;
    }
    $navigation.html(fullNavigation);

}