<?php

class Sales_Model_DbTable_DbPartnerservice extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_partnerservice';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function addService($post){
    	$_arr=array(
    			'partner_name' 		 => $post['partner_name'],
    			'gender'			 => $post['gender'],
    			'tel' 				 => $post['tel'],
    			'addresss'	      	 => $post['addresss'],
    	//		'cread_date'		 => $post['Y-m-d'],
    	//		'offer_sevice'	     => $post['offer_sevice'],
    			'service_fee'	     => $post['service_fee'],
    			'description'	     => $post['description'],
    	);
    	return  $this->insert($_arr);
    }
    function getServices($data){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,partner_name,gender,tel,addresss,offer_sevice,service_fee,description FROM tb_partnerservice";
    	return $db->fetchAll($sql);
    }
    public function updateservice($post){
    	$_arr=array(
    			'partner_name' 		 => $post['partner_name'],
    			'gender'			 => $post['gender'],
    			'tel' 				 => $post['tel'],
    			'addresss'	      	 => $post['addresss'],
    	//		'cread_date'		 => $post['Y-m-d'],
    	//		'offer_sevice'	     => $post['offer_sevice'],
    			'service_fee'	     => $post['service_fee'],
    			'description'	     => $post['description'],
    	);
    	$where="id=".$post['id'];
		//echo $where; exit();
		$this->update($where);
    }
// 	public function getProvinceById($id){
// 		$db = $this->getAdapter();
// 		$sql = "SELECT * FROM ln_province WHERE province_id = ".$id;
// 		$sql.=" LIMIT 1";
// 		$row=$db->fetchRow($sql);
// 		return $row;
// 	}
//     public function updateProvince($_data,$id){
//     	$_arr=array(
//     			'code' 			   => $_data['code'],
//     			'province_en_name' => $_data['en_province'],
//     			'province_kh_name' => $_data['kh_province'],
//     			'displayby'	       => $_data['display'],
//     			'modify_date'      => Zend_Date::now(),
//     			'status'           => $_data['status'],
//     			'user_id'	       => $this->getUserId()
//     	);
//     	$where=$this->getAdapter()->quoteInto("province_id=?", $id);
//     	$this->update($_arr, $where);
//     }
 
   
}

