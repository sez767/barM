var Ext;
// Вкладка "Звонки"
function CallsTab(tabs) {


    globalQueueStore.reload();
    var tab = tabs.queryById('CallsTab');
    if (tab) {
        tab.show();
    } else {

        // Хранилище "Звонки"
        var CallsStore = new Ext.data.JsonStore({
            storeId: 'CallsStores',
            autoDestroy: true,
            autoLoad: true,
            pageSize: 50,
            remoteSort: false,
            remoteFilter: false,
            proxy: {
                type: 'ajax',
                url: '/handlers/get_calldata_v2' + (globalStaffId === 11111111 ? '' : '') + '.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    root: 'data',
                    idProperty: 'id',
                    totalProperty: 'total'
                }
            },
            sortInfo: {
                field: 'calldate',
                direction: 'DESC'
            },
            fields: [
                {name: 'checked', type: 'boolean'},
                {name: 'calldate', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'uniqueid', type: 'string'},
                {name: 'country', type: 'string'},
                {name: 'src', type: 'string'},
                {name: 'dst', type: 'string'},
                {name: 'dstchannel', type: 'string'},
                {name: 'channel', type: 'string'},
                {name: 'duration', type: 'number'},
                {name: 'billsec', type: 'number'},
                {name: 'disposition', type: 'string'},
                {name: 'userfield', type: 'string'},
                {name: 'object_id', type: 'string'},
                {name: 'data_int', type: 'string'},
                {name: 'created_by', type: 'string'},
                {name: 'created_at', type: 'string'}
            ]
        });
        // Фильтра "Звонки"
        var CallsFilter = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: true,
            filters: [
                {dataIndex: 'calldate', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'src', type: 'string'},
                {dataIndex: 'uniqueid', type: 'string'},
                {dataIndex: 'duration', type: 'numeric'},
                {dataIndex: 'billsec', type: 'numeric'},
                {dataIndex: 'dst', type: 'string'},
                {dataIndex: 'dstchannel', type: 'string'},
                {dataIndex: 'channel', type: 'string'},
                {dataIndex: 'data_int', type: 'list', phpMode: true, options: globalColdStatusStore.ValuesArr},
                {dataIndex: 'object_id', type: 'numeric'},
//                {dataIndex: 'created_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr},
                {dataIndex: 'disposition', type: 'list', phpMode: true, options: [['ANSWERED', 'ANSWERED'], ['NO ANSWER', 'NO ANSWER'], ['BUSY', 'BUSY'], ['FAILED', 'FAILED']]}
            ]
        });
        // Таб "Звонки"
        var CallsGrid = new Ext.grid.GridPanel({
            frame: true,
            autoScroll: false,
            region: 'center',
            features: [CallsFilter],
            height: 580,
            id: 'CallsGrid',
            store: CallsStore,
            columns: [
                {
                    xtype: 'checkcolumn',
                    text: 'Проверен?',
                    dataIndex: 'checked',
                    width: 60,
                    listeners: {
                        checkchange: function (el, rowIndex, checked, eOpts) {
                            CallsGrid.setLoading(true);
                            var store = CallsGrid.getStore();
                            var record = store.getAt(rowIndex);
                            store.commitChanges();
                            var action = (checked ? "select" : "deselect");
                            Ext.Ajax.request({
                                url: '/handlers/set_calldata.php',
                                method: 'POST',
                                params: {
                                    id: record.get("uniqueid"),
                                    action: action
                                },
                                scope: this,
                                timeout: 10000,
                                success: function (response, opts) {
                                    CallsGrid.setLoading(false);
                                },
                                failure: function (response, opts) {
                                    Ext.MessageBox.alert('Error', 'Error occurred during request execution! Please try again!');
                                    CallsGrid.setLoading(false);
                                }
                            });
                        }
                    }
                },
                {dataIndex: 'calldate', width: 140, header: 'date', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'uniqueid', header: 'uniqueid', width: 140},
                {dataIndex: 'src', header: 'src'},
                {dataIndex: 'dst', header: 'dst'},
                {dataIndex: 'dstchannel', header: 'dstchannel'},
                {dataIndex: 'channel', header: 'channel'},
                {dataIndex: 'object_id', width: 100, header: 'Id заказа',
                    renderer: function (value, meta, record) {
                        if (value > 0) {
                            return '<a href="#" onclick="javascript:CreateMenuDelivery({order_id: ' + value + '}, \'' + record.get('country') + '\');">' + value + '</a>';
                        }
                        return value;
                    }
                },
                {dataIndex: 'data_int', width: 100, header: 'Хол статус',
                    renderer: function (v) {
                        return globalColdStatusStore.ValuesJson[v] ? globalColdStatusStore.ValuesJson[v] : v;
                    }
                }, {dataIndex: 'created_by', header: 'Кто создал', width: 150, sortable: true, hidden: false,
                    renderer: function (v) {
                        return (globalManagerStore.ValuesJson[v]) ? globalManagerStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'duration', header: 'duration'},
                {dataIndex: 'billsec', header: 'billsec'},
                {dataIndex: 'disposition', header: 'disposition'},
                {dataIndex: 'uniqueid', width: 100, header: 'file',
                    renderer: function (v, p, r) {
                        if (r.data.disposition === 'ANSWERED' && r.data.dst.length > 5) {
                            files = r.data.userfield;
                            return('<a href="http://call.baribarda.com/call.php?file=' + files + '&type=2&date=' + r.data.calldate.format('Y-m-d') + '&sip=' + r.data.src + '">Запись</a>');
                        } else {
                            return '';
                        }
                    }
                },
                {dataIndex: 'uniqueid', width: 200, header: 'file',
                    renderer: function (v, p, r) {
                        if (r.data.disposition === 'ANSWERED' && r.data.dst.length > 5) {
                            files = r.data.userfield;
                            return('<audio controls="controls" volume="0.9"><source src=http://call.baribarda.com/call.php?file=' + files + '&type=2&date=' + r.data.calldate.format('Y-m-d') + '&sip=' + r.data.src + '" type="audio/mp4" codecs="m4a" /></audio>');
                        } else {
                            return '';
                        }
                    }
                }
            ],
            tbar: [{
                    text: 'Cтатистика',
                    handler: function () {
                        BellStore = new Ext.data.JsonStore({
                            autoDestroy: true,
                            remoteSort: false,
                            pageSize: 100,
                            autoSync: true,
                            proxy: {
                                type: 'ajax',
                                url: '/handlers/get_BellStat.php',
                                simpleSortMode: true,
                                reader: {
                                    type: 'json',
                                    successProperty: 'success',
                                    idProperty: 'id',
                                    root: 'data',
                                    messageProperty: 'message'
                                }
                            },
                            storeId: 'BellStore',
                            fields: [
                                {name: 'period', type: 'string'},
                                {name: 'duration', type: 'string'},
                                {name: 'billsec', type: 'string'},
                                {name: 'count', type: 'string'},
                                {name: 'avg_call', type: 'string'},
                                {name: 'min_call', type: 'string'},
                                {name: 'max_call', type: 'string'},
                                {name: 'price', type: 'string'}
                            ]
                        });
                        BellStore.load();
                        var BellGrid = new Ext.grid.GridPanel({
                            frame: true,
                            autoScroll: false,
                            loadMask: true,
                            height: 550,
                            width: 800,
                            region: 'center',
                            id: 'Bell_grid',
                            store: BellStore,
                            columns: [
                                {dataIndex: 'period', width: 140, header: 'Дата'},
                                {dataIndex: 'duration', width: 80, header: 'Длительность'},
                                {dataIndex: 'billsec', width: 80, header: 'Разговор'},
                                {dataIndex: 'count', width: 60, header: 'К-во'},
                                {dataIndex: 'avg_call', width: 80, header: 'Средняя дл.'},
                                {dataIndex: 'min_call', width: 80, header: 'Минимальная дл.'},
                                {dataIndex: 'max_call', width: 80, header: 'Максимальная дл.'},
                                {dataIndex: 'price', width: 40, header: 'Затраты'}
                            ],
                            tbar: [{
                                    xtype: 'combo',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    width: 190,
                                    name: 'group_us',
                                    anchor: '35%',
                                    id: 'stat_NotifBel',
                                    labelWidth: 70,
                                    value: 'day',
                                    RawValue: 'по дням',
                                    allowBlank: false,
                                    store: [['day', 'по дням'], ['month', 'по месяцам'], ['oper', 'по операторам']],
                                    fieldLabel: 'Поставщик',
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'Дата от',
                                    startDay: 1,
                                    width: 150,
                                    format: 'Y-m-d',
                                    labelWidth: 30,
                                    value: Ext.Date.format(new Date(), 'Y-m-d'),
                                    itemId: 'date',
                                    id: 'stat_StartDateBel',
                                    allowBlank: false
                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'до',
                                    startDay: 1,
                                    width: 150,
                                    format: 'Y-m-d',
                                    labelWidth: 20,
                                    value: Ext.Date.format(new Date(), 'Y-m-d'),
                                    itemId: 'edate',
                                    id: 'stat_EndDateBel',
                                    allowBlank: false
                                }, '->', {
                                    xtype: 'button',
                                    text: 'Построить',
                                    handler: function () {
                                        BellStore.load({method: 'post',
                                            params: {
                                                p1: Ext.getCmp('stat_NotifBel').getValue(),
                                                p2: Ext.getCmp('stat_StartDateBel').getValue().format('Y-m-d'),
                                                p3: Ext.getCmp('stat_EndDateBel').getValue().format('Y-m-d')
                                            }
                                        });
                                    }
                                }],
                            viewConfig: {
                                forceFit: true,
                                enableTextSelection: true,
                                showPreview: true,
                                enableRowBody: true
                            }
                        });
                        Ext.create('widget.uxNotification', {
                            title: 'Данные для ' + globalStaffId,
                            position: 'tc',
                            modal: true,
                            manager: 'demo1',
                            iconCls: 'ux-notification-icon-error',
                            autoCloseDelay: 10000000,
                            height: 600,
                            autoScroll: true,
                            spacing: 20,
                            useXAxis: true,
                            closable: true,
                            id: 'win-group',
                            slideInDuration: 800,
                            slideBackDuration: 1500,
                            slideInAnimation: 'elasticIn',
                            slideBackAnimation: 'elasticIn',
                            items: [BellGrid]
                        }).show();
                    }
                }, {
                    text: 'Статистика по отпр. SMS',
                    handler: function () {
                        Ext.create('Ext.window.Window', {
                            title: 'Статистика по отпр. SMS',
                            height: 480,
                            width: 1024,
                            layout: 'fit',
                            items: Ext.create("Ext.grid.Panel", {
                                xtype: 'grid',
                                border: false,
                                viewConfig: {
                                    forceFit: true,
                                    enableTextSelection: true
                                },
                                tbar: Ext.create("Ext.form.Panel", {
                                    layout: 'hbox',
                                    frame: 1,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            fieldLabel: 'ID',
                                            labelWidth: 50,
                                            labelSeparator: '',
                                            name: 'phone',
                                            allowBlank: true
                                        }, {
                                            xtype: 'button',
                                            text: 'Ok',
                                            handler: function () {
                                                var form = this.up("form");
                                                if (form.getForm().isValid()) {
                                                    var values = form.getForm().getValues();
                                                    var SMSStatStore = Ext.data.StoreManager.lookup('SMSStat');
                                                    SMSStatStore.proxy.url = '/handlers/get_sms_stat.php?phone=' + values.phone;
                                                    SMSStatStore.load();
                                                }
                                            }
                                        }
                                    ]
                                }),
                                columns: [
                                    {
                                        dataIndex: 'id',
                                        header: 'ID',
                                        width: 60
                                    }, {
                                        dataIndex: 'country',
                                        header: 'Country'
                                    }, {
                                        dataIndex: 'operator',
                                        header: 'Operator'
                                    }, {
                                        dataIndex: 'sender_id',
                                        header: 'Sender ID'
                                    }, {
                                        dataIndex: 'status_name',
                                        header: 'Status'
                                    }, {
                                        dataIndex: 'message',
                                        header: 'Message',
                                        flex: true
                                    }, {
                                        dataIndex: 'check_time',
                                        header: 'Check time'
                                    }, {
                                        dataIndex: 'send_date',
                                        header: 'Send date'
                                    }
                                ],
                                store: Ext.create('Ext.data.Store', {
                                    storeId: 'SMSStat',
                                    fields: [
                                        {
                                            name: 'id',
                                            type: 'int'
                                        }, {
                                            name: 'country',
                                            type: 'string'
                                        }, {
                                            name: 'operator',
                                            type: 'string'
                                        }, {
                                            name: 'sender_id',
                                            type: 'string'
                                        }, {
                                            name: 'status_name',
                                            type: 'string'
                                        }, {
                                            name: 'message',
                                            type: 'string'
                                        }, {
                                            name: 'check_time',
                                            type: 'string'
                                        }, {
                                            name: 'send_date',
                                            type: 'string'
                                        }
                                    ],
                                    proxy: {
                                        type: 'ajax',
                                        url: '/handlers/get_sms_stat.php',
                                        reader: {
                                            type: 'json',
                                            root: 'data',
                                            totalProperty: 'total'
                                        }
                                    }
                                })
                            })
                        }).show();
                    }
                }, '->', {
                    text: 'Оценки',
                    handler: function () {
                        var filtersPoint = new Ext.ux.grid.FiltersFeature({
                            encode: false,
                            local: false,
                            filters: [
                                {dataIndex: 'sip_id', type: 'string'},
                                {dataIndex: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
                            ]
                        });
                        PointStore = new Ext.data.JsonStore({
                            autoDestroy: true,
                            remoteSort: false,
                            pageSize: 100,
                            autoSync: true,
                            proxy: {
                                type: 'ajax',
                                url: '/handlers/get_calls_rating.php',
                                simpleSortMode: true,
                                reader: {
                                    type: 'json',
                                    successProperty: 'success',
                                    idProperty: 'id',
                                    root: 'data',
                                    messageProperty: 'message'
                                }
                            },
                            storeId: 'PointStore',
                            fields: [
                                {name: 'cid', type: 'string'},
                                {name: 'order_id', type: 'numeric'},
                                {name: 'callid', type: 'string'},
                                {name: 'sip_id', type: 'string'},
                                {name: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                                {name: 'source', type: 'string'},
                                {name: 'point', type: 'numeric'}
                            ]
                        });
                        PointStore.load();

                        var PointGrid = new Ext.grid.GridPanel({
                            frame: true,
                            autoScroll: false,
                            loadMask: true,
                            height: 550,
                            width: 650,
                            features: [
                                filtersPoint,
                                {ftype: 'summary'}
                            ],
                            region: 'center',
                            id: 'PointGrid',
                            store: PointStore,
                            columns: [
                                {dataIndex: 'cid', width: 60, header: 'ID', hidden: true},
                                {dataIndex: 'order_id', width: 70, header: 'ID заказа'},
                                {dataIndex: 'callid', width: 170, header: 'ID звонка'},
                                {dataIndex: 'sip_id', width: 50, header: 'Сип'},
                                {dataIndex: 'date', width: 140, header: 'Дата', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                                {dataIndex: 'source', width: 100, header: 'Направление'},
                                {summaryType: 'sum', summaryRenderer: function (val) {
                                        if (val > 0) {
                                            return '<span style="color: #04B404;">' + val + '</span>';
                                        } else if (val < 0) {
                                            return '<span style="color: #FF0000;">' + val + '</span>';
                                        }
                                        return val;
                                    }, dataIndex: 'point', width: 30, header: 'Баллы'
                                }
                            ],
                            bbar: new Ext.PagingToolbar({
                                store: PointStore,
                                beforePageText: 'Страница',
                                displayMsg: 'Отображается {0} - {1} из {2}',
                                afterPageText: 'из {0}',
                                displayInfo: true,
                                plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [10, 20, 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000, 2500]})]
                            }),
                            viewConfig: {
                                forceFit: true,
                                enableTextSelection: true,
                                showPreview: true,
                                enableRowBody: true
                            }
                        });
                        Ext.create('widget.uxNotification', {
                            title: 'Оценки входящей',
                            position: 'tc',
                            modal: true,
                            manager: 'demo1',
                            iconCls: 'ux-notification-icon-error',
                            autoCloseDelay: 10000000,
                            height: 680,
                            autoScroll: true,
                            spacing: 20,
                            useXAxis: true,
                            closable: true,
                            id: 'win-point',
                            slideInDuration: 800,
                            slideBackDuration: 1500,
                            slideInAnimation: 'elasticIn',
                            slideBackAnimation: 'elasticIn',
                            items: [PointGrid]
                        }).show();
                    }
                }, '->', {
                    text: 'Все звонки',
                    icon: '/images/all_16x16.png',
                    handler: function () {
                        CallsStore.proxy.url = '/handlers/get_calldata_v2' + (globalStaffId === 11111111 ? '' : '') + '.php';
                        CallsStore.load();
                    }
                }, {
                    text: 'Входящие звонки',
                    icon: '/images/cellphone_16x16.png',
                    handler: function () {
                        CallsStore.proxy.url = '/handlers/get_calldata_v2' + (globalStaffId === 11111111 ? '' : '') + '.php?income=1';
                        CallsStore.load();
                    }
                }, {
                    text: 'Мин к-во операторов',
                    icon: '/images/minimum_16x16.png',
                    hidden: globalStaffId !== 11111111,
                    handler: function () {
                        window.alert('kuku');
                    }
                }, {
                    text: 'Очистить фильтры',
                    icon: '/images/clear_filters.png',
                    handler: function (b, e) {
                        b.up('grid').filters.clearFilters();
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({
                store: CallsStore,
                beforePageText: 'Страница',
                displayMsg: 'Отображается {0} - {1} из {2}',
                afterPageText: 'из {0}',
                displayInfo: true,
                plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [10, 20, 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000, 2500]})]
            }),
            viewConfig: {
                forceFit: true,
                enableTextSelection: true
            }
        });
        tabs.add({
            id: 'CallsTab',
            iconCls: 'fa fa-1x fa-users',
            layout: {type: 'card'},
            title: '<div style="font-size: 18px; padding-left:15px;">Звонки</div>',
            items: [CallsGrid],
            closable: true
        }).show();
    }
}