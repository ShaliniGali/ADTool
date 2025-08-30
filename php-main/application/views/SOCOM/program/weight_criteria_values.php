<?php $this->load->view('SOCOM/weights/tooltip'); ?>
<div class="d-flex flex-column ml-3 mr-1">
    <h4><?= $title ?> Weights:</h4>
    <div class="weighted_table">
        <table id="<?= $criteria; ?>-table" class="bx--data-table display nowrap" style="width:100%">
        </table>
    </div>
</div>

<script>
    var p_data = {
        'cols': [
            <?php foreach ($default_criteria_description as $criteria_term): ?>
                {
                    'data': '<?= htmlspecialchars($criteria_term['CRITERIA'], ENT_QUOTES, 'UTF-8'); ?>',
                    'title': `<?= renderTooltip($criteria_term['CRITERIA'], $criteria_term['CRITERIA_DESCRIPTION'] ?? '') ?>`
                },
            <?php endforeach; ?>
        ],
        'col_defs': [
            <?php for ($i = 0; $i < count($default_criteria_description); $i++): ?>
                {
                    targets: <?= $i ?>,
                    defaultContent: "<span style='display:inline-block; min-width: 100px;'>-</span>"
                },
            <?php endfor; ?>
        ]
    };

    var length_weights = <?= count($default_criteria_description); ?>;

    selected_<?= $title ?>_weight_table = $('#<?= $criteria; ?>-table').DataTable({
        scrollY: false,
        scrollX: true,
        autoWidth: true,
        bPaginate: false,
        bFilter: false,
        bInfo: false,
        ordering: false,
        lengthChange: false,
        responsive: false,
        fixedHeader: true, 
        initComplete: function(settings, json) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            selected_<?= $title ?>_weight_table.columns.adjust().draw();

            if (typeof load_program_table === 'function') {
                load_program_table();
            }
            if (typeof load_optimizer_table === 'function') {
                load_optimizer_table();
            }
        },
        ajax: {
            url: "/socom/resource_constrained_coa/program/weight_table/get/<?= $title ?>",
            type: 'GET',
            data: function(d) {
                d.weight_id = $('#fp-weight-sel').val();
            },
        },
        columns: p_data['cols'],
        columnDefs: p_data['col_defs']
    });
</script>
