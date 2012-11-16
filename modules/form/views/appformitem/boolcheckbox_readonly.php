
<div  class="appformitemcontainer appformitemboolcheckbox <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">
  <?php if (!empty($error_message)): ?>
    <span class="validation_error alert alert-error"><?= $error_message; ?></span>
  <?php endif ?>
    <label for="<?= $attr ?>">
    <input type="checkbox" disabled="disabled" id="<?= $attr ?>" name="" value="1" <?php if ($value) echo 'checked="checked"'; ?> />
    <?php if ($value): ?>
        <input type="hidden" name="<?= $attr ?>" value="1" />
    <?php endif; ?>
        <?= $label ?>
    </label>

  <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
  <?php endif ?>
</div>