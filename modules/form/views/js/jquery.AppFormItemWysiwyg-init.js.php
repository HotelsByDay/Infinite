// <script>
$(document).ready(function(){
    var config = <?= json_encode($config) ?>;
    var settings = {
        path: '<?= url::base();?>redactor/',
        autoresize: true,
        resize: false,
        // See http://redactorjs.com/docs/toolbar/
        buttons: ['formatting', '|', 'bold', 'italic', '|','fontcolor','|',
            'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'link'],
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
        settings.buttons[redactor_settings.buttons.length] = 'image';
    }

    //options to align text
    settings.buttons.push('|');
    settings.buttons.push('alignleft');
    settings.buttons.push('aligncenter');
    settings.buttons.push('alignright');

    $("#<?= $uid;?> textarea").redactor(settings);
});
