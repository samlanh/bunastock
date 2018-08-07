<?php 
Class report_Model_DbPaidToSupplyer extends Zend_Db_Table_Abstract{
	
	function getPurchasePayment($search){
		$db= $this->getAdapter();
		$sql="SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
					(SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_tel,
					p.order_number,
					v.remark,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = v.user_id LIMIT 1 ) AS user_name,
					v.expense_date,
					v.payment_type,
					v.total,
					v.paid,
					v.balance,
					(SELECT name_kh FROM `tb_view` WHERE key_code = v.status AND `type`=5 LIMIT 1) As status
				FROM
					`tb_purchase_order` AS p,
					`tb_vendor_payment` AS v
				WHERE
					p.id=v.purchase_id
					AND p.status=1
			";
//		$where= '';
		$from_date =(empty($search['start_date']))? '1': " v.expense_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.expense_date <= '".$search['end_date']." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " p.branch_id LIKE '%{$s_search}%'";
			$s_where[] = " p.order_number LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch']>0){
			$where .= " AND p.branch_id =".$search['branch'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY v.id ASC ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getPartnerServicePayment($search){
		$db=$this->getAdapter();
		$sql = "SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=pp.branch_id) AS branch_name,
					p.partner_name,
					p.tel,
					pp.date_payment,
					pp.payment_type,
					pp.note,
					pp.total_payment,
					pp.paid,
					pp.balance,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = pp.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM `tb_view` WHERE key_code = pp.status AND `type`=5 LIMIT 1) As status
				FROM
					`tb_partnerservice` AS p,
					`tb_partnerservice_payment` AS pp
				WHERE
					p.id=pp.partner_id
					AND pp.status=1
			";
//		$where= ' ';
		$from_date =(empty($search['start_date']))? '1': " pp.date_payment >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " pp.date_payment <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " partner_name LIKE '%{$s_search}%'";
			$s_where[] = " tel LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch']>0){
			$where .= " AND branch_id =".$search['brancd'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY pp.id ASC ";
	//	echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getConstructorPayment($search){
		$db=$this->getAdapter();
		$sql = "SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=m.branch_id) AS branch_name,
					(select name from tb_constructor as c where c.id = m.constructor) as constructor_name,
					(select phone from tb_constructor as c where c.id = m.constructor) as constructor_tel,
					m.invoice_no,
					mp.date_payment,
					mp.payment_type,
					mp.note,
					mp.total_payment,
					mp.paid,
					mp.balance,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = mp.user_id LIMIT 1 ) AS user_name,
					(SELECT name_kh FROM `tb_view` WHERE key_code = mp.status AND `type`=5 LIMIT 1) As status
				FROM
					`tb_mong` AS m,
					`tb_mong_constructor_payment` AS mp
				WHERE
					m.id=mp.mong_id
					AND m.status=1
			";
//		$where= ' ';	
		$from_date =(empty($search['start_date']))? '1': " mp.date_payment >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " mp.date_payment <= '".$search['end_date']." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
	
		if(!empty($search['ad_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['ad_search']));
			$s_where[] = " m.invoice_no LIKE '%{$s_search}%'";
			$s_where[] = " mp.payment_type LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch']>0){
			$where .= " AND m.branch_id =".$search['branch'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY mp.id ASC  ";	
		return $db->fetchAll($sql.$where.$order);
	}
	
	
	function getPurchaseExpense($search){
		$db= $this->getAdapter();
		$sql="SELECT
					SUM(paid) AS total_paid
				FROM
					`tb_vendor_payment` AS v
				WHERE
					v.status = 1
			";
		$from_date =(empty($search['start_date']))? '1': " v.expense_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " v.expense_date <= '".$search['end_date']." 23:59:59'";
		$where = " and ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where .= " AND v.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY v.id ASC ";
		return $db->fetchRow($sql.$where.$order);
	}
	
	function getPartnerServiceExpense($search){
		$db=$this->getAdapter();
		$sql="SELECT
					SUM(pp.paid) AS total_paid
				FROM
					`tb_partnerservice_payment` AS pp
				WHERE
					pp.status = 1
			";
		$from_date =(empty($search['start_date']))? '1': " pp.date_payment >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " pp.date_payment <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where .= " AND pp.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY pp.id ASC";
		return $db->fetchRow($sql.$where.$order);
	}
	
	function getConstructorExpense($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				  SUM(paid) AS total_paid
				FROM
				  `tb_mong_constructor_payment` AS m 
				WHERE 
				  m.status = 1
			";
		$from_date =(empty($search['start_date']))? '1': " m.date_payment >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " m.date_payment <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where .= " AND m.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY m.id ASC ";
		return $db->fetchRow($sql.$where.$order);
	}
	
	function getOtherExpense($search){
		$db=$this->getAdapter();
		$sql="SELECT 
				  SUM(total_amount) AS total_paid
				FROM
				  `tb_expense` AS e 
				WHERE 
				  e.status = 1
		";
		$from_date =(empty($search['start_date']))? '1': " e.for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " e.for_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where .= " AND e.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY e.id ASC ";
		return $db->fetchRow($sql.$where.$order);
	}
}

?>