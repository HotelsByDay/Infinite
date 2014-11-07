<?php if ( ! empty($error_message)): ?>
    <span class="validation_error alert alert-error"><?= $error_message;?></span>
<?php endif ?>

<div class="appformitemcontainer form-group <?= $css ?>">
    <label for="<?= $attr ?>_id"><?= $label ?></label>
    <select disabled="disabled">
        <option value="<?= $value ?>"><?= arr::get($values, $value);?></option>
    </select>
</div>