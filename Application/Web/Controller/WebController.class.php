<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/9 0009
 * Time: 14:49
 */
namespace Web\Controller;
use Think\Controller;

class WebController extends Controller{
    public function index(){
        $this -> display();
    }

    /**
     * 䣽酒大师
     */
    public function master(){
        $this->display();
    }

    /**
     * 酿造工艺
     */
    public function tech(){
        $this -> display();
    }

    /**
     * 䣽酒的故事
     */
    public function story(){
        $this -> display();
    }

    /**
     * 公司介绍
     */
    public function about(){
        $this -> display();
    }


    /**
     * 关于我们
     */
    public function women(){
        $this -> display();
    }

    /**
     * 宣传视频
     */
    public function xcsp(){
        $this -> display();
    }
    /**
     * 免责声明
     */
    public function mzsm(){
        $this -> display();
    }

}