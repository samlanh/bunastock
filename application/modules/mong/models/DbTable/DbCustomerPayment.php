<?php

class Mong_Model_DbTable_DbCustomerPayment extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_receipt";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllReciept($search){
		$db= $this->getAdapter();
		$sql=" SELECT 
					r.id,
					(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND name!='' LIMIT 1) AS branch_name,
					r.`receipt_no`,
					(select invoice_no from tb_mong where tb_mong.id = r.invoice_id) as invoice,
					(SELECT cust_name FROM `tb_customer` AS c WHERE c.id=r.customer_id LIMIT 1 ) AS customer_name,
					r.`date_input`,
					r.`total`,
					r.`paid`,
					r.`balance`,
					'បោះពុម្ភ',
					'លុប',
					r.remark,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name
				FROM 
					`tb_receipt` AS r 
				where 
					r.type=2
			";
			
		$from_date =(empty($search['start_date']))? '1': " r.`receipt_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " r.`receipt_date` <= '".$search['end_date']." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
		
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " r.`receipt_no` LIKE '%{$s_search}%'";
			$s_where[] = " r.`total` LIKE '%{$s_search}%'";
			$s_where[] = " r.`paid` LIKE '%{$s_search}%'";
			$s_where[] = " r.`balance` LIKE '%{$s_search}%'";
			$s_where[] = " r.`remark` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
// 		if($search['branch_id']>0){
// 			$where .= " AND r.`branch_id` = ".$search['branch_id'];
// 		}
// 		if($search['customer_id']>0){
// 			$where .= " AND r.customer_id =".$search['customer_id'];
// 		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		
// 		echo $sql.$where;
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function addCustomerPayment($post)
	{
		try{	
			$db=$this->getAdapter();
			$_db = new Application_Model_DbTable_DbGlobal();
			$receipt = $_db->getReceiptNumber(1);
			$data=array(
	 				'branch_id'			=> 1,
					'invoice_id'		=> $post['invoice_id'],
					'customer_id'		=> $post['cus_id'],
					'payment_id'		=> $post['payment_id'],
					'receipt_no'		=> $receipt,
					'receipt_date'		=> date("Y-m-d",strtotime($post['date_in'])),
					
					'begining_balance'	=> $post['all_total'],
					'total'				=> $post['all_total'],
					'paid'				=> $post['paid'],
					'balance'			=> $post['balance'],
					'remark'			=> $post['other_note'],
					
					'cheque_number'		=> $post['cheque'],
					'bank_name'			=> $post['bank_name'],
					
					'type'				=> 2,// 2=mong receipt type
					
					'status'			=> 1,
					'date_input'		=> date("Y-m-d"),
					'user_id'			=> $this->getUserId(),
			);
			
			$this->insert($data);
			
			
			$sql="select * from tb_mong where id = ".$post['invoice_id'];
			$row_mong = $db->fetchRow($sql);
			if(!empty($row_mong)){
				$balance_after = $row_mong['balance_after'] - $post['paid'];
				$paid = $row_mong['paid'] + $post['paid'];
			}
			$arr = array(
					'balance_after'	=> $balance_after,
					'paid'			=> $paid,
					);
			$where = " id = ".$row_mong['id'];
			$this->_name="tb_mong";
			$this->update($arr, $where);
		}catch (Exception $e){
			echo $e->getMessage();exit();
		}
		
	}
	public function updateCustomer($post){
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db = $this->getAdapter();
		$data=array(
				//'cu_code'		=> $post['cu_code'],
				'cust_name'		=> $post['txt_name'],
				'phone'			=> $post['txt_phone'],
				'contact_name'	=> $post['txt_contact_name'],//test
				'contact_phone'	=> $post['contact_phone'],//test
				'address'		=> $post['txt_address'],
				'province_id'=> $post['province'],
				'fax'			=> $post['txt_fax'],
				'email'			=> $post['txt_mail'],
				'website'		=> $post['txt_website'],//test
				'add_remark'	=>	$post['remark'],
				'user_id'		=> $GetUserId,
				'date'			=> date("Y-m-d"),
				'branch_id'		=> $post['branch_id'],
				'customer_level'=> $post['customer_level'],
				'cu_type'		=>	$post["customer_type"],
				'credit_limit'	=>	$post["credit_limit"],
				'credit_team'	=>	$post["credit_tearm"],
				'status'	=>	$post["status"],
		);
		$where=$this->getAdapter()->quoteInto('id=?',$post["id"]);
		$this->_name="tb_customer";
		$this->update($data,$where);
	}
	
	function getRecieptById($id){
		$db = $this->getAdapter();
		$sql="SELECT 
					r.*,
					(select invoice_no from tb_mong where tb_mong.id = r.invoice_id) as invoice,
					c.cust_name AS customer_name,
					c.phone contact_phone,
					c.address AS address,
					(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name
				FROM 
					tb_receipt as r,
					tb_customer as c
				WHERE 
					c.id = r.customer_id
					and r.id = $id 
				LIMIT 1 
			";
		return $db->fetchRow($sql);
	}
	
	function deletePayment($id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
				
			$rs = $this->getRecieptDetail($id);
			if(!empty($rs)){
				$rssale = $this->getSaleById($rs['invoice_id']);
				if(!empty($rssale)){
					$data= array(
						'balance_after'=>$rssale['balance_after']+$rs['paid'],
						'paid'	=>$rssale['paid']-$rs['paid'],
					);
					$this->_name="tb_mong";
					$where = " id = ".$rs['invoice_id'];
					$this->update($data, $where);
				}
			}
				
			$this->_name="tb_receipt";
			$where = "id =  ".$id;
			$this->delete($where);
				
			$db->commit();
		}Catch(Exception $e){
			$db->rollBack();
		}
	}
	
	function getRecieptDetail($reciept_id){
		$db= $this->getAdapter();
		$sql="SELECT
					*
				FROM
					tb_receipt AS r
				WHERE
					r.id = $reciept_id
					limit 1
			";
		return $db->fetchRow($sql);
	}
	
	function getSaleById($mong_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM tb_mong AS m WHERE m.id = $mong_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getMongCustomerName(){
		$db = $this->getAdapter();
		$sql = "SELECT id,(select cust_name from tb_customer where tb_customer.id = customer_id) as name FROM tb_mong WHERE balance_after>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	function getMongInvoice(){
		$db = $this->getAdapter();
		$sql = "SELECT id,invoice_no as name FROM tb_mong WHERE balance_after>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	function getCustomerInfo($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_mong as m,tb_customer as c where c.id = m.customer_id and m.id=$id";
		return $db->fetchRow($sql);
	}
	
    function getReceipt($mong_id,$cus_id,$type){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				*,
    				DATE_FORMAT(receipt_date, '%d-%M-%Y') AS receipt_date 
    			FROM 
    				tb_receipt 
    			WHERE 
    				invoice_id=$mong_id 
    				and customer_id=$cus_id 
    				and type=$type
    			order by 
    				id ASC		
    		";
    	return $db->fetchAll($sql);
    }
    

}