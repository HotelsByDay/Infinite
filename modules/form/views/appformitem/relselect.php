<div name="<?= $attr ?>_item" class="appformitemcontainer appformitem_relselect <?= $css ?> form-group <?= empty($error_message) ? '' : 'has-error' ?>" id="<?= $uid;?>">

<label for="<?= $attr ?>_name"><?= $label ?></label>

<!--<div class="form-inline">-->
<input type="text" id="<?= $attr ?>_name" name="<?= $name_attr ?>" value="<?= $name ?>" class="form-control <?= isset($watermark) ? $watermark : '' ?> <?= isset($input_class) ? $input_class : '' ?>" />

<input type="hidden" id="<?= $attr ?>_id" name="<?= $value_attr ?>" value="<?= (int)$value ?>" />

<?php if (isset($new) && $new): ?>
    <a href="#" class="add_new btn btn-primary"><?= $new_label;?></a>
<?php endif ?>
<!--</div>-->


    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>

</div>