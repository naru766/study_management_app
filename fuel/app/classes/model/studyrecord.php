<?php
class Model_StudyRecord
{
	/*
	* 指定日の学習記録を取得
	*/
  public static function get_by_date($user_id, $date)
  {
    return DB::select(
      ['study_records.id', 'id'],
      ['study_records.study_date', 'study_date'],
      ['study_records.hours', 'hours'],
      ['study_records.minutes', 'minutes'],
      ['study_records.total_minutes', 'total_minutes'],
      ['subjects.name', 'name'],
      ['subjects.color_code', 'color_code']
    )
      ->from('study_records')
      ->join('subjects', 'INNER')
      ->on('study_records.subject_id', '=', 'subjects.id')
      ->where('study_records.user_id', '=', $user_id)
      ->where('study_records.study_date', '=', $date)
      ->order_by('study_records.id', 'DESC')
      ->execute()
      ->as_array();
  }

  /*
  * 指定日の合計学習時間を取得
  */
  public static function get_total_minutes_by_date($user_id, $date)
  {
    $result = DB::select(
      [DB::expr('COALESCE(SUM(total_minutes), 0)'), 'sum_minutes']
    )
      ->from('study_records')
      ->where('user_id', '=', $user_id)
      ->where('study_date', '=', $date)
      ->execute()
      ->current();

    return (int) ($result['sum_minutes'] ?? 0);
  }

  /*
  * 期間内の日別合計を取得（週間グラフ用）
  */
  public static function get_daily_totals($user_id, $start_date, $end_date)
  {
    return DB::select(
      ['study_date', 'study_date'],
      [DB::expr('COALESCE(SUM(total_minutes), 0)'), 'sum_minutes']
    )
      ->from('study_records')
      ->where('user_id', '=', $user_id)
      ->where('study_date', 'between', [$start_date, $end_date])
      ->group_by('study_date')
      ->execute()
      ->as_array();
  }

  /*
  * 全科目一覧用のクエリを取得
  */
  public static function get_all_with_sort($user_id, $sort)
  {
		// subjectsとstudy_recordsテーブルをJOINして、関連データを一緒に取得
    $q = DB::select(
      ['study_records.id', 'id'],
      ['study_records.study_date', 'study_date'],
      ['study_records.hours', 'hours'],
      ['study_records.minutes', 'minutes'],
      ['study_records.total_minutes', 'total_minutes'],
      ['subjects.name', 'subject_name']
    )
      ->from('study_records')
      ->join('subjects', 'INNER')
      ->on('study_records.subject_id', '=', 'subjects.id')
      ->where('study_records.user_id', '=', $user_id);

		// 並び替え
    switch ($sort)
    {
      case 'date': // 記録日付の最新順
        $q->order_by('study_records.study_date', 'DESC')
          ->order_by('study_records.id', 'DESC'); //同じ日付ならIDが大きい順
        break;

      case 'long': // 学習時間が長い順
        $q->order_by('study_records.total_minutes', 'DESC')
          ->order_by('study_records.id', 'DESC'); //同じ日付ならIDが大きい順
        break;

      case 'subject': // 日本語、英字(ABC)の順にしたい
        // 科目名が英字で始まるかどうか(英字なら１、日本語なら０)
        $q->order_by(DB::expr("CASE WHEN subjects.name REGEXP '^[A-Za-z]' THEN 1 ELSE 0 END"), 'ASC') 
          ->order_by('subjects.name', 'ASC') // 日本語名科目(0)から英語名科目(1)の ascending order
          ->order_by('study_records.id', 'DESC'); //同じ日付ならIDが大きい順
        break;

      case 'added': // 追加順（新しい順）ページのデフォルト
      default:
        $q->order_by('study_records.id', 'DESC'); //同じ日付ならIDが大きい順
        break;
    }

    return $q->execute()->as_array();
  }

  /*
  * 学習記録を追加
  */
  public static function create($user_id, $subject_id, $data)
  {
    return DB::insert('study_records')->set([
      'user_id'       => $user_id,
      'subject_id'    => $subject_id,
      'study_date'    => $data['study_date'],
      'hours'         => $data['hours'],
      'minutes'       => $data['minutes'],
      'total_minutes' => $data['total_minutes'],
    ])->execute();
  }

  /*
  * 学習記録を削除
  */
  public static function delete($id, $user_id)
  {
    return DB::delete('study_records')
      ->where('id', '=', $id)
      ->where('user_id', '=', $user_id)
      ->execute();
  }

  /*
  * 自分の記録か確認
  */
  public static function find_own($id, $user_id)
  {
    return DB::select('id')
      ->from('study_records')
      ->where('id', '=', $id)
      ->where('user_id', '=', $user_id)
      ->execute()
      ->current();
  }

  /*
  * 学習記録を更新
  */
  public static function update($id, $user_id, $data)
  {
    return DB::update('study_records')->set([
      'subject_id'    => $data['subject_id'],
      'study_date'    => $data['study_date'],
      'hours'         => $data['hours'],
      'minutes'       => $data['minutes'],
      'total_minutes' => $data['total_minutes'],
    ])
      ->where('id', '=', $id)
      ->where('user_id', '=', $user_id)
      ->execute();
  }
}