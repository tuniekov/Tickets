Tickets2.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'tickets2-panel-home',
            renderTo: 'tickets2-panel-home-div',
            baseCls: 'tickets2-formpanel',
        }]
    });
    Tickets2.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.page.Home, MODx.Component);
Ext.reg('tickets2-page-home', Tickets2.page.Home);


Tickets2.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        border: false,
        items: [{
            html: '<h2>' + _('tickets2') + '</h2>',
            border: false,
            cls: 'modx-page-header container',
        }, {
            xtype: 'modx-tabs',
            id: 'tickets2-home-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            stateful: true,
            stateId: 'tickets2-home-panel',
            stateEvents: ['tabchange'],
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            },
            hideMode: 'offsets',
            items: [{
                title: _('comments'),
                layout: 'anchor',
                items: [{
                    html: _('ticket_comments_intro'),
                    border: false,
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'tickets2-panel-comments',
                    preventRender: true,
                }]
            }, {
                title: _('threads'),
                layout: 'anchor',
                items: [{
                    html: _('ticket_threads_intro'),
                    border: false,
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'tickets2-panel-threads',
                    preventRender: true,
                }]
            }, {
                title: _('tickets2'),
                layout: 'anchor',
                items: [{
                    html: _('ticket_tickets2_intro'),
                    border: false,
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'tickets2-panel-tickets2',
                    preventRender: true,
                }]
            }, {
                title: _('authors'),
                layout: 'anchor',
                items: [{
                    html: _('ticket_authors_intro'),
                    border: false,
                    bodyCssClass: 'panel-desc',
                }, {
                    xtype: 'tickets2-panel-authors',
                    preventRender: true,
                }]
            }]
        }]
    });
    Tickets2.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.panel.Home, MODx.Panel);
Ext.reg('tickets2-panel-home', Tickets2.panel.Home);