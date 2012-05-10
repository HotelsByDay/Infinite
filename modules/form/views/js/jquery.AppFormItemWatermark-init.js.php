
$(document).ready(function(){
    $("#<?= $uid;?>").AppFormItemWatermark({
        'x': <?= $value['pos']; ?>,
        'width': <?= $value['width']; ?>,
        'opacity': <?= $value['opacity']; ?>,
    });
});



    
    