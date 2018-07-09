<?php

class Sales_Model_DbTable_DbRepay extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_borrowers';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function getAllRepay($search){
    	$db = $this->getAdapter();
    	$sql=" SELECT id,
					name_borrow,
					(SELECT name_kh FROM tb_view WHERE TYPE=19 AND key_code=gender) AS gender,
					phone,
					DATE,
					qtys,
					notes,
					(SELECT name_kh FROM tb_view AS v WHERE v.type=5 AND v.key_code = tb_borrowers.status) AS STATUS
					FROM `tb_borrowers`
					WHERE name_borrow!='' AND  type=2
    	";
    	$where = '';

    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " and ".$from_date." AND ".$to_date;
    	if(!empty($search['ad_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['ad_search']));
    		$s_search = str_replace(' ', '', $s_search);
    		$s_where[] = "REPLACE(name_borrow,'','') LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where .= " AND status = ".$search['status'];
    	}
    	$order=" ORDER BY id DESC ";
  //  	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addRepays($post){
    	$_arr=array(
    			'name_borrow' 		 => $post['name_borrow'],
    			'gender'			 => $post['gender'],
    			'phone' 			 => $post['phone'],
    			'date'				 => empty($post['date'])?null:date("Y-m-d H:i:s",strtotime($post['date'])),
    			'qtys'	     	     => $post['qtys'],
    			'notes'	     	     => $post['notes'],
    			'status'	         => 1,
    			'type'	      	     => 2,
    	);
    	return  $this->insert($_arr);
    }
    function getAllRepays(){
    	$db = $this->getAdapter();
    	$sql=" SELECT DISTINCT(name_borrow) AS name FROM tb_borrowers WHERE name_borrow!=''";
    	return $db->fetchAll($sql);
    }
    public function updateRepay($post, $id){
    //	print_r($post);exit();
    	$_arr=array(
    			'name_borrow' 		 => $post['name_borrow'],
    			'gender'			 => $post['gender'],
    			'phone' 			 => $post['phone'],
    			'date'				 => empty($post['date'])?null:date("Y-m-d H:i:s",strtotime($post['date'])),
    			'qtys'	     	     => $post['qtys'],
    			'notes'	     	     => $post['notes'],
    			'status'	         => $post['status'],
    			'type'	      	     => 2,
    	);
    	$where="id= $id";
		$this->update($_arr, $where);
    }
    public function getRepayById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM tb_borrowers WHERE id = $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
 
   
}
