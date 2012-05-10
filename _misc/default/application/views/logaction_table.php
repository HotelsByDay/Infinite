


<table>
    <thead>
        <tr>
        <th>#</th>
        <th><?= call_user_func_array($generate_table_order_col_element, array(__('logaction.th_created'), 'created')); ?></th>
        <th><?= call_user_func_array($generate_table_order_col_element, array(__('logaction.th_text'), 'text'));?></th>
        <th><?= __('logaction.th_reference') ?></th>
        <th><?= __('logaction.th_userid') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $i => $row): ?>
            <tr>
                <td><?= $row->pk();?></td>
                <td><?= $row->_created; ?></td>
                <td><?= $row->text;?></td>
                <td><a href="<?= appurl::object_overview(lognumber::getTableName($row->relAtype), $row->relAid);?>"><?= ORM::factory(lognumber::getTableName($row->relAtype), $row->relAid)->preview();?></a></td>
                <td><?= $row->user->_name;?></td>
            </tr>
        <?php endforeach ?>
    </tbody>

</table>
