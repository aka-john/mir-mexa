<?php
class model 
{
    private $modx = null;
    private $db   = null;
    public $price_def = array (
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
    
    function __construct ($modx)
    {
        $this->modx  = $modx;
        $this->db    = $modx->db;
    }
    
    public function paginationBuild($total = null, $page = null, $display = 9) 
    {
        $page = $page == 0 ? 1 : intval($page);
		$totPages = ceil($total / $display);
		$curentPage = intval($page) == 0 ? 1 : intval($page);
		$pagesBefore = $page - 1;
		$pagesAfter = $totPages - $page;
		$tabArr = array();

        if($totPages > 15) {
            if($pagesBefore > 7) {
                $tabArr = array(1,2,0);
                if($pagesAfter > 7)
                {
                    for($i=($page-(4)); $i<$page; $i++) { $tabArr[] = $i; }
                } else {
                    for($i=($totPages-11); $i<$page; $i++) { $tabArr[] = $i; }
                }
            } else {
                for($i=1; $i<$page; $i++) { $tabArr[] = $i; }
            }
            $tabArr[] = $page;
            if($pagesAfter > 7) {
                if($pagesBefore > 7) {                          
                    for($i=($page+1); $i<=$page+4; $i++) { $tabArr[] = $i; }
                } else {
                    for($i=($page+1); $i<13; $i++) { $tabArr[] = $i; }
                }
                $tabArr[] = 0;
                $tabArr[] = $totPages-1;
                $tabArr[] = $totPages;
            } else {
                for($i=($page+1); $i<=$totPages; $i++) { $tabArr[] = $i; }
            } 
        } else {
            for($i=1;$i<=$totPages;$i++) { $tabArr[] = $i; }
        }  

        $pagination = '';
        $left = '';
        $right = '';
        $page = '';
        
        unset($_GET['q']);
        
        foreach ($tabArr as $page) {
		    if($page == 0) {
	            $pagination .= '<a>...</a>';
		    } elseif ($page == $curentPage) {
	            $pagination .= '<span class="active">'.$page.'</span>';                
		    } else {
	            $pagination .= '<a href="'. $this->modx->config['base_url'].$_REQUEST['q'].'?'.http_build_query(array_merge($_GET,array('page' => $page))).'">'.$page.'</a>';
		    }
        }

		if ($totPages > 1) {
            if ($curentPage > 1) {
                $page = $curentPage - 1;
				$left = '<a href="'. $this->modx->config['base_url'].$_REQUEST['q'].'?'.http_build_query(array_merge($_GET,array('page' => $page))).'" class="prev"></a>';
            }

            if ($curentPage == 0 || $curentPage*$display < $total) {
                $page  = $curentPage + 1;
				$right = '<a href="'. $this->modx->config['base_url'].$_REQUEST['q'].'?'.http_build_query(array_merge($_GET,array('page' => $page))).'" class="next"></a>';
            }
		}

        if ($totPages > 1) {
			return '
			    <div class="pagination">
					'.$left.'
					'.$pagination.'
					'.$right.'
			    </div>
			';
		} else {
			return '';
		}
    }
    
     public function json_encode_cyr($str) 
    {
        $arr_replace_utf = array('\u0410','\u0430','\u0411','\u0431','\u0412','\u0432',
            '\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416',
            '\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a',
            '\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f',
            '\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443',
            '\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428',
            '\u0448','\u0429','\u0449','\u042a','\u044a','\u042b','\u044b','\u042c','\u044c',
            '\u042d','\u044d','\u042e','\u044e','\u042f','\u044f');
        $arr_replace_cyr = array('А','а','Б','б','В','в','Г','г','Д','д','Е','е',
            'Ё','ё','Ж','ж','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н','О','о',
            'П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ч','ч','Ш','ш',
            'Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я');
        $arr = array();
        $str1 = json_encode($str);//JSON_NUMERIC_CHECK
        $result = str_replace($arr_replace_utf, $arr_replace_cyr, $str1);
        return $result;
    }
    
    public function getToArray() 
    {
        unset($_GET["q"]);
        if (count($_GET) > 0) {
            $result = array();
            foreach ($_GET as $key => $value) {
                $exp = explode('_', $key);
                switch ($exp[0]) {
                    case 'f':
                        $result['f'][$exp[1]][] = $this->num($exp[2]);
                        break;
                    case 'size':
                        $result['size'][] = $this->num($exp[1]);
                        break;
                    case 'price':
                        $result['price'][] = $exp[1];
                        break;
                }
            }
        }

        return $result;
    }
    
    public function num($value) 
    {
    	return intval(preg_replace("/\D/","",$value));
    }
    
    public function getIP() {
        if(isset($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];
        return $_SERVER['REMOTE_ADDR'];
    }

    public function charactersTranslate($text) 
    {
		$trans = array("'" => " ",'"' => " ","@" => " ", "&" => " and ", "#" => " ", "+" => " plus ", "?" => " ", "*" => " ", "$" => "", ">" => " ", "<" => " ");
        $data = strtr($text, $trans);
        return $data;
    }
    
    public function rmdirRecursive($dir, $flag = false) 
    {
	    if ($objs = glob($dir."/*")) {
	       foreach($objs as $obj) {
	         is_dir($obj) ? $this->rmdirRecursive($obj) : unlink($obj);
	       }
	    }
        
        if ($flag == true) {
            rmdir($dir);
        }
	}

    public function cutText($maxwords, $maxchar, $text)
    {
		$text = strip_tags($text);
		$tag  = substr($text, 0, 2);
		if ($tag == '[!' || $tag == '[[' || $tag == '{{') {
			return '';
		} else {
			$words=explode(' ',$text);
			$text='';
			foreach ($words as $word) {
				if (mb_strlen($text.' '.$word)<$maxchar) {
					$text.=' '.$word;
				} else {
					$text.='...';
					break;
				}
			}
			return $text;
		}
    }
    
    public function scanDir($dir) 
    {
	    $list = scandir($dir);
	    unset($list[0],$list[1]);
	    return array_values($list);
	}

	public function clearDir($dir) 
    {
	    $list = $this->scanDir($dir);
	    foreach ($list as $file) {
	        if (is_dir($dir.$file)) {
	            $this->clear_dir($dir.$file.'/');
	            rmdir($dir.$file);
	        } else {
	            unlink($dir.$file);
	        }
	    }
	}
    
    public function xssFilter($arr)
    {
        $filter = array("<", ">","="," (",")",";","/");
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
        		$value = (string)$this->db->escape($value);
    			$key   = (string)$this->db->escape($key);
        	}
            
			$arr[str_replace($filter_key, "", $key)] = str_replace($filter, "", $value);
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
    
    public function sendMail($to, $subject, $mail, $file = null) {
        $this->modx->loadExtension('MODxMailer');
        $this->modx->mail->Subject = $subject;
        $this->modx->mail->AddAddress($to);
        $this->modx->mail->MsgHTML($mail);
        $this->modx->mail->SMTPSecure = "ssl";
        $this->modx->mail->Send();
	}
    
    public function generateString($number) 
    {
        $arr = array(
        'a','b','c','d','e','f',
        'g','h','i','j','k','l',
        'm','n','o','p','r','s',
        't','u','v','x','y','z',
        'A','B','C','D','E','F',
        'G','H','I','J','K','L',
        'M','N','O','P','R','S',
        'T','U','V','X','Y','Z',
        '1','2','3','4','5','6',
        '7','8','9','0');
        $pass = "";
        for($i = 0; $i < $number; $i++) {
            $index = rand(0, count($arr) - 1);
            $pass .= $arr[$index];
        }
    	return $pass;
    }
    
    public function SortArray2m($array = array(), $key = null, $type = 'ASC') 
    {
        $res = array();
        if (sizeof($array)){
            $temp_array = array();
            foreach ($array as $k => $v){
                $temp_array[$k] = $v[$key];
            }
            if ( strtolower($type) == 'desc' ){ arsort($temp_array); }else{ asort($temp_array); }
            foreach ($temp_array as $k => $v) {
                $res[] = $array[$k];
                unset($array[$k]);
            }
        }
        return $res;
    }
    
    public function prevNextPagination($pid = null, $id = null)
    {
        $cid  = $this->modx->getActiveChildren($pid);
        $page = 0;
        for ($i = 0; $i < count($cid); $i++) {
            if ($cid[$i]['id'] == $id) {
                $page=$i;
            }
        }
        if ($page-1 >= 0) {
            $url = $this->modx->makeUrl($cid[$page-1]['id']);
            $prev .= '<a href="'.$url.'" class="next_post btn_gray">Следующая</a>';
        } else {
            $prev .= '<span></span>'; 
        }
        if ($page+1 < count($cid)) {
            $url = $this->modx->makeUrl($cid[$page+1]['id']);
            $next .= '<a href="'.$url.'" class="prev_post btn_gray">Предыдущая</a>';
        } else {
            $next .= '<span></span>'; 
        }
        
        $all = '<a href="'.$this->modx->makeUrl($pid).'" class="all_post btn_gray">Все публикации</a>';
        return $next.$prev.$all;
    }
    
    function sortBy($field, $array, $direction = 'asc')
    {
        usort($array, create_function('$a, $b', '
            $a = $a["' . $field . '"];
            $b = $b["' . $field . '"];

            if ($a == $b)
            {
                return 0;
            }

            return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
        '));

        return true;
    }

    public function priceToString($str) 
    {
        $str = number_format($str, 2, '.', ',');
        $rubkop = explode('.', $str);
        $rub = $rubkop[0];
        $kop = (isset($rubkop[1])) ? $rubkop[1] : '00';
        $rub = (strlen($rub) == 1) ? '0' . $rub : $rub;
        $rub = explode(',', $rub);
        $rub = array_reverse($rub);

        $word = array();
        $word[] = $this->priceToStringDvig($kop, 'k', false);
        foreach($rub as $key => $value) {
            if (intval($value) > 0 || $key == 0)
                $word[] = $this->priceToStringDvig($value, $key);
        }

        $word = array_reverse($word);
        return ucfirst(trim(implode(' ', $word)));
    }
       
    public function priceToStringDvig($str, $key, $do_word = true) 
    {
        $def =& $this->price_def;
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

    public function check($fields = array()) 
    {
		$error_arr = array();
		$loginexist = false;
		if ($_SESSION['webEmail'] != '') {
			$fields['email'] = $_SESSION['webEmail'];
		}
		if (count($fields) < 1) {
			return json_encode(array('error_status' => false));
		}
		foreach ($fields as $key => $value) {
			if (!isset($value['check']) || $value['check'] == null) {  //
                continue;
            }
			
			
            
		    $val = explode('::', $value['check']);
			
		    switch ($val[0]) {
		        case 'integer':
		            if (preg_match("/[^0-9]/s", $value["value"])) {
		                $error_arr[$key]["name"]    = $value["name"];
		                $error_arr[$key]["message"] = 'поле "'.$val[1].'" не является целочисленным';
		            }
		            break;
		        case 'float':
		            if (!is_numeric($value["value"])) {
		                $error_arr[$key]["name"]    = $value["name"];
		                $error_arr[$key]["message"] = 'поле "'.$val[1].'" не является числом';
		            }
		            break;
		        case 'email':
		        	if ($value["value"] == '') {
		                $error_arr[$key]["name"]    = $value["name"];
		                $error_arr[$key]["message"] = 'поле "'.$val[1].'" не заполнено';
		            } else {
		            	if (preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $value["value"]) == 0) {
			                $error_arr[$key]["name"]    = $value["name"];
			                $error_arr[$key]["message"] = 'email введён некоректно';
			            }
		            }
		            break;
		        case 'string':
		            if ($value["value"] == "") {
		                $error_arr[$key]["name"]    = $value["name"];
		                $error_arr[$key]["message"] = 'поле "'.$val[1].'" не заполнено';
		            }
		            break;
		        case 'phone':
		            if (!preg_match('/((8|\+3)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/', $value["value"]) || $value["value"] == '') {
		                $error_arr[$key]["name"]    = $value["name"];
		                $error_arr[$key]["message"] = 'телефон введён некоректно';
		            }
		            break;
		        case 'date':
				//if (!preg_match("/[0-9]{2}\\/[0-9]{2}\\/[0-9]{4}/", $value["value"])) {
				if (!preg_match("/[0-9]{2}\.[0-9]{2}\.[0-9]{4}/", $value["value"])) {
		                $error_arr[$key]["name"]    = $value["name"];
		                $error_arr[$key]["message"] = 'дата введена некоректно';
		            }
		            break;
		        case 'emailconf':
		            if (preg_match("|^[-0-9a-z_\.]+@[-0-9a-z_^\.]+\.[a-z]{2,6}$|i", $value["value"]) == 0) {
		                $error_arr[$key]["name"]    = $value["name"];
                        $error_arr[$key]["message"] = 'email введён некоректно';
		            } else {
		                $query = "select *, u.id as 'uid' from `modx_web_users` u join `modx_web_user_attributes` a on a.internalKey = u.id where LOWER(a.email) = '".$this->db->escape(strtolower($value['value']))."'";
		                $valid_user = $this->db->getRow(mysql_query($query));
		                if (count($valid_user) > 0 && $valid_user['email'] == $fields['email'] && $this->modx->getLoginUserID() != $valid_user['internalKey']) {
		                    $error_arr[$key]["name"]    = $value["name"];
		                    $error_arr[$key]["message"] = 'этот e-mail уже используется';
		                }
		            }
		            break;
		        case 'confirm':
		        	if (mb_strlen($value["value"],'UTF-8') < 6) {
		                $error_arr[$key]["name"]    = $value["name"];
		                $error_arr[$key]["message"] = 'Необходимо ввести 6 или больше символов';
		            } else {
			            if ($fields['password'] != $value["value"] || $fields['password'] == "") {
			                $error_arr[$key]["name"]    = $value["name"];
			                $error_arr[$key]["message"] = 'Пароль не совпадает';
			            }
		            }
		            break;
		        case 'passwordconf':
		            if (mb_strlen($value["value"],'UTF-8') < 6) {
		                $error_arr[$key]["name"]    = $value["name"];
		                $error_arr[$key]["message"] = 'Необходимо ввести 6 или больше символов';
		            }
		            break;
		        case 'password':
	                if (mb_strlen($value["value"],'UTF-8') < 6) {
	                    $error_arr[$key]["name"]    = $value["name"];
	                    $error_arr[$key]["message"] = 'Необходимо ввести 6 или больше символов';
	                } else {
		                if ($fields['email'] != '' || $fields['login'] != '') {
		                	$login = $fields['login'] != '' ? $fields['login'] : $fields['email'];
			                $query = "select *, u.id as 'uid' from `modx_web_users` u join `modx_web_user_attributes` a on a.internalKey = u.id where LOWER(u.username) = '".$this->db->escape(strtolower($login))."'";
			                $valid_user = $this->db->getRow(mysql_query($query));
		                }
		                if ($valid_user['password'] != md5($value["value"])) {
		                    $error_arr[$key]["name"]    = $value["name"];
		                    $error_arr[$key]["message"] = 'Пароль введён неверно';
		                }
	                }
		            break;
                case 'user':
		            if ($value["value"] == "") {
		                $error_arr[$key]["name"]    = $value["name"];
		                $error_arr[$key]["message"] = 'поле "'.$val[1].'" не заполнено';
		            } else {
		                    $query = "select *, u.id as 'uid' from `modx_web_users` u join `modx_web_user_attributes` a on a.internalKey = u.id where LOWER(a.email) = '".$this->db->escape(strtolower($value["value"]))."'";
		                    $valid_user = $this->db->getRow(mysql_query($query));
		                    if ($valid_user['email'] == "") {
		                        $error_arr[$key]["name"]    = $value["name"];
		                        $error_arr[$key]["message"] = 'пользователь с таким Email не найден';
		                    }
		            }
		            break;
		    }
		    $data[$value["name"]] = $value["value"];
		}
        
		/*if (isset($fields['recaptcha_challenge_field']) && $fields['recaptcha_challenge_field'] != '') {
		    $_POST["recaptcha_challenge_field"] = $fields['recaptcha_challenge_field'];
		    $_POST["recaptcha_response_field"]  = $fields['recaptcha_response_field'];
		    $varicode = $this->modx->runSnippet('reCaptcha', array('get' => 'result'));
            
		    if ($varicode == 'false' || $varicode == '') {
		        $count = count($error_arr)+1;
		        $error_arr['vericode']["name"]     = 'vericode';
		        $error_arr['vericode']["vericode"] = $this->modx->runSnippet('reCaptcha', array('get' => 'code'));
		        $error_arr['vericode']["message"]  = 'Код подтверждения введён неверно';
		    }
		    $data[count($data)+1] = 'vericode';
		}*/
		
		//$response = $_POST["g-recaptcha-response"];
		//echo $fields[$key]['g-recaptcha-response'];
		/*echo '<br>';*/
		//echo $fields['g-recaptcha-response'].'<br>';
		//echo '<pre>';
		//print_r($_POST);
		//print_r($_POST['data']);
		//echo 123;
		if(!empty($_POST['data'])){
			//echo 222;
		$exploded = array();
		parse_str($_POST['data'], $exploded);
			//print_r($exploded);
			//echo $exploded['g-recaptcha-response']; 
			//die();
			if(isset($exploded['g-recaptcha-response'])){
			$recaptchaResponse  = $exploded['g-recaptcha-response'];			
			$url = 'https://www.google.com/recaptcha/api/siteverify';
			$data = array(
				'secret' => $this->modx->config['reCaptcha_private'],
				'response' => $recaptchaResponse
			);
			
			$options = array(
				'http' => array (
					'method' => 'POST',
					'content' => http_build_query($data)
				)
			);
			$context  = stream_context_create($options);
			$verify = file_get_contents($url, false, $context);
			$captcha_success=json_decode($verify);
			//print_r($captcha_success);
			if ($captcha_success->success == false OR empty($captcha_success->success)) {
				 $count = count($error_arr)+1;
		         $error_arr['vericode']["name"]     = 'vericode';
		         $error_arr['vericode']["vericode"] = '';
		         $error_arr['vericode']["message"]  = 'Код подтверждения введён неверно';
			}
		   
            
		    $data[count($data)+1] = 'vericode';
			}
		}
		
		
		/*if ($value['name'] == 'g-recaptcha-response') {
		 
		   
		}*/
		
		
		
		if (count($error_arr) < 1) {
		    return json_encode(array('error_status' => true, 'array' => $data));
		} else {
		    return json_encode(array('error_status' => false, 'loginexist' => $loginexist, 'error' => $error_arr));
		}	
	}

    
}
