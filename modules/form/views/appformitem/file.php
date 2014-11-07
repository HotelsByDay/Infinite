<div class="appformitemcontainer appformitemfile<?= $css ?> form-group <?= empty($error_message) ? '' : 'has-error' ?>" name="<?= $attr;?>_item" id="<?= $uid;?>">

    <span class=""><?=  $label;?></span>

    <style type="text/css">
            /* pro otestovani funkcnosti jquery pluginy */
        th.warning select {
            border: 1px solid red;
            background-color: pink;
        }
    </style>

    <?= $tooltip; ?>

    <?php if ( ! empty($error_message)): ?>
        <span class="validation_error text-error"><?= $error_message; ?></span>
    <?php endif ?>

    <?php if ($table_header): ?>
        <table class="list table">

            <?= $table_header; ?>

            <tbody>
            <?php foreach ($files as $file): ?>
            <?=(string)$file;?>
            <?php endforeach ?>
            </tbody>
        </table>
        <div class="clearfix cb"></div>
    <?php else: ?>
        <ul class="list unstyled">
            <?php foreach ($files as $file): ?>
            <li class="list_item">
            <?=(string)$file;?>
            <li class="list_item">
            <?php endforeach ?>
        </ul>
        <div class="clearfix cb"></div>
    <?php endif ?>

    <div class="message_placeholder" style="display:none;">
        <div class="validation_error"></div>
    </div>

    <div class="button">
        <noscript>
            <p><?= __('appformitemfile.enable_javascript_to_upload_files');?></p>
        </noscript>
    </div>

    <?php if (isset($hint) && !empty($hint)): ?>
    <span class="hint"><?= $hint; ?></span>
    <?php endif ?>

    <?php if (isset($lang_view)): ?>

        <div class="clone_lang_view" style="display: none;">
            <?= $lang_view ?>
        </div>

    <?php endif; ?>


</div>