define(
[
	'Library/jquery-with-dependencies',
	'./SelectBoxEditor',
    '../InspectorController'
],
function(
    $,
    SelectBoxEditor,
    InspectorController
) {
	return SelectBoxEditor.extend({
		init: function() {
			this.set('placeholder', 'Loading ...');
			this._loadOptionsOnChange();
            InspectorController.get('nodeProperties').addObserver('plugin', this, '_loadOptionsOnChange')

		    this._super();
		},
		_loadOptionsOnChange: function() {
			var nodePath = InspectorController.get('nodeProperties.plugin');
			var workspaceName = InspectorController.get('nodeProperties.__workspacename');
			var that = this;

			if (!Ember.empty(nodePath)) {
				this._loadValuesFromController('/neos/content/pluginViews?node=' + nodePath + '@' + workspaceName, function(results) {
					var values = {}, placeholder, i = 0;

					values[''] = {};

					for (var key in results) {
						if (results[key] === undefined || results[key].label === undefined) {
							continue;
						}
						values[key] = {
							value: key,
							label: results[key].label,
							disabled: results[key].pageNode !== undefined
						};
						i++;
					}
					if (i > 0) {
						placeholder = 'Select a View';
					} else {
						placeholder = 'No view configured for this plugin';
						values = {};
					}
					that.setProperties({
						placeholder: placeholder,
						values: values
					});
				});
			} else {
				this.set('placeholder', 'No Plugin selected');
			}
		}
	});
});
