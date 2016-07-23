<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Kiefer.' . $_EXTKEY,
	'Kiwipopup',
	array(
		'Popup' => 'showPopup',
		
	),
	// non-cacheable actions
	array(
		'Popup' => '',
		
	)
);
