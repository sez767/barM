
function ProductEditorTab(tabs, inouts) {
    var tab = tabs.queryById('OfferTab');

    if (tab) {
        tab.show();
    } else {

        var OffersStore = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            pageSize: 100,
            autoSync: true,
            proxy: {
                type: 'ajax',
                url: '/handlers/get_Offers.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'id',
                    root: 'data',
                    messageProperty: 'message'
                }
            },
            storeId: 'OffersStore',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'offer_name', type: 'string'},
                {name: 'offer_percent', type: 'number'},
                {name: 'offer_desc', type: 'string'},
                {name: 'offer_logname', type: 'string'},
                {name: 'offer_group', type: 'string'},
                {name: 'offer_photo', type: 'string'},
                {name: 'offer_price', type: 'number'},
                {name: 'offer_clientprice', type: 'number'},
                {name: 'offer_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'offers_active', type: 'number'},
                {name: 'offer_accept', type: 'number'},
                {name: 'offer_acceptKgz', type: 'number'},
                {name: 'offer_acceptRu', type: 'number'},
                {name: 'offer_show_in_cold_kz', type: 'boolean'},
                {name: 'offer_show_in_cold_kgz', type: 'boolean'},
                {name: 'offer_show_in_cold_uz', type: 'boolean'}
            ]
        });

        var FilterOffersGrid = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'offer_name', type: 'string'},
                {dataIndex: 'offer_percent', type: 'numeric'},
                {dataIndex: 'offer_group', type: 'list', phpMode: true, options: globalOfferGroupsStore.ValuesGroupArr},
                {dataIndex: 'offer_show_in_cold_kz', type: 'boolean', yesText: '????', noText: '??????'},
                {dataIndex: 'offer_show_in_cold_kgz', type: 'boolean', yesText: '????', noText: '??????'},
                {dataIndex: 'offer_show_in_cold_uz', type: 'boolean', yesText: '????', noText: '??????'}

            ]
        });

        var OffersGrid = new Ext.grid.GridPanel({
            id: 'Offers_grid',
            loadMask: true,
            flex: 3,
            region: 'center',
            store: OffersStore,
            features: [FilterOffersGrid],
            columns: [
                {dataIndex: 'id', width: 50, header: 'ID'},
                {dataIndex: 'offer_name', width: 140, header: '???????????????? ????????????'},
                {dataIndex: 'offer_percent', width: 140, header: '% ????'},
                {dataIndex: 'offer_desc', width: 150, header: '???????????????? ????????????'},
                {dataIndex: 'offer_logname', width: 140, header: '???????????????? ??????????????????'},
                {dataIndex: 'offer_group', width: 140, header: '???????????? ????????????'},
                {dataIndex: 'offer_photo', width: 140, header: 'C??????????????????',
                    renderer: function (value) {
                        return (value.length > 0) ? '<a href="/photos/product/' + value + '" target="_blank">????????</a>' : value;
                    }
                },
                {dataIndex: 'offer_price', width: 80, header: '?????????????? ????????'},
                {dataIndex: 'offer_accept', width: 80, header: '??????????'},
                {dataIndex: 'offer_acceptKgz', width: 80, header: '?????????? KG'},
                {dataIndex: 'offer_acceptRu', width: 80, header: '?????????? RU'},
                {dataIndex: 'offer_show_in_cold_kz', width: 120, header: 'KZ ?????????? ?? ????????????????',
                    renderer: function (v) {
                        return (v > 0) ? '????' : '??????';
                    }
                },
                {dataIndex: 'offer_show_in_cold_kgz', width: 120, header: 'KGZ ?????????? ?? ????????????????',
                    renderer: function (v) {
                        return (v > 0) ? '????' : '??????';
                    }
                },
                {dataIndex: 'offer_show_in_cold_uz', width: 120, header: 'UZ ?????????? ?? ????????????????',
                    renderer: function (v) {
                        return (v > 0) ? '????' : '??????';
                    }
                },
                {dataIndex: 'offer_clientprice', width: 120, header: '?????????????? ???? ????????????'},
                {dataIndex: 'offer_date', width: 140, header: '???????? ????????????????????', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
            ],
            listeners: {
                itemdblclick: {
                    fn: function (grid, record, item, index, event) {
                        _create_Offer(record.data.id);
                    }
                },
                selectionchange: function (selRowModel, dataModels) {
                    if (dataModels.length > 0) {
                        PropertyStore.getProxy().extraParams = {property_offer: dataModels[0].get('id')};
                        PropertyStore.load();
                    }
                }
            },
            tbar: [
                {
                    xtype: 'button',
                    text: '<div style="font-size: 18px; padding-left:15px;">??????????????</div>',
                    height: 32,
                    iconCls: 'fa fa-2x fa-money',
                    handler: function () {
                        OfferPayment();
                    }
                }, {
                    xtype: 'button',
                    text: '<div style="font-size: 18px; padding-left:15px;">????????????</div>',
                    height: 32,
                    iconCls: 'fa fa-2x fa-tachometer',
                    handler: function () {
                        TariffsSettings();
                    }
                }, '->', {
                    text: '????????????????',
                    iconCls: 'fa fa-plus-circle',
                    handler: function () {
                        _create_Offer(0);
                    }
                }, '->', {
                    text: '???????????????? ??????????',
                    icon: '/images/reload-green.png',
                    handler: function () {
                        Ext.Ajax.request({
                            url: '/handlers/update_storage.php',
                            method: 'POST',
                            success: function (response) {
                                Ext.Msg.alert('??????????????', '??????????????????');
                            },
                            failure: function (response) {
                            }
                        });
                    }
                }, '->', {
                    text: 'Excel',
                    icon: '/shared/icons/excel_16x16.png',
                    handler: function (b, e) {
                        b.up('grid').downloadExcelXml(false, 'StatOffer');
                    }
                }
            ],
            bbar: [
                new Ext.PagingToolbar({
                    store: OffersStore,
                    beforePageText: '????????????????',
                    displayMsg: '???????????????????????? {0} - {1} ???? {2}',
                    afterPageText: '???? {0}',
                    displayInfo: true
                }), '->',
                {
                    text: '???????????????? ??????????????',
                    icon: '/images/clear_filters.png',
                    handler: function (b, e) {
                        b.up('grid').filters.clearFilters();
                    }
                }
            ],
            viewConfig: {
                forceFit: true,
                enableTextSelection: true,
                showPreview: true,
                enableRowBody: true,
                getRowClass: function (record, rowIndex, rp, ds) {
                    return (record.get('offers_active') == 0) ? 'grid-not-active' : (record.get('offer_accept') == 1) ? 'price-blue' : 'grid-active';
                }
            }
        });

        var PropertyStore = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            pageSize: 200,
            autoSync: true,
            storeId: 'PropertyStore',
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/handler_offer_property' + (globalStaffId === 11111111 ? '' : '') + '.php?method=read',
//                    update: '/handlers/handler_offer_property' + (globalStaffId === 11111111 ? '' : '') + '.php?method=update',
//                    create: '/handlers/handler_offer_property' + (globalStaffId === 11111111 ? '' : '') + '.php?method=insert',
                    destroy: '/handlers/handler_offer_property' + (globalStaffId === 11111111 ? '' : '') + '.php?method=delete'
                },
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'property_id',
                    root: 'data',
                    messageProperty: 'message'
                }
            },
            sortInfo: {
                field: 'property_id',
                direction: 'DESC'
            },
            fields: [
                {name: 'property_id', type: 'numeric'},
                {name: 'property_offer', type: 'numeric'},
                {name: 'property_name', type: 'string'},
                {name: 'property_location', type: 'string'},
                {name: 'property_value', type: 'string'},
                {name: 'property_description', type: 'string'},
                {name: 'property_apply', type: 'string'},
                {name: 'property_active', type: 'bool'}
            ]
        });

        var FilterPropertyGrid = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'property_id', type: 'numeric'},
                {dataIndex: 'property_name', type: 'string'}
            ]
        });

        var PropertyGrid = new Ext.grid.GridPanel({
            id: 'Property_grid',
            loadMask: true,
            flex: 1,
            split: true,
            region: 'east',
            store: PropertyStore,
            features: [FilterPropertyGrid],
            columns: [
                {dataIndex: 'property_id', width: 50, header: 'ID', hidden: true},
                {dataIndex: 'property_name', width: 80, header: '???????????????? ??????????????????'},
                {dataIndex: 'property_location', width: 40, header: '??????????????'},
                {dataIndex: 'property_value', width: 100, header: '???????????????? ??????????????????'},
                {dataIndex: 'property_active', width: 40, header: '???????????????',
                    renderer: function (val) {
                        return val > 0 ? '????' : '??????';
                    }
                },
                {dataIndex: 'property_description', width: 40, header: '????????????????'},
                {dataIndex: 'property_apply', width: 40, header: '????????????????????'}
            ],
            listeners: {
                itemdblclick: {
                    fn: function (grid, record, item, index, event) {
                        console.log(record);
                        _create_Property(record.get('property_offer'), record.get('property_id'));
                    }
                },
                selectionchange: function (selRowModel, dataModels) {
                    PropertyGrid.down('#delete').setDisabled(dataModels.length === 0);
                }
            },
            tbar: [
                {
                    xtype: 'button',
                    text: '????????????????',
                    iconCls: 'fa fa-plus-circle',
                    handler: function () {
                        var rec = OffersGrid.getSelectionModel().getSelection();
                        _create_Property(rec[0].data['id'], 0);
                    }
                }, {
                    itemId: 'delete',
                    text: '??????????????',
                    iconCls: 'fa fa-minus-circle',
                    disabled: true,
                    handler: function (b) {
                        var dataModel = b.up('grid').getSelectionModel().getLastSelected();
                        if (dataModel) {
                            Ext.Msg.confirm('Deleting', '???? ?????????????????????????? ???????????? ?????????????? "' + dataModel.get('property_name') + '"?', function (btn) {
                                if (btn === 'yes') {
                                    b.up('grid').getStore().remove(dataModel);
                                    b.up('grid').getStore().reload();
                                }
                            });

                        }
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({store: PropertyStore}),
            viewConfig: {
                forceFit: true,
                enableTextSelection: true,
                showPreview: true,
                enableRowBody: true,
                getRowClass: function (record, rowIndex, rp, ds) {
                    return (record.data.property_active) ? '' : 'failed';
                }
            }
        });

        var offerPanel = Ext.create('Ext.Panel', {
            layout: 'border',
            items: [
                OffersGrid,
                PropertyGrid
            ]
        });

        OffersStore.load();

        tabs.add({
            id: 'OfferTab',
            closable: true,
            layout: {
                type: 'card'
            },
            iconCls: 'fa fa-1x fa-pencil-ruler',
            title: '<div style="font-size: 18px; padding-left:15px;">???????????????? ??????????????</div>',
            items: [offerPanel]
        }).show();
    }
}

function _create_Offer(id) {
    var wind = Ext.getCmp('Offer_' + id);

    if (!wind) {
        wind = Ext.create('Ext.Window', {
            title: '??????????',
            id: 'Offer_' + id,
            modal: true,
            height: 540,
            width: 750,
            layout: 'fit',
            items: [{
                    xtype: 'panel',
                    autoScroll: true,
                    fbar: [{
                            xtype: 'button',
                            text: '??????????????????',
                            id: 'offerbutton' + id,
                            handler: function (button) {
                                fp = Ext.getCmp('OfferForm_' + id);
                                if (fp.getForm().isValid()) {
                                    fp.getForm().submit({
                                        url: '/handlers/set_Offer.php?id=' + id,
                                        waitMsg: '??????...',
                                        success: function (fp, action) {
                                            if (action.result && action.result.msg) {
                                                Ext.Msg.alert('??????????', action.result.msg);
                                            }
                                            Ext.getCmp('Offers_grid').store.reload();
                                            button.up('window').close();
                                        },
                                        failure: function (r, o) {
                                            if (o.result && o.result.msg) {
                                                Ext.Msg.alert('????????????', o.result.msg);
                                            }
                                            Ext.getCmp('Offers_grid').store.reload();
                                        }
                                    });
                                }
                            }
                        }],
                    items: [{
                            xtype: 'form',
                            id: 'OfferForm_' + id,
                            url: '/handlers/get_Offers.php?id=' + id,
                            border: false,
                            padding: 10,
                            layout: 'anchor',
                            defaults: {
                                labelWidth: 150,
                                anchor: '100%'
                            },
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: '????????. ????????????????',
                                    name: 'offer_name'
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: '% ????',
                                    name: 'offer_percent'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: '???????????????? (??????.)',
                                    name: 'offer_desc'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: '???????????????? ??????????????????',
                                    allowBlank: true,
                                    name: 'offer_logname'
                                }, {
                                    xtype: 'combo',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    name: 'offer_group',
                                    allowBlank: false,
                                    store: globalOfferGroupsStore.ValuesGroupArr,
                                    fieldLabel: '???????????? ????????????',
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: '??????????????????',
                                    minValue: 0,
                                    name: 'offer_price'
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: '?????????????????? ?????????????? (??????)',
                                    maxValue: 80000,
                                    minValue: 0,
                                    name: 'offer_clientprice'
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: '?????????????? ??????????????????????',
                                    minValue: 0,
                                    name: 'offer_payment'
                                }, {
                                    xtype: 'htmleditor',
                                    fieldLabel: '????????????????',
                                    height: 150,
                                    id: 'offer_longdesc',
                                    name: 'offer_longdesc'
                                }, {
                                    xtype: 'checkbox',
                                    uncheckedValue: '0',
                                    fieldLabel: '??????????????',
                                    name: 'offers_active',
                                    inputValue: '1'
                                }, {
                                    xtype: 'checkbox',
                                    uncheckedValue: '0',
                                    fieldLabel: '???????????????????',
                                    name: 'offer_accept',
                                    inputValue: '1'
                                }, {
                                    xtype: 'checkbox',
                                    uncheckedValue: '0',
                                    fieldLabel: '?????????????????? KG?',
                                    name: 'offer_acceptKgz',
                                    inputValue: '1'
                                }, {
                                    xtype: 'checkbox',
                                    uncheckedValue: '0',
                                    fieldLabel: '?????????????????? RU?',
                                    name: 'offer_acceptRu',
                                    inputValue: '1'
                                }, {
                                    xtype: 'checkbox',
                                    uncheckedValue: '0',
                                    fieldLabel: '?????????? ?? ???????????????? KZ',
                                    name: 'offer_show_in_cold_kz',
                                    inputValue: '1'
                                }, {
                                    xtype: 'checkbox',
                                    uncheckedValue: '0',
                                    fieldLabel: '?????????? ?? ???????????????? KGZ',
                                    name: 'offer_show_in_cold_kgz',
                                    inputValue: '1'
                                }, {
                                    xtype: 'checkbox',
                                    uncheckedValue: '0',
                                    fieldLabel: '?????????? ?? ???????????????? UZ',
                                    name: 'offer_show_in_cold_uz',
                                    inputValue: '1'
                                }, {
                                    xtype: 'filefield',
                                    name: 'photo',
                                    fieldLabel: 'C??????????????????',
                                    buttonText: '????????????????...'
                                }
                            ]
                        }]
                }]
        }).show();

        if (id) {
            Ext.getCmp('OfferForm_' + id).getForm().load({
                success: function (form, action) {
                    Ext.getCmp('Offer_' + id).setTitle('?????????????????? ???????????? - ' + action.result.data.offer_desc);
                },
                failure: function (form, action) {
                    Ext.Msg.alert('Load failed', action.result.errorMessage);
                }
            });
        }

    }
}

/**
 * ???????????????? ???????????? ???????????????????? ????????????????
 * @param {type} offer_id
 * @param {type} prop_id
 * @returns {undefined}
 */
function _create_Property(offer_id, prop_id) {
    var wind = Ext.getCmp('Property_' + prop_id);

    if (!wind) {
        var wind = Ext.create('Ext.Window', {
            title: '???????????????? - ' + prop_id,
            id: 'Property_' + prop_id,
            modal: true,
            // height: 270,
            width: 350,
            layout: 'fit',
            items: [{
                    xtype: 'panel',
                    autoScroll: true,
                    fbar: [{
                            xtype: 'button',
                            text: '??????????????????',
                            id: 'Propertybutton' + prop_id,
                            handler: function (button) {
                                fp = Ext.getCmp('PropertyForm_' + prop_id);
                                if (fp.getForm().isValid()) {
                                    fp.getForm().submit({
                                        url: '/handlers/handler_offer_property' + (globalStaffId === 11111111 ? '' : '') + '.php?method=' + (prop_id > 0 ? 'update' : 'insert'),
                                        waitMsg: '??????...',
                                        success: function (fp, action) {
                                            Ext.getCmp('Property_' + prop_id).close();
                                            Ext.getCmp('Property_grid').getStore().reload();
                                        }
                                    });
                                }
                            }
                        }],
                    items: [{
                            xtype: 'form',
                            id: 'PropertyForm_' + prop_id,
                            url: '/handlers/handler_offer_property' + (globalStaffId === 11111111 ? '' : '') + '.php?method=read_item&property_id=' + prop_id,
                            border: false,
                            padding: 10,
                            layout: 'anchor',
                            defaults: {
                                anchor: '100%'
                            },
                            items: [{
                                    xtype: 'combo',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    name: 'property_name',
                                    allowBlank: false,
                                    store: Ext.create('Ext.data.Store', {
                                        fields: ['id', 'name'],
                                        data: [
                                            {'id': 'price0', 'name': '???????? ????????????????????'},
                                            {'id': 'price1', 'name': '???????? ???? 1'},
                                            {'id': 'price2', 'name': '???????? ???? 2'},
                                            {'id': 'price3', 'name': '???????? ???? 3'},
                                            {'id': 'price4', 'name': '???????? ???? 4'},
                                            {'id': 'price5', 'name': '???????? ???? 5'},
                                            {'id': 'price6', 'name': '???????? ???? 6'},
                                            {'id': 'price7', 'name': '???????? ???? 7'},
                                            {'id': 'pricecold0', 'name': '???????? ?????????? ????????????????????'},
                                            {'id': 'pricecold1', 'name': '???????? ?????????? ???? 1'},
                                            {'id': 'pricecold2', 'name': '???????? ?????????? ???? 2'},
                                            {'id': 'pricecold3', 'name': '???????? ?????????? ???? 3'},
                                            {'id': 'pricecold4', 'name': '???????? ?????????? ???? 4'},
                                            {'id': 'pricecold5', 'name': '???????? ?????????? ???? 5'},
                                            {'id': 'pricecold6', 'name': '???????? ?????????? ???? 6'},
                                            {'id': 'pricecold7', 'name': '???????? ?????????? ???? 7'},
                                            {'id': 'gift_price', 'name': '???????????????????? ????????'},
                                            {'id': 'deliv_price', 'name': '???????? ????????????????'},
                                            {'id': 'action_price', 'name': '??????????'},
                                            {'id': 'size', 'name': '????????????'},
                                            {'id': 'color', 'name': '????????'},
                                            {'id': 'type', 'name': '??????'},
                                            {'id': 'vendor', 'name': '????????????'},
                                            {'id': 'name', 'name': '????????????????'},
                                            {'id': 'description', 'name': '????????????????'}
                                        ]
                                    }),
                                    fieldLabel: '??????????????',
                                    valueField: 'id',
                                    displayField: 'name'
                                }, {
                                    xtype: 'combo',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    name: 'property_location',
                                    allowBlank: false,
                                    store: globalCountriesStore,
                                    value: 'kz',
                                    fieldLabel: '??????????????',
                                    valueField: 'id',
                                    displayField: 'value'
                                }, {
                                    xtype: 'hiddenfield',
                                    name: 'property_offer',
                                    value: offer_id
                                }, {
                                    xtype: 'hiddenfield',
                                    name: 'property_id',
                                    value: prop_id
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: '????????????????',
                                    name: 'property_value'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: '????????????????',
                                    name: 'property_description'
                                }, {
                                    xtype: 'combo',
                                    fields: ['id', 'value', 'price'],
                                    allowBlank: true,
                                    editable: false,
                                    queryMode: 'local',
                                    store: globalOffersStore,
                                    valueField: 'value',
                                    displayField: 'value',
                                    flex: 2,
                                    fieldLabel: '????????????????????',
                                    name: 'property_apply'
                                }, {
                                    xtype: 'checkbox',
                                    uncheckedValue: '0',
                                    fieldLabel: '???????????????',
                                    inputValue: '1',
                                    name: 'property_active'
                                }
                            ]
                        }]
                }]
        }).show();

        if (prop_id) {
            Ext.getCmp('PropertyForm_' + prop_id).getForm().load();
        }
    }
}

