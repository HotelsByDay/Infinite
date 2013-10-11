<div class="appformitemgps" id="<?= $uid; ?>">

    <?php if ( ! empty($error_message)): ?>
        <div class="row">
            <div class="span6">
                <span class="validation_error alert alert-error"><?= $error_message; ?></span>
            </div>
        </div>
    <?php endif ?>


            <div class="">
<!--                <div style="padding: 10px; background-color: #FE7569;">-->
                    <div class="pull-left">
                        <span><?= $lat_label ?></span><br />
                        <input style="width: 180px" type="text" name="<?= $attr ?>[latitude]" value="<?= $value['latitude'] ?>" />
                    </div>

                    <div class="pull-left" style="margin-left: 10px;">
                        <span><?= $lon_label ?></span><br />
                        <input style="width: 180px" type="text" name="<?= $attr ?>[longitude]" value="<?= $value['longitude'] ?>" />
                    </div>
<!--                </div>-->
            </div>

        <div class="row">
            <div class="span6">
                <div class="map">
                    <div class="canvas" style="width:600px; height:400px;"></div>
                </div>
                <a href="#" style="display: none;" class="show_original_position"><?= __('appformitemgps.show_original_position'); ?></a>
            </div>

        </div><!-- row -->


    <div class="clear"></div>
</div>