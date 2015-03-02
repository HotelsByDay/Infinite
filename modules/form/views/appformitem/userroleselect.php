<div  class="appformitemnnsimpleselect" name="<?= $attr ?>_item" id="<?= $uid;?>">

<?php if ( ! empty($error_message)): ?>
<span class="validation_error alert alert-error"><?= $error_message ?></span>
<?php endif ?>

<label class="main"><?= $label ?></label>

    <?php if (isset($hint) && !empty($hint)): ?>
      <span class="hint"><?= $hint; ?></span>
    <?php endif ?>

    <?php foreach ($values as $role): ?>
        <div class="item">
          <label class="checkbox">
              <input type="checkbox" name="<?= $attr ?>[<?= $role->pk() ?>]" value="<?= $role->name ?>" <?= (in_array($role->pk(), $value)) ? 'checked="checked"': '' ?> />
              <?= $role->title ?>
          </label>
        </div>
    <?php endforeach; ?>

</div>