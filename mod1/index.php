<?php
	/***************************************************************
	*  Copyright notice
	*
	*  (c) 2009 JoH asenau <jh@eqony.com>
	*  All rights reserved
	*
	*  This script is part of the TYPO3 project. The TYPO3 project is
	*  free software; you can redistribute it and/or modify
	*  it under the terms of the GNU General Public License as published by
	*  the Free Software Foundation; either version 2 of the License, or
	*  (at your option) any later version.
	*
	*  The GNU General Public License can be found at
	*  http://www.gnu.org/copyleft/gpl.html.
	*
	*  This script is distributed in the hope that it will be useful,
	*  but WITHOUT ANY WARRANTY; without even the implied warranty of
	*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	*  GNU General Public License for more details.
	*
	*  This copyright notice MUST APPEAR in all copies of the script!
	***************************************************************/
	 
	 
	// DEFAULT initialization of a module [BEGIN]
	unset($MCONF);
	require_once('conf.php');
	require_once($BACK_PATH.'init.php');
	require_once($BACK_PATH.'template.php');
	 
		$LANG->includeLLFile('EXT:tagpack/mod1/locallang.xml');
	require_once(PATH_t3lib.'class.t3lib_scbase.php');
	$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.

	include_once(t3lib_extMgm::extPath('tagpack') . 'lib/class.tx_tagpack_api.php');

	// DEFAULT initialization of a module [END]
	 
	 
	 
	/**
	* Module 'Site Generator' for the 'tagpack' extension.
	*
	* @author JoH asenau <jh@eqony.com>
	* @package TYPO3
	* @subpackage tx_tagpack
	*/
	class tx_tagpack_module1 extends t3lib_SCbase {
		var $pageinfo;
		 
		/**
		* Initializes the Module
		*
		* @return void
		*/
		function init() {
			global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
			 
			parent::init();
			 
			/*
			if (t3lib_div::_GP('clear_all_cache')) {
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
			}
			*/
		}
		 
		/**
		* Adds items to the->MOD_MENU array. Used for the function menu selector.
		*
		* @return void
		*/
		function menuConfig() {
			global $LANG;
			$this->MOD_MENU = Array (
			'function' => Array (
			'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
				)
			);
			parent::menuConfig();
		}
		 
		/**
		* Main function of the module. Write the content to $this->content
		* If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
		*
		* @return [type]  ...
		*/
		function main() {
			global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
			 
			// Access check!
			// The page will show only if there is a valid page and if this page may be viewed by the user
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 : 0;
			 
			if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id)) {
				
				$this->tpm = t3lib_div::_GP('tpm');
				$this->tpm = $this->tpm ? $this->tpm : $BE_USER->getModuleData('user_txtagpackM1/tpm');
				$BE_USER->pushModuleData('user_txtagpackM1/tpm',$this->tpm);
				$this->tagContainer = tx_tagpack_api::getTagContainer();
			 
				// Draw the header.
				$this->doc = t3lib_div::makeInstance('bigDoc');
				$this->doc->backPath = $BACK_PATH;
				$this->doc->JScode .= '
				<link rel="stylesheet" type="text/css" href="css/tagmanager.css" />';
/*				$this->doc->JScode .= '
				<script type="text/javascript" src="/typo3/contrib/prototype/prototype.js"><!--PROTOTYPE--></script>';
				$this->doc->JScode .= '
				<script type="text/javascript" src="/typo3/contrib/scriptaculous/scriptaculous.js"><!--SCRIPTACULOUS--></script>';*/
				$this->doc->JScode .= '
				<script type="text/javascript" src="js/tabMenuFunctions.js"><!--TABMENU--></script>';
				$this->doc->form = '<form id="tagmanager_form" action="index.php" method="POST">';
				 
				$this->content .= $this->doc->startPage($LANG->getLL('title'));

				if ($this->tagpack['save']) {
					//Save Pagetree
					$this->savePageTree();
				} else {
					// Render content:
					$this->moduleContentDynTabs();
				}
				 
				 
				// ShortCut
				if ($BE_USER->mayMakeShortcut()) {
					$this->content .= '<div id="shortcuticon">'.$this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name'])).'</div>';
				}
				 
			} else {
				// If no access or if ID == zero
				 
				$this->doc = t3lib_div::makeInstance('mediumDoc');
				$this->doc->backPath = $BACK_PATH;
				 
				$this->content .= $this->doc->startPage($LANG->getLL('title'));
				$this->content .= $this->doc->header($LANG->getLL('title'));
				$this->content .= $this->doc->spacer(5);
				$this->content .= $this->doc->spacer(10);
			}
		}
		 
		/**
		* [Describe function...]
		*
		* @return [type]  ...
		*/
		function moduleContentDynTabs() {
		
		    $this->content .= '<ul id="tabmenu">';
		    $this->content .= '<li id="tabitem1" class="'.($this->tpm['active_tab'] > 1 ? 'redbutton' : 'greenbutton').'"><a href="#" onclick="triggerTab(this,1);tpmIframeHide();return false;">'.$GLOBALS['LANG']->getLL('TabLabel1').'</a></li>';
		    $this->content .= '<li id="tabitem2" class="'.($this->tpm['active_tab'] == 2 ? 'greenbutton' : 'redbutton').'"><a href="#" onclick="triggerTab(this,2);tpmIframeHide();return false;">'.$GLOBALS['LANG']->getLL('TabLabel2').'</a></li>';
		    $this->content .= '<li id="tabitem3" class="'.($this->tpm['active_tab'] == 3 ? 'greenbutton' : 'redbutton').'"><a href="#" onclick="triggerTab(this,3);tpmIframeHide();return false;">'.$GLOBALS['LANG']->getLL('TabLabel3').'</a></li>';
		    $this->content .= '<li id="tabitem3" class="'.($this->tpm['active_tab'] == 4 ? 'greenbutton' : 'redbutton').'"><a href="#" onclick="triggerTab(this,4);tpmIframeHide();return false;">'.$GLOBALS['LANG']->getLL('TabLabel4').'</a></li>';
		    $this->content .= '</ul>
		    <input id="tpm_active_tab" type="hidden" name="tpm[active_tab]" value="'.($this->tpm['active_tab'] ? $this->tpm['active_tab'] : 1).'" />
		    <div id="tabcontent1" class="'.($this->tpm['active_tab'] > 1 ? 'tabcontent_off' : 'tabcontent_on').'">
			'.$this->moduleContentTab1().'
		    </div>
		    <div id="tabcontent2" class="'.($this->tpm['active_tab'] == 2 ? 'tabcontent_on' : 'tabcontent_off').'">
			'.$this->moduleContentTab2().'
		    </div>
		    <div id="tabcontent3" class="'.($this->tpm['active_tab'] == 3 ? 'tabcontent_on' : 'tabcontent_off').'">
			'.$this->moduleContentTab3().'
		    </div>
		    <div id="tabcontent4" class="'.($this->tpm['active_tab'] == 4 ? 'tabcontent_on' : 'tabcontent_off').'">
			'.$this->moduleContentTab4().'
		    </div>
		    <div id="iframe_container" style="display:none;">
			<iframe id="inner_frame" name="inner_frame" src="/typo3/alt_doc.php" onblur="tpmIframeHide();return false;"><!--//IFRAME FOR TCE-FORM//--></iframe>
		    </div>';
		    
		}
		 
		/**
		* Prints out the module HTML
		*
		* @return void
		*/
		function printContent() {
			 
			$this->content .= $this->doc->endPage();
			echo $this->content;
		}
		 
		/**
		* Generates the content for tab 1
		*
		* @return void
		*/
		function moduleContentTab1() {
			$tab1Content .= '<div class="tabscreenback1"><!--BACKGROUND--></div><div class="tabcontent tabscreen_left">'.$this->doc->header($GLOBALS['LANG']->getLL('Tab1_Left'));
			$tab1Content .= $this->makeDefaultFormFields(1);
			$blockedChecked = $this->tpm['approve']['blocked'] || (!$this->tpm['approve']['blocked'] && !$this->tpm['approve']['approved']) ? ' checked="checked"' : '';
			$approvedChecked = $this->tpm['approve']['approved'] || (!$this->tpm['approve']['blocked'] && !$this->tpm['approve']['approved']) ? ' checked="checked"' : '';
			$tab1Content .= '
			    <div id="approvedfilter">
				'.$GLOBALS['LANG']->getLL('find').' <input type="checkbox" class="tpm_checkbox" id="tpm_approve_blocked" name="tpm[approve][blocked]" value="1"'.$blockedChecked.' />
				<label for="tpm_approve_blocked"> '.$GLOBALS['LANG']->getLL('blocked').' </label>
				<input type="checkbox" class="tpm_checkbox" id="tpm_approve_approved" name="tpm[approve][approved]" value="1"'.$approvedChecked.' />
				<label for="tpm_approve_approved"> '.$GLOBALS['LANG']->getLL('approved').' </label>
				'.$GLOBALS['LANG']->getLL('tags').'
			    </div>
			';
			$tab1Content .= '<input type="submit" class="submit" value="submit" />';
			$tab1Content .= '</div>';
			$tab1Content .= '<div class="tabscreenback2"><!--BACKGROUND--></div><div class="tabcontent tabscreen_right">'.$this->doc->header($GLOBALS['LANG']->getLL('Tab1_Right'));
			$tab1Content .= $this->makeResultList(1,TRUE);
			$tab1Content .= '</div>';
			return $tab1Content;
		}
		 
		 
		/**
		* Generates the content for tab 2
		*
		* @return void
		*/
		function moduleContentTab2() {
			$tab2Content .= '<div class="tabscreenback1"><!--BACKGROUND--></div><div class="tabcontent tabscreen_left">'.$this->doc->header($GLOBALS['LANG']->getLL('Tab2_Left'));
			$tab2Content .= $this->makeDefaultFormFields(2);
			$tab2Content .= '<input type="submit" class="submit" value="submit" />';
			$tab2Content .= '</div>';
			$tab2Content .= '<div class="tabscreenback2"><!--BACKGROUND--></div><div class="tabcontent tabscreen_right">'.$this->doc->header($GLOBALS['LANG']->getLL('Tab2_Right'));
			$tab2Content .= $this->makeResultList(2);
			$tab2Content .= '</div>';
			return $tab2Content;
		}
		 
		 
		/**
		* Generates the content for tab 3
		*
		* @return void
		*/
		function moduleContentTab3() {
			$tab3Content .= '<div class="tabscreenback1"><!--BACKGROUND--></div><div class="tabcontent tabscreen_left">'.$this->doc->header($GLOBALS['LANG']->getLL('Tab3_Left'));
			$tab3Content .= $this->makeDefaultFormFields(3,FALSE);
			$tab3Content .= '<input type="submit" class="submit" value="submit" />';
			$tab3Content .= '</div>';
			$tab3Content .= '<div class="tabscreenback2"><!--BACKGROUND--></div><div class="tabcontent tabscreen_right">'.$this->doc->header($GLOBALS['LANG']->getLL('Tab3_Right'));
			$tab3Content .= $this->makeResultList(3);
			$tab3Content .= '</div>';
			return $tab3Content;
		}
		 
		 
		/**
		* Generates the content for tab 4
		*
		* @return void
		*/
		function moduleContentTab4() {
			$tab4Content .= '<div class="tabscreenback1"><!--BACKGROUND--></div><div class="tabcontent tabscreen_left">'.$this->doc->header($GLOBALS['LANG']->getLL('Tab4_Left'));
			$tab4Content .= $this->makeDefaultFormFields(4);
			$tab4Content .= '<input type="submit" class="submit" value="submit" />';
			$tab4Content .= '</div>';
			$tab4Content .= '<div class="tabscreenback2"><!--BACKGROUND--></div><div class="tabcontent tabscreen_right">'.$this->doc->header($GLOBALS['LANG']->getLL('Tab4_Right'));
			$tab4Content .= $this->makeResultList(4);
			$tab4Content .= '</div>';
			return $tab4Content;
		}
		 
		function makeDefaultFormFields($tab,$multiple=TRUE) {
			$content .= $this->makeContainerSelector($tab,$multiple);
			if(count($this->tpm['container_page'][$tab])) {
				$content .= '<p>'.$GLOBALS['LANG']->getLL('within_containers').':</p>';
				$content .= $this->makeSearchbox($tab);
			}
			return $content;
		}
		 
		function makeContainerSelector($tab,$multiple=TRUE) {
			if(count($this->tpm['container_page'][$tab])) {
				foreach($this->tpm['container_page'][$tab] as $value) {
					$selectedOptions[$value]=1;
				}
			}
			if(count($this->tagContainer)) {
				$i=0;
				foreach($this->tagContainer as $pageData) {
					$this->availableContainers[$pageData['uid']]=$pageData;
					$selected = $selectedOptions[$pageData['uid']] ? ' selected="selected"' : '';
					$i++;
					$optionList .= '<option value="'.$pageData['uid'].'"'.$selected.'>['.$pageData['uid'].'] '.substr($pageData['title'],0,16).(strlen($pageData['title'])>16 ? '...' : '').'</option>';
				}
				$multiple = $multiple ? ' multiple="multiple" size="5"' : '';
				$selectBox = '<label for="tpm_container_page_'.$tab.'">'.$GLOBALS['LANG']->getLL('Tab'.$tab.'_Label1').'</label>
				<select'.$multiple.' id="tpm_container_page_'.$tab.'" class="container_page" name="tpm[container_page]['.$tab.'][]">'.$optionList.'</select>';
			}
			return $selectBox;
		} 


		function makeSearchbox($tab) {
			$searchBox = '<label for="tpm_tagname_'.$tab.'">'.$GLOBALS['LANG']->getLL('Tab'.$tab.'_Label2').'</label>
				<input class="search_tagname" id="tpm_tagname_'.$tab.'" type="text" name="tpm[tagname]['.$tab.']" value="'.$this->tpm['tagname'][$tab].'"/>';
			return $searchBox;
		} 
	

		function makeResultlist($tab,$hidden=FALSE) {
			if(count($this->tpm['container_page'][$tab])) {
			    $tagName = trim($this->tpm['tagname'][$tab]) ? trim($this->tpm['tagname'][$tab]) : '%';
			    if(count($resultData = tx_tagpack_api::getTagDataByTagName($tagName,implode(',',$this->tpm['container_page'][$tab]),FALSE,$hidden))) {
				foreach ($resultData as $tagData) {
				    if($tagData['hidden']) {
					if($this->tpm['approve']['blocked'] || (!$this->tpm['approve']['blocked'] && !$this->tpm['approve']['approved'])) {
				    	    $sortedData[$tagData['pid']][ucwords($tagData['name'])]=$tagData;
					}
				    } else if ($this->tpm['approve']['approved'] || (!$this->tpm['approve']['blocked'] && !$this->tpm['approve']['approved'])) {
					$sortedData[$tagData['pid']][ucwords($tagData['name'])]=$tagData;
			    	    }
				}
			    }
			}
			if(count($sortedData)) {

			    foreach($this->tpm['container_page'][$tab] as $selectedId) {
				if(count($sortedData[$selectedId])) {
				    ksort($sortedData[$selectedId]);
				    $resultList .= '<h3>['.$selectedId.'] '.$this->availableContainers[$selectedId]['title'].'</h3>';
				    $resultList .= '<table cellspacing="1" cellpadding="0" border="0" class="resultlist" width="400px">
				    <colgroup>
					<col width="50px" />
					<col width="320px" />
					<col width="15px" />
					<col width="15px" />
				    </colgroup>
				    <tr>
					<th>
					    ID
					</th>
					<th>
					    Name
					</th>
					';
				    if($tab == 1) {
					$resultList .= '
					    <th>
						<img src="icons/button_unhide.gif" alt="'.$GLOBALS['LANG']->getLL('blocked').'" title="'.$GLOBALS['LANG']->getLL('blocked').'" />
					    </th>
					    <th>
						<img src="icons/garbage.gif" alt="'.$GLOBALS['LANG']->getLL('remove').'" title="'.$GLOBALS['LANG']->getLL('remove').'" />
					    </th>
					';
				    } else if($tab == 2) {
					$resultList .= '
					    <th colspan="2">
						Edit
					    </th>
					';
				    } else {
					$resultList .= '
					    <th>
					    </th>
					    <th>
					    </th>
					';
				    }
				    $resultList .= '
				    </tr>
				    ';
				    $counter = 0;
				    foreach($sortedData[$selectedId] as $tagData) {
					$counter++;
					$trClass = fmod($counter,2) ? 'odd' : 'even';
					$resultList .= '<tr class="'.$trClass.'" id="tag'.$tagData['uid'].'"><td align="right">'.$tagData['uid'].'</td><td>'.$tagData['name'].'</td>';
					if($tab == 1) {
					    $hiddenClass = $tagData['hidden'] ? ' class="caution"' : ' class="ok"';
					    $resultList .= '<td'.$hiddenClass.'>
						<input title="'.($tagData['hidden'] ? $GLOBALS['LANG']->getLL('blocked') : $GLOBALS['LANG']->getLL('approved')).'" class="tpm_checkbox" type="checkbox" name="data[tx_tagpack_tags]['.$tagData['uid'].'][hidden]" value="1"'.($tagData['hidden'] ? '' : ' checked="checked"').' onclick="switchStatus(this);return false;" />
						</td>
						<td class="alert">
						<input title="'.$GLOBALS['LANG']->getLL('remove').'" class="tpm_checkbox" type="checkbox" name="cmd[tx_tagpack_tags]['.$tagData['uid'].'][delete]" value="1" onclick="switchStatus(this);return false;" />
						</td>';
					} else if($tab == 2) {
					    $resultList .= '<td colspan="2"><a href="#" onclick="tpmEditItem('.$tagData['uid'].');return false;"><img src="icons/edit2.gif" /></a></td>';
					} else if($tab == 3) {
					    $resultList .= '<td>3</td><td></td>';
					} else {
					    $resultList .= '<td>4</td><td></td>';
					}
					$resultList .= '</tr>
					';
				    }
				    $resultList .= '
				    </table>';
				}
			    }
			}
			return $resultList;
		} 
	
	}
	 
	 
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tagpack/mod1/index.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tagpack/mod1/index.php']);
	}
	 
	 
	 
	 
	// Make instance:
	$SOBE = t3lib_div::makeInstance('tx_tagpack_module1');
	$SOBE->init();
	 
	// Include files?
	foreach($SOBE->include_once as $INC_FILE) include_once($INC_FILE);
	 
	$SOBE->main();
	$SOBE->printContent();
	 
?>
