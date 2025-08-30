<?php
    $page_data['page_title'] = "Update Tiles";
    $page_data['page_tab'] = "Update Tiles";
    $page_data['page_navbar'] = true;
    $page_data['page_specific_css'] = array('select2.css', 'nav_tabs.css');
    $page_data['compression_name'] = trim(pathinfo(__FILE__, PATHINFO_FILENAME), '.php');
    $this->load->view('templates/header_view', $page_data);
?>

<style>
.header-block{
   padding: 1em;
   text-align: center;
   font-size: 20px;
   background-color: #3f3f3f;
}
.input-block{
   padding: 1em;
   font-size: 15px;
   background-color: #000000;
}
.bx--dropdown-text{
   line-height:2.80;
}

#label-text{
    min-width: 350px;
}

#svg-text{
    min-width: 200px;
}
</style>

<div class="form-container p-5 m-2">
    <div id="input-guide-panel">
        <div class="header-block">Tile Update Form</div>
        <div class="input-block">
            <div class="d-flex flex-wrap mt-4">
                <h3 class="ml-3 mr-5 mt-1"> Select a Tile name to update the respective values </h3>
                <!-- POM cycle -->
                <div class="bx--form-item">
                    <div class="d-flex flex-column w-100">
                        <div class="mb-1 bx--label">Tile Name</div>
                        <select id="select-tile" class="form-control w-100 create-select">
                        <option></option>
                        <?php foreach($tile_data as $t): ?>
                            <option value="<?= $t['title']; ?>"><?= $t['title']; ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="pt-2 input-block">
            <div class="d-flex flex-wrap mt-4 justify-content-between">
                <div 
                    class="bx--form-item bx--text-input-wrapper">
                    <label for="label-text" class="bx--label">Label</label>
                    <div class="bx--text-input__field-wrapper">
                        <input id="label-text" type="text" placeholder="Project Label"
                        class="bx--text-input">
                    </div>
                </div>
                <div 
                    class="bx--form-item bx--text-input-wrapper">
                    <label for="svg-text" class="bx--label">Icon Svg</label>
                    <div class="bx--text-input__field-wrapper">
                        <input id="svg-text" type="text" placeholder="SVG Name"
                        class="bx--text-input">
                    </div>
                </div>
                <div class="bx--form-item">
                    <label for="description-text" class="bx--label">Description</label>
                    <div class="bx--text-area__wrapper">
                        <textarea id="description-text"
                        class="bx--text-area bx--text-area--v2"
                        rows="4" cols="50" placeholder="Populate Markup Description for the selected Project"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bx--form-item w-100 text-center mt-2"> 
        <button id="update-tile" onclick='save_tiles()' class="bx--btn bx--btn--primary mr-2 what-if-navigation ml-auto mr-auto" type="submit" disabled>
         Update
        </button>
    </div>
</div>

<?php
  $this->load->view('templates/essential_javascripts');
?>


<?php
  $js_files = array();
  $CI = &get_instance();
  $js_files["select"] = ["select2.full.js",'global'];
  $js_files["update_tile"] = ["sso_tiles/update_tile.js",'custom'];

  $CI->load->library('RB_js_css');
  $CI->rb_js_css->compress($js_files);
?>

<script>
    $(document).ready(function() {

        $(".create-select").select2({
            placeholder: "Select an option",
            allowClear: true,
            width: 'resolve'
        }).on("change", function() {
            let title = $('#select-tile').val();
            if(title!=''){
                populate_fields(title);
            }else{
                $('#update-tile').prop('disabled',true);
                $('#description-text').val('');
                $('#svg-text').val('');
                $('#label-text').val('');
            }
        });
    })
</script>


<?php
    $this->load->view('templates/close_view');
?>