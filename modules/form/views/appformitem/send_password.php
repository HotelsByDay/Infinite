
<div class="appformitemcontainer appformitemsendpassword <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">

    <label><?= $label ?></label>
    <?php if ($allow_reset): ?>
        <div class="clearfix"></div>
        <a href="javascript: ;" class="btn btn-info reset_pass">Reset & Send</a>
    <?php else: ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="<?= $attr ?>[generate_pass]" value="1" checked="checked" />
                <?= __('afi_sendpassword.generate_and_send_password.label') ?>
            </label>
        </div>
    <?php endif; ?>

</div>