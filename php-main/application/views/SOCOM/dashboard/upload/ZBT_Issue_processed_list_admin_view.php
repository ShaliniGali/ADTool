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
    <h3 class="text-center mb-3"><b>Admin - ZBT Events and Issue Events Import Jobs Processing Complete</b></h3>

    <table id="admin-list5" class="bx--data-table w-100">
        <thead>
            <tr>
                <th>Final Table Name</th>
                <th>Dirty Table Name</th>
                <th>Dirty Table Revision</th>
                <th>Status</th>
                <th>Approve</th>
                <th>Final Table Active</th>
                <th>Created</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    if (typeof get_admin_table_columns_zbt_issue === 'undefined') {
        function get_admin_table_columns_zbt_issue() {
            function renderOrNull(data) {
                return (data === null || data === undefined || data.toString().trim() === '')
                    ? '<span style="color: #888;"></span>'
                    : data;
            }

            return [
                { title: 'Final Table Name', data: 'FINAL_TABLE_NAME', render: renderOrNull },
                { title: 'Dirty Table Name', data: 'DIRTY_TABLE_NAME', render: renderOrNull },
                { title: 'Revision', data: 'REVISION', render: renderOrNull },
                { title: 'Status', 
                    data: null,
                    orderable: false,
                    render: function () {
                        return `
                            <button class="bx--btn bx--btn--tertiary bx--btn--sm view-status-btn-5" data-table-id="admin-list5">
                                View Status
                            </button>`;
                    } 
                },
                {
                    title: 'Approve',
                    data: '',
                    orderable: false,
                    render: function (data, type, row) {
                        if (row && row['IS_FINAL_TABLE_ACTIVE'] === 0) {
                            return `
                                <button class="bx--btn bx--btn--primary bx--btn--sm activate-btn admin-submit-btn" 
                                        data-id="${row['ID']}">
                                    Approve
                                </button>`;
                        }
                        return '<span style="color:#888;"></span>';
                    }
                },
                { title: 'Final Table Active', data: 'IS_FINAL_TABLE_ACTIVE', render: function (data, type, row) {
                        return row['IS_FINAL_TABLE_ACTIVE'] === 1 ? 'True' : 'False'
                    }
                },
                { title: 'Created', data: 'CREATED_TIMESTAMP', render: renderOrNull },
                { title: 'Last Updated', data: 'UPDATED_TIMESTAMP', render: renderOrNull },
            ];
        }
    }

    $(document).on('click', '#admin-list5 .view-status-btn-5', function () {
        const table = $('#admin-list5').DataTable();
        const row = table.row($(this).closest('tr')).data();
        update_modal_content_zbt_iss_admin(row);
        $('#status_modal_admin > .bx--modal.bx--modal-tall').addClass('is-visible');
    
    });
     $(document).on('click', '#admin-list5 .activate-btn', handleSaveApproveClick);
</script>
