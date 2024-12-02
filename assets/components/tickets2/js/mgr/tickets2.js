var Tickets2 = function (config) {
    config = config || {};
    Tickets2.superclass.constructor.call(this, config);
};
Ext.extend(Tickets2, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('tickets2', Tickets2);

Tickets2 = new Tickets2();