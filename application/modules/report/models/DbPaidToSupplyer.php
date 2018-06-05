<?php 
Class report_Model_DbPaidToSupplyer extends Zend_Db_Table_Abstract{
	
	function getPurchasePayment($search){
		$db= $this->getAdapter();
		$sql="SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
					p.order_number,
					
					v.remark,
				
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = v.user_id LIMIT 1 ) AS user_name,
					v.expense_date,
					v.payment_type,
					v.total,
					v.paid,
					v.balance
				FROM
					`tb_purchase_order` AS p,
					`tb_vendor_payment` AS v
				WHERE
					p.id=v.purchase_id
					AND p.status=1
			";
		$from_date =(empty($search['start_date']))? '1': " v.expense_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.expense_date <= '".$search['end_date']." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " p.order_number LIKE '%{$s_search}%'";
			$s_where[] = " s.total LIKE '%{$s_search}%'";
			$s_where[] = " s.paid LIKE '%{$s_search}%'";
			$s_where[] = " s.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
// 		if($search['branch_id']>0){
// 			$where .= " AND branch_id =".$search['branch_id'];
// 		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		
// 		if($search['order']==1){
// 			$order=" ORDER BY r.receipt_date ASC , r.id ASC ";
// 		}else{
// 			$order=" ORDER BY r.invoice_id ASC , r.id ASC ";
// 		}
		$order=" ORDER BY v.id ASC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getPartnerServicePayment($search){
		$db=$this->getAdapter();
		$sql = "SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
					s.sale_no,
					pp.date_payment,
					pp.payment_type,
					pp.note,
					pp.total_payment,
					pp.paid,
					pp.balance,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = pp.user_id LIMIT 1 ) AS user_name
				FROM
					`tb_sales_order` AS s,
					`tb_partnerservice_payment` AS pp
				WHERE
					s.id=pp.sale_order_id
					AND s.status=1
			";
		$where= ' ';
	
		$from_date =(empty($search['start_date']))? '1': " pp.date_payment >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " pp.date_payment <= '".$search['end_date']." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
	
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " s.sale_no LIKE '%{$s_search}%'";
			$s_where[] = " pp.payment_type LIKE '%{$s_search}%'";
			$s_where[] = " pp.paid LIKE '%{$s_search}%'";
			$s_where[] = " pp.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch']>0){
			$where .= " AND branch_id =".$search['branch'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
	
		// 		if($search['order']==1){
		// 			$order=" ORDER BY r.receipt_date ASC , r.id ASC ";
		// 		}else{
		// 			$order=" ORDER BY r.invoice_id ASC , r.id ASC ";
		// 		}
	
		$order=" ORDER BY pp.id ASC  ";
			
		return $db->fetchAll($sql.$where.$order);
	}
	
}

?>