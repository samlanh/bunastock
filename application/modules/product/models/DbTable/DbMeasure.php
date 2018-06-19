<?php

class Product_Model_DbTable_DbMeasure extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_measure";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["measure_name"],
// 				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_measure";
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["measure_name"],
// 				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_measure";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	//Insert Popup=============================================================================
	public function addNew($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["measure_name"],
// 				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_measure";
		return $this->insert($arr);
	}
	public function getAllMeasure($data){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,m.`name`,m.`status`,m.`remark` FROM `tb_measure` AS m WHERE m.id";
		$where = '';
		if(!empty($data["name"]!="")){
		    $s_where=array();
		    $s_search = addslashes(trim($data['name']));
		    $s_where[]= " m.`name` LIKE '%{$s_search}%'";
		    $where.=' AND ('.implode(' OR ', $s_where).')';
		}
		//echo $sql.$where;
		$where.=" ORDER BY id DESC";
		return $db->fetchAll($sql.$where);
	}
	
	public function getMeasure($id){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,m.`name`,m.`status`,m.`remark` FROM `tb_measure` AS m  WHERE m.`id`= $id";
		return $db->fetchRow($sql);
	}
}