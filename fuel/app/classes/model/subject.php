<?php
class Model_Subject
{
  /*
	* 科目名からIDを取得（なければ新規作成）
	*/
  public static function find_or_create($user_id, $name)
  {
    // 既存の科目を検索
    $row = DB::select('id')
      ->from('subjects')
      ->where('user_id', '=', $user_id)
      ->where('name', '=', $name)
      ->execute()
      ->current();

    if ($row)
    {
      return (int) $row['id'];
    }

    // 新規作成
    list($id,) = DB::insert('subjects')->set([
      'user_id'    => $user_id,
      'name'       => $name,
      'color_code' => '#' . substr(md5($name), 0, 6),
    ])->execute();

    return (int) $id;
  }
}