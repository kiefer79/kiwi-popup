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
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;


/**
 * PopupController
 */
class PopupController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	
	/**
	 * action showPopup
	 *
	 * @return void
	 */
	public function showPopupAction() {
		$this->cObj = $this->configurationManager->getContentObject();
		$this->includeAssets();
		
		// show only once per session (if activated in ff)
		if (!$this->settings['sessionStorage'] || !$this->alreadyShown()) {
			switch ($this->settings['type']) {
				case 'IMAGE':
					$this->assignImage();
					break;
				case 'HTML':
					$this->view->assign('popupcontent', $this->settings['popupcontent']);
					break;
				case 'COBJ':
					$cObjContent = $this->renderCObject($this->settings['cObject'], 'tt_content');
					$this->view->assign('popupcontent', $cObjContent);
					break;
				default:
					
			}
			$this->assignCaption();
			$this->assignAutoclose();
			$this->assignPopupLink();
			// $this->storeSessionData();
		}
	}
	
	/**
	 * include js and css assets
	 */
	protected function includeAssets(){
		/** @var TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
		$pageRenderer = $GLOBALS['TSFE']->getPageRenderer();
		$siteRelPath = ExtensionManagementUtility::siteRelPath('kiwi_popup');
		$resourcesPath = 'Resources/Public/';
		
		// add main css
		$pageRenderer->addCssFile($siteRelPath.$resourcesPath.'Css/kiwi_popup.css');
		
		// include jQuery?
		if ($this->settings['jQueryInclude']) {
			$pageRenderer->addJsLibrary('jQuery', $siteRelPath . $resourcesPath . 'Js/jQuery.min.js');
		}
		
		// add main js
		$pageRenderer->addJsFooterFile($siteRelPath.'Resources/Public/Js/kiwi_popup.js');
		
	}
	
	
	/**
	 * assign image to view
	 */
	protected function assignImage(){
		$showImage = TRUE;
		
		/** @var \TYPO3\CMS\Core\Resource\FileRepository $fileRepository */
		$fileRepository = GeneralUtility::makeInstance('TYPO3\CMS\Core\Resource\FileRepository');
		$fileObjects = $fileRepository->findByRelation('tt_content', 'image', $this->cObj->data['uid']);
		
		if (is_array($fileObjects) && count($fileObjects)) {
			foreach ($fileObjects as $key => $value) {
				$images[$key]['reference'] = $value->getReferenceProperties();
				$images[$key]['original'] = $value->getOriginalFile()->getProperties();
			}
		}
		
		// random image or first?
		$randomImageKey = (is_array($images) && count($images) > 1) ? array_rand($images, 1) : 0;
		$this->imageKey = $images[$this->imageKey]['reference']['uid'];
		$image = $images[$randomImageKey];
		
		// has this image already been showed in session?
		if ($this->settings['sessionStorageOption'] == 'image') {
			$this->sD = $GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_kiwipopup');
			if ($this->sD['image'][$this->cObj->data['uid']][$this->imageKey]) {
				$showImage = FALSE;
			}
		}
		
		if ($showImage) {
			$this->view->assign('popupimage', $image);
			$this->view->assign('imageMaxW', intval($this->settings['imageMaxW']));
			$this->view->assign('imageMaxH', intval($this->settings['imageMaxH']));
		}
	}
	
	/**
	 * Render a cObject
	 *
	 * @param int    $uid
	 * @param string $table
	 *
	 * @return string
	 */
	protected function renderCObject($uid, $table) {
		$configuration = array(
			'tables' => $table, 'source' => $uid, 'dontCheckPid' => 1,
		);
		$html = $this->cObj->cObjGetSingle('RECORDS', $configuration);
		return $html;
	}
	
	/**
	 * Assign caption to view
	 */
	protected function assignCaption() {
		if ($this->settings['showCaption']) {
			$captions = GeneralUtility::trimExplode("\n", $this->settings['captionText'], TRUE);
			if ($this->settings[type] == 'IMAGE') {
				$caption = $captions[$this->imageKey];
			} else $caption = $captions[0];
			$this->view->assign('caption', $caption);
		}
	}
	
	/**
	 * assign autoclose to view
	 */
	protected function assignAutoclose(){
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
		$this->view->assign('autoclose', $autoclose);
		$this->view->assign('hideclosebutton', $hideclosebutton);
	}
	
	/**
	 * assign popup link to view
	 */
	protected function assignPopupLink() {
		if ($this->settings['link']) {
			unset($linkconf);
			$links = GeneralUtility::trimExplode("\n", $this->settings['link'], TRUE);
			if ($this->settings[type] == 'IMAGE') {
				$link = $links[$this->imageKey];
			} else $link = $link = $links[0];
			$linkconf['parameter'] = $link;
			$linkURL = $this->cObj->typoLink_URL($linkconf);
			$this->view->assign('link', $linkURL);
		}
	}
	
	/**
	 * store session data
	 */
	function storeSessionData() {
		if (!$this->settings['sessionStorage']) exit();
		
		$sessionVars = $GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_kiwipopup');
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
		if (!$this->settings['sessionStorage']) return FALSE;
		
		// get session data
		$this->sD = $GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_kiwipopup');
		
		// check if session entry for current element exists
		switch ($this->settings['sessionStorageOption']) {
			case 'general': // GENERAL
				if ($this->sD['general'] == 1) return TRUE;
				break;
			case 'page': // PAGE ID
				if ($this->sD['page'][$GLOBALS['TSFE']->id] == 1) return TRUE;
				break;
			case 'plugin': // PLUGIN UID
				if ($this->sD['plugin'][$this->cObj->data['uid']] == 1) return TRUE;
				break;
			case 'picture': // PLUGIN UID
				return FALSE;
				break;
		}
		
		// return false if no session var found
		return FALSE;
	}
	
	
}