<div  class="appformitemcontainer <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error"><?= $error_message;?></span>
<?php endif ?>

<label for="<?= $attr ?>_date"><?= $label ?></label>
<input type="text" id="<?= $attr ?>[date]" name="<?= $attr ?>[date]" value="<?= arr::get($value, 'date') ?>" class="date_picker small" /><span class="input-space">-</span><input type="text" id="<?= $attr ?>[time]" name="<?= $attr ?>[time]" value="<?= arr::get($value, 'time') ?>" class="small" />
  <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
  <?php endif ?>
</div>