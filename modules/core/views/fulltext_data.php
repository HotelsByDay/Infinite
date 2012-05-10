<div class="hd">
    <ul class="searchNav">
        
        <li class="<?= $active_object_name == NULL ? 'active' : '';?>">
            <?php if ($active_object_name != NULL):?>
            <a class="object_button" href="<?= $all_url;?>">
            <?php endif ?>
                
            Vše

            <?php if ($active_object_name != NULL):?>
            </a>
            <?php endif ?>
        </li>

        <?php foreach ($search_results as $object_name => $data): ?>
            <?php if ($data['total'] > 0): ?>
            <li class="<?= $active_object_name == $object_name ? 'active' : '';?>">
                
                <?php if ($active_object_name  != $object_name): ?>
                <a class="object_button" href="#" parameters="<?= $data['parameters'];?>">
                <?php endif ?>
                
                <?= $object_name;?> (<?= $data['total'];?>)

                <?php if ($active_object_name  != $object_name): ?>
                </a>
                <?php endif ?>
                
            <?php endif ?>
        <?php endforeach ?>
                
    </ul>
</div>

<div class="data">
    <div class="dc1">
    <?php foreach ($search_results as $object_name => $data): ?>
        <?php if (count($data['data']) == 0)
              {
                continue;
              }
        ?>
        <?php if ($active_object_name == NULL): ?>
        <strong><?= $object_name;?></strong><br/>
        <?php endif ?>
        
        <?php foreach ($data['data'] as $record): ?>
        <p><a href="<?= call_user_func($data['url_generator'], $record);?>"><?= $record->preview();?></a></p>
        <?php endforeach ?>
    <?php endforeach ?>
    </div>
</div>







