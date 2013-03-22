<div class="appformitemadvertaddress advertaddress" id="<?= $uid; ?>">

    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error alert alert-error"><?= $error_message; ?></span>
    <?php endif ?>

    <!-- <span class="hint"><?= __('appformitemadvertaddress.filltext_hint'); ?></span> -->


        <div class="row">

            <div class="span3">
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
            </div>

            <div class="span3">
                <div style="padding: 10px; background-color: #FE7569;">
                    <label style="font-weight: bold;">Current GPS:</label>
                    <div class="item-element">
                        <label for="<?= $uid ?>-latitude"><?= __('appformitempropertyaddress.latitude') ?></label>
                        <input style="width: 180px" type="text" name="<?= $attr ?>[latitude]" value="<?= $value['latitude'] ?>" readonly="readonly" />
                        <div class="clear"></div>
                    </div>

                    <div class="item-element">
                        <label for="<?= $uid ?>-longitude"><?= __('appformitempropertyaddress.longitude') ?></label>
                        <input style="width: 180px" type="text" name="<?= $attr ?>[longitude]" value="<?= $value['longitude'] ?>" readonly="readonly" />
                        <input type="hidden" name="<?= $attr ?>[gps_ok]" value="0" />
                        <label class="checkbox" style="font-weight: bold;">
                            <input type="hidden" name="<?= $attr ?>[gps_ok]" value="<?= $value['gps_ok'] ?>" />
                            <input type="checkbox" name="<?= $attr ?>[gps_ok]" value="1" <?= ($value['gps_ok'] ? 'checked' : '') ?> />GPS OK
                        </label>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>

            <div class="span3">
                <div style="padding: 10px; background-color: #ffbc8a;">
                    <label style="font-weight: bold;">ARN GPS:</label>
                    <div class="item-element">
                        <label for="<?= $uid ?>-latitude"><?= __('appformitempropertyaddress.latitude') ?></label>
                        <input style="width: 180px" type="text" name="<?= $attr ?>[arn_latitude]" value="<?= $value['arn_latitude'] ?>" readonly="readonly" />
                        <div class="clear"></div>
                    </div>

                    <div class="item-element">
                        <label for="<?= $uid ?>-longitude"><?= __('appformitempropertyaddress.longitude') ?></label>
                        <input style="width: 180px" type="text" name="<?= $attr ?>[arn_longitude]" value="<?= $value['arn_longitude'] ?>" readonly="readonly" />
                        <a href="javascript: ;" class="btn use_arn">Use ARN GPS</a>
                        <div class="clear"></div>
                    </div>
                </div>

            </div>

            <div class="span3">
                <div style="padding: 10px; background-color: #8CA1F7;">
                    <label style="font-weight: bold;">Google GPS:</label>
                    <div class="item-element">
                        <label for="<?= $uid ?>-glatitude"><?= __('appformitempropertyaddress.latitude') ?></label>
                        <input style="width: 180px" type="text" name="<?= $attr ?>[google_latitude]" value="<?= $value['google_latitude'] ?>" readonly="readonly" />
                        <div class="clear"></div>
                    </div>

                    <div class="item-element">
                        <label for="<?= $uid ?>-glongitude"><?= __('appformitempropertyaddress.longitude') ?></label>
                        <input style="width: 180px" type="text" name="<?= $attr ?>[google_longitude]" value="<?= $value['google_longitude'] ?>" readonly="readonly" />
                        <a href="javascript: ;" class="btn use_google">Use Google GPS</a>
                        <div class="clear"></div>
                    </div>
                </div>
            </div><!-- span3 -->
            </div><!-- row -->
        <div class="row">
            <div class="span6">
                <div class="map">
                    <div class="canvas" style="width:970px; height:600px;"></div>
                </div>
                <a href="#" style="display: none;" class="show_original_position"><?= __('appformitemadvertaddress.show_original_position'); ?></a>
            </div>

        </div><!-- row -->


    <div class="clear"></div>
</div>