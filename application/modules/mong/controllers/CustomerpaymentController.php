<?php
class Mong_CustomerpaymentController extends Zend_Controller_Action
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
					'text_search'=>'',
					'start_date'=>date("Y-m-d"),
					'end_date'=>date("Y-m-d"),
					'branch_id'=>-1,
					'customer_id'=>-1,
					);
		}
		$db = new Mong_Model_DbTable_DbCustomerPayment();
		$rows = $db->getAllReciept($search);
		$columns=array("BRANCH_NAME","RECIEPT_NO","បង់លើវិក័យបត្រ","CUSTOMER_NAME","DATE","TOTAL","PAID","BALANCE","បង្កាន់ដៃបង់","លុប","NOTE","BY_USER");
		
		$link=array('module'=>'mong','controller'=>'customerpayment','action'=>'edit',);
		
 		$receipt=array('module'=>'mong','controller'=>'customerpayment','action'=>'receipt',);
 		
 		$delete=array('module'=>'mong','controller'=>'customerpayment','action'=>'deleteitem',);
 				
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('លុប'=>$delete,'បោះពុម្ភ'=>$receipt,'receipt_no'=>$link,'customer_name'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	function addAction(){
		$db = new Mong_Model_DbTable_DbCustomerPayment();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addCustomerPayment($data);
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				Application_Form_FrmMessage::redirectUrl("/mong/customerpayment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				echo $e->getMessage();
			}
		}
		
		$db = new Mong_Model_DbTable_DbCustomerPayment();
		$this->view->customer_name = $db->getMongCustomerName();
		$this->view->customer_invoice = $db->getMongInvoice();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->receipt = $_db->getReceiptNumber(1);
		
	}
	function editAction(){
		$id = $this->getRequest()->getParam('id');
		$db = new Mong_Model_DbTable_DbCustomerPayment();
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			try {
				if(!empty($data['identity'])){
					$db->updatePayment($data);
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/payment");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$row = $db->getRecieptById($id);
	}	
	
	function receiptAction(){
		$dbq = new Mong_Model_DbTable_DbCustomerPayment();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$this->view->rs = $dbq->getRecieptById($id);
	}
	
	public function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		echo "<script language='javascript'>
		var r = confirm('តើលោកអ្នកពិតចង់លុបប្រតិបត្តិការណ៍នេះឫ!')​​;
		if (r == true) {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/payment/deleteitem/id/".$id."'";
		echo"}else {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/payment/'";
		echo"}
		</script>";
	
	}
	function deleteitemAction(){
		$id = $this->getRequest()->getParam("id");
		//echo $id;exit();
		$db = new Mong_Model_DbTable_DbCustomerPayment();
		$db->deletePayment($id);
		$this->_redirect("mong/customerpayment");
	}
	
	public function getCustomerInfoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbCustomerPayment();
			$rs = $db->getCustomerInfo($post['id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	public function getReceiptAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbCustomerPayment();
			$rs = $db->getReceipt($post['mong_id'],$post['cus_id'],$post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
}