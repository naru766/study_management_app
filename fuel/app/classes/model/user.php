<?php
class Model_User
{
  /*
  * メールアドレスでユーザーを検索（ログイン用）
  */
  public static function find_by_email($email)
  {
    return DB::select('*')
      ->from('users')
      ->where('email', '=', $email)
      ->where('deleted_at', 'is', null)
      ->execute()
      ->current();
  }

  /*
  * ユーザー名とメールアドレスでユーザーを検索（退会用）
  */
  public static function find_by_username_and_email($username, $email)
  {
    return DB::select('*')
      ->from('users')
      ->where('username', '=', $username)
      ->where('email', '=', $email)
      ->where('deleted_at', 'is', null)
      ->execute()
      ->current();
  }

  /*
  * メールアドレスの重複チェック
  */
  public static function email_exists($email)
  {
    $result = DB::select('id')
      ->from('users')
      ->where('email', '=', $email)
      ->execute()
      ->current();
    
    return (bool) $result;
  }

  /*
  * 新規ユーザーを作成
  */
  public static function create($username, $email, $password)
  {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    return DB::insert('users')
      ->set([
        'username' => $username,
        'email'    => $email,
        'password' => $hash,
      ])
      ->execute();
  }

  /*
  * ユーザーを削除
  */
  public static function delete($user_id)
  {
    return DB::delete('users')
      ->where('id', '=', $user_id)
      ->execute();
  }
}