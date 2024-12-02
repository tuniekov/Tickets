Tickets2.panel.Tickets2 = function (config) {
    config = config || {};
    if (typeof config.standalone == 'undefined') {
        config.standalone = true;
    }

    Ext.applyIf(config, {
        layout: 'anchor',
        border: false,
        anchor: '100%',
        items: [{
            xtype: 'tickets2-grid-tickets2',
            cls: 'main-wrapper',
            standalone: config.standalone,
            parent: config.parent || 0,
        }],
        cls: 'tickets2',
    });
    Tickets2.panel.Tickets2.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.panel.Tickets2, MODx.Panel);
Ext.reg('tickets2-panel-tickets2', Tickets2.panel.Tickets2);