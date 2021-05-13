
globalIncomeCallsDealStatusesStore = new Ext.data.Store({
    storeId: 'globalIncomeCallsDealStatusesStore',
    ValuesArr: [],
    ValuesJson: {},
    fields: ['id', 'value'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');
            });
        }
    }
});
globalIncomeCallsDealStatusesStore.loadRawData([
    {id: '0', value: 'Не определен'},
    {id: '1', value: 'Не согласен'},
    {id: '2', value: 'Перезвонить'},
    {id: '3', value: 'Брак'},
    {id: '4', value: 'Черный список (просит никогда ему не звонить)'},
    {id: '5', value: 'Заказ оформлен'},
    {id: '6', value: 'Не доступен'},
    {id: '7', value: 'В обработке'},
    {id: '8', value: 'Нет доставки'},
    {id: '9', value: 'Отключён'},
    {id: '10', value: 'Не идёт звонок'},
    {id: '11', value: 'Не поднимает'}
]);
// Вкладка "Входящие звонки"
function IncomeCallsTab(tabs) {

    var tab = tabs.queryById('IncomeCallsTab');
    if (tab) {
        tab.show();
    } else {
// Хранилище "Входящие звонки"
        var IncomeCallsStore = new Ext.data.JsonStore({
            storeId: 'IncomeCallsStore',
            autoDestroy: true,
            remoteSort: true,
            autoSync: true,
            autoLoad: true,
            pageSize: 50,
            proxy: {
                type: 'ajax',
                simpleSortMode: true,
                api: {
                    read: '/handlers/handler_income_calls.php?method=read'
                },
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'id',
                    root: 'data',
                    messageProperty: 'message'
                },
                timeout: 120000,
                writer: {
                    type: 'json'
                },
                afterRequest: function (request, success) {
                    if (request.action === 'read') {
                        var excelExportBtn = Ext.getCmp('btnExcelIncomeCallsStore');
                        var grid = excelExportBtn ? excelExportBtn.up('grid') : (IncomeCallsGrid ? IncomeCallsGrid : Ext.getCmp('IncomeCalls_grid'));
                        if (excelExportBtn && !excelExportBtn.hidden) {
                            var href = '';
                            if (success) {
                                href = this.getUrl(request); // or this.url
                                var params = Ext.clone(request.params);
                                params.xlsx = 1;
                                params.cols = Ext.JSON.encode(excelExportBtn.up('grid').getExportColums(false));
                                href += '&' + Ext.Object.toQueryString(params);
                            }
                            excelExportBtn.btnEl.dom.href = href;
                            excelExportBtn.setDisabled(!href);
                        }

                        if (grid) {
                            grid.commonIdsDataArr = [];
                            grid.commonIdsDataStr = '';
                            grid.getStore().each(function (rec) {
                                grid.commonIdsDataArr.push(rec.get('id'));
                                grid.commonIdsDataStr += rec.get('id') + ',';
                            });
                            grid.commonIdsDataJson = Ext.JSON.encode(grid.commonIdsDataArr);
                            var DocumMenuButton = grid.down('#DocumIncomeCallsButton');
                            if (DocumMenuButton && !DocumMenuButton.hidden) {
                                DocumMenuButton.setDisabled(grid.commonIdsDataArr.length === 0);
                            }
                        }
                    }
                }
            },
            sorters: [{property: 'created_at', direction: 'DESC'}],
            listeners: {
                beforeload: function (store, operation) {
                    var grid = IncomeCallsGrid ? IncomeCallsGrid : Ext.getCmp('IncomeCalls_grid');
                    if (grid) {
                        grid.commonIdsDataArr = [];
                        grid.commonIdsDataStr = '';
                        grid.commonIdsDataJson = Ext.JSON.encode(grid.commonIdsDataArr);
                        var DocumMenuButton = grid.down('#DocumIncomeCallsButton');
                        if (DocumMenuButton) {
                            DocumMenuButton.setDisabled(grid.commonIdsDataArr.length === 0);
                        }
                    }
                }
            },
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'order_id', type: 'numeric'},
                {name: 'fio', type: 'string'},
                {name: 'phone', type: 'string'},
                {name: 'queue', type: 'string'},
                {name: 'sip_id', type: 'string'},
                {name: 'status_call', type: 'string'},
                {name: 'call_id', type: 'string'},
                {name: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'status_talk', type: 'string'},
                {name: 'who_set', type: 'numeric'}
            ]
        });
        // Фильтра "Входящие звонки"
        var FilterIncomeCalls = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'string'},
                {dataIndex: 'order_id', type: 'numeric'},
                {dataIndex: 'fio', type: 'string'},
                {dataIndex: 'phone', type: 'string'},
                {dataIndex: 'queue', type: 'string'},
                {dataIndex: 'sip_id', type: 'string'},
                {dataIndex: 'status_call', type: 'list', phpMode: true, options: [['ANSWERED', 'ANSWERED'], ['NO ANSWER', 'NO ANSWER'], ['BUSY', 'BUSY'], ['FAILED', 'FAILED']]},
                {dataIndex: 'call_id', type: 'string'},
                {dataIndex: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'status_talk', type: 'string'},
                {dataIndex: 'who_set', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr}
            ]
        });

        var IncomeCallsGrid = new Ext.grid.GridPanel({
            id: 'IncomeCallsGridId',
            loadMask: true,
            forceFit: false,
            stateful: true,
            stateId: 'IncomeCallsGridStateId',
            store: IncomeCallsStore,
            features: [
                FilterIncomeCalls,
                {ftype: 'summary', dock: 'top'}
            ],
            selType: 'rowmodel',
            viewConfig: {
                enableTextSelection: true,
                showPreview: true,
                enableRowBody: true
            },
            columns: [
                {dataIndex: 'id', width: 80, header: 'ID'},
                {dataIndex: 'order_id', width: 80, header: 'ID заказа'},
                {dataIndex: 'fio', width: 80, header: 'ФИО'},
                {dataIndex: 'phone', width: 80, header: 'Phone'},
                {dataIndex: 'queue', width: 80, header: 'queue'},
                {dataIndex: 'sip_id', width: 80, header: 'SIP'},
                {dataIndex: 'status_call', width: 80, header: 'status_call'},
                {dataIndex: 'call_id', width: 80, header: 'call_id'},
                {dataIndex: 'date', width: 150, header: 'Дата', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'status_talk', width: 80, header: 'status_talk'},
                {dataIndex: 'who_set', width: 150, header: 'Менеджер клиента', sortable: true, hidden: false,
                    renderer: function (v) {
                        return globalManagerStore.ValuesJson[v] ? globalManagerStore.ValuesJson[v] : v;
                    }
                }
            ],
            listeners: {
                itemdblclick: function (grid, record, item, index, event) {
                    CreateMenuIncomeCalls(record.data.id, 'kz');
                }
            },
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                    ]
                }, {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        new Ext.PagingToolbar({
                            store: IncomeCallsStore,
                            beforePageText: 'Страница',
                            displayMsg: 'Отображается {0} - {1} из {2}',
                            afterPageText: 'из {0}',
                            displayInfo: true,
                            plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [10, 20, 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000, 2500]})]
                        }), '->', {
                            text: 'Очистить фильтры',
                            icon: '/images/clear_filters.png',
                            handler: function (b, e) {
                                b.up('grid').filters.clearFilters();
                            }
                        }
                    ]
                }
            ]
        });
        tabs.add({
            id: 'IncomeCallsTab',
            iconCls: 'fa fa-1x fa-phone-alt',
            layout: {type: 'card'},
            title: '<div style="font-size: 18px; padding-left:15px;">Входящие звонки</div>',
            items: [IncomeCallsGrid],
            closable: true
        }).show();
    }
}

function CreateMenuIncomeCalls(id) {
    console.log('Анкета "Входящие звонки": id=' + id);
    var wind = Ext.getCmp('incomecallsWindow_' + id);
    if (wind) {
        wind.show();
    } else {
        wind = Ext.create('Ext.Window', {
            title: 'ID звоночка - ' + id,
            id: 'incomecallsWindow_' + id,
            modal: false,
            closable: true,
            height: 550,
            width: 850,
            layout: 'border',
            items: [
                {
                    xtype: 'form',
                    id: 'IncomeCallsForm_' + id,
                    region: 'center',
                    autoScroll: true,
                    url: '/handlers/handler_income_calls.php?method=readItem&id=' + id,
                    border: false,
                    padding: 5,
                    defaults: {
                        anchor: '100%',
                        bodyPadding: 2,
                        columnWidth: 0.5
                    },
                    layout: 'column',
                    items: []
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                        {
                            xtype: 'button',
                            text: 'Позвонить',
                            id: 'call_me' + id,
                            icon: '/images/phone_circle_blue_24x24.png',
                            href: 'javascript:void(0);',
                            scale: 'medium',
                            hrefTarget: '_self'
                        }, {
                            xtype: 'button',
                            text: 'Внести заказ',
                            scale: 'medium',
                            hidden: false,
                            icon: '/images/plus-circle-green_24x24.png',
                            handler: function (button) {
                                return false;
                                if (Ext.getCmp('have_orders' + id).getValue() > 0) {
                                    Ext.Msg.alert('Внимание!', 'Запрет на оформление Заказа!!!<br/>ЕСТЬ ДУБЛЬ!!!');
                                } else {
                                    var params = {
                                        id: id,
                                        staff_id: 22222222
                                    };
                                    createNewOrder(id, params);
                                    button.up('window').close();
                                }
                            }
                        }
                    ]
                }, {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        '->',
                        {
                            xtype: 'button',
                            scale: 'medium',
                            icon: '/images/floppy_24x24.png',
                            text: 'Сохранить',
                            handler: function (button) {
                                var fp = button.up('panel').child('form');
                                if (fp.getForm().isValid()) {
                                    console.log(fp.getForm().getValues());
                                    fp.getForm().submit({
                                        url: '/handlers/set_menu_incomecalls.php?&id=' + id,
                                        waitMsg: 'Жди...',
                                        success: function (fp, action) {
                                            button.up('window').close();
                                            var grid = Ext.getCmp('IncomeCallsGridId');
                                            if (grid) {
                                                grid.getStore().reload();
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    ]
                }
            ]
        }).show();
        Ext.getCmp('IncomeCallsForm_' + id).getForm().load({
            success: function (form, action) {
            }
        });
    }

}

