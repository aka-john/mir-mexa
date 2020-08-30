<?php
$output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
$output .='<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

$pages = $modx->db->query("select id,editedon from `modx_site_content` where searchable = 1 and published = 1 and deleted = 0");

$no_parents = array(1);
$no_ids = array(5,26,16,7);

while ($doc = $modx->db->getRow($pages)) {
    if (!in_array($doc['id'], $no_ids) && !in_array($doc['parent'], $no_parents)) {
        $url  = $modx->makeUrl($doc['id'],'','','full');
        $date = $doc['editedon'];
        $date = date("Y-m-d", $date);
        $docPriority   = ($doc[$priority]) ? $doc[$priority] : 0;
        $docChangefreq = ($doc[$changefreq]) ? $doc[$changefreq] : 0;
    
        $level = count($modx->getParentIds($doc['id']));
        if ($level  == 0 && $doc['id'] != 1) {$level = 0.9;}
        if ($level  == 1) {$level = 0.9;}
        if ($level  == 2) {$level = 0.8;}
        if ($level  == 3) {$level = 0.5;}
        if ($level  == 4) {$level = 0.4;}            
        if ($doc['id'] == 1) {$level = 1;$url = $modx->config['site_url'];}
            $output .= "\t".'<url>'."\n";
            $output .= "\t\t".'<loc>'.$url.'</loc>'."\n";
            $output .= "\t\t".'<lastmod>'.$date.'</lastmod>'."\n";
            $output .= "\t\t".'<priority>'.$level.'</priority>'."\n";
            $output .= ($docPriority) ? ("\t\t".'<priority>'.$docPriority.'</priority>'."\n") : '';
            $output .= ($docChangefreq) ? ("\t\t".'<changefreq>'.$docChangefreq.'</changefreq>'."\n") : '';
            $output .= "\t".'</url>'."\n";
    }
}

$pages = $modx->db->query("select p.id,p.created_at,p.alias,(select modx_id from modx_mm_p2c_link where product_id = p.id limit 1) as modx_id from `modx_mm_products` p where p.searchable = 1 and p.published = 1");
while ($doc = $modx->db->getRow($pages)) {
	$url  = $modx->makeUrl((int)$doc['modx_id'],'','','full').$doc['alias'].'/';
	$date = $doc['created_at'];
	$date = date("Y-m-d", strtotime($date));
	$docPriority   = ($doc[$priority]) ? $doc[$priority] : 0;
	$docChangefreq = ($doc[$changefreq]) ? $doc[$changefreq] : 0;
	
	$level = count($modx->getParentIds($doc['id']));
	if ($level  == 0 && $doc['id'] != 1) {$level = 0.9;}
	if ($level  == 1) {$level = 0.9;}
	if ($level  == 2) {$level = 0.8;}
	if ($level  == 3) {$level = 0.5;}
	if ($level  == 4) {$level = 0.4;}            
	if ($doc['id'] == 1) {$level = 1;$url = $modx->config['site_url'];}
	$output .= "\t".'<url>'."\n";
	$output .= "\t\t".'<loc>'.$url.'</loc>'."\n";
	$output .= "\t\t".'<lastmod>'.$date.'</lastmod>'."\n";
	$output .= "\t\t".'<priority>'.$level.'</priority>'."\n";
	$output .= ($docPriority) ? ("\t\t".'<priority>'.$docPriority.'</priority>'."\n") : '';
	$output .= ($docChangefreq) ? ("\t\t".'<changefreq>'.$docChangefreq.'</changefreq>'."\n") : '';
	$output .= "\t".'</url>'."\n";
}

$output .= '</urlset>';
echo $output;