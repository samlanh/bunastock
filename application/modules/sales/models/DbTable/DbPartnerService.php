<?php

class Sales_Model_DbTable_DbPartnerService extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_partnerservice';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    function getAllPartnerService($search=null){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,partner_name,
     	(SELECT v.name_en FROM `tb_view` AS v WHERE v.key_code=gender AND v.type=17 LIMIT 1)AS aline,
	    tel,addresss,
	    (SELECT p.item_name FROM `tb_product` AS p WHERE p.id=service_cate LIMIT 1) AS item_name,
	    service_fee,description 
		FROM tb_partnerservice WHERE partner_name!='' ORDER BY id DESC";
    	$where = '';
    	if(!empty($search['ad_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['ad_search']));
    		$s_where[]=" partner_name LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	} 
//     	     	if($search['partner_name']>0){
//     	     		$where.= " AND partner_name = ".$db->quote($search['partner_name']);
//     	     	}
     	$order=" ORDER BY id DE​SC ";
    // 	echo $sql.$where;
    	return $db->fetchAll($sql);
    }
    public function addService($post){
    	$_arr=array(
    			'partner_name' 		=> $post['partner_name'],
    			'gender' 			=> $post['gender'],
    			'tel' 				=> $post['tel'],
    			'addresss'	      	=> $post['addresss'],
    			'service_cate' 		=> $post['service_cate'],
    			'service_fee'		=> $post['service_fee'],
    			'description'	    => $post['description'],
    			'modify_date'     	=> Zend_Date::now(),
    			'user_id'	     	=> $this->getUserId()
    	);
    	return  $this->insert($_arr);
    } 
    public function updateService($post){
    	
    	//print_r($post); exit();
    	$_arr=array(
    			'partner_name' 		=> $post['partner_name'],
    			'gender' 			=> $post['gender'],
    			'tel' 				=> $post['tel'],
    			'addresss'	      	=> $post['addresss'],
    			'service_cate' 		=> $post['service_cate'],
    			'service_fee'		=> $post['service_fee'],
    			'description'	    => $post['description'],
    			'modify_date'     	=> Zend_Date::now(),
    			'user_id'	     	=> $this->getUserId()
    	);
    	$where=" id=".$post['id'];
    	$this->update($_arr, $where);
    }   
    public function getServiceById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * 
		FROM tb_partnerservice WHERE id= $id LIMIT 1";
     	return $db->fetchRow($sql);
      	}    
    function getAllService(){
     	$db  = $this->getAdapter();
     	$sql="SELECT id,barcode,item_name FROM tb_product 
     		WHERE item_name!='' AND STATUS=1 AND is_service=1 
     		AND is_package=0 ";
     	return $db->fetchAll($sql);
     } 
}
