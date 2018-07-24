<?php

class Sales_Model_DbTable_DbPartnerservice extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_partnerservice';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function getAllPartnerService($search){
    	$db = $this->getAdapter();
    	$sql=" SELECT
		    	id,
		    	partner_name,
		    	(SELECT name_kh FROM tb_view WHERE TYPE=19 AND key_code=gender) AS gender,
		    	tel,
		    	addresss,
		    	service_fee,
		    	description,
		    	(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id =`user_id`) AS user_name,
		    	(SELECT name_en FROM `tb_view` WHERE TYPE=5 AND key_code= STATUS LIMIT 1) STATUS 
		    	FROM
		    	tb_partnerservice 
		    	WHERE 
		    	partner_name!=''
    		";
    	$where = ''; 	
    	if(!empty($search['ad_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['ad_search']));
    		$s_where[] = " partner_name LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['service']>0){
    		$where .= " AND service_cate = ".$search['service'];
    	}
    	if($search['status']>-1){
    		$where .= " AND status = ".$search['status'];
    	}
    	$order=" ORDER BY id DESC ";
   // 	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addService($post){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    	$_arr=array(
    			'partner_name' 		 => $post['partner_name'],
    			'gender'			 => $post['gender'],
    			'tel' 				 => $post['tel'],
    			'addresss'	      	 => $post['addresss'],
    	//		'service_cate'	     => $post['service_cate'],
    			'service_fee'	     => $post['service_fee'],
    			'description'	     => $post['description'],
    	);
    	$this->_name="tb_partnerservice";
    	$id = $this->insert($_arr);
    	
    	if(!empty($post['identity'])){
    		$iden = explode(",", $post['identity']);
    		foreach ($iden as $i){
    			$arra=array(
    					'service_id'		=> $id,
    					'service_cate'		=> $post['service_cate'.$i],
    					'prices'			=> $post['prices_'.$i],
    					'notes'				=> $post['notes_'.$i],
    			);
    			$this->_name="tb_partner_cost";
    			$this->insert($arra);
    		}
    	//	print_r($this->$post); exit();
    	}
    	$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }  
    function getPartnerCostById($id){
    	$db = $this->getAdapter();
    	$sql ="SELECT 
				 p.*,
				 (SELECT item_name FROM `tb_product` WHERE id=p.service_cate LIMIT 1) AS service_name,
				 (SELECT barcode FROM `tb_product` WHERE id=p.service_cate LIMIT 1) AS service_barcode 
				FROM
				  `tb_partner_cost` AS p 
				WHERE p.service_id = $id ";
    	return $db->fetchAll($sql);
    }
//     function getServices($data){
//     	$db = $this->getAdapter();
//     	$sql = "SELECT id,partner_name,gender,tel,addresss,service_cate,service_fee,description FROM tb_partnerservice";
//     	return $db->fetchAll($sql);
//     }
    function getAllService(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,barcode,item_name FROM tb_product WHERE item_name!='' AND status=1 AND is_service=1 AND is_package=0 ORDER BY item_name ASC";
    	return $db->fetchAll($sql);
    }
    public function updateservice($post, $id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    //	print_r($post);exit();
    	$_arr=array(
    			'partner_name' 		 => $post['partner_name'],
    			'gender'			 => $post['gender'],
    			'tel' 				 => $post['tel'],
    			'addresss'	      	 => $post['addresss'],
    			'service_cate'	 	=> $post['service_cate'],
    			'service_fee'	     => $post['service_fee'],
    			'description'	     => $post['description'],
    	);
    	$where="id= $id";
		$this->update($_arr, $where);
		
		$this->_name="tb_partner_cost";
		$where=" service_id = $id";
		$this->delete($where);
		
		if(!empty($post['identity'])){
			$iden = explode(",", $post['identity']);
			foreach ($iden as $i){
				$arra=array(
						'service_id'		=> $id,
						'service_cate'		=> $post['service_cate'.$i],
						'prices'			=> $post['prices_'.$i],
						'notes'				=> $post['notes_'.$i],
				);
				$this->_name="tb_partner_cost";
				$this->insert($arra);
			}
			//	print_r($this->$post); exit();
		}
		$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
    }
    public function getServiceById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM tb_partnerservice WHERE id = $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
 
   
}

