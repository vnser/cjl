<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/12/1 0001
 * Time: 下午 14:06
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'Lib/PHPMailer/Exception.php';
require_once 'Lib/PHPMailer/PHPMailer.php';
require_once 'Lib/PHPMailer/SMTP.php';

require_once 'comm.php';
require_once 'checklogin.php';
if(!$login_flag){
    ajaxReturn([
        'status'=>false,
        'code'=>'请先登录!'
    ]);
}
$qq = $_POST['qq'];
if(!is_numeric($qq)){
    ajaxReturn([
        'status'=>false,
        'code'=>'请输入正确的QQ号!'
    ]);
}
$res = db()->query("select * from vj_user where qq='{$qq}' ")->fetch(2);
if($res){
    ajaxReturn([
        'status'=>false,
        'code'=>'输入的QQ号已经绑定过其他账号,请更换QQ号!'
    ]);
}
$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
try {
    //Server settings
    $mail->SMTPDebug = 0;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.qq.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'vjl_qf@qq.com';                 // SMTP username
    $mail->Password = 'cpbdmovhzwuybhfh';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to
    $mail->CharSet = 'utf-8';
    //Recipients
    $mail->setFrom('vjl_qf@qq.com', '清風');
    $mail->addAddress($qq.'@qq.com');     // Add a recipient
    session_start();
    $_SESSION['ver'] = [
        'qq'=>$qq,
        'code'=>rand(10000,999999)
    ];
    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'vjl賬戶綁定驗證';
    $mail->Body    = '尊敬的賬戶:'.$user['user_id'].',您的驗證碼為: <span style="font-size: 16px;font-weight: bold">'. $_SESSION['ver']['code']."</span><br>(售後客服企鵝: 2132500461)<br><u>購買賬號、充值鉲密鏈接: <a href=\"http://t.cn/RWVVQj3 
\">http://t.cn/RWVVQj3</a></u>";
    $mail->send();
    ajaxReturn([
        'status'=>true,
        'code'=>'success'
    ]);
} catch (Exception $e) {
    ajaxReturn([
        'status'=>false,
        'code'=>'发送失败,请稍后再试!',
        'debug'=>$e->getMessage()
    ]);
}