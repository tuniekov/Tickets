Tickets2.grid.Authors = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        url: Tickets2.config.connector_url,
        baseParams: {
            action: 'Tickets2\\Processors\\Mgr\\Author\\GetList',
        },
        fields: this.getFields(),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        listeners: this.getListeners(config),
        autoHeight: true,
        paging: true,
        remoteSort: true,
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            showPreview: true,
            getRowClass: function (rec) {
                var cls = [];
                if (rec.data.active != 1) {
                    cls.push('tickets2-row-unpublished');
                }
                if (rec.data.blocked == 1) {
                    cls.push('tickets2-row-deleted');
                }
                return cls.join(' ');
            },
        },
        stateful: true,
        stateId: 'tickets2-authors-state',
    });
    Tickets2.grid.Authors.superclass.constructor.call(this, config);
    this.getStore().sortInfo = {
        field: 'rating',
        direction: 'DESC'
    };
};
Ext.extend(Tickets2.grid.Authors, MODx.grid.Grid, {

    getFields: function () {
        return [
            'id', 'fullname', 'createdon', 'visitedon', 'active', 'blocked',
            'rating', 'tickets2', 'comments', 'views', 'stars',
            'votes_tickets2', 'votes_comments', 'stars_tickets2', 'stars_comments',
            'votes_tickets2_up', 'votes_tickets2_down', 'votes_comments_up', 'votes_comments_down'
        ];
    },

    getColumns: function () {
        var columns = [{
            header: _('id'),
            dataIndex: 'id',
            width: 35,
            sortable: true,
        }, {
            header: _('ticket_author'),
            dataIndex: 'fullname',
            width: 100,
            sortable: true,
            renderer: function (value, metaData, record) {
                return Tickets2.utils.userLink(value, record['data']['id'])
            },
        }, {
            header: _('ticket_author_createdon'),
            dataIndex: 'createdon',
            width: 75,
            sortable: true,
            renderer: Tickets2.utils.formatDate,
        }, {
            header: _('ticket_author_visitedon'),
            dataIndex: 'visitedon',
            width: 75,
            sortable: true,
            renderer: Tickets2.utils.formatDate,
        }];

        var add = {
            rating: {
                header: '<i class="icon icon-star-half-o">',
            },
            tickets2: {
                header: '<i class="icon icon-files-o">',
            },
            comments: {
                header: '<i class="icon icon-comments-o">',
            },
            views: {
                header: '<i class="icon icon-eye">',
            },
            votes_tickets2: {
                header: '<i class="icon icon-thumbs-up"> ' +
                '<i class="icon icon-file-o">',
                renderer: {
                    fn: function (value, metaData, record) {
                        return this._renderRating(value, record, 'tickets2')
                    }, scope: this
                }
            },
            votes_comments: {
                header: '<i class="icon icon-thumbs-up"> ' +
                '<i class="icon icon-comment-o">',
                renderer: {
                    fn: function (value, metaData, record) {
                        return this._renderRating(value, record, 'comments')
                    }, scope: this
                }
            },
            stars: {
                header: '<i class="icon icon-star">',
                renderer: {
                    fn: function (value, metaData, record) {
                        return this._renderStars(record)
                    }, scope: this
                },
            },
            stars_tickets2: {
                header: '<i class="icon icon-star"> \
                    <i class="icon icon-file-o">',
                hidden: true
            },
            stars_comments: {
                header: '<i class="icon icon-star"> \
                    <i class="icon icon-comment-o">',
                hidden: true
            },
            votes_tickets2_up: {
                header: '<i class="icon icon-thumbs-up"> \
                    <i class="icon icon-file-o"> \
                    <i class="icon icon-arrow-up">',
                hidden: true
            },
            votes_tickets2_down: {
                header: '<i class="icon icon-thumbs-up"> \
                <i class="icon icon-file-o"> \
                <i class="icon icon-arrow-down">',
                hidden: true,
            },
            votes_comments_up: {
                header: '<i class="icon icon-thumbs-up"> \
                <i class="icon icon-comment-o"> \
                <i class="icon icon-arrow-up">',
                hidden: true,
            },
            votes_comments_down: {
                header: '<i class="icon icon-thumbs-up"> \
                <i class="icon icon-comment-o"> \
                <i class="icon icon-arrow-down">',
                hidden: true,
            },
        };
        for (var i in add) {
            if (!add.hasOwnProperty(i)) {
                continue;
            }
            columns.push(Ext.apply({
                    header: _('ticket_author_' + i),
                    tooltip: _('ticket_author_' + i),
                    dataIndex: i,
                    width: 35,
                    sortable: true,
                },
                add[i]
            ));
        }

        return columns;
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-refresh"></i> ' + _('ticket_authors_rebuild'),
            handler: this.rebuildRating,
            scope: this,
        }, '->', {
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
        return {};
    },

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = Tickets2.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        this.getBottomToolbar().changePage(1);
    },

    rebuildRating: function () {
        Ext.MessageBox.confirm(
            _('ticket_authors_rebuild'),
            _('ticket_authors_rebuild_confirm'),
            function (val) {
                if (val == 'yes') {
                    this._rebuildRating(0);
                }
            },
            this
        );
    },

    _rebuildRating: function (start) {
        if (!this._wait) {
            this._wait = Ext.MessageBox.wait(
                _('ticket_authors_rebuild_wait'),
                _('please_wait')
            );
        }
        MODx.Ajax.request({
            url: Tickets2.config.connector_url,
            params: {
                action: 'mgr/author/rebuild',
                start: start || 0,
            },
            listeners: {
                success: {
                    fn: function (response) {
                        if (response.object['total'] == response.object['processed']) {
                            this._wait.hide();
                            this._wait = null;
                            //noinspection JSUnresolvedFunction
                            this.refresh();
                        }
                        else {
                            this._wait.updateText(
                                _('ticket_authors_rebuild_wait_ext')
                                    .replace('[[+processed]]', response.object['processed'])
                                    .replace('[[+total]]', response.object['total'])
                            );
                            //noinspection JSUnresolvedFunction
                            this._rebuildRating(response.object['processed']);
                        }
                    }, scope: this
                }
            }
        });
    },

    _renderRating: function (value, record, type) {
        var up = record.data['votes_' + type + '_up'];
        var down = record.data['votes_' + type + '_down'];

        return value || up || down
            ? value + '<div><small title="' + _('ticket_author_rating_desc') + '">' +
        up + ' / ' + down + '</small></div>'
            : '-';
    },

    _renderStars: function (record) {
        var tickets2 = record.data['stars_tickets2'];
        var comments = record.data['stars_comments'];

        return tickets2 || comments
            ? '<div title="' + _('ticket_author_stars_desc') + '">' +
        tickets2 + ' / ' + comments + '</div>'
            : '-';
    },

    _wait: null,

});
Ext.reg('tickets2-grid-authors', Tickets2.grid.Authors);