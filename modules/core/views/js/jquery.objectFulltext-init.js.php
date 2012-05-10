$(document).ready(function(){
    $("#fulltext_results").globalFulltext({params: <?= json_encode($default_params);?>});
});