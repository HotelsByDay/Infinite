$(document).ready(function() {
    tinymce.init({
        selector: '#<?= $uid;?> textarea',
        plugins: 'autolink link table anchor lists checklist',
        toolbar_mode: 'floating',
        toolbar: 'undo redo bold italic underline strikethrough forecolor backcolor fontsize lineheight | alignleft aligncenter alignright alignjustify blocks numlist bullist checklist link table',
        menubar: '',
    });
});
