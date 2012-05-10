<div id="record_comment_overview">
<a href="#" class="add button red" onclick="$(this).hide().parent().find('.form_container').show().find('textarea:first').focus();return false;"><?= __('comment.add_new'); ?></a>
<div class="form_container" style="display:none;">
    <div class="form_content">
        <?= $form; ?>
    </div>
</div>

<div class="list">
    <?=
        View::factory('object_data_panel', array(
                    'label' => __('comment.comment_list'),
                    'conf' => array(
                        'dataUrl' => appurl::object_odp_dataUrl('comment', 'comment_overview_panel', array('reltype' => $model->reltype(), 'relid' => $model->pk())),
                        'onEditAjaxClick' => 'function($item){
                            $("#record_comment_overview .form_container").show();
                            $("#record_comment_overview .add").hide();

                            $("#record_comment_overview div.form_container").objectForm("loadEditation", $item.attr("itemid"));
                            //dialog muze mit scrollbar, ktery musi byt vyscrollovan nahoru, tak aby
                            //byl uzivateli zobrazen editacni formular pred ocima
                            $("#record_comment_overview").parents(".ui-dialog-content:first").scrollTop(0);
                            return false;
                        }',
                    ),
                    'css_class' => 'comment_list',
                ));
    ?>
</div>

</div>
<?= $script_include_tag;?>
<script type="text/javascript">
    $("#record_comment_overview").initCommentPanel();
</script>
