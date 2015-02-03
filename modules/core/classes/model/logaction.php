<?php defined('SYSPATH') or die('No direct access allowed.');


/**
 * Tato trida se stara o logovani zmen (ukladani historie zmen) urcitych tabulek
 * a pripadne urcitych sloupcu. V logovacich zaznamek se misto nazvu tabulek/sloupcu 
 * vyskytuji pouze zastupna cisla (pro zvyseni rychlosti), proto je pro funkci teto
 * tridy nutne definovat patricne ciselniky v configuracnim souboru logaction.php
 * nad kterym je definovan stejnojmenny helper.
 * 
 * @uses LogAction helper pro preklady nazvu tabulek/sloupcu na cisla a obracene
 * @author Jiri Dajc
 */
class Model_LogAction extends ORM_Authorized {

    protected $_preview = '@text';

    /**
     * Nazev DB tabulky nad kterou stoji tento model.
     * @var <string>
     */
    protected $_table_name = 'log_action';

    protected $_belongs_to = Array(
        'user' => Array('model' => 'user', 'foreign_key' => 'userid'),
    );

    /**
     * Seznam souvisejicich modelu
     * @var <array>
     */
    protected $_has_many = Array(
        Array('model' => 'LogActionDetail', 'foreign_key' => 'log_actionid'),
    );

    /**
     * Ciselniky
     * @var <array>
     */
    protected $_rel_cb = array(
        'cb_log_action_category'
    );

    public function getPreview()
    {
        return $this->text;
    }

    public function __get($column)
    {
        switch ($column)
        {
            case '_created':
                return date('j.n.Y H:i:s', strtotime(parent::__get('created')));
            break;

            //pokud jsou definovany atributy relid a reltype, tak nacte ORM
            //prislusneho relacniho zaznamu a vrati referenci
            case '_relA':
                if ($this->hasAttr('relAid') && $this->hasAttr('relAtype'))
                {
                    if ( ! empty($this->relAtype) && ! empty($this->relAid))
                    {
                        try
                        {
                             return ORM::factory($this->relAtype, $this->relAid);
                        }
                        catch (Exception $e)
                        {
                            kohana::$log->add(Kohana::ERROR, 'Unable to load _rel attribute model on model ":model" [:id], reltype: ":reltype" and relid: ":relid".', array(
                                ':id'       => $this->pk(),
                                ':model'    => $this->_table_name,
                                ':reltype'  => $this->relAtype,
                                ':relid'    => $this->relAid
                            ));
                        }
                    }
                }

                return FALSE;
            break;

            default:
                return parent::__get($column);
        }
    }
    
    
    /**
     * Zaloguje ze doslo ke zmene zaznamu - volano z orm::save() pro 
     * modely jejichz zmeny se maji logovat.
     * @param <string> $table_name nazev tabulky (modelu!)
     * @param <int> $row_id PK editovaneho zaznamu
     * @param <array> $original_data puvodni data 
     * @param <array> $new_data nova data
     * 
     */
    /*
    public function logChangeAndSave($table_name, $row_id, $original_data, $new_data) 
    {
        if (empty($original_data) or empty($new_data)) return;
        
        // Cislo tabulky
        $this->reltype = LogAction::getTableNumber($table_name);
        // ID updatovaneho zaznamu
        $this->relid = $row_id;
        // @TODO - nevim kde to brat
        $this->estateagencyid = NULL; 
        // @TODO - nevim kde to brat
        $this->sellerid = NULL;
        // Ulozeni log_action zaznamu
        $this->save();
        // Ulozeni detailnich zaznamu - s hodnotami jednotlivych atributu
        if ($this->_saved) {
            foreach ($original_data as $column => $value) {
                $log_detail = ORM::factory('LogActionDetail');
                // Id log_action zaznamu
                $log_detail->log_actionid = $this->pk();
                // Cislo sloupce
                $log_detail->attr = LogAction::getColNumber($table_name, $column);
                // Puvodni a nova hodnota
                $log_detail->old_value = $original_data[$column];
                $log_detail->new_value = $new_data[$column];
                $log_detail->save();
            }
        }
    }
     * 
     */
    
    
}