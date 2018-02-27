<?php
namespace Admin\Controller;
use Think\Controller;

class ExcelController extends ActionController{
	function index($info){
		//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        //创建PHPExcel对象，注意，不能少了\
		 \PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
        $objPHPExcel=new \PHPExcel();
		$objSheet=$objPHPExcel->getActiveSheet();//获得当前活动sheet
		$objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置excel文件默认水平垂直方向居中
		//$objSheet->getDefaultStyle()->getFont()->setSize(12)->setName("微软雅黑");//设置默认字体大小和格式
		//$objSheet->getStyle("A1:H1")->getFont()->setSize(14)->setBold(true);//设置第一行字体大小和加粗
		//$objSheet->getStyle("A1:H1")->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_WHITE);;//设置第一行文字颜色
		$objSheet->getColumnDimension("A")->setAutoSize(true);      
		$objSheet->getColumnDimension("G")->setAutoSize(true); 
		//$objSheet->getDefaultRowDimension()->setRowHeight(20);//设置默认行高
		//$objSheet->getRowDimension(1)->setRowHeight(50);//设置第一行行高
		//$objSheet->getStyle("A1:H1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3c8dbc');//填充第一行背景
		$objSheet->setTitle("订单统计");//给当前活动sheet起个名称
//		$objSheet-> setCellValue('A1','发件姓名')-> setCellValue('B1','发件手机')
//            ->setCellValue("C1","收件姓名")
//            ->setCellValue("D1","收件手机")
//            ->setCellValue("E1","收件街道")
//            ->setCellValue("F1","订单物品");//填充数据
		$objSheet-> setCellValue('A1','订单编号')
            -> setCellValue('B1','收件人姓名（必填）')
            ->setCellValue("C1","收件人手机（二选一）")
            ->setCellValue("D1","收件人电话（二选一）")
            ->setCellValue("E1","收件人地址（必填）")
            ->setCellValue("F1","商品信息")
            ->setCellValue("G1","寄件人姓名")
            ->setCellValue("H1","寄件人手机（二选一）")
            ->setCellValue("I1","寄件人电话（二选一）")
            ->setCellValue("J1","寄件人地址");//填充数据
		//dump($info);exit;
		$k =2;
		foreach($info as $key=>$val){
			if(strpos($val['nickname'],'=') === 0){
				$val['nickname'] = "'".$val['nickname'];
			}
//			$objSheet
//                -> setCellValue('A'.$k,$val['fh_name'])
//                -> setCellValue('B'.$k,$val['fh_tel'])
//                -> setCellValue("C".$k,$val['address']['username'])
//                ->setCellValue("D".$k,' '.$val['address']['telphone'])
//                ->setCellValue("E".$k,$val['address']['address'])
//                ->setCellValue("F".$k,'【'.$val['pay_more'].'】'.$val['product'])  ;//填充数据
            $objSheet
                -> setCellValue('A'.$k,time())
                -> setCellValue('B'.$k,$val['address']['username'])
                -> setCellValue("C".$k,$val['address']['telphone'])
                -> setCellValue("D".$k,'')
                -> setCellValue("E".$k,$val['address']['city'] . $val['address']['address'])
                -> setCellValue("F".$k,'【'.$val['pay_more'].'】'.$val['product'])
                -> setCellValue("G".$k,$val['fh_name'])
                -> setCellValue("H".$k,$val['fh_tel'])
                -> setCellValue("I".$k,'')
                -> setCellValue("J".$k,'');//填充数据
			$k++;
		}
		$objWriter=\PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
		//$objWriter->save(APP_PATH."/excel/export_1.xls");//保存文件
		$this->browser_export('Excel','已付款订单统计.xls');//输出到浏览器
		$objWriter->save("php://output");
	
	}

    function index_order($info){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        //创建PHPExcel对象，注意，不能少了\
        \PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
        $objPHPExcel=new \PHPExcel();
        $objSheet=$objPHPExcel->getActiveSheet();//获得当前活动sheet
        $objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置excel文件默认水平垂直方向居中
        //$objSheet->getDefaultStyle()->getFont()->setSize(12)->setName("微软雅黑");//设置默认字体大小和格式
        //$objSheet->getStyle("A1:H1")->getFont()->setSize(14)->setBold(true);//设置第一行字体大小和加粗
        //$objSheet->getStyle("A1:H1")->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_WHITE);;//设置第一行文字颜色
        $objSheet->getColumnDimension("A")->setAutoSize(true);
        $objSheet->getColumnDimension("G")->setAutoSize(true);
        //$objSheet->getDefaultRowDimension()->setRowHeight(20);//设置默认行高
        //$objSheet->getRowDimension(1)->setRowHeight(50);//设置第一行行高
        //$objSheet->getStyle("A1:H1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3c8dbc');//填充第一行背景
        $objSheet->setTitle("订单统计");//给当前活动sheet起个名称
//		$objSheet-> setCellValue('A1','发件姓名')-> setCellValue('B1','发件手机')
//            ->setCellValue("C1","收件姓名")
//            ->setCellValue("D1","收件手机")
//            ->setCellValue("E1","收件街道")
//            ->setCellValue("F1","订单物品");//填充数据
        $objSheet-> setCellValue('A1','订单编号')
            -> setCellValue('B1','收件人姓名（必填）')
            ->setCellValue("C1","收件人地址（必填）")
            ->setCellValue("D1","商品信息");
        //dump($info);exit;
        $k =2;
        foreach($info as $key=>$val){
            if(strpos($val['nickname'],'=') === 0){
                $val['nickname'] = "'".$val['nickname'];
            }
//			$objSheet
//                -> setCellValue('A'.$k,$val['fh_name'])
//                -> setCellValue('B'.$k,$val['fh_tel'])
//                -> setCellValue("C".$k,$val['address']['username'])
//                ->setCellValue("D".$k,' '.$val['address']['telphone'])
//                ->setCellValue("E".$k,$val['address']['address'])
//                ->setCellValue("F".$k,'【'.$val['pay_more'].'】'.$val['product'])  ;//填充数据
            $objSheet
                -> setCellValue('A'.$k,$val['order_sn'])
                -> setCellValue('B'.$k,$val['name'])
                -> setCellValue("C".$k,$val['address'])
                -> setCellValue("D".$k,$val['good_name'] . ','.$val['good_spec'] . ',数量：' . $val['good_num']);

            $k++;
        }
        $objWriter=\PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
        //$objWriter->save(APP_PATH."/excel/export_1.xls");//保存文件
        $this->browser_export('Excel','已付款订单统计.xls');//输出到浏览器
        $objWriter->save("php://output");

    }
	
	function detail($info,$username){
		//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel=new \PHPExcel();
		$objSheet=$objPHPExcel->getActiveSheet();//获得当前活动sheet
		$objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置excel文件默认水平垂直方向居中
		$objSheet->getDefaultStyle()->getFont()->setSize(10)->setName("微软雅黑");//设置默认字体大小和格式
		$objSheet->getStyle("A1:H1")->getFont()->setSize(12)->setBold(true);//设置第一行字体大小和加粗
		$objSheet->getStyle("A1:H1")->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_WHITE);//设置第一行文字颜色
		$objSheet->getColumnDimension("D")->setAutoSize(true);      
		$objSheet->getColumnDimension("E")->setAutoSize(true);      
		$objSheet->getColumnDimension("G")->setAutoSize(true);      
		$objSheet->getColumnDimension("C")->setWidth(300);
		$objSheet->getDefaultRowDimension()->setRowHeight(25);//设置默认行高
		$objSheet->getRowDimension(1)->setRowHeight(50);//设置第一行行高
		$objSheet->getStyle("A1:H1")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3c8dbc');//填充第一行背景
		$objSheet->setTitle("订单表");//给当前活动sheet起个名称
		$objSheet->setCellValue("A1","订单编号")->setCellValue("B1","订单金额")->setCellValue("C1","商品名称")->setCellValue("D1","用户名字")->setCellValue("E1","收货人")->setCellValue("F1","联系方式")->setCellValue("G1","收货地址")->setCellValue("H1","下单时间");//填充数据
		//$token=session('token');
		//$info = M('users')->where(" token = '$token' ")->select();
		//dump($info);//exit;
		$k =2;
		foreach($info as $key=>$val){			
			$objSheet->setCellValue("A".$k,$val['order_sn'])->setCellValue("B".$k,$val['total_fee'])->setCellValue("C".$k,$val['pay_more'])->setCellValue("D".$k,$val['user_id']."、".$val['username'])->setCellValue("E".$k,$val['address']['username'])->setCellValue("F".$k,$val['address']['telphone'])->setCellValue("G".$k,$val['address']['address'])->setCellValue("H".$k,$val['time']);//填充数据
			// if($val['is_true'] == '未发货'){
				// $objSheet->getStyle("F".$k)->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_RED);//填充未发货背景
			// }
			
			$k++;
		}
		$objWriter=\PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
		//$objWriter->save($dir."/export_1.xls");//保存文件
		$this->browser_export('Excel5',$username.'订单统计.xls');//输出到浏览器
		$objWriter->save("php://output");

	}
	
	function browser_export($type,$filename){
		if($type=="Excel5"){
				header('Content-Type: application/vnd.ms-excel');//告诉浏览器将要输出excel03文件
		}else{
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
		}
		header('Content-Disposition: attachment;filename="'.$filename.'"');//告诉浏览器将输出文件的名称
		header('Cache-Control: max-age=0');//禁止缓存
	}
	
}
?>