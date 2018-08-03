<?php

class Sales_Model_DbTable_DbRepay extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_borrowers';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function getAllRepay($search){
    	$db = $this->getAdapter();
    	$sql=" SELECT id,
					name_borrow,
					(SELECT name_kh FROM tb_view WHERE TYPE=19 AND key_code=gender) AS gender,
					phone,
					DATE,
					qtys,
					notes,
					(SELECT name_kh FROM tb_view AS v WHERE v.type=5 AND v.key_code = tb_borrowers.status) AS STATUS
					FROM `tb_borrowers`
					WHERE name_borrow!='' AND  type=2
    	";
    	$where = '';

    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " and ".$from_date." AND ".$to_date;
    	if(!empty($search['ad_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['ad_search']));
    		$s_search = str_replace(' ', '', $s_search);
    		$s_where[] = "REPLACE(name_borrow,'','') LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where .= " AND status = ".$search['status'];
    	}
    	$order=" ORDER BY id DESC ";
  //  	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addRepays($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
		try{
			$rs= $this->getRepayDetails($data['name_borrow']);
			//print_r($rows);exit();
			$total=$data['qtys'];
			$repay=0;
	    	if(!empty($rs)) foreach($rs As $row){
	    		if($total>0){
	    			$total = $total - $row['qtys_after'];
	    			if($total<0){
	    				$arr = array(
	    					'qtys_after'=>abs($total)
	    				);
	    			}else{
	    				$arr=array(
	    					'is_complete'=>1,
	    					'qtys_after'=>0
	    				);
	    			}
	    			$this->_name='tb_borrowers';
	    			$where=" id=".$row['id'];
	    			$this->update($arr, $where);
	    		}
	    	}
	    	$_arr=array(
	    			'name_borrow' 		 => $data['name_borrow'],
	    			'gender'			 => $data['gender'],
	    			'phone' 			 => $data['phone'],
	    			'date'				 => empty($data['date'])?null:date("Y-m-d H:i:s",strtotime($data['date'])),
	    			'qtys'	     	     => $data['qtys'],
	    			'notes'	     	     => $data['notes'],
	    			'status'	         => 1,
	    			'type'	      	     => 2,
	    	);
	    	$this->insert($_arr);
			$db->commit();
    	}catch(Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message('INSERT_FAIL');
    		echo $e->getMessage();exit();
    	}
    }
    public function getRepayDetail($name_borrow){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				*,
    				SUM(qtys_after)As total,
					DATE_FORMAT(DATE, '%d-%m-%Y') AS date_borrrow	
				FROM 
					`tb_borrowers` 
				WHERE
					status=1
				    AND name_borrow!='' 
				    AND name_borrow='$name_borrow'				
					AND type=1 LIMIT 1
    		";
    	return $db->fetchRow($sql);
    }
    
    public function getRepayDetails($name_borrow){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				*,
    				qtys As total,
			    	DATE_FORMAT(DATE, '%d-%m-%Y') AS date_borrrow
			    FROM 
			    	`tb_borrowers`
			    WHERE
			    	status=1
			    	AND name_borrow!=''
			    	AND name_borrow='$name_borrow'
			    	AND type=1 
			    	and is_complete=0
			    order by 
			    	id ASC	
    		";
    	return $db->fetchAll($sql);
    }
    
    function getAllRepays(){
    	$db = $this->getAdapter();
    	$sql=" SELECT DISTINCT(name_borrow) AS name FROM tb_borrowers WHERE name_borrow!=''";
    	return $db->fetchAll($sql);
    }
    public function updateRepay($data, $id){
    	$db = $this->getAdapter();
    	try{
    	////////////////////// update old payment back ////////////////////////////////////////////////////////
	    	$old_row = $this->getRepayById($id);
	    	$sql = "select * from tb_borrowers where name_borrow = '".$old_row['name_borrow']."' and type=1 and status=1 order by id DESC limit 1";
	    	$row = $db->fetchRow($sql);
	    	$arr=array(
		    			'qtys_after'	=> $row['qtys_after'] + $old_row['qtys'],
		    			'is_complete'	=>0,
	    			);
	    	$where = " id = ".$row['id'];
	    	$this->update($arr, $where);
	    ////////////////////////////////////////////////////////////////////////////////////////////////////////
	    		    	
	    	$rs= $this->getRepayDetails($data['name_borrow']);
	    	$total=$data['qtys'];
	    	if(!empty($rs)) foreach($rs As $row){
	    		if($total>0){
	    			$total = $total - $row['qtys_after'];
	    			if($total<0){
	    				$arr = array(
	    						'qtys_after'=>abs($total)
	    				);
	    			}else{
	    				$arr=array(
	    						'is_complete'=>1,
	    						'qtys_after'=>0
	    				);
	    			}
	    			$this->_name='tb_borrowers';
	    			$where=" id=".$row['id'];
	    			$this->update($arr, $where);
	    		}
	    	}
	    	
	    	$_arr=array(
	    			'name_borrow' 		 => $data['name_borrow'],
	    			'gender'			 => $data['gender'],
	    			'phone' 			 => $data['phone'],
	    			'date'				 => empty($data['date'])?null:date("Y-m-d H:i:s",strtotime($data['date'])),
	    			'qtys'	     	     => $data['qtys'],
	    			'notes'	     	     => $data['notes'],
	    			'status'	         => $data['status'],
	    			'type'	      	     => 2,
	    	);
	    	$where="id= $id";
			$this->update($_arr, $where);
    	}catch (Exception $e){
    		echo $e->getMessage();exit();
    	}
    }
    public function getRepayById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM tb_borrowers WHERE id = $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
 
   
}

