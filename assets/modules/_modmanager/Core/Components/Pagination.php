<?php

namespace Core\Components;
use Core\Components\Request;
use Core\Config\Config;

/*
 * Pagination class
 */
class Pagination {
    public $base_url = '';
    public $display = '';
    public $page = '';
    
    /*
     * Init pagination
     * @param int display
     * @param int page number
     * @return ''
     */
    public function init($display = null, $page = null) {
        $request = Request::getRequest();
     
        $display = current(Config::loadConfigFile('grid')->getNode('display/item')->nodeToArray()); //change reset to current by eyrad4
                
        $this->page = $page != null ? $page : '';
        $this->display = $display != null ? $display : '';
        
        $this->page = !isset($request['page']) || $request['page'] == 0 ? 1 : $request['page'];
        $this->display = isset($request['grid_display']) ? $request['grid_display'] : $display['value'];
        $limit = $this->display == '' ? (int)$request['grid_display'] : (int)$this->display;
        $start = (int)($this->page - 1) * $limit;
        
        Request::setRequestValue('grid_limit', abs($limit));
        Request::setRequestValue('grid_start', abs($start));
    }

    /*
     * Build pagination
     * @param int total items
     * @return ''
     */
    public function build($total = null) {
        Request::setRequestValue('grid_total_view', abs($total));
        Request::setRequestValue('grid_display_view', $this->display*$this->page > $total ? $total : $this->display*intval($this->page));

		$totPages    = ceil($total / $this->display);
		$curentPage  = intval($this->page) == 0 ? 1 : intval($this->page);
		$pagesBefore = $this->page - 1;
		$pagesAfter  = $totPages - $this->page;
		$tabArr      = array();
        if($totPages > 15) {
            if($pagesBefore > 7) {
                $tabArr = array(1,2,0);
                if($pagesAfter > 7)
                {
                    for($i=($this->page-(4)); $i<$this->page; $i++) { $tabArr[] = $i; }
                } else {
                    for($i=($totPages-11); $i<$this->page; $i++) { $tabArr[] = $i; }
                }
            } else {
                for($i=1; $i<$this->page; $i++) { $tabArr[] = $i; }
            }
            $tabArr[] = $this->page;
            if($pagesAfter > 7) {
                if($pagesBefore > 7) {                          
                    for($i=($this->page+1); $i<=$this->page+4; $i++) { $tabArr[] = $i; }
                } else {
                    for($i=($this->page+1); $i<13; $i++) { $tabArr[] = $i; }
                }
                $tabArr[] = 0;
                $tabArr[] = $totPages-1;
                $tabArr[] = $totPages;
            } else {
                for($i=($this->page+1); $i<=$totPages; $i++) { $tabArr[] = $i; }
            } 
        } else {
            for($i=1;$i<=$totPages;$i++) { $tabArr[] = $i; }
        }  

        $pagination = '';
        $left = '';
        $right = '';

        foreach ($tabArr as $page) {
		    if($page == 0) {
	            $pagination .= '<li><a>...</a></li>';
		    } elseif ($page == $curentPage) {
	            $pagination .= '<li class="disabled"><a href="'.CRM_CURRENT_URL().http_build_query(array_merge($_REQUEST,array('page' => $page))).'">'.$page.'</a></li>';                
		    } else {
	            $pagination .= '<li><a href="'.CRM_CURRENT_URL().http_build_query(array_merge($_REQUEST,array('page' => $page))).'">'.$page.'</a></li>';
		    }
        }

		if ($totPages > 1) {
            if ($curentPage > 1) {
                $page = $curentPage - 1;
				$left = '<li><a href="'.CRM_CURRENT_URL().http_build_query(array_merge($_REQUEST,array('page' => $page))).'"> Назад </a></li>';
            }

            if ($curentPage == 0 || $curentPage*$this->display < $total) {
                $page  = $curentPage + 1;
				$right = '<li><a href="'.CRM_CURRENT_URL().http_build_query(array_merge($_REQUEST,array('page' => $page))).'"> Вперёд </a></li>';
            }
		}

        if ($totPages > 1) {
			return '
			    <ul class="pagination pull-left">
					'.$left.'
					'.$pagination.'
					'.$right.'
			    </ul>
			';
		} else {
			return '';
		}
    }
}
