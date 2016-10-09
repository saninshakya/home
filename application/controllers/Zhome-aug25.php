<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Zhome extends CI_Controller {

    function __construct(){
	    parent::__construct();
	    $this->load->helper('url');
		$this->load->helper('form');
		$this->load->helper('file');
		$this->load->helper('directory');
		$this->load->helper('array');
		$this->load->helper('common_function');

		$this->load->library('tank_auth');
	    $this->load->library('session');
		$this->load->library('user_agent');
		$this->load->library('googlemaps');

	    $this->load->model('tank_auth/users');
		$this->load->model('usermenu');
		$this->load->model('post');
		$this->load->model('dashboard');
		$this->load->model('search');
		$this->load->model('backend');
		//Add 24-09-58
		$this->load->model('unitdetail');
		//Add 03-01-59
		$this->load->library('My_PHPMailer');
		//Add 11-01-59
		$this->load->model('postemail');
		$this->load->model('testlog');
		//Add 07-06-59
		$this->load->model('developer');
    //Add 02-08-59
    $this->load->model('credit');
	}

  public function testcredit(){
      $txt=$this->credit->CreditBanlance();
      echo $txt;
  }

  public function testaddcredit(){
    $this->credit->AddCredit(101);
    $this->testcredit();
  }

  public function testpayment(){
    $this->load->view('payment2');
  }
	public function index(){
		$this->session->set_userdata('last_page','/');
		$this->session->set_userdata('token'.'');
		$this->lang_check();
		$data['BTS']=$this->backend->listKeyMarker2('BTS');
		$data['MRT']=$this->backend->listKeyMarker2('MRT');
		$data['ARL']=$this->backend->listKeyMarker2('ARL');
		$data['Unit']=$this->backend->homeCountUnit(1);
		$data['Sale']=$this->backend->homeCountUnit(82);
		$data['Rent']=$this->backend->homeCountUnit(81);
		$data['Project']=$this->backend->projectCount();
		$ChkLogin=$this->ChkLogin();
		if($ChkLogin==1){//check Messenger Id from Facebook Login
			$MsgID=$this->session->userdata('MsgID');
			if($MsgID!='' or $MsgID!='null' or $MsgID!=null){
				$this->backend->checkFacebookMsgID($MsgID);
			}
		}
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		$POID=$this->session->userdata('POID');
		$this->load->view($header,$profile);
		$this->load->view('home',$data);
		$this->load->view('footerstandard');
		$this->load->view('footer_home');
		//click กลับหน้า Home ไม่ได้
		/*if($POID=='null' || $POID==''){
			$this->load->view($header,$profile);
			$this->load->view('home',$data);
			$this->load->view('footerstandard');
			$this->load->view('footer_home');
		}else{
			redirect('/zhome/unitdetail2/'.$POID);
			//$this->unitdetail2($POID);
		}*/
	}

	public function dashboard()
	{
		if(!$user_id){
			$user_id=$this->session->userdata('user_id');
		}
		$language=1;
		$this->session->set_userdata('token'.'');
		$this->post->DelTmpPost();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		//query data for display
		$user_id=$this->session->userdata('user_id');
		$data['ListPostType1']=$this->dashboard->ListPost($user_id);
		//$data['ListPostType1']=$this->dashboard->ListPostType1($user_id);
		//$data['ListPostType2']=$this->dashboard->ListPostType1($user_id);
		//$data['ListPostType5']=$this->dashboard->ListPostType1($user_id);
		$data['ListUnPost']=$this->dashboard->ListUnPost($user_id);
		//query data from old dashboard
		$data['unlist']=$this->dashboard->check_unlist();
		$data['clist']=$this->dashboard->check_list();
		$data['qProblem']=$this->post->qProblem();
		$data['qLineAlert']=$this->dashboard->qLineAlert();
		$this->load->view($header,$profile);
		if ($ChkLogin==1){
			$type=$this->uri->segment(3);
			if(!isset($type)){
				$type='owner';
			}
			if (strlen($type)>20){
				$type='owner';
			}
			$data['type']=$type;
			$this->load->view('dashboard',$data);
			if($type=='owner'){
				//Add this line for dashboard developer 07/06/59
				$data['qLabel']=$this->search->qLabel('owner',$language);
				if ($this->dashboard->CheckDeveloper($user_id)==1){
					$this->load->view('dashboard_developer',$data);
				} else {
					$this->load->view('dashboard_owner',$data);
				}
				//Comment for dashboard developer 07/06/59
				//$this->load->view('dashboard_owner',$data);
			}else if($type=='favourite'){
				$data['qFavUnit']=$this->dashboard->getUnitFromFavourite();
				$data['qLabel']=$this->search->qLabel('favourite',$language);
				$this->load->view('dashboard_favourite',$data);
			}else if($type=='lastview'){
				$data['qLastView']=$this->dashboard->getUnitFromLastView();
				$data['qLabel']=$this->search->qLabel('lastview',$language);
				$this->load->view('dashboard_lastview',$data);
			}else if($type=='profile'){
				$data['Profile']=$this->search->getProfile($user_id);
				$data['qLabel']=$this->search->qLabel('profile',$language);
				$this->load->view('dashboard_profile',$data);
				//Comment for dashboard developer 07/06/59
				//}
				//Add this line for dashboard developer 07/06/59
			}else if($type=='developer_addroom'){
				$data['PID']=$this->uri->segment(4);
				$this->load->view('developer_addroom',$data);
			}else if ($type=='edit_developer_room'){
				$data['POID']=$this->uri->segment(4);
				$this->load->view('edit_developer_room',$data);
			}
			$this->load->view('dashboard_footer',$data);
		}
		$this->load->view('footer');
	}

	public function dpost()
	{
		$Token=$this->uri->segment(3);
		$this->dashboard->DelPost($Token);
		$this->dashboard();
	}

	public function hpost()
	{
		$Token=$this->uri->segment(3);
		$this->dashboard->HiddenPost($Token);
		$this->dashboard();
	}

	public function unhpost()
	{
		$Token=$this->uri->segment(3);
		$this->dashboard->UnHiddenPost($Token);
		$this->dashboard();
	}

	public function epost()
	{
		$Token=$this->uri->segment(3);
		$this->dashboard->EndPost($Token);
		$this->dashboard();
	}

	public function AddDateExpire()
	{
		$Token=$this->uri->segment(3);
		$Day=$this->uri->segment(4);
		$this->dashboard->AddDateExpire($Token,$Day);
		$this->dashboard();
	}

	public function EditPost2()
	{
		$Token=$this->uri->segment(3);
		$this->session->set_userdata('token',$Token);
		//$this->post->deactive($Token);
		$this->post11();
		//echo $Token;
	}

	public function EditPost()
	{
			$this->session->set_userdata('token',$_POST['Token']);
			$Token=$_POST['Token'];
			//$this->post->deactive($Token);
			$query=$this->db->query('Select Step1, Step2, Step3, Step4, Step5 from Post Where Token="'.$Token.'"');
			$row=$query->row();
			if ($row->Step1==0){
				$this->post11();
			} else {
				if ($row->Step2==0){
					$this->post2();
				} else {
					if ($row->Step3==0){
						$this->post3();
					} else {
						if ($row->Step4==0){
							//$this->post4();
							redirect('/zhome/post4', 'location');
						} else {
							$this->post5();
						}
					}
				}
			}
	}

	function ChkLogin()
	{
	    if (!$this->tank_auth->is_logged_in()) {
			return 0;
	    } else {
			return 1;
		}
	}

	public function index_2()
	{
	    //if (!$this->tank_auth->is_logged_in()) {
		//redirect('/auth/login/');
	    //} else {
		//$data['user_id']	= $this->tank_auth->get_user_id();
		//$data['username']	= $this->tank_auth->get_username();
		$user_data = $this->users->get_user_by_id($this->tank_auth->get_user_id(),1);
		$data['firstname'] = $user_data->firstname;
		$data['lastname'] = $user_data->lastname;
		if( !$this->session->userdata('image') == '' ){
		    $data['image'] = $this->session->userdata('image');
		}else{
		    $data['image'] = '/images/blank_man.gif';
		}
		echo var_dump($user_data);
		echo var_dump($this->session->userdata());
		$this->load->view('welcome', $data);
		//echo $this->session->userdata('user_id');
	    //}
	}


	function header_inc($ChkLogin)
	{
		if ($ChkLogin==0) {
			$sess_id = $this->session->userdata('user_id');
			if(!empty($sess_id)){
				$this->session->sess_destroy();
			};
			$data="header_nonauth";
		} else {
			$data="header_auth";
		}
		return $data;
	}

	function header_searchmap_inc($ChkLogin)
	{
		if ($ChkLogin==0) {
			$sess_id = $this->session->userdata('user_id');
			if(!empty($sess_id)){
				$this->session->sess_destroy();
			}
			$data="header_searchmap_nonauth";
		} else {
			$data="header_searchmap_auth";
		}
		return $data;
	}

	function user_profile($ChkLogin,$title="",$description="",$keyword="",$imageOG="",$specMeta="")
	{
		$data['facebook_id']=$this->search->getFacebookID();
		if($title==''){
			//$data["title"]="ZmyHome | Buy and rent from owners without commission.";
			$data["title"]="ZmyHome | คอนโดเจ้าของขายเอง ซื้อง่าย ขายคล่อง เชื่อถือได้";
		}else{
			$data["title"]=$title." | ZmyHome";
		}
		if($description==''){
			$data["description"]="We provides a large stock of property for buyer that can buy and rent from owners without commission";
			$data["description"]="ซื้อง่าย ขายคล่อง เชื่อถือได้";
		}else{
			$data["description"]=$description;
		}
		if($keyword==''){
			$data["keyword"]="Buy,Sell,Condo,House,Estate,Real Estate";
		}else{
			$data["keyword"]=$keyword;
		}
		if($imageOG==''){
			$data["imageOG"]="http://www.zmyhome.com/img/how-it-works-symbols-v3-10.png";
		}else{
			$data["imageOG"]=$imageOG;
		}
		if($specMeta==''){
			$data["specMeta"]="";
		}else{
			$data["specMeta"]=$specMeta;
		}
		if ($ChkLogin==0) {
			$data["Name"]="No_Profile";
			$sess_id = $this->session->userdata('user_id');
			if(!empty($sess_id)){
				$this->session->sess_destroy();
			}
		} else {
			$user_data = $this->users->get_user_by_id($this->tank_auth->get_user_id(),1);
			$data['firstname'] = $user_data->firstname;
			$data['lastname'] = $user_data->lastname;
			if (strpos($user_data->email,'|') !== false) {
				$new_email = explode('|',$user_data->email,2);
				$data['email'] = $new_email[1];
			}else{
				$data['email'] = $user_data->email;
			}
			if( !$this->session->userdata('image') == '' ){
				$data['image'] = $this->session->userdata('image');
			}else{
				$data['image'] = '/images/blank_man.gif';
			}
			$data['lang'] = $this->session->userdata('lang');
			$user_id=$this->session->userdata('user_id');
			$query=$this->db->query('select count(PID) as CNumber from Post Where user_id="'.$user_id.'" and (Active=0 or Active=1 or Active=3 or Active=81 or Active=82 or Active=92 or Active=93)');
			$row=$query->row();
			$data['CNumber']=$row->CNumber;
		}
		return $data;
	}


	function lang_check()
	{
		if (!$this->session->userdata('lang')){
			$this->session->set_userdata('lang','th');
		};
	}

	public function change_lang()
	{
		if ($this->session->userdata('lang')=="th"){
			$this->session->set_userdata('lang','en');
		} else {
			$this->session->set_userdata('lang','th');
		}
		redirect($this->session->userdata('lastpage'));
	}

	function postMapChange()
	{
		$token=$this->session->userdata('token');
		$query=$this->db->query('Select Lat, Lng from Post Where Token="'.$token.'"');
		$row=$query->row();
		$Lat=$row->Lat;
		$Lng=$row->Lng;

		if ($Lat==0){
		//	if ($this->agent->is_browser('Safari'))
		//	{
				$config['geocodeCaching'] = false;
				$config['zoom'] = '16';
				$config['center'] = '13.764934,100.5382955';
				$config['map_height']= '300px';
				$this->googlemaps->initialize($config);
				$marker = array();
				$marker['position'] = '13.764934,100.5382955';
				$marker['draggable'] = true;
				$marker['ondragend'] = 'updateDatabase(event.latLng.lat(), event.latLng.lng());';
				$this->googlemaps->add_marker($marker);
				//echo 'You are using Safari.';
		//	} else {
		//		$config = array();
		//		$config['center'] = 'auto';
		//		$config['onboundschanged'] = 'if (!centreGot) {
		//			var mapCentre = map.getCenter();
		//			marker_0.setOptions({
		//			position: new google.maps.LatLng(mapCentre.lat(), mapCentre.lng())
		//			});
		//			}
		//			centreGot = true;';
		//		$config['geocodeCaching'] = TRUE;
		//		$config['zoom'] = '16';
		//		$config['map_height']= '300px';
		//		$this->googlemaps->initialize($config);
		//		$marker = array();
		//		$marker['draggable'] = true;
		//		$marker['ondragend'] = 'updateDatabase(event.latLng.lat(), event.latLng.lng());';
		//		$this->googlemaps->add_marker($marker);
		//	};
		} else {
			$config['geocodeCaching'] = false;
			$config['zoom'] = '16';
			$config['center'] = $Lat.','.$Lng;
			$config['map_height']= '300px';
			$this->googlemaps->initialize($config);
			$marker = array();
			$marker['position'] = $Lat.','.$Lng;
			$marker['draggable'] = true;
			$marker['ondragend'] = 'updateDatabase(event.latLng.lat(), event.latLng.lng());';
			$this->googlemaps->add_marker($marker);
		}
		$return=$this->googlemaps->create_map();
		return $return;
	}

	function txtMapChange()
	{
		$token=$this->session->userdata('token');
		$query=$this->db->query('Select Lat, Lng from Post Where Token="'.$token.'"');
		$row=$query->row();
		$Lat=$row->Lat;
		$Lng=$row->Lng;
		if ($Lat!=0){
			$txt="disabled";
		} else {
			$txt="ไม่มีข้อมูลในฐานข้อมูลกรุณาปักหมุดเอง";
		}
		return $txt;
	}

	public function post1()
	{
		$language=1;
		$KeyNewPost = $this->uri->segment(3);
		$this->post->DelTmpPost();
		if ($KeyNewPost=="newpost"){
			$this->post->newPost2();
		} else {
			$this->post->newPost();
		};
		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);

		$map = $this->postMapChange();
		$data['map'] = $map;
		$profile['map'] = $map;
		$profile['HSNumber']=3;
		$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
		$S['2']='<script type="text/javascript">var centreGot = false;</script>';
		$profile['HSrc']=$S;

		$data['qProjectName']=$this->post->qProject();
		$data['qTOOwner']=$this->post->qTOOwner();
		$data['qTOProperty']=$this->post->qTOProperty();
		$data['qTOAdvertising']=$this->post->qTOAdvertising();
		$data['qPost']=$this->post->qPost();
		$data['qLabel']=$this->search->qLabel('post1',$language);
		//$data['txtMapChange']=$this->txtMapChange();
		$this->load->view($header,$profile);
		$this->load->view('inputform1',$data);
		$this->load->view('footer_post1',$data);

	}

	public function post11()
	{
		//if (!$this->session->userdata('token')||$this->session->userdata('token')==null){
		//	$this->post->newPost();
		//};
		$language=1;
		$this->post->newPost();
		$token=$this->session->userdata('token');

		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);


		$map = $this->postMapChange();
		$data['map'] = $map;
		$profile['map'] = $map;
		$profile['HSNumber']=3;
		$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
		$S['2']='<script type="text/javascript">var centreGot = false;</script>';
		$profile['HSrc']=$S;

		$data['qProjectName']=$this->post->qProject();
		$data['qTOOwner']=$this->post->qTOOwner();
		$data['qTOProperty']=$this->post->qTOProperty();
		$data['qTOAdvertising']=$this->post->qTOAdvertising();
		$data['qPost']=$this->post->qPost();
		$data['txtMapChange']=$this->txtMapChange();
		$data['qLabel']=$this->search->qLabel('post1',$language);
		$this->load->view($header,$profile);
		$this->load->view('inputform1',$data);
		$this->load->view('footer_post1',$data);

	}

	public function update_coords()
	{
		$this->db->query('SET NAMES utf8');
        $this->db->query('SET character_set_results=utf8');
		$lat=$_POST['newLat'];
		$lng=$_POST['newLng'];
		$token=$this->session->userdata('token');
        $query=$this->db->query('Update Post set Lat="'.$lat.'", Lng="'.$lng.'" Where Token="'.$token.'"');
	}

	public function update_TOOwner()
    {
		$this->post->update_TOOwner($_POST);
	}

	public function update_TOProperty()
	{
		$this->post->update_TOProperty($_POST);
	}

	public function update_TOAdvertising()
	{
		$this->post->update_TOAdvertising($_POST);
	}

	public function update_OwnerName()
	{
		$this->post->update_OwnerName($_POST);
	}

	public function update_ProjectName()
	{
		$this->post->update_ProjectName($_POST);
	}

	public function changePage1(){
		if ($this->post->checkPost('Agree_Owner',$_POST['unit_token'])==0){
			echo "<script>alert('คุณไม่ใช้คนที่มีอำนาจในการตัดสินใจ ไม่สามารถดำเนินการต่อได้');window.location.href='/zhome/post11';</script>";
		} else {
			$key_change=$this->uri->segment(3);
			if($key_change==''){
				$key_change=$_POST['key_change'];
			}
			//Update Post data into database
			if (!$_POST['last_page']){
				$last_page=$this->session->userdata('last_page');
			} else {
				$last_page=$_POST['last_page'];
			}
			if ($last_page==1){
				$this->post->updatePost1($_POST);
			}
			if ($last_page==2){
				$this->post->updatePost2($_POST);
			}
			if ($last_page==31){
				$this->post->updatePost31($_POST);
			}
			if ($last_page==32){
				$this->post->updatePost32($_POST);
			}
			if ($last_page==33){
				$this->post->updatePost33($_POST);
			}
			if ($last_page==34){
				$this->post->updatePost34($_POST);
			}
			if ($last_page==35){
				$this->post->updatePost35($_POST);
			}
			if ($last_page==4){
				$this->post->updatePost4();
			}
			if ($last_page==5){
				$this->post->updatePost5();
				if ($key_change==6){
					$this->dashboard();
				}
			}

			//Redirect into other page.
			if ($key_change==0){
				$this->post11();
			}
			if ($key_change==1){
				$this->post11();
			}
			if ($key_change==2){
				$this->post2();
			}
			if ($key_change==3){
				$this->post3($_POST['user_token']);
			}
			if ($key_change==4){
				//$this->post4();
				redirect('/zhome/post4', 'location');
			}
			if ($key_change==5){
				$this->post5();
			}
		}
	}

	public function changePage2()
	{
		$postPage = $this->uri->segment(3);
		//echo $postPage;
		if ($postPage==1){
			$this->post11();
		}
		if ($postPage==2){
			$this->post2();
		}
		if ($postPage==3){
			$this->post3();
		}
		if ($postPage==4){
			//$this->post4();
			redirect('/zhome/post4', 'location');
		}
		if ($postPage==5){
			$this->post5();
		}
	}

	public function LatLngProject()
	{
		$this->post->LatLngProject($_GET);
	}

	public function post2()
	{
		//if (!$this->session->userdata('token')){
		//	$this->post->newPost();
		//};
		$language=1;
		$this->post->newPost();
		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		$profile['HSNumber']=2;
		$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
		$profile['HSrc']=$S;
		$data['qProjectName']=$this->post->qProject();
		$data['TOCondoSpec1']=$this->post->TOCondoSpec(1);
		$data['TOCondoSpec2']=$this->post->TOCondoSpec(2);
		$data['TOCondoSpec3']=$this->post->TOCondoSpec(3);
		$data['qLabel']=$this->search->qLabel('post2',$language);
		$this->load->view($header,$profile);
		$this->load->view('inputform2',$data);
		$this->load->view('footer_post2',$data);
	}

	public function updateCondoSpec()
	{
		$this->post->updateCondoSpec($_POST);
	}

	public function updatePost()
	{
		$this->post->updatePost($_POST);
	}

	public function updateFavorite()
	{
		$ChkLogin=$this->ChkLogin();
		if($ChkLogin==1){
			$this->unitdetail->add_favorite($_POST);
		}
	}

	public function updatePostDCondo()
	{
		$this->post->updatePostDCondo($_POST);
	}

	public function post3($unit_token='')
	{
		//if (!$this->session->userdata('token')){
		//	$this->post->newPost();
		//};
		$language=1;
		$this->post->newPost();
		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		$profile['HSNumber']=2;
		$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
		$profile['HSrc']=$S;
		$data['qProjectName']=$this->post->qProject();
		$data['TOCondoSpec1']=$this->post->TOCondoSpec(1);
		$data['TOCondoSpec2']=$this->post->TOCondoSpec(2);
		$data['TOCondoSpec3']=$this->post->TOCondoSpec(3);
		$data['qLabel']=$this->search->qLabel('post3',$language);
		$this->load->view($header,$profile);
		$checkAdvertising=$this->post->checkAdvertising();
		if ($this->post->checkPost('useArea',$unit_token)==0){
			echo "<script>alert('ต้องใส่พื้นที่ของสินทรัพย์ก่อน');window.location.href='/zhome/changePage2/2';</script>";
		} else {
			if ($checkAdvertising==1){
				$this->load->view('inputform31',$data);
				$this->load->view('footer_post31',$data);
			};
			if ($checkAdvertising==3){
				$this->load->view('inputform33',$data);
				$this->load->view('footer_post33',$data);
			};
			if ($checkAdvertising==4){
				$this->load->view('inputform34',$data);
				$this->load->view('footer_post34',$data);
			};
			if ($checkAdvertising==5){
				$this->load->view('inputform35',$data);
				$this->load->view('footerstandard');
				$this->load->view('footer_post35',$data);
			};
			if ($checkAdvertising==2){
				$this->load->view('inputform32',$data);
				$this->load->view('footer_post32',$data);
			};
		};

	}

	public function post5()
	{
		$language=1;
		$this->post->newPost();
		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		$profile['HSNumber']=2;
		$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
		$profile['HSrc']=$S;
		$data['qProjectName']=$this->post->qProject();
		$data['TOCondoSpec1']=$this->post->TOCondoSpec(1);
		$data['TOCondoSpec2']=$this->post->TOCondoSpec(2);
		$data['TOCondoSpec3']=$this->post->TOCondoSpec(3);
		$data['qLabel']=$this->search->qLabel('post5',$language);
		$this->load->view($header,$profile);
		$this->load->view('inputform5',$data);
		$this->load->view('footer_post5',$data);
	}

/// add by tas on 17 Aug 15 ///
	public function post4(){
		$language=1;
		$this->post->newPost();
		$userid=$this->session->userdata('user_id');
		$this->db->query('delete from checkUpload Where user_id="'.$userid.'"');
		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		$profile['HSNumber']=2;
		$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
		$profile['HSrc']=$S;
		$data['qProjectName']=$this->post->qProject();
		$data['TOCondoSpec1']=$this->post->TOCondoSpec(1);
		$data['TOCondoSpec2']=$this->post->TOCondoSpec(2);
		$data['TOCondoSpec3']=$this->post->TOCondoSpec(3);
		$data['POID'] =  $this->post->getPOID();
		$this->post->ClearImgFail($data['POID']);
		$data['imgRoom'] = $this->post->qImg('room',$data['POID']);
		$data['imgView'] = $this->post->qImg('view',$data['POID']);
		$data['imgFac'] = $this->post->qImg('facilities',$data['POID']);

		//$data['imgShare'] = $this->post->qImg_share($data['POID']);
		$data['PID']= $this->post->qPID($data['POID']);
		$data['PName'] = $this->post->qProjectName($data['POID']);

		$data['imgProjShare'] = $this->findDirectory($data['PName']);
		$profile['ipage']= 4;
		$data['qLabel']=$this->search->qLabel('post4',$language);
		$this->load->view($header,$profile);
		$this->load->view('inputform4',$data);
		$this->load->view('footer_post4',$data);

		//$this->POST
	}

	public function submit_img(){
			//$this->load->helper('file');


			$imgData = $this->input->post('img');
			$userid = $this->input->post('userid');
			$postid = $this->input->post('postid');
			$type = $this->input->post('type');

			 define('IMG_DIR', 'userImg/'.$userid.'/');
             if(!is_dir(IMG_DIR)) //create the folder if it's not already exists
             {
               mkdir(IMG_DIR,0755,TRUE);
             }

			 	$file = IMG_DIR . uniqid() . $postid.$userid.".png";

             	$imgData =$this->input->post('img');
				$sfile = "/".$file;
				//$presult = $this->post->insertImgPost($_POST,$sfile);


             	$imgD = base64_decode(stripslashes(substr($imgData,22)));


				if (!getimagesize($imgData)) {
       				 echo "Uploaded file is not a valid image or File size exceed";
      			}else{
      				$presult = $this->post->insertImgPost($_POST,$sfile);
					if($presult){
             			$success = write_file($file, $imgD);
					}else{
						$rdelImg = $this->post->delImgShare($file,$postid);
					}
				}
			 //}


              echo json_encode($success ? $presult : 'Unable to save the file.');
              //echo json_encode($success ? $file : 'Unable to save the file.');
              //echo json_encode(print_r($presult));



	}
	public function submit_imgshare(){
			$postid = $this->input->post('postid');
			$src = $this->input->post('src');

			$rImgShare = $this->post->insertImgShare($src,$postid);

			echo json_encode($cresult?'Save':'Fail to Save');

	}
	public function delete_imgshare(){
		$src = $this->input->post('src');
		$postid = $this->input->post('postid');
		$rdelImg = $this->post->delImgShare($src,$postid);

		echo json_encode($rdelImg?'Save':'Fail to Save');
	}

	public function update_imgCap(){
		$desc = $this->input->post('cap');
		$id = $this->input->post('id');
		$cresult = $this->post->update_description($desc,$id);

		echo json_encode($cresult?'Save':'Fail to Save');
	}
	public function update_imgAllow(){
		$check = $this->input->post('allow');
		$tid = $this->input->post('id');

		if($check=="true"){
			$check = TRUE;
		}else{
			 $check=FALSE;
		}

		$aresult = $this->post->update_allow($check,$tid);
		echo json_encode($check?'Allow other people to use this image':'Do not allow anyone to use this image');
	}/**/
	public function remove_img(){
		$imgid = $this->input->post('id');
		$del = $this->post->delImg($imgid);

		echo json_encode($del?'remove this image':'cannot remove this image');
	}
	private function findDirectory($pname){
		//echo "find directory";
		//echo "panme : ".$pname;
		$map = directory_map('projImg/'.$pname.'/Picture');
		//echo "map : ".$map;
		if ($map!=false){
			$total = sizeof($map);
			$file = array();
			for($i=0;$i<$total;$i++){
				$file[$i] = 'projImg/'.$pname.'/Picture/'.$map[$i];
			}
		} else {
			$query=$this->db->query('Select PID from Project Where PName_en="'.$pname.'"');
			$row=$query->row();
			$PID=$row->PID;
			$map = directory_map('projImg/'.$PID.'/Picture');
			if ($map!=false){
				$total = sizeof($map);
				$file = array();
				for($i=0;$i<$total;$i++){
					$file[$i] = 'projImg/'.$PID.'/Picture/'.$map[$i];
				}
			} else {
				$file[0]='projImg/no-icon.gif';
			};
		}
		return $file;
	}

	public function getFirstPoint(){
		$namePoint=$_GET['namePoint'];
		$TOAdvertising=$_GET['TOAdvertising'];

		//$this->session->set_userdata('np',$namePoint);

		$this->search->getFirstPoint($namePoint,$TOAdvertising);

	}
	//// add by tas 31 Aug /////
	public function searchResult(){
		$language=1;
		$this->session->set_userdata('last_page','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_searchmap_inc($ChkLogin);
		$checkAdmin=$this->backend->checkAdmin();
		$data['checkAdmin']=$checkAdmin;
		$data['qProjectName']=$this->post->qProject();
		$data['qTOOwner']=$this->post->qTOOwner();
		$data['qTOProperty']=$this->post->qTOProperty();
		$data['qTOAdvertising']=$this->post->qTOAdvertising();
		$data['qPost']=$this->post->qPost();
		$data['distance_length']=3000;
		$data['minPrice']=0;
		$data['maxPrice']=0;
		$data['minArea']=0;
		$data['maxArea']=0;
		$data['order']=5;
		$data['TransType']="1,0,";
		$data['qLabel']=$this->search->qLabel('searchmap',$language);
		$title="คอนโด ที่พัก ใกล้ รถไฟฟ้า ";
		$description="";
		$keyword="ขาย,ขายดาวน์,คอนโด,ใกล้,รถไฟฟ้า,เจ้าของขายเอง,มือหนึ่ง,มือสอง,ราคาถูก";
		if ($_POST){
			$data['namePoint'] = $_POST['namePoint'];
			if ($data['namePoint']==""){
				$data['namePoint']="BTS อนุสาวรีย์ชัย";
			}
			$data['namePointSearch']=$data['namePoint'];
			$data['TOAdvertising'] = $_POST['TOAdvertising'];
			if ($data['TOAdvertising']==""){
				$data['TOAdvertising']='1,2,8,';
			}
			$data['TOProperty']='1,';
			$data['TOAdvertising_url']=str_replace(",","-",$data['TOAdvertising']);
			$data['TOProperty_url']=str_replace(",","-",$data['TOProperty']);
			$data['namePoint_url']=str_replace(" ","_",$data['namePoint']);
			$data['namePoint_url']=urlencode(str_replace("/","-sl-",$data['namePoint_url']));
		} else {
			$StationID=$this->uri->segment(3);
			$query_name=$this->backend->getKeyMarker($StationID);
			$name=$query_name->row()->KeyName;
			$nameAnchor=$this->uri->segment(4);
			$minPrice=$this->uri->segment(5);
			$maxPrice=$this->uri->segment(6);
			$Distance=$this->uri->segment(7);
			//$RefPlace=urldecode($this->uri->segment(8));
			$TOAdvertising=str_replace("-",",",$this->uri->segment(8));
			$TOProperty=str_replace("-",",",$this->uri->segment(9));
			$Bedroom=str_replace("-",",",$this->uri->segment(10));
			$Bathroom=str_replace("-",",",$this->uri->segment(11));
			$TransDist=$this->uri->segment(12);
			$TransType=str_replace("-",",",$this->uri->segment(13));
			$PYear=$this->uri->segment(14);
			$minArea=$this->uri->segment(15);
			$maxArea=$this->uri->segment(16);
			$Order=$this->uri->segment(17);
			if($TOProperty==''){$TOProperty='1,';}
			if($Bedroom==0){$Bedroom='';}
			if($Bathroom==0){$Bathroom='';}
			if($TransDist==0){$TransDist='';}
			if($PYear==0){$PYear='';}
			if($name!=''){
				$namePoint = urldecode($name);
				$data['namePointSearch']=$name;
			}else{
				$data['namePointSearch']=$namePoint;
				$namePoint=str_replace("_"," ",urldecode($nameAnchor));
				$namePoint=str_replace("-and-","&",urldecode($namePoint));
				$namePoint=str_replace("-sl-","/",urldecode($namePoint));
			}
			if($TOAdvertising==''){$TOAdvertising='1,2,';}
			$data['namePoint']=$namePoint;
			$data['TOAdvertising'] = $TOAdvertising;
		}
		$checkProject=$this->search->getProject('',$data['namePointSearch'],'PID');
		if($checkProject!=0){
			$data['distance_length']=500;
		}
		if($data['TOAdvertising']==5){
			$title="ให้เช่า ".$title;
		}else{
			$title="ขาย ขายดาวน์ ".$title;
		}
		$title.=$data['namePoint'];
		if($minPrice!='' or $maxPrice!=''){
			$data['minPrice'] = $minPrice;
			$data['maxPrice'] = $maxPrice;
			if($minPrice!=0 and $maxPrice!=0){
				$title="คอนโดราคา ";
				if($minPrice!=''){
					if($minPrice>0 and ($minPrice/1000000 >= 1)){
						$minPriceShow=($minPrice/1000000)."ล้าน";
					}else{
						$minPriceShow=$minPrice;
					}
					$title.=$minPriceShow;
				}
				if($maxPrice!=''){
					if($maxPrice>0 and ($maxPrice/1000000 >= 1)){
						$maxPriceShow=($maxPrice/1000000)."ล้าน";
					}else{
						$maxPriceShow=$maxPrice;
					}
					$title.="-".$maxPriceShow;
				}
			}
			//$title.=" ในกรุงเทพ";
			$data['distance_length']=20000;
			$data['order']=1;
		}
		if($Distance<>''){$data['distance_length']=$Distance;}
		if($TOProperty<>''){$data['TOProperty']=$TOProperty;}
		if($TOAdvertising<>''){$data['TOAdvertising']=$TOAdvertising;}
		if($Bedroom<>''){$data['Bedroom']=$Bedroom;}
		if($Bathroom<>''){$data['Bathroom']=$Bathroom;}
		if($TransDist<>''){$data['TransDist']=$TransDist;}
		if($TransType<>''){$data['TransType']=$TransType;}
		if($PYear<>''){$data['PYear']=$PYear;}
		if($minArea!='' or $maxArea!=''){
			$data['minArea']=$minArea;
			$data['maxArea']=$maxArea;
		}
		if($Order<>''){$data['order']=$Order;}
		$title.=" เจ้าของขายเอง";
		$description="ขาย ขายดาวน์ คอนโด ที่พัก ใกล้รถไฟฟ้า ".$data['namePoint']." รัศมี ".number_format(($data['distance_length']/1000),1)." กม. เจ้าของขายเอง ทั้งมือหนึ่งและมือสอง ราคาถูก";
		$keyword.=",".$data['namePoint'];
		$imageOG="http://www.zmyhome.com/img/zcity.jpg";
		$profile=$this->user_profile($ChkLogin,$title,$description,$keyword,$imageOG);
		//$header="header_searchmap";//hearder for mobile
		if($_POST){
			redirect('/Zhome/searchResult/0/'.$data['namePoint_url'].'/'.$data['minPrice'].'/'.$data['maxPrice'].'/'.$data['distance_length'].'/'.$data['TOAdvertising_url'].'/'.$data['TOProperty_url'], 'refresh');
		}
		$this->load->view($header,$profile);
		$this->load->view('searchMap',$data);
		//$this->load->view('footerstandard');
	}

	public function searchResult2(){
		$language=1;
		$this->session->set_userdata('last_page','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_searchmap_inc($ChkLogin);
		$checkAdmin=$this->backend->checkAdmin();
		$data['checkAdmin']=$checkAdmin;
		$data['qProjectName']=$this->post->qProject();
		$data['qTOOwner']=$this->post->qTOOwner();
		$data['qTOProperty']=$this->post->qTOProperty();
		$data['qTOAdvertising']=$this->post->qTOAdvertising();
		$data['qPost']=$this->post->qPost();
		$data['distance_length']=3000;
		$data['minPrice']="";
		$data['maxPrice']="";
		$data['order']=5;
		$data['TransType']="1,0,";
		$data['qLabel']=$this->search->qLabel('searchmap',$language);
		$title="คอนโด ที่พัก ใกล้ รถไฟฟ้า ";
		$description="";
		$keyword="ขาย,ขายดาวน์,คอนโด,ใกล้,รถไฟฟ้า,เจ้าของขายเอง,มือหนึ่ง,มือสอง,ราคาถูก";
		if ($_POST){
			$data['namePoint'] = $_POST['namePoint'];
			if ($data['namePoint']==""){
				$data['namePoint']="BTS อนุสาวรีย์ชัย";
			}
			$data['TOAdvertising'] = $_POST['TOAdvertising'];
			if ($data['TOAdvertising']==""){
				$data['TOAdvertising']='1,2,8,';
			}
			$data['TOProperty']='1,';
		} else {
			$StationID=$this->uri->segment(3);
			$query_name=$this->backend->getKeyMarker($StationID);
			$name=$query_name->row()->KeyName;
			$nameAnchor=$this->uri->segment(4);
			$minPrice=$this->uri->segment(5);
			$maxPrice=$this->uri->segment(6);
			$Distance=$this->uri->segment(7);
			//$RefPlace=urldecode($this->uri->segment(8));
			$TOAdvertising=str_replace("-",",",$this->uri->segment(8));
			$TOProperty=str_replace("-",",",$this->uri->segment(9));
			$Bedroom=str_replace("-",",",$this->uri->segment(10));
			$Bathroom=str_replace("-",",",$this->uri->segment(11));
			$TransDist=$this->uri->segment(12);
			$TransType=str_replace("-",",",$this->uri->segment(13));
			$PYear=$this->uri->segment(14);
			$Order=$this->uri->segment(15);
			if($TOProperty==''){$TOProperty='1,';}
			if($Bedroom==0){$Bedroom='';}
			if($Bathroom==0){$Bathroom='';}
			if($TransDist==0){$TransDist='';}
			if($PYear==0){$PYear='';}
			if($name!=''){
				$namePoint = urldecode($name);
			}else{
				$namePoint=str_replace("-"," ",urldecode($nameAnchor));
			}
			if($TOAdvertising==''){$TOAdvertising='1,2,';}
			$data['namePoint']=$namePoint;
			$data['TOAdvertising'] = $TOAdvertising;
		}
		$checkProject=$this->search->getProject('',$data['namePoint'],'PID');
		if($checkProject!=0){
			$data['distance_length']=500;
		}
		if($data['TOAdvertising']==5){
			$title="ให้เช่า ".$title;
		}else{
			$title="ขาย ขายดาวน์ ".$title;
		}
		$title.=$data['namePoint'];
		if($minPrice!='' or $maxPrice!=''){
			$data['minPrice'] = $minPrice;
			$data['maxPrice'] = $maxPrice;
			if($minPrice!=0 and $maxPrice!=0){
				$title="คอนโดราคา ";
				if($minPrice!=''){
					if($minPrice>0 and ($minPrice/1000000 >= 1)){
						$minPriceShow=($minPrice/1000000)."ล้าน";
					}else{
						$minPriceShow=$minPrice;
					}
					$title.=$minPriceShow;
				}
				if($maxPrice!=''){
					if($maxPrice>0 and ($maxPrice/1000000 >= 1)){
						$maxPriceShow=($maxPrice/1000000)."ล้าน";
					}else{
						$maxPriceShow=$maxPrice;
					}
					$title.="-".$maxPriceShow;
				}
			}
			//$title.=" ในกรุงเทพ";
			$data['distance_length']=20000;
			$data['order']=1;
		}
		if($Distance<>''){$data['distance_length']=$Distance;}
		if($TOProperty<>''){$data['TOProperty']=$TOProperty;}
		if($TOAdvertising<>''){$data['TOAdvertising']=$TOAdvertising;}
		if($Bedroom<>''){$data['Bedroom']=$Bedroom;}
		if($Bathroom<>''){$data['Bathroom']=$Bathroom;}
		if($TransDist<>''){$data['TransDist']=$TransDist;}
		if($TransType<>''){$data['TransType']=$TransType;}
		if($PYear<>''){$data['PYear']=$PYear;}
		if($Order<>''){$data['order']=$Order;}
		$title.=" เจ้าของขายเอง";
		$description="ขาย ขายดาวน์ คอนโด ที่พัก ใกล้รถไฟฟ้า ".$data['namePoint']." รัศมี ".number_format(($data['distance_length']/1000),1)." กม. เจ้าของขายเอง ทั้งมือหนึ่งและมือสอง ราคาถูก";
		$keyword.=",".$data['namePoint'];
		$imageOG="http://www.zmyhome.com/img/zcity.jpg";
		$profile=$this->user_profile($ChkLogin,$title,$description,$keyword,$imageOG);
		//$header="header_searchmap";//hearder for mobile
		$this->load->view($header,$profile);
		$this->load->view('searchMap2',$data);
		//$this->load->view('footerstandard');
	}

	public function searchResult_old()
	{
		$this->session->set_userdata('last_page','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$checkAdmin=$this->backend->checkAdmin();
		$data['checkAdmin']=$checkAdmin;
		$data['qProjectName']=$this->post->qProject();
		$data['qTOOwner']=$this->post->qTOOwner();
		$data['qTOProperty']=$this->post->qTOProperty();
		$data['qTOAdvertising']=$this->post->qTOAdvertising();
		$data['qPost']=$this->post->qPost();
		if ($_POST){
			$title=$_POST['namePoint'];
			$data['namePoint'] = $_POST['namePoint'];
			if ($data['namePoint']==""){
				$data['namePoint']="BTS อนุสาวรีย์ชัย";
			}
			$data['TOAdvertising'] = $_POST['TOAdvertising'];
			if ($data['TOAdvertising']==""){
				$data['TOAdvertising']=1;
			}
		} else {
			if($name!=''){
				$namePoint = urldecode($name);
				$title=$namePoint;
				$data['namePoint']=$namePoint;
			}else{
				$data['namePoint'] = "";
			}
			$data['TOAdvertising'] = 1;
		}
		$data['distance_length']=3000;
		$checkProject=$this->search->getProject('',$data['namePoint'],'PID');
		if($checkProject){
			$data['distance_length']=500;
		}
		$profile=$this->user_profile($ChkLogin,$title);
		$this->load->view($header,$profile);
		$this->load->view('searchMap_old',$data);
	}

	//getPoint
	public function getUpdateMarker(){

		$namePoint=$_GET['namePoint'];
		if ($namePoint==""){
			$namePoint="BTS อนุสาวรีย์ชัย";
		}
		$veiwmode = $_GET['viewmode'];
		$distance = $_GET['distance'];
		$TOProperty = $_GET['TOProperty'];
		$Bedroom = $_GET['Bedroom'];
		$Bathroom = $_GET['Bathroom'];
		$TransDist = $_GET['TransDist'];
		$TransType = $_GET['TransType'];
		$Year = $_GET['Year'];
		$TOAdvertising=$_GET['TOAdvertising'];
		$minPrice = $_GET['minPrice'];
		$maxPrice = $_GET['maxPrice'];
		$minArea = $_GET['minArea'];
		$maxArea = $_GET['maxArea'];
		$OrderType = $_GET['Order'];

		$txt='getPoint('.$veiwmode.','.$distance.','.$namePoint.','.$TOProperty.','.$Bedroom.','.$Bathroom.','.$TransDist.','.$TransType.','.$Year.','.$TOAdvertising.','.$minPrice.','.$maxPrice.','.$minArea.','.$maxArea.','.$OrderType.');';
		$txt=md5($txt);
		$Caching=$this->getJsonCache($txt);
		if ($Caching=='0'){
			$res=$this->search->getPoint($veiwmode,$distance,$namePoint,$TOProperty,$Bedroom,$Bathroom,$TransDist,$TransType,$Year,$TOAdvertising,$minPrice,$maxPrice,$minArea,$maxArea,$OrderType);
			$this->putJsonCache($txt, $res);
			echo $res;
		} else {
			echo $Caching;
		}


		//$r = $this->search->getPoint(0,$namePoint,1,0,0,1,0,0);
		//$this->session->set_userdata('np',$namePoint);
		//$this->session->set_userdata('returnSearch',$res);
		//echo decode_json($res);

	}
	/// q unit search ////
	public function getImgUnit(){
		$PID = $_GET['PID'];
		$TOProperty = $_GET['TOProperty'];
		$Bedroom = $_GET['Bedroom'];
		$Bathroom = $_GET['Bathroom'];
		$Year = $_GET['Year'];
		$TOAdvertising=$_GET['TOAdvertising'];
		$minPrice = $_GET['minPrice'];
		$maxPrice = $_GET['maxPrice'];

		$r = $this->search->getPostFromPoint($PID,$TOProperty,$Bedroom,$Bathroom,$Year,$TOAdvertising,$minPrice,$maxPrice);
		$this->session->set_userdata('imgUnits',$r);
	}

	public function searchPage()
	{
		echo $_POST['namePoint']."</br>";
		echo $_POST['TOAdvertising']."</br>";
	}

	public function unitDetail()
	{
		//Hard Code for Checking
		//$POID='88';
		$POID = $_POST['POID'];
		/*
		$this->search->updateViewPost($POID);
		$data['unit']=$this->search->getUnitFromPOID($POID);
		$data['project']=$this->unitdetail->getProjectFromPOID($POID);
		$TOAdvertising=$this->search->checkTypeAdvertising($POID);
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		$this->load->view($header,$profile);
		if ($TOAdvertising=="2"){
			$this->load->view('unitdetail',$data);
		};
		if ($TOAdvertising=="5"){
			$this->load->view('unitdetail_rent',$data);
		};
		if ($TOAdvertising=="1"){
			$this->load->view('unitdetail_sale',$data);
		};
//		$this->load->view('footer');
		*/
		 redirect('/zhome/unitDetail2/'.$POID, 'refresh');
	}

	public function unitDetail2_old()
	{
		$POID = $this->uri->segment(3);
		//$POID = $_POST['POID'];
		$this->search->updateViewPost($POID);
		$data['unit']=$this->search->getUnitFromPOID($POID);
		$data['project']=$this->unitdetail->getProjectFromPOID($POID);
		$TOAdvertising=$this->search->checkTypeAdvertising($POID);
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		$this->load->view($header,$profile);

		$data['qProblem']=$this->post->qProblem();
		if ($TOAdvertising=="2"){
			//$this->load->view('unitdetail',$data);
			$this->load->view('unitdetail_sale-with-tenant',$data);
		};
		if ($TOAdvertising=="5"){
			$this->load->view('unitdetail_rent',$data);
		};
		if ($TOAdvertising=="1"){
			$this->load->view('unitdetail_sale',$data);
		};
//		$this->load->view('footer');
	}

	public function unitDetail2($POID='')
	{
		if($POID==''){
//			$last = $this->uri->total_segments();
//			$POID = $this->uri->segment($last);
//			$segs = $this->uri->segment_array();
//			$tsegs = sizeof($segs);
			$POID = $this->uri->segment(3);
		}
		if (!is_numeric($POID)){
			$POID = $this->uri->segment(4);
		}
		if ($POID=="1"){
			$POID = $this->uri->segment(5);
		}

		echo $last;
		//Add Check Blocking Show Post
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$CheckBlocking=0;
		} else {
			$CheckBlocking=$this->unitdetail->blocking_post($POID);
		}
		if ($CheckBlocking==0){
			//AddTest360
			$T360=$this->uri->segment(4);
			//Add Share Facebook Via SMS
			$mfb=$this->uri->segment(4);
			//$POID = $_POST['POID'];
			$this->unitdetail->add_lastview($POID);
			$this->search->updateViewPost($POID);
			$data['unit']=$this->search->getUnitFromPOID($POID);
			$data['project']=$this->unitdetail->getProjectFromPOID($POID);
			$data['spec']=$this->unitdetail->getUnitSpecFromPOID($POID);
			$data['favourite']=$this->unitdetail->get_favourite($POID);
			$data['qProblem']=$this->post->qProblem();
			$data['qLineAlert']=$this->dashboard->qLineAlert();
			$TOAdvertising=$this->search->checkTypeAdvertising($POID);
			$data['TOAdvertising']=$TOAdvertising;
			$data['Banner']=$this->unitdetail->getImageBanner($POID,$TOAdvertising);
			if($firstname!=''){
				$fullname=$firstname.' '.$lastname;
			}else{
				$fullname='';
			}
			$data['max_location']=3;
			$data['mainunit']=array();
			$data['location']=array();
			$data['rowUnit']=$data['unit']->row();
			if($data['favourite']!=null){
				$data['rowFavourite']=$data['favourite']->row();
			}
			if($data['project']!=null){
				$data['rowProject']=$data['project']->row();
			}
			$data['train']=$this->unitdetail->getNearestTrainFromPID($data['rowProject']->PID);
			$data['rowTrain']=$data['train']->row();
			$unitSpec="";
			if($data['spec']->num_rows()>0){
				$unitView="";
				$unitRoom="";
				$unitFur="";
				foreach ($data['spec']->result() as $rowSpec){
					if($rowSpec->GCSID==1){
						$unitView.=$rowSpec->CSName_th.",";
					}
					if($rowSpec->GCSID==2){
						$unitRoom.=$rowSpec->CSName_th.",";
					}
					if($rowSpec->GCSID==3){
						$unitFur.=$rowSpec->CSName_th.",";
					}
				}
				if($unitView!=""){
					$unitView="วิว : ".substr($unitView,0,-1);
				}
				if($unitRoom!=""){
					$unitRoom="ห้อง : ".substr($unitRoom,0,-1);
				}
				if($unitFur!=""){
					$unitFur="เฟอร์นิเจอร์ : ".substr($unitFur,0,-1);
				}
				$unitSpec=$unitView." ".$unitRoom." ".$unitFur;
			}
			$projectAddress=" ที่อยู่โครงการ";
			if($data['rowProject']->Address!=""){
				$projectAddress.=" ".$data['rowProject']->Address;
			}
			if($data['rowProject']->Soi!=""){
				$projectAddress.=" ซ.".$data['rowProject']->Soi;
			}
			$projectAddress.=" แขวง".$data['rowProject']->District." เขต".$data['rowProject']->Area." จังหวัด".$data['rowProject']->Province;
			$data['Img']=$this->search->SelectImgFromPOID($POID);
			$data['facebook_id']=$this->search->getFacebookID();
			if($data['rowUnit']->TOAdvertising==1){
				$Price=$data['rowUnit']->TotalPrice;
				$PriceShow=number_format($Price/1000000,1)." ล้านบาท";
			}else if($data['rowUnit']->TOAdvertising==2){
				$Price=$data['rowUnit']->DTotalPrice;
				$PriceShow=number_format($Price/1000000,1)." ล้านบาท";
			}else if($data['rowUnit']->TOAdvertising==5){
				$Price=$data['rowUnit']->PRentPrice;
				$PriceShow=number_format($Price,0)." บาท";
			}
			if($data['rowUnit']->Bedroom==99){
				$Bedroom="สตูดิโอ";
			}else{
				$Bedroom=$data['rowUnit']->Bedroom." ห้องนอน";
			}
			$AdvertisingName=$this->search->getAdvertising($data['rowUnit']->TOAdvertising,'AName_th');
			$PropertyName=$this->search->getProperty($data['rowUnit']->TOProperty,'TOPName_th');
			$title=$AdvertisingName.$PropertyName." ".$data['rowUnit']->ProjectName." เจ้าของขายเอง";
			$specMeta=$AdvertisingName."คอนโด".$data['rowUnit']->ProjectName;
			$specMeta=str_replace(" ", "", $specMeta);
			//$title.="-".strlen($title);
			$description=$AdvertisingName.$PropertyName." ".$Bedroom." ".$data['rowUnit']->Bathroom." ห้องน้ำ ชั้น ".$data['rowUnit']->Floor." ขนาด ".$data['rowUnit']->useArea." ตร.ม. ราคา ".$PriceShow." หรือ ".number_format($data['rowUnit']->PricePerSq,0)." บาท/ตร.ม. อยู่ห่างรถไฟฟ้า ".$data['rowTrain']->KeyName." ".number_format($data['rowTrain']->Distance,0)." ม. สร้างเสร็จปี พ.ศ.".$data['rowProject']->YearEnd." ".$unitSpec.$projectAddress;
			$imageOG=$data['Img'][0];
			$ChkLogin=$this->ChkLogin();
			$data['ChkLogin']=$ChkLogin;
			$data['clickTel']=$this->session->userdata('clickTel');
			if ($mfb=="mfb"){
				$header="header_mfb";
				$data2['title']=$title;
				$this->load->view($header,$data2);
			} else {
				$header=$this->header_inc($ChkLogin);
				$profile=$this->user_profile($ChkLogin,$title,$description,'',$imageOG,$specMeta);
				$this->load->view($header,$profile);
			};
			//$header=$this->header_inc($ChkLogin);
			//$profile=$this->user_profile($ChkLogin,$title);
			//$this->load->view($header,$profile);
			//$this->load->view('unitdetail_p0',$data);//Config & Variable
			//Add Test T360
			if ($T360=="360"){
				$this->load->view('unitdetail360_p1',$data);//Summary
			} else {
				$this->load->view('unitdetail_p1',$data);//Summary
			}
			$this->load->view('unitdetail_p2',$data);//Location
			//Detail
			$data['tSale']=$TOAdvertising.",";
			if ($TOAdvertising=="1"){//ขาย
				$this->load->view('unitdetail_p3sale',$data);
			}
			if ($TOAdvertising=="2"){//ขายดาวน์
				$this->load->view('unitdetail_p3down',$data);
			}
			if ($TOAdvertising=="5"){//เช่า
				$this->load->view('unitdetail_p3rent',$data);
			}
			$this->load->view('unitdetail_p4',$data);//Compare
			//Add Test 360
			if ($T360=="360"){
				$this->load->view('footer_unitdetail360',$data);
			} else {
				$this->load->view('footer_unitdetail',$data);
	}
		} else {
			$Data['Msg']="ประกาศนี้ไม่สามารถไม่แสดงได้เนื่องจาก  อาจถูกขาย ให้เช่าแล้ว หรือหมดอายุ";
			$this->load->view('header_nonauth');
			$this->load->view('message',$Data);
		}
		$this->load->view('footerstandard');
	}

	public function listKeyMarker()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$Type=$this->uri->segment(3);
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listKeyMarker']=$this->backend->listKeyMarker($Type);
			$this->load->view($header,$profile);
			$this->load->view('listKeyMarker',$data);
			$this->load->view('footer');
		}
	}

	function postMapChange2($txt){
				$config['geocodeCaching'] = false;
				$config['zoom'] = '17';
				if ($txt==""){
					$config['center'] = '13.764934,100.5382955';
				} else {
					$config['center'] = $txt;
				}
				$config['map_height']= '300px';
				$this->googlemaps->initialize($config);
				$marker = array();
				if ($txt==""){
					$marker['position'] = '13.764934,100.5382955';
				} else {
					$marker['position'] = $txt;
				}
				$marker['draggable'] = true;
				$marker['ondragend'] = 'updateDatabase(event.latLng.lat(), event.latLng.lng());';
				$this->googlemaps->add_marker($marker);
				$return=$this->googlemaps->create_map();
				return $return;
	}

	public function addMarker()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			if (!isset($_POST['PID'])){
				$map = $this->postMapChange2("");
			} else {
				$queryProject=$this->db->query('Select Lat,Lng from Project Where PID="'.$_POST['PID'].'"');
				$rowProject=$queryProject->row();
				$Lat=$rowProject->Lat;
				$Lng=$rowProject->Lng;
				$txt=$Lat.','.$Lng;
				$map = $this->postMapChange2($txt);
			};
			$data['map'] = $map;
			$profile['map'] = $map;
			$profile['HSNumber']=3;
			$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
			$S['2']='<script type="text/javascript">var centreGot = false;</script>';
			$profile['HSrc']=$S;
			$this->load->view($header,$profile);
			$this->load->view('addMarker',$data);
			$this->load->view('footeraddmarker');
		}
	}

	public function addMarker2()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			if (isset($_POST['Old_ID']) and $_POST['Old_ID']!="" and ($_POST['Lat']!=$_POST['Old_Lat'] or $_POST['Lng']!=$_POST['Old_Lng'])){
				$this->backend->delMarker($_POST['Old_ID']);
			}
			$this->backend->inputKeyMarker($_POST);
			$this->listKeyMarker();
		}
	}

	public function delMarker()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$this->backend->delMarker($_POST['Old_ID2']);
			$this->listKeyMarker();
		}
	}

	public function editKeyMarker(){
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ID=$this->uri->segment(3);
			$query=$this->db->query('Select * from KeyMarker Where ID="'.$ID.'"');
			$data['query']=$query;
			$row=$query->row();
			$Lat=$row->Lat;
			$Lng=$row->Lng;
			$txt=$Lat.','.$Lng;
			//$this->backend->delMarker($ID);
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$map = $this->postMapChange2($txt);
			$data['map'] = $map;
			$profile['map'] = $map;
			$profile['HSNumber']=3;
			$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
			$S['2']='<script type="text/javascript">var centreGot = false;</script>';
			$profile['HSrc']=$S;
			$this->load->view($header,$profile);
			$this->load->view('editMarker',$data);
			$this->load->view('footeraddmarker');
		}
	}

	public function listProject()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listProject']=$this->backend->listProject();
			$this->load->view($header,$profile);
			$this->load->view('listProject',$data);
			$this->load->view('footer');
		}
	}

	public function editProject()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$PID=$this->uri->segment(3);
			$data['queryImg']=$this->backend->queryImg($PID);
			//$this->backend->queryImg($PID);
			$query=$this->db->query('Select * from Project Where PID="'.$PID.'"');
			$data['query']=$query;
			$row=$query->row();
			$Lat=$row->Lat;
			$Lng=$row->Lng;
			$txt=$Lat.','.$Lng;
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$map = $this->postMapChange2($txt);
			$data['map'] = $map;
			$profile['map'] = $map;
			$profile['HSNumber']=3;
			$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
			$S['2']='<script type="text/javascript">var centreGot = false;</script>';
			$profile['HSrc']=$S;
			$this->load->view($header,$profile);
			$this->load->view('editProject',$data);
			$this->load->view('footeraddmarker_editproject');
		}
	}

	public function editProject3($PID)
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			//$PID=$this->uri->segment(3);
			$data['queryImg']=$this->backend->queryImg($PID);
			$query=$this->db->query('Select * from Project Where PID="'.$PID.'"');
			$data['query']=$query;
			$row=$query->row();
			$Lat=$row->Lat;
			$Lng=$row->Lng;
			$txt=$Lat.','.$Lng;
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$map = $this->postMapChange2($txt);
			$data['map'] = $map;
			$profile['map'] = $map;
			$profile['HSNumber']=3;
			$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
			$S['2']='<script type="text/javascript">var centreGot = false;</script>';
			$profile['HSrc']=$S;
			$this->load->view($header,$profile);
			$this->load->view('editProject',$data);
			$this->load->view('footeraddmarker_editproject');
		}
	}

	public function delImgProject()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$PID=$this->uri->segment(4);
			$Img=$this->uri->segment(6);
			//echo $PID." ".$Img;
			$this->backend->delImg($PID,$Img);
			$this->editProject3($PID);
		};
	}

	public function addProject()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$map = $this->postMapChange2("");
			$data['map'] = $map;
			$profile['map'] = $map;
			$profile['HSNumber']=3;
			$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
			$S['2']='<script type="text/javascript">var centreGot = false;</script>';
			$profile['HSrc']=$S;
			$this->load->view($header,$profile);
			$this->load->view('addProject',$data);
			$this->load->view('footeraddmarker');
		}
	}

	public function addProject2()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$this->backend->addProject($_POST);
			$this->listProject();
		}
	}

	public function editProject2()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$this->backend->editProject($_POST);
			$this->listProject();
		}
	}

	public function listApprovePost()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listProject']=$this->backend->listProject();
			$this->load->view($header,$profile);
			$this->load->view('listPost',$data);
			$this->load->view('footer');
		}
	}

	public function listApprovePost1()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listProject']=$this->backend->listProject();
			$this->load->view($header,$profile);
			$this->load->view('listPost1',$data);
			$this->load->view('footer');
		}
	}

	public function listApprovePost5()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listProject']=$this->backend->listProject();
			$this->load->view($header,$profile);
			$this->load->view('listPost5',$data);
			$this->load->view('footer');
		}
	}

	public function adminDelUnit()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$POID = $this->uri->segment(3);
			$Type=$this->uri->segment(4);
			$this->backend->adminDelUnit($POID);
			if ($Type==2){
				$this->listApprovePost();
			};
			if ($Type==5){
				$this->listApprovePost5();
			};
			if ($Type==1){
				$this->listApprovePost1();
			};
		}
	}

	public function adminCancelHideUnit()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$this->backend->adminCancelHideUnit($_POST);
		}
	}

	public function adminViewUnitDetail_old()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$POID = $this->uri->segment(3);
			$data2['POID']=$POID;
			$TOAdvertising=$this->search->checkTypeAdvertising($POID);
			$data['unit']=$this->search->getUnitFromPOID($POID);
			$data['project']=$this->unitdetail->getProjectFromPOID($POID);
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			$data['qProblem']=$this->post->qProblem();
			if ($TOAdvertising=="2"){
				$this->load->view('activatePost',$data2);
				$this->load->view('unitdetail',$data);
			}
			if ($TOAdvertising=="5"){
				$this->load->view('activatePost',$data2);
				$this->load->view('unitdetail_rent',$data);
			}
			if ($TOAdvertising=="1"){
				$this->load->view('activatePost',$data2);
				$this->load->view('unitdetail_sale',$data);
			}
		}
	}

	public function adminViewUnitDetail()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$POID = $this->uri->segment(3);
			$data2['POID']=$POID;
			$data2['unit']=$this->search->getUnitFromPOID($POID);
			$TOAdvertising=$this->search->checkTypeAdvertising($POID);
			$data['TOAdvertising']=$TOAdvertising;
			$data['unit']=$this->search->getUnitFromPOID($POID);
			$data['project']=$this->unitdetail->getProjectFromPOID($POID);
			$data['qProblem']=$this->post->qProblem();
			$data['qLineAlert']=$this->dashboard->qLineAlert();
			$data['Banner']=$this->unitdetail->getImageBanner($TOAdvertising);
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			if($firstname!=''){
				$fullname=$firstname.' '.$lastname;
			}else{
				$fullname='';
			}
			$data['max_location']=3;
			$data['mainunit']=array();
			$data['location']=array();
			$data['rowUnit']=$data['unit']->row();
			if($data['project']!=null){
				$data['rowProject']=$data['project']->row();
			}
			$data['Img']=$this->search->SelectImgFromPOID($POID);
			$data['facebook_id']=$this->search->getFacebookID();

			$this->load->view('activatePost',$data2);
			//$this->load->view('unitdetail_p0',$data);//Config & Variable
			$this->load->view('unitdetail_p1',$data);//Summary
			$this->load->view('unitdetail_p2',$data);//Location
			//Detail
			if ($TOAdvertising=="2"){
				$data['tSale']=2;
				$this->load->view('unitdetail_p3down',$data);
			}
			if ($TOAdvertising=="5"){
				$data['tSale']=3;
				$this->load->view('unitdetail_p3rent',$data);
			}
			if ($TOAdvertising=="1"){
				$data['tSale']=1;
				$this->load->view('unitdetail_p3sale',$data);
			}
			$this->load->view('unitdetail_p4',$data);//Compare
			$this->load->view('footer_unitdetail',$data);
		}
	}

	public function activatePost(){
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			if($_POST['Msg2']!=''){
				$this->backend->updateNotePost($_POST);
			}
			if($_POST['approvePost']=='1'){
				$this->backend->activatePost($_POST['POID']);
			}
			if($_POST['sendEmail']=='1'){
				$this->backend->activatePost($_POST['POID']);
				$this->backend->sendEmailActivate($_POST['POID']);
			}
			if($_POST['blockPost']=='1'){
				$this->backend->blockPost($_POST);
			}
			$POID=$_POST['POID'];
			$this->backend->sendEmailBlock($POID);
			$Type=$this->search->checkTypeAdvertising($POID);
			if ($Type==2){
				if($_POST['approvePost']=='1'){
					$this->load->view('listpost2rediect');//Google Analytic use On ZmyHome
				}else{
					$this->listApprovePost();
				}
			}
			if ($Type==5){
				if($_POST['approvePost']=='1'){
					$this->load->view('listpost5rediect');//Google Analytic use On ZmyHome
				}else{
					$this->listApprovePost5();
				}
			}
			if ($Type==1){
				if($_POST['approvePost']=='1'){
					$this->load->view('listpost1rediect');//Google Analytic use On ZmyHome
				}else{
					$this->listApprovePost1();
				}
			}
		}
	}

	public function blockPost()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$this->backend->blockPost($_POST);
			$POID=$_POST['POID'];
			$this->backend->sendEmailBlock($POID);
			$Type=$this->search->checkTypeAdvertising($POID);
			if ($Type==2){
				$this->listApprovePost();
			};
			if ($Type==5){
				$this->listApprovePost5();
			};
			if ($Type==1){
				$this->listApprovePost1();
			};
		}
	}

	public function updateNotePost()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$this->backend->updateNotePost($_POST);
			$POID=$_POST['POID'];
			$Type=$this->search->checkTypeAdvertising($POID);
			if ($Type==2){
				$this->listApprovePost();
			};
			if ($Type==5){
				$this->listApprovePost5();
			};
			if ($Type==1){
				$this->listApprovePost1();
			};
		}
	}

//Modify 24-09-58
	public function unitDetailOwner()
	{
		$POID=$this->uri->segment(3);
		if ($POID==""){
			$POID = $this->post->getPOID();
		};
/*
		$data['unit']=$this->unitdetail->getUnitFromPOID($POID);
		$data['project']=$this->unitdetail->getProjectFromPOID($POID);
		$TOAdvertising=$this->search->checkTypeAdvertising($POID);
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		$this->load->view($header,$profile);
		if ($TOAdvertising=="2"){
			$this->load->view('unitdetail',$data);
		};
		if ($TOAdvertising=="5"){
			$this->load->view('unitdetail_rent',$data);
		};
		if ($TOAdvertising=="1"){
			$this->load->view('unitdetail_sale',$data);
		};
//		$this->load->view('footer');
*/
//		redirect('/zhome/unitDetail2/'.$POID, 'refresh');
		redirect('/'.$this->dashboard->PostPathName($POID).'/condo/'.$this->dashboard->ProjectNameFolder($POID).'/'.$POID, 'refresh');
	}
	public function howItWork(){
		$this->session->set_userdata('last_page','/');
		$this->session->set_userdata('token'.'');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);
		$this->load->view($header,$profile);
		$this->load->view('howitwork');
		$this->load->view('footerstandard');
		$this->load->view('footer_home');

	}

	public function getOwnerNumber(){
		$POID = $_GET['POID'];
		$r = $this->unitdetail->viewTel($POID);
		echo json_encode($r);
	}

	public function add_view_facebook(){
		$POID=$this->uri->segment(3);
		$this->unitdetail->add_view_facebook($POID);
	}

	public function add_Helpdesk()
	{
		$this->post->add_Helpdesk($_POST);
	}

	public function listHelpdesk()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			$this->load->view('listHelpdesk');
			$this->load->view('footer');
		}
	}

	public function updateHelpdesk()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$data=array(
			'hid' => $this->input->post('hid'),
			'check_pass' => $this->input->post('check_pass')
			);
			$update=$this->backend->updateHelpdesk($data);
			if($update==1){
				echo redirect('zhome/listHelpdesk','refresh');
				//$this->listHelpdesk();
			}
		}
	}

	public function searchProject2()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			$this->load->view('searchProject2');
			$this->load->view('footer_search');
		}
	}

	public function listProject2()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listProject']=$this->backend->listProject2($_POST);
			if ($_POST){
				$data['ProjectName'] = $_POST['ProjectName'];
				$data['Province'] = $_POST['Province'];
			} else {
				$data['ProjectName'] = "";
				$data['Province'] = "";
			};
			$this->load->view($header,$profile);
			$this->load->view('searchProject2',$data);
			$this->load->view('listProject2',$data);
			$this->load->view('footer_search');
		}
	}

	public function searchUnit()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['qTOAdvertising']=$this->post->qTOAdvertising();
			$data['qActivePost']=$this->post->qActivePost();
			$this->load->view($header,$profile);
			$this->load->view('searchUnit',$data);
			$this->load->view('footer_search');
		}
	}

	public function listUnit()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listUnit']=$this->backend->listUnit($_POST);
			$data['qTOAdvertising']=$this->post->qTOAdvertising();
			$data['qActivePost']=$this->post->qActivePost();
			if ($_POST){
				$data['ProjectName'] = $_POST['ProjectName'];
				$data['Ownername'] = $_POST['Ownername'];
				$data['Advertising'] = $_POST['Advertising'];
				$data['ActivePost'] = $_POST['ActivePost'];
				$data['SortPost'] = $_POST['SortPost'];
				$data['expire_check'] = $_POST['expire_check'];
				$data['expire_day'] = $_POST['expire_day'];
				$data['expire_type'] = $_POST['expire_type'];
			} else {
				$data['ProjectName'] = "";
				$data['Ownername'] = "";
				$data['Advertising'] = "";
				$data['ActivePost'] = "";
				$data['SortPost'] = 1;
				$data['expire_day'] = "";
			}
			$this->load->view($header,$profile);
			$this->load->view('searchUnit',$data);
			$this->load->view('listUnit',$data);
			$this->load->view('footer_search');
		}
	}

	public function getMarketCont(){
		$namepoint = $_GET['namepoint'];
		$distance = $_GET['distance'];
		$advertising = $_GET['advertising'];
		$YearEnd = $_GET['nowyear'];
		$proptype = $_GET['proptype'];
		$bedroom = $_GET['bedroom'];
		$bathroom = $_GET['bathroom'];
		$transdist = $_GET['transdist'];
		$transtype = $_GET['TransType'];
		$age = $_GET['age'];
		$minPrice = $_GET['minPrice'];
		$maxPrice = $_GET['maxPrice'];
		$minArea = $_GET['minArea'];
		$maxArea = $_GET['maxArea'];
		//Add Test Log
		//$process_id=$this->testlog->genprocessid();
		//$this->testlog->savelogstart($process_id);
		$txt='getMarketCont('.$namepoint.','.$distance.','.$advertising.','.$YearEnd.','.$proptype.','.$bedroom.','.$bathroom.','.$transdist.','.$transtype.','.$age.','.$minPrice.','.$maxPrice.','.$minArea.','.$maxArea.');';
		$txt=md5($txt);
		$Caching=$this->getJsonCache($txt);
		$Caching=0;
		if ($Caching=='0'){
			$res = $this->search->getMarketCont($namepoint,$distance,$advertising,$YearEnd,$proptype,$bedroom,$bathroom,$transdist,$transtype,$age,$minPrice,$maxPrice,$minArea,$maxArea);
			//$this->putJsonCache($txt, $res);
			echo $res;
		} else {
			echo $Caching;
		}
		//$this->testlog->savelogend($process_id);
		$this->session->set_userdata('market_cont',$res);
	}

	public function testupload()
	{
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			$this->load->view('testupload');
			$this->load->view('footer');
	}

	public function uploadResized()
	{
		if ($_POST) {
			 $userid=$this->session->userdata('user_id');
			 $token=$this->session->userdata('token');
			 define('IMG_DIR', 'userImg/'.$userid.'/');
             if(!is_dir(IMG_DIR)) //create the folder if it's not already exists
             {
               mkdir(IMG_DIR,0755,TRUE);
             }
			define('UPLOAD_DIR', 'userImg/'.$userid.'/');
			$img = $_POST['image'];
			$imgType= $_POST['imgType'];
			$orit=$_POST['orit'];
			$img = str_replace('data:image/jpeg;base64,', '', $img);
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			$file = UPLOAD_DIR . uniqid() . '.jpg';
			$success = file_put_contents($file, $data);
			print $success ? $file : 'Unable to save the file.';
			$record=$file;
			$inFile = $file;
			$outFile = UPLOAD_DIR . uniqid() . '.jpg';
			$image = new Imagick($inFile);
			if ($orit==3){
			  $image->rotateImage(new ImagickPixel('none'), 180);
			}
			if ($orit==6){
			  $image->rotateImage(new ImagickPixel('none'), 90);
			}
			if ($orit==8){
			  $image->rotateImage(new ImagickPixel('none'), -90);
			}
			$width=$image->getImageWidth();
			$height=$image->getImageHeight();
			if ($height<600){
				$width=$width*600/$height;
				$image->scaleImage($width, 600);
			}
			$ratio=$width/600;
			if ($ratio>2.1){
				$center_width=round($width/2);
				$start_width=$center_width-630;
				$image->cropImage(1260,600,$start_width,0);
//				$image->writeImage($outFile);
//				$record=$outFile;
			}
			$watermark = new Imagick();
			$watermark->readImage("img/water.png");
			// how big are the images?
			$iWidth = $image->getImageWidth();
			$iHeight = $image->getImageHeight();
			if ($iWidth>=$iHeight){
				$Horizental=1;
			} else {
				$Horizental=0;
			}
			$wWidth = $watermark->getImageWidth();
			$wHeight = $watermark->getImageHeight();

			if ($iHeight < $wHeight || $iWidth < $wWidth) {
				// resize the watermark
				$watermark->scaleImage($iWidth, $iHeight);

				// get new size
				$wWidth = $watermark->getImageWidth();
				$wHeight = $watermark->getImageHeight();
			}

			// calculate the position
			$x = ($iWidth - $wWidth) / 2;
			$y = ($iHeight - $wHeight) / 2;

			$image->compositeImage($watermark, imagick::COMPOSITE_OVER, $x, $y);
			$image->writeImage($outFile);
			$record=$outFile;
			$query=$this->db->query('select POID from Post Where Token="'.$token.'"');
			$row=$query->row();
			$POID=$row->POID;
			$this->db->query('insert into ImagePost set POID="'.$POID.'", file="/'.$record.'", type="'.$imgType.'", Horizental="'.$Horizental.'"');
			$this->db->query('insert into checkUpload set user_id="'.$userid.'", token="'.$token.'"');
			$this->post->deactive($token);
		}
	}

	public function uploadResizedProject()
	{
		if ($_POST) {
			 $userid=$this->session->userdata('user_id');
			 $token=$this->session->userdata('token');
			 $PID=$_POST['PID'];
			 define('IMG_DIR', 'projImg/'.$PID.'/Picture/');
             if(!is_dir(IMG_DIR)) //create the folder if it's not already exists
             {
               mkdir(IMG_DIR,0755,TRUE);
             }
			define('UPLOAD_DIR', 'projImg/'.$PID.'/Picture/');
			$img = $_POST['image'];
			//$imgType= $_POST['imgType'];

			$orit=$_POST['orit'];
			$img = str_replace('data:image/jpeg;base64,', '', $img);
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			$file = UPLOAD_DIR . uniqid() . '.jpg';
			$success = file_put_contents($file, $data);
			print $success ? $file : 'Unable to save the file.';
			$record=$file;
			$inFile = $file;
			$outFile = UPLOAD_DIR . uniqid() . '.jpg';
			$image = new Imagick($inFile);
			if ($orit==3){
			  $image->rotateImage(new ImagickPixel('none'), 180);
			}
			if ($orit==6){
			  $image->rotateImage(new ImagickPixel('none'), 90);
			}
			if ($orit==8){
			  $image->rotateImage(new ImagickPixel('none'), -90);
			}
			$width=$image->getImageWidth();
			$height=$image->getImageHeight();
			if ($height<600){
				$width=$width*600/$height;
				$image->scaleImage($width, 600);
			}
			$ratio=$width/600;
			if ($ratio>2.1){
				$center_width=round($width/2);
				$start_width=$center_width-630;
				$image->cropImage(1260,600,$start_width,0);
//				$image->writeImage($outFile);
//				$record=$outFile;
			}
			$watermark = new Imagick();
			$watermark->readImage("img/water.png");
			// how big are the images?
			$iWidth = $image->getImageWidth();
			$iHeight = $image->getImageHeight();
			$wWidth = $watermark->getImageWidth();
			$wHeight = $watermark->getImageHeight();

			if ($iHeight < $wHeight || $iWidth < $wWidth) {
				// resize the watermark
				$watermark->scaleImage($iWidth, $iHeight);

				// get new size
				$wWidth = $watermark->getImageWidth();
				$wHeight = $watermark->getImageHeight();
			}

			// calculate the position
			$x = ($iWidth - $wWidth) / 2;
			$y = ($iHeight - $wHeight) / 2;

			$image->compositeImage($watermark, imagick::COMPOSITE_OVER, $x, $y);
			$image->writeImage($outFile);
			$record=$outFile;
			//$query=$this->db->query('select POID from Post Where Token="'.$token.'"');
			//$row=$query->row();
			//$POID=$row->POID;
			//$this->db->query('insert into ImagePost set POID="'.$POID.'", file="/'.$record.'", type="'.$imgType.'"');
			$this->db->query('insert into checkUpload set user_id="'.$userid.'", token="'.$token.'"');
			unlink($file);
		}
	}

	public function updateMarker()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$mtype='Project';
			$r=$this->backend->updateMarker($mtype);
			echo $r;
		}
	}

	public function searchLabel()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['qLabel']=$this->post->qLabel();
			$this->load->view($header,$profile);
			$this->load->view('searchLabel',$data);
			$this->load->view('footer_search');
		}
	}

	public function listLabel()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listLabel']=$this->backend->listLabel($_POST);
			$data['qLabel']=$this->post->qLabel();
			if ($_POST){
				$data['Label'] = $_POST['Label'];
				$data['ActiveLabel'] = $_POST['ActiveLabel'];
			} else {
				$data['Label'] = "";
				$data['ActiveLabel'] = "";
			}
			$this->load->view($header,$profile);
			$this->load->view('searchLabel',$data);
			$this->load->view('listLabel',$data);
			$this->load->view('footer_search');
		}
	}

	public function updateLabel()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$data=array(
			'lid' => $this->input->post('lid'),
			'desc' => $this->input->post('desc'),
			'status' => $this->input->post('status')
			);
			$update=$this->backend->updateLabel($data);
			if($update==1){
				$this->session->set_flashdata('message', 'Information Has been Successfully Updated');
				$this->session->set_flashdata('Label',$this->input->post('Label'));
				$this->session->set_flashdata('ActiveLabel',$this->input->post('ActiveLabel'));
				//echo redirect($this->session->userdata('previous_page'));
				echo redirect('zhome/searchLabel','refresh');
				//$this->listLabel();
			}
		}
	}

	public function searchProfile()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			$this->load->view('searchProfile',$data);
			$this->load->view('footer_search');
		}
	}

	public function listProfile()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listProfile']=$this->backend->listProfile($_POST);
			$data['qLabel']=$this->post->qLabel();
			if ($_POST){
				$data['Name'] = $_POST['Name'];
				$data['Surname'] = $_POST['Surname'];
				$data['ActiveProfile'] = $_POST['ActiveProfile'];
			} else {
				$data['Name'] = "";
				$data['Surname'] = "";
				$data['ActiveProfile'] = "";
			}
			$this->load->view($header,$profile);
			$this->load->view('searchProfile',$data);
			$this->load->view('listProfile',$data);
			$this->load->view('footer_search');
		}
	}

	public function editProfile($user_id)
	{
		if(!$user_id){
			$user_id=$this->session->userdata('user_id');
		}
		if ($user_id){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['Profile']=$this->search->getProfile($user_id);
			$data['qLabel']=$this->search->qLabel('profile',$language);
			$this->load->view($header,$profile);
			$this->load->view('editProfile',$data);
			$this->load->view('footer_search');
		}
	}

	public function updateProfile()
	{
		$ChkLogin=$this->ChkLogin();
		if($ChkLogin){
			$data=array(
				'user_id' => $this->input->post('user_id'),
				'first_name' => $this->input->post('first_name'),
				'last_name' => $this->input->post('last_name'),
				'email' => $this->input->post('email'),
				'telephone' => $this->input->post('telephone')
			);
			$update=$this->backend->updateProfile($data);
			if($update==1){
				$this->session->set_flashdata('message', 'Information Has been Successfully Updated');
				echo redirect('zhome/dashboard/profile','refresh');
			}
		}else{
			echo redirect('zhome','refresh');
		}
	}

	public function RenameImgProject()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$PID=$_POST['PID'];
			$FromFile=$_POST['FromFile'];
			$ToFile=$_POST['ToFile'];
			//echo $PID." ".$Img;
			//rename($ToFile,$BToFile);
			$this->db->query('update ImagePost Set file="/'.$FromFile.'" Where file="/'.$ToFile.'"');
			//echo 'update ImagePost Set file="/'.$FromFile.'" Where file="/'.$ToFile.'"';
			$this->editProject3($PID);
		}
	}

	public function searchStation()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['qStationType']=$this->search->qStationType();
			$this->load->view($header,$profile);
			$this->load->view('searchStation',$data);
			$this->load->view('footer_search');
		}
	}

	public function listStation()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['listStation']=$this->backend->listStation($_POST);
			$data['qStationType']=$this->search->qStationType();
			if ($_POST){
				$data['StationType'] = $_POST['StationType'];
				$data['Distance'] = $_POST['Distance'];
			} else {
				$data['StationType'] = "";
				$data['Distance'] = "";
			}
			$this->load->view($header,$profile);
			$this->load->view('searchStation',$data);
			$this->load->view('listStation',$data);
			$this->load->view('footer_search');
		}
	}

	public function listStationProject()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$Station=$this->uri->segment(3);
			$Distance=$this->uri->segment(4);
			$YearGroup=$this->uri->segment(5);
			$data['listProject']=$this->backend->listStationProject($Station,$Distance,$YearGroup);
			$this->load->view($header,$profile);
			$this->load->view('listStationProject',$data);
			$this->load->view('footer_search');
		}
	}

	public function activatePostAndEmail()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$this->backend->activatePost($_POST['POID']);
			$this->backend->sendEmailActivate($_POST['POID']);
			$POID=$_POST['POID'];
			$Type=$this->search->checkTypeAdvertising($POID);
			if ($Type==2){
				//$this->listApprovePost();
				$this->load->view('listpost2rediect');
			}
			if ($Type==5){
				//$this->listApprovePost5();
				$this->load->view('listpost5rediect');
			}
			if ($Type==1){
				//$this->listApprovePost1();
				$this->load->view('listpost1rediect');
			}
		}
	}


	public function listViewProject(){
		$this->session->set_userdata('last_page','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$checkAdmin=$this->backend->checkAdmin();
		$data['checkAdmin']=$checkAdmin;
		$title="ขาย เช่า คอนโด ที่พัก ใกล้ ";
		$description="ขาย ขายดาวน์ คอนโด ที่พัก ใกล้";
		$keyword="ขาย,ขายดาวน์,คอนโด,ใกล้,รถไฟฟ้า,เจ้าของขายเอง,มือหนึ่ง,มือสอง,ราคาถูก";
		$KeyType=$this->uri->segment(3);
		$KeyID=$this->uri->segment(4);
		$KeyNameAnchor=$this->uri->segment(5);
		$query_marker=$this->backend->listKeyMarker2($KeyType);
		$data['KeyType']=$KeyType;
		$data['KeyID']=$KeyID;
		$data['Distance']=3000;
		$data['listMarker']=$query_marker;
		if($KeyID!=''){
			$query_name=$this->backend->getKeyMarker($KeyID);
			$KeyName=$query_name->row()->KeyName;
			$KeyTypeName=$query_name->row()->Name_th;
			if($KeyName!=''){
				$KeyName = urldecode($KeyName);
				$data['KeyName']=$KeyName;
			}else{
				$data['KeyName']="";
			}
			$title.=$KeyTypeName." ".$data['KeyName'];
			$description=$KeyTypeName." ".$KeyName;
			$keyword.=",".$data['KeyName'];
		}else{
			$title.=$KeyType;
			$description=$KeyType;
		}
		$title.=" เจ้าของขายเอง";
		$description.=" รัศมี ".number_format(($data['Distance']/1000),1)." กม. เจ้าของขายเอง ทั้งมือหนึ่งและมือสอง ราคาถูก";
		$profile=$this->user_profile($ChkLogin,$title,$description,$keyword);
		$this->load->view($header,$profile);
		$this->load->view('listViewProject',$data);
		$this->load->view('footerstandard');
	}

	public function getProjectList(){
		$KeyType = $_GET['KeyType'];
		$KeyID = $_GET['KeyID'];
		$distance = $_GET['Distance'];
		$res=$this->search->getProjectList($KeyType,$KeyID,$Distance);
	}

	public function renewpostbyemail()
	{
		$POID=$this->uri->segment(3);
		$Token=$this->uri->segment(4);
		//echo $POID;
		//echo $Token;
		$this->postemail->renewpostbyemail($POID,$Token);
		$Data['Msg']="ประกาศของคุณได้รับการต่ออายุอีก 30 วันเรียบร้อยแล้ว";
		$this->load->view('header_nonauth');
		$this->load->view('message',$Data);
		$this->load->view('footerstandard');
	}

	public function rentpostbyemail()
	{
    $checkRobot=$this->usermenu->check_robot();
    if ($checkRobot!=1){
  		$POID=$this->uri->segment(3);
  		$Token=$this->uri->segment(4);
  		$this->postemail->rentpostbyemail($POID,$Token);
  		$Data['Msg']="ประกาศของคุณได้รับการลบออกจากระบบเนื่องจากเช่าได้แลัว";
  		$this->load->view('header_nonauth');
  		$this->load->view('message',$Data);
  		$this->load->view('footerstandard');
    };
	}

	public function closepostbyemail()
	{
    $checkRobot=$this->usermenu->check_robot();
    if ($checkRobot!=1){
  		$POID=$this->uri->segment(3);
  		$Token=$this->uri->segment(4);
  		$this->postemail->closepostbyemail($POID,$Token);
  		$Data['Msg']="ประกาศของคุณได้รับการลบออกจากระบบเนื่องจากขายได้แลัว";
  		$this->load->view('header_nonauth');
  		$this->load->view('message',$Data);
  		$this->load->view('footerstandard');
    };
	}

	public function hidepostbyemail()
	{
    $checkRobot=$this->usermenu->check_robot();
    if ($checkRobot!=1){
  		$POID=$this->uri->segment(3);
  		$Token=$this->uri->segment(4);
  		$this->postemail->hidepostbyemail($POID,$Token);
  		$Data['Msg']="ประกาศของคุณได้รับการซ่อนจากระบบแลัว";
  		$this->load->view('header_nonauth');
  		$this->load->view('message',$Data);
  		$this->load->view('footerstandard');
    };
	}

	public function unitDetailEmail()
	{
		$POID=$this->uri->segment(3);
		$this->postemail->updatelogviewbyemail($POID);
		redirect('/zhome/unitDetail2/'.$POID);
	}

	public function closepost()
	{
    $checkRobot=$this->usermenu->check_robot();
    if ($checkRobot!=1){
  		$Token=$this->uri->segment(3);
  		$Month=$this->uri->segment(4);
  		$Year=$this->uri->segment(5);
  		$this->dashboard->closepost($Token,$Month,$Year);
  		$this->dashboard();
    };
	}

	public function reopenpost()
	{
		$Token=$this->uri->segment(3);
		$this->dashboard->reopenpost($Token);
		$this->dashboard();
	}

	public function add_LineAlert()
	{
		$this->dashboard->add_LineAlert($_POST);
	}

	public function checkHomeSearch(){
		$namePoint = $_GET['namePoint'];
		$r = $this->search->checkHomeSearch($namePoint);
		echo json_encode($r);
	}

	public function check_LineAlert()
	{
		$POID=$this->uri->segment(3);
		$this->dashboard->check_LineAlert($POID);
	}

	public function searchUnitView()
	{
		$checkAdmin=$this->backend->checkAdmin();
		$nowdate=date('Y-m-d');
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$data['qProject']=$this->post->qProject2();
			$data['qTOAdvertising']=$this->post->qTOAdvertising();
			$data['qActivePost']=$this->post->qActivePost();
			$data['ReportType'] = 1;
			$data['StartDate'] = $nowdate;
			$data['EndDate'] = $nowdate;
			$data['ProjectName'] = "";
			$data['OwnerName'] = "";
			$data['Advertising'] = "";
			$data['UserName'] = "";
			$data['OrderBy'] = 1;
			$this->load->view($header,$profile);
			$this->load->view('searchUnitView',$data);
			$this->load->view('footer_search');
		}
	}

	public function listUnitView()
	{
		$checkAdmin=$this->backend->checkAdmin();
		$nowdate=date('Y-m-d');
		if ($checkAdmin==1){
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			if ($_POST){
				$data['ReportType'] = $_POST['ReportType'];
				$data['StartDate'] = $_POST['StartDate'];
				$data['EndDate'] = $_POST['EndDate'];
				$data['ProjectName'] = $_POST['ProjectName'];
				$data['OwnerName'] = $_POST['OwnerName'];
				$data['Advertising'] = $_POST['Advertising'];
				$data['UserName'] = $_POST['UserName'];
				$data['OrderBy'] = $_POST['OrderBy'];
			} else {
				$data['ReportType'] = 1;
				$data['StartDate'] = $nowdate;
				$data['EndDate'] = $nowdate;
				$data['ProjectName'] = "";
				$data['OwnerName'] = "";
				$data['Advertising'] = "";
				$data['UserName'] = "";
				$data['OrderBy'] = 1;
			}
			if($data['ReportType']==1){
				$data['listUnit']=$this->backend->listUnitView($_POST);
			}else{
				$data['listUnit']=$this->backend->listUnitView2($_POST);
			}
			$data['qProject']=$this->post->qProject2();
			$data['qTOAdvertising']=$this->post->qTOAdvertising();
			$data['qActivePost']=$this->post->qActivePost();
			$this->load->view($header,$profile);
			$this->load->view('searchUnitView',$data);
			if($data['ReportType']==1){
				$this->load->view('listUnitView',$data);
			}else{
				$this->load->view('listUnitView2',$data);
			}
			$this->load->view('footer_search');
		}
	}

	public function listUnitViewDetail()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$POID = $_GET['POID'];
			$viewType = $_GET['viewType'];
			$StartDate = $_GET['StartDate'];
			$EndDate = $_GET['EndDate'];
			$res = $this->backend->listUnitViewDetail($POID,$viewType,$StartDate,$EndDate);
		}
	}

	public function listUnitViewDetail2()
	{
		$checkAdmin=$this->backend->checkAdmin();
		if ($checkAdmin==1){
			$user_id = $_GET['user_id'];
			$viewType = $_GET['viewType'];
			$OperateDate = $_GET['OperateDate'];
			$res = $this->backend->listUnitViewDetail2($user_id,$viewType,$OperateDate);
		}
	}

	public function getImageBanner(){
		$viewtype = $_GET['viewtype'];
		$distance = $_GET['distance'];
		$namePoint=$_GET['namePoint'];
		$TOProperty = $_GET['TOProperty'];
		$Bedroom = $_GET['Bedroom'];
		$Bathroom = $_GET['Bathroom'];
		$TransDist = $_GET['TransDist'];
		$TransType = $_GET['TransType'];
		$Year = $_GET['Year'];
		$TOAdvertising=$_GET['TOAdvertising'];
		$minPrice = $_GET['minPrice'];
		$maxPrice = $_GET['maxPrice'];
		$OrderType = $_GET['Order'];

		$res=$this->search->getImageBanner($viewtype,$distance,$namePoint,$TOProperty,$Bedroom,$Bathroom,$TransDist,$TransType,$Year,$TOAdvertising,$minPrice,$maxPrice,$OrderType);
	}

	public function getJsonCache($txt){
		$time=time()-(6*60*60);
		$this->db->query('delete from caching where unix_timestamp<"'.$time.'"');
		$query=$this->db->query('Select count(id) as id,  txt_json from caching where txt_function="'.$txt.'" and status=1 limit 1');
		$rowCaching=$query->row();
		$id=$rowCaching->id;
		$json=$rowCaching->txt_json;
		if ($id==0){
			$result='0';
			return $result;
		} else {
			return $json;
		}
	}
	 public function putJsonCache($txt,$json){
	 	$unix_time=time();
		$json=addslashes($json);
		$this->db->query("insert into caching set txt_function='".$txt."', txt_json='".$json."', unix_timestamp='".$unix_time."'");
	 }


	public function listallunit(){
			$type=$this->uri->segment(3);
			if ($type=="sell"){
				$data['result']=$this->db->query('select * from Post Where (TOAdvertising=1 or TOAdvertising=2) and Active=1 Order by ActiveDay');
			} else {
				$data['result']=$this->db->query('select * from Post Where TOAdvertising=5 and Active=1 Order by ActiveDay');
			}
			$ChkLogin=$this->ChkLogin();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			$this->load->view('listallunit',$data);
			$this->load->view('footerstandard');
			$this->load->view('footer_home');
	}

	public function updateMarkerProvince(){
		$this->backend->updateMarkerProvince($_POST);
	}

	public function updateViewBanner(){
		$this->search->updateViewBanner($_POST);
	}

	public function developer_addunit(){
		if(!$user_id){
				$user_id=$this->session->userdata('user_id');
			}
		if ($this->dashboard->CheckDeveloper($user_id)==1){
		  $this->developer->add_unit($_POST);
		  $this->dashboard();
		}
	}

	public function del_developer_unit(){
		$POID=$this->uri->segment(3);
		if(!$user_id){
			$user_id=$this->session->userdata('user_id');
		}
		$this->developer->del_unit($POID,$user_id);
		redirect('/zhome/dashboard', 'location');
	}

	public function edit_developer_unit(){
		if(!$user_id){
				$user_id=$this->session->userdata('user_id');
			}
		if ($this->dashboard->CheckDeveloper($user_id)==1){
		  $this->developer->edit_unit($_POST);
		  $this->dashboard();
		}
	}

	public function checkSignupEmail(){
		$email = $_POST['email'];
		$chkemail=$this->backend->checkSignupEmail($email);
		echo $chkemail;
	}

	public function checkLoginPassword(){
		$chkpassword=$this->backend->checkLoginPassword($_POST);
		echo $chkpassword;
	}

	public function checkRefPlaceType(){
		$reftype=$this->search->checkRefPlaceType($_POST);
		echo $reftype;
	}
	
	public function updateNotePostExpire(){
		$this->backend->updateNotePostExpire($_POST);
	}

	public function postHome(){
		$language=1;
		/*$KeyNewPost = $this->uri->segment(3);
		$this->post->DelTmpPost();
		if ($KeyNewPost=="newpost"){
			$this->post->newPost2();
		} else {
			$this->post->newPost();
		}*/
		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		$header=$this->header_inc($ChkLogin);
		$profile=$this->user_profile($ChkLogin);

		$map = $this->postMapChange();
		$data['map'] = $map;
		$profile['map'] = $map;
		$profile['HSNumber']=3;
		$S['1']='<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false&libraries=places"></script>';
		$S['2']='<script type="text/javascript">var centreGot = false;</script>';
		$profile['HSrc']=$S;

		//$data['qProjectName']=$this->post->qProject();
		$data['qTOOwner']=$this->post->qTOOwner();
		$data['qTOProperty']=$this->post->qTOProperty();
		$data['qTOAdvertising']=$this->post->qTOAdvertising();
		//$data['qLabel']=$this->search->qLabel('post1',$language);
		//$data['txtMapChange']=$this->txtMapChange();
		$this->load->view($header,$profile);
		$this->load->view('inputhome',$data);
		$this->load->view('footerstandard');
		$this->load->view('footer_post_home',$data);
	}

	function boostPost(){
		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		if($ChkLogin==1){
			$data['POID']=$this->uri->segment(3);
			$user_id=$this->session->userdata('user_id');
			$data['ListPostType1']=$this->credit->ListPost($data['POID']);
			$data['ListUnPost']=$this->dashboard->ListUnPost($user_id);
			//query data from old dashboard
			//$data['unlist']=$this->dashboard->check_unlist();
			$data['clist']=$this->dashboard->check_list();
			$data['qProblem']=$this->post->qProblem();
			$data['qLineAlert']=$this->dashboard->qLineAlert();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			$this->load->view('boostpost',$data);
			$this->load->view('dashboard_footer',$data);
		}
	}

	function boostPostEdit(){
		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		if($ChkLogin==1){
			$user_id=$this->session->userdata('user_id');
			$data['POID']=$this->uri->segment(3);
			$data['ListPostType1']=$this->credit->ListPost($data['POID']);
			$data['ListUnPost']=$this->dashboard->ListUnPost($user_id);
			//query data from old dashboard
			$data['unlist']=$this->dashboard->check_unlist();
			$data['clist']=$this->dashboard->check_list();
			$data['qProblem']=$this->post->qProblem();
			$data['qLineAlert']=$this->dashboard->qLineAlert();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			$this->load->view('boostpost_edit',$data);
			$this->load->view('dashboard_footer',$data);
		}
	}

	function payment(){
		$this->session->set_userdata('lastpage','/');
		$this->lang_check();
		$ChkLogin=$this->ChkLogin();
		if($ChkLogin==1){
			$user_id=$this->session->userdata('user_id');
			$data['ListPostType1']=$this->dashboard->ListPost($user_id);
			$data['ListUnPost']=$this->dashboard->ListUnPost($user_id);
			//query data from old dashboard
			$data['unlist']=$this->dashboard->check_unlist();
			$data['clist']=$this->dashboard->check_list();
			$data['qProblem']=$this->post->qProblem();
			$data['qLineAlert']=$this->dashboard->qLineAlert();
			$header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
			$this->load->view($header,$profile);
			$type=$this->uri->segment(3);
			if(!isset($type)){
				$type='owner';
			}
			if (strlen($type)>20){
				$type='owner';
			}
			$data['type']=$type;
			//$this->load->view('payment_header',$data);
			$this->load->view('payment',$data);
			$this->load->view('dashboard_footer',$data);
		}
	}

  public function checkout()
	{
    $ChkLogin=$this->ChkLogin();
		if($ChkLogin==1){
      $header=$this->header_inc($ChkLogin);
			$profile=$this->user_profile($ChkLogin);
      $this->load->view($header,$profile);
  		$this->load->view('checkout');
  		$this->load->view('footerstandard');
    }
	}

  public function submitBoostPost()
  {
    $POID=$_POST['POID'];
    if ($_POST['CountBoostPost']!="define"){
      $credit_limit_pday=$_POST['CountBoostPost'];
    } else {
      $credit_limit_pday=$_POST['defineCountBoostPost'];
    };
    if ($_POST['DayBoostPost']!="define"){
      $length_date=$_POST['DayBoostPost'];
      $end_date=date("Y-m-d H:i:s",mktime(date("H"), date("i"), date("s"), date("m")  , date("d")+$_POST['DayBoostPost'], date("Y")));
    } else {
      //Wait for change code
      $d2 = DateTime::createFromFormat('d/m/Y', $_POST['defineDayBoostPost']);
      $d1 = DateTime::createFromFormat('d/m/Y', date("d/m/d"));
      $interval = $d1->diff($d2);
      $length_date=$interval->format('%d');
      $end_date=$_POST['defineDayBoostPost'];
    }
    $AdTXT=$_POST['textBoostPost'];
    $this->credit->AddCreditJob($POID,$credit_limit_pday,$end_date,$AdTXT,$length_date);
    $this->dashboard();

  }

	public function checkPromoCode(){
		$PromoCode=$_GET['PromoCode'];
		$result=$this->credit->checkPromoCode($PromoCode);
	}

	public function addPromotionCredit(){
		$PromoCode=$_POST["PromoCode"];
		$Credit=$_POST["Credit"];
		$result = $this->credit->addPromotionCredit($PromoCode,$Credit);
		echo $result;
	}

	public function controlBoostPost(){
		$Active=$_POST["Active"];
		$POID=$_POST["POID"];
		$result = $this->credit->controlBoostPost($Active,$POID);
		echo $result;
	}




}