<?php
class Sales_PartnerservicepaymentController extends Zend_Controller_Action
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
		$db = new Sales_Model_DbTable_DbPartnerServicepayment();
		$rows = $db->getAllPartnerPayment($search);
		$columns=array("សាខា","ដៃគូរសេវាកម្ម","កាលបរិច្ឋេទ","បង់ជា","តម្លៃសរុប","សម្គាល់","អ្នកប្រើប្រាស់","ស្ថានភាព");
		$link=array(
			'module'=>'sales','controller'=>'partnerservicepayment','action'=>'edit',
		);
 		
 		$delete=array(
 				'module'=>'sales','controller'=>'partnerservicepayment','action'=>'deleteitem',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows, array('receipt_no'=>$link,'customer_name'=>$link,'branch_name'=>$link,
				'date_input'=>$link));
		
		$this->view->sale_invoice = $db->getPartnerPaymentBalance();
		$this->view->search = $search;
		
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
	    Application_Model_Decorator::removeAllDecorator($formFilter);
	}	
	function addAction(){
		$db = new Sales_Model_DbTable_DbPartnerServicepayment();
		
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				$db->addPartnerServicePayment($data);
				Application_Form_FrmMessage::message("INSERT_SUCESS");
				Application_Form_FrmMessage::redirectUrl("/sales/partnerservicepayment/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				echo $e->getMessage();
			}
		}
		
		$this->view->invoice = $db->getSaleInvoice();
		$this->view->partner = $db->getAllPartner();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->receipt = $_db->getReceiptNumber(1);
		
	}
	function editAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Sales_Model_DbTable_DbPartnerServicepayment();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data['id']=$id;
			try {
				$db->updatePartnerServicePayment($data,$id);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/sales/partnerservicepayment");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('UPDATE_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$this->view->row = $db->getPartnerSerivcePaymentById($id);
		
		$this->view->invoice = $db->getSaleInvoice();
		$this->view->partner = $db->getAllPartner();
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$this->view->receipt = $_db->getReceiptNumber(1);
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
		$dbq = new Sales_Model_DbTable_DbPartnerServicepayment();
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$this->view->rs = $dbq->getRecieptById($id);
	}
	public function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
		echo "<script language='javascript'>
		var r = confirm('តើលោកអ្នកពិតចង់លុបប្រតិបត្តិការណ៍នេះឫ!')​​;
		if (r == true) {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/partnerservicepayment/deleteitem/id/".$id."'";
		echo"}else {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/partnerservicepayment/'";
		echo"}
		</script>";
	
	}
	function deleteitemAction(){
		$id = $this->getRequest()->getParam("id");
		//echo $id;exit();
		$db = new Sales_Model_DbTable_DbPartnerServicepayment();
		$db->delettePayment($id);
		$this->_redirect("sales/payment");
	}
	
	public function getCustomerInfoAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbPartnerServicepayment();
			$rs = $db->getCustomerInfo($post['id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	public function getPartnerServicePaymentAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbPartnerServicepayment();
			$rs = $db->getPartnerSerivcePayment($post['sale_order_id'],$post['action']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	
	public function getAllserviceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbPartnerServicepayment();
			$rs = $db->getAllService($post['partner_id'],$post['action']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
	public function getPaidserviceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_DbPartnerServicepayment();
			$rs = $db->getPaidService($post['id']);
			echo Zend_Json::encode($rs);
			exit();
		}
	}
	
}