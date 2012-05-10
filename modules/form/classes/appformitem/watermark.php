<?php

/**
 * Virtualni prvek, ktery stoji nad 4 atributy modelu. Jsou to:
 * watermark_x
 * watermark_y - pozice watermarku v obrazku - v %
 * watermark_width - sirka watermarku v % vuci obrazku
 * watermark_opacity - uroven pruhlednosti
 */
class AppFormItem_Watermark extends AppFormItem_Base {

    /// Tento prvek je virtualni - v $this->model neexistuje atribut $this->attr
    protected $virtual = TRUE;
    
    // Nazev pohledu pro tento prvek
    protected $view_name = 'appformitem/watermark';
    
    /**
     * Interni ciselnik prvku - miry pruhlednosti
     */
    protected $opacity_levels = Array();
    
    
     /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function init()
    {
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemWatermark.js'));
        
        $this->addInitJS(View::factory('js/jquery.AppFormItemWatermark-init.js')->set('value', $this->getValue()));
        
        return parent::init();
    }
    
    
    /**
     * Pouze nastaveni opacity_levels - do DB se uklada klic, tedy 1, 2 nebo 3
     * @param type $attr
     * @param type $config
     * @param Kohana_ORM $model
     * @param type $form_data
     * @param type $form 
     */
    public function __construct($attr, $config, Kohana_ORM $model, Kohana_ORM $loaded_model, $form_data, $form) {
        parent::__construct($attr, $config, $model, $form_data, $form);
        $this->opacity_levels = Array(
            '20' => __('appformitem_watermark.opacity_level_1'),
            '50' => __('appformitem_watermark.opacity_level_2'),
            '80' => __('appformitem_watermark.opacity_level_3'),
        );
    }
    
    
    
    /**
     * Zapise hodnotu do modelu
     * @param type $value 
     */
    public function setValue($value) {
        // Nevim jestli ma smysl definovat ty defaultni hodnoty jako atributy, pripadne v configu nebo vys
        $this->model->watermark_pos = arr::get($value, 'x', 0);
//        $this->model->watermark_y = arr::get($value, 'y', 0);
        $this->model->watermark_width = arr::get($value, 'width', 20);
        $this->model->watermark_opacity = arr::get($value, 'opacity', 50);
        
        // Nevolame parent::setValue($val), protoze tento prvek je virtuani (nevaze se primo na jeden atribut modelu)
    }
    
    
    /**
     * Vrati vsechny ctyri atributy, nad kterymi tento prvek stoji, jako jedno pole
     * @return array 
     */
    public function getValue()
    {
        return Array(
            'pos' => (int)$this->model->watermark_pos,
//            'y' => (int)$this->model->watermark_y,
            'width'   => (int)$this->model->watermark_width,
            'opacity' => $this->model->watermark_opacity,
        );
    }
      
    public function Render($render_style = NULL, $error_messages = NULL) {
        $view = parent::Render($render_style, $error_messages);
        
        // Nevim jestli ma smysl definovat treba jako atribut tridy nejaky defaultni bg image
        $view->background_image = arr::get($this->config, 'background_image', '');
        
        $view->opacity_levels = $this->opacity_levels;

        $view->init_watermark_image = $this->model->estateagency_watermark->loaded()
                                        ? url::base().$this->model->estateagency_watermark->getFileDiskName()
                                        : url::base().'watermark.jpg';

        
        return $view;
    }
    
    
}

?>
