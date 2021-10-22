<?php
session_start();
$post = $_SESSION['form'];
include "functions.php";
$year      = isset($post['selectYear']) ? $post['selectYear'] : "";
$month     = isset($post['selectMonth']) ? $post['selectMonth'] : "";
$day       = isset($post['selectDay']) ? $post['selectDay'] : "";
$thisDay   = $year . "年" . $month . "月" . $day . "日";
$title     = isset($post['plan']) ? $post['plan'] : "";
$name      = isset($post['yourName']) ? $post['yourName'] : "";
$tel       = isset($post['yourTel']) ? $post['yourTel'] : "";
$mail      = isset($post['yourMail']) ? $post['yourMail'] : "";
$startTime = isset($post['startTime']) ? $post['startTime'] : "";
$endTime   = isset($post['endTime']) ? $post['endTime'] : "";
$memo      = isset($post['memo']) ? $post['memo'] : "";
$memo      = nl2br($memo);
include "header.php";
?>
<section class="thanks">
  <div class="container text-center">
    <h2 class="mb-4">ご予約情報を送信しました</h2>
    <p class="mb-4">担当者からの連絡を持って予約完了となりますので、<br class="d-inline d-sm-none" />しばらくお待ち下さい</p>
    <div class="card">
      <div class="card-header bg-wine">送信内容</div>
      <div class="card-body">
        <table class="thanks table table-borderess">
          <tr>
            <th>日時</th><td><?php echo $thisDay; ?> <?php echo $startTime; ?>～<?php echo $endTime; ?></td>
          </tr>
          <tr>
            <th>メニュー</th><td><?php echo $title; ?></td>
          </tr>
          <tr>
            <th>お名前</th><td><?php echo $name; ?></td>
          </tr>
          <tr>
            <th>電話</th><td><?php echo $tel; ?></td>
          </tr>
          <tr>
            <th>メール</th><td><?php echo $mail; ?></td>
          </tr>
          <tr>
            <th>備考</th><td><?php echo $memo; ?></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="mt-5">
    	<a href="index.php" class="btn btn-secondary">最初に戻る</a>
    </div>
  </div>
</section>

<?php include "footer.php"; ?>