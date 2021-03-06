<?php
namespace TYPO3\Neos\TypoScript;

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
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Http\Response;
use TYPO3\Flow\Mvc\Dispatcher;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Neos\Service\ContentElementWrappingService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

/**
 * A TypoScript Plugin object.
 */
class PluginImplementation extends AbstractTypoScriptObject implements \ArrayAccess {

	/**
	 * @Flow\Inject
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @Flow\Inject
	 * @var Dispatcher
	 */
	protected $dispatcher;

	/**
	 * @Flow\Inject
	 * @var ContentElementWrappingService
	 */
	protected $contentElementWrappingService;

	/**
	 * @var string
	 */
	protected $package = NULL;

	/**
	 * @var string
	 */
	protected $subpackage = NULL;

	/**
	 * @var string
	 */
	protected $controller = NULL;

	/**
	 * @var string
	 */
	protected $action = NULL;

	/**
	 * @var string
	 */
	protected $argumentNamespace = NULL;

	/**
	 * @var NodeInterface
	 */
	protected $node;

	/**
	 * @var NodeInterface
	 */
	protected $documentNode;

	/**
	 * @Flow\Inject
	 * @var SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @param string $package
	 * @return void
	 */
	public function setPackage($package) {
		$this->package = $package;
	}

	/**
	 * @return string
	 */
	public function getPackage() {
		return $this->tsValue('package');
	}

	/**
	 * @param string $subpackage
	 * @return void
	 */
	public function setSubpackage($subpackage) {
		$this->subpackage = $subpackage;
	}

	/**
	 * @return string
	 */
	public function getSubpackage() {
		return $this->tsValue('subpackage');
	}

	/**
	 * @param string $controller
	 * @return void
	 */
	public function setController($controller) {
		$this->controller = $controller;
	}

	/**
	 * @return string
	 */
	public function getController() {
		return $this->tsValue('controller');
	}

	/**
	 * @param string $action
	 * @return void
	 */
	public function setAction($action) {
		$this->action = $action;
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return $this->tsValue('action');
	}

	/**
	 * @param string $argumentNamespace
	 * @return void
	 */
	public function setArgumentNamespace($argumentNamespace) {
		$this->argumentNamespace = $argumentNamespace;
	}

	/**
	 * @return string
	 */
	public function getArgumentNamespace() {
		return $this->tsValue('argumentNamespace');
	}

	/**
	 * Build the pluginRequest object
	 *
	 * @return \TYPO3\Flow\Mvc\ActionRequest
	 */
	protected function buildPluginRequest() {
		$parentRequest = $this->tsRuntime->getControllerContext()->getRequest();
		$pluginRequest = new ActionRequest($parentRequest);
		$pluginRequest->setArgumentNamespace('--' . $this->getPluginNamespace());
		$this->passArgumentsToPluginRequest($pluginRequest);

		if ($this->node instanceof NodeInterface) {
			$pluginRequest->setArgument('__node', $this->node);
			if ($pluginRequest->getControllerPackageKey() === NULL) {
				$pluginRequest->setControllerPackageKey($this->node->getProperty('package') ?: $this->getPackage());
			}
			if ($pluginRequest->getControllerSubpackageKey() === NULL) {
				$pluginRequest->setControllerSubpackageKey($this->node->getProperty('subpackage') ?: $this->getSubpackage());
			}
			if ($pluginRequest->getControllerName() === NULL) {
				$pluginRequest->setControllerName($this->node->getProperty('controller') ?: $this->getController());
			}
			if ($pluginRequest->getControllerActionName() === NULL) {
				$actionName = $this->node->getProperty('action');
				if ($actionName === NULL) {
					$actionName = $this->getAction() !== NULL ? $this->getAction() : 'index';
				}
				$pluginRequest->setControllerActionName($actionName);
			}

			foreach ($this->properties as $key => $value) {
				$evaluatedValue = $this->tsRuntime->evaluateProcessor($key, $this, $value);
				$pluginRequest->setArgument('__' . $key, $evaluatedValue);
			}

			$pluginRequest->setArgument('__node', $this->node);
			$pluginRequest->setArgument('__documentNode', $this->documentNode);
		} else {
			$pluginRequest->setControllerPackageKey($this->getPackage());
			$pluginRequest->setControllerSubpackageKey($this->getSubpackage());
			$pluginRequest->setControllerName($this->getController());
			$pluginRequest->setControllerActionName($this->getAction());
		}
		return $pluginRequest;
	}

	/**
	 * Returns the rendered content of this plugin
	 *
	 * @return string The rendered content as a string
	 * @throws \TYPO3\Flow\Mvc\Exception\StopActionException
	 */
	public function evaluate() {
		try {
			$currentContext = $this->tsRuntime->getCurrentContext();
			$this->node = $currentContext['node'];
			$this->documentNode = $currentContext['documentNode'];
			$parentResponse = $this->tsRuntime->getControllerContext()->getResponse();
			$pluginResponse = new Response($parentResponse);

			$this->dispatcher->dispatch($this->buildPluginRequest(), $pluginResponse);
			$content = $pluginResponse->getContent();
		} catch (\Exception $exception) {
			$content = $this->tsRuntime->handleRenderingException($this->path, $exception);
		}
		if ($this->node instanceof NodeInterface) {
			return $this->contentElementWrappingService->wrapContentObject($this->node, $this->path, $content);
		} else {
			return $content;
		}
	}

	/**
	 * Returns the plugin namespace that will be prefixed to plugin parameters in URIs.
	 * By default this is <plugin_class_name>
	 *
	 * @return string
	 */
	protected function getPluginNamespace() {
		if ($this->node instanceof NodeInterface) {
			$nodeArgumentNamespace = $this->node->getProperty('argumentNamespace');
			if ($nodeArgumentNamespace !== NULL) {
				return $nodeArgumentNamespace;
			}

			$nodeArgumentNamespace = $this->node->getNodeType()->getName();
			$nodeArgumentNamespace = str_replace(':', '-', $nodeArgumentNamespace);
			$nodeArgumentNamespace = str_replace('.', '_', $nodeArgumentNamespace);
			$nodeArgumentNamespace = strtolower($nodeArgumentNamespace);
			return $nodeArgumentNamespace;
		}

		if ($this->getArgumentNamespace() !== NULL) {
			return $this->getArgumentNamespace();
		}

		$argumentNamespace = str_replace(array(':', '.', '\\'), array('_', '_', '_'), ($this->getPackage() . '_' . $this->getSubpackage() . '-' . $this->getController()));
		$argumentNamespace = strtolower($argumentNamespace);

		return $argumentNamespace;
	}

	/**
	 * Pass the arguments which were addressed to the plugin to its own request
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $pluginRequest The plugin request
	 * @return void
	 */
	protected function passArgumentsToPluginRequest(ActionRequest $pluginRequest) {
		$arguments = $pluginRequest->getMainRequest()->getPluginArguments();
		$pluginNamespace = $this->getPluginNamespace();
		if (isset($arguments[$pluginNamespace])) {
			$pluginRequest->setArguments($arguments[$pluginNamespace]);
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->evaluate();
	}
}
?>
