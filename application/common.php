<?php
/**
 * Created by PhpStorm.
 * User: HZM
 * Date: 2018/4/13
 * Time: 20:33
 */


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


/**
 * 删除二维数组重复值
 * @param $array
 * @return array
 */
function two_array_unique($array)
{
    $out = array();
    foreach ( $array as $key => $value ) {
        if( !in_array($value,$out)){
            $out[$key] = $value;
        }
    }
    return $out;
}


/**
 * 手机号合格验证
 * @param $str_data
 * @return bool
 */
function validate_phone($str_data)
{
    $str_rule = "/^1[34578]\d{9}$/";
    $result = false;
    if( preg_match($str_rule,$str_data) == 1){
        $result = true;
    }
    return $result;
}


/**
 * 接口请求 调用第三方接口
 * @param $url
 * @array $parameter  参数
 * @return mixed
 */
function request_url($url,$parameter)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT,15);   //只需要设置一个秒的数量就可以
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // post数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // post的变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
    $output = curl_exec($ch);
    curl_close($ch);
    $output = json_decode($output,true);
    return $output;
}

/**
 * 判断请求是否来自微信浏览器
 * @return bool
 */
function is_wechat_browser()
{
    $result = false;
    if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ){
        $result = true;
    }
    return $result;
}

/**
 * 判断请求是否来自小程序
 * @return bool
 */
function is_wechat_small_app()
{
    $referer = $_SERVER['HTTP_REFERER'];
    $result = false;
    if( !empty($referer) ){
        $referer =parse_url($referer);
        if( $referer['host'] != 'servicewechat.com' ){
            $result = true;
        }
    }
    return $result;
}

/**
 * 加密解密
 * @param $text
 * @param $key
 * @param string $type encode:加密 decode:解密
 * @return bool|string
 */
function encode_div( $text, $key, $type = 'encode')
{
    $result = false;
    $chr_arr = array(
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o',
        'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
        'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
    );
    // 解密
    if ( $type == 'decode') {
        if ( strlen($text) >= 14 ) {
            $verity_str = substr($text,0,8);
            $text = substr($text,8);
            // 密文完整性验证
            if ( $verity_str == substr(md5($text),0,8)){
                $key_b = substr($text, 0, 6);
                $rand_key = $key_b.$key;
                $rand_key = md5($rand_key);
                $text = base64_decode(substr($text, 6));
                $result = '';
                for ($i = 0; $i < strlen($text); $i++) {
                    $result .= $text{$i} ^ $rand_key{$i % 32};
                }
            }
        }
        // 加密
    }else{
        $key_b = $chr_arr[rand() % 62] . $chr_arr[rand() % 62] . $chr_arr[rand() % 62] . $chr_arr[rand() % 62] . $chr_arr[rand() % 62] . $chr_arr[rand() % 62];
        $rand_key = $key_b.$key;
        $rand_key = md5($rand_key);
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $result .= $text{$i} ^ $rand_key{$i % 32};
        }
        $result = trim($key_b.base64_encode($result), "==");
        $result = substr(md5($result), 0, 8) . $result;
    }
    return $result;
}

/**
 * 随机数
 * @param int $len
 * @param string $type
 * @param string $addChars
 * @return bool|string
 */
function rand_string($len = 5, $type = '2', $addChars = '')
{
    $str = '';
    switch ($type) {
        case '0':
            $chars = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz" . $addChars;
            break;
        case '1':
            $chars = "0123456789";
            break;
        case '2':
            $chars = "abcdefghijklmnpqrstuvwxyz123456789";
            break;
        default :
            $chars = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789" . $addChars;
            break;
    }
    $chars = str_shuffle($chars);
    $str = substr($chars, 1, $len);
    return $str;
}

/**
 * 返回当前访问的 url
 * @return string
 */
function get_current_url()
{
    $result = $_SERVER['REQUEST_URI'] ? trim(C('pin_site_host'),'/').$_SERVER['REQUEST_URI'] :
        trim(C('pin_site_host'),'/').$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
    return $result;
}

/**
 * 判断是否移动端访问
 * @return bool
 */
function is_mobile(){
    $result = false;
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if ( isset($_SERVER['HTTP_X_WAP_PROFILE']) ) $result = true;
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if( isset ($_SERVER['HTTP_VIA']) ) $result = stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $result = true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if(!$result){
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
                && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false
                    || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml')
                        < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                $result = true;
            }
        }
    }
    return $result;
}



