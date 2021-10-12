<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_Core_User extends Model_Auth_User {

    protected $_has_many = array(
        'role' => array('model' => 'role', 'through' => 'role_user'),
    );

    /**
     * Validacni pravidla
     */
    protected $_rules = array
    (
        'username'      => array
        (
            'not_empty'     => NULL,
            'min_length'    => array(4),
            'max_length'    => array(32),
            'regex'         => array('/^[a-zA-Z0-9\.@]+$/')
        ),
        'password'      => array(
            'not_empty'     => NULL,
            'min_length'    => array(8),
            'max_length'    => array(50),
            'matches'       => array('password_confirm')
        ),
        'active'        => array(
            'not_empty'     => NULL
        )
    );



    /**
     * Callbacky pro provedeni slozitejsich kontrol nad hodnotami atributu pri validaci.
     */
    protected $_callbacks = array
    (
        //kontroluje zda je uzivatelske jmeno unikatni
        'username' => array('username_available'),
    );

    /**
     * Do tohoto atributu se nacita seznam opravneni uzivatele na zaklade role.
     * Aplikuje se tam dedicnost roli.
     * @var <array> 
     */
    protected $permission_list = NULL;

    /**
     * Definuje ze pri mazani zaznamu ma dojit pouze k aktualizaci a nastaveni
     * atributu 'deleted' na hodnotu '1' namisto skutecneho odstraneni z tabulky.
     * @var <bool>
     */
    protected $update_on_delete = TRUE;

    /**
     * Seznam uzivatelskych roli, ktere ma dany uzivatel. Seznam naplni metoda
     * loadUserRoles.
     * @var <array>
     */
    protected $roles = array();


    /**
     * Toto je volano z login controlleru ihned po uspesnem prihlaseni uzivatele
     */
    public function afterLogin()
    {
        // nothing by default
    }

    protected function getDefaults()
    {
        $defaults = parent::getDefaults();
        $defaults['active'] = 1;
        return $defaults;
    }

    /**
     * Metoda testuje zda uzivatel ma roli s danym nazvem. Druha varianta pouziti
     * je ze $role_name je pole retezcu (nazvu uzivatelskych roli) a podle druheho
     * argumentu se testuje zda ma uzivatl kazdou z nich nebo alespon jednu.
     * 
     * @param <string|array> $role_name Nazev role, ktera je testovana. Nebo pole
     * ktere obsahuje nazvy uzivatelskych roli, ktere jsou testovany.
     *
     * @param <bool> $and_operator V pripade ze se testuje vice nez jedna uzivatelska
     * role tak definuje zda uzivatel musi mit vsechny nebo alespon jednu roli
     * aby metoda vratila TRUE.
     *
     * @param <bool> V pripade ze $and_operator je FALSE, tak vraci TRUE pokud
     * ma uzivatel alespon jednu z testovanych uzivatelskych roli. V opacnem
     * pripade musi mit uzivatel vsechny testovane uzivatelske role.
     */
    public function HasRole($role_name, $and_operator = FALSE)
    {
        //nacte seznam aktualnim roli uzivatele do $this->roles
        $this->loadUserRoles();

        //pokud se testuje jen jedna role, tak prevedu na variantu s polem
        //abych nize mohl jednotnym zpusobem testovat
        if ( ! is_array($role_name))
        {
            $role_name = array($role_name);
        }

        foreach ($role_name as $one_role_name)
        {
            //uzivatel musi mit vsechny testovano uzivatelske role
            if ($and_operator)
            {
                if ( ! in_array($one_role_name, $this->roles))
                {
                    return FALSE;
                }
            }
            //uzivatel musi mit alespon jednu uzivatelskou roli
            else
            {
                if (in_array($one_role_name, $this->roles))
                {
                    return TRUE;
                }
            }
        }
    }


    /**
     * Metoda testuje zda dany uzivatel je administratorem.
     * Poznava to tak ze uzivatel ma roli s nazvem 'admin'.
     * @return <bool> Vraci true pokud uzivatel danou roli me. False v opacnem
     * pripade.
     */
    public function IsAdmin()
    {
        return $this->username === 'root';
    }

    /**
     *
     * @param <type> $object_name
     * @param <type> $function
     * @return <type>
     *
     * @TODO: Provest revizi metody, vypada to ze to je napsane OK, ale mozna
     * by to slo vylepsit.
     */
    public function HasPermission($object_name, $function = NULL)
    {

        /**
         * PP 16.12.2013: added config settings to disable ACL on application level
         */
        if ( ! Kohana::config('application.acl_enabled', true)) {
            return TRUE;
        }

        //admin ma opravneni na vsecko
        if ($this->IsAdmin()) {
            return TRUE;
        }

        if ($this->permission_list == NULL) {

            //nacte seznam aktualnich roli uzivatele do $this->roles
            $this->loadUserRoles();

            //definice jednotlivych roli
            $roles_definition = Kohana::config('_user_roles');

            //v profileru chci mit cas pro vypocet opravneni uzivatele
            $profiler_token = Profiler::start('auth', 'Generating user permission list');

            $this->permission_list = $this->getUserPermissionList($this->roles, $roles_definition);

            //trick for not logged-in users
            if(!$this->permission_list) {
                $this->permission_list = $this->getUserPermissionList('public', $roles_definition);
            }

            Profiler::stop($profiler_token);
        }

        //pokud nema vubec zadne opravneni na dany objekt, tak vraci false
        if ( ! isset($this->permission_list[$object_name]))
        {
            return false;
        }

        //pokud neni definovana specificka pozadovana funkce objektu, tak
        //se kontroluje jednoduse vyskyt klice s nazevm objektu
        if ($function == NULL)
        {
            return isset($this->permission_list[$object_name]);
            //vracim uroven opravneni pro danou funkci - pokud neni definovano tak FALSE - nema opravneni
            //return arr::get($this->permission_list, $object_name, FALSE);
        }
        else
        {
            //vracim uroven opravneni pro danou funkci - pokud neni definovano tak FALSE - nema opravneni
            return arr::get($this->permission_list[$object_name], $function, FALSE);
        }
    }

    /**
     * Vraci hash vsech uzivatelskych opravneni - pri zmene nektereho z jeho opravneni dojde i ke zmene hashe.
     * @return string
     */
    public function getPermissionListHash()
    {
        // @todo - refaktorizovat - udelat metodu loadPermissionList()
        $this->HasPermission('', '');
        return md5(serialize($this->permission_list));
    }

    /**
     * Nacte seznam nazvu (atribut 'role.name') vsech uzivatelskcych roli, ktere
     * jsou uzivatel prirazeny.
     * @return <type>
     */
    public function loadUserRoles()
    {
        if ($this->roles != NULL)
        {
            return $this->roles;
        }

        $role_query = DB::select('name')->from('role_user')
                                        ->join('role', 'left')->on('role_user.roleid', '=', 'role.roleid')
                                        ->where('role_user.userid', '=', $this->pk())
                                        ->order_by('priority', 'asc');

        foreach ($role_query->execute() as $role)
        {
            $this->roles[] = $role['name'];
        }

        //kazdy uzivatel ma prirazenou roli se stejnym nazvem jako jeho uzivatelske
        //jmeno - tohle nam umozni snadno delat customizace pro jednotlive uzivatele
        //navic se to pouziva ve webove casti - kde je kazdy navstevnik prihlasen
        //jako web_anonymous - ma automaticky prirazenou roli web_anonymous a neni
        //treba vytvaret zadne vazby v DB
        $this->roles[] = (string)$this->username;

        return $this->roles;
    }

    public function getUserPermissionList($user_roles, $roles_definition)
    {
        $permission_mix = array();

        foreach ((array)$user_roles as $user_role) {

            if (isset($roles_definition[(string)$user_role]))
            {
                $permission_mix = $this->mergeRolePermissions($permission_mix, $user_role, $roles_definition);
            }
        }

        return $permission_mix;
    }

    /**
     * Provadi vypocet funkci opravneni dane role a to pripoji k zakladnim funkcim
     * opravneni, ktere jsou definovane prvnim argumentem.
     *
     * Ocekavane pouziti je takove ze se budou ve smycce prochazet vsechny role
     * uzivatele a pres prvni argument se bude predavat navratova hodnota teto
     * metody pri minule iteraci - tim padem se tam vzdy primichaji opravneni
     * dalsi role.
     *
     * @param <type> $base
     * @param <type> $role_name
     * @param <type> $roles_definition
     * @return <type> 
     */
    public function mergeRolePermissions($base, $role_name, & $roles_definition)
    {
        //pokud neni co mergovat , tak vracim pouze zaklad
        if ( ! isset($roles_definition[$role_name]) || ! isset($roles_definition[$role_name]['functions']))
        {
            return $base;
        }

        //zpracovani definice opravneni pro danou roli (nektere funkce
        //se rozgeneruji a upravi se tvar pole na asoc. (pravidla mohou byt definovany
        //ve forme index. pole)
        $roles_definition[$role_name]['functions'] = $this->expandRoleFunctions($roles_definition[$role_name]['functions']);

        //k funkcim dane role primicham funkce dedenych roli
        if (isset($roles_definition[$role_name]['inherits']) && ! empty($roles_definition[$role_name]['inherits']))
        {
            $inherited_roles_functions = array();
            foreach ((array)$roles_definition[$role_name]['inherits'] as $inherited_role_name)
            {
                if (isset($roles_definition[$inherited_role_name]))
                {
                   $inherited_roles_functions = $this->mergeRolePermissions($inherited_roles_functions, $inherited_role_name, $roles_definition);
                }
            }
            //vyresetuju to co dedi - to uz priste nebudu muset pocitat, protoze
            //to uz mam cele v 'functions' "namichane"
            $roles_definition[$role_name]['inherits'] = array();

            //mam zaklad role - to co dedi od ostatnich, k tomu pridam to co
            //ma explicitne definovane
            $roles_definition[$role_name]['functions'] = $this->mergeRolePermissions($inherited_roles_functions, $role_name, $roles_definition);
        }

        //smicham opravneni dane roli + zaklad, a to tak aby zaklad mel vyssi prioritu
        return arr::merge($base, $roles_definition[$role_name]['functions']);
    }

    /**
     * Pro funkce orpavneni "action_*" automaticky pridava funkce pro cteni z DB
     * typu "db_*", ktere se pouzivaji v ORM. Tyto funkce pridava bez modifikatoru
     * a pouze pokud nejsou explicitne definovany.
     * @param <type> $functions 
     */
    protected function expandRoleFunctions($all_functions)
    {
        //pro funkce na klicich pridam funkce, ktere jsou v hodnote
        $expand = array(
            'overview' => array(
                'db_select',
            ),
            'new' => array(
                'edit',
                'db_insert'
            ),
            'edit' => array(
                'db_select',
                'db_update'
            ),
            'delete' => array(
                'db_delete'
            ),
            'table' => 'db_select',
        );
        
        $functions_out = array();

        //projdu vsechny opravneni ve vstupnim poli
        foreach ($all_functions as $object => $functions)
        {
            $functions_out[$object] = array();

            foreach ((array)$functions as $f => $mod)
            {
                //pokud plati is_numeric tak opravneni je zapsane bez modifikatoru
                if (is_numeric($f))
                {
                    $functions_out[$object][$mod] = TRUE;
                }
                else
                {
                    $functions_out[$object][$f] = $mod;
                }
            }

            //provedu rozsireni
            foreach ($expand as $function => $next_functions)
            {
                if (isset($functions_out[$object][$function]))
                {
                    foreach ((array)$next_functions as $next_function)
                    {
                        if ( ! isset($functions_out[$object][$next_function]))
                        {
                            $functions_out[$object][$next_function] = TRUE;
                        }
                    }
                }
            }
        }

        return $functions_out;
    }

    protected function mergeObjectPermissions($functions1, $functions2)
    {
        return arr::merge($functions1, $functions2);
    }

    /**
     * Pri hledani zaznamu vzdy pridat podminku pro filtrovani pouze zaznamu pro
     * ktere plati public=1.
     * @return <type> 
     */
    public function find_all($deleted_too = FALSE)
    {
        $this->where('public', '=', '1');

        return parent::find_all($deleted_too);
    }

    public function count_all()
    {
        $this->where('public', '=', '1');

        return parent::count_all(); // TODO: Change the autogenerated stub
    }

    public function findUserToLogin($username)
    {
        return $this->where($this->unique_key($username), '=', $username)
            //uzivatel musi byt aktivni
            ->where('active', '=', '1')
            ->find();
    }

    /**
     * Nastavuje priznak $this->_waking_up, ktery slouzi k detekci ze prave
     * je model vytahovan ze Session. Vice viz. dokumentace atributu $this->_waking_up.
     */
    public function __wakeup()
    {
        // Initialize database
	$this->_initialize();

	if ($this->_reload_on_wakeup === TRUE)
	{
            $this->_waking_up = TRUE;

            // Reload the object
            $this->reload();

            $this->_waking_up = FALSE;
	}
    }

    /**
     * Zajistuje refresh ORM modelu prihlaseneho uzivatele v session pokud doslo
     * k jeho editaci - kdyz uzivatel edituje svuj profil. Je to potreba napr.
     * k tomu aby se projevilo nove nastaveni jmena uzivatele, ktere je
     * zobrazeno v pravem hornim panelu.
     * @return <type>
     */
    public function save()
    {
        $retval = parent::save();

        //pokud doslo k ulozeni zaznamu aktualne prihlaseneho uzivatele, tak provedu
        //__wakeup coz zajisti nove nacteni zaznamu do session - mohlo se zmenit
        //treba jmeno uzivatele (diky zmene vazby na maklere) apod.
        if (Auth::instance()->logged_in() && $this->pk() == Auth::instance()->get_user()->pk())
        {
            Auth::instance()->get_user()->__wakeup();
        }

        return $retval;
    }

    /**
     * V pripade ze dochazi k "waking up" tak preskakuje kontrolu opravneni.
     * Tato vyjimka ze musi byt aby bylo mozne prihlasit uzivatele.
     *
     * @return <BOOL>
     */
    protected function applyUserUpdatePermission()
    {
        //pri prihlasovani jeste neni uzivatel prihlasen a pak neni zadouci provadet
        //aplikaci opravneni
        if ($this->_waking_up || ! Auth::instance()->logged_in())
        {
            return TRUE;
        }

        //pokud nema uzivatel opravneni pro cteni na objektu user tak mu bude
        //povoleno precist alespon jeho vlastni uzivatelsky zaznam
        if ( ! parent::applyUserUpdatePermission())
        {
            $this->applyUserUpdatePermissionModificator('own');
        }

        return true;
    }

    /**
     * Metoda zajistuje ze pokud dochazi k "waking up" modelu, tak nedochazi ke
     * kontrole opravneni. Vice viz. dokumentace k atributu $this->_waking_up. 
     * 
     * @return <type>
     */
    protected function applyUserSelectPermission()
    {
        //pri prihlasovani jeste neni uzivatel prihlasen a pak neni zadouci provadet
        //aplikaci opravneni
        if ($this->_waking_up || ! Auth::instance()->logged_in())
        {
            return TRUE;
        }

        //pokud nema uzivatel opravneni pro cteni na objektu user tak mu bude
        //povoleno precist alespon jeho vlastni uzivatelsky zaznam
        if ( ! parent::applyUserSelectPermission())
        {
            $this->applyUserSelectPermissionModificator('own');
        }

        return TRUE;
    }
    
    /**
     * Metoda aplikuje modifikator opravneni 'db_select'.
     * @param <string> $modificator
     */
    protected function applyUserSelectPermissionModificator($modificator)
    {
        kohana::$log->add(Kohana::ERROR, 'applyUserSelectPermissionModificator');
        kohana::$log->write();
        switch ($modificator)
        {
            case 'own':
                $this->where('userid', '=', Auth::instance()->get_user()->pk());
            break;

            case 'public':
                $this->where('public', '=', '1');
            break;
        }
    }

    /**
     * Pridava nektere virtualni atributy k modelu.
     * 
     * @param <type> $column
     * @return <type>
     */
    public function __get($column)
    {
        switch ($column)
        {
            case '_active':
                return __('user.active_value_'.parent::__get('active'));
            break;

            case '_last_login':
                return date('j.n.Y H:i:s', strtotime(parent::__get('last_login')));
            break;

            case '_created':
                return date('j.n.Y', strtotime(parent::__get('created')));
            break;

            case '_role':

                $list = array();

                foreach ($this->role->find_all() as $role)
                {
                    $list[] = $role->name;
                }

                return implode(', ', $list);

                break;

            default:
                return parent::__get($column);
        }
    }

    /**
     * Name displayed in top navigation panel.
     * @return bool|mixed|ORM|string
     */
    public function name()
    {
        $res = trim($this->name . ' ' . $this->surname);
        if (empty($res)) {
            $res = $this->username;
        }
        return $res;
    }

    public function contact_email()
    {
        return $this->email;
    }

    /**
     * Slouzi k ulozeni nastaveni pro daneho uzivatele ve tvaru klic=>hodnota.
     *
     * @param <string> $name Nazev klice
     * @param <string> $value Hodnota
     *
     * @return <$this> Vraci referenci na danou instance, je tedy chainable.
     *
     * @chainable
     */
    public function setSetting($name, $value)
    {
        //nastaveni uzivatele je ulozene ve slouci settings ve formatu JSON
        $settings = json_decode($this->settings, TRUE);

        //vlozim do asoc. pole
        $settings[$name] = $value;

        //ulozim zpatky
        $this->settings = json_encode($settings);

        //chainable
        return $this;
    }

    /**
     * Vraci hodnotu specifickeho klice v nastaveni pro daneho uzivatele.
     * 
     * @param <string> $name Nazev klice
     * @param <string> $default Defaultni hodnota, pokud neni v nastaveni
     * uzivatele definovana
     * @return <string>
     */
    public function getSetting($name, $default)
    {
        //nastaveni uzivatele je ulozene ve slouci settings ve formatu JSON
        $settings = json_decode($this->settings, TRUE);

        //vratim hodnotu nastaveni daneho uzivatele
        return arr::get($settings, $name, $default);
    }

    /**
     * Metoda slouzi jako callback pri validaci. Kontroluje zda je emailova adresa uzivatele
     * unikatni.
     * @param Validate $array
     * @param <type> $field
     */
    public function email_available(Validate $array, $field)
    {
        if ( ! $this->is_unique_value($field, $array[$field]))
        {
            $array->error($field, 'email_available', array($array[$field]));
        }
    }

    /**
     * Metoda slouzi jako callback pri validaci. Kontroluje zda je uzivatelske
     * jmeno unikatni.
     * @param Validate $array
     * @param <type> $field
     */
    public function username_available(Validate $array, $field)
    {
        if ( ! $this->is_unique_value($field, $array[$field]))
        {
            $array->error($field, 'username_available', array($array[$field]));
        }
    }

} // End User Model