<?php
/*

*/
	define("ROOT", dirname(__FILE__));
	$get            = isset($_GET['get']) ? $_GET['get'] : "redirect";
	$res            = Array();
	$res['version'] = "v 1.0";
	$res['url']     = $table['url'] = $url = "index.php?a=112&id=".$_GET['id']."";
	$res['get']     = isset($_GET['get']) ? $_GET['get'] : "";

	switch ($get) {
		case "sitemap":
			if (isset($_POST['post'])) {
				file_put_contents(ROOT."/sitemap.php", $_POST['post']);
				$res['alert'] = "Алгоритм обновлен";
			}
			if ($_GET['do'] == "reconstruct") $modx->invokeEvent("OnDocFormSave");
			$tpl = "/tpl/sitemap.tpl";
		break;
		case "robots":
			if (isset($_POST['post'])) {
				file_put_contents($_SERVER['DOCUMENT_ROOT']."/robots.txt", $_POST['post']);
				$res['alert'] = "Файл robots.txt обновлен";
			}
			$tpl = "/tpl/robots.tpl";
		break;
		case "counters":
			if (count($_POST) > 0) {
				foreach ($_POST as $k => $v) 
					$modx->db->query("update `modx_system_settings` set setting_value = '".$modx->db->escape($v)."' where setting_name = '$k'");
				$res['alert'] = "Настройки обновлены!";
				include_once MODX_BASE_PATH . "manager/processors/cache_sync.class.processor.php";
				$sync = new synccache();
				$sync->setCachepath(MODX_BASE_PATH . "assets/cache/");
				$sync->setReport(false);
				$sync->emptyCache(); // first empty the cache
				$modx->getSettings();
				header("Location: ".$url."&get=counters");
				die;
			}
			$tpl = "/tpl/counters.tpl";
		break;
		case "redirect":
			if (isset($_GET['delete']))
				$modx->db->query("delete from `modx_a_redirect` where redirect_id = '".$_GET['delete']."'");
			if (count($_POST['add']) > 0) {
			 	$modx->db->query("insert into `modx_a_redirect` set 
									redirect_code   = '".$modx->db->escape($_POST['add']['code'])."', 
									redirect_source = '".$modx->db->escape($_POST['add']['source'])."', 
									redirect_target = '".$modx->db->escape($_POST['add']['target'])."'");
			}
			if (count($_POST['edit']) > 0) 
				$modx->db->query("update `modx_a_redirect` set 
									redirect_code     = '".$modx->db->escape($_POST['edit']['code'])."', 
									redirect_source   = '".$modx->db->escape($_POST['edit']['source'])."', 
									redirect_target   = '".$modx->db->escape($_POST['edit']['target'])."' 
									where redirect_id = '".$modx->db->escape($_POST['edit']['redirect'])."'");
			$redirects = $modx->db->query("select * from `modx_a_redirect` order by redirect_id desc limit 20");
			$tpl = "/tpl/redirect.tpl";
		break;
	}

	if (isset($tpl)) {
		ob_start();
		include ROOT . $tpl;
		$res['content'] = ob_get_contents();
		ob_end_clean();
	}
	include ROOT . "/tpl/index.tpl";
	die;
