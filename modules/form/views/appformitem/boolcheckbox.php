
<div  class="appformitemcontainer appformitemboolcheckbox <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">
  <?php if (!empty($error_message)): ?>
    <span class="validation_error alert alert-error"><?= $error_message; ?></span>
  <?php endif ?>

    <input type="hidden" name="<?= $attr ?>" value="0" />

    <div class="checkbox">

    <label class="pull-left">
       <input type="checkbox" name="<?= $attr ?>" value="1" <?php if ($value) echo 'checked="checked"'; ?> />
        <?= $label ?>
    </label>

        <?php if (isset($hint) && !empty($hint)): ?>
            &nbsp; <i class="icon icon-question-sign" rel="tooltip" title="<?= $hint ?>"></i>
        <?php endif ?>
    </div>

</div>