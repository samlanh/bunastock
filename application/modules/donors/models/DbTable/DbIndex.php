<?php

class Donors_Model_DbTable_DbIndex extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_donors";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllDonor($search){
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
			$s_where[] = " donor_female LIKE '%{$s_search}%'";
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
	public function addDonor($data)
	{
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$receipt = $_db->getReceiptNumber();
		
		$arr=array(
				'branch_id'			=> 1,
 				'donor_name'		=> $data['donor_name'],
				'donor_female'		=> $data['donor_female'],
				'tel'				=> $data['tel'],
				'address'			=> $data['address'],
				'required_using'	=> $data['required_using'],
				'invalid_date'		=> date("Y-m-d",strtotime($data['invalid_date'])),
				'note'				=> $data['note'],
				
				'receipt_no'		=> $receipt,
				'paid_date'			=> date("Y-m-d",strtotime($data['paid_date'])),
				'qty'				=> $data['qty'],
				'unit_price'		=> $data['unit_price'],
				'total_amount'		=> $data['total_amount'],
				'payment_note'		=> $data['payment_note'],
				
				'user_id'			=> $this->getUserId(),
				'create_date'		=> date("Y-m-d H:i:s"),
				'status'			=> 1,
		);
		
		$this->insert($arr);
	}
	public function editDonor($data,$id){
		$arr=array(
				'branch_id'			=> 1,
 				'donor_name'		=> $data['donor_name'],
				'donor_female'		=> $data['donor_female'],
				'tel'				=> $data['tel'],
				'address'			=> $data['address'],
				'required_using'	=> $data['required_using'],
				'invalid_date'		=> date("Y-m-d",strtotime($data['invalid_date'])),
				'note'				=> $data['note'],
				
				'receipt_no'		=> $data['receipt_no'],
				'paid_date'			=> date("Y-m-d",strtotime($data['paid_date'])),
				'qty'				=> $data['qty'],
				'unit_price'		=> $data['unit_price'],
				'total_amount'		=> $data['total_amount'],
				'payment_note'		=> $data['payment_note'],
				
				'modify_by'			=> $this->getUserId(),
				'modify_date'		=> date("Y-m-d H:i:s"),
				'status'			=> $data['status'],
		);
		
		$where=" id = $id ";
		$this->_name="tb_donors";
		$this->update($arr,$where);
	}
	function getdonorpeopleById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
					*,
					(SELECT fullname FROM `tb_acl_user` as u WHERE u.user_id=d.user_id LIMIT 1) AS user_name,
					(SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=d.status LIMIT 1) status
		 		FROM 
					tb_donors as d
				WHERE 
					id= $id limit 1
		";
		return $db->fetchRow($sql);
	}

	function getDonorById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM tb_donors where id = $id limit 1";
    	return $db->fetchRow($sql);
    }
    

}