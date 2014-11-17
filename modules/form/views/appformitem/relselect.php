<div name="<?= $attr ?>_item" class="appformitemcontainer appformitem_relselect <?= $css ?> form-group <?= empty($error_message) ? '' : 'has-error' ?>" id="<?= $uid;?>">

<label for="<?= $attr ?>_name"><?= $label ?></label>

    <?php if (isset($new) && $new): ?>
    <div class="input-group">
    <?php endif; ?>

        <input type="text" id="<?= $attr ?>_name" name="<?= $name_attr ?>" value="<?= $name ?>" class="form-control <?= isset($watermark) ? $watermark : '' ?> <?= isset($input_class) ? $input_class : '' ?>" />

        <input type="hidden" id="<?= $attr ?>_id" name="<?= $value_attr ?>" value="<?= (int)$value ?>" />

    <?php if (isset($new) && $new): ?>
        <span class="input-group-btn">
            <a href="#" class="add_new btn btn-success"><?= $new_label;?></a>
        </span>
    </div>
    <?php endif ?>
    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>
</div>