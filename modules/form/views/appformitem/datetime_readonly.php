<div  class="appformitemcontainer <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error alert alert-error"><?= $error_message;?></span>
<?php endif ?>

<label for="<?= $attr ?>_date"><?= $label ?></label>
<span class="small input-small"><?= $date_value ?></span><span class="input-space">-</span>
<span class="small input-small"><?= $time_value ?></span>
</div>