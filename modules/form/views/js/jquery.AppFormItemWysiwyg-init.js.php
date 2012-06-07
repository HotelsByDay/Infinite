$(document).ready(function(){
    $("#<?= $uid;?> textarea").redactor({
        path: '<?= url::base();?>redactor/',
        autoresize: true,
        focus: false
    });
});
