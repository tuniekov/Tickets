Tickets2.page.UpdateTickets2Section = function (config) {
    config = config || {record: {}};
    config.record = config.record || {};
    Ext.applyIf(config, {
        panelXType: 'tickets2-panel-section-update',
    });
    config.canDuplicate = false;
    config.canDelete = false;
    Tickets2.page.UpdateTickets2Section.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.page.UpdateTickets2Section, MODx.page.UpdateResource);
Ext.reg('tickets2-page-section-update', Tickets2.page.UpdateTickets2Section);


Tickets2.panel.UpdateTickets2Section = function (config) {
    config = config || {};
    Tickets2.panel.UpdateTickets2Section.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.panel.UpdateTickets2Section, MODx.panel.Resource, {

    getFields: function (config) {
        var fields = [];
        var originals = MODx.panel.Resource.prototype.getFields.call(this, config);
        for (var i in originals) {
            if (!originals.hasOwnProperty(i)) {
                continue;
            }
            var item = originals[i];
            if (item.id == 'modx-resource-tabs') {
                item.stateful = true;
                item.stateId = 'tickets2-section-upd-tabpanel';
                item.stateEvents = ['tabchange'];
                item.getState = function () {
                    return {activeTab: this.items.indexOf(this.getActiveTab())};
                };
                var tabs = [];
                for (var i2 in item.items) {
                    if (!item.items.hasOwnProperty(i2)) {
                        continue;
                    }
                    var tab = item.items[i2];
                    if (tab.id == 'modx-resource-settings') {
                        tab.title = _('tickets2_section');
                        tab.items = this.getMainFields(config);
                    }
                    else if (tab.id == 'modx-page-settings') {
                        tab.title = _('tickets2_section_settings');
                        tab.items = this.getSectionSettings(config);
                        tab.cls = 'modx-resource-tab';
                        tab.bodyCssClass = 'tab-panel-wrapper form-with-labels';
                        tab.labelAlign = 'top';
                    }
                    tabs.push(tab);
                    if (tab.id == 'modx-page-settings') {
                        tabs.push(this.getComments(config));
                        if (config.mode == 'update') {
                            tabs.push(this.getSubscribes(config));
                        }
                    }
                }
                item.items = tabs;
            }
            if (item.id == 'modx-resource-content') {
                fields.push(this.getTickets2(config));
            }
            else {
                fields.push(item);
            }
        }
        fields.push(this.getTickets2(config));
        return fields;
    },

    getMainFields: function (config) {
        var fields = MODx.panel.Resource.prototype.getMainFields.call(this, config);
        fields.push({
            xtype: 'hidden',
            name: 'class_key',
            id: 'modx-resource-class-key',
            value: 'Tickets2\\Model\\Tickets2Section'
        });
        fields.push({
            xtype: 'hidden',
            name: 'content_type',
            id: 'modx-resource-content-type',
            value: MODx.config['default_content_type'] || 1
        });

        return fields;
    },

    getSectionSettings: function (config) {
        return [{
            xtype: 'tickets2-section-tab-settings',
            record: config.record,
        }];
    },

    getTickets2: function (config) {
        return [{
            xtype: 'tickets2-panel-tickets2',
            parent: config.resource,
            standalone: false,
        }];
    },

    getComments: function (config) {
        return {
            title: _('comments'),
			id: 'modx-tickets2-comments',
            items: [{
                xtype: 'tickets2-panel-comments',
                record: config.record,
                section: config.record.id,
            }]
        };
    },

    getSubscribes: function (config) {
        return {
            title: _('subscribes'),
			id: 'modx-tickets2-subscribes',
            items: [{
                xtype: 'tickets2-panel-subscribes',
                record: config.record,
                parents: config.record.id,
            }]
        };
    },

});
Ext.reg('tickets2-panel-section-update', Tickets2.panel.UpdateTickets2Section);
