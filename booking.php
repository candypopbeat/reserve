<?php
include "functions.php";
$selectedPlan = isset($_POST['selectedPlan']) ? $_POST['selectedPlan'] : "";
$year         = isset($_POST['unixTime']) ? get_unix_year($_POST['unixTime']) : "";
$month        = isset($_POST['unixTime']) ? get_unix_month($_POST['unixTime']) : "";
$day          = isset($_POST['unixTime']) ? get_unix_day($_POST['unixTime']) : "";
$startHour    = isset($_POST['unixTime']) ? get_unix_hour($_POST['unixTime']) : "";
$startMinute  = isset($_POST['unixTime']) ? get_unix_minute($_POST['unixTime']) : "";
$thisDay      = $year . '年' . $month . '月' . $day . '日';
$events       = get_google_calendar_this_event($year, $month, $day);
$url          = 'booking-error/?date=' . $thisDay;
judge_close_redirect($events, $url);
include "header.php";
?>
<div id="app">

  <section class="reserveForm">
    <div class="container">
      <div class="card border-top">
        <div class="card-body">
          <div class="step">Step.3</div>
          <h2 class="title"><?php echo $thisDay; ?>の予約ページ</h2>
          <p>初めての方はお電話かメールアドレスをご入力ください</p>
          <div class="text-center text-sm-right">
            <a href="timetable.php" class="btn btn-outline-secondary mb-4">
              メニューと日時選択へ戻る
            </a>
          </div>
          <form action="save.php" method="post">
            <div class="row pb-4">
              <div class="col-12 col-md-3">
                <h3>メニュー<span class="required"></span></h3>
              </div>
              <div class="col-12 col-md-9">
                <template v-for="v in plan">
                  <h4 v-if="v.slug === planActive" v-text="v.title" class="selectedMenu"></h4>
                  <input v-if="v.slug === planActive" type="hidden" name="plan" :value="v.title">
                </template>
              </div>
            </div>
            <div class="row mt-2 pb-3">
              <div class="col-12 col-md-3">
                <h3>開始<span class="required"></span></h3>
              </div>
              <div class="col-12 col-md-9">
                <h4 v-html="startTime"></h4>
                <input name="startTime" v-model="startTime" type="hidden">
              </div>
            </div>
            <div class="row mt-2 pb-3">
              <div class="col-12 col-md-3">
                <h3>終了<span class="required"></span></h3>
              </div>
              <div class="col-12 col-md-9">
                <h4 v-html="endTime"></h4>
                <input name="endTime" v-model="endTime" type="hidden" class="form-control">
              </div>
            </div>
            <div class="row mt-2 pb-3">
              <div class="col-12 col-md-3">
                <h3>お名前<span class="required"></span></h3>
              </div>
              <div class="col-12 col-md-9">
                <div class="form-group">
                  <input type="text" name="yourName" v-model="yourName" @change="changeInput('yourName')" class="form-control">
                </div>
              </div>
            </div>
            <div class="row mt-2 pb-3">
              <div class="col-12 col-md-3">
                <h3>電話</h3>
              </div>
              <div class="col-12 col-md-9">
                <div class="form-group">
                  <input type="text" name="yourTel" v-model="yourTel" @change="changeInput('yourTel')" class="form-control">
                  <ul class="notice">
                    <li>ご入力頂いた場合、お電話にて確認のご連絡を致します</li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="row mt-2 pb-3">
              <div class="col-12 col-md-3">
                <h3>メール</h3>
              </div>
              <div class="col-12 col-md-9">
                <div class="form-group">
                  <input type="text" name="yourMail" v-model="yourMail" @change="changeInput('yourMail')" class="form-control">
                  <ul class="notice">
                    <li>ご入力頂いた場合、ご予約内容をメールにて自動送信致します</li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-12 col-md-3">
                <h3>備考</h3>
              </div>
              <div class="col-12 col-md-9">
                <div class="form-group">
                  <textarea name="memo" v-model="memo" rows="5" @change="changeInput('memo')" class="form-control"></textarea>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-center my-4">
              <a href="timetable.php" class="btn btn-secondary text-white me-5">戻る</a>
              <button class="btn btn-danger" type="submit" :disabled="reserveActive">予約する</button>
              <input type="hidden" name="selectYear" value="<?php echo $year; ?>">
              <input type="hidden" name="selectMonth" value="<?php echo $month; ?>">
              <input type="hidden" name="selectDay" value="<?php echo $day; ?>">
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

</div><!-- ./ #app -->

<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="inc/menus.js"></script>
<script>
  var app = new Vue ({
    el: '#app',
    data: {
      plan: menus,
      planActive: '<?php echo $selectedPlan; ?>',
      reserveActive: true,
      isStartDisabled: true,
      selectedPlanTime: 0,
      startTime: '<?php echo $startHour; ?>:<?php echo $startMinute; ?>',
      endTime: '',
      yourName: '',
      yourMail: '',
      memo: ''
    },
    mounted: function() {
      var setData = [
        'yourName',
        'yourMail',
        'memo'
      ];
      this.setData(setData);
      this.setPlanTime();
      this.setEndTime();
    },
    methods: {
      setData: function(v){
        for( var k in v){
          var t = v[k];
          if (localStorage.getItem(t)) {
            this[t] = localStorage.getItem(t);
          }
        }
      },
      clickMenu: function(v){
        this.planActive = v;
        this.setPlanTime();
      },
      activeMenu: function(v){
        if (this.planActive === v) {
          return 'active';
        }
        return false;
      },
      setPlanTime: function(){
        for( var k in this.plan ){
          if ( this.planActive === this.plan[k].slug ) {
            this.selectedPlanTime = this.plan[k].time;
          }
        }
        this.setEndTime();
      },
      setEndTime: function(){
        var startDateHour = this.startTime.split(":")[0];
        if (startDateHour == '') {
          return false;
        }
        var startDateMinute = this.startTime.split(":")[1];
        var startDateTime   = new Date(2000, 1, 1, startDateHour, startDateMinute, 0);
        var endDateTime     = startDateTime.setMinutes(startDateTime.getMinutes() + this.selectedPlanTime);
        var endDateHour     = new Date(endDateTime).getHours();
        endDateHour         = ('0' + endDateHour).slice(-2);
        var endDateMinute   = new Date(endDateTime).getMinutes();
        endDateMinute       = ('0' + endDateMinute).slice(-2);
        this.endTime        = endDateHour + ':' + endDateMinute;
      },
      changeInput: function(c){
        localStorage.setItem(c, this[c]);
      },
      addDigits: function(v){
        return Number(v).toLocaleString();
      },
      switchReserveActive: function(){
        if (this.planActive != "" && this.startTime != "" && this.endTime != "" && this.yourName != "") {
          this.reserveActive = false;
        }else{
          this.reserveActive = true;
        }
      },
    },
    watch: {
      planActive: function (v) {
        this.switchReserveActive();
        if (v.length > 0) {
          this.isStartDisabled = false;
        }else{
          this.isStartDisabled = true;
        }
      },
      startTime: function (v) {
        this.switchReserveActive();
      },  
      endTime: function (v) {
        this.switchReserveActive();
      },  
      yourName: function (v) {
        this.switchReserveActive();
      }  
    }
  })
</script>

<?php include "footer.php"; ?>