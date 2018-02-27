<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/7 0007
 * Time: 09:42
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Image;
use Think\Pageajax;
use Think\Upload;

class CanshuController extends Controller{

    public function __construct()
    {
        parent::__construct();
        //echo session('admin_id');
        if(!session('admin_id')){
            $this->error('请登录',U('User/index'));
        }
    }

    public function tuijianjiang(){
        if(IS_GET){
            $tuijian = M('feetuijian') -> select();
            $tuijian = $tuijian['0'];
            $this -> assign('daili',$tuijian);
            $this -> display();
        }else{
            $id = $_POST['id'];
            $data['one'] = $_POST['one'];
            $data['two'] = $_POST['two'];
            if($id){
                $res = M('feetuijian') -> where(['id' => $id]) -> save($data);
            }else{
                $res = M('feetuijian') -> add($data);
            }
            if($res){
                $this -> success('修改成功','tuijianjiang');
            }else{
                $this -> error('修改失败','tuijianjiang');
            }
        }

    }

    //设置代金券金额
    public function fenhongjiang(){
        if(IS_GET){
            $fenhong = M('feefenhong') -> select();
            $fenhong = $fenhong['0'];
            $this -> assign('dj_fee',$fenhong);
            $this -> display();
        }else{
            $id = $_POST['id'];
            $data['bili'] = $_POST['bili'];

            if($id){
                $res = M('feefenhong') -> where(['id' => $id]) -> save($data);
            }else{
                $res = M('feefenhong') -> add($data);
            }
            if($res){
                $this -> success('修改成功','fenhongjiang');
            }else{
                $this -> error('修改失败','fenhongjiang');
            }
        }
    }

    //设置代金券金额
    public function huikuijiang(){
        if(IS_GET){
            $huikui = M('feehuikui') -> select();
            $huikui = $huikui['0'];
            $this -> assign('dj_fee',$huikui);
            $this -> display();
        }else{
            $id = $_POST['id'];
            $data['bili'] = $_POST['bili'];
            if($id){
                $res = M('feehuikui') -> where(['id' => $id]) -> save($data);
            }else{
                $res = M('feehuikui') -> add($data);
            }
            if($res){
                $this -> success('修改成功','huikuijiang');
            }else{
                $this -> error('修改失败','huikuijiang');
            }
        }
    }

    //设置领导奖
    public function lingdaojiang(){
        if(IS_GET){
            $lingdao = M('feelingdao') -> select();
            $lingdao = $lingdao['0'];
            $this -> assign('daili',$lingdao);
            $this -> display();
        }else{
            $id = $_POST['id'];

            if($id){
                $res = M('feelingdao') -> where(['id' => $id]) -> save($_POST);
            }else{
                $res = M('feelingdao') -> add($_POST);
            }
            if($res){
                $this -> success('修改成功','lingdaojiang');
            }else{
                $this -> error('修改失败','lingdaojiang');
            }
        }
    }



    public function shouquan(){
        if(IS_GET){
            $shouquan = M('shouquan') -> select();
            $shouquan = $shouquan['0'];
            $this -> assign('shouquan',$shouquan);
            $this -> display();
        }else{
            $id = $_POST['id'];
//            dump($_POST);
//            exit;
            if($id){
                if($_FILES['file']['error'] == 0){
                    //删除原来的。
                    //修改图片，删除原来的。
                    $path = dirname(dirname(dirname(__DIR__)));
                    $filepath = $path . $_POST['img_url'];
                    @unlink($filepath);
                    $data['img_url'] = $this -> uploadPic('shouquan');
                    $res = M('shouquan') -> where(['id' => $id]) -> save($data);
                }
            }else{
                if($_FILES['file']['error'] == 0){
                    $data['img_url'] = $this -> uploadPic('shouquan');
                    $res = M('shouquan') -> add($data);
                }

            }
            if($res){
                $this -> success('修改成功','shouquan');
            }else{
                $this -> error('修改失败','shouquan');
            }
        }
    }

    //上传图片信息，并且保存图片到服务器
    private function uploadPic($file){
        $upload = new Upload();
        $upload -> maxSize = 3145728;
        $upload -> exts = array('jpg','png','jpeg');
        $upload -> rootPath = './Uploads/';
        $upload -> savePath = $file . '/';
        $upload -> saveName = 'time';
        $info = $upload -> upload();
        if(!$info){
            $this -> error($upload -> getError());
        }else{
            $xw_img = '/Uploads/' . $info['file']['savepath'] . $info['file']['savename'];
            $image = new Image();
            $image -> open('.' . $xw_img);
            $image -> thumb(720,540) -> save('.' . $xw_img);
            return $xw_img;
        }
    }

    public function redian(){
        $this -> display();
    }

    public function redianbb(){
        $model = M('redian');
        $count=$model->count();
        $Page = $this -> setPage($count);
        $arr= $model
            -> limit($Page -> firstRow . ',' . $Page -> listRows)
            -> order('time desc')
            -> select();
        $this->assign('arr',$arr);
        $show = $Page->show();
        $this->assign('count',$count);
        $this->assign('page',$show);
        $this->assign('empty','<tr><td colspan="9" style="text-align:center;line-height:40px;">没有查询到相关信息</td></tr>');
        $this->display();
    }

    public function setPage($count){
        $pagecount = 10;
        $Page = new Pageajax($count,$pagecount);
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 '.I('p',1).' 页/共 %TOTAL_PAGE% 页 ( '.$pagecount.' 条/页 共 %TOTAL_ROW% 条)');
        return $Page;
    }


    public function create(){
        if(IS_GET){
            $this -> display();
        }else{
            $res = M('redian') -> add($_POST);
            if($res){
                $this -> success('添加成功','redian');
            }else{
                $this -> error('添加失败','redian');
            }
        }

    }

    public function del(){
        $redian_id = $_POST['id'];
        $res = M('redian') -> delete($redian_id);
        if($res){
            echo 0;
        }else{
            echo -1;
        }
    }
	
	public function yunfei(){
        if(IS_GET){
            $daili = M('yunfei') -> select();
            $daili = $daili['0'];
            $this -> assign('yunfei',$daili);
            $this -> display();
        }else{
            $id = $_POST['id'];
            $data['fee'] = $_POST['fee'];
            $data['weight'] = $_POST['weight'];
            if($id){
                $res = M('yunfei') -> where(['id' => $id]) -> save($data);
            }else{
                $res = M('yunfei') -> add($data);
            }
            if($res){
                $this -> success('修改成功','yunfei');
            }else{
                $this -> error('修改失败','yunfei');
            }
        }

    }

    public function setFen(){
	    if(IS_GET){
            $data = F('setFen','',DATA_ROOT);
            $this -> assign('data',$data);
            $this -> display();
        }else{
	        $data['number'] = $_POST['number'];
	        F('setFen',$data,DATA_ROOT);
            $this -> success('设置成功','setFen');
        }
    }

    public function setPassword(){
        if(IS_GET){
            $this -> display();
        }else{
            $pass1 = I('post.password');
            $pass2 = I('post.password1');
            if($pass1 != $pass2){
                $this -> error('两次输入的密码不一样','setPassword');
            }else{
                $data['password'] = md5($pass1);
                $res = M('admin') -> where("id = 1") -> save($data);
                if($res){
                    session(null);
                    $this -> success('设置成功','setPassword');
                }else{
                    $this -> error('未知错误','setPassword');
                }
            }
        }
    }
	
}