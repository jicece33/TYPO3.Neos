<?php
namespace TYPO3\Neos\Tests\Functional\TypoScript;

	/*                                                                        *
	 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
	 *                                                                        *
	 * It is free software; you can redistribute it and/or modify it under    *
	 * the terms of the GNU General Public License, either version 3 of the   *
	 * License, or (at your option) any later version.                        *
	 *                                                                        *
	 * The TYPO3 project - inspiring people to share!                         *
	 *                                                                        */


/**
 * Base test case for nodes
 */
abstract class AbstractNodeTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @var boolean
	 */
	protected $testableSecurityEnabled = TRUE;

	/**
	 * @var \TYPO3\TYPO3CR\Domain\Model\NodeInterface
	 */
	protected $node;

	/**
	 * @var \TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface
	 */
	protected $contextFactory;

	public function setUp() {
		parent::setUp();
		$this->contextFactory = $this->objectManager->get('TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface');
		$contentContext = $this->contextFactory->create(array('workspaceName' => 'live'));
		$siteImportService = $this->objectManager->get('TYPO3\Neos\Domain\Service\SiteImportService');
		$siteImportService->importSitesFromFile(__DIR__ . '/../Fixtures/NodeStructure.xml', $contentContext);
		$this->persistenceManager->persistAll();

		$propertyMapper = $this->objectManager->get('TYPO3\Flow\Property\PropertyMapper');
		$this->node = $propertyMapper->convert('/sites/example/home', 'TYPO3\TYPO3CR\Domain\Model\Node');
		$this->assertFalse($propertyMapper->getMessages()->hasErrors());
	}

	public function tearDown() {
		parent::tearDown();

		$this->inject($this->contextFactory, 'contextInstances', array());
	}
}
?>