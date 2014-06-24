<?php defined('SYSPATH') or die('No direct script access.');

class AppFormItem_RelNNSelectAjax extends AppFormItem_Base
{
    protected $view_name = 'appformitem/relnnselectajax';

    protected $virtual = true;

    protected $map = null;

    protected $config = array(

    );


    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);

        $this->map = FormItem::NNTableName($this->model->object_name(), $this->config['rel']);
    }

    public function init()
    {
        parent::init();

        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemRelNNSelectAjax.js'));

        parent::addInitJS(
            View::factory('js/jquery.AppFormItemRelNNSelectAjax-init.js')->set('rel', $this->config['rel'])
        );
    }


    protected function getRelItems()
    {
        $selectedItems = array();

        $relKey = $this->config['rel'].'id';
        $mapData = $this->model->{$this->config['rel']}->find_all();

        foreach ($mapData as $model) {
            $selectedItems[] = array('value' => $model->{$relKey}, 'name' => $model->preview());
        }

        return $selectedItems;
    }

    protected function addRelItem($rel, $model)
    {
        if (!$this->model->has($rel, $model)) {
            $this->model->add($rel, $model);
        }
    }

    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            case AppForm::FORM_EVENT_AFTER_SAVE:

                $rel = $this->config['rel'];

                if ($this->form_data) {

                    $dataIds = explode(',', $this->form_data);

                    foreach ($this->model->{$rel}->find_all() as $model) {
                        if (!in_array($model->pk(), $dataIds)) {
                            $this->model->remove($rel, $model);
                        }
                    }

                    foreach ($dataIds as $relitemid) {
                        $model = ORM::factory($rel, $relitemid);

                        $this->addRelItem($rel, $model);
                    }

                } elseif (!$this->isRequired()) {

                    foreach ($this->model->{$rel}->find_all() as $model) {
                        $this->model->remove($rel, $model);
                    }
                }
        }
    }

    /**
     * Generates form item's HTML
     *
     * @param null $render_style Definuje zpusob zobrazeni formularoveho prvku.
     * Ocekava jednu z konstant AppForm::RENDER_STYLE_*.
     *
     * @param null $error_message Definuje validacni chybu, ktera ma byt
     * u prvku zobrazena.
     *
     * @return \View
     */
    public function Render($render_style = null, $error_message = null)
    {
        return parent::Render($render_style, $error_message)
            ->set('selected', $this->getRelItems());
    }

}
