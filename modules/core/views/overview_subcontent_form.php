    <div id="<?= $form_config;?>">
        <div class="content form_content form-pg">
        <?php
            // Prectu config
            $config = Kohana::config($form_config);

            $js_config = (array)arr::get($config, 'js_config');

            $form_class = arr::get($config, 'class', 'Form_AppOverviewSubcontent');

            if ( ! class_exists($form_class)) {
                throw new AppException('Form class "'.$form_class.'" from config "'.$form_config.'" was not found.');
            }

            //vytvorim si novy objekt formulare
            $form = new $form_class($model, $config, array(), TRUE);

            //URL na kterou se bude formular odesilat
            $action_link = appurl::object_edit_ajax($model->table_name(), $form_config, $model->pk());

            //vlozim jej do sablony
            echo $form->Render($action_link);
        ?>
        </div>
    </div>

<script type="text/javascript">
$("#<?= $form_config;?>").objectForm(<?= json_encode($js_config) ?>);
</script>
