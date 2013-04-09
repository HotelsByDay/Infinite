<?php

class Helper_UrlStorage
{

    protected static $storrage_model = 'url_name';

    protected static $object_name_column = 'object_name';

    protected static $object_id_column = 'object_id';

    protected static $url_name_column = 'url_name';

    protected static $latest_column = 'latest';

    // Local cache
    protected static $_cache = Array();


    public static function getUriForObject(Kohana_ORM $object)
    {
        return static::getUri($object->object_name(), $object->pk());
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
                'object_name' => $url_name->object_name,
                'object_id'   => $url_name->object_id,
                'latest'      => $url_name->latest,
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
    public static function setObjectUriByTitle(Kohana_ORM $model, $title, $make_unique=true)
    {
        if ( ! $model->loaded()) {
            throw new AppException('Trying to set uri for non-loaded object.');
        }
        $object_name = $model->object_name();
        $object_id = $model->pk();
        static::setUri($object_name, $object_id, $title, $make_unique);
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
                ->and_where_close();
        }
        $url_name->find();
        return ! $url_name->loaded();
    }


    public static function setUri($object_name, $object_id, $title, $make_unique=true)
    {
        if (empty($title)) {
            throw new AppException('Trying to set empty uri for object '.$object_name.':'.$object_id.'.');
        }

        $title = $final_title = url::title($title);
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
                    ->where(static::$object_name_column, '=', $object_name)
                    ->where(static::$object_id_column, '=', $object_id)
                    ->where(static::$url_name_column, '=', $final_title)
                    ->find();
                ;
                $url_name->{static::$url_name_column} = $final_title;
                $url_name->{static::$object_name_column} = $object_name;
                $url_name->{static::$object_id_column} = $object_id;
                // Make sure currently stored url_name will be the latest version
                $url_name->{static::$latest_column} = 1;
                $url_name->save();

                // Other sotered url names for current object are not the latest
                DB::query(Database::UPDATE, "UPDATE ".static::$storrage_model." SET ".static::$latest_column."=0 WHERE ".static::$object_id_column."=:object_id AND ".static::$object_name_column."=:object_name AND ".static::$url_name_column." <> :url_name")
                    ->parameters(array(
                    ':object_id' => $object_id,
                    ':object_name' => $object_name,
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
                }
            }
        }
    }


    public static function getUri($object_name, $object_id)
    {
        $uri = ORM::factory(static::$storrage_model)
            ->where(static::$object_name_column, '=', $object_name)
            ->where(static::$object_id_column, '=', $object_id)
            ->where(static::$latest_column, '=', 1)
            ->find();
        return $uri->url_name;
    }



}