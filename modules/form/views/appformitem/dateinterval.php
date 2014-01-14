
<div  class="appformitemcontainer appformitemdateinterval <?= $css; ?> control-group <?= empty($error_message) ? '' : 'error' ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">

    <label for="<?= $attr ?>_from"><?= $label ?></label>
    <input type="text" id="<?= $attr ?>_from" name="<?= $attr ?>[from]" value="<?= htmlspecialchars($value['from']) ?>" class="date_picker" readonly="readonly" />
    <span><?= __('appformitem.dateinterval.to_label') ?></span>
    <input type="text" id="<?= $attr ?>_to" name="<?= $attr ?>[to]" value="<?= htmlspecialchars($value['to']) ?>" class="date_picker" readonly="readonly" />

  <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
  <?php endif ?>


    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>
</div>