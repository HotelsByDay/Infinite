<div class="appformitemgps" id="<?= $uid; ?>">

    <?php if ( ! empty($error_message)): ?>
        <div class="row">
            <div class="span6">
                <span class="validation_error alert alert-error"><?= $error_message; ?></span>
            </div>
        </div>
    <?php endif ?>

    <div class="map">
        <div class="map-canvas" style="width: <?= $width ?>px; height: <?= $height ?>px;"></div>
    </div>
    <a href="#" style="display: none;" class="show_original_position"><?= __('appformitemgps.show_original_position'); ?></a>

    <div style="display: <?= $inputs_hidden ? 'none' : 'block' ?>;">
        <div class="pull-left">
            <span><?= $lat_label ?></span><br />
            <input <?= $inputs_readonly ? 'readonly="readonly"' : '' ?> style="width: 180px" type="text" name="<?= $attr ?>[latitude]" value="<?= $value['latitude'] ?>" />
        </div>

        <div class="pull-left" style="margin-left: 10px;">
            <span><?= $lon_label ?></span><br />
            <input <?= $inputs_readonly ? 'readonly="readonly"' : '' ?> style="width: 180px" type="text" name="<?= $attr ?>[longitude]" value="<?= $value['longitude'] ?>" />
        </div>
    </div>

    <div class="clearfix"></div>
</div>

