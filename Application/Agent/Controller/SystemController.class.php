<?php
namespace Agent\Controller;
use Think\Controller;

class SystemController extends ActionController{
	function __construct(){
		parent::__construct();
		//echo session('admin_id');
		if(!session('admin_id')){
			$this->error('请登录',U('User/index'));
		}
	}
	
	function index(){
		if($_POST){
			$wap_color = array('main'=>$_POST['main'],'self'=>$_POST['self']);
			F('wap_color',$wap_color,DATA_ROOT);
			$this -> success('系统配置信息已保存');
		}else{
			$this->assign('wap_color',F('wap_color','',DATA_ROOT));
			$this->display();
		}
	}
	function template(){
		if($_POST){
			F('template_info',$_POST,DATA_ROOT);
			$this->success("保存配置信息成功");
		}else{
			$template_info = F('template_info','',DATA_ROOT);//dump($template_info);
			$this -> assign('template_info',$template_info);
			$this->display();
		}
		
	}
	/* 二维码设置视图 */
	function qr(){
		if($_POST){
			$upload = new \Think\Upload();// 实例化上传类  
			$upload->rootPath = './Uploads/';  
			$upload->maxSize   =     3145728 ;// 设置附件上传大小 
			$upload->autoSub = false;   
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
			$upload->savePath  =      ''; // 设置附件上传目录    // 上传文件  
			$upload->autoSub = true;
			$upload->subName = date("Ymd");
			$imginfo   =   $upload->upload();
			if($imginfo){
				$_POST['pic_url'] = "Uploads/".$imginfo['image1']["savepath"].$imginfo['image1']["savename"];
			}
			if($_POST[qr] == null){
				$res = M('qrset') -> add($_POST);
			}else{
				$res = M('qrset') -> where(" id = '$_POST[qr]' ") -> save($_POST);
			}
			if($res){
			    //删除生成好的图片，重新生成
                $path = './Public/qr_path';
                @unlink($path);
            }
			M('qrcode') -> where(" update_time > 0 ") -> setField('update_time','0');
			$this->success("保存成功");
		}else{
			$qrset = M('qrset') -> select();
			$qrset[0]['pic_url'] = substr($qrset[0]['pic_url'],1);
			$this->assign("qrset",$qrset);
			$this->display();
		}
		
	}

    /* 二维码设置视图 */
    function shouquan(){
        if($_POST){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->rootPath = './Uploads/';
            $upload->maxSize   =     3145728 ;// 设置附件上传大小
            $upload->autoSub = false;
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->savePath  =      ''; // 设置附件上传目录    // 上传文件
            $upload->autoSub = true;
            $upload->subName = date("Ymd");
            $imginfo   =   $upload->upload();
            if($imginfo){
                $_POST['pic_url'] = "Uploads/".$imginfo['image1']["savepath"].$imginfo['image1']["savename"];
            }
            if($_POST[qr] == null){
                $res = M('qrsq') -> add($_POST);
            }else{
                $res = M('qrsq') -> where(" id = '$_POST[qr]' ") -> save($_POST);
            }
            if($res){
                //删除生成好的图片，重新生成
                $path = './Public/qr_path';
                @unlink($path);
            }
            M('qrcode') -> where(" update_time > 0 ") -> setField('update_time','0');
            $this->success("保存成功");
        }else{
            $qrset = M('qrsq') -> select();
            $qrset[0]['pic_url'] = substr($qrset[0]['pic_url'],1);
            $this->assign("qrset",$qrset);
            $this->display();
        }

    }

	function imgtest(){
		$info = M('qrset')->select();
		if(!$info){
			$text = "发现您还没有设置推广二维码相关参数，请先行设置后再查看";
			$this->assign("text",$text);
		}else{
			$weixin=A("Wxapi/Qrimg");
			$res = $weixin->index(0,0,"未知用户");
			$url = __ROOT__."/".$res;
			$this->assign("url",$url);
			
		}
		$this->display();
	}
	
	/* 权限组列表视图 */
	function admin(){
		/* 查询权限组 */
		$auth_group = M('auth_group');
		$auth_group_access = M('auth_group_access');
		
		$pagecount = 10;
		$count = $auth_group-> count();
		$Page = new \Think\Page($count,$pagecount);
		$group_info=$auth_group->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		$show = $Page->show();
		$this->assign('page',$show);
		foreach($group_info as $k=>$v){
			$group_info[$k]['num'] = $auth_group_access -> where(" group_id = '$v[id]' ")->count();
		}
		$this->assign('group_info',$group_info);
		$this->display();
	}
	/* 新增或修改权限组 */
	function admin_add(){
		$auth_rule = M('auth_rule');
		$rule_info = $auth_rule -> order("code asc") -> select();
		if(I('id')){
			$id = I('id');
			$auth_group = M('auth_group');
			$group_info = $auth_group -> where("id = '$id' ") -> find();
			$this->assign('group_info',$group_info);
			$arr = explode(',',$group_info['rules']);
			foreach($rule_info as $k=>$v){
				foreach($arr as $kk ){
					if($kk == $v['id']){
						$str = true;
						break;
					}else{
						$str = false;
					}
				}
				if($str){
					$rule_info[$k]['check'] = 1;
				}else{
					$rule_info[$k]['check'] = 0;
				}
			}
		}
		
		
		$this->assign('id',$id);
		$this->assign('rule_info',$rule_info);
		$this->display();
	}
	/* 保存权限组信息 */
	function admin_save(){
		$role = array_keys(I());
		$rules = '';
		foreach($role as $v){
			if(strstr($v,'rule') ){
				if($rules == ''){
					$rules = I($v);
				}else{
					$rules = $rules.','.I($v);
				}
			}
		}
		$array = array(
			'rules'=>$rules,
			'title'=>I('title'),
			'status'=>I('status'),
		);
		if(I('id') == null){
			M('auth_group') -> add($array);
		}else{
			$id = I('id');
			M('auth_group')-> where(" id = '$id' ") ->save($array);
		}
		$this->success('保存权限组信息成功',admin);
	}
	/* 删除权限组表 */
	function admin_del(){
		$id = $_POST['id'];
		if($id == null || $id == 1){$arr['success'] = 0;$arr['info'] = '默认管理员和超级管理员群组不能删除！';echo json_encode($arr);exit;}
		/* 检查该组下是否有用户 */
		$auth_group_access = M('auth_group_access');$arr = array();
		$access = $auth_group_access -> where("group_id = '$id'") -> find();
		if($access != null){$arr['success'] = 0;$arr['info'] = '该权限组下有管理员，请删除管理员后再删除！';echo json_encode($arr);exit;}
		$auth_group = M('auth_group');
		$auth_group -> where(" id = '$id' ") -> delete();
		$arr['success'] = 1;
		echo json_encode($arr);
		
	}
	
	/* 管理员列表视图 */
	function user_list(){
		$admin = M('admin');$auth_group_access = M('auth_group_access');$auth_group = M('auth_group');
		$pagecount = 10;
		$count = $admin-> count();
		$Page = new \Think\Page($count,$pagecount);
		$user_list=$admin->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
		$Page->setConfig('first','首页');
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('last','尾页');
		$Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
		$show = $Page->show();
		$this->assign('page',$show);
		foreach($user_list as $k=>$v){
			$admin_id = $v['id'];
			$group_id = $auth_group_access->getFieldByUid($admin_id,'group_id');
			$user_list[$k]['title'] = $auth_group -> getFieldById($group_id,'title');
			$user_list[$k]['last_time'] = date("Y-m-d H:i:s",$user_list[$k]['last_time']);
		}

		
		$this->assign('user_list',$user_list);
		$this->display();
	}
	/* 添加、编辑管理员 */
	function user_add(){
		$auth_group = M('auth_group');
		if(I('id')){
			$id = I('id');
			$user_info = M('admin')-> where("id='$id'")->find();
			$group_id = M('auth_group_access') -> getFieldByUid($id,'group_id');
			$this->assign('group_id',$group_id);
			$this->assign('user_info',$user_info);
		}
		$group_list = $auth_group -> select();
		
		$this->assign('group_list',$group_list);
		$this->display();
	}
	function user_save(){
		$username = I('username');if($username == null){$this->error('管理员账号不能为空！',admin);exit;}
		$pwd1 = I('pwd1');if($pwd1 == null){$this->error('管理员密码不能为空！',admin);exit;}
		$pwd2 = I('pwd2');if($pwd1 != $pwd2){$this->error('两次密码不一致！',admin);exit;}
		$group_id = I('group_id');if($group_id == null){$this->error('权限组不能为空！',admin);exit;}
		/* 保存用户信息 */
		if(I('id')){
			$id = I('id');
			$user_data = array('password'=>md5($pwd1));
			M('admin')->where("id='$id'")->save($user_data);$res=true;
		}else{
			$user_data = array('username'=>$username,'password'=>md5($pwd1));
			$res = M('admin')->add($user_data);
		}
		if($res){
			/* 记录用户权限组信息 */
			$auth_group_access = M('auth_group_access');
			if(I('old_group_id') && $id){
				if(I('old_group_id') != $group_id){
					$auth_group_access->where("uid = '$id'")->setField('group_id',$group_id);
				}
			}else{
				$access_data = array('uid'=>$res,'group_id'=>$group_id);
				$auth_group_access->add($access_data);
			}
		}
		$this->success('保存管理员信息成功！',admin);
	}
	/* 删除管理员 */
	function user_del(){
		$id = $_POST['id'];
		if($id == null || $id == 1){$arr['success'] = 0;$arr['info'] = '默认管理员和超级管理员群组不能删除！';echo json_encode($arr);exit;}
		/* 检查该组下是否有用户 */
		$admin = M('admin');$arr = array();
		$access = $admin -> where("id = '$id'") -> delete();
		$arr['success'] = 1;
		echo json_encode($arr);
	}
}