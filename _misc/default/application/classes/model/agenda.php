<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_Agenda extends ORM {

    protected $_rel_cb = Array(
        'cb_agenda_type', 'cb_agenda_category', 'cb_agenda_priority', 
    );

    /**
     * Definuje ze pri mazani zaznamu ma dojit pouze k aktualizaci a nastaveni
     * atributu 'deleted' na hodnotu '1' namisto skutecneho odstraneni z tabulky.
     * @var <bool>
     */
    protected $update_on_delete = TRUE;

    protected $_preview = '@name';

    /**
     * Pomocna metoda pro nastaveni filtru pouze na ukoly.
     * @return <$this>
     */
    public function onlyTasks()
    {
        return $this->where('cb_agenda_typeid', '=', '1');
    }

    /**
     * Pomocna metoda pro nastaveni filtru na pouze 'finished' zaznamy.
     * Plati pouze pro ukoly.
     * @return <$this>
     */
    public function onlyUnfinished()
    {
        return $this->where('datedone', 'IS', DB::Expr('NULL'));
    }

    /**
     * Predikat rikajici, zda se jedna o udalost
     * @return <bool>
     */
    public function isEvent()
    {
        return $this->cb_agenda_typeid == '2';
    }
    /**
     * Predikat rikajici zda se jedna o ukol
     * @return <bool>
     */
    public function isTask() 
    {
        return $this->cb_agenda_typeid == '1';
    }

    public function __get($column)
    {
        switch ($column)
        {
            case '_info':

                if ($this->IsEvent())
                {
                    return $this->cb_agenda_category->value . ' - (' . date('H:i', strtotime($this->time_from)). '-' . date('H:i', strtotime($this->time_to)) . ')';
                }
                else if ($this->IsTask())
                {
                    return $this->cb_agenda_category->value;
                }

                break;

            case '_overdue':

                return $this->isTask() && ! $this->datedone == NULL && strtotime($this->datedue) < strtotime(date('Y-m-d'));

                break;

            //datum pod kterym ma byt zaznam zobrazen - pro udalosti se jedna o
            //atribut 'date' a u ukolu se jedna:
            //  a) pro hotove ukoly -   datedone
            //  b) pro nehotove ukoly - datedue
            case '_date_display':

                if ($this->IsTask())
                {
                    //pokud je ukol hotovy, tak vracim datum dokonceni
                    if ($this->datedone != NULL)
                    {
                        //pokud byl ukol dokoncen po terminu tak pozdejsi datum dokonceni
                        if ($this->datedone > $this->datedue)
                        {
                            return date('j.n.Y', strtotime($this->datedone));
                        }
                        return date('j.n.Y', strtotime($this->datedue));
                    }

                    //jen pomocna promenna abych nemusel funkci volat vicekrat
                    $today = date('j.n.Y');

                    //pokud neni hotovy a je po terminu tak dnesni datum
                    if ($this->datedue < $today)
                    {
                        return $today;
                    }

                    //neni po terminu - vraci jeho standardni datum naplavani
                    return date('j.n.Y', strtotime($this->datedue));
                }
                else
                {
                    return $this->datedue;
                }

                break;

            default:
                return parent::__get($column);
        }
    }


    /**
     * Metoda navic krome ulozeni kontroluje zmenu atributu datedone. Pokud je
     * hodnota zmenena na jinou nez NULL hodnotu, tak to znamena ze ukol je nastaven
     * na hotovy a je potreba projit cilovy zaznam (na ktery ukol ukazuje) a
     * v pripade ze k nemu neexistuje jiz zadny jiny ne-hotovy zaznam v agende
     * tak nastavit atribut planned na hodnotu 0.
     */
    public function Save()
    {
        $datedone = $this->datedone;

        $retval = parent::save();

        //staci mi jakakoli zmena tohoto atributu
        if ($this->attrValueChangedTo('datedone', $datedone))
        {
            //ted zkontroluju zaznam ke kteremu ukol patril a pokud ma
            //obsahuje priznak naplanovany a uz k nemu neexistuje zadna dalsi
            //agenda tak jej prepnu na nenaplanovany
            $model_name = LogNumber::getTableName($this->reltype);

            if ( ! empty($model_name))
            {
                //nactu si relacni model
                $rel_model = ORM::factory($model_name, $this->relid);

                //pokud obsahuje priznak naplanovani
                if ($rel_model->hasAttr('planned'))
                {
                    //existuje k zaznamu jina nedodelana agenda ?
                    $other_undone_agenda = (bool)ORM::factory('agenda')->where('reltype', '=', $this->reltype)
                                                                       ->where('relid', '=', $this->relid)
                                                                       ->where('datedone', 'IS',  DB::expr('NULL'))
                                                                       ->count_all();

                    //pokud zadna jina agenda neexistuje, tak zaznam prepnu na ne-naplanovany
                    if ( ! $other_undone_agenda)
                    {
                        $rel_model->planned = 0;
                        $rel_model->save();
                    }
                }
            }
        }

        return $retval;
    }
    
}
