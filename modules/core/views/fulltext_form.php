<form method="GET" action="<?= appurl::fulltext_search();?>">
    <input type="text" name="<?= Controller_Fulltext::FULLTEXT_QUERY_KEY;?>" value="<?= isset($query) ? $query : '';?>"/>
    <button>Vyhledat</button>
</form>