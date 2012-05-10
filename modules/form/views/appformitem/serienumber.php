
<div class="appformitemcontainer <?= $css ?>">
<label><?= $label ?></label>

	<?php if (isset($msg)): ?>
	<strong><?=  $msg ?></strong>
	<?php else: ?>
	
    <label for="<?= $attr ?>"><?= $label ?></label>
    <input type="text" id="<?= $attr ?>" name="<?= $attr ?>" value="<?= $value ?>" <?= isset($min_length) ? "minlength=\"$min_length\"" : '' ?> <?= isset($max_length) ? "maxlength=\"$max_length\"" : '' ?> />
    <?php if (isset($hint) && !empty($hint)): ?>
      <span class="hint"><?= $hint; ?></span>
    <?php endif ?>
	<?php endif ?>

</div>
