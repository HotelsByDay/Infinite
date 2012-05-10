<?php defined('SYSPATH') or die('No direct access allowed.');


/**
 * Reprezentuje objekt Comment - uzivatelske komentare.
 */
class Model_Core_Comment extends ORM
{

    protected $_has_many = array('attachements' => array('model' => 'comment_attachement','foreign_key' => 'commentid'),
                                 'comment_attachement' => array('model' => 'comment_attachement','foreign_key' => 'commentid'),
    );

    protected $_belongs_to = array('user' => array('model' => 'user',
                                                   'foreign_key' => 'userid')
    );

    public function __get($column)
    {
        switch($column)
        {
            case '_text':
                return nl2br(htmlentities(parent::__get('text')));
            break;

            case '_created':
                return date('j M Y H:i:s', strtotime(parent::__get('created')));
            break;

            default:
                return parent::__get($column);
        }
    }

    /**
     * 
     * @param <type> $user_id
     * @param <type> $relid
     * @param <type> $reltype
     * @return <type> 
     */
    public function getNumberOfUnreadComments($user_id, $relid, $reltype)
    {
        return (int)DB::select('unread')->from('comment_unread')
                                        ->where('comment_unread.relid',  '=', $relid)
                                        ->where('comment_unread.reltype','=', $reltype)
                                        ->where('comment_unread.userid', '=', $user_id)
                                        ->execute($this->_db)
                                        ->get('unread');
    }

    /**
     *
     * @param <type> $user_id
     * @param <type> $relid
     * @param <type> $reltype
     * @return <type>
     */
    public function getNumberOfAllComments($relid, $reltype)
    {
        return (int)DB::select(array('COUNT("*")', 'total_count'))
                                        ->from('comment')
                                        ->where('comment.relid',  '=', $relid)
                                        ->where('comment.reltype','=', $reltype)
                                        ->execute($this->_db)
                                        ->get('total_count');
    }

    public function setAllAsRead($user_id, $relid, $reltype)
    {
        return DB::delete('comment_unread')->where('comment_unread.relid',  '=', $relid)
                                           ->where('comment_unread.reltype','=', $reltype)
                                           ->where('comment_unread.userid', '=', $user_id)
                                           ->execute($this->_db);
    }

    /**
     *
     * @param <type> $user_id
     */
    public function hasBeenRead($user_id = NULL)
    {
        if ($user_id === NULL)
        {
            $user_id = Auth::instance()->get_user()->pk();
        }

        return 0 == DB::select(array('COUNT("*")', 'total_count'))
                                    ->from('comment_unread')
                                    ->where('comment_unread.commendid', '=', $this->pk())
                                    ->where('comment_unread.userid', '=', $user_id)
                                    ->execute($this->_db)
                                    ->get('total_count');
    }

    protected function getRelatedUserList()
    {
        //vytahnu si vsechny uzivatele systemu, ktery maji priznak 'verejny'
        //napriklad root tam nepatri
        $user_list = DB::select('userid')->from('user')
                                         ->where('userid', '!=', Auth::instance()->get_user()->pk())
                                         ->where('public', '=', '1')
                                         ->execute($this->_db);

        $out = array();

        foreach ($user_list as $user_item)
        {
            $out[] = $user_item['userid'];
        }

        return $out;
    }

    protected function createUnreadUserRelations()
    {

        $user_list = $this->getRelatedUserList();

        $values_string = '';

        foreach ($user_list as $userid)
        {
            if ($values_string !== '')
            {
                $values_string .= ',';
            }
            $values_string .= '('.$userid.', '.$this->relid.', '.$this->reltype.', 1)';
        }

        DB::query(Database::INSERT, 'INSERT INTO `comment_unread` (`userid`, `relid`, `reltype`, `unread`)
                                                    VALUES '.$values_string.'
                                   ON DUPLICATE KEY UPDATE `unread`=`unread`+1')->execute();
    }

    public function save()
    {
        //byl zaznam ulozen pred volanim metody save() (takto detekuji zda dochazi
        //k insertu)
        $loaded_before_save = $this->loaded();

        $retval = parent::save();

        //pokud pred volanim save() nebyl ulozen a ted je, tak doslo k prvnimu
        //ulozeni
        if ( ! $loaded_before_save && $this->loaded())
        {
            //vytvori vazby na aktualni uzivatele systemu, ktere definuji ze
            //uzivatele dany komentar jeste neprecetli
            $this->createUnreadUserRelations();
        }

        return $retval;
    }

    /**
     * Format preview tohoto prvku je "Komentář od Jiří Melichar, 15:36 1.4.2012"
     * 
     * @param <type> $preview
     */
    public function preview($preview=NULL)
    {
        return __('comment.preview_format', array(
            ':username' => $this->user->preview(),
            ':datetime' => time::toUserTZ(Auth::instance()->get_user()->getTimezone(), 'j M Y H:i:s', $this->created),
        ));
    }
}
