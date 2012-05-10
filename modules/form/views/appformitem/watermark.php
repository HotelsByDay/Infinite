<?php if ( ! empty($error_message)): ?>
<span class="validation_error"><?= $error_message;?></span>
<?php endif ?>


<div  class="appformitemcontainer appformitemwatermark<?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid;?>">

    <div class="photo" style="position: relative; float: left; border: 1px solid #aaa; overflow: hidden; width:300px;height:200px;">


            <label for="appformitemwatermark_pos1">1</label>
            <input id="appformitemwatermark_pos1" type="radio" name="test" value="1"/>

            <label for="appformitemwatermark_pos2">2</label>
            <input id="appformitemwatermark_pos2" type="radio" name="test" value="2"/>

            <label for="appformitemwatermark_pos3">3</label>
            <input id="appformitemwatermark_pos3" type="radio" name="test" value="3"/>

            <label for="appformitemwatermark_pos4">4</label>
            <input id="appformitemwatermark_pos4" type="radio" name="test" value="4"/>

            <label for="appformitemwatermark_pos5">5</label>
            <input id="appformitemwatermark_pos5" type="radio" name="test" value="5"/>

            <label for="appformitemwatermark_pos6">6</label>
            <input id="appformitemwatermark_pos6" type="radio" name="test" value="6"/>

            <label for="appformitemwatermark_pos7">7</label>
            <input id="appformitemwatermark_pos7" type="radio" name="test" value="7"/>


        <img src="<?= $init_watermark_image; ?>"
             style="opacity: <?= $value['opacity'];?>;
                    position: relative;
                    <?php if ( ! empty($value['width'])): ?>
                    width: <?= $value['width'] ?>%;
                    <?php endif ?>
                    cursor: pointer;"
            class="watermark_image" />

    </div>

<input type="text" name="<?= $attr; ?>[width]"  value="<?= $value['width'] ?>"  readonly="readonly" />

<?= form::select($attr.'[opacity]', $opacity_levels, $value['opacity']); ?>

</div>