
<div  class="appformitemcontainer appformitemdate <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">
  <?php if (!empty($error_message)): ?>
    <span class="validation_error"><?= $error_message; ?></span>
  <?php endif ?>

    <label for="<?= $attr ?>"><?= $label ?></label>
    <input type="text" id="<?= $attr ?>" readonly="readonly" value="<?= htmlspecialchars($value) ?>" />
</div>