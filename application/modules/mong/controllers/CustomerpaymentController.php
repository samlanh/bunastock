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
					'ad_search'		=>'',
					'start_date'	=>date("Y-m-d"),
					'end_date'		=>date("Y-m-d"),
					'customer_id'	=>"",
				);
		}
		$db = new Mong_Model_DbTable_DbCustomerPayment();
		$rows = $db->getAllReciept($search);
		$columns=array("លេខបង្កាន់ដៃ","ទីតាំងបុណ្យ","លេខវិក័យបត្រ","CUSTOMER_NAME","DATE","TOTAL","PAID","BALANCE","NOTE","BY_USER","ស្ថានភាព");
		
		$link=array(
			'module'=>'mong','controller'=>'customerpayment','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows, array('receipt_no'=>$link,'customer_name'=>$link,'branch_name'=>$link,'date_input'=>$link,'invoice'=>$link));
				
				
		$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	
	function invoiceprintAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_Dbpayment();
		$invoice_id = $db->getInvoiceByReceiptId($id,2); // 2=mong receipt
		if(!empty($invoice_id)){
			$this->_redirect("/mong/index/invoice/id/".$invoice_id);
		}
	}
	
	function addAction(){
		$db = new Mong_Model_DbTable_DbCustomerPayment();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addCustomerPayment($data);
				Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				Application_Form_FrmMessage::redirectUrl("/mong/customerpayment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('បញ្ចូលមិនត្រឹមត្រូវ');
				echo $e->getMessage();
			}
		}
		
		$db = new Mong_Model_DbTable_DbCustomerPayment();
		$this->view->customer_name = $db->getMongCustomerName();
		$this->view->customer_invoice = $db->getMongInvoice();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->receipt = $_db->getReceiptNumber(1);
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->receiver_name = $db->getAllReceiverName();
	}
	function editAction(){
		$id = $this->getRequest()->getParam('id');
		$db = new Mong_Model_DbTable_DbCustomerPayment();
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			try {
				$db->updateCustomerPayment($data,$id);
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ","/mong/customerpayment");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('កែប្រែមិនត្រឹមត្រូវ');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->row = $db->getRecieptById($id);
		$this->view->customer_name = $db->getMongCustomerName();
		$this->view->customer_invoice = $db->getMongInvoice();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->receipt = $_db->getReceiptNumber(1);
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->receiver_name = $db->getAllReceiverName();
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
			$rs = $db->getReceipt($post['mong_id'],$post['cus_id'],$post['type_id'],$post['action']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
}