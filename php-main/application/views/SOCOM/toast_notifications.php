<div class="d-flex flex-row justify-content-center w-100">
	<div class="socom-toast-notification position-fixed d-none" id="socom-toast-notification-success" style="z-index:99999;">
		<div class="d-flex flex-column justify-content-center align-items-center">
			<?php $this->load->view(
				'templates/carbon/inline_notification_low_contrast_view',
				[
					'bx_notification_title' => '',
					'bx_notification_subtitle' => '',
					'custom_close' => false,
					'class' => 'bx--inline-notification--success ml-auto mr-auto',
					'bx_notification_title_class' => ''
				]
			);
			?>
		</div>
	</div>
	<div class="socom-toast-notification position-fixed d-none" id="socom-toast-notification-error" style="z-index:99999;">
		<div class="d-flex flex-column justify-content-center align-items-center">
			<?php $this->load->view(
				'templates/carbon/inline_notification_low_contrast_view',
				[
					'bx_notification_title' => '',
					'bx_notification_subtitle' => '',
					'custom_close' => false,
					'class' => 'bx--inline-notification--error ml-auto mr-auto',
					'bx_notification_title_class' => ''
				]
			);
			?>
		</div>
	</div>
</div>