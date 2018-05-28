<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 系统邮件发送函数
 * @param string $tomail 接收邮件者邮箱
 * @param string $name   发送者名称
 * @param string $subject 邮件主题
 * @param string $body 	 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null) {
	$mail             = new \PHPMailer\PHPMailer\PHPMailer();          //实例化PHPMailer对象
	$mail->CharSet    = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
	$mail->IsSMTP();                    // 设定使用SMTP服务
	$mail->SMTPDebug  = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
	$mail->SMTPAuth   = true;             // 启用 SMTP 验证功能
	$mail->SMTPSecure = 'ssl';          // 使用安全协议
	$mail->Host       = "smtp.qq.com"; // SMTP 服务器
	$mail->Port       = 465;                  // SMTP服务器的端口号
	$mail->Username   = "hzm009@qq.com";    // SMTP服务器用户名
	$mail->Password   = "xxxx";     // SMTP服务器密码
	$mail->SetFrom('hzm009@qq.com', $name);
	$replyEmail       = '';                   //留空则为发件人EMAIL
	$replyName        = '';                    //回复名称（留空则为发件人名称）
	$mail->AddReplyTo($replyEmail, $replyName);
	$mail->Subject    = $subject;			// 邮件主题
	$mail->MsgHTML($body);				// 邮件内容
	$mail->AddAddress($tomail);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}


/**
 * excel表格导出
 * @param string $fileName 文件名称
 * @param array $headArr 表头名称
 * @param array $data 要导出的数据 
 * */
function excel_export($fileName = '', $headArr = [], $data = []){
    if( empty($fileName) ) $fileName = time();

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    if( empty($headArr) ){
        $headArr = array_keys($data[0]);
    }
    
    $sheet->fromArray($headArr);
    $sheet->fromArray($data,null,'A2');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
    //header('Content-Type:application/vnd.ms-excel');//告诉浏览器将要输出Excel03版本文件
    header("Content-Disposition: attachment;filename='{$fileName}.xlsx'");//告诉浏览器输出浏览器名称
    header('Cache-Control: max-age=0');//禁止缓存
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

/**
 * excel表格导入
 * @param array $file 文件上传 eg: $_FILE['name']
 * @param array $data 导入的数据 
 * */
function excel_import($file=''){
    $filename = $file['tmp_name'];

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

    if( !$reader->canRead($filename) ){
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        if( !$reader->canRead($filename) ) {
            echo "<script>alert('excel读取失败，只能读取xls、xlsx格式!');history.go(-1)</script>"; exit;
        }
    }

    $obj = $reader->load($filename);

    $sheet = $obj->getSheet(0);

    $highestRow = $sheet->getHighestRow();       // 取得总行数
    $highestColumn = $sheet->getHighestColumn(); // 取得总列数

    //echo $sheet->getCellByColumnAndRow(1, 1)->getCalculatedValue();
    $data = $sheet->rangeToArray("A1:$highestColumn$highestRow",1,true,true,true);
    return $data;
}



