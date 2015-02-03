<?php
    $config['use_hash'] = ! isset($use_hash) || $use_hash ? 'true' : 'false';
?>

$(document).ready(function(){
    $("#<?=$overview_container_id;?>").objectOverview(<?= json_encode($config) ?>);
});