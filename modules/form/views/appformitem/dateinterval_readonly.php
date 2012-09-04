<div  class="appformitemcontainer appformitemdateinterval readonly <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">

  <?php if ( ! empty($error_message)): ?>
    <span class="validation_error"><?= $error_message; ?></span>
  <?php endif ?>

    <label><?= $label ?></label>
    <input type="text" name="<?= $attr ?>[from]" value="<?= htmlspecialchars($value['from']) ?>" readonly="readonly" />
    <span><?= __('appformitem.dateinterval.to_label') ?></span>
    <input type="text" name="<?= $attr ?>[to]" value="<?= htmlspecialchars($value['to']) ?>" readonly="readonly" />

</div>