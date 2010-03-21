<?php
/*****************************************************************************
 *
 * GlobalBackendMgmt.php - class for handling all backends
 *
 * Copyright (c) 2004-2008 NagVis Project (Contact: lars@vertical-visions.de)
 *
 * License:
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 *****************************************************************************/
 
/**
 * @author	Lars Michelsen <lars@vertical-visions.de>
 */
class GlobalBackendMgmt {
	protected $CORE;
	public $BACKENDS;
	private $aInitialized;
	
	/**
	 * Constructor
	 *
	 * Initializes all backends
	 *
	 * @param   config  $MAINCFG
	 * @author  Lars Michelsen <lars@vertical-visions.de>
	 */
	public function __construct($CORE) {
		$this->CORE = $CORE;
		
		$this->BACKENDS = Array();
		
		$this->aInitialized = Array();
		
		$this->loadBackends();
		
		return 0;
	}
	
	/**
	 * Loads all backends and prints an error when no backend defined
	 *
	 * @author 	Lars Michelsen <lars@vertical-visions.de>
	 */
	private function loadBackends() {
		$aBackends = $this->CORE->getDefinedBackends();
		
		if(!count($aBackends)) {
			new GlobalMessage('ERROR', $this->CORE->getLang()->getText('noBackendDefined'));
		}
	}
	
	/**
	 * Checks for existing backend file
	 *
	 * @param	Boolean $printErr
	 * @return	Boolean	Is Successful?
	 * @author 	Lars Michelsen <lars@vertical-visions.de>
	 */
	private function checkBackendExists($backendId, $printErr) {
		if(isset($backendId) && $backendId != '') {
			if(file_exists($this->CORE->getMainCfg()->getValue('paths','class').'GlobalBackend'.$this->CORE->getMainCfg()->getValue('backend_'.$backendId,'backendtype').'.php')) {
				return TRUE;
			} else {
				if($printErr == 1) {
					new GlobalMessage('ERROR', $this->CORE->getLang()->getText('backendNotExists','BACKENDID~'.$backendId.',BACKENDTYPE~'.$this->CORE->getMainCfg()->getValue('backend_'.$backendId,'backendtype')));
				}
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Initializes a backend
	 *
	 * @return	Boolean	Is Successful?
	 * @author 	Lars Michelsen <lars@vertical-visions.de>
	 */
	private function initializeBackend($backendId) {
		if($this->checkBackendExists($backendId, 1)) {
			$backendClass = 'GlobalBackend'.$this->CORE->getMainCfg()->getValue('backend_'.$backendId,'backendtype');
			$this->BACKENDS[$backendId] = new $backendClass($this->CORE,$backendId);
			
			// Mark backend as initialized
			$this->aInitialized[$backendId] = true;
			
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Checks for an initialized backend
	 *
	 * @param	Boolean $printErr
	 * @return	Boolean	Is Successful?
	 * @author 	Lars Michelsen <lars@vertical-visions.de>
	 */
	public function checkBackendInitialized($backendId, $printErr) {
		if(isset($this->aInitialized[$backendId])) {
			return true;
		} else {
			// Try to initialize backend
			if($this->initializeBackend($backendId)) {
				return true;
			} else {
				if($printErr == 1) {
					new GlobalMessage('ERROR', $this->CORE->getLang()->getText('backendNotInitialized','BACKENDID~'.$backendId.',BACKENDTYPE~'.$this->CORE->getMainCfg()->getValue('backend_'.$backendId,'backendtype')));
				}
				return false;
			}
		}
	}
	
	/**
	 * Checks if the given feature is provided by the given backend
	 *
	 * @param	Boolean $printErr
	 * @return	Boolean	Is Successful?
	 * @author 	Lars Michelsen <lars@vertical-visions.de>
	 */
	public function checkBackendFeature($backendId, $feature, $printErr = 1) {
		$backendClass = 'GlobalBackend'.$this->CORE->getMainCfg()->getValue('backend_'.$backendId,'backendtype');
		if(method_exists($backendClass, $feature)) {
			return true;
		} else {
			if($printErr == 1) {
				new GlobalMessage('ERROR', $this->CORE->getLang()->getText('The requested feature [FEATURE] is not provided by the backend (Backend-ID: [BACKENDID], Backend-Type: [BACKENDTYPE]). The requested view may not be available using this backend.', Array('FEATURE' => htmlentities($feature), 'BACKENDID' => $backendId, 'BACKENDTYPE' => $this->CORE->getMainCfg()->getValue('backend_'.$backendId,'backendtype'))));
			}
			return false;
		}
	}
}
?>
