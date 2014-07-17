<?php defined('SYSPATH') or die('No direct script access.');

class Database_Query extends Kohana_Database_Query {


    public function getSingleScalarResult($db=null)
    {
        $res = $this->execute($db);
        $res = (array)arr::get($res, 0);
        return current($res);
    }

}
