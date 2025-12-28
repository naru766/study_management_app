<?php

class Controller_Dashboard extends Controller_Base
{
  /*
  ダッシュボード: /dashboard
  */
  public function action_index()
  {
    $today = date('Y-m-d');
    $today_records = DB::select(
      array('study_records.id', 'id'),
      array('study_records.study_date', 'study_date'),
      array('study_records.hours', 'hours'),
      array('study_records.minutes', 'minutes'),
      array('study_records.total_minutes', 'total_minutes'),
      array('subjects.name', 'name'),
      array('subjects.color_code', 'color_code')
    )
      ->from('study_records')
      ->join('subjects', 'INNER') // 科目名は必須
      ->on('study_records.subject_id', '=', 'subjects.id')
      ->where('study_records.user_id', '=', $this->user_id)
      ->where('study_records.study_date', '=', $today)
      ->order_by('study_records.id', 'DESC')
      ->execute()
      ->as_array();

    // 今日の勉強時間
    $today_total_time = DB::select(
      [DB::expr('COALESCE(SUM(study_records.total_minutes), 0)'), 'sum_minutes']
    )
      ->from('study_records')
      ->where('study_records.user_id', '=', $this->user_id)
      ->where('study_records.study_date', '=', $today)
      ->execute()
      ->current();

    $total_minutes = (int) ($today_total_time['sum_minutes'] ?? 0);
    $hours = intdiv($total_minutes, 60);
    $minutes = $total_minutes % 60;
    $total_hm = sprintf('%d時間%d分', $hours, $minutes);

    // 科目ごとの合計時間を計算
    $by_subject = [];
    foreach (($today_records ?? []) as $r)
    {
      $name = $r['name'];
      $by_subject[$name] = ($by_subject[$name] ?? 0) + (int) $r['total_minutes'];
    }
    arsort($by_subject); // 降順ソート
    $top3 = array_slice($by_subject, 0, 3, true); // 上位３科目だけ取得

    $this->template->title = 'ダッシュボード';

    // views/dashboard/index.phpで使用
    $this->template->content = View::forge('dashboard/index', [
      'today_records' => $today_records, 
      'top3' => $top3,
      'total_minutes' => $total_minutes, // 合計分数(mins)
      'total_hm' => $total_hm, // X時間Y分の表示用
      'today' => $today,
      'current_user' => ['username' => Session::get('username')],
    ]);
  }

  /*
  週間グラフ: /weekly
  */
  public function action_weekly()
  {
    // 今日
    $today = new DateTime('today');
    $weekday = (int) $today->format('w'); // 0: 日曜日 ~ 6: 土曜日

    // 直近の日曜日〜その土曜日
    $start = clone $today;
    $start->modify("-{$weekday} days"); // 直近の日曜 ex. weekdayが月曜なら１日引いて日曜に設定
    $end = clone $start;
    $end->modify('+6 days'); // その週の土曜

    $start_str = $start->format('Y-m-d');
    $end_str = $end->format('Y-m-d');

    // 曜日ラベル（日〜土）
    $labels = ['日', '月', '火', '水', '木', '金', '土'];

    // 7日ぶんの配列を0分で初期化
    $week_days = [];
    for ($i = 0; $i < 7; $i++)
    {
      $d = clone $start;
      $d->modify("+{$i} days");
      $week_days[$i] = [
        'date' => $d->format('Y-m-d'),
        'label' => $labels[$i], // i=0: 日曜 i=6: 土曜
        'minutes' => 0,
      ];
    }

    // DBからこの期間の1日ごとの合計時間を取得
    // study_dateとtotal_minutesを取得: total_minutesがNULLの場合、その日は０分
    $rows = DB::select(
      ['study_date', 'study_date'],
      [DB::expr('COALESCE(SUM(total_minutes), 0)'), 'sum_minutes']
    )
      ->from('study_records')
      ->where('user_id', '=', $this->user_id)
      ->where('study_date', 'between', [$start_str, $end_str])
      ->group_by('study_date')
      ->execute()
      ->as_array();

    // 日付に対応したマップの作成 key: 日付 value: 分数
    $map = [];
    foreach ($rows as $row)
    {
      $map[$row['study_date']] = (int) $row['sum_minutes'];
    }

    $weekly_total_minutes = 0;
    $max_daily = 0;

    // 初期化した'week_days'にDBの値を挿入
    foreach ($week_days as $i => &$day)
    {
      // マップに日付が保持されていれば、その日の'minutes'を更新
      $date = $day['date'];
      if (isset($map[$date]))
      {
        $day['minutes'] = $map[$date];
      }

      // 週の合計時間
      $weekly_total_minutes += $day['minutes'];

      // 最大値の記録（グラフの縦軸のスケール調整のため）
      if ($day['minutes'] > $max_daily)
      {
        $max_daily = $day['minutes'];
      }
    }
    unset($day); // 参照解除

    // 平均時間（曜日に応じて割る数を変える）
    // 日曜=1, 月曜=2... 土曜=7
    $denom = max($weekday + 1, 1);
    $avg_minutes = $denom > 0 ? (int) floor($weekly_total_minutes / $denom) : 0;

    // グラフ用スケール（棒の最大値と平均ライン）
    $chart_max = max($max_daily, $avg_minutes, 1); // 全部0のときに0割りしないため
    $avg_height = ($avg_minutes > 0) ? ($avg_minutes / $chart_max) * 100 : 0; // パーセント計算

    // 表示用の'X時間Y分'に変換
    $weekly_total_hm = Helper_Format::hm($weekly_total_minutes);
    $avg_hm = Helper_Format::hm($avg_minutes);

    // 初期表示として'今日'の曜日を選択状態に
    $selected_index = $weekday;
    $selected_label = $labels[$selected_index]; // 日、月...
    $selected_minutes = $week_days[$selected_index]['minutes'];
    $selected_hm = $to_hm($selected_minutes);

    // 直近日曜から土曜の期間の表示用: A月B日〜C月D日
    $range_label = $start->format('n月j日') . '〜' . $end->format('n月j日');

    $this->template->title = '週間グラフ';

    // views/weekly/index.phpで使用
    $this->template->content = View::forge('dashboard/weekly', [
      'week_days' => $week_days,
      'range_label' => $range_label,
      'weekly_total_minutes' => $weekly_total_minutes,
      'weekly_total_hm' => $weekly_total_hm,
      'avg_minutes' => $avg_minutes,
      'avg_hm' => $avg_hm,
      'avg_height' => $avg_height,
      'chart_max' => $chart_max,
      'selected_label' => $selected_label,
      'selected_hm' => $selected_hm,
      'current_user' => ['username' => Session::get('username')],
    ]);
  }
}