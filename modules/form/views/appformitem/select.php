<div class="appformitemcontainer <?= $css ?>">
    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error alert alert-error" style="color:red;"><?= $error_message;?></span>
    <?php endif ?>
    <label for="<?= $attr ?>_id"><?= $label ?></label>

    <?php if (isset($field_prefix)): ?>
        <span class="field_prefix"><?= $field_prefix ?></span>
    <?php endif; ?>

    <?= form::select($attr, $values, (string)$value, Array('id'=>$attr.'_id')) ?>

    <?php if (isset($field_suffix)): ?>
        <span class="field_suffix"><?= $field_suffix ?></span>
    <?php endif; ?>
</div>