<?
/**
* @author 	NETLOR CONCEPT
* @version  	1.0
* @package  	forms
* @access  	public
*/

class forms extends data_object
{
	/**
	* Class constructor
	*
	* @param int $connection_id
	* @access public
	**/

	function forms()
	{
		parent::data_object('ploopi_mod_forms');
	}

	function save()
	{
		if ($this->fields['tablename'] == '') $this->fields['tablename'] = $this->fields['label'];
		$this->fields['tablename'] = forms_createphysicalname($this->fields['tablename']);
		return(parent::save());
	}

	function getfields()
	{
		global $db;

		$fields = array();

		$select = "SELECT * FROM ploopi_mod_forms_field WHERE id_forms = {$this->fields['id']} AND separator = 0";

		$db->query($select);

		while ($row = $db->fetchrow())
		{
			$fields[$row['id']] = $row;
		}

		return($fields);

	}
}
?>