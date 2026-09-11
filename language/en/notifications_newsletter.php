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
	'NOTIFICATION_TYPE_NEWSLETTER_SUB'	=> 'Someone subscribes to or leaves a newsletter',
	'NOTIFICATION_NEWSLETTER_JOINED'	=> '<strong>%1$s</strong> subscribed to <strong>%2$s</strong>',
	'NOTIFICATION_NEWSLETTER_LEFT'		=> '<strong>%1$s</strong> unsubscribed from <strong>%2$s</strong>',
	'NOTIFICATION_NEWSLETTER_TOTAL'		=> 'That newsletter now has %d subscribers',
));
