const columnDefsPage = [
    {
        targets: 0,
        data: "table_name",
        title: "Table Name",
        searchable: true,
        orderable: true,
        defaultContent: ''
    },
    {
        targets: 1,
        data: "page",
        title: "Page",
        searchable: true,
        orderable: true,
        defaultContent: ''
    },
    {
        targets: 2,
        data: "position",
        title: "Position",
        searchable: true,
        orderable: true,
        defaultContent: ''
    },
    {
        targets: 3,
        data: "set",
        title: "Data Set",
        searchable: true,
        orderable: true,
        defaultContent: ''
    },
    {
        targets: 4,
        data: "availability",
        title: "Availability",
        searchable: true,
        orderable: true,
        defaultContent: ''
    }
];

const columnDefsPom = [
    {
        targets: 0,
        data: "table_name",
        title: "Table Name",
        searchable: true,
        orderable: true,
        defaultContent: ''
    },
    {
        targets: 1,
        data: "availability",
        title: "Availability",
        searchable: true,
        orderable: true,
        defaultContent: ''
    }
];

let loadPomTable = function(id, columnDefs, page, $avail = 1, init = function() { }) {
    $(`#${id}`).DataTable({
        columnDefs: columnDefs,
        ajax: {
            url: `/dashboard/pom/get_tables_exist/${page}`, 
            type: 'POST',
            data: {
                pom_position: function() { return pom_position; },
                pom_year: function() { return pom_year; },
                latest_pom_year: latest_pom_year,
                rhombus_token: function() { return rhombuscookie(); },
            }
        },
        rowCallback: function(row, data, index) {
            if (data['availability'] === 1) {
                $('td', row).eq($avail).empty().append('<i class="text-success fas fa-database"></i><span class="ml-2">Available in POM</span>');
            } else if (data['availability'] === 2) {
                $('td', row).eq($avail).empty().append('<i class="text-warning fas fa-database"></i><span class="ml-2">Available from Previous POM</span>');
            }  else {
                $('td', row).eq($avail).empty().append('<i class="text-danger fas fa-database"></i><span class="ml-2">Not Available</span>');
            }
        },
        init: init()
    });
 };

 $(function() {
    let loadTables = function() {
        loadPomTable('active-pom-table', columnDefsPom, '', 1, function() {
            loadPomTable('active-zbt-table', columnDefsPage, 'ZBT_SUMMARY', 4, function() {
                loadPomTable('active-iss-table', columnDefsPage, 'ISS_SUMMARY', 4, function() {
                    loadPomTable('active-coa-table', columnDefsPage, 'RESOURCE_CONSTRAINED_COA', 4, function() {
                        //loadPomTable = null;
                    })
                })
            })
        });

        $('#pom-center-admin').off('click', loadTables);

        loadTables = null;
    };

    $('#pom-center-admin').on('click', loadTables);
 })
