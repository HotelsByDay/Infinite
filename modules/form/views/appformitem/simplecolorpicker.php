<div  class="appformitemcontainer appformitemsimplecolorpicker <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">
  <?php if (!empty($error_message)): ?>
    <span class="validation_error"><?= $error_message; ?></span>
  <?php endif ?>

    <label for="<?= $uid ?>_input"><?= $label ?></label>
    <input type="text" id="<?= $uid ?>_input" name="<?= $attr ?>" value="<?= htmlspecialchars($value) ?>" />
    <div class="color_picker_div"></div>

  <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
  <?php endif ?>
</div>