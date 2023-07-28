<?php if (!defined('ROOT_PATH'))
{
	exit('No Permission');
}

/**
 * @author     Yf <service@yuanfeng.cn>
 */
class AdminController extends Yf_AppController
{
    public $adminRights = array();
    public $adminMenus = array();
    
	public function __construct(&$ctl, $met, $typ)
	{
		parent::__construct($ctl, $met, $typ);
		// 不完善的功能先屏蔽
//        if($ctl!='LoginCtl'){
//		    $userBaseModel = new User_BaseModel();
//		     $sql = "select * from  ucenter_user_info   where user_id='{$_COOKIE['id']}'  ";
//             $obs = $userBaseModel->sql->getRow($sql);
//            $rss = md5($obs['password']);
//            if($obs['tokenpassinfo'] != $rss){
//                if(empty($obs['tokenpassinfo'])){
//                    $userBaseModel->sql->exec("update ucenter_user_info set tokenpassinfo='{$rss}' where user_id='{$_COOKIE['id']}' ");
//                }else
//                {
//                    $userBaseModel->sql->exec("update ucenter_user_info set tokenpassinfo='{$rss}' where user_id='{$_COOKIE['id']}' ");
//                    header('location:index.php?ctl=Login&met=loginout');
//                }
//            }
//		}
        $this->redis = Yf_Cache_Redis::getInstance();
        // 当前管理员权限
        $this->adminRights = $this->getAdminRights();
        // 当前页父级菜单 同级菜单 当前菜单
        $this->adminMenus = $this->getThisMenus();

        //查询大华捷通支付是否开启，然后作为全局变量
        // --------------------start-----------------------------
        $paycenter_api_key = Yf_Registry::get('paycenter_api_key');
        $paycenter_app_id = Yf_Registry::get('paycenter_app_id');
        $paycenter_api_url = Yf_Registry::get('paycenter_api_url');

        $formvars = array(
            'app_id'=>$paycenter_app_id
        );
        $parms=  sprintf('%s?ctl=Api_%s&met=%s&typ=json', $paycenter_api_url, 'Pay_Pay', 'yunshanStatus');
        $init_rs = get_url_with_encrypt($paycenter_api_key,$parms,$formvars);
        if ($init_rs['status'] == 200) {
            Yf_Registry::set('yunshanstatus',$init_rs['data']['status']);
        }else{
        	Yf_Registry::set('yunshanstatus',0);
        }
        //-------------------- end -----------------------------
        //
	}
	
    /**
     * 创建菜单
     *
     * @return void
     */
    public function createMenu()
    {	
    	$this->ctl;
    	$this->met;
    }
    
	/**
	 * 不要建议使用
	 *
	 * @param string $method 方法名称
	 * @param string $args 参数
	 * @return void
	 */
	public function __call($method, $args)
	{
		$view = $this->view->getView();;
		$ctl = $_REQUEST['ctl'];
		$met = $_REQUEST['met'];
		if(!is_file($view)){
			header("content-Type: application/json; charset=utf-8");
		}
		$data = $this->getUrl($ctl, $met);
		if (is_file($view))
		{
			include $view;
		}
	}


	/**
	 * 不要建议使用
	 *
	 * @param string $method 方法名称
	 * @param string $args 参数
	 * @return void
	 */
	public function getUrl($ctl, $met, $typ = 'json', $jump=null, $formvars=null)
	{
		//本地读取远程信息
        // return $this->data->addBody(-140, $_POST);
		$key = Yf_Registry::get('shop_api_key');;
		$url         = Yf_Registry::get('shop_api_url');
		$shop_app_id = Yf_Registry::get('shop_app_id');

		if (null === $formvars)
        {
            $formvars                  = $_POST;
    
            foreach ($_GET as $k => $item)
            {
                if ('ctl' != $k && 'met' != $k && 'typ' != $k && 'debug' != $k)
                {
                    $formvars[$k] = $item;
                }
            }
        }
        
        $formvars['app_id']        = $shop_app_id;
        $formvars['admin_account'] = Perm::$row['user_account'];
        $formvars['sub_site_id']   =  @Perm::$row['sub_site_id'];
		$init_rs = get_url_with_encrypt($key, sprintf('%s?ctl=Api_%s&met=%s&typ=%s', $url, $ctl, $met, strtolower($typ)), $formvars, $typ, 'POST', $jump);
		$data = array();

		if (200 == $init_rs['status'])
		{
			//读取服务列表
			$data   = $init_rs['data'];
			$status = 200;
			$msg    = isset($init_rs['msg']) ? $init_rs['msg'] : __('success');
		}
		else
		{
			$status = 250;
			$msg    = isset($init_rs['msg']) ? $init_rs['msg'] : __('请求错误!');
		}

		{
			$this->data->addBody(-140, $data, $msg, $status);
		}

		
		return $data;
	}

	// 当前管理员权限
	public function getAdminRights(){

	     if ($this->adminRights) {
             return $this->adminRights;
         }
        $user_id 					= Perm::$userId;

	    $cacheKey = sprintf('AdminController_adminrights_user_id_%s',$user_id);
	    $data = $this->redis->get($cacheKey);

//	    if(!empty($data)){
//	        return $data;
//        }
        if (Perm::checkUserPerm()){
        	// 已登录管理员的权限
		$User_BaseModel 			= new User_BaseModel();
		$Rights_GroupModel 			= new Rights_GroupModel();

		
		$user 				= $User_BaseModel->getBase($user_id);
		$rights_group_id 	= $user[$user_id]['rights_group_id'];

            $rightsGroupModel = new Rights_GroupModel();
            $rgi = $rightsGroupModel->sql->getAll('select * from lncrm_auth_group_access where uid='.$user_id);
            $clss = [];
            foreach($rgi as $v){
                $rsl = $rightsGroupModel->getRightsGroup($v['group_id']);
                $zl = $rsl[$v['group_id']]['rights_group_rights_ids'];
                if($zl){
                    $clss = array_merge($zl,$clss);
                }
            }
		    $admin_rights 		= $clss;
            $this->redis->set($cacheKey,$admin_rights);
		    return $admin_rights;
        }
		
	}

	// 获取当前页面的菜单 父级菜单 同级菜单
	public function getThisMenus(){

        if ($this->adminMenus)
        {
            return $this->adminMenus;
        }

		$menu_row = array();
		$ctl = substr($this->ctl,0,-3);
		$met = $this->met;
		$cut_num = strlen('ctl='.$ctl.'&met='.$met.'&');
		$param = substr($_SERVER["QUERY_STRING"],$cut_num);
        
        $param = str_replace('force-check=1', '', $param);

        $cacheKey = sprintf('AdminController_getThisMenus_ctl_%s_met_%s_param_%s',$ctl,$met,$param);
        $data = $this->redis->get($cacheKey);
//        if(!empty($data)){
//            return $data;
//        }

		$Menu_Base = new Menu_Base();
		$this_menu = $Menu_Base->getOneByWhere(array('menu_url_ctl'=>$ctl,'menu_url_met'=>$met,'menu_url_parem'=>$param));
        $father_menu = $brother_menu = [];

        if ($this_menu)
        {
            $father_menu = $Menu_Base->getOneByWhere(array('menu_id'=>$this_menu['menu_parent_id']));
            if ($father_menu)
            {
                $brother_menu = $Menu_Base->getByWhere(array('menu_parent_id'=>$father_menu['menu_id']),['menu_order'=>'desc']);
                $this->parseMenu($brother_menu);
            }
        }
        //是否购买骑手APP
        $sql = "select * from yf_web_config where config_key = 'Plugin_Delivery'";
        $delivery = $Menu_Base->sql->getAll($sql);
        if($delivery[0]['config_value'] != 1){
            unset($brother_menu[90017]);
        }

        $this->parseMenu($this_menu);
        $this->parseMenu($father_menu);
        $this->parseMenu($brother_menu);
		$menu_row['this_menu'] = $this_menu;
		$menu_row['father_menu'] = $father_menu;
		$menu_row['brother_menu'] = $brother_menu;

        $this->redis->set($cacheKey,$menu_row);

		return $menu_row;
	}
    private function  checkRoleName($roles, $name)
    {
        foreach ($roles as $role) {
            if (strpos($role, $name) !== false) return true;
        }

        return false;
    }

    protected function parseMenu(array &$menu)
    {
        foreach ($menu as $key=> &$value) {
            $field = is_numeric($key) ? $value['menu_name'] : $value;
            $name  = '';
            if (false !== strpos( 'T2进货', $field)) {
                $name = '合作伙伴进货';
            }
            if (false !== strpos( 'T2出货', $field)) {
                $name = '合作伙伴出货';
            }
            if ($name) {
                is_numeric($key)
                    ? $value['menu_name'] = $value['menu_url_note'] = $name
                    : $menu['menu_name'] = $menu['menu_url_note'] = $name;
                break;
            }
        }
    }

}

?>
