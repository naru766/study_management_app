<?php

class Controller_Auth extends Controller_Template
{
  public $template = 'template_auth';

  /*
  ログインコントローラ: /auth/login
  */
  public function get_login()
  {
    $this->template->chrome = false;
    $this->template->title = 'ログイン - Study Time!';
    $this->template->content = View::forge('auth/login');
  }

  public function post_login()
  { 
    if (Security::check_token() === false)
    {
      Session::set_flash('error', 'セキュリティエラーが発生しました');
      return Response::redirect('auth/login');
    }

    $email = trim((string) Input::post('email'));
    $password = (string) Input::post('password');

    // ユーザーデータを取得
    $user = Model_User::find_by_email($email);

    // 入力されたパスワードと抽出されたパスワードを比較
    if ($user && password_verify($password, $user['password']))
    {
      // ログイン成功
      Session::set('user_id', $user['id']);
      Session::set('username', $user['username']);
      Session::set('email', $user['email']);
      return Response::redirect('/dashboard');
    }
    else
    {
      // ログイン失敗
      Session::set_flash('error', 'メールアドレスまたはパスワードが正しくありません');
      return Response::redirect('/auth/login');
    }
  }

  /* 
  アカウント登録コントローラ: /auth/register
  */
  public function get_register()
  {
    $this->template->chrome = false;
    $this->template->title = '新規登録';
    $this->template->content = View::forge('auth/register');
  }

  public function post_register()
  {
    if (Security::check_token() === false)
    {
      Session::set_flash('error', 'セキュリティエラーが発生しました');
      return Response::redirect('auth/register');
    }

    $username = trim((string) Input::post('username', ''));
    $email = trim((string) Input::post('email', ''));
    $password = (string) Input::post('password', '');

    if (mb_strlen($username, 'UTF-8') > 7) // ユーザー名は最大７文字
    {
      Session::set_flash('error', 'ユーザー名は７文字以内で入力してください');
    }
    elseif (strlen($password) > 14) // パスワードは最大１４文字
    {
      Session::set_flash('error', 'パスワードは１４文字以内で入力してください');
    }
    else
    {
      // 同じemailで登録したアカウントがあるかをチェック
      if (Model_User::email_exists($email))
      {
        // 同じemailで登録されたアカウントがある
        Session::set_flash('error', 'このメールアドレスはすでに登録されています');
        return Response::redirect('/auth/register');
      }
      else // アカウント新規作成
      {
        // 'users'テーブルに新規ユーザーデーターを挿入
        Model_User::create($username, $email, $password);

        Session::set_flash('success', '登録完了しました。ログインして下さい');
        return Response::redirect('/auth/login');
      }
    }

    $this->template->chrome = false;
    $this->template->title = '新規登録';

    // views/auth/register.phpにアクセス
    $this->template->content = View::forge('auth/register');
  }

  /*
  ログアウトコントローラ: /auth/logout
  */
  public function action_logout()
  {
    // ログイン情報を削除
    Session::delete('user_id');
    Session::delete('username');
    Session::delete('email');

    // ログイン画面にジャンプ
    Session::set_flash('success', 'ログアウトしました');
    return Response::redirect('/auth/login');
  }

  /*
  退会コントローラ /auth/withdraw
  */
  public function get_withdraw()
  {
    $this->template->chrome = false;
    $this->template->title = '退会手続き';
    $this->template->content = View::forge('auth/withdraw');
  }
  
  public function post_withdraw()
  {
    if (Security::check_token() === false)
    {
      Session::set_flash('error', 'セキュリティエラーが発生しました');
      return Response::redirect('auth/withdraw');
    }

    $username = trim((string) Input::post('username', ''));
    $email = trim((string) Input::post('email', ''));
    $password = (string) Input::post('password', '');
    $agree = Input::post('agree', '0') === '1';

    // チェックボックスの同意
    if (!$agree)
    {
      Session::set_flash('error', '退会に同意してください');
      return Response::redirect('/auth/withdraw');
    }

    $user = Model_User::find_by_username_and_email($username, $email);

    // '$user'にデータが抽出できなかった
    if (!$user)
    {
      Session::set_flash('error', 'アカウントが見つかりません');
      return Response::redirect('/auth/withdraw');
    }

    // username, email, passwardのいずれかが一致しない
    if ($username !== $user['username'] || $email !== $user['email'] || !password_verify($password, $user['password']))
    {
      Session::set_flash('error', 'メールアドレス、ユーザー名、パスワードが一致しません。');
      return Response::redirect('/auth/withdraw');
    }

    try
    {
      Model_User::delete($user['id']);
    }
    catch (Database_Exception $e) // DBエラーが起きたら
    {
      Session::set_flash('error', '退会処理に失敗しました');
      return Response::redirect('/auth/withdraw');
    }

    Session::set_flash('success', '退会が完了しました');
    return Response::redirect('/auth/login');
  }
}