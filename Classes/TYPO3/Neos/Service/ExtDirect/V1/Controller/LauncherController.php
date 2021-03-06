<?php
namespace TYPO3\Neos\Service\ExtDirect\V1\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\ExtJS\Annotations\ExtDirect;

/**
 * ExtDirect Controller for launcher search
 *
 * @Flow\Scope("singleton")
 */
class LauncherController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @var string
	 */
	protected $viewObjectNamePattern = 'TYPO3\ExtJS\ExtDirect\View';

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\Domain\Service\NodeSearchService
	 */
	protected $nodeSearchService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\NodeTypeManager
	 */
	protected $nodeTypeManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * Select special error action
	 *
	 * @return void
	 */
	protected function initializeAction() {
		$this->errorMethodName = 'extErrorAction';
	}

	/**
	 * Returns the specified node
	 *
	 * @param string $term
	 * @param integer $requestIndex
	 * @return void
	 * @ExtDirect
	 * @todo Improve this WIP search implementation
	 */
	public function searchAction($term, $requestIndex) {
		$user = $this->securityContext->getPartyByType('TYPO3\Neos\Domain\Model\User');
		$workspaceName = $user->getPreferences()->get('context.workspace');

		$searchContentGroups = array();
		$searchNodeTypes = array();
		foreach (array('TYPO3.Neos:Page', 'TYPO3.Neos:Content') as $nodeType) {
			$searchContentGroups[$nodeType] = $this->nodeTypeManager->getNodeType($nodeType)->getConfiguration();
			array_push($searchNodeTypes, $nodeType);
			$subNodeTypes = $this->nodeTypeManager->getSubNodeTypes($nodeType);
			if (count($subNodeTypes) > 0) {
				$searchContentGroups[$nodeType]['subNodeTypes'] = $subNodeTypes;
				$searchNodeTypes = array_merge($searchNodeTypes, array_keys($subNodeTypes));
			}
		}

		$staticWebBaseUri = $this->resourcePublisher->getStaticResourcesWebBaseUri() . 'Packages/TYPO3.Neos/';

		$this->uriBuilder->setRequest($this->request->getMainRequest());

		$groups = array();
		foreach ($this->nodeSearchService->findByProperties($term, $searchNodeTypes) as $result) {
			$nodeType = $result->getNodeType();

			if (array_key_exists($nodeType->getName(), $searchContentGroups)) {
				$type = $nodeType->getName();
			} else {
				foreach ($searchContentGroups as $searchContentGroup => $searchContentGroupConfiguration) {
					if (isset($searchContentGroupConfiguration['subNodeTypes']) && array_key_exists($nodeType->getName(), $searchContentGroupConfiguration['subNodeTypes'])) {
						$type = $searchContentGroup;
						break;
					}
				}
			}
			if (!array_key_exists($type, $groups)) {
				$groups[$type] = array(
					'type' => $nodeType->getName(),
					'label' => $searchContentGroups[$type]['ui']['launcher']['searchCategory'],
					'items' => array()
				);
			}

			$this->uriBuilder->reset();
			if ($result->getNodeType()->isOfType('TYPO3.Neos:Page')) {
				$pageNode = $result;
			} else {
				$pageNode = $this->findNextParentDocumentNode($result);
				$this->uriBuilder->setSection('c' . $result->getIdentifier());
			}
			$searchResult = array(
				'type' => $nodeType->getName(),
				'label' => $result->getLabel(),
				'action' => $this->uriBuilder->uriFor('show', array('node' => $pageNode), 'Frontend\Node', 'TYPO3.Neos'),
				'path' => $result->getPath()
			);
			$nodeTypeConfiguration = $nodeType->getConfiguration();
			if (isset($nodeTypeConfiguration['ui']['icon'])) {
				$searchResult['icon'] = $staticWebBaseUri . $nodeTypeConfiguration['ui']['icon'];
			}
			array_push($groups[$type]['items'], $searchResult);
		}
		$data = array(
			'requestIndex' => $requestIndex,
			'actions' => array(
				array(
					'label' => 'Clear all cache',
					'command' => 'clear:cache:all'
				),
				array(
					'label' => 'Clear page cache',
					'command' => 'clear:cache:pages'
				)
			),
			'results' => array_values($groups)
		);
		$this->view->assign('value', array('data' => $data, 'success' => TRUE));
	}

	/**
	 * A preliminary error action for handling validation errors
	 * by assigning them to the ExtDirect View that takes care of
	 * converting them.
	 *
	 * @return void
	 */
	public function extErrorAction() {
		$this->view->assignErrors($this->arguments->getValidationResults());
	}

	/**
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $node
	 * @return \TYPO3\TYPO3CR\Domain\Model\NodeInterface
	 */
	protected function findNextParentDocumentNode(\TYPO3\TYPO3CR\Domain\Model\NodeInterface $node) {
		while ($node = $node->getParent()) {
			if ($node->getNodeType()->isOfType('TYPO3.Neos:Document')) {
				return $node;
			}
		}
		return NULL;
	}

}
?>