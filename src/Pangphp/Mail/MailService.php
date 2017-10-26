<?php

namespace Pangphp\Mail;

class MailService {

  protected $_mailer;
  protected $_config;
  protected $_data;
  protected $_to = [];

  function __construct(\PHPMailer $mailer, $config) {

    $this->_mailer = $mailer;
    $this->_config = $config;
    $this->_mailer->isHTML(true);
    $this->_mailer->AltBody = 'If you see this message please contact your system administrator';

  }

  function setTo(array $to) {
    $this->_to = $to;
  }

  function setFrom($email, $name) {
    $this->_mailer->setFrom($email, $name);
  }

  function setSubject($subject) {
    $this->_mailer->Subject = $subject;
  }

  function addAttachment($attachment) {
    $this->_mailer->addAttachment($attachment);
  }

  function sendFromAdmin() {

    $this->_mailer->setFrom($this->_config->get('mail.admin_email'), $this->_config->get('mail.admin_name'));
  }
  
  function sendToAdmin(){
	  $this->setTo([
		  "email" => $this->_config->get("mail.admin_email"),
		  "name"  => $this->_config->get("mail.admin_name")
	  ]);
  }

  function setData($key, $val = null) {
    
    if(is_array($key)) {
      foreach($key as $k=>$v) {
        $this->_data[$k] = $v;
      }
    } else {
      $this->_data[$key] = $val;
    }
    
  }

  function setTemplate($template) {
    
    if(!file_exists($template)) {
      throw new MailException("Template not found at " . $template);
    }
    $this->_template = $template;
  }

  function getTemplate() {
    ob_start();

    //Set the template variables
    require $this->_template;

    $this->_mailer->Body = ob_get_contents();

    ob_end_clean();
  }

  function sendmail() {

    $this->setSMTP();

    foreach($this->_to as $to_address) {
 
      $this->setData('to_email', $to_address["email"]);
      $this->setData('to_name', $to_address["name"]);
      $this->_mailer->addAddress($to_address["email"], $to_address["name"]);
      
      $this->getTemplate();
 

      if(!$this->_mailer->send()) {
          throw new MailException($this->_mailer->ErrorInfo);
      }

      $this->_mailer->ClearAllRecipients();
    }

  }

  function setSMTP() {

    if($this->_config->get("mail.use_smtp") === true) {
      
      $this->_mailer->isSMTP();
      $this->_mailer->Host = $this->_config->get("mail.host");
      $this->_mailer->SMTPOptions =array(
          "ssl" => array(
              "verify_peer" => false,
              "verify_peer_name" => false,
              "allow_self_signed" => true
          )
      );
      $this->_mailer->SMTPAuth = true;
      $this->_mailer->Username = $this->_config->get("mail.username");
      $this->_mailer->Password = $this->_config->get("mail.password");
      $this->_mailer->Port = $this->_config->get("mail.port");
    }

  }
}

?>