<?php

$pangphp_exceptions = [
	[
		"instance" => "DeveloperException",
		"code"     => 0,
		"message"  => "Please inform your site administrator of this issue.",
	],
	[
		"instance" => "PrivilegeException",
		"code"     => 403,
		"message"  => "Your Role needs to be updated to complete this action.",
	],
	[
		"instance" => "ForeignKeyConstraintViolationException",
		"code"     => 500,
		"message"  => "This item cannot be deleted as it is referenced somewhere else",
	],
	[
		"instance" => "Pangphp\Auth\AuthException",
		"code"     => 401,
		"message"  => "You are not Authorised to access this page.",
	],
	[
		"instance" => "RuntimeException",
		"code"     => 500,
		"message"  => "You request failed, please try again or contact your system admin",
	],
	[
		"instance" => "Doctrine\ORM\Query\QueryException",
		"code"     => 500,
		"message"  => "Your request failed, please try again or contact your system admin",
	],
	[
		"instance" => "Doctrine\DBAL\Exception\UniqueConstraintViolationException",
		"code"     => 500,
		"message"  => "Duplicate entry found",
	],
	[
		"instance" => "Exception",
		"code"     => 500,
		"message"  => "You request failed, please try again or contact your system admin",
	],
];