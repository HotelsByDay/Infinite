
<div class="control-group <?= isset($error_message[$name]) ? 'error' : '' ?>">
    <label><?= __('gift_firstname.form.firstname.label') ?></label>
    <input type="text" class="input-block-level" name="<?= $attr ?>[<?= $name ?>][]" value="<?= $model->{$name} ?>" />


    <?php if (isset($error_message[$name])): ?>
        <div class="validation_error text-error">
            <?= $error_message[$name] ?>
        </div>
    <?php endif; ?>
</div>
