<?php
  // 表示用: ユーザー名、頭文字のアイコン、今日の日付、上位3科目
  $uname = isset($current_user['username']) ? $current_user['username'] : Session::get('username');
  $avatar = mb_substr((string) $uname, 0, 1, 'UTF-8'); // $unameの頭文字
  $jp_today = date('n月j日', strtotime($today));
?>
<div class="dash-screen">
  <section class="dash dash--panel">
    <!-- 上部 -->
    <!-- 左: ページタイトル -->
    <div class="dash__nav dash__nav--3col">
      <div class="tabs">
        <span class="tab tab--active">ダッシュボード</span>
      </div>

      <!-- 中央: ユーザー名とアイコン -->
      <div class="userbox">
        <span class="userbox__name"><?= e($uname) ?></span>
        <span class="avatar" aria-hidden="true"><?= e($avatar) ?></span>
      </div>

      <!-- 右: ログアウトボタン -->
      <div class="logoutbox">
        <?= Html::anchor('/auth/logout', 'ログアウト', ['class' => 'btn btn--outline']) ?>
      </div>
    </div>

    <div class="dash__grid">
      <!-- 下部: 合計時間と追加フォーム -->
      <div class="dash__left">
        <div class="dash__headline">今日の学習時間：</div>
        <div class="dash__total"><?= e($total_hm) ?></div>

        <?= Form::open(['action' => '/records/add', 'method' => 'post', 'class' => 'addform']) ?>
        <?= Form::csrf() ?>

        <div class="addform__row">
          <?php
            // Fuel側の$todayの日付の区切りを'-'に固定
            $date_value = Input::post('study_date', $today);
            $date_value = str_replace('/', '-', (string) $date_value);
            $date_value = date('Y-m-d', strtotime($date_value));
          ?>

          <!-- 日付フォーム -->
          <?= Form::input('study_date', $date_value, [
            'type' => 'date',
            'class' => 'input input--date',
            'required' => true,
          ]) ?>
        </div>

        <!-- 科目、時間、分フォーム -->
        <div class="addform__row addform__row--split">
          <?= Form::input('subject', Input::post('subject', ''), [
            'class' => 'input', 'placeholder' => '科目', 'required' => true
          ]) ?>
          <?= Form::input('hours', Input::post('hours', ''), [
            'type' => 'number', 'class' => 'input', 'min' => 0, 'max' => 23, 'placeholder' => '時間', 'required' => true
          ]) ?>
          <?= Form::input('minutes', Input::post('minutes', ''), [
            'type' => 'number', 'class' => 'input', 'min' => 0, 'max' => 59, 'placeholder' => '分', 'required' => true
          ]) ?>
        </div>

        <?= Form::submit('submit', '追加', ['class' => 'btn btn--primary btn--block']) ?>
        <?= Form::close() ?>
      </div>

      <!-- 右：週間グラフと全科目一覧ボタン、上位3科目カード -->
      <div class="dash__right">
        <div class="dash__actions">
          <?= Html::anchor('/weekly', '週間グラフ', ['class' => 'btn btn--big btn--pill']) ?>
          <?= Html::anchor('/subjects', '全科目一覧', ['class' => 'btn btn--big btn--pill']) ?>
        </div>

        <div class="card">
          <div class="card__title">今日の上位３科目</div>
          <div class="card__subtitle"><?= e($jp_today) ?></div>
          <ol class="rank">
            <?php if ($top3): ?>
              <?php foreach ($top3 as $name => $min): ?>
                <li><?= e($name) ?> <?= e(Helper_Format::hm($min)) ?></li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="rank-empty">記録がありません</li>
            <?php endif; ?>
          </ol>
        </div>
      </div>
    </div>
  </section>
</div>