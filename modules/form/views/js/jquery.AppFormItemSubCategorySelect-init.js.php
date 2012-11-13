
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemSubCategorySelect(<?= json_encode($config) ?>);
});
