<?php

class Controller_Base extends Controller_Template
{
  protected $user_id;
  public $template = 'template_auth';

  public function before()
  {
    // Controller_Templateのbefore()をコール
    parent::before();
    $this->template->chrome = false;

    // ログインステータスをチェック
    if (!Session::get('user_id'))
    {
      Session::set_flash('error', 'ログインが必要です');
      Response::redirect('/auth/login');
    }

    // プロパティにuser_idを保存
    $this->user_id = Session::get('user_id');

    // 継承先のユーザー情報のグローバル変数
    $this->template->set_global('current_user', [
      'id' => Session::get('user_id'),
      'username' => Session::get('username'),
      'email' => Session::get('email')
    ]);
  }
}