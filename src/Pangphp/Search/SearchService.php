<?php

namespace Pangphp\Search;

class SearchService {

  protected $_elastic;
  protected $_config;

  public $total_items;
  public $ids = [];
  public $seach_max_score;

  function __construct($elastic, $config) {
    $this->_elastic = $elastic;
    $this->_config = $config;
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

  function createIndex($type, $id, $body) {
    
    $params = [
      'index' => $this->_config->get("search_index"),
      'type' => $type,
      'id' => $id,
      'body' => ["search_body" => $body]
    ];

    $this->_elastic->index($params);

  }

  function updateIndex($type, $id, $body) {
    $params = [
      'index' => $this->_config->get("search_index"),
      'type' => $type,
      'id' => $id,
      'body' => ["search_body" => $body]
    ];

    $this->_elastic->index($params);
  }

  function delete() {
    $params = [
        'index' => $this->_config->get("search_index"),
        'type' => $type,
        'id' => 'my_id'
    ];
  
    // Delete doc at /my_index/my_type/my_id
    $response = $client->delete($params);
  }

}