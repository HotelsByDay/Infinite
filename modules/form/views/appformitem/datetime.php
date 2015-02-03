<div  class="appformitemcontainer <?= $css; ?> control-group <?= empty($error_message) ? '' : 'error' ?>" name="<?= $attr ?>_item" id="<?= $uid;?>">


<label for="<?= $attr ?>_date"><?= $label ?></label>
<input type="text" id="<?= $attr ?>[date]" name="<?= $attr ?>[date]" value="<?= arr::get($value, 'date') ?>" class="date_picker small input-small" /><span class="input-space">-</span><input type="text" id="<?= $attr ?>[time]" name="<?= $attr ?>[time]" value="<?= arr::get($value, 'time') ?>" class="small input-small" />
  <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
  <?php endif ?>


    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>
</div>