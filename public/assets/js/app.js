/*
共通のhelper function
すべての入力フォームがからでないかチェック
空白を除去
*/
function allFilled(inputs) {
  return Array.from(inputs).every((el) => (el.value || "").trim().length > 0);
}

/*
Withdraw (退会)
*/
function initWithdraw() {
  const form = document.getElementById("withdraw-form");
  if (!form) return; // フォームがなければ何もしない

  const submit = document.getElementById("withdraw-submit");
  const agree = document.getElementById("agree"); // 同意チェックボックス
  if (!submit || !agree) return;

  // 入力フォームを取得
  const inputs = form.querySelectorAll(
    'input[name="email"], input[name="password"], input[name="username"]'
  );

  // ボタンの有効化｜無効化
  const update = () => {
    submit.disabled = !(allFilled(inputs) && agree.checked);
  };

  // 入力変更時にupdate()を実行
  inputs.forEach((el) => el.addEventListener("input", update));
  agree.addEventListener("change", update);

  // 初期ロード
  update();
}

/*
Register (新規登録)
*/
function initRegister() {
  const form = document.getElementById("register-form");
  if (!form) return; // フォームがなければ何もしない

  const submit = document.getElementById("register-submit");
  if (!submit) return;

  // 入力フォームを取得
  const inputs = form.querySelectorAll(
    'input[name="email"], input[name="password"], input[name="username"]'
  );

  // ボタンの有効化｜無効化
  const update = () => {
    submit.disabled = !allFilled(inputs);
  };

  // 入力変更時にupdate()を実行
  inputs.forEach((el) => el.addEventListener("input", update));

  // 初期ロード
  update();
}

/*
Edit (全科目一覧の編集モーダル)
*/
function initEditModal() {
  const modal = document.getElementById("edit-modal");
  const form = document.getElementById("edit-form");
  if (!modal || !form) return;

  // モーダルを開く
  const open = () => {
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
  };

  //　モーダルを閉じる
  const close = () => {
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
  };

  // xボタンや背景をクリックで閉じる（data-closer属性があるものをクリックすると閉じる）
  modal.addEventListener("click", (e) => {
    if (e.target.matches("[data-close]")) close();
  });

  // Escapeキーで閉じる
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modal.classList.contains("is-open")) close();
  });

  // .js-edit属性ボタンを押したら開く
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-edit");
    if (!btn) return;

    // data-*属性からデータを取得して入力フォームにセット
    document.getElementById("edit-id").value = btn.dataset.recordId || "";
    document.getElementById("edit-date").value = btn.dataset.date || "";
    document.getElementById("edit-subject").value = btn.dataset.subject || "";
    document.getElementById("edit-hours").value = btn.dataset.hours || "0";
    document.getElementById("edit-minutes").value = btn.dataset.minutes || "0";

    // エラーメッセージをクリア
    const msg = document.getElementById("edit-msg");
    if (msg) msg.textContent = "";
    open();
  });

  // 保存をクリックしたら開始
  form.addEventListener("submit", async (e) => {
    e.preventDefault(); // 通常のフォーム送信を止める

    const msg = document.getElementById("edit-msg");
    if (msg) msg.textContent = "";

    const fd = new FormData(form); // フォームデータを取得

    try {
      // サーバーにPOST
      const res = await fetch("/records/api_update", {
        method: "POST",
        body: fd,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      });

      // HTTPエラー
      if (!res.ok) {
        // 500/404 など
        if (msg) msg.textContent = `更新に失敗しました（HTTP ${res.status}）`;
        return;
      }

      // JSONを解析
      const data = await res.json();

      // アプリケーションエラー
      if (!data.ok) {
        if (msg) msg.textContent = data.error || "更新に失敗しました";
        return;
      }

      // OK: ページをリロード
      location.reload();
    } catch (err) {
      // ネットワークエラー
      if (msg) msg.textContent = "通信に失敗しました";
    }
  });
}

/*
Boot
DOMContentLoadedで登録した関数を安全に実行
*/
document.addEventListener("DOMContentLoaded", () => {
  initWithdraw();
  initRegister();
  initEditModal();
});
