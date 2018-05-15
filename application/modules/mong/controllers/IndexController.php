<?php
class Mong_IndexController extends Zend_Controller_Action
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
    	$db = new Product_Model_DbTable_DbProduct();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'brand'		=>	'',
    			'category'	=>	'',
    			'model'		=>	'',
    			'color'		=>	'',
    			'size'		=>	'',
    			'status'	=>	1
    		);
    	}
		$rows = $db->getAllProductForAdmin($data);
		$columns=array("BRANCH_NAME","ITEM_CODE","ITEM_NAME",
					"PRODUCT_CATEGORY","OPTION_TYPE","MEASURE","QTY","SOLD_PRICE","COST_PRICE","USER","STATUS");
		$link=array(
				'module'=>'mong','controller'=>'index','action'=>'edit',
		);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('item_name'=>$link,'item_code'=>$link,'barcode'=>$link,'branch'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Mong_Model_DbTable_DbIndex();
		if($this->getRequest()->isPost()){ 
			try{
				$data = $this->getRequest()->getPost();
				$db->addMong($data);
				if(isset($data["save_close"]))
				{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/mong/index/index');
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			  }catch (Exception $e){
			  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			  }
		}
		
		$this->view->mong_type = $db->getMongType();
		$this->view->person_in_charge = $db->getPersonInCharge();
		$this->view->all_dead = $db->getDeadPerson();
		$this->view->constructor = $db->getConstructor();
		$this->view->constructor_item = $db->getConstructorItem();
		
		$_db=new Sales_Model_DbTable_DbProgram();
		$khmer_year = $_db->getAllKhmerYear();
		$this->view->khmer_year = $khmer_year;
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rsservice = $db->getAllProductName(1);
		$this->view->rscustomer = $db->getAllCustomerName();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->invoice = $db->getReceiptNumber(1);
		$this->view->saleagent = $db->getSaleAgent();
		$this->view->diepeople = $db->getAllDiePeople();
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$db = new Mong_Model_DbTable_DbIndex();
		
// 		if($this->getRequest()->isPost()){ 
// 				try{
// 					$post = $this->getRequest()->getPost();
// 					$post["id"] = $id;
// 					$db->edit($post);
// 					if(isset($post["save_close"]))
// 					{
// 						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/product/index');
// 					}
// 				  }catch (Exception $e){
// 				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
// 				  }
// 		}
// 		$this->view->rs_location = $db->getProductLocation($id);
		
		$this->view->mong_type = $db->getMongType();
		$this->view->person_in_charge = $db->getPersonInCharge();
		$this->view->all_dead = $db->getDeadPerson();
		$this->view->constructor = $db->getConstructor();
		$this->view->constructor_item = $db->getConstructorItem();
		
		$_db=new Sales_Model_DbTable_DbProgram();
		$khmer_year = $_db->getAllKhmerYear();
		$this->view->khmer_year = $khmer_year;
		
		$db = new Sales_Model_DbTable_Dbpos();
		$this->view->rsproduct = $db->getAllProductName();
		$this->view->rsservice = $db->getAllProductName(1);
		$this->view->rscustomer = $db->getAllCustomerName();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->invoice = $db->getReceiptNumber(1);
		$this->view->saleagent = $db->getSaleAgent();
		$this->view->diepeople = $db->getAllDiePeople();

	}
	
	function getDeadDetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbIndex();
			$detail = $db->getDeadDetail($data['dead_id']);
			print_r(Zend_Json::encode($detail));
			exit();
		}
	}
	
	function getConstructorDetailAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbIndex();
			$detail = $db->getConstructorDetail($data['constructor']);
			print_r(Zend_Json::encode($detail));
			exit();
		}
	}
	
	function getItemPriceAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Mong_Model_DbTable_DbIndex();
			$price = $db->getItemPrice($data['item_id']);
			print_r(Zend_Json::encode($price));
			exit();
		}
	}
	
}