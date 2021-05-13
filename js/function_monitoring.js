var Ext;
function MonitorTab(tabs, inouts) {

    globalQueueStore.reload();
    // Online MonitorinOut 
    Ext.define('LeftModel', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'que', type: 'string'},
            {name: 'caller', type: 'string'},
            {name: 'waiting', type: 'string'},
            {name: 'time', type: 'string'},
            {name: 'agent', type: 'string'}
        ]
    });
    var leftStore = Ext.create('Ext.data.Store', {
        model: 'LeftModel',
        autoDestroy: true
    });
    Ext.define('DongleModel', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'device', type: 'string'},
            {name: 'status', type: 'string'},
            {name: 'number', type: 'string'}
        ]
    });
    var dongleStore = Ext.create('Ext.data.Store', {
        model: 'DongleModel',
        autoDestroy: true
    });
    Ext.define('RightModel', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'agent', type: 'string'},
            {name: 'server', type: 'string'},
            {name: 'responsible', type: 'numeric', sortable: true},
            {name: 'que', type: 'string'},
            {name: 'status', type: 'string'},
            {name: 'penalty', type: 'string'}
        ]
    });
    var rightStore = Ext.create('Ext.data.Store', {
        model: 'RightModel',
        autoDestroy: true
    });
    // END get stores

    // Left grid

    var gridLeft = new Ext.grid.GridPanel({
        store: leftStore,
        region: 'west',
        layout: 'fit',
        split: true,
        id: 'OnlineMonitorGridLeft_',
        title: 'Текущие звонки',
        autoScroll: true,
        minWidth: 300,
        stateId: 'OnlineMonitorGridLeft_',
        features: [
            new Ext.ux.grid.FiltersFeature({
                encode: false,
                autoReload: true,
                local: true,
                filters: [
                    {dataIndex: 'que', type: 'string'}
                ]
            })
        ],
        columns: [
            {xtype: 'rownumberer', header: '№', width: 50, resizable: true, cls: 'row_numberer', align: 'center', tdCls: 'row_numberer'},
            {dataIndex: 'que', header: 'queuename', width: 100},
            {dataIndex: 'caller', header: 'caller', width: 170},
            {dataIndex: 'waiting', header: 'waiting', width: 70},
            {dataIndex: 'time', header: 'timer', width: 70},
            {dataIndex: 'agent', header: 'agent', width: 80}
        ],
        dockedItems: [
            {
                xtype: 'toolbar',
                items: [
//                    {xtype: 'component', width: 10},
                    {xtype: 'tbtext', text: 'waiting' + ': 0  ', id: 'waiting_'},
                    '-',
                    {xtype: 'tbtext', text: 'timer' + ': 0  ', id: 'taking_'},
                    '-',
                    '->',
                    {xtype: 'tbtext', text: '', id: 'pred_'}
                ]
            }
        ],
        viewConfig: {
            enableTextSelection: true
        }
    });
    // END Left grid
    var gridDongle = new Ext.grid.GridPanel({
        border: true,
        store: dongleStore,
        region: 'west',
        id: 'OnlineMonitorGridDongle_',
        stateId: 'OnlineMonitorGridDongle_',
        columns: [
            {dataIndex: 'device', header: 'Модем', width: 100},
            {dataIndex: 'status', header: 'Статус', width: 120},
            {dataIndex: 'number', header: 'Номер', width: 120}
        ],
        width: 370,
        dockedItems: [{
                xtype: 'toolbar',
                items: [
                    {xtype: 'component', width: 10},
                    {xtype: 'tbtext', text: 'Модемов' + ': 0  ', id: 'modems_', width: 280}
                ]
            }],
        title: 'Состояние модемов',
        margins: '0 2 0 0',
        viewConfig: {
            enableTextSelection: true,
            getRowClass: function (record, index) {
                var ret = '';
                var c = record.get('status');
                switch (c) {
                    case 'Free':
                        ret = 'unavailable';
                        break;
                    case 'Outgoing':
                        ret = 'inuse';
                        break;
                    case 'Incoming':
                        ret = 'notinuse';
                        break;
                    case 'Dialing':
                        ret = 'busy';
                        break;
                    case 'Ring':
                        ret = 'ringing';
                        break;
                    case 'Not connec':
                        ret = 'failed';
                        break;
                }
                return ret;
            }
        }
    });

    var gridRight = new Ext.grid.GridPanel({
        border: true,
        region: 'center',
        autoScroll: true,
        store: rightStore,
        id: 'OnlineMonitorGrid_',
        stateId: 'OnlineMonitorGrid_',
        title: 'Состояние операторов в очереди',
        layout: 'fit',
        split: true,
        features: [
            new Ext.ux.grid.FiltersFeature({
                encode: false,
                autoReload: true,
                local: true,
                filters: [
                    {dataIndex: 'responsible', type: 'list', phpMode: true, options: globalResponsibleStore.ValuesExtendedFiltersArr},
                    {dataIndex: 'server', type: 'list', phpMode: true, options: [["SIP", "SIP"], ["SIP2", "SIP2"]]},
                    {dataIndex: 'status', type: 'string'}
                ]
            })
        ],
        columns: [
            {xtype: 'rownumberer', header: '№', width: 70, resizable: true, cls: 'row_numberer', align: 'center', tdCls: 'row_numberer'},
            {dataIndex: 'agent', header: 'agent', width: 200},
            {dataIndex: 'server', header: 'server', width: 40},
            {dataIndex: 'que', header: 'queuename'},
            {dataIndex: 'status', header: 'status'},
            {dataIndex: 'penalty', header: 'Рейтинг', width: 60, hidden: true},
            {dataIndex: 'responsible', width: 200, sortable: true, header: 'Ответственный',
                renderer: function (v) {
                    return globalResponsibleStore.ValuesJson[v] ? globalResponsibleStore.ValuesJson[v] : v;
                }
            }
        ],
        viewConfig: {
            enableTextSelection: true,
            getRowClass: function (record, index) {
                var c = record.get('status');
                switch (c) {
                    case 'Unavailable':
                        return 'unavailable';
                        break;
                    case 'In use':
                        return 'inuse';
                        break;
                    case 'Not in use':
                        return 'notinuse';
                        break;
                    case 'Busy':
                        return 'busy';
                        break;
                    case 'Ringing':
                        return 'ringing';
                        break;
                    default:
                        return '';
                        break;
                }
            }
        }
    });

    var ReloadProgressPanel = Ext.create('Ext.Panel', {
        itemId: 'ReloadProgressPanel',
        region: 'south',
        layout: 'fit',
        maxHeight: 50,
        items: [
            {xtype: 'component', flex: 0.5},
            {
                xtype: 'progressbar',
                width: 400,
                id: 'pbar_'
            },
            {xtype: 'component', flex: 0.5}
        ]
    });

    progr(leftStore, rightStore, dongleStore, false, 'ALL', inouts, '82*');

    var monitoringPanel = {
        id: 'MonitoringTab',
        closable: false,
        title: 'Онлайн мониторинг',
        iconCls: 'fa fa-1x fa-desktop',
        layout: 'border',
        items: [
            gridLeft,
            gridRight,
            ReloadProgressPanel
        ],
        tbar: [
            {
                text: 'Управление коефициентами',
                itemId: 'koefsettings',
                disabled: true,
                hidden: [11111111, 25937686, 63077972, 77777777].indexOf(globalStaffId) < 0,
                icon: '/images/fast-4_16x16.png',
                handler: function () {
                    var wind = Ext.getCmp('KoefSettingsWindow');
                    if (!wind) {

                        Ext.define('KoefSettingsModel', {
                            extend: 'Ext.data.Model',
                            fields: [
                                {name: 'id'},
                                {name: 'queue_name'},
                                {name: 'factor'}
                            ]
                        });
                        var KoefSettingsStore = new Ext.data.JsonStore({
                            model: 'KoefSettingsModel',
                            autoSync: true,
                            autoLoad: true,
                            proxy: {
                                type: 'ajax',
                                api: {
                                    read: '/handlers/handler_queue_koef.php?method=read',
                                    update: '/handlers/handler_queue_koef.php?method=update',
                                    create: '/handlers/handler_queue_koef.php?method=insert',
                                    destroy: '/handlers/handler_queue_koef.php?method=delete'
                                },
                                simpleSortMode: true,
                                reader: {
                                    type: 'json',
                                    successProperty: 'success',
                                    idProperty: 'id',
                                    root: 'data',
                                    messageProperty: 'message'
                                }
                            },
                            remoteSort: true,
                            sorters: [{property: 'queue_name', direction: 'ASC'}],
                            pageSize: 50
                        });
                        var rowEditing = new Ext.grid.plugin.RowEditing({
                            saveText: 'Сохранить',
                            clicksToEdit: 2,
                            listeners: {
                                edit: function (editor, e) {
                                    e.store.reload();
                                },
                                canceledit: function (editor, e, eOpts) {
                                    var dataModel = e.grid.getSelectionModel().getLastSelected();
                                    if (!dataModel.raw.id) {
                                        e.store.remove(dataModel);
                                        e.store.reload();
                                    }
                                    e.store.reload();
                                }
                            }
                        });
                        var KoefSettingsGrid = new Ext.grid.GridPanel({
                            autoScroll: true,
                            store: KoefSettingsStore,
                            loadMask: true,
                            forceFit: true,
                            plugins: [rowEditing],
                            features: [
                                new Ext.ux.grid.FiltersFeature({
                                    encode: false,
                                    local: false,
                                    filters: [
                                        {dataIndex: 'queue_name', type: 'list', phpMode: true, options: globalQueueStore.ValuesArr},
                                        {dataIndex: 'factor', type: 'numeric'}
                                    ]
                                })
                            ],
                            columns: [
                                {dataIndex: 'id', width: 40, header: 'ID', hidden: true},
                                {dataIndex: 'queue_name', width: 140, header: 'Очередь',
                                    editor: {
                                        xtype: 'combo',
                                        editable: false,
                                        queryMode: 'local',
                                        allowBlank: true,
                                        store: globalQueueStore,
                                        valueField: 'id',
                                        displayField: 'value'
                                    }
                                },
                                {dataIndex: 'factor', width: 40, header: 'Коефициент',
                                    editor: {
                                        xtype: 'numberfield',
                                        decimalPrecision: 1,
                                        allowBlank: false,
                                        minValue: 0,
                                        maxValue: 6,
                                        step: 0.1
                                    }
                                }
                            ],
                            viewConfig: {
                                preserveScrollOnRefresh: true,
                                enableTextSelection: true
                            },
                            listeners: {
                                selectionchange: function (selRowModel, dataModels) {
                                    KoefSettingsGrid.down('#delete').setDisabled(dataModels.length === 0);
                                }
                            },
                            dockedItems: [
                                {
                                    xtype: 'toolbar',
                                    dock: 'top',
                                    items: [{
                                            text: 'Добавить',
                                            iconCls: 'fa fa-plus-circle',
                                            handler: function () {
                                                KoefSettingsStore.insert(0, new KoefSettingsModel());
                                                rowEditing.startEdit(0, 0);
                                            }
                                        },
                                        {
                                            text: 'Удалить',
                                            itemId: 'delete',
                                            iconCls: 'fa fa-minus-circle',
                                            disabled: true,
                                            handler: function (b) {
                                                var dataModel = b.up('grid').getSelectionModel().getLastSelected();
                                                if (dataModel) {
                                                    KoefSettingsStore.remove(dataModel);
                                                    KoefSettingsStore.reload();
                                                }
                                            }
                                        }]
                                }, {
                                    xtype: 'toolbar',
                                    dock: 'bottom',
                                    items: [
                                        new Ext.PagingToolbar({
                                            store: KoefSettingsStore,
                                            beforePageText: 'Страница',
                                            displayMsg: 'Отображается {0} - {1} из {2}',
                                            afterPageText: 'из {0}',
                                            displayInfo: true,
                                            plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [50, 100, 200, 500, 1000]})]
                                        }),
                                        '->', {
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
                        var wind = Ext.create('Ext.Window', {
                            id: 'KoefSettingsWindow',
                            width: 500,
                            height: 500,
                            autoScroll: true,
                            title: 'Управление коефициентами',
                            iconCls: 'fa fa-2x fa-tachometer',
                            constrainHeader: true,
                            plain: true,
                            layout: 'fit',
                            flex: true,
                            maximizable: true,
                            items: [KoefSettingsGrid]
                        });
                    }
                    wind.show();
                }
            }, '->',
            {
                text: 'SIP активность',
                icon: '/images/chart-5_16x16.png',
                handler: function () {
                    SipGant();
                }
            }, {
                text: 'Активность в предиктиве',
                icon: '/images/timer-1_16x16.png',
                handler: function () {
                    var OperStore = new Ext.data.JsonStore({autoDestroy: true, remoteSort: false, pageSize: 100, autoSync: true,
                        proxy: {type: 'ajax', url: '/handlers/get_OperStat.php', simpleSortMode: true,
                            reader: {type: 'json', successProperty: 'success', idProperty: 'id', root: 'data', messageProperty: 'message'}
                        },
                        storeId: 'OperStore',
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
                    OperStore.load();
                    var OperGrid = new Ext.grid.GridPanel({
                        frame: true,
                        autoScroll: false,
                        loadMask: true,
                        height: 550,
                        width: 800,
                        region: 'center',
                        id: 'Oper_grid',
                        store: OperStore,
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
                                id: 'group_notif',
                                labelWidth: 70,
                                value: 'day',
                                RawValue: 'по дням',
                                allowBlank: false,
                                store: [['day', 'по дням'], ['month', 'по месяцам'], ['oper', 'по предиктиву'], ['oper2', 'по ручному']],
                                fieldLabel: 'Поставщик',
                                valueField: 'value',
                                displayField: 'value'
                            }, {
                                xtype: 'combo',
                                editable: false,
                                forceSelection: true,
                                triggerAction: 'all',
                                queryMode: 'local',
                                width: 190,
                                name: 'group_que',
                                anchor: '35%',
                                id: 'group_que',
                                labelWidth: 70,
                                value: 'day',
                                RawValue: 'по очередям',
                                store: ['LogistKZG_P', 'LogistKZ_P', 'IncomeTorgKZ', 'OldTorgKZ_P', 'PostKZ_P', 'KgzDelivery_P', 'TorgKGZ_P', 'TorgKZ_P', 'TorgKZn_P', 'oplachen1_P', 'otkaz1_P', 'otmena1_P', 'oplachen2_P', 'otkaz2_P', 'Recovery_P'],
                                fieldLabel: 'Очередь',
                                valueField: 'value',
                                displayField: 'value'
                            }, {
                                xtype: 'datefield',
                                fieldLabel: 'Дата от',
                                startDay: 1,
                                width: 150,
                                format: 'Y-m-d',
                                labelWidth: 30,
                                value: new Date().format('Y-m-d'),
                                itemId: 'date',
                                id: 'stat_StartDateOper',
                                allowBlank: false
                            }, {
                                xtype: 'datefield',
                                fieldLabel: 'до',
                                startDay: 1,
                                width: 150,
                                value: new Date().format('Y-m-d'),
                                format: 'Y-m-d',
                                labelWidth: 20,
                                itemId: 'edate',
                                id: 'stat_EndDateOper',
                                allowBlank: false
                            }, '->', {
                                xtype: 'button',
                                text: 'Построить',
                                handler: function () {
                                    OperStore.load({method: 'post',
                                        params: {
                                            p1: Ext.getCmp('group_notif').getValue(),
                                            p2: Ext.getCmp('stat_StartDateOper').getValue().format('Y-m-d'),
                                            p3: Ext.getCmp('stat_EndDateOper').getValue().format('Y-m-d'),
                                            p4: Ext.getCmp('group_que').getValue()
                                        }
                                    });
                                }
                            }],
                        viewConfig: {
                            forceFit: true,
                            enableTextSelection: true,
                            showPreview: true, // custom property
                            enableRowBody: true, // required to create a second, full-width row to show expanded Record data
                            getRowClass: function (record, rowIndex, rp, ds) { // rp = rowParams
                                //return (record.data.Ban == '0') ? 'price-fall' : 'price-red';
                            }
                        }
                    });
                    Ext.create('widget.uxNotification', {
                        title: 'Данные для ',
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
                        items: [OperGrid]
                    }).show();
                }
            }, {
                text: 'Время регистраций SIP',
                icon: '/images/clock-5_16x16.png',
                handler: function () {
                    store = new Ext.data.JsonStore({
                        // store configs
                        autoDestroy: true,
                        proxy: {
                            type: 'ajax',
                            url: 'handlers/get_time.php',
                            simpleSortMode: true,
                            reader: {
                                type: 'json',
                                root: 'data',
                                idProperty: 'id',
                                totalProperty: 'total'
                            }
                        },
                        remoteSort: true,
                        sortInfo: {
                            field: 'date',
                            direction: 'ASC'
                        },
                        storeId: 'myStoreActiv',
                        fields: [
                            {name: 'id', type: 'string'},
                            {name: 'date', type: 'date', dateFormat: 'Y-m-d'},
                            {name: 'sip', type: 'string'},
                            {name: 'Inuse', type: 'string'}
                        ]
                    });
                    var filters = new Ext.ux.grid.FiltersFeature({
                        encode: false,
                        local: false,
                        filters: [
                            {dataIndex: 'id', type: 'string'},
                            {dataIndex: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                            {dataIndex: 'sip', type: 'string'},
                            {dataIndex: 'Inuse', type: 'string'}
                        ]
                    });
                    var columns = [
                        {
                            dataIndex: 'id',
                            header: 'Id',
                            width: 80
                        }, {
                            dataIndex: 'date',
                            flex: 1,
                            header: 'Дата',
                            renderer: Ext.util.Format.dateRenderer('Y-m-d')
                        }, {
                            dataIndex: 'sip',
                            flex: 1,
                            header: 'СИП'
                        }, {
                            dataIndex: 'Inuse',
                            header: 'Время в регистрации',
                            width: 160,
                            renderer: function (v) {
                                return(secondsToTime(v));
                            }
                        }
                    ];
                    var grid = new Ext.grid.GridPanel({
                        border: false,
                        store: store,
                        id: 'SipActivityGrid',
                        stateful: true,
                        stateId: 'SipActivityGrid',
                        columns: columns,
                        loadMask: true,
                        viewConfig: {
                            enableTextSelection: true
                        },
                        features: [filters],
                        listeners: {
                            render: {
                                fn: function () {
                                    store.load({
                                        params: {
                                            start: 0,
                                            limit: 50
                                        }
                                    });
                                }
                            },
                            itemdblclick: {
                                fn: function (grid, record, item, index, event) {
                                    var record = grid.getStore().getAt(index);
                                    temp_date = record.data.date;
                                    SipSessions(temp_date.format('Y-m-d'), record.data.sip);
                                }
                            }
                        },
                        bbar: new Ext.PagingToolbar({
                            store: store,
                            pageSize: 50,
                            features: [filters],
                            displayInfo: true
                        }),
                        tbar: {
                            items: [{
                                    xtype: 'numberfield',
                                    maxValue: 9999,
                                    width: 190,
                                    labelWidth: 60,
                                    fieldLabel: 'Sip',
                                    id: 'now_sip'
                                },
                                '->', {
                                    xtype: 'button',
                                    text: 'Построить',
                                    handler: function () {
                                        dates = new Date().toJSON().slice(0, 10);
                                        sip = Ext.getCmp('now_sip').getValue();
                                        SipSessions(dates, sip);
                                    }
                                }
                            ]
                        }
                    });
                    Ext.create('Ext.window.Window', {
                        id: 'SipActivityWindow',
                        width: 700,
                        height: 400,
                        title: 'Время регистраций SIP',
                        iconCls: 'staff-list',
                        constrainHeader: true,
                        plain: true,
                        layout: 'fit',
                        stateId: 'SipActivityWindow',
                        items: [grid]
                    });
                    var win = Ext.getCmp('SipActivityWindow');
                    win.show();
                }

            }

        ]
    };

    tabs.add(monitoringPanel).show();
}

function progr(leftStore, rightStore, dongleStore, stopik, queue, inOut, prefix) {
    if (stopik) {
        clearInterval(progress[queue]);
        return false;
    } else {
        var pb = Ext.getCmp('pbar_');
        var prog = 10;
        progress[queue] = setInterval(function () {
            if (prog === 0) {
                clearInterval(progress[queue]);
                pb.updateText(prog);
                pb.updateProgress(prog / 10);
                Ext.Ajax.request({
                    url: '/handlers/get_monitor.php?que=' + queue + '&inOut=' + inOut + '&prefix=' + prefix,
                    success: function (response) {
                        var json = Ext.decode(response.responseText);
                        leftStore.loadData(json.leftStore);
                        rightStore.loadData(json.rightStore);
                        //dongleStore.loadData(json.dongleStore);

                        //modems = json.modems;
                        //Ext.getCmp('modems_').el.dom.innerHTML = modems;

                        Ext.getCmp('waiting_').el.dom.innerHTML = 'waiting' + ': ' + json.waiting;
                        Ext.getCmp('taking_').el.dom.innerHTML = 'timer' + ': ' + json.taking;
                        Ext.getCmp('pred_').el.dom.innerHTML = json.pred;
                        var leftGrid = Ext.getCmp('OnlineMonitorGridLeft_');
                        if (leftGrid) {
                            if (leftGrid.filters.getFilter('que')) {
                                leftGrid.filters.reload();
                            }
                        }
                        // Инициализация фильтра очередей, если он его нет
                        var rightGrid = Ext.getCmp('OnlineMonitorGrid_');
                        if (rightGrid) {
                            if (rightGrid.filters.getFilter('que')) {
                                rightGrid.filters.reload();
                            } else {
                                rightGrid.filters.addFilter({dataIndex: 'que', type: 'list', phpMode: true, options: globalQueueStore.ValuesArr});
                                Ext.getCmp('MonitoringTab').down('#koefsettings').setDisabled(false);
                            }
                        }

                        obj = {
                            sipsCount: json.sipsCount,
                            unavailable: json.unavailable,
                            inuse: json.inuse,
                            busy: json.busy,
                            notinuse: json.notinuse,
                            ringing: json.ringing
                        };
                        // var detailPanel = Ext.getCmp('detailPanel_');
                        // additionTpl.overwrite(detailPanel.body, obj); 

                        progr(leftStore, rightStore, dongleStore, stopik, queue, inOut, prefix);
                    }
                });
                return false;
            }
            pb.updateText(prog);
            pb.updateProgress(prog / 10);
            prog -= 1;
        }, 1000);
    }
}
