<div class="appformitemcontainer <?= $css ?> form-group <?= empty($error_message) ? '' : 'has-error' ?>">
    <label for="<?= $attr ?>_id"><?= $label ?></label>

    <?= form::select($attr, $values, (string)$value, Array('id'=>$attr.'_id', 'class' => 'input-block-level form-control')) ?>

    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>
</div>