<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\Domain\Service;

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
 * The Content Service
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 */
class ContentService {

	/**
	 * @var \F3\TYPO3\Domain\Service\ContentContext
	 */
	protected $contentContext;

	/**
	 * @var \F3\FLOW3\Object\ObjectFactoryInterface $objectFactory
	 */
	protected $objectFactory;

	/**
	 * Constructs this service
	 *
	 * @param \F3\TYPO3\Domain\Service\ContentContext $contentContext The context for this service
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct(\F3\TYPO3\Domain\Service\ContentContext $contentContext) {
		$this->contentContext = $contentContext;
	}

	/**
	 * @param \F3\FLOW3\Object\ObjectFactoryInterface $objectFactory The object factory
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectObjectFactory(\F3\FLOW3\Object\ObjectFactoryInterface $objectFactory) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * Returns the Content Context this service runs in
	 *
	 * @return \F3\TYPO3\Domain\Service\ContentContext
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getContentContext() {
		return $this->contentContext;
	}

	/**
	 * Creates new content of the specified type the given reference.
	 *
	 * The reference may either be an existing content object (a page, text etc.) or
	 * an object implementing the NodeInterface (eg. a Site).
	 *
	 * @param string $contentType Object name of the content to create
	 * @param object $reference An object implementing either the ContentInterface or the NodeInterface. A new content node will be created inside the given reference.
	 * @param string $section If specified, the new content is inserted into the given section of the existing node. Note that some node types (for example "Site") don't support sections.
	 * @return object The newly created content object
	 * @throws \F3\TYPO3\Domain\Exception\InvalidReference if the given reference is of an invalid type
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function createInside($nodeName, $contentType, $reference, $section = 'default') {
		if (!is_object($reference) || !($reference instanceof \F3\TYPO3\Domain\Model\Content\ContentInterface || $reference instanceof \F3\TYPO3\Domain\Model\Structure\NodeInterface)) {
			throw new \F3\TYPO3\Domain\Exception\InvalidReference('The given reference is not a valid content node or site.', 1245411515);
		}

		$newNode = $this->objectFactory->create('F3\TYPO3\Domain\Model\Structure\ContentNode');
		$newNode->setNodeName($nodeName);

		$locale = $this->contentContext->getLocale();
		$content = $this->objectFactory->create($contentType, $locale, $newNode);

		if ($reference instanceof \F3\TYPO3\Domain\Model\Content\ContentInterface) {
			$reference->getNode()->addChildNode($newNode, $locale, $section);
		} elseif ($reference instanceof \F3\TYPO3\Domain\Model\Structure\NodeInterface) {
			$reference->addChildNode($newNode, $locale, $section);
		}
		return $content;
	}

	/**
	 * Creates new content of the specified type inside the given reference.
	 *
	 * The reference may either be an existing content object (a page, text etc.) or
	 * an object implementing the NodeInterface (eg. a Site).
	 *
	 * @param string $contentType Object name of the content to create
	 * @param object $reference An object implementing either the ContentInterface or the NodeInterface. A new content node will be created after the given reference.
	 * @return object The newly created content object
	 * @throws \F3\TYPO3\Domain\Exception\InvalidReference if the given reference is of an invalid type
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function createAfter($nodeName, $contentType, $reference) {
		if (!is_object($reference) || !($reference instanceof \F3\TYPO3\Domain\Model\Content\ContentInterface || $reference instanceof \F3\TYPO3\Domain\Model\Structure\NodeInterface)) {
			throw new \F3\TYPO3\Domain\Exception\InvalidReference('The given reference is not a valid content node or site.', 1245411516);
		}

		// TODO

		return $content;
	}
}
?>