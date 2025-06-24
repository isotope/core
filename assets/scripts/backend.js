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

    /**
     * Enable blank select option
     */
    Isotope.makeSelectExtendable = function()
    {
        var collections = {};
        document.getElements('select.extendable').forEach(function(select) {

            var previous = select.value;
            var parent = select.getParent('table').id;
            collections[parent] = collections[parent] || [];
            collections[parent].push(select);

            select.grab(new Element('option', {'text':'Add …', 'value':'extendSelect'})).addEvent('change', function(e) {
                if (select.value == 'extendSelect') {
                    var name = prompt('Please enter the new group name.');

                    if (name != '') {
                        if (parent && collections[parent]) {
                            collections[parent].forEach(function(s) {
                                new Element('option', {'text':name, 'value':name}).inject(s.getLast(), 'before');
                            });
                        } else {
                            new Element('option', {'text':name, 'value':name}).inject(select.getLast(), 'before');
                        }

                        select.value = name;
                        previous = name;
                    } else {
                        select.value = previous;
                    }
                    select.fireEvent('change');
                } else {
                    previous = select.value;
                }
            });
        });
    };

    /**
     * Make parent view items sortable
     *
     * @param {object} ul The DOM element
     */
    Isotope.makeParentViewSortable = function(ul) {
        var ds = new Scroller(document.getElement('body'), {
            onChange: function(x, y) {
                this.element.scrollTo(this.element.getScroll().x, y);
            }
        });

        var list = new Sortables(ul, {
            contstrain: true,
            opacity: 0.6,
            onStart: function() {
                ds.start();
            },
            onComplete: function() {
                ds.stop();
            },
            onSort: function(el) {
                var div = el.getFirst('div'),
                    prev, next, first;

                if (!div) return;

                if (div.hasClass('wrapper_start')) {
                    if ((prev = el.getPrevious('li')) && (first = prev.getFirst('div'))) {
                        first.removeClass('indent');
                    }
                    if ((next = el.getNext('li')) && (first = next.getFirst('div'))) {
                        first.addClass('indent');
                    }
                } else if (div.hasClass('wrapper_stop')) {
                    if ((prev = el.getPrevious('li')) && (first = prev.getFirst('div'))) {
                        first.addClass('indent');
                    }
                    if ((next = el.getNext('li')) && (first = next.getFirst('div'))) {
                        first.removeClass('indent');
                    }
                } else if (div.hasClass('indent')) {
                    if ((prev = el.getPrevious('li')) && (first = prev.getFirst('div')) && first.hasClass('wrapper_stop')) {
                        div.removeClass('indent');
                    } else if ((next = el.getNext('li')) && (first = next.getFirst('div')) && first.hasClass('wrapper_start')) {
                        div.removeClass('indent');
                    }
                } else {
                    if ((prev = el.getPrevious('li')) && (first = prev.getFirst('div')) && first.hasClass('wrapper_start')) {
                        div.addClass('indent');
                    } else if ((next = el.getNext('li')) && (first = next.getFirst('div')) && first.hasClass('wrapper_stop')) {
                        div.addClass('indent');
                    }
                }
            },
            handle: '.drag-handle'
        });

        list.active = false;

        list.addEvent('start', function() {
            list.active = true;
        });

        list.addEvent('complete', function(el) {
            if (!list.active) return;
            var id, pid, req, href;

            if (el.getPrevious('li')) {
                id = el.get('id').replace(/li_/, '');
                pid = el.getPrevious('li').get('id').replace(/li_/, '');
                req = window.location.search.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=1&page_id=' + pid;
                href = window.location.href.replace(/\?.*$/, '');
                new Request.Contao({'url':href+req, 'followRedirects':false}).get();
            } else if (el.getParent('ul')) {
                id = el.get('id').replace(/li_/, '');
                pid = el.getParent('ul').get('id').replace(/ul_/, '');
                req = window.location.search.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=2&page_id=' + pid;
                href = window.location.href.replace(/\?.*$/, '');
                new Request.Contao({'url':href+req, 'followRedirects':false}).get();
            }
        });
    };
})();



// Initialize the back end script
window.addEvent('domready', function()
{
    Isotope.makeSelectExtendable();
});
