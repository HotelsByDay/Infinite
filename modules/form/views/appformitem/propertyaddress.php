<div class="appformitemadvertaddress advertaddress" id="<?= $uid; ?>">

    <?php if ( ! empty($error_message)): ?>
    <span class="validation_error alert alert-error"><?= $error_message;?></span>
    <?php endif ?>

    <!-- <span class="hint"><?= __('appformitemadvertaddress.filltext_hint'); ?></span> -->


        <div class="c50l">
            <div class="item-element property_locationid">
                <label for="<?= $uid ?>-city"><?= __('appformitempropertyaddress.property_locationid') ?></label>

                <input type="text" name="<?= $attr ?>[name]" value="<?= $value['name'] ?>" <?php if ($readonly) echo 'readonly="readonly"'; ?> />
                <input type="hidden" name="<?= $attr ?>[value]" value="<?= $value['value'] ?>" />
                <input type="hidden" name="<?= $attr ?>[property_location_indexed_csc]" value="<?= $value['property_location_indexed_csc'] ?>" />
                <div class="clear"></div>
            </div>

            <div class="item-element">
                <label for="<?= $uid ?>-postal_code"><?= __('appformitempropertyaddress.postal_code') ?></label>
                <input type="text" name="<?= $attr ?>[postal_code]" value="<?= $value['postal_code'] ?>" <?php if ($readonly) echo 'readonly="readonly"'; ?> />
                <div class="clear"></div>
            </div>

            <div class="item-element">
            <label for="<?= $uid ?>-address"><?= __('appformitempropertyaddress.address') ?></label>
            <input type="text" name="<?= $attr ?>[address]" value="<?= $value['address'] ?>" />
            <div class="clear"></div>
            </div>


            <div class="item-element">
            <label for="<?= $uid ?>-latitude"><?= __('appformitempropertyaddress.latitude') ?></label>
            <input type="text" name="<?= $attr ?>[latitude]" value="<?= $value['latitude'] ?>" readonly="readonly" />
            <div class="clear"></div>
            </div>

            <div class="item-element">
            <label for="<?= $uid ?>-longitude"><?= __('appformitempropertyaddress.longitude') ?></label>
            <input type="text" name="<?= $attr ?>[longitude]" value="<?= $value['longitude'] ?>" readonly="readonly" />
            <div class="clear"></div>
            </div>

            <div class="c50l">
                <div class="map">
                    <div class="canvas" style="width:400px; height:400px;"></div>
                </div>
                <a href="#" style="display: none;" class="show_original_position"><?= __('appformitemadvertaddress.show_original_position'); ?></a>

            </div>
        </div>


    <div class="clear"></div>
</div>