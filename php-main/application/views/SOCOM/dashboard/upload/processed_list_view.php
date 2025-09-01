<div class="card-body w-100">
    <h3 class="text-center mb-3"><b>Program Import Jobs Processing Complete</b></h3>
    <!-- Program Import Jobs Processing Complete and Successful using Guardian Automation -->
    <table id="processed-list" class="bx--data-table w-100">
        <thead>
            <tr>
                <th scope="col">
                    File Name
                </th>
                <th scope="col">
                    Version
                </th>
                <th scope="col">
                    File Status
                </th>
                <th scope="col">
                    Cron Processed Status
                </th>
                <th scope="col">
                    Errors
                </th>
                <th scope="col">
                    Updated Timestamp
                </th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    if (typeof get_processed_table_columns_program_alignment === 'undefined') {
        function get_processed_table_columns_program_alignment() {
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
					title: 'Cron Status',
					data: 'FILE_STATUS_TXT'
				},
                {
					title: 'Cron Processed',
					data: 'CRON_PROCESSED_TXT'
				},
                {
					title: 'Errors',
					data: 'ERRORS'
				},
				{
					title: 'Updated',
					data: 'UPDATED_TIMESTAMP'
				}
            ];
        }
    }

    $(document).on('click', '.view-status-btn', function(){
        const table = $('#processed-list2').DataTable();
        const row = table.row($(this).closest('tr')).data();
        update_modal_content(row);
        $('#status_modal > .bx--modal.bx--modal-tall').addClass('is-visible');
    });

    $(document).ready(function () {
        const table = $('#processed-list').DataTable();
        table.on('draw', function () {
            $('#processed-list .view-status-btn').closest('td').css({
                width: '0px',
                padding: '0',
                border: 'none'
            }).empty();
        });
    });
</script>