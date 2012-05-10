<div class="item">

    <i>(<?= $file->getFileType();?>)</i>&nbsp;
    <a href="<?= $file->getURL();?>"><?= $file->nicename;?></a>&nbsp;(<?= $file->getFileSize();?>)

    &nbsp;<a href="#" class="cancel"><?= __('appformitemfile.remove_file');?></a>

    <?php $type_key = $file->IsTempFile() ? 'n' : 'l';?>
    <input type="hidden" name="<?=$attr;?>[<?= $type_key;?>][id][]" value="<?= $file->pk();?>"/>
</div>