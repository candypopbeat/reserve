<?php
session_start();
$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_SESSION['form'] = $post;
include "functions.php";
$adminMail       = "";
$adminName       = "予約システム";
$adminSubject    = "予約システム";
$fromAddress     = "";
$fromName        = "テスト送信者";
// $adminMailCc     = array();
// $adminMailBcc    = array();
$customerSubject = "ご予約ありがとうございます";
$year            = !empty($post['selectYear']) ? $post['selectYear'] : "";
$month           = !empty($post['selectMonth']) ? $post['selectMonth'] : "";
$day             = !empty($post['selectDay']) ? $post['selectDay'] : "";
$thisDay         = $year . "年" . $month . "月" . $day . "日";
$title           = !empty($post['plan']) ? $post['plan'] : "";
$name            = !empty($post['yourName']) ? $post['yourName'] : "";
$customerMail    = !empty($post['yourMail']) ? $post['yourMail'] : "";
$customerTel     = !empty($post['yourTel']) ? $post['yourTel'] : "";
$startTime       = !empty($post['startTime']) ? $post['startTime'] : "";
$endTime         = !empty($post['endTime']) ? $post['endTime'] : "";
$memo            = !empty($post['memo']) ? $post['memo'] : "";
$memoBr          = nl2br($memo);
$desc            = $title;
if (!empty($memo)) {
  $desc .= "\n" . $memo;
}
$events = get_google_calendar_this_event($year, $month, $day);
$url    = 'booking-error/?date=' . $thisDay;
judge_close_redirect($events, $url);
$event = insert_google_calendar_event($name, $desc, $year, $month, $day, $startTime, $endTime);

/**
 * メール送信ライブラリの「PHP Mailer」の読み込み
 * Googleの認証ライブラリ「OAuth2」の読み込み
 */
require('vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\Exception;
use League\OAuth2\Client\Provider\Google;

/**
 * Gmail API セット
 */
$CLIENT_ID     = '';
$CLIENT_SECRET = '';
$REFRESH_TOKEN = '';
$USER_NAME     = '';

/**
 * メール送信インスタンス作成
 */
$mail = new PHPMailer(true);

/**
 * メール送信設定
 */
// $mail->SMTPDebug = 2; //デバッグ用
$mail->isSMTP();
$mail->isHTML(true);
$mail->SMTPAuth = true;
$mail->Host = 'smtp.gmail.com';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->AuthType = 'XOAUTH2';
$provider = new Google(
  [
    'clientId' => $CLIENT_ID,
    'clientSecret' => $CLIENT_SECRET,
  ]
);
$mail->setOAuth(
  new OAuth(
    [
      'provider'     => $provider,
      'clientId'     => $CLIENT_ID,
      'clientSecret' => $CLIENT_SECRET,
      'refreshToken' => $REFRESH_TOKEN,
      'userName'     => $USER_NAME,
    ]
  )
);
$mail->CharSet  = "utf-8";
$mail->Encoding = "base64";

/**
 * 管理者宛メール本文
 */
$adminMessage = <<<eof
予約システムからご依頼です。<br /><br />
予約内容は以下になります。<br /><br />
<table>
  <tr>
    <th style="padding: 5px; text-align: left; vertical-align: top;">お名前</th>
    <td style="padding: 5px; vertical-align: top;">${name}</td>
  </tr>
  <tr>
    <th style="padding: 5px; text-align: left; vertical-align: top;">メール</th>
    <td style="padding: 5px; vertical-align: top;">${customerMail}</td>
  </tr>
  <tr>
    <th style="padding: 5px; text-align: left; vertical-align: top;">電話</th>
    <td style="padding: 5px; vertical-align: top;">${customerTel}</td>
  </tr>
  <tr>
    <th style="padding: 5px; text-align: left; vertical-align: top;">日時</th>
    <td style="padding: 5px; vertical-align: top;">${thisDay}</td>
  </tr>
  <tr>
    <th style="padding: 5px; text-align: left; vertical-align: top;">メニュー</th>
    <td style="padding: 5px; vertical-align: top;">${title}</td>
  </tr>
  <tr>
    <th style="padding: 5px; text-align: left; vertical-align: top;">開始</th>
    <td style="padding: 5px; vertical-align: top;">${startTime}</td>
  </tr>
  <tr>
    <th style="padding: 5px; text-align: left; vertical-align: top;">終了</th>
    <td style="padding: 5px; vertical-align: top;">${endTime}</td>
  </tr>
  <tr>
    <th style="padding: 5px; text-align: left; vertical-align: top;">備考</th>
    <td style="padding: 5px; vertical-align: top;">${memoBr}</td>
  </tr>
</table>
eof;


/**
 * 管理者宛メール送信処理
 */
try {
  $mail->setFrom($fromAddress, $fromName); // 送信者
  $mail->addAddress($adminMail, $adminName);   // 宛先
  // $mail->addReplyTo('replay@example.com', 'お問い合わせ'); // 返信先
  // $mail->addCC('cc@example.com', '受信者名'); // CC宛先
  // $mail->addBCC('bcc@sample.com');
  // $mail->Sender = 'return@example.com'; // Return-path
  $mail->Subject = $adminSubject;
  $mail->Body    = $adminMessage;
  $mail->send();
} catch (Exception $e) {
  $_SESSION['error'] = $mail->ErrorInfo;
  $sendErrors['user'] = $mail->ErrorInfo;
}

/**
 * ユーザー宛メール本文
 */
$customerMessage = <<<eof
${name} 様<br /><br />予約システムへのご予約ありがとうございます。<br /><br />
当メールはシステムによる自動送信になります。<br />
こちらから最終確認連絡をいたしますので、しばらくおまちください。<br />
送信されたご予約内容は以下になります。<br /><br />
<table>
<tr>
<th style="padding: 5px; text-align: left; vertical-align: top;">お名前</th>
<td style="padding: 5px; vertical-align: top;">${name}</td>
</tr>
<tr>
<th style="padding: 5px; text-align: left; vertical-align: top;">メール</th>
<td style="padding: 5px; vertical-align: top;">${customerMail}</td>
</tr>
<tr>
<th style="padding: 5px; text-align: left; vertical-align: top;">電話</th>
<td style="padding: 5px; vertical-align: top;">${customerTel}</td>
</tr>
<tr>
<th style="padding: 5px; text-align: left; vertical-align: top;">日時</th>
<td style="padding: 5px; vertical-align: top;">${thisDay}</td>
</tr>
<tr>
<th style="padding: 5px; text-align: left; vertical-align: top;">メニュー</th>
<td style="padding: 5px; vertical-align: top;">${title}</td>
</tr>
<tr>
<th style="padding: 5px; text-align: left; vertical-align: top;">開始</th>
<td style="padding: 5px; vertical-align: top;">${startTime}</td>
</tr>
<tr>
<th style="padding: 5px; text-align: left; vertical-align: top;">終了</th>
<td style="padding: 5px; vertical-align: top;">${endTime}</td>
</tr>
<tr>
<th style="padding: 5px; text-align: left; vertical-align: top;">備考</th>
<td style="padding: 5px; vertical-align: top;">${memoBr}</td>
</tr>
</table>
eof;

/**
 * ユーザー宛メール送信処理
 */
try {
  $mail->setFrom($fromAddress, $fromName); // 送信者
  $mail->ClearAddresses();
  $mail->addAddress($customerMail, $name); // 宛先
  // $mail->addReplyTo('replay@example.com', 'お問い合わせ'); // 返信先
  // $mail->addCC('cc@example.com', '受信者名'); // CC宛先
  // $mail->addBCC('bcc@sample.com');
  // $mail->Sender = 'return@example.com'; // Return-path
  $mail->Subject = $customerSubject;
  $mail->Body    = $customerMessage;
  $mail->send();
} catch (Exception $e) {
  $_SESSION['error'] = $mail->ErrorInfo;
  $sendErrors['user'] = $mail->ErrorInfo;
}

header("Location: thanks.php");

?>