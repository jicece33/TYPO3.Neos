<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\Service\ExtDirect\V1\View;

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
 * An ExtDirect View specialized on single or multiple Nodes
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @scope prototype
 */
class NodeView extends \F3\ExtJS\ExtDirect\View {

	const LISTSTYLE = 1;
	const TREESTYLE = 2;

	/**
	 * Assigns a node to the NodeView.
	 *
	 * @param \F3\TYPO3CR\Domain\Model\Node $node The node to render
	 * @param array $propertyNames Optional list of property names to include in the JSON output
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function assignNode(\F3\TYPO3CR\Domain\Model\Node $node, array $propertyNames = array('name', 'path', 'identifier', 'properties', 'contentType')) {
		$this->setConfiguration(
			array(
				'value' => array(
					'data' => array(
						'_only' => array('name', 'path', 'identifier', 'properties', 'contentType'),
						'_descend' => array('properties' => $propertyNames)
					)
				)
			)
		);
		$this->assign('value', array('data' => $node, 'success' => TRUE));
	}

	/**
	 * Prepares this view to render the specified list of nodes
	 *
	 * @param array<\F3\TYPO3CR\Domain\Model\Node> $nodes The nodes to render
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function assignNodes(array $nodes) {
		$data = array();
		$propertyNames = array();

		foreach ($nodes as $node) {
			$this->collectNodeData($data, $propertyNames, $node);
		}

		$this->setConfiguration(array('value' => array('data' => array('_descendAll' => array()))));
		$this->assign('value',
			array(
				'data' => $data,
				'metaData' => array(
					'idProperty' => '__nodePath',
					'root' => 'data',
					'fields' => array_keys($propertyNames)
				),
				'success' => TRUE
			)
		);
	}


	/**
	 * Prepares this view to render a list or tree of child nodes of the given node.
	 *
	 * @param \F3\TYPO3CR\Domain\Model\Node $node The node to fetch child nodes of
	 * @param string $contentTypeFilter Criteria for filtering the child nodes
	 * @param integer $outputStyle Either TREESTYLE or LISTSTYLE
	 * @param integer $depth how many levels of childNodes (0 = unlimited)
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Christian Müller <christian@kitsunet.de>
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function assignChildNodes(\F3\TYPO3CR\Domain\Model\Node $node, $contentTypeFilter, $outputStyle = self::LISTSTYLE, $depth = 0) {
		$contentTypeFilter = ($contentTypeFilter === '' ? NULL : $contentTypeFilter);
		$metaData = array();

		switch ($outputStyle) {
			case self::TREESTYLE :
				$data = array();
				foreach ($node->getChildNodes($contentTypeFilter) as $childNode) {
					$data[] = array(
						'id' => $childNode->getPath(),
						'text' => $childNode->getProperty('title'),
						'leaf' => $childNode->hasChildNodes() === FALSE,
						'cls' => 'folder'

					);
				}
			break;

			case self::LISTSTYLE :
				$data = array();
				$propertyNames = array();
				$this->collectChildNodeData($data, $propertyNames, $node, $contentTypeFilter, $depth);
				$metaData = array(
					'idProperty' => '__nodePath',
					'root' => 'data',
					'fields' => array_keys($propertyNames)
				);
		}

		$this->setConfiguration(array('value' => array('data' => array('_descendAll' => array()))));
		$value = array('data' => $data, 'success' => TRUE);
		if ($metaData !== array()) {
			$value['metaData'] = $metaData;
		}
		$this->assign('value', $value);
	}

	/**
	 * Collect node data and recurse into child nodes
	 *
	 * @param array &$data
	 * @param array &$propertyNames
	 * @param \F3\TYPO3CR\Domain\Model\Node $node
	 * @param string $contentTypeFilter
	 * @param integer $depth levels of child nodes to fetch. 0 = unlimited
	 * @param integer $recursionPointer current recursion level
	 * @return void
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	protected function collectChildNodeData(array &$data, array &$propertyNames, \F3\TYPO3CR\Domain\Model\Node $node, $contentTypeFilter, $depth = 0, $recursionPointer = 1) {
		foreach ($node->getChildNodes($contentTypeFilter) as $childNode) {
			$this->collectNodeData($data, $propertyNames, $childNode);
			if ($depth === 0 || ($recursionPointer < $depth)) {
				$this->collectChildNodeData($data, $propertyNames, $childNode, $contentTypeFilter, $depth, ($recursionPointer+1));
			}
		}
	}

	/**
	 * Collects node data of the given node
	 *
	 * @param array &$data
	 * @param array &$propertyNames
	 * @param \F3\TYPO3CR\Domain\Model\Node $node
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function collectNodeData(array &$data, array &$propertyNames, \F3\TYPO3CR\Domain\Model\Node $node) {
		$properties = $node->getProperties();
		$properties['__nodePath'] = $node->getPath();
		$properties['__workspaceName'] = $node->getWorkspace()->getName();
		$properties['__nodeName'] = $node->getName();
		$properties['__contentType'] = $node->getContentType();
		$properties['__label'] = $node->getLabel();
		$properties['__abstract'] = $node->getAbstract();
		$data[] = $properties;

		foreach ($properties as $propertyName => $propertyValue) {
			$propertyNames[$propertyName] = TRUE;
		}

	}
}
?>