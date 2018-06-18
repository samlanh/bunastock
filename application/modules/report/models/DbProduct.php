<?php 
Class report_Model_DbProduct extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getBranch($id){
		$db = $this->getAdapter();
		$sql ="SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`='".$id."'";
		return $db->fetchOne($sql);
	}
	function getAllProduct($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT
					  p.`id`,
					  p.`barcode`,
					  p.`item_code`,
					  p.`item_name` ,
		  			  p.`status`,
		  			  p.`unit_label`,
					  p.`qty_perunit`,
					  p.`price`,
					  p.selling_price,
					  p.is_service,
					  pl.`location_id`,
					   (SELECT b.`name` FROM `tb_sublocation` AS b WHERE b.`id`=pl.`location_id` LIMIT 1) AS branch,
					  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id`=p.`brand_id` LIMIT 1) AS brand,
					  (SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
					  
					  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
					  SUM(pl.`qty`) AS qty
				FROM
					  `tb_product` AS p ,
					  `tb_prolocation` AS pl
				WHERE 
				    p.status=1
					AND p.`id`=pl.`pro_id` ";
		$where = '';
		
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]!=""){
			$where.=' AND pl.`location_id`='.$data["branch"];
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["status_qty"]>-1){
			if($data["status_qty"]==1){
				$where.=' AND pl.qty>0';
			}else{
				$where.=' AND pl.qty=0';
			}
		}
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		$group = " GROUP BY p.`id` ORDER BY p.`id`";
		return $db->fetchAll($sql.$where.$location.$group);
	}
	function getAllcurrentstock($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT
		p.`id`,
		p.`barcode`,
		p.`item_code`,
		p.`item_name` ,
		p.`status`,
		p.`unit_label`,
		p.`qty_perunit`,
		p.`price`,
		p.selling_price,
		pl.`location_id`,
		(SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
		(SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
		SUM(pl.`qty`) AS qty
		FROM
		`tb_product` AS p ,
		`tb_prolocation` AS pl
		WHERE
		p.status=1
		AND p.`id`=pl.`pro_id` ";
		$where = '';
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]=" p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if($data["branch"]!=""){
			$where.=' AND pl.`location_id`='.$data["branch"];
		}
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		if($data["status_qty"]>-1){
			if($data["status_qty"]==1){
				$where.=' AND pl.qty>0';
			}else{
				$where.=' AND pl.qty=0';
			}
				
		}
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		$group = " GROUP BY p.`id` ORDER BY p.id";
		return $db->fetchAll($sql.$where.$location.$group);
	}
	
	function getQtyProductByProIdLoca($id,$loc_id){
		$db = $this->getAdapter();
		$sql = "SELECT pl.`qty` FROM `tb_prolocation` AS pl  WHERE pl.`pro_id`=$id AND pl.`location_id`=$loc_id";
		return $db->fetchOne($sql);
	}
	function getAllLOcation(){
		$db = $this->getAdapter();
		$sql = "SELECT s.`prefix`,s.`id`  FROM `tb_sublocation` AS s WHERE s.`status`=1";
		return $db->fetchAll($sql);
	}
	
	function getAllAdjustStock($data){
		$db = $this->getAdapter();
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$sql ="SELECT 
				  a.* ,
				  p.`item_name`,
				  p.`barcode`,
				  p.`item_code`,
				  (SELECT b.`name` FROM `tb_brand` AS b WHERE b.`id` = p.`brand_id`) AS brand ,
				  (SELECT b.`name` FROM `tb_category` AS b WHERE b.`id` = p.`cate_id`) AS cat ,
				  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.id=a.`location_id` LIMIT 1) AS location,
				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=a.`user_id` LIMIT 1) AS `username`,
				   a.`date`
				FROM
				  `tb_product_adjust` AS a ,
				  `tb_product` AS p
				WHERE 
					a.`pro_id`=p.`id`";
		$where = '';
		
		$from_date =(empty($data['start_date']))? '1': " a.date >= '".$data['start_date']." 00:00:00'";
		$to_date = (empty($data['end_date']))? '1': " a.date <= '".$data['end_date']." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
		
		if($data["ad_search"]!=""){
			$s_where=array();
			$s_search = addslashes(trim($data['ad_search']));
			$s_where[]= " p.item_name LIKE '%{$s_search}%'";
			$s_where[]= " p.barcode LIKE '%{$s_search}%'";
			$s_where[]= " p.item_code LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		
		if($data["brand"]!=""){
			$where.=' AND p.brand_id='.$data["brand"];
		}
		if($data["category"]!=""){
			$where.=' AND p.cate_id='.$data["category"];
		}
		
		$location = $db_globle->getAccessPermission('m.`location_id`');
// 		echo $sql.$where.$location;
		return $db->fetchAll($sql.$where.$location);
			
	}
	
}

?>