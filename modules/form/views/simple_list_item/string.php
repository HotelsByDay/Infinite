
<div class="control-group <?= isset($error_message[$name]) ? 'error' : '' ?>">
    <label>
        <?= __($model->object_name().'.form.'.$name.'.label') ?>
        <?php if (isset($required) and $required): ?>
            <span class="required_label"></span>
        <?php endif; ?>
    </label>
    <input type="text" class="input-block-level" name="<?= $attr ?>[<?= $name ?>][]" value="<?= $model->{$name} ?>" />


    <?php if (isset($error_message[$name])): ?>
        <div class="validation_error text-error">
            <?= $error_message[$name] ?>
        </div>
    <?php endif; ?>
</div>

