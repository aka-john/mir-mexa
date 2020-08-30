<?php
require_once(MODX_BASE_PATH.'assets/site/frontEnd.php');
$frontend = new frontEnd($modx);

//Фильтрация XSS
if (isset($_REQUEST)) {
    $_REQUEST = $frontend->model->xssFilter($_REQUEST);
}

$alerts = array(
    'form_reg'        => '<div class="message">Посмотрите подробную информацию о новом профиле в вашем почтовом ящике.</div>',
    'form_forgot'     => '<div class="message">Получите подробную инструкцию по активации нового пароля в вашем почтовом ящике.</div>',
    'form_feedback'   => '<div class="message">Спасибо за обращение!</div>',
    'form_callback'   => '<div class="message">Спасибо за обращение! Вам перезвонят в ближайшее время</div>',
    'form_report'     => '<div class="message">Спасибо за обращение! Когда товар появится в наличии, мы с вами свяжемся.</div>',
    'form_fitting'    => '<div class="message">Спасибо за обращение! Вам перезвонят в ближайшее время</div>',
    'cart_empty'      => '<div class="message">Ваша корзина пуста</div>',
    'form_review'     => '<div class="message">Ваш отзыв успешно отправлен на модерацию</div>',
    'review_error'    => '<div class="message">Комментариев пока нет</div>',
    'form_subscribe'  => '<div class="message">Вы успешно подписались</div>',
    'password_change' => '<div class="message">Пароль успешно изменен.</div>',
    'profile_save'    => '<div class="message">Ваш информационный профиль обновлён.</div>',
    'no_order'        => '<div class="message">Выберите материал</div>',
    'no_products'     => '<div class="message">По Вашему запросу ничего не найдено</div>',
    'cart_add'        => '<div class="message">Товар добавлен в корзину</div>'
);

switch($modx->event->name) { 
    case 'OnWebPageInit': case 'OnPageNotFound': 
        if (isset($_GET['logout'])) {
            unset($_SESSION['webShortname']);
            unset($_SESSION['webFullname']);
            unset($_SESSION['webEmail']);
            unset($_SESSION['webValidated']);
            unset($_SESSION['webInternalKey']);
            unset($_SESSION['webValid']);
            unset($_SESSION['webUser']);
            unset($_SESSION['webFailedlogins']);
            unset($_SESSION['webLastlogin']);
            unset($_SESSION['webnrlogins']);
            unset($_SESSION['webUsrConfigSet']);
            unset($_SESSION['webUserGroupNames']);
            unset($_SESSION['webDocgroups']);
            unset($_SESSION['webuser']);
            setcookie('token', '');
        }

        if ($modx->documentIdentifier == 26 && $_SESSION['webuser']['id'] == '') {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: /");
            die(); 
        }

        include_once (MODX_BASE_PATH.'assets/site/ajax.php');

        $frontend->checkUrlAlias($_GET['q']);//Проверка на URL товара
        
        $frontend->chackAuthCookie();

        break;
    case 'OnLoadWebDocument':
        if (is_array($_SESSION['webuser'])) {
            foreach ($_SESSION['webuser'] as $k => $v) {
                $modx->documentObject['wu_'.$k] = $v;
            }
        }

        $city_confirm = $frontend->getCityConfirmedCookie();

        //есть пост с id города и пользователь не зареган
        if (isset($_POST['unauth_city']) && !$modx->getLoginUserID()) {
            $frontend->setCityCookie((int)$_POST['unauth_city']);
            $city_confirm = $frontend->setCityConfirmedCookie();
            $frontend->rebuildOrderPrices();
        }

        //сброс цены по городу если пользователь зареган
        if ($modx->getLoginUserID() && isset($_POST['unauth_city'])) {
            $frontend->unsetCityCookie();
        }

        //если пользователь не зареган и цены по городу нет то пробуем определить по ip
        if (!$frontend->getCityCookieId() && !$modx->getLoginUserID()) {
            $user_city = $frontend->getUserCityByIp();
            $frontend->setUserCity($user_city);
            $frontend->rebuildOrderPrices();
        }

        $unauth_city = $frontend->getCityCookie();

        $citys = $frontend->getAllCitys();
        $citys_arr = $modx->db->makeArray($citys);
        foreach ($citys_arr as $key_city => $value_city) {
            if ($unauth_city['id'] && $unauth_city['id'] == $value_city['id']) {
                
                $value_city['selected'] = 'selected';
            } else {
                $value_city['selected'] = '';
            }
            
            $value_city['value'] = $value_city['id'];
            $value_city['label'] = $value_city['name'];
            $citys_items .= $modx->parseChunk('option', $value_city,'[+','+]');
        }

        $modx->documentObject['city_list'] = $citys_items;

        $modx->documentObject['unauth_city_id'] = $unauth_city['id'];
        $modx->documentObject['unauth_city_name'] = $unauth_city['name'];
        $modx->documentObject['unauth_city_confirmed'] = $city_confirm;
        
        //Check LiqPay  
        $frontend->checkLiqPay();

        $modx->documentObject['cartHead'] = $frontend->rebuildHeadCart();
        $modx->documentObject['cartBlock'] = $frontend->rebuildCart();

        $modx->documentObject['emailsender'] = $modx->config['emailsender'];
        $modx->documentObject['year'] = date('Y', time());
        $modx->documentObject['referer'] = $_SERVER["HTTP_REFERER"];
        $modx->documentObject['q'] = $_GET['q'];
        
        $seo = $frontend->getSeolink();

        if ($modx->documentObject['template'] == 7 && $seo) {
            $modx->documentObject['meta_block'] = '
                <title>'.$seo['title'].'</title>
                <base href="[(site_url)]" />
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta name="keywords" content="'.$seo['keywords'].'" />
                <meta name="description" content="'.$seo['description'].'" />
                <meta name="robots" content="'.$seo['robots'].'" />
                [*phx:if=`'.$seo['canonical'].'`:ne=``:then=`<link rel="canonical" href="'.$seo['canonical'].'" />`*]';
        } else {
            $modx->documentObject['meta_block'] = '
                <title>[*phx:if=`[*phx:get=`page`*]`:is=`0`:or:is=``:then=``:else=`Страница [*phx:get=`page`*] | `*][*phx:if=`[*tv_seo_title*]`:ne=``:then=`[*tv_seo_title*]`:else=`[*pagetitle*]`*] | [(site_name)]</title>
                <base href="[(site_url)]" />
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta name="keywords" content="[*tv_seo_keywords*]" />
                <meta name="description" content="[*tv_seo_description*]" />
                <meta name="robots" content="[*tv_seo_robots*]" />
                [*phx:if=`[*tv_seo_canonical*]`:ne=``:then=`<link rel="canonical" href="[*tv_seo_canonical*]" />`*]';
        }

        if ($modx->documentObject['template'] == 5) {
            $product = $frontend->getProductInfo($_SESSION['product']['id']);
            $modx->documentObject['breadcrumbs'] = $modx->runSnippet('Breadcrumbs', array(
                'id'          => $product['modx_id'],
                'lastLink'    => true,
                'templateSet' => 'defaultList',
                'outerClass'  => 'breadcrumbs'
            ));
        } else {
            $modx->documentObject['breadcrumbs'] = $modx->runSnippet('Breadcrumbs', array(
                'templateSet'     => 'defaultList',
                'outerClass'      => 'breadcrumbs',
                'respectHidemenu' => '1'
            ));
        }

        /*==========Выборка по ID==========*/

        switch ($modx->documentObject['id']) {
            case '7'://Поиск
                $display = $_GET['total'] == '' ? 12 : intval($_GET['total']);
                $page = $_GET['page'] == 0 ? 1 : intval($_GET['page']);
                $result = $frontend->rebuildCatalog($page, $display);
                $modx->documentObject['productsBlock'] = $result['catalogItems'];
                $modx->documentObject['sortBlock']     = $result['sortBlock'];
                $modx->documentObject['pagination']    = $result['pagination'];
                $modx->documentObject['word'] = $_GET['search'];
                break;
            case '26'://Профиль
                $result = '';
                $address = $_SESSION['webuser']['comment'];
                
                $citys = $frontend->getAllCitys();
                $citys_arr = $modx->db->makeArray($citys);

                if ($address != '') {
                    $decode = json_decode($address, true);
                    
                    if (count($decode["address"]) > 0) {
                        foreach ($decode["address"] as $key => $value) {
                            $value['counter'] = $key;
                            
                            $citys_items = '';
                            if ($citys_arr) {
                                foreach ($citys_arr as $key_city => $value_city) {
                                    if ($value['city'] == $value_city['id']) {
                                        $value_city['selected'] = 'selected';  
                                    } else {
                                        $value_city['selected'] = '';
                                    }

                                    $value_city['value'] = $value_city['id'];
                                    $value_city['label'] = $value_city['name'];
                                    $citys_items .= $modx->parseChunk('option', $value_city,'[+','+]');
                                }
                            }
                            
                            $value['all_citys'] = $citys_items;

                            $result .= $modx->parseChunk('form_profile_address', $value,'[+','+]');
                        }
                    }
                    
                    $modx->documentObject['profile_address'] = $result;
                } else {
                    $arr = array(
                        'counter' => 0,
                        'phone'   => '',
                        'city'    => '',
                        'street'  => '',
                        'house'   => '',
                        'kv'      => '',
                        'massage' => ''
                    );
                    
                    $citys_items = '';
                    if ($citys_arr) {
                        foreach ($citys_arr as $key_city => $value_city) {
                            $value_city['value'] = $value_city['id'];
                            $value_city['label'] = $value_city['name'];
                            $citys_items .= $modx->parseChunk('option', $value_city,'[+','+]');
                        }
                    }
                    
                    $arr['all_citys'] = $citys_items;

                    $modx->documentObject['profile_address'] = $modx->parseChunk('form_profile_address', $arr,'[+','+]');
                }
                
                $order_item = '';
                $orders = $frontend->getUserOrders();
                if ($orders) {
                    $count = 0;
                    while ($row = $modx->db->getRow($orders)) {
                        $status_xml = $frontend->getXml('shop', 'status/item/value[.="'.$row['status'].'"]/parent::*');
                        if ($row['orders'] != '') {
                            $orders_decode = json_decode($row['orders'], true);
                            if (count($orders_decode) == 0) {
                                continue;
                            }

                            foreach ($orders_decode as $key => $value) {
                                if ($key == 0) {
                                    $value['order_status_name'] = $status_xml[0]['label'];
                                    $value['class'] = 'order';
                                    $value['created_at'] = date('d/m/Y', strtotime($row['created_at']));
                                    $value['count_product'] = count($orders_decode);
                                }
                                
                                $value['status'] = $row['status'];
                                $value['order_id'] = $row['id'];
                                
                                $order_item .= $modx->parseDocumentSource($modx->parseChunk('tpl_profileOrderItem', $value,'[+','+]'));
                            }
                        }
                        
                        $count++;
                    }
                }

                $modx->documentObject['profile_orders'] = $order_item;

                $citys_items = '';
                foreach ($citys_arr as $key_city => $value_city) {
                    if ($_SESSION['webuser']['city'] == $value_city['id']) {
                        $value_city['selected'] = 'selected';  
                    } else {
                        $value_city['selected'] = '';
                    }
                    
                    $value_city['value'] = $value_city['id'];
                    $value_city['label'] = $value_city['name'];
                    $citys_items .= $modx->parseChunk('option', $value_city,'[+','+]');
                }

                $modx->documentObject['city_list'] = $citys_items;
                
                break;
            case '8'://Оформление заказа
                $cart = $frontend->rebuildOrderCart();
                
                $modx->documentObject['count'] = $cart['count'];
                $modx->documentObject['total_summ'] = $cart['total_summ'];
                $modx->documentObject['delivery_summ'] = 0;
                $modx->documentObject['productBlock'] = $cart['products'];

                //города
                $citys_items = '';
                $all_citys_items = '';
                
                $citys = $frontend->getFittingCitys();
                
                if ($citys) {
                    $count = 0;
                    $first_city = array();
                    while ($row = $modx->db->getRow($citys)) {
                        if ($count == 0) {
                            $first_city['value'] = $row['id'];
                            $first_city['label'] = $row['pagetitle'];
                        }
                        
                        $row['value'] = $row['id'];
                        $row['label'] = $row['pagetitle'];
                        
                        $citys_items .= $modx->parseChunk('option', $row,'[+','+]');
                        $count++;
                    }
                }

                $modx->documentObject['citys'] = $citys_items;
 
                $modx->documentObject['shop_address'] = $modx->runSnippet('multiTV', array(
                    'tvName' => 'tv_address',
                    'docid' => $first_city['value'],
                    'rowTpl' => '@CODE:<option value="((name))">((name))</option>',
                    'display' => 'all',
                    'rows' => 'all',
                    'toPlaceholder' => '0',
                    'randomize' => '0'
                ));

                $result = '';
                $address = $_SESSION['webuser']['comment'];
                if ($address != '') {
                    $decode = json_decode($address, true);
                    
                    if (count($decode["address"]) > 0) {
                        foreach ($decode["address"] as $key => $value) {
                            $value['key'] = $key;
                            $result .= $modx->parseChunk('order_addressItem', $value,'[+','+]');
                        }
                    }
                    
                    $modx->documentObject['address_list'] = $result;
                }

                //формирование файла новой почты
                $nv_array = $frontend->getNvData();
                
                $stock_items = '';
                $address_items = '';
                
                if (count($nv_array['citys']) > 0) {
                    $count = 0;
                    foreach ($nv_array['citys'] as $key => $value) {
                        if ($count == 0) {
                            foreach ($nv_array['stocks'] as $key_stock => $value_stock) {
                                if ($value_stock['city_id'] == $value['id']) {
                                    $value_stock['value'] = $value_stock['name'];
                                    $value_stock['label'] = $value_stock['name'];
                                    $address_items .= $modx->parseDocumentSource($modx->parseChunk('option', $value_stock,'[+','+]'));
                                }
                            } 
                        }

                        $value['value'] = $value['name'];
                        $value['label'] = $value['name'];
                        $value['data'] = $value['id'];
                        $stock_items .= $modx->parseChunk('option', $value,'[+','+]');
                        $count++;
                    }
                }
                
                $modx->documentObject['np_citys'] = $stock_items;
                $modx->documentObject['np_address'] = $address_items;

                break;
            case '5'://Товар
                $product = $frontend->getProductInfo($_SESSION['product']['id']);

                if (isset($_SESSION['product']['id']) && count($product) > 0 && $product['published'] == 1) {
                    foreach ($product as $key => $value) {
                        $modx->documentObject[$key] = $value;
                    }
                } else {
                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: /");
                }

                $modx->documentObject['is_product'] = true;
			
			
			if($modx->documentObject['modx_id']){
			$modx_makeUrl = $modx->makeUrl($modx->documentObject['modx_id'], '', '', 'full');
				// <meta property="og:url" content="'.$modx->makeUrl($modx->documentObject['modx_id'], '', '', 'full').$modx->documentObject['alias'].'" />
			}

                $modx->documentObject['meta_data'] = '
                    <meta property="og:title" content="'.$modx->documentObject['name'].'" />
                    <meta property="og:type" content="Товар" />
                    <meta property="og:url" content="'.$modx_makeUrl.$modx->documentObject['alias'].'" />
                    <meta property="og:image" content="'.$modx->config['site_url'].'assets/files/product/'.$modx->documentObject['id'].'/image/'.$modx->documentObject['image'].'" />
                    <meta property="og:description" content="'.$modx->documentObject['description'].'" />';
               
                $modx->documentObject['tv_seo_title']       = ($product['meta_title'] != '' ? $product['meta_title'] : $product['name']);
                $modx->documentObject['tv_seo_description'] = ($product['meta_description'] != '' ? $product['meta_description'] : $modx->documentObject['tv_seo_description']);
                $modx->documentObject['tv_seo_keywords']    = ($product['meta_keywords'] != '' ? $product['meta_keywords'] : $modx->documentObject['tv_seo_keywords']);
                $modx->documentObject['tv_seo_robots']      = ($product['meta_robots'] != '' ? $product['meta_robots'] : $modx->documentObject['tv_seo_robots']);
                $modx->documentObject['tv_seo_canonical']   = ($product['meta_canonical'] != '' ? $product['meta_canonical'] : $modx->documentObject['tv_seo_canonical']);
  
                //города
                $citys_items = '';
                $citys = $frontend->getFittingCitys();
                
                if ($citys) {
                    while ($row = $modx->db->getRow($citys)) {
                        $row['value'] = $row['pagetitle'];
                        $row['label'] = $row['pagetitle'];
                        $citys_items .= $modx->parseChunk('option', $row,'[+','+]');
                    }
                }
                
                $modx->documentObject['fitting_city'] = $citys_items;
                
                //размеры
                $sizes_items = '';
                $sizes = $frontend->getProductSizes($product['id']);
                
                if ($sizes) {
                    $first_view = 0;
                    $first_size = array();

                    $count = 0;	
					
					while ($row = $modx->db->getRow($sizes)) {
                   				
                        $size = $frontend->getXml('filter', 'size/item/value[.="'.$row['size_id'].'"]/parent::*');
                        $row['name'] = $size[0]['label'];

                        if ($count == 0) {
                            $first_size = $row;
                        }

						if ($row['price'] == '') {
                            $row['price'] = $row['base_price'];
                        }
                        
                        if ($row['sale_price'] == '') {
                            $row['sale_price'] = $row['base_sale_price'];
                        }		
						
						if ($product['fix_price']) {
							$row['fix_price'] = $product['fix_price'];
						}						
						
						$row = $frontend->categoryDiscount($row, $product['modx_id'], $row['price'], $row['sale_price']);
                        
                        if ($first_view != 1 && $row['amount'] > 0) {
                            $modx->documentObject['first_size_id'] = $row['id'];
                            $modx->documentObject['first_size_amount'] = $row['amount'];
                            $modx->documentObject['first_size_price'] = $row['price'];

                            $modx->documentObject['price'] = $row['price'];
                            $modx->documentObject['sale_price'] = $row['sale_price'];

                            $modx->documentObject['base_price'] = $row['sale_price'];
                            
                            if ($row['amount'] == 0) {
                                $data = $frontend->getXml('product', 'presence/item/value[.="2"]/parent::*');
                            } else {
                                $data = $frontend->getXml('product', 'presence/item/value[.="1"]/parent::*');
                            }

                            $row['selected'] = 'selected';
                            
                            $modx->documentObject['presence_name'] = $data[0]['label'];
                            $first_view = 1;
                        }
                        
                        $sizes_items .= $modx->parseChunk('tpl_productSizeItem', $row,'[+','+]');
                        $count++;
                    }

                    if ($first_view == 0) {//если все недоступны
                        $modx->documentObject['first_size_id'] = $first_size['id'];
                        $modx->documentObject['first_size_amount'] = $first_size['amount'];
                        $modx->documentObject['first_size_price'] = $first_size['price'];

                        $modx->documentObject['price'] = $first_size['price'];
                        $modx->documentObject['sale_price'] = $first_size['sale_price'];

                        $modx->documentObject['base_price'] = $first_size['sale_price'];
                        
                        $data = $frontend->getXml('product', 'presence/item/value[.="2"]/parent::*');

                        $modx->documentObject['presence_name'] = $data[0]['label'];
                    }
                }
                
                $modx->documentObject['sizes'] = $sizes_items;
                
                //цвета
                $colors_items = '';
                $colors = $frontend->getProductInGroup($product['group_id']);
                
                if ($colors) {
                    while ($row = $modx->db->getRow($colors)) {
                        if ($row['price'] == '') {
                            $row['price'] = $row['base_price'];
                        }
                        
                        if ($row['sale_price'] == '') {
                            $row['sale_price'] = $row['base_sale_price'];
                        }

                        $colors_items .= $modx->parseChunk('tpl_productColorItem', $row,'[+','+]');
                    }
                }
                
                $modx->documentObject['colors'] = $colors_items;
                
                //изображения
                $images_items = '';
                $images = $frontend->getProductImages($product['id']);
                
                if ($images) {
                    $count = 2;
                    while ($row = $modx->db->getRow($images)) {
                        $row['key'] = $count;
                        $images_items .= $modx->parseChunk('tpl_productGalleryItem', $row,'[+','+]');
                        $thumbs_items .= $modx->parseChunk('tpl_productGalleryItemThumb', $row,'[+','+]');
                        $count++;
                    }
                }

                $modx->documentObject['gallery'] = $images_items;
                $modx->documentObject['gallery_thumbs'] = $thumbs_items;
                
                //добавочные товары
                $additional = '';
                if ($product['additional'] != '') {
                    $additional = $frontend->getProductAdditionals($product['additional']);
                    if ($additional) {
                        while ($row = $modx->db->getRow($additional)) {
                            if ($row['price'] == '') {
                                $row['price'] = $row['base_price'];
                            }
                            
                            if ($row['sale_price'] == '') {
                                $row['sale_price'] = $row['base_sale_price'];
                            }

                            $additional_items .= $modx->parseChunk('tpl_productAdditionalItem', $row,'[+','+]');
                        }
                    }
                }
                
                $modx->documentObject['additional'] = $additional_items;
                
                //tv vars
                $tv = $frontend->getProductTvVars($product['modx_id']);
                $modx->documentObject['tv_care'] = $tv['tv_care'];
                $modx->documentObject['tv_delivery'] = $tv['tv_delivery'];
                $modx->documentObject['tv_exchange'] = $tv['tv_exchange'];
                $modx->documentObject['tv_return'] = $tv['tv_return'];
                
                //отзывы
                $reviews_items = '';
                $reviews = $frontend->getProductReviews($product['id']);
                
                if ($reviews) {
                    $count = 0;
                    while ($row = $modx->db->getRow($reviews)) {
                        $row['created_at'] = date('d.m.Y', strtotime($row['created_at']));
                        
                        if ($count == 0) {
                            $modx->documentObject['last_review'] = $modx->parseChunk('productLastReview', $row,'[+','+]');
                        }
                        $reviews_items .= $modx->parseChunk('tpl_productReviewItem', $row,'[+','+]');
                        $count++;
                    }
                }
                
                $modx->documentObject['reviews'] = $reviews_items == '' ? $alerts['review_error'] : $reviews_items;
                
                break;
        }

        /*==========Выборка по TEMPLATE==========*/

        switch ($modx->documentObject['template']) {
            case '7'://категория
                $display = $_GET['total'] == '' ? 9 : intval($_GET['total']);
                $page = $_GET['page'] == 0 ? 1 : intval($_GET['page']);
                $result = $frontend->rebuildCatalog($page, $display);
                $modx->documentObject['productsBlock'] = $result['catalogItems'];
                $modx->documentObject['sortBlock']     = $result['sortBlock'];
                $modx->documentObject['filterBlock']   = $result['filterBlock'];
                $modx->documentObject['pagination']    = $result['pagination'];
                break;
        }
        
        switch ($modx->documentObject['parent']) {
            case '6'://статьи блога
                $image = $modx->getTemplateVar('tv_image', '*', $modx->documentObject['id']); 
                $modx->documentObject['meta_product'] = '
                    <meta property="og:title" content="'.$modx->documentObject['pagetitle'].'" />
                    <meta property="og:type" content="Товар" />
                    <meta property="og:url" content="'.$modx->makeUrl($modx->documentObject['id'], '', '', 'full').'" />
                    <meta property="og:image" content="[(site_url)]'.$image['value'].'" />
                    <meta property="og:description" content="'.$modx->documentObject['introtext'].'" />';
                
                $modx->documentObject['prev_next'] = $frontend->model->prevNextPagination($modx->documentObject['parent'], $modx->documentObject['id']);
                break;
        }

        break;
    case "OnCacheUpdate":
        if ($_GET['a'] == 26) {
            $list = glob(MODX_BASE_PATH."assets/cache/*.cache");
            if (is_array($list)) foreach ($list as $v) unlink($v);
            echo '<p style="color:red">Удалено кэшированных блоков <b>'.count($list).'</b></p>';

            $list = MODX_BASE_PATH . "assets/cache/images/";
            if (is_array($list)) foreach ($list as $v) unlink($v);
            $frontend->model->rmdirRecursive($list);
            echo '<p style="color:green">Изображения очищены</p>';

            $list = glob(MODX_BASE_PATH."assets/cache/viewer/*");
            if (is_array($list)) foreach ($list as $v) unlink($v);
            echo '<p style="color:green">Удалено кэшированние сниппета (Viewer) <b>'.count($list).'</b></p>';

            $list = glob(MODX_BASE_PATH."assets/cache/wf/*");
            if (is_array($list)) foreach ($list as $v) unlink($v);
            echo '<p style="color:green">Удалено кэшированние сниппета (Wayfinder) <b>'.count($list).'</b></p>';
        }
    break;
}