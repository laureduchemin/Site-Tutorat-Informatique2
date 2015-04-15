<?php
/** 
 *	iCagenda
 *----------------------------------------------------------------------------
 * @package     com_icagenda
 * @copyright	Copyright (C) 2012 JOOMLIC - All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Jooml!C - http://www.joomlic.com
 * 
 * @since		1.3
 *----------------------------------------------------------------------------
*/

// No direct access to this file
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * event Table class
 */
class iCagendaTableevent extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__icagenda_events', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */
	public function bind($array, $ignore = '')
	{
		
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}
		
				// formatto l'ora
		if (isset($array['ora'])){
			$ora=preg_replace("/\W/", "", $array['ora']);
			$oraone=substr($ora, 0, 2);
			$oratwo=substr($ora, 2, 2);
			$ora=$oraone.'.'.$oratwo;
		}else{
			$ora='00.00';
		}
		$array['ora'] = $ora;
		
		
		return parent::bind($array, $ignore);
	}
	
	function getNext ($dataini, $datafin)
	{
		$ini=$this->mkt($dataini);
		$fin=$this->mkt($datafin);
		$today=time();
		
		if ($fin <= $today){
			$next=$fin;
		}elseif ($ini>=$today){
			$next=$ini;
		}else{
			$next=$today;
		}
		
		return date('Y-m-d', $next);
	}
	
	function mkt($data)
	{
		$ex_data=explode('-', $data);
		$ris=mktime('00', '00', '00', $ex_data['1'], $ex_data['2'], $ex_data['0']);
		return $ris;
	}
	
	/**
	 * upload
	 */

	function upload ($file){

		$filename = JFile::makeSafe($file['name']['file']);

		if($filename!=''){

			$src = $file['tmp_name']['file'];
			$dest =  JPATH_SITE. DS ."images".DS."icagenda_doc".DS.$filename;
			
			if(!is_dir($dest)){
				mkdir($intDir, 0777);
			}


			if ( JFile::upload($src, $dest, false) ){
				echo 'upload';
				return 'images/icagenda_doc/'.$filename;
			}
			
			return 'images/icagenda_doc/'.$filename;
		}
	}	

    /**
    * Overloaded check function
    */
    public function check() {

        //If there is an ordering column and this is a new row then get the next ordering value
        if (property_exists($this, 'ordering') && $this->id == 0) {
            $this->ordering = self::getNextOrder();
        }
        
        return parent::check();
    }


    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table.  The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param    mixed    An optional array of primary key values to update.  If not
     *                    set the instance property value is used.
     * @param    integer The publishing state. eg. [0 = unpublished, 1 = published]
     * @param    integer The user id of the user performing the operation.
     * @return    boolean    True on success.
     * @since    1.0.4
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        // Initialise variables.
        $k = $this->_tbl_key;

        // Sanitize input.
        JArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state  = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks))
        {
            if ($this->$k) {
                $pks = array($this->$k);
            }
            // Nothing to set publishing state on, return false.
            else {
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k.'='.implode(' OR '.$k.'=', $pks);

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
            $checkin = '';
        }
        else {
            $checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $userId.')';
        }

        // Update the publishing state for rows with the given primary keys.
        $this->_db->setQuery(
            'UPDATE `'.$this->_tbl.'`' .
            ' SET `state` = '.(int) $state .
            ' WHERE ('.$where.')' .
            $checkin
        );
        $this->_db->query();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
        {
            // Checkin the rows.
            foreach($pks as $pk)
            {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->state = $state;
        }

        $this->setError('');
        return true;
    }




}
