<style>
   .status-info-table {
       border-collapse: collapse;
       width: 100%;
       margin-top: 1rem;
       background-color: white;
   }
   .status-info-table th, .status-info-table td {
       border: 1px solid #ccc;
       padding: 12px;
       text-align: left;
   }
   .status-info-table th {
       background-color: #f4f4f4;
       width: 30%;
   }
   .status-info-table td {
       background-color: #fafafa;
   }
   .status-info-container {
       padding: 1rem;
   }
</style>
<div class="status-info-container">
    <table class="cds--data-table status-info-table">
        <tbody>
            <tr>
                <th>Type</th>
                <td id="type-name">-</td>
            </tr>
            <tr>
                <th>Upload Owner</th>
                <td id="upload-owner">-</td>
            </tr>
            <tr>
                <th>Upload Date Time</th>
                <td id="upload-datetime">-</td>
            </tr>
            <tr>
                <th>Active</th>
                <td id="is-active-status">-</td>
            </tr>
            <tr>
                <th>Table Name</th>
                <td id="upload-table-name">-</td>
            </tr>
            <tr>
                <th>Table Create Time</th>
                <td id="upload-table-create">-</td>
            </tr>
            <tr>
                <th>Table Update Time</th>
                <td id="upload-table-update">-</td>
            </tr>
            <tr>
                <th>SQL Server Time</th>
                <td id="current-time">-</td>
            </tr>
        </tbody>
    </table>
</div>

<div id="status-action-buttons" class="mt-3 d-flex justify-content-end gap-3 px-4">

        <button class="bx--btn bx--btn--primary" id="view-table-btn">
                    View
        </button>
        <button class="bx--btn bx--btn--primary ml-3" id="edit-table-btn">
                    Edit
        </button>
</div>

<script>
    const is_guest_user = <?= $this->rbac_users->is_guest() ? 'true' : 'false' ?>;
</script>
 