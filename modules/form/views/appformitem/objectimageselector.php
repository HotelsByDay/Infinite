
<div  class="appformitemcontainer appformitemobjectimageselector <?= $css; ?>" name="<?= $attr ?>_item" id="<?= $uid; ?>">
  <?php if (!empty($error_message)): ?>
    <span class="validation_error"><?= $error_message; ?></span>
  <?php endif ?>

    <input type="hidden" name="<?= $attr ?>[images_list]" value="" />
    <input type="hidden" name="<?= $attr ?>[id]" value="<?= $value ?>" />

    <style type="text/css">

        .image_preview {
            float: left;
            margin: 5px;
            padding: 4px;
            height: 110px;
        }
        .image_preview img, .image_preview span.select {
            cursor: pointer;
        }
        .image_preview.selected {
            border: 1px solid #009900;
            /* pridali jsme 1px border, musime odebrat 1px paddingu aby vyska zustala stejna */
            padding: 3px;
            background-color: #d2f1cb;
        }
        .images_preview_outer {
            border: 1px solid #0033CC;
            background-color: #eee;
            float: left;
            padding: 5px;
            margin: 3px;
        }
        .no_images {
            padding: 10px;
        }
    </style>

    <div><label for="<?= $attr; ?>-password"><?= $label ?></label><br /></div>

    <div class="images_preview_outer">

    <div class="no_images" style="display: none">
        <?= __($attr.'.objectimageselector.no_images_found') ?>
    </div>

    <div class="images_preview">
        <?php foreach ($images as $image): ?>
            <div class="image_preview" image_id="<?= $image['id'] ?>">
                <div>
                    <a href="<?= $image['zoomed_url'] ?>" class="zoom" title="<?= $image['preview']; ?>"><img src="<?= $image['url'] ?>" /></a>
                </div>
                <span class="preview"><?= $image['preview'] ?></span>
                <br />
                <span class="select"><a href="javascript: ;"><?= __('objectimageselector.select_image') ?></a></span>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
    <div class="clear"></div>


    <div class="image_preview_template" style="display: none;">
        <div>
            <a href="" class="zoom" title=""><img src="" /></a>
        </div>
        <span class="preview"></span>
        <br />
        <span class="select"><a href="javascript: ;"><?= __('objectimageselector.select_image') ?></a></span>
    </div>


  <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
  <?php endif ?>

</div>