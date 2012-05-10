

<table class="agenda_table">
    <thead>
        <tr>
        <th><?= __('agenda.th_type'); ?></th>
        <th><?= __('agenda.th_name'); ?></th>
        <th><?= __('agenda.th_note'); ?></th>
        <th><?= __('agenda.th_info'); ?></th>
        <th><?= __('advert.th_action') ?></th>
        </tr>
    </thead>
    <tbody>

        <?php

            //zde se bude ukladat datum aktualniho ukolu - pomoci toho budu detekovat
            //zmenu a tedy iteraci kdy se ma generovat novy popisek pro den
            $current_date = NULL;

            //zde se bude ukladat stav 'finished' - pouziju pro detekci zmeny stavu
            //'finished'
            $current_overdue = NULL;

            //unixtime v 00:00 tohoto dne - pouziju pro kontrolu ze zaznam
            //nepatrik ke vcerejsimu dni
            $today_zero = strtotime(date('Y-m-d'));



        ?>

        <?php foreach ($results as $i => $row): ?>

            <?php if ($current_date != $row->datedue || $current_overdue != $row->_overdue):

                $current_date     = $row->datedue;
                $current_overdue  = $row->_overdue;
                $current_unixtime = strtotime($current_date);

                //priznaky pro zobrazeni nadpisu tak jak chci
                $is_near             = ($current_unixtime >= $today_zero) && ($current_unixtime <= time() + (3600 * 24 * 1));
                $is_closer_than_week = ! $is_near && ($current_unixtime <= time() + (3600*24*5));


                $is_overdue = FALSE;

                if ($is_near && $row->_overdue)
                {
                    $is_overdue = TRUE;
                }

            ?>
            <tr>
                <th colspan="6">
                <?php
                    if ($is_overdue)
                    {
                        echo __('agenda.section_label_overdue');
                    }
                    else if ($is_near)
                    {
                        echo __('agenda.section_label_with_day_spec', array(
                            ':date'    => date('j.n.Y', $current_unixtime),
                            ':day_name' => __('agenda.day_name_'.date('N', $current_unixtime)),
                            ':day_spec' => __('agenda.day_spec_'.(int)(date('N', $current_unixtime) - date('N')))
                        ));
                    }
                    else if ($is_closer_than_week)
                    {
                        echo __('agenda.section_label_with_day_name', array(
                            ':date'    => date('j.n.Y', $current_unixtime),
                            ':day_name' => __('agenda.day_name_'.date('N', $current_unixtime))
                        ));
                    }
                    else
                    {
                        echo __('agenda.section_label_with_date', array(
                            ':date'    => date('j.n.Y', $current_unixtime),
                        ));
                    }
                ?>
                </th>
            </tr>
            <?php endif ?>

            <tr class="<?= $row->_overdue ? 'overdue' : '';?> <?= $row->datedone != NULL ? 'finished' : '';?>">
                <td>
                    <?= $row->cb_agenda_type->value; ?>
                    <?php if ($row->IsTask()): ?>
                    <input type="checkbox" class="task_finished" item_id="<?= $row->pk();?>" <?= $row->datedone != NULL ? 'checked="checked"' : '';?> />
                    <?php endif ?>
                </td>
                <td><?= $row->name;?></td>
                <td><?= $row->note;?></td>
                <td><?= $row->_info;?></td>
                <td>
                    <a class="edit_ajax" href="<?= appurl::object_edit_ajax('agenda', $row->IsTask() ? 'agenda_task_form' : 'agenda_event_form', $row->pk()) ;?>"><?= __('object.edit_action');?></a>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>

</table>
