<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? $title : 'Study Time!'; ?></title>
  <?php
    // 既存の bootstrap を使う場合は残す。不要なら 'bootstrap.css' を外してOK。
    echo Asset::css(['bootstrap.css', 'app.css']);
  ?>
</head>
<body>
  <?php echo $content; ?>
  <?php echo Asset::js(['app.js']); ?>
</body>
</html>