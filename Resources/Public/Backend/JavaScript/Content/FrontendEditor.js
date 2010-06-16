Ext.ns("F3.TYPO3.Content");

/**
 * @class F3.TYPO3.Content.FrontendEditor
 * @namespace F3.TYPO3.Content
 * @extends Ext.Panel
 *
 * The main frontend editor.
 */
F3.TYPO3.Content.FrontendEditor = Ext.extend(Ext.Container, {
	/**
	 * Reference to the IFrame box component
	 */
	contentIframe: null,

	/**
	 * Initialize the frontend editor component
	 */
	initComponent: function() {
		var uri =
			Ext.util.Cookies.get('TYPO3FeURI') ?
			Ext.util.Cookies.get('TYPO3FeURI') :
			F3.TYPO3.Configuration.Application.frontendBaseUri;

		var config = {
			border: false,
			style: {
				overflow: 'hidden'
			},
			items: {
				itemId: 'contentIframe',
				xtype: 'box',
				ref: '../contentIframe',
				autoEl: {
					tag: 'iframe',
					src: uri,
					style: {
						width: '100%',
						height: '100%',
						border: '0'
					}
				}
			}
		};
		Ext.apply(this, config);
		F3.TYPO3.Content.FrontendEditor.superclass.initComponent.call(this);
	}
});
Ext.reg('F3.TYPO3.Content.FrontendEditor', F3.TYPO3.Content.FrontendEditor);