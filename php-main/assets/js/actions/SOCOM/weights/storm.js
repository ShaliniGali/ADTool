let load_storm_table = function() {
    load_storm_table = null;
    $('#storm-score-display').DataTable({
        columnDefs: [{
                targets: 0,
                data: "storm_id",
                name: "StoRM ID",
                defaultContent: ''
            },
            {
                targets: 1,
                data: "storm",
                name: "StoRM",
                defaultContent: ''
            }
        ],
        ajax: {
            url: "/socom/resource_constrained_coa/program/list/get_storm",
            type: 'POST',
            data: {
                rhombus_token: function() { return rhombuscookie(); },
            },
            dataSrc: 'data',
        },
        length: 10,
        lengthChange: true,
        orderable: false,
        ordering: false,
        searching: true,
        rowHeight: '75px'
    });
}

window._rb = {
    load_storm_table: load_storm_table
}