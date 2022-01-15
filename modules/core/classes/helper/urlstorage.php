<?php

class Helper_UrlStorage
{

    protected static $storrage_model = 'url_name';

    protected static $object_name_column = 'object_name';

    protected static $object_id_column = 'object_id';

    protected static $url_name_column = 'url_name';

    protected static $language_column = 'language';

    protected static $latest_column = 'latest';

    // Local cache
    protected static $_cache = Array();


    public static function getUriForObject(Kohana_ORM $object)
    {
        return static::getUri($object->object_name(), $object->pk(), Lang::getCurrentLanguageCode());
    }

    /**
     * Returns Array('object_name' => 'foo', 'object_id' => X) for given URI.
     * If uri not found then empty array is returned
     * @static
     * @param $uri
     * @return array
     */
    public static function getObjectArrayForUri($uri)
    {
        if (isset(static::$_cache[$uri])) {
            return static::$_cache[$uri];
        }

        $url_name = ORM::factory(static::$storrage_model, array('url_name' => $uri));
        if ($url_name->loaded()) {
            $data = array(
                'object_name' => $url_name->{static::$object_name_column},
                'object_id'   => $url_name->{static::$object_id_column},
                'language'   => $url_name->{static::$language_column},
                'latest'      => $url_name->{static::$latest_column},
            );
            return static::$_cache[$uri] = $data;
        }

        // Object for uri not found
        return array();
    }


    public static function isUriLatest($uri)
    {
        return (bool)arr::get(static::getObjectArrayForUri($uri), 'latest', false);
    }

    /**
     * Returns object name for given URI
     * @static
     * @param $uri
     * @return mixed false if URI not found
     */
    public static function getObjectNameForUri($uri)
    {
        return arr::get(static::getObjectArrayForUri($uri), 'object_name', false);
    }

    /**
     * Returns object_id (PK) for given URI
     * @static
     * @param $uri
     * @return mixed false if uri not found
     */
    public static function getObjectIdForUri($uri)
    {
        return arr::get(static::getObjectArrayForUri($uri), 'object_id', false);
    }

    /**
     * Returns object language for given URI
     * @static
     * @param $uri
     * @return mixed false if URI not found
     */
    public static function getObjectLanguageForUri($uri)
    {
        return arr::get(static::getObjectArrayForUri($uri), 'language', false);
    }

    public static function getLatestUri($uri)
    {
        $uri_data = static::getObjectArrayForUri($uri);
        if ( ! arr::get($uri_data, 'latest', false)) {
            return static::getUri($uri_data['object_name'], $uri_data['object_id']);
        }
    }


    /**
     * Generate and persist unique url-name for given model based on it's title.
     * @static
     * @param Kohana_ORM $model
     * @param $title
     * @param $make_unique - whether to make non-unique value unique - by appending number
     */
    public static function setObjectUriByTitle(Kohana_ORM $model, $language = 'en', $title, $make_unique=true)
    {
        if ( ! $model->loaded()) {
            throw new AppException('Trying to set uri for non-loaded object.');
        }
        $object_name = $model->object_name();
        $object_id = $model->pk();
        static::setUri($object_name, $object_id, $language, $title, $make_unique);
    }

    /**
     * Returns TRUE if given URL is available - if object is given then url is available
     * if it's not used OR it's used for given object.
     * @param $uri
     * @param null $object_name
     * @param null $object_id
     * @return bool
     */
    public static function isUriAvailable($uri, $object_name=NULL, $object_id=NULL)
    {
        $url_name = ORM::factory(static::$storrage_model)
            ->where(static::$url_name_column, '=', $uri);
        if ( ! empty($object_name) and ! empty($object_id)) {
            $url_name->and_where_open()
                ->where(static::$object_name_column, '!=', $object_name)
                ->or_where(static::$object_id_column, '!=', $object_id)
                ->and_where_close()
                // we are concerned only about latest urls - archived urls can be re-assigned to a different object - #5486
                ->where(static::$latest_column, '=', '1');
        }
        $url_name->find();
        return ! $url_name->loaded();
    }


    public static function setUri($object_name, $object_id, $language = 'en', $title, $make_unique=true, $params=array())
    {
        if (empty($title)) {
            if($language == Lang::getDefaultLanguage()) {
                throw new AppException('Trying to set empty uri for object '.$object_name.':'.$object_id.'.');
            }else{
                return;
            }
        }

        $title = $final_title = url::title($title, '-', true);
        $number = 0; $saved = false;

        $reserved_static_segments = (array)Kohana::config('routes.reserved_static_segments');

        while ( ! $saved) {
            // If title is used for static URI
            if (in_array($final_title, $reserved_static_segments)) {
                // Change title and continue
                $number++;
                $final_title = $title.'-'.$number;
                continue;
            }
            // Try to store url_name
            try {
                // We try to find the record we are about to create
                $url_name = ORM::factory(static::$storrage_model)
                    ->where_open()
                        ->where_open()
                            ->where(static::$object_name_column, '=', $object_name)
                            ->where(static::$object_id_column, '=', $object_id)
                            ->where(static::$language_column, '=', $language)
                            ->where(static::$url_name_column, '=', $final_title)
                        ->where_close()
                        // #5486 - we can re-assign archived url to a different object
                        ->or_where_open()
                            ->where(static::$language_column, '=', $language)
                            ->where(static::$url_name_column, '=', $final_title)
                            ->where(static::$latest_column, '=', '0')
                        ->or_where_close()
                    ->where_close()
                    ->find();
                ;
                // Load additional params
                $url_name->values($params);
                $url_name->{static::$language_column} = $language;
                $url_name->{static::$url_name_column} = $final_title;
                $url_name->{static::$object_name_column} = $object_name;
                $url_name->{static::$object_id_column} = $object_id;
                // Make sure currently stored url_name will be the latest version
                $url_name->{static::$latest_column} = 1;

                $url_name->disablePermissions()->save();

                // Other sotered url names for current object are not the latest
                DB::query(Database::UPDATE, "UPDATE ".static::$storrage_model." SET ".static::$latest_column."=0 WHERE ".static::$object_id_column."=:object_id AND ".static::$object_name_column."=:object_name AND ".static::$language_column."=:language AND ".static::$url_name_column." <> :url_name")
                    ->parameters(array(
                    ':object_id' => $object_id,
                    ':object_name' => $object_name,
                    ':language' => $language,
                    ':url_name' => $final_title,
                ))->execute();

                $saved = true;
            }
            catch (Database_Exception $e) {
                // Duplicate entry for unique key
                if ($e->getCode() == 1062) {
                    // We are supposed to make title unique
                    if ($make_unique) {
                        $number++;
                        $final_title = $title.'-'.$number;
                    } else {
                        // we're not supposed to generate unique url name
                        // re-throw the exception
                        throw $e;
                    }
                } else {
                    throw $e;
                }
            }
        }
    }


    public static function getUri($object_name, $object_id, $language = 'en', $strict_language = false)
    {
        $uri = ORM::factory(static::$storrage_model)
            ->where(static::$object_name_column, '=', $object_name)
            ->where(static::$object_id_column, '=', $object_id)
            ->where(static::$language_column, '=', $language)
            ->where(static::$latest_column, '=', 1)
            ->find();

        if(!$uri->loaded() && !$strict_language) {
            return self::getUri($object_name, $object_id, Lang::getDefaultLanguage(), true);
        }

        return $uri->url_name;
    }



}
