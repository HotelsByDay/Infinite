<?php

class Helper_UrlStorage
{

    protected static $storrage_model = 'url_name';

    // Local cache
    protected static $_cache = Array();

    public static function deleteUriForObject(Kohana_ORM $object)
    {
        ORM::factory(self::$storrage_model)
            ->where('object_name', '=', $object->object_name())
            ->where('object_id', '=', $object->pk())
            ->delete_all();
    }


    public static function getUriForObject(Kohana_ORM $object)
    {
        return self::getUri($object->object_name(), $object->pk());
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
        if (isset(self::$_cache[$uri])) {
            return self::$_cache[$uri];
        }

        $url_name = ORM::factory(self::$storrage_model, array('url_name' => $uri));
        if ($url_name->loaded()) {
            $data = array(
                'object_name' => $url_name->object_name,
                'object_id' => $url_name->object_id,
                'latest'    => $url_name->latest,
            );
            return self::$_cache[$uri] = $data;
        }

        // Object for uri not found
        return array();
    }


    public static function isUriLatest($uri)
    {
        return (bool)arr::get(self::getObjectArrayForUri($uri), 'latest', false);
    }

    /**
     * Returns object name for given URI
     * @static
     * @param $uri
     * @return mixed false if URI not found
     */
    public static function getObjectNameForUri($uri)
    {
        return arr::get(self::getObjectArrayForUri($uri), 'object_name', false);
    }

    /**
     * Returns object_id (PK) for given URI
     * @static
     * @param $uri
     * @return mixed false if uri not found
     */
    public static function getObjectIdForUri($uri)
    {
        return arr::get(self::getObjectArrayForUri($uri), 'object_id', false);
    }

    public static function getLatestUri($uri)
    {
        $uri_data = self::getObjectArrayForUri($uri);
        if ( ! arr::get($uri_data, 'latest', false)) {
            return self::getUri($uri_data['object_name'], $uri_data['object_id']);
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
        self::setUri($object_name, $object_id, $title, $make_unique);
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
                $url_name = ORM::factory(self::$storrage_model)
                    ->where('object_name', '=', $object_name)
                    ->where('object_id', '=', $object_id)
                    ->where('url_name', '=', $final_title)
                    ->find();
                ;
                $url_name->url_name = $final_title;
                $url_name->object_name = $object_name;
                $url_name->object_id = $object_id;
                // Make sure currently stored url_name will be the latest version
                $url_name->latest = 1;
                $url_name->save();

                // Other sotered url names for current object are not the latest
                DB::query(Database::UPDATE, "UPDATE ".self::$storrage_model." SET latest=0 WHERE object_id=:object_id AND object_name=:object_name AND url_name <> :url_name")
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
        $uri = ORM::factory(self::$storrage_model)
            ->where('object_name', '=', $object_name)
            ->where('object_id', '=', $object_id)
            ->where('latest', '=', 1)
            ->find();
        return $uri->url_name;
    }



}