<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\Domain\Model\Structure;

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
 * Domain model of a site
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 * @entity
 */
class Site extends \F3\TYPO3\Domain\Model\Structure\AbstractNode implements \F3\TYPO3\Domain\Model\Structure\IndexNodeAwareInterface {

	/**
	 * Site statusses
	 */
	const STATE_ONLINE = 1;
	const STATE_OFFLINE = 2;

	/**
	 * Name of the site
	 * @var string
	 * @validate AlphaNumeric, StringLength(minimum = 1, maximum = 255)
	 */
	protected $name = 'Untitled Site';

	/**
	 * The site's state
	 * @var integer
	 * @validate NumberRange(minimum = 1, maximum = 2)
	 */
	protected $state = self::STATE_ONLINE;

	/**
	 * @var string
	 */
	protected $siteResourcesPackageKey;

	/**
	 * Sets the name for this site
	 *
	 * @param string $name The site name
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the name of this site
	 *
	 * @return string The name
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the state for this site
	 *
	 * @param integer $state The site's state, must be one of the STATUS_* constants
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * Returns the state of this site
	 *
	 * @return integer The state - one of the STATUS_* constant's values
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * Sets the key of a package containing the static resources for this site.
	 *
	 * @param string $packageKey The package key
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setSiteResourcesPackageKey($packageKey) {
		$this->siteResourcesPackageKey = $packageKey;
	}

	/**
	 * Returns the key of a package containing the static resources for this site.
	 *
	 * @return string The package key
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getSiteResourcesPackageKey() {
		return $this->siteResourcesPackageKey;
	}

	/**
	 * Returns the index node of this site
	 *
	 * @param \F3\TYPO3\Domain\Service\ContentContext $contentContext The current content context
	 * @param string $section Must always be "default"
	 * @return \F3\TYPO3\Domain\Model\Structure\ContentNode
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getIndexNode(\F3\TYPO3\Domain\Service\ContentContext $contentContext, $section = 'default') {
		$childNodesMatchingContext = $this->getChildNodes($contentContext, $section);
		return reset($childNodesMatchingContext);
	}

}

?>