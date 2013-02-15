LwEnetMultipleActionForms = {
	previousActionUrl: null,
	formName: null,

	getForm: function() {
		var form = null;
		try {
			if (LwEnetMultipleActionForms.formName === null) {
				throw 'formName must be set.'
			}
			for (i = 0; i < document.forms.length; i++) {
				if (document.forms[i].name === LwEnetMultipleActionForms.formName) {
					form = document.forms[i];
				}
			}
			if (form === null) {
				throw 'No form with given formName: ' + LwEnetMultipleActionForms.formName + ' found.'
			}
		} catch (error) {
			LwEnetMultipleActionForms.showError(error);
		}
		return form;
	},

	triggerFormSubmit: function(element) {
		try {
			if (element.href.length === 0) {
				throw 'href must be set.'
			}
			var form = LwEnetMultipleActionForms.getForm();
			form.action = element.href;
			form.submit();
		} catch (error) {
			LwEnetMultipleActionForms.showError(error);
		}
	},

	setPreviousActionUrl: function () {
		try {
			if (LwEnetMultipleActionForms.previousActionUrl === null) {
				throw 'previousActionUrl must be set.'
			}
			var form = LwEnetMultipleActionForms.getForm();
			form.action = LwEnetMultipleActionForms.previousActionUrl;
		} catch (error) {
			LwEnetMultipleActionForms.showError(error);
		}
	},

	showError: function(error) {
		alert(error);
	}
};

