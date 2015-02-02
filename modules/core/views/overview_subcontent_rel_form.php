
    <div id="<?=$rel_form_config;?>">
        <div class="content form_content form-pg">
        <?php
            //defaultni hodnoty pro relacni zaznam
            $defaults = array(
                $model->primary_key() => $model->pk()
            );

            // Prectu config
            $config = Kohana::config($rel_form_config);

            $js_config = (array)arr::get($config, 'js_config');

            $form_class = arr::get($config, 'class', 'Form_AppOverviewSubcontent');

            if ( ! class_exists($form_class)) {
                throw new AppException('Form class "'.$form_class.'" from config "'.$form_config.'" was not found.');
            }

            //vytvorim si novy objekt formulare
            $form = new $form_class($model->{$rel_model}, $config, array('overwrite' => $defaults), TRUE);
            
            //URL na kterou se bude formular odesilat
            $action_link = $model->{$rel_model}->loaded()
                            ? appurl::object_edit_ajax($rel_model, $rel_form_config, $model->{$rel_model}->pk())
                            : appurl::object_new_ajax($rel_model, $rel_form_config, $defaults);

            //vlozim jej do sablony
            echo $form->Render($action_link);
            ?>
        </div>
    </div>

<script type="text/javascript">
$("#<?= $rel_form_config;?>").objectForm(<?= json_encode($js_config) ?>);
</script>
