<table>
    <thead>
        <tr>
        <th><?= __('serie.th_name');?></th>
        <th><?= __('serie.th_format') ?></th>
        <th><?= __('serie.th_next_value') ?></th>
        <th><?= __('serie.th_action') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $i => $row): ?>
            <tr>
                <td><?= $row->name; ?></td>
                <td><?= $row->format; ?></td>
                <td><?= $row->next_value; ?></td>
                <td><a class="button" href="<?= appurl::object_edit('serie', $row->pk());?>"><?= __('object.edit_action');?></a></td>
            </tr>
        <?php endforeach ?>
    </tbody>

</table>
