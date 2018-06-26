<?php 
class Rsvacl_Model_DbTable_DbExchangeRate extends Zend_Db_Table_Abstract
{
	protected  $_name = "tb_exchange_rate";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	
	function submit($data){
		try {
			$arr = array(
					'reil'		=>	$data["reil"],
					'user_id'	=>	$this->getUserId(),
					'create_date'=>	date("Y-m-d H:i:s"),
			);
			$where = " id = ".$data['id'];
			$this->update($arr,$where);
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
		
	function getExchangeRate(){
		$db = $this->getAdapter();
		$sql="select * from tb_exchange_rate where active=1 order by id DESC limit 1";
		return $db->fetchRow($sql);
	}
	
}
?>
