define(
	[
		'emberjs',
		'Library/underscore',
		'vie/instance',
		'Content/Application',
		'Shared/ResourceCache',
		'text!./InsertDocumentNodePanel.html'
	],
	function (Ember, _, vie, ContentModule, ResourceCache, template) {
		return Ember.View.extend({
			template: Ember.Handlebars.compile(template),
			classNames: ['neos-overlay-component'],

			_node: null,

			content: function () {
				var groupedDocumentNodeTypes = [];

				$.when(ResourceCache.get(T3.Configuration.NodeTypeSchemaUri)).done(function(dataString) {
					$.each(JSON.parse(dataString), function (nodeType, nodeTypeInfo) {
						if ($.inArray('TYPO3.Neos:Document', nodeTypeInfo.superTypes) > -1) {
							var groupName = (nodeTypeInfo.ui.group ? nodeTypeInfo.ui.group : 'General');
							if (!groupedDocumentNodeTypes[groupName]) {
								groupedDocumentNodeTypes[groupName] = {
									'name': groupName,
									'children': []
								};
							}
							groupedDocumentNodeTypes[groupName].children.push({
								'nodeType': nodeType,
								'label': nodeTypeInfo.ui.label,
								'icon': (nodeTypeInfo.ui.icon ? nodeTypeInfo.ui.icon : 'icon-file')
							});
						}
					});
				}).fail(function(xhr, status, error) {
					console.error('Error loading node type schemata.', xhr, status, error);
				});

				var data = [];
				T3.Configuration.nodeTypeGroups.forEach(function(groupName) {
					if (groupedDocumentNodeTypes[groupName]) {
						data.push(groupedDocumentNodeTypes[groupName]);
					}
				});

				return data;
			}.property(),

			cancel: function () {
				this.destroy();
			}
		});
	}
);
