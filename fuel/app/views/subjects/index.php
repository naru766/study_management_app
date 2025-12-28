<?php
  // ユーザー名とアイコンを取得
  $uname = $uname ?? (string) Session::get('username', '');
  $avatar = $avatar ?? mb_substr($uname, 0, 1, 'UTF-8');
?>

<div class="dash-screen">
  <section class="dash dash--panel">

    <!-- 上部 -->
    <div class="dash__nav dash__nav--3col">
      <!-- ページタイトル -->
      <div class="tabs">
        <span class="tab tab--active">全科目一覧</span>
      </div>
    
      <!-- ユーザー名とアイコン -->
      <div class="userbox">
        <span class="userbox__name"><?= e($uname) ?></span>
        <span class="avatar" aria-hidden="true"><?= e($avatar) ?></span>
      </div>

      <!-- ログアウトボタン -->
      <div class="logoutbox">
        <?= Html::anchor('/auth/logout', 'ログアウト', ['class' => 'btn btn--outline']) ?>
      </div>
    </div>

    <!-- 並び替えボタン -->
    <div class="list-actions">
      <?= Html::anchor('/subjects?sort=added', '追加順', ['class' => 'btn btn--sort ' . ($sort === 'added' ? 'is-active' : '')]) ?>
      <?= Html::anchor('/subjects?sort=date', '日付順', ['class' => 'btn btn--sort ' . ($sort === 'date' ? 'is-active' : '')]) ?>
      <?= Html::anchor('/subjects?sort=long', '長い順', ['class' => 'btn btn--sort ' . ($sort === 'long' ? 'is-active' : '')]) ?>
      <?= Html::anchor('/subjects?sort=subject', '科目順', ['class' => 'btn btn--sort ' . ($sort === 'subject' ? 'is-active' : '')]) ?>
    </div>

    <!-- リスト(右側スクロール) -->
    <!-- 記録エレメントの並び: 科目　’科目名’ ’学習時間’ ’学習日付’　編集ボタン　削除ボタン -->
    <div class="list-box">
      <?php if (!empty($records)): ?>
        <?php foreach ($records as $r): ?>
          <div class="list-row">
            <div class="list-row__main">
              <span class="list-row__label">科目：</span>
              <span class="list-row__subject"><?= e($r['subject_name']) ?></span>
              <span class="list-row__time"><?= e(Helper_Format::hm($r['total_minutes'])) ?></span>
              <span class="list-row__date"><?= e(Helper_Format::jp_date($r['study_date'])) ?></span>
            </div>

            <div class="list-row__icons">
              <!-- 編集（ペン） JavaScriptで取得-->
              <!-- data-*でJSにデータを渡す -->
              <button
                type="button"
                class="icon-btn js-edit"
                title="編集"
                data-record-id="<?= (int) $r['id'] ?>"
                data-subject="<?= e($r['subject_name']) ?>"
                data-date="<?= e($r['study_date']) ?>"
                data-hours="<?= (int) ($r['hours'] ?? 0) ?>"
                data-minutes="<?= (int) ($r['minutes'] ?? 0) ?>"
              >✏️</button>

              <!-- 削除（ゴミ箱）POSTのみ -->
              <?= Form::open(['action' => '/records/delete/' . $r['id'], 'method' => 'post', 'class' => 'icon-form']) ?>
              <?= Form::csrf() ?>
              <button type="submit" class="icon-btn" title="削除"
                onclick="return confirm('この記録を削除しますか？');">🗑️</button>
              <?= Form::close() ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="list-empty">記録がありません</div>
      <?php endif; ?>
    </div>

    <!-- 下部ボタン -->
    <div class="list-footer">
      <?= Html::anchor('/weekly', '週間グラフ', ['class' => 'btn btn--big btn--pill']) ?>
      <?= Html::anchor('/dashboard', 'ダッシュボード', ['class' => 'btn btn--big btn--pill']) ?>
    </div>

  </section>
</div>

<!-- 編集モーダル -->
<div class="modal" id="edit-modal" aria-hidden="true">
  <!-- 背景をクリックで閉じる -->
  <div class="modal__backdrop" data-close></div>
  
  <div class="modal__panel" role="dialog" aria-modal="true" aria-label="学習記録の編集">
    <div class="modal__head">
      <div class="modal__title">記録を編集</div>
      <button type="button" class="modal__close" data-close>x</button>
    </div>
    
    <!-- 入力フォーム -->
    <form id="edit-form">
      <?= Form::csrf() ?>
      <input type="hidden" name="record_id" id="edit-id">

      <div class="modal__row">
        <label>日付</label>
        <input class="input" type="date" name="study_date" id="edit-date" required>
      </div>

      <div class="modal__row">
        <label>科目</label>
        <input class="input" type="text" name="subject" id="edit-subject" required>
      </div>

      <div class="modal__row modal__row--split">
        <div>
          <label>時間</label>
          <input class="input" type="number" name="hours" id="edit-hours" min="0" max="23" required>
        </div>
        <div>
          <label>分</label>
          <input class="input" type="number" name="minutes" id="edit-minutes" min="0" max="59" required>
        </div>
      </div>

      <div class="modal__actions">
        <button type="button" class="btn btn--outline" data-close>キャンセル</button>
        <button type="submit" class="btn btn--pill">保存</button>
      </div>

      <p class="modal__msg" id="edit-msg"></p>
    </form>
  </div>
</div>