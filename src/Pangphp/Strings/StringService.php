<?php

namespace Pangphp\Strings;

class StringService {

  function randomStringGenerator($int) {
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $max = mb_strlen($keyspace, '8bit') - 1;
    $str = "";
    for ($i = 0; $i < $int; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
  }

  function arrayToSearchString($array, $arr = null) {
    $array_of_values = isset($arr) ? $arr : [];
    foreach($array as $k=>$v) {
      if(is_array($v)) {
        $array_of_values = $this->arrayToSearchString($v, $array_of_values);
      } else if($v instanceof \DateTime) {
        $array_of_values[] = $v->format('Y-m-d');
      } else {
        $array_of_values[] = $v;
      }
    }
    return $array_of_values;
  }
  
}

?>