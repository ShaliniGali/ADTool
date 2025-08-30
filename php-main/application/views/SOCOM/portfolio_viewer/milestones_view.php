<?php



?>

<style>
  #program-execution-drilldown-milestones-view {
    padding-top: 16px;
  }


  .view-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #172B4D;
    margin-bottom: 8px;
  }


  .milestones-header {
    text-align: center;
    margin: 24px 0;
  }

  .milestones-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #172B4D;
  }

  .milestones-header .program-name {
    font-size: 1rem;
    font-weight: normal;
    color: #42526E;
    margin-top: 4px;
  }


  .milestones-grid {
    display: flex;
    gap: 24px;
    align-items: stretch;

  }


  @media (max-width: 768px) {
    .milestones-grid {
      flex-direction: column;
    }
  }
</style>

<div id="program-execution-drilldown-milestones-view">


  <?php
  $this->load->view('SOCOM/portfolio_viewer/components/common/program_group_dropdown_view.php', [
    'title'             => "Program Name",
    'program_groups'    => $program_selections,
    'dropdown_id'       => 'program-execution-drilldown-milestones-dropdown',
    'onchange_handler'  => "milestoneDropdownOnchange('" . htmlspecialchars($tab_type) . "','" . htmlspecialchars($program_group) . "')",
    'placeholder_text'  => 'Select a program',
    'select2_width'     => '300px'
  ]);
  ?>
</div>
</div>


<div class="milestones-header">
  <h3>Procurement Strategy: <span id="milestones-procurement-strategy"><?= htmlspecialchars($procurement_strategy); ?></span></h3>
  <h3 class="program-name"><span id="milestones-program-name"><?= htmlspecialchars($selected_program); ?></span></h3>
</div>

<div id="program-execution-drilldown-milestones-table-view" class="milestones-grid">
  <?php $this->load->view('SOCOM/portfolio_viewer/milestones_table_view'); ?>
</div>

</div>

<script>
  $(document).ready(function() {
    $('#program-execution-drilldown-milestones-dropdown').select2({
      placeholder: "Select a program",
      width: '300px',
      allowClear: true
    });
  });
</script>