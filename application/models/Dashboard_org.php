<?php
class Dashboard extends CI_Model {
 
    function __construct()
     {
         // Call the Model constructor
         parent::__construct();
     }

	function check_unlist()
	{
		$user_id=$this->session->userdata('user_id');
		$query=$this->db->query('select * from Post Where user_id="'.$user_id.'" and (Active=0 or Active=3 or Active=93)');
		return $query;
	}

	function check_list()
	{
		$user_id=$this->session->userdata('user_id');
		$this->db->query('update Post set DateExpire=DATE_ADD(DateCreate,INTERVAL AgreePostDay Day) Where user_id="'.$user_id.'" and DateExpire is null');
		$query=$this->db->query('select * from Post Where user_id="'.$user_id.'" and (Active=1 or Active=92)');
		return $query;
	}

	function imageList($POID)
	{
		$query=$this->db->query('select file from ImagePost Where POID="'.$POID.'" Limit 1');
		if ($query->num_rows() > 0){
			$row=$query->row();
			$file=$row->file;
		} else {
			$file="/projImg/no-icon.gif";
		}
		return $file;
	}	
	
	function DateExpireNum($Token)
	{
		$query=$this->db->query('Select DATEDIFF(DateExpire,CURDATE()) as NumDate from Post Where Token="'.$Token.'"');
		$row=$query->row();
		return $row->NumDate;
	}
	 function AddDateExpire($Token)
	{
		$this->db->query('Update Post set  DateExpire=DATE_ADD(DateExpire,INTERVAL 60 Day) Where Token="'.$Token.'"');
	}

	function checkMsg($POID)
	{
		$query=$this->db->query('Select Msg from BlockPostMsg Where POID="'.$POID.'"');
		if ($query->num_rows() >0){
			$row=$query->row();
			$Msg=$row->Msg;
		} else {
			$Msg="";
		}
		return $Msg;
	}

	function checkMsg2($POID)
	{
		$query=$this->db->query('Select Msg2 from BlockPostMsg Where POID="'.$POID.'"');
		if ($query->num_rows() >0){
			$row=$query->row();
			$Msg=$row->Msg2;
		} else {
			$Msg="";
		}
		return $Msg;
	}

	function countViewPost($POID)
	{
		$query=$this->db->query('Select ID from LogViewPost Where POID="'.$POID.'"');
		return $query->num_rows();
	}
	
	function countTelPost($POID)
	{
		$query=$this->db->query('Select ID from LogViewPost Where POID="'.$POID.'" and ViewTelByUserID is not null');
		return $query->num_rows();
	}

	function DelPost($Token)
	{
		$user_id=$this->session->userdata('user_id');
		$query=$this->db->query('Update Post set Active="91" Where user_id="'.$user_id.'" and Token="'.$Token.'"');
	}
	
	function HiddenPost($Token)
	{
		$user_id=$this->session->userdata('user_id');
		$query=$this->db->query('Update Post set Active="93" Where user_id="'.$user_id.'" and Token="'.$Token.'"');
	}

	function UnHiddenPost($Token)
	{
		$user_id=$this->session->userdata('user_id');
		$query=$this->db->query('Update Post set Active="1" Where user_id="'.$user_id.'" and Token="'.$Token.'"');
	}

	function EndPost($Token)
	{
		$user_id=$this->session->userdata('user_id');
		$query=$this->db->query('Update Post set Active="99" Where user_id="'.$user_id.'" and Token="'.$Token.'"');
	}
}
?>