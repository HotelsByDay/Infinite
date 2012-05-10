//<script>
$(document).ready(function(){
    $("#<?= $uid;?>").appFormItemSimpleItemList(<?= json_encode($params);?>);
});