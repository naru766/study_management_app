<?php
class Helper_Format
{
  /*
   * 合計分数を'X時間Y分'に変換
   */
  public static function hm($min)
  {
    $min = (int) $min;
    $h = intdiv($min, 60);
    $m = $min % 60;
    return $h . '時間' . $m . '分';
  }

  /*
   * 'YYYY-mm-dd'を'YYYY年m月d日'に変換
   */
  public static function jp_date($ymd)
  {
    $ts = strtotime($ymd);
    return date('Y年n月j日', $ts);
  }
}