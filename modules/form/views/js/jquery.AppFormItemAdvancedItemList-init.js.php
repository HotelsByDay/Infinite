$(document).ready(function(){
    $("#<?= $uid;?>").appFormItemAdvancedItemList(<?= json_encode($params);?>);
});