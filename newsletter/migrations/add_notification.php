<?php
/**
 *
 * Newsletter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 salvocortesiano
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\newsletter\migrations;

/**
 * Registra il tipo di notifica presso phpBB.
 *
 * Non esiste uno strumento di migrazione "notification": gli strumenti di
 * phpBB sono config, config_text, module e permission, e basta. I tipi di
 * notifica si registrano chiedendolo al gestore delle notifiche, che si
 * ottiene dal contenitore dei servizi - da cui l'estensione di
 * container_aware_migration invece della classe base, che il contenitore non
 * ce l'ha.
 */
class add_notification extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * {@inheritdoc}
	 */
	public static function depends_on()
	{
		return array('\salvocortesiano\newsletter\migrations\v2_lists');
	}

	/**
	 * {@inheritdoc}
	 */
	public function effectively_installed()
	{
		return isset($this->config['newsletter_notify_subs']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_data()
	{
		return array(
			array('config.add', array('newsletter_notify_subs', 1)),
			array('custom', array(array($this, 'attiva_notifiche'))),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'spegni_notifiche'))),
			array('config.remove', array('newsletter_notify_subs')),
		);
	}

	/**
	 * Accende il tipo di notifica.
	 *
	 * Racchiuso in una rete di sicurezza: se per qualsiasi ragione il gestore
	 * non fosse disponibile, l'estensione deve comunque installarsi. Perdere
	 * le notifiche e un inconveniente; un'estensione che non si abilita e un
	 * forum senza newsletter.
	 */
	public function attiva_notifiche()
	{
		try
		{
			$this->container->get('notification_manager')
				->enable_notifications('salvocortesiano.newsletter.notification.type.subscription');
		}
		catch (\Exception $e)
		{
		}
		catch (\Throwable $e)
		{
		}
	}

	/**
	 * Spegne il tipo di notifica e ne cancella quelle gia consegnate
	 */
	public function spegni_notifiche()
	{
		try
		{
			$this->container->get('notification_manager')
				->purge_notifications('salvocortesiano.newsletter.notification.type.subscription');
		}
		catch (\Exception $e)
		{
		}
		catch (\Throwable $e)
		{
		}
	}
}
