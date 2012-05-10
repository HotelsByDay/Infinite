<div id="lang_panel">
    <strong><?= arr::get($lang_list, $active_lang_code, 'N/A');?></strong>
    <ul id="lang_panel_list">
        <?php foreach ((array)$lang_list as $lang_code => $name):

            //aktivni jazyk nebudu nabyzet na vyber
            if ($lang_code == $active_lang_code)
            {
                continue;
            }

        ?>
        <li><a href="#" name="<?= $lang_code;?>"><?= $name;?></a></li>
        <?php endforeach ?>
    </ul>
</div>