<?php
class frontEnd 
{
    private $modx = null;
    private $db   = null;
    private $xml  = null;
    
    function __construct ($modx)
    {
        require_once(MODX_BASE_PATH.'assets/site/model.php');
        $this->modx  = $modx;
        $this->db    = $modx->db;
        $this->model = new model($modx);
    }

    public function checkUrlAlias($q) 
    {
        $alias = end(array_filter(explode('/',$this->db->escape($q))));
        $alias = str_replace($this->modx->config['friendly_url_suffix'], '', $alias);

        if ($q != '' && $q != null && $alias != '') {
            $sql = 'SELECT 
                        p.id
                    FROM modx_mm_products p
                    WHERE p.alias = "'.$alias.'"';
            $query = $this->db->query($sql);
            
            if ($query) {
                if (mysql_num_rows($query) > 0) {
                    unset($_SESSION['product']);
                    $_SESSION['product'] = mysql_fetch_assoc($query);
                    $this->modx->sendForward(5);
                    die();
                } else {
                    //header("HTTP/1.0 404 Not Found");
                }
            }
        } else {
            //header("HTTP/1.0 404 Not Found");
        }
    }

    public function getSeolink() 
    {
        $q = $_SERVER['REQUEST_URI'];
        $q = preg_replace('/&page=[^&]+(&|$)/','$1',$q);
        $q = preg_replace('/&total=[^&]+(&|$)/','$1',$q);
        $q = preg_replace('/&status=[^&]+(&|$)/','$1',$q);

        $result = array();

        if ($q != '') {
            $sql = "SELECT * FROM modx_mm_seolink WHERE url = '".$this->db->escape($q)."' AND active = 1 LIMIT 1";
            $query = $this->db->query($sql);

            if ($query) {
                $result = $this->db->getRow($query);
            }
        }

        return $result;
    }
    
    public function registration($fields) 
    {
        if ($_SESSION['hash']['reg'] != md5(serialize($fields))) {
            if ($fields['password'] == '') {
                $fields['password'] = $this->model->generateString(8);
            }

            $query = "SELECT * FROM `modx_web_users` WHERE LOWER(email) = '".$this->db->escape(strtolower($fields['email']))."'";
            $valid_user = $this->db->getRow(mysql_query($query));
            if (count($valid_user) > 0) {
                return '';
            }

            if ($fields['birthday'] != '') {
				//$explode_date = explode('/', $fields['birthday']);
				$explode_date = explode('.', $fields['birthday']);//заменяем / на точку по просьбе
                $fields['birthday'] = $explode_date[2].'-'.$explode_date[1].'-'.$explode_date[0];
            }

            $this->db->query("INSERT INTO modx_web_users(username, password) VALUES ('".$this->db->escape($fields['email'])."', '".md5($fields['password'])."')");
            $id = $this->db->getInsertId();
            
            $this->db->query("INSERT INTO modx_web_user_attributes (internalKey,fullname,email,phone,birthday,city) VALUES (
                    ".$id.",
                    '".$this->db->escape($fields['name'])."',
                    '".$this->db->escape($fields['email'])."',
                    '".$this->db->escape($fields['phone'])."',
                    '".$this->db->escape(date('Y-m-d H:i:s', strtotime($fields['birthday'])))."',
                    '".$this->db->escape($fields['city'])."'
                )");
            $this->db->query("INSERT INTO modx_web_groups(id, webgroup, webuser) VALUES (null, 0,'".$id."')");

            $fields['login_url'] = $this->modx->makeUrl(26, '', '', 'full');

            $mail = $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('mail_reg', $fields,'[+','+]'))); 

            $this->model->sendMail($fields['email'], "Регистрация на сайте ".$this->modx->config['site_name'], $mail);

            $_SESSION['hash']['reg'] = md5(serialize($fields));

            return array('id' => $id, 'email' => $fields['email'], 'password' => $fields['password']);
        }
    }
    
    public function chackAuthCookie() 
    {
        if (isset($_COOKIE['token']) && !isset($_SESSION['webuser'])) {
            $token = htmlspecialchars($_COOKIE['token']);
            $sql= "SELECT * from modx_web_user_attributes WHERE token = '".$this->db->escape($token)."'";
            $user = $this->db->getRow($this->db->query($sql));

            if (!$user) {
               setcookie('token', '');
            }else {
               $_SESSION['webuser'] = $user;
            }
        }
    }

    public function setCityCookie($city) 
    {
        if ($city != '') {
            $city = $this->getCity($city);
            if (!$city) {
                return false;
            }
            $data = serialize(array('id'=>$city['id'],'name'=>$city['name']));
            setcookie("unauth_city", $data, time() + (86400 * 30) , '/' );
            $_COOKIE['unauth_city'] = $data;
            return $this->getCityCookie();
        }

        return false;
    }

    public function setCityConfirmedCookie() 
    {
        setcookie("unauth_city_conf", '1', time() + (86400 * 30) , '/' );

        return true;
    }

    public function getCityConfirmedCookie() 
    {
        if (isset($_COOKIE['unauth_city_conf'])) {
            return '1';
        }

        return '0';
    }

    public function unsetCityCookie() 
    {
        if (isset($_COOKIE['unauth_city'])) {
            setcookie("unauth_city", "", time()-3600);
            setcookie("unauth_city_conf", "", time()-3600);
        }
    }

    public function getCityCookie() 
    {
        if (isset($_COOKIE['unauth_city'])) {
            $data = unserialize($_COOKIE['unauth_city']);
            return $data;
        }

        return false;
    }

    public function getCityCookieId() 
    {
        if (isset($_COOKIE['unauth_city'])) {
            $data = unserialize($_COOKIE['unauth_city']);
            return $data['id'];
        }

        return false;
    }

    public function authorization($action, $fields) 
    {
        switch ($action) {
            case 'forgot':
                $sql = "SELECT * from modx_web_user_attributes WHERE LOWER(email) = '".$this->db->escape(strtolower($fields['email']))."'";
                $query = $this->db->query($sql);
                $user = $this->db->makeArray($query); 
                
                $newPassword  = $this->model->generateString(8);
                
                $this->db->query("UPDATE modx_web_users SET password = '".md5($newPassword)."' WHERE id = '".$user[0]['internalKey']."'");

                $fields['name']         = $user['fullname'];
                $fields['login_url']    = $this->modx->makeUrl(1, '', '#login', 'full');
                $fields['new_password'] = $newPassword;

                $mail = $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('mail_forgot', $fields,'[+','+]')));  

                $this->model->sendMail($fields['email'], "Восстановление пароля", $mail);

                break;
            case 'login':
                $this->db->query("UPDATE modx_web_user_attributes SET blocked = 0, failedlogincount = 0, blockeduntil = 0, blockedafter = 0 where blockeduntil <= ".time());

                $username = $this->db->escape($fields['email']);
                $password = md5($fields['password']);
                
                $time  = time();
                $sql = "SELECT *, u.id AS 'uid'
                    FROM modx_web_users u
                    JOIN modx_web_user_attributes a ON a.internalKey = u.id
                    WHERE u.username = '".$username."'";

                $valid_user = $this->db->getRow($this->db->query($sql));

                if ($valid_user['password'] != $password) {
                    return '';
                }
                
                if ($valid_user['uid']) {
                    $sql = "UPDATE modx_web_user_attributes SET
                                sessionid  = '".session_id()."',
                                logincount = logincount + 1,
                                lastlogin  = '".$time."'
                            WHERE internalKey = ".$valid_user['uid'];

                    $this->db->query($sql);

                    $dg  = '';
                    $i   = 0;
                    $sql = "SELECT uga.documentgroup
                        FROM modx_web_groups ug
                        INNER JOIN modx_webgroup_access uga ON uga.webgroup=ug.webgroup
                        WHERE ug.webuser =".$valid_user['uid'];

                    $ds = $this->db->query($sql);
                    while ($row = $this->db->getRow($ds,'num')) $dg[$i++] = $row[0];

                    $_SESSION['webShortname']      = $valid_user['username'];
                    $_SESSION['webFullname']       = $valid_user['fullname'];
                    $_SESSION['webEmail']          = $valid_user['email'];
                    $_SESSION['webValidated']      = 1;
                    $_SESSION['webInternalKey']    = $valid_user['internalKey'];
                    $_SESSION['webValid']          = base64_encode($fields['password']);
                    $_SESSION['webUser']           = base64_encode($valid_user['username']);
                    $_SESSION['webFailedlogins']   = $valid_user['failedlogincount'];
                    $_SESSION['webLastlogin']      = $valid_user['lastlogin'];
                    $_SESSION['webnrlogins']       = $valid_user['logincount'];
                    $_SESSION['webUserGroupNames'] = $this->db->getColumn("name", "SELECT wgn.name FROM modx_webgroup_names wgn INNER JOIN modx_web_groups wg ON wg.webgroup=wgn.id AND wg.webuser=".intval($valid_user['uid']));
                    $_SESSION['webDocgroups']      = $dg;
                    
                    if(isset($fields['remember_me'])) {
                        $token = md5(time().$fields['email']);
                        setcookie('token', $token, time() + 60 * 60 * 24 * 14);
                        $sql = "UPDATE modx_web_user_attributes SET
                            token = '".$token."'
                        WHERE internalKey = ".$valid_user['uid'];
                        $this->db->query($sql);
                    }

                    unset($valid_user['password']);
                    $this->unsetCityCookie();

                    $_SESSION['webuser'] = $valid_user;
                    $_SESSION['login'] = $username;
                   
                    if($id != $this->modx->documentIdentifier) {
                        if (getenv("HTTP_CLIENT_IP")) {
                            $ip = getenv("HTTP_CLIENT_IP");
                        } else if(getenv("HTTP_X_FORWARDED_FOR")) {
                            $ip = getenv("HTTP_X_FORWARDED_FOR");
                        } else if(getenv("REMOTE_ADDR")) {
                            $ip = getenv("REMOTE_ADDR");
                        } else { 
                            $ip = "UNKNOWN";
                        }
                        $_SESSION['ip'] = $ip;
                        $itemid = isset($_REQUEST['id']) ? $_REQUEST['id'] : 'NULL' ;
                        $a = 998;
                        if($a != 1) {
                            $sql = "REPLACE INTO modx_active_users (internalKey, username, lasthit, action, id, ip) values (-".intval($_SESSION['webInternalKey']).", '".$this->db->escape($_SESSION['webShortname'])."', '".$time."', '".$a."', ".intval($itemid).", '$ip')";
                            $this->db->query($sql);
                        }
                    }
                }

                break;
        }
    }
    
    public function changePassword($fields) 
    {
        $sql = 'UPDATE modx_web_users SET password = "'.md5($fields['password']).'" WHERE id = '.$this->modx->getLoginUserID();
        $this->db->query($sql);
        
        $user = $this->modx->getWebUserInfo($this->modx->getLoginUserID());
        
        $fields['name'] = $user['fullname'];
        $fields['login_url'] = $this->modx->makeUrl(1, '', '#login', 'full');
        $fields['new_password'] = $fields['password'];

        $mail = $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('mail_changepass', $fields,'[+','+]')));  
        $this->model->sendMail($user['email'], "Смена пароля", $mail); 
        return '';
    }
    
    public function updateProfile($fields) 
    {
        if ($fields['birthday'] != '') {
            $explode_date = explode('/', $fields['birthday']);
            $fields['birthday'] = $explode_date[2].'-'.$explode_date[1].'-'.$explode_date[0];
        }

        $sql = 'UPDATE modx_web_user_attributes SET 
                    fullname = "'.$this->db->escape($fields['name']).'",
                    email    = "'.$this->db->escape($fields['email']).'",
                    phone    = "'.$this->db->escape($fields['phone']).'",
                    city     = "'.$this->db->escape($fields['city']).'",
                    birthday = "'.$this->db->escape(date('Y-m-d H:i:s', strtotime($fields['birthday']))).'"
                WHERE internalKey = '.$this->modx->getLoginUserID();
        $this->db->query($sql);  

        $sql = 'UPDATE modx_web_users SET 
                    username = "'.$this->db->escape($fields['email']).'" 
                WHERE id = '.$this->modx->getLoginUserID();
        $this->db->query($sql);

        $sql = 'UPDATE modx_mm_mailer_user SET 
                    email = "'.$this->db->escape($fields['email']).'" 
                WHERE user_id = '.$this->modx->getLoginUserID();
        $this->db->query($sql);

        $this->updateUserSession();
        return '';
    }
    
    public function updateProfileAddress($fields) 
    {
        $sql = 'UPDATE modx_web_user_attributes SET 
                    comment = "'.addslashes($this->model->json_encode_cyr($fields)).'"
                WHERE internalKey = "'.$this->modx->getLoginUserID().'"';
        $this->db->query($sql);  
        $this->updateUserSession();
        return '';
    }

    public function updateUserSession() 
    {
        $user = $this->modx->getWebUserInfo($this->modx->getLoginUserID());
        if (is_array($_SESSION['webuser'])) {
            foreach ($_SESSION['webuser'] as $k => $v) {
                $_SESSION['webuser'][$k] = $user[$k];
            }
        }
    }

    public function getUserIp() 
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }

    public function getUserCityByIp() 
    {
        $ip = $this->getUserIp();
        $int = sprintf("%u", ip2long($ip));

        $sql = "SELECT * FROM modx_mm_city WHERE region_id = (
                    SELECT region_id FROM city WHERE name = (
                        SELECT name_ru FROM net_city WHERE id = (
                            SELECT city_id FROM (
                                SELECT * FROM net_ru WHERE begin_ip <= '".$int."' order by begin_ip desc limit 1
                            ) AS t WHERE end_ip >= '".$int."'
                        )
                    )
                )";
 
        $query = mysql_query($sql);
        $result = mysql_fetch_array($query);

        return $result;
    }

    public function setUserCity($city) 
    {
        /*$arr = array();
        $arr['name'] = strtolower($data['name_ru']);
        $arr['alias'] = strtolower($data['name_en']);
        $city = $this->getCityByNameOrAlias($arr);*/
        
        //выбранный город
        if ($city && is_array($city)) {
            $this->setCityCookie($city['id']);
        } else {
            //город по умолчанию
            $sql = "select id from modx_mm_city where default_city = 1 limit 1";
            $query = $this->db->query($sql);
            $city = $this->db->getRow($query);
            $this->setCityCookie($city['id']);
        }

        return $this->getCityCookieId();
    }

    public function getDefaultCityFromQuery() 
    {
        if ($this->modx->getLoginUserID()) {
            $user_info = $this->modx->getWebUserInfo($this->modx->getLoginUserID());

            if (intval($user_info['city']) != 0) {
                $city_query = intval($user_info['city']);
            } else {
                $city_query = '(select id from modx_mm_city where default_city = 1 limit 1)';
            }
        } else {
            $unauth_city = $this->getCityCookieId();
            $city_query = '(select id from modx_mm_city where '.($unauth_city ? 'id = '.intval($unauth_city) : 'default_city = 1').' limit 1)';
        }

        return $city_query;
    }
    
    public function rebuildCatalog($page, $display) 
    {
		//$sort = 'p.id desc, p.position desc';//сортировка
		//$sort = 'sale_price DESC, price DESC';//сортировка
		if (isset($_GET['orderby']) && $_GET['orderby'] != 0) {
			
			switch($_GET['orderby']){
				case '1':
					$sort = 'sale_price ASC, price ASC';//сортировка
				break;
				
				case '2':
				$sort = 'sale_price DESC, price DESC';//сортировка
				break;
			}
			
		}else{
			$sort = 'p.amount desc,p.id desc, p.position desc';//сортировка
		}
        if (isset($_GET['status']) && $_GET['status'] != 0) {
            $where[] = 'p.status = '.$this->model->num($_GET['status']);
        }

        if (isset($_GET['price_min']) || isset($_GET['price_max'])) {
            $city_query = $this->getDefaultCityFromQuery();
            
            $where_price = ' 
                AND (
                    IF (
                        (select count(*) from modx_mm_size2city where size_id = (select id from modx_mm_product_size where product_id = p.id order by size_id asc limit 1) AND city_id = '.$city_query.') > 0
                        , 
                            (select sale_price from modx_mm_size2city where size_id = 
                                (select id from modx_mm_product_size s where s.product_id = p.id  
                                    and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                                order by s.size_id asc limit 1)
                                AND
                                city_id = '.$city_query.'
                            limit 1)
                        ,
                        (select sale_price from modx_mm_product_size where product_id = p.id order by size_id asc limit 1)
                    )
                >= '.$this->model->num($_GET['price_min']).' 
                AND 
                    IF (
                        (select count(*) from modx_mm_size2city where size_id = (select id from modx_mm_product_size where product_id = p.id order by size_id asc limit 1) AND city_id = '.$city_query.') > 0
                        , 
                            (select sale_price from modx_mm_size2city where size_id = 
                                (select id from modx_mm_product_size s where s.product_id = p.id 
                                    and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                                order by s.size_id asc limit 1)
                                AND
                                city_id = '.$city_query.'
                            limit 1)
                        ,
                        (select sale_price from modx_mm_product_size where product_id = p.id order by size_id asc limit 1)
                    )
                <= '.$this->model->num($_GET['price_max']).')';
        }

        if (isset($_GET['search'])) {
            if (mb_strlen($_GET["search"],'UTF-8') < 2) {
                $result = array('pagination' => '','catalogItems' => '<div class="message">Введите поисковое слово содержащее более 2 символов</div>', 'filterBlock' => '', 'sortBlock' => $this->rebuildSortBlock());
                return $result;
            }

            $search_query = array();
            $search_query[] = 'p.name LIKE "%'.$this->db->escape($_GET['search']).'%"';
            $search_query[] = 'p.sku LIKE "%'.$this->db->escape($_GET['search']).'%"';
            $search_query[] = 'p.content LIKE "%'.$this->db->escape($_GET['search']).'%"';
            $search_query[] = 'p.description LIKE "%'.$this->db->escape($_GET['search']).'%"';
            
            $where[] = '('.implode(' OR ', $search_query).')';
        }
        
        $filter_checkbox = $this->model->getToArray($_GET);

        if (count($filter_checkbox['f']) > 0) {//блок фильтров
            foreach ($filter_checkbox['f'] as $key => $value) {
                $values_ids = array();
                if (count($value) > 0) {
                    for ($i = 0; $i < count($value); $i++) { 
                        if ($value[$i] != 0) {
                            $values_ids[] = $value[$i];
                        }
                    }
                }
                if (count($values_ids) > 0) {
                    $f[] = ' 0 < (select count(*) from modx_mm_fv2p_link where value_id in ('.implode(',', $values_ids).') and product_id = p.id)';
                }
            }
        }
        
        if (count($filter_checkbox['size']) > 0) {//блок размеров
            foreach ($filter_checkbox['size'] as $key => $value) {
                if ($value != '') {
                    $f_size[] = ' 0 < (select count(*) from modx_mm_product_size where size_id in ('.intval($value).') and product_id = p.id)';
                }
            }
        }

        $size_query = count($f_size) > 0 ? 'AND ('.implode(' OR ', $f_size).')' : '';
        $filter_query = count($f) > 0 ? 'AND ('.implode(' AND ', $f).')' : '';

        if (count($f) > 0) {
            $where[] = 'p.id in ( (select DISTINCT(product_id) from modx_mm_p2c_link where modx_id = '.$this->modx->documentIdentifier.') ) '.$size_query.' '.$filter_query;
        } elseif(!isset($_GET['search'])) {
            $where[] = 'p.id in ( (select product_id from modx_mm_p2c_link where modx_id = '.$this->modx->documentIdentifier.') ) '.$size_query.' '.$filter_query;
        }

        $product_where = (count($where) > 0 ? ' AND '.implode(' AND ', $where) : '').$where_price;

        $query = $this->getProducts($product_where, $page, $sort, $display);

        $total_sql = $this->db->query('SELECT FOUND_ROWS() AS cnt');
        $total = $this->db->getRow($total_sql);
        $this->modx->documentObject['total'] = $total['cnt'];

        $all_products = $this->getProductsToAmount();
        $prices = array();

        while ($row = $this->db->getRow($all_products)) {
            // if ($row['size_amount'] == 0) {
            //     continue;
            // }

            if ($row['sale_price'] == '') {
                $row['price'] = $row['base_sale_price'];
            }

            $prices[] = $row['sale_price'];
        }

        $additional['price_min'] = count($prices) > 0 ? (int)min($prices) : 0;
        $additional['price_max'] = count($prices) > 0 ? (int)max($prices) : 0;

        $product_ids = array();

        if ($this->db->getRecordCount($query) > 0) {
            $items = '';
            while ($row = $this->db->getRow($query)) {
                // if ($row['size_amount'] == 0) {
                //     continue;
                // }
                
                $product_ids[] = $row['id'];
                $status = array();
                $status = $this->getXml('product', 'status/item/value[.="'.$row['status'].'"]/parent::*');
                $row['status_class'] = $status[0]['class'];
                $row['status_name'] = $status[0]['label'];

                if ($row['price'] == '') {
                    $row['price'] = $row['base_price'];
                }
                
                if ($row['sale_price'] == '') {
                    $row['sale_price'] = $row['base_sale_price'];
                }
				
				$row = $this->categoryDiscount($row, $row['modx_id'], $row['price'], $row['sale_price']);
				
                $items .= $this->modx->parseChunk('tpl_catalogProductItem', $row,'[+','+]');   
            }
            
        } else {
           $items = '<div class="message">Нет продуктов по запросу</div>';
        }
        
        $pagination = $this->model->paginationBuild($total['cnt'], $page, $display);

        $result = array('pagination' => $pagination,'catalogItems' => $items, 'filterBlock' => $this->rebuildFilterBlock($additional, $where), 'sortBlock' => $this->rebuildSortBlock());
        return $result;
    }
	
	public function categoryDiscount($array, $modx_id, $old_price, $sale_price, $ajax = false)
    {			
		if(!empty($array) AND !empty($modx_id) AND $array['fix_price'] != true) { // AND !empty($sale_price)
            $category_discount = $this->modx->getTemplateVarOutput('tv_percent', $modx_id);
            $category_discount = (int)$category_discount['tv_percent'];
    
            if(!empty($category_discount) AND is_int($category_discount) AND $category_discount != 0){
                if($ajax == true){
					$sale_price_new = (!empty($old_price)) ? $old_price : $sale_price;
                    $array['sale_price'] = $old_price;                  
                    $array['price'] = $sale_price_new - round(($sale_price_new / 100) * $category_discount);
                }else{
                    $array['category_discount'] = $category_discount;
                    $array['price'] = (!empty($old_price)) ? $old_price : $sale_price;
                    $array['base_price'] = $sale_price;
                    $array['status'] = 3;
                    $array['sale_price'] = $sale_price - round(($sale_price / 100) * $category_discount);
                }
                
            }    
            return $array;
        }
        return $array;      
    }
    
    public function getProductsToAmount() 
    {
        $city_query = $this->getDefaultCityFromQuery();

        $where = 'AND p.id in ( (select product_id from modx_mm_p2c_link where modx_id = '.$this->modx->documentIdentifier.') )';

        $price_query = '
            (select sale_price from modx_mm_size2city where size_id = 
                (select id from modx_mm_product_size s where s.product_id = p.id 
                    and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                order by s.size_id asc limit 1)
                AND
                city_id = '.$city_query.' 
             limit 1) AS sale_price,
            (select sale_price from modx_mm_product_size where product_id = p.id order by size_id asc limit 1) AS base_sale_price
        ';

        $sql = 'SELECT   
                    p.id,
                    (select SUM(amount) from modx_mm_product_size where product_id = p.id) as size_amount,
                    '.$price_query.'
                FROM modx_mm_products p
                WHERE p.published = 1 '.$where;
        $query = $this->db->query($sql); 
        return $query; 
    }

    public function getProducts($where, $page, $sort, $display) 
    {
        $city_query = $this->getDefaultCityFromQuery();

        $price_query = '
            (select price from modx_mm_size2city where size_id = 
                (select id from modx_mm_product_size s where s.product_id = p.id 
                    and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                order by s.size_id asc limit 1)
                AND
                city_id = '.$city_query.' 
             limit 1) AS price,
            (select sale_price from modx_mm_size2city where size_id = 
                (select id from modx_mm_product_size s where s.product_id = p.id 
                    and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                order by s.size_id asc limit 1)
                AND
                city_id = '.$city_query.'  
             limit 1) AS sale_price,
            (select price from modx_mm_product_size where product_id = p.id order by size_id asc limit 1) AS base_price,
            (select sale_price from modx_mm_product_size where product_id = p.id order by size_id asc limit 1) AS base_sale_price,
        ';

        $sql = 'SELECT SQL_CALC_FOUND_ROWS  
                    p.id,
                    p.name,
                    p.position,
                    p.description,
                    p.introtext,
                    p.content,
                    p.alias,
                    p.published,
                    p.to_slider,
                    p.created_at,
                    p.status,
                    p.sku,
                    p.image,
                    p.amount,
					p.fix_price,
                    p.searchable,					
                    p.additional,
                    p.video,
                    p.presence,
                    p.material,
                    (select SUM(amount) from modx_mm_product_size where product_id = p.id) as size_amount,
                    '.$price_query.'
                    (select modx_id from modx_mm_p2c_link where product_id = p.id limit 1) as modx_id
                FROM modx_mm_products p
                WHERE p.published = 1 '.$where.'
                ORDER BY '.$sort.'
                LIMIT '.abs(intval($display * $page - $display)).', '.abs(intval($display));
                //var_dump($sql);die;
        $query = $this->db->query($sql); 
        return $query;  
    }
    
    public function rebuildSortBlock($tpl = 'catalogSortBlock') 
    {
        $status_arr = $this->getXml('product', 'status/item');
        $catalog_view_arr = $this->getXml('product', 'catalog_view/item');
        
        $block = array();
                
        foreach ($status_arr as $key => $value) {
            if ($_GET['status'] == $value['value']) {
                $value['selected'] = 'selected';  
            } else {
                $value['selected'] = '';
            }
            
            $block['status_select'] .= $this->modx->parseChunk('option', $value,'[+','+]');
        }
        
        foreach ($catalog_view_arr as $key => $value) {
            if ($_GET['total'] == $value['value']) {
                $value['active'] = 'active';  
            } else {
                $value['active'] = '';
            }

            unset($_GET['page']);

            $value['url'] = $this->modx->config['base_url'].$_REQUEST['q'].'?'.http_build_query(array_merge($_GET,array('total' => $value['value'])));
            
            $block['total_select'] .= $this->modx->parseChunk('link', $value,'[+','+]');
        }
        
        $result = $this->modx->parseChunk($tpl, $block,'[+','+]');
        
        return $result;
    }
    
    public function rebuildFilterBlock($additional = null, $where = array()) 
    {
        $filter_checkbox = $this->model->getToArray($_GET);

        $where_count = '';
        $f_result = '';

        $where_count[] = 'product_id in ( (select DISTINCT(product_id) from modx_mm_p2c_link where modx_id = '.$this->modx->documentIdentifier.') )';
        
        $sql = "SELECT 
                    f.*
                FROM modx_mm_filters f
                WHERE f.id in (select filter_id from modx_mm_f2c_link where modx_id = ".$this->modx->documentIdentifier.") 
                ORDER BY f.position ASC";
        $query = $this->db->query($sql);
        
        if ($query) {
            while ($filter_value = $this->db->getRow($query)) {//пробег по фильтрам
                $f_items = '';
                $sql_value = "SELECT 
                        fv.*,
                        (SELECT count(*) FROM modx_mm_fv2p_link WHERE value_id = fv.id AND product_id IN (
                            SELECT p.id FROM modx_mm_products p WHERE p.published = 1 AND ".implode(' AND ', $where_count).") 
                        ) as product_count 
                    FROM modx_mm_filters_value fv
                    WHERE fv.filter_id = ".$filter_value['id']." 
                    ORDER BY fv.position ASC";
                $query_value = $this->db->query($sql_value);

                while ($value = $this->db->getRow($query_value)) {
                    if ($value['product_count'] > 0) {
                        if ($filter_checkbox != null) {
                            if (array_search($value['id'], (array)$filter_checkbox['f'][$value['filter_id']]) !== false) {
                                $value['checked'] = 'checked';
                            } else {
                                $value['checked'] = '';
                            }
                        }
                        
                        $value['filter_type'] = $filter_value['type'];
                        
                        $f_items .= $this->modx->parseChunk('tpl_catalogFilterBlockItem', $value,'[+','+]');
                    }
                }
                
                if ($f_items != '') {
                    $f_result .= $this->modx->parseChunk('tpl_catalogFilterBlock', array('filter_name' => $filter_value['name'], 'filter_checkboxItem' => $f_items),'[+','+]');
                }
            }
        }
        
        //блок размеров
        $sql = "SELECT 
                    DISTINCT(ps.size_id) 
                FROM modx_mm_product_size ps 
                JOIN modx_mm_products p ON p.id = ps.product_id 
                WHERE ".implode(' AND ', $where_count)." AND p.published = 1 
                ORDER BY ps.size ASC";//ps.size_id
        $query = $this->db->query($sql);

        $s_items = '';
        $s_result = '';

        if ($query) {
            while ($row = $this->db->getRow($query)) {
                if ($filter_checkbox != null) {
                    if (array_search($row['size_id'], (array)$filter_checkbox['size']) !== false) {
                        $row['checked'] = 'checked';
                    } else {
                        $row['checked'] = '';
                    }
                }
                
                $size = $this->getXml('filter', 'size/item/value[.="'.$row['size_id'].'"]/parent::*');
                $row['name'] = $size[0]['label'];

                $s_items .= $this->modx->parseChunk('tpl_catalogSizeBlockItem', $row,'[+','+]');
            }
            
            if ($s_items == '') {
                $s_items = 'Размеров нет';
            }
            
            $s_result = $this->modx->parseChunk('tpl_catalogSizeBlock', array('size_checkboxItem' => $s_items),'[+','+]');
        }

        if ($additional['price_min'] == 0 && $additional['price_max'] == 0) {
            return $s_result.$f_result;
        }

        //блок цены
        if (isset($additional['price_min']) && isset($additional['price_max'])) {
            $additional['cur_price_min'] = intval($_GET['price_min']) == 0 ? $additional['price_min'] : intval($_GET['price_min']);
            $additional['cur_price_max'] = intval($_GET['price_max']) == 0 ? $additional['price_max'] : intval($_GET['price_max']);
            $p_result = $this->modx->parseChunk('tpl_catalogPriceBlock', $additional,'[+','+]');
        }
        
        return $p_result.$s_result.$f_result;
    }
    
    public function getXml($xml, $path) 
    {
        $file = 'assets/modules/modmanager/Core/Settings/'.$xml.'.xml';
        $this->xml = simplexml_load_string(file_get_contents($file), 'SimpleXMLElement', LIBXML_NOCDATA);

        $node = $this->xml->xpath($path);
        return json_decode(json_encode($node), 1);
    }
    
    public function getProductReviews($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_review row 
                WHERE product_id = ".intval($id)." AND status = 2 
                ORDER BY created_at DESC ";
        $query = $this->db->query($sql);
        return $query;
    }
    
    public function getProductSizes($id) 
    {
        $city_query = $this->getDefaultCityFromQuery();

        $price_query = '
            (select price from modx_mm_size2city where size_id = row.id 
                AND
                city_id = '.$city_query.' 
            limit 1) AS price,
            row.price AS base_price,
            (select sale_price from modx_mm_size2city where size_id = row.id 
                AND
                city_id = '.$city_query.' 
            limit 1) AS sale_price,
            row.sale_price AS base_sale_price
        ';

        $sql = "SELECT 
                    row.id,
                    row.size_id,
                    row.size,
                    row.amount,
                    row.product_id,
                    ".$price_query."
                FROM modx_mm_product_size row 
                WHERE row.product_id = ".intval($id)." AND (SELECT published FROM modx_mm_products WHERE id = row.product_id LIMIT 1) > 0 
                ORDER BY size_id ASC";//size_id
        $query = $this->db->query($sql);
		return $query;
    }

    public function getProductSizesAvailable($id) 
    {
        $sql = "SELECT count(*) as cnt FROM modx_mm_product_size row WHERE product_id = ".intval($id);
        $result =$this->db->getRow($query);
        return $result['cnt'];
    }
    
    public function getProductSizeAmount($size_id, $product_id) 
    {
        $sql = "SELECT 
                    row.amount  
                FROM modx_mm_product_size row 
                WHERE size_id = ".intval($size_id)." AND product_id = ".intval($product_id)."";
        $query = $this->db->query($sql);
        return $this->db->getRow($query);
    }
    
    public function getProductSize($id) 
    {
        $city_query = $this->getDefaultCityFromQuery();

        $price_query = '
            CASE 
                WHEN (select count(*) from modx_mm_size2city where size_id = row.id) > 0
                    THEN (select price from modx_mm_size2city where size_id = row.id 
                            AND
                            city_id = '.$city_query.' 
                         limit 1)
                ELSE row.price
            END AS price,
            CASE 
                WHEN (select count(*) from modx_mm_size2city where size_id = row.id) > 0
                    THEN (select sale_price from modx_mm_size2city where size_id = row.id 
                            AND
                            city_id = '.$city_query.' 
                         limit 1)
                ELSE row.sale_price
            END AS sale_price
        ';

        $sql = "SELECT 
                    row.id,
                    row.size_id,
                    row.size,
                    row.amount,
                    row.product_id,
                    ".$price_query." 
                FROM modx_mm_product_size row 
                WHERE id = ".intval($id);
        $query = $this->db->query($sql);
        return $this->db->getRow($query);
    }
    
    public function getProductInfo($id) 
    {
        $city_query = $this->getDefaultCityFromQuery();

        $price_query = '
            CASE 
                WHEN (select count(*) from modx_mm_size2city where size_id in (select id from modx_mm_product_size where product_id = p.id) AND city_id = '.$city_query.' ) > 0
                    THEN (select price from modx_mm_size2city where size_id = 
                            (select id from modx_mm_product_size s where s.product_id = p.id 
                                and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                            order by s.size_id asc limit 1)
                            AND
                            city_id = '.$city_query.' 
                         limit 1)
                ELSE (select price from modx_mm_product_size where product_id = p.id order by size_id asc limit 1)
            END AS price,
            CASE 
                WHEN (select count(*) from modx_mm_size2city where size_id in (select id from modx_mm_product_size where product_id = p.id) AND city_id = '.$city_query.' ) > 0
                    THEN (select sale_price from modx_mm_size2city where size_id = 
                            (select id from modx_mm_product_size s where s.product_id = p.id 
                                and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                            order by s.size_id asc limit 1)
                            AND
                            city_id = '.$city_query.' 
                         limit 1)
                ELSE (select sale_price from modx_mm_product_size where product_id = p.id order by size_id asc limit 1)
            END AS sale_price,
        ';

        $sql = 'SELECT 
                    p.id,
                    p.name,
                    p.position,
                    p.description,
                    p.introtext,
                    p.content,
                    p.alias,
                    p.published,
                    p.to_slider,
                    p.created_at,
                    p.status,
                    p.sku,
                    p.image,
                    p.amount,
					p.fix_price,
                    p.searchable,
                    p.additional,
                    p.video,
                    p.presence,
                    p.material,
                    '.$price_query.'
                    m.title as meta_title,
                    m.description as meta_description,
                    m.keywords as meta_keywords,
                    m.robots as meta_robots,
                    m.canonical as meta_canonical,
                    (select modx_id from modx_mm_p2c_link where product_id = p.id limit 1) as modx_id,
                    (select group_id from modx_mm_p2g_link where product_id = p.id limit 1) as group_id
                FROM modx_mm_products p
                LEFT JOIN modx_mm_metadata m ON m.page_id = p.id
                WHERE p.published = 1 AND p.id = '.intval($id);
        $query = $this->db->query($sql);
        return $this->db->getRow($query);
    }
    
    public function getProductInGroup($group_id) 
    {
        $city_query = $this->getDefaultCityFromQuery();

        $price_query = '
            (select price from modx_mm_size2city where size_id = 
                (select id from modx_mm_product_size s where s.product_id = row.id 
                    and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                order by s.size_id asc limit 1)
                AND
                city_id = '.$city_query.' 
             limit 1) AS price,
            (select price from modx_mm_product_size where product_id = row.id order by size_id asc limit 1) AS base_price,
            (select sale_price from modx_mm_size2city where size_id = 
                (select id from modx_mm_product_size s where s.product_id = row.id 
                    and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                order by s.size_id asc limit 1)
                AND
                city_id = '.$city_query.' 
             limit 1) AS sale_price,
            (select sale_price from modx_mm_product_size where product_id = row.id order by size_id asc limit 1) AS base_sale_price,
        ';

        $sql = "SELECT 
                    row.id, 
                    row.name, 
                    row.alias,
                    ".$price_query."
                    (SELECT modx_id FROM modx_mm_p2c_link WHERE product_id = row.id limit 1) as modx_id, 
                    (SELECT param FROM modx_mm_filters_value WHERE filter_id = 
                            (SELECT id FROM modx_mm_filters WHERE type = 3 limit 1)
                        AND id in (SELECT value_id FROM modx_mm_fv2p_link WHERE product_id = row.id)
                    ) as color
                FROM modx_mm_products row 
                WHERE id in (SELECT product_id FROM modx_mm_p2g_link WHERE group_id = ".intval($group_id).") AND published = 1 
                ORDER BY name DESC";
        $query = $this->db->query($sql);
        return $query;
    }

    public function getNvData() 
    {
		if (!file_exists($modx->config['base_path'].'assets/site/nv-citys.txt') || date ("Y-m-d", filemtime($modx->config['base_path'].'assets/site/nv-citys.txt')) != date ("Y-m-d")) {
			// 
			// $np_content = file_get_contents('http://novaposhta.ua/shop/office/getJsonWarehouseList/');
			$np_content = file_get_contents('https://novaposhta.ua/shop/office/getJsonWarehouseList/');
            
            $nv_array = array();
            
            $exp = json_decode($np_content, true);

            foreach ($exp['response'] as $key => $value) {
				/*$nv_array['citys'][$value['city_id']]['id'] = $value['city_id'];
                $nv_array['citys'][$value['city_id']]['name'] = $value['cityRu'];
                
                $nv_array['stocks'][$key]['id'] = $value['wareId'];
                $nv_array['stocks'][$key]['city_id'] = $value['city_id'];
                $nv_array['stocks'][$key]['name'] = $value['addressRu'];
				*/
				if (!is_string($value['cityRu']))  continue;
				$nv_array['citys'][$value['city_ref']]['id'] = $value['city_ref'];
                $nv_array['citys'][$value['city_ref']]['name'] = $value['cityRu'];
                
                $nv_array['stocks'][$key]['id'] = $value['number'];
                $nv_array['stocks'][$key]['city_ref'] = $value['city_ref'];
                $nv_array['stocks'][$key]['name'] = $value['addressRu'];
            }
			//echo '<pre>';
			//print_r($nv_array['citys']);
			//die();
			
            function cust_sort($a,$b) {				
				//if (is_string($a['name']) && is_string($b['name'])) {}
				return strtolower($a['name']) > strtolower($b['name']);
            }
			
			usort($nv_array['citys'], 'cust_sort');
			
			/*if (is_array($nv_array['citys'][0]['name'])) {
				unset($nv_array['citys'][0]);
			}*/          
			
            $nv_citys = json_encode($nv_array);
            file_put_contents($modx->config['base_path'].'assets/site/nv-citys.txt', $nv_citys);
        } else {			
            $np_content = file_get_contents($modx->config['base_path'].'assets/site/nv-citys.txt');
            $nv_array = json_decode($np_content, true);
        }

        return $nv_array ;
    }
    
    public function getProductAdditionals($ids) 
    {
        $city_query = $this->getDefaultCityFromQuery();

        $price_query = '
            (select price from modx_mm_size2city where size_id = 
                (select id from modx_mm_product_size s where s.product_id = row.id 
                    and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                order by s.size_id asc limit 1)
                AND
                city_id = '.$city_query.' 
            limit 1) AS price,
            (select price from modx_mm_product_size where product_id = row.id order by size_id asc limit 1) AS base_price,
            (select sale_price from modx_mm_size2city where size_id = 
                (select id from modx_mm_product_size s where s.product_id = row.id 
                    and (select count(*) from modx_mm_size2city where city_id = '.$city_query.' AND size_id = s.id) > 0 
                order by s.size_id asc limit 1)
                AND
                city_id = '.$city_query.' 
            limit 1) AS sale_price,
            (select sale_price from modx_mm_product_size where product_id = row.id order by size_id asc limit 1) AS base_sale_price,
        ';

        $sql = "SELECT 
                    row.id, 
                    row.name, 
                    row.introtext, 
                    row.description,
                    row.status,
                    row.image,
                    row.alias,
                    (select count(*) from modx_mm_product_size where product_id = row.id) as size_count,
                    ".$price_query."
                    (SELECT modx_id FROM modx_mm_p2c_link WHERE product_id = row.id limit 1) as modx_id, 
                    (SELECT param FROM modx_mm_filters_value WHERE filter_id = 
                            (SELECT id FROM modx_mm_filters WHERE type = 3 limit 1)
                        AND id in (SELECT value_id FROM modx_mm_fv2p_link WHERE product_id = row.id)
                    ) as color
                FROM modx_mm_products row 
                WHERE row.id in (".$this->db->escape($ids).") AND row.published = 1 AND (select SUM(amount) from modx_mm_product_size where product_id = row.id) > 0
                ORDER BY name DESC";
        $query = $this->db->query($sql);
        return $query;
    }
    
    public function getProductImages($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_images row 
                WHERE product_id = ".intval($id)." AND flag = 0 
                ORDER BY row.position ASC";
        $query = $this->db->query($sql);
        return $query;
    }
    
    public function getUserOrders() 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_shop row 
                WHERE user_id = ".intval($this->modx->getLoginUserID())."  
                ORDER BY row.created_at DESC";
        $query = $this->db->query($sql);
        return $query;
    }
    
    public function getOrder($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_shop row 
                WHERE id = ".intval($id);
        $query = $this->db->query($sql);
        return $this->db->getRow($query);
    }
    
    public function gettFittingShop($city_id, $shop_id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_site_tmplvar_contentvalues row 
                WHERE contentid = ".intval($city_id)." AND tmplvarid = 21 
                ORDER BY id DESC";
        $query = $this->db->query($sql);
        
        $row = '';
        $result = '';
        
        if ($query) {
            $row = $this->db->getRow($query);
            $result = json_decode($row['value'], true);
            return $result["fieldValue"][$shop_id-1];
        }
        
        return array();
    }

    public function getPriceBySizeAndCity($fields, $city_id) 
    {
        $sql = "SELECT 
                    * 
                FROM modx_mm_size2city  
                WHERE size_id = (SELECT id FROM modx_mm_product_size WHERE size_id = '".$fields['size_id']."' AND size = '".$fields['size']."' LIMIT 1) AND city_id = ".intval($city_id)."
                LIMIT 1";
        $query = $this->db->query($sql);
        return $this->db->getRow($query);
    }

    public function getFittingCitys() 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_site_content row 
                WHERE parent = 10 AND deleted = 0 AND published = 1 
                ORDER BY pagetitle ASC";
        $query = $this->db->query($sql);
        return $query;
    }
    
    public function getAllCitys() {
        $sql = "SELECT * FROM modx_mm_city";
        $query = $this->db->query($sql);
        return $query;
    }

    public function getCity($id) {
        $sql = "SELECT * FROM modx_mm_city WHERE id = ".intval($id);
        $query = $this->db->query($sql);
        return $this->db->getRow($query);
    }

    public function getCityByNameOrAlias($fields) {
        $sql = "SELECT * FROM modx_mm_city WHERE LOWER(name) = '".$fields['name']."' OR LOWER(alias) = '".$fields['alias']."'";
        $query = $this->db->query($sql);
        return $this->db->getRow($query);
    }
    
    public function getProductTvVars($modx_id) 
    {
        $sql = "SELECT 
                    row.value as `tv_care`, 
                    (SELECT value FROM modx_site_tmplvar_contentvalues WHERE contentid  = 2 AND tmplvarid = 11 ) as `tv_delivery`,
                    (SELECT value FROM modx_site_tmplvar_contentvalues WHERE contentid  = 2 AND tmplvarid = 12 ) as `tv_exchange`,
                    (SELECT value FROM modx_site_tmplvar_contentvalues WHERE contentid  = 2 AND tmplvarid = 13 ) as `tv_return`
                FROM modx_site_tmplvar_contentvalues row 
                WHERE contentid  = ".intval($modx_id)." AND tmplvarid = 14";
        $query = $this->db->query($sql);
        return $this->db->getRow($query);
    }
    
    public function addReview($product_id, $fields)
    {
        $sql = "INSERT INTO modx_mm_review (
                    name,
                    email,
                    message,
                    created_at,
                    status,
                    user_id,
                    product_id
                ) VALUES (
                    '".$this->db->escape($fields['name'])."',
                    '".$this->db->escape($fields['email'])."',
                    '".$this->db->escape($fields['massage'])."',
                    '".date('Y-m-d H:i:s', time())."',
                    1,
                    ".$this->modx->getLoginUserID().",
                    ".intval($product_id)."
                )";
		$this->db->query($sql);
		return '';
    }
    
    public function addReportProductToUser($fields = array())
    {
        $sql = "SELECT count(*) as cnt FROM modx_mm_availability WHERE status != 1 AND email = '".$this->db->escape($fields['email'])."' AND product_id = ".intval($_SESSION['product']['id']);
        $query = $this->db->query($sql);
        $result = $this->db->getRow($query);
        
        if ($result['cnt'] > 0) {
            return false;
        }
        
        $sql = "INSERT INTO modx_mm_availability (
                    product_id,
                    user_id,
                    size_id,
                    email,
                    status,
                    created_at
                ) VALUES (
                    ".intval($_SESSION['product']['id']).",
                    '".$this->modx->getLoginUserID()."',
                    ".intval($fields['size_id']).",
                    '".$this->db->escape($fields['email'])."',
                    '0',
                    '". date('Y-m-d H:i:s', time())."'
                )";
		$this->db->query($sql);
		return true;
    }
    
    public function addToCart($data) 
    {
        if ($data['product_id'] != '') {
            $order = array();
            $new_order = array();
            
            $product = $this->getProductInfo($data['product_id']);
            $order['id']    = $data['product_id'];
			$order['modx_id'] = $product['modx_id'];
			
			$percent = '';
			$status = $this->db->query("SELECT fix_price FROM modx_mm_products WHERE id = ".$data['product_id']." LIMIT 1");	
			$status = $this->db->getRow($status);
			if ($status['fix_price'] != true) {
				//получаем % скидки с категории товаров modx_id - категория товаров
				 $sql = "SELECT value FROM modx_site_tmplvar_contentvalues WHERE tmplvarid = 27 AND contentid = '".$order['modx_id']."' ";
				 $query = $this->db->query($sql);
				 
				
				
				if($query){
					$result = $this->db->getRow($query);
					$percent = $result['value'];	
				}
			}

            if ($data['size'] != '') {
                $size = $this->getProductSize($data['size']);
                $size_xml = $this->getXml('filter', 'size/item/value[.="'.$size['size_id'].'"]/parent::*');
                $order['size_name']  = $size_xml[0]['label'];
                $order['size_id']  = $size_xml[0]['value'];
                $order['size']  = $size['size'];
				
				
				if(!empty($percent)){
					
					//получаем процент от суммы
					$percent_sum = $size['sale_price'] * $percent / 100;
					$order['original_price'] = round($size['sale_price']);
					$order['percent'] = $percent;
					$order['price'] = round($size['sale_price'] - $percent_sum);
				
				}else{
                $order['price'] = $size['sale_price'];
				}	
            }
            
            $order['sku'] = $product['sku'];
            $order['name'] = $product['name'];
            $order['alias'] = $product['alias'];
            $order['image'] = $product['image'];
            $order['amount'] = 1;
            

            $order['total_price'] = $order['price'];

            $flag = false;
            if (!isset($_SESSION['order'])) {
                $_SESSION['order'][] = $order;
            } else {
                $tmp_array_product = array();
                $tmp_array_order = array();
                $new_order = $_SESSION['order'];

                $tmp_array_order['id'] = $order['id'];
                $tmp_array_order['size'] = $order['size'];

                foreach ($_SESSION['order'] as $key => $value) {
                    $tmp_array_product['id'] = $value['id'];
                    $tmp_array_product['size'] = $value['size'];
                    $diff = array_diff_assoc($tmp_array_product, $tmp_array_order);
                    if (count($diff) == 0) {
                        $_SESSION['order'][$key]['amount'] = $value['amount'] + $order['amount'];
                        $_SESSION['order'][$key]['total_price']    = $value['price'] * $value['amount'];
                        $flag = true;
                    }
                }
                if ($flag == false) {
                    $_SESSION['order'][] = $order;
                }
            }
            
            $arr = array(
                'popupCart' => $this->rebuildCart(),
                'headCart' => $this->rebuildHeadCart()
            );
            return $arr;
        }
    }
    
    public function rebuildHeadCart() 
    {
        $total_items = 0;
        $price_total = 0;
        if (!isset($_SESSION['order']) || count($_SESSION['order']) < 1) {
            $result = $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('headCartBlockEmpty', array(),'[+','+]')));
        } else {
            $_SESSION['order'] = array_values($_SESSION['order']);
            foreach ($_SESSION['order'] as $key => $value) {
                $total_items += $value['amount'];
                $price_total += $value['price'] * $value['amount'];
            }
            $arr = array(
                'price_total' => $price_total,
                'total_items' => $total_items
            );
            $result = $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('headCartBlock', $arr,'[+','+]')));
        }
        return $result;
    }
    
    public function rebuildOrderPrices() 
    {
        $total_price = 0;
        if (isset($_SESSION['order'])) {
            foreach ($_SESSION['order'] as $key => $value) {
                $prices = $this->getPriceBySizeAndCity($value, $this->getCityCookieId());
                $_SESSION['order'][$key]['price'] = $prices['sale_price'];
                $_SESSION['order'][$key]['total_price'] = $_SESSION['order'][$key]['price'] * $value['amount'];
                $total_price += $_SESSION['order'][$key]['total_price'] ;
            }
        }

        return array('total_price' => $total_price);
    }
    
    public function rebuildOrderCart() 
    {
        $products = '';
        $count = 1;
        $total_summ = 0;
                
        if (!isset($_SESSION['order']) || count($_SESSION['order']) < 1) {
            $products = '';
        } else {
            
            $_SESSION['order'] = array_values($_SESSION['order']);
            foreach($_SESSION['order'] as $key => $value) {
                $total_summ += $value['price'] * $value['amount'];
                $products .=  $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('tpl_orderProductItem', $value,'[+','+]')));
                $count++;
            }
        }
        return array('count' => $count, 'total_summ' => $total_summ, 'products' => $products);
    }

    public function rebuildCart() 
    {
        $total_price = 0;
        $cart_items  = '';
        if (count($_SESSION['order']) > 0) {
            foreach ($_SESSION['order'] as $key => $value) {
                $value['total_price'] = round($value['price'] * $value['amount']);
                $value['price'] = round($value['price']);
                $value['amount'] = ($value['amount'] != 0 && $value['amount'] != '' ? $value['amount'] : 1);
                $cart_items  .= $this->modx->parseChunk('tpl_cartItem', $value,'[+','+]');
                $total_price += $value['total_price'];
            }
        }

        $arr = array(
            'total_price' => $total_price,
            'items'       => $cart_items
        );
        $result = $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('cartBlock', $arr,'[+','+]')));
        return $result;
    }
    
    public function subscribe($fields)
    {
        $exist_email = $this->db->query("SELECT * FROM modx_mm_mailer_user WHERE email='".$this->db->escape($fields['email'])."' ");
        if($this->db->getRow($exist_email) == false) {
            $sql = "INSERT INTO modx_mm_mailer_user (
                        name, 
                        email, 
                        user_id, 
                        created_at,
                        status
                    ) VALUES (
                        '".$_SESSION['webuser']['fullname']."',
                        '".$this->db->escape($fields['email'])."',
                        ".$this->modx->getLoginUserID().",
                        '".date('Y-m-d H:i:s', time())."',
                        1
                    )";
            $this->db->query($sql);

            return true;
        }
        
        return false;
    }
    
    public function createOrder($fields = null) 
    {
        if (count($_SESSION['order']) < 1) {
            return false;
        }
        
        if ($fields != null && count($_SESSION['order']) > 0) {
            $info = array();
            $user_id = $this->modx->getLoginUserID();
            $user_info = $this->modx->getWebUserInfo($user_id);

            if (!$this->modx->getLoginUserID()) {
                $fields['city'] = $this->getCityCookieId();
                $result = $this->registration($fields);
                $this->authorization('login', $result);
                $user_id = $this->modx->getLoginUserID();
                $user_info = $this->modx->getWebUserInfo($user_id);
                $info = $user_info;
            } else {
                $info = $fields;
            }

            $city = $this->getCity($user_info["city"]);
            $user_info["city_name"] = $city['name'];

        } else {
            return false;
        }

        if ($fields["delivery_method"] == 3) {
            switch ($fields['address']) {
                case 'new':
                    $fields['info']['street'] = $info["street"];
                    $fields['info']['kv'] = $info["kv"];
                    $fields['info']['house'] = $info["house"];
                    $fields['info']['comment'] = $info["comment"];
                    break;
                default:
                    $decode = json_decode($user_info['comment'], true);
                    $fields['info']['street'] = $decode['address'][(int)$fields['address']]["street"];
                    $fields['info']['kv'] = $decode['address'][(int)$fields['address']]["kv"];
                    $fields['info']['house'] = $decode['address'][(int)$fields['address']]["house"];
                    $fields['info']['comment'] = $decode['address'][(int)$fields['address']]["comment"];
                    break;
            }
        }

        switch ($fields["delivery_method"]) {
            case '1':
                $fields["np_stock"] = '';
                $fields["np_address"] = '';
                break;
            case '2':
                $fields["fitting_city"] = '';
                $fields["fitting_shop"] = '';
                break;
            case '3':
                $fields["np_stock"] = '';
                $fields["np_address"] = '';
                $fields["fitting_city"] = '';
                $fields["fitting_shop"] = '';
                break;
        }

        $fields['info']['name'] = $info["name"] == '' ? $info["fullname"] : $info["name"];
        $fields['info']['email'] = $info["email"];
        $fields['info']['phone'] = $info["phone"];
        $fields['info']['city'] = $user_info["city_name"];
        $fields['info']['city_delivery'] = $info["city_delivery"];

        $order = array();
        $product = '';
        $total_amount = 0;
        
        foreach ($_SESSION['order'] as $key => $value) {
            $product = $this->getProductInfo($value['id']);

            $price = $value['price'];

            $fields['order_price'] += $price * $value['amount'];
            $order_price += $price * $value['amount'];
            $total_amount += $value['amount'];
            $value['total_price'] = $price * $value['amount'];
            $order[$key] = $value;
            $value['key'] = $key;
            $fields['product_items'] .= $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('order_productToMail', $value,'[+','+]')));
			//$fields['invoice_items'] .= $this->modx->parseChunk('invoiceItem', $value,'[+','+]');//счет-фактура
			$fields['invoice_items'] .= $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('invoiceItem', $value,'[+','+]')));//счет-фактура
            $this->db->query("UPDATE modx_mm_products SET buying = buying + 1 WHERE id = ".intval($value['id']));
            $this->db->query("UPDATE modx_mm_product_size SET amount = amount - ".intval($value['amount'])." WHERE size_id = ".intval($value['size_id'])." AND product_id = ".intval($value['id']));
        }
        
        if ($fields["delivery_method"] == 2 && $fields['order_price'] < $this->modx->config['free_delivery']) {
            $fields['delivery_price'] = $this->modx->config['delivery_price'];
            $fields['order_price'] += $this->modx->config['delivery_price'];
        }

        //delivery name
        $delivery_xml = $this->getXml('shop', 'delivery_method/item/value[.="'.$fields["delivery_method"].'"]/parent::*');
        $fields['delivery_name'] = $delivery_xml[0]['label'];
        
        //payment name
        $pay_xml = $this->getXml('shop', 'pay_method/item/value[.="'.$fields["pay_method"].'"]/parent::*');
        $fields['pay_name'] = $pay_xml[0]['label'];
        
        //stock data
        $city_stock = $this->gettFittingShop($fields["city_shop"], $fields["shop"]);
        $fields['city_stock_name'] = $city_stock['name'];
        
        $date = date('Y-m-d H:i:s', time());
        $fields['invoice_end'] = strtotime(date("d.m.Y"))+24*60*60*2;
        $fields['total_amount'] = $total_amount;
        $fields['string_summ'] = $this->model->priceToString($fields['order_price']);
        $fields['user_name'] = $fields['info']['name'] == '' ? $fields['info']['email'] : $fields['info']['name'];
        $fields['total_summ'] = number_format($order_price, 2, '.', '');
        $fields['summ_string_value'] = $this->model->priceToString($fields['order_price']);
        $fields['date'] = date('d.m.Y', strtotime($date));
        $fields['tdate'] = date('d-m-Y', strtotime($date));
        $fields['price_pdv'] = round($order_price / 100 * 20, 2);
        $fields['total_summ_pdv'] = number_format($order_price, 2, '.', '');
        $fields['pdv_string_value'] = $this->model->priceToString($fields['price_pdv']);

        $sql = "INSERT INTO modx_mm_shop (
                    `number`,
                    `created_at`,
                    `order_price`,
                    `delivery_method`,
                    `pay_method`,
                    `np_stock`,
                    `np_address`,
                    `fitting_city`,
                    `fitting_shop`,
                    `delivery_price`,
                    `payment_price`,
                    `orders`,
                    `status`,
                    `user_id`,
                    `transaction`,
                    `comment`,
                    `user_info`
                ) VALUES (
                    '".uniqid()."',
                    '".$date."',
                    ".str_replace(",",".",floatval($fields['order_price'])).",
                    ".intval($fields["delivery_method"]).",
                    ".intval($fields["pay_method"]).",
                    '".$this->db->escape($fields["np_stock"])."',
                    '".$this->db->escape($fields["np_address"])."',
                    ".intval($fields["fitting_city"]).",
                    ".intval($fields["fitting_shop"]).",
                    ".str_replace(",",".",floatval($fields['delivery_price'])).",
                    '0',
                    '".$this->model->json_encode_cyr($order)."',
                    '1',
                    ".intval($user_id).",
                    '',
                    '".$this->db->escape($fields["comment"])."',
                    '".$this->model->json_encode_cyr($fields['info'])."'
                )";
        $this->db->query($sql);
        $id = $this->db->getInsertId();
        
        $fields['id'] = $id;
        $fields['login_url'] = $this->modx->makeUrl(26, '', '#tab4', 'full');

        $mail = $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('mail_order', $fields, '[+','+]'))); 
        $mail_admin = $this->modx->rewriteUrls($this->modx->parseDocumentSource($this->modx->parseChunk('mail_order_admin', $fields, '[+','+]'))); 

        if ($this->modx->config['emailsender'] != '') {
            $explode = explode(',', $this->modx->config['emailsender']);
            foreach ($explode as $key => $value) {
                $this->model->sendMail($value, $this->modx->config['site_name']." - Новый заказ", $mail_admin);
            }
        }
        $this->model->sendMail($fields["email"], $this->modx->config['site_name']." - Новый заказ", $mail);

        switch ($fields["pay_method"]) {
            case '2'://invoice
                $invoice = $this->modx->parseChunk('invoice', $fields,'[+','+]');

                $file = $this->generateOrderPdf($invoice, $id.'_'.$user_id.'_'.date('d-m-Y-H-i-s', strtotime($date)), 'F');

                $this->model->sendMail($fields['email'], "Счет-фактура", $invoice, $file);
                $arr = array(
                    'action' => 'invoice',
                    'html'   => $invoice
                );
                break;
            case '3'://liqpay
                $liqpay_form = $this->liqpayQuery($id, $fields);
                $arr = array(
                    'action' => 'liqpay',
                    'html' => $liqpay_form,
                    'order_id' => $id
                );
                break;
            default:
                $arr = array(
                    'action' => 'pickup',
                    'order_id' => $id
                );
                break;
        }

        unset($_SESSION['order']);
        unset($_SESSION['product']);
        
        return $arr;
    }

    public function generateOrderPdf($html = '', $filename = '', $output = "I") 
    {
        require_once(MODX_BASE_PATH.'assets/modules/modmanager/Core/Includes/Mpdf/mPDF.php');
        $mpdf = new mPDF();

        $filename = $filename == '' ? time().'.pdf' : $filename.'.pdf';

        $mpdf->mPDF('utf-8', 'A4', '8', '', 30, 30, 15, 15, 30, 30);

        $mpdf->list_indent_first_level = 0;
        $mpdf->WriteHTML($html, 2);
        switch ($output) {
            case "D":
                $mpdf->Output(MODX_BASE_PATH.'assets/files/orders/'.$filename, 'D');
            break;
            case "F":
                $mpdf->Output(MODX_BASE_PATH.'assets/files/orders/'.$filename, 'F');
            break;
            default:
                $mpdf->Output($filename, 'I'); 
            break;
        }
        return MODX_BASE_PATH.'assets/files/orders/'.$filename;
    }
    
    public function liqpayQuery($id, $order) 
    {
        require_once(MODX_BASE_PATH.'assets/site/LiqPay.php');
        $liqpay = new LiqPay($this->modx->config['liqpay_merchant'], $this->modx->config['liqpay_sign']);
        $pay_form = $liqpay->getForm(array(
            'order_id'    => $order['id'], 
            'amount'      => $order['order_price'], 
            'currency'    => 'UAH', 
            'description' => "Оплата заказа № ".$id,
            'server_url'  => $this->modx->makeUrl(1, '', '', 'full'),
            'result_url'  => $this->modx->makeUrl(25, '', '&order='.$order['id'], 'full'),
            'pay_way'     => 'card',
        ));
        return $pay_form;
    }
    
    public function checkLiqPay() 
    {
        if (isset($_POST['operation_xml']) && $_POST['operation_xml'] != '') {
            ob_start();
            var_dump($_REQUEST);
            $request = ob_get_contents();
            ob_end_clean();

            $_POST['unixtime'] = time();
            $xml  = base64_decode($_POST['operation_xml']);

            $sig  = $_POST['signature'];
            $sign = base64_encode(sha1($this->modx->config["liqpay_sign"].$xml.$this->modx->config["liqpay_sign"], 1));

            if ($sign == $sig) {
                $simple_xml = simplexml_load_string($xml);

                if ($simple_xml->status == "success") {
                    $sql = "
                        UPDATE `modx_mm_shop` SET
                            status = 7,
                            transaction = '".$simple_xml->transaction_id."'
                        WHERE id  = '".$simple_xml->order_id."'";
                    $this->db->query($sql);
                } else {
                    $sql = "
                        UPDATE `modx_mm_shop` SET
                            status = 6,
                            transaction = '".$simple_xml->transaction_id."'
                        WHERE id  = '".$simple_xml->order_id."'";
                    $this->db->query($sql);
                }
            } 
        }
    }
}
