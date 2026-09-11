<?php
/**
 *
 * Newsletter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 salvocortesiano
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'NOTIFICATION_TYPE_NEWSLETTER_SUB'	=> 'Qualcuno si iscrive o si cancella da un notiziario',
	'NOTIFICATION_NEWSLETTER_JOINED'	=> '<strong>%1$s</strong> si è iscritto a <strong>%2$s</strong>',
	'NOTIFICATION_NEWSLETTER_LEFT'		=> '<strong>%1$s</strong> ha annullato l’iscrizione a <strong>%2$s</strong>',
	'NOTIFICATION_NEWSLETTER_TOTAL'		=> 'Ora quel notiziario ha %d iscritti',
));
