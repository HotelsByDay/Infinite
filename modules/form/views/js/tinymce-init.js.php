$(document).ready(function() {
    tinymce.init({
        selector: '#<?= $uid;?> textarea',
        plugins: 'print preview powerpaste casechange importcss tinydrive searchreplace autolink autosave save directionality advcode visualblocks visualchars fullscreen image link media mediaembed template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists checklist wordcount tinymcespellchecker a11ychecker imagetools textpattern noneditable help formatpainter permanentpen pageembed charmap tinycomments mentions quickbars linkchecker emoticons advtable export',
        toolbar_mode: 'floating',
        toolbar: 'undo redo bold italic underline strikethrough forecolor backcolor fontsize lineheight | alignleft aligncenter alignright alignjustify blocks numlist bullist checklist link table',
        menubar: '',
    });
});
