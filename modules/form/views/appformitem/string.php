<div class="appformitemstring appformitemcontainer <?= $css?> " id="<?= $uid;?>">

    <?php if (!empty($error_message)): ?>
        <span class="validation_error alert alert-error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

    <label for="<?= $attr ?>"><?= $label ?></label>

    <?php if (isset($field_prefix)): ?>
        <span class="field_prefix"><?= $field_prefix ?></span>
    <?php endif; ?>
    <input type="text" <?php if (isset($placeholder) and ! empty($placeholder)) echo "placeholder=\"$placeholder\""; ?> <?php if ($html_autocomplete) echo 'autocomplete="'.$html_autocomplete.'"';?> id="<?= $attr ?>" name="<?= $attr ?>" value="<?= htmlspecialchars($value) ?>" <?= ! $editable ? 'readonly="readonly"' : '';?> <?= isset($min_length) ? "minlength=\"$min_length\"" : '' ?> <?= isset($max_length) ? "maxlength=\"$max_length\"" : '' ?> />
    <?php if (isset($hint) && !empty($hint)): ?>
        <span class="hint"><?= $hint; ?></span>
    <?php endif ?>
</div>