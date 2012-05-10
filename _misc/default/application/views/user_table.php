<table>
    <thead>
        <tr>
            <th><?= __('user.th_active'); ?></th>
            <th><?= __('user.th_username'); ?></th>
            <th><?= __('user.th_roles'); ?></th>
            <th><?= __('user.th_last_login'); ?></th>
            <th><?= __('user.th_logins'); ?></th>
            <th><?= __('user.th_created'); ?></th>
            <th><?= __('user.th_action'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $i => $row): ?>
        <tr>
            <td><?= $row->_active;?></td>
            <td><?= $row->username; ?></td>
            <td><?= $row->_role; ?></td>
            <td><?= $row->_last_login; ?></td>
            <td><?= $row->logins; ?></td>
            <td><?= $row->_created; ?></td>
            <td>
                <a class="button" href="<?= appurl::object_edit('user', $row->pk(), $current_object_table_url);?>"><?= __('object.edit_action');?></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>