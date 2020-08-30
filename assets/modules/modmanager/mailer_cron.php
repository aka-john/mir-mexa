<?php
define (ROOT, dirname(__FILE__));
error_reporting(E_ALL);
require_once(ROOT.'/../../../manager/includes/protect.inc.php');
include_once ROOT.'/../../../manager/includes/config.inc.php';
define('MODX_API_MODE', true);
include_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');

$modx = new DocumentParser();
$modx->db->connect();
$modx->getSettings();
global $modx;
startCMSSession();
error_reporting(0);

$lim = $modx->config['smtp_limit'];
$query = "select 
			m.* ,
			t.*,
			u.*
		  from `modx_mm_mailer` m 
		  join `modx_mm_mailer_letter` t on t.id = m.letter_id 
		  join `modx_mm_mailer_user` u on u.id = m.subscriber_id 
		  where m.mailed = 0
		  limit $lim ";
$list  = $modx->db->query($query);
$host  = parse_url($modx->config['site_url']);
$host  = $host['scheme']."://".$host['host']."/";

while ($l = $modx->db->getRow($list)) {
	switch ($l['type']) {
		case '1'://шаблон
			$mail = $l['text'];
			break;
		case '2'://чанк
			$mail = $modx->rewriteUrls($modx->parseDocumentSource($this->modx->parseChunk($l['chunk_id'], array(),'[+','+]')));
			break;
		case '3'://ресурс
			$doc = $modx->getDocument(intval($l['resource_id']));
			$mail = $doc['content'];
			break;
	}
	$modx->loadExtension('MODxMailer');
    $modx->mail->Subject = $l['subject'];
    $modx->mail->AddAddress($l['email']);
    $modx->mail->MsgHTML($mail);
    $modx->mail->Send();
	$modx->db->query("update `modx_mm_mailer` set mailed = 1 where subscriber_id = '".$l['user_id']."' and letter_id = '".$l['letter_id']."'");
}
echo $modx->db->getRecordCount($list);
die("End");