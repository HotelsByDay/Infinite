<?php $type_key = $file->IsTempFile() ? 'n' : 'l'; ?>

<div class="file_item item">

    <a href="<?= appurl::object_file($file); ?>" class="fancybox" title="<?= $file->IsTempFile() ? '' : ($file->title); ?>">
        <img src="<?= appurl::object_file($file, 'thumbnail'); ?>" style="max-width: 200px; max-height: 200px;" alt="<?= $file->nicename; ?>" title="<?= $file->nicename; ?>"/>
    </a>
    <br />
    <span class="cancel btn btn-danger btn-mini"><?= __('form_action_button.delete_label'); ?></span>

    <input type="hidden" name="<?= $attr; ?>[<?= $type_key; ?>][id][]" value="<?= $file->pk(); ?>"/>
</div>

