<?php
class PublicAction extends Action {
	//login page
	public function login() {
		if(!isset($_SESSION[C('ADMIN_AUTH_KEY')])) {
			$this->display();
		}else{
			$this->redirect('/Admin/Public/index');
		}
	}
	
	//home page
	public function index() {
		if(isset($_SESSION[C('ADMIN_AUTH_KEY')])) {
			$this->display();
		}else{
			$this->redirect('/Admin/Public/login');
		}
	}
	

    public function register(){
        $this->display();
    }

    public function edit(){
        $this->display();   
    }

    public function loadProfile(){
        $id = $_SESSION[C('ADMIN_AUTH_KEY')];

        $User = D('User');
        $profile = $User->getUserInfoById($id);

        if ($profile === false) {
            header("HTTP/1.0 200 OK");
            jsonecho(0, L('load_data_failed'));
        } else if (empty($profile)) {
            header("HTTP/1.0 200 OK");
            jsonecho(2, L('no_data'));
        } else {
            header("HTTP/1.0 200 OK");
            echo json_encode(array(status => 1, data => $profile));
        }
    }


    private function phpDateToMysqlDate($phpdate) {
        $day = substr($phpdate,0,2);
        $month = substr($phpdate,3,2);
        $year = substr($phpdate,6,4);
        return $year . '-' . $month . '-' . $day;
    }

    public function doRegister(){
        $data['account'] = $this->_post('account');
        $data['username'] = $data['account'];
        $data['email'] = $this->_post('email');
        $data['password'] = md5($this->_post('password'));
        $data['groupid'] = 2;

        $Admin = D('Admin');

        if(empty($data['account']) || empty($data['password']) || empty($data['email'])){
            $this->error(L('empty_require'));
        }

        if($Admin->accountExisted($data['account'])) {
            $this->error(L('email_existed'));
        }

        if ($uid = $Admin->add($data)) {
            $User = D('User');
            $data = array();
            $data['firstname'] = $this->_post('firstname');
            $data['secondname'] = $this->_post('secondname');
            $data['lastname'] = $this->_post('lastname');
            $data['gender'] = $this->_post('gender');
            $data['avatar'] = $this->_post('hidAvatar');
            $data['birthday'] = $this->phpDateToMysqlDate($this->_post('birthday'));
            $data['pwdhint'] = $this->_post('pwdhint');
            $data['createtime'] = date('Y-m-d H:i:s', time());
            $data['createdby'] = 2;
            $data['statusid'] = 1;
            $data['paid'] = 0;
            $data['aid'] = $uid; 

            if ($aid = $User->add($data)) {
                $_SESSION[C('USER_GROUP')] = 'visiter';
                $_SESSION[C('ADMIN_AUTH_KEY')] = $uid;

                $_SESSION['account'] = $this->_post('account');
                $_SESSION['loginUserName'] = $this->_post('account');
                $_SESSION['email'] = $this->_post('email');
                $_SESSION['lastLoginTime'] = date('Y-m-d H:i:s', time());
                $_SESSION['login_count'] = 1;
                
                $this->success(L('Register finished'),'/Admin/');
            }else{
                $this->error(L('add_failed'));    
            }
        } else {
            $this->error(L('add_failed'));
        }
    }

    public function doEdit(){
        $data['firstname'] = $this->_post('firstname');
        $data['secondname'] = $this->_post('secondname');
        $data['lastname'] = $this->_post('lastname');
        $data['gender'] = $this->_post('gender');
        $data['avatar'] = $this->_post('hidAvatar');
        $data['birthday'] = $this->phpDateToMysqlDate($this->_post('birthday'));
        $data['pwdhint'] = $this->_post('pwdhint');
        $condition['aid'] = $_SESSION[C('ADMIN_AUTH_KEY')];

        $User = D('User');
        $result = $User->where($condition)->save($data);

        if($result !== false){
            $this->success(L('Edit finished'),'/Admin/Public/edit');
        }else{
            $this->error(L('edit_failed'));
        }
    }

    //logout
    public function logout() {
        unset($_SESSION[C('ADMIN_AUTH_KEY')]);
        unset($_SESSION);
   		session_destroy();
        $this->redirect('/Admin/Public/login');
    }

    //login check
    public function checkLogin() {
        if(empty($_POST['account'])) {
			header("HTTP/1.0 200 OK");
        	jsonecho(0, L('pls_enter_account'));
        }elseif (empty($_POST['password'])){
			header("HTTP/1.0 200 OK");
            jsonecho(0, L('wrong_password'));
        }
        
        $map = array();
        $map['account']	= $_POST['account'];
        $map["status"]	=	array('gt',0);

        $authInfo = M('Admin')->where($map)->find();
        if(false === $authInfo) {
			header("HTTP/1.0 200 OK");
            jsonecho(0, L('no_account'));
        }
        if($authInfo['password'] != md5($_POST['password'])) {
			header("HTTP/1.0 200 OK");
            jsonecho(0, L('wrong_password'));
        }

        $_SESSION[C('ADMIN_AUTH_KEY')] =	$authInfo['id'];
        if($authInfo['groupid'] != 1){
            $_SESSION[C('USER_GROUP')] = 'visiter';
        }

        $_SESSION['account'] = $authInfo['account']; 
        $_SESSION['loginUserName'] = $authInfo['username'];
        $_SESSION['email'] = $authInfo['email'];
        $_SESSION['lastLoginTime'] = $authInfo['last_login_time'];
        $_SESSION['login_count'] = $authInfo['login_count'];
        
        //save login information
        $User =	M('Admin');
        $ip	= get_client_ip();
        $time =	time();
        $data = array();
        $data['id']	= $authInfo['id'];
        $data['last_login_time'] = $time;
        $data['login_count'] = array('exp','login_count+1');
        $data['last_login_ip'] = $ip;
		$condition['id'] = $data['id'];
		$User->where($condition)->data($data)->save();
		header("HTTP/1.0 200 OK");
        jsonecho(1);
        
    }
    
    //upload image
    public function uploadImg() {
    	if (!empty($_FILES)) {
    		$thumbPrefix = $_POST['prefix'];
    		$thumbW = $_POST['thumbW'];
    		$thumbH = $_POST['thumbH'];
    		$this->_uploadImg($thumbPrefix, $thumbW, $thumbH);
    	}
    }
    
    //upload image
    protected function _uploadImg($thumbPrefix,$thumbW,$thumbH) {
    	import('@.ORG.Util.UploadFile');
    	$upload = new UploadFile();
    	$upload->maxSize            = 3292200;
    	$upload->allowExts          = explode(',', 'jpg,gif,png,jpeg');
    	$upload->savePath           = './Uploads/';
    	$upload->thumb              = true;
    	$upload->imageClassPath     = '@.ORG.Util.Image';
    	$upload->thumbPrefix        = $thumbPrefix; 
    	$upload->thumbMaxWidth      = $thumbW;
    	$upload->thumbMaxHeight     = $thumbH;
    	$upload->saveRule           = 'uniqid';
    	$upload->thumbRemoveOrigin  = false; //delete the original image
    	if (!$upload->upload()) {
    		$this->error($upload->getErrorMsg());
    	} else {
    		$uploadList = $upload->getUploadFileInfo();
    	}
    	if ($list !== false) {
			header("HTTP/1.0 200 OK");
    		echo json_encode(array(status => 1, imgName =>$uploadList[0]['savename']));
    	} else {
			header("HTTP/1.0 200 OK");
    		echo json_encode(array(status => 0));
    	}
    }
    
   

    public function profile() {
        $this->checkUser();
        $User	 =	 M("User");
        $vo	=	$User->getById($_SESSION[C('USER_AUTH_KEY')]);
        $this->assign('vo',$vo);
        $this->display();
    }

    public function verify() {
        $type	 =	 isset($_GET['type'])?$_GET['type']:'gif';
        import("ORG.Util.Image");
        Image::buildImageVerify(4,1,$type);
    }
}