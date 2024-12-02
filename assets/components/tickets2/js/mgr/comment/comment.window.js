Tickets2.window.CreateComment = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('ticket_comment_create'),
        url: Tickets2.config.connector_url,
        action: 'Tickets2\\Processors\\Mgr\\Comment\\Create',
        fields: this.getFields(config),
        keys: this.getKeys(config),
        width: 700,
        height: 550,
        layout: 'anchor',
        autoHeight: false,
        cls: 'tickets2-window tickets2',
    });
    Tickets2.window.CreateComment.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.window.CreateComment, MODx.Window, {

    getKeys: function () {
        return [{
            key: Ext.EventObject.ENTER,
            shift: true,
            fn: this.submit,
            scope: this
        }];
    },

    getFields: function (config) {
        var is_reply = config.record.reply_to != undefined;
        return [{
            xtype: 'hidden',
            name: 'thread',
        }, {
            xtype: 'hidden',
            name: 'parent',
        }, {
            xtype: 'textarea',
            fieldLabel: _('comment'),
            name: 'text',
            anchor: is_reply
                ? '99% -140'
                : '99% -0',
        }, {
            xtype: is_reply
                ? 'textarea'
                : 'hidden',
            fieldLabel: _('ticket_comment_reply_to'),
            name: 'reply_to',
            height: 100,
            disabled: true,
            cls: 'reply_to',
            anchor: '99%'
        }];
    },
});
Ext.reg('tickets2-window-comment-create', Tickets2.window.CreateComment);


Tickets2.window.UpdateComment = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('ticket_comment_update'),
        action: 'Tickets2\\Processors\\Mgr\\Comment\\Update',
    });
    Tickets2.window.UpdateComment.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.window.UpdateComment, Tickets2.window.CreateComment, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
        }, {
            xtype: 'textarea',
            fieldLabel: _('comment'),
            name: 'text',
            anchor: '99% -210'
        }, {
            items: [{
                layout: 'form',
                cls: 'modx-panel',
                items: [{
                    layout: 'column',
                    border: false,
                    items: [{
                        columnWidth: .5,
                        border: false,
                        layout: 'form',
                        items: this.getLeftFields(config),
                    }, {
                        columnWidth: .5,
                        border: false,
                        layout: 'form',
                        cls: 'right-column',
                        items: this.getRightFields(config),
                    }]
                }]
            }]
        }];
    },

    getLeftFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('ticket_comment_name'),
            name: 'name',
            anchor: '99%',
            disabled: config.record.createdby != 0
        }, {
            xtype: 'numberfield',
            fieldLabel: _('ticket_comment_parent'),
            name: 'parent',
            anchor: '75%'
        }, {
            xtype: 'tickets2-combo-thread',
            fieldLabel: _('ticket_thread'),
            name: 'thread',
            anchor: '75%'
        }];
    },

    getRightFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('ticket_comment_email'),
            name: 'email',
            anchor: '99%',
            disabled: config.record.createdby != 0
        }, {
            layout: 'column',
            border: false,
            items: [{
                columnWidth: .5,
                border: false,
                layout: 'form',
                items: [{
                    xtype: 'displayfield',
                    fieldLabel: _('ticket_comment_createdon'),
                    name: 'createdon',
                    anchor: '99%',
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'IP',
                    name: 'ip',
                    anchor: '99%',
                }]
            }, {
                columnWidth: .5,
                border: false,
                layout: 'form',
                cls: 'right-column',
                items: [{
                    xtype: 'displayfield',
                    fieldLabel: _('ticket_comment_editedon'),
                    name: 'editedon',
                    anchor: '99%',
                }, {
                    xtype: 'displayfield',
                    fieldLabel: _('ticket_comment_deletedon'),
                    name: 'deletedon',
                    anchor: '99%',
                }]
            }]
        }];
    }

});
Ext.reg('tickets2-window-comment-update', Tickets2.window.UpdateComment);