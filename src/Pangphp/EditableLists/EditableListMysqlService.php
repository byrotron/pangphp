<?php

namespace Pangphp\EditableLists;
use \Pangphp\EditableLists\Entities\EditableList;
use \Pangphp\EditableLists\Entities\EditableListItem;
use \Pangphp\EditableLists\Entities\EditableListGroup;

use \Doctrine\ORM\EntityManager;

class EditableListMysqlService {

  protected $_em;

  function __construct(EntityManager $em) {
    $this->_em = $em;
  }

  function getGroupLists($group_name) {
    $qb = $this->_em->createQueryBuilder();

    $qb->select('g', 'l', 'i')
      ->from('Pangphp\EditableLists\Entities\EditableListGroup')
      ->innerJoin('g.lists', 'l')
      ->innerJoin('l.items', 'i')
      ->where('g.group = :group')
      ->setParameter('group', $group_name)
      ->getQuery()
      ->getSingleResult();
  }

  function getList($id) {

      $repo = 'Pangphp\EditableLists\Entities\EditableList';
      return $this->_em->find($repo, $id);

  }

  function addList($group_name, $list_name) {
    $group = $this->_em->getRepository('Pangphp\EditableLists\Entities\EditableListGroup')
      ->findOneBy(array(
        'name' => $group_name
      ));

    if(!$group) {
      $group = new EditableListGroup();
      $group->setName($group_name);
      $this->_em->persist($group);
    };

    $list = $this->_em->getRepository('Pangphp\EditableLists\Entities\EditableList')
      ->findOneBy(array(
        'name' => $list_name
      ));
    
    if(!$list) {
      $list = new EditableList();
      $list->setName($list_name);
      $list->setGroup($group);
      $this->_em->persist($list);
    }

    $this->_em->flush();
    return $list;
  }

  function addItem($list, $value) {
    $item = new EditableListItem();
    $item->setValue($value);
    $item->setList($list);
    $this->_em->persist($item);
    $this->_em->flush();

    return $item;
  }

  function addListItem($id, $name, $group, $item) {

    // Does the list already exist other wise create it
    if($id) {
      $list = $this->getList($id);
      $item = $this->addItem($list, $item["value"]);
    } else {
      $list = $this->addList($group, $name);
      $item = $this->addItem($list, $item["value"]);
    }

    return $item;

  }

  function editListItem($item_id, $values) {

    $repo = 'Pangphp\EditableLists\Entities\EditableListItem';
    $list_item = $this->_em->find($repo, $item_id);
    $list_item->setValue($values["value"]);
    $list_item->setStatus($values["status"]);
    $this->_em->persist($list_item);
    $this->_em->flush();

    return $list_item;

  }

  function removeListItem($item_id) {
    
    $repo = 'Pangphp\EditableLists\Entities\EditableListItem';
    $list_item = $this->_em->find($repo, $item_id);
    $list_item->setStatus(0);
    $this->_em->persist($list_item);
    $this->_em->flush();

  }

  function getListItems($group, $name) {
    $qb = $this->_em->createQueryBuilder();

    return $qb->select(array('l', 'g', 'i'))
      ->from('Pangphp\EditableLists\Entities\EditableList', 'l')
      ->innerJoin('l.group', 'g')
      ->innerJoin('l.items', 'i')
      ->where('g.name = :group AND l.name = :name')
      ->setParameters(
        array(
          'group' => $group,
          'name' => $name
        )
      )
      ->getQuery();
  }
  
}