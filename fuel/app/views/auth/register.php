<section class="auth-screen">
  <div class="auth-card">
    <div class="auth-panel">
      <div class="auth-panel__title">新規登録</div>

      <?php if ($msg = Session::get_flash('success')): ?>
        <div class="alert alert--success"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = Session::get_flash('error')): ?>
        <div class="alert alert--error"><?= e($msg) ?></div>
      <?php endif; ?>

      <!-- action_registerにpostで送信、app.jsで参照 -->
      <?= Form::open(['action' => '/auth/register', 'method' => 'post', 'class' => 'auth-form', 'id' => 'register-form']) ?>
      <?= Form::csrf() ?>

      <!-- emailのラベルとフォーム -->
      <div class="form-row">
        <div class="form-label">メールアドレス</div>
        <?= Form::input('email', Input::post('email', ''), [
          'type' => 'email',
          'class' => 'form-input',
          'placeholder' => 'Email',
          'required' => true
        ]) ?>
      </div>

      <!-- passwordのラベルとフォーム -->
      <div class="form-row">
        <div class="form-label">パスワード</div>
        <?= Form::password('password', null, [
          'class' => 'form-input',
          'maxlength' => 14,
          'placeholder' => '最大14文字',
          'required' => true
        ]) ?>
      </div>

      <!-- usernameのラベルとフォーム -->
      <div class="form-row">
        <div class="form-label">ユーザー名</div>
        <?= Form::input('username', Input::post('username', ''), [
          'class' => 'form-input',
          'maxlength' => 7,
          'placeholder' => '最大７文字',
          'required' => true
        ]) ?>
      </div>

      <!-- 登録ボタン -->
      <?= Form::submit('submit', '登録', [
        'class' => 'btn btn--sm btn--primary btn--block',
        'id' => 'register-submit',
        'disabled' => 'disabled',
      ]) ?>

      <p class="auth-links">
        ログインは <?= Html::anchor('/auth/login', 'こちら', ['class'=>'link--register']) ?> から
      </p>

      <?= Form::close() ?>
    </div>
  </div>
</section>