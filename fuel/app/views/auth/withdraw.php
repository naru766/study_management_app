<section class="auth-screen">
  <div class="auth-card">
    <div class="auth-panel">
      <div class="auth-panel__title">退会手続き</div>

      <?php if ($msg = Session::get_flash('success')): ?>
        <div class="alert alert--success"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = Session::get_flash('error')): ?>
        <div class="alert alert--error"><?= e($msg) ?></div>
      <?php endif; ?>

      <!-- action_withdrawにpostで送信 -->
      <?= Form::open(['action' => '/auth/withdraw', 'method' => 'post', 'class' => 'auth-form', 'id' => 'withdraw-form']) ?>
      <?= Form::csrf() ?>

      <!-- emailのラベルとフォーム -->
      <div class="form-row">
        <div class="form-label">メールアドレス</div>
        <?= Form::input('email', Input::post('email'), [
          'type' => 'email', 'class' => 'form-input', 'placeholder' => 'Email'
        ]) ?>
      </div>

      <!-- passwordのラベルとフォーム -->
      <div class="form-row">
        <div class="form-label">パスワード</div>
        <?= Form::password('password', null, [
          'class' => 'form-input', 'placeholder' => 'Password'
        ]) ?>
      </div>

      <!-- usernameのラベルとフォーム -->
      <div class="form-row">
        <div class="form-label">ユーザー名</div>
        <?= Form::input('username', '', [
          'class' => 'form-input', 'placeholder' => 'ユーザー名'
        ]) ?>
      </div>

      <!-- チェックボックス、初期値はfalse -->
      <label class="form-check">
        <?= Form::checkbox('agree', '1', false, ['id' => 'agree', 'class' => 'form-check__input']) ?>
        <span class="form-check__label">退会することに同意します。</span>
      </label>

      <!-- すべてのフォームとチェックボックスを記入しないと退会ボタンをクリックできない -->
      <?= Form::submit('submit', '退会', [
        'class' => 'btn btn--sm btn--danger btn--block', 'id' => 'withdraw-submit', 'disabled' => 'disabled'
      ]) ?>

      <p class="auth-links">
        ログインは <?= Html::anchor('/auth/login', 'こちら', ['class'=>'link link--login']) ?> から
      </p>
      <?= Form::close() ?>
    </div>
  </div>
</section>