<?php 
$is_pom_admin = $this->SOCOM_Site_User_model->is_user_by_group(2);
?>
<style>
.bx--file__selected-file {
    display: none;
    max-width: 30rem !important;
    padding: 10px;
}
.bx--file__selected-file:not(:empty){
    display: block;
}
</style>
<div data-content-switcher class="bx--content-switcher w-50 mb-5">
    <button id="upload-tab4" class="bx--content-switcher-btn bx--content-switcher--selected" data-target=".admin--panel--opt-1">Upload</button>
    <button id="file-list-tab4" class="bx--content-switcher-btn" data-target=".admin--panel--opt-2">File List</button>
    <button class="bx--content-switcher-btn" data-target=".admin--panel--opt-3">Processed File List</button>
    <?php if($is_pom_admin): ?>
    <button class="bx--content-switcher-btn" data-target=".admin--panel--opt-4">Admin Processed File List</button>
    <?php endif; ?>
</div>

<div class="admin--panel--opt-1">

    <form id="upload-form">

        <div class="w-50 h-100 d-flex flex-column align-items-left">

            <div class="d-flex">
                <?php
                    $this->load->view('templates/carbon/inline_notification_low_contrast_view', [
                        'notification_id' => 'upload_form_instructions',
                        'class' => 'bx--inline-notification--info',
                        'file_upload_class' => 'd-flex flex-column',
                        'bx_notification_title' => 'File Upload Instructions for current FY: ' . $form_data['cycle_name'],
                        'bx_notification_subtitle' => '<ol class="bx--list--ordered mt-2">
                                                            <li class="bx--list__item">Add document using "Choose File" button</li>
                                                            <li class="bx--list__item">Wait until system has processed</li>
                                                            <li class="bx--list__item">Input version</li>
                                                            <li class="bx--list__item">Click Submit</li>
                                                        </ol>',
                        'close' => false
                    ]);
                ?>
            </div>
        </div>

        <div class="d-flex flex-column m-1">
            <div class="w-100">
                <div class="bx--file__container" data-file>
                    <div data-file-drop-container class="bx--form-item">
                        <p class="bx--file--label">Upload</p>
                        <p class="bx--label-description">ZBT Events and Issue Events Upload in xlsx format</p>
                        <label id="file-input-label4" tabindex="0" class="bx--btn bx--btn--primary bx--btn--md" for="dashboard-file-uploader4">
                            <span role="button" aria-disabled="false">Choose File</span>
                        </label>
                        <input type="file" class="bx--file-input" id="dashboard-file-uploader4" data-file-uploader data-target="[data-file-container]" />
                        <div data-file-container class="bx--file-container" id="file-upload-success4">
                            <div class="bx--file__selected-file"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-50 d-flex flex-wrap flex-column">

            <div class="bx--form-item bx--select w-25 mb-1">
                <label for="year-input4" class="bx--label">POM Year</label>
                <select  id="year-input4" name="year4" class="bx--select-input">
                    <option disabled selected value="0"> -- select -- </option>
                    <?php foreach($filtered_pom_years as $year):?>
                        <option class="bx--select-option" value="<?=$year;?>"><?=$year;?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="bx--form-item bx--text-input-wrapper w-25 mb-1">
                <label for="table-listing-input4" class="bx--label">Table Listing</label>
                <select  id="table-listing-input4" class="bx--select-input">
                    <option disabled selected value="0"> -- select -- </option>
                    <option class="bx--select-option" value="ZBT_EXTRACT">ZBT Events</option>
                    <option class="bx--select-option" value="ISS_EXTRACT">ISS Events</option>
                </select>
            </div>

            <div class="bx--form-item bx--text-input-wrapper w-50 mb-1">
                <label for="version-input4" class="bx--label">Version</label>
                <div class="bx--text-input__field-wrapper">
                    <input id="version-input4" name="version4" type="text" class="bx--text-input" placeholder="Import Version" />
                </div>
            </div>

            <div class="bx--form-item bx--text-input-wrapper w-50 mb-1">
                <label for="title-input4" class="bx--label">Title</label>
                <div class="bx--text-input__field-wrapper">
                    <input id="title-input4" name="title4" type="text" class="bx--text-input" placeholder="Import Title" />
                </div>
            </div>

            <div class="bx--form-item">
                <div class="bx--text-area__label-wrapper">
                    <label for="test2" class="bx--label">Description</label>
                </div>
                <div class="bx--text-area__wrapper">
                    <textarea cols="50" rows="4" id="description4" placeholder="Import Description" class="bx--text-area"></textarea>
                </div>
            </div>

            <div class="bx--form-item mt-1">
                <button id="save-file4" class="bx--btn bx--btn--primary bx--btn--enabled" type="submit" disabled>
                    <span data-loading class="bx--loading bx--loading--small d-none ml-5">
                        <svg class="bx--loading__svg" viewBox="-75 -75 150 150">
                            <title>Loading</title>
                            <circle class="bx--loading__stroke" cx="0" cy="0" r="37.5" />
                        </svg>
                    </span>
                    <span class="submit--btn">Submit Button</span>
                </button>
            </div>
        </div>
    </form>
    
</div>

<div class="admin--panel--opt-2" hidden>
    <div class="d-flex">
        <?php
            $this->load->view('templates/carbon/inline_notification_low_contrast_view', [
                'notification_id' => 'processed_instructions',
                'class' => 'bx--inline-notification--info',
                'file_upload_class' => 'd-flex flex-column',
                'bx_notification_title' => 'Document Processing Time',
                'bx_notification_subtitle' => '<div class="mt-2"></div>
                    Documents are processed by Guardian Automation every 20 minutes.
                    The data table will refresh every 10 minutes and the processing status will be updated.
                    Additionally the hamburger menu in each uploaded document row will enable to user to:
                    <ol class="bx--list--ordered mt-2">
                        <li class="bx--list__item">Process  document by clicking Process</li>
                        <li class="bx--list__item">Stop document processing by clicking Cancel</li>
                        <li class="bx--list__item">Remove from document list by clicking Delete</li>
                    </ol>',
                'close' => false,
                'align_right' => FALSE
            ]);
        ?>
    </div>
    <div>
        <?php
            $this->load->view('SOCOM/dashboard/upload/ZBT_Issue_upload_list_view');
        ?>
    </div>
</div>

<div class="admin--panel--opt-3" hidden>
    <div class="d-flex">
    <?php
		$this->load->view('templates/carbon/inline_notification_low_contrast_view', [
			'notification_id' => 'processed_instructions',
			'class' => 'bx--inline-notification--info',
			'file_upload_class' => 'd-flex flex-column',
			'bx_notification_title' => 'Document Processing Time',
			'bx_notification_subtitle' => '<div class="mt-2"></div>
				Documents are processed by Guardian Automation every 20 minutes.
				The data table will refresh every 10 minutes and the processing status will be updated.
				Additionally the hamburger menu in each uploaded document row will enable to user to:
				<ol class="bx--list--ordered mt-2">
					<li class="bx--list__item">Process  document by clicking Process</li>
					<li class="bx--list__item">Stop document processing by clicking Cancel</li>
					<li class="bx--list__item">Remove from document list by clicking Delete</li>
				</ol>',
			'close' => false,
			'align_right' => FALSE
		]);
	?>
    </div>
    
    <?php
       $this->load->view('SOCOM/dashboard/upload/ZBT_Issue_processed_list_view');
    ?>
</div>

<div class="admin--panel--opt-4" hidden>
    <div class="d-flex">
    <?php
		$this->load->view('templates/carbon/inline_notification_low_contrast_view', [
			'notification_id' => 'processed_instructions',
			'class' => 'bx--inline-notification--info',
			'file_upload_class' => 'd-flex flex-column',
			'bx_notification_title' => 'Document Processing Time',
			'bx_notification_subtitle' => '<div class="mt-2"></div>
				Documents are processed by Guardian Automation every 20 minutes.
				The data table will refresh every 10 minutes and the processing status will be updated.
				Additionally the hamburger menu in each uploaded document row will enable to user to:
				<ol class="bx--list--ordered mt-2">
					<li class="bx--list__item">Process  document by clicking Process</li>
					<li class="bx--list__item">Stop document processing by clicking Cancel</li>
					<li class="bx--list__item">Remove from document list by clicking Delete</li>
				</ol>',
			'close' => false,
			'align_right' => FALSE
		]);
	?>
    </div>
    
    <?php
       $this->load->view('SOCOM/dashboard/upload/ZBT_Issue_processed_list_admin_view');
    ?>
</div>

<script>
    document.getElementById("dashboard-file-uploader4").addEventListener("change", function(event) {
    let fileContainer = document.querySelector("#file-upload-success4 > div.bx--file__selected-file");
    fileContainer.textContent = event.target.files.length ? event.target.files[0].name : "";
    });
</script>