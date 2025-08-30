// JS file to manually load properties
function p1_socom() {
    if(P1_FLAG){
        CarbonComponents.NavigationMenu.init();
        CarbonComponents.Modal.init();
        CarbonComponents.Accordion.init();
        CarbonComponents.Tab.init();
        CarbonComponents.Checkbox.init();
        CarbonComponents.Notification.init();
        CarbonComponents.DatePicker.init();
        CarbonComponents.Tooltip.init();
        CarbonComponents.OverflowMenu.init();
        CarbonComponents.ProductSwitcher.init();
        CarbonComponents.NumberInput.init();
        CarbonComponents.Dropdown.init();
        CarbonComponents.ContentSwitcher.init()
        
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
    }
}

$(p1_socom)

if (!window._rb) window._rb = {}
window._rb.p1_socom = p1_socom;