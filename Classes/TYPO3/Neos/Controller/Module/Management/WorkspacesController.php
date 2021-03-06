<?php
namespace TYPO3\Neos\Controller\Module\Management;

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

/**
 * The TYPO3 Workspaces module controller
 *
 * @Flow\Scope("singleton")
 */
class WorkspacesController extends \TYPO3\Neos\Controller\Module\AbstractModuleController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\Service\PublishingService
	 */
	protected $publishingService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\WorkspaceRepository
	 */
	protected $workspaceRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository
	 */
	protected $nodeDataRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\Domain\Repository\SiteRepository
	 */
	protected $siteRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Property\PropertyMapper
	 */
	protected $propertyMapper;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Property\PropertyMappingConfigurationBuilder
	 */
	protected $propertyMappingConfigurationBuilder;

	/**
	 * @return void
	 */
	protected function initializeAction() {
		if ($this->arguments->hasArgument('node')) {
			$this->arguments->getArgument('node')->getPropertyMappingConfiguration()->setTypeConverterOption('TYPO3\TYPO3CR\TypeConverter\NodeConverter', \TYPO3\TYPO3CR\TypeConverter\NodeConverter::REMOVED_CONTENT_SHOWN, TRUE);
		}
		parent::initializeAction();
	}

	/**
	 * @param string $workspaceName
	 * @return void
	 * @todo Pagination
	 * @todo Tree filtering + level limit
	 * @todo Search field
	 * @todo Difference mechanism
	 */
	public function indexAction($workspaceName = NULL) {
		if (is_null($workspaceName)) {
			$user = $this->securityContext->getPartyByType('TYPO3\Neos\Domain\Model\User');
			$workspaceName = $user->getPreferences()->get('context.workspace');
		}

		$sites = array();
		foreach ($this->publishingService->getUnpublishedNodes($workspaceName) as $node) {
			if (!$node->getNodeType()->isOfType('TYPO3.Neos:ContentCollection')) {
				$pathParts = explode('/', $node->getPath());
				if (count($pathParts) > 2) {
					$siteNodeName = $pathParts[2];
					$document = $this->findDocumentNode($node);
					$documentPath = implode('/', array_slice(explode('/', $document->getPath()), 3));
					$relativePath = str_replace(sprintf('/sites/%s/%s', $siteNodeName, $documentPath), '', $node->getPath());
					if (!isset($sites[$siteNodeName]['siteNode'])) {
						$sites[$siteNodeName]['siteNode'] = $this->siteRepository->findOneByNodeName($siteNodeName);
					}
					$sites[$siteNodeName]['documents'][$documentPath]['documentNode'] = $document;
					$change = array('node' => $node);
					if ($node->getNodeType()->isOfType('TYPO3.Neos:Node')) {
						$change['configuration'] = $node->getNodeType()->getConfiguration();
					}
					$sites[$siteNodeName]['documents'][$documentPath]['changes'][$relativePath] = $change;
				}
			}
		}

		$liveWorkspace = $this->workspaceRepository->findOneByName('live');

		ksort($sites);
		foreach ($sites as $siteKey => $site) {
			foreach ($site['documents'] as $documentKey => $document) {
				foreach ($document['changes'] as $changeKey => $change) {
					$liveNode = $this->nodeDataRepository->findOneByIdentifier($change['node']->getIdentifier(), $liveWorkspace);
					$sites[$siteKey]['documents'][$documentKey]['changes'][$changeKey]['isNew'] = is_null($liveNode);
					$sites[$siteKey]['documents'][$documentKey]['changes'][$changeKey]['isMoved'] = $liveNode && $change['node']->getPath() !== $liveNode->getPath();
				}
			}
			ksort($sites[$siteKey]['documents']);
		}

		$workspaces = array();
		foreach ($this->workspaceRepository->findAll() as $workspace) {
			array_push($workspaces, array(
				'workspaceNode' => $workspace,
				'unpublishedNodesCount' => $this->publishingService->getUnpublishedNodesCount($workspace->getName())
			));
		}

		$this->view->assignMultiple(array(
			'workspaceName' => $workspaceName,
			'workspaces' => $workspaces,
			'sites' => $sites
		));
	}

	/**
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $node
	 * @return void
	 */
	public function publishNodeAction(\TYPO3\TYPO3CR\Domain\Model\NodeInterface $node) {
		$this->publishingService->publishNode($node);
		$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Message('Node has been published'));
		$this->redirect('index');
	}

	/**
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $node
	 * @return void
	 */
	public function discardNodeAction(\TYPO3\TYPO3CR\Domain\Model\NodeInterface $node) {
		$this->nodeDataRepository->remove($node);
		$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Message('Node has been discarded'));
		$this->redirect('index');
	}

	/**
	 * @param array<\TYPO3\TYPO3CR\Domain\Model\NodeInterface> $nodes
	 * @param string $action
	 * @return void
	 */
	public function publishOrDiscardNodesAction(array $nodes, $action) {
		$propertyMappingConfiguration = $this->propertyMappingConfigurationBuilder->build();
		$propertyMappingConfiguration->setTypeConverterOption('TYPO3\TYPO3CR\TypeConverter\NodeConverter', \TYPO3\TYPO3CR\TypeConverter\NodeConverter::REMOVED_CONTENT_SHOWN, TRUE);
		foreach ($nodes as $key => $node) {
			$nodes[$key] = $this->propertyMapper->convert($node, 'TYPO3\TYPO3CR\Domain\Model\NodeInterface', $propertyMappingConfiguration);
		}
		switch ($action) {
			case 'publish':
				foreach ($nodes as $node) {
					$this->publishingService->publishNode($node);
				}
				$message = 'Selected changes have been published';
			break;
			case 'discard':
				foreach ($nodes as $node) {
					$this->nodeDataRepository->remove($node);
				}
				$message = 'Selected changes have been discarded';
			break;
			default:
				throw new \RuntimeException('Invalid action "' . $action . '" given.', 1346167441);
		}

		$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Message($message));
		$this->redirect('index');
	}

	/**
	 * @param string $workspaceName
	 * @return void
	 */
	public function publishWorkspaceAction($workspaceName) {
		$this->workspaceRepository->findOneByName($workspaceName)->publish('live');
		$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Message('Changes in workspace "%s" have been published', NULL, array($workspaceName)));
		$this->redirect('index');
	}

	/**
	 * @param string $workspaceName
	 * @return void
	 */
	public function discardWorkspaceAction($workspaceName) {
		foreach ($this->publishingService->getUnpublishedNodes($workspaceName) as $node) {
			if ($node->getPath() !== '/') {
				$this->nodeDataRepository->remove($node);
			}
		}
		$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Message('Changes in workspace "%s" have been discarded', NULL, array($workspaceName)));
		$this->redirect('index');
	}

	/**
	 * Finds the nearest parent document node of the provided node by looping recursively trough
	 * the node and it's parent nodes and checking if they are a sub node type of TYPO3.Neos:Document
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeInterface $node
	 * @return \TYPO3\TYPO3CR\Domain\Model\NodeInterface
	 */
	protected function findDocumentNode(\TYPO3\TYPO3CR\Domain\Model\NodeInterface $node) {
		while ($node) {
			if ($node->getNodeType()->isOfType('TYPO3.Neos:Document')) {
				return $node;
			}
			$node = $node->getParent();
		}
		return NULL;
	}

}
?>