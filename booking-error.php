<?php
include "header.php";
$thisDay = !empty($_GET['date']) ?  $_GET['date'] : "";
?>
<section class="py-4">
  <div class="container">
    <div class="card">
      <div class="card-body text-center">
        <div class="h4"><?php echo $thisDay; ?><span class="small">の</span></div>
        <div class="h5 mb-5">予約を締め切りました<br />申し訳ございません</div>
        <a href="schedule.php" class="btn btn-danger">カレンダーへ戻る</a>
      </div>
    </div>
  </div>
</section>
<?php include "footer.php"; ?>