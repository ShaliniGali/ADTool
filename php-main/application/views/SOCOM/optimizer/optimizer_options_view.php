<style>
    .checkbox-spacing{
        margin-bottom: 16px;
    }
</style>

<div class="d-flex flex-row">
    <div class="d-flex flex-column mr-5">
        
        <div class="d-flex flex-row">
            <?php $this->load->view('components/radioButtonGroup', [
                'name' => 'all_years',
                'label' => 'Selected Programs Must Meet All FYDP Years Delta',
                'radioButtons' => [
                    'Yes' => [
                        'id' => 'r-yes',
                        'value' => 'yes',
                        'key' => 1,
                        'checked' => true,
                        'label' => 'Yes'
                    ],
                    'no' => [
                        'id' => 'r-no',
                        'value' => 'no',
                        'key' => 2,
                        'checked' => false,
                        'label' => 'No'
                    ]
                ]
                ]); ?>
        </div>
        <div class="d-flex flex-row">
            <?php $this->load->view('components/radioButtonGroup', [
                'name' => 'per_resource_optimizer',
                'label' => 'Score Type',
                'useTile' => false,
                'radioButtons' => [
                    'Score Per Dollar' => [
                        'id' => 'r-score-per-dollar',
                        'value' => true,
                        'key' => 1,
                        'checked' => false,
                        'label' => 'Score Per $'
                    ],
                    'Score' => [
                        'id' => 'r-score',
                        'value' => false,
                        'key' => 2,
                        'checked' => true,
                        'label' => 'Score'
                    ]
                ]
                ]); ?>
        </div>
    </div>
</div>
