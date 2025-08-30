<div class="d-flex flex-row h-100 mt-5 mb-5">
    <div class="d-flex flex-column ml-auto mr-auto w-75 mb-5">
		<div class="neumorphism mb-auto mt-3">
				<div class="card bg-translucent flex-row w-100 neumorphism align-items-center">
					<div class="card-body w-85">
						<div class="d-flex flex-row mb-3">
							<h4 class="mb-3 mr-5">Weights Criteria List</h4>
						</div>
                    	<table id="criteria-list" class="bx--data-table w-100">
                    	</table>
                	</div>
            	</div>
		</div>
    </div>
</div>

<?php
$this->load->view('templates/carbon/carbon_modal', [
    'modal_id' => 'weight_view_modal',
    'role' => 'weight_view_list',
    'title' => 'Edit Weight',
    'title_heading' => '',
    'basic_modal_body_id' => 'weight_view_body',
    'buttons' => [
        [
            'class' => 'bx--btn--secondary',
            'aria-label' => 'close',
            'text' => 'Close'
        ],
        [
            'class' => 'bx--btn--primary edit_button',
            'aria-label' => 'save',
            'text' => 'Save Weight'
        ]
    ],
    'html_content' => $this->load->view('SOCOM/weights/weight_view_modal', [
        'id' => 'weight_view'
    ], true),
    'close_event' => 'function() {
                $("#weight_view_modal > div.bx--modal.bx--modal-tall").removeClass("is-visible");
            }',
    'save_event' => 'saveWeight'
]);
?>

<script>

const column_definition = [
    {
        title: 'Weight Name',
        data: 'NAME',
        className: 'w2px'
    },
    {
        title: 'Created Date',
        data: 'TIMESTAMP'
    },
    {
        searchable: false,
        orderable: false,
        lengthChange: false,
        render: data => `<?php $this->load->view('SOCOM/weights/list_overflow_menu', ['id' => '']); ?>`
    }
];

</script>

