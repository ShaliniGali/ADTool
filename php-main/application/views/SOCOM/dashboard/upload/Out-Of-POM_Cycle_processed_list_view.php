<style>
      .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
    }

    table.dataTable {
        width: 100% !important;
        min-width: max-content;
    }
</style>
<div class="card-body w-100">
    <h3 class="text-center mb-3"><b>Out-Of-POM Cycle Import Jobs Processing Complete</b></h3>
    <!-- Program Import Jobs Processing Complete and Successful using Guardian Automation -->
    <table id="processed-list3" class="bx--data-table w-100">
        <thead>
            <tr>
                <th scope="col">
                    File Name
                </th>
                <th scope="col">
                    Version
                </th>
                <th scope="col">
                    Table Type
                </th>
                <th scope="col">
                    File Status
                </th>
                <th scope="col">
                    Cron Processed Status
                </th>
                <th scope="col">
                    Warnings
                </th>
                <th scope="col">
                    Errors
                </th>
                <th scope="col">
                    Updated Timestamp
                </th>
                <th scope="col">
                    Status
                </th>
                <th scope="col">
                    Activate
                </th>
                <th scope="col">
                    Active
                </th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    $(document).ready(function () {
        $('#processed-list3').DataTable({
            scrollX: true,
            columns: get_processed_table_columns_out_of_pom()
        });
    });
        if (typeof get_processed_table_columns_out_of_pom === 'undefined') {
        function get_processed_table_columns_out_of_pom() {
            return [
                {
					title: 'File Name',
					data: 'FILE_NAME',
					
				},
				{
					title: 'Version',
					data: 'VERSION',
				},
                {
					title: 'Table type',
					data: 'TABLE_TYPE_DESCR'
				},
                {
					title: 'Cron Status',
					data: 'FILE_STATUS_TXT'
				},
                {
					title: 'Cron Processed',
					data: 'CRON_PROCESSED_TXT'
				},
                 {
                    title: 'Warnings',
                    data: 'WARNINGS',
                    orderable: false,
                    render: function(data, type, row, meta) {
                    if (!data || data.trim() === '') return '';
                    let warningsEncoded = encodeURIComponent(data);
                    return `
                        <button class="bx--btn bx--btn--primary bx--btn--sm view-warning-btn"
                                data-warnings="${warningsEncoded}"
                                data-table-id="processed-list4">
                            View Warnings
                        </button>
                    `;
                }
                },
                {
                    title: 'Errors',
                    data: 'ERRORS', 
                    orderable: false,
                    render: function(data, type, row, meta) {
                    if (!data || data.trim() === '') 
                            return '';
                    let errorsEncoded = encodeURIComponent(data);
                    return `
                        <button class="bx--btn bx--btn--primary bx--btn--sm view-error-btn"
                                data-errors="${errorsEncoded}"
                                data-table-id="processed-list4">
                            View Errors
                        </button>
                    `;
                }

                },
				{
					title: 'Updated',
					data: 'UPDATED_TIMESTAMP'
				},
                {
                    title: 'Status',
                    data: null,
                    orderable: false,
                    render: function() {
                        return `
                            <button class="bx--btn bx--btn--primary bx--btn--sm view-status-btn-3" data-table-id="processed-list3">
                                View Status
                            </button>
                        `;
                    }
                },
                {
                    title: 'Activate',
                    data: '',
                    orderable: false,
                    render: function(data, type, row, meta) {
                        if (row != null && row['CRON_PROCESSED_TXT'] === 'Completed' && row['IS_ACTIVE'] != 1) {
                            return `
                            <button id="out-of-pom-activate-${row['ID']}" class="bx--btn bx--btn--primary bx--btn--sm activate-btn"
                            onclick="activate_upload('${row['ID']}')">
                                Activate
                            </button>
                        `;
                        }
                        return `
                            <button class="bx--btn bx--btn--primary bx--btn--sm activate-btn" hidden>
                                Activate
                            </button>
                        `;
                    }
                },
                {
                    title: 'Active',
                    data: 'IS_ACTIVE',
                    render: function(data, type, row) {
                        return data == 1 ? 'True' : 'False';
                    }
                },
            ];
        }
    }
</script>
<script>
$(document).on('click', '.view-warning-btn', function () {
    const tableId = $(this).data('table-id');
    const table = $('#' + tableId).DataTable();
    const row = table.row($(this).closest('tr')).data();

    let warningText = decodeURIComponent($(this).attr('data-warnings') || '');
    if ((!warningText || warningText === 'undefined') && row && row.WARNINGS) {
        warningText = row.WARNINGS;
    }

    $('#warning_text_area').text(warningText || 'No warnings available.');
    $('#warning_modal').find('.bx--modal').addClass('is-visible');
});

 $(document).on('click', '.view-error-btn', function () {
    const tableId = $(this).data('table-id');
    const table = $('#' + tableId).DataTable();

    const row = table.row($(this).closest('tr')).data();

    let errorText = decodeURIComponent($(this).attr('data-errors') || '');
    if (!errorText && row && row.ERROR) {
        errorText = row.ERROR;
    }

    $('#error_text_area').text(errorText);
    $('#error_modal').find('.bx--modal').addClass('is-visible');
});

$(document).on('click', '.view-status-btn-3', function(){
    const table = $('#processed-list3').DataTable();
    const row = table.row($(this).closest('tr')).data();
    update_modal_content_out_of_pom(row);
    $('#status_modal > .bx--modal.bx--modal-tall').addClass('is-visible');
});

$(document).ready(function () {
    const table = $('#processed-list').DataTable();
    table.on('draw', function () {
        $('#processed-list .view-status-btn-3').closest('td').css({
            width: '0px',
            padding: '0',
            border: 'none'
        }).empty();
    });
});
</script>