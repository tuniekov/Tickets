Tickets2.panel.Threads = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        layout: 'anchor',
        border: false,
        anchor: '100%',
        items: [{
            xtype: 'tickets2-grid-threads',
            cls: 'main-wrapper',
        }],
        cls: 'tickets2'
    });
    Tickets2.panel.Threads.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.panel.Threads, MODx.Panel);
Ext.reg('tickets2-panel-threads', Tickets2.panel.Threads);