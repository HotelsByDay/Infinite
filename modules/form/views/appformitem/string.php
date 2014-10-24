<div class="appformitemstring appformitemcontainer <?= $css?> control-group <?= empty($error_message) ? '' : 'error' ?> " id="<?= $uid;?>">

    <label for="<?= $attr ?>">
        <?= $label ?>
        <?php if (isset($hint) && !empty($hint)): ?>
            &nbsp; <i class="icon icon-question-sign" rel="tooltip" title="<?= $hint ?>"></i>
        <?php endif ?>
    </label>

    <?php if (isset($field_prefix)): ?>
        <span class="field_prefix"><?= $field_prefix ?></span>
    <?php endif; ?>

    <input type="text" class="<?= isset($input_class) ? $input_class : '' ?>" <?php if (isset($placeholder) and ! empty($placeholder)) echo "placeholder=\"$placeholder\""; ?> <?php if ($html_autocomplete) echo 'autocomplete="'.$html_autocomplete.'"';?> id="<?= $attr ?>" name="<?= $attr ?>" value="<?= htmlspecialchars($value) ?>" <?= ! $editable ? 'readonly="readonly"' : '';?> <?= isset($min_length) ? "minlength=\"$min_length\"" : '' ?> <?= isset($max_length) ? "maxlength=\"$max_length\"" : '' ?> />

    <?php if (isset($field_suffix)): ?>
        <span class="field_suffix"><?= $field_suffix ?></span>
    <?php endif; ?>


    <?php if (!empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>


</div>