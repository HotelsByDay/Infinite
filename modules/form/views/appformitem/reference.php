<div class="formitemreference <?= $css ?>" id="<?= $uid;?>">

    <?php if ( ! empty($error_message)): ?>
    <span class="validation_error"><?= $error_message;?></span>
    <?php endif ?>

    <label for="<?= $attr ?>_reltype"><?= $label ?></label>
    <select for="<?= $attr ?>_reltype" name="<?= $attr;?>[reltype]">
        <?php foreach ($rel_objects as $reltype => $name):?>
        <option value="<?= $reltype;?>" <?= arr::get($value, 'reltype') == $reltype ? 'selected="selected"' : '';?>><?= $name;?></option>
        <?php endforeach ?>
    </select>

    <input type="text" id="<?= $attr ?>_name" name="<?= $attr ?>[relpreview]" value="<?= arr::get($value, 'relpreview' , ''); ?>" <?= isset($watermark) ? 'class="watermark"' : '' ?> />

    <input type="hidden" id="<?= $attr ?>_id" name="<?= $attr ?>[relid]" value="<?= arr::get($value, 'relid', ''); ?>" />

</div>