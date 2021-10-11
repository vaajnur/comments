<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); 

$arComponentDescription = array(
	"NAME" => GetMessage("COMMENTS"),
	"DESCRIPTION" => GetMessage("DESCRIPTION"),
	"ICON" => "/images/comments.gif",
	"PATH" => array(
		"ID" => "comments",
		"CHILD" => array(
			"ID" => "comments_add",
			"NAME" => GetMessage("COMMENTS_ADD"),
		),
	),
);
?>