<?php defined('SYSPATH') or die('No direct script access.');


class AppFormItem_ExportFormat extends AppFormItem_Select
{
    
    public function getValue()
    {
        //hodnota, kterou ma tento prvek
        $value = parent::getValue();

        //pokud je hodnota prazdna, tak zvolim defaultni - vybiram podle
        //HTTP hlavicky operacni system a podle toho zkusim zvolit vhodny
        //format
        if (empty($value))
        {
            return rand(0,1) ? '1' : '0';
//            if (preg_match('/win/i', Request::user_agent('platform')) !== FALSE)
//            {
//                //windows platforma
//            }
//            else
//            {
//                //unix platforma
//            }
        }
    }
}