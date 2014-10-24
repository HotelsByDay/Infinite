<div class="appformitemcontainer appformitembuttonset <?= $css ?>" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error alert alert-error"><?= $error_message;?></span>
<?php endif ?>

<label class="main">
    <?= $label ?>

    <?php if (isset($hint) && !empty($hint)): ?>
        &nbsp; <i class="icon icon-question-sign" rel="tooltip" title="<?= $hint ?>"></i>
    <?php endif ?>
</label>


<div class="items">
    <?php foreach ($values as $key => $val): ?>
        <input type="radio" id="<?= $attr.$key; ?>" name="<?= $attr ?>" value="<?= $key ?>" <?= ((string)$key == (string)$value) ? 'checked="checked"': ' ' ?>/>
        <label class="inline" for="<?= $attr.$key; ?>"><?= $val; ?></label>
    <?php endforeach; ?>
</div>

</div>