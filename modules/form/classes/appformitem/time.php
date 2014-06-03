<?php

/**
 * slouží pro nastavení casu pomoci selectu
 */
class AppFormItem_Time extends AppFormItem_Base {

    protected $view_name = 'appformitem/select';

    protected $config = Array (
        'time_start' => '00:00',
        'time_end'  => '23:59',
        'time_step' => 15,
        'time_format' => 'H:i',
    );

    // Local cache
    protected $times = array();

    protected function getTimeValues()
    {
        if ( ! empty($this->times)) {
            return $this->times;
        }
        $this->times[''] = '';
        $time = strtotime("2012-01-01 ".$this->config['time_start']);
        $end_time = strtotime("2012-01-01 ".$this->config['time_end']);
        while ($time <= $end_time) {
            $formatted_time = date('H:i', $time);
            $this->times[$formatted_time] = date($this->config['time_format'], $time);
            $time += 60*$this->config['time_step'];
        }
        return $this->times;
    }

    public function getValue()
    {
        $value = parent::getValue();
        if (empty($value)) {
            return '';
        }
        return date('H:i', strtotime($value));
    }

    public function Render($render_style = NULL, $error_messages = NULL)
    {
        $view =  parent::Render($render_style, $error_messages);

        $view->values = $this->getTimeValues();
        return $view;
    }


}


?>
