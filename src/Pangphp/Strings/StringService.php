<?php

namespace Pangphp\Strings;

class StringService {

  function randomStringGenerator($int) {
    $str = "";

    for($i = 0; $i < $int; $i++) {
        $rand = rand(0, 2);
        $caps = chr(rand(65, 90));
        $nums = chr(rand(48, 57));
        $lows = chr(rand(97, 122));
        
        $array = array($caps, $nums, $lows);
        
        $str .= $array[$rand];
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