<div class="card-body w-100">
    <h3 class="text-center mb-3"><b>Upload Notifications</b></h3>
    <table id="upload-notifications" class="bx--data-table w-100">
        <thead>
            <tr>
                <th scope="col">
                    Message
                </th>
                <th scope="col">
                    Time
                </th>
                <th scope="col">
                    Status
                </th>
                <th scope="col">
                    Acknowledge
                </th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-btn')) {
            const row = e.target.closest('tr');
            acknowledgeNotification(row.id)
        }
    });
</script>

<style>
    .unread-message {
    font-weight: bold;
    }
</style>