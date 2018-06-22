<?php
class report_OtherController extends Zend_Controller_Action
{
	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    public function indexAction()
    {   	 
    
    }
    public function rptdonorslistAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'status'	=>	-1,
    			'start_date'=> date('Y-m-d'),
    			'end_date'=>date('Y-m-d')
    		);
    	}
    	$this->view->other = $db->getAllDonors($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_donors = $formFilter;
     	Application_Model_Decorator::removeAllDecorator($formFilter);
		$this->view->rssearch = $data;
    }
    public function rptsponsorshipAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'status'	=>	-1,
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d') 				
    		);
    	}
		$this->view->rssearch = $data;
    	$this->view->other = $db->getAllsposorship($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_sponsorship = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    
    public function rptpaymentlistAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'scale'		=>	0,
    				'category'	=>	0,
    				'status'	=>	-1,
    		);
    	}
    	//$this->view->rssearch = $data;
    	$this->view->other = $db->getAllpaymentList($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_paymentlist = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function partnerserviceAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'service'	=>	0,
    				'status'	=>	-1,
    		);
    	}
    	//$this->view->rssearch = $data;
    	$this->view->other = $db->getAllPartnerService($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->partnerservice = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function rptworkerAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();
    	}else{
    		$search = array(
    				'ad_search'	=>	'',
    				'status'	=>	-1,
    		);
    	}
    	$this->view->search = $search;
    	$this->view->other = $db->getAllworker($search);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_worker = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function listsponorshipAction()
    {
    	$id=$this->getRequest()->getParam('id');
    	$db = new report_Model_DbOther();
    	$this->view->donor = $db->getAllDonorship($id);
    	$this->view->other = $db->getAlllistSponorship($id);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_listsponorship = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    	
//     	$donorship = $db->getDonorship();
//     	$this->view->row = $donorship;
    }
    public function fastiveprogramAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();
    	}else{
    		$search = array(
    				'ad_search'	=>	'',
    				'status'	=>	-1,
    		);
    	}
    	$this->view->search = $search;
    	$this->view->other = $db->getAllprogram($search);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_fastiveprogram = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    	
}