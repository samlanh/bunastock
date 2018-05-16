<?php

class Mong_Model_DbTable_DbIndex extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_mong";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllConstructor($search){
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
		
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " name LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " email LIKE '%{$s_search}%'";
			$s_where[] = " address LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where .= " AND status = ".$search['status'];
		}
		$order=" ORDER BY id DESC ";
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addMong($data)
	{
		try{	
			$db = $this->getAdapter();
			$db->beginTransaction();
			
			$array_photo_name = "";
			 
			$part= PUBLIC_PATH.'/images/';
			$photo_name = $_FILES['photo']['name'];
			 
			if (!empty($photo_name)){
				foreach($photo_name as $key=>$tmp_name){
					$tem = explode(".", $photo_name[$key]);
					$image_name = time().$key.".".end($tem);
					$tmp = $_FILES['photo']['tmp_name'][$key];
					if(move_uploaded_file($tmp, $part.$image_name)){
						move_uploaded_file($tmp, $part.$image_name);
						if($key==0){
							$comma = "";
						}else{
							$comma = ",";
						}
						$array_photo_name = $array_photo_name.$comma.$image_name;
					}
				}
			}
			
			$array=array(
	 			'customer_id'			=> $data['customer_id'],
				'note'					=> $data['note'],
				'date_clearpayment'		=> date("Y-m-d",strtotime($data['date_clearpayment'])),
				'receiver_name'			=> $data['receiver_name'],
				'receipt_no'			=> $data['receipt_no'],
				'sale_date'				=> date("Y-m-d",strtotime($data['sale_date'])),
				'sale_agent'			=> $data['sale_agent'],
				'comission'				=> $data['comission'],
				'other_note'			=> $data['other_note'],
				'sub_total'				=> $data['sub_total'],
				'paid_before'			=> $data['paid_before'],
				'paid'					=> $data['paid'],
				'balance'				=> $data['balance'],
				'return_amount'			=> $data['return_amount'],
					
					
				'construct_type'		=> $data['construct_type'],
				'mong_type'				=> $data['mong_type'],
				'builder'				=> $data['builder'],
				'mong_code'				=> $data['mong_code'],
				'mong_number'			=> $data['mong_number'],
				'mong_address'			=> $data['mong_address'],
				'person_in_charge'		=> $data['person_in_charge'],
				'mong_note'				=> $data['mong_note'],
				'land_longitude'		=> $data['land_longitude'],
				'land_width'			=> $data['land_width'],
				'mong_longitude'		=> $data['mong_longitude'],
				'mong_width'			=> $data['mong_width'],
				'date_finish'			=> date("Y-m-d",strtotime($data['date_finish'])),
				'date_sen'				=> date("Y-m-d",strtotime($data['date_sen'])),
				'time_sen'				=> $data['time_sen'],
				'date_chlong_mong'		=> date("Y-m-d",strtotime($data['date_chlong_mong'])),
				'time_chlong_mong'		=> $data['time_chlong_mong'],
				'photo'					=> $array_photo_name,
					
				'dead_id'				=> $data['dead_id'],
					
				'constructor'			=> $data['constructor'],
				'total_construct_item'	=> $data['total_construct_item'],
					
				'user_id'			=> $this->getUserId(),
				'status'			=> 1,
				'create_date'		=> date("Y-m-d H:i:s"),
			);
			$mong_id = $this->insert($array);
			
			if(!empty($data['identity_sale'])){
				$iden = explode(",", $data['identity_sale']);
				foreach ($iden as $i){
					
					$_db = new Sales_Model_DbTable_Dbpos();
					$rs = $_db->getProductByProductId($data['pro_'.$i], $data["branch_id"]);//check if service not need update stock
					
					if(!empty($rs)){
						$this->_name='tb_prolocation';
						$arr = array(
								'qty'=>$rs['qty']-$data['qty_sold_'.$i]
						);
						$where=" id =".$rs['id'];
						$this->update($arr, $where);
					}
					
					$arr=array(
						'mong_id'		=> $mong_id,
						'pro_id'		=> $data['pro_'.$i],
						'qty_unit'		=> $data['qtyunit_'.$i],
						'qty_detail'	=> $data['qtydetail_'.$i],
						'qty_order'		=> $data['qty_sold_'.$i],
						'cost_price'	=> $data['cost_price_'.$i],
						'price'			=> $data['selling_price_'.$i],
						'sub_total'		=> $data['sale_total_'.$i],
					);
					$this->_name="tb_mong_sale_item";
					$this->insert($arr);
				}
			}
			
			if(!empty($data['identity'])){
				$iden = explode(",", $data['identity']);
				foreach ($iden as $i){
					$arra=array(
						'mong_id'		=> $mong_id,
						'constructor'	=> $data['constructor'],
						'item_id'		=> $data['item_'.$i],
						'item_price'	=> $data['item_price_'.$i],
						'item_qty'		=> $data['item_qty_'.$i],
						'item_total'	=> $data['item_total_'.$i],
					);
					$this->_name="tb_mong_construct_item";
					$this->insert($arra);
				}
			}
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
	
	public function editConstructor($data,$id)
	{
		$db=$this->getAdapter();
		$array=array(
				'name'			=> $data['name'],
				'sex'			=> $data['sex'],
				'phone'			=> $data['phone'],
				'email'			=> $data['email'],
				'address'		=> $data['address'],
				'constructor_type'=> $data['constructor_type'],
				'note'			=> $data['note'],
				'user_id'		=> $this->getUserId(),
				'status'		=> $data['status'],
		);
		$where = " id = $id";
		$this->update($array, $where);
	}
	
	
	function getConstructorById($id){
		$db=$this->getAdapter();
		$sql="select * from tb_constructor where id = $id";
		return $db->fetchRow($sql);
	}
	
	function getMongType(){
		$db=$this->getAdapter();
		$sql="select id,title from tb_mong_type where status=1 and title!='' ";
		return $db->fetchAll($sql);
	}
	
	function getPersonInCharge(){
		$db=$this->getAdapter();
		$sql="select id,name from tb_person_in_charge where status=1 and name!='' ";
		return $db->fetchAll($sql);
	}
	
	function getDeadPerson(){
		$db=$this->getAdapter();
		$sql="select id,dead_name,dead_name_chinese from tb_program where status=1 and dead_name!='' ";
		return $db->fetchAll($sql);
	}
	
	function getConstructor(){
		$db=$this->getAdapter();
		$sql="select id,name from tb_constructor where status=1 and name!='' ";
		return $db->fetchAll($sql);
	}
	function getConstructorItem(){
		$db=$this->getAdapter();
		$sql="select id,title,price from tb_constructor_item where status=1 and title!='' ";
		return $db->fetchAll($sql);
	}
	function getDeadDetail($id){
		$db=$this->getAdapter();
		$sql="select 
					*,
					DATE_FORMAT(dead_dob, '%d-%m-%Y') AS dead_dob,
					DATE_FORMAT(date_time_dead, '%d-%m-%Y %H:%i:%s') AS date_time_dead,
					DATE_FORMAT(partner_dob, '%d-%m-%Y') AS partner_dob
				from 
					tb_program 
				where 
					status=1 
					and dead_name!='' 
					and id=$id 
				limit 1 
			";
		return $db->fetchRow($sql);
	}
	
	function getConstructorDetail($id){
		$db=$this->getAdapter();
		$sql="select * from tb_constructor where id=$id limit 1 ";
		return $db->fetchRow($sql);
	}
	
	function getItemPrice($id){
		$db=$this->getAdapter();
		$sql="select price from tb_constructor_item where id=$id limit 1 ";
		return $db->fetchOne($sql);
	}
}