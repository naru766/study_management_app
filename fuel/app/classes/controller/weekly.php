<?php

class Controller_Weekly extends Controller_Base
{
	/*
	HTMLテンプレートを返す: /weekly
	*/
  public function action_index()
  {
    $this->template->title = '週間グラフ';
    $this->template->content = View::forge('weekly/index', [
      'current_user' => ['username' => Session::get('username')],
    ]);
  }

	/*
	/weekly/api_dataにAjaxリクエスト
	*/
  public function action_api_data()
  {
    // 直近日曜〜土曜を計算
    $today = new DateTime('today');
    $weekday = (int) $today->format('w'); // ０:日〜６:土

    $start = clone $today;
    $start->modify("-{$weekday} days"); // 直近日曜
    $end = clone $start;
    $end->modify('+6 days'); // その週の土曜

    $start_date = $start->format('Y-m-d');
    $end_date = $end->format('Y-m-d');

    // DBからその週だけ記録取得
    $data = DB::select(
      'study_date',
      [DB::expr('SUM(total_minutes)'), 'total'],
      [DB::expr('DAYOFWEEK(study_date)'), 'day_of_week'] // １:日〜７:土
    )
      ->from('study_records')
      ->where('user_id', '=', $this->user_id)
      ->where('study_date', '>=', $start_date)
      ->where('study_date', '<=', $end_date)
      ->group_by('study_date')
      ->execute()
      ->as_array();

    // ７日分の各要素を０で初期化
    $week_data = array_fill(0, 7, 0);
    foreach ($data as $row)
    {
      $index = (int) $row['day_of_week'] - 1; // ０〜６
      $week_data[$index] = (int) $row['total']; // index(０:日〜６:土)曜日ごとに学習時間を追加
    }

    // 総学習時間
    $total_minutes = array_sum($week_data);

    // 今日までの日数で平均時間を計算: 日:1, 月:2...土:3
    $denom = $weekday + 1; // 1〜7
    $average_minutes = $denom > 0 ? (int) floor($total_minutes / $denom) : 0;

    // 今日の曜日を初期選択
    $today_index = $weekday;

    $payload = [
      'week_data' => $week_data, // 各曜日の学習時間のarray
      'dates' => [$start_date, $end_date], // 直近の日曜からその週の土曜
      'total_minutes' => $total_minutes,
      'average_minutes' => $average_minutes,
      'today_index' => $today_index, // 今日の曜日
    ];

    return Response::forge(
      json_encode($payload), // データをJSON文字列に変換
      200, // ステータスコード: OK
      ['Content-Type' => 'application/json']
    );
  }
}