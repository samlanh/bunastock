<?php
class Sales_IndexController extends Zend_Controller_Action
{	
    public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
   	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
			$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
		}
		else{
			$search =array(
					'ad_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'branch'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new Sales_Model_DbTable_DbSaleOrder();
		$rows = $db->getAllSaleOrder($search);
		$columns=array("BRANCH_NAME","ឈ្មោះអតិថិជន","លេខទូរស័ព្ទ","ឈ្មោះសព","លេខវិក័យបត្រ","ថ្ងៃលក់",
				"តម្លៃសរុប","ប្រាក់បានបង់","ប្រាក់នៅខ្វះ","បោះពុម្ភ","លុបវិក្កយបត្រ","អ្នកប្រើប្រាស់");
		$link=array(
				'module'=>'sales','controller'=>'possale','action'=>'edit',
		);
		$invoice=array(
				'module'=>'sales','controller'=>'possale','action'=>'invoice',);
		$delete=array(
				'module'=>'sales','controller'=>'possale','action'=>'delete',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('លុបវិក្កយបត្រ'=>$delete,'វិក្កយបត្រ'=>$invoice,'contact_name'=>$link,'branch_name'=>$link,'customer_name'=>$link,
				'sale_no'=>$link));
		
	    $formFilter = new Product_Form_FrmProduct();
	    $this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	function addAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_DbSaleOrder();
				if(!empty($data['identity'])){
					$dbq->addSaleOrder($data);
				}
				Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/sales/quoatation");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('បញ្ចូលមិនត្រឹមត្រូវ');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmSale(null);
		$form_sale = $frm_purchase->SaleOrder(null);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->term_opt = $db->getAllTermCondition(1);
		$this->view->sale_term_defual = $db->getAllTermCondition(null,2,1);
		
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$this->view->userinfo = $this->GetuserInfoAction();
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_DbSaleOrder();
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$dbq->updateSaleOrder($data);
				}
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ","/sales/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('កែប្រែមិនត្រឹមត្រូវ');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getSaleorderItemById($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/index");
		}if($row['is_approved']==1){
			$user = $this->GetuserInfoAction();
			if($user['level']!=1){
				Application_Form_FrmMessage::Sucessfull("SALE_ORDER_WARNING","/sales/index");
			}
		}
		$this->view->rs = $dbq->getSaleorderItemDetailid($id);
		$this->view->rsterm = $dbq->getTermconditionByid($id);
		
		///link left not yet get from DbpurchaseOrder
		$frm_purchase = new Sales_Form_FrmSale(null);
		$form_sale = $frm_purchase->SaleOrder($row);
		Application_Model_Decorator::removeAllDecorator($form_sale);
		$this->view->form_sale = $form_sale;
		$this->view->discount_type = $row['discount_type'];
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->term_opt = $db->getAllTermCondition(1);
	}	
	function viewappAction(){
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_DbSaleOrder();
				$dbq->RejectSale($data);
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ","/sales/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('កែប្រែមិនត្រឹមត្រូវ');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/salesapprove");
		}
		$query = new Sales_Model_DbTable_Dbsalesapprov();
		$rs = $query->getProductSaleById($id);
		if(empty($rs)){
			$this->_redirect("/sales/salesapprove");
		}
		$this->view->product = $rs;
	}
	public function getproductpriceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db ->getProductPriceBytype($post['customer_id'], $post['product_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	function getsonumberAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$qo = $db->getSalesNumber($post['branch_id']);
			echo Zend_Json::encode($qo);
			exit();
		}
	}
		
}