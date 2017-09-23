<?php

$path = dirname(__FILE__);

$pangphp_entities_array = [
  'Sessions',
  'EditableLists',
  'Errors'
];

foreach($pangphp_entities_array as $k => $folder) {

  $pangphp_entities_array[$k] = $path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . 'Entities';

  if(!is_dir($pangphp_entities_array[$k])) {
    throw new Exception("Could not find: " . $pangphp_entities_array[$k]);
  }

}

