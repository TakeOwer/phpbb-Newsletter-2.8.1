<?php
/**
 *
 * Newsletter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 salvocortesiano
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace salvocortesiano\newsletter\notification\type;

/**
 * Notifica di iscrizione o cancellazione da un notiziario.
 *
 * Si appoggia all'impianto di phpBB invece di aggiungere una campanella
 * propria: cosi il conteggio dei non letti, il pannello a comparsa, la
 * marcatura come letto, l'adattamento al telefono e la preferenza per
 * spegnerla sono gia fatti, uguali a tutte le altre notifiche del forum, e
 * funzionano in ogni stile installato senza che si debba toccare nulla.
 *
 * Iscrizione e cancellazione sono un tipo solo, distinte da un dato interno.
 * Separarle darebbe due interruttori invece di uno, ma raddoppierebbe il
 * codice per una differenza che quasi nessuno userebbe.
 */
class subscription extends \phpbb\notification\type\base
{
	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/**
	 * Voce che compare nelle preferenze di notifica dell'utente
	 *
	 * @var array
	 */
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_NEWSLETTER_SUB',
		'group'	=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
	);

	/**
	 * Il caricatore di utenti non e nella classe base di phpBB: ogni tipo di
	 * notifica che mostra un nome o un avatar deve dichiararsi il proprio
	 * metodo, ed e il contenitore a chiamarlo alla costruzione
	 *
	 * @param \phpbb\user_loader $user_loader
	 */
	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	 * @param \phpbb\controller\helper $helper
	 */
	public function set_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type()
	{
		return 'salvocortesiano.newsletter.notification.type.subscription';
	}

	/**
	 * Chi puo ricevere questa notifica.
	 *
	 * Solo chi amministra la newsletter: a chiunque altro non direbbe niente,
	 * e comparirebbe come rumore fra le notifiche vere.
	 *
	 * @return bool
	 */
	public function is_available()
	{
		return $this->auth->acl_get('a_newsletter');
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_item_id($data)
	{
		return (int) $data['subscriber_id'];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_item_parent_id($data)
	{
		return isset($data['list_id']) ? (int) $data['list_id'] : 0;
	}

	/**
	 * Destinatari della notifica.
	 *
	 * Chi si e appena iscritto non riceve la propria notifica: sa gia di
	 * essersi iscritto, e avvisarlo sarebbe solo fastidioso.
	 *
	 * @param array $data
	 * @param array $options
	 * @return array
	 */
	public function find_users_for_notification($data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'	=> array(),
		), $options);

		// acl_get_list restituisce una struttura annidata per foro e permesso:
		// senza foro, l'elenco degli utenti sta sotto la chiave zero
		$elenco = $this->auth->acl_get_list(false, 'a_newsletter', false);
		$utenti = isset($elenco[0]['a_newsletter']) ? $elenco[0]['a_newsletter'] : array();

		$utenti = array_diff($utenti, array((int) $data['subscriber_id'], ANONYMOUS), $options['ignore_users']);

		if (empty($utenti))
		{
			return array();
		}

		return $this->check_user_notification_options($utenti, $options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function users_to_query()
	{
		return array($this->get_data('subscriber_id'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('subscriber_id'), false, true);
	}

	/**
	 * Testo della notifica
	 *
	 * @return string
	 */
	public function get_title()
	{
		$nome = $this->user_loader->get_username($this->get_data('subscriber_id'), 'no_profile');
		$notiziario = (string) $this->get_data('list_name');

		return $this->language->lang(
			$this->get_data('unsubscribed') ? 'NOTIFICATION_NEWSLETTER_LEFT' : 'NOTIFICATION_NEWSLETTER_JOINED',
			$nome,
			$notiziario
		);
	}

	/**
	 * Riga sotto il titolo, con il momento e il totale
	 *
	 * @return string
	 */
	public function get_reference()
	{
		$totale = (int) $this->get_data('list_total');

		return ($totale > 0)
			? $this->language->lang('NOTIFICATION_NEWSLETTER_TOTAL', $totale)
			: '';
	}

	/**
	 * Dove porta il clic.
	 *
	 * Al profilo di chi si e iscritto e non al pannello di amministrazione:
	 * l'indirizzo dell'amministrazione cambia da forum a forum e non e sempre
	 * costruibile da qui, mentre il profilo e sempre valido e dice subito con
	 * chi si ha a che fare.
	 *
	 * @return string
	 */
	public function get_url()
	{
		return append_sid(
			$this->phpbb_root_path . 'memberlist.' . $this->php_ext,
			'mode=viewprofile&amp;u=' . (int) $this->get_data('subscriber_id')
		);
	}

	/**
	 * Nessuna email: e un avviso di servizio, non merita una notifica per posta
	 *
	 * @return string|false
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template_variables()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('subscriber_id', (int) $data['subscriber_id']);
		$this->set_data('list_id', isset($data['list_id']) ? (int) $data['list_id'] : 0);
		$this->set_data('list_name', isset($data['list_name']) ? (string) $data['list_name'] : '');
		$this->set_data('list_total', isset($data['list_total']) ? (int) $data['list_total'] : 0);
		$this->set_data('unsubscribed', !empty($data['unsubscribed']));

		parent::create_insert_array($data, $pre_create_data);
	}
}
