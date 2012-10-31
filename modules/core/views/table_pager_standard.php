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


<div class="paginator fr pagination">
    <ul>
    <li>
      <?php if ($current_page_index != 0): ?>
              <a href="#" class="pager_button" <?= $prev_page_index !== FALSE ? 'pi="' . $prev_page_index . '"' : ''; ?>>
        <?php endif ?>
        <?= '&lt;';//__('general.pager_goto_previous_page'); ?>
        <?php if ($current_page_index != 0): ?>
        </a>
      <?php endif ?>
        </li>


    <?php
          foreach ($page_item_list as $i => $data):

            list($pi, $label) = $data
    ?>
    <li class="<?= ($pi === FALSE && is_numeric($label) ) ? "active" : ""; ?>">
      <?php if ($pi !== FALSE): ?>
              <a href="#" class="pager_button pager_button" pi="<?= $pi; ?>"><?= $label; ?></a>
      <?php else : ?>
      <?php if (is_numeric($label)): ?>
                  <?= $label; ?>
      <?php else: ?>
                    <?= $label; ?>
      <?php endif; ?>

      <?php endif ?>
    </li>
    <?php endforeach ?>


    <li>
      <?php if ($current_page_index < $total_page_count - 1): ?>
            <a href="#" <?= $next_page_index !== FALSE ? 'pi="' . $next_page_index . '"' : ''; ?> class="pager_button">
        <?php endif ?>
        <?= '&gt;';//__('general.pager_goto_next_page'); ?>
        <?php if ($current_page_index < $total_page_count - 1): ?>
        </a>
      <?php endif ?>
    </li>
  </ul>

</div>
