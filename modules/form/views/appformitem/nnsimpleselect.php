<div  class="appformitemnnsimpleselect" name="<?= $attr ?>_item" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error alert alert-error"><?= $error_message ?></span>
<?php endif ?>

<label class="main"><?= $label ?></label>

    <?php if (isset($hint) && !empty($hint)): ?>
      <span class="hint"><?= $hint; ?></span>
    <?php endif ?>

<?php foreach ($values as $key => $val): ?>
    <div class="item">
      <label class="checkbox">
          <input type="checkbox" name="<?= $attr ?>[<?= $key ?>]" value="<?= $key ?>" <?= (in_array($key, $value)) ? 'checked="checked"': '' ?> />
          <?= $val ?>
      </label>
    </div>
<?php endforeach; ?>

</div>