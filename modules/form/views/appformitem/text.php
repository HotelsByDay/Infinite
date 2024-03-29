<div class="appformitemcontainer <?= $css?> form-group <?= empty($error_message) ? '' : 'has-error' ?>" id="<?= $uid; ?>">
<?php
    if (isset($disabled)) {
        $name = ''; $disabled = ' disabled="disabled"';
    } else {
        $name = ' name="'.$attr.'"';
    }
?>


<label for="<?= $attr ?>"><?= $label ?></label>
<textarea class="<?= $input_class ?> form-control" <?php if (isset($placeholder) and ! empty($placeholder)) echo "placeholder=\"$placeholder\""; ?> rows="<?= $rows ?>" id="<?= $attr ?>"<?= $name ?><?= $disabled ?><?= isset($min_length) ? " minlength=\"$min_length\"" : '' ?> <?= isset($max_length) ? " maxlength=\"$max_length\"" : '' ?>><?= htmlspecialchars($value) ?></textarea>

<?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
<?php endif ?>

    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>

</div>
