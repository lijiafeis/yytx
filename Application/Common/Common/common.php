<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22 0022
 * Time: 09:38
 */

function settel($num){
//    $num = "13966778888"
    $str = substr_replace($num,'****',3,4);
    return $str;
}