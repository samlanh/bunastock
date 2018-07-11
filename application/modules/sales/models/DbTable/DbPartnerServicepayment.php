<?php

class Sales_Model_DbTable_DbPartnerServicepayment extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_receipt";
	
	function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	
	function getAllPartnerPayment($search){
			$db= $this->getAdapter();
			$sql=" SELECT 
						pp.id,
						(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = pp.`branch_id` AND status=1 AND name!='' LIMIT 1) AS branch_name,
						(SELECT sale_no FROM `tb_sales_order` WHERE id=pp.sale_order_id) AS invoice_no,
						pp.`date_payment`,
						pp.`payment_type`,
						pp.`total_payment`,
						pp.`paid`,
						pp.`balance`,
						pp.note,
						(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = pp.`user_id`) AS user_name,
						(select name_kh from tb_view where type=5 and key_code = pp.status) as status_name 
					FROM 
						`tb_partnerservice_payment` AS pp 
					where 
						1
			";
			
			$from_date =(empty($search['start_date']))? '1': " pp.`date_payment` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " pp.`date_payment` <= '".$search['end_date']." 23:59:59'";
			$where = " and ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " pp.`total_payment` LIKE '%{$s_search}%'";
				$s_where[] = " pp.`paid` LIKE '%{$s_search}%'";
				$s_where[] = " pp.`balance` LIKE '%{$s_search}%'";
				$s_where[] = " pp.`note` LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch']>0){
				$where .= " AND pp.`branch_id` = ".$search['branch'];
			}
			if($search['sale_order_id']>0){
				$where .= " AND pp.`sale_order_id` = ".$search['sale_order_id'];
			}
			
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			
			$order=" ORDER BY date_payment DESC,id DESC ";
			
			return $db->fetchAll($sql.$where.$order);
	}
	public function addPartnerServicePayment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$array=array(
					'branch_id'			=> 1,
					'sale_order_id'		=> $data['sale_order_id'],
					'payment_type'		=> $data['payment_type'],
					'cheque_number'		=> $data['cheque_number'],
					'bank_name'			=> $data['bank_name'],
					'note'				=> $data['note'],
					
					'date_payment'		=> date("Y-m-d",strtotime($data['date_payment'])),
						
					'total_payment'		=> $data['total_payment'],
					'paid'				=> $data['paid'],
					'balance'			=> $data['balance'],
					
					'status'			=> 1,
					'create_date'		=> date("Y-m-d H:i:s"),
					'user_id'			=> $this->getUserId(),
			);
			$this->_name="tb_partnerservice_payment";
			$this->insert($array); 
			
			
			$sql="select * from tb_sales_order where id = ".$data['sale_order_id'];
			$row_sale = $db->fetchRow($sql);
			
			if(!empty($row_sale)){
				$balance = $row_sale['partner_service_balance'] - $data['paid'];
				$paid = $row_sale['partner_service_paid'] + $data['paid'];
			}
			
			$arr = array(
					'partner_service_balance'	=> $balance,
					'partner_service_paid'		=> $paid,
			);
			$where = " id = ".$row_sale['id'];
			$this->_name="tb_sales_order";
			$this->update($arr, $where);
			
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			echo $e->getMessage();exit();
		}
	}
	public function updatePartnerServicePayment($data,$id){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			
			if($data['status']==0){
				$rs = $this->getPartnerSerivcePaymentById($id);
				if(!empty($rs)){
					$rssale = $this->getSaleById($rs['sale_order_id']);
					if(!empty($rssale)){
						$arr= array(
								'partner_service_balance'	=>$rssale['partner_service_balance']+$rs['paid'],
								'partner_service_paid'		=>$rssale['partner_service_paid']-$rs['paid'],
						);
						$this->_name="tb_sales_order";
						$where = " id = ".$rs['sale_order_id'];
						$this->update($arr, $where);
					}
				}
				$this->_name="tb_partnerservice_payment";
				$array= array(
						'status' =>$data['status'],
				);
				$where1 = "id = $id ";
				$this->update($array,$where1);
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
	
	function getSaleInvoice(){
		$db = $this->getAdapter();
		$sql = "SELECT id,sale_no as name FROM tb_sales_order WHERE partner_service_balance>0 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	function getPartnerSerivcePayment($sale_id,$action){
		$db = $this->getAdapter();
		$status="";
		if($action=="add"){
			$status=" and pp.status=1";
		}
		$sql = "SELECT 
					pp.*,
					DATE_FORMAT(pp.date_payment, '%d-%M-%Y') AS receipt_date,
					s.partner_service_balance ,
					(select name_kh from tb_view where type=5 and key_code = pp.status) as status_name
				FROM 
					tb_partnerservice_payment as pp,
					tb_sales_order as s 
				WHERE 
					s.id = pp.sale_order_id 
					and sale_order_id=$sale_id
					$status
			";
		return $db->fetchAll($sql);
	}
	
	function getPartnerPaymentBalance(){
		$db = $this->getAdapter();
		$sql = "SELECT id,sale_no as name FROM tb_sales_order where 1 ";
		return $db->fetchAll($sql);
	}
	
	function getPartnerSerivcePaymentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM tb_partnerservice_payment where id = $id limit 1";
		return $db->fetchRow($sql);
	}
	
}