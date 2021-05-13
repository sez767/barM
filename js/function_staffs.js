// Вкладка "Сотрудники"
function StaffsTab(tabs) {

    var tab = tabs.queryById('StaffsTab');

    if (tab) {
        tab.show();
    } else {

        // store "Сотрудники"
        var ManagerStore = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            pageSize: 50,
            groupField: 'Responsible',
            autoLoad: true,
            autoSync: true,
            proxy: {
                type: 'ajax',
                url: '/handlers/get_users.php',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'id',
                    root: 'data',
                    totalProperty: 'total',
                    messageProperty: 'message'
                }
            },
            storeId: 'ManagerStore',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'FirstName', type: 'string'},
                {name: 'LastName', type: 'string'},
                {name: 'Position', type: 'string'},
                {name: 'Responsible', type: 'numeric'},
                {name: 'Curator', type: 'numeric'},
                {name: 'Skype', type: 'string'},
                {name: 'Rank', type: 'string'},
                {name: 'PaymentOption', type: 'string'},
                {name: 'PaymentBase', type: 'string'},
                {name: 'Email', type: 'string'},
                {name: 'City', type: 'string'},
                {name: 'Birthday', type: 'string'},
                {name: 'Group', type: 'string'},
                {name: 'team', type: 'string'},
                {name: 'Notes', type: 'string'},
                {name: 'Level', type: 'string'},
                {name: 'Sip', type: 'string'},
                {name: 'Location', type: 'string'},
                {name: 'queues', type: 'string'},
                {name: 'staff_oper_use', type: 'numeric'},
                {name: 'IsResponsible', type: 'bool'},
                {name: 'IsCurator', type: 'bool'},
                {name: 'IsPost', type: 'bool'},
                {name: 'Ban', type: 'bool'},
                {name: 'Type', type: 'bool'},
                {name: 'PasswordChangeDate', type: 'date'},
                {name: 'DismissalDate', type: 'date'}
            ]
        });

        // Фильтра "Сотрудники"
        var ManagerFilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'string'},
                {dataIndex: 'FirstName', type: 'string'},
                {dataIndex: 'Position', type: 'list', phpMode: true, options: globalPositionStore.ValuesArr},
                {dataIndex: 'Responsible', type: 'list', phpMode: true, options: globalResponsibleStore.ValuesArr},
                {dataIndex: 'Curator', type: 'list', phpMode: true, options: globalCuratorStore.ValuesArr},
                {dataIndex: 'Skype', type: 'string'},
                {dataIndex: 'PaymentOption', type: 'numeric'},
                {dataIndex: 'PaymentBase', type: 'numeric'},
                {dataIndex: 'Email', type: 'string'},
                {dataIndex: 'Sip', type: 'string'},
                {dataIndex: 'Location', type: 'list', phpMode: true, options: globalCountriesStore.ValuesArr},
                {
                    dataIndex: 'Group',
                    type: 'list',
                    phpMode: true,
                    options: [
                        ['0', 'Без Группы'],
                        ['1', 'Группа 1'], ['2', 'Группа 2'], ['3', 'Группа 3'], ['4', 'Группа 4'], ['5', 'Группа 5'],
                        ['6', 'Группа 6'], ['7', 'Группа 7'], ['8', 'Группа 8'], ['9', 'Группа 9'], ['10', 'Группа 10'],
                        ['11', 'Группа 11'], ['12', 'Группа 12'], ['13', 'Группа 13'], ['14', 'Группа 14'], ['15', 'Группа 15'],
                        ['16', 'Группа 16'], ['17', 'Группа 17'], ['18', 'Группа 18'], ['19', 'Группа 19'], ['20', 'Группа 20'],
                        ['21', 'Группа 21'], ['22', 'Группа 22'], ['23', 'Группа 23'], ['24', 'Группа 24'], ['25', 'Группа 25'],
                        ['26', 'Группа 26'], ['27', 'Группа 27'], ['28', 'Группа 28'], ['29', 'Группа 29'], ['30', 'Группа 30'],
                        ['31', 'Группа 31'], ['32', 'Группа 32'], ['33', 'Группа 33'], ['34', 'Группа 34'], ['35', 'Группа 35'],
                        ['36', 'Группа 36'], ['37', 'Группа 37'], ['38', 'Группа 38'], ['39', 'Группа 39'], ['40', 'Группа 40'],
                        ['41', 'Группа 41'], ['42', 'Группа 42'], ['43', 'Группа 43'], ['44', 'Группа 44'], ['45', 'Группа 45'],
                        ['46', 'Группа 46'], ['47', 'Группа 47'], ['48', 'Группа 48'], ['49', 'Группа 49'], ['40', 'Группа 40'],
                        ['51', 'Группа 51'], ['52', 'Группа 52'], ['53', 'Группа 53'], ['54', 'Группа 54'], ['55', 'Группа 55'],
                        ['56', 'Группа 56'], ['57', 'Группа 57'], ['58', 'Группа 58'], ['59', 'Группа 59'], ['50', 'Группа 50'],
                        ['61', 'Группа 61'], ['62', 'Группа 62'], ['63', 'Группа 63'], ['64', 'Группа 64'], ['65', 'Группа 65'],
                        ['66', 'Группа 66'], ['67', 'Группа 67'], ['68', 'Группа 68'], ['69', 'Группа 69'], ['60', 'Группа 60'],
                        ['71', 'Группа 71'], ['72', 'Группа 72'], ['73', 'Группа 73'], ['74', 'Группа 74'], ['75', 'Группа 75'],
                        ['76', 'Группа 76'], ['77', 'Группа 77'], ['78', 'Группа 78'], ['79', 'Группа 79'], ['70', 'Группа 70'],
                        ['81', 'Группа 81'], ['82', 'Группа 82'], ['83', 'Группа 83'], ['84', 'Группа 84'], ['85', 'Группа 85'],
                        ['86', 'Группа 86'], ['87', 'Группа 87'], ['88', 'Группа 88'], ['89', 'Группа 89'], ['80', 'Группа 80'],
                        ['91', 'Группа 91'], ['92', 'Группа 92'], ['93', 'Группа 93'], ['94', 'Группа 94'], ['95', 'Группа 95'],
                        ['96', 'Группа 96'], ['97', 'Группа 97'], ['98', 'Группа 98'], ['99', 'Группа 99'], ['100', 'Группа 100']
                    ]
                }, {
                    dataIndex: 'team',
                    type: 'list',
                    phpMode: true,
                    options: [['0', 'Без команды'], ['1', 'Команда 1'], ['2', 'Команда 2'], ['3', 'Команда 3'], ['4', 'Команда 4'], ['5', 'Команда 5'], ['6', 'Команда 6'], ['7', 'Команда 7'], ['8', 'Команда 8'], ['9', 'Команда 9'], ['10', 'Команда 10'], ['11', 'Команда 11'], ['12', 'Команда 12'], ['13', 'Команда 13'], ['14', 'Команда 14'], ['15', 'Команда 15'], ['16', 'Команда 16'], ['17', 'Команда 17'], ['18', 'Команда 18'], ['19', 'Команда 19'], ['20', 'Команда 20'], ['21', 'Команда 21'], ['22', 'Команда 22'], ['23', 'Команда 23'], ['24', 'Команда 24'], ['25', 'Команда 25'], ['26', 'Команда 26'], ['27', 'Команда 27'], ['28', 'Команда 28'], ['29', 'Команда 29'], ['30', 'Команда 30'], ['31', 'Команда 31'], ['32', 'Команда 32'], ['33', 'Команда 33'], ['34', 'Команда 34'], ['35', 'Команда 35'], ['36', 'Команда 36']]
                }, {
                    dataIndex: 'Level',
                    type: 'list',
                    phpMode: true,
                    options: [
                        ['1048576', 'Логист предоплаты'],
                        ['524288', 'Оффлайн островок'],
                        ['262144', 'Бишкек Админ логистики'],
                        ['131072', 'Бишкек логист'],
                        ['65536', 'Оператор Бишкек'],
                        ['32768', 'Оператор посева'],
                        ['16384', 'На опыте'],
                        ['2048', 'WhatsApp оператор'],
                        ['1024', 'Оператор клиники'],
                        ['512', 'Холодный оператор'],
                        ['256', 'Оператор входящей'],
                        ['128', 'Логист почта'],
                        ['64', 'Город админ'],
                        ['32', 'Контроль качества'],
                        ['16', 'Админ логистики Почта'],
                        ['8', 'Админ логистики'],
                        ['4', 'Оператор'],
                        ['2', 'Логист'],
                        ['1', 'Администратор']
                    ]
                },
                {dataIndex: 'Rank', type: 'list', phpMode: true, options: globalRankStore.ValuesArr},
                {dataIndex: 'City', type: 'list', phpMode: true, options: globalDeliveryCouriersStore.ValuesArr},
                {dataIndex: 'staff_oper_use', type: 'numeric'},
                {dataIndex: 'IsResponsible', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'Ban', type: 'boolean', yesText: 'Нет', noText: 'Да'},
                {dataIndex: 'IsCurator', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'IsPost', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'PasswordChangeDate', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'DismissalDate', type: 'date', dateFormat: 'Y-m-d H:i:s'}
            ]
        });

        var groupingSummaryFeature = {
            id: 'staffsGroupingFeatureId',
            ftype: 'grouping',
            enableGroupingMenu: true,
            startCollapsed: true,
            groupHeaderTpl: '{columnName}: {name}'
        };

        // Таб "Сотрудники"
        var ManagerGrid = new Ext.grid.GridPanel({
            frame: true,
            autoScroll: true,
            loadMask: true,
            layout: 'border',
            height: 500,
            features: [
                groupingSummaryFeature,
                ManagerFilters
            ],
            id: 'ManagerGrid',
            store: ManagerStore,
            listeners: {
                viewready: function (grid, eOpts) {
                    var groupingFeature = grid.getView().getFeature('staffsGroupingFeatureId');
                    if (groupingFeature) {
                        groupingFeature.disable();
                    }
                },
                itemdblclick: {
                    fn: function (grid, record, item, index, event) {
                        CreateStaff(record.data.id);
                    }
                }
            },
            columns: [
                {dataIndex: 'id', width: 90, header: 'Логин'},
                {dataIndex: 'FirstName', width: 200, header: 'Фамилия Имя'},
                {dataIndex: 'Position', width: 140, header: 'Должность'},
                {dataIndex: 'Responsible', width: 140, header: 'Ответственный',
                    renderer: function (v, p, r) {
                        return globalResponsibleStore.ValuesJson[v] ? globalResponsibleStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'Curator', width: 140, header: 'Куратор',
                    renderer: function (v, p, r) {
                        return globalCuratorStore.ValuesJson[v] ? globalCuratorStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'Skype', minWidth: 80, flex: 1, header: 'Skype'},
                {dataIndex: 'Rank', minWidth: 100, flex: 1, header: 'Ранг',
                    renderer: function (v, p, r) {
                        return globalRankStore.ValuesJson[v] ? globalRankStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'PaymentOption', minWidth: 80, flex: 1, header: 'Условия, %'},
                {dataIndex: 'PaymentBase', minWidth: 80, flex: 1, header: 'Ставка'},
                {dataIndex: 'Email', minWidth: 80, flex: 1, header: 'E-Mail'},
                {dataIndex: 'City', minWidth: 80, flex: 1, header: 'Город'},
                {dataIndex: 'Group', minWidth: 80, flex: 1, header: 'Группа'},
                {dataIndex: 'staff_oper_use', minWidth: 80, flex: 1, header: 'В работе у'},
                {dataIndex: 'team', minWidth: 80, flex: 1, header: 'Команда'},
                {dataIndex: 'Birthday', width: 120, header: 'Дата рождения'},
                {dataIndex: 'Notes', width: 140, hidden: true, header: 'Описание(должность)'},
                {dataIndex: 'Level', width: 240, header: 'Уровень'},
                {dataIndex: 'Sip', width: 50, header: 'Sip'},
                {dataIndex: 'Location', width: 90, header: 'Location'},
                {dataIndex: 'queues', width: 160, header: 'queues'},
                {dataIndex: 'Ban', width: 160, header: 'Источник трафика',
                    renderer: function (v) {
                        return v > 0 ? 'нет' : 'да';
                    }
                },
                {dataIndex: 'IsResponsible', width: 80, header: 'Является ответственным',
                    renderer: function (v) {
                        return v > 0 ? 'да' : 'нет';
                    }
                },
                {dataIndex: 'IsCurator', width: 80, header: 'Является куратором',
                    renderer: function (v) {
                        return v > 0 ? 'да' : 'нет';
                    }
                },
                {dataIndex: 'IsPost', width: 80, header: 'Почта',
                    renderer: function (v) {
                        return v > 0 ? 'да' : 'нет';
                    }
                },
                {header: 'Дата смены пароля', dataIndex: 'PasswordChangeDate', width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {header: 'Дата удаления', dataIndex: 'DismissalDate', width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {
                    header: 'Работает?',
                    xtype: 'actioncolumn',
                    items: [
                        {
                            icon: '/shared/icons/exit.png',
                            iconCls: 'icon',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.getStore().getAt(rowIndex);
                                Ext.Ajax.request({
                                    url: '/handlers/set_useractive.php?id=' + record.data.id + '&Type=0',
                                    success: function (response, opts) {
                                        ManagerGrid.getStore().reload();
                                    }
                                });
                            }
                        }, {
                            icon: '/images/checkbox_checked.png',
                            iconCls: 'icon',
                            handler: function (grid, rowIndex, colIndex) {
                                var record = grid.getStore().getAt(rowIndex);
                                Ext.Ajax.request({
                                    url: '/handlers/set_useractive.php?id=' + record.data.id + '&Type=1',
                                    success: function (response, opts) {
                                        ManagerGrid.getStore().reload();
                                    }
                                });
                            }
                        }
                    ]
                }
            ],
            tbar: [
                {
                    text: 'Добавить сотрудника',
                    handler: function () {
                        CreateStaff(0);
                    }
                }, {
                    text: 'Присутствие в очередях',
                    handler: function () {
                        var rec = ManagerGrid.getSelectionModel().getSelection();
                        if (!rec[0]) {
                            alert('Выбери сотрудника!');
                            return false;
                        }
                        if (rec[0].data['Sip'].length != 4) {
                            alert('Сначала нужно присвоить Sip!');
                            return false;
                        }
                        CreateQueue(rec[0].data['Sip']);
                    }
                }, {
                    text: 'Управление вебами',
                    handler: function () {
                        var rec = ManagerGrid.getSelectionModel().getSelection();
                        if (!rec[0]) {
                            alert('Выбери источник!');
                            return false;
                        }
                        if (rec[0].data['Ban'] > 0) {
                            alert('Это не источник трафика!');
                            return false;
                        }
                        CreateWebUtil(rec[0].data['id']);
                    }
                }, '->',
                {
                    text: 'Обзвон',
                    icon: '/images/hot-orange.png',
                    handler: get_StatObzvon
                }, {
                    text: 'Конверсия',
                    icon: '/images/money-orange-3_16x16.png',
                    handler: get_StatAc
                }, {
                    text: 'Доходы+ЗП',
                    icon: '/images/money-real-1_16x16.png',
                    handler: get_StatCold
                }, {
                    text: 'Доходы+ЗП Курьерка',
                    icon: '/images/money-real-1_16x16.png',
                    handler: get_StatCur
                }, {
                    text: 'Логистики',
                    icon: '/images/logistic_16x16.png',
                    handler: function () {
                        StatStoreLogAc = new Ext.data.JsonStore({autoDestroy: true, remoteSort: true, pageSize: 100, autoSync: true,
                            proxy: {type: 'ajax', url: '/handlers/get_StatLogistAc.php', simpleSortMode: true,
                                reader: {type: 'json',
                                    successProperty: 'success',
                                    idProperty: 'id',
                                    root: 'data',
                                    messageProperty: 'message'
                                }
                            },
                            storeId: 'StaffStoreLogAc',
                            fields: [
                                {name: 'id', type: 'numeric'},
                                {name: 'whosets', type: 'string'},
                                {name: 'accept', type: 'string'},
                                {name: 'otkaz', type: 'string'},
                                {name: 'opl', type: 'string'},
                                {name: 'price_bablo', type: 'string'},
                                {name: 'avg_check', type: 'string'},
                                {name: 'accept_bablo', type: 'string'},
                                {name: 'bablo', type: 'string'},
                                {name: 'data_bablo', type: 'string'},
                                {name: 'new_bablo', type: 'string'}
                            ]

                        });
                        StatStoreLogAc.load();
                        StatUsagestoreLogAc = new Ext.data.JsonStore({autoDestroy: true, autoLoad: true,
                            proxy: {
                                type: 'ajax',
                                url: '/handlers/get_StatLogistAc.php',
                                reader: {
                                    type: 'json',
                                    root: 'datas',
                                    idProperty: 'id',
                                    totalProperty: 'totals'
                                }
                            },
                            remoteSort: true,
                            storeId: 'StatUsagestoreLogAc',
                            fields: [
                                {name: 'orders', type: 'numeric'},
                                {name: 'status', type: 'string'}
                            ]
                        });
                        var chartLogAc = Ext.create('Ext.chart.Chart', {
                            xtype: 'chart', animate: true,
                            width: 450,
                            height: 280,
                            store: StatUsagestoreLogAc, id: 'StatUsageChartLogAc',
                            shadow: true, insetPadding: 10, theme: 'Base:gradients',
                            series: [{type: 'pie', field: 'orders', donut: false,
                                    tips: {trackMouse: true, width: 200, height: 28,
                                        renderer: function (storeItem, item) {
                                            var total = 0;
                                            StatUsagestoreLogAc.each(function (rec) {
                                                total += rec.get('orders');
                                            });
                                            this.setTitle(storeItem.get('status') + ': ' + Math.round(storeItem.get('orders') / total * 100) + '% (' + storeItem.get('orders') + ')');
                                        }
                                    },
                                    highlight: {segment: {margin: 20}},
                                    label: {field: 'status', display: 'rotate', contrast: true, font: '14px Arial'}
                                }]
                        });
                        var StatGridLogAc = new Ext.grid.GridPanel({
                            frame: true,
                            autoScroll: false,
                            loadMask: true,
                            height: 280,
                            width: 1000,
                            id: 'Stat_gridLogAc',
                            store: StatStoreLogAc,
                            stateId: 'Stat_gridLogAcId',
                            stateful: true,
                            columns: [
                                {dataIndex: 'id', width: 60, header: 'ID', hidden: true},
                                {dataIndex: 'whosets', width: 120, header: 'Оператор'},
                                {dataIndex: 'price_bablo', width: 90, header: 'К-во выкупов'},
                                {dataIndex: 'bablo', width: 120, header: 'На сумму'},
                                {dataIndex: 'bablo', width: 90, header: 'Заработок',
                                    renderer: function (v) {
                                        return (v / 100) * Ext.getCmp('stat_zpLogAc').getValue();
                                    }
                                },
                                {dataIndex: 'avg_check', width: 90, header: 'Средний чек'},
                                {dataIndex: 'opl', width: 90, header: 'Выкуп'},
                                {dataIndex: 'otkaz', width: 90, header: 'Отказ'},
                                {dataIndex: 'all', width: 90, header: 'Все'}
                            ],
                            tbar: [
                                {
                                    xtype: 'combo',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    width: 160,
                                    name: 'country',
                                    anchor: '25%',
                                    id: 'stat_CountryLogAc',
                                    labelWidth: 40,
                                    store: [['kz', 'Казахстан'], ['kzg', 'Киргизия'], ['uz', 'Узбекистан'], ['am', 'Армения'], ['az', 'Азербайджан'], ['md', 'Молдова'], ['ru', 'Россия'], ['ae', 'OAE']],
                                    fieldLabel: 'Страна',
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'Дата от',
                                    startDay: 1,
                                    width: 170,
                                    format: 'Y-m-d',
                                    labelWidth: 50,
                                    value: Ext.Date.format(new Date(), 'Y-m-d'),
                                    id: 'stat_StartDateLogAc',
                                    allowBlank: false
                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'до',
                                    startDay: 1,
                                    width: 150,
                                    format: 'Y-m-d',
                                    labelWidth: 20,
                                    value: Ext.Date.format(new Date(), 'Y-m-d'),
                                    id: 'stat_EndDateLogAc',
                                    allowBlank: false
                                }, {
                                    xtype: 'combo',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    width: 140,
                                    labelWidth: 40,
                                    name: 'stat_operator',
                                    id: 'stat_operatorLogAc',
                                    allowBlank: false,
                                    store: globalOperatorLogistStore,
                                    fieldLabel: 'Опер',
                                    valueField: 'id',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    width: 140,
                                    labelWidth: 40,
                                    queryMode: 'local',
                                    name: 'stat_admin',
                                    id: 'stat_adminLogAc',
                                    allowBlank: true,
                                    store: globalKzAdminStore,
                                    fieldLabel: 'Админ',
                                    valueField: 'id',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    width: 100,
                                    labelWidth: 30,
                                    name: 'kz_delivery',
                                    id: 'stat_kz_deliveryLogAc',
                                    emptyText: 'Тип',
                                    store: ['Вся курьерка', 'Почта'],
                                    valueField: 'id',
                                    displayField: 'value'
                                }, {
                                    xtype: 'textfield',
                                    name: 'stat_zp',
                                    id: 'stat_zpLogAc',
                                    anchor: '100%',
                                    emptyText: 'ЗП',
                                    width: 45,
                                    value: 50,
                                    minValue: 0,
                                    maxValue: 100,
                                    labelWidth: 1,
                                    fieldLabel: ''
                                }, '->', {
                                    xtype: 'button',
                                    text: 'Построить',
                                    handler: function () {
                                        var params = {
                                            p1: Ext.getCmp('stat_CountryLogAc').getValue(),
                                            p2: Ext.getCmp('stat_StartDateLogAc').getValue().format('Y-m-d'),
                                            p3: Ext.getCmp('stat_EndDateLogAc').getValue().format('Y-m-d'),
                                            p4: Ext.getCmp('stat_operatorLogAc').getValue(),
                                            p5: Ext.getCmp('stat_adminLogAc').getValue(),
                                            p6: Ext.getCmp('stat_zpLogAc').getValue(),
                                            p7: Ext.getCmp('stat_kz_deliveryLogAc').getValue()
                                        };
                                        StatStoreLogAc.load({method: 'post',
                                            params: params
                                        });
                                        StatUsagestoreLogAc.load({method: 'post',
                                            params: params
                                        });
                                    }
                                }],
                            viewConfig: {
                                forceFit: true,
                                enableTextSelection: true,
                                showPreview: true,
                                enableRowBody: true,
                                getRowClass: function (record, rowIndex, rp, ds) {

                                }
                            }
                        });

                        Ext.create('widget.uxNotification', {
                            title: 'Данные для ' + globalStaffId,
                            position: 'tc',
                            modal: true,
                            iconCls: 'ux-notification-icon-error',
                            autoCloseDelay: 10000000,
                            height: 650,
                            autoScroll: true,
                            spacing: 20,
                            useXAxis: true,
                            closable: true,
                            id: 'win-statLogAc',
                            slideInDuration: 800,
                            slideBackDuration: 1500,
                            slideInAnimation: 'elasticIn',
                            slideBackAnimation: 'elasticIn',
                            items: [StatGridLogAc, chartLogAc]
                        }).show();
                    }
                }, {
                    text: 'Источники трафика',
                    icon: '/images/resource-1_16x16.png',
                    handler: function () {
                        ManagerStore.proxy.url = '/handlers/get_users.php?show_staff=1';
                        ManagerStore.load();
                    }
                }, {
                    text: 'Показать всех',
                    handler: function () {
                        ManagerStore.proxy.url = '/handlers/get_users.php?show_all=1';
                        ManagerStore.load();
                    }
                }, {
                    text: 'Скрыть удаленных',
                    handler: function () {
                        ManagerStore.proxy.url = '/handlers/get_users.php';
                        ManagerStore.load();
                    }
                }
            ],
            fbar: [
                {
                    text: 'Сбросить ВСЕ пароли',
                    icon: '/images/lock-2_16x16.png',
                    hidden: [11111111, 77777777, 25937686].indexOf(globalStaffId) < 0,
                    handler: function () {
                        Ext.Msg.confirm('Deleting', 'Вы действительно хотите удалить все пароли"?', function (btn) {
                            if (btn === 'yes') {
                                Ext.Ajax.request({
                                    url: '/handlers/clear_user_passwords.php',
                                    params: {
                                        pfgbplfnj: 1
                                    },
                                    success: function (response) {
                                        Ext.Msg.alert('СЧАСТЬЕ!', 'Доступы ко всем аккаунтам были успешно утрачены!!!');
                                    },
                                    failure: function (response, opts) {
                                        Ext.Msg.alert('Ошибка!', 'Ошибка.');
                                    }
                                });
                            }
                        });
                    }
                }, {
                    text: 'Excel',
                    icon: '/shared/icons/excel_16x16.png',
                    handler: function (b, e) {
                        b.up('grid').downloadExcelXml(false, 'StatSotrudnik');
                    }
                }, {
                    text: 'Доходы+ЗП NEW',
                    hidden: [11111111, 77777777].indexOf(globalStaffId) < 0,
                    icon: '/images/money-real-1_16x16.png',
                    handler: get_StatZpNew
                }, '->', {
                    text: 'Статистика звонков',
                    handler: function () {
                        var prompt;
                        if (!prompt) {
                            prompt = new Ext.Window({width: 250, modal: true, layout: 'form', resizable: false,
                                title: 'Статистика звонков',
                                items: [
                                    {
                                        xtype: 'datefield',
                                        fieldLabel: 'От',
                                        startDay: 1,
                                        itemId: 'start_date',
                                        allowBlank: false
                                    }, {
                                        xtype: 'datefield',
                                        fieldLabel: 'До',
                                        startDay: 1,
                                        itemId: 'end_date',
                                        allowBlank: false
                                    }, {
                                        xtype: 'combo',
                                        store: [["8*", "TorgKZ_P"], ["9*", "LogistKZ_P"], ["5*", "PostKZ_P"]],
                                        itemId: 'combo_que_out',
                                        valueField: 'id',
                                        displayField: 'value',
                                        queryMode: 'local',
                                        fieldLabel: 'Направление',
                                        anchor: '100%',
                                        allowBlank: false,
                                        editable: false,
                                        typeAhead: true,
                                        triggerAction: 'all'
                                    }],
                                buttons: [{
                                        text: 'Применить',
                                        handler: function (b) {
                                            start_date = b.ownerCt.ownerCt.getComponent('start_date').getValue();
                                            start_date = start_date.format('Y-m-d');
                                            combo_que_out = b.ownerCt.ownerCt.getComponent('combo_que_out').getValue();
                                            end_date = b.ownerCt.ownerCt.getComponent('end_date').getValue();
                                            end_date = end_date.format('Y-m-d');
                                            if (start_date && end_date) {
                                                prompt.close();
                                                PrintOutPdf(combo_que_out, start_date, end_date);
                                            } else
                                                alert("Выберите период отчета");
                                        }
                                    },
                                    {text: 'Отмена', handler: function () {
                                            prompt.close();
                                        }
                                    }
                                ]
                            });
                        }
                        prompt.show();
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({
                store: ManagerStore,
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
                enableRowBody: true,
                getRowClass: function (record, rowIndex, rp, ds) {
                    if (record.data.Ban === false) {
                        return 'govno';
                    }
                    if (record.data.Type === false) {
                        return 'price-blue';
                    } else {
                        return 'price';
                    }
                }
            }
        });

        tabs.add({
            id: 'StaffsTab',
            constrainHeader: true,
            closable: true,
            layout: {type: 'card'},
            iconCls: 'fa fa-1x fa-male',
            title: '<div style="font-size: 18px; padding-left:15px;">Сотрудники</div>',
            items: [ManagerGrid]
        }).show();

    }

}

/**
 * Добавление/Редактирование сотрудника
 * @param {type} id
 * @returns {undefined}
 */
function CreateStaff(id) {
    var wind = Ext.getCmp('sendo_' + id);

    if (id === 0) {
        var is_v = false;
    } else {
        var is_v = true;
    }

    if (!wind) {
        // Cities
        Ext.define('CourierCityModel', {
            extend: 'Ext.data.Model',
            fields: [
                {
                    name: 'zip',
                    type: 'numeric'
                }, {
                    name: 'city',
                    type: 'string'
                }
            ]
        });

        Ext.create('Ext.data.Store', {
            storeId: 'CourierCityStore',
            model: 'CourierCityModel',
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: '/handlers/get_city_regions.php?type=cities',
                reader: {
                    type: 'json',
                    root: 'data',
                    totalProperty: 'total'
                }
            },
            listeners: {
                load: function (store, records) {
                    store.insert(0, [{
                            city: '--- Нет ---'
                        }]);
                }
            },
            autoLoad: true
        });

        var title = id > 0 ? 'Сотрудник - ' + id : 'Новый сотрудник';

        var wind = Ext.create('Ext.Window', {
            title: title,
            id: 'sendm_' + id,
            modal: true,
            height: 520,
            width: 600,
            layout: 'fit',
            items: [{
                    xtype: 'panel',
                    autoScroll: true,
                    fbar: [{
                            id: 'mbutton' + id,
                            xtype: 'button',
                            text: 'Сохранить',
                            handler: function (button) {
                                fp = Ext.getCmp('ManagerForm_Form_' + id);
                                if (fp.getForm().isValid()) {
                                    fp.getForm().submit({
                                        url: '/handlers/set_user.php?id=' + id,
                                        waitMsg: 'Жди...',
                                        success: function (fp, action) {
                                            Ext.getCmp('sendm_' + id).close();
                                            Ext.getCmp('ManagerGrid').store.reload();
                                        }
                                    });
                                }
                            }
                        }],
                    items: [{
                            xtype: 'form',
                            id: 'ManagerForm_Form_' + id,
                            url: '/handlers/get_user.php?id=' + id,
                            border: false,
                            labelWidth: 120,
                            padding: 10,
                            defaults: {
                                labelWidth: 150,
                                anchor: '100%'
                            },
                            items: [
                                {
                                    xtype: 'hidden',
                                    id: 'created_by' + id,
                                    name: 'created_by'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Фамилия Имя',
                                    id: 'FirstName' + id,
                                    name: 'FirstName'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Должность',
                                    name: 'Position',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    allowBlank: false,
                                    store: globalPositionStore,
                                    displayField: 'value',
                                    valueField: 'value'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Ответственный',
                                    name: 'Responsible',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    allowBlank: true,
                                    store: session_adminsales > 0 ? globalResponsibleStore.ValuesOnlyMeArr : globalResponsibleStore,
                                    valueField: 'id',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Куратор',
                                    name: 'Curator',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    allowBlank: true,
                                    store: globalCuratorStore,
                                    displayField: 'value',
                                    valueField: 'id'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Пароль',
                                    inputType: 'password',
                                    minLength: 6,
                                    maxLength: 32,
                                    vtype: ['password'],
                                    matches: 'NewPassConfirm' + id,
                                    allowBlank: is_v,
                                    id: 'NewPass' + id,
                                    name: 'Password'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Повторить пароль',
                                    inputType: 'password',
                                    minLength: 6,
                                    maxLength: 32,
                                    vtype: 'password',
                                    matches: 'NewPass' + id,
                                    allowBlank: is_v,
                                    id: 'NewPassConfirm' + id,
                                    name: 'Password'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Моб. Номер',
                                    name: 'Phone'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Skype',
                                    name: 'Skype'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'E-Mail',
                                    name: 'Email',
                                    regex: /^((([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z\s?]{2,5}){1,25})*(\s*?;\s*?)*)*$/,
                                    regexText: 'точно проверил раскладку и заполняешь трезвым?',
                                    blankText: 'Введите мыло, медленно'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Город',
                                    name: 'City',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    store: Ext.data.StoreManager.lookup('CourierCityStore'),
                                    displayField: 'city',
                                    valueField: 'city'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Группа',
                                    name: 'Group',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    store: [['0', 'Без Группы'],
                                        ['1', 'Группа 1'], ['2', 'Группа 2'], ['3', 'Группа 3'], ['4', 'Группа 4'], ['5', 'Группа 5'],
                                        ['6', 'Группа 6'], ['7', 'Группа 7'], ['8', 'Группа 8'], ['9', 'Группа 9'], ['10', 'Группа 10'],
                                        ['11', 'Группа 11'], ['12', 'Группа 12'], ['13', 'Группа 13'], ['14', 'Группа 14'], ['15', 'Группа 15'],
                                        ['16', 'Группа 16'], ['17', 'Группа 17'], ['18', 'Группа 18'], ['19', 'Группа 19'], ['20', 'Группа 20'],
                                        ['21', 'Группа 21'], ['22', 'Группа 22'], ['23', 'Группа 23'], ['24', 'Группа 24'], ['25', 'Группа 25'],
                                        ['26', 'Группа 26'], ['27', 'Группа 27'], ['28', 'Группа 28'], ['29', 'Группа 29'], ['30', 'Группа 30'],
                                        ['31', 'Группа 31'], ['32', 'Группа 32'], ['33', 'Группа 33'], ['34', 'Группа 34'], ['35', 'Группа 35'],
                                        ['36', 'Группа 36'], ['37', 'Группа 37'], ['38', 'Группа 38'], ['39', 'Группа 39'], ['40', 'Группа 40'],
                                        ['41', 'Группа 41'], ['42', 'Группа 42'], ['43', 'Группа 43'], ['44', 'Группа 44'], ['45', 'Группа 45'],
                                        ['46', 'Группа 46'], ['47', 'Группа 47'], ['48', 'Группа 48'], ['49', 'Группа 49'], ['40', 'Группа 40'],
                                        ['51', 'Группа 51'], ['52', 'Группа 52'], ['53', 'Группа 53'], ['54', 'Группа 54'], ['55', 'Группа 55'],
                                        ['56', 'Группа 56'], ['57', 'Группа 57'], ['58', 'Группа 58'], ['59', 'Группа 59'], ['50', 'Группа 50'],
                                        ['61', 'Группа 61'], ['62', 'Группа 62'], ['63', 'Группа 63'], ['64', 'Группа 64'], ['65', 'Группа 65'],
                                        ['66', 'Группа 66'], ['67', 'Группа 67'], ['68', 'Группа 68'], ['69', 'Группа 69'], ['60', 'Группа 60'],
                                        ['71', 'Группа 71'], ['72', 'Группа 72'], ['73', 'Группа 73'], ['74', 'Группа 74'], ['75', 'Группа 75'],
                                        ['76', 'Группа 76'], ['77', 'Группа 77'], ['78', 'Группа 78'], ['79', 'Группа 79'], ['70', 'Группа 70'],
                                        ['81', 'Группа 81'], ['82', 'Группа 82'], ['83', 'Группа 83'], ['84', 'Группа 84'], ['85', 'Группа 85'],
                                        ['86', 'Группа 86'], ['87', 'Группа 87'], ['88', 'Группа 88'], ['89', 'Группа 89'], ['80', 'Группа 80'],
                                        ['91', 'Группа 91'], ['92', 'Группа 92'], ['93', 'Группа 93'], ['94', 'Группа 94'], ['95', 'Группа 95'],
                                        ['96', 'Группа 96'], ['97', 'Группа 97'], ['98', 'Группа 98'], ['99', 'Группа 99'], ['100', 'Группа 100']
                                    ],
                                    displayField: 'value',
                                    valueField: 'value'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'В работе у',
                                    name: 'staff_oper_use',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    store: [['0', 'Пусто'],
                                        ['1', 'Оператора 1'], ['2', 'Оператора 2'], ['3', 'Оператора 3'], ['4', 'Оператора 4'], ['5', 'Оператора 5'],
                                        ['6', 'Оператора 6'], ['7', 'Оператора 7'], ['8', 'Оператора 8'], ['9', 'Оператора 9'], ['10', 'Оператора 10'],
                                        ['11', 'Оператора 11'], ['12', 'Оператора 12'], ['13', 'Оператора 13'], ['14', 'Оператора 14'], ['15', 'Оператора 15'],
                                        ['16', 'Оператора 16'], ['17', 'Оператора 17'], ['18', 'Оператора 18'], ['19', 'Оператора 19'], ['20', 'Оператора 20'],
                                        ['21', 'Оператора 21'], ['22', 'Оператора 22'], ['23', 'Оператора 23'], ['24', 'Оператора 24'], ['25', 'Оператора 25'],
                                        ['26', 'Оператора 26'], ['27', 'Оператора 27'], ['28', 'Оператора 28'], ['29', 'Оператора 29'], ['30', 'Оператора 30'],
                                        ['31', 'Оператора 31'], ['32', 'Оператора 32'], ['33', 'Оператора 33'], ['34', 'Оператора 34'], ['35', 'Оператора 35'],
                                        ['36', 'Оператора 36'], ['37', 'Оператора 37'], ['38', 'Оператора 38'], ['39', 'Оператора 39'], ['40', 'Оператора 40'],
                                        ['41', 'Оператора 41'], ['42', 'Оператора 42'], ['43', 'Оператора 43'], ['44', 'Оператора 44'], ['45', 'Оператора 45'],
                                        ['46', 'Оператора 46'], ['47', 'Оператора 47'], ['48', 'Оператора 48'], ['49', 'Оператора 49'], ['40', 'Оператора 40'],
                                        ['51', 'Оператора 51'], ['52', 'Оператора 52'], ['53', 'Оператора 53'], ['54', 'Оператора 54'], ['55', 'Оператора 55'],
                                        ['56', 'Оператора 56'], ['57', 'Оператора 57'], ['58', 'Оператора 58'], ['59', 'Оператора 59'], ['50', 'Оператора 50'],
                                        ['61', 'Оператора 61'], ['62', 'Оператора 62'], ['63', 'Оператора 63'], ['64', 'Оператора 64'], ['65', 'Оператора 65'],
                                        ['66', 'Оператора 66'], ['67', 'Оператора 67'], ['68', 'Оператора 68'], ['69', 'Оператора 69'], ['60', 'Оператора 60'],
                                        ['71', 'Оператора 71'], ['72', 'Оператора 72'], ['73', 'Оператора 73'], ['74', 'Оператора 74'], ['75', 'Оператора 75'],
                                        ['76', 'Оператора 76'], ['77', 'Оператора 77'], ['78', 'Оператора 78'], ['79', 'Оператора 79'], ['70', 'Оператора 70'],
                                        ['81', 'Оператора 81'], ['82', 'Оператора 82'], ['83', 'Оператора 83'], ['84', 'Оператора 84'], ['85', 'Оператора 85'],
                                        ['86', 'Оператора 86'], ['87', 'Оператора 87'], ['88', 'Оператора 88'], ['89', 'Оператора 89'], ['80', 'Оператора 80'],
                                        ['91', 'Оператора 91'], ['92', 'Оператора 92'], ['93', 'Оператора 93'], ['94', 'Оператора 94'], ['95', 'Оператора 95'],
                                        ['96', 'Оператора 96'], ['97', 'Оператора 97'], ['98', 'Оператора 98'], ['99', 'Оператора 99'], ['100', 'Оператора 100']
                                    ],
                                    displayField: 'value',
                                    valueField: 'value'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Отдел продаж',
                                    name: 'team',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    store: [['0', 'Без команды'], ['1', 'Команда 1'], ['2', 'Команда 2'], ['3', 'Команда 3'], ['4', 'Команда 4'], ['5', 'Команда 5'], ['6', 'Команда 6'], ['7', 'Команда 7'], ['8', 'Команда 8'], ['9', 'Команда 9'], ['10', 'Команда 10'], ['11', 'Команда 11'], ['12', 'Команда 12'], ['13', 'Команда 13'], ['14', 'Команда 14'], ['15', 'Команда 15'], ['16', 'Команда 16'], ['17', 'Команда 17'], ['18', 'Команда 18'], ['19', 'Команда 19'], ['20', 'Команда 20'], ['21', 'Команда 21'], ['22', 'Команда 22'], ['23', 'Команда 23'], ['24', 'Команда 24'], ['25', 'Команда 25'], ['26', 'Команда 26'], ['27', 'Команда 27'], ['28', 'Команда 28'], ['29', 'Команда 29'], ['30', 'Команда 30'], ['31', 'Команда 31'], ['32', 'Команда 32'], ['33', 'Команда 33'], ['34', 'Команда 34'], ['35', 'Команда 35'], ['36', 'Команда 36']],
                                    displayField: 'value',
                                    valueField: 'value'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Ранг',
                                    name: 'Rank',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    queryMode: 'local',
                                    store: globalRankStore,
                                    displayField: 'value',
                                    valueField: 'id'
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Условия, %',
                                    name: 'PaymentOption',
                                    allowBlank: false,
                                    decimalPrecision: 0,
                                    step: 1
                                }
                                , {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Ставка',
                                    name: 'PaymentBase',
                                    allowBlank: false,
                                    decimalPrecision: 0,
                                    step: 1
                                }, {
                                    xtype: 'checkbox',
                                    fieldLabel: 'Является ответственным',
                                    inputValue: '1',
                                    uncheckedValue: '0',
                                    name: 'IsResponsible'
                                }, {
                                    xtype: 'checkbox',
                                    fieldLabel: 'Является куратором',
                                    inputValue: '1',
                                    uncheckedValue: '0',
                                    name: 'IsCurator'
                                }, {
                                    xtype: 'checkbox',
                                    fieldLabel: 'Почта',
                                    inputValue: '1',
                                    uncheckedValue: '0',
                                    hidden: [11111111, 77777777, 63077972, 72758398, 13131313, 25937686].indexOf(globalStaffId) < 0,
                                    name: 'IsPost'
                                }, {
                                    xtype: 'fieldset',
                                    title: 'Права пользователя',
                                    padding: 5,
                                    items: [
                                        {
                                            layout: 'column',
                                            border: false,
                                            defaults: {
                                                columnWidth: 0.5,
                                                layout: 'form',
                                                border: false,
                                                labelWidth: 150,
                                                xtype: 'panel',
                                                bodyStyle: 'padding:0 12px 0 0'
                                            },
                                            items: [{
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'admin' + id,
                                                    fieldLabel: 'Администратор',
//                                                    hidden: session_adminsales > 0,
                                                    hidden: [25937686, 63077972].indexOf(globalStaffId) < 0 || session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'admin'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'operator' + id,
                                                    fieldLabel: 'Оператор',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'operator'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'logist' + id,
                                                    fieldLabel: 'Логист',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'logist'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'adminlogist' + id,
                                                    fieldLabel: 'Админ логистики',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'adminlogist'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'postlogist' + id,
                                                    fieldLabel: 'Логист почта',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'postlogist'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'admincity' + id,
                                                    fieldLabel: 'Город админ',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'admincity'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'adminlogistpost' + id,
                                                    fieldLabel: 'Админ логистики Почта',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'adminlogistpost'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'logistcity' + id,
                                                    fieldLabel: 'Контроль качества',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'logistcity'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'incomeoper' + id,
                                                    fieldLabel: 'Оператор входящей',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'incomeoper'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'operatorcold' + id,
                                                    fieldLabel: 'Холодный оператор',
                                                    inputValue: '1',
                                                    name: 'operatorcold'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'operatorrecovery' + id,
                                                    fieldLabel: 'Оператор клиники',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'operatorrecovery'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'whatsappoperator' + id,
                                                    fieldLabel: 'WhatsApp оператор',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'whatsappoperator'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'adminsales' + id,
                                                    hidden: [11111111, 25937686, 63077972].indexOf(globalStaffId) < 0 || session_adminsales > 0,
                                                    fieldLabel: 'Админ продаж',
                                                    inputValue: '1',
                                                    name: 'adminsales'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'webmaster' + id,
                                                    fieldLabel: 'Web-мастер',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'webmaster'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'onexperience' + id,
                                                    fieldLabel: 'На опыте',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'onexperience'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'operator_seeding' + id,
                                                    fieldLabel: 'Оператор посева',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'operator_seeding'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'operator_bishkek' + id,
                                                    fieldLabel: 'Оператор Бишкек',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'operator_bishkek'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'bishkek_logist' + id,
                                                    fieldLabel: 'Бишкек логист',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'bishkek_logist'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'bishkek_admin_logist' + id,
                                                    fieldLabel: 'Бишкек Админ логистики',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'bishkek_admin_logist'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'offline_island' + id,
                                                    fieldLabel: 'Оффлайн островок',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'offline_island'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'logistprepayment' + id,
                                                    fieldLabel: 'Логист предоплаты',
                                                    hidden: session_adminsales > 0,
                                                    inputValue: '1',
                                                    name: 'logistprepayment'
                                                }
                                            ]
                                        }
                                    ]
                                }, {
                                    xtype: 'fieldset',
                                    title: 'Доступ по локации',
                                    padding: 5,
                                    items: [{
                                            layout: 'column',
                                            border: false,
                                            defaults: {
                                                columnWidth: 0.5,
                                                layout: 'form',
                                                border: false,
                                                labelWidth: 150,
                                                xtype: 'panel',
                                                bodyStyle: 'padding:0 12px 0 0'
                                            },
                                            items: [{
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'kz' + id,
                                                    fieldLabel: 'Казахстан',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'kz'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'kzg' + id,
                                                    fieldLabel: 'Киргизия',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'kzg'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'ru' + id,
                                                    fieldLabel: 'Россия',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'ru'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'ae' + id,
                                                    fieldLabel: 'OAE',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'ae'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'am' + id,
                                                    fieldLabel: 'Армения',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'am'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'az' + id,
                                                    fieldLabel: 'азербайджан',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'az'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'uz' + id,
                                                    fieldLabel: 'Узбекистан',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'uz'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'it' + id,
                                                    fieldLabel: 'Италия',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'it'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'de' + id,
                                                    fieldLabel: 'Германия',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'de'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'fr' + id,
                                                    fieldLabel: 'Франция',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'fr'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'es' + id,
                                                    fieldLabel: 'Испания',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'es'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'lt' + id,
                                                    fieldLabel: 'Латвия',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'lt'
                                                }, {
                                                    xtype: 'checkbox',
                                                    uncheckedValue: '0',
                                                    id: 'lv' + id,
                                                    fieldLabel: 'Литва',
                                                    inputValue: '1',
                                                    listeners: {
                                                        change: function () {
                                                        }
                                                    },
                                                    name: 'lv'
                                                }
                                            ]
                                        }
                                    ]
                                }, {
                                    xtype: 'combo',
                                    editable: true,
                                    queryMode: 'local',
                                    name: 'Sip',
                                    id: 'sip' + id,
                                    store: Ext.create('Ext.data.ArrayStore', {
                                        storeId: 'oblstore',
                                        proxy: {
                                            type: 'ajax',
                                            url: '/handlers/get_SipStore.php',
                                            reader: {type: 'array'}
                                        },
                                        fields: ['id', 'value'],
                                        autoLoad: true,
                                        initComponent: function () {
                                            this.addEvents('ready');
                                        },
                                        is_ready: function () {
                                            this.fireEvent('ready', this);
                                        }
                                    }),
                                    fieldLabel: 'SIP',
                                    valueField: 'id',
                                    displayField: 'value'
                                }, {
                                    xtype: 'fieldset',
                                    title: 'Доступ по городам',
                                    padding: 2,
                                    items: [
                                        {
                                            xtype: 'panel',
                                            border: 0,
                                            frame: true,
                                            items: [
                                                {
                                                    id: 'delivc' + id,
                                                    xtype: 'checkboxgroup',
                                                    columns: 3
                                                }
                                            ]
                                        }
                                    ]
                                }, {
                                    xtype: 'fieldset',
                                    title: 'Доступ по WEBам',
                                    padding: 2,
                                    items: [{
                                            xtype: 'panel',
                                            border: 0,
                                            frame: true,
                                            items: [{
                                                    id: 'webaccess' + id,
                                                    xtype: 'checkboxgroup',
                                                    columns: 5
                                                }]
                                        }]
                                }, {
                                    xtype: 'fieldset',
                                    title: 'Является источником трафика',
                                    padding: 1,
                                    checkboxToggle: true,
                                    checkboxName: 'Ban',
                                    listeners: {
                                        collapse: function (fieldSet) {
                                            Ext.getCmp('Secret' + id).allowBlank = fieldSet.collapsed;
                                        },
                                        expand: function (fieldSet) {
                                            Ext.getCmp('Secret' + id).allowBlank = fieldSet.collapsed;
                                        }
                                    },
                                    items: [{
                                            border: false,
                                            defaults: {
                                                layout: 'form',
                                                border: false,
                                                labelWidth: 100,
                                                xtype: 'panel',
                                                bodyStyle: 'padding:0 12px 0 0'
                                            },
                                            items: [
                                                {
                                                    xtype: 'textfield',
                                                    id: 'Secret' + id,
                                                    fieldLabel: 'Секретное слово',
                                                    allowBlank: false,
                                                    name: 'Secret',
                                                    maskRe: /[A-Za-z0-9]/,
                                                    regex: /^[A-Za-z0-9\S]{8,20}$/,
                                                    regexText: 'Только латиница и длина ключа не менее 8 символов'
                                                }
                                            ]
                                        }
                                    ]
                                }, {
                                    xtype: 'checkbox',
                                    uncheckedValue: '0',
                                    id: 'predictive' + id,
                                    fieldLabel: 'Работает на предиктиве?',
                                    inputValue: '1',
                                    name: 'Predictive'
                                }, {
                                    xtype: 'hidden',
                                    name: 'Delivers',
                                    id: 'Delivers' + id
                                }, {
                                    xtype: 'hidden',
                                    name: 'staff_id',
                                    id: 'staff_id' + id
                                }
                            ]
                        }]
                }]
        }).show();

        // Load user data
        Ext.getCmp('ManagerForm_Form_' + id)
                .getForm()
                .load({
                    success: function (form, action) {
                        Ext.getCmp('sendm_' + id).setTitle(Ext.getCmp('FirstName' + id).getValue() + ' ID: ' + id);

                        var protectedIds = [25937686, 63077972];
                        if (
                                protectedIds.indexOf(parseInt(Ext.getCmp('created_by' + id).getValue())) > -1 &&
                                protectedIds.indexOf(globalStaffId) < 0
                                ) {
                            Ext.getCmp('FirstName' + id).hide();
                        }

                        if (typeof action.result.data.delivery_access !== 'undefined') {
                            var items = new Array;

                            Ext.Object.each(action.result.data.delivery_access, function (key, item) {
                                item.labelWidth = 'auto';
                                item.labelSeparator = '';
                                item.fieldLabel = '';
                                item.labelStyle = 'font-weight: bold; color: red;';
                                item.name = 'delivers[]';
                                items[key] = Ext.create('Ext.form.field.Checkbox', item);
                            });

                            Ext.getCmp('delivc' + id).add(items);
                        }

                        if (typeof action.result.data.webs !== 'undefined') {

                            var items = new Array;

                            Ext.Object.each(action.result.data.webs, function (key, item) {
                                item.labelWidth = 'auto';
                                item.labelSeparator = '';
                                item.fieldLabel = '';
                                item.width = 100;
                                item.labelStyle = 'font-weight: bold; color: red;';
                                item.name = 'webs[]';

                                items[key] = Ext.create('Ext.form.field.Checkbox', item);
                            });

                            Ext.getCmp('webaccess' + id).add(items);
                        }
                    }
                });
    }
}

function get_StatAc() {

    var StatStoreAc = new Ext.data.JsonStore({
        autoDestroy: true,
        remoteSort: true,
        pageSize: 100,
        autoSync: true,
        proxy: {
            type: 'ajax',
            url: '/handlers/get_StatAc.php',
            simpleSortMode: true,
            reader: {
                type: 'json',
                successProperty: 'success',
                idProperty: 'id',
                root: 'data',
                messageProperty: 'message'
            }
        },
        storeId: 'StaffStoreAc',
        fields: [
            {name: 'id', type: 'numeric'},
            {name: 'all', type: 'numeric'},
            {name: 'news', type: 'numeric'},
            {name: 'whosets', type: 'string'},
            {name: 'accept', type: 'string'},
            {name: 'cancel', type: 'string'},
            {name: 'recall', type: 'string'},
            {name: 'nocall', type: 'string'},
            {name: 'bad', type: 'string'},
            {name: 'otkaz', type: 'string'},
            {name: 'vukup', type: 'string'},
            {name: 'price_bablo', type: 'string'},
            {name: 'avg_check', type: 'string'},
            {name: 'avg_checkm', type: 'string'},
            {name: 'accept_bablo', type: 'string'},
            {name: 'bablo', type: 'string'},
            {name: 'data_bablo', type: 'string'},
            {name: 'new_bablo', type: 'string'},
            {name: 'stat', type: 'string'}
        ]
    }).load();

    var StatUsagesStoreAc = new Ext.data.JsonStore({
        autoDestroy: true,
        autoLoad: true,
        proxy: {
            type: 'ajax',
            url: '/handlers/get_StatAc.php',
            reader: {type: 'json',
                root: 'datas',
                idProperty: 'id',
                totalProperty: 'totals'
            }
        },
        remoteSort: true,
        storeId: 'StatUsagesStoreAc',
        fields: [
            {name: 'orders', type: 'numeric'},
            {name: 'status', type: 'string'}
        ]
    });

    var chartAc = Ext.create('Ext.chart.Chart', {
        xtype: 'chart',
        animate: true,
        width: 450,
        height: 280,
        store: StatUsagesStoreAc,
        id: 'StatUsageChartAc',
        shadow: true,
        insetPadding: 10,
        theme: 'Base:gradients',
        series: [
            {
                type: 'pie',
                field: 'orders',
                donut: false,
                tips: {
                    trackMouse: true,
                    width: 200,
                    height: 28,
                    renderer: function (storeItem, item) {
                        var total = 0;
                        StatUsagesStoreAc.each(function (rec) {
                            total += rec.get('orders');
                        });
                        this.setTitle(storeItem.get('status') + ': ' + Math.round(storeItem.get('orders') / total * 100) + '% (' + storeItem.get('orders') + ')');
                    }
                },
                highlight: {segment: {margin: 20}},
                label: {field: 'status', display: 'rotate', contrast: true, font: '14px Arial'}
            }
        ]
    });

    var statSourceStore = [['', '-Все-'], ['-1', '- Кроме перечисленных -']];
    var whiteStatArr = ['11111111', '22222222', '33333333', '55555555', '47369504'];
    globalPartnersStore.each(function (entry) {
        if (entry.get('id') !== '' && whiteStatArr.indexOf(entry.get('id')) > -1) {
            statSourceStore.push([entry.get('id'), entry.get('value')]);
        }
    });

    var StatGridAc = new Ext.grid.GridPanel({
        frame: true,
        autoScroll: false,
        loadMask: true,
        height: 280,
        width: 970,
        id: 'Stat_gridAc',
        stateful: true,
        stateId: 'Stat_gridAc',
        store: StatStoreAc,
        columns: [
            {dataIndex: 'id', width: 60, header: 'ID', hidden: true},
            {dataIndex: 'whosets', width: 120, header: 'Оператор'},
            {dataIndex: 'accept', width: 90, header: 'Подтвердил'},
            {dataIndex: 'news', width: 90, header: 'Новых'},
            {dataIndex: 'cancel', width: 90, header: 'Отменил'},
            {dataIndex: 'recall', width: 90, header: 'Перезвон'},
            {dataIndex: 'nocall', width: 90, header: 'Недозвоны'},
            {dataIndex: 'bad', width: 90, header: 'Брак'},
            {dataIndex: 'price_bablo', width: 90, header: 'К-во выкупов'},
            {dataIndex: 'bablo', width: 120, header: 'на сумму'},
            {dataIndex: 'data_bablo', width: 90, header: 'Заработок Старый', hidden: true},
            {dataIndex: 'new_bablo', width: 90, header: 'Заработок'},
            {dataIndex: 'accept_bablo', width: 90, header: 'Подтв. на сумму'},
            {dataIndex: 'stat', width: 90, header: 'Средний чек ВЫКУПА!'},
            {dataIndex: 'avg_check', width: 90, header: 'Средний чек'},
            {dataIndex: 'avg_checkm', width: 90, header: 'Средний чек Max'},
            {dataIndex: 'otkaz', width: 90, header: 'Отказ'},
            {dataIndex: 'vukup', width: 90, header: 'Выкуп'},
            {dataIndex: 'all', width: 90, header: 'Все'}
        ],
        tbar: [
            {
                xtype: 'combo',
                editable: false,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                width: 160,
                anchor: '100%',
                id: 'stat_CountryAc',
                labelWidth: 45,
                value: 'kz',
                store: [['kz', 'Казахстан'], ["uz", "Узбекистан"], ['am', 'Армения'], ['az', 'Азербайджан'], ['md', 'Молдова'], ['kzg', 'Киргизия'], ['ru', 'Россия'], ['ae', 'OAE']],
                fieldLabel: 'Страна',
                valueField: 'value',
                displayField: 'value'
            }, {
                xtype: 'splitbutton',
                text: 'Даты',
                menu: [
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Дата от',
                        startDay: 1,
                        width: 145,
                        format: 'Y-m-d',
                        labelWidth: 40,
                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_StartDateAc',
                        allowBlank: false
                    }, {
                        xtype: 'datefield',
                        fieldLabel: 'до',
                        startDay: 1,
                        width: 125,
                        format: 'Y-m-d',
                        labelWidth: 20,
                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_EndDateAc',
                        allowBlank: false
                    }
                ]
            }, {
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 190,
                anchor: '100%',
                id: 'stat_ResponsibleAc',
                labelWidth: 55,
                store: globalResponsibleStore,
                fieldLabel: 'Ответств',
                valueField: 'id',
                displayField: 'value'
            }, {
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 150,
                anchor: '100%',
                id: 'stat_offerAc',
                labelWidth: 40,
                store: globalOffersStore,
                fieldLabel: 'Товар',
                valueField: 'value',
                displayField: 'name'
            }, {
                xtype: 'combo',
                fieldLabel: 'Источник',
                editable: false,
                hidden: [94448321].indexOf(globalStaffId) > -1,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                width: 160,
                anchor: '100%',
                id: 'stat_xolodAc',
                labelWidth: 55,
                store: statSourceStore,
                valueField: 'id',
                displayField: 'name'
            }, {
                xtype: 'combo',
                fieldLabel: 'Доставка',
                editable: false,
                hidden: [94448321].indexOf(globalStaffId) > -1,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                width: 160,
                anchor: '100%',
                id: 'stat_kz_deliveryAc',
                labelWidth: 55,
                store: ['Почта', 'Вся курьерка']
            }, '->',
            {
                xtype: 'button',
                text: 'Построить',
                handler: function () {
                    var params = {
                        p1: Ext.getCmp('stat_CountryAc').getValue(),
                        p2: Ext.getCmp('stat_StartDateAc').getValue().format('Y-m-d'),
                        p3: Ext.getCmp('stat_EndDateAc').getValue().format('Y-m-d'),
                        p4: Ext.getCmp('stat_offerAc').getValue(),
                        p100: Ext.getCmp('stat_xolodAc').getValue(),
                        p110: Ext.getCmp('stat_kz_deliveryAc').getValue(),
                        p120: Ext.getCmp('stat_ResponsibleAc').getValue()
                    };
                    StatStoreAc.load({method: 'post',
                        params: params
                    });
                    StatUsagesStoreAc.load({
                        method: 'post',
                        params: params
                    });
                }
            }
        ],
        viewConfig: {
            forceFit: true,
            enableTextSelection: true,
            showPreview: true,
            enableRowBody: true,
            getRowClass: function (record, rowIndex, rp, ds) {

            }
        }
    });

    Ext.create('widget.uxNotification', {
        title: 'Данные для ' + globalStaffId,
        position: 'tc',
        modal: true,
        iconCls: 'ux-notification-icon-error',
        autoCloseDelay: 10000000,
        height: 670,
        autoScroll: true,
        spacing: 20,
        useXAxis: true,
        closable: true,
        id: 'win-statAc',
        slideInDuration: 800,
        slideBackDuration: 1500,
        slideInAnimation: 'elasticIn',
        slideBackAnimation: 'elasticIn',
        items: [StatGridAc, chartAc]
    }).show();
}

function get_StatObzvon() {

    var StatStoreObzvon = new Ext.data.JsonStore({
        autoDestroy: true,
        remoteSort: true,
        pageSize: 100,
        autoSync: true,
        proxy: {
            type: 'ajax',
            url: '/handlers/get_StatObzvon.php',
            simpleSortMode: true,
            reader: {
                type: 'json',
                successProperty: 'success',
                idProperty: 'id',
                root: 'data',
                messageProperty: 'message'
            }
        },
        storeId: 'StaffStoreObzvon',
        fields: [
            {name: 'id', type: 'numeric'},
            {name: 'country', type: 'string'},
            {name: 'responsible', type: 'string'},
            {name: 'express', type: 'numeric'},
            {name: 'curator', type: 'string'},
            {name: 'oper_status', type: 'string'},
            {name: 'operator', type: 'string'},
            {name: 'obr', type: 'numeric'},
            {name: 'od', type: 'numeric'},
            {name: 'dostavka_now', type: 'numeric'},
            {name: 'opl', type: 'numeric'},
            {name: 'vukup', type: 'numeric'},
            {name: 'zp', type: 'numeric'},
            {name: 'bablo', type: 'numeric'}

        ]
    }).load();

    var StatGridObzvon = new Ext.grid.GridPanel({
        frame: true,
        autoScroll: true,
        loadMask: true,
        layout: 'fit',
        forceFit: true,
        id: 'Stat_gridObzvon',
        stateId: 'StatGridObzvonState',
        stateful: true,
        features: [
            {ftype: 'summary', dock: 'top'}
        ],
        store: StatStoreObzvon,
        columns: [
            {dataIndex: 'id', width: 60, header: 'ID', hidden: true},
            {dataIndex: 'country', width: 80, header: 'ГЕО'},
            {dataIndex: 'responsible', width: 120, header: 'Ответственный'},
            {dataIndex: 'curator', width: 120, header: 'Куратор'},
            {dataIndex: 'operator', width: 120, header: 'Оператор'},
            {dataIndex: 'oper_status', width: 90, header: 'Состояние'},
            {dataIndex: 'obr', width: 90, header: 'Обработка', summaryType: 'sum',
                summaryRenderer: function (val) {
                    if (val > 0) {
                        return '<span style="color: #04B404;">' + val + '</span>';
                    } else if (val < 0) {
                        return '<span style="color: #FF0000;">' + val + '</span>';
                    }
                    return val;
                }},
            {dataIndex: 'od', width: 90, header: 'Отложка', summaryType: 'sum',
                summaryRenderer: function (val) {
                    if (val > 0) {
                        return '<span style="color: #04B404;">' + val + '</span>';
                    } else if (val < 0) {
                        return '<span style="color: #FF0000;">' + val + '</span>';
                    }
                    return val;
                }},
            {dataIndex: 'dostavka_now', width: 90, header: 'Доставка сегодня', summaryType: 'sum',
                summaryRenderer: function (val) {
                    if (val > 0) {
                        return '<span style="color: #04B404;">' + val + '</span>';
                    } else if (val < 0) {
                        return '<span style="color: #FF0000;">' + val + '</span>';
                    }
                    return val;
                }},
            {dataIndex: 'opl', width: 90, header: 'ВСЕ ОПЛ', summaryType: 'sum',
                summaryRenderer: function (val) {
                    if (val > 0) {
                        return '<span style="color: #04B404;">' + val + '</span>';
                    } else if (val < 0) {
                        return '<span style="color: #FF0000;">' + val + '</span>';
                    }
                    return val;
                }},
            {dataIndex: 'express', width: 90, header: 'экспрес', summaryType: 'sum',
                summaryRenderer: function (val) {
                    if (val > 0) {
                        return '<span style="color: #04B404;">' + val + '</span>';
                    } else if (val < 0) {
                        return '<span style="color: #FF0000;">' + val + '</span>';
                    }
                    return val;
                }},
            {dataIndex: 'vukup', width: 120, header: '% выкупа'},
            {dataIndex: 'bablo', width: 90, header: 'КАССА', summaryType: 'sum',
                summaryRenderer: function (val) {
                    if (val > 0) {
                        return '<span style="color: #04B404;">' + val + '</span>';
                    } else if (val < 0) {
                        return '<span style="color: #FF0000;">' + val + '</span>';
                    }
                    return val;
                }},
            {dataIndex: 'zp', width: 120, header: 'ЗП', summaryType: 'sum',
                summaryRenderer: function (val) {
                    if (val > 0) {
                        return '<span style="color: #04B404;">' + val + '</span>';
                    } else if (val < 0) {
                        return '<span style="color: #FF0000;">' + val + '</span>';
                    }
                    return val;
                }}
        ],
        tbar: [
            {
                xtype: 'combo',
                editable: false,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                width: 160,
                anchor: '100%',
                id: 'stat_CountryObzvon',
                labelWidth: 45,
                value: 'kz',
                store: [['kz', 'Казахстан'], ["uz", "Узбекистан"], ['am', 'Армения'], ['az', 'Азербайджан'], ['md', 'Молдова'], ['kzg', 'Киргизия'], ['ru', 'Россия'], ['ae', 'OAE']],
                fieldLabel: 'Страна',
                valueField: 'value',
                displayField: 'value'
            }, {
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 190,
                anchor: '100%',
                id: 'stat_ResponsibleObzvon',
                labelWidth: 55,
                store: globalResponsibleStore,
                fieldLabel: 'Ответств',
                valueField: 'id',
                displayField: 'value'
            }, {
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 190,
                anchor: '100%',
                id: 'stat_CuratorObzvon',
                labelWidth: 55,
                store: globalCuratorStore,
                fieldLabel: 'Курватор',
                valueField: 'id',
                displayField: 'value'
            }, {
                xtype: 'combo',
                fieldLabel: 'Оператор',
                editable: false,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                width: 160,
                anchor: '100%',
                id: 'stat_OperObzvon',
                labelWidth: 55,
                store: globalCallOperatorStore.ValuesArr
            }, '->',
            {
                xtype: 'button',
                text: 'Построить',
                handler: function () {
                    var params = {
                        p1: Ext.getCmp('stat_CountryObzvon').getValue(),
                        p5: Ext.getCmp('stat_ResponsibleObzvon').getValue(),
                        p6: Ext.getCmp('stat_CuratorObzvon').getValue(),
                        p4: Ext.getCmp('stat_OperObzvon').getValue()
                    };
                    StatStoreObzvon.load({method: 'post',
                        params: params
                    });
                }
            }
        ],
        viewConfig: {
            forceFit: true,
            enableTextSelection: true,
            showPreview: true,
            enableRowBody: true,
            getRowClass: function (record, rowIndex, rp, ds) {

            }
        }
    });

    Ext.create('widget.uxNotification', {
        title: 'Данные для ' + globalStaffId,
        position: 'tc',
        modal: true,
        iconCls: 'ux-notification-icon-error',
        autoCloseDelay: 10000000,
        layout: 'fit',
        autoScroll: true,
        maximized: true,
        useXAxis: true,
        closable: true,
        id: 'win-statObzvon',
        slideInDuration: 800,
        slideBackDuration: 1500,
        slideInAnimation: 'elasticIn',
        slideBackAnimation: 'elasticIn',
        items: [StatGridObzvon]
    }).show();
}

function get_StatZpNew() {

    Ext.create('widget.uxNotification', {
        title: 'Данные для ' + globalStaffId,
        position: 'tc',
        modal: true,
        iconCls: 'ux-notification-icon-error',
        autoCloseDelay: 10000000,
        height: 300,
        width: 500,
        autoScroll: true,
        layout: 'fit',
        useXAxis: true,
        closable: true,
        slideInDuration: 800,
        slideBackDuration: 1500,
        slideInAnimation: 'elasticIn',
        slideBackAnimation: 'elasticIn',
        items: [
            {
                xtype: 'panel',
                autoScroll: true,
                fbar: [
                    {
                        xtype: 'button',
                        text: 'Построить',
                        handler: function (button) {
                            var fp = Ext.getCmp('StatZpNewForm');
                            if (fp.getForm().isValid()) {
                                var params = {
                                    country: Ext.getCmp('StatZpNewCountry').getValue(),
                                    startDate: Ext.getCmp('StatZpNewStartDate').getValue().format('Y-m-d'),
                                    endDate: Ext.getCmp('StatZpNewEndDate').getValue().format('Y-m-d'),
                                    responsible: Ext.getCmp('StatZpNewResponsible').getValue()
                                };

                                var href = window.location.origin + '/handlers/handler_StatZpNew.php?' + Ext.Object.toQueryString(params);
                                console.log(href);
                                window.open(href, "_blank");

                            }
                        }
                    }
                ],
                items: [
                    {
                        xtype: 'form',
                        id: 'StatZpNewForm',
                        border: false,
                        labelWidth: 120,
                        padding: 10,
                        defaults: {
                            labelWidth: 150,
                            anchor: '100%'
                        },
                        items: [
                            {
                                xtype: 'combo',
                                editable: false,
                                forceSelection: true,
                                triggerAction: 'all',
                                queryMode: 'local',
                                anchor: '100%',
                                id: 'StatZpNewCountry',
                                value: 'kz',
                                store: globalCountriesStore,
                                fieldLabel: 'Страна',
                                valueField: 'id',
                                displayField: 'value',
                                allowBlank: false
                            },
                            {
                                xtype: 'datefield',
                                fieldLabel: 'Дата оплаты от',
                                startDay: 1,
                                format: 'Y-m-d',
                                id: 'StatZpNewStartDate',
                                allowBlank: false
                            }, {
                                xtype: 'datefield',
                                fieldLabel: 'Дата оплаты до',
                                startDay: 1,
                                format: 'Y-m-d',
                                id: 'StatZpNewEndDate',
                                allowBlank: false
                            }, {
                                xtype: 'combo',
                                editable: false,
                                triggerAction: 'all',
                                queryMode: 'local',
                                anchor: '100%',
                                id: 'StatZpNewResponsible',
                                store: globalResponsibleStore,
                                fieldLabel: 'Ответственный',
                                valueField: 'id',
                                displayField: 'value'
                            }
                        ]
                    }
                ]
            }
        ]
    }).show();
}

function get_StatCold() {

    var StatStoreCold = new Ext.data.JsonStore({
        autoDestroy: true,
        remoteSort: true,
        groupField: 'responsible_id',
        autoLoad: true,
        pageSize: 1000,
        autoSync: true,
        proxy: {
            type: 'ajax',
            url: '/handlers/get_StatCold' + (globalStaffId === 11111111 ? '' : '') + '.php',
            simpleSortMode: true,
            reader: {
                type: 'json',
                successProperty: 'success',
                idProperty: 'staff_id',
                root: 'data',
                messageProperty: 'message'
            }
        },
        fields: [
            {name: 'staff_id', type: 'int'},
            {name: 'id', type: 'string'},
            {name: 'responsible_id', type: 'int'},
            {name: 'responsible', type: 'string'},
            {name: 'B_KUR', type: 'int'},
            {name: 'B_POST', type: 'int'},
            {name: 'B_POST_TOTAL', type: 'int'},
            {name: 'B_POST_PREDOPLATA', type: 'int'},
            {name: 'B_BONUS_OBOROTKA', type: 'int'},
            {name: 'B', type: 'int'},
            {name: 'C', type: 'numeric'},
            {name: 'D', type: 'int'},
            {name: 'E', type: 'numeric'},
            {name: 'F', type: 'numeric'},
            {name: 'G', type: 'float'},
            {name: 'H', type: 'float'},
            {name: 'I', type: 'float'},
            {name: 'J', type: 'float'},
            {name: 'P', type: 'float'}
        ]
    });

    var groupingSummaryFeature = {
        ftype: 'groupingsummary',
        enableGroupingMenu: true,
        startCollapsed: true,
        showSummaryRow: true,
        groupHeaderTpl: 'Ответственный: {[globalManagerStore.ValuesJson[[values.rows[0].data.responsible_id]]]}'
    };

    var StatGrid = new Ext.grid.GridPanel({
        autoScroll: true,
        loadMask: true,
        stateful: false,
        store: StatStoreCold,
        columns: [
            {dataIndex: 'staff_id', width: 100, header: 'ID Оператора', hidden: true},
            {dataIndex: 'id', width: 200, header: 'Оператор',
                renderer: function (v, p, r) {
                    return (globalManagerStore.ValuesJson[v]) ? globalManagerStore.ValuesJson[v] : v;
                }
            },
            {dataIndex: 'responsible', width: 200, sortable: false, header: 'Ответственный',
                renderer: function (v) {
                    return globalResponsibleStore.ValuesJson[v] ? globalResponsibleStore.ValuesJson[v] : v;
                }
            },
            {dataIndex: 'B_KUR', width: 120, header: 'Оборотка курьерка', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_POST', width: 120, header: 'Оборотка предоплата почта', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_POST_TOTAL', width: 120, header: 'Цена итого Почта', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_POST_PREDOPLATA', width: 120, header: 'Цена почта предоплата', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_BONUS_OBOROTKA', width: 120, header: 'Бонус оборотка', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B', width: 120, header: 'Оборотка', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'D', width: 100, header: 'Дни, шт', sortable: true, summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'C', width: 100, header: 'Условия, %', sortable: false,
                renderer: function (value, summaryData, dataIndex) {
                    return Ext.util.Format.round(value, 2) + '%';
                }
            },
            {dataIndex: 'E', width: 100, header: 'Ставка оклада', sortable: false},
            {dataIndex: 'F', width: 100, header: 'Бонус, %', sortable: false,
                renderer: function (value, summaryData, dataIndex) {
                    return Ext.util.Format.round(value, 2) + '%';
                }
            },
            {dataIndex: 'G', width: 100, header: 'ЗП процент', sortable: false, summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '%</span>';
                }
            },
            {dataIndex: 'H', width: 100, header: 'ЗП Оклад', sortable: false, summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'I', width: 100, header: 'ЗП Бонус', sortable: false, summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'J', width: 100, header: 'ЗП итого', sortable: false, summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'P', width: 100, header: 'ЗП итого, %', sortable: false, summaryType: 'average',
                renderer: function (value, p, r) {
                    return Ext.util.Format.round(value, 2) + '%';
                },
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '%</span>';

                    var maxVal, lastVal = 0;
                    Ext.Object.each(summaryData, function (k, sValue) {
                        if (dataIndex !== 'P' && sValue > 0) {
                            lastVal = sValue;
                        }

                        if (sValue > maxVal) {
                            maxVal = sValue;
                        }
                    });

                    return '<span style="color: #04B404;">' + Ext.util.Format.round((lastVal > 0) ? lastVal / maxVal * 100 : 0, 2) + '%</span>';
                }
            }
        ],
        features: [
            groupingSummaryFeature
        ],
        tbar: [
            {
                xtype: 'combo',
                editable: false,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                width: 160,
                anchor: '100%',
                id: 'stat_CountryHot',
                labelWidth: 45,
                value: 'kz',
                store: [['kz', 'Казахстан'], ["uz", "Узбекистан"], ['am', 'Армения'], ['az', 'Азербайджан'], ['md', 'Молдова'], ['kzg', 'Киргизия'], ['ru', 'Россия'], ['ae', 'OAE']],
                fieldLabel: 'Страна',
                valueField: 'value',
                displayField: 'value'
            }, {
                xtype: 'splitbutton',
                text: 'Дата оплаты',
                menu: [
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Дата от',
                        startDay: 1,
                        width: 145,
                        format: 'Y-m-d',
                        labelWidth: 40,
//                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_StartDateHot',
                        allowBlank: false
                    }, {
                        xtype: 'datefield',
                        fieldLabel: 'до',
                        startDay: 1,
                        width: 125,
                        format: 'Y-m-d',
                        labelWidth: 20,
//                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_EndDateHot',
                        allowBlank: false
                    }
                ]
            }, {
                xtype: 'splitbutton',
                text: 'Дата предоплаты',
                menu: [
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Дата от',
                        startDay: 1,
                        width: 145,
                        format: 'Y-m-d',
                        labelWidth: 40,
//                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_StartDatePreHot',
                        allowBlank: false
                    }, {
                        xtype: 'datefield',
                        fieldLabel: 'до',
                        startDay: 1,
                        width: 125,
                        format: 'Y-m-d',
                        labelWidth: 20,
//                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_EndDatePreHot',
                        allowBlank: false
                    }
                ]
            }, {
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 225,
                anchor: '100%',
                id: 'stat_ResponsibleHot',
                labelWidth: 55,
                store: globalResponsibleStore,
                fieldLabel: 'Ответств',
                valueField: 'id',
                displayField: 'value'
            }, {
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 200,
                anchor: '100%',
                id: 'stat_KuratorHot',
                labelWidth: 45,
                store: globalCuratorStore,
                fieldLabel: 'Куратор',
                valueField: 'id',
                displayField: 'value'
            },
//            , {
//                xtype: 'combo',
//                editable: false,
//                triggerAction: 'all',
//                queryMode: 'local',
//                width: 200,
//                anchor: '100%',
//                id: 'stat_OfferHot',
//                labelWidth: 40,
//                store: globalOffersStore,
//                fieldLabel: 'Товар',
//                valueField: 'value',
//                displayField: 'name'
//            }
            '->',
            {
                xtype: 'button',
                text: 'Построить',
                handler: function () {
                    var params = {
                        p1: Ext.getCmp('stat_CountryHot').getValue(),
//                        p4: Ext.getCmp('stat_OfferHot').getValue(),
                        p100: Ext.getCmp('stat_ResponsibleHot').getValue(),
                        p110: Ext.getCmp('stat_KuratorHot').getValue()
                    };

                    if (Ext.getCmp('stat_StartDateHot').getValue()) {
                        params.p2 = Ext.getCmp('stat_StartDateHot').getValue().format('Y-m-d');
                    }
                    if (Ext.getCmp('stat_EndDateHot').getValue()) {
                        params.p3 = Ext.getCmp('stat_EndDateHot').getValue().format('Y-m-d');
                    }
                    if (Ext.getCmp('stat_StartDatePreHot').getValue()) {
                        params.p5 = Ext.getCmp('stat_StartDatePreHot').getValue().format('Y-m-d');
                    }
                    if (Ext.getCmp('stat_EndDatePreHot').getValue()) {
                        params.p6 = Ext.getCmp('stat_EndDatePreHot').getValue().format('Y-m-d');
                    }

                    StatStoreCold.load({method: 'post',
                        params: params
                    });
                }
            }
        ],
        fbar: [
            {
                text: 'Excel',
                icon: '/images/excel-6_16x16.png',
                handler: function (b, e) {
                    b.up('grid').downloadExcelXml(false, 'Доходы+ЗП');
                }
            }
        ],
        viewConfig: {
            forceFit: true,
            enableTextSelection: true,
            showPreview: true,
            enableRowBody: true,
            getRowClass: function (record, rowIndex, rp, ds) {

            }
        }
    });

    Ext.create('widget.uxNotification', {
        title: 'Данные для ' + globalStaffId,
        position: 'tc',
        modal: true,
        iconCls: 'ux-notification-icon-error',
        autoCloseDelay: 10000000,
        height: 550,
        width: 900,
        autoScroll: true,
        layout: 'fit',
        useXAxis: true,
        closable: true,
        slideInDuration: 800,
        slideBackDuration: 1500,
        slideInAnimation: 'elasticIn',
        slideBackAnimation: 'elasticIn',
        items: [StatGrid]
    }).show();
}

function get_StatCur() {

    var StatStoreCur = new Ext.data.JsonStore({
        autoDestroy: true,
        remoteSort: true,
        groupField: 'responsible_id',
        autoLoad: true,
        pageSize: 1000,
        autoSync: true,
        proxy: {
            type: 'ajax',
            url: '/handlers/get_StatCur' + (globalStaffId === 11111111 ? '' : '') + '.php',
            simpleSortMode: true,
            reader: {
                type: 'json',
                successProperty: 'success',
                idProperty: 'staff_id',
                root: 'data',
                messageProperty: 'message'
            }
        },
        fields: [
            {name: 'staff_id', type: 'string'},
            {name: 'id', type: 'string'},
            {name: 'responsible_id', type: 'int'},
            {name: 'responsible', type: 'string'},
            {name: 'B_OPLATA', type: 'int'},
            {name: 'B_TOTAL_PRICE_OPLATA', type: 'int'},
            {name: 'B_TOTAL_PRICE_CUR', type: 'int'},
            {name: 'B_PRED_OPLATA', type: 'int'},
            {name: 'B_PART_PRED_OPLATA', type: 'int'},
            {name: 'B_OBOROTKA', type: 'int'},
            {name: 'B_OBOROTKA_BONUS', type: 'int'}
        ]
    });

    var groupingSummaryFeature = {
        ftype: 'groupingsummary',
        enableGroupingMenu: true,
        startCollapsed: true,
        showSummaryRow: true,
        groupHeaderTpl: 'Ответственный: {[globalManagerStore.ValuesJson[[values.rows[0].data.responsible_id]]]}'
    };

    var StatGrid = new Ext.grid.GridPanel({
        autoScroll: true,
        loadMask: true,
        stateful: false,
        store: StatStoreCur,
        columns: [
            {dataIndex: 'staff_id', width: 100, header: 'ID Оператора', hidden: true},
            {dataIndex: 'id', width: 200, header: 'Оператор',
                renderer: function (v, p, r) {
                    return (globalManagerStore.ValuesJson[v]) ? globalManagerStore.ValuesJson[v] : v;
                }
            },
            {dataIndex: 'responsible', width: 200, sortable: false, header: 'Ответственный',
                renderer: function (v) {
                    return globalResponsibleStore.ValuesJson[v] ? globalResponsibleStore.ValuesJson[v] : v;
                }
            },
            {dataIndex: 'B_OPLATA', width: 120, header: 'Оборотка "Без предоплаты"', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_TOTAL_PRICE_OPLATA', width: 120, header: 'Цена итого Почта', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_TOTAL_PRICE_CUR', width: 120, header: 'Цена итого Курьерка', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_PRED_OPLATA', width: 120, header: 'Оборотка "Предоплатные КС"', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_PART_PRED_OPLATA', width: 120, header: 'Цена предоплаты', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_OBOROTKA', width: 120, header: 'Итоговая оборотка', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            },
            {dataIndex: 'B_OBOROTKA_BONUS', width: 120, header: 'Оборотка бонус', summaryType: 'sum',
                summaryRenderer: function (value, summaryData, dataIndex) {
                    return '<span style="color: #04B404;">' + Ext.util.Format.round(value, 2) + '</span>';
                }
            }
        ],
        features: [
            groupingSummaryFeature
        ],
        tbar: [
            {
                xtype: 'combo',
                editable: false,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                width: 160,
                anchor: '100%',
                id: 'stat_CountryHot',
                labelWidth: 45,
                value: 'kz',
                store: [['kz', 'Казахстан'], ["uz", "Узбекистан"], ['am', 'Армения'], ['az', 'Азербайджан'], ['md', 'Молдова'], ['kzg', 'Киргизия'], ['ru', 'Россия'], ['ae', 'OAE']],
                fieldLabel: 'Страна',
                valueField: 'value',
                displayField: 'value'
            }, {
                xtype: 'splitbutton',
                text: 'Дата оплаты',
                menu: [
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Дата от',
                        startDay: 1,
                        width: 145,
                        format: 'Y-m-d',
                        labelWidth: 40,
//                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_StartDateHot',
                        allowBlank: false
                    }, {
                        xtype: 'datefield',
                        fieldLabel: 'до',
                        startDay: 1,
                        width: 125,
                        format: 'Y-m-d',
                        labelWidth: 20,
//                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_EndDateHot',
                        allowBlank: false
                    }
                ]
            }, {
                xtype: 'splitbutton',
                text: 'Дата предоплаты',
                menu: [
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Дата от',
                        startDay: 1,
                        width: 145,
                        format: 'Y-m-d',
                        labelWidth: 40,
//                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_StartDatePreHot',
                        allowBlank: false
                    }, {
                        xtype: 'datefield',
                        fieldLabel: 'до',
                        startDay: 1,
                        width: 125,
                        format: 'Y-m-d',
                        labelWidth: 20,
//                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        id: 'stat_EndDatePreHot',
                        allowBlank: false
                    }
                ]
            }, {
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 225,
                anchor: '100%',
                id: 'stat_ResponsibleHot',
                labelWidth: 55,
                store: globalResponsibleStore,
                fieldLabel: 'Ответств',
                valueField: 'id',
                displayField: 'value'
            }, {
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 200,
                anchor: '100%',
                id: 'stat_KuratorHot',
                labelWidth: 45,
                store: globalCuratorStore,
                fieldLabel: 'Куратор',
                valueField: 'id',
                displayField: 'value'
            },
//             , {
//                xtype: 'combo',
//                editable: false,
//                triggerAction: 'all',
//                queryMode: 'local',
//                width: 200,
//                anchor: '100%',
//                id: 'stat_OfferHot',
//                labelWidth: 40,
//                store: globalOffersStore,
//                fieldLabel: 'Товар',
//                valueField: 'value',
//                displayField: 'name'
//            }
            '->',
            {
                xtype: 'button',
                text: 'Построить',
                handler: function () {
                    var params = {
                        p1: Ext.getCmp('stat_CountryHot').getValue(),
//                        p4: Ext.getCmp('stat_OfferHot').getValue(),
                        p100: Ext.getCmp('stat_ResponsibleHot').getValue(),
                        p110: Ext.getCmp('stat_KuratorHot').getValue()
                    };

                    if (Ext.getCmp('stat_StartDateHot').getValue()) {
                        params.p2 = Ext.getCmp('stat_StartDateHot').getValue().format('Y-m-d');
                    }
                    if (Ext.getCmp('stat_EndDateHot').getValue()) {
                        params.p3 = Ext.getCmp('stat_EndDateHot').getValue().format('Y-m-d');
                    }
                    if (Ext.getCmp('stat_StartDatePreHot').getValue()) {
                        params.p5 = Ext.getCmp('stat_StartDatePreHot').getValue().format('Y-m-d');
                    }
                    if (Ext.getCmp('stat_EndDatePreHot').getValue()) {
                        params.p6 = Ext.getCmp('stat_EndDatePreHot').getValue().format('Y-m-d');
                    }

                    StatStoreCur.load({method: 'post',
                        params: params
                    });
                }
            }
        ],
        fbar: [
            {
                text: 'Excel',
                icon: '/images/excel-6_16x16.png',
                handler: function (b, e) {
                    b.up('grid').downloadExcelXml(false, 'Доходы+ЗП Курьерка');
                }
            }
        ],
        viewConfig: {
            forceFit: true,
            enableTextSelection: true,
            showPreview: true,
            enableRowBody: true,
            getRowClass: function (record, rowIndex, rp, ds) {

            }
        }
    });

    Ext.create('widget.uxNotification', {
        title: 'Данные для ' + globalStaffId,
        position: 'tc',
        modal: true,
        iconCls: 'ux-notification-icon-error',
        autoCloseDelay: 10000000,
        height: 550,
        width: 900,
        autoScroll: true,
        layout: 'fit',
        useXAxis: true,
        closable: true,
        slideInDuration: 800,
        slideBackDuration: 1500,
        slideInAnimation: 'elasticIn',
        slideBackAnimation: 'elasticIn',
        items: [StatGrid]
    }).show();
}

function CreateQueue(id) {
    Ext.define('QueueModel', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'id', type: 'string'},
            {name: 'name', type: 'string'},
            {name: 'accept', type: 'boolean'}
        ]
    });

    var QueueStore = Ext.create('Ext.data.Store', {
        model: 'QueueModel',
        autoSync: true,
        proxy: {
            type: 'ajax',
            api: {
                read: '/handlers/get_Queues.php?staff=' + id,
                update: '/handlers/set_Queues.php?staff=' + id
            },
            reader: {
                type: 'json',
                totalProperty: 'total',
                successProperty: 'success',
                idProperty: 'id',
                root: 'data',
                messageProperty: 'message'
            },
            writer: new Ext.data.JsonWriter({
                encode: false
            })
        }
    });
    var columnsQueue = [
        {width: 55, xtype: 'checkcolumn', dataIndex: 'accept', header: 'accept'},
        {width: 60, hidden: true, dataIndex: 'id', header: 'id'},
        {width: 150, dataIndex: 'name', sortable: true, header: 'name'}
    ];
    var Queuegrid = new Ext.grid.GridPanel({
        border: true,
        store: QueueStore,
        id: 'Queuegrid' + id,
        stateId: 'Queuegrid' + id,
        columns: columnsQueue,
        width: 230,
        margins: '0 2 0 0',
        split: true,
        region: 'center'
    });
    QueueStore.load();

    var win = Ext.getCmp('Queue' + id);
    if (!win) {
        win = new Ext.Window({
            id: 'Queue' + id,
            constrainHeader: true,
            plain: true,
            frame: true,
            modal: true,
            layout: 'fit',
            title: 'Agent ' + id,
            iconCls: 'icon-Info',
            width: 250,
            height: 300,
            items: [Queuegrid]
        });
    }
    win.show();
}

function CreateWebUtil(id) {
    Ext.define('WebModel', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'id', type: 'string'},
            {name: 'web', type: 'string'},
            {name: 'accept', type: 'boolean'}
        ]
    });

    var WebStore = Ext.create('Ext.data.Store', {
        model: 'WebModel',
        autoSync: true,
        proxy: {
            type: 'ajax',
            api: {
                read: '/handlers/get_Webs.php?staff=' + id,
                update: '/handlers/set_Webs.php?staff=' + id
            },
            reader: {
                type: 'json',
                totalProperty: 'total',
                successProperty: 'success',
                idProperty: 'id',
                root: 'data',
                messageProperty: 'message'
            },
            writer: new Ext.data.JsonWriter({
                encode: false
            })
        }
    });
    var columnsWeb = [
        {width: 55, xtype: 'checkcolumn', dataIndex: 'accept', header: 'accept'},
        {width: 60, hidden: true, dataIndex: 'id', header: 'id'},
        {width: 150, dataIndex: 'web', sortable: true, header: 'name'}
    ];
    var Webgrid = new Ext.grid.GridPanel({
        border: true,
        store: WebStore,
        id: 'Webgrid' + id,
        stateId: 'Webgrid' + id,
        columns: columnsWeb,
        width: 230,
        margins: '0 2 0 0',
        split: true,
        region: 'center'
    });
    WebStore.load();

    var win = Ext.getCmp('Web' + id);
    if (!win) {
        win = new Ext.Window({
            id: 'Web' + id,
            constrainHeader: true,
            plain: true,
            frame: true,
            modal: true,
            layout: 'fit',
            title: 'Партнерка ' + id,
            iconCls: 'icon-Info',
            width: 250,
            height: 300,
            items: [Webgrid]
        });
    }
    win.show();
}
