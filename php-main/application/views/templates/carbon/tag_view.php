<!-- COLOR OPTIONS
red
magenta
purple
blue
cyan
teal
green
gray
cool-gray
warm-gray
red -->

<button id="<?= isset($id) ? $id : ''; ?>" class="bx--tag bx--tag--<?= isset($color) ? $color : 'cool-gray'; ?> <?= isset($extra_classes) ? $extra_classes : ''; ?>" <?= isset($name) ? "name=".$name : ''; ?> <?= isset($modal) ? "data-modal-target=".$modal : ''; ?>>
    <span class="bx--tag__label" onclick="<?= isset($onclick) ? $onclick : ""; ?>"><?= $bx_label; ?></span>
</button>