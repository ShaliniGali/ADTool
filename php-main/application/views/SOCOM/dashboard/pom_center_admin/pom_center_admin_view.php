<style>
    .set_current_button{
        height: calc(2.5rem +2px); 
        padding: 0.5rem 1rem; 
        font-size: 1rem;
    }
</style>
<div data-content-switcher class="bx--content-switcher w-75 mb-5">
  <button class="bx--content-switcher-btn bx--content-switcher--selected" data-target=".pom-admin-panel-1">POM</button>
  <button class="bx--content-switcher-btn" data-target=".pom-admin-panel-2">ZBT Summary</button>
  <button class="bx--content-switcher-btn" data-target=".pom-admin-panel-3">ISS Summary</button>
  <button class="bx--content-switcher-btn" data-target=".pom-admin-panel-4">Create COA</button>
</div>


<div class="d-flex flex-row h-100 mt-5 mb-5">
    <div class="d-flex flex-column ml-auto mr-auto w-75 mb-5">
		<div class="neumorphism mb-auto mt-3">
            <div class="card bg-translucent flex-col w-100 neumorphism">
                    <div class="d-flex flex-column mt-3 ml-3">
                        <h4 class="mb-3">Active POM Year: <?php echo $pom_year; ?></h4>
                        <h4 class="mb-3">Latest POM Position: <?php echo $pom_position; ?></h4>
                        <div class="d-flex justify-content-start">
                        <div class="d-flex w-25 mr-4">
                            <div class="bx--form-item w-100">
                                <label for="pom-year-select" class="bx--label">POM Year</label>
                                <div class="bx--select">
                                    <select id="pom-year-select" class="bx--select-input" onchange="handlePomYearChange(this.value)">
                                        <?php foreach ($all_pom_years as $year): ?>
                                            <option value="<?php echo $year; ?>" <?php echo ($year == $pom_year) ? 'selected' : ''; ?>>
                                                <?php echo $year; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <svg class="bx--select__arrow" width="10" height="5" viewBox="0 0 10 5" fill-rule="evenodd">
                                        <path d="M10 0L5 5 0 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex w-25 mr-4">
                            <div class="bx--form-item w-100">
                                <label for="pom-position-select" class="bx--label">POM Position</label>
                                <div class="bx--select">
                                    <select id="pom-position-select" class="bx--select-input" onchange="handlePositionChange(this.value)">
                                        <?php foreach ($pom_positions as $position): ?>
                                            <option value="<?php echo $position; ?>" <?php echo ($position == $pom_position) ? 'selected' : ''; ?>>
                                                <?php echo $position; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <svg class="bx--select__arrow" width="10" height="5" viewBox="0 0 10 5" fill-rule="evenodd">
                                        <path d="M10 0L5 5 0 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-end">
                            <button type="button" class="btn btn-primary save_pom_button" onclick="handleSavePom()"> Save POM</button>
                        </div>
                    </div>
                <div class="card-body w-85 pom-admin-panel-1">
                    <div class="d-flex flex-row">
                        <h5 class="mb-3 mr-5">POM Table Listing</h5>
                    </div>
                    <table id="active-pom-table" class="bx--data-table w-100">
                        
                    </table>
                </div>

                <div class="card-body w-85 pom-admin-panel-2" hidden>
                    <div class="d-flex flex-row">
                        <h5 class="mb-3 mr-5">ZBT Summary Table Listing</h5>
                    </div>
                    <table id="active-zbt-table" class="bx--data-table w-100">
                        
                    </table>
                </div>
                <div class="card-body w-85 pom-admin-panel-3" hidden>
                    <div class="d-flex flex-row">
                        <h5 class="mb-3 mr-5">ISS Summary Table Listing</h5>
                    </div>
                    <table id="active-iss-table" class="bx--data-table w-100">
                        
                    </table>
                </div>
                <div class="card-body w-85 pom-admin-panel-4" hidden>
                    <div class="d-flex flex-row">
                        <h5 class="mb-3 mr-5">Create COA Table Listing</h5>
                    </div>
                    <table id="active-coa-table" class="bx--data-table w-100">
                        
                    </table>
                </div>
            </div>
		</div>
    </div>
</div>

<script>
    let pom_position = '<?= $pom_position ?>';
    let pom_year = '<?= $pom_year ?>';
    let latest_pom_year = '<?= $latest_pom_year ?>';

function handlePomYearChange(selectedYear) {
    pom_year = selectedYear;
    console.log('Selected POM Year:', selectedYear);
    if(selectedYear !== (latest_pom_year)) {
        $('#active-pom-table').DataTable().ajax.reload(function() {
            $('#active-iss-table').DataTable().ajax.reload(function() {
                $('#active-zbt-table').DataTable().ajax.reload(function() {
                    $('#active-coa-table').DataTable().ajax.reload();
                });
            });
        });
    }
}
function handlePositionChange(selectedPosition) {
    pom_position = selectedPosition;
    console.log('Selected POM Year:', selectedPosition);
    if(pom_year == (latest_pom_year)){
        $('#active-pom-table').DataTable().ajax.reload(function() {
            $('#active-iss-table').DataTable().ajax.reload(function() {
                $('#active-zbt-table').DataTable().ajax.reload(function() {
                    $('#active-coa-table').DataTable().ajax.reload();
                });
            });
        });
    }
}
function handleSavePom() {
   const saveButton = document.querySelector('.save_pom_button');
   saveButton.disabled = true;
   saveButton.innerHTML = 'Saving...';
   $.ajax({
       url: '/dashboard/pom/save_new_pom',
       type: 'POST',
       dataType: 'json',
       data: {
           year: pom_year,
           position: pom_position,
           rhombus_token: function() { return rhombuscookie(); },
       },
       success: function(response) {
           if (response.status) {
                status = 'success';
                success = 'POM saved successfully';
                displayToastNotification(status, success);
                latest_pom_year = pom_year;
            } else {
                alert(response.message || 'Failed to save POM');
            }
       },
       error: function(xhr, status, error) {
            status = 'error';
            error = 'Error occurred while saving POM';
            console.error('Error:', error);

            if (typeof xhr.responseJSON === 'object' && typeof xhr.responseJSON.message === 'string') {
                displayToastNotification('error', xhr.responseJSON.message);
            } else {
                    displayToastNotification('error', 'Unknown error when trying to save POM.');
            }
       },
       complete: function() {
           saveButton.disabled = false;
           saveButton.innerHTML = 'Save POM';
       }
   });
}
</script>