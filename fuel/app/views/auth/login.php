<section class="auth-screen">
  <div class="auth-card">
    <div class="brand-pill">Study Time!</div>

    <div class="auth-panel">
      <div class="auth-panel__title">ログイン</div>

      <?php if ($msg = Session::get_flash('success')): ?>
        <div class="alert alert--success"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = Session::get_flash('error')): ?>
        <div class="alert alert--error"><?= e($msg) ?></div>
      <?php endif; ?>

      <!-- action_loginにpostで送信 -->
      <?= Form::open(['action' => '/auth/login', 'method' => 'post', 'class' => 'auth-form']) ?>
      <?= Form::csrf() ?>

      <!-- emailのラベルとフォーム, 初期値は前回の入力 -->
      <div class="form-row">
        <div class="form-label">メールアドレス</div>
        <?= Form::input('email', Input::post('email'), [
          'type' => 'email', 'class' => 'form-input', 'placeholder' => 'Email'
        ]) ?>
      </div>

      <!-- emailのラベルとフォーム, 初期値はnull -->
      <div class="form-row">
        <div class="form-label">パスワード</div>
        <?= Form::password('password', null, [
          'class' => 'form-input', 'placeholder' => 'Password'
        ]) ?>
      </div>

      <!-- ログインボタン -->
      <?= Form::submit('submit', 'ログイン', ['class' => 'btn btn--sm btn--primary btn--block']) ?>

      <p class="auth-links">
        新規登録は <?= Html::anchor('/auth/register', 'こちら', ['class'=>'link link--register']) ?> から<br>
        <?= Html::anchor('/auth/withdraw', '退会手続き', ['class'=>'link link--muted']) ?>
      </p>
      <?= Form::close() ?>
    </div>
  </div>
</section>