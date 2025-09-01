<?php
// milestones_table_view.php
// Renders the three milestone cards with new gradient headers.
?>

<style>
  /* UPDATED: Main card box adjusted for the new header style */
  .milestone-box {
    background: #ffffff;
    border: 1px solid #dfe1e6;
    border-radius: 8px;
    flex: 1;
    display: flex;
    flex-direction: column;
    text-align: center;
    transition: box-shadow 0.2s ease-in-out, transform 0.2s ease-in-out, border 0.2s ease-in-out;
    overflow: hidden; /* Keeps the child elements within the rounded corners */
  }

  .milestone-box:hover {
    border: 2px solid #0052CC;
    box-shadow: 0 4px 12px rgba(0, 82, 204, 0.15);
  }

  /* NEW: Gradient header styles */
  .milestone-box__header {
    font-weight: 600;
    color: #172B4D; /* Dark text for contrast */
    flex-shrink: 0;
    padding: 16px 24px;
    /* Subtle light-to-darker grey gradient */
    background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dfe1e6; /* Clean separator line */
  }

  /* UPDATED: Body now has its own padding */
  .milestone-box__body {
    max-height: 550px;
    overflow-y: auto;
    padding: 24px; /* Restores spacing for the content below the header */
  }

  /* Custom Scrollbar Styling (for Webkit browsers) */
  .milestone-box__body::-webkit-scrollbar {
    width: 8px;
  }
  .milestone-box__body::-webkit-scrollbar-track {
    background: transparent;
  }
  .milestone-box__body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
  }
  .milestone-box__body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }

  /* Container for each milestone's data */
  .milestone-content {
    padding: 8px;
  }

  .milestone-content + .milestone-content {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #dfe1e6;
  }

  .is-clickable:hover {
    cursor: pointer;
    background-color: #f7faff;
    border-radius: 4px;
  }

  /* Star icon styling */
  .milestone-star {
    margin-bottom: 12px;
  }
  .milestone-star svg {
    width: 32px;
    height: 32px;
  }

  /* Milestone name */
  .milestone-title {
    font-size: 1.25rem;
    font-weight: bold;
    color: #172B4D;
    margin-bottom: 16px;
  }

  /* Table for start and end dates */
  .milestone-dates {
    width: 100%;
    margin: 0 auto 16px;
    max-width: 200px;
  }
  .milestone-dates td {
    padding: 4px;
    font-size: 0.9rem;
  }
  .milestone-dates td:first-child {
    text-align: right;
    color: #42526E;
  }
  .milestone-dates td:last-child {
    text-align: left;
    font-weight: 600;
  }

  /* Asterisk for requirements */
  .milestone-requirements-indicator {
    font-size: 1.5rem;
    font-weight: bold;
    color: #172B4D;
    min-height: 28px;
  }

  /* Placeholder for when there's no data */
  .no-milestone-data {
    color: #6b778c;
    font-style: italic;
    margin: auto;
  }
</style>

<?php
// Define the order of milestones to ensure they always display correctly
$milestone_order = ['current','previous','future'];
$structured_data = [];

// Populate the structured data in the correct order
foreach ($milestone_order as $key) {
    if (isset($milestone_data[$key])) {
        $structured_data[$key] = $milestone_data[$key];
    } else {
        $structured_data[$key] = [ 'title' => ucfirst($key) . ' Milestone', 'data' => [], 'fill' => '#ccc' ];
    }
}
?>

<?php foreach ($structured_data as $key => $section): ?>
  <?php
    $title = $section['title'];
    $items = $section['data'];
    $fill  = $section['fill'] ?? '#ccc';
    $card_class = 'milestone-box';
  ?>
  <div id="<?= htmlspecialchars($key); ?>-milestone-card" class="<?= $card_class; ?>">
    <div class="milestone-box__header">
      <?= htmlspecialchars($title); ?>
    </div>

    <div class="milestone-box__body">
      <?php if (empty($items)): ?>
        <div class="no-milestone-data">
          No <?= htmlspecialchars($key); ?> milestones
        </div>
      <?php else: ?>
        <?php foreach ($items as $data): ?>
          <?php
            $is_clickable = $data['HAS_REQUIREMENTS'] ? 'is-clickable' : '';
            $onclick_attr = $data['HAS_REQUIREMENTS']
                ? "onclick=\"showMilestonesRequirementsModal(
                     '" . htmlspecialchars($data['PXID'], ENT_QUOTES) . "',
                     '" . htmlspecialchars($data['MILESTONE'], ENT_QUOTES) . "',
                     '" . htmlspecialchars($title, ENT_QUOTES) . "',
                     '" . htmlspecialchars($data['HAS_REQUIREMENTS'], ENT_QUOTES) . "'
                   )\"
                  data-modal-target=\"#program-execution-drilldown-milestones-requirements\""
                : '';
          ?>
          <div class="milestone-content <?= $is_clickable; ?>" <?= $onclick_attr; ?>>
            <div class="milestone-star">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                <path fill="<?= htmlspecialchars($fill); ?>" stroke="#42526E" stroke-width="1" d="M16,2l-4.55,9.22L1.28,12.69l7.36,7.18L6.9,30,16,25.22,25.1,30,23.36,19.87l7.36-7.17L20.55,11.22Z" />
              </svg>
            </div>
            <div class="milestone-title"><?= htmlspecialchars($data['MILESTONE']); ?></div>
            <table class="milestone-dates">
              <tbody>
                <tr>
                  <td>Start Year:</td>
                  <td><strong><?= (strtotime($data['START_DATE']) > 0) ? date('Y', strtotime($data['START_DATE'])) : 'N/A'; ?></strong></td>
                </tr>
                <tr>
                  <td>End Year:</td>
                  <td><strong><?= (strtotime($data['END_DATE']) > 0) ? date('Y', strtotime($data['END_DATE'])) : 'N/A'; ?></strong></td>
                </tr>
              </tbody>
            </table>
            <div class="milestone-requirements-indicator"><?= $data['HAS_REQUIREMENTS'] ? '*' : ''; ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<script>
  $(document).ready(function() {
    $('#milestones-procurement-strategy').text('<?= htmlspecialchars($procurement_strategy, ENT_QUOTES); ?>');
    $('#milestones-program-name').text('<?= htmlspecialchars($selected_program, ENT_QUOTES); ?>');
  });
</script>