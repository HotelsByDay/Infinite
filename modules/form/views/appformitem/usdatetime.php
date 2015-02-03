<div  class="appformitemcontainer <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error alert alert-error"><?= $error_message;?></span>
<?php endif ?>

<label for="<?= $attr ?>_date"><?= $label ?></label>
<input type="text" id="<?= $attr ?>[date]" name="<?= $attr ?>[date]" value="<?= arr::get($value, 'date') ?>" class="date_picker small input-small" /><span class="input-space">-</span>


<?= form::select($attr.'[time]', $time_values, arr::get($value, 'time'), array('class' => 'input-small')); ?>
<?= form::select($attr.'[time_type]', array('am' => 'am', 'pm' => 'pm'), arr::get($value, 'time_type'), array('class' => 'input-mini')); ?>


<?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
<?php endif ?>
</div>