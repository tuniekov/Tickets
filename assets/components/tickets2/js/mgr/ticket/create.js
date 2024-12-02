Tickets2.page.CreateTicket = function (config) {
    config = config || {record: {}};
    config.record = config.record || {};
    Ext.applyIf(config, {
        panelXType: 'modx-panel-ticket',
    });
    config.canDuplicate = false;
    config.canDelete = false;
    Tickets2.page.CreateTicket.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2.page.CreateTicket, MODx.page.CreateResource, {

    getButtons: function (config) {
        var buttons = [];
        var originals = MODx.page.CreateResource.prototype.getButtons.call(this, config);
        for (var i in originals) {
            if (!originals.hasOwnProperty(i)) {
                continue;
            }
            var button = originals[i];
            switch (button.id) {
                case 'modx-abtn-help':
                    button.text = '<i class="icon icon-question-circle"></i>';
                    if (MODx.config.manager_language == 'ru')
                        MODx.helpUrl = 'https://docs.modx.pro/komponentyi/tickets2/interfejs/sozdanie-tiketa';
                    else
                        MODx.helpUrl = 'https://docs.modx.pro/en/components/tickets2/interface/create-ticket';
                    break;
            }
            buttons.push(button)
        }

        return buttons;
    }
});
Ext.reg('tickets2-page-ticket-create', Tickets2.page.CreateTicket);