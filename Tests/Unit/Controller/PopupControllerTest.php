<?php
namespace Kiefer\KiwiPopup\Tests\Unit\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Andreas Kiefer <kiefer@kiefer.koeln>
 *  			
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

/**
 * Test case for class Kiefer\KiwiPopup\Controller\PopupController.
 *
 * @author Andreas Kiefer <kiefer@kiefer.koeln>
 */
class PopupControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{

	/**
	 * @var \Kiefer\KiwiPopup\Controller\PopupController
	 */
	protected $subject = NULL;

	public function setUp()
	{
		$this->subject = $this->getMock('Kiefer\\KiwiPopup\\Controller\\PopupController', array('redirect', 'forward', 'addFlashMessage'), array(), '', FALSE);
	}

	public function tearDown()
	{
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function listActionFetchesAllPopupsFromRepositoryAndAssignsThemToView()
	{

		$allPopups = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array(), array(), '', FALSE);

		$popupRepository = $this->getMock('Kiefer\\KiwiPopup\\Domain\\Repository\\PopupRepository', array('findAll'), array(), '', FALSE);
		$popupRepository->expects($this->once())->method('findAll')->will($this->returnValue($allPopups));
		$this->inject($this->subject, 'popupRepository', $popupRepository);

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$view->expects($this->once())->method('assign')->with('popups', $allPopups);
		$this->inject($this->subject, 'view', $view);

		$this->subject->listAction();
	}

	/**
	 * @test
	 */
	public function showActionAssignsTheGivenPopupToView()
	{
		$popup = new \Kiefer\KiwiPopup\Domain\Model\Popup();

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$this->inject($this->subject, 'view', $view);
		$view->expects($this->once())->method('assign')->with('popup', $popup);

		$this->subject->showAction($popup);
	}
}
