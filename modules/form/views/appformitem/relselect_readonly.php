<div name="<?= $attr;?>_item" class="appformitemcontainer <?= $css ?>">

<label ><?= $label;?></label>
<input type="text" readonly="readonly" id="<?= $attr;?>_name" value="<?= $name;?>" />
<input type="hidden" id="<?= $attr; ?>_id" name="<?= $attr; ?>[value]" value="<?= $value; ?>" />

</div>