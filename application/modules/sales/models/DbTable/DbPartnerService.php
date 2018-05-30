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
//     	id,
//     	dead_name,
//     	(select name_kh from tb_view where type=19 and key_code=dead_sex) as dead_sex,
//     	dead_age,date_jom,
//     	dead_address,
//     	(select donor_name from tb_donors where tb_donors.id = donor_id) as donor_name,
//     	date_jenh,
//     	note,notes,
//     	create_date,
//     	(SELECT fullname FROM `tb_acl_user` as u WHERE u.user_id=d.user_id LIMIT 1) AS user_name,
//     	(SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=d.status LIMIT 1) status
//     	FROM
//     	tb_donor_donate as d
//     	WHERE
//     	dead_name!=''
//     	and donor_id>0
    	";
    	$where = ''; 	
    	if(!empty($search['ad_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['ad_search']));
    		$s_where[] = " dead_name LIKE '%{$s_search}%'";
    		$s_where[] = " (select donor_name from tb_donors where tb_donors.id = donor_id) LIKE '%{$s_search}%'";
    		$s_where[] = " (select name_kh from tb_view where type=19 and key_code=dead_sex) LIKE '%{$s_search}%'";
    		$s_where[] = " dead_address LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['status']>-1){
    		$where .= " AND status = ".$search['status'];
    	}
    	if($search['branch']>0){
    		$where .= " AND branch_id = ".$search['branch'];
    	}
    	$order=" ORDER BY id DESC ";
   // 	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
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
 
   
}

