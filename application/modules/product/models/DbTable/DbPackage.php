<?php

class Product_Model_DbTable_DbPackage extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_product';
    public function setName($name)
    {
    	$this->_name=$name;
    }
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
// 	 public function getProductCoded(){
// 		$db =$this->getAdapter();
// 		$sql=" SELECT id FROM tb_sale_agent ";
// 		$acc_no = $db->fetchAll($sql);
// 		$count = count($acc_no);
// 		$i=0;
// 		foreach($acc_no as $rs){ $i++;
// 			$new_acc_no= $rs["id"];
// 			$acc_no= strlen($rs["id"]);
// 			$pre = "EID";
// 			$id = 32+$i;
// 			$sqls = "UPDATE tbl_user_copys SET id = "."'".$id."'"." WHERE id=".$rs["id"];
// 			$db->query($sqls);
// 		}
//   }
 
  public function getProductCode(){
  	$db =$this->getAdapter();
  	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$pre = "PID";
  	for($i = $acc_no;$i<5;$i++){
  		$pre.='0';
  	}
  	return $pre.$new_acc_no;
  }

//   function getAllProduct($data){
//   	$db = $this->getAdapter();
//   	$db_globle = new Application_Model_DbTable_DbGlobal();
// 	$user_id = $this->getUserId();
//   	$sql ="SELECT 
// 			  p.`id`,
// 			  (SELECT b.name FROM `tb_sublocation` AS b WHERE b.id=pl.`location_id` LIMIT 1) AS branch,
// 			  p.`item_code`,
// 			  p.`item_name` ,
			  
// 			  (SELECT c.name FROM `tb_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
// 			  (SELECT m.name FROM `tb_measure` AS m WHERE m.id = p.`measure_id` LIMIT 1) AS measure,
// 			  SUM(pl.`qty`) AS qty,
// 			  (SELECT pp.`price` FROM `tb_product_price` AS pp WHERE pp.`pro_id`=p.`id` AND `type_id`=1 LIMIT 1) AS master_price,
// 			  (SELECT `fullname` FROM `tb_acl_user` WHERE `user_id`=p.`user_id` LIMIT 1) AS user_name,
//   			  (SELECT v.`name_en` FROM tb_view AS v WHERE v.`type`=5  AND p.`status`=v.`key_code` LIMIT 1) AS status
// 			FROM
// 			  `tb_product` AS p ,
// 			  `tb_prolocation` AS pl
// 			WHERE p.`id`=pl.`pro_id` ";
//   	$where = '';
//   	if($data["ad_search"]!=""){
// 		$string = str_replace(' ','',$data['ad_search']);
//   		$s_where=array();
//   		$s_search = addslashes(trim($string));
//   		$s_where[]= " REPLACE(p.item_name,' ','') LIKE '%{$s_search}%'";
//   		$s_where[]=" REPLACE(p.barcode,' ','') LIKE '%{$s_search}%'";
//   		$s_where[]= " REPLACE(p.item_code,' ','') LIKE '%{$s_search}%'";
//   		$where.=' AND ('.implode(' OR ', $s_where).')';
//   	}
//   	if($data["branch"]!=""){
//   		$where.=' AND pl.`location_id`='.$data["branch"];
//   	}
//   	if($data["brand"]!=""){
//   		$where.=' AND p.brand_id='.$data["brand"];
//   	}
//   	if($data["category"]!=""){
//   		$where.=' AND p.cate_id='.$data["category"];
//   	}
//   	if($data["model"]!=""){
//   		$where.=' AND p.model_id='.$data["model"];
//   	}
//   	if($data["size"]!=""){
//   		$where.=' AND p.size_id='.$data["size"];
//   	}
//   	if($data["color"]!=""){
//   		$where.=' AND p.color_id='.$data["color"];
//   	}
//   	if($data["status"]!=-1){
//   		$where.=' AND p.status='.$data["status"];
//   	}
//   	$location = $db_globle->getAccessPermission('pl.`location_id`');
//   	$group_by = " GROUP BY p.id";
//   	return $db->fetchAll($sql.$where.$location.$group_by);
  	
//   }
  
  
//   function getProductById($id){
//   	$db = $this->getAdapter();
//   	$sql ="SELECT 
// 			 * 
// 			FROM
// 			  `tb_product` AS p 
// 			WHERE p.id = $id ";
//   	return $db->fetchRow($sql);
//   }
  
  // Insert and  Update section
    public function addPackage($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$arr = array(
    			'item_name'		=>	$data["product_name"],
    			'item_code'		=>	$data["product_code"],
    			'barcode'		=>	"",
    			'cate_id'		=>	$data["cate_id"],
    			'measure_id'	=>0,
    			'brand_id'		=>	1,
    			'color_id'		=>	0,
    			'is_package'	=>	1,
    			'is_service'	=>	$data["product_type"],
    			//'is_costprice'	=>	$data["cost_pricetype"],
    			'selling_price'	=>	$data["total_cost"],
    			"price"			=>  0,
    			'qty_perunit'	=>	1,
    			'unit_label'	=>	"",
    			'user_id'		=>	$this->getUserId(),
    			'note'			=>	$data["noted"],
    			'status'		=>	1,
    		);
    		$this->_name="tb_product";
    		$id = $this->insert($arr);
			
    		if(!empty($data['identity'])){
    			$identitys = explode(',',$data['identity']);
    			foreach($identitys as $i)
    			{
    				$arr1 = array(
    					'package_id'  => $id,
    					'product_id'  => $data["product_id".$i],
    					'qty'		  => $data["qty_".$i],
    					'price'		  => $data["price_".$i],
    					'total'	      => $data["total_".$i],
    					'note'		  => $data["note_".$i],
    				);
    				$this->_name = "tb_product_package";
    				$this->insert($arr1);
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
}