<?php

class Filter_Comment extends Filter_Base {

    protected function applyFilter($orm)
    {
        //vazba na relacni zaznam
        if (($relid = arr::get($this->filter_params, 'relid', '')) != '') {
            $orm->where('relid', '=', $relid);
        }
        if (($reltype = arr::get($this->filter_params, 'reltype', '')) != '') {
            $orm->where('reltype', '=', $reltype);
        }
    }

    protected function applyFulltextFilter($orm, $query)
    {
        //bude se prohledavat i v nazvech prilozenych souboru i nazvech uzivatelu
        //kteri komentar vytvorili
        $orm->join('comment_attachement', 'LEFT')
            ->on('comment.commentid', '=', 'comment_attachement.commentid')
            ->join('user', 'LEFT')
            ->on('comment.userid', '=', 'user.userid')
            ->and_where_open()
                ->where('comment.text', 'LIKE', '%'.$query.'%')
                ->or_where('comment_attachement.nicename', 'LIKE', '%'.$query.'%')
                ->or_where('user.name', 'LIKE', '%'.$query.'%')
            ->and_where_close()
            ->group_by('comment.commentid');
    }
}

?>
