// <script>
$(document).ready(function(){
    $("#<?= $uid;?> textarea").redactor({
            path: '<?= url::base();?>redactor/',
            autoresize: false,
            resize: false,
            // See http://redactorjs.com/docs/toolbar/
            buttons: ['formatting', '|', 'bold', 'italic', '|',
            'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'link'],
            focus: false,
            callback: function() {
                // Find parent form
                var $form = $(this).parents(".<?= AppForm::FORM_CSS_CLASS ?>:first");

                // Fire a form event - the layout of the form has changed
                $form.objectForm('fireEvent', 'itemLayoutChanged', $(this));
            }
    });
});
