
// Меню "настройки"
function Settings() {
    Ext.create('Ext.window.Window', {
        title: 'Тип настроек',
        width: 300,
        layout: 'fit',
        modal: true,
        items: [{
                xtype: 'form',
                layout: 'anchor',
                bodyPadding: 5,
                bodyBorder: false,
                defaults: {
                    anchor: '100%'
                },
                items: [{
                        xtype: 'combo',
                        hideLabel: true,
                        store: [
                            ['cold_bonuses_plans', 'Бонусы и Проценты'],
                            ['responsible_plans', 'Планы/Премии ответственных'],
                            ['cold_otkaz_reasons', 'Причины холодных отказов'],
                            ['cold_brak_reasons', 'Причины холодного брака'],
                            ['status_kz_otkaz_reasons', 'Причины отказов (статус посылки)'],
                            ['position_man', 'Должность'],
                            ['delivery_week_days', 'Тип доставки - дни недели'],
                            ['services_categories', 'Категории услуг'],
                            ['services', 'Услуги'],
                            ['offer_groups', 'Группы товаров'],
                            ['control_admins', 'Админы контроля'],
                            ['oprosnik', 'Опросник'],
                            ['kz_operator_logist_persents', 'KZ-Оператор логист начисления'],
                            ['kz_admins_night_list', 'KZ-Админы логистики ночники'],
                            ['kz_admins_night_persents', 'KZ-Админы логистики вечерние начисления'],
                            ['kz_admins_day_persents', 'KZ-Админы логистики дневные начисления'],
                            ['kgz_operator_logist_persents', 'KGZ-Оператор логист начисления'],
                            ['kgz_admins_night_list', 'KGZ-Админы логистики ночники'],
                            ['kgz_admins_night_persents', 'KGZ-Админы логистики вечерние начисления'],
                            ['kgz_admins_day_persents', 'KGZ-Админы логистики дневные начисления']
                        ],
                        queryMode: 'local',
                        displayField: 'name',
                        valueField: 'id',
                        allowBlank: false,
                        editable: false,
                        name: 'id'
                    }, {
                        xtype: 'button',
                        text: 'OK',
                        handler: function () {
                            var form = this.up('form').getForm();
                            if (form.isValid()) {
                                this.up('window').close();
                                var data = form.getValues();

                                switch (data.id) {
                                    case 'cold_bonuses_plans':
                                        BonusePlansSettings('cold', globalResponsibleStore, 'Бонусы и Проценты', true);
                                        break;
                                    case 'responsible_plans':
                                        BonusePlansSettings('responsible', globalResponsibleStore, 'Планы/Премии ответственных', false);
                                        break;
                                    case 'delivery_week_days':
                                        SettingsDeliveryWeekDays();
                                        break;
                                    case 'services':
                                        SettingsServices();
                                        break;
                                    case 'control_admins':
                                        SettingsControlAdmins();
                                        break;
                                    case 'oprosnik':
                                        SettingsOprosnik();
                                        break;
                                    case 'kz_operator_logist_persents':
                                        var columns = [
                                            {dataIndex: 'id', header: 'Сумма от', editor: {xtype: 'numberfield', step: 1000, allowBlank: false}},
                                            {dataIndex: 'value', header: 'Процент начисления', editor: {xtype: 'numberfield', allowBlank: false, decimalPrecision: 1, step: 0.1}}
                                        ];
                                        CommonSettings(data.id, form.getFields('id').items[0].rawValue, columns);
                                        break;
                                    case 'kz_admins_night_list':
                                        var columns = [
                                            {dataIndex: 'id', header: 'ID', editor: {xtype: 'numberfield', allowBlank: false}},
                                            {
                                                dataIndex: 'value', header: 'Значение',
                                                editor: {
                                                    xtype: 'combo',
                                                    editable: false,
                                                    forceSelection: true,
                                                    triggerAction: 'all',
                                                    queryMode: 'local',
                                                    valueField: 'id',
                                                    displayField: 'value',
                                                    store: globalKzAdminStore
                                                },
                                                renderer: function (v) {
                                                    return globalKzAdminStore.ValuesJson[v] ? globalKzAdminStore.ValuesJson[v] : v;
                                                }
                                            }
                                        ];
                                        CommonSettings(data.id, form.getFields('id').items[0].rawValue, columns);
                                        break;
                                    case 'kz_admins_night_persents':
                                        var columns = [
                                            {dataIndex: 'id', header: 'Сумма от', editor: {xtype: 'numberfield', step: 1000, allowBlank: false}},
                                            {dataIndex: 'value', header: 'Процент начисления', editor: {xtype: 'numberfield', allowBlank: false, decimalPrecision: 1, step: 0.1}}
                                        ];
                                        CommonSettings(data.id, form.getFields('id').items[0].rawValue, columns);
                                        break;
                                    case 'kz_admins_day_persents':
                                        var columns = [
                                            {dataIndex: 'id', header: 'Сумма от', editor: {xtype: 'numberfield', step: 1000, allowBlank: false}},
                                            {dataIndex: 'value', header: 'Процент начисления', editor: {xtype: 'numberfield', allowBlank: false, decimalPrecision: 1, step: 0.1}}
                                        ];
                                        CommonSettings(data.id, form.getFields('id').items[0].rawValue, columns);
                                        break;
                                    case 'kgz_operator_logist_persents':
                                        var columns = [
                                            {dataIndex: 'id', header: 'Сумма от', editor: {xtype: 'numberfield', step: 1000, allowBlank: false}},
                                            {dataIndex: 'value', header: 'Процент начисления', editor: {xtype: 'numberfield', allowBlank: false, decimalPrecision: 1, step: 0.1}}
                                        ];
                                        CommonSettings(data.id, form.getFields('id').items[0].rawValue, columns);
                                        break;
                                    case 'kgz_admins_night_list':
                                        var columns = [
                                            {dataIndex: 'id', header: 'ID', editor: {xtype: 'numberfield', allowBlank: false}},
                                            {
                                                dataIndex: 'value', header: 'Значение',
                                                editor: {
                                                    xtype: 'combo',
                                                    editable: false,
                                                    forceSelection: true,
                                                    triggerAction: 'all',
                                                    queryMode: 'local',
                                                    valueField: 'id',
                                                    displayField: 'value',
                                                    store: globalKzAdminStore
                                                },
                                                renderer: function (v) {
                                                    return globalKzAdminStore.ValuesJson[v] ? globalKzAdminStore.ValuesJson[v] : v;
                                                }
                                            }
                                        ];
                                        CommonSettings(data.id, form.getFields('id').items[0].rawValue, columns);
                                        break;
                                    case 'kgz_admins_night_persents':
                                        var columns = [
                                            {dataIndex: 'id', header: 'Сумма от', editor: {xtype: 'numberfield', step: 1000, allowBlank: false}},
                                            {dataIndex: 'value', header: 'Процент начисления', editor: {xtype: 'numberfield', allowBlank: false, decimalPrecision: 1, step: 0.1}}
                                        ];
                                        CommonSettings(data.id, form.getFields('id').items[0].rawValue, columns);
                                        break;
                                    case 'kgz_admins_day_persents':
                                        var columns = [
                                            {dataIndex: 'id', header: 'Сумма от', editor: {xtype: 'numberfield', step: 1000, allowBlank: false}},
                                            {dataIndex: 'value', header: 'Процент начисления', editor: {xtype: 'numberfield', allowBlank: false, decimalPrecision: 1, step: 0.1}}
                                        ];
                                        CommonSettings(data.id, form.getFields('id').items[0].rawValue, columns);
                                        break;
                                    default :
                                        CommonSettings(data.id, form.getFields('id').items[0].rawValue);
                                        break;
                                }
                            }
                        }
                    }]
            }
        ]
    }).show();
}

function BonusePlansSettings(type, store, title, wihtGeo) {
    var windo = Ext.getCmp('BonusePlansSettingsWindow_' + type);
    if (!windo) {

        Ext.define('BonusePlansModel', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'id'},
                {name: 'type'},
                {name: 'country'},
                {name: 'responsible_id'},
                {name: 'plan'},
                {name: 'bonuse'},
                {name: 'percent'},
                {name: 'created_at', type: 'date'},
                {name: 'created_by'},
                {name: 'deleted_at', type: 'date'},
                {name: 'deleted_by'}
            ]
        });

        var BonusePlansStore = new Ext.data.JsonStore({
            model: 'BonusePlansModel',
            autoSync: true,
            autoLoad: true,
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/handler_common_bonuse_plans_settings' + (globalStaffId === 11111111 ? '' : '') + '.php?method=read&type=' + type,
                    update: '/handlers/handler_common_bonuse_plans_settings' + (globalStaffId === 11111111 ? '' : '') + '.php?method=update&type=' + type,
                    create: '/handlers/handler_common_bonuse_plans_settings' + (globalStaffId === 11111111 ? '' : '') + '.php?method=insert&type=' + type,
                    destroy: '/handlers/handler_common_bonuse_plans_settings' + (globalStaffId === 11111111 ? '' : '') + '.php?method=delete&type=' + type
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
            sorters: [{property: 'responsible_id', direction: 'ASC'}],
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

        var BonusePlansGrid = new Ext.grid.GridPanel({
            autoScroll: true,
            store: BonusePlansStore,
            loadMask: true,
            forceFit: true,
            plugins: [rowEditing],
            features: [
                new Ext.ux.grid.FiltersFeature({
                    encode: false,
                    local: false,
                    filters: [
                        {dataIndex: 'id', type: 'numeric'},
                        {dataIndex: 'country', type: 'list', phpMode: true, options: globalCountriesStore.ValuesArr},
                        {dataIndex: 'responsible_id', type: 'list', phpMode: true, options: globalResponsibleStore.ValuesArr},
                        {dataIndex: 'plan', type: 'numeric'},
                        {dataIndex: 'bonuse', type: 'numeric'},
                        {dataIndex: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                        {dataIndex: 'created_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr},
                        {dataIndex: 'deleted_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                        {dataIndex: 'deleted_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr}
                    ]
                })
            ],
            columns: [
                {dataIndex: 'id', width: 80, header: 'ID', hidden: true},
                {dataIndex: 'country', width: 140, header: 'Страна', hidden: !wihtGeo,
                    editor: {
                        xtype: 'combo',
                        editable: false,
                        queryMode: 'local',
                        allowBlank: false,
                        store: globalCountriesStore,
                        valueField: 'id',
                        displayField: 'value'
                    },
                    renderer: function (v) {
                        return globalCountriesStore.ValuesJson[v] ? globalCountriesStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'responsible_id', width: 140, header: 'Ответственный',
                    editor: {
                        xtype: 'combo',
                        editable: false,
                        queryMode: 'local',
                        allowBlank: true,
                        store: store,
                        valueField: 'id',
                        displayField: 'value'
                    },
                    renderer: function (v) {
                        return globalManagerAllStore.ValuesJson[v] ? globalManagerAllStore.ValuesJson[v] : '- Общие настройки -';
                    }
                },
                {dataIndex: 'plan', width: 130, header: 'План',
                    editor: {
                        xtype: 'numberfield',
                        editable: true,
                        decimalPrecision: 0,
                        allowBlank: false,
                        minValue: 0,
                        step: 1
                    }
                },
                {dataIndex: 'bonuse', width: 130, header: 'Премия',
                    editor: new Ext.form.NumberField({
                        editable: true,
                        decimalPrecision: 0,
                        allowBlank: false,
                        minValue: 0,
                        step: 1
                    })
                },
                {header: 'Дата создания', dataIndex: 'created_at', width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'), hidden: true},
                {header: 'Кто создал', dataIndex: 'created_by', width: 150, sortable: true, hidden: true,
                    renderer: function (v) {
                        return (globalManagerStore.ValuesJson[v]) ? globalManagerStore.ValuesJson[v] : v;
                    }
                }
            ],
            viewConfig: {
                preserveScrollOnRefresh: true,
                enableTextSelection: true
            },
            listeners: {
                selectionchange: function (selRowModel, dataModels) {
                    BonusePlansGrid.down('#delete').setDisabled(dataModels.length === 0);
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
                                BonusePlansStore.insert(0, new BonusePlansModel());
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
                                    BonusePlansStore.remove(dataModel);
                                    BonusePlansStore.reload();
                                }
                            }
                        }]
                }, {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        new Ext.PagingToolbar({
                            store: BonusePlansStore,
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
            id: 'BonusePlansSettingsWindow_' + type,
            width: 1000,
            height: 500,
            autoScroll: true,
            title: title,
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            flex: true,
            maximizable: true,
            items: [BonusePlansGrid]
        });
    }
    windo.show();
}

function CommonSettings(key, settingsTitle, columns) {
    console.log('CommonSettings: key => ' + key + ' settingsTitle => ' + settingsTitle + ' columns => ' + columns);
    //////////////////////////////////////////
    var window = Ext.getCmp('CommonSettingsWindow' + key);
    if (!window) {

        if (!columns) {
            columns = [
                {dataIndex: 'id', header: 'ID', editor: {xtype: 'numberfield', minValue: 1, allowBlank: false}},
                {dataIndex: 'value', header: 'Значение', editor: {allowBlank: false}}
            ];
        }

        Ext.define('SettingsModel', {
            extend: 'Ext.data.Model',
            initComponent: function () {
                this.addEvents('onControlAdminsWrite');
            },
            listeners: {
                onControlAdminsWrite: function (dataModel, operation, eOpts) {
                    if (operation.action === 'create') {
                    } else if (operation.action === "update") {
                        CommonSettingsStore.reload();
                    } else if (operation.action === "destroy") {
                        CommonSettingsStore.reload();
                    }
                }
            },
            fields: [
                {name: 'id'},
                {name: 'value'}
            ]
        });

        var CommonSettingsStore = new Ext.data.JsonStore({
            autoDestroy: true,
            autoSync: true,
            autoLoad: true,
            storeId: 'CommonSettingsStore',
            model: 'SettingsModel',
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/handler_common_settings.php?method=read&key=' + key,
                    update: '/handlers/handler_common_settings.php?method=update&key=' + key,
                    create: '/handlers/handler_common_settings.php?method=insert&key=' + key,
                    destroy: '/handlers/handler_common_settings.php?method=delete&key=' + key
                },
                reader: {
                    type: 'json',
                    idProperty: 'id',
                    successProperty: 'success',
                    messageProperty: 'message',
                    root: 'data',
                    totalProperty: 'total'
                },
                writer: new Ext.data.JsonWriter({
                    encode: false
                }),
                simpleSortMode: true,
                listeners: {
                    exception: function (proxy, response, operation) {
                        Ext.lib.StoreUtils.exception(proxy, response, operation);
                    }
                }
            },
            remoteSort: true,
            sorters: [{property: 'id', direction: 'DESC'}],
            pageSize: 20,
            listeners: {
                write: function (store, operation, eOpts) {
                    var dataModel = operation.getRecords().shift();
                    dataModel.fireEvent('oncommonSettingsWrite', dataModel, operation, eOpts);
                }
            }
        });

        var commonSettingsRowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            saveText: 'Сохранить',
            saveBtnText: 'Сохранить',
            cancelBtnText: 'Отмена',
            errorsText: 'Ошибка',
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

        var SettingsGrid = new Ext.grid.GridPanel({
            margins: '0 2 0 0',
            frame: true,
            width: 950,
            height: 450,
            loadMask: true,
            split: true,
            stateId: 'commonSettingsGridState' + key,
            stateful: false,
            store: CommonSettingsStore,
            plugins: [commonSettingsRowEditing],
            stripeRows: true,
            columnLines: true,
            autoScroll: true,
            emptyText: 'Нет данных',
            forceFit: true,
            viewConfig: {
                forceFit: true,
                preserveScrollOnRefresh: true,
                enableTextSelection: true
            },
            columns: columns,
            tbar: [
                {
                    text: 'Добавить',
                    itemId: 'add',
                    iconCls: 'fa fa-plus-circle',
                    handler: function (b) {
                        b.up('grid').getStore().insert(0, new SettingsModel());
                    }
                }, {
                    text: 'Удалить',
                    itemId: 'delete',
                    iconCls: 'fa fa-minus-circle',
                    disabled: true,
                    handler: function (b) {
                        var dataModel = b.up('grid').getSelectionModel().getLastSelected();
                        if (dataModel) {
                            var tt = dataModel.get('value');
                            Ext.Msg.confirm('Deleting', 'Вы действительно хотите удалить' + ' "' + (globalManagerStore.ValuesJson[tt] ? globalManagerStore.ValuesJson[tt] : tt) + '"?', function (btn) {
                                if (btn === 'yes') {
                                    b.up('grid').getStore().remove(dataModel);
                                }
                            });
                        }
                    }
                }, '->', {
                    xtype: 'button',
                    text: 'Обновить',
                    iconCls: 'x-tbar-loading',
                    handler: function (b) {
                        b.up('grid').getStore().reload();
                    }
                }
            ],
            listeners: {
                selectionchange: function (selRowModel, dataModels) {
                    SettingsGrid.down('#delete').setDisabled(dataModels.length === 0);
                }
            }
        });

        window = Ext.create('Ext.Window', {
            id: 'CommonSettingsWindow' + key,
            title: settingsTitle,
            width: 500,
            height: 350,
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            stateId: 'ControlAdminsState',
            items: [SettingsGrid]
        });
    }
    window.show();
}

/**
 * открыть / закрыть дни недели для типа доставки
 * @constructor
 */
function SettingsDeliveryWeekDays() {
    var window = Ext.getCmp('DeliveryWeekDaysWindow');
    if (!window) {
        Ext.define('DeliveryWeekDaysModel', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'delivery_type', type: 'string'},
                {name: 'mon', type: 'numeric'},
                {name: 'tue', type: 'numeric'},
                {name: 'wed', type: 'numeric'},
                {name: 'thu', type: 'numeric'},
                {name: 'fri', type: 'numeric'},
                {name: 'sat', type: 'numeric'},
                {name: 'sun', type: 'numeric'},
                {name: 'active', type: 'numeric'}
            ]
        });

        var DeliveryWeekDaysStore = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            autoSync: true,
            storeId: 'DeliveryWeekDaysStore',
            model: 'DeliveryWeekDaysModel',
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/handler_delivery_week_days.php?method=read',
                    update: '/handlers/handler_delivery_week_days.php?method=update',
                    create: '/handlers/handler_delivery_week_days.php?method=insert',
                    destroy: '/handlers/handler_delivery_week_days.php?method=delete'
                },
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'id',
                    root: 'data',
                    messageProperty: 'message'
                }
            }
        });

        var DeliveryWeekDaysFilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'numeric'},
                {dataIndex: 'delivery_type', type: 'string'},
                {dataIndex: 'mon', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'tue', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'wed', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'thu', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'fri', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'sat', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'sun', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'active', type: 'boolean', yesText: 'Да', noText: 'Нет'}
            ]
        });

        // row editing
        var rowEditDeliveryWeekDays = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToMoveEditor: 1,
            listeners: {
                canceledit: function (editor, e, eOpts) {
                    if (e.record.get('region') === '') {
                        Ext.data.StoreManager.lookup('DeliveryWeekDaysStore').remove(e.record);
                    }
                    return false;
                }
            }
        });

        var DeliveryWeekDaysGrid = new Ext.grid.GridPanel({
            border: false,
            store: DeliveryWeekDaysStore,
            width: 950,
            height: 450,
            loadMask: true,
            features: [DeliveryWeekDaysFilters],
            stripeRows: true,
            columnLines: true,
            viewConfig: {
                enableTextSelection: true
            },
            listeners: {
                selectionchange: function (selRowModel, dataModels) {
                    DeliveryWeekDaysGrid.down('#delete').setDisabled(dataModels.length === 0);
                }
            },
            columns: [
                {dataIndex: 'id', header: 'Id', hidden: true},
                {dataIndex: 'delivery_type', header: 'Тип доставки', width: 200,
                    editor: {
                        xtype: 'combobox',
                        typeAhead: true,
                        triggerAction: 'all',
                        editable: false,
                        store: globalDeliveryCouriersStore,
                        valueField: 'id',
                        displayField: 'value',
                        allowBlank: false
                    }
                },
                {dataIndex: 'mon', header: 'Понедельник', editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return v > 0 ? 'Да' : 'Нет';
                    }
                },
                {dataIndex: 'tue', header: 'Вторник', editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return v > 0 ? 'Да' : 'Нет';
                    }
                },
                {dataIndex: 'wed', header: 'Среда', editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return v > 0 ? 'Да' : 'Нет';
                    }
                },
                {dataIndex: 'thu', header: 'Четверг', editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return v > 0 ? 'Да' : 'Нет';
                    }
                },
                {dataIndex: 'fri', header: 'Пятница', editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return v > 0 ? 'Да' : 'Нет';
                    }
                },
                {dataIndex: 'sat', header: 'Суббота', editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return v > 0 ? 'Да' : 'Нет';
                    }
                },
                {dataIndex: 'sun', header: 'Воскресенье', editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return v > 0 ? 'Да' : 'Нет';
                    }
                },
                {dataIndex: 'active', header: 'Работает', editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return v > 0 ? 'Да' : 'Нет';
                    }
                }
            ],
            tbar: [
                {
                    text: 'Добавить',
                    iconCls: 'fa fa-plus-circle',
                    handler: function (b) {
                        rowEditDeliveryWeekDays.cancelEdit();
                        var store = b.up('grid').getStore();
                        store.insert(0, new DeliveryWeekDaysModel());
                        rowEditDeliveryWeekDays.startEdit(0, 0);
                    }
                }, {
                    itemId: 'delete',
                    text: 'Удалить',
                    iconCls: 'fa fa-minus-circle',
                    disabled: true,
                    handler: function (b) {
                        var dataModel = b.up('grid').getSelectionModel().getLastSelected();
                        if (dataModel) {
                            Ext.Msg.confirm('Deleting', 'Вы действительно хотите удалить "' + dataModel.get('delivery_type') + '"?', function (btn) {
                                if (btn === 'yes') {
                                    b.up('grid').getStore().remove(dataModel);
                                    b.up('grid').getStore().reload();
                                }
                            });

                        }
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({
                store: DeliveryWeekDaysStore,
                features: [DeliveryWeekDaysFilters],
                items: [
                    {
                        text: 'Очистить фильтр',
                        handler: function () {
                            DeliveryWeekDaysGrid.filters.clearFilters();
                        }
                    }
                ],
                displayInfo: true
            }),
            plugins: [rowEditDeliveryWeekDays]
        });
        DeliveryWeekDaysGrid.store.load();

        window = Ext.create('Ext.Window', {
            id: 'DeliveryWeekDaysWindow',
            width: 950,
            height: 500,
            title: 'Тип доставки - дни недели',
            constrainHeader: true,
            plain: true,
            layout: 'border',
            flex: true,
            maximizable: true,
            stateId: 'DeliveryWeekDaysState',
            items: [DeliveryWeekDaysGrid]
        });
    }
    window.show();
}

/**
 * открыть / закрыть дни недели для типа доставки
 * @constructor
 */
function SettingsServices() {
    var window = Ext.getCmp('ServicesWindow');
    if (!window) {
        Ext.define('ServicesModel', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'name', type: 'string'},
                {name: 'category_id', type: 'numeric'},
                {name: 'city_id', type: 'numeric'},
                {name: 'description', type: 'string'},
                {name: 'phone', type: 'string'},
                {name: 'price', type: 'numeric'},
                {name: 'address', type: 'string'},
                {name: 'created_at', type: 'date'},
                {name: 'created_by', type: 'numeric'},
                {name: 'updated_at', type: 'date'},
                {name: 'updated_by', type: 'numeric'}
            ]
        });

        var ServicesStore = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            autoSync: true,
            storeId: 'ServicesStore',
            model: 'ServicesModel',
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/handler_services.php?method=read',
                    update: '/handlers/handler_services.php?method=update',
                    create: '/handlers/handler_services.php?method=insert',
                    destroy: '/handlers/handler_services.php?method=delete'
                },
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'id',
                    root: 'data',
                    messageProperty: 'message'
                }
            }
        });

        var ServicesFilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'numeric'},
                {dataIndex: 'name', type: 'string'},
                {dataIndex: 'category_id', type: 'list', phpMode: true, options: globalServicesCategoriesStore.ValuesArr},
                {dataIndex: 'city_id', type: 'list', phpMode: true, options: globalCitiesKZStore.ValuesArr},
                {dataIndex: 'description', type: 'string'},
                {dataIndex: 'phone', type: 'string'},
                {dataIndex: 'price', type: 'numeric'},
                {dataIndex: 'address', type: 'string'},
                {dataIndex: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'created_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr},
                {dataIndex: 'updated_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'updated_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr},
                {dataIndex: 'deleted_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'deleted_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr}
            ]
        });

        // row editing
        var rowEditServices = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToMoveEditor: 1,
            listeners: {
                canceledit: function (editor, e, eOpts) {
                    if (e.record.get('region') === '') {
                        Ext.data.StoreManager.lookup('ServicesStore').remove(e.record);
                    }
                    return false;
                }
            }
        });

        var ServicesGrid = new Ext.grid.GridPanel({
            border: false,
            store: ServicesStore,
            width: 950,
            height: 450,
            loadMask: true,
            features: [ServicesFilters],
            stripeRows: true,
            columnLines: true,
            viewConfig: {
                enableTextSelection: true
            },
            listeners: {
                selectionchange: function (selRowModel, dataModels) {
                    ServicesGrid.down('#delete').setDisabled(dataModels.length === 0);
                }
            },
            columns: [
                {dataIndex: 'id', header: 'Id'},
                {dataIndex: 'name', header: 'Наименование', editor: {xtype: 'textfield', allowBlank: false}},
                {dataIndex: 'category_id', header: 'Категория', width: 200,
                    editor: {
                        xtype: 'combobox',
                        typeAhead: true,
                        triggerAction: 'all',
                        editable: false,
                        store: globalServicesCategoriesStore,
                        valueField: 'id',
                        displayField: 'value',
                        allowBlank: false
                    },
                    renderer: function (v) {
                        return (globalServicesCategoriesStore.ValuesJson[v]) ? globalServicesCategoriesStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'city_id', header: 'Город', width: 200,
                    editor: {
                        xtype: 'combobox',
                        typeAhead: true,
                        triggerAction: 'all',
                        editable: false,
                        store: globalCitiesKZStore,
                        valueField: 'id',
                        displayField: 'value',
                        allowBlank: false
                    },
                    renderer: function (v) {
                        return (globalCitiesKZStore.ValuesJson[v]) ? globalCitiesKZStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'description', header: 'Описание', editor: {xtype: 'textfield'}},
                {dataIndex: 'phone', header: 'Контанктный номер', editor: {xtype: 'textfield', allowBlank: false}},
                {dataIndex: 'price', header: 'Себестоимость', editor: {xtype: 'numberfield', allowBlank: false}},
                {dataIndex: 'address', header: 'Адрес предоставления услуги', editor: {xtype: 'textfield'}},
                {header: 'Дата создания', dataIndex: 'created_at', width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'), hidden: false},
                {header: 'Кто создал', dataIndex: 'created_by', width: 150, sortable: true, hidden: false,
                    renderer: function (v) {
                        return (globalManagerStore.ValuesJson[v]) ? globalManagerStore.ValuesJson[v] : v;
                    }
                },
                {header: 'Дата изменения', dataIndex: 'updated_at', width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'), hidden: false},
                {header: 'Кто редактировал', dataIndex: 'updated_by', width: 150, sortable: true, hidden: false,
                    renderer: function (v) {
                        return (globalManagerStore.ValuesJson[v]) ? globalManagerStore.ValuesJson[v] : v;
                    }
                },
                {header: 'Дата удаления', dataIndex: 'deleted_at', width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'), hidden: true},
                {header: 'Кто удалил', dataIndex: 'deleted_by', width: 150, sortable: true, hidden: true,
                    renderer: function (v) {
                        return (globalManagerStore.ValuesJson[v]) ? globalManagerStore.ValuesJson[v] : v;
                    }
                }
            ],
            tbar: [
                {
                    text: 'Добавить',
                    iconCls: 'fa fa-plus-circle',
                    handler: function (b) {
                        rowEditServices.cancelEdit();
                        var store = b.up('grid').getStore();
                        store.insert(0, new ServicesModel());
                        rowEditServices.startEdit(0, 0);
                    }
                }, {
                    itemId: 'delete',
                    text: 'Удалить',
                    iconCls: 'fa fa-minus-circle',
                    disabled: true,
                    handler: function (b) {
                        var dataModel = b.up('grid').getSelectionModel().getLastSelected();
                        if (dataModel) {
                            Ext.Msg.confirm('Deleting', 'Вы действительно хотите удалить "' + dataModel.get('delivery_type') + '"?', function (btn) {
                                if (btn === 'yes') {
                                    b.up('grid').getStore().remove(dataModel);
                                    b.up('grid').getStore().reload();
                                }
                            });

                        }
                    }
                }
            ],
            bbar: new Ext.PagingToolbar({
                store: ServicesStore,
                features: [ServicesFilters],
                items: [
                    {
                        text: 'Очистить фильтр',
                        handler: function () {
                            ServicesGrid.filters.clearFilters();
                        }
                    }
                ],
                displayInfo: true
            }),
            plugins: [rowEditServices]
        });
        ServicesGrid.store.load();

        window = Ext.create('Ext.Window', {
            id: 'ServicesWindow',
            width: 950,
            height: 500,
            title: 'Услуги',
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            maximizable: true,
            stateId: 'ServicesState',
            items: [ServicesGrid]
        });
    }
    window.show();
}

/**
 * Админы контроля
 * @constructor
 */
function SettingsControlAdmins() {
    var window = Ext.getCmp('ControlAdminsWindow');
    if (!window) {

        Ext.define('ControlAdminsModel', {
            extend: 'Ext.data.Model',
            initComponent: function () {
                this.addEvents('onControlAdminsWrite');
            },
            listeners: {
                onControlAdminsWrite: function (dataModel, operation, eOpts) {
                    if (operation.action === 'create') {
                    } else if (operation.action === "update") {
                        ControlAdminsStore.reload();
                    } else if (operation.action === "destroy") {
                        ControlAdminsStore.reload();
                    }
                }
            },
            fields: [
                {name: 'id'},
                {name: 'value'}
            ]
        });

        var ControlAdminsStore = new Ext.data.JsonStore({
            autoDestroy: true,
            autoSync: true,
            autoLoad: true,
            storeId: 'ControlAdminsStore',
            model: 'ControlAdminsModel',
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/handler_control_admins.php?method=read',
                    update: '/handlers/handler_control_admins.php?method=update',
                    create: '/handlers/handler_control_admins.php?method=insert',
                    destroy: '/handlers/handler_control_admins.php?method=delete'
                },
                reader: {
                    type: 'json',
                    idProperty: 'id',
                    successProperty: 'success',
                    messageProperty: 'message',
                    root: 'data',
                    totalProperty: 'total'
                },
                writer: new Ext.data.JsonWriter({
                    encode: false
                }),
                simpleSortMode: true,
                listeners: {
                    exception: function (proxy, response, operation) {
                        Ext.lib.StoreUtils.exception(proxy, response, operation);
                    }
                }
            },
            remoteSort: true,
            sorters: [{property: 'id', direction: 'DESC'}],
            pageSize: 20,
            listeners: {
                write: function (store, operation, eOpts) {
                    var dataModel = operation.getRecords().shift();
                    dataModel.fireEvent('oncontrolAdminsWrite', dataModel, operation, eOpts);
                }
            }
        });

        var controlAdminsRowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            saveText: 'Сохранить',
            saveBtnText: 'Сохранить',
            cancelBtnText: 'Отмена',
            errorsText: 'Ошибка',
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

        var controlAdminsGrid = new Ext.grid.GridPanel({
            margins: '0 2 0 0',
            frame: true,
            width: 950,
            height: 450,
            loadMask: true,
            split: true,
            stateId: 'controlAdminsGridState',
            stateful: false,
            store: ControlAdminsStore,
            plugins: [controlAdminsRowEditing],
            stripeRows: true,
            columnLines: true,
            autoScroll: true,
            emptyText: 'Нет данных',
            forceFit: true,
            viewConfig: {
                forceFit: true,
                preserveScrollOnRefresh: true,
                enableTextSelection: true
            },
            columns: [
                {dataIndex: 'id', header: 'Сотрудник', hidden: true},
                {dataIndex: 'value', header: 'Сотрудник',
                    renderer: function (value) {
                        return globalManagerStore.ValuesJson[value] ? globalManagerStore.ValuesJson[value] : value;
                    },
                    editor: new Ext.form.ComboBox({
                        triggerAction: 'all',
                        queryMode: 'local',
                        store: globalManagerStore,
                        valueField: 'id',
                        displayField: 'value',
                        typeAhead: true,
                        editable: false,
                        minChars: 1
                    })
                }
            ],
            tbar: [
                {
                    itemId: 'add',
                    iconCls: 'fa fa-plus-circle',
                    handler: function () {
                        ControlAdminsStore.insert(0, new ControlAdminsModel());
                    }
                }, {
                    itemId: 'delete',
                    iconCls: 'fa fa-minus-circle',
                    disabled: true,
                    handler: function (b) {
                        var dataModel = b.up('grid').getSelectionModel().getLastSelected();
                        if (dataModel) {
                            var tt = dataModel.get('value');
                            Ext.Msg.confirm('Deleting', 'Вы действительно хотите удалить' + ' "' + (globalManagerStore.ValuesJson[tt] ? globalManagerStore.ValuesJson[tt] : tt) + '"?', function (btn) {
                                if (btn === 'yes') {
                                    ControlAdminsStore.remove(dataModel);
                                }
                            });
                        }
                    }
                }
            ],
            listeners: {
                selectionchange: function (selRowModel, dataModels) {
                    controlAdminsGrid.down('#delete').setDisabled(dataModels.length === 0);
                }
            }
        });

        window = Ext.create('Ext.Window', {
            id: 'ControlAdminsWindow',
            width: 950,
            height: 500,
            title: 'Админы контроля',
            constrainHeader: true,
            plain: true,
            layout: 'border',
            flex: true,
            maximizable: true,
            stateId: 'ControlAdminsState',
            items: [controlAdminsGrid]
        });
    }
    window.show();
}

/**
 * Опросник
 * @constructor
 */
function SettingsOprosnik() {
    var window = Ext.getCmp('OprosnikWindow');
    if (!window) {

        Ext.define('OprosnikModel', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'country', type: 'string'},
                {name: 'status', type: 'string'},
                {name: 'offer_group', type: 'string'},
                {name: 'offer', type: 'string'},
                {name: 'active', type: 'boolean'},
                {name: 'question', type: 'string'},
                {name: 'created_at', type: 'date'},
                {name: 'created_by'},
                {name: 'deleted_at', type: 'date'},
                {name: 'deleted_by'}
            ]
        });

        // store "Опросник"
        var OprosnikStore = new Ext.data.JsonStore({
            storeId: 'OprosnikStore',
            model: 'OprosnikModel',
            autoDestroy: true,
            remoteSort: true,
            pageSize: 500,
            groupField: 'country',
            autoLoad: true,
            autoSync: true,
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/handler_oprosnik.php?method=read',
                    update: '/handlers/handler_oprosnik.php?method=update',
                    create: '/handlers/handler_oprosnik.php?method=insert',
                    destroy: '/handlers/handler_oprosnik.php?method=delete'
                },
                reader: {
                    type: 'json',
                    idProperty: 'id',
                    successProperty: 'success',
                    messageProperty: 'message',
                    root: 'data',
                    totalProperty: 'total'
                },
                writer: new Ext.data.JsonWriter({
                    encode: false
                }),
                simpleSortMode: true,
                listeners: {
                    exception: function (proxy, response, operation) {
                        Ext.lib.StoreUtils.exception(proxy, response, operation);
                    }
                }
            },
            sortInfo: {
                field: 'id',
                direction: 'DESC'
            },
            listeners: {
                write: function (store, operation, eOpts) {
//                    OprosnikStore.reload();
                }
            }
        });

        var OprosnikFilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'string'},
                {dataIndex: 'country', type: 'list', phpMode: true, options: globalCountriesStore.ValuesArr},
                {dataIndex: 'status', type: 'list', phpMode: true, options: globalStatusStore.ValuesArr},
                {dataIndex: 'offer_group', type: 'list', phpMode: true, options: globalOfferGroupsStore.ValuesGroupArr},
                {dataIndex: 'offer', type: 'list', phpMode: true, options: globalOffersStore.ValuesArr},
                {dataIndex: 'question', type: 'string'},
                {dataIndex: 'active', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'created_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr},
                {dataIndex: 'deleted_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'deleted_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr}
            ]
        });

        var groupingSummaryFeature = {
            id: 'oprosnikGroupingFeatureId',
            ftype: 'groupingsummary',
            enableGroupingMenu: true,
            groupHeaderTpl: '{columnName}: {name}'
        };

        var OprosnikRowEditing = new Ext.grid.plugin.RowEditing({
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
                }
            }
        });

        var OprosnikGrid = new Ext.grid.GridPanel({
            id: 'OprosnikGrid',
            frame: true,
            loadMask: true,
            split: true,
            stateId: 'OprosnikGridState',
            stateful: true,
            store: OprosnikStore,
            stripeRows: true,
            columnLines: true,
            autoScroll: true,
            emptyText: 'Нет данных',
            forceFit: true,
            plugins: [OprosnikRowEditing],
            features: [
                groupingSummaryFeature,
                OprosnikFilters
            ],
            viewConfig: {
                forceFit: true,
                enableTextSelection: true,
                showPreview: true,
                enableRowBody: true
            },
            columns: [
                {header: 'Id', dataIndex: 'id', width: 90, sortable: true, hidden: true},
                {header: 'Активен', dataIndex: 'active', width: 30, sortable: true, editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return (v > 0) ? 'Да' : 'Нет';
                    }
                },
                {header: 'Страна', dataIndex: 'country', width: 140, sortable: true,
                    editor: new Ext.form.ComboBox({
                        editable: false,
                        triggerAction: 'all',
                        queryMode: 'local',
                        store: globalCountriesStore,
                        valueField: 'id',
                        displayField: 'value'
                    }),
                    renderer: function (v) {
                        return globalCountriesStore.ValuesJson[v] ? globalCountriesStore.ValuesJson[v] : v;
                    }
                },
                {header: 'Статус', dataIndex: 'status', width: 140, sortable: true,
                    editor: new Ext.form.ComboBox({
                        xtype: 'combo',
                        editable: false,
                        forceSelection: true,
                        triggerAction: 'all',
                        queryMode: 'local',
                        valueField: 'value',
                        displayField: 'value',
                        store: globalStatusStore
                    }),
                    renderer: function (v) {
                        return globalStatusStore.ValuesJson[v] ? globalStatusStore.ValuesJson[v] : v;
                    }
                },
                {header: 'Группа товара', dataIndex: 'offer_group', width: 140, sortable: true,
                    editor: new Ext.form.ComboBox({
                        editable: false,
                        triggerAction: 'all',
                        queryMode: 'local',
                        store: globalOfferGroupsStore.ValuesGroupArr,
                        valueField: 'value',
                        displayField: 'value'
                    }),
                    renderer: function (v) {
                        return globalOfferGroupsStore.ValuesGroupJson[v] ? globalOfferGroupsStore.ValuesGroupJson[v] : v;
                    }
                },
                {header: 'Продукт', dataIndex: 'offer', width: 140, sortable: true,
                    editor: new Ext.form.ComboBox({
                        editable: false,
                        triggerAction: 'all',
                        queryMode: 'local',
                        store: globalOffersStore,
                        valueField: 'value',
                        displayField: 'value'
                    }),
                    renderer: function (v) {
                        return globalOffersStore.ValuesJson[v] ? globalOffersStore.ValuesJson[v] : v;
                    }
                },
                {header: 'Вопрос', dataIndex: 'question', width: 200, sortable: true, editor: new Ext.form.TextField({allowBlank: false})},
                {header: 'Дата создания', dataIndex: 'created_at', width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'), hidden: true},
                {header: 'Кто создал', dataIndex: 'created_by', width: 150, sortable: true, hidden: true,
                    renderer: function (v) {
                        return (globalManagerStore.ValuesJson[v]) ? globalManagerStore.ValuesJson[v] : v;
                    }
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                        {
                            xtype: 'button',
                            text: 'Добавить',
                            iconCls: 'fa fa-plus-circle',
                            handler: function () {
                                OprosnikStore.insert(0, new OprosnikModel());
                                OprosnikRowEditing.startEdit(0, 0);
                            }
                        }, {
                            itemId: 'delete',
                            text: 'Удалить',
                            iconCls: 'fa fa-minus-circle',
                            disabled: true,
                            handler: function (b) {
                                var dataModel = b.up('grid').getSelectionModel().getLastSelected();
                                if (dataModel) {
                                    console.log('remove');
                                    OprosnikStore.remove(dataModel);
                                }
                            }
                        }
                    ]
                }, {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        new Ext.PagingToolbar({
                            store: OprosnikStore,
                            displayInfo: true
                        }),
                        '->',
                        {
                            text: 'Excel',
                            icon: '/shared/icons/excel_16x16.png',
                            handler: function (b, e) {
                                b.up('grid').downloadExcelXml();
                            }
                        }, {
                            text: 'Очистить фильтры',
                            icon: '/images/clear_filters.png',
                            handler: function (b, e) {
                                b.up('grid').filters.clearFilters();
                            }
                        }
                    ]
                }
            ],
            listeners: {
                viewready: function (grid, eOpts) {
                    var groupingFeature = grid.getView().getFeature('oprosnikGroupingFeatureId');
                    if (groupingFeature) {
                        groupingFeature.disable();
                    }
                },
                selectionchange: function (selRowModel, dataModels) {
                    OprosnikGrid.down('#delete').setDisabled(dataModels.length === 0);
                }
            }
        });

        window = Ext.create('Ext.Window', {
            id: 'OprosnikWindow',
            width: 950,
            height: 500,
            title: 'Опросник',
            constrainHeader: true,
            plain: true,
            maximizable: true,
            stateId: 'OprosnikState',
            items: [OprosnikGrid],
            autoScroll: true,
            layout: 'fit'
        });
    }
    window.show();
}
