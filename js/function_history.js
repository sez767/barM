
// Вкладка "Опросник"
function HistoryTab(tabs) {
//    return false;

    var tab = tabs.queryById('HistoryTab');
    if (tab) {
        tab.show();
    } else {

        Ext.define('HistoryModel', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'object_name', type: 'string'},
                {name: 'object_id', type: 'string'},
                {name: 'worker', type: 'numeric'},
                {name: 'worker_id', type: 'numeric'},
                {name: 'type', type: 'string'},
                {name: 'property', type: 'string'},
                {name: 'property_str', type: 'string'},
                {name: 'was', type: 'string'},
                {name: 'set', type: 'string'},
                {name: 'comment', type: 'string'}
            ]
        });
        var HistoryStore = Ext.create('Ext.data.Store', {
            model: "HistoryModel",
            autoLoad: true,
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/handler_history.php?method=read'
                },
                reader: {
                    type: 'json',
                    idProperty: 'id',
                    successProperty: 'success',
                    messageProperty: 'message',
                    root: 'data',
                    totalProperty: 'total'
                },
                timeout: 60000,
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
                    dataModel.fireEvent('onHistoryWrite', dataModel, operation, eOpts);
                }
            }
        });
        var HistoryFilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'numeric'},
                {dataIndex: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'worker', type: 'numeric'},
//                {dataIndex: 'worker', type: 'list', phpMode: true, options: globalManagerAllStore.ValuesArr},
                {dataIndex: 'object_name', type: 'list', options: [
                        ['StaffObj', 'Сотрудник'],
                        ['ClientsObj', 'Клиент'],
                        ['StaffOrderObj', 'Заказ'],
                        ['OprosnikObj', 'Опросник'],
                        ['PredictiveMultiplierObj', 'Коефициенты'],
                        ['EmailObj', 'Email'],
                        ['SMSObj', 'SMS'],
                        ['ExchangeObj', 'Замены'],
                        ['CallObjectHistoryObj', 'История звонков'],
                        ['ServicesObj', 'Услуга']
                    ], phpMode: true
                },
                {dataIndex: 'object_id', type: 'string'},
                {dataIndex: 'type', type: 'list', options: [['update', 'update'], ['insert', 'insert'], ['delete', 'delete'], ['login', 'login'], ['logout', 'logout'], ['user', 'user']], phpMode: true},
                {dataIndex: 'property', type: 'string'},
                {dataIndex: 'was', type: 'string'},
                {dataIndex: 'set', type: 'string'},
                {dataIndex: 'comment', type: 'string'}
            ]
        });
        var HistoryGrid = new Ext.grid.GridPanel({
            store: HistoryStore,
            autoScroll: true,
            stateId: 'HistoryGridState',
            stateful: true,
            multiSelect: false,
            frame: true,
            loadMask: true,
            columns: [
                {hidden: true, dataIndex: 'id', header: 'id'},
                {dataIndex: 'date', width: 140, header: 'Дата изменений', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'worker', header: 'Кто изменял ID'},
                {dataIndex: 'worker_id', header: 'Кто изменял',
                    renderer: function (value, metaData, dataModel) {
                        return globalManagerAllStore.ValuesJson[value] ? globalManagerAllStore.ValuesJson[value] : value;
                    }
                },
                {dataIndex: 'object_name', header: 'Изменяемый объект',
                    renderer: function (val) {
                        var ret = '';
                        switch (val) {
                            case 'StaffObj':
                                ret = 'Сотрудник';
                                break;
                            case 'ClientsObj':
                                ret = 'Клиент';
                                break;
                            case 'StaffOrderObj':
                                ret = 'Заказ';
                                break;
                            case 'OprosnikObj':
                                ret = 'Опросник';
                                break;
                            case 'ServicesObj':
                                ret = 'Услуга';
                                break;
                            case 'PredictiveMultiplierObj':
                                ret = 'Коефициенты';
                                break;
                            case 'OfferPropertyObj':
                                ret = 'Свойства товара';
                                break;
                            case 'EmailObj':
                                ret = 'Email';
                                break;
                            case 'SMSObj':
                                ret = 'SMS';
                                break;
                            case 'ExchangeObj':
                                ret = 'Замены';
                                break;
                            case 'CallObjectHistoryObj':
                                ret = 'История звонков';
                                break;
                            default :
                                ret = val;
                                break;
                        }
                        return ret;
                    }
                },
                {dataIndex: 'object_id', header: 'Изменяемое id'},
                {dataIndex: 'type', header: 'Тип изменений'},
                {dataIndex: 'property', header: 'Изменяемое свойство'},
                {dataIndex: 'property_str', header: 'Рус изменяемое свойство',
                    renderer: function (val) {
                        var ret = '';
                        switch (val) {
                            case 'id':
                                ret = 'Id';
                                break;
                            case 'uuid':
                                ret = 'Кл ID';
                                break;
                            case 'client_group':
                                ret = 'Кл група';
                                break;
                            case 'client_team':
                                ret = 'Кл отдел прод';
                                break;
                            case 'client_oper_use':
                                ret = 'Кл В работе У';
                                break;
                            case 'client_worries':
                                ret = 'Недуги/беспокойства клиента';
                                break;
                            case 'phone':
                                ret = 'Телефон';
                                break;
                            case 'fio':
                                ret = 'ФИО';
                                break;
                            case 'country':
                                ret = 'Локация';
                                break;
                            case 'status':
                                ret = 'Статус';
                                break;
                            case 'send_status':
                                ret = 'Статус отправки';
                                break;
                            case 'status_kz':
                                ret = 'Статус посылки';
                                break;
                            case 'status_cur':
                                ret = 'Статус курьера';
                                break;
                            case 'who_get':
                                ret = 'Кто открыл анкету';
                                break;
                            case 'kz_admin':
                                ret = 'Админ логистики';
                                break;
                            case 'tovara_net':
                                ret = 'Товара нет';
                                break;
                            case 'ch_response':
                                ret = 'Источник предоплаты';
                                break;
                            case 'rings':
                                ret = 'Звонки';
                                break;
                            case 'status_check':
                                ret = 'Статус проверки';
                                break;
                            case 'kz_zone':
                                ret = 'Зона доставки';
                                break;
                            case 'kz_delivery':
                                ret = 'Тип доставки';
                                break;
                            case 'index':
                                ret = 'Почтовый индекс';
                                break;
                            case 'control_admin':
                                ret = 'Админ контроля';
                                break;
                            case 'control_status':
                                ret = 'Статус контроля';
                                break;
                            case 'phone_sms':
                                ret = 'Доп. телефон';
                                break;
                            case 'city_region':
                                ret = 'Район города';
                                break;
                            case 'offer':
                                ret = 'Тип продукта';
                                break;
                            case 'other_data':
                                ret = 'Свойства продукта';
                                break;
                            case 'offer_groups':
                                ret = 'Группа товара';
                                break;
                            case 'site':
                                ret = 'Ленд';
                                break;
                            case 'price':
                                ret = 'Цена';
                                break;
                            case 'post_price':
                                ret = 'Цена предоплаты';
                                break;
                            case 'total_price':
                                ret = 'Цена итого';
                                break;
                            case 'youtube_url':
                                ret = 'YouTube ссылка';
                                break;
                            case 'addr':
                                ret = 'Адрес';
                                break;
                            case 'deliv_desc':
                                ret = 'Примечание доставки';
                                break;
                            case 'kz_curier':
                                ret = '№ курьера';
                                break;
                            case 'fill_date':
                                ret = 'Дата изменения статуса';
                                break;
                            case 'recall_date':
                                ret = 'Перезв. через';
                                break;
                            case 'call_date':
                                ret = 'Дата посл. звонка';
                                break;
                            case 'call_count':
                                ret = 'К-во наб.';
                                break;
                            case 'call_count_hand':
                                ret = 'Ручн.';
                                break;
                            case 'call_count_now':
                                ret = 'К-во сег.';
                                break;
                            case 'date_delivery':
                                ret = 'Дата доставки';
                                break;
                            case 'date_delivery_first':
                                ret = 'Дата доставки1';
                                break;
                            case 'is_cold':
                                ret = 'Хол статус';
                                break;
                            case 'is_cold_new_id':
                                ret = 'Хол Id';
                                break;
                            case 'is_cold_in_date':
                                ret = 'Хол дата добавления';
                                break;
                            case 'is_cold_out_date':
                                ret = 'Хол дата статуса';
                                break;
                            case 'is_cold_staff_id':
                                ret = 'Хол оператор';
                                break;
                            case 'common_cancel_type':
                                ret = 'Хол/Гар прич.отказа';
                                break;
                            case 'common_recall_date':
                                ret = 'Хол перезвонить';
                                break;
                            case 'return_date':
                                ret = 'Дата оплаты';
                                break;
                            case 'next_return_date':
                                ret = 'Дата следующего списания';
                                break;
                            case 'deferred_date':
                                ret = 'Дата отл. доставки';
                                break;
                            case 'take_away_date':
                                ret = 'Дата заберет';
                                break;
                            case 'give_date':
                                ret = 'Дата предоплаты';
                                break;
                            case 'ch_response':
                                ret = 'Источник предоплаты';
                                break;
                            case 'status_income':
                                ret = 'Статус вход линии';
                                break;
                            case 'deliv_cost':
                                ret = 'Оператор входящей линии';
                                break;
                            case 'date_vruchen':
                                ret = 'Дата статуса вход линии';
                                break;
                            case 'stcur_date':
                                ret = 'Дата статуса курьера';
                                break;
                            case 'date_vozvrat':
                                ret = 'Дата Возврат';
                                break;
                            case 'description':
                                ret = 'Описание (причина отказа)';
                                break;
                            case 'description_str':
                                ret = 'Описание 1 (причина отказа)';
                                break;
                            case 'kz_operator':
                                ret = 'Оператор логист';
                                break;
                            case 'date':
                                ret = 'Дата добавления';
                                break;
                            case 'cancel_date':
                                ret = 'Дата отказа';
                                break;
                            case 'date_otl':
                                ret = 'Дата скинут предоплату';
                                break;
                            case 'delivery_date':
                                ret = 'Дата груз в дороге';
                                break;
                            case 'dop_tovar':
                                ret = 'Дополнительный товар';
                                break;
                            case 'dop_tovar_price':
                                ret = 'Доп. товар цена';
                                break;
                            case 'package':
                                ret = 'К-во';
                                break;
                            case 'last_edit':
                                ret = 'Оператор обзвона';
                                break;
                            case 'responsible':
                                ret = 'Ответственный';
                                break;
                            case 'responsible_cold':
                                ret = 'Хол ответственный';
                                break;
                            case 'team':
                                ret = 'Отдел продаж';
                                break;
                            case 'Group':
                                ret = 'Горяч группа';
                                break;
                            case 'Group_cold':
                                ret = 'Хол группа';
                                break;
                            case 'oper_use':
                                ret = 'В работе у';
                                break;
                            case 'recall_date':
                                ret = 'Перезв. через';
                                break;
                            case 'staff_id':
                                ret = 'Источник';
                                break;
                            case 'staff_id_orig':
                                ret = 'Ориг источник';
                                break;
                            case 'ext_id':
                                ret = 'ID внешний';
                                break;
                            case 'kz_code':
                                ret = 'Barcode';
                                break;
                            case 'web_id':
                                ret = 'Веб';
                                break;
                            case 'courier_group':
                                ret = 'Оператор предоплаты';
                                break;
                            case 'uae_id':
                                ret = 'Ket-ID';
                                break;
                            case 'status_mail_reset':
                                ret = 'Статус почтового возврата';
                                break;
                            case 'status_mail_reset_date':
                                ret = 'Дата статуса почтового возврата';
                                break;
                            case 'sex':
                                resext = 'Пол';
                                break;
                            case 'age':
                                ret = 'Возраст';
                                break;
                            case 'not_rus':
                                ret = 'Не понимает RU';
                                break;
                            case 'city':
                                ret = 'Город';
                                break;
                            case 'birthday':
                                ret = 'День рождения';
                                break;
                            case '':
                                ret = '';
                                break;
                            default :
                                ret = val;
                                break;
                        }
                        return ret;
                    }
                },
                {dataIndex: 'was', header: 'Старое значение'},
                {dataIndex: 'set', header: 'Новое значение'},
                {dataIndex: 'comment', type: 'string', width: 160, header: 'Комментарий'}
            ],
            viewConfig: {
                preserveScrollOnRefresh: true,
                enableTextSelection: true
            },
            features: [HistoryFilters],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        new Ext.PagingToolbar({
                            store: HistoryStore,
                            displayInfo: true,
                            beforePageText: 'Страница',
                            displayMsg: 'Отображается {0} - {1} из {2}',
                            afterPageText: 'из {0}',
                            plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [10, 20, 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000, 2500]})]
                        }),
                        '->', {
                            text: 'Excel',
                            icon: '/images/excel-6_16x16.png',
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
            ]
        });
        tabs.add({
            id: 'HistoryTab',
            constrainHeader: true,
            closable: true,
            layout: {type: 'card'},
            iconCls: 'fa fa fa-history',
            title: '<div style="font-size: 18px; padding-left:15px;">Вся история</div>',
            items: [HistoryGrid]
        }).show();
    }

}

/**
 * История сохранений
 */
function HistorySave() {
    var windo = Ext.getCmp('HistorySave');
    if (!windo) {

        var H_store = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            autoSync: true,
            proxy: {
                type: 'ajax',
                url: '/handlers/get_menu_delivery.php?history=1',
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
                {name: 'fio', type: 'string'},
                {name: 'phone', type: 'string'},
                {name: 'addr', type: 'string'},
                {name: 'country', type: 'string'},
                {name: 'offer', type: 'string'},
                {name: 'kz_delivery', type: 'string'},
                {name: 'kz_operator', type: 'string'},
                {name: 'kz_code', type: 'string'},
                {name: 'tip', type: 'string'}
            ]
        });
        var Hfilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'string'},
                {dataIndex: 'phone', type: 'string'},
                {dataIndex: 'fio', type: 'string'},
                {dataIndex: 'last_edit', type: 'list', phpMode: true, options: globalCallOperatorStore.ValuesArr},
                {dataIndex: 'kz_operator', type: 'list', phpMode: true, options: globalOperatorLogistStore.ValuesArr},
                {dataIndex: 'kz_delivery', type: 'list', phpMode: true, options: globalDeliveryCouriersStore.ValuesArr},
                {dataIndex: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'fill_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'kz_code', type: 'string'},
                {dataIndex: 'offer', type: 'list', phpMode: true, options: globalOffersStore.ValuesArr},
                {dataIndex: 'country', type: 'list', phpMode: true, options: globalCountriesStore.ValuesArr}
            ]
        });
        var HGrid = new Ext.grid.GridPanel({
            frame: true,
            autoScroll: false,
            loadMask: true,
            height: 580,
            id: 'H_grid',
            store: H_store,
            features: [Hfilters],
            columns: [
                {dataIndex: 'id', width: 80, header: 'ID'},
                {dataIndex: 'fio', width: 240, header: 'ФИО'},
                {dataIndex: 'phone', width: 120, header: 'Телефон'},
                {dataIndex: 'addr', width: 180, header: 'Адрес'},
                {dataIndex: 'country', width: 100, header: 'Локация'},
                {dataIndex: 'offer', width: 140, header: 'Тип продукта'},
                {dataIndex: 'kz_code', width: 130, header: 'Barcode'}
            ],
            listeners: {
                itemdblclick: {
                    fn: function (grid, record, item, index, event) {
                        var record = grid.getStore().getAt(index);
                        Ext.create('Ext.window.Window', {
                            title: 'History id - ' + record.data.id,
                            height: 600,
                            width: 800,
                            autoScroll: true,
                            layout: 'fit',
                            html: record.data.tip
                        }).show();
                    }
                }
            },
            bbar: new Ext.PagingToolbar({
                store: H_store,
                beforePageText: 'Страница',
                displayMsg: 'Отображается {0} - {1} из {2}',
                afterPageText: 'из {0}',
                displayInfo: true,
                plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [20, 50, 100, 200, 500, 1000]})]
            }),
            viewConfig: {
                forceFit: true,
                enableTextSelection: true,
                showPreview: true,
                enableRowBody: true
            }
        });
        HGrid.store.load();
        var windo = Ext.create('Ext.Window', {
            id: 'HistorySave',
            width: 1000,
            height: 500,
            autoScroll: true,
            title: 'История сохранений',
            iconCls: 'staff-list',
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            flex: true,
            maximizable: true,
            stateId: 'HistorySave',
            items: [HGrid]
        });
    }
    windo.show();
}
