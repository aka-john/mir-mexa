<?php                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
    switch ($_REQUEST['action']) {
        case 'form_auth':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                $frontend->authorization('login', $fields);
                $arr = array( 
                    'key'   => 'form_auth', 
                    'value' => $modx->config['site_url'].$_GET['q']
                );
                die (json_encode($arr));
            }
            break;
        case 'form_auth_order':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                $frontend->authorization('login', $fields);
                $arr = array( 
                    'key'   => 'form_auth_order', 
                    'value' => $modx->config['site_url'].$_GET['q']
                );
                die (json_encode($arr));
            }
            break;
        case 'form_reg':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                $frontend->registration($fields);
                $arr = array( 
                    'key'   => 'form_reg', 
                    'value' => $alerts['form_reg']
                );
                die (json_encode($arr));
            }
            break;
        case 'form_forgot':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                $frontend->authorization('forgot', $fields);
                $arr = array( 
                    'key'   => 'form_forgot', 
                    'value' => $alerts['form_forgot']
                );
                die (json_encode($arr));
            }
            break;
        case 'form_callback':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                
                $mail = $modx->rewriteUrls($modx->parseDocumentSource($modx->parseChunk('mail_callback', $fields,'[+','+]'))); 

                if ($modx->config['emailsender'] != '') {
                    $explode = explode(',', $modx->config['emailsender']);
                    foreach ($explode as $key => $value) {
                        $frontend->model->sendMail($value, "Перезвонить ".$modx->config['site_name'], $mail);
                    }
                }
     
                $arr = array( 
                    'key'   => 'form_callback', 
                    'value' => $alerts['form_callback']
                );
                die (json_encode($arr));
            }
            break;
        case 'form_feedback':
		
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                
                $mail = $modx->rewriteUrls($modx->parseDocumentSource($modx->parseChunk('mail_feedback', $fields,'[+','+]'))); 
                
                if ($modx->config['emailsender'] != '') {
                    $explode = explode(',', $modx->config['emailsender']);
                    foreach ($explode as $key => $value) {
                        $frontend->model->sendMail($value, "Контактная форма ".$modx->config['site_name'], $mail);
                    }
                }
                        
                $arr = array( 
                    'key'   => 'form_feedback', 
                    'value' => $alerts['form_feedback']
                );
                die (json_encode($arr));
            }
            break;
        case 'form_report':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);

                $status = $frontend->addReportProductToUser($fields);
                
                if (!$status) {
                    die(json_encode(array('error_status' => false, 'alert' => 'Вы уже добавили даный товар в заявки')));
                }
                
                $size = $frontend->getProductSize($fields['size_id']);
                if ($size) {
                    $fields['size'] = $size['size'];
                    $fields['size_name'] = $size['name'];
                }
                
                $product = $frontend->getProductInfo($_SESSION['product']['id']);

                $fields['modx_id'] = $product['modx_id'];
                $fields['product_alias'] = $product['alias'];
                $fields['product_name'] = $product['name'];

                $arr = array( 
                    'key'   => 'form_report', 
                    'value' => $alerts['form_report']
                );
                die (json_encode($arr));
            }
            break;
        case 'form_review':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                $result = $frontend->addReview($_SESSION['product']['id'], $fields);
                $arr = array( 
                    'key'   => 'form_review', 
                    'value' => $alerts['form_review']
                );
            }
            die (json_encode($arr));
            break;
        case 'form_fitting':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                
                $product = $frontend->getProductInfo($_SESSION['product']['id']);
				// unset($product['name']);
				//$product['product_name'] = $product['name'];
				
				$product = $frontend->getProductInfo($_SESSION['product']['id']);

                $fields['modx_id'] = $product['modx_id'];
                $fields['product_alias'] = $product['alias'];
                $fields['product_name'] = $product['sku'];
				
                $fields = array_merge($fields, $product);

                $mail = $modx->rewriteUrls($modx->parseDocumentSource($modx->parseChunk('mail_fitting', $fields,'[+','+]'))); 

                if ($modx->config['emailsender'] != '') {
                    $explode = explode(',', $modx->config['emailsender']);
                    foreach ($explode as $key => $value) {
                        $frontend->model->sendMail($value, "Запись на примерку ".$modx->config['site_name'], $mail);
                    }
                }
                
                $arr = array( 
                    'key'   => 'form_fitting', 
                    'value' => $alerts['form_fitting']
                );
            }
            die (json_encode($arr));
            break;
        case 'cart_add':
            $cart = $frontend->addToCart($_REQUEST);
            $cart['message'] = $alerts['cart_add'];
            die(json_encode($cart));
            break;
        case 'cart_remove':
            if ($_POST['key'] != '') {
                unset($_SESSION['order'][$_POST['key']]);
            }
            if (count($_SESSION['order']) < 1) {
                unset($_SESSION['order']);
                $empty = $modx->parseDocumentSource($alerts['cart_empty']);
            }
            
            $order_cart = $frontend->rebuildOrderCart();
            
            $arr = array(
                'cart' => $empty,
                'headCart' => $frontend->rebuildHeadCart(),
                'orderCart' => $order_cart['products']
            );
            die(json_encode($arr));
            break;
        case 'cart_clear':
            unset($_SESSION['order']);
            
            $order_cart = $frontend->rebuildOrderCart();
            
            $arr = array(
                'cart' => $modx->parseDocumentSource($alerts['cart_empty']),
                'headCart' => $frontend->rebuildHeadCart(),
                'orderCart' => $order_cart['products']
            );
            die(json_encode($arr));
            break;
        case 'cart_amount':
            $order = $_SESSION['order'][$_POST['key']];
            $size_amount = $frontend->getProductSizeAmount($order['size_id'], $order['id']);

            $amount = $size_amount['amount'] >= intval($_POST['amount']) ? intval($_POST['amount']) : $size_amount['amount'];
            $amount = $amount <= 0 ? 1 : $amount;
            $_SESSION['order'][$_POST['key']]['amount'] = $amount;

            $order_cart = $frontend->rebuildOrderCart();
            
            $arr = array(
                'amount' => $amount,
                'headCart' => $frontend->rebuildHeadCart(),
                'orderCart' => $order_cart['products']
            );
            die(json_encode($arr));
            break;
        case 'password_change':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                $frontend->changePassword($fields);
                $arr = array( 
                    'key'   => 'password_change', 
                    'value' => $alerts['password_change']
                );
                die (json_encode($arr));
            }
            break;
        case 'profile_save':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                
                $frontend->updateProfile($fields);
                $arr = array( 
                    'key'   => 'profile_save', 
                    'value' => $alerts['profile_save']
                );
                die (json_encode($arr));
            }
            break;
        case 'add_profile_address':
            $arr = array(
                'counter' => intval($_POST['counter']),
                'phone' => '',
                'city' => '',
                'street' => '',
                'house' => '',
                'kv' => '',
                'comment' => ''
            );
            
            $all_citys = $frontend->getAllCitys();
            
            if ($all_citys) {
                while ($row = $modx->db->getRow($all_citys)) {
                    $row['value'] = $row['id'];
                    $row['label'] = $row['name'];
                    $citys_items .= $modx->parseChunk('option', $row,'[+','+]');
                }
            }

            $arr['all_citys'] = $citys_items;

            $result = $modx->parseChunk('form_profile_address', $arr,'[+','+]');
            die($result);
            break;
        case 'form_subscribe':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            
            if ($flag['error_status'] == false) {
				
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);

                if ($frontend->subscribe($fields) == false) {
                    $arr['error_status'] = false;
                    $arr['error'][0]['name'] = 'email';
                    $arr['error'][0]['message'] = 'Вы уже подписаны';
                    die(json_encode($arr));
                }

                $arr = array( 
                    'key'   => 'form_subscribe', 
                    'value' => $alerts['form_subscribe']
                );
                die (json_encode($arr));
            }
            break;
        case 'profile_address_save':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                $frontend->updateProfileAddress($fields);
                $arr = array( 
                    'key'   => 'profile_save', 
                    'value' => $alerts['profile_save']
                );
                die (json_encode($arr));
            }
            break;
        case 'check_size_amount':
            if (isset($_POST['size']) && $_POST['size'] != '') {
                $size = $frontend->getProductSize($_POST['size']);
                $arr = $size;
                
                $product = $frontend->getProductInfo($_SESSION['product']['id']);
                
				/*if ($size['sale_price'] != 0 && $product['status'] == 2) {
                    $arr['sale_price'] = $size['price'];
                    $arr['price'] = $size['sale_price'];
                } elseif ($size['sale_price'] != 0 && $product['status'] == 3) {
                    $arr['sale_price'] = $size['price'];
                    $arr['price'] = $size['sale_price'];
                } else {
                    $arr['price'] = $size['sale_price'];
                }*/
				
				if ($size['sale_price'] != 0 && $product['status'] == 2) {
                    $arr['sale_price'] = $size['price'];
                    $arr['price'] = $size['sale_price'];
                } else {
					$arr['sale_price'] = $size['price'];
                    $arr['price'] = $size['sale_price'];
                }
				
				if ($arr['price'] == '') {
					$arr['price'] = $size['base_price'];
				}
				
				$arr['fix_price'] = $product['fix_price'];
							 //echo $arr['price'].' - '.$arr['sale_price'];
							 //print_r($arr);
				$arr = $frontend->categoryDiscount($arr, $product['modx_id'], $arr['price'], $arr['sale_price'], true);
				
                
                $data = array();
                if ($arr['amount'] == 0) {
                    $data = $frontend->getXml('product', 'presence/item/value[.="2"]/parent::*');
                } else {
                    $data = $frontend->getXml('product', 'presence/item/value[.="1"]/parent::*');
                }
                
                $arr['presence'] = $data[0]['label'];
            }
            
            die (json_encode($arr));
            break;
        case 'form_order':
            $check = $frontend->model->check($_POST['fields']);
            $flag = json_decode($check, true);
            if ($flag['error_status'] == false) {
                die($check);
            } else {
                $fields = array();
                parse_str($_POST['data'], $fields);
                $result = $frontend->createOrder($fields);
                $url = $modx->makeUrl(25, '', '&order='.$result['order_id'], 'full');
                $arr = array( 
                    'key'    => 'form_order', 
                    'value'  => $url,
                    'action' => $result['action'],
                    'html'   => $result['html']
                );
                die (json_encode($arr));
            }
            break;
        case 'order_shops_view':
                die($modx->runSnippet('multiTV', array(
                    'tvName' => 'tv_address',
                    'docid' => $_POST['city'],
                    'rowTpl' => '@CODE:<option value="((name))">((name))</option>',
                    'display' => 'all',
                    'rows' => 'all',
                    'toPlaceholder' => '0',
                    'randomize' => '0'
                )));
            break;
        case 'order_stock_view':
            if (file_exists($modx->config['base_path'].'assets/site/nv-citys.txt')) {
                $np_content = file_get_contents($modx->config['base_path'].'assets/site/nv-citys.txt');

                $result = '';

                $exp = json_decode($np_content, true);

                foreach ($exp['stocks'] as $key => $value) {
                    if ($value['city_ref'] == $_POST['city']) {
                        $value['value'] = $value['name'];
                        $value['label'] = $value['name'];
                        $result .= $modx->parseDocumentSource($modx->parseChunk('option', $value,'[+','+]'));
                    }
                }  
            }

            die($result);
            break;
        case 'profile_pay_order':
            if (isset($_POST['order_id']) && $_POST['order_id'] != '') {
                $order = $frontend->getOrder($_POST['order_id']);
                
                $liqpay_form = $frontend->liqpayQuery($_POST['order_id'], $order);
            }

            die($liqpay_form);
            break;
        case 'change_user_city':
            $frontend->setCityCookie($_POST['city_id']);
            $prices = $frontend->rebuildOrderPrices();

            $order_cart = $frontend->rebuildOrderCart();

            $frontend->setCityConfirmedCookie();
            
            $arr = array(
                'popupCart' => $frontend->rebuildCart(),
                'headCart' => $frontend->rebuildHeadCart(),
                'orderCart' => $order_cart['products'],
                'total_price' => $prices['total_price']
            );
            die (json_encode($arr));
            break;
        case 'set_user_city_confirm':
            $frontend->setCityConfirmedCookie();
            die;
            break;
    }
} 