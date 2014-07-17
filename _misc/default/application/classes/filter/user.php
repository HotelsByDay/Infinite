<?php

class Filter_User extends Filter_Base {

    protected function applyFilter($orm)
    {
        if (($value = arr::get($this->filter_params, 'fulltext')) != NULL)
        {
            $orm->join('seller')
                ->on('seller.sellerid', '=', 'user.sellerid');
            
            $orm->and_where_open()
                    ->like('username', $value)
                    ->or_like('seller.firstname', $value)
                    ->or_like('seller.surname', $value)
                ->and_where_close();
        }


        return $this;
    }
    
    
    protected function applyFulltextFilter($orm, $query)
    {
        $orm->like('username', $query);

        return $this;
    }

}

?>
