
<div class="appformitemcontainer appformitemsendpassword <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">

    <label><?= $label ?></label>
    <?php if ($allow_reset): ?>
        <a href="javascript: ;" class="btn btn-info reset_pass">Reset & Send</a>
    <?php else: ?>
        <label class="checkbox">
            <input type="checkbox" name="<?= $attr ?>[generate_pass]" value="1" checked="checked" />
            <?= __('afi_sendpassword.generate_and_send_password.label') ?>
        </label>
    <?php endif; ?>

</div>