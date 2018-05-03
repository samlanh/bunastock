<?php

class Sales_Model_DbTable_DbProgram extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_program';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    
    public function addProgram($data){
    	$db=$this->getAdapter();
    	$db->beginTransaction();
    	
//     	echo $data['lerk_sop_date']."<br>";
//     	echo $data['hae_sop_date']."<br>";
// //     	echo $data['pjos_sop_date']."<br>";
    	
// //     	echo $data['dead_dob']."<br>";
// //     	echo $data['partner_dob']."<br>";
    	
// //     	echo date("Y-m-d",strtotime($data['dead_dob']))."<br>";
// //     	echo date("Y-m-d",strtotime($data['partner_dob']))."<br>";
    	
//     	echo date("Y-m-d",strtotime($data['lerk_sop_date']))."<br>";
//     	echo date("Y-m-d",strtotime($data['hae_sop_date']))."<br>";
// //     	echo date("Y-m-d",strtotime($data['pjos_sop_date']))."<br>";
    	
// //     	$date = new DateTime($data['lerk_sop_date']);
// //     	echo $date->format("Y-m-d")."<br>";
    	
    	
//     	exit();
    	
    	try{
	    	$_arr=array(
	    			'dead_name' 			=> $data['dead_name'],
	    			'dead_name_chinese' 	=> $data['dead_name_chinese'],
	    			'dead_sex' 				=> $data['dead_sex'],
	    			'dead_khmer_year'	    => $data['dead_khmer_year'],
	    			'dead_age'      		=> $data['dead_age'],
	    			'dead_dob'           	=> date("Y-m-d",strtotime($data['dead_dob'])),
	    			'date_time_dead'	    => date("Y-m-d h:i:s",strtotime($data['date_time_dead'])),
	    			'dead_pob'           	=> $data['dead_pob'],
	    			
	    			'partner_name' 			=> $data['partner_name'],
	    			'partner_name_chinese' 	=> $data['partner_name_chinese'],
	    			'partner_sex' 			=> $data['partner_sex'],
	    			'partner_khmer_year'	=> $data['partner_khmer_year'],
	    			'partner_age'      		=> $data['partner_age'],
	    			'partner_dob'           => date("Y-m-d",strtotime($data['partner_dob'])),
	    			'partner_status'	    => $data['partner_status'],
	    			'partner_pob'           => $data['partner_pob'],
	    			
	    			'place_of_program'      => $data['place_of_program'],
	    			'type_romleay_sop'      => $data['type_romleay_sop'],
	    			'place_pjos_sop'	    => $data['place_pjos_sop'],
	    			'note'           		=> $data['note'],
	    			
	    			'lerk_sop_date'      	=> date("Y-m-d",strtotime($data['lerk_sop_date'])),
	    			'lerk_sop_time'     	=> $data['lerk_sop_time'],
	    			'lerk_sop_opposite_year'=> $data['lerk_sop_opposite_year'],
	    			
	    			'hae_sop_date'      	=> date("Y-m-d",strtotime($data['hae_sop_date'])),
	    			'hae_sop_time'      	=> $data['hae_sop_time'],
	    			'hae_sop_opposite_year'	=> $data['hae_sop_opposite_year'],
	    			
	    			'pjos_sop_date'      	=> date("Y-m-d",strtotime($data['pjos_sop_date'])),
	    			'pjos_sop_time'      	=> $data['pjos_sop_time'],
	    			'pjos_sop_opposite_year'=> $data['pjos_sop_opposite_year'],
	    			
	    			'create_date'      => date("Y-m-d"),
	    			'modify_date'      => date("Y-m-d"),
	    			'user_id'	       => $this->getUserId(),
	    			'modify_by'	       => $this->getUserId()
	    	);
	    	
	    	$program_id = $this->insert($_arr);
	    	
	    	$this->_name = "tb_program_son_khmer_year";
	    	
	    	if(!empty($data['identity_boy'])){
	    		$ids = explode(",", $data['identity_boy']);
	    		foreach ($ids as $i){
	    			$array = array(
		    			'program_id'    => $program_id,
		    			'type'      	=> 1, // 1 = ឆ្នាំកូនប្រុស
		    			'khmer_year_id'	=> $data['khmer_year_boy_'.$i],
	    				'note'      	=> $data['boy_note_'.$i],
	    			);
	    			
	    			$this->insert($array);
	    		}
	    	}
	    	
	    	if(!empty($data['identity_girl'])){
	    		$ids = explode(",", $data['identity_girl']);
	    		foreach ($ids as $i){
	    			$array = array(
	    					'program_id'    => $program_id,
	    					'type'      	=> 2, // 2 = ឆ្នាំកូនស្រី
	    					'khmer_year_id'	=> $data['khmer_year_girl_'.$i],
	    					'note'      	=> $data['girl_note_'.$i],
	    			);
	    			 
	    			$this->insert($array);
	    		}
	    	}
	    	
	    	$db->commit();
	    	
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();
    	}
    	
    }
    
    
    /*
	public function getProvinceById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM ln_province WHERE province_id = ".$id;
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
    public function updateProvince($_data,$id){
    	$_arr=array(
    			'code' 			   => $_data['code'],
    			'province_en_name' => $_data['en_province'],
    			'province_kh_name' => $_data['kh_province'],
    			'displayby'	       => $_data['display'],
    			'modify_date'      => Zend_Date::now(),
    			'status'           => $_data['status'],
    			'user_id'	       => $this->getUserId()
    	);
    	$where=$this->getAdapter()->quoteInto("province_id=?", $id);
    	$this->update($_arr, $where);
    }
    function getAllProvince($search=null){
    	$db = $this->getAdapter();
    	$sql = " SELECT province_id AS id,province_en_name,province_kh_name,modify_date
    	FROM $this->_name
    	WHERE 1 ";
    	$order=" order by province_id DESC";
    	$where = '';
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]=" code LIKE '%{$s_search}%'";
    		$s_where[]=" province_en_name LIKE '%{$s_search}%'";
    		$s_where[]=" province_kh_name LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND status = ".$db->quote($search['status']);
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
   
    */
   
    function getAllKhmerYear(){
    	$db=$this->getAdapter();
    	$sql="select id,name from tb_year_khmer where status=1";
    	return $db->fetchAll($sql);
    }
    
    
    
}

