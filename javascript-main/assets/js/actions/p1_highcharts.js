// JS file to manually load properties
function p1_highcharts() {
    if(P1_FLAG){
        if(typeof heatmap === "function"){
            heatmap(Highcharts);
        }
        if(typeof highchartsMore === "function"){
            highchartsMore(Highcharts)
        }
        if(typeof highchartsSankey === "function"){
            highchartsSankey(Highcharts)
        }
        if(typeof histogram_bellcurve === "function"){
            histogram_bellcurve(Highcharts)
        }
        if(typeof indicators === "function"){
            indicators(Highcharts)
        }
        if(typeof trendline === "function"){
            trendline(Highcharts)
        }
        if(typeof highchartsExport === "function"){
            highchartsExport(Highcharts)
        }
        if(typeof treemap === "function"){
            treemap(Highcharts)
        }
        if(typeof highchartsDrilldown === "function"){
            highchartsDrilldown(Highcharts)
        }
    }
}

$(p1_highcharts)

if (!window._rb) window._rb = {}
window._rb.p1_highcharts = p1_highcharts;