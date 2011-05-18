Ext.ns('F3.TYPO3.Module.Dummy');

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
 * @class F3.TYPO3.Module.DummyModule
 *
 * Module Descriptor for the Dummy module, faking functionality which is not yet there.
 *
 * @namespace F3.TYPO3.Module.Dummy
 * @singleton
 */
F3.TYPO3.Core.Application.createModule('F3.TYPO3.Module.DummyModule', {

	/**
	 * Modify the registry
	 *
	 * @param {F3.TYPO3.Core.Registry} registry
	 * @return {void}
	 */
	configure: function(registry) {

		/**
		 * This is an exmple of how a module can be registered in the registry
		 *
		 * registry.append('menu[main]', 'report', {
		 *	title: 'Report',
		 *	itemId: 'report'
		 * });
		 */
	},

	/**
	 * Set up event handlers
	 *
	 * @param {F3.TYPO3.Core.Application} application
	 * @return {void}
	 */
	initialize: function(application) {
		application.afterInitializationOf('F3.TYPO3.Module.UserInterfaceModule', function(userInterfaceModule) {

			userInterfaceModule.addContentArea('report', 'dummy', {
				xtype: 'F3.TYPO3.Module.Dummy.DummyContentArea',
				name: F3.TYPO3.Core.I18n.get('TYPO3', 'report')
			});
			userInterfaceModule.contentAreaOn('menu[main]/report', 'report', 'dummy');

			userInterfaceModule.addContentArea('layout', 'dummy', {
				xtype: 'F3.TYPO3.Module.Dummy.DummyContentArea',
				name: F3.TYPO3.Core.I18n.get('TYPO3', 'layout')
			});
			userInterfaceModule.contentAreaOn('menu[main]/layout', 'layout', 'dummy');

			userInterfaceModule.addContentArea('system', 'dummy', {
				xtype: 'F3.TYPO3.Module.Dummy.DummyContentArea',
				name: F3.TYPO3.Core.I18n.get('TYPO3', 'system')
			});
			userInterfaceModule.contentAreaOn('menu[main]/system', 'system', 'dummy');
		});
	}
});