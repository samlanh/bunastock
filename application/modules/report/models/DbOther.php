<?php 
Class report_Model_DbOther extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
function getAllDonors($search){
		$db = $this->getAdapter();
		$sql=" SELECT 
					id,
					donor_name,
					donor_female,
					tel,
					address,
					required_using,
					invalid_date,
					note,
					receipt_no,
					paid_date,
					qty,
					unit_price,
					total_amount,
					payment_note,
					create_date,
					(SELECT fullname FROM `tb_acl_user` as u WHERE u.user_id=d.user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=d.status LIMIT 1) status
		 		FROM 
					tb_donors as d
				WHERE 
					donor_name!='' 
			";
		
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " donor_name LIKE '%{$s_search}%'";
			$s_where[] = " receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " tel LIKE '%{$s_search}%'";
			$s_where[] = " address LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['branch']>0){
			$where .= " AND branch_id = ".$search['branch'];
		}
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		$order=" ORDER BY id DESC ";
		
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
}
	function getAllsposorship($search){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT 
					id,
					dead_name,
					(select name_kh from tb_view where type=19 and key_code=dead_sex) as dead_sex,
					dead_age,date_jom,
					dead_address,
					(select donor_name from tb_donors where tb_donors.id = donor_id) as donor_name,
					date_jenh,
					note,notes,
					create_date,'សប្បុរសជន',
					(SELECT fullname FROM `tb_acl_user` as u WHERE u.user_id=d.user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=d.status LIMIT 1) status
		 		FROM 
					tb_donor_donate as d
				WHERE 
					dead_name!='' 
					and donor_id>0
			";
		
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " dead_name LIKE '%{$s_search}%'";
 			$s_where[] = " (select donor_name from tb_donors where tb_donors.id = donor_id) LIKE '%{$s_search}%'";
 			$s_where[] = " (select name_kh from tb_view where type=19 and key_code=dead_sex) LIKE '%{$s_search}%'";
 			$s_where[] = " dead_address LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['status']>0){
			$where .= " AND status = ".$search['status'];
		}
		if($search['branch']>0){
			$where .= " AND branch_id = ".$search['branch'];
		}

		$order=" ORDER BY id DESC ";
		
 	//	echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllsaleMong($search){
		$db = $this->getAdapter();	
		$sql=" SELECT
				id,
				invoice_no,
				(SELECT cust_name FROM `tb_customer` AS c WHERE c.id=m.customer_id LIMIT 1 ) AS customer_name,
					
				(select dead_name from tb_program as p where p.id=m.dead_id LIMIT 1) as dead_id,
				(select name_kh from tb_view where type=20 and key_code=m.construct_type LIMIT 1) as construct_type,
				mong_code,
					
				(SELECT name FROM `tb_person_in_charge` AS p WHERE p.id=m.person_in_charge LIMIT 1 ) as person_in_charge,
				(SELECT name FROM `tb_constructor` AS c WHERE c.id=m.constructor LIMIT 1 ) as constructor,
				sale_date,
				sub_total,
				paid,
				balance_after,
					
				'វិក័យបត្រ',
				'សែនបើកឆាក',
				'សែនឆ្លងម៉ុង',
				other_note,
				(SELECT fullname FROM tb_acl_user as u WHERE user_id=user_id LIMIT 1) AS user_name,
				(SELECT name_en FROM tb_view WHERE type=5 AND key_code=status LIMIT 1) status
				FROM
				tb_mong as m
				WHERE
				1
				";
		$where= '';
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT cust_name FROM `tb_customer` AS c WHERE c.id=m.customer_id LIMIT 1 ) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		if($search['branch']>0){
			$where .= " AND branch_id = ".$search['branch'];
		}
		$order=" ORDER BY id DESC ";
	//	echo $sql.$where.$order; exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllpaymentList($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
//		$user_id = $this->getUserId();
		$sql ="SELECT
				p.`id`,
				(SELECT b.name FROM `tb_sublocation` AS b WHERE b.id=pl.`location_id` LIMIT 1) AS branch,
				p.`item_code`,
				p.`item_name` ,
				(SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
				(SELECT v.`name_en` FROM tb_view AS v WHERE v.`type`=16  AND p.`is_service`=v.`key_code` LIMIT 1) AS is_service,
				(SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
				SUM(pl.`qty`) AS qty,
				p.selling_price AS master_price,
				p.price,
				(SELECT `fullname` FROM `tb_acl_user` WHERE `user_id`=p.`user_id` LIMIT 1) AS user_name,
				(SELECT v.`name_en` FROM tb_view AS v WHERE v.`type`=5  AND p.`status`=v.`key_code` LIMIT 1) AS status
				FROM
				`tb_product` AS p ,
				`tb_prolocation` AS pl
				WHERE p.`id`=pl.`pro_id` ";
		$where = '';
		if($data["ad_search"]!=""){
			$string = str_replace(' ','',$data['ad_search']);
			$s_where=array();
			$s_search = addslashes(trim($string));
			$s_where[]=" REPLACE(p.item_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(p.barcode,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(p.item_code,' ','') LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]>0){
			$where.=' AND p.brand_id='.$data["branch"];
		}
		if($data["status"]>-1){
			$where.=' AND p.status='.$data["status"];
		}
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		$group_by = " GROUP BY p.id DESC ";
//		echo $sql.$where.$location.$group_by; exit();
		return $db->fetchAll($sql.$where.$location.$group_by);
	}
function getAllworker($search){
		$db = $this->getAdapter();	
		$sql=" SELECT 
					id,
					name,
					(select name_kh from tb_view where type=19 and key_code=sex LIMIT 1) as sex,
					phone,
					email,
					address,
					(select name_kh from tb_view where type=20 and key_code=constructor_type LIMIT 1) as constructor_type,
					note,
					create_date,
					(SELECT fullname FROM tb_acl_user as u WHERE user_id=user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM tb_view WHERE type=5 AND key_code=status LIMIT 1) status
		 		FROM 
					tb_constructor
				WHERE 
					name!=''
			";			
		$where = "  ";
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " name LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		$order=" ORDER BY id DESC ";
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getAlllistSponorship($id){
		$db = $this->getAdapter();
		$sql=" SELECT 
					id,
					dead_name,
					(SELECT name_kh FROM tb_view WHERE TYPE=19 AND key_code=dead_sex) AS dead_sex,
					dead_age,date_jom,
					dead_address,
					(SELECT donor_name FROM tb_donors WHERE tb_donors.id = donor_id) AS donor_name,
					date_jenh,
					note,notes,
					create_date,'សប្បុរសជន',
					(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id=d.user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM `tb_view` WHERE TYPE=5 AND key_code=d.status LIMIT 1) STATUS
		 		FROM 
					tb_donor_donate AS d
				WHERE 
					dead_name!='' 
					AND donor_id>0
									
					AND  d.`donor_id`= $id
					";	 
		return $db->fetchAll($sql);
	}
// 	function getQtyProductByProIdLoca($id,$loc_id){
// 		$db = $this->getAdapter();
// 		$sql = "SELECT pl.`qty` FROM `tb_prolocation` AS pl  WHERE pl.`pro_id`=$id AND pl.`location_id`=$loc_id";
// 		return $db->fetchOne($sql);
// 	}
// 	function getAllLOcation(){
// 		$db = $this->getAdapter();
// 		$sql = "SELECT s.`prefix`,s.`id`  FROM `tb_sublocation` AS s WHERE s.`status`=1";
// 		return $db->fetchAll($sql);
// 	}
	
// 	function getAllAdjustStock($data){
// 		$db = $this->getAdapter();
// 		$db_globle = new Application_Model_DbTable_DbGlobal();
// 		$sql ="SELECT 
// 				  m.* ,
// 				  p.`item_name`,
// 				  p.`barcode`,
// 				  p.`item_code`,
// 				  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id` = p.`brand_id`) AS brand ,
// 				  (SELECT b.`name` FROM `tb_category` AS b WHERE b.`id` = p.`cate_id`) AS cat ,
// 				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=4) AS color,
// 				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=2) AS model,
// 				  (SELECT v.`name_en` FROM `tb_view` AS v WHERE v.id = p.`color_id` AND v.`type`=3) AS size,
// 				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
// 				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.id=m.`location_id` LIMIT 1) AS location,
// 				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=m.`user_mod` LIMIT 1) AS `username`,
// 				   m.`date`
// 				FROM
// 				  `tb_move_history` AS m ,
// 				  `tb_product` AS p
// 				WHERE m.`pro_id`=p.`id`";
// 		$where = '';
// 		if($data["ad_search"]!=""){
// 			$s_where=array();
// 			$s_search = addslashes(trim($data['ad_search']));
// 			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
// 			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
// 			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
// 			$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
// 			//$s_where[]= " cate LIKE '%{$s_search}%'";
// 			$where.=' AND ('.implode(' OR ', $s_where).')';
// 		}
// 		if($data["pro_id"]!=""){
// 			$where.=' AND m.pro_id='.$data["pro_id"];
// 		}
// 		$location = $db_globle->getAccessPermission('m.`location_id`');
// 		//echo $location;
// 		return $db->fetchAll($sql.$where.$location);
			
// 	}
	
}

?>