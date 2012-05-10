
<div name="<?= $attr ?>_item" class="appformitemcontainer <?= $css ?>" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error" style="color:red;"><?= $error_message;?></span>
<?php endif ?>

<label for="<?= $attr ?>_name"><?= $label ?></label>
<input type="text" id="<?= $attr ?>_name" name="<?= $name_attr ?>" value="<?= $name ?>" <?= isset($watermark) ? 'class="watermark"' : '' ?> />

<input type="hidden" id="<?= $attr ?>_id" name="<?= $value_attr ?>" value="<?= (int)$value ?>" />

<?php if (isset($new) && $new): ?>
    <a href="#" class="add_new button red"><?= $new_label;?></a>
<?php endif ?>

</div>