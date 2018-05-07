<?php
class Donors_DonateController extends Zend_Controller_Action
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
    	$db = new Donors_Model_DbTable_DbDonate();
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
		$rows = $db->getAllDonate($data);
		$columns=array("ឈ្មោះសព","ភេទ","អាយុ","អាស័យដ្ឋាន","ឈ្មោះសប្បុរសជន","ថ្ងៃចេញម្ឈូស","សម្គាល់","ថ្ងៃបង្កើត","USER","STATUS");
		$link=array(
				'module'=>'donors','controller'=>'donate','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('dead_name'=>$link,'donor_name'=>$link,'date_jenh'=>$link,'dead_sex'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Donors_Model_DbTable_DbDonate();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->addDonate($data);
				if(isset($data["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/donors/donate/index');
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			  }
		}

		$this->view->donor = $db->getAllDonor();
		
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$db = new Donors_Model_DbTable_DbDonate();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->editDonate($data,$id);
				if(isset($data["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",'/donors/donate/index');
				}
			}catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			}
		}
		$this->view->donor = $db->getAllDonor();
		$this->view->row = $db->getDonateById($id);

	}
}