<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\TypoScript;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A TypoScript Html object
 *
 * @version $Id: Html.php 4448 2010-06-07 13:24:31Z robert $
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 */
class Block extends \F3\TypoScript\AbstractContentObject {

	/**
	 * @var \F3\TYPO3\Domain\Model\Content\Block
	 */
	protected $model;

	/**
	 * @var string
	 */
	protected $modelType = 'F3\TYPO3\Domain\Model\Content\Block';

	/**
	 * @var string
	 */
	protected $templateSource = 'resource://TYPO3/Private/TypoScript/Templates/Block.html';

	/**
	 * Names of the properties of this TypoScript which should be available in
	 * this TS object's template while rendering it.
	 *
	 * @var array
	 */
	protected $presentationModelPropertyNames = array('html');

	/**
	 * @var string
	 */
	protected $html;

	/**
	 * Overrides the body html of this html element
	 *
	 * @param string $html
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setHtml($html) {
		$this->html = $html;
	}

	/**
	 * Returns the body html of this html element
	 *
	 * @return string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getHtml() {
		return $this->html;
	}

}
?>