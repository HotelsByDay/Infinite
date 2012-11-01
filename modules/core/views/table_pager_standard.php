<?php if (isset($page_size_list) && FALSE): ?>
  <div class="per-page">Výsledků na stránce:
  <?php if (count($page_size_list) > 1): ?>
    <select class="page_size_select">
    <?php foreach ($page_size_list as $value => $label): ?>
      <option value="<?= $value; ?>" <?= $current_page_size == $value ? 'selected="selected"' : ''; ?>><?= $label; ?></option>
    <?php endforeach; ?>
    </select>
  <?php endif ?>
    </div>
<?php endif ?>


<div class="paginator fr">
    <ul>
    <li <?php if ($current_page_index == 0): ?> class="disabled" <?php endif; ?>>
            <a href="#" class="pager_button" <?= $prev_page_index !== FALSE ? 'pi="' . $prev_page_index . '"' : ''; ?>>
            <?= '&lt;';//__('general.pager_goto_previous_page'); ?>
            </a>
    </li>


    <?php
          foreach ($page_item_list as $i => $data):

            list($pi, $label) = $data
    ?>
    <li class="<?= ($pi === FALSE && is_numeric($label) ) ? "active" : ""; ?><?php if ($pi === FALSE): ?> disabled<?php endif; ?> ">
              <a href="#" class="pager_button pager_button" pi="<?= $pi; ?>"><?= $label; ?></a>
    </li>
    <?php endforeach ?>


    <li <?php if ($current_page_index >= $total_page_count - 1): ?>class="disabled" <?php endif; ?>>
            <a href="#" <?= $next_page_index !== FALSE ? 'pi="' . $next_page_index . '"' : ''; ?> class="pager_button">
        <?= '&gt;';//__('general.pager_goto_next_page'); ?>
        </a>
    </li>
  </ul>

</div>
