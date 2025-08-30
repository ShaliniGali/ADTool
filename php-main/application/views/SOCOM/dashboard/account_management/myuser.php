<div data-content-switcher class="bx--content-switcher w-50 mb-5">
    <button class="bx--content-switcher-btn" data-target=".admin--panel--opt-1">Current Status</button>
    <button class="bx--content-switcher-btn bx--content-switcher--selected" data-target=".admin--panel--opt-2">Request Additional Status</button>
</div>
<div class="admin--panel--opt-1" hidden>
    <section class="bx--structured-list w-50">
        <div class="bx--structured-list-thead">
            <div class="bx--structured-list-row bx--structured-list-row--header-row">
                <div class="bx--structured-list-th">Information Group:</div>
                <div class="bx--structured-list-th">User Details</div>
            </div>
        </div>
        <div class="bx--structured-list-tbody">
            <div class="bx--structured-list-row">
                <div class="bx--structured-list-td bx--structured-list-content--nowrap">Email</div>
                <div class="bx--structured-list-td bx--structured-list-content--nowrap"><?= $email ?? 'not found' ?></div>
            </div>
            <div class="bx--structured-list-row">
                <div class="bx--structured-list-td bx--structured-list-content--nowrap">Pom Status</div>
                <div class="bx--structured-list-td bx--structured-list-content--nowrap"><?= $pom_group ?? 'No Pom Status' ?></div>
            </div>
            <div class="bx--structured-list-row">
                <div class="bx--structured-list-td">Admin Status</div>
                <div class="bx--structured-list-td"><?= $admin_group ?? 'No Admin Status' ?></div>
            </div>
            <div class="bx--structured-list-row">
                <div class="bx--structured-list-td">AO AD Status</div>
                <div class="bx--structured-list-td"><?= $ao_ad_group ?? 'No AO or AD Status' ?></div>
            </div>
            <div class="bx--structured-list-row">
                <div class="bx--structured-list-td">Cycle Weight Criteria Status</div>
                <div class="bx--structured-list-td"><?= $cycle_weight_group ?? 'No Cycle or Weight Criteria Status' ?></div>
            </div>
            <div class="bx--structured-list-row">
                <div class="bx--structured-list-td bx--structured-list-content--nowrap">Cap Sponsor Status</div>
                <div class="bx--structured-list-td bx--structured-list-content--nowrap"><?= $cap_sponsor_group ?? 'No Cap Sponsor Status' ?></div>
            </div>
        </div>
    </section>
</div>
<div class="admin--panel--opt-2 w-100">
    <?php if($is_user || $is_admin): ?>
    <div class="row">
        <div class="col">
            <div class="bx--form-item">
                <div class="bx--select">
                    <label for="admin-status-id" class="bx--label">Choose your User Admin Status</label>
                    <select id="admin-status-id" class="bx--select-input">
                        <option class="bx--select-option" value="1">No Status</option>
                        <option class="bx--select-option" value="2">User Admin</option>
                    </select>
                    <svg class="bx--select__arrow"
                        width="10" height="5" viewBox="0 0 10 5">
                        <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                    </svg>
                </div>
                <div class="bx--form-item mt-3">
                    <button id="admin-status-save" class="bx--btn bx--btn--primary" type="button">Save Admin Status</button>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="bx--form-item">
                <div class="bx--select">
                    <label for="pom-status-id" class="bx--label">Choose your Pom Status</label>
                    <select id="pom-status-id" class="bx--select-input">
                        <option class="bx--select-option" value="1">None</option>
                        <option class="bx--select-option" value="2">Pom Admin</option>
                        <option class="bx--select-option" value="3">Pom User</option>
                    </select>
                    <svg class="bx--select__arrow"
                        width="10" height="5" viewBox="0 0 10 5">
                        <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                    </svg>
                </div>
                <div class="bx--form-item mt-3">
                    <button id="pom-status-save" class="bx--btn bx--btn--primary" type="button">Save Pom Status</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="bx--form-item mt-5">
                <div class="bx--select">
                    <label for="ao-ad-status-id" class="bx--label">Choose your AO or AD Status</label>
                    <select id="ao-ad-status-id" class="bx--select-input">
                        <option class="bx--select-option" value="1">No Status</option>
                        <option class="bx--select-option" value="2">AO Status</option>
                        <option class="bx--select-option" value="3">AD Status</option>
                        <option class="bx--select-option" value="4">AO and AD Status</option>
                    </select>
                    <svg class="bx--select__arrow"
                        width="10" height="5" viewBox="0 0 10 5">
                        <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                    </svg>
                </div>
                <div class="bx--form-item mt-3">
                    <button id="ao-ad-status-save" class="bx--btn bx--btn--primary" type="button">Save AO or AD Status</button>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="bx--form-item mt-5">
                <div class="bx--select">
                    <label for="cycle-status-id" class="bx--label">Choose your Cycle Weight Criteria Status</label>
                    <select id="cycle-status-id" class="bx--select-input">
                        <option class="bx--select-option" value="1">No Status</option>
                        <option class="bx--select-option" value="2">Cycle Status</option>
                        <option class="bx--select-option" value="3">Weight Criteria Status</option>
                    </select>
                    <svg class="bx--select__arrow"
                        width="10" height="5" viewBox="0 0 10 5">
                        <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                    </svg>
                </div>
                <div class="bx--form-item mt-3">
                    <button id="cycle-status-save" class="bx--btn bx--btn--primary" type="button">Save Cycle Status</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="col">
            <div class="bx--form-item mt-5">
                <div class="bx--select <?= $has_old_cap_sponsor_group === true ? 'bx--select--invalid' : '' ?>">
                    <label for="cap-status-id" class="bx--label">Choose your Cap Sponsor Status</label>
                    <?php
                    if ($has_old_cap_sponsor_group === true):
                    ?>
                        <div class="bx--select-input__wrapper" data-invalid="true">
                        <?php endif; ?>
                        <select id="cap-status-id" class="bx--select-input" <?= $has_old_cap_sponsor_group === true ? 'aria-describedby="select-1-error-msg" aria-invalid="true"' : '' ?>>
                            <option class="bx--select-option" value="0">No Status</option>
                            <?php foreach ($all_cap_groups as $value => $label): ?>
                                <option class="bx--select-option" value="<?= $label ?>"
                                    <?= ($all_cap_groups == $value) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="bx--select__arrow"
                            width="10" height="5" viewBox="0 0 10 5">
                            <path d="M0 0l5 4.998L10 0z" fill-rule="evenodd" />
                        </svg>

                        <?php
                        if ($has_old_cap_sponsor_group === true):
                        ?>
                        </div>
                        <div class="bx--form-requirement" id="select-1-error-msg">Current Cap Sponsor Group is no Longer Available.</div>
                    <?php endif; ?>
                </div>
                <div class="bx--form-item mt-3">
                    <button id="cap-status-save" class="bx--btn bx--btn--primary" type="button">Save Cap Sponsor Status</button>
                </div>
            </div>
        </div> 
        <div class="col"></div>
    </div>
</div>

<?php if ($this->session->flashdata('error')): ?>
    <script>
        displayToastNotification('error', '<?= $this->session->flashdata('error') ?>');
    </script>
<?php endif; ?>

<script>
    const cap_groups_all = JSON.parse('<?= json_encode(array_values($all_cap_groups)); ?>');

    <?php
    if ($has_old_cap_sponsor_group === true):
    ?>
        displayToastNotification('error', 'The Cap Sponsor Group assigned to your account is no longer available.  Please choose a new Cap Sponsor Group.');
    <?php
    endif;
    ?>
</script>