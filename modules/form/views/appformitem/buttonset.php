<div class="appformitemcontainer appformitembuttonset <?= $css ?>" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error alert alert-error"><?= $error_message;?></span>
<?php endif ?>

<label><?= $label ?></label>


<div class="items">
    <?php foreach ($values as $key => $val): ?>
        <input type="radio" id="<?= $attr.$key; ?>" name="<?= $attr ?>" value="<?= $key ?>" <?= ((string)$key == (string)$value) ? 'checked="checked"': ' ' ?>/>
        <label class="inline" for="<?= $attr.$key; ?>"><?= $val; ?></label>
    <?php endforeach; ?>
</div>

    <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
    <?php endif ?>

</div>