<?php

namespace Pangphp\Search;

class SearchService {

  protected $_elastic;

  public $total_items;
  public $ids = [];
  public $seach_max_score;

  function __construct($elastic) {
    $this->_elastic = $elastic;
  }

  function getSearchResults($index, $type, $term) {
    $params = [
      'index' => $index,
      'type' => $type,
      'body' => [
        'query' => [
          'wildcard' => [
            'search_body' => "*" . $term . "*"
          ]
        ]
      ]
    ];

    return $this->_elastic->search($params); 
  }

  function search($index, $type, $term) {
    
    $search_results = $this->getSearchResults($index, $type, $term);

    $this->total_items = $search_results["hits"]["total"];
    $this->max_score = $search_results["hits"]["max_score"];
    $this->setResultingIds($search_results["hits"]["hits"]);

  }

  protected function setResultingIds($hits) {
    
    foreach($hits as $hit) {
      if(isset($hit["_source"]["id"])) {
        array_push($this->ids, $hit["_source"]["id"]);
      }
    }

  }

}