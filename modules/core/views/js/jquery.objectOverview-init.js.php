$(document).ready(function(){
    $("#<?=$overview_container_id;?>").objectOverview({
        use_hash: <?= ! isset($use_hash) || $use_hash ? 'true' : 'false';?>
    });
});