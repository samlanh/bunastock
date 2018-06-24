<?php
class Sales_PaymentController extends Zend_Controller_Action
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
		$db = new Sales_Model_DbTable_Dbpayment();
		$rows = $db->getAllReciept($search);
		$columns=array("លេខបង្កាន់ដៃ","លេខវិក័យបត្រ","CUSTOMER_NAME","DATE","TOTAL","PAID","BALANCE","បោះពុម្ភ","បោះពុម្ភ","លុប","NOTE","BY_USER");
		$link=array(
			'module'=>'sales','controller'=>'payment','action'=>'edit',
		);
 		$receipt=array(
 			'module'=>'sales','controller'=>'payment','action'=>'receipt',
 		);
 		$invoice=array(
 				'module'=>'sales','controller'=>'payment','action'=>'invoiceprint',
 		);
 		$delete=array(
 			'module'=>'sales','controller'=>'payment','action'=>'deleteitem',
 		);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows, array('លុប'=>$delete,'បង្កាន់ដៃ'=>$receipt,'វិក័យបត្រ'=>$invoice,'receipt_no'=>$link,'customer_name'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->formFilter = $formFilter;
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	function addAction(){
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$dbq = new Sales_Model_DbTable_Dbpayment();
				$dbq->addReceiptPayment($data);
				Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ");
				Application_Form_FrmMessage::redirectUrl("/sales/payment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('បញ្ចូលមិនត្រឹមត្រូវ');
				echo $e->getMessage();
			}
		}
		
		$db = new Sales_Model_DbTable_Dbpayment();
		$this->view->customer_name = $db->getSaleCustomerName();
		$this->view->customer_invoice = $db->getSaleInvoice();
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->receiver_name = $db->getAllReceiverName();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->receipt = $_db->getReceiptNumber(1);
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$dbq = new Sales_Model_DbTable_Dbpayment();
		$db = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			try {
				if(!empty($data['identity'])){
					$dbq->updatePayment($data);
				}
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ","/sales/payment");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('កែប្រែមិនត្រឹមត្រូវ');
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
	
	function invoiceprintAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_Dbpayment();
		$invoice_id = $db->getInvoiceByReceiptId($id,1); // 1=Sale receipt
		if(!empty($invoice_id)){
			$this->_redirect("/sales/possale/invoice/id/".$invoice_id);
		}
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
		$dbq = new Sales_Model_DbTable_Dbpayment();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$this->view->rs = $dbq->getRecieptById($id);
	}
	public function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
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
		$db = new Sales_Model_DbTable_Dbpayment();
		$db->delettePayment($id);
		$this->_redirect("sales/payment");
	}
	
	public function getCustomerInfoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpayment();
			$rs = $db->getCustomerInfo($post['id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	public function getReceiptAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpayment();
			$rs = $db->getReceipt($post['mong_id'],$post['cus_id'],$post['type_id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
}