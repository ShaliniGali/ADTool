<style>
	.bx--overflow-menu__icon {
		fill: #161616 !important;
	}

	.bx--overflow-menu:hover,
	.bx--overflow-menu__trigger:hover {
		background-color: #696969 !important;
	}

	.bx--overflow-menu-options__btn {
		color: #161616 !important;
	}

	.bx--overflow-menu-options__btn:hover {
		color: #d3d4d3 !important;
	}
</style>

<div data-overflow-menu class="bx--overflow-menu" id="option_panel">
	<button class="bx--overflow-menu__trigger" aria-haspopup="true" aria-expanded="false" id="<?=$id ? $id.'-trigger' : 'option_panel_btn'?>" aria-controls="list-menu">
		<svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--overflow-menu__icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" fill="#333">
			<circle cx="8" cy="3" r="1"></circle>
			<circle cx="8" cy="8" r="1"></circle>
			<circle cx="8" cy="13" r="1"></circle>
		</svg>
	</button>
	<div class="bx--overflow-menu-options" tabindex="-1" role="menu" aria-labelledby="list-menu-trigger" data-floating-menu-direction="bottom" id="<?=$id ?? ''?>">
		<ul class="bx--overflow-menu-options__content">
		<li class="bx--overflow-menu-options__option">
				<button class="bx--overflow-menu-options__btn" role="active" title="Set score active for the option optimizer" data-floating-menu-primary-focus>
					<span class="bx--overflow-menu-options__option-content">
						Set Active
					</span>
				</button>
			</li>
			<li class="bx--overflow-menu-options__option">
				<button class="bx--overflow-menu-options__btn" role="edit" title="Edit the option score">
					<span class="bx--overflow-menu-options__option-content">
						Edit
					</span>
				</button>
			</li>
			<li class="bx--overflow-menu-options__option">
				<button class="bx--overflow-menu-options__btn" title="delete the option score" role="delete">
					<span class="bx--overflow-menu-options__option-content">
						Delete
					</span>
				</button>
			</li>
		</ul>
		<!-- Note: focusable span allows for focus wrap feature within Overflow Menus -->
		<span tabindex="0"></span>
	</div>
</div>
