<?php
namespace TYPO3\Neos\Tests\Unit\TypoScript\FlowQueryOperations;

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
 * Testcase for the Neos FlowQuery FilterOperation
 */
class FilterOperationTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function filterWithIdentifierUsesNodeIdentifier() {
		$node1 = $this->getMock('TYPO3\TYPO3CR\Domain\Model\NodeInterface');
		$node2 = $this->getMock('TYPO3\TYPO3CR\Domain\Model\NodeInterface');
		$node2->expects($this->any())->method('getIdentifier')->will($this->returnValue('node-identifier-uuid'));

		$context = array($node1, $node2);
		$q = new \TYPO3\Eel\FlowQuery\FlowQuery($context);

		$operation = new \TYPO3\Neos\TypoScript\FlowQueryOperations\FilterOperation();
		$operation->evaluate($q, array('#node-identifier-uuid'));

		$this->assertEquals(array($node2), $q->getContext());
	}

}
?>