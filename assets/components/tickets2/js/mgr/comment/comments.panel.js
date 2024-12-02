Tickets2.panel.Comments = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        layout: 'anchor',
        border: false,
        anchor: '100%',
        items: [{
            xtype: 'tickets2-grid-comments',
            cls: 'main-wrapper',
            section: config.section || 0,
            parents: config.parents || 0,
            threads: config.threads || 0,
        }],
        cls: 'tickets2',
    });
    Tickets2.panel.Comments.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.panel.Comments, MODx.Panel);
Ext.reg('tickets2-panel-comments', Tickets2.panel.Comments);