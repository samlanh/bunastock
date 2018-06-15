<?php
class Mong_ConstructorpaymentController extends Zend_Controller_Action
{	
	
public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
					'sale_order_id'=>"",
				);
		}
		$db = new Mong_Model_DbTable_DbConstructorPayment();
		$rows = $db->getAllPartnerPayment($search);
		$columns=array("សាខា","វិក័យបត្រ","កាលបរិច្ឋេទ","បង់ជា","តម្លៃសរុប","ប្រាក់បានបង់","នៅខ្វះ","លុប","សម្គាល់","អ្នកប្រើប្រាស់");
		$link=array(
			'module'=>'mong','controller'=>'constructorpayment','action'=>'edit',
		);
 		
 		$delete=array(
 				'module'=>'mong','controller'=>'constructorpayment','action'=>'deleteitem',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('លុប'=>$delete,'receipt_no'=>$link,'customer_name'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$this->view->sale_invoice = $db->getPartnerPaymentBalance();
		$this->view->search = $search;
		
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	function addAction(){
		$db = new Mong_Model_DbTable_DbConstructorPayment();
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addConstructorPayment($data);
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				Application_Form_FrmMessage::redirectUrl("/mong/constructorpayment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				echo $e->getMessage();
			}
		}
		
		$this->view->invoice = $db->getMongInvoice();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->receipt = $_db->getReceiptNumber(1);
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Mong_Model_DbTable_DbConstructorPayment();
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			try {
				if(!empty($data['identity'])){
					$dbq->updatePayment($data);
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/mong/constructorpayment");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $dbq->getRecieptById($id);
		$this->view->reciept_detail = $dbq->getRecieptDetail($id);
		$frm = new Sales_Form_FrmPayment(null);
		$form_pay = $frm->Payment($row);
		Application_Model_Decorator::removeAllDecorator($form_pay);
		$this->view->form_sale = $form_pay;
				 
		$items = new Application_Model_GlobalClass();
		$this->view->items = $items->getProductOption();
		$this->view->term_opt = $db->getAllTermCondition(1);
	}	
	
	public function getinvoiceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getAllInvoicePayment($post['post_id'], $post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	function receiptAction(){
		$dbq = new Mong_Model_DbTable_DbConstructorPayment();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$this->view->rs = $dbq->getRecieptById($id);
	}
	public function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
		echo "<script language='javascript'>
		var r = confirm('តើលោកអ្នកពិតចង់លុបប្រតិបត្តិការណ៍នេះឫ!')​​;
		if (r == true) {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/mong/constructorpayment/deleteitem/id/".$id."'";
		echo"}else {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/mong/constructorpayment/'";
		echo"}
		</script>";
	
	}
	function deleteitemAction(){
		$id = $this->getRequest()->getParam("id");
		//echo $id;exit();
		$db = new Mong_Model_DbTable_DbConstructorPayment();
		$db->delettePayment($id);
		$this->_redirect("sales/payment");
	}
	
	public function getCustomerInfoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbConstructorPayment();
			$rs = $db->getCustomerInfo($post['id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	public function getConstructorPaymentAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbConstructorPayment();
			$rs = $db->getConstructorPayment($post['mong_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
}