
<?php if ( ! empty($error_message)): ?>
<span class="validation_error alert alert-error"><?= $error_message ?></span>
<?php endif ?>

<div  class="appformitemcontainer <?= $css ?>" name="<?= $attr ?>_item" id="<?= $uid;?>">
<label for="<?= $attr ?>_id"><?= $label ?></label>

<ul class="list">
<?php foreach ($values as $key => $val): ?>
    <li>
        <input type="checkbox" id="<?= $attr.$key ?>" name="<?= $attr ?>[<?= $key ?>]" value="<?= $key ?>" <?= (in_array($key, $value)) ? 'checked="checked"': ' ' ?>/>
        <label for="<?= $attr.$key ?>"><?= $val ?></label>

        <span class="handle">===</span>
    </li>
<?php endforeach; ?>
</ul>

</div>