<div class="panel <?= isset($css) ? $css : '';?>" <?= isset($id) ? 'id="'.$id.'"' : '';?>>
    <h2><?= $header;?></h2>
    <?php if (empty($items) || ( count($items) == 0) ): ?>
      <span class="help_text">
        <?php echo __('general.saved_filters_empty'); ?>
      </span>
    <?php else: ?>
      <ul>
      <?php foreach ($items as $item): ?>
          <li><?= $item;?></li>
      <?php endforeach ?>
      </ul>
    <?php endif; ?>
</div>