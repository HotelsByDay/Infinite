<?php
/** @var string $label */
/** @var string $attr */
/** @var string $css */
/** @var array $selected */
?>
<div class="appformitemcontainer appformitemrelnnselect <?= $css ?>">

    <label for="<?= $uid ?>"><?= $label ?></label>
    <input type="text" name="<?= $attr ?>" placeholder="<?= isset($placeholder) ? $placeholder : '' ?>" id="<?= $uid ?>" data-selected="<?= htmlentities(json_encode($selected)) ?>" />

    <?php if (!empty($error_message)): ?>
        <span class="validation_error text-error" style="margin-top: 2px;"><?= $error_message ?></span>
    <?php endif ?>
</div>