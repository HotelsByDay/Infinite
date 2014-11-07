<div name="<?= $attr;?>_item" class="form-group appformitemcontainer <?= $css ?>">

<label ><?= $label;?></label>
<input type="text" readonly="readonly" id="<?= $attr;?>_name" class="form-control" value="<?= $name;?>" />
<input type="hidden" id="<?= $attr; ?>_id" name="<?= $attr; ?>[value]" value="<?= $value; ?>" />

</div>