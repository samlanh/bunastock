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
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'status'	=>	-1,
    			'start_date'=> date('Y-m-d'),
    			'end_date'=>date('Y-m-d')
    		);
    	}
// 		$this->view->search = $db->getBranch($data["branch"]);
    	$this->view->other = $db->getAllDonors($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_donors = $formFilter;
     	Application_Model_Decorator::removeAllDecorator($formFilter);
//     	$this->view->donors = donors();
    }
    public function rptsponsorshipAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['start_date']=date("Y-m-d",strtotime($data['start_date']));
    		$data['end_date']=date("Y-m-d",strtotime($data['end_date']));
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'status'	=>	-1,
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d')
    		);
    	}
// 		$this->view->search = $db->getBranch($data["branch"]);
    	$this->view->other = $db->getAllsposorship($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_sponsorship = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function rptsalemongAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    				'ad_search'	=>	'',
    				'branch'	=>	'',
    				'status'	=>	-1,
    		);
    	}
    	// 		$this->view->search = $db->getBranch($data["branch"]);
    	$this->view->other = $db->getAllsaleMong($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_salemong = $formFilter;
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
    				'branch'	=>	'',
    				'status'	=>	-1,
    		);
    	}
    // 		$this->view->search = $db->getBranch($data["branch"]);
    	$this->view->other = $db->getAllpaymentList($data);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_paymentlist = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
    public function rptworkerAction()
    {
    	$db = new report_Model_DbOther();
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();
    		$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    	}else{
    		$search = array(
    				'ad_search'	=>	'',
    				'status'	=>	-1,
    		);
    	}
    	// 		$this->view->search = $db->getBranch($data["branch"]);
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
//     	if($this->getRequest()->isPost()){
//     		$search = $this->getRequest()->getPost();
//     		$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
//     		$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
//     	}else{
//     		$search = array(
//     				'ad_search'	=>	'',
//     				'status'	=>	-1,
//     		);
//     	}
//     	// 		$this->view->search = $db->getBranch($data["branch"]);
//     	$this->view->search = $search;
    	$this->view->other = $db->getAlllistSponorship($id);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	$this->view->form_listsponorship = $formFilter;
    	Application_Model_Decorator::removeAllDecorator($formFilter);
    }
	
    
    
    
    
    
    
//     function showbarcodeAction(){
//     	$id = ($this->getRequest()->getParam('id'));
//     	$sql ="SELECT id,barcode,item_name,cate_id,
// 			((SELECT name FROM `tb_category` WHERE id=cate_id)) as cate_name
//     	FROM `tb_product` WHERE id IN (".$id.")";
//     	$db = new Application_Model_DbTable_DbGlobal();
// //     	print_r($db->getGlobalDb($sql));
//     	$this->view->rsproduct = $db->getGlobalDb($sql);
//     }
//     public function generateBarcodeAction(){
//     	$loan_code = $this->getRequest()->getParam('pro_code');
//     	header('Content-type: image/png');
//     	$this->_helper->layout()->disableLayout();
//     	//$barcodeOptions = array('text' => "$_itemcode",'barHeight' => 30);
//     	$barcodeOptions = array('text' => "$loan_code",'barHeight' => 40);
//     	//'font' => 4(set size of label),//'barHeight' => 40//set height of img barcode
//     	$rendererOptions = array();
//     	$renderer = Zend_Barcode::factory(
//     			'Code128', 'image', $barcodeOptions, $rendererOptions
//     	)->render();
    
//     }
    
	
}