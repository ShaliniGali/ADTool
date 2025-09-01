<?php
    $is_guest = $this->rbac_users->get_role('auth_guest');
    $is_admin = $this->rbac_users->get_role('auth_admin');
    $id = encrypted_string($usr_dt_upload, 'decode');
    if($is_admin){

        $result = $this->SOCOM_Database_Upload_Metadata_model->get_metadata_aggregate_admin_id($id);
    }else{
        $result = $this->SOCOM_Database_Upload_Metadata_model->get_metadata_with_cap_sponsor($id,  UploadType::DT_UPLOAD_EXTRACT_UPLOAD);
    }
?>
<div id="view-status-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <button class="modal-close-btn" id="close-status-modal">&times;</button>
        <div class="view-status-container">
            <h4>Status Information</h4>
            <pre id="status-text-area" style="white-space: pre-wrap; word-wrap: break-word;" hidden></pre>
            <table>
                <tbody>
                    <tr><th>Upload ID</th><td><?= htmlspecialchars($id) ?></td></tr>
                    <tr><th>File Name</th><td><?= htmlspecialchars($result['FILE_NAME']) ?></td></tr>
                    <tr><th>S3 Path</th><td><?= htmlspecialchars($result['S3_PATH']) ?></td></tr>
                    <tr><th>File Status</th><td><?= htmlspecialchars($result['FILE_STATUS']) ?></td></tr>
                    <tr><th>Type</th><td><?= htmlspecialchars($result['TYPE']) ?></td></tr>
                    <tr><th>Version</th><td><?= htmlspecialchars($result['VERSION']) ?></td></tr>
                    <tr><th>Cycle Name</th><td><?= htmlspecialchars($result['CYCLE_NAME']) ?></td></tr>
                    <tr><th>Sponsor</th><td><?= htmlspecialchars($result['CAP_SPONSOR']) ?></td></tr>
                    <tr><th>Created</th><td><?= htmlspecialchars($result['CREATED_TIMESTAMP']) ?></td></tr>
                    <tr><th>Updated</th><td><?= htmlspecialchars($result['UPDATED_TIMESTAMP']) ?></td></tr>
                    <tr><th>Final Table</th><td><?= htmlspecialchars($result['TABLE_NAME']) ?></td></tr>
                    <tr><th>Dirty Table</th><td><?= htmlspecialchars($result['DIRTY_TABLE_NAME']) ?></td></tr>
                    <tr><th>Revision</th><td><?= htmlspecialchars($result['REVISION']) ?></td></tr>
                    <tr><th>Cron Status</th><td><?= htmlspecialchars($result['CRON_STATUS']) ?></td></tr>
                    <tr><th>Cron Processed</th><td><?= htmlspecialchars($result['CRON_PROCESSED']) ?></td></tr>
                    <tr><th>Errors</th><td><?= htmlspecialchars($result['ERRORS']) ?: 'None' ?></td></tr>
                    <tr><th>Warnings</th>
                        <td>
                            <?php
                            if (!empty($result['WARNINGS'])) {
                                $warnings = json_decode($result['WARNINGS'], true);
                                foreach ($warnings as $warning) {
                                    echo htmlspecialchars($warning) . "<br>";
                                }
                            } else {
                                echo "None";
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>


        </div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
    padding: 20px;
    overflow: auto;
}

.modal-content {
    background: white;
    padding: 20px;
    width: 80vw;
    max-width: 800px;
    max-height: 80vh;
    border-radius: 8px;
    position: relative;
    box-shadow: 0 0 10px rgba(0,0,0,0.25);
    overflow-y: auto;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #222;
    line-height: 1.5;
}

.modal-close-btn {
    position: absolute;
    top: 10px;
    right: 14px;
    font-size: 24px;
    background: none;
    border: none;
    cursor: pointer;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-family: inherit;
    font-size: inherit;
    color: inherit;
}

table th, table td {
    text-align: left;
    padding: 6px;
    border-bottom: 1px solid #ccc;
    vertical-align: top;
}

table th {
    width: 180px;
    font-weight: 600;
}



</style>