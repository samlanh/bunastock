<?php

class Sales_Model_DbTable_Dbpayment extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_receipt";
	
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
						(SELECT sale_no FROM `tb_sales_order` WHERE id=r.invoice_id) AS invoice_no,
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
						type=1
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
			if($search['branch_id']>0){
				$where .= " AND r.`branch_id` = ".$search['branch_id'];
			}
			if($search['customer_id']>0){
				$where .= " AND r.customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	public function addReceiptPayment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$receipt = $db_global->getReceiptNumber(1);
			
			$array=array(
					'branch_id'			=> 1,
					'invoice_id'		=> $data['invoice_id'],
					'customer_id'		=> $data['cus_id'],
					'payment_id'		=> $data['payment_id'],
					'receipt_no'		=> $receipt,
					'receipt_date'		=> date("Y-m-d",strtotime($data['date_in'])),
						
					'begining_balance'	=> $data['all_total'],
					'total'				=> $data['all_total'],
					'paid'				=> $data['paid'],
					'balance'			=> $data['balance'],
					'remark'			=> $data['other_note'],
						
					'cheque_number'		=> $data['cheque'],
					'bank_name'			=> $data['bank_name'],
						
					'type'				=> 1,// 1=sale payment
						
					'status'			=> 1,
					'date_input'		=> date("Y-m-d"),
					'user_id'			=> $this->getUserId(),
			);
			$this->_name="tb_receipt";
			$this->insert($array); 
			
			$sql="select * from tb_sales_order where id = ".$data['invoice_id'];
			$row_sale = $db->fetchRow($sql);
			//print_r($row_sale);exit();
			
			if(!empty($row_sale)){
				$balance_after = $row_sale['balance_after'] - $data['paid'];
				$paid = $row_sale['paid'] + $data['paid'];
			}
			$arr = array(
					'balance_after'	=> $balance_after,
					'paid'			=> $paid,
			);
			$where = " id = ".$row_sale['id'];
			$this->_name="tb_sales_order";
			$this->update($arr, $where);
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();
		}
	}
	public function updatePayment($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$id = $data["id"];
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
				
			$ids=explode(',',$data['identity']);
			$branch_id = '';
			foreach ($ids as $row){
				$branch_id = $this->getBranchByInvoice($data['invoice_no'.$row]);
				$data_invoice = array(
						'discount_after'  => 	$branch_id['discount'],
						'paid_after'	  => 	$branch_id['paid_amount'],
						'balance_after'	  => 	$branch_id['balance'],
						'is_fullpaid'	  => 	0,
				);
				$this->_name='tb_invoice';
				$where = 'id = '.$data['invoice_no'.$row];
				$this->update($data_invoice, $where); // Reset Invoice like As The First
				unset($data_invoice);
			}
				
			$info_purchase_order=array(
					"branch_id"   => 	$branch_id['branch_id'],
					"customer_id"     => 	$data["customer_id"],
					"payment_type"       => 	$data["payment_method"],//payment by customer/invoice
					"payment_id"     => 	$data["payment_name"],	//payment by cash/paypal/cheque
					"receipt_no"  => 	$data['receipt'],
					"receipt_date"    => $data['date_in'],
					"total"         => 	$data['all_total'],
					"paid"      => 	$data['paid'],
					"balance" => 	$data['balance'],
					"remark"      => 	$data['remark'],
					"user_id"       => 	$GetUserId,
					'status' =>1,
					"date_input"      => 	date("Y-m-d"),
			);
			$this->_name="tb_receipt";
			$where_reciept="id = ".$id;
			$this->update($info_purchase_order, $where_reciept);
			unset($info_purchase_order);
		
			$this->_name='tb_receipt_detail';
			$where_detail = " receipt_id =".$id;
			$this->delete($where_detail);
			
			$ids=explode(',',$data['identity']);
			$count = count($ids);
			$paid = $data['paid'];
			foreach ($ids as $key => $i)
			{
				$invoice = $this->getBranchByInvoice($data['invoice_no'.$i]);
			

			}
				
		
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getBranchByInvoice($invoice_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_invoice` AS i WHERE i.`id` = $invoice_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getSaleById($sale_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_sales_order` AS i WHERE i.`id` = $sale_id LIMIT 1";
		return $db->fetchRow($sql);
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
	
	function getRecieptDetail($reciept_id){
		$db= $this->getAdapter();
		$sql="SELECT 
					*
				FROM 
					tb_receipt AS d 
				WHERE 
					d.id = $reciept_id
				limit 1	
			";
		return $db->fetchRow($sql);
	}
	function getRecieptDetailforPrint($reciept_id){
		$db= $this->getAdapter();
		$sql="SELECT d.`id`,d.`receipt_id`,d.`invoice_id`,
		(SELECT s.sale_no FROM `tb_sales_order` AS s WHERE s.id=d.invoice_id) AS invoice_name,
		(SELECT i.invoice_no FROM `tb_invoice` AS i  WHERE i.id = d.`invoice_id` ) AS invoice_no,
		d.`total`,d.`paid`,d.`balance`,d.`discount`,d.`date_input`,
		(SELECT s.all_total FROM `tb_sales_order` AS s WHERE s.id=d.invoice_id) AS all_total,
		(SELECT SUM(rd.paid) FROM tb_receipt_detail AS rd WHERE rd.`invoice_id` = d.invoice_id AND rd.id<d.id) AS all_paid
		FROM `tb_receipt_detail` AS d,
			tb_receipt as r
		WHERE 
			r.id=d.`receipt_id`
			AND d.`receipt_id` =".$reciept_id;
		return $db->fetchAll($sql);
	}
	function getSaleorderItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_salesorder_item` WHERE saleorder_id=$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=2 ";
		return $db->fetchAll($sql);
	} 
	function delettePayment($id){
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
					$this->_name="tb_sales_order";
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
	
	function getSaleCustomerName(){
		$db = $this->getAdapter();
		$sql = "SELECT id,(select cust_name from tb_customer where tb_customer.id = customer_id) as name FROM tb_sales_order WHERE balance_after>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	function getSaleInvoice(){
		$db = $this->getAdapter();
		$sql = "SELECT id,sale_no as name FROM tb_sales_order WHERE balance_after>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	
	function getCustomerInfo($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_sales_order as s,tb_customer as c where c.id = s.customer_id and s.id=$id";
		return $db->fetchRow($sql);
	}
	
	function getReceipt($mong_id,$cus_id,$type){
		$db = $this->getAdapter();
		$sql = "SELECT *,DATE_FORMAT(receipt_date, '%d-%M-%Y') AS receipt_date FROM tb_receipt WHERE invoice_id=$mong_id and customer_id=$cus_id and type=$type";
		return $db->fetchAll($sql);
	}
	
	
}