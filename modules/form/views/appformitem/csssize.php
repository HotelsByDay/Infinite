<div  class="appformitemcontainer appformitemcsssize<?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">
  <?php if (!empty($error_message)): ?>
    <span class="validation_error"><?= $error_message; ?></span>
  <?php endif ?>

    <label for="<?= $uid ?>_value"><?= $label ?></label>
    <input type="text" id="<?= $uid ?>_value" name="<?= $attr ?>" value="<?= htmlspecialchars($value) ?>" />
    <div class="slider"></div>


  <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
  <?php endif ?>
</div>