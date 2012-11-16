<div  class="appformitemcontainer appformitemgradientcolorpicker <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">
  <?php if (!empty($error_message)): ?>
    <span class="validation_error alert alert-error"><?= $error_message; ?></span>
  <?php endif ?>

    <label for="<?= $uid ?>_color"><?= $label ?></label>
        <input type="text" id="<?= $uid ?>_color" name="<?= $attr ?>[color]" value="<?= htmlspecialchars($value['color']) ?>" />
    <div class="color_picker_color"></div>

    <div class="gradient_active">
        <input type="checkbox" id="<?= $uid ?>_gradient" name="<?= $attr ?>[gradient]" value="1" <?php if ($value['gradient']) echo 'checked'; ?> />
        <label for="<?= $uid ?>_gradient"><?= __('appformitem_gradientcolorpicker.gradient_enabled'); ?></label>
    </div>


    <div class="gradient_colors">
        <div class="slider"></div>
        <input type="hidden" name="<?= $attr ?>[slider]" value="<?= $value['slider'] ?>" />

        <label for="<?= $uid ?>_start"><?= __('gradientcolorpicker.start') ?></label>
        <input type="text" id="<?= $uid ?>_start" name="<?= $attr ?>[start]" value="<?= htmlspecialchars($value['start']) ?>" />
        <div class="color_picker_start"></div>

        <label for="<?= $uid ?>_end"><?= __('gradientcolorpicker.end') ?></label>
        <input type="text" id="<?= $uid ?>_end" name="<?= $attr ?>[end]" value="<?= htmlspecialchars($value['end']) ?>" />
        <div class="color_picker_end"></div>
    </div>


  <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
  <?php endif ?>
</div>