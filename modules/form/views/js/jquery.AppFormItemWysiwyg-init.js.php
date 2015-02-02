// <script>
$(document).ready(function() {
    var config = <?= json_encode($config) ?>;
    var settings = {
        path: '<?= url::base();?>redactor/',
        autoresize: true,
        resize: false,
        // See http://redactorjs.com/docs/toolbar/
        buttons: config.buttons,
        focus: false,
        callback: function() {
            // Find parent form
            var $form = $(this).parents(".<?= AppForm::FORM_CSS_CLASS ?>:first");

            // Fire a form event - the layout of the form has changed
            $form.objectForm('fireEvent', 'itemLayoutChanged', $(this));
        }
    };

    if (config.images_upload) {
        settings.imageUpload = config.images_upload;
        settings.buttons[settings.buttons.length] = 'image';
    }

    if (typeof config.formatting_tags != 'undefined') {
        settings.formattingTags = config.formatting_tags;
    }

    $("#<?= $uid;?> textarea").redactor(settings);
});
