<?php 
Class report_Model_DbMongPayment extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	
	function getMongPaymentById($id){
		$db=$this->getAdapter();
		$sql = "SELECT 
					r.*,
					c.cu_code,
					c.cust_name,
					c.phone,
					m.invoice_no,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = r.branch_id LIMIT 1) AS branch_name,
					(SELECT fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.user_id)  AS user_name,
					(select name_en from tb_view where type = 5 and key_code = r.status) as status
				FROM 
					tb_receipt as r,
					tb_mong as m,
					tb_customer as c
				WHERE 
					r.invoice_id = m.id
					and m.customer_id = c.id
					and r.type = 2 
					and r.invoice_id = $id
			";
		$where= ' ';
		
		$order=" ORDER BY r.id ASC  ";
		 
		return $db->fetchAll($sql.$where.$order);
	}

}

?>