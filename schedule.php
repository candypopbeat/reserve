<?php
include "functions.php";
include "header.php";
?>
<section class="py-4">
  <div class="container">
    <h2>Schedule</h2>
    <p>日曜日は定休日です</p>
    <p class="text-center">予約希望日の赤いボタン <i class="fas fa-dot-circle text-red"></i> を押して下さい</p>
    <p class="text-center"><i class="fas fa-times"></i> ボタンの日は予約できません</p>
    <?php
      echo output_single_calendar(date('Y'),date('n'));
      echo output_single_calendar(date('Y'),date('n')+1);
    ?>
  </div>
</section>

<?php include "footer.php"; ?>