$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemAdvertAddress({
        autocomplete_url: "<?= $autocomplete_url;?>",
        placedetail_url: "<?= $placedetail_url;?>",
    });
});
