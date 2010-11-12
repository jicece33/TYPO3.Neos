<?php
declare(ENCODING = 'utf-8');
namespace F3\TYPO3\Service\ExtDirect\V1\Controller;

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
 * ExtDirect Controller for managing Workspaces
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class WorkspaceController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @var string
	 */
	protected $viewObjectNamePattern = 'F3\ExtJS\ExtDirect\View';

	/**
	 * Publishes the given sourceWorkspace to the specified targetWorkspace
	 *
	 * @param string $sourceWorkspaceName
	 * @param string $targetWorkspaceName
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @extdirect
	 */
	public function publishAction($sourceWorkspaceName, $targetWorkspaceName) {
		$sourceWorkspace = $this->objectManager->create('F3\TYPO3\Domain\Service\ContentContext', $sourceWorkspaceName)->getWorkspace();
		$sourceWorkspace->publish($targetWorkspaceName);

		$this->view->assign('value', array('data' => '', 'success' => TRUE));
	}

}
?>