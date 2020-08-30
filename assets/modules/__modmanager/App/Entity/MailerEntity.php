<?php

namespace App\Entity;

use Core\Components\Database;
use Core\Components\Request;

/*
 * Review entity class, extend /Core/Components/Database class
 */
class MailerEntity extends Database
{

    public function __construct() 
    {
        
    }
    
    /*
     * Generate list of subscribers by request params
     * buildGrideFilters - build filters by request params
     * @return array
     */
    public function getGridSubscribers() 
    {
        $request = Request::getRequest();
        
        $grid_sort_dir = isset($request['grid_sort_dir']) && $request['grid_sort_dir'] != '' ? $request['grid_sort_dir'] : 'row.id';
        $grid_sort_by = isset($request['grid_sort_by']) && $request['grid_sort_by'] != '' ? $request['grid_sort_by'] : 'DESC';
        
        $query = $this->query("SELECT  
                    SQL_CALC_FOUND_ROWS 
                    row.* 
                FROM modx_mm_mailer_user row 
                ".($this->buildGrideFilters() != '' ? $this->buildGrideFilters() : '')."
                ORDER BY ".$grid_sort_dir." ".$grid_sort_by." 
                LIMIT ".$request['grid_limit']."  
                OFFSET ".$request['grid_start']);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Generate list of letters by request params
     * buildGrideFilters - build filters by request params
     * @return array
     */
    public function getGridLetters() 
    {
        $request = Request::getRequest();
        
        $grid_sort_dir = isset($request['grid_sort_dir']) && $request['grid_sort_dir'] != '' ? $request['grid_sort_dir'] : 'row.id';
        $grid_sort_by = isset($request['grid_sort_by']) && $request['grid_sort_by'] != '' ? $request['grid_sort_by'] : 'DESC';
        
        $query = $this->query("SELECT  
                    SQL_CALC_FOUND_ROWS 
                    row.* 
                FROM modx_mm_mailer_letter row 
                ".($this->buildGrideFilters() != '' ? $this->buildGrideFilters() : '')."
                ORDER BY ".$grid_sort_dir." ".$grid_sort_by." 
                LIMIT ".$request['grid_limit']."  
                OFFSET ".$request['grid_start']);
        $rows = $this->fetchAll($query);
        return $rows;
    }

    /*
     * Get subscriber fields
     * @param int subscriber id
     * @return single result array
     */
    public function getSubscriber($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_mailer_user row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Get letter fields
     * @param int letter id
     * @return single result array
     */
    public function getLetter($id) 
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_mailer_letter row 
                WHERE id = ".intval($id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }
    
    /*
     * Add subscriber
     * @param array subscriber fields
     * @return inserted id
     */
    public function addSubscriber($fields)
    {
        $sql = "INSERT INTO modx_mm_mailer_user (
                    name,
                    email,
                    user_id,
                    created_at,
                    status
                ) VALUES (
                    '".$this->escape($fields['name'])."',
                    '".$this->escape($fields['email'])."',
                    ".intval($fields['user_id']).",
                    '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
                    ".intval($fields['status'])."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }
    
    /*
     * Add letter
     * @param array letter fields
     * @return inserted id
     */
    public function addLetter($fields)
    {
        $sql = "INSERT INTO modx_mm_mailer_letter (
                    name,
                    subject,
                    text,
                    chunk_id,
                    resource_id,
                    created_at,
                    type
                ) VALUES (
                    '".$this->escape($fields['name'])."',
                    '".$this->escape($fields['subject'])."',
                    '".$this->escape($fields['text'])."',
                    ".intval($fields['chunk_id']).",
                    ".intval($fields['resource_id']).",
                    '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
                    ".intval($fields['type'])."
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }

    /*
     * Update subscriber
     * @param int subscriber id
     * @param array subscriber fields
     * @return single result array
     */
    public function updateSubscriber($id, $fields)
    {
        $sql = "UPDATE modx_mm_mailer_user SET 
			name    = '".$this->escape($fields['name'])."',
			email   = '".$this->escape($fields['email'])."',
			created_at = '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
			user_id = ".intval($fields['user_id']).",
            status = ".intval($fields['status'])." 
        WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getSubscriber($id);
    }
    
    /*
     * Update letter
     * @param int letter id
     * @param array letter fields
     * @return single result array
     */
    public function updateLetter($id, $fields)
    {
        $sql = "UPDATE modx_mm_mailer_letter SET 
			name    = '".$this->escape($fields['name'])."',
			subject   = '".$this->escape($fields['subject'])."',
            text   = '".$this->escape($fields['text'])."',
            chunk_id = ".intval($fields['chunk_id']).", 
            resource_id = ".intval($fields['resource_id']).", 
			created_at = '".($fields['created_at'] == '' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', strtotime($fields['created_at'])))."',
            type = ".intval($fields['type'])." 
        WHERE id = ".intval($id);
		$query = $this->query($sql);
		return $this->getLetter($id);
    }

    /*
     * Remove subscriber
     * @param int subscriber id
     * @return ''
     */
    public function removeSubscriber($id)
    {
        $this->query("DELETE FROM modx_mm_mailer_user WHERE id = ".intval($id));
    }
    
    /*
     * Remove letter
     * @param int letter id
     * @return ''
     */
    public function removeLetter($id)
    {
        $this->query("DELETE FROM modx_mm_mailer_letter WHERE id = ".intval($id));
    }
   
    /*
     * Get all subscribers 
     * @return array
     */
    public function getAllSubscribers()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_mailer_user row";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all letters 
     * @return array
     */
    public function getAllLetters()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_mailer_letter row";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all letters and mail status
     * @param int subscriber id
     * @return array
     */
    public function getAllLettersAndMailStatus($subscriber_id = 0)
    {
        $sql = "SELECT 
                    row.id, 
                    IF( (SELECT mailed FROM modx_mm_mailer WHERE letter_id = row.id AND subscriber_id = ".intval($subscriber_id).") = 1 , CONCAT(row.name, ' - ', 'отправлено') , CONCAT(row.name, ' - ', 'не отправлено')) as name
                FROM modx_mm_mailer_letter row";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get all web users
     * @return array
     */
    public function getAllWebUsers()
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_web_user_attributes row
                WHERE row.internalKey not IN (SELECT user_id FROM modx_mm_mailer_user)";
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        return $rows;
    }
    
    /*
     * Get subscriber letters by id
     * @param int subscriber id
     * @return array
     */
    public function getSubscriberLettersIds($id)
    {
        $sql = "SELECT 
                    row.* 
                FROM modx_mm_mailer row
                WHERE row.subscriber_id = ".intval($id);
        $query = $this->query($sql);
        $rows = $this->fetchAll($query);
        
        $result = array();
        
        foreach ($rows as $key => $value) {
            $result[] = $value['letter_id'];
        }
        
        return $result;
    }
    
    /*
     * Letter exist
     * param int subscriber id
     * param int letter id
     * @return array
     */
    public function existLetter($subscriber_id, $letter_id)
    {
        $sql = "SELECT 
                    row.id 
                FROM modx_mm_mailer row
                WHERE row.letter_id = ".intval($letter_id)." AND row.subscriber_id = ".intval($subscriber_id);
        $query = $this->query($sql);
        $row = $this->fetchOne($query);
        return $row;
    }

    /*
     * Add letter to subscriber
     * param int subscriber id
     * param int letter id
     * @return inserted id
     */
    public function addLettersToSubscriber($subscriber_id, $letter_id)
    {
        $sql = "INSERT INTO modx_mm_mailer (
                    subscriber_id,
                    letter_id,
                    mailed
                ) VALUES (
                    ".intval($subscriber_id).",
                    ".intval($letter_id).",
                    0
                )
        ";
		$this->query($sql);
		return $this->getInsertId();
    }
    
    /*
     * Remove subscriber letters
     * param array letter ids
     * @return ''
     */
    public function removeSubscriberLettersByIds($ids = array())
    {
        $this->query("DELETE FROM modx_mm_mailer WHERE id not IN (".implode(',', $ids).") ");
    }
}

