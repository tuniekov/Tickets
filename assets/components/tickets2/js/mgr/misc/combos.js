Tickets2.combo.User = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'user',
        fieldLabel: config.name || 'createdby',
        hiddenName: config.name || 'createdby',
        displayField: 'username',
        valueField: 'id',
        anchor: '99%',
        fields: ['username', 'id', 'fullname'],
        pageSize: 20,
        url: MODx.config.connector_url,
        typeAhead: false,
        minChars: 1,
        editable: true,
        allowBlank: false,
        baseParams: {
            action: 'security/user/getlist',
            combo: 1,
            id: config.value
        },
        tpl: new Ext.XTemplate('\
            <tpl for=".">\
                <div class="x-combo-list-item tickets2-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{username}</b>\
                        <tpl if="fullname"> - {fullname}</tpl>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    Tickets2.combo.User.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.combo.User, MODx.combo.ComboBox);
Ext.reg('tickets2-combo-user', Tickets2.combo.User);


MODx.combo.Tickets2Section = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        fieldLabel: _('resource_parent'),
        description: '<b>[[*parent]]</b><br />' + _('resource_parent_help'),
        fields: ['id', 'pagetitle', 'parents'],
        valueField: 'id',
        displayField: 'pagetitle',
        name: 'parent-cmb',
        hiddenName: 'parent-cmp',
        url: Tickets2.config.connector_url,
        baseParams: {
            action: 'Tickets2\\Processors\\Mgr\\Section\\GetList',
            combo: 1,
            id: config.value
        },
        pageSize: 10,
        width: 300,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        tpl: new Ext.XTemplate('\
            <tpl for=".">\
                <div class="x-combo-list-item tickets2-list-item">\
                    <tpl if="parents">\
                        <span class="parents">\
                            <tpl for="parents">\
                                <nobr>{pagetitle} / </nobr>\
                            </tpl>\
                        </span>\
                    </tpl>\
                    <span>\
                        <small>({id})</small>\
                        <b>{pagetitle}</b>\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    MODx.combo.Tickets2Section.superclass.constructor.call(this, config);
};
Ext.extend(MODx.combo.Tickets2Section, MODx.combo.ComboBox);
Ext.reg('tickets2-combo-section', MODx.combo.Tickets2Section);

/*
 Tickets2.combo.PublishStatus = function(config) {
 config = config || {};
 Ext.applyIf(config, {
 store: [[1, _('published')], [0, _('unpublished')]],
 name: 'published',
 hiddenName: 'published',
 triggerAction: 'all',
 editable: false,
 selectOnFocus: false,
 preventRender: true,
 forceSelection: true,
 enableKeyEvents: true
 });
 Tickets2.combo.PublishStatus.superclass.constructor.call(this, config);
 };
 Ext.extend(Tickets2.combo.PublishStatus, MODx.combo.ComboBox);
 Ext.reg('tickets2-combo-publish-status', Tickets2.combo.PublishStatus);


 Tickets2.combo.FilterStatus = function(config) {
 config = config || {};
 Ext.applyIf(config,{
 store: [['', _('ticket_all')], ['published', _('published')], ['unpublished', _('unpublished')], ['deleted', _('deleted')]],
 name: 'filter',
 hiddenName: 'filter',
 triggerAction: 'all',
 editable: false,
 selectOnFocus: false,
 preventRender: true,
 forceSelection: true,
 enableKeyEvents: true,
 emptyText: _('select')
 });
 Tickets2.combo.FilterStatus.superclass.constructor.call(this,config);
 };
 Ext.extend(Tickets2.combo.FilterStatus,MODx.combo.ComboBox);
 Ext.reg('tickets2-combo-filter-status',Tickets2.combo.FilterStatus);
 */

Tickets2.combo.TicketThread = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        fieldLabel: _('ticket_thread'),
        fields: ['id', 'name', 'pagetitle'],
        valueField: 'id',
        displayField: 'name',
        name: 'thread',
        hiddenName: 'thread',
        url: Tickets2.config.connector_url,
        baseParams: {
            action: 'Tickets2\\Processors\\Mgr\\Thread\\GetList',
            combo: 1,
            id: config.value
        },
        pageSize: 10,
        width: 300,
        typeAhead: false,
        editable: true,
        allowBlank: false,
        tpl: new Ext.XTemplate('\
            <tpl for=".">\
                <div class="x-combo-list-item tickets2-list-item">\
                    <span>\
                        <small>({id})</small>\
                        <b>{name}</b> - {pagetitle}\
                    </span>\
                </div>\
            </tpl>',
            {compiled: true}
        ),
    });
    Tickets2.combo.TicketThread.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.combo.TicketThread, MODx.combo.ComboBox);
Ext.reg('tickets2-combo-thread', Tickets2.combo.TicketThread);


Tickets2.combo.Template = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'properties[tickets2][template]',
        hiddenName: 'properties[tickets2][template]',
        url: MODx.config.connector_url,
        baseParams: {
            action: 'element/template/getlist',
            combo: 1,
        }
    });
    Tickets2.combo.Template.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.combo.Template, MODx.combo.Template);
Ext.reg('tickets2-children-combo-template', Tickets2.combo.Template);


Tickets2.combo.Search = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        xtype: 'twintrigger',
        ctCls: 'x-field-search',
        allowBlank: true,
        msgTarget: 'under',
        emptyText: _('search'),
        name: 'query',
        triggerAction: 'all',
        clearBtnCls: 'x-field-search-clear',
        searchBtnCls: 'x-field-search-go',
        onTrigger1Click: this._triggerSearch,
        onTrigger2Click: this._triggerClear,
    });
    Tickets2.combo.Search.superclass.constructor.call(this, config);
    this.on('render', function () {
        this.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
            this._triggerSearch();
        }, this);
    });
    this.addEvents('clear', 'search');
};
Ext.extend(Tickets2.combo.Search, Ext.form.TwinTriggerField, {

    initComponent: function () {
        Ext.form.TwinTriggerField.superclass.initComponent.call(this);
        this.triggerConfig = {
            tag: 'span',
            cls: 'x-field-search-btns',
            cn: [
                {tag: 'div', cls: 'x-form-trigger ' + this.searchBtnCls},
                {tag: 'div', cls: 'x-form-trigger ' + this.clearBtnCls}
            ]
        };
    },

    _triggerSearch: function () {
        this.fireEvent('search', this);
    },

    _triggerClear: function () {
        this.fireEvent('clear', this);
    },

});
Ext.reg('tickets2-field-search', Tickets2.combo.Search);
