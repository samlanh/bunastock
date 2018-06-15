<?php

class Mong_Model_DbTable_DbIndex extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_mong";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllMong($search){
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
		
		$from_date =(empty($search['start_date']))? '1': " sale_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " sale_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " mong_code LIKE '%{$s_search}%'";
			$s_where[] = " (select dead_name from tb_program as p where p.id=m.dead_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name FROM `tb_person_in_charge` AS p WHERE p.id=m.person_in_charge LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name FROM `tb_constructor` AS c WHERE c.id=m.constructor LIMIT 1 ) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['customer_id'])){
			$where .= " AND customer_id = ".$search['customer_id'];
		}
		$order=" ORDER BY id DESC ";
// 		echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addMong($data)
	{
		try{	
			$db = $this->getAdapter();
			$db->beginTransaction();
			
			$db_global = new Application_Model_DbTable_DbGlobal();
			$invoice = $db_global->getInvoiceNumber(1);
			$receipt = $db_global->getReceiptNumber(1);
			
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
				'invoice_no'			=> $invoice,
				'sale_date'				=> date("Y-m-d",strtotime($data['sale_date'])),
				'sale_agent'			=> $data['sale_agent'],
				'comission'				=> $data['comission'],
				'other_note'			=> $data['other_note'],
				'sub_total'				=> $data['sub_total'],
//				'paid_before'			=> $data['paid_before'],
				'paid'					=> $data['paid'],
				'balance'				=> $data['balance'],
				'balance_after'			=> $data['balance'],
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
				'constructor_price'		=> $data['constructor_price'],
				'constructor_paid'		=> 0,
				'constructor_balance'	=> $data['constructor_price'],
				'total_construct_item'	=> $data['total_construct_item'],
					
				'user_id'			=> $this->getUserId(),
				'status'			=> 1,
				'create_date'		=> date("Y-m-d H:i:s"),
			);
			$mong_id = $this->insert($array);
			
			if($data['paid']>0){
				$arr_receipt = array(
						"branch_id"   		=> $data['branch_id'],
						"invoice_id"    	=> $mong_id,
						"customer_id"   	=> $data['customer_id'],
						"payment_id"    	=> 1,//payment by cash/paypal/cheque
						"receipt_no"    	=> $receipt,
						"receipt_date"  	=> date("Y-m-d",strtotime($data['sale_date'])),
						"date_input"    	=> date("Y-m-d"),
						"begining_balance"	=> $data['sub_total'],
						"total"         	=> $data['sub_total'],
						"paid"          	=> $data['paid'],
						"balance"       	=> $data['balance'],
						"remark" 			=> $data['other_note'],
						"user_id"       	=> $this->getUserId(),
						"status"        	=> 1,
						"bank_name"     	=> '',
						"cheque_number" 	=> '',
						"type"        		=> 2, // from mong sale 
			
				);
				$this->_name="tb_receipt";
				$this->insert($arr_receipt);
			}
			
			
			
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
	
	
	function getGoodtimeById($id){
		$db=$this->getAdapter();
		$sql="select * from tb_mong where id = $id";
		return $db->fetchRow($sql);
	}
	function getTimemolById($id){
		$db=$this->getAdapter();
		$sql="select * from tb_mong where id = $id";
		return $db->fetchRow($sql);
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
		$sql="select * from tb_mong where id=$id limit 1 ";
		return $db->fetchRow($sql);
	}
	
	function getItemPrice($id){
		$db=$this->getAdapter();
		$sql="select price from tb_constructor_item where id=$id limit 1 ";
		return $db->fetchOne($sql);
	}
	
	function getInvoiceById($id){
		$sql=" SELECT 
					m.*,
					(SELECT (cust_name) FROM `tb_customer` WHERE tb_customer.id=m.customer_id LIMIT 1 ) AS customer_name,
					(SELECT (phone) FROM `tb_customer` WHERE tb_customer.id=m.customer_id LIMIT 1 ) AS phone,
					(SELECT address FROM `tb_customer` WHERE tb_customer.id=m.customer_id LIMIT 1 ) AS address,
					(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = m.user_id LIMIT 1) AS user_name
				FROM 
					tb_mong AS m 
				WHERE 
					m.id = $id 
			";
		return $this->getAdapter()->fetchRow($sql);
	}
	
	function getInvoiceDetailById($id){
		$sql=" SELECT 
					msi.*,
					(SELECT item_name FROM `tb_product` WHERE id=msi.pro_id) As pro_name
				FROM 
					tb_mong_sale_item as msi 
				WHERE 
					msi.mong_id = $id
			";
		return $this->getAdapter()->fetchAll($sql);
	}
	
	
}