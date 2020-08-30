<?php

namespace Core\Components;

/*
 * Class include convert functions
 */
class Convert {
    
    protected static $_instance;
    
    public static $price_def = array (
        'form' => array('1' => 0, '2' => 1, '1f' => 0, '2f' => 1, '3' => 1, '4' => 1),
        'rank' => array(
            0 => array('гривня', 'гривнi', 'гривень', 'f' => ''),
            1 => array('тисяча', 'тисячi', 'тисяч', 'f' => 'f'),
            //2 => array('миллион', 'миллиона', 'миллионов', 'f' => ''),
            //3 => array('миллиард', 'миллиарда', 'миллиардов', 'f' => ''),
            'k' => array('копiйка', 'копiйки', 'копiйок', 'f' => 'f')
        ),

        'words' => array(
            '0' => array( '', 'десять', '', ''),
            '1' => array( 'одна', 'одинадцять', '', 'сто'),
            '2' => array( 'двi', 'дванадцять', 'двадцять', 'двiстi'),
            '1f' => array( 'одна', '', '', ''),
            '2f' => array( 'двi', '', '', ''),
            '3' => array( 'три', 'тринадцять', 'тридцять', 'триста'),
            '4' => array( 'чотири', 'чотирнадцять', 'сорок', 'чотириста'),
            '5' => array( 'п`ять', 'п`ятнадцять', 'п`ятдесят', 'п`ятсот'),
            '6' => array( 'шiсть', 'шiстнадцять', 'шiстдесят', 'шiстсот'),
            '7' => array( 'сiм', 'сiмнадцять', 'сiмдесят', 'сiмсот'),
            '8' => array( 'вiсiм', 'вiсiмнадцять', 'вiсiмдесят', 'вiсiмсот'),
            '9' => array( 'дев`ять', 'дев`ятнадцять', 'дев`яносто', 'дев`ятсот')
        )
    );
    
    private function __construct(){}
    
    private function __clone(){}
    
    public static function getInstance() 
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /*
     * Convetr object to array
     * @param object 
     * @return array
     */
    public static function objectToArray($object)
    {

    }
    
    /*
     * Crop string
     * @param string 
     * @param int 
     * @return string
     */
    public function cropString($string, $limit)
    {
        $substring_limited = substr($string, 0, $limit);//режем строку от 0 до limit
        return $substring_limited.'...';//берем часть обрезанной строки от 0 до последнего пробела
    }
    
    /*
     * Convetr array to object
     * @param array 
     * @return object
     */
    public static function arrayToObject($array = array())
    {
        foreach($array as $key => $value){
            if(is_array($value)){
                $array[$key] = static::arrayToObject($value);
            }
        }
        return (object)$array;
    }
    
    public static function monthToLang($date, $lang = 'ru')
    {
        switch ($lang) {
            case 'ua':
                switch (date("n", $date)) {
                    case 1:  $dt = "січня"; break;
                    case 2:  $dt = "лютого"; break;
                    case 3:  $dt = "березня"; break;
                    case 4:  $dt = "квітеня"; break;
                    case 5:  $dt = "травня"; break;
                    case 6:  $dt = "червня"; break; 
                    case 7:  $dt = "липня"; break;
                    case 8:  $dt = "серпня"; break;
                    case 9:  $dt = "вересня"; break;
                    case 10: $dt = "жовтня"; break;
                    case 11: $dt = "лыстопада"; break;
                    case 12: $dt = "грудня"; break;
                }
                break;

            case 'ru':
                switch (date("n", $date)) {
                    case 1:  $dt = "января"; break;
                    case 2:  $dt = "февраля"; break;
                    case 3:  $dt = "марта"; break;
                    case 4:  $dt = "апреля"; break;
                    case 5:  $dt = "мая"; break;
                    case 6:  $dt = "июня"; break; 
                    case 7:  $dt = "июля"; break;
                    case 8:  $dt = "августа"; break;
                    case 9:  $dt = "сентября"; break;
                    case 10: $dt = "октября"; break;
                    case 11: $dt = "ноября"; break;
                    case 12: $dt = "декабря"; break;
                } 
                break;
        }

        return $dt;
    }
    
    public static function priceToString($str) 
    {
        $str = number_format($str, 2, '.', ',');
        $rubkop = explode('.', $str);
        $rub = $rubkop[0];
        $kop = (isset($rubkop[1])) ? $rubkop[1] : '00';
        $rub = (strlen($rub) == 1) ? '0' . $rub : $rub;
        $rub = explode(',', $rub);
        $rub = array_reverse($rub);

        $word = array();
        $word[] = self::priceToStringDvig($kop, 'k', false);
        foreach($rub as $key => $value) {
            if (intval($value) > 0 || $key == 0) //подсказал skrabus
                $word[] = self::priceToStringDvig($value, $key);
        }

        $word = array_reverse($word);
        return ucfirst(trim(implode(' ', $word)));
    }
       
    public function priceToStringDvig($str, $key, $do_word = true) 
    {
        $def =& static::$price_def;
        $words = $def['words'];
        $form = $def['form'];

        if (!isset($def['rank'][$key])) return '!razriad';
        $rank = $def['rank'][$key];
        $sotni = '';
        $word = '';
        $num_word = '';

        $str = (strlen($str) == 1) ? '0' . $str : $str;
        $dig = str_split($str);
        $dig = array_reverse($dig);

        if (1 == $dig[1]) {
                $num_word = ($do_word) ? $words[$dig[0]][1] : $dig[1] . $dig[0];
                $word = $rank[2];
        } else {
            //$rank[3] - famale
            if ($dig[0] != 1 && $dig[0] != 2) $rank['f'] = '';
            $num_word = ($do_word) 
                    ? $words[$dig[1]][2] . ' ' . $words[$dig[0] . $rank['f']][0]
                    : $dig[1] . $dig[0];
            $key = (isset($form[$dig[0]])) ? $form[$dig[0]] : false;
            $word = ($key !== false) ? $rank[$key] : $rank[2];
        }

        $sotni = (isset($dig[2])) ? (($do_word) ? $words[$dig[2]][3] : $dig[2]) : '';
        if ($sotni && $do_word) $sotni .= ' ';

        return $sotni . $num_word . ' ' . $word;
    }
}
