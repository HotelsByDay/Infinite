function hbdTinymceInit(selector) {
    tinymce.init({
        selector: selector,
        plugins: 'autolink link table anchor lists',
        toolbar_mode: 'floating',
        toolbar: 'undo redo bold italic underline strikethrough forecolor backcolor fontsize lineheight | alignleft aligncenter alignright alignjustify blocks numlist bullist link table',
        menubar: '',
        font_size_formats: '8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 21pt 22pt 23pt 24pt 25pt 26pt 27pt 28pt 29pt 30pt 31pt 32pt 33pt 34pt 35pt 36pt'
    });
}

$(document).ready(function() {
    hbdTinymceInit('#<?= $uid;?> textarea');
});
