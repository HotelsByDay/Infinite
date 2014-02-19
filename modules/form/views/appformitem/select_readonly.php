<?php if ( ! empty($error_message)): ?>
    <span class="validation_error alert alert-error"><?= $error_message;?></span>
<?php endif ?>

<div class="appformitemcontainer <?= $css ?>">
    <label for="<?= $attr ?>_id"><?= $label ?></label>
    <input type="text" disabled="disabled" value="<?= arr::get($values, $value);?>" />
</div>