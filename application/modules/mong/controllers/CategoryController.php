<?php
class Mong_CategoryController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$db = new Mong_Model_DbTable_DbCategory();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    		);
    	}
		$rows = $db->getAllCategory($data);
		$columns=array("NAME_CATEGORY","NOTE","STATUS");
		$link=array('module'=>'mong','controller'=>'category','action'=>'edit',);
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('title'=>$link,'tel'=>$link,'description'=>$link));
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Mong_Model_DbTable_DbCategory();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addCategory($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/mong/category/index');
					}else{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}			
//		$this->view->rsservice = $db->getAllService();
	}
	function editAction(){
		$id = $this->getRequest()->getParam('id') ;
		$db = new Mong_Model_DbTable_DbCategory();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post['id']=$id;
					$db->updateCategory($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS", '/mong/category/index');
					}else{
						Application_Form_FrmMessage::message("UPDATE_SUCCESS");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("UPDATE_ERROR",$err = $e->getMessage());
				  }
			}			
	 		$row = $db->getCategoryById($id);
	 		$this->view->category = $row;
	 		//print_r($row); exit();
//	 		$this->view->rsresponsible = $db->getAllService();				 
	}	
}