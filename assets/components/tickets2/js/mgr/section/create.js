Tickets2.page.CreateTickets2Section = function (config) {
    config = config || {record: {}};
    config.record = config.record || {};
    Ext.applyIf(config, {
        panelXType: 'tickets2-panel-section-create'
    });
    config.canDuplicate = false;
    config.canDelete = false;
    Tickets2.page.CreateTickets2Section.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.page.CreateTickets2Section, MODx.page.CreateResource);
Ext.reg('tickets2-page-section-create', Tickets2.page.CreateTickets2Section);


Tickets2.panel.CreateTickets2Section = function (config) {
    config = config || {};
    Tickets2.panel.CreateTickets2Section.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.panel.CreateTickets2Section, MODx.panel.Resource, {

    getFields: function (config) {
        var fields = [];
        var originals = MODx.panel.Resource.prototype.getFields.call(this, config);
        for (var i in originals) {
            if (!originals.hasOwnProperty(i)) {
                continue;
            }
            var item = originals[i];

            if (item.id == 'modx-resource-header') {
                item.html = '<h2>' + _('tickets2_section_new') + '</h2>';
            }
            else if (item.id == 'modx-resource-tabs') {
                item.stateful = true;
                item.stateId = 'tickets2-section-new-tabpanel';
                item.stateEvents = ['tabchange'];
                item.getState = function () {
                    return {activeTab: this.items.indexOf(this.getActiveTab())};
                };
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
                        tab.bodyCssClass = 'tab-panel-wrapper';
                        tab.labelAlign = 'top';
                    }
                }
            }

            if (item.id != 'modx-resource-content') {
                fields.push(item);
            }
        }

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

});
Ext.reg('tickets2-panel-section-create', Tickets2.panel.CreateTickets2Section);
