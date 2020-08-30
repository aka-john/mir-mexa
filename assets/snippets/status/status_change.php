<?php
	//error_reporting(E_ALL);
	//ini_set("display_errors", 1);
$site_root = $_SERVER['DOCUMENT_ROOT'];
require_once($site_root.'/manager/includes/config.inc.php');
require_once($site_root.'/manager/includes/protect.inc.php');
define('MODX_API_MODE', true);
require_once($site_root.'/manager/includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();

/*
emailsender
echo $modx->config['smtp_host'];
echo '<br>';	
echo $modx->config['smtp_username'];
echo '<br>';
echo $modx->config['smtppw'];
echo '<br>';
echo $modx->config["site_name"];
*/	
//echo '1111';			
//echo $shop['id'];
//					echo '<br>';
//					echo $shop['status'];

/*
function add_amount($order){
	global $modx;  
	//получаем json данных, в котором собираем id и количество
	//в будушем сделать проверку на количество товаров в заказе
	
	$order_arr = json_decode($order);
	
	$product_id = $order_arr['id'];
	$product_size_id = $order_arr['size_id'];
	$product_amount = $order_arr['amount'];
	
	
	//добавляем остаток товаров после смены статуса на отмена и взврат
	
	//делаем запрос в таблицу mm_product_size и получаем текущий остаток
	$table = $modx->getFullTableName('mm_product_size'); 
	$result = $modx->db->query( 'SELECT * FROM '.$table.' WHERE product_id = "'.$product_id.'" AND size_id = "'.$product_size_id.'" ' );  
	
	 $amount = $modx->db->makeArray( $result ); 
	
	if($amount['amount'] == $product_amount AND $amount['size_id'] == $product_size_id AND $amount['id'] == $product_id){
	
		$fields = array( 'amount' => $product_amount+1 );  

$result = $modx->db->update( $fields, $table, 'id = "' . $product_id . '" AND size_id = "'.$product_size_id.'" ' );   
if( $result ) {  
    echo 'Информация обновлена! amount '.$product_amount+1;  
} else {  
    echo 'Возникла проблема во время запроса...';  
}
		
		
	}
	
	
	//обновляем поле с новым остатком

}
*/


function clearCache() {
    global $modx; 
    $modx->clearCache('full');
    include_once MODX_MANAGER_PATH . 'processors/cache_sync.class.processor.php';
    $sync = new synccache();
    $sync->setCachepath(MODX_BASE_PATH . "assets/cache/");
    $sync->setReport(false);
    $sync->emptyCache();
}



$urer_info = json_decode($shop['user_info'], true);


//echo $shop['status'].'<br>';
//echo $_POST['status'].'<br>';
switch($shop['status']){
case '1':
$text = $modx->parseChunk('email_status_new', array( 'status' => 'Новый', 'number' => $shop['number']), '[+', '+]' );
break;

case '2':
$text = $modx->parseChunk('email_status_wait', array( 'status' => 'Ожидает', 'number' => $shop['number']), '[+', '+]' );
break;
	
case '3':
$text = $modx->parseChunk('email_status_pay', array( 'status' => 'Оплачен', 'number' => $shop['number']), '[+', '+]' );
break;
	
case '4':
$text = $modx->parseChunk('email_status_deliv', array( 'status' => 'Доставлен', 'number' => $shop['number']), '[+', '+]' );
break;
	
case '5':
$text = $modx->parseChunk('email_status_fail', array( 'status' => 'Отменен', 'number' => $shop['number']), '[+', '+]' );
clearCache();
break;
	
case '6':
$text = $modx->parseChunk('email_status_error', array( 'status' => 'Ошибка', 'number' => $shop['number']), '[+', '+]' );	
clearCache();
break;
	
case '7':
$text = $modx->parseChunk('email_status_payliqpay', array( 'status' => 'Оплачено LiqPay', 'number' => $shop['number']), '[+', '+]' );	
break;
	
case '8':
$text = $modx->parseChunk('email_status_sent', array( 'status' => 'Отправлен', 'number' => $shop['number']), '[+', '+]' );	
break;
	
case '9':
$text = $modx->parseChunk('email_status_return', array( 'status' => 'Возврат', 'number' => $shop['number']), '[+', '+]' );
clearCache();
break;
}
/*

include_once MODX_MANAGER_PATH . "includes/controls/class.phpmailer.php";	
function php_mail($email, $subject, $body) {
        global $modx;
        $mail = new PHPMailer();
        //$mail->IsMail();
		$mail->IsSMTP();
        $mail->IsHTML(true);
        $mail->From      = $modx->config['emailsender'];
        $mail->FromName  = $modx->config['site_name'];
        $mail->Subject   = $subject;
        $mail->Body      = $body;
        $mail->AddAddress($email);
        $mail->Send();
}

include_once MODX_MANAGER_PATH . "includes/controls/class.phpmailer.php";
global $modx; //добавить если нету
    function php_mail($email, $subject, $body) {
      
      $mail = new PHPMailer();
      
      $mail->IsSMTP();// отсылать используя SMTP
      $mail->Host  = $modx->config['smtp_host']; // SMTP сервер
      $mail->SMTPAuth = true;  // включить SMTP аутентификацию
      $mail->Username = $modx->config['smtp_username']; // SMTP username
      $mail->Password = $modx->config['smtppw']; // SMTP password
      $mail->From     = $modx->config['smtp_username'];
      
     // $mail->CharSet = $modx->config["modx_charset"]; 
      $mail->IsHTML(true);    
      $mail->FromName = $modx->config["site_name"];
      $mail->Subject = $subject;
      $mail->Body = $body;
      $mail->AddAddress($email);
      $mail->Send();
      
   }


	
		
php_mail('eyrad4@gmail.com', 'mir-mexa.com статус заказа изменен', 'текст');
*/
//echo $text;
$to = $urer_info['email'];//'eyrad4@yandex.ru';
//$to = 'eyrad4@gmail.com';
global $modx; //добавить если нету
  include_once MODX_MANAGER_PATH . "includes/controls/class.phpmailer.php";
$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.ukr.net'; // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'mirkm@ukr.net';               // SMTP username
$mail->Password = 'baltika1';                          // SMTP password
$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465;   //587                                 // TCP port to connect to

$mail->From = 'mirkm@ukr.net';
$mail->FromName = $modx->config["site_name"];
//$mail->MIMEHeader  = 'MIME-Version: 1.0\r\n';
//$mail->mailHeader = 'Content-type: text/html; charset=UTF-8\r\n';
//$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
$mail->CharSet ='UTF-8';
	$mail->ContentType = 'text/html';
$mail->addAddress($to);


$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = 'mir-mexa.com статус заказа изменен';
$mail->Body    = $text;
//$mail->AltBody = 'тутуту 1';

if(!$mail->send()) {
	//echo 'Message could not be sent.';
	// echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
	//  echo 'Message has been sent';
}



