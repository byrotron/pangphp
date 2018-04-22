<p>Dear <?php echo ucwords($this->_data["user"]->getfullName()) ?></p>
<p>We got a requset to reset your account, please click the link below to proceed</p>

<?php
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
?>

<a href="<?php echo $protocol . $_SERVER["HTTP_HOST"] ?>/reset-account/<?php echo $this->_data["link"] ?>">RESET</a>

<p>If you do not click the link and update your password it will remain the same.</p>

<p>Regards,</p>