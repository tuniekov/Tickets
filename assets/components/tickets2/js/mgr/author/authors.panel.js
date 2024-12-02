Tickets2.panel.Authors = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        layout: 'anchor',
        border: false,
        anchor: '100%',
        items: [{
            xtype: 'tickets2-grid-authors',
            cls: 'main-wrapper',
        }],
        cls: 'tickets2',
    });
    Tickets2.panel.Authors.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.panel.Authors, MODx.Panel);
Ext.reg('tickets2-panel-authors', Tickets2.panel.Authors);