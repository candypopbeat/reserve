<?php
include "functions.php";
$unixTime     = isset($_POST['unixTime']) ? $_POST['unixTime'] : "";
$selectedPlan = isset($_POST['selectedPlan']) ? $_POST['selectedPlan'] : "";
$year         = isset($_POST['unixTime']) ? get_unix_year($_POST['unixTime']) : $_POST['selectYear'];
$month        = isset($_POST['unixTime']) ? get_unix_month($_POST['unixTime']) : $_POST['selectMonth'];
$day          = isset($_POST['unixTime']) ? get_unix_day($_POST['unixTime']) : $_POST['selectDay'];
$startHour    = isset($_POST['unixTime']) ? get_unix_hour($_POST['unixTime']) : "";
$startMinute  = isset($_POST['unixTime']) ? get_unix_minute($_POST['unixTime']) : "";
$thisDay      = $year . '年' . $month . '月' . $day . '日';
$events       = get_google_calendar_this_event($year, $month, $day);
$url          = 'booking-error/?date=' . $thisDay;
judge_close_redirect($events, $url);
include "header.php";
?>
<div id="app">
  <section class="py-4">
    <div class="container">
      <div class="card border-top">
        <div class="card-body">
          <h2 class="title"><?php echo $thisDay; ?>の予約ページ</h2>
          <?php if ( empty($_POST['unixTime']) ): ?>
            <p>別日に変更する際はスケジュールカレンダーにお戻り下さい</p>
          <?php endif; ?>
          <?php if ( empty($_POST['unixTime']) ): ?>
            <?php if (count($events) > 0): ?>
              <p>下記以外の時間帯をご指定下さい</p>
              <ul>
                <?php foreach ($events as $k => $v) {
                  $startTime = convert_hour_minute($v->start->dateTime);
                  $endTime = convert_hour_minute($v->end->dateTime);
                  ?>
                  <li><?php echo $startTime; ?>～<?php echo $endTime; ?></li>
                <?php } ?>
              </ul>
            <?php endif ?>
          <?php endif; ?>
          <div class="text-right">
            <button onclick="history.back()" class="btn btn-secondary text-white">スケジュールへ戻る</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php if ( empty($_POST['unixTime']) ) { ?>
    <section>
      <div class="container">
        <div class="card">
          <div class="card-body">
            <h2>メニューをお選びください</h2>
            <p>クリックすると選択状態になります</p>
            <div class="row row-cols-1 row-cols-md-3">
              <div class="col py-1" v-for="v in plan">
                <div class="card card-hover" :class="activeMenu(v.slug)" @click="clickMenu(v.slug)">
                  <div class="card-header d-flex justify-content-between">
                    <span v-text="v.title"></span><span v-text="'￥' + addDigits(v.price)"></span>
                  </div>
                  <div class="card-body" v-text="v.desc"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php } ?>

  <section class="py-4">
    <div class="container">
      <div class="card">
        <div class="card-body">
          <form action="save.php" method="post">
            <div class="row pb-4">
              <div class="col-12 col-md-3">
                <h3>メニュー<span class="required"></span></h3>
              </div>
              <div class="col-12 col-md-9 d-flex">
                <span v-show="planActive == ''">上記よりクリック選択して下さい</span>
                <template v-for="v in plan">
                  <span v-if="v.slug === planActive" v-text="v.title" class="selectedMenu"></span>
                  <input v-if="v.slug === planActive" type="hidden" name="plan" :value="v.title">
                </template>
              </div>
            </div>
            <div class="row mt-2 pb-3">
              <div class="col-12 col-md-3">
                <h3>開始<span class="required"></span></h3>
              </div>
              <div class="col-12 col-md-9 d-flex">
                <div class="form-group">
                  <input
                    name="startTimeView"
                    v-model="startTime"
                    :disabled="isStartDisabled"
                    @change="setEndTime()"
                    type="time"
                    min="09:00"
                    max="18:00"
                    step="900"
                    class="form-control">
                  <input name="startTime" v-model="startTime" type="hidden" class="form-control">
                  <?php if ( empty($_POST['unixTime']) ): ?>
                    <ul class="notice">
                      <li>メニューを選択すると入力できます</li>
                      <li>15分単位の選択になります</li>
                    </ul>
                  <?php else: ?>
                    <ul class="notice">
                      <li>自動入力されました</li>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="row mt-2 pb-3">
              <div class="col-12 col-md-3">
                <h3>終了<span class="required"></span></h3>
              </div>
              <div class="col-12 col-md-9 d-flex">
                <div class="form-group">
                  <input name="endTimeView" v-model="endTime" type="time" class="form-control" disabled>
                  <input name="endTime" v-model="endTime" type="hidden" class="form-control">
                  <ul class="notice">
                    <li>開始時間から自動入力されます</li>
                  </ul>
                </div>
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
            <div class="row mt-2 pb-3">
              <div class="col-12 col-md-3">
                <h3>電話</h3>
              </div>
              <div class="col-12 col-md-9">
                <div class="form-group">
                  <input type="text" name="yourTel" v-model="yourTel" @change="changeInput('yourTel')" class="form-control">
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
            <div class="d-flex justify-content-around my-4">
              <a href="schedule.php" class="btn btn-secondary text-white mr-5">戻る</a>
              <button class="btn btn-primary" type="submit" :disabled="reserveActive">予約する</button>
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
      yourTel: '',
      memo: ''
    },
    mounted: function() {
      var setData = [
        'yourName',
        'yourMail',
        'yourTel',
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