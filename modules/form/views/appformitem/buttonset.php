<div class="appformitembuttonset <?= $css ?>" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error"><?= $error_message;?></span>
<?php endif ?>

<label class="main"><?= $label ?></label>

<?php foreach ($values as $key => $val): ?>

    <input type="radio" id="<?= $attr.$key; ?>" name="<?= $attr ?>" value="<?= $key ?>" <?= ((string)$key == (string)$value) ? 'checked="checked"': ' ' ?>/>
    <label for="<?= $attr.$key; ?>"><?= $val; ?></label>

<?php endforeach; ?>

</div>