Tickets2.window.Thread = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('tickets2_thread'),
        url: Tickets2.config.connector_url,
        items: this.getItems(config),
        buttons: this.getButtons(config),
        width: 700,
        layout: 'anchor',
        autoHeight: true,
        cls: 'tickets2-window tickets2',
    });
    Tickets2.window.Thread.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.window.Thread, MODx.Window, {

    getItems: function (config) {
        return [{
            xtype: 'tickets2-grid-comments',
            section: config.section,
            parents: config.parents,
            threads: config.threads,
            pageSize: 5,
        }];
    },

    getButtons: function (config) {
        return [{
            text: _('close'),
            scope: this,
            handler: function () {
                config.closeAction !== 'close'
                    ? this.hide()
                    : this.close();
            }
        }]
    },

});
Ext.reg('tickets2-window-thread', Tickets2.window.Thread);