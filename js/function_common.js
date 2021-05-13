
Ext.apply(Ext.form.VTypes, {
    password: function (value, field) {
        var valid = false;
        if (field.matches) {
            var otherField = Ext.getCmp(field.matches);
            if (value == otherField.getValue()) {
                otherField.clearInvalid();
                valid = true;
            } else {
                return false;
            }
        }
        return valid;
    },
    passwordText: 'Пароль не соответствует требованиям!'
});

function approveSms(id, kz_delivery) {
    console.log(id);
    Ext.Ajax.request({
        url: '/handlers/sendApproveSMS.php?id=' + id + '&deliv=' + kz_delivery,
        method: 'POST',
        success: function (response) {
            Ext.Msg.alert('Успех!', 'Сообщение успешно отправлено.');
        },
        failure: function (response, opts) {
            Ext.Msg.alert('Ошибка!', 'Ошибка отправки');
        }
    });
}

function delFormElem(elemIds) {
    for (var i = 0; i < elemIds.length; i++) {
        var elemItem = Ext.getCmp(elemIds[i]);
        if (elemItem) {
            elemItem.destroy( );
        }
    }
}

function showDeliveryTab(commonDoAutoload) {
    var tabs = Ext.getCmp('mainTabPanelId');

    var tab = tabs.queryById('DostavkaTab');
    if (tab) {
        tab.show();
    } else {
        var grid = Ext.getCmp('DostavkaGridId');
        grid.commonDoAutoload = typeof (commonDoAutoload) === 'undefined' ? true : commonDoAutoload;
        tabs.add({
            id: 'DostavkaTab',
            iconCls: 'fa fa-1x fa-shopping-cart',
            layout: {
                type: 'card'
            },
            title: '<div style="font-size: 18px; padding-left:15px;">Доставка</div>',
            items: [grid]
        }).show();
    }
}

Ext.create('Ext.data.Store', {
    storeId: 'AllMessagesStore',
    fields: [
        {name: 'Message_Id', type: 'int'},
        {name: 'Message_UserName'},
        {name: 'Message_UserId'},
        {name: 'Message_Status'},
        {name: 'Creator'},
        {name: 'CreatorName'},
        {name: 'created_at', dateFormat: 'Y-m-d H:i:s'},
        {name: 'Message_ReadTimestamp', dateFormat: 'Y-m-d H:i:s'},
        {name: 'MessageTemplate_Header'},
        {name: 'Message_MessageTaskId', type: 'numeric'}
    ],
    proxy: {
        type: 'ajax',
        url: '/handlers/get_messages.php',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    },
    autoLoad: true
});

if ((session_admin + session_adminlogist + session_adminlogistpost) > 0) {
    var setpost = false;
} else {
    var setpost = true;
}

var MessageGrid = Ext.create('Ext.grid.Panel', {
    autoScroll: true,
    frame: true,
    store: Ext.data.StoreManager.lookup('AllMessagesStore'),
    columns: [
        {dataIndex: 'Message_Id', header: 'Id', hidden: true},
        {dataIndex: 'created_at', header: 'date', width: 140},
        {dataIndex: 'Message_UserName', header: 'Адресат (имя)'},
        {dataIndex: 'Message_UserId', header: 'Адресат (фамилия)'},
        {dataIndex: 'MessageTemplate_Header', header: 'Header', width: 250},
        {dataIndex: 'Message_Status', header: 'Status'},
        {dataIndex: 'CreatorName', header: 'Создатель (имя)'},
        {dataIndex: 'Creator', header: 'Создатель (фамилия)'},
        {dataIndex: 'Message_ReadTimestamp', header: 'date read', width: 140}
    ],
    // features: [AllMessages_filters],
    viewConfig: {
        enableTextSelection: true
    },
    listeners: {
        itemdblclick: {
            fn: function (grid, record, item, index, event) {
                create_MessageView(grid.store.data.items[index].data.Message_Id);
            }
        }
    },
    tbar: [
        {
            xtype: 'button',
            text: 'Написать',
            disabled: setpost,
            handler: function () {
                create_MessageView(0);
            }
        }
    ],
    dockedItems: [{
            xtype: 'pagingtoolbar',
            store: Ext.data.StoreManager.lookup('AllMessagesStore'),
            dock: 'bottom',
            pageSize: 50,
            displayInfo: true
        }
    ]
});

function create_MessageView(id) {
    if (id == 0) {
        var visibles = 'textfield';
        var visibles2 = 'textarea';
    } else {
        var visibles = 'displayfield';
        var visibles2 = 'displayfield';
    }
    var groups = ['Операторы КЗ', 'Операторы RU', 'Операторы КГЗ,АМ,УЗб', 'Админы', 'Логисты'];
    var users = Ext.create('Ext.data.ArrayStore', {
        autoDestroy: true,
        storeId: 'my_users',
        proxy: {
            type: 'ajax',
            url: '/handlers/get_usersm.php',
            reader: {
                type: 'array'
            }
        },
        fields: ['id', 'value'],
        initComponent: function () {
            this.addEvents('ready');
        },
        is_ready: function () {
            this.fireEvent('ready', this);
        }
    });
    users.load();

    var win = Ext.getCmp('Message_' + id + '_window');
    if (!win) {
        win = new Ext.Window({
            id: 'Message_' + id + '_window',
            //constrainHeader:true,
            autoScroll: true,
            xtype: 'window',
            title: 'Сообщения №' + id,
            width: 800,
            height: 420,
            layout: 'fit',
            stateId: 'Message_all',
            items: [{
                    xtype: 'panel',
                    autoScroll: true,
                    border: false,
                    fbar: [{
                            xtype: 'button',
                            text: 'Отправить сообщение',
                            hidden: id,
                            handler: function (button) {
                                var this_form = Ext.getCmp('MessageForm_' + id).getForm();
                                if (this_form.isValid()) {
                                    group = Ext.getCmp('message_group' + id).getValue();
                                    user = Ext.getCmp('selector2' + id).getValue();
                                    texts = Ext.getCmp('text' + id).getValue();
                                    header = Ext.getCmp('header' + id).getValue();
                                    if ((group || user) && texts && header) {
                                        this_form.submit({
                                            url: '/handlers/set_Message.php?set=1',
                                            waitMsg: 'Ждите отравку...',
                                            success: function (fp, action) {
                                                Ext.Msg.alert('Success', 'Сообщение отправлено');
                                            }
                                        });
                                    } else {
                                        Ext.Msg.alert('<br><br>Заполни все данные!');
                                    }
                                } else {
                                    Ext.Msg.alert('error', 'check_for_errors');
                                }
                            }
                        }],
                    items: [{
                            xtype: 'form',
                            id: 'MessageForm_' + id,
                            url: '/handlers/get_Message.php?id=' + id,
                            border: false,
                            labelWidth: 70,
                            padding: 10,
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Id',
                                    anchor: '100%',
                                    name: 'Id'
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Дата',
                                    anchor: '100%',
                                    name: 'Timestamp'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Группа',
                                    anchor: '50%',
                                    queryMode: 'local',
                                    editable: false,
                                    store: groups,
                                    labelStyle: 'width:100px',
                                    id: 'message_group' + id,
                                    valueField: 'id',
                                    displayField: 'value',
                                    allowBlank: true,
                                    triggerAction: 'all',
                                    hiddenName: 'groups',
                                    name: 'groups'
                                }, {
                                    allowBlank: true,
                                    msgTarget: 'under',
                                    allowAddNewData: true,
                                    id: 'selector2' + id,
                                    xtype: 'combo',
                                    addNewDataOnBlur: true,
                                    fieldLabel: 'Пользователь',
                                    labelStyle: 'width:100px',
                                    emptyText: 'insert_FIO',
                                    name: 'user[]',
                                    anchor: '80%',
                                    store: users,
                                    queryMode: 'local',
                                    displayField: 'value',
                                    valueField: 'id',
                                    extraItemCls: 'x-tag'
                                }, {
                                    xtype: visibles,
                                    fieldLabel: 'Заглавие',
                                    anchor: '100%',
                                    id: 'header' + id,
                                    name: 'Header'
                                }, {
                                    xtype: visibles2,
                                    fieldLabel: 'Текст',
                                    id: 'text' + id,
                                    anchor: '100%',
                                    name: 'Text'
                                }
                            ]
                        }]
                }]
        });
    }
    win.show();

    if (id > 0) {
        Ext.getCmp('MessageForm_' + id).getForm().load();
    }
}

function PrintOutPdf(project, start_date, end_date) {

    var win = Ext.getCmp('PrintOutPdf');
    if (!win) {
        win = new Ext.Window({
            id: 'PrintOutPdf',
            constrainHeader: true,
            plain: true,
            frame: true,
            layout: 'fit',
            title: 'print_report',
            iconCls: 'icon-Info',
            width: 850,
            height: 500,
            html: '<iframe style="overflow:auto;width:100%;height:100%;" frameborder="0" src="/handlers/print_outPDF.php?start_date=' + start_date + '&end_date=' + end_date + '&project=' + project + '"></iframe>'
        });
    }
    win.show();
}

function AVGcheck() {
    var windo = Ext.getCmp('AVGTimeWindow');
    if (!windo) {
        var z_store2 = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            proxy: {
                type: 'ajax',
                url: '/handlers/get_staffStat.php',
                reader: {
                    type: 'json',
                    root: 'data',
                    idProperty: 'id',
                    messageProperty: 'message'
                }
            },
            storeId: 'StoreAVG',
            fields: [
                {name: 'siteAVG', type: 'numeric'},
                {name: 'time', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'payAVG', type: 'numeric'},
                {name: 'allAVG', type: 'numeric'},
                {name: 'cancelAVG', type: 'numeric'}
            ]
        });

        StatStorePay = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            pageSize: 100,
            autoSync: true,
            proxy: {type: 'ajax', url: '/handlers/get_staffStat.php', simpleSortMode: true,
                reader: {type: 'json', successProperty: 'success', idProperty: 'id', root: 'data', messageProperty: 'message'}
            },
            storeId: 'StatStorePay',
            fields: [
                {name: 'id', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'siteAVG', type: 'numeric'},
                {name: 'payAVG', type: 'string'},
                {name: 'allAVG', type: 'string'},
                {name: 'cancelAVG', type: 'string'}
            ]
        });

        var StatGridPay = new Ext.grid.GridPanel({
            autoScroll: false,
            loadMask: true,
            region: 'center',
            height: 200,
            width: 400,
            id: 'Stat_gridPay',
            stateful: true,
            stateId: 'Stat_gridPay',
            store: z_store2,
            columns: [
                {dataIndex: 'id', width: 60, header: 'ID', hidden: true},
                {dataIndex: 'time', width: 120, header: 'Дата', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'siteAVG', width: 120, header: 'Заработали'},
                {dataIndex: 'payAVG', width: 90, header: 'Расход'},
                {dataIndex: 'allAVG', width: 90, header: 'Доход'},
                {dataIndex: 'cancelAVG', width: 90, header: 'ЗП операторам'}
            ]
        });

        var lineChart = Ext.create('Ext.chart.Chart', {
            xtype: 'chart',
            region: 'south',
            style: 'background:#fff',
            animate: true,
            flex: 1,
            store: z_store2,
            shadow: true,
            legend: {
                position: 'right'
            },
            listeners: {
                render: {
                    fn: function () {
                        z_store2.load();
                    }
                }
            },
            axes: [{
                    type: 'Numeric',
                    position: 'left',
                    fields: ['siteAVG', 'payAVG', 'allAVG', 'cancelAVG'],
                    title: 'Доход | Расход',
                    grid: {
                        odd: {
                            opacity: 1,
                            fill: '#ddd',
                            stroke: '#bbb',
                            'stroke-width': 0.5
                        }
                    }
                }, {
                    grid: true,
                    type: 'Time',
                    position: 'bottom',
                    fields: ['time'],
                    dateFormat: 'Y-m-d',
                    step: [Ext.Date.DAY, 1],
                    title: 'Дата'
                }
            ],
            series: [{
                    type: 'line',
                    highlight: {
                        size: 8,
                        radius: 8
                    },
                    axis: 'left',
                    style: {
                        stroke: '#0000FF',
                        'stroke-width': 2,
                        fill: '#0000FF',
                        opacity: 0.8
                    },
                    xField: ['time'],
                    yField: ['siteAVG'],
                    title: ['Заработали'],
                    tips: {
                        trackMouse: true,
                        width: 250,
                        height: 50,
                        renderer: function (storeItem, item) {
                            this.setTitle(storeItem.get('time').format('Y-m-d') + ' : ' + storeItem.get('siteAVG'));
                        }
                    },
                    markerConfig: {
                        type: 'cross',
                        size: 4,
                        radius: 4,
                        'stroke-width': 0
                    }
                }, {
                    type: 'line',
                    highlight: {
                        size: 4,
                        radius: 4
                    },
                    axis: 'left',
                    style: {
                        stroke: '#81C66E',
                        'stroke-width': 2,
                        fill: '#81C66E',
                        opacity: 0.8
                    },
                    xField: ['time'],
                    yField: ['allAVG'],
                    title: ['Доход'],
                    tips: {
                        trackMouse: true,
                        width: 250,
                        height: 50,
                        renderer: function (storeItem, item) {
                            this.setTitle(storeItem.get('time').format('Y-m-d') + ' : ' + storeItem.get('allAVG'));
                        }
                    },
                    markerConfig: {
                        type: 'cross',
                        size: 4,
                        radius: 4,
                        'stroke-width': 0
                    }
                }, {
                    type: 'line',
                    highlight: {
                        size: 4,
                        radius: 4
                    },
                    axis: 'left',
                    style: {
                        stroke: '#B22222',
                        'stroke-width': 2,
                        fill: '#B22222',
                        opacity: 0.8
                    },
                    xField: ['time'],
                    yField: ['payAVG'],
                    title: ['Расход'],
                    tips: {
                        trackMouse: true,
                        width: 250,
                        height: 50,
                        renderer: function (storeItem, item) {
                            this.setTitle(storeItem.get('time').format('Y-m-d') + ' : ' + storeItem.get('payAVG'));
                        }
                    },
                    markerConfig: {
                        type: 'cross',
                        size: 4,
                        radius: 4,
                        'stroke-width': 0
                    }
                }, {
                    type: 'line',
                    highlight: {
                        size: 4,
                        radius: 4
                    },
                    axis: 'left',
                    style: {
                        stroke: '#E8E117',
                        'stroke-width': 2,
                        fill: '#B22222',
                        opacity: 0.8
                    },
                    xField: ['time'],
                    yField: ['cancelAVG'],
                    title: ['ЗП операторам'],
                    tips: {
                        trackMouse: true,
                        width: 300,
                        height: 50,
                        renderer: function (storeItem, item) {
                            this.setTitle(storeItem.get('time').format('Y-m-d') + ' : ' + storeItem.get('cancelAVG'));
                        }
                    },
                    markerConfig: {
                        type: 'cross',
                        size: 4,
                        radius: 4,
                        'stroke-width': 0
                    }
                }
            ]
        });
        var windo = Ext.create('Ext.Window', {
            id: 'AVGTimeWindow',
            width: 1000,
            height: 550,
            title: 'Доход-расход',
            iconCls: 'staff-list',
            constrainHeader: true,
            plain: true,
            layout: 'border',
            flex: true,
            maximizable: true,
            stateId: 'AVGTimeWindow',
            items: [lineChart, StatGridPay],
            tbar: [{
                    xtype: 'combo',
                    editable: false,
                    forceSelection: true,
                    triggerAction: 'all',
                    queryMode: 'local',
                    width: 140,
                    name: 'country',
                    anchor: '25%',
                    id: 'stat_CountryPay',
                    labelWidth: 40,
                    value: 'kz',
                    store: globalCountriesStore,
                    fieldLabel: 'Страна :',
                    valueField: 'value',
                    displayField: 'value'
                }, {
                    xtype: 'datefield',
                    fieldLabel: 'Дата от',
                    startDay: 1,
                    width: 170,
                    format: 'Y-m-d',
                    labelWidth: 50,
                    itemId: 'date',
                    id: 'stat_StartDatePay',
                    allowBlank: false
                }, {
                    xtype: 'datefield',
                    fieldLabel: 'до',
                    startDay: 1,
                    width: 140,
                    value: new Date().getTime(),
                    format: 'Y-m-d',
                    labelWidth: 20,
                    itemId: 'edate',
                    id: 'stat_EndDatePay',
                    allowBlank: false
                }, {
                    xtype: 'combo',
                    editable: false,
                    forceSelection: true,
                    triggerAction: 'all',
                    queryMode: 'local',
                    width: 160,
                    name: 'offer',
                    anchor: '25%',
                    id: 'stat_offerPay',
                    labelWidth: 40,
                    store: globalOffersStore,
                    fieldLabel: 'Товар',
                    valueField: 'value',
                    displayField: 'value'
                }, {
                    xtype: 'combo',
                    editable: false,
                    forceSelection: true,
                    triggerAction: 'all',
                    queryMode: 'local',
                    name: 'cpa_paymentPay',
                    id: 'cpa_paymentPay',
                    store: globalPartnersStore,
                    fieldLabel: 'Партнерка',
                    valueField: 'id',
                    displayField: 'value'
                }, '->', {
                    xtype: 'button',
                    text: 'Построить',
                    handler: function () {
                        z_store2.load({
                            method: 'post',
                            params: {
                                p1: Ext.getCmp('stat_CountryPay').getValue(),
                                p2: Ext.getCmp('stat_StartDatePay').getValue().format('Y-m-d'),
                                p3: Ext.getCmp('stat_EndDatePay').getValue().format('Y-m-d'),
                                p4: Ext.getCmp('stat_offerPay').getValue(),
                                p5: Ext.getCmp('cpa_paymentPay').getValue()
                            }
                        });
                    }
                }
            ]
        });
    }
    windo.show();
}

function KgzModem() {
    var windo = Ext.getCmp('KgzModem');
    if (!windo) {
        H_store = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            autoSync: true,
            proxy: {
                type: 'ajax',
                url: '/handlers/get_ussd.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'id',
                    root: 'data',
                    messageProperty: 'message'
                }
            },
            storeId: 'HStore',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'text', type: 'string'},
                {name: 'imei', type: 'string'},
                {name: 'date', type: 'string'}
            ]
        });
        var Hfilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'string'},
                {dataIndex: 'text', type: 'string'},
                {dataIndex: 'imei', type: 'string'},
                {dataIndex: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
            ]
        });
        var HGrid = new Ext.grid.GridPanel({
            frame: true,
            autoScroll: false,
            loadMask: true,
            height: 380,
            id: 'Kgz_grid',
            store: H_store,
            features: [Hfilters],
            columns: [
                {dataIndex: 'id', width: 20, header: 'ID'},
                {dataIndex: 'text', width: 540, header: 'Текст', renderer: function (v) {
                        if (v.length < 10)
                            return '<span style="color:red;">Подозрение блокировки</span>';
                        else
                            return v;
                    }},
                {dataIndex: 'imei', width: 140, header: 'IMEI модема'},
                {dataIndex: 'date', width: 120, header: 'Дата'}
            ],
            bbar: new Ext.PagingToolbar({
                store: H_store,
                beforePageText: 'Страница',
                displayMsg: 'Отображается {0} - {1} из {2}',
                afterPageText: 'из {0}',
                displayInfo: true,
                plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [20, 50, 100, 200, 500, 1000]})]
            })
        });
        HGrid.store.load();
        var windo = Ext.create('Ext.Window', {
            id: 'KgzModem',
            width: 850,
            height: 400,
            title: 'Ответы по состоянию счета',
            iconCls: 'staff-list',
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            flex: true,
            maximizable: true,
            stateId: 'KgzModem',
            items: [HGrid]
        });
    }
    windo.show();
}

function CheckHistory() {
    var windo = Ext.getCmp('CheckHistory');
    if (!windo) {
        CH_store = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            autoSync: true,
            proxy: {
                type: 'ajax',
                url: '/handlers/get_chhistory.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'id',
                    root: 'data',
                    messageProperty: 'message'
                }
            },
            storeId: 'СHStore',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'fio_podtv', type: 'string'},
                {name: 'date_podtv', type: 'string'},
                {name: 'fio_ch', type: 'string'},
                {name: 'date_ch', type: 'string'},
                {name: 'st_change', type: 'string'},
                {name: 'kz_deliv', type: 'string'}
            ]
        });
        var CHFilter = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'string'},
                {dataIndex: 'date_ch', type: 'date', dateFormat: 'Y-m-d H:i:s'}
            ]
        });
        var СHGrid = new Ext.grid.GridPanel({
            frame: true,
            autoScroll: false,
            loadMask: true,
            height: 580,
            id: 'СH_grid',
            features: [CHFilter],
            store: CH_store,
            columns: [
                {dataIndex: 'id', width: 80, header: 'ID заказа'},
                {dataIndex: 'fio_podtv', width: 180, header: 'ФИО подтвердил'},
                {dataIndex: 'date_podtv', width: 140, header: 'Дата подтверддения'},
                {dataIndex: 'fio_ch', width: 180, header: 'ФИО поменял'},
                {dataIndex: 'date_ch', width: 140, header: 'Дата поменял'},
                {dataIndex: 'st_change', width: 140, header: 'На что поменял'},
                {dataIndex: 'kz_deliv', width: 130, header: 'Тип доставки'}
            ],
            bbar: [{
                    text: 'Изменения сумм',
                    handler: function () {
                        CH_store.proxy.url = '/handlers/get_chhistory.php?summ=1';
                        CH_store.load();
                    }
                }],
            viewConfig: {
                forceFit: true,
                enableTextSelection: true,
                showPreview: true, // custom property
                enableRowBody: true, // required to create a second, full-width row to show expanded Record data
                getRowClass: function (record, rowIndex, rp, ds) { // rp = rowParams
                    return (record.data.id === 'Оплачен') ? 'inuse' : (record.data.send_status === 'Отказ') ? 'failed' : 'price-red';
                }
            }
        });
        СHGrid.store.load();
        var windo = Ext.create('Ext.Window', {
            id: 'CheckHistory',
            width: 1000,
            height: 460,
            autoScroll: true,
            title: 'История измененны подтверждений',
            iconCls: 'staff-list',
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            flex: true,
            maximizable: true,
            stateId: 'CheckHistory',
            items: [СHGrid]
        });
    }
    windo.show();
}

function createPayment(id) {
    var offers_store = Ext.create('Ext.data.ArrayStore', {
        storeId: 'staff_store',
        proxy: {type: 'ajax', url: '/handlers/get_Store.php?t=1', reader: {type: 'array'}},
        fields: ['id', 'value'],
        initComponent: function () {
            this.addEvents('ready');
        },
        is_ready: function () {
            this.fireEvent('ready', this);
        }
    });
    offers_store.load();
    var prompt;
    if (!prompt) {
        prompt = Ext.create('Ext.window.Window', {
            width: 400,
            height: 320,
            layout: 'form',
            title: 'Выплата',
            items: [{
                    xtype: 'form',
                    width: 380,
                    autoHeight: true,
                    id: 'pay_form',
                    url: '/handlers/get_PayOffer.php?id=' + id,
                    bodyStyle: 'padding: 10px 10px 10px 10px;',
                    labelWidth: 50,
                    defaults: {
                        anchor: '95%',
                        allowBlank: true,
                        msgTarget: 'side'
                    },
                    items: [{
                            xtype: 'combo',
                            editable: false,
                            forceSelection: true,
                            triggerAction: 'all',
                            queryMode: 'local',
                            name: 'offer_id',
                            anchor: '80%',
                            allowBlank: true,
                            store: offers_store,
                            fieldLabel: 'Название оффера',
                            valueField: 'id',
                            displayField: 'value'
                        }, {
                            xtype: 'combo',
                            editable: false,
                            forceSelection: true,
                            triggerAction: 'all',
                            allowBlank: true,
                            queryMode: 'local',
                            anchor: '80%',
                            name: 'country_payment',
                            fieldLabel: 'Локация',
                            store: globalCountriesStore,
                            valueField: 'id',
                            displayField: 'value'
                        }, {
                            xtype: 'datefield',
                            fieldLabel: 'Дата введения',
                            startDay: 1,
                            allowBlank: true,
                            format: 'Y-m-d H:i:s',
                            name: 'date_payment'
                        }, {
                            xtype: 'numberfield',
                            fieldLabel: 'Выплата вебу',
                            width: 150,
                            labelWidth: 50,
                            editable: true,
                            allowBlank: true,
                            name: 'web_payment'
                        }, {
                            xtype: 'numberfield',
                            fieldLabel: 'Цена на ленде',
                            width: 150,
                            labelWidth: 50,
                            editable: true,
                            allowBlank: true,
                            name: 'offer_cost'
                        }, {
                            xtype: 'combo',
                            editable: false,
                            forceSelection: true,
                            triggerAction: 'all',
                            queryMode: 'local',
                            name: 'cpa_payment',
                            anchor: '80%',
                            allowBlank: true,
                            store: globalPartnersStore.ValuesArr,
                            fieldLabel: 'Партнерка',
                            valueField: 'id',
                            displayField: 'value'
                        }],
                    buttons: [{
                            text: 'Сохранить',
                            handler: function () {
                                fp = Ext.getCmp('pay_form');
                                if (fp.getForm().isValid()) {
                                    fdatt = fp.getForm().getValues();
                                    Ext.Ajax.request({
                                        url: '/handlers/set_PayOffer.php?id=' + id,
                                        method: 'POST',
                                        params: {
                                            FormData: Ext.encode(fdatt)
                                        },
                                        success: function (response) {
                                            prompt.close();
                                        },
                                        failure: function (response, opts) {
                                            Ext.Msg.alert('Ошибка!', 'Ошибка отправки');
                                        }
                                    });
                                }
                            }
                        }, {
                            text: 'Отмена',
                            handler: function () {
                                prompt.close();
                            }
                        }
                    ]}]
        });
    }
    prompt.show();
    if (id > 0)
        Ext.getCmp('pay_form').load();
}

function OfferPayment() {
    var windo = Ext.getCmp('OfferPayment');
    if (!windo) {

        var Pay_store = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            autoSync: true,
            proxy: {
                type: 'ajax',
                url: '/handlers/get_OfferPayment.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'id',
                    root: 'data',
                    messageProperty: 'message'
                }
            },
            storeId: 'PayStore',
            fields: [
                {name: 'id_payment', type: 'numeric'},
                {name: 'offer_id', type: 'string'},
                {name: 'country_payment', type: 'string'},
                {name: 'web_payment', type: 'string'},
                {name: 'offer_cost', type: 'string'},
                {name: 'date_payment', type: 'string'},
                {name: 'cpa_payment', type: 'string'}
            ]
        });
        var Payfilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id_payment', type: 'string'},
                {dataIndex: 'date_payment', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'web_payment', type: 'string'},
                {dataIndex: 'offer_id', type: 'list', phpMode: true, options: globalOffersStore.ValuesIdArr},
                {dataIndex: 'cpa_payment', type: 'list', phpMode: true, options: globalPartnersStore.ValuesArr},
                {dataIndex: 'country_payment', type: 'list', phpMode: true, options: globalCountriesStore.ValuesArr}
            ]
        });
        var PayGrid = new Ext.grid.GridPanel({
            frame: true,
            autoScroll: false,
            loadMask: true,
            height: 580,
            id: 'Pay_grid',
            store: Pay_store,
            features: [Payfilters],
            columns: [
                {dataIndex: 'id_payment', width: 80, header: 'ID', hidden: true},
                {dataIndex: 'offer_id', width: 140, header: 'Оффер', renderer: function (v, p, r) {
                        return globalOffersStore.ValuesIdJson[v] ? globalOffersStore.ValuesIdJson[v] : v;
                    }},
                {dataIndex: 'country_payment', width: 120, header: 'Гео'},
                {dataIndex: 'web_payment', width: 180, header: 'Выплата'},
                {dataIndex: 'offer_cost', width: 100, header: 'Цена на ленде'},
                {dataIndex: 'date_payment', width: 140, header: 'Дата установки'},
                {dataIndex: 'cpa_payment', width: 130, header: 'Партнерка', renderer: function (v, p, r) {
                        return globalPartnersStore.ValuesJson[v] ? globalPartnersStore.ValuesJson[v] : v;
                    }
                }
            ],
            listeners: {
                itemdblclick: {
                    fn: function (grid, record, item, index, event) {
                        var record = grid.getStore().getAt(index);
                        createPayment(record.data.id_payment);
                    }
                }
            },
            tbar: [{
                    text: 'Добавить выплату',
                    handler: function () {
                        createPayment(0);
                    }
                }],
            bbar: new Ext.PagingToolbar({
                store: Pay_store,
                beforePageText: 'Страница',
                displayMsg: 'Отображается {0} - {1} из {2}',
                afterPageText: 'из {0}',
                displayInfo: true,
                plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [20, 50, 100, 200, 500, 1000]})]
            })
        });
        PayGrid.store.load();
        var windo = Ext.create('Ext.Window', {
            id: 'OfferPayment',
            width: 1000,
            height: 500,
            autoScroll: true,
            title: 'Выплаты',
            iconCls: 'staff-list',
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            flex: true,
            maximizable: true,
            stateId: 'OfferPayment',
            items: [PayGrid]
        });
    }
    windo.show();
}

function TariffsSettings() {
    var windo = Ext.getCmp('TariffsSettingsWindow');
    if (!windo) {

        var tariffColmnStore = [['e', 'E'], ['g', 'G'], ['i', 'I']];
        var tariffDelivType = [['post', 'Почта'], ['courier', 'Курьер']];

        Ext.define('TariffModel', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'id'},
                {name: 'country'},
                {name: 'deliv_type'},
                {name: 'column'},
                {name: 'date_start', type: 'date'},
                {name: 'staff_id'},
                {name: 'percent'}
            ]
        });

        var TariffStore = new Ext.data.JsonStore({
            model: 'TariffModel',
            autoSync: true,
            autoLoad: true,
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/tariffsSettings.php?method=read',
                    update: '/handlers/tariffsSettings.php?method=update',
                    create: '/handlers/tariffsSettings.php?method=insert',
                    destroy: '/handlers/tariffsSettings.php?method=delete'
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
            sortInfo: {
                field: 'date_start',
                direction: 'DESC'
            },
            pageSize: 20
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

        var TariffGrid = new Ext.grid.GridPanel({
            autoScroll: true,
            store: TariffStore,
            stateful: true,
            loadMask: true,
            forceFit: true,
            stateId: 'TariffGridState',
            plugins: [rowEditing],
            features: [
                new Ext.ux.grid.FiltersFeature({
                    encode: false,
                    local: false,
                    filters: [
                        {dataIndex: 'id', type: 'numeric'},
                        {dataIndex: 'country', type: 'list', phpMode: true, options: globalCountriesStore.ValuesArr},
                        {dataIndex: 'deliv_type', type: 'list', phpMode: true, options: tariffDelivType},
                        {dataIndex: 'column', type: 'list', phpMode: true, options: tariffColmnStore},
                        {dataIndex: 'date_start', type: 'date', dateFormat: 'Y-m-d'},
                        {dataIndex: 'staff_id', type: 'numeric'},
                        {dataIndex: 'percent', type: 'numeric'}
                    ]
                })
            ],
            columns: [
                {dataIndex: 'id', width: 80, header: 'ID', hidden: true},
                {dataIndex: 'country', width: 140, header: 'Локация',
                    editor: new Ext.form.ComboBox({
                        editable: false,
                        triggerAction: 'all',
                        allowBlank: false,
                        mode: 'local',
                        store: globalCountriesStore,
                        valueField: 'id',
                        displayField: 'value'
                    }),
                    renderer: function (v) {
                        return globalCountriesStore.ValuesJson[v] ? globalCountriesStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'deliv_type', width: 140, header: 'Тип доставки',
                    editor: new Ext.form.ComboBox({
                        editable: false,
                        triggerAction: 'all',
                        allowBlank: false,
                        mode: 'local',
                        store: tariffDelivType,
                        valueField: 'id',
                        displayField: 'value'
                    }),
                    renderer: function (v) {
                        return v === 'post' ? 'Почта' : 'Курьер';
                    }
                },
                {dataIndex: 'column', width: 180, header: 'Колонка',
                    editor: new Ext.form.ComboBox({
                        editable: false,
                        triggerAction: 'all',
                        allowBlank: false,
                        mode: 'local',
                        store: tariffColmnStore,
                        valueField: 'id',
                        displayField: 'value'
                    }),
                    renderer: function (v) {
                        return Ext.String.capitalize(v);
                    }
                },
                {dataIndex: 'date_start', width: 140, header: 'Дата начала',
                    editor: new Ext.form.DateField({
                        dateFormat: 'Y-m-d',
                        allowBlank: false
                    }),
                    renderer: Ext.util.Format.dateRenderer('Y-m-d')
                },
                {dataIndex: 'staff_id', width: 140, header: 'Источник',
                    editor: new Ext.form.ComboBox({
                        editable: false,
                        triggerAction: 'all',
                        allowBlank: false,
                        mode: 'local',
                        store: globalPartnersStore,
                        valueField: 'id',
                        displayField: 'value'
                    }),
                    renderer: function (v) {
                        return globalPartnersStore.ValuesJson[v] ? globalPartnersStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'percent', width: 130, header: 'Процент',
                    editor: new Ext.form.NumberField({
                        editable: true,
                        decimalPrecision: 2,
                        allowBlank: false,
                        minValue: 0,
                        step: 0.05,
                        maxValue: 99
                    })
                }
            ],
            viewConfig: {
                forceFit: true,
                preserveScrollOnRefresh: true,
                enableTextSelection: true
            },
            listeners: {
                selectionchange: function (selRowModel, dataModels) {
                    TariffGrid.down('#delete').setDisabled(dataModels.length === 0);
                }
            },
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [{
                            text: 'Добавить',
                            iconCls: 'add',
                            handler: function () {
                                TariffStore.insert(0, new TariffModel());
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
                                    TariffStore.remove(dataModel);
                                    TariffStore.reload();
                                }
                            }
                        }]
                }, {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        new Ext.PagingToolbar({
                            store: TariffStore,
                            beforePageText: 'Страница',
                            displayMsg: 'Отображается {0} - {1} из {2}',
                            afterPageText: 'из {0}',
                            displayInfo: true,
                            plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [20, 50, 100, 200, 500, 1000]})]
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

        var windo = Ext.create('Ext.Window', {
            id: 'TariffsSettingsWindow',
            width: 1000,
            height: 500,
            autoScroll: true,
            title: 'Управление тарифами',
            iconCls: 'fa fa-2x fa-tachometer',
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            flex: true,
            maximizable: true,
            items: [TariffGrid]
        });
    }
    windo.show();
}

Ext.define('BlackModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {name: 'Phone', type: 'string'}
    ]
});

var writerBlack = new Ext.data.JsonWriter({encode: false});

var editorBlack = new Ext.grid.plugin.RowEditing({
    saveText: 'Сохранить',
    clicksToEdit: 2,
    listeners: {canceledit: function () {
            BlackGrid.store.load();
        }}
});

var BlackStore = Ext.create('Ext.data.Store', {
    model: 'BlackModel',
    autoSync: true,
    proxy: {
        type: 'ajax',
        api: {
            read: '/handlers/Blackhandler.php?method=read',
            update: '/handlers/Blackhandler.php?method=update',
            create: '/handlers/Blackhandler.php?method=insert',
            destroy: '/handlers/Blackhandler.php?method=delete'
        },
        reader: {
            type: 'json',
            successProperty: 'success',
            idProperty: 'id',
            root: 'data',
            messageProperty: 'message'
        },
        writer: writerBlack
    }
});

var BlackGrid = new Ext.grid.GridPanel({
    frame: true,
    autoScroll: true,
    height: 580,
    id: 'BlackGrid',
    store: BlackStore,
    plugins: [editorBlack],
    columns: [
        {header: 'id', width: 50, sortable: true, dataIndex: 'id'},
        {header: '№ ЗАЯВКИ', width: 160, sortable: true, dataIndex: 'Phone', editor: new Ext.form.TextField({})}
    ],
    tbar: [{
            text: 'Добавить',
            iconCls: 'add',
            handler: function (btn, ev) {
                Ext.Ajax.request({
                    url: '/handlers/Blackhandler.php',
                    params: {method: 'max'},
                    success: function (response) {
                        var u = {id: parseInt(Ext.JSON.decode(response.responseText)) + 1, Phone: ''};
                        editorBlack.cancelEdit();
                        BlackGrid.getStore().insert(0, u);
                        editorBlack.startEdit(0, 0);
                    },
                    failure: function (response, opts) {
                        Ext.Msg.alert('Ошибка!', 'Ошибка добавления.');
                    }
                });
            }
        }, '-', {
            text: 'Удалить',
            iconCls: 'fa fa-minus-circle',
            handler: function onDelete() {
                var rec = editorBlack.getSelectionModel().getSelection();
                if (!rec) {
                    return false;
                }
                Ext.Msg.confirm('Удаление', 'Реально вкурсе че творишь?',
                        function (btn) {
                            if (btn === 'yes') {
                                editorBlack.store.remove(rec);
                            }
                        }
                );
            }
        }, '-', {
            text: 'Обновить',
            iconCls: 'x-tbar-loading',
            handler: function () {
                BlackGrid.store.load();
            }
        }],
    viewConfig: {
        forceFit: true,
        enableTextSelection: true
    }
});

var SMSStore = new Ext.data.JsonStore({
    autoDestroy: false,
    pageSize: 50,
    proxy: {type: 'ajax', url: '/handlers/get_sms.php', simpleSortMode: true,
        reader: {type: 'json', root: 'data', idProperty: 'id', totalProperty: 'total'}
    },
    remoteSort: true,
    sortInfo: {field: 'date', direction: 'DESC'},
    storeId: 'SMSStore',
    fields: [
        {name: 'id', type: 'numeric'},
        {name: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'text', type: 'string'},
        {name: 'number', type: 'string'}
    ]
});

var SMSGrid = new Ext.grid.GridPanel({
    frame: true,
    autoScroll: true,
    autoDestroy: false,
    height: 580,
    id: 'SMSGrid',
    store: SMSStore,
    columns: [
        {header: 'id', width: 50, sortable: true, dataIndex: 'id'},
        {header: 'Дата', width: 140, sortable: true, dataIndex: 'date'},
        {header: 'Текст', width: 460, sortable: true, dataIndex: 'text'},
        {header: 'Номер телефона', width: 110, sortable: true, dataIndex: 'number'}
    ],
    viewConfig: {
        forceFit: true,
        enableTextSelection: true
    }
});

Ext.define('LoginWindow', {
    extend: 'Ext.Window',
    title: 'Входящие СМС',
    height: 600,
    width: 800,
    layout: 'border',
    items: [SMSGrid]
});

function offerView(id) {
    Ext.Ajax.request({
        url: '/handlers/get_Productdesc.php?id=' + id,
        success: function (response) {
            var prompt;
            if (!prompt) {
                prompt = new Ext.Window({width: 1000, height: 490, modal: true, autoScroll: true, layout: 'form', resizable: true,
                    title: 'Описание продукта',
                    items: [{
                            xtype: 'displayfield',
                            width: 570,
                            style: {
                                background: 'white'
                            },
                            height: 450,
                            autoScroll: true,
                            hideLabel: true,
                            value: response.responseText
                        }]
                });
            }
            prompt.show();
        },
        failure: function (response, opts) {
            Ext.Msg.alert('Ошибка!', 'Ошибка открытия');
        }
    });

}

function showRingsGrid(id, country) {
    Ext.Ajax.request({
        url: '/handlers/show_call' + ([11111111, 66629642, 25937686].indexOf(globalStaffId) > -1 ? '' : '') + '.php?id=' + id + '&country=' + country,
        method: 'POST',
        success: function (response) {
            if (Ext.getCmp('showRingsGridId' + id)) {
                return false;
            }
            Ext.create('Ext.window.Window', {
                title: 'Звонки',
                id: 'showRingsGridId' + id,
                width: 500,
                maxHeight: 400,
                autoScroll: true,
                bodyPadding: 5,
                layout: 'fit',
                html: response.responseText
            }).show();
        },
        failure: function () {
        }
    });
    return false;
}

function showRingsGridAuto(id, country) {
    var tmp = Ext.getCmp('showRingsGridId' + id);
    if (tmp) {
        tmp.destroy();
    }
    showRingsGrid(id, country);
    return false;
}

function showRingsAnket(id, country) {
    Ext.Ajax.request({
        url: 'handlers/show_call' + ([11111111, 66629642, 25937686].indexOf(globalStaffId) > -1 ? '' : '') + '.php?id=' + id + '&country=' + country,
        method: 'POST',
        success: function (response) {
            document.getElementById('calls_' + id).innerHTML = response.responseText;
        },
        failure: function () {
        }
    });
    document.getElementById('calls_' + id).style.overflowY = 'scroll';
    document.getElementById('calls_' + id).style.overflowX = 'scroll';
    document.getElementById('calls_' + id).style.display = 'block';
    document.getElementById('calls_' + id).style.height = '150px';
    Ext.getCmp('MenuForm_' + id).autoScroll = true;
    Ext.getCmp('MenuForm_' + id).doLayout();
    return false;
}

function destroyElArr(delElArr) {
    Ext.Array.each(delElArr, function (val, key) {
        var new_field = Ext.getCmp(val);
        if (new_field) {
            new_field.destroy();
        }
    });
}

var sipsCount, unavailable, inuse, busy, notinuse, ringing, que, win, waiting, taking;
var stop = false;
var additionTplMarkup1 = [
    '<p>Всего: {sipsCount}</p>',
    '<p class="unavailable">Unavailable: {unavailable}</p>',
    '<p class="inuse">In use: {inuse} </p>',
    '<p class="busy">Busy: {busy} </p>',
    '<p class="notinuse">Not in use: {notinuse} </p>',
    '<p class="ringing">Ringing: {ringing} </p>'
];
var progress = new Object;
var someId;

/**
 * Учет персонала / Документы / Загрузить ЗП
 */
function ordrersToDecline() {

    var dialog = Ext.getCmp('windowUploadDataId');
    if (dialog) {
        dialog.show();
        return true;
    }

    Ext.create('Ext.window.Window', {
        id: 'windowUploadDataId',
        title: 'В отмену',
        modal: true,
        resizable: false,
        plain: true,
        autoShow: true,
        width: 440,
        maxHeight: 480,
        layout: 'fit',
        border: false,
        items: [
            {
                xtype: 'form',
                id: 'formUploadData',
                frame: true,
                fileUpload: true,
                autoScroll: true,
                bodyPadding: 5,
                loadMask: true,
                defaults: {
                    anchor: '100%'
                },
                items: [{
                        xtype: 'fileuploadfield',
                        name: 'document',
                        submitValue: true,
                        emptyText: 'Добавьте файл с перечнем ID',
                        fieldLabel: 'Файл' + ' .xls',
                        buttonText: 'Выбрать'
                    }
                ],
                buttons: ['->', {
                        text: 'Upload',
                        xtype: 'button',
                        handler: function () {
                            var form = Ext.getCmp('formUploadData');
                            if (form.getForm().isValid()) {
                                form.setLoading(true, true);
                                form.submit({
                                    url: '/handlers/upload_decline.php',
                                    submitEmptyText: false,
                                    timeout: 30,
                                    success: function (fr, action) {
                                        form.setLoading(false);
                                        Ext.MessageBox.alert('success', action.result.msg);
                                        dialog.close();
                                    },
                                    failure: function (fr, action) {
                                        Ext.MessageBox.alert('error', action.result.msg);
                                        form.setLoading(false);
                                    }
                                });
                            }
                        }
                    }
                ]
            }
        ]
    }).show();
}

function changeOrdrersStaffId(grid) {

    var fstore = grid.getStore();
    var ids_data = [];
    fstore.data.each(function (entry) {
        ids_data.push(parseInt(entry.get('id')));
    });
    var prompt;
    if (!prompt) {
        prompt = Ext.create('Ext.window.Window', {
            title: 'Изменить источник',
            modal: true,
            resizable: false,
            plain: true,
            autoShow: true,
            width: 440,
            maxHeight: 480,
            layout: 'fit',
            border: false,
            items: [
                {
                    xtype: 'form',
                    layout: 'anchor',
                    autoHeight: true,
                    bodyPadding: 10,
                    defaults: {
                        labelWidth: 120,
                        anchor: '95%',
                        allowBlank: false
                    },
                    items: [
                        {
                            xtype: 'combo',
                            editable: false,
                            forceSelection: true,
                            triggerAction: 'all',
                            queryMode: 'local',
                            name: 'staff_id',
                            id: 'newStaffId',
                            store: globalPartnersStore,
                            fieldLabel: 'Источник',
                            valueField: 'id',
                            displayField: 'value'
                        }, {
                            xtype: 'numberfield',
                            fieldLabel: 'Кво ограничения',
                            id: 'stOgrChangeOrdrers',
                            editable: true,
                            minValue: 1,
                            maxValue: 50,
                            value: 1
                        }
                    ],
                    buttons: [
                        {
                            text: 'Изменить',
                            handler: function () {
                                Ext.Ajax.request({
                                    url: '/handlers/change_staff_id.php',
                                    method: 'POST',
                                    params: {
                                        staff_id: Ext.getCmp('newStaffId').getValue(),
                                        ids_data: Ext.JSON.encode(ids_data),
                                        ogr: Ext.getCmp('stOgrChangeOrdrers').getValue()
                                    },
                                    success: function (response) {
                                        prompt.close();
                                        fstore.reload();
                                    },
                                    failure: function (response, opts) {
                                        Ext.Msg.alert('Ошибка!', 'Ошибка сохранения');
                                    }
                                });
                            }
                        }, {
                            text: 'Отмена',
                            handler: function () {
                                prompt.close();
                            }
                        }
                    ]}]
        }).show();
    }
}

function changeLastEditId(grid) {

    var fstore = grid.getStore();
    var ids_data = [];
    fstore.data.each(function (entry) {
        ids_data.push(parseInt(entry.get('id')));
    });
    var prompt;
    if (!prompt) {
        prompt = Ext.create('Ext.window.Window', {
            title: 'Перебить оператора',
            modal: true,
            resizable: false,
            plain: true,
            autoShow: true,
            width: 440,
            maxHeight: 480,
            layout: 'fit',
            border: false,
            items: [
                {
                    xtype: 'form',
                    layout: 'anchor',
                    autoHeight: true,
                    bodyPadding: 10,
                    defaults: {
                        labelWidth: 120,
                        anchor: '95%',
                        allowBlank: false
                    },
                    items: [
                        {
                            xtype: 'combo',
                            editable: false,
                            forceSelection: true,
                            triggerAction: 'all',
                            queryMode: 'local',
                            name: 'staff_id',
                            id: 'newOperatorId',
                            store: globalManagerStore,
                            fieldLabel: 'Оператор',
                            valueField: 'id',
                            displayField: 'value'
                        }, {
                            xtype: 'numberfield',
                            fieldLabel: 'Кво ограничения',
                            id: 'stOgrChangeLastEdit',
                            editable: true,
                            minValue: 1,
                            maxValue: 50,
                            value: 1
                        }
                    ],
                    buttons: [{
                            text: 'Изменить',
                            handler: function () {
                                Ext.Ajax.request({
                                    url: '/handlers/change_last_edit_id.php',
                                    method: 'POST',
                                    params: {
                                        staff_id: Ext.getCmp('newOperatorId').getValue(),
                                        ids_data: Ext.JSON.encode(ids_data),
                                        ogr: Ext.getCmp('stOgrChangeLastEdit').getValue()
                                    },
                                    success: function (response) {
                                        prompt.close();
                                        fstore.reload();
                                    },
                                    failure: function (response, opts) {
                                        Ext.Msg.alert('Ошибка!', 'Ошибка сохранения');
                                    }
                                });
                            }
                        }, {
                            text: 'Отмена',
                            handler: function () {
                                prompt.close();
                            }
                        }
                    ]}]
        }).show();
    }
}

function checkWorkDelivDays(id, isObzvon) {
    console.log('checkWorkDelivDays id=' + id + ', isObzvon = ' + isObzvon);

    var statusKzEl = Ext.getCmp('status_kz' + id);
    var dateDeliveryEl = Ext.getCmp('date_delivery' + id);
    var kzDeliveryEl = Ext.getCmp('kz_delivery' + id);

    var deltaMinDays = 0;
    if (session_admin < 1 && (isObzvon || (session_logist + session_operatorcold + session_operatorrecovery + session_operator) > 0)) {
        deltaMinDays = 1;
    }
    if (session_adminlogist) {
        deltaMinDays = -1;
    }

    if (statusKzEl && ['На доставку', 'Вручить подарок'].indexOf(statusKzEl.getValue()) > -1) {
        if (dateDeliveryEl && kzDeliveryEl) {
            var deliv = kzDeliveryEl.getValue();
            if (statusKzEl && statusKzEl.getValue() === 'На доставку') {
                // этот запрос приходит с сервера с запозданием
                // по-этому перетирает настройки ограничений, установленных ниже
                Ext.Ajax.request({
                    method: 'GET',
                    url: '/handlers/handler_delivery_week_days.php?method=read&filter[0][field]=delivery_type&filter[0][data][type]=string&filter[0][data][value]=' + deliv,
                    success: function (response) {
                        response = Ext.decode(response.responseText);
                        if (response.total > 0) {
                            var days = [];
                            if (response.data[0].sun < 1) {
                                days.push(0);
                            }
                            if (response.data[0].mon < 1) {
                                days.push(1);
                            }
                            if (response.data[0].tue < 1) {
                                days.push(2);
                            }
                            if (response.data[0].wed < 1) {
                                days.push(3);
                            }
                            if (response.data[0].thu < 1) {
                                days.push(4);
                            }
                            if (response.data[0].fri < 1) {
                                days.push(5);
                            }
                            if (response.data[0].sat < 1) {
                                days.push(6);
                            }

                            dateDeliveryEl.setDisabledDays(days);
                            dateDeliveryEl.setMaxValue(new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 6)).format('Y-m-d'));
                        } else {
                            dateDeliveryEl.setDisabledDays([]);
                            dateDeliveryEl.setMaxValue(new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 2)).format('Y-m-d'));
                        }
                    }
                });
            }

            var def = Ext.getCmp('deferred_date' + id);
            if (def) {
                Ext.getCmp('deferred_date' + id).destroy();
            }
            var dgiv = Ext.getCmp('date_give' + id);
            if (dgiv) {
                Ext.getCmp('date_give' + id).destroy();
            }
            dateDeliveryEl.allowBlank = false;
            dateDeliveryEl.setMinValue(new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * deltaMinDays)).format('Y-m-d'));
            if (statusKzEl.getValue() === 'Вручить подарок') {
                dateDeliveryEl.setMaxValue(new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 30)).format('Y-m-d'));
            } else {
                dateDeliveryEl.setMaxValue(new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 2)).format('Y-m-d'));
            }
            dateDeliveryEl.validate();
        }
    } else if (dateDeliveryEl) {
        dateDeliveryEl.allowBlank = true;
        dateDeliveryEl.validate();
        dateDeliveryEl.setMaxValue(new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 2)).format('Y-m-d'));
        if (isObzvon) {
            dateDeliveryEl.setMinValue(new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 0)).format('Y-m-d'));
        }
    }

}

function showInvalid(id, fontSize) {
    console.log('Анкета "Инвалид" id=' + id + ', fontSize = ' + fontSize);

    if (id > 0) {
        var wind = Ext.getCmp('invalid_' + id);

        if (!wind) {

            switch (fontSize) {
                case 1:
                    fontSize = '250%';
                    break;
                case 2:
                    fontSize = '300%';
                    break;
                case 3:
                    fontSize = '350%';
                    break;
                case 4:
                    fontSize = '400%';
                    break;
                default :
                    fontSize = '250%';
                    break;
            }

            wind = Ext.create('Ext.Window', {
                title: 'ID заказа - ' + id,
                id: 'invalid_' + id,
                modal: true,
                height: 600,
                width: 850,
                items: [
                    {
                        xtype: 'form',
                        id: 'InvalidForm_' + id,
                        url: '/handlers/get_menu_obzvon.php?id=' + id,
                        border: false,
                        padding: 10,
                        layout: 'anchor',
                        defaults: {
                            anchor: '100%',
                            labelWidth: 200
                        },
                        items: [
                            {
                                xtype: 'displayfield',
                                fieldLabel: 'ID',
                                name: 'id',
                                labelStyle: 'font-size: ' + fontSize + '; color: black;',
                                fieldStyle: 'font-size: ' + fontSize + '; color: darkgreen;'
                            }, {
                                xtype: 'displayfield',
                                fieldLabel: 'ФИО',
                                name: 'fio',
                                labelStyle: 'font-size: ' + fontSize + '; color: black;',
                                fieldStyle: 'font-size: ' + fontSize + '; color: darkgreen;'
                            }, {
                                xtype: 'displayfield',
                                fieldLabel: 'Продукт',
                                name: 'offer',
                                labelStyle: 'font-size: ' + fontSize + '; color: black;',
                                fieldStyle: 'font-size: ' + fontSize + '; color: darkgreen;'
                            }, {
                                xtype: 'displayfield',
                                fieldLabel: 'Цена',
                                name: 'price',
                                labelStyle: 'font-size: ' + fontSize + '; color: black;',
                                fieldStyle: 'font-size: ' + fontSize + '; color: darkgreen;'
                            }
                        ]
                    }
                ]
            }).show();

            if (id > 0) {
                Ext.getCmp('InvalidForm_' + id).getForm().load({
                });
            }

        } else {
            wind.show();
        }
    } else {
        alert('Invalid value id: ' + id);
    }

    return true;

}

function setReminderReaded(messId) {
    Ext.Ajax.request({
        url: '/handlers/set_MessageRead.php?id=' + messId
    });
}

function onOrderReminderOpen(messId, orderId, country, level) {
    CreateMenuDelivery({order_id: orderId}, country, is_status, 'LogistKZ_P', new_sim, level);
    setReminderReaded(messId);
    var win = Ext.getCmp('MessageViewWindow' + messId);
    if (win) {
        win.close();
    }
}

function onClientReminderOpen(messId, uuId, country) {
    CreateMenuClient(uuId, country);
    setReminderReaded(messId);
    var win = Ext.getCmp('MessageViewWindow' + messId);
    if (win) {
        win.close();
    }
}

function create_MessageViewTask(messData) {
    var id = messData.id;
    if (messData.addData && messData.addData.id) {
        id = messData.addData.id;
    }

    var win = Ext.getCmp('MessageViewWindow' + messData.id);
    if (!win) {

        if (messData.type === 'reminder') {

            win = Ext.create('widget.uxNotification', {
                id: 'MessageViewWindow' + messData.id,
                title: messData.header,
                position: 'br',
                manager: 'reminder',
                iconCls: 'ux-notification-icon-error',
                autoCloseDelay: 10000000,
                spacing: 20,
                useXAxis: true,
                closable: true,
                width: 200,
                slideInDuration: 800,
                slideBackDuration: 1500,
                slideInAnimation: 'elasticIn',
                slideBackAnimation: 'elasticIn',
                html: messData.text,
                listeners: {
                    close: function (panel, eOpts) {
                        if (messData.addData && messData.addData.id) {
                            setReminderReaded(messData.id);
                            return true;
                        }

                    }
                }
            }).show();
        } else {
            Ext.create('Ext.window.Window', {
                id: 'MessageViewWindow' + messData.id,
                constrainHeader: true,
                autoScroll: true,
                xtype: 'window',
                title: messData.header,
                width: 500,
                height: 475,
                layout: 'fit',
                stateId: 'MessageView_all' + messData.id,
                items: [{
                        xtype: 'panel',
                        autoScroll: true,
                        fbar: [{
                                xtype: 'button',
                                text: 'Подтвердить прочтение',
                                handler: function (button) {
                                    fp = Ext.getCmp('MessageForm_' + messData.id);
                                    if (fp.getForm().isValid()) {
                                        fp.getForm().submit({
                                            url: '/handlers/set_MessageRead.php?id=' + messData.id,
                                            waitMsg: 'Жди...',
                                            success: function (fp, action) {
                                                Ext.Msg.alert('Success', 'Ок');
                                                Ext.getCmp('MessageViewWindow' + messData.id).close();
                                            }
                                        });
                                    }
                                }
                            }],
                        items: [{
                                xtype: 'form',
                                id: 'MessageForm_' + messData.id,
                                url: '/handlers/get_Message.php?id=' + messData.id,
                                border: false,
                                labelWidth: 70,
                                padding: 10,
                                items: [
                                    {
                                        xtype: 'displayfield',
                                        fieldLabel: 'Message Id',
                                        anchor: '100%',
                                        name: 'Id'
                                    }, {
                                        xtype: 'displayfield',
                                        fieldLabel: 'Дата отправки',
                                        anchor: '100%',
                                        name: 'Timestamp'
                                    }, {
                                        xtype: 'hidden',
                                        fieldLabel: 'От ',
                                        anchor: '100%',
                                        name: 'From'
                                    }, {
                                        xtype: 'hidden',
                                        fieldLabel: 'date' + "(" + 'read' + ")",
                                        anchor: '100%',
                                        name: 'ReadTimestamp'
                                    }, {
                                        xtype: 'displayfield',
                                        fieldLabel: 'Тема',
                                        anchor: '100%',
                                        name: 'Header'
                                    }, {
                                        xtype: 'displayfield',
                                        fieldLabel: 'Сообщение',
                                        anchor: '100%',
                                        name: 'Text'
                                    }
                                ]
                            }]
                    }]
            }).show();

            Ext.getCmp('MessageForm_' + messData.id).getForm().load();
        }

    } else {
        win.show();
    }
}

function createNewOrder(id, country, params) {
    if (!params.id && !params.uuid) {
        params.id = id;
    }

    Ext.Ajax.request({
        url: '/handlers/create_new_order.php',
        method: 'POST',
        params: params,
        success: function (response, opts) {
            var respObj = Ext.decode(response.responseText);
            if (respObj.new_id && respObj.new_staff_id) {
                if (respObj.new_staff_id === 11113333) {
                    CreateMenuDelivery({order_id: respObj.new_id}, respObj.country, is_status, '', new_sim, 11113333);
                } else {
                    if (country === 'RU' || country === 'ru') {
                        CreateMenuObzvonRus(respObj.new_id, country, true);
                    } else {
                        CreateMenuObzvon(respObj.new_id, country, true);
                    }
                }
            } else {
                Ext.MessageBox.alert('Error', 'Error update status - обратитесть к программистам!');
            }
        },
        failure: function (response, opts) {
            Ext.MessageBox.alert('Error', 'Error occurred during request execution! Please try again!');
        }
    });
}


/**
 * @param {type} formPanel
 * @param {type} url
 * @param {type} method
 * @param {type} sendfields
 * @returns {undefined}
 */
function ajaxSendForm(formPanel, url, method, sendfields) {

    if (formPanel.getForm().isValid()) {
        var formValues = formPanel.getForm().getValues();

        var params = sendfields ? {} : formValues;
        if (sendfields) {
            Ext.Array.each(sendfields, function (name, index) {
                params[name] = formValues[name] ? formValues[name] : null;
            });
        }

        Ext.Ajax.request({
            url: url,
            method: method ? method : 'POST',
            params: params,
            success: function (response, opts) {
                var respObj = Ext.decode(response.responseText);
                if (respObj.msg) {
                    Ext.example.msg('Данные успешно сохранены', '', 'sms');
                }
                return response;
            },
            failure: function (response, opts) {
                var respObj = Ext.decode(response.responseText);
                if (respObj.msg) {
                    Ext.example.msg('Error: ' + respObj.msg, '', 'synth');
                } else {
                    Ext.example.msg('Error: обратитесть к программистам!', '', 'synth');

                }
            }
        });
    }

}

function playAlarm(alarmType) {
    var myaudio = new Audio('/music/alarms/' + alarmType + '.mp3');
    myaudio.play();
}

function playSound(soundType) {
    var myaudio = new Audio('/music/other/' + soundType + '.mp3');
    myaudio.play();
}

function concursNotif(title, html) {
    Ext.create('widget.uxNotification', {
        title: 'title',
        position: 'tr',
        modal: false,
        cls: 'ux-notification-conkurs-window',
//        manager: 'demo1',
//        iconCls: 'ux-notification-icon-error',
        icon: '/images/clear_filters.png',
        autoCloseDelay: 10000000,
//        height: 650,
        autoScroll: true,
        spacing: 20,
        useXAxis: true,
        closable: false,
//        id: 'win-d-conkurs-ost',
        slideInDuration: 800,
        slideBackDuration: 1500,
        slideInAnimation: 'elasticIn',
        slideBackAnimation: 'elasticIn',
        html: html
    }).show();
}

var globalProgressShowed = {};
function progressAction() {

    if ((session_operatorcold + session_operatorrecovery) > 0) {
        var progressDiv = Ext.get('progress-div');
        if (!progressDiv) {
            progressDiv = Ext.DomHelper.insertFirst(document.body, {
                id: 'progress-div',
                width: 400,
                height: 150,
                style: "z-index:30000; background-color:color:rgba(0, 0, 0, 0.5);; width:auto; height:auto; position:absolute; font-size: x-large; right:10px; top:40px; padding:3px; color:red;"
            },
            true);
        }

        Ext.Ajax.request({
            url: '/handlers/get_progress.php',
            method: 'GET',
            success: function (response) {
                var jsonData = Ext.decode(response.responseText);

                var progressDiv = Ext.get('progress-div');
                if (progressDiv && jsonData.progressHTML) {
                    progressDiv.setHTML(jsonData.progressHTML);

                    if (jsonData.progressJS) {
                        if (jsonData.progressJSExecOnes) {
                            if (!globalProgressShowed[jsonData.progressJSExecOnes]) {
                                eval(jsonData.progressJS);
                            }
                            globalProgressShowed[jsonData.progressJSExecOnes] = true;
                        } else {
                            eval(jsonData.progressJS);
                        }
                    }
                }

            },
            failure: function (response, opts) {
                Ext.Msg.alert('Ошибка!', 'Ошибка отправки');
            }
        });
    }
}


Ext.example = function () {
    var msgCt;

    function createBox(t, s) {
        // return ['<div class="msg">',
        //         '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
        //         '<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc"><h3>', t, '</h3>', s, '</div></div></div>',
        //         '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
        //         '</div>'].join('');
        return '<div class="msg ' + Ext.baseCSSPrefix + 'border-box"><h3>' + t + '</h3><p>' + s + '</p></div>';
    }
    return {
        msg: function (title, format, playSound) {
            if (!msgCt) {
                msgCt = Ext.DomHelper.insertFirst(document.body, {id: 'msg-div'}, true);
            }
            if (playSound) {
                playAlarm(playSound);
            }
            var s = Ext.String.format.apply(String, Array.prototype.slice.call(arguments, 1));
            var m = Ext.DomHelper.append(msgCt, createBox(title, s), true);
            m.hide();
            m.slideIn('t').ghost("t", {delay: 1000, remove: true});
        },
        init: function () {
            if (!msgCt) {
                // It's better to create the msg-div here in order to avoid re-layouts
                // later that could interfere with the HtmlEditor and reset its iFrame.
                msgCt = Ext.DomHelper.insertFirst(document.body, {id: 'msg-div'}, true);
            }
        }
    };
}();

function uploadAnkets() {
    // Project Table Model
    Ext.define('ProjectHeaders', {
        extend: 'Ext.data.Model',
        fields: [
            {
                name: 'fieldName',
                type: 'string'
            }, {
                name: 'displayName',
                type: 'string'
            }
        ]
    });
    // Upload to project table
    Ext.create('Ext.window.Window', {
        id: 'windowUploadData',
        title: 'Загрузить базу по проекту',
        modal: true,
        resizable: false,
        plain: true,
        autoShow: true,
        width: 440,
        maxHeight: 480,
        layout: 'fit',
        border: false,
        items: [
            {
                xtype: 'form',
                id: 'formUploadData',
                frame: true,
                fileUpload: true,
                autoScroll: true,
                bodyPadding: 5,
                loadMask: true,
                defaults: {
                    anchor: '100%'
                },
                items: [{
                        xtype: 'fileuploadfield',
                        name: 'document',
                        submitValue: true,
                        emptyText: 'Добавьте файл для загрузки',
                        fieldLabel: 'Файл' + ' .xls',
                        buttonText: 'Выбрать',
                        id: 'formUploadXLS'
                    }, {
                        xtype: 'combo',
                        lazyRender: true,
                        editable: false,
                        allowBlank: true,
                        mode: 'local',
                        name: 'manager',
                        anchor: '100%',
                        width: 200,
                        store: ['hotpartner', 'abc', 'obzvon', 'obzvon_cold', 'new'],
                        fieldLabel: 'Проект',
                        valueField: 'id',
                        displayField: 'value'
                    }
                ],
                buttons: [
                    {
                        text: 'Контроль загрузок',
                        xtype: 'button',
                        handler: function () {
                            load_control();
                        }
                    }, '->', {
                        text: 'Upload',
                        xtype: 'button',
                        handler: function () {
                            var form = Ext.getCmp('formUploadData');

                            if (form.getForm().isValid()) {
                                form.setLoading(true, true);

                                form.submit({
                                    url: '/handlers/uploadQuestionnaire.php',
                                    submitEmptyText: false,
                                    success: function (fr, action) {
                                        form.setLoading(false);
                                        Ext.MessageBox.alert('success', action.result.msg);
                                        Ext.getCmp('windowUploadData').close();
                                    },
                                    failure: function (fr, action) {
                                        Ext.MessageBox.alert('error', action.result.msg);
                                        form.setLoading(false);
                                    }
                                });
                            }
                        }
                    }
                ]
            }
        ]
    });
}

Ext.onReady(Ext.example.init, Ext.example);

function calculateTicketsCount(id, prognosis) {
    console.log('calculateTicketsCount START');
    console.log('prognosis => ' + prognosis);
    if (Ext.getCmp('payed_curr_year_count' + id).getValue()) {
        var offersCount = Ext.getCmp('package' + id).getValue() ? parseInt(Ext.getCmp('package' + id).getValue()) : 0;
        var payedOrdersCount = parseInt(Ext.getCmp('payed_curr_year_count' + id).getValue());
        Ext.Array.each(Ext.getCmp('MenuForm_' + id).query('#dop_tovar_count'), function (item) {
            if (item.getValue()) {
                offersCount += parseInt(item.getValue());
            }
        });
        var itog = offersCount * (payedOrdersCount + (prognosis === true ? 1 : 0));
        var itogStr = payedOrdersCount > 0 ? offersCount + ' * (' + payedOrdersCount + (prognosis === true ? ' + 1' : '') + ') = ' + itog : itog;
        Ext.getCmp('prognosis_tickets_count' + id).setValue(itogStr);
    }

    /////////////////////////////
    if (Ext.getCmp('order_payed_2020_count' + id)) {
        var titleAdd = '';
        var heartCount = parseInt(Ext.getCmp('order_payed_2020_count' + id).getValue());
        if (prognosis) {
            heartCount++;
        }
        if (heartCount > 12) {
            heartCount = 12;
        }
        titleAdd += '&nbsp;&nbsp;&nbsp;&nbsp;';
        for (var i = 1; i <= heartCount; i++) {
            titleAdd += '&#9829;';
        }
        titleAdd += ' (' + heartCount + ')';

//        heartCount = parseInt(Ext.getCmp('order_payed_2020_total' + id).getValue());
//        if (prognosis) {
//            heartCount += parseInt(Ext.getCmp('price' + id).getValue());
//        }
//        heartCount = Math.floor(heartCount / 10000);
//        if (heartCount > 12) {
//            heartCount = 12;
//        }
//        titleAdd += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
//        for (var i = 1; i <= heartCount; i++) {
//            titleAdd += '&#9829;';
//        }
//        titleAdd += ' (' + heartCount + ')';

        if (prognosis) {
            var title = 'ID заказа - ' + id + titleAdd;
        } else {
            var title = '<table border="0"><tr><td style="vertical-align: top;">ID заказа - ' + id + titleAdd + '</td><td style="padding-left: 50px;"><img src="/images/price_table.png"/></td></tr></table>';
        }
        

        Ext.getCmp('windAnket_' + id).setTitle(title);
    }
    /////////////////////////////
    var itogKoef = payedOrdersCount + (prognosis === true ? 1 : 0);
    if (itogKoef > 0 && Ext.getCmp('east' + id)) {
        console.log('itog=>' + itog);

        var podskazkaStr = '<p><b>Если ты оформишь:</b><br/>';
        for (var i = 1; i <= 10; i++) {
            podskazkaStr += i + 'уп * (' + itogKoef + ') = ' + (i * itogKoef) + ' билетов<br/>';
        }
        console.log('podskazkaStr');
        console.log(podskazkaStr);
        Ext.getCmp('east' + id).update(podskazkaStr);
        if (Ext.getCmp('east' + id).getCollapsed()) {
            Ext.getCmp('east' + id).toggleCollapse();
        }

    }
    console.log('calculateTicketsCount END');
}

function checkPost(code) {
    Ext.Ajax.request({
        url: '/handlers/checkPost.php?code=' + code,
        method: 'POST',
        success: function (response) {
            Ext.create('Ext.window.Window', {
                title: 'Отслеживание',
                width: 400,
                maxHeight: 400,
                autoScroll: true,
                bodyPadding: 5,
                layout: 'fit',
                html: response.responseText
            }).show();
        },
        failure: function () {
        }
    });
    return false;
}

function secondsToTime(secs) {
    var hours = Math.floor(secs / (60 * 60));
    var divisor_for_minutes = secs % (60 * 60);
    var minutes = Math.floor(divisor_for_minutes / 60);
    var divisor_for_seconds = divisor_for_minutes % 60;
    var seconds = Math.ceil(divisor_for_seconds);
    var obj = '' + hours + ':' + minutes + ':' + seconds;
    return obj;
}

function delete_file(file) {
    Ext.Ajax.request({
        url: '/handlers/delete_file.php?file=' + file,
        method: 'POST',
        success: function (response) {
            Ext.StoreManager.lookup('StoreRules').reload();
        },
        failure: function () {
        }
    });

    return false;
}

function sendMsg(phone, code) {
    Ext.Ajax.request({
        url: 'handlers/sendpost_msg.php?phone=' + phone + '&code=' + code,
        method: 'POST',
        success: function (response) {
            alert('CMC отправлено')
        },
        failure: function () {
        }
    });
    return false;
}
