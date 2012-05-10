
<?php if ( ! empty($error_message)): ?>
<strong style="color:red;"><?= $error_message;?></strong>
<?php endif ?>




<div class="appformitembuttonset <?= $css ?>" id="<?= $uid;?>">
    
<label class="main"><?= $label ?></label>

<?php foreach ($values as $key => $val): ?>

    <input type="radio" id="<?= $attr.$key; ?>" name="<?= $attr ?>" value="<?= $key ?>" disabled="disabled" <?= ($key==$value) ? 'checked="checked"': ' ' ?>/>
    <label for="<?= $attr.$key; ?>"><?= $val; ?></label>

<?php endforeach; ?>

</div>