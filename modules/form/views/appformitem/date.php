
<div  class="appformitemcontainer appformitemdate form-group <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">

    <label for="<?= $attr ?>"><?= $label ?></label>
    <input type="text" id="<?= $attr ?>" name="<?= $attr ?>" value="<?= htmlspecialchars($value) ?>" class="form-control date_picker" />
    <?php if (isset($hint) && !empty($hint)): ?>
        <span class="hint"><?= $hint; ?></span>
    <?php endif ?>


    <?php if (!empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>
</div>