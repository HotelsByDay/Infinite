
<?php if ( ! empty($error_message)): ?>
<strong style="color:red;"><?= $error_message;?></strong>
<?php endif ?>




<div class="appformitembuttonset <?= $css ?>" id="<?= $uid;?>">
    
<label class="main"><?= $label ?></label>

    <div class="items">
        <?php foreach ($values as $key => $val): ?>
        <input type="radio" id="<?= $attr.$key; ?>" name="<?= $attr ?>" disabled="disabled" value="<?= $key ?>" <?= ((string)$key == (string)$value) ? 'checked="checked"': ' ' ?>/>
        <label class="inline" for="<?= $attr.$key; ?>"><?= $val; ?></label>
        <?php endforeach; ?>
    </div>

</div>