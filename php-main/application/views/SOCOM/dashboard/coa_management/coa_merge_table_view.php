<div class="d-flex flex-column w-100">
    <div class="d-flex justify-content-center p-3">
        <h3><?= $title; ?></h3>
    </div>
    <div id="show-weighted-score" class="flex-row">
        <?php $this->load->view('components/radioButtonGroup', [
            'name' => 'weighted_score_based',
            'label' => 'Weighted Scores Based',
            'radioButtons' => [
                'STORM' => [
                    'id' => 'r-storm',
                    'value' => 'storm',
                    'key' => 4,
                    'checked' => false,
                    'label' => 'StoRM'
                ],
                'POM' => [
                    'id' => 'r-pom',
                    'value' => 'pom',
                    'key' => 3,
                    'checked' => false,
                    'label' => 'POM'
                ],
                'Guidance' => [
                    'id' => 'r-guidance',
                    'value' => 'guidance',
                    'key' => 2,
                    'checked' => false,
                    'label' => 'Guidance'
                ],
                'POM & Guidance' => [
                    'id' => 'r-both',
                    'value' => 'both',
                    'key' => 1,
                    'checked' => true,
                    'label' => 'POM & Guidance'
                ]
            ]
        ]); ?>
    </div>
    <div class="d-flex flex-row" style="gap: 1.5rem;">
        <?php foreach($table_data as $data): ?>
        <div class="neumorphism mb-auto mt-3 w-50">
            <div class="card bg-translucent flex-row w-100 neumorphism align-items-center" style="overflow-x: scroll;">
                <div class="card-body w-85">
                    <div class="d-flex flex-row justify-content-between mb-3">
                        <h4 class="mb-3 mr-5"><?= $data['title']; ?></h4>
                    </div>
        
                        <table id="coa-merge-table-<?= $data['id']; ?>" class="bx--data-table ml-0" style="width:100%">
                            <thead>
                            <tr>
                                <?php foreach($data['headers'] as $header): ?>
                                    <td><?= $header['title']; ?></td>
                                <?php endforeach; ?>
                            </tr>
                            </thead>
                        </table>
                
                </div>
            </div>
		</div>
        <?php endforeach; ?>

    </div>

    <div class="d-flex justify-content-center mt-4">
        <button id="merge-coa-modal-btn" class="bx--btn bx--btn--primary" disabled>Merge COA</button>
    </div>

</div>






<style>

    .invalid-uncommitted-cell{
        background: rgb(246, 89, 89)!important;
        color: black !important;
        font-weight: bold;
    }

    #budget-table_wrapper {
        width: 50%;
        margin-top: 20px;
    }

    #show-coa-table_wrapper {
        width: 100%;
        margin-top: 20px;
    }

    #show-coa-table_wrapper select {
        background-color: #FFF;
    }

    #show-coa-table_wrapper input {
        background-color: #FFF;
    }

    table.dataTable thead > tr > th.sorting {
        padding-left: 18px;
    }

    table.dataTable thead .sorting {
        background-image: none;
    }

    .dataTables_filter {
        margin: 8px 0;
    }
</style>

<script>
    var table_data = <?= json_encode($table_data); ?>;

    table_data.forEach( v => {
        let data = v['data'];
        let headers = v['headers'];
        let id = 'coa-merge-table-' + v['id'];
        let saved_coa_id = v['id'];
        let visible_score_columns = v['visible_score_columns'];
        initCOADatatable(id, data, headers, saved_coa_id, visible_score_columns);
    })

    // Initialize the following
    var openMergeCoaModalBtn = $('#merge-coa-modal-btn');
    openMergeCoaModalBtn.on('click', () => { showMergeCOA()});
</script>
