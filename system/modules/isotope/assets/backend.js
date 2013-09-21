/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */


var Isotope =
{

    /**
     * Make the wizards sortable
     */
    makeWizardsSortable: function() {
        $$('.tl_mediamanager .sortable').each(function(el) {
            new Sortables(el, {
                contstrain: true,
                opacity: 0.6,
                handle: '.drag-handle',
                onComplete: function() {
                    Isotope.wizardResort(el);
                }
            });
        });

        $$('.tl_fieldwizard .sortable').each(function(el) {
            new Sortables(el, {
                contstrain: true,
                opacity: 0.6,
                handle: '.drag-handle',
                onComplete: function() {
                    Isotope.wizardResort(el);
                }
            });
        });
    },

    /**
     * Media Manager
     * @param object
     * @param string
     * @param string
     */
    mediaManager: function(el, command, id) {
        var table = document.id(id).getFirst('table');
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

        Isotope.wizardResort(tbody);
    },

    /**
     * Resort the media manager fields
     * @param object
     */
    wizardResort: function(tbody) {
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
    },

    /**
     * Field wizard
     * @param object
     * @param string
     * @param string
     */
    fieldWizard: function(el, command, id)
    {
        var table = document.id(id);
        var tbody = table.getFirst().getNext();
        var parent = document.id(el).getParent('tr');
        var rows = tbody.getChildren();

        Backend.getScrollOffset();

        switch (command)
        {
            case 'copy':
                var tr = new Element('tr');
                var childs = parent.getChildren();

                for (var i=0; i<childs.length; i++)
                {
                    var next = childs[i].clone(true).injectInside(tr);
                    next.getFirst().value = childs[i].getFirst().value;

                    if (next.getFirst().type == 'checkbox')
                    {
                        next.getFirst().checked = childs[i].getFirst().checked ? 'checked' : '';
                        if (Browser.Engine.trident && Browser.Engine.version < 5) next.innerHTML = next.innerHTML.replace(/CHECKED/ig, 'checked="checked"');
                    }
                }

                tr.injectAfter(parent);
                break;

            case 'up':
                parent.getPrevious() ? parent.injectBefore(parent.getPrevious()) : parent.injectInside(tbody);
                break;

            case 'down':
                parent.getNext() ? parent.injectAfter(parent.getNext()) : parent.injectBefore(tbody.getFirst());
                break;

            case 'delete':
                (rows.length > 1) ? parent.destroy() : null;
                break;
        }

        rows = tbody.getChildren();
        var fieldnames = new Array('value', 'label', 'default');

        for (var i=0; i<rows.length; i++)
        {
            var childs = rows[i].getChildren();

            for (var j=0; j<childs.length; j++)
            {
                var first = childs[j].getFirst();

                if (first.type == 'text' || first.type == 'checkbox' || first.type == 'hidden')
                {
                    first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']')
                }
            }
        }
    },


    /**
     * Toggle checkbox group
     * @param object
     * @param string
     */
    toggleCheckboxGroup: function(el, id)
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
    },

    /**
     * Toggle the product tree (input field)
     * @param object
     * @param string
     * @param string
     * @param string
     * @param integer
     * @return boolean
     */
    toggleProductTree: function (el, id, field, name, level)
    {
        el.blur();
        var item = document.id(id);
        var image = document.id(el).getFirst();

        if (item)
        {
            if (item.getStyle('display') == 'none')
            {
                item.setStyle('display', 'inline');
                image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
                document.id(el).title = CONTAO_COLLAPSE;
                new Request.Contao().post({'action':'toggleProductTree', 'id':id, 'state':1, 'REQUEST_TOKEN':REQUEST_TOKEN});
            }
            else
            {
                item.setStyle('display', 'none');
                image.src = image.src.replace('folMinus.gif', 'folPlus.gif');
                document.id(el).title = CONTAO_EXPAND;
                new Request.Contao().post({'action':'toggleProductTree', 'id':id, 'state':0, 'REQUEST_TOKEN':REQUEST_TOKEN});
            }

            return false;
        }

        new Request.Contao(
        {
            onRequest: AjaxRequest.displayBox('Loading data …'),
            onSuccess: function(txt, json)
            {
                var ul = new Element('ul');

                ul.addClass('level_' + level);
                ul.set('html', txt);

                item = new Element('li');

                item.addClass('parent');
                item.setProperty('id', id);
                item.setStyle('display', 'inline');

                ul.injectInside(item);
                item.injectAfter(document.id(el).getParent('li'));

                document.id(el).title = CONTAO_COLLAPSE;
                image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
                AjaxRequest.hideBox();

                // HOOK
                window.fireEvent('ajax_change');
               }
        }).post({'action':'loadProductTree', 'id':id, 'level':level, 'field':field, 'name':name, 'state':1, 'REQUEST_TOKEN':REQUEST_TOKEN});

        return false;
    },

    /**
     * Toggle the product product tree (input field)
     * @param object
     * @param string
     * @param string
     * @param string
     * @param integer
     * @return boolean
     */
    toggleProductGroupTree: function (el, id, field, name, level)
    {
        el.blur();
        Backend.getScrollOffset();

        var item = $(id),
            image = $(el).getFirst('img');

        if (item)
        {
            if (item.getStyle('display') == 'none')
            {
                item.setStyle('display', 'inline');
                image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
                $(el).store('tip:title', Contao.lang.collapse);
                new Request.Contao().post({'action':'toggleProductGroupTree', 'id':id, 'state':1, 'REQUEST_TOKEN':Contao.request_token});
            }
            else
            {
                item.setStyle('display', 'none');
                image.src = image.src.replace('folMinus.gif', 'folPlus.gif');
                $(el).store('tip:title', Contao.lang.expand);
                new Request.Contao().post({'action':'toggleProductGroupTree', 'id':id, 'state':0, 'REQUEST_TOKEN':Contao.request_token});
            }

            return false;
        }

        new Request.Contao(
        {
            field: el,
            evalScripts: true,
            onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
            onSuccess: function(txt, json)
            {
                var li = new Element('li',
                {
                    'id': id,
                    'class': 'parent',
                    'styles':
                    {
                        'display': 'inline'
                    }
                });

                var ul = new Element('ul',
                {
                    'class': 'level_' + level,
                    'html': txt
                }).inject(li, 'bottom');

                li.inject($(el).getParent('li'), 'after');

                // Update the referer ID
                li.getElements('a').each(function(el) {
                    el.href = el.href.replace(/&ref=[a-f0-9]+/, '&ref=' + Contao.referer_id);
                });

                $(el).store('tip:title', Contao.lang.collapse);
                image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
                AjaxRequest.hideBox();

                // HOOK
                window.fireEvent('ajax_change');
               }
        }).post({'action':'loadProductGroupTree', 'id':id, 'level':level, 'field':field, 'name':name, 'state':1, 'REQUEST_TOKEN':Contao.request_token});

        return false;
    },

    /**
     * Open a group selector in a modal window
     * @param object
     * @return object
     */
    openModalGroupSelector: function(options)
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
            var inp = frm.document.getElementById('tl_listing').getElementsByTagName('input');
            for (var i=0; i<inp.length; i++)
            {
                if (!inp[i].checked || inp[i].id.match(/^check_all_/)) continue;
                if (!inp[i].id.match(/^reset_/)) val.push(inp[i].get('value'));
            }
            new Request.Contao(
            {
                evalScripts: false,
                onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
                onSuccess: function(txt, json)
                {
                    if (txt != '')
                    {
                        window.location.href = txt;
                    }
                }
            }).post({'action':opt.action, 'value':val[0], 'redirect':opt.redirect, 'REQUEST_TOKEN':Contao.request_token});
            this.hide();
            if (opt.trigger)
            {
                opt.trigger.fireEvent('closeModal');
            }
        });
        M.show({
            'title': opt.title,
            'contents': '<iframe src="' + opt.url + '" name="simple-modal-iframe" width="100%" height="' + opt.height + '" frameborder="0"></iframe>',
            'model': 'modal'
        });
        return M;
    },

    /**
     * Open a page selector in a modal window
     * @param object
     * @return object
     */
    openModalPageSelector: function(options)
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
            var inp = frm.document.getElementById('tl_listing').getElementsByTagName('input');
            for (var i=0; i<inp.length; i++)
            {
                if (!inp[i].checked || inp[i].id.match(/^check_all_/)) continue;
                if (!inp[i].id.match(/^reset_/)) val.push(inp[i].get('value'));
            }
            new Request.Contao(
            {
                evalScripts: false,
                onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
                onSuccess: function(txt, json)
                {
                    if (txt != '')
                    {
                        window.location.href = txt;
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
    },

    /**
     * Add the interactive help
     */
    addInteractiveHelp: function() {
        new Tips.Contao('a.tl_tip', {
            offset: {x:9, y:21},
            text: function(e) {
                return e.get('longdesc');
            }
        });
    },


    inheritFields: function(fields, label)
    {
        var injectError = false;

        fields.each(function(name, i)
        {
            var el = document.id(('ctrl_'+name));

            if (el)
            {
                var parent = el.getParent('div').getFirst('h3');

                if (!parent && el.match('.tl_checkbox_single_container'))
                {
                    parent = el;
                }

                if (!parent)
                {
                    injectError = true;
                    return;
                }

                parent.addClass('inherit');

                var check = document.id('ctrl_inherit').getFirst(('input[value='+name+']'));

                check.setStyle('float', 'right').inject(parent);
                document.id('ctrl_inherit').getFirst(('label[for='+check.get('id')+']')).setStyles({'float':'right','padding-right':'5px', 'font-weight':'normal'}).set('text', label).inject(parent);

                check.addEvent('change', function(event)
                {
                    var element = document.id(('ctrl_'+event.target.get('value')));

                    // Single checkbox
                    if (element.match('.tl_checkbox_single_container'))
                    {
                        element.getFirst('input[type=checkbox]').disabled = event.target.checked;
                    }
                    else
                    {
                        // textarea with TinyMCE
                        if (!element.getNext() || !element.getNext().get('id') || !element.getNext().get('id').test(/_parent$/))
                        {
                            element.setStyle('display', (event.target.checked ? 'none' : 'inherit'));
                        }

                        // Query would fail if there is no tooltip
                        try { element.getNext(':not(.tl_tip):not(script)').setStyle('display', (event.target.checked ? 'none' : 'inherit')); } catch (e) {}
                    }
                });

                if (el.match('.tl_checkbox_single_container'))
                {
                    el.getFirst('input[type=checkbox]').disabled = check.checked;
                }
                else
                {
                    el.setStyle('display', (check.checked ? 'none' : 'inherit'));

                    // Query would fail if there is no tooltip
                    try { el.getNext(':not(.tl_tip):not(script)').setStyle('display', (check.checked ? 'none' : 'inherit')); } catch (e) {}
                }
            }
        });

        if (!injectError)
        {
            document.id('ctrl_inherit').getParent('div').setStyle('display', 'none');
        }
    },

    /**
     * Enable blank select option
     */
    makeSelectExtendable: function()
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
    },

    /**
     * Make parent view items sortable
     * @param object
     */
    makePageViewSortable: function(ul)
    {
        var list = new Sortables(ul,
        {
            contstrain: true,
            opacity: 0.6
        });

        list.active = false;

        list.addEvent('start', function()
        {
            list.active = true;
        });

        list.addEvent('complete', function(el)
        {
            if (!list.active)
            {
                return;
            }

            if (el.getPrevious())
            {
                var id = el.get('id').replace(/li_/, '');
                var pid = el.getPrevious().get('id').replace(/li_/, '');
                var req = window.location.search.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=1&page_id=' + pid;
                new Request({url: window.location.href, method: 'get', data: req}).send();
            }
            else if (el.getParent())
            {
                var id = el.get('id').replace(/li_/, '');
                var pid = el.getParent().get('id').replace(/ul_/, '');
                var req = window.location.search.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=2&page_id=' + pid;
                new Request({url: window.location.href, method: 'get', data: req}).send();
            }
        });
    }
};

// Initialize the back end script
window.addEvent('domready', function()
{
    Isotope.addInteractiveHelp();
    Isotope.makeSelectExtendable();
    Isotope.makeWizardsSortable();
}).addEvent('structure', function()
{
    Isotope.addInteractiveHelp();
});

// Re-apply certain changes upon ajax_change
window.addEvent('ajax_change', function() {
    Isotope.addInteractiveHelp();
    Isotope.makeWizardsSortable();
});
