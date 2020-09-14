<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class users_model extends MY_Model
{
    function users_model()
    {
        parent::__construct();
    }


	function getUser($d)
	{
		$this->db->where($d);
		$this->db->where('IFNULL(inactive,0)!=', 1);  //--ryn
		unset($rec);
		
		$rec = $this->db->get('user')->row_array();
		if($this->db->get('user')->num_rows() > 0){
			return  $this->db->get('user')->row();
		}else{
			return FALSE;
		}
	}
	
	
	function getEmail($d)
	{
		unset($rec);
		$this->db->select('email');
		$this->db->where($d);
		
		$rec = $this->db->get('user')->row_array();
		if(count($rec) > 0)
		{
			return $rec;
		}else{
			return FALSE;
		}
	}
	
	
	function getUserID($data)
	{
		$this->db->select("user_id");
		$this->db->where($data);
		return $this->db->get('user')->row_array();	
	}
	
	/* added by espie */
	function getUserCategory($data)
	{
		$this->db->select("group_id");
		$this->db->where("user_id", $data);
		return $this->db->get('user')->row_array();	
	}
	
	
	function getEmployeeID($data)
	{
		$this->db->select("employee_id");
		$this->db->where($data);
		return $this->db->get('user')->row()->employee_id;	
	}
	
	/* espie*/
	
    function getGroupLevel($data)
	{
        return 0;
    }
	
	
    function addUser($data){
        
    }
	
	
	function getPorletParams($params)
	{
		$this->db->select('params');
		$this->db->where($params);
		$rec = $this->db->get('user')->row_array();
		
		if( !empty($rec['params']) )
		{
			$userconfig = array();
			eval($rec['params']);
			if( !empty($userconfig['portlet']) )
			{
				return $userconfig['portlet'];
			}
		}
		return FALSE;
	}
	
	//return the actual user params
	function getUserParams($params)
	{
		$this->db->select('params');
		
		if(!empty($params))
			$this->db->where($params);
			
		$rec = $this->db->get('user')->row_array();
		if( !empty($rec['params']) )
			return $rec['params'];
		else
			return '';
	}
	
	function updateParams($newParams, $userID)
	{
		$data = array('params' => $newParams);
		$whre = array('users_id' => $userID);
		$str = $this->db->update_string('user', $data, $whre);
		
		$this->db->query($str);
		//return $str;
	}


    function getUserByEmail($d)
    {
       return $this->db->get_where('user',$d)->row();
    }


    function updateUser($where='',$d=array())
    {
        $this->db->where($where);
        $this->db->update('user', $d);
    }

}

?>