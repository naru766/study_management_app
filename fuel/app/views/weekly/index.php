<?php
  // ユーザー名とアイコンを取得
  $uname = isset($current_user['username']) ? $current_user['username'] : Session::get('username');
  $avatar = mb_substr((string) $uname, 0, 1, 'UTF-8');
?>
<div class="dash-screen">
  <section class="dash dash--panel">
    <!-- 上部: ページタイトル、ユーザー名とアイコン、ログアウトボタン -->
    <div class="dash__nav dash__nav--3col">
      <div class="tabs">
        <span class="tab tab--active">週間グラフ</span>
      </div>

      <div class="userbox">
        <span class="userbox__name"><?= e($uname) ?></span>
        <span class="avatar" aria-hidden="true"><?= e($avatar) ?></span>
      </div>

      <div class="logoutbox">
        <?= Html::anchor('/auth/logout', 'ログアウト', ['class' => 'btn btn--outline']) ?>
      </div>
    </div>

    <!-- 下部 -->
    <div class="dash__grid">
      <!-- 左：グラフ -->
      <div class="weekly-left">
        <div class="weekly-chart">
          <div class="weekly-chart__inner">
            <div class="weekly-chart__bars">
              <canvas id="graph" width="600" height="300"></canvas>
              <!-- 平均ラインは canvas 内で描画する -->
            </div>
          </div>
        </div>
      </div>

      <!-- 右：数値とボタン -->
      <!-- data-bind(Knockout.js): JSの変数が変わると、自動的にHTMLも更新 -->
      <div class="weekly-right">
        <div class="weekly-stats">
          <p class="weekly-text weekly-text--avg">
            平均時間：<span data-bind="text: averageText"></span>
          </p>
          <p class="weekly-text weekly-text--sum">
            週間時間：<span data-bind="text: weekTotalText"></span>
          </p>
          <p class="weekly-text">
            <span data-bind="text: selectedDay"></span>
            <span data-bind="text: selectedText"></span>
          </p>
          <p class="weekly-text weekly-text--range">
            <span data-bind="text: rangeLabel"></span>
          </p>
        </div>

        <div class="weekly-buttons">
          <?= Html::anchor('/subjects', '全科目一覧', ['class' => 'btn btn-big btn--pill']) ?>
          <?= Html::anchor('/dashboard', 'ダッシュボード', ['class' => 'btn btn-big btn--pill']) ?>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- ライブラリーの読み込み -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.js"></script>
<script>
  /*
  合計分数を'X時間Y分'に変換
  */
  function minutesToHM(min)
  {
    min = min || 0;
    const h = Math.floor(min / 60);
    const m = min % 60;
    return h + '時間' + m + '分';
  }

  /*
  Knockout.js ViewModel
  */
  function WeeklyViewModel() 
  {
    // selfに変更前のthisを保存
    const self = this;

    // ko.observable*(): HTMLによる値の自動更新
    self.weekData = ko.observableArray([0, 0, 0, 0, 0, 0, 0]); // 各曜日分
    self.weekTotal = ko.observable(0); // 分
    self.averageMinutes = ko.observable(0); // 分

    // 表示用テキスト
    self.weekTotalText = ko.observable('0時間0分');
    self.averageText = ko.observable('0時間0分');
    self.selectedDay = ko.observable(''); // 曜日選択
    self.selectedText = ko.observable('0時間0分');
    self.rangeLabel = ko.observable(''); // 直近日曜からその週の土曜までの日付表示(A月B日〜C月D日)

    self.dayNames = ['日', '月', '火', '水', '木', '金', '土'];

    self.loadData = function() 
    {
      // APIにリクエスト
      fetch('/weekly/api_data')
        .then(response => response.json())
        .then(data => {
          self.weekData(data.week_data || []);

          const total = data.total_minutes || 0;
          const avg = data.average_minutes || 0;

          self.weekTotal(total);
          self.averageMinutes(avg);

          self.weekTotalText(minutesToHM(total));
          self.averageText(minutesToHM(avg));

          // YYYY-MM-DDを"M月D日"に変換
          if (data.dates && data.dates.length === 2) 
          {
            const start = new Date(data.dates[0]);
            const end = new Date(data.dates[1]);
            const fmt = d => (d.getMonth() + 1) + '月' + d.getDate() + '日';
            self.rangeLabel(fmt(start) + '〜' + fmt(end));
          }

          // 初期選択は今日の曜日
          const idx = data.today_index ?? 0;
          const label = self.dayNames[idx] + '曜日';
          const minutes = (data.week_data || [])[idx] || 0;

          self.selectedDay(label);
          self.selectedText(minutesToHM(minutes));

          self.drawGraph();
        });
    };

    self.drawGraph = function() 
    {
      const canvas = document.getElementById('graph');
      if (!canvas) return;
      const ctx = canvas.getContext('2d'); // 2D描画
      ctx.clearRect(0, 0, canvas.width, canvas.height); // 画面クリア

      const data = self.weekData();
      const maxValue = Math.max.apply(null, data.concat(60));

      const barWidth = 50; // 棒の幅
      const spacing = 20; // 棒の間隔
      const chartHeight = 160; // グラフの高さ
      const baseY = 250; // 底辺のY座標 
      const paddingX = 60; // 左の余白

      data.forEach(function(value, index) 
      {
        // 棒の高さを計算
        const height = (value / maxValue) * chartHeight;

        // 棒の左上座標を計算
        const x = paddingX + index * (barWidth + spacing);
        const y = baseY - height;

        // 棒を描画
        ctx.fillStyle = '#667EEA';
        ctx.fillRect(x, y, barWidth, height);

        // 曜日ラベル
        ctx.fillStyle = '#000';
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(self.dayNames[index], x + barWidth / 2, baseY + 22);
      });

      // 平均ライン
      const avg = self.averageMinutes() || 0;
      const avgY = baseY - (avg / maxValue) * chartHeight;
      const lastBarRight = paddingX + (data.length - 1) * (barWidth + spacing) + barWidth;

      // 線の開始と終了位置
      const extra = 12;
      const lineStartX = Math.max(0, paddingX - extra);
      const lineEndX = Math.min(canvas.width, lastBarRight + extra);

      // 赤い線を描画
      ctx.strokeStyle = 'red';
      ctx.beginPath();
      ctx.moveTo(lineStartX, avgY);
      ctx.lineTo(lineEndX, avgY);
      ctx.stroke();
    };

    // グラフクリックでその曜日の時間を表示
    document.addEventListener('DOMContentLoaded', function() 
    {
      const canvas = document.getElementById('graph');
      if (!canvas) return;
      canvas.addEventListener('click', function(e)
      {
        // クリック位置を取得
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;

        // どの棒がクリックされたか計算
        const barWidth = 50;
        const spacing = 20;
        const index = Math.floor((x - 40) / (barWidth + spacing));

        // グラフ上をクリックした場合に計算
        if (index >= 0 && index < 7) {
          self.selectedDay(self.dayNames[index] + '曜日');
          const minutes = self.weekData()[index] || 0;
          self.selectedText(minutesToHM(minutes));
        }
      });
    });

    // 初期ロード
    self.loadData();
  }

  // Knockoutを起動
  ko.applyBindings(new WeeklyViewModel());
</script>