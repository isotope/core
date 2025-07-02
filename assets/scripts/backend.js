var Isotope = {};

(function() {
	"use strict";

	/**
	 * Inherit the fields
	 * @param array
	 * @param string
	 */
    Isotope.inheritFields = function(fields, label)
    {
        var injectError = false;

        fields.each(function(name, i) {
            var el = document.id(('ctrl_'+name));

            if (el) {
                el.removeProperty('required');
                var parent = el.getParent('div').getFirst('h3');

                if (!parent && el.match('.tl_checkbox_single_container')) {
                    parent = el;
                }

                if (!parent) {
                    injectError = true;
                    return;
                }

                parent.addClass('inherit');

                var check = document.id('ctrl_inherit').getFirst(('input[value='+name+']'));

                check.setStyle('float', 'right').inject(parent);
                document.id('ctrl_inherit').getFirst(('label[for='+check.get('id')+']')).setStyles({'float':'right','padding-right':'5px', 'font-weight':'normal'}).set('text', label).inject(parent);

                check.addEvent('change', function(event) {
                    var element = document.id(('ctrl_'+event.target.get('value')));

                    // Single checkbox
                    if (element.match('.tl_checkbox_single_container')) {
                        element.getFirst('input[type=checkbox]').disabled = event.target.checked;
                    } else {
                        // textarea with TinyMCE
                        if (element.getPrevious() && element.getPrevious().hasClass('mce-tinymce')) {
                            element.getPrevious().setStyle('display', (event.target.checked ? 'none' : null));
                        } else {
                            element.setStyle('display', (event.target.checked ? 'none' : null));
                        }

                        // Query would fail if there is no tooltip
                        try { element.getAllNext(':not(script)').setStyle('display', (event.target.checked ? 'none' : null)); } catch (e) {}
                    }
                });

                if (el.match('.tl_checkbox_single_container')) {
                    el.getFirst('input[type=checkbox]').disabled = check.checked;
                } else {
                    el.setStyle('display', (check.checked ? 'none' : null));

                    // Query would fail if there is no tooltip
                    try { el.getAllNext(':not(script)').setStyle('display', (check.checked ? 'none' : null)); } catch (e) {}
                }
            }
        });

        if (!injectError) {
            document.id('ctrl_inherit').getParent('div').setStyle('display', 'none');
        }
    };
})();
