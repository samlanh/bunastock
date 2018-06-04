<?php
class Purchase_PaymentController extends Zend_Controller_Action
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
					'purchase_id'=>-1,
					);
		}
		$db = new Purchase_Model_DbTable_Dbpayment();
		$rows = $db->getAllReciept($search);
		$columns=array("សាខា","អតិថិជន","លេខបញ្ជាទិញ","ថ្ងៃចំណាយ","ចំណាយជា","តម្លៃសរុប","ប្រាក់បានបង់","នៅខ្វះ","អ្នកប្រើប្រាស់");
		$link=array(
				'module'=>'purchase','controller'=>'payment','action'=>'edit',
		);
// 		$link1=array(
// 				'module'=>'sales','controller'=>'index','action'=>'viewapp',
// 		);

		$this->view->search = $search;
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('receipt_no'=>$link,'customer_name'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	    
	    $db = new Purchase_Model_DbTable_Dbpayment();
	    $this->view->purchase = $db->getAllPurchase();
	    
	}	
	function addAction(){
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Purchase_Model_DbTable_Dbpayment();
				if($data['all_total']>0){
					$dbq->addPurchasePayment($data);
				}
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::redirectUrl("/purchase/payment/add");
				}
				Application_Form_FrmMessage::redirectUrl("/purchase/payment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		///link left not yet get from DbpurchaseOrder
		$frm = new Purchase_Form_FrmPayment(null);
		$form_pay = $frm->Payment(null);
		Application_Model_Decorator::removeAllDecorator($form_pay);
		$this->view->form_sale = $form_pay;
		 
		$db = new Purchase_Model_DbTable_Dbpayment();
		$this->view->all_vendor = $db->getAllVendor();
		$this->view->purchase_num = $db->getAllPurchaseNo();
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Purchase_Model_DbTable_Dbpayment();
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			try {
				if(!empty($data['identity'])){
					$dbq->updatePayment($data);
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/purchase/payment");
				}
				if(!empty($data['btnsavenew'])){
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/purchase/payment/add");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getVendorPaymentById($id);
		$this->view->reciept_detail = $row;
// 		if(empty($row)){
// 			Application_Form_FrmMessage::Sucessfull("NO_DATA","/sales/payment");
// 		}if($row['is_approved']==1){
// 			Application_Form_FrmMessage::Sucessfull("SALE_ORDER_WARNING","/sales/payment");
// 		}
// 		$this->view->rs = $dbq->getSaleorderItemDetailid($id);
// 		$this->view->rsterm = $dbq->getTermconditionByid($id);
		
		///link left not yet get from DbpurchaseOrder
		$frm = new Purchase_Form_FrmPayment(null);
		$form_pay = $frm->Payment($row);
		Application_Model_Decorator::removeAllDecorator($form_pay);
		$this->view->form_sale = $form_pay;
		 
		// item option in select
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->term_opt = $db->getAllTermCondition(1);
	}
	
	public function getinvoiceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Purchase_Model_DbTable_Dbpayment();
			$rs = $db->getAllInvoicePaymentPurchase($post['post_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}	
	
}