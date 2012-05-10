<div class="appformitemfile <?= $css ?>" name="<?= $attr;?>_item" id="<?= $uid;?>">

    <span class="label"><?=  $label;?></span>

    <?php if (!empty($error_message)):?>
        <span class="validation_error" style="color:red;"><?= $error_message; ?></span>
    <?php endif ?>

    <?php if ($table_header): ?>
        <table class="list">

            <?= $table_header;?>

            <?php foreach ($files as $file): ?>
            <?=(string)$file;?>
            <?php endforeach ?>
            
        </table>
        <div class="clearfix cb"></div>
    <?php else: ?>
        <div class="list">
            <?php foreach ($files as $file): ?>
            <?=(string)$file;?>
            <?php endforeach ?>
           <div class="clearfix cb"></div>
        </div>
        <div class="clearfix cb"></div>
    <?php endif ?>

    <div class="message_placeholder" style="display:none;">
        <div class="msg3"></div>
    </div>

    <div class="button">
        <noscript>
            <p><?= __('appformitemfile.enable_javascript_to_upload_files');?></p>
        </noscript>
    </div>

</div>