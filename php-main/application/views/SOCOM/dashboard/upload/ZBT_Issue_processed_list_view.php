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
    <h3 class="text-center mb-3"><b>ZBT Events and Issue Events Import Jobs Processing Complete</b></h3>
    <!-- Program Import Jobs Processing Complete and Successful using Guardian Automation -->
    <table id="processed-list4" class="bx--data-table w-100">
        <thead>
            <tr>
                <th scope="col">
                    File Name
                </th>
                <th scope="col">
                    Version
                </th>
                <th scope="col">
                    Cap Sponsor
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
                    Activate
                </th>
                <th scope="col">
                    Status
                </th>
                <th scope="col">
                    Submit
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
        $('#processed-list4').DataTable({
            scrollX: true,
            columns: get_processed_table_columns_zbt_issue()
        });
    });
        if (typeof get_processed_table_columns_zbt_issue === 'undefined') {
        function get_processed_table_columns_zbt_issue() {
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
					title: 'Cap Sponsor',
					data: 'CAP_SPONSOR',
				},
                {
					title: 'Table Type',
					data: 'TABLE_TYPE_DESCR', render: function (data, type, row) {
                        if(row['TABLE_TYPE_DESCR'] === 'ZBT_EXTRACT')
                            return 'ZBT Events';
                        else if(row['TABLE_TYPE_DESCR'] === 'ISS_EXTRACT')
                            return 'ISS Events';
                        else
                            return row['TABLE_TYPE_DESCR'];
                    }
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
                    if (!data || data.trim() === '') 
                            return '';
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
                            <button class="bx--btn bx--btn--primary bx--btn--sm view-status-btn-4" data-table-id="processed-list4">
                                View Status
                            </button>
                        `;
                    }
                },
                {
                    title: 'Activate',
                    data: '',
                    orderable: false,
                    render: function (data, type, row) {
                        if (row && row['IS_ACTIVE'] != 1 && row['CRON_PROCESSED'] == 1) {
                            return `
                                <button class="bx--btn bx--btn--primary bx--btn--sm cap-parse-btn cap-sponsor-parse-btn" 
                                        data-id="${row['ID']}">
                                    Parse
                                </button>`;
                        }
                        return '<span style="color:#888;"></span>';
                    }
                },
                {
                    title: 'Submit',
                    data: '',
                    orderable: false,
                    render: function (data, type, row) {
                        if (row && row['IS_ACTIVE'] == 1) {
                            return `
                                <button class="bx--btn bx--btn--primary bx--btn--sm cap-submit-btn cap-sponsor-submit-btn" 
                                        data-id="${row['ID']}">
                                    Submit
                                </button>`;
                        }
                        return '<span style="color:#888;"></span>';
                    }
                },
                {
                    title: 'Active',
                    data: 'IS_ACTIVE',
                    render: function(data, type, row) {
                        return data == 1 ? 'True' : 'False';
                    }
                }
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

$(document).on('click', '#processed-list4 .view-status-btn-4', function(){
    const table = $('#processed-list4').DataTable();
    const row = table.row($(this).closest('tr')).data();
    update_modal_content_zbt_iss_guest(row);
    $('#status_modal_cap_sponsor > .bx--modal.bx--modal-tall').addClass('is-visible');
});

$(document).ready(function () {
    const table = $('#processed-list').DataTable();
    table.on('draw', function () {
        $('#processed-list .view-status-btn-4').closest('td').css({
            width: '0px',
            padding: '0',
            border: 'none'
        }).empty();
    });
});

 $(document).on('click', '#processed-list4 .cap-submit-btn', handleSaveSubmitClick);
 $(document).on('click', '#processed-list4 .cap-parse-btn', handleSaveParseClick);

</script>