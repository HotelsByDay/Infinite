

<div class="appformitemcontainer <?= $css ?>">
    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error" style="color:red;"><?= $error_message;?></span>
    <?php endif ?>
    <label for="<?= $attr ?>_id"><?= $label ?></label>

    <?= form::select($attr, $values, (string)$value, Array('id'=>$attr.'_id')) ?>

</div>