<?php 
Class report_Model_DbPurchase extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	public function getAllPurchaseReport($search){//1
		$db= $this->getAdapter();
		$sql=" SELECT id,
					(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) AS vendor_name,
					order_number,
					invoice_no,
					date_order,
					date_in,
					currency_id,
					net_total,
					paid,
					balance,
					balance_after,
					(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=payment_method LIMIT 1 ) as payment_method,
					(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1 ) As purchase_status,
					(SELECT name_en FROM `tb_view` WHERE key_code =tb_purchase_order.status AND type=2 LIMIT 1),
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 ) AS user_name
				FROM 
					`tb_purchase_order`  
		
			";
	
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE status=1 and ".$from_date." AND ".$to_date;
	
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " net_total LIKE '%{$s_search}%'";
			$s_where[] = " paid LIKE '%{$s_search}%'";
			$s_where[] = " balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
// 		if($search['suppliyer_id']>0){
// 			$where .= " AND vendor_id = ".$search['suppliyer_id'];
// 		}
// 		if($search['branch_id']>0){
// 			$where .= " AND branch_id =".$search['branch_id'];
// 		}
	
// 		if($search['status_paid']>0){
// 			if($search['status_paid']==1){
// 				$where .= " AND balance <=0 ";
// 			}
// 			elseif($search['status_paid']==2){
// 				$where .= " AND balance >0 ";
// 			}
// 		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductPruchaseById($id){//2
		$db = $this->getAdapter();
		$sql=" SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
					p.order_number,p.date_order,p.date_in,p.remark,
					p.commission,p.commission_ensur,p.bank_name,p.date_issuecheque,
					(SELECT item_name FROM `tb_product` WHERE id= po.pro_id LIMIT 1) AS item_name,
					(SELECT item_code FROM `tb_product` WHERE id=po.pro_id LIMIT 1 ) AS item_code,
						
					(SELECT tb_measure.name FROM `tb_measure` WHERE tb_measure.id=(SELECT measure_id FROM `tb_product` WHERE id= po.pro_id LIMIT 1)) as measue_name,
					(SELECT qty_perunit FROM `tb_product` WHERE id= po.pro_id LIMIT 1) AS qty_perunit,
					(SELECT unit_label FROM `tb_product` WHERE id=po.pro_id LIMIT 1 ) AS unit_label,
					(SELECT payment_name FROM `tb_paymentmethod` WHERE payment_typeId=p.payment_method) as payment_method,
					p.payment_number,
				
					(SELECT symbal FROM `tb_currency` WHERE id=p.currency_id limit 1) As curr_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
					(SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS v_phone,
					(SELECT contact_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS contact_name,
					(SELECT add_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS add_name,
					(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1) As purchase_status,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = p.user_mod LIMIT 1 ) AS user_name,
					po.qty_order,po.qty_unit,po.qty_detail,po.price,po.sub_total,p.net_total,
						
					p.paid,p.discount_real,p.tax,
					p.balance
				FROM 
					`tb_purchase_order` AS p,
					`tb_purchase_order_item` AS po 
				WHERE 
					p.id=po.purchase_id
					AND po.status=1 
					AND p.id = $id 
		";
		return $db->fetchAll($sql);
	}
	function getPruchaseProductDetail($search){//3
		$db = $this->getAdapter();
		$sql=" SELECT
					(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
					it.item_name,
					it.item_code,
					(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
					(SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
					(SELECT symbal FROM `tb_currency` WHERE id=p.currency_id limit 1) As curr_name,
					(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
					(SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS v_phone,
					(SELECT add_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS add_name,
					(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1) As purchase_status,
					(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = p.user_mod LIMIT 1 ) AS user_name,
					po.qty_order,po.price,po.sub_total,p.currency_id,p.net_total,
					p.id,p.order_number,p.date_order,p.date_in,p.remark,
					p.paid,p.discount_real,p.tax,
					p.balance
				FROM 
					tb_purchase_order AS p,
					tb_purchase_order_item AS po,
					tb_product AS it
				WHERE 
					p.id=po.purchase_id 
					AND it.id=po.pro_id
					AND po.status=1  AND p.status=1 
			
			";
		
		$from_date =(empty($search['start_date']))? '1': " p.date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " p.date_order <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		
		if(!empty($search['txt_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txt_search']));
			$s_where[] = " it.item_name LIKE '%{$s_search}%'";
			$s_where[] = " it.item_code LIKE '%{$s_search}%'";
			$s_where[] = " it.barcode LIKE '%{$s_search}%'";
			$s_where[] = " p.order_number LIKE '%{$s_search}%'";
			$s_where[] = " p.net_total LIKE '%{$s_search}%'";
			$s_where[] = " p.paid LIKE '%{$s_search}%'";
			$s_where[] = " p.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['item']>0){
			$where .= " AND it.id =".$search['item'];
		}
		if($search['category_id']>0){
			$where .= " AND it.cate_id =".$search['category_id'];
		}
		if($search['brand_id']>0){
			$where .= " AND it.brand_id =".$search['brand_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND p.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY p.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	

}

?>