<?php
$dropdown_id = $dropdown_id ?? 'program-execution-drilldown-program-group-dropdown';
$onchange_handler = $onchange_handler ?? 'onProgramGroupChange()';
$placeholder_text = $placeholder_text ?? 'Select an option';
$select2_width = $select2_width ?? '17vw';
$title = $title ?? '';
?>

<style>
  .metadata-section__title {
    font-size: 1rem;
    font-weight: 500;
    color: #172B4D;
    margin-bottom: 12px;
    text-transform: none !important;
  }

  .selection-dropdown {
    padding: 0.5rem;
    font-size: 1rem;
    border-radius: 4px;
    border: 1px solid #ccc;
    width: 100%;
    height: 40px;
    line-height: 40px;
    box-sizing: border-box;
  }

  .select2-container--default .select2-selection--single {
    height: 40px;
    display: flex;
    align-items: center;
    border-radius: 8px;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal;
  }
</style>

<div>
  <div class="metadata-section__title"><?= htmlspecialchars($title) ?></div>

  <select
    id="<?= htmlspecialchars($dropdown_id) ?>"
    class="selection-dropdown w-100"
    onchange="<?= htmlspecialchars($onchange_handler) ?>">
    <option></option>
    <?php foreach ($program_groups as $value): ?>
      <option value="<?= htmlspecialchars($value) ?>">
        <?= htmlspecialchars($value) ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<script>
  $(document).ready(function() {
    $('#<?= htmlspecialchars($dropdown_id) ?>').select2({
      placeholder: "<?= htmlspecialchars($placeholder_text) ?>",
      width: '<?= htmlspecialchars($select2_width) ?>'
    });
  });
</script>