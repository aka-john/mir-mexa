<?php

namespace Core\Components;

use Core\Components\Convert;
/*
 * Request class. Handle all request and sessions. Singleton class
 */
class Request
{
    public static $request;
    public static $session;
    protected static $_instance;
    
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
     * Ger request. Get all request list if data is value
     * @param string value
     * @return array
     */
    public static function getRequest($value = null) 
    {
        if ($value == null) {
            return CRM_GET_REQUEST();
        }
        
        static::$request = CRM_GET_REQUEST();

        if (!isset(static::$request[$value])) {
            return '';
        }
        
        return $value == '' ? static::$request : static::$request[$value];
    }
    
    /*
     * Set request
     * @param string value
     * @return ''
     */
    public static function setRequestValue($key, $value) 
    {
        $_REQUEST[$key] = $value;
        static::$request[$key] = $value;

        return '';
    }
    
    /*
     * Get request object. Convert request to object
     * @param string value
     * @return ''
     */
    public static function getRequestObject($data = null) 
    {
        if (!isset(static::$request[$data])) {
            return null;
        }
        
        $data = $data == '' ? static::$request : static::$request[$data];
        return Convert::arrayToObject($data);
    }
    
    /*
     * Ger session.
     * @param string value
     * @return array
     */
    public static function getSession($data = null)
    {
        if ($data != null){
            static::$session = $_SESSION;
            $result = '';
            if (count(static::$session[$data]) == 1) {
                $result = reset(static::$session[$data]);
            } else {
                $result = static::$session[$data];
            }
            return Convert::arrayToObject($result);
        }
        return '';
    }

    /*
     * Redirect
     * @param string redirect url
     * @return ''
     */
    public static function redirect($url) 
    {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: ".CRM_GET_HTTP_HOST()."/manager/".$url);
        exit();
    }

    /*
     * Xss protect
     * @param array parametrs to check
     * @return array
     */
    public static function xssFilter($arr) 
    {
        $filter_key = array("<", ">","="," (",")","/");
        $find = array(
	        '/data:/i'       => 'd&#097;ta:',
	        '/about:/i'      => '&#097;bout:',
	        '/vbscript:/i'   => 'vbscript<b></b>:',
	        '/onclick/i'     => '&#111;nclick',
	        '/onload/i'      => '&#111;nload',
	        '/onunload/i'    => '&#111;nunload',
	        '/onabort/i'     => '&#111;nabort',
	        '/onerror/i'     => '&#111;nerror',
	        '/onblur/i'      => '&#111;nblur',
	        '/onchange/i'    => '&#111;nchange',
	        '/onfocus/i'     => '&#111;nfocus',
	        '/onreset/i'     => '&#111;nreset',
	        '/onsubmit/i'    => '&#111;nsubmit',
	        '/ondblclick/i'  => '&#111;ndblclick',
	        '/onkeydown/i'   => '&#111;nkeydown',
	        '/onkeypress/i'  => '&#111;nkeypress',
	        '/onkeyup/i'     => '&#111;nkeyup',
	        '/onmousedown/i' => '&#111;nmousedown',
	        '/onmouseup/i'   => '&#111;nmouseup',
	        '/onmouseover/i' => '&#111;nmouseover',
	        '/onmouseout/i'  => '&#111;nmouseout',
	        '/onselect/i'    => '&#111;nselect',
	        '/javascript/i'  => 'j&#097;vascript',
	        '#<script#i'     => '&lt;script'
	    );
        foreach($arr as $key => $value){
        	if (!is_array($value)) {
        		$value = (string)urldecode($value);
    			$key   = (string)$key;
        	}
			$arr[str_replace($filter_key, "", $key)] = str_replace(array('&amp;amp;','&amp;lt;','&amp;gt;'), array('&amp;amp;amp;','&amp;amp;lt;','&amp;amp;gt;'), $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('/(&amp;#*\w+)[\x00-\x20]+;/u', '$1;', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('/(&amp;#x*[0-9A-F]+);*/iu', '$1;', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('#(&lt;[^&gt;]+?[\x00-\x20"\'])(?:on|xmlns)[^&gt;]*+&gt;#iu', '$1&gt;', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript…', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript…', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding…', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('#(&lt;[^&gt;]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^&gt;]*+&gt;#i', '$1&gt;', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('#(&lt;[^&gt;]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^&gt;]*+&gt;#i', '$1&gt;', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('#(&lt;[^&gt;]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^&gt;]*+&gt;#iu', '$1&gt;', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('#&lt;/*\w+:\w[^&gt;]*+&gt;#i', '', $value);
			$arr[str_replace($filter_key, "", $key)] = preg_replace('#&lt;/*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^&gt;]*+&gt;#i', '', $value);
        	foreach ($find as $k => $v) {
	            $arr[str_replace($filter_key, "", $key)] = preg_replace($k, $v, $value);
	        }
        }
        return $arr;
    }

}
