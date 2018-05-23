<?php
class Donors_IndexController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
//     protected function GetuserInfoAction(){
//     	$user_info = new Application_Model_DbTable_DbGetUserInfo();
//     	$result = $user_info->getUserInfo();
//     	return $result;
//     }
// 	function updatecodeAction(){
// 		$db = new Product_Model_DbTable_DbProduct();
// 		$db->getProductCoded();
// 	}
    public function indexAction()
    {
    	$db = new Donors_Model_DbTable_DbIndex();
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
		$rows = $db->getAllDonor($data);
		$columns=array("ឈ្មោះប្ដី","ឈ្មោះប្រពន្ទ","ទូរស័ព្ទ","អាស័យដ្ឋាន","តម្រូវការ","ប្រើយូរបំផុត","សម្គាល់ផ្សេងៗ","លេខបង្កាន់ដៃ","ថ្ងៃបង់ប្រាក់","ចំនួនម្ឈូស","តម្លៃរាយ","តម្លៃសរុប","សម្គាល់","ថ្ងៃបង្កើត","USER","STATUS");
		$link=array(
				'module'=>'donors','controller'=>'index','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(10, $columns, $rows,array('donor_name'=>$link,'donor_female'=>$link,'tel'=>$link,'address'=>$link,'receipt_no'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Donors_Model_DbTable_DbIndex();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->addDonor($data);
				if(isset($data["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/donors/index/index');
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			  }
		}
		$_db = new Application_Model_DbTable_DbGlobal();
		$receipt = $_db->getReceiptNumber();
		$this->view->receipt_no =  $receipt;
			
	}
	public function editAction()
	{
		echo $id = $this->getRequest()->getParam("id"); 
		$db = new Donors_Model_DbTable_DbIndex();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->editDonor($data,$id);
				if(isset($data["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/donors/index/index');
				}
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			  }
		}
		$this->view->row = $db->getDonorById($id);

	}
}