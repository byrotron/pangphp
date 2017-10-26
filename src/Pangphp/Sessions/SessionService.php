<?php
 
namespace Pangphp\Sessions;
 
use \Pangphp\Sessions\Entities\Session;
use \Doctrine\ORM\EntityManager;
 
class SessionService implements \SessionHandlerInterface {
     
    private $_em;
     
    function __construct(EntityManager $em = null) {
        $this->_em = $em;
    }
     
    function open($save_path, $session_name)  {
        return true;
    }
     
    function close() {
        return true;
    }
     
    function read($session_id) {
 
        $session = $this->_em->getRepository("Pangphp\Sessions\Entities\Session")
            ->findOneBy(array("session_id" => $session_id));
         
        if(isset($session)) {
            return $session->getData();
        }
        return "";
    }
     
    function write($session_id, $session_data) {
        
        if ($this->_em->isOpen()) {

            $session = $this->_em->getRepository('Pangphp\Sessions\Entities\Session')
                                 ->findOneBy(array('session_id' => $session_id));
    
            if(!isset($session)) {
                $session = new Session();
            }
    
            $session->setSessionId($session_id);
		        $session->setData($session_data);
	          $session->setLastAccessed();
    
            $this->_em->persist($session);
            $this->_em->flush();
        }
        
        return true;
         
    }
     
    function destroy($session_id) {
 
        $session = $this->_em->getRepository("Pangphp\Sessions\Entities\Session")
                             ->findOneBy(array("session_id" => $session_id));
        
        if($session) {
            $this->_em->remove($session);
            $this->_em->flush();
        }
        return true;
    }
     
    function gc($max_lifetime) {
        $date = new \DateTime(date("Y-m-d H:i"));
        $date->sub(new \DateInterval("P14D"));
         
        $qb = $this->_em->createQueryBuilder();
        $qb->delete('sessions', 's')
            ->where('s.last_accessed <= :date')
            ->setParameter('project', $date->format("Y-m-d H:i"));
        return true;
    }
 
}