<?php
include "functions.php";
include "gcal-api.php";
date_default_timezone_set('Asia/Tokyo');
$now              = date('Y-m-d');
$today            = date('Ymd' . convert2dig($iniStart) . '0000');
$todayUnix        = strtotime($today);
$todayLast        = date('Ymd' . convert2dig($iniEnd) . '0000');
$todayLastUnix    = strtotime($todayLast);
$timeMin          = $now . 'T00:00:00+0900';
$timeMinUnix      = strtotime($timeMin);
$timeMaxUnix      = strtotime('+' . $span . ' day', $timeMinUnix);
$timeMax          = date('Y-m-d', $timeMaxUnix);
$timeMax          = $timeMax . 'T23:59:00+0900';
$optParams        = array(
	'timeZone'     => 'Asia/Tokyo',
	'orderBy'      => 'startTime',
	'singleEvents' => true,
	'timeMin'      => $timeMin,
	'timeMax'      => $timeMax
);
$results = $service->events->listEvents($calendarId, $optParams);
$events  = $results->getItems();
if (isset($events)) :
	$ngsArr = array();
	foreach ($events as $k => $v) {
		$title = empty($v->getSummary()) ? "no title" : $v->getSummary();
		if ($title === $closeKey) {
			$start       = $v->start->date; // 2020-03-10
			$ngStart     = $start . 'T' . convert2dig($iniStart) . ':00:00+0900';
			$ngEnd       = $start . 'T' . convert2dig($iniEnd) . ':00:00+0900';
			$ngStartUnix = strtotime($ngStart);
			$ngEndUnix   = strtotime($ngEnd);
			$ngDiff      = $ngEndUnix - $ngStartUnix;
			$ngDiffMin   = $ngDiff / 60;
			$ngDiffNum   = $ngDiffMin / $divideMin;
			$ngDiffArr   = array();
			for ($i      = 0; $i < $ngDiffNum; $i++) {
				$minute      = $divideMin * $i;
				$ngDiffArr[] = strtotime('+' . $minute . ' minute', $ngStartUnix);
			}
			$ngsArr[] = $ngDiffArr;
		} else {
			$start       = $v->start->dateTime; // 2020-03-10T10:00:00+09:00
			$end         = $v->end->dateTime; // 2020-03-10T11:00:00+09:00
			$ngStartUnix = strtotime($start);
			$ngEndUnix   = strtotime($end);
			$ngDiff      = $ngEndUnix - $ngStartUnix;
			$ngDiffMin   = $ngDiff / 60;
			$ngDiffNum   = $ngDiffMin / $divideMin;
			$ngDiffArr   = array();
			for ($i      = 0; $i < $ngDiffNum; $i++) {
				$minute      = $divideMin * $i;
				$ngDiffArr[] = strtotime('+' . $minute . ' minute', $ngStartUnix);
			}
			$ngsArr[] = $ngDiffArr;
		}
	}
	$ngsArr2 = array();
	foreach ($ngsArr as $k => $v) {
		foreach ($v as $k2 => $v2) {
			$ngsArr2[] = $v2;
		}
	}
	for ($i = 0; $i < $span; $i++) {
		$iniDayLastUnix = strtotime('+' . $i . ' day', $todayLastUnix);
		$ngsArr2[]      = $iniDayLastUnix;
	}
endif;
include "header.php";
?>
<div id="app">

	<section class="selectMenu">
		<div class="container">
			<div class="card border-top">
				<div class="card-body">
					<div class="step">Step.1</div>
					<h2>メニューをお選びください</h2>
					<p>クリックすると選択状態になります</p>
					<div class="row row-cols-1 row-cols-lg-3">
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

	<section class="timetable">
		<div class="container">
			<div class="card border-top">
				<div class="card-body">
					<div class="step">Step.2</div>
					<h2>日時を選択してください</h2>
					<p>ピンク色の時間帯は選択できません</p>
					<div class="table-responsive">
						<table class="table table-bordered table-timetable" :class="{ 'disabled': judgePlanActive }">
							<thead>
								<tr>
									<th class="th1"></th>
									<?php
									$todayYmd = date('m/d', $todayUnix);
									$idArr    = array($todayUnix);
									$dateArr  = array($todayYmd);
									for ($i = 0; $i < $iniDivide; $i++) {
										$todayUnix = strtotime('+' . $divideMin . ' minute', $todayUnix);
										$todayYmd  = date('m/d', $todayUnix);
										$idArr[]   = $todayUnix;
										$dateArr[] = $todayYmd;
									}
									?>
									<?php for ($i = 0; $i < $span; $i++) { ?>
										<?php
										$iniDayUnix = strtotime('+' . $i . ' day', $todayUnix);
										$iniDayYm   = date('n/j', $iniDayUnix);
										$iniDayW    = date('w', $iniDayUnix);
										$iniDayW    = get_week_kanji($iniDayW);
										?>
										<th class="th2">
											<div><?php echo $iniDayYm; ?></div>
											<small><?php echo $iniDayW; ?></small>
										</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($idArr as $k => $v) :
									if (array_key_last($idArr) == $k) { ?>
										<tr>
											<?php $thisTime = date('H:i', $v); ?>
											<th class="th3">
												<div class="thTime"><?php echo $thisTime; ?></div>
											</th>
											<?php for ($i = 0; $i < $span; $i++) : ?>
												<td v-on:mouseover="hoverEffect(<?php echo $nextDayUnix; ?>)" v-on:mouseleave="hoverLeave()" :class="{ 'okTd': hoverJudge(<?php echo $nextDayUnix; ?>), 'okClick': clickJudge(<?php echo $nextDayUnix; ?>) }" id="<?php echo $nextDayUnix; ?>" class="td1">
												</td>
											<?php endfor; ?>
										</tr>
									<?php } else { ?>
										<tr>
											<?php $thisTime = date('H:i', $v); ?>
											<th class="th3">
												<div class="thTime"><?php echo $thisTime; ?></div>
											</th>
											<?php for ($i = 0; $i < $span; $i++) :
												$nextDayUnix   = strtotime('+' . $i . 'day', $v);
												$flagOpenClose = 1;
												foreach ($ngsArr as $v2) {
													foreach ($v2 as $v3) {
														if ($v3 == $nextDayUnix) {
															$flagOpenClose = 0;
														}
													}
												}
												if ($flagOpenClose) { ?>
													<td v-on:mouseover="hoverEffect(<?php echo $nextDayUnix; ?>)" v-on:mouseleave="hoverLeave()" :class="{ 'okTd': hoverJudge(<?php echo $nextDayUnix; ?>), 'okClick': clickJudge(<?php echo $nextDayUnix; ?>) }" id="<?php echo $nextDayUnix; ?>" class="td2">
														<form action="booking.php" method="post">
															<button type="submit" class="timeSubmit"></button>
															<input type="hidden" name="selectedPlan" v-model="planActive">
															<input type="hidden" name="unixTime" value="<?php echo $nextDayUnix; ?>">
														</form>
													</td>
												<?php } else { ?>
													<td class="cellClose"></td>
											<?php }
											endfor; ?>
										</tr>
								<?php }
								endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</section>

</div><!-- /#app -->

<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="inc/menus.js"></script>
<script>
	var app = new Vue({
		el: '#app',
		data: {
			plan: menus,
			planActive: '',
			hoverActive: [],
			closed: <?php echo json_encode($ngsArr2); ?>,
			selectedPlanTime: 0,
			startTime: '',
			endTime: '',
			judgePlanActive: true
		},
		mounted: function() {
			this.setData();
			this.setPlanActive();
		},
		methods: {
			setData() {
				var setData = ['planActive'];
				for (var k in setData) {
					var t = setData[k];
					if (localStorage.getItem(t)) {
						this[t] = localStorage.getItem(t);
					}
				}
			},
			setPlanActive() {
				if (localStorage.getItem('planActive')) {
					this.judgePlanActive = false;
					this.setPlanTime();
				}
			},
			hoverEffect(e) {
				var planTime = ((this.selectedPlanTime / <?php echo $divideMin; ?>) - 1) * 60 * <?php echo $divideMin; ?>;
				var start    = new Date(e * 1000);
				var end      = new Date((e + planTime) * 1000);
				var diff     = end - start;
				var diffNum  = diff / 1000 / 60 / <?php echo $divideMin; ?>;
				var diffArr  = [e];
				for (var i = 0; i < diffNum; i++) {
					var minute   = <?php echo $divideMin; ?> * (i + 1);
					var pushTime = (e * 1000) + (minute * 60 * 1000);
					pushTime     = pushTime / 1000;
					diffArr.push(pushTime);
				}
				this.hoverActive = diffArr;
			},
			hoverLeave() {
				this.hoverActive = '';
			},
			hoverJudge(e) {
				if (this.hoverActive.indexOf(e) >= 0) {
					for (var key in this.hoverActive) {
						if (this.closed.indexOf(this.hoverActive[key]) >= 0) {
							return false;
						}
					}
					return true;
				}
				return false;
			},
			clickJudge(e) {
				if (this.judgePlanActive) {
					return false;
				}
				var planTime = ((this.selectedPlanTime / <?php echo $divideMin; ?>) - 1) * 60 * <?php echo $divideMin; ?>;
				var start    = new Date(e * 1000);
				var end      = new Date((e + planTime) * 1000);
				var diff     = end - start;
				var diffNum  = diff / 1000 / 60 / <?php echo $divideMin; ?>;
				var diffArr  = [e];
				for (var i = 0; i < diffNum; i++) {
					var minute   = <?php echo $divideMin; ?> * (i + 1);
					var pushTime = (e * 1000) + (minute * 60 * 1000);
					pushTime     = pushTime / 1000;
					diffArr.push(pushTime);
				}
				for (var key in diffArr) {
					if (this.closed.indexOf(diffArr[key]) >= 0) {
						return false;
					}
				}
				return true;
			},
			activeMenu: function(v) {
				if (this.planActive === v) {
					return 'active';
				}
				return false;
			},
			clickMenu: function(v) {
				this.planActive      = v;
				this.judgePlanActive = false;
				this.setPlanTime();
				localStorage.setItem('planActive', v);
			},
			addDigits: function(v) {
				return Number(v).toLocaleString();
			},
			setPlanTime: function() {
				for (var k in this.plan) {
					if (this.planActive === this.plan[k].slug) {
						this.selectedPlanTime = this.plan[k].time;
					}
				}
				this.setEndTime();
			},
			setEndTime: function() {
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
			}
		},
	})
</script>

<?php include "footer.php"; ?>