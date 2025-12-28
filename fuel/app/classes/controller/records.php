<?php

class Controller_Records extends Controller_Base
{
  /*
  記録の追加 POST: /records/add
  */
  public function post_add()
  {
    if (Security::check_token() === false) 
    {
      Session::set_flash('error', 'セキュリティエラーが発生しました');
      return Response::redirect('/dashboard');
    }

    // フォームからデータを取得
    $subject_name = trim((string) Input::post('subject', ''));
    $hours        = (int) Input::post('hours', 0);
    $minutes      = (int) Input::post('minutes', 0);
    $study_date   = (string) (Input::post('study_date') ?: date('Y-m-d'));

    // YYYY-MM-DDのフォーマット維持、/ を　- に変換
    $study_date = str_replace('/', '-', $study_date);

    // バリデーション（入力チェック）
    if ($subject_name === '') 
    {
      Session::set_flash('error', '科目を入力してください');
      return Response::redirect('/dashboard');
    }
    if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59)
    {
      Session::set_flash('error', '時間/分を入力し直してください');
      return Response::redirect('/dashboard');
    }

    // 合計分数に変換
    $total_minutes = $hours * 60 + $minutes;

    // 科目名からIDを取得、なければ新規作成
    $subject_id = Model_Subject::find_or_create($this->user_id, $subject_name);

    // DBに挿入
    Model_StudyRecord::create($this->user_id, $subject_id, [
      'study_date'    => $study_date,
      'hours'         => $hours,
      'minutes'       => $minutes,
      'total_minutes' => $total_minutes,
    ]);

    Session::set_flash('success', '学習記録を追加しました');
    return Response::redirect('/dashboard');
  }

  /*
  記録の削除 POST: /records/delete/{id}
  idは学習記録のID
  */
  public function post_delete($id = null)
  {
    //　科目IDの取得
    $id = (int) $id;
    if ($id <= 0) return Response::redirect('/subjects');

    if (Security::check_token() === false)
    {
      Session::set_flash('error', 'セキュリティエラーが発生しました');
      return Response::redirect('/subjects');
    }

    // DBから記録データを削除
    $deleted = Model_StudyRecord::delete($id, $this->user_id);

    Session::set_flash('success', $deleted ? '記録を削除しました' : '削除対象が見つかりません');
    return Response::redirect('/subjects');
  }

  /*
  モーダル更新（JSON）: /records/api_update
  記録編集の際の、ページ遷移なし
  */
  public function post_api_update()
  {
    if (Security::check_token() === false)
    {
      return $this->json(['ok' => false, 'error' => 'セキュリティエラー'], 400);
    }

    // フォームからデータを取得
    $id         = (int) Input::post('record_id', 0);
    $study_date = trim((string) Input::post('study_date', ''));
    $subject    = trim((string) Input::post('subject', ''));
    $hours      = (int) Input::post('hours', 0);
    $minutes    = (int) Input::post('minutes', 0);

    // YYYY-MM-DDのフォーマット維持。/ を　- に変換
    $study_date = str_replace('/', '-', $study_date);

    // バリデーション
    if ($id <= 0 || $study_date === '' || $subject === '') 
    {
      return $this->json(['ok' => false, 'error' => '入力が不正です'], 400);
    }
    if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) 
    {
      return $this->json(['ok' => false, 'error' => '時間/分を入力し直してください'], 400);
    }

    // 自分のレコードか確認
    $rec = Model_StudyRecord::find_own($id, $this->user_id);

    if (!$rec) 
    {
      return $this->json(['ok' => false, 'error' => '記録が見つかりません'], 404);
    }

    // 科目名から科目IDを取得
    $subject_id = Model_Subject::find_or_create($this->user_id, $subject);

    // 合計分数に変換
    $total = $hours * 60 + $minutes;

    // DBを更新
    Model_StudyRecord::update($id, $this->user_id, [
      'subject_id'    => $subject_id,
      'study_date'    => $study_date,
      'hours'         => $hours,
      'minutes'       => $minutes,
      'total_minutes' => $total,
    ]);

    return $this->json(['ok' => true]);
  }

  /*
  ======== helper function ===
  */

  /*
  JavaScriptにデータを返す
  */
  private function json(array $payload, int $status = 200)
  {
    return Response::forge(
      json_encode($payload), // 配列をJSON文字列に変換
      $status, // HTTPステータスコード: OK
      ['Content-Type' => 'application/json; charset=utf-8']
    );
  }
}
