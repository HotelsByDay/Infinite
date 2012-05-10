<table class="comment_panel">
    <tbody>
        <?php foreach ($results as $i => $row):?>
        <tr>
<!--            <td class="image">
                <img src="/css/images/ico-person.gif" alt="Avatar" title="Avatar" />              
            </td>
-->
            <td class="content">
                <div>
                    <div class="headline">
                      <div class="headline_content">
                        <strong><?= $row->user->preview();?></strong> <small><?= $row->_created ?></small>
                        <?php if($row->userid == Auth::instance()->get_user()->pk()): ?>
                      </div>
                      <div class="cb"></div>
                      <?php endif ?>
                    </div>
                    <div class="text">
                      <p><?= $row->_text;?></p>
                      <div>
                          <?php foreach ($row->attachements->find_all() as $attachement): ?>
                          <div>     
                          <i>(<?= $attachement->getFileTypeIcon();?>)</i>&nbsp;<a href="<?= $attachement->getURL();?>" target="_blank"><?= $attachement->getFilename();?></a> (<?= $attachement->getFileSize();?>)
                      </div>
                      <?php endforeach ?>
                  </div>
                  <?php if($row->userid == Auth::instance()->get_user()->pk()): ?>
                  <a href="#" class="edit_ajax" itemid="<?= $row->pk();?>"><?= __('comment.edit');?></a>
                  <?php endif ?>
                  </div>

            </td>

        </tr>
        <?php endforeach ?>
    </tbody>
</table>
