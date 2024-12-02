Tickets2.grid.Threads = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        url: Tickets2.config.connector_url,
        baseParams: {
            action: 'Tickets2\\Processors\\Mgr\\Thread\\GetList',
        },
        fields: this.getFields(),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        listeners: this.getListeners(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        autoHeight: true,
        paging: true,
        remoteSort: true,
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            showPreview: true,
            getRowClass: function (rec) {
                var cls = [];
                if (rec.data.closed) {
                    cls.push('tickets2-row-unpublished');
                }
                if (rec.data.deleted) {
                    cls.push('tickets2-row-deleted');
                }
                return cls.join(' ');
            },
        },
        stateful: true,
        stateId: 'tickets2-threads-state',
    });
    Tickets2.grid.Threads.superclass.constructor.call(this, config);
    this.getStore().sortInfo = {
        field: 'createdon',
        direction: 'DESC'
    };
};
Ext.extend(Tickets2.grid.Threads, MODx.grid.Grid, {

    getFields: function () {
        return [
            'id', 'resource', 'name',
            'createdon', 'createdby', 'deletedon', 'deletedby',
            'closed', 'deleted',
            'pagetitle', 'comments', 'url', 'actions'
        ];
    },

    getColumns: function () {
        return [{
            header: _('id'),
            dataIndex: 'id',
            width: 35,
            sortable: true,
        }, {
            header: _('ticket_thread_name'),
            dataIndex: 'name',
            width: 100,
            sortable: true,
        }, {
            header: _('ticket_thread_createdon'),
            dataIndex: 'createdon',
            width: 75,
            sortable: true,
            renderer: Tickets2.utils.formatDate,
        }, {
            header: _('ticket_thread_comments'),
            dataIndex: 'comments',
            width: 75,
            sortable: true,
        }, {
            header: _('ticket'),
            dataIndex: 'pagetitle',
            width: 75,
            renderer: function (value, metaData, record) {
                return Tickets2.utils.ticketLink(value, record['data']['resource'], true)
            },
            sortable: true,
        }, {
            header: _('ticket_actions'),
            dataIndex: 'actions',
            renderer: Tickets2.utils.renderActions,
            sortable: false,
            width: 75,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return ['->', {
            xtype: 'tickets2-field-search',
            width: 250,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                },
                clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
        }];
    },

    getListeners: function () {
        return {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.viewThread(grid, e, row);
            }
        };
    },

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = Tickets2.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof(row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                }
                else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        }
        return this.processEvent('click', e);
    },

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        this.getBottomToolbar().changePage(1);
    },

    threadAction: function (method) {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: Tickets2.config.connector_url,
            params: {
                action: 'Tickets2\\Processors\\Mgr\\Thread\\Multiple',
                method: method,
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        //noinspection JSUnresolvedFunction
                        this.refresh();
                    }, scope: this
                },
                failure: {
                    fn: function (response) {
                        MODx.msg.alert(_('error'), response.message);
                    }, scope: this
                },
            }
        })
    },

    deleteThread: function () {
        this.threadAction('delete');
    },

    undeleteThread: function () {
        this.threadAction('undelete');
    },

    closeThread: function () {
        this.threadAction('close');
    },

    openThread: function () {
        this.threadAction('open');
    },

    removeThread: function () {
        Ext.MessageBox.confirm(
            _('ticket_thread_remove'),
            _('ticket_thread_remove_confirm'),
            function (val) {
                if (val == 'yes') {
                    this.threadAction('remove');
                }
            },
            this
        );
    },

    viewThread: function (btn, e, row) {
        var record = typeof(row) != 'undefined'
            ? row.data
            : this.menu.record;

        var w = MODx.load({
            xtype: 'tickets2-window-thread',
            title: record.name,
            threads: record.id,
        });
        w.show(e.target);
    },

    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();

        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }

        return ids;
    },

});
Ext.reg('tickets2-grid-threads', Tickets2.grid.Threads);