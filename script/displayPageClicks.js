function ClickStatistic (config) {
    this.readConfig(config);
    
    if(this.day){
        
        this.displayDayGraph();
        this.printNavigation();
        return;
    }
    
    if(this.month){
        this.displayMonthGraph();
        this.printNavigation();
        return;
    }
    
    if(this.year){
        this.displayYearGraph();
        this.printNavigation();
        return;
    }
}

ClickStatistic.prototype.readConfig = function(config) {
    this.containerDivId = config.containerDivId || "container";
    this.navigationDivId = config.navigationDivId || "navigation";
    this.year = config.year || new Date().getFullYear();
    this.month = config.month || false;
    this.day = config.day || false;
    this.domain = config.domain || "";
    this.userAddonTemplate = "&user=1";
    this.userAddon = config.user ? this.userAddonTemplate : "";
    this.baseUrl = config.baseUrl || document.location.protocol +"//"+ document.location.hostname + document.location.pathname;
    this.months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun','Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    this.jsonAction = "getPageClicks";
    this.displayAction = "displayPageClicks";
}

ClickStatistic.prototype.displayYearGraph = function(){
    self = this;
    var jsonUrl = this.baseUrl+"?action="+this.jsonAction+this.userAddon+"&domain="+this.domain+"&year="+this.year;
    var clickUrl = this.baseUrl+"?action="+this.displayAction+this.userAddon+"&domain="+this.domain+"&year="+this.year;
    var graphData = new Array();

    $.getJSON(jsonUrl, function(data) {
        $.each(data, function(key, val){
            temp = new Object();     
            temp["y"] = val*1;
            temp["url"] = clickUrl+"&month="+key;
            graphData.push(temp);
        });
        xAxisUnits = self.months;
        self.printLineGraph(graphData,xAxisUnits,500);
    });
}

ClickStatistic.prototype.displayMonthGraph = function(){
    self = this;
    var jsonUrl = this.baseUrl+"?action="+this.jsonAction+this.userAddon+this.userAddon+"&domain="+this.domain+"&year="+this.year+"&month="+this.month;
    var clickUrl = this.baseUrl+"?action="+this.displayAction+this.userAddon+this.userAddon+"&domain="+this.domain+"&year="+this.year+"&month="+this.month;
    var graphData = new Array();
    var xAxisUnits = new Array();

    $.getJSON(jsonUrl, function(data) {
        $.each(data, function(key, val){
            xAxisUnits.push(key);
            temp = new Object();     
            temp["y"] = val*1;
            temp["url"] = clickUrl+"&day="+key;
            graphData.push(temp);
        });
        maxYAxis = 50;
        if(self.userAddon){
            maxYAxis = 20;
        }
        self.printLineGraph(graphData,xAxisUnits,maxYAxis);
    });
}

ClickStatistic.prototype.displayDayGraph = function(){
    self = this;
    var jsonUrl = this.baseUrl+"?action="+this.jsonAction+this.userAddon+"&domain="+this.domain+"&year="+this.year+"&month="+this.month+"&day="+this.day;
    var graphData = new Array();

    $.getJSON(jsonUrl, function(data) {
        
        formattedData = new Array();
        OneClickEntriesCount = 0;
        OneClickEntriesLabel = "";
        graphData = new Array();
        
        miscellaneous = 1;
        
        if(data.length<7 || self.userAddon){
            miscellaneous = 0;
        }
        visitorCount = 1;
        $.each(data, function(key, val){
            if((val['clicks']*1)===miscellaneous){
                OneClickEntriesCount ++;
                OneClickEntriesLabel+=val['url']+"<br>";
            }else{
                temp = new Object();
                temp["y"] = val['clicks']*1;
                temp["url"] = val['url'];
                if(self.userAddon){
                    temp["name"] = "visitor"+visitorCount;
                    visitorCount++;
                }
                else{
                    temp["name"] = val['url'].substr(val['url'].lastIndexOf("/",val['url'].length-2),val['url'].length);
                }
                
                //TODO shorten if neccessary
                graphData.push(temp);
                console.log(temp["name"]);
            }
        });
        if(OneClickEntriesCount>0){
            graphData.push({y:OneClickEntriesCount,url:OneClickEntriesLabel,name:"misc"});
        }
        
        
        
        self.printPieGraph(graphData);
    });
}

ClickStatistic.prototype.printLineGraph = function(graphData,xAxisUnits,yAxisMax){
    self = this;
    if(self.userAddon){
        unit = "visitors";
    }
    else{
        unit = "unique page calls";
    }
    $(function () {
        $('#'+self.containerDivId).highcharts({
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
                min : 0,
                max  : yAxisMax,
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

ClickStatistic.prototype.printPieGraph = function(graphData){    
    $(function () {
        $('#container').highcharts({
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
                pointFormat: '{point.url}',
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

ClickStatistic.prototype.printNavigation = function(){
    self = this;
    
    
    if(this.day){
        
        navigation = "<a href=\""+self.baseUrl+"?action="+self.displayAction+"&domain="+self.domain+"&year="+self.year+"\">"+self.year+"</a><br/>";
        navigation += "<a href=\""+self.baseUrl+"?action="+self.displayAction+self.userAddon+"&domain="+self.domain+"&year="+self.year+"&month="+self.month+"\">"+self.months[self.month-1]+"</a><br/>";
        
        daysInMonth = this.daysInMonth(self.year,self.month);
        for(i=1;i<=daysInMonth;i++){
            if(i === (this.day*1)){
                navigation += " "+i;
            }
            else{
                navigation += " <a href=\""+self.baseUrl+"?action="+self.displayAction+self.userAddon+"&domain="+self.domain+"&year="+self.year+"&month="+self.month+"&day="+i+"\">"+i+"</a>";
            }
        }
        navigation += "<br/>";
        if(this.userAddon){
            navigation += "<a href =\""+self.baseUrl+"?action="+self.displayAction+"&domain="+self.domain+"&year="+self.year+"&month="+self.month+"&day="+self.day+"\">unique page actions</a> visitors";
        }
        else{
            navigation += "unique page actions <a href =\""+self.baseUrl+"?action="+self.displayAction+self.userAddonTemplate+"&domain="+self.domain+"&year="+self.year+"&month="+self.month+"&day="+self.day+"\">visitors</a>";
        }
        
        
        $("#"+this.navigationDivId).html(navigation);
        return;
    }
    
    if(this.month){
        
        navigationMonths = new Array();
        ctr = 1;
        $.each(this.months, function(key, val){
            temp = new Array();
            temp["name"] = val;
            temp["active"] = (ctr == self.month);
            temp["url"] = self.baseUrl+"?action="+self.displayAction+self.userAddon+"&domain="+self.domain+"&year="+self.year+"&month="+ctr;
            navigationMonths.push(temp);
            ctr++;
        });
        
        navigation = "<a href=\""+self.baseUrl+"?action="+self.displayAction+"&domain="+self.domain+"&year="+self.year+"\">"+self.year+"</a><br/>";
        
        $.each(navigationMonths, function(key, month){
            if(month.active){
                navigation+= " "+month.name;
            }
            else{
                navigation+= " <a href=\""+month.url+"\">"+month.name+"</a>";
            }
        });
        
        navigation += "<br/>";
        
        if(this.userAddon){
            navigation += "<a href =\""+self.baseUrl+"?action="+self.displayAction+"&domain="+self.domain+"&year="+self.year+"&month="+self.month+"\">unique page actions</a> visitors";
        }
        else{
            navigation += "unique page actions <a href =\""+self.baseUrl+"?action="+self.displayAction+self.userAddonTemplate+"&domain="+self.domain+"&year="+self.year+"&month="+self.month+"\">visitors</a>";
        }
        
        $("#"+this.navigationDivId).html(navigation);
        return;
    }
    
    if(this.year){
        yearUrlBase = self.baseUrl+"?action="+self.displayAction+"&domain="+self.domain+"&year=";
        
        navigation = " <a href=\""+yearUrlBase+(((self.year)*1)-1)+"\">"+(((self.year)*1)-1)+"</a>";
        navigation += " "+self.year;
        if(new Date().getFullYear() != this.year){
            navigation += " <a href=\""+yearUrlBase+(((self.year)*1)+1)+"\">"+(((self.year)*1)+1)+"</a>";
        }
        $("#"+this.navigationDivId).html(navigation);
        
        return;
    }
}

ClickStatistic.prototype.daysInMonth = function(year,month){
    return new Date(year, month, 0).getDate();
}