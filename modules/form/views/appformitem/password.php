<div class="appformitemcontainer appformpassword <?= $css ?> control-group <?= empty($error_message) ? '' : 'error' ?>" id="<?= $uid; ?>">

            <label for="<?= $attr; ?>-password"><?= $label;?></label>

            <input <?php if (isset($placeholder) and ! empty($placeholder)) echo "placeholder=\"$placeholder\""; ?> id="<?= $attr; ?>-password" autocomplete="off"  type="password" name="<?= $attr; ?>[password]"/>

            <label for="<?= $attr; ?>-password_confirm"><?= $label_confirm;?></label>

            <input <?php if (isset($placeholder_confirm) and ! empty($placeholder_confirm)) echo "placeholder=\"$placeholder_confirm\""; ?> id="<?= $attr; ?>-password_confirm" autocomplete="off" type="password" name="<?= $attr; ?>[password_confirm]"/>

            <div class="passwords_dont_match alert alert-error" style="display:none;">
            <?= __('appformitempassword.passwords_dont_match_message'); ?>
            </div>

            <div class="password_strength_info alert alert-info " style="display:none;">
                <span class="message"></span>
            </div>


    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>


</div>