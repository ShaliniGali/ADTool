<style>
	.bx--overflow-menu__icon {
		fill: #161616 !important;
	}

	.overflow-btn-primary {
		color: #fff !important;
		background-color: #0f62fe !important;
		border-color: rgba(0,0,0,0) !important;
		border-style: solid !important;
		border-width: 1px !important;
	}

	.overflow-btn {
		font-size: .875rem !important;
		font-weight: 400 !important;
		line-height: 1.29 !important;
		letter-spacing: .16px !important;
		position: relative !important;
		display: flex !!important;
		flex-shrink: 0 !important;
		align-items: center !important;
		justify-content: space-between !important;
		width:unset !important;
		height: unset !important;
		max-width: 20rem !important;
		min-height: 3rem !important;
		margin: 0 !important;
		padding: calc(0.875rem - 3px) 63px calc(0.875rem - 3px) 15px !important;
		text-align: left !important;
		text-decoration: none !important;
		vertical-align: top !important;
		border-radius: 0 !important;
		outline: none !important;
		cursor: pointer !important;
		-webkit-transition: background 70ms cubic-bezier(0, 0, 0.38, 0.9),border-color 70ms cubic-bezier(0, 0, 0.38, 0.9),outline 70ms cubic-bezier(0, 0, 0.38, 0.9),-webkit-box-shadow 70ms cubic-bezier(0, 0, 0.38, 0.9) !important;
		transition: background 70ms cubic-bezier(0, 0, 0.38, 0.9),border-color 70ms cubic-bezier(0, 0, 0.38, 0.9),outline 70ms cubic-bezier(0, 0, 0.38, 0.9),-webkit-box-shadow 70ms cubic-bezier(0, 0, 0.38, 0.9) !important;
		transition: background 70ms cubic-bezier(0, 0, 0.38, 0.9),box-shadow 70ms cubic-bezier(0, 0, 0.38, 0.9),border-color 70ms cubic-bezier(0, 0, 0.38, 0.9),outline 70ms cubic-bezier(0, 0, 0.38, 0.9) !important;
		transition: background 70ms cubic-bezier(0, 0, 0.38, 0.9),box-shadow 70ms cubic-bezier(0, 0, 0.38, 0.9),border-color 70ms cubic-bezier(0, 0, 0.38, 0.9),outline 70ms cubic-bezier(0, 0, 0.38, 0.9),-webkit-box-shadow 70ms cubic-bezier(0,0,0.38,0.9) !important;
	}

	.bx--overflow-menu:hover,
	.bx--overflow-menu__trigger:hover {
		background-color: #696969 !important;
	}
</style>

<div data-overflow-menu class="bx--overflow-menu">
	<button class="bx--overflow-menu__trigger overflow-btn overflow-btn-primary" aria-haspopup="true" aria-expanded="false" id="<?=$id ? $id.'-trigger' : ''?>" aria-controls="list-menu">
		Set Action 
	</button>
	<div class="bx--overflow-menu-options" tabindex="-1" role="menu" aria-labelledby="list-menu-trigger" data-floating-menu-direction="bottom" id="<?=$id ?? ''?>">
		<ul class="bx--overflow-menu-options__content">
			<li class="bx--overflow-menu-options__option">
				<button class="bx--overflow-menu-options__btn" role="edit" title="An example option that is really long to show what should be done to handle long text" data-floating-menu-primary-focus>
					<span class="bx--overflow-menu-options__option-content">
						Process
					</span>
				</button>
			</li>
			<li class="bx--overflow-menu-options__option">
				<button class="bx--overflow-menu-options__btn" role="delete" disabled>
					<span class="bx--overflow-menu-options__option-content">
						Delete
					</span>
				</button>
			</li>
			<li class="bx--overflow-menu-options__option">
				<button class="bx--overflow-menu-options__btn" role="cancel">
					<span class="bx--overflow-menu-options__option-content">
						Cancel
					</span>
				</button>
			</li>
		</ul>
		<!-- Note: focusable span allows for focus wrap feature within Overflow Menus -->
		<span tabindex="0"></span>
	</div>
</div>