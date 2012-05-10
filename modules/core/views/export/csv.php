<?php
$i = 0;
foreach ($metadata as $column)
{
    if ($i++ != 0)
    {
        echo ';';
    }
    echo @iconv('UTF-8', $target_encoding, arr::get($column, 'label', $column));
}
echo "\n";
?>
<?php
foreach ($results as $result)
{
    $i = 0;
    foreach ($metadata as $column)
    {
        if ($i++ != 0)
        {
            echo ';';
        }

        $attr = $column['attr'];

        $value = is_string($attr)
                    ? $result->{$attr}
                    : call_user_func($attr, $result);

        echo @iconv('UTF-8', $target_encoding, $value);
    }
    echo "\n";
}
?>
