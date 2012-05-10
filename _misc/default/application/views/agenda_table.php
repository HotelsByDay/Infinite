<?php

$monday_time = $filter_params[':monday_time'];

$dates['monday']    = date('Y-m-d', $monday_time);
$dates['tuesday']   = date('Y-m-d', $monday_time + (3600 * 24 * 1));
$dates['wednesday'] = date('Y-m-d', $monday_time + (3600 * 24 * 2));
$dates['thursday']  = date('Y-m-d', $monday_time + (3600 * 24 * 3));
$dates['friday']    = date('Y-m-d', $monday_time + (3600 * 24 * 4));
$dates['saturday']  = date('Y-m-d', $monday_time + (3600 * 24 * 5));
$dates['sunday']    = date('Y-m-d', $monday_time + (3600 * 24 * 6));

$date_today = date('Y-m-d');
$week_today = date('W', $monday_time);

?>

<div class="dc1 clearfix">
<h1><?= __('agenda.week_heading', array(':week_number' => $week_today));?></h1>

<?php foreach ($dates as $day => $date): ?>
<div class="dc7 <?= $date_today == $date ? 'active' : '';?>">
    <h2><?= date('j.n', strtotime($date)) . ' ' . __('agenda.th_'.$day);?></h2>
    <div class="day">
        <a class="edit_ajax" href="<?= appurl::object_new_ajax('agenda', 'agenda_task_form',  array('datedue' => date('Y-m-d', strtotime($date)), 'cb_agenda_typeid' => '1'));?>">+úkol</a><br/>
        <a class="edit_ajax" href="<?= appurl::object_new_ajax('agenda', 'agenda_event_form', array('datedue' => date('Y-m-d', strtotime($date)), 'cb_agenda_typeid' => '2'));?>">+udalost</a>
    <ul>
        <?php while ( $results->valid() ) {
            //reference na aktualni zaznam
            $row = $results->current();
        ?>

            <?php
            if ($row->datedue != $date)
            {
                //konec vnitrni smycky - pristi smycka zacne na stejne pozici iteratoru
                break;
            }
            ?>
            <li>
                <?php if ($row->IsTask()): ?>
                    <input type="checkbox" class="task_finished" item_id="<?= $row->pk();?>" <?= $row->datedone != NULL ? 'checked="checked"' : '';?> />
                <?php endif ?>

                            
                <a class="edit_ajax" href="<?= appurl::object_edit_ajax('agenda', $row->IsTask() ? 'agenda_task_form' : 'agenda_event_form', $row->pk()) ;?>"><?= $row->name;?></a>
                <a class="action" href="#" action="delete" item_id="<?= $row->pk();?>">x</a>


            </li>

        <?php
                //posun na dalsi polozku iteratoru
                $results->next();
            }
        ?>
    </ul>
    </div>
</div>
<?php endforeach?>

</div>
<div class="cb"></div>