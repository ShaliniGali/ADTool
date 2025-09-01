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

<div class="status-admin-info-container">
    <table class="cds--data-table status-info-table">
        <tbody>
            <tr>
                <th>Uploader Email ID</th>
                <td id="email-id">-</td>
            </tr>
            <tr>
                <th>Capability Sponsor</th>
                <td id="cap-sponsor">-</td>
            </tr>
            <tr>
                <th>Title</th>
                <td id="title">-</td>
            </tr>
            <tr>
                <th>Submission Status</th>
                <td id="submission-status">-</td>
            </tr>
        </tbody>
    </table>
</div>

<div id="status-modal-pagination" class="d-flex justify-content-center align-items-center gap-3 mt-3">
    <button id="status-prev" class="bx--btn bx--btn--secondary">Previous</button>
    <span id="status-page-info">Page 1 of 1</span>
    <button id="status-next" class="bx--btn bx--btn--secondary">Next</button>
</div>

<div id="status-admin-action-buttons" class="mt-3 d-flex justify-content-end gap-3 px-4">

        <button class="bx--btn bx--btn--primary ml-3" id="view-admin-table-btn">
                    View
        </button>
        <button class="bx--btn bx--btn--primary ml-3" id="edit-admin-table-btn">
                    Edit
        </button>
</div>

<script>
    let currentStatusPage = 0;
    let statusData = [];
    $(document).off('click', '#status-prev').on('click', '#status-prev', function () {
        if (currentStatusPage > 0) {
            currentStatusPage--;
            renderStatusPage(currentStatusPage);
        }
    });

    $(document).off('click', '#status-next').on('click', '#status-next', function () {
        if (currentStatusPage < statusData.length - 1) {
            currentStatusPage++;
            renderStatusPage(currentStatusPage);
        }
    });
</script>