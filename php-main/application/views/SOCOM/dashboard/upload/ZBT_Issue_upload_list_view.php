<div class="card-body w-100">
    <h3 class="text-center mb-3"><b>ZBT Events and Issue Events Import Jobs</b></h3>
    <!-- Your Program Import Jobs Recently Uploaded and require Process Action before Guardian Automation will complete !-->
    <table id="upload-list4" class="bx--data-table w-100">
        <thead>
            <tr>
                <th scope="col">
                    File Name
                </th>
                <th scope="col">
                    Version
                </th>
                <th scope="col">
                    Cycle Name
                </th>
                <th scope="col">
                    Description
                </th>
				<th scope="col">
                    Updated Timestamp
                </th>
                <th scope="col">
                    FILE_STATUS_TXT
                </th>
                <th scope="col">
				</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>

if (typeof get_upload_table_columns === 'undefined') {
function get_upload_table_columns() {
    return [
                    {
                        title: 'File Name',
                        data: 'FILE_NAME'
                        
                    },
                    {
                        title: 'Version',
                        data: 'VERSION'
                    },
                    {
                        title: 'Cycle Name',
                        data: 'CYCLE_NAME'
                    },
                    {
                        title: 'Description',
                        data: 'DESCRIPTION'
                    },
                    {
                        title: 'Updated',
                        data: 'UPDATED_TIMESTAMP',
                    },
                    {
                        title: 'File Status',
                        data: 'FILE_STATUS_TXT'
                    },
                    {
                        'width': 100,
                        searchable: false,
                        orderable: false,
                        render: data => `<?php $this->load->view('SOCOM/dashboard/upload/list_overflow_menu', ['id' => '']); ?>`
                    }
            ];
    }
}
</script>