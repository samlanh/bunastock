<?php
class Donors_IndexsController extends Zend_Controller_Action
{
	public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function rptServiceAction()
    {
//     	$db = new Donors_Model_DbTable_DbIndex();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'ad_search'	=>	'',
    		);
    	}
		$list = new Application_Form_Frmlist();
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function rptTravelAction()
	{
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'ad_search'	=>	'',
			);
		}
		$list = new Application_Form_Frmlist();
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function rptLocationAction()
	{
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'ad_search'	=>	'',
			);
		}
		$list = new Application_Form_Frmlist();
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function rptTimeAction()
	{
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'ad_search'	=>	'',
			);
		}
		$list = new Application_Form_Frmlist();
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function rptTimemolAction()
	{
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'ad_search'	=>	'',
			);
		}
		$list = new Application_Form_Frmlist();
		$formFilter = new Product_Form_FrmProduct();
		$this->view->formFilter = $formFilter->productFilter();
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
}