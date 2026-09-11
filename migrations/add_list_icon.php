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
 * Icona per ogni notiziario.
 *
 * Serve solo a distinguerli a colpo d'occhio in un elenco: con sette notiziari
 * uno sotto l'altro, un simbolo davanti al nome si legge molto piu in fretta
 * del nome stesso.
 */
class add_list_icon extends \phpbb\db\migration\migration
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
		return $this->db_tools->sql_column_exists($this->table_prefix . 'newsletter_lists', 'list_icon');
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'newsletter_lists' => array(
					// Solo il nome della classe, per esempio fa-envelope: il
					// resto della marcatura lo mette il modello di pagina
					'list_icon' => array('VCHAR:64', ''),
				),
			),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'newsletter_lists' => array('list_icon'),
			),
		);
	}
}
