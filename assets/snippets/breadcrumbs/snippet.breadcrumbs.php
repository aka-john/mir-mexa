<?php
/*
    Breadcrumbs Next Generation
    (c) kharkiv.adminko@gmail.com 
    coded 2013-01-06
    ---
    Params:
    name              default        
    ============================ 
    &id               page_id         
    &showHomeCrumb    true            
    &showCurrCrumb    true 
    &separator        &rarr;
    &title            pagetitle
    
    &outerClass
    &crumbClass
    &laseCrumbClass
    
    &outerTpl
    &crumbTpl
    &lastCrumbTpl     last
    
*/
    $id              = isset($id) ? $id : $modx->documentIdentifier;
    $showHomeCrumb   = isset($showHomeCrumb) ? $showHomeCrumb : 1;
    $showCurrCrumb   = isset($showCurrCrumb) ? $showCurrCrumb : 1;
    $respectHidemenu = isset($respectHidemenu) ? $respectHidemenu : 1;
    $separator       = isset($separator) ? $separator : '<span class="divider"></span>';
    $title           = isset($title) ? $title : 'pagetitle';
    $lastCrumbClass  = "last";
    $documents       = Array();
    $lastLink        = isset($lastLink) ? $lastLink : false;
    
    $outerTpl      = '<ul id="breadcrumbs" class="'.$outerClass.'">[+crumbs+]</ul>';
    if ($lastLink) {
        $crumbTpl      = $lastCrumbTpl  = '<li class="'.$crumbClass.'"><a href="[~[+url+]~]">[+title+]</a> '.$separator.'</li>';
        $lastCrumbTpl .= '<li class="'.$lastCrumbClass.'"><span>[*name*]</span></li>';
    } else {
        $crumbTpl      = '<li class="'.$crumbClass.'"><a href="[~[+url+]~]">[+title+]</a> '.$separator.'</li>';
        $lastCrumbTpl  = '<li class="'.$lastCrumbClass.'"><span>[+title+]</span></li>';
    }
    
    $lang          = $modx->config['lang'];
    $parse         = "";
    if ($showHomeCrumb) $documents[] = $modx->getConfig('site_start');
    $documents     = array_merge($documents, array_reverse(array_values($modx->getParentIds($id))));
    if ($showCurrCrumb) $documents[] = $id;
    $document      = implode(',', $documents);
    $query         = "select 
                            id,
                            hidemenu,
                            pagetitle".($modx->config['lang_enable'] ? "_".$lang : "")." as 'pagetitle',
                            menutitle".($modx->config['lang_enable'] ? "_".$lang : "")." as 'menutitle'
                      from `modx_site_content`
                      where id in (".$document.") 
                      order by field(id, ".$document.")";
    $crumbs        =   $modx->db->query($query);
                                 
    foreach ($documents as $value) {
             $crumb  = $modx->db->getRow($crumbs);
             if ($crumb['hidemenu'] == 0 || $showHomeCrumb == 1 && $crumb['id'] == 1) {
                $parse .= strtr(($value == end($documents) ? $lastCrumbTpl : $crumbTpl), 
                             Array("[+url+]"   => $value, 
                                   "[+title+]" => ($title == "menutitle" ? $crumb['menutitle'] : $crumb['pagetitle'])));
             }
    }

    $crumbs = str_replace("[+crumbs+]", $parse, $outerTpl);
    return $crumbs;
?>