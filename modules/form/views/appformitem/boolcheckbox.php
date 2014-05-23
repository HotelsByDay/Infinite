
<div  class="appformitemcontainer appformitemboolcheckbox <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">
  <?php if (!empty($error_message)): ?>
    <span class="validation_error alert alert-error"><?= $error_message; ?></span>
  <?php endif ?>

    <input type="hidden" name="<?= $attr ?>" value="0" />
    <label class="checkbox"><?= $label ?>
       <input class="checkbox" type="checkbox" name="<?= $attr ?>" value="1" <?php if ($value) echo 'checked="checked"'; ?> />
    </label>

  <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
  <?php endif ?>
</div>