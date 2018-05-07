<?php
class Sales_PartnerserviceController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$db = new Sales_Model_DbTable_DbPartnerService();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    			'branch'	=>	'',
    			'brand'		=>	'',

    		);
    	}
		$rows = $db->getAllPartnerService($data);
		$columns=array("BRANCH_NAME","ITEM_CODE","ITEM_NAME","PRODUCT_CATEGORY","OPTION_TYPE","MEASURE","QTY");
		$link=array('module'=>'sales','controller'=>'index','action'=>'edit',);
		
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('partner_name'=>$link,'descrn'=>$link,'addresss'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Sales_Model_DbTable_DbPartnerService();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addService($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/sales/partnerservice/index');
					}else{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}	
		
		$this->view->rsservice = $db->getAlService();
	}
	public function editAction(){
		$id = $this->getRequest()->getParam("id"); 
		$db = new Sales_Model_DbTable_DbPartnerService();
		if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->updateService($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/sales/partnerservice/index');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
		}
//		$rs = $db->getServiceById($id);
 		$this->view->rs =  $db->getServiceById($id);
		$this->view->Service = $service;
 
		}
}