<div class="appformpassword <?= $css ?>" id="<?= $uid; ?>">

    <?php if (!empty($error_message)): ?>
        <span class="validation_error"><?= $error_message; ?></span>
    <?php endif ?>

        <table>
            <tr>
                <td>
                    <label for="<?= $attr; ?>-password"><?= $label;?></label>
                </td>
                <td>
                    <input id="<?= $attr; ?>-password" autocomplete="off" type="password" name="<?= $attr; ?>[password]"/>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?= $attr; ?>-password_confirm"><?= $label_confirm;?></label>
                </td>
                <td>
                    <input id="<?= $attr; ?>-password_confirm" autocomplete="off" type="password" name="<?= $attr; ?>[password_confirm]"/>
                </td>
            </tr>
            <tr>
                <td>
                </td>
                <td>
                    <div class="passwords_dont_match" style="display:none;">
                    <?= __('appformitempassword.passwords_dont_match_message'); ?>
                </div>

                <div class="password_strength_info" style="display:none;">
                    <span class="message"></span>
                </div>
            </td>
        </tr>
    </table>

</div>