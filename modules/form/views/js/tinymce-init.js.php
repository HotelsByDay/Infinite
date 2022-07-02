$(document).ready(function() {
    tinymce.init({
        selector: '#<?= $uid;?> textarea',
        plugins: 'autolink link table anchor lists checklist',
        toolbar_mode: 'floating',
        toolbar: 'undo redo bold italic underline strikethrough forecolor backcolor fontsize | alignleft aligncenter alignright alignjustify numlist bullist checklist link table',
        menubar: '',
    });
});
