<!-- Class options
	bx--inline-notification--success 
	bx--inline-notification--error
	bx--inline-notification--warning
	bx--inline-notification--info
-->
<div data-notification class="bx--inline-notification bx--inline-notification--low-contrast <?= isset($class) ? $class : ''; ?> <?= isset($align_right) && $align_right ? 'ml-auto' : ' ml-auto mr-auto ' ?>" role="alert" <?php if (isset($id)) : ?> id="<?= $id; ?>" <?php endif; ?>>
	<div class="bx--inline-notification__details">
		<svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bx--inline-notification__icon" width="20" height="20" viewBox="0 0 32 32" aria-hidden="true">
			<path fill="none" d="M16,8a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,16,8Zm4,13.875H17.125v-8H13v2.25h1.875v5.75H12v2.25h8Z"></path>
			<path d="M16,2A14,14,0,1,0,30,16,14,14,0,0,0,16,2Zm0,6a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,16,8Zm4,16.125H12v-2.25h2.875v-5.75H13v-2.25h4.125v8H20Z"></path>
		</svg>
		<div class="bx--inline-notification__text-wrapper <?= isset($file_upload_class) ? $file_upload_class : ''; ?>">
			<p class="bx--inline-notification__title <?= isset($bx_notification_title_class) ? $bx_notification_title_class : ''; ?>"><?= $bx_notification_title; ?></p>
			<p class="bx--inline-notification__subtitle"><?= isset($bx_notification_subtitle) ? $bx_notification_subtitle : ''; ?></p>
		</div>
	</div>
	<?php if (isset($bx_button_action) && $bx_button_action) : ?>
		<button tabindex="0" class="bx--inline-notification__action-button bx--btn bx--btn--sm bx--btn--ghost" type="button"><?= isset($bx_button_action) ? $bx_button_action : ''; ?>
		</button>
	<?php endif; ?>
	<?php if (isset($custom_close) && $custom_close) : ?>
		<button class="bx--inline-notification__close-button" type="button" onclick=<?= $custom_close; ?> aria-label="close">
			<svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bx--inline-notification__close-icon" width="20" height="20" viewBox="0 0 32 32" aria-hidden="true">
				<path d="M24 9.4L22.6 8 16 14.6 9.4 8 8 9.4 14.6 16 8 22.6 9.4 24 16 17.4 22.6 24 24 22.6 17.4 16 24 9.4z"></path>
			</svg>
		</button>
	<?php endif; ?>
	<?php if (isset($close) && $close) : ?>
		<button data-notification-btn class="bx--inline-notification__close-button" style="<?= isset($close_button_style) ? $close_button_style : '' ; ?>"  type="button" aria-label="close">
			<svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bx--inline-notification__close-icon" width="20" height="20" viewBox="0 0 32 32" aria-hidden="true">
				<path d="M24 9.4L22.6 8 16 14.6 9.4 8 8 9.4 14.6 16 8 22.6 9.4 24 16 17.4 22.6 24 24 22.6 17.4 16 24 9.4z"></path>
			</svg>
		</button>
	<?php endif; ?>
</div>
