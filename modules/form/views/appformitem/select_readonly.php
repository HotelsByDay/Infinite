<?php if ( ! empty($error_message)): ?>
    <span class="validation_error alert alert-error"><?= $error_message;?></span>
<?php endif ?>

<div class="appformitemcontainer <?= $css ?>">
    <label for="<?= $attr ?>_id"><?= $label ?></label>
    <select disabled="disabled" class="input-block-level">
        <option value="<?= $value ?>"><?= arr::get($values, $value);?></option>
    </select>
</div>