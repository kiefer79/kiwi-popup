<?php
namespace Kiefer\KiwiPopup\Controller;
	
	/***************************************************************
	 *
	 *  Copyright notice
	 *
	 *  (c) 2016 Andreas Kiefer <kiefer@kiefer.koeln>
	 *
	 *  All rights reserved
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as published by
	 *  the Free Software Foundation; either version 3 of the License, or
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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * PopupController
 */
class PopupController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	
	/**
	 * popupRepository
	 *
	 * @var \Kiefer\KiwiPopup\Domain\Repository\PopupRepository
	 * @inject
	 */
	protected $popupRepository = null;
	
	/**
	 * action showPopup
	 *
	 * @return void
	 */
	public function showPopupAction() {
		
		$this->cObj = $this->configurationManager->getContentObject();
		
		
		DebuggerUtility::var_dump($this->settings);
		DebuggerUtility::var_dump($this->alreadyShown());
		
		// show only once per session (if activated in ff)
		if (!$this->settings['sessionStorage'] || !$this->alreadyShown()) {
			
			// IMAGE
			if ($this->settings['type'] == 'IMAGE') {
				
				DebuggerUtility::var_dump('IMAGE');
				
				$showImage = TRUE;
				
				
				
				$images = GeneralUtility::trimExplode(",", $this->settings['popupfile'], TRUE);
				
				// random image or first?
				$this->imageKey = (is_array($images) && count($images) > 1) ? array_rand($images, 1) : 0;
				$image = $images[$this->imageKey];
				
				DebuggerUtility::var_dump($image);
				
				// has this image already been showed in session?
				if ($this->settings['sessionStorageOption'] == 'image') {
					$this->sD = $GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_kiwipopup');
					if ($this->sD['image'][$this->cObj->data['uid']][$this->imageKey]) {
						$showImage = FALSE;
					}
				}
				
				if ($showImage) {
					$this->view->assign('imageUid', $image);
					$this->view->assign('imageMaxW', $this->settings['imageMaxW']);
					$this->view->assign('imageMaxH', $this->settings['imageMaxH']);
				}
			}
			
			// HTML
			if ($this->seetings['type'] == 'HTML') {
				$content = $this->settings['popupcontent'];
			}
			
			// COBJ
			if ($this->settings['type'] == 'COBJ') {
				$cObjConf = array(
					'tables' => 'tt_content', 'source' => $this->settings['cObject'], 'dontCheckPid' => 1
				);
				$content = $this->cObj->RECORDS($cObjConf);
			}
			
			// render content
			if (!empty($content)) {
				
				// get html template
				
				
				
				$this->templateFile = t3lib_extMgm::siteRelPath($this->extKey) . 'res/kiwi_popup.tmpl';
				$this->templateCode = $this->cObj->fileResource($this->templateFile);
				
				// init Fluid
				$this->initFluid();
				
				// include jQuery library?
				if ($this->settings['jQueryInclude'])
					$this->jQueryInclude();
				
				// loadCSS
				$cssfile = t3lib_extMgm::siteRelPath($this->extKey) . 'res/kiwi_popup.css';
				$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] .= '<link rel="stylesheet" type="text/css" href="' . $cssfile . '" />';
				
				// get html template
				$this->templateFile = t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/' . $this->prefixId . '.html';
				$this->templateCode = $this->cObj->fileResource($this->templateFile);
				
				// show caption?
				if ($this->settings['showCaption']) {
					$captions = t3lib_div::trimExplode("\n", $this->settings['captionText'], TRUE);
					if ($this->settings[type] == 'IMAGE') {
						$caption = $captions[$this->imageKey];
					} else $caption = $captions[0];
					$this->renderer->assign('caption', $caption);
				}
				
				// autoclose enabled?
				if ($this->settings['autoClose']) {
					$autoclose = intval($this->settings['autoCloseSeconds']);
					// hide close button?
					if ($this->settings['hideCloseButton']) {
						$hideclosebutton = 1;
					} else $hideclosebutton = 0;
				} else {
					$autoclose = 0;
					$hideclosebutton = 0;
				}
				$this->renderer->assign('autoclose', $autoclose);
				$this->renderer->assign('hideclosebutton', $hideclosebutton);
				
				// link popup?
				if ($this->settings['link']) {
					unset($linkconf);
					$links = t3lib_div::trimExplode("\n", $this->settings['link'], TRUE);
					if ($this->settings[type] == 'IMAGE') {
						$link = $links[$this->imageKey];
					} else $link = $link = $links[0];
					$linkconf['parameter'] = $link;
					$linkURL = $this->cObj->typoLink_URL($linkconf);
					$this->renderer->assign('link', $linkURL);
				}
				
				// assign content to renderer
				$this->renderer->assign('content', $content);
				
				// store in session
				if ($this->settings['sessionStorage']) {
					$sessionVars = $this->generateSessionData();
				}
				
				// render
				return $this->pi_wrapInBaseClass($this->renderer->render());
				
			}
		}
	}
	
	/*
	 * function generateSessionData
	 */
	function generateSessionData() {
		$sessionVars = $GLOBALS['TSFE']->fe_user->getKey('ses','tx_kiwipopup');
		switch ($this->settings['sessionStorageOption']) {
			case 'general': // GENERAL
				$sessionVars['general'] = 1;
				break;
			case 'page': // PAGE ID
				$sessionVars['page'][$GLOBALS['TSFE']->id] = 1;
				break;
			
			case 'image': // IMAGE
				$sessionVars['image'][$this->cObj->data['uid']][$this->imageKey] = 1;
				break;
			
			case 'plugin': // PLUGIN UID
			default:
				$sessionVars['plugin'][$this->cObj->data['uid']] = 1;
				break;
		}
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_kiwipopup', $sessionVars);
		$GLOBALS['TSFE']->storeSessionData();
	}
	
	
	/**
	 * @return bool
	 */
	function alreadyShown() {
		
		// do not process check if session storage is disabled
		if (!$this->settings['sessionStorage']) return false;
		
		// get session data
		$this->sD = $GLOBALS['TSFE']->fe_user->getKey('ses','tx_kiwipopup');
		
		// check if session entry for current element exists
		switch($this->settings['sessionStorageOption']) {
			case 'general': // GENERAL
				if ($this->sD['general'] == 1) return true;
				break;
			case 'page': // PAGE ID
				if ($this->sD['page'][$GLOBALS['TSFE']->id]==1) return true;
				break;
			case 'plugin': // PLUGIN UID
				if ($this->sD['plugin'][$this->cObj->data['uid']] == 1) return true;
				break;
			case 'picture': // PLUGIN UID
				return false;
				break;
		}
		// return false if no session var found
		return false;
	}
	
	
}