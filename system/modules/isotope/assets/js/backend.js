/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

var Isotope = {};

(function() {
	"use strict";

    /**
     * Toggle checkbox group
     * @param object
     * @param string
     */
    Isotope.toggleCheckboxGroup = function(el, id)
    {
        var cls = document.id(el).className;
        var status = document.id(el).checked ? 'checked' : '';

        if (cls == 'tl_checkbox')
        {
            $$('#' + id + ' .tl_checkbox').each(function(checkbox)
            {
                if (!checkbox.disabled)
                    checkbox.checked = status;
            });
        }
        else if (cls == 'tl_tree_checkbox')
        {
            $$('#' + id + ' .parent .tl_tree_checkbox').each(function(checkbox)
            {
                if (!checkbox.disabled)
                    checkbox.checked = status;
            });
        }

        Backend.getScrollOffset();
    };

    /**
     * Open a group selector in a modal window
     * @param object
     * @return object
     */
    Isotope.openModalGroupSelector = function(options)
    {
        var opt = options || {};
        var max = (window.getSize().y-180).toInt();
        if (!opt.height || opt.height > max) opt.height = max;
        var M = new SimpleModal(
        {
            'width': opt.width,
            'btn_ok': Contao.lang.close,
            'draggable': false,
            'overlayOpacity': .5,
            'onShow': function() { document.body.setStyle('overflow', 'hidden'); },
            'onHide': function() { document.body.setStyle('overflow', 'auto'); }
        });
        M.addButton(Contao.lang.close, 'btn', function()
        {
            this.hide();
        });
        M.addButton(Contao.lang.apply, 'btn primary', function()
        {
            var val = [],
                frm = null,
                frms = window.frames;
            for (var i=0; i<frms.length; i++)
            {
                if (frms[i].name == 'simple-modal-iframe')
                {
                    frm = frms[i];
                    break;
                }
            }
            if (frm === null)
            {
                alert('Could not find the SimpleModal frame');
                return;
            }
            var container = frm.document.getElementById('tl_select');
            var inp = container.getElementsByTagName('input');
            for (var i=0; i<inp.length; i++)
            {
                if (!inp[i].checked || inp[i].id.match(/^check_all_/)) continue;
                if (!inp[i].id.match(/^reset_/)) val.push(inp[i].get('value'));
            }
            new Request.Contao({
                evalScripts: false,
                onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
                onSuccess: function(txt, json) {
                    if (txt != '') {
                        window.location.href = txt;
                    }
                },
                onFailure: function(xhr) {
                    if ([302,303,307].indexOf(xhr.status) > -1 && xhr.responseText != '') {
                        window.location.href = xhr.responseText;
                    }
                }
            }).post({'action':opt.action, 'value':val[0], 'redirect':opt.redirect, 'REQUEST_TOKEN':Contao.request_token});
            this.hide();
            if (opt.trigger) {
                opt.trigger.fireEvent('closeModal');
            }
        });
        M.show({
            'title': opt.title,
            'contents': '<iframe src="' + opt.url + '" name="simple-modal-iframe" width="100%" height="' + opt.height + '" frameborder="0"></iframe>',
            'model': 'modal'
        });
        return M;
    };

    /**
     * Open a page selector in a modal window
     * @param object
     * @return object
     */
    Isotope.openModalPageSelector = function(options)
    {
        var opt = options || {};
        var max = (window.getSize().y-180).toInt();
        if (!opt.height || opt.height > max) opt.height = max;
        var M = new SimpleModal(
        {
            'width': opt.width,
            'btn_ok': Contao.lang.close,
            'draggable': false,
            'overlayOpacity': .5,
            'onShow': function() { document.body.setStyle('overflow', 'hidden'); },
            'onHide': function() { document.body.setStyle('overflow', 'auto'); }
        });
        M.addButton(Contao.lang.close, 'btn', function()
        {
            this.hide();
        });
        M.addButton(Contao.lang.apply, 'btn primary', function()
        {
            var val = 0,
                frm = null,
                frms = window.frames;
            for (var i=0; i<frms.length; i++)
            {
                if (frms[i].name == 'simple-modal-iframe')
                {
                    frm = frms[i];
                    break;
                }
            }
            if (frm === null)
            {
                alert('Could not find the SimpleModal frame');
                return;
            }
            var container = frm.document.getElementById('tl_select');
            var inp = container.getElementsByTagName('input');
            for (var i=0; i<inp.length; i++)
            {
                if (!inp[i].checked || inp[i].id.match(/^check_all_/)) continue;
                if (!inp[i].id.match(/^reset_/)) val = inp[i].get('value');
            }
            new Request.Contao(
            {
                evalScripts: false,
                onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
                onSuccess: function(txt, json) {
                    if (txt != '') {
                        window.location.href = txt;
                    }
                },
                onFailure: function(xhr) {
                    if ([302,303,307].indexOf(xhr.status) > -1 && xhr.responseText != '') {
                        window.location.href = xhr.responseText;
                    }
                }
            }).post({'action':opt.action, 'value':val, 'redirect':opt.redirect, 'REQUEST_TOKEN':Contao.request_token});
            this.hide();
        });
        M.show({
            'title': opt.title,
            'contents': '<iframe src="' + opt.url + '" name="simple-modal-iframe" width="100%" height="' + opt.height + '" frameborder="0"></iframe>',
            'model': 'modal'
        });
        return M;
    };

    /**
     * Add the interactive help
     */
    Isotope.addInteractiveHelp = function() {
        new Tips.Contao('a.tl_tip', {
            offset: {x:9, y:21},
            text: function(e) {
                return e.get('longdesc');
            }
        });
    };

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

Isotope.MediaManager = {};

(function() {
    "use strict";

    /**
     * Initialize the MediaManager
     * @param object
     * @param string
     * @param string
     * @return object
     */
    Isotope.MediaManager.init = function(el, field, extensions) {
        var container = $('ctrl_' + field);
        var files = [];
        var chunks, i, input_index, inputs, input_name, value;

        var params = {
            element: document.id(el),
            request: {
                endpoint: window.location.href,
                inputName: field,
                params: {
                    action: 'uploadMediaManager',
                    name: field,
                    REQUEST_TOKEN: Contao.request_token
                }
            },
		    failedUploadTextDisplay: {
		        mode: 'custom',
		        maxChars: 50,
		        responseProperty: 'error'
		    },
            validation: {
                allowedExtensions: extensions
            },
            callbacks: {
                onUpload: function() {
                    AjaxRequest.displayBox(Contao.lang.loading + ' …');
                },
                onComplete: function(id, name, result) {
                    if (!result.success) {
                        AjaxRequest.hideBox();
                        return;
                    }

                    // Add the uploaded file to value
                    if (result.file) {
                        files.push(result.file);
                    }

                    if (this.getInProgress() > 0) {
                        return;
                    }

                    value = {};
                    inputs = container.getElements('[name^="' + field + '"]');

                    // Collect the values
                    for (i=0; i<inputs.length; i++) {
                        chunks = inputs[i].get('name').split('[');

                        if (chunks.length != 3) {
                            continue;
                        }

                        input_index = chunks[1].replace(']', '');

                        if (!value[input_index]) {
                            value[input_index] = {};
                        }

                        input_name = chunks[2].replace(']', '');

                        if (inputs[i].get('type') == 'radio') {
                            if (!value[input_index][input_name]) {
                                value[input_index][input_name] = '';
                            }

                            if (inputs[i].get('checked')) {
                                value[input_index][input_name] = inputs[i].get('value');
                            }
                        } else {
                            value[input_index][input_name] = inputs[i].get('value');
                        }
                    }

                    new Request.Contao({
                        evalScripts: false,
                        onSuccess: function(txt, json) {
                            container.getElement('div').set('html', json.content);
                            json.javascript && Browser.exec(json.javascript);
                            AjaxRequest.hideBox();
                            window.fireEvent('ajax_change');
                        }
                    }).post({'action':'reloadMediaManager', 'name':field, 'value':value, 'files':files, 'REQUEST_TOKEN':Contao.request_token});

                    // Empty the files
                    files = [];
                }
            }
        };

        return new qq.FineUploader(params);
    };

    /**
     * Make the wizards sortable
     */
    Isotope.MediaManager.makeSortable = function() {
        $$('.tl_mediamanager .sortable').each(function(el) {
            new Sortables(el, {
                contstrain: true,
                opacity: 0.6,
                handle: '.drag-handle',
                onComplete: function() {
                    Isotope.MediaManager.resort(el);
                }
            });
        });
    };

    /**
     * Perform a MediaManager action (button handler)
     * @param object
     * @param string
     * @param string
     */
    Isotope.MediaManager.act = function(el, command, id) {
        var table = document.id(id).getElement('table');
        var tbody = table.getFirst('tbody');
        var parent = document.id(el).getParent('tr');
        var rows = tbody.getChildren();

        Backend.getScrollOffset();

        switch (command) {
            case 'up':
                parent.getPrevious() ? parent.injectBefore(parent.getPrevious()) : parent.injectInside(tbody);
                break;
            case 'down':
                parent.getNext() ? parent.injectAfter(parent.getNext()) : parent.injectBefore(tbody.getFirst());
                break;
            case 'delete':
                parent.destroy();
                break;
        }

        Isotope.MediaManager.resort(tbody);
    };

    /**
     * Resort the media manager fields
     * @param object
     */
    Isotope.MediaManager.resort = function(tbody) {
        var rows = tbody.getChildren(),
            textarea, inputs, labels, i, j;

        for (i=0; i<rows.length; i++) {
            inputs = rows[i].getElements('[name]');

            // Update the inputs
            for (j=0; j<inputs.length; j++) {
                inputs[j].name = inputs[j].name.replace(/\[[0-9]+\]/g, '[' + i + ']');
                inputs[j].id = inputs[j].id.replace(/_[0-9]+/g, '_' + i);
            }

            labels = rows[i].getElements('label');

            // Update the labels
            for (j=0; j<labels.length; j++) {
                labels[j].set('for', labels[j].get('for').replace(/_[0-9]+/g, '_' + i));
            }
        }
    };
})();

// Initialize the back end script
window.addEvent('domready', function()
{
    Isotope.addInteractiveHelp();
    Isotope.makeSelectExtendable();
    Isotope.MediaManager.makeSortable();
}).addEvent('structure', function()
{
    Isotope.addInteractiveHelp();
});

// Re-apply certain changes upon ajax_change
window.addEvent('ajax_change', function() {
    Isotope.addInteractiveHelp();
    Isotope.MediaManager.makeSortable();
});
