<?php
class report_IncomeController extends Zend_Controller_Action
{
	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    
    public function indexAction()
    {
    	
    }
    public function rptAllIncomeAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'		=>'',
    				'start_date'	=>date("Y-m-d"),
    				'end_date'		=>date("Y-m-d"),
    				'customer_id'	=>0,
    				'type'			=>0,
    		);
    	}
    	$this->view->rssearch = $data;
    	
    	$db = new report_Model_DbSale();
    	if($data['type']==0){
    		$this->view->sale_payment = $db->totalSale($data);
    		$this->view->mong_payment = $db->totalMong($data);
    		$this->view->donor_payment = $db->totalDonor($data);
    	}else if($data['type']==1){
    		$this->view->sale_payment = $db->totalSale($data);
    	}else if($data['type']==2){
    		$this->view->mong_payment = $db->totalMong($data);
    	}else{
    		$this->view->donor_payment = $db->totalDonor($data);
    	}
    	
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_purchase = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    
    public function rptSalesAction()//purchase report
    {
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'		=>'',
    				'start_date'	=>date("Y-m-d"),
    				'end_date'		=>date("Y-m-d"),
    				'customer_id'	=>0,
    				'is_complete'	=>'',
    		);
    	}
    	$this->view->rssearch = $data;
    	
    	$query = new report_Model_DbSale();
    	$this->view->repurchase =  $query->getAllSaleOrderReport($data);
    	
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_purchase = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function rptSalesPaymentAction(){
    	$id = $this->getRequest()->getParam('id');
    	$query = new report_Model_DbSale();
    	$this->view->sale_payment =  $query->getSalePaymentById($id);
    }
   
    public function salesdetailAction(){
    	$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
    	if(empty($id)){
    		$this->_redirect("/report/index/rpt-sales");
    	}
    	$query = new report_Model_DbQuery();
    	$this->view->product =  $query->getProductSaleById($id);
		$rs = $query->getProductSaleById($id);
    	if(empty($rs)){
    		$this->_redirect("/report/index/rpt-sales");
    	}
    }
    
    public function rptsalemongAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']	= date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']	= date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'		=>	'',
    				'is_complete'	=>	'',
    				'start_date'	=>	date("Y-m-d"),
    				'end_date'		=>	date("Y-m-d"),
    				'customer_id'	=>0,
    		);
    	}
    	$this->view->rssearch = $data;
    	$this->view->other = $db->getAllsaleMong($data);
    	
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_salemong = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
	
	function rptMongPaymentAction(){
		$id = $this->getRequest()->getParam('id');
		$query = new report_Model_DbCustomerPayment();
		$this->view->rsreceipt = $query->getMongPaymentById($id);
		 
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->form_purchase = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	public function rptCustomerPaymentAction()
	{
		$db = new report_Model_DbCustomerPayment();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['start_date']	= date("Y-m-d",strtotime($data['start_date']));
			$data['end_date']	= date("Y-m-d",strtotime($data['end_date']));
		}else{
			$data = array(
					'text_search'	=>	'',
					'customer_id'=>	'',
					'start_date'=>	date("Y-m-d"),
					'end_date'	=>	date("Y-m-d"),
					'status'	=>	-1,
					'order'		=>	1,
					'type'		=>	0,
			);
		}
		$this->view->rssearch = $data;
		if($data['type']==0){
			$this->view->sale_payment = $db->getSaleCustomerPayment($data);
			$this->view->mong_payment = $db->getMongCustomerPayment($data);
		}else if($data['type']==1){
			$this->view->sale_payment = $db->getSaleCustomerPayment($data);
		}else{
			$this->view->mong_payment = $db->getMongCustomerPayment($data);
		}
		
		$formFilter = new Sales_Form_FrmSearch();
		$this->view->form_sale = $formFilter;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	
	
}




