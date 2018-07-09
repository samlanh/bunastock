<?php
class Sales_PossaleController extends Zend_Controller_Action
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
		$db = new Sales_Model_DbTable_Dbpos();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$db->addSaleOrder($data);
				}
				Application_Form_FrmMessage::message("INSERT_SUCESS");
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
 		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rscustomer = $db->getAllCustomerName();
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->term_opt = $db->getAllTermCondition();
		
		$formpopup = new Sales_Form_FrmCustomer(null);
		$formpopup = $formpopup->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->invoice = $db->getSalesNumber(1);
		
		$db = new Sales_Model_DbTable_Dbexchangerate();
		$this->view->rsrate= $db->getExchangeRate();
	}
	
	public function addAction()
	{
		$db = new Sales_Model_DbTable_Dbpos();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			try {
				if(!empty($data['identity'])){
					$db->addSaleOrder($data);
				}
				Application_Form_FrmMessage::message("បញ្ចូលដោយជោគជ័យ",'/sales/index');
			}catch (Exception $e){
				Application_Form_FrmMessage::message('បញ្ចូលមិនត្រឹមត្រូវ');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$this->view->branch_id = $db->getBranchId();
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->category = $db->getAllProductCategory();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rsservice = $db->getAllProductName(1);
		$this->view->rscustomer = $db->getAllCustomerName();
		$this->view->partner = $db->getAllPartnerService();
	
		$this->view->receiver_name = $db->getAllReceiverName();
		
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->invoice = $db->getInvoiceNumber(1);
		$this->view->saleagent = $db->getSaleAgent();
		$this->view->diepeople = $db->getAllDiePeople();
	
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->exchange_rate = $db->getExchangeRate();
		
	}
	
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index");
		}
		$query = new Sales_Model_DbTable_Dbpos();
		
		$db = new Sales_Model_DbTable_Dbpos();
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			//print_r($data);exit();
			try {
				if(!empty($data['identity'])){
					$db->editSale($data,$id);
				}
				Application_Form_FrmMessage::Sucessfull("កែប្រែដោយជោគជ័យ", '/sales/index/index');
			}catch (Exception $e){
				Application_Form_FrmMessage::message('Failed');
				echo $e->getMessage();
			}
		}
		$db = new Sales_Model_DbTable_Dbpos();
		
		$this->view->row = $db->getSaleById($id);
		$this->view->row_detail = $db->getSaleDetailById($id);
		$this->view->row_partner = $db->getPartnerServiceById($id);
		
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rsservice = $db->getAllProductName(1);
		$this->view->rscustomer = $db->getAllCustomerName();
		$this->view->partner = $db->getAllPartnerService();
		$this->view->receiver_name = $db->getAllReceiverName();
		$this->view->category = $db->getAllProductCategory();
	
		$form = new Sales_Form_FrmCustomer(null);
		$formpopup = $form->Formcustomer(null);
		Application_Model_Decorator::removeAllDecorator($formpopup);
		$this->view->form_customer = $formpopup;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->invoice = $db->getInvoiceNumber(1);
		$this->view->saleagent = $db->getSaleAgent();
		$this->view->diepeople = $db->getAllDiePeople();
	
		$db = new Sales_Model_DbTable_Dbexchangerate();
		$this->view->rsrate= $db->getExchangeRate();
	}
	public function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
		echo "<script language='javascript'>
		var txt;
		var r = confirm('áž�áž¾áž›áŸ„áž€áž¢áŸ’áž“áž€áž–áž·áž�áž…áž„áŸ‹áž›áž»áž”ážœáž·áž€áŸ’áž€áž™áž”áž�áŸ’ážšáž“áŸ�áŸ‡áž«!');
		if (r == true) {";
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/possale/deleteitem/id/".$id."'";
		echo"}";
		echo"else {";
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/sales/index/'";
		echo"}
		</script>";
	}
	function deleteitemAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Sales_Model_DbTable_Dbpos();
		$db->deleteSale($id);
		$this->_redirect("sales/index");
	}
	function invoiceAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index");
		}
		$query = new Sales_Model_DbTable_Dbpos();
		$rs = $query->getSaleById($id);
		$this->view->rs = $rs;
		$this->view->rsdetail = $query->getSaleDetailById($id);
		if(empty($rs)){
			$this->_redirect("/sales/");
		}
	}
	function comlistingAction(){
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		if(empty($id)){
			$this->_redirect("/sales/index");
		}
		$query = new Sales_Model_DbTable_Dbpos();
		$rs = $query->getSaleById($id);
		$this->view->rs = $rs;
		$this->view->rsdetail = $query->getSaleDetailById($id);
		if(empty($rs)){
			$this->_redirect("/sales/");
		}
	}
		
	function getproductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$rs =$db->getProductById($post['product_id'],$post['branch_id']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	function getServicePartnerPriceAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$fee =$db->getServicePartnerPrice($post['partner_id'],$post['service_id']);
			print_r(Zend_Json::encode($fee));
			exit();
		}
	}
	
	function getTypeAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$type =$db->getType($post['product_id']);
			print_r(Zend_Json::encode($type));
			exit();
		}
	}
	
	function refreshProductAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$product = $db->getAllProductName();
			$service = $db->getAllProductName(1);
			$result = array('product'=>$product,'service'=>$service);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
	function refreshProgramAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbIndex();
			//$program = $db->getDeadPerson();
			$program = $db->getRefreshProgram();
			print_r(Zend_Json::encode($program));
			exit();
		}
	}

	function getPackageproductAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$package =$db->getPackageProduct($post['product_id']);
			print_r(Zend_Json::encode($package));
			exit();
		}
	}
	
	function getProductbycategoryAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Sales_Model_DbTable_Dbpos();
			$rs = $db->getProductByCategoryId($post['category'],$post['type']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
}