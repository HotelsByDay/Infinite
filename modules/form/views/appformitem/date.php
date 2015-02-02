
<div  class="appformitemcontainer appformitemdate <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">
    <?php if (!empty($error_message)): ?>
        <span class="validation_error alert alert-error"><?= $error_message; ?></span>
    <?php endif ?>

    <label for="<?= $attr ?>"><?= $label ?></label>
    <input type="text" id="<?= $attr ?>" name="<?= $attr ?>" value="<?= htmlspecialchars($value) ?>" class="date_picker" />
    <?php if (isset($hint) && !empty($hint)): ?>
        <span class="hint"><?= $hint; ?></span>
    <?php endif ?>
</div>