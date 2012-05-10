<?php

class Filter_LogAction extends Filter_Base {

    /**
     * Konfigurace pro tento filtr.
     * @var <arrary>
     */
    protected $config = array(
        //vyber velikosti stranky
        'page_size' => array(
            '5' => '5',
            '15' => '15',
            '50' => '50'
        ),
        //defaultni velikost stranky
        'default_page_size' => 15,
        //defaultni zpusob razeni
        'default_orderby'     => 'created',
        'default_orderby_dir' => 'desc'
    );

    protected function applyFilter($orm)
    {
        if (($relAtype = arr::get($this->filter_params, 'relAtype')) != NULL)
        {
            $orm->where('log_action.relAtype', '=', $relAtype);
        }
        if (($relAid = arr::get($this->filter_params, 'relAid')) != NULL)
        {
            $orm->where('log_action.relAid', '=', $relAid);
        }

        // Min datum vlozeni
        if (($value = arr::get($this->filter_params, 'created_from', '')) != '') {
            $orm->where(DB::Expr('DATE(`log_action`.`created`)'), '>=', Format::mysqlDate($value));
        }
        // Max datum vlozeni
        if (($value = arr::get($this->filter_params, 'created_to', '')) != '') {
            $orm->where(DB::Expr('DATE(`log_action`.`created`)'), '<=', Format::mysqlDate($value));
        } 

        return $this;
    }


    protected function applyFulltextFilter($orm, $query)
    {
        $orm->like('log_action.text', $query);

        return $this;
    }

    /**
     * Zajistuje vlozeni potrebnych JS souboru do stranky.
     * @return <type>
     */
    public function getForm($form_view = NULL)
    {
        //do stranky vlozim potrebne JS soubory (zajistuje dynamicke funkce
        //na formulari pro vlozeni filtru)
        Web::instance()->addCustomJSFile(View::factory('js/jquery.logactionFilterForm.js'));
        Web::instance()->addCustomJSFile(View::factory('js/jquery.logactionFilterForm-init.js'));

        //standardni vygenerovani formulare
        return parent::getForm($form_view);
    }

}

?>
