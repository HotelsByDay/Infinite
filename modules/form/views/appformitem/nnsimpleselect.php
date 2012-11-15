<div  class="appformitemnnsimpleselect" name="<?= $attr ?>_item" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error alert alert-error"><?= $error_message ?></span>
<?php endif ?>

<label for="<?= $attr ?>_id"><?= $label ?></label>

    <?php if (isset($hint) && !empty($hint)): ?>
      <span class="hint"><?= $hint; ?></span>
    <?php endif ?>

<?php foreach ($values as $key => $val): ?>
    <div class="item">
      <label for="<?= $attr.$key ?>"><?= $val ?></label>
      <input type="checkbox" id="<?= $attr.$key ?>" name="<?= $attr ?>[<?= $key ?>]" value="<?= $key ?>" <?= (in_array($key, $value)) ? 'checked="checked"': ' ' ?>/>
      
    </div>
<?php endforeach; ?>

</div>