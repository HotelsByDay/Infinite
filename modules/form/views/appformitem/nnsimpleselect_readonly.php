<div  class="appformitemnnsimpleselect" name="<?= $attr ?>_item" id="<?= $uid;?>">
    
<label for="<?= $attr ?>_id"><?= $label ?></label>

    <?php if (isset($hint) && !empty($hint)): ?>
      <span class="hint"><?= $hint; ?></span>
    <?php endif ?>

<?php foreach ($values as $key => $val): ?>
    <div class="item">
      <label for="<?= $attr.$key ?>"><?= $val ?></label>
      <input type="checkbox" id="<?= $attr.$key ?>" disabled="disabled" <?= (in_array($key, $value)) ? 'checked="checked"': ' ' ?>/>
      
    </div>
<?php endforeach; ?>

</div>