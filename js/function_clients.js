
globalClientDealStatusesStore = new Ext.data.Store({
    storeId: 'globalClientDealStatusesStore',
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
globalClientDealStatusesStore.loadRawData([
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
// Вкладка "Клиенты"
function ClientsTab(tabs) {

    var tab = tabs.queryById('ClientTab');
    if (tab) {
        tab.show();
    } else {
// Хранилище "Клиенты"
        var ClientStore = new Ext.data.JsonStore({
            storeId: 'ClientStore',
            autoDestroy: true,
            remoteSort: true,
            autoSync: true,
            autoLoad: true,
            pageSize: 50,
            proxy: {
                type: 'ajax',
                simpleSortMode: true,
                api: {
                    read: '/handlers/get_menu_clients.php'
                },
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'uuid',
                    root: 'data',
                    messageProperty: 'message'
                },
                timeout: 60000,
                writer: {
                    type: 'json'
                },
                afterRequest: function (request, success) {
                    if (request.action === 'read') {
                        var excelExportBtn = Ext.getCmp('btnExcelClientStore');
                        var grid = excelExportBtn ? excelExportBtn.up('grid') : (ClientsGrid ? ClientsGrid : Ext.getCmp('Client_grid'));
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
                                grid.commonIdsDataArr.push(rec.get('uuid'));
                                grid.commonIdsDataStr += rec.get('uuid') + ',';
                            });
                            grid.commonIdsDataJson = Ext.JSON.encode(grid.commonIdsDataArr);
                            var DocumMenuButton = grid.down('#DocumClientButton');
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
                    var grid = ClientsGrid ? ClientsGrid : Ext.getCmp('Client_grid');
                    if (grid) {
                        grid.commonIdsDataArr = [];
                        grid.commonIdsDataStr = '';
                        grid.commonIdsDataJson = Ext.JSON.encode(grid.commonIdsDataArr);
                        var DocumMenuButton = grid.down('#DocumClientButton');
                        if (DocumMenuButton) {
                            DocumMenuButton.setDisabled(grid.commonIdsDataArr.length === 0);
                        }
                    }
                }
            },
            fields: [
                {name: 'uuid', type: 'string'},
                {name: 'order_id', type: 'string'},
                {name: 'country', type: 'string'},
                {name: 'client_group', type: 'number'},
                {name: 'client_team', type: 'numeric'},
                {name: 'oper_use', type: 'numeric'},
                {name: 'phone', type: 'string'},
                {name: 'phone_orig', type: 'string'},
                {name: 'fio', type: 'string'},
                {name: 'client_deal_status', type: 'number'},
                {name: 'client_deal_status_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'client_recall_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'updated_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'updated_by'},
                {name: 'responsible', type: 'numeric'},
                {name: 'order_first_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'order_first_offer', type: 'string'},
                {name: 'order_first_group', type: 'string'},
                {name: 'order_all_count', type: 'numeric'},
                {name: 'order_all_total', type: 'numeric'},
                {name: 'order_all_offer', type: 'string'},
                {name: 'order_all_group', type: 'string'},
                {name: 'order_last_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'order_last_offer', type: 'string'},
                {name: 'order_last_group', type: 'string'},
                {name: 'order_first_staff_id', type: 'numeric'},
                {name: 'order_last_staff_id', type: 'numeric'},
                {name: 'order_last_status', type: 'string'},
                {name: 'order_last_status_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'order_last_status_description', type: 'string'},
                {name: 'order_delivery_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'order_kz_delivery', type: 'string'},
                {name: 'order_pay_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'order_payed_count', type: 'numeric'},
                {name: 'order_payed_2020_count', type: 'numeric'},
                {name: 'order_payed_total', type: 'number'},
                {name: 'order_payed_2020_total', type: 'number'},
                {name: 'client_worries', type: 'string'},
                {name: 'order_cancel_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'order_cancel_count', type: 'numeric'},
                {name: 'addr', type: 'string'},
                {name: 'comment', type: 'string'},
                {name: 'decline_reason', type: 'string'},
                {name: 'sex', type: 'numeric'},
                {name: 'age', type: 'numeric'},
                {name: 'birthday', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'lang', type: 'string'},
                {name: 'call_count', type: 'numeric'},
                {name: 'call_count_now', type: 'numeric'},
                {name: 'call_count_hand', type: 'numeric'},
                {name: 'visible', type: 'bool'},
                {name: 'rings', type: 'string'}
            ]
        });
        // Фильтра "Клиенты"
        var FilterClients = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'uuid', type: 'string'},
                {dataIndex: 'order_id', type: 'string'},
                {dataIndex: 'fio', type: 'string'},
                {dataIndex: 'phone', type: 'string'},
                {dataIndex: 'addr', type: 'string'},
                {dataIndex: 'comment', type: 'string'},
                {dataIndex: 'decline_reason', type: 'string'},
                {dataIndex: 'order_delivery_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'order_kz_delivery', type: 'list', phpMode: true, options: globalDeliveryCouriersStore.ValuesArr},
                {dataIndex: 'country', type: 'list', phpMode: true, options: globalCountriesStore.ValuesArr},
                {dataIndex: 'order_all_group', type: 'list', phpMode: true, options: globalOfferGroupsStore.ValuesGroupArr},
                {dataIndex: 'order_last_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'order_first_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'order_first_offer', type: 'string'},
                {dataIndex: 'order_first_group', type: 'list', phpMode: true, options: globalOfferGroupsStore.ValuesGroupArr},
                {dataIndex: 'order_last_offer', type: 'string'},
                {dataIndex: 'order_last_group', type: 'list', phpMode: true, options: globalOfferGroupsStore.ValuesGroupArr},
                {dataIndex: 'order_last_status', type: 'list', phpMode: true,
                    options: [
                        ['Брак', 'Брак'],
                        ['Груз отправлен', 'Груз отправлен'],
                        ['Заказ уже обработан', 'Заказ уже обработан'],
                        ['Заказано у конкурентов', 'Заказано у конкурентов'],
                        ['Недб', 'Недб'],
                        ['Недозвон', 'Недозвон'],
                        ['недозвон_ночь', 'недозвон_ночь'],
                        ['новая', 'новая'],
                        ['Оплачен', 'Оплачен'],
                        ['Отказ', 'Отказ'],
                        ['Отменён', 'Отменён'],
                        ['Отправлен', 'Отправлен'],
                        ['Перезвонить', 'Перезвонить'],
                        ['Предварительно подтвержден', 'Предварительно подтвержден'],
                        ['Уже получил заказ', 'Уже получил заказ'],
                        ['Черный список', 'Черный список'],
                        ['Предоплата', 'Предоплата'],
                        ['Полная предоплата', 'Полная предоплата'],
                        ['Отказ-предоплата', 'Отказ-предоплата']
                    ]
                },
                {dataIndex: 'order_last_status_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'order_last_status_description', type: 'string'},
                {dataIndex: 'order_first_staff_id', type: 'list', phpMode: true, options: globalPartnersStore.ValuesExtendedFiltersArr},
                {dataIndex: 'order_last_staff_id', type: 'list', phpMode: true, options: globalPartnersStore.ValuesExtendedFiltersArr},
                {dataIndex: 'order_all_count', type: 'numeric'},
                {dataIndex: 'order_all_total', type: 'numeric'},
                {dataIndex: 'order_all_offer', type: 'string'},
                {dataIndex: 'order_all_group', type: 'list', phpMode: true, options: globalOfferGroupsStore.ValuesGroupArr},
                {dataIndex: 'oper_use', type: 'list', phpMode: true,
                    options: [
                        [-1, '- Все кроме 0 -'], [0, '0'], [1, '1'], [2, '2'], [3, '3'], [4, '4'], [5, '5'], [6, '6'], [7, '7'], [8, '8'], [9, '9'], [10, '10'],
                        [11, '11'], [12, '12'], [13, '13'], [14, '14'], [15, '15'], [16, '16'], [17, '17'], [18, '18'], [19, '19'], [20, '20'],
                        [21, '21'], [22, '22'], [23, '23'], [24, '24'], [25, '25'], [26, '26'], [27, '27'], [28, '28'], [29, '29'], [30, '30'],
                        [31, '31'], [32, '32'], [33, '33'], [34, '34'], [35, '35'], [36, '36'], [37, '37'], [38, '38'], [39, '39'], [40, '40'],
                        [41, '41'], [42, '42'], [44, '44'], [44, '44'], [45, '45'], [46, '46'], [47, '47'], [48, '48'], [49, '49'], [50, '50'],
                        [51, '51'], [52, '52'], [53, '53'], [54, '54'], [55, '55'], [56, '56'], [57, '57'], [58, '58'], [59, '59'], [60, '60'],
                        [61, '61'], [62, '62'], [63, '63'], [64, '64'], [65, '65'], [66, '66'], [67, '67'], [68, '68'], [69, '69'], [70, '70'],
                        [71, '71'], [72, '72'], [73, '73'], [74, '74'], [75, '75'], [76, '76'], [77, '77'], [78, '78'], [79, '79'], [80, '80'],
                        [81, '81'], [82, '82'], [83, '83'], [84, '84'], [85, '85'], [86, '86'], [87, '87'], [88, '88'], [89, '89'], [90, '90'],
                        [91, '91'], [92, '92'], [93, '93'], [94, '94'], [95, '95'], [96, '96'], [97, '97'], [98, '98'], [99, '99'], [100, '100']
                    ]
                },
                {dataIndex: 'client_group', type: 'list', phpMode: true, options: [['-1', 'Не определена'], ['1', 'Группа 1'], ['2', 'Группа 2'], ['3', 'Группа 3'], ['4', 'Группа 4'], ['5', 'Группа 5'], ['6', 'Группа 6'], ['7', 'Группа 7']]},
                {dataIndex: 'client_team', type: 'list', phpMode: true, options: [['-1', 'Без отдела'], ['1', 'Отдел 1'], ['2', 'Отдел 2'], ['3', 'Отдел 3'], ['4', 'Отдел 4'], ['5', 'Отдел 5'], ['6', 'Отдел 6'], ['7', 'Отдел 7'], ['8', 'Отдел 8'], ['9', 'Отдел 9'], ['10', 'Отдел 10']]},
                {dataIndex: 'responsible', type: 'list', phpMode: true, options: globalResponsibleStore.ValuesExtendedFiltersArr},
                {dataIndex: 'order_payed_count', type: 'numeric'},
                {dataIndex: 'order_payed_2020_count', type: 'numeric'},
                {dataIndex: 'client_deal_status', type: 'list', phpMode: true, options: globalClientDealStatusesStore.ValuesArr},
                {dataIndex: 'client_deal_status_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'client_recall_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'call_count', type: 'numeric'},
                {dataIndex: 'call_count_now', type: 'numeric'},
                {dataIndex: 'call_count_hand', type: 'numeric'},
                {dataIndex: 'order_cancel_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'order_pay_date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'order_payed_total', type: 'numeric'},
                {dataIndex: 'order_payed_2020_total', type: 'numeric'},
                {dataIndex: 'client_worries', type: 'string'},
                {dataIndex: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'updated_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'updated_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr},
                {dataIndex: 'lang', type: 'list', phpMode: true, options: globalCountriesStore.ValuesLangArr}
            ]
        });
        var ClientsGrid = new Ext.grid.GridPanel({
            id: 'ClientGridId',
            loadMask: true,
            forceFit: false,
            stateful: true,
            stateId: 'ClientGridStateId',
            store: ClientStore,
            features: [
                FilterClients,
                {ftype: 'summary', dock: 'top'}
            ],
            selType: 'rowmodel',
            viewConfig: {
                enableTextSelection: true,
                showPreview: true,
                enableRowBody: true
            },
            columns: [
                {dataIndex: 'uuid', width: 80, header: 'ID'},
                {dataIndex: 'order_id', width: 80, header: '<span style="color:blue;font-size: initial;">&#128269;</span> ID Заказа', hidden: true},
                {dataIndex: 'country', width: 90, header: 'Локация'},
                {dataIndex: 'phone', width: 80, header: 'Call',
                    renderer: function (v, e, r) {
                        var ret = "&#9990;";
                        var uuid = r.get('uuid');
                        var tmp_phone = v;
                        var tmp2_phone = r.get('phone_sms');
                        var tmp_country = r.get('country');
                        var pre_post = '';
                        if (tmp_country === 'kzg') {
                            if (tmp_phone.length === 10) {
                                tmp_phone = '+996' + tmp_phone.substr(1);
                            } else if (tmp_phone.length === 11) {
                                tmp_phone = '+996' + tmp_phone.substr(2);
                            } else if (tmp_phone.length === 12) {
                                tmp_phone = '+996' + tmp_phone.substr(3);
                            } else {
                                tmp_phone = '+996' + tmp_phone.substr(4);
                            }
                            ret = '<a style="font-size:20px;text-decoration:none;" href="sip:' + (session_operator_bishkek ? '33*' : '58*') + uuid + '">&#9990;</a>';
                        } else if (tmp_country === 'am') {
                            tmp_phone = '+' + tmp_phone;
                            ret = '<a style="font-size:20px;text-decoration:none;" href="sip:23*' + tmp_phone + '#' + uuid + '">&#9990;</a>';
                        } else if (tmp_country === 'az') {
                            tmp_phone = '+' + tmp_phone;
                            ret = '<a style="font-size:20px;text-decoration:none;" href="sip:23*' + tmp_phone + '#' + uuid + '">&#9990;</a>';
                        } else if (tmp_country === 'uz') {
                            tmp_phone = '+' + tmp_phone;
                            ret = '<a style="font-size:20px;text-decoration:none;" href="sip:47*' + tmp_phone + '#' + uuid + '">&#9990;</a>';
                        } else if (tmp_country === 'ae') {
                            tmp_phone = '+' + tmp_phone;
                            ret = '<a style="font-size:20px;text-decoration:none;" href="sip:77*' + tmp_phone + '#' + uuid + '">&#9990;</a>';
                        } else if (tmp_country === 'ru') {
                            tmp_phone = '7' + tmp_phone.substr(1);
                            ret = '<a style="font-size:20px;text-decoration:none;" href="sip:7*' + tmp_phone + '#' + uuid + '">&#9990;</a>';
                        } else {
                            tmp_phone = '8' + tmp_phone.substr(1);
                            var prefixKostyl = '5*';
                            if ([67402570, 66120779, 39997689, 14249018, 11001013].indexOf(globalStaffId) > -1 || session_operatorrecovery) {
                                prefixKostyl = '5*';
                            }
                            ret = '<a style="font-size:20px;text-decoration:none;" href="sip:' + prefixKostyl + tmp_phone + '#' + uuid + '">&#9990;</a>' + ' ' + pre_post;
                        }

                        return ret;
                    }
                },
                {dataIndex: 'phone', width: 90, header: '<span style="color:blue;font-size: initial;">&#9990;</span> Телефон',
                    renderer: function (val, e, r) {
                        return ['kz', 'kzg'].indexOf(r.get('country')) > -1 ? '' : val;
                    }
                },
                {dataIndex: 'comment', width: 150, header: 'Комментарий'},
                {dataIndex: 'decline_reason', width: 150, header: 'Причина не соглася'},
                {dataIndex: 'client_group', width: 90, header: '<span style="color:blue;font-size: initial;">&#9875;</span> Группа клиента'},
                {dataIndex: 'client_team', width: 120, header: 'Отдел продаж'},
                {dataIndex: 'oper_use', width: 60, header: '<span style="color:blue;font-size: initial;">&#9873;</span> В работе у'},
                {dataIndex: 'fio', width: 160, header: 'ФИО'},
                {dataIndex: 'client_deal_status', width: 100, header: 'Статус подписки',
                    renderer: function (v) {
                        return globalClientDealStatusesStore.ValuesJson[v] ? globalClientDealStatusesStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'client_deal_status_date', width: 150, header: '<span style="color:blue;font-size: initial;">&#8986;</span> Дата статуса сделки', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'client_recall_date', width: 150, header: '<span style="color:blue;font-size: initial;">&#9742;</span> Перезвонить', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'created_at', width: 150, header: 'Дата добавления клиента', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'updated_at', width: 150, header: '<span style="color:blue;font-size: initial;">&#128269;</span> Дата изменения клиента', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'), hidden: false},
                {dataIndex: 'updated_by', width: 150, header: '<span style="color:blue;font-size: initial;">&copy;</span> Личный менеджер клиента', sortable: true, hidden: false,
                    renderer: function (v) {
                        return globalManagerStore.ValuesJson[v] ? globalManagerStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'responsible', width: 200, header: 'Ответственный',
                    renderer: function (v) {
                        return globalResponsibleStore.ValuesJson[v] ? globalResponsibleStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'order_first_date', width: 150, header: '<span style="color:blue;font-size: initial;">&uArr;</span> Дата перв добавл заказа', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'order_first_offer', width: 150, header: '<span style="color:blue;font-size: initial;">&uArr;</span> Перв добавл тов.', hidden: false},
                {dataIndex: 'order_first_group', width: 150, header: '<span style="color:blue;font-size: initial;">&uArr;</span> Перв гр. тов.'},
                {dataIndex: 'order_all_count', width: 90, header: '<span style="color:blue;font-size: initial;">&sum;</span> Всего заказов'},
                {dataIndex: 'order_all_total', width: 90, header: '<span style="color:blue;font-size: initial;">&sum;</span> Всего на сумму'},
                {dataIndex: 'order_all_offer', width: 90, header: '<span style="color:blue;font-size: initial;">&#8660;</span> Все тов.'},
                {dataIndex: 'order_all_group', width: 150, header: '<span style="color:blue;font-size: initial;">&#8660;</span> Все группы тов.'},
                {dataIndex: 'order_first_staff_id', width: 100, header: '<span style="color:blue;font-size: initial;">&uArr;</span> Перв источник', renderer: function (v, p, r) {
                        return globalPartnersStore.ValuesJson[v] ? globalPartnersStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'order_last_staff_id', width: 100, header: '<span style="color:blue;font-size: initial;">&dArr;</span> Посл источник', renderer: function (v, p, r) {
                        return globalPartnersStore.ValuesJson[v] ? globalPartnersStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'order_last_date', width: 150, header: '<span style="color:blue;font-size: initial;">&dArr;</span> Дата посл добавл заказа', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'order_last_offer', width: 150, header: '<span style="color:blue;font-size: initial;">&dArr;</span> Посл тов.', hidden: false},
                {dataIndex: 'order_last_group', width: 150, header: '<span style="color:blue;font-size: initial;">&dArr;</span> Посл гр. тов.'},
                {dataIndex: 'order_last_status', width: 150, header: '<span style="color:blue;font-size: initial;">&#10004;</span> Статус посл заказа'},
                {dataIndex: 'order_last_status_date', width: 150, header: '<span style="color:blue;font-size: initial;">&#8986;</span> Дата статуса посл заказа', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'order_last_status_description', width: 150, header: '<span style="color:blue;font-size: initial;">&#9997;</span> Коммент статуса посл заказа'},
                {dataIndex: 'order_kz_delivery', width: 120, header: 'Тип доставки'},
                {dataIndex: 'order_pay_date', width: 150, header: '<span style="color:blue;font-size: initial;">&dArr;</span> Посл дата оплаты', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'order_payed_count', width: 90, header: '<span style="color:blue;font-size: initial;">&sum;</span> Оплаченных заказов'},
                {dataIndex: 'order_payed_2020_count', width: 90, header: '<span style="color:blue;font-size: initial;">&sum;</span> 2020 оплаченных заказов'},
                {dataIndex: 'order_payed_total', width: 90, header: '<span style="color:blue;font-size: initial;">&#36;</span> Итоговая касса'},
                {dataIndex: 'order_payed_2020_total', width: 90, header: '<span style="color:blue;font-size: initial;">&#36;</span> 2020 итоговая касса'},
                {dataIndex: 'order_count_percent', width: 90, header: '%  выкупа',
                    renderer: function (v, e, r) {
                        return  Ext.util.Format.number(r.get('order_payed_count') / r.get('order_all_count') * 100, '0.00 %');
                    }
                },
                {dataIndex: 'order_cancel_date', width: 150, header: '<span style="color:blue;font-size: initial;">&dArr;</span> Посл дата отмены', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'order_cancel_count', width: 90, header: '<span style="color:blue;font-size: initial;">&sum;</span> Всего отменено'},
                {dataIndex: 'addr', width: 150, header: 'Адрес'},
                {dataIndex: 'client_worries', width: 150, header: 'Недуги/беспокойства'},
                {dataIndex: 'call_count', width: 40, header: 'К-во наб.'},
                {dataIndex: 'call_count_hand', width: 30, header: 'Ручн.'},
                {dataIndex: 'call_count_now', width: 50, header: 'К-во сег.'},
                {dataIndex: 'rings', width: 120, header: 'Звонки'},
                {dataIndex: 'lang', width: 90, header: 'Язык',
                    renderer: function (v, p, r) {
                        return globalCountriesStore.ValuesLangJson[v] ? globalCountriesStore.ValuesLangJson[v] : v;
                    }
                }
            ],
            listeners: {
                itemdblclick: function (grid, record, item, index, event) {
                    if (record.get('client_deal_status') !== 7) {
                        CreateMenuClient(record.data.uuid, record.data.country);
                    } else {
                        Ext.example.msg('Заказ клиента находится в обработке', '', 'sms');
                    }
                }
            },
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'top',
                    items: [
                        {
                            text: ' Массовые изменения',
                            icon: '/images/changes-5_24x24.png',
                            scale: 'medium',
                            hidden: (session_admin + session_IsCurator + session_IsResponsible) < 1,
                            handler: function (b, e) {
                                if (b.up('grid').commonIdsDataArr.length === 0) {
                                    return;
                                }


                                var prompt;
                                if (!prompt) {
                                    var massChangeStore = [
                                        ['oper_use', 'В работе у'],
                                        ['client_deal_status', 'Статус подписки']
                                    ];
                                    if ([11111111, 63077972, 57637454, 44917943, 36710186, 17729178].indexOf(globalStaffId) > -1) {
                                        massChangeStore.push(['client_team', 'Отдел продаж']);
                                    }
                                    var prompt = Ext.create('Ext.window.Window', {
                                        width: 400,
                                        layout: 'fit',
                                        title: 'Изменение',
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
                                                        id: 'clients_send_type',
                                                        fieldLabel: 'Тип изменения',
                                                        valueField: 'value',
                                                        displayField: 'value',
                                                        store: massChangeStore,
                                                        listeners: {
                                                            select: function () {

                                                                var sendClientsStatusCombobox = Ext.getCmp('sendClientsStatusCombobox');
                                                                if (this.value === 'client_group') {
                                                                    var data = [
                                                                        {'id': 1, 'name': 'Группа 1'}, {'id': 2, 'name': 'Группа 2'}, {'id': 3, 'name': 'Группа 3'}, {'id': 4, 'name': 'Группа 4'}, {'id': 5, 'name': 'Группа 5'}, {'id': 6, 'name': 'Группа 6'}
                                                                    ];
                                                                    sendClientsStatusCombobox.reset();
                                                                    var massChangeValueStore = Ext.data.StoreManager.lookup('massChangeValueStore');
                                                                    massChangeValueStore.removeAll();
                                                                    massChangeValueStore.add(data);
                                                                    sendClientsStatusCombobox.setEditable(false);
                                                                } else if (this.value === 'client_team') {
                                                                    var data = [
                                                                        {'id': 0, 'name': 'Без Отдела'}, {'id': 1, 'name': 'Отдел 1'}, {'id': 2, 'name': 'Отдел 2'},
                                                                        {'id': 3, 'name': 'Отдел 3'}, {'id': 4, 'name': 'Отдел 4'}, {'id': 5, 'name': 'Отдел 5'}
                                                                    ];
                                                                    sendClientsStatusCombobox.reset();
                                                                    var massChangeValueStore = Ext.data.StoreManager.lookup('massChangeValueStore');
                                                                    massChangeValueStore.removeAll();
                                                                    massChangeValueStore.add(data);
                                                                    sendClientsStatusCombobox.setEditable(false);
                                                                } else if (this.value === 'client_deal_status') {
                                                                    var data = [
                                                                        {'id': 0, 'name': 'Не определен'}
                                                                    ];
                                                                    sendClientsStatusCombobox.reset();
                                                                    var massChangeValueStore = Ext.data.StoreManager.lookup('massChangeValueStore');
                                                                    massChangeValueStore.removeAll();
                                                                    massChangeValueStore.add(data);
                                                                    sendClientsStatusCombobox.setEditable(false);
                                                                } else if (this.value === 'oper_use') {
                                                                    var data = [
                                                                        [0, '0'], [1, '1'], [2, '2'], [3, '3'], [4, '4'], [5, '5'], [6, '6'], [7, '7'], [8, '8'], [9, '9'], [10, '10'],
                                                                        [11, '11'], [12, '12'], [13, '13'], [14, '14'], [15, '15'], [16, '16'], [17, '17'], [18, '18'], [19, '19'], [20, '20'],
                                                                        [21, '21'], [22, '22'], [23, '23'], [24, '24'], [25, '25'], [26, '26'], [27, '27'], [28, '28'], [29, '29'], [30, '30'],
                                                                        [31, '31'], [32, '32'], [33, '33'], [34, '34'], [35, '35'], [36, '36'], [37, '37'], [38, '38'], [39, '39'], [40, '40'],
                                                                        [41, '41'], [42, '42'], [44, '44'], [44, '44'], [45, '45'], [46, '46'], [47, '47'], [48, '48'], [49, '49'], [50, '50'],
                                                                        [51, '51'], [52, '52'], [53, '53'], [54, '54'], [55, '55'], [56, '56'], [57, '57'], [58, '58'], [59, '59'], [60, '60'],
                                                                        [61, '61'], [62, '62'], [63, '63'], [64, '64'], [65, '65'], [66, '66'], [67, '67'], [68, '68'], [69, '69'], [70, '70'],
                                                                        [71, '71'], [72, '72'], [73, '73'], [74, '74'], [75, '75'], [76, '76'], [77, '77'], [78, '78'], [79, '79'], [80, '80'],
                                                                        [81, '81'], [82, '82'], [83, '83'], [84, '84'], [85, '85'], [86, '86'], [87, '87'], [88, '88'], [89, '89'], [90, '90'],
                                                                        [91, '91'], [92, '92'], [93, '93'], [94, '94'], [95, '95'], [96, '96'], [97, '97'], [98, '98'], [99, '99'], [100, '100']
                                                                    ];
                                                                    sendClientsStatusCombobox.reset();
                                                                    var massChangeValueStore = Ext.data.StoreManager.lookup('massChangeValueStore');
                                                                    massChangeValueStore.removeAll();
                                                                    massChangeValueStore.add(data);
                                                                }
                                                            }
                                                        }
                                                    }, {
                                                        xtype: 'combo',
                                                        id: 'sendClientsStatusCombobox',
                                                        fieldLabel: 'Значение',
                                                        editable: false,
                                                        queryMode: 'local',
                                                        store: Ext.data.StoreManager.lookup('massChangeValueStore'),
                                                        displayField: 'name',
                                                        valueField: 'id'
                                                    }, {
                                                        xtype: 'numberfield',
                                                        fieldLabel: 'Кво ограничения',
                                                        id: 'clietns_stogr',
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
                                                                url: '/handlers/set_Clients_MasKz.php',
                                                                method: 'POST',
                                                                params: {
                                                                    type: Ext.getCmp('clients_send_type').getValue(),
                                                                    status: Ext.getCmp('sendClientsStatusCombobox').getValue(),
                                                                    ogr: Ext.getCmp('clietns_stogr').getValue(),
                                                                    ids_data: b.up('grid').commonIdsDataJson
                                                                },
                                                                success: function (response) {
                                                                    prompt.close();
                                                                    ClientsGrid.getStore().reload();
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
                                                ]
                                            }
                                        ]
                                    });
                                }
                                prompt.show();
                            }
                        },
                        {
                            text: 'Стат "В работе У"',
                            icon: '/images/users_group-3_24x24.png',
                            scale: 'medium',
                            handler: get_StatClientsOperUse()
                        }
                    ]
                }, {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        new Ext.PagingToolbar({
                            store: ClientStore,
                            beforePageText: 'Страница',
                            displayMsg: 'Отображается {0} - {1} из {2}',
                            afterPageText: 'из {0}',
                            displayInfo: true,
                            plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [10, 20, 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1500, 2000, 2500]})]
                        }), {
                            text: 'SMS текст',
                            icon: '/images/sms_chat-color.png',
                            hidden: (session_admin + session_adminlogistpost + session_adminlogist) < 1,
                            handler: function (b, e) {
                                if (b.up('grid').commonIdsDataArr.length === 0) {
                                    return false;
                                }
                                var prompt;
                                if (!prompt) {
                                    prompt = Ext.create('Ext.window.Window', {
                                        width: 400,
                                        height: 290,
                                        layout: 'form',
                                        title: 'Отправка SMS',
                                        icon: '/images/sms_chat-color.png',
                                        items: [{
                                                xtype: 'form',
                                                width: 380,
                                                autoHeight: true,
                                                id: 'text_form',
                                                bodyStyle: 'padding: 10px 10px 10px 10px;',
                                                labelWidth: 50,
                                                defaults: {
                                                    anchor: '95%',
                                                    allowBlank: false,
                                                    msgTarget: 'side'
                                                },
                                                items: [
                                                    {
                                                        xtype: 'textareafield',
                                                        name: 'text_kz',
                                                        id: 'text_kz',
                                                        anchor: '80%',
                                                        minLength: 10,
                                                        enableKeyEvents: true,
                                                        maxLength: 140,
                                                        listeners: {
                                                            'keyup': function () {
                                                                var newValue = 140 - Ext.getCmp('text_kz').getValue().length;
                                                                Ext.get('numChar').update(newValue);
                                                            }
                                                        },
                                                        labelWidth: 50,
                                                        fieldLabel: 'Текст',
                                                        allowBlank: false
                                                    }, {
                                                        xtype: 'displayfield',
                                                        id: 'numChar',
                                                        value: '70'
                                                    }, {
                                                        xtype: 'checkbox',
                                                        fieldLabel: 'Киргизия?',
                                                        name: 'kgz',
                                                        id: 'is_kg',
                                                        inputValue: '1'
                                                    }
                                                ],
                                                buttons: [
                                                    {
                                                        text: 'Отправить',
                                                        handler: function () {
                                                            fp = Ext.getCmp('text_form');
                                                            if (fp.getForm().isValid()) {
                                                                texts = Ext.getCmp('text_kz').getValue();
                                                                kg = Ext.getCmp('is_kg').getValue();
                                                                Ext.Ajax.request({
                                                                    url: '/handlers/send_SMSText.php?uuids=' + b.up('grid').commonIdsDataStr + '&text=' + texts + ' &kg=' + kg,
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
                                                ]
                                            }
                                        ]
                                    });
                                }
                                prompt.show();
                            }
                        }, '->', {
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
            id: 'ClientTab',
            iconCls: 'fa fa-1x fa-users',
            layout: {type: 'card'},
            title: '<div style="font-size: 18px; padding-left:15px;">Клиенты</div>',
            items: [ClientsGrid],
            closable: true
        }).show();
    }
}

function CreateMenuClient(uuid, country) {
    console.log('Анкета "Клиенты": uuid=' + uuid + ', country=' + country);
    var wind = Ext.getCmp('clientWindow_' + uuid);
    if (wind) {
        wind.show();
    } else {

        var offerGroupsPanelItems = [];
        Ext.Object.each(globalOfferGroupsStore.ValuesGroupJson, function (key, item) {
            if (item.length > 1) {
                offerGroupsPanelItems.push({
                    fieldLabel: item,
                    inputValue: '1',
                    uncheckedValue: '0',
                    disabled: true,
                    name: 'offer_group["' + item + '"]'
                });
            }
        });
        var clientOrderHisroryStore = new Ext.data.Store({
            storeId: 'clientOrderHisroryStore',
            fields: [
                'id',
                'uuid',
                'date',
                'addr',
                'staff_id',
                'offer',
                'package',
                'status',
                'send_status',
                'status_kz',
                'description'
            ]
        });
        var clientOrderHisroryFilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: true,
            filters: [
                {dataIndex: 'uuid', type: 'string'},
                {dataIndex: 'addr', type: 'string'},
                {dataIndex: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'offer', type: 'string'},
                {dataIndex: 'package', type: 'numeric'},
                {dataIndex: 'description', type: 'string'}
            ]
        });
        // Панель "Дубли (история продаж)"
        var clientOrderHisroryGrid = new Ext.grid.GridPanel({
            autoScroll: true,
            minHeight: 200,
            maxHeight: 200,
            id: 'clientOrderHisroryGrid',
            store: clientOrderHisroryStore,
            features: [clientOrderHisroryFilters],
            region: 'north',
            collapsible: true,
            title: 'Дубли (история продаж)',
            collapsed: true,
            columns: [
                {dataIndex: 'id', width: 80, header: 'ID'},
                {dataIndex: 'date', width: 150, header: 'Дата добавления', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
                {dataIndex: 'status', width: 90, header: 'Статус'},
                {dataIndex: 'send_status', width: 90, header: 'Статус отправки'},
                {dataIndex: 'status_kz', width: 90, header: 'Статус посылки'},
                {dataIndex: 'addr', width: 160, header: 'Адрес'},
                {dataIndex: 'staff_id', width: 160, header: 'Источник', renderer: function (v, p, r) {
                        return globalPartnersStore.ValuesJson[v] ? globalPartnersStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'offer', width: 100, header: 'Название товара', renderer: function (v, p, r) {
                        return globalOffersStore.ValuesJson[v] ? globalOffersStore.ValuesJson[v] : v;
                    }
                },
                {dataIndex: 'package', width: 40, header: 'К-во'},
                {dataIndex: 'description', width: 150, header: 'Подстатус (коммент)'}
            ],
            viewConfig: {
                enableTextSelection: true,
                getRowClass: function (record, rowIndex, rp, ds) {
                    return record.data.send_status === 'Оплачен' ? 'inuse' : 'failed';
                }
            }
        });
        // Window
        wind = Ext.create('Ext.Window', {
            title: 'ID клиента - ' + uuid,
            id: 'clientWindow_' + uuid,
            modal: false,
            closable: true,
            height: 550,
            width: 850,
            layout: 'border',
            items: [
                clientOrderHisroryGrid,
                {
                    xtype: 'form',
                    id: 'ClientForm_' + uuid,
                    region: 'center',
                    autoScroll: true,
                    url: '/handlers/get_menu_clients.php?uuid=' + uuid,
                    border: false,
                    padding: 5,
                    defaults: {
                        anchor: '100%',
                        bodyPadding: 2,
                        columnWidth: 0.5
                    },
                    layout: 'column',
                    items: [
                        {
                            xtype: 'fieldset',
                            title: 'Данные клиента',
                            margin: 3,
                            defaults: {
                                anchor: '100%',
                                editable: false
                            },
                            items: [
                                {
                                    xtype: 'hidden',
                                    id: 'have_orders' + uuid,
                                    name: 'have_orders'
                                }, {
                                    xtype: 'hidden',
                                    id: 'uuid' + uuid,
                                    name: 'uuid'
                                }, {
                                    xtype: 'hiddenfield',
                                    name: 'phone_orig',
                                    id: 'phone_orig' + uuid
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Группа клиента',
                                    name: 'client_group'
                                },
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Отдел продаж',
                                    name: 'client_team'
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'ФИО',
                                    name: 'fio'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Страна',
                                    name: 'country',
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    store: globalCountriesStore.ValuesArr,
                                    valueField: 'id',
                                    displayField: 'value'
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Номер карты',
                                    hideTrigger: true,
                                    regex: /\d{16}/,
                                    regexText: 'Не корректный номер карты (16 цифр)',
                                    editable: true,
                                    name: 'client_card_number'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Язык клиента',
                                    name: 'lang',
                                    valueField: 'id',
                                    displayField: 'value',
                                    store: globalCountriesStore.ValuesLangArr,
                                    forceSelection: true,
                                    triggerAction: 'all'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Адрес Клиента',
                                    name: 'addr'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Тип доставки',
                                    name: 'order_kz_delivery',
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    store: globalDeliveryCouriersStore,
                                    valueField: 'id',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Пол',
                                    name: 'sex',
                                    forceSelection: true,
                                    valueField: 'id',
                                    displayField: 'value',
                                    store: [['0', '-Не указан-'], ['1', 'Мужчина'], ['2', 'Женщина']]
                                }, {
                                    xtype: 'datefield',
                                    fieldLabel: 'Дата рождения',
                                    name: 'Birthday',
                                    format: 'd.m.Y'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Недуги/беспокойства',
                                    name: 'client_worries',
                                    maxLength: 96,
                                    allowBlank: false,
                                    labelStyle: 'font-weight: bold; color: black;'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Комментарий',
                                    name: 'comment'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Статус подписки',
                                    name: 'client_deal_status',
                                    id: 'client_deal_status' + uuid,
                                    queryMode: 'local',
                                    store: globalClientDealStatusesStore,
                                    valueField: 'id',
                                    displayField: 'value',
                                    listeners: {
                                        change: function (combo, newValue, oldValue, eOpts) {
                                            var destroyArr = [];
                                            switch (newValue) {
                                                case '1':
                                                    var new_field = {
                                                        xtype: 'combo',
                                                        forceSelection: true,
                                                        name: 'decline_reason',
                                                        id: 'decline_reason' + uuid,
                                                        store: globalCancelTypesStore,
                                                        fieldLabel: 'Причина не согласия',
                                                        valueField: 'value',
                                                        displayField: 'value'
                                                    };

                                                    var parEl = combo.up();
                                                    parEl.insert(parEl.items.indexOf(combo) + 1, new_field);
                                                    destroyArr = ['client_recall' + uuid];
                                                    break;

                                                case '2':
                                                    var new_field = {
                                                        xtype: 'fieldset',
                                                        title: 'Перезвон: дата - время',
                                                        labelAlign: 'right',
                                                        layout: 'column',
                                                        id: 'client_recall' + uuid,
                                                        columns: 2,
                                                        defaults: {
                                                            allowBlank: false,
                                                            labelWidth: '30%',
                                                            columnWidth: 0.5
                                                        },
                                                        items: [
                                                            {
                                                                xtype: 'datefield',
                                                                fieldLabel: 'Дата',
                                                                name: 'client_recall_date',
                                                                startDay: 1,
                                                                format: 'Y-m-d',
                                                                minValue: new Date()
                                                            }, {
                                                                xtype: 'timefield',
                                                                fieldLabel: 'Время',
                                                                name: 'client_recall_time',
                                                                format: 'H:i:s'
                                                            }
                                                        ]
                                                    };

                                                    var parEl = combo.up();
                                                    parEl.insert(parEl.items.indexOf(combo) + 1, new_field);
                                                    destroyArr = ['decline_reason' + uuid];
                                                    break;

                                                default :
                                                    // остальные варианты
                                                    destroyArr = ['client_recall' + uuid, 'decline_reason' + uuid];
                                                    break;
                                            }

                                            if (destroyArr.length > 0) {
                                                destroyElArr(destroyArr);
                                            }
                                        }
                                    }
                                }
                            ]
                        }, {
                            xtype: 'fieldset',
                            hidden: true,
                            title: 'Группы товаров: заказывал (да/нет)',
                            margin: 5,
                            items: [
                                {
                                    xtype: 'checkboxgroup',
                                    columns: 2,
                                    defaults: {
                                        anchor: '100%'
                                    },
                                    items: offerGroupsPanelItems
                                }
                            ]
                        }, {
                            xtype: 'fieldset',
                            title: 'Информация о подписке',
                            margin: 5,
                            layout: 'anchor',
                            defaults: {
                                labelStyle: 'font-weight: bold; color: black;',
                                labelWidth: 150,
                                anchor: '100%'
                            },
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Дата оплаты подписки',
                                    fieldStyle: 'font-weight: bold; color: darkgreen;',
                                    name: 'date_group["start"]',
                                    value: '01.01.2020'
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Дата окончания подписки',
                                    fieldStyle: 'font-weight: bold; color: red;',
                                    name: 'date_group["end"]',
                                    value: '01.03.2020'
                                }
                            ]
                        }, {
                            xtype: 'fieldset',
                            title: 'Успользованные услуги',
                            margin: 5,
                            layout: 'anchor',
                            defaults: {
                                labelStyle: 'font-weight: bold; color: black;',
                                fieldStyle: 'font-weight: bold; color: darkgreen;',
                                anchor: '100%'
                            },
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Фитнес',
                                    name: 'service_group["fitnes"]',
                                    value: 3
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Кофе',
                                    name: 'service_group["coffee"]',
                                    value: 7
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Массаж',
                                    name: 'service_group["massage"]',
                                    value: 5
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Бильярд',
                                    name: 'service_group["billiard"]',
                                    value: 2
                                }
                            ]
                        }, {
                            xtype: 'fieldset',
                            title: 'Итоговые данные', // title or checkboxToggle creates fieldset header
                            margin: 5,
                            layout: 'anchor',
                            items: [
                                {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Итого выкупил на сумму',
                                    labelWidth: 200,
                                    name: 'order_payed_total',
                                    labelStyle: 'font-weight: bold; color: black;',
                                    fieldStyle: 'font-weight: bold; color: darkgreen;'
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Колличество Оплаченных заказов',
                                    labelWidth: 200,
                                    name: 'order_payed_count',
                                    labelStyle: 'font-weight: bold; color: black;',
                                    fieldStyle: 'font-weight: bold; color: darkgreen;'
                                }
                            ]
                        }
                    ]
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
                            id: 'call_me' + uuid,
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
                                if (Ext.getCmp('have_orders' + uuid).getValue() > 0) {
                                    Ext.Msg.alert('Внимание!', 'Запрет на оформление Заказа!!!<br/>ЕСТЬ ДУБЛЬ!!!');
                                } else {
                                    var params = {
                                        uuid: uuid,
                                        staff_id: 22222222
                                    };
                                    createNewOrder(uuid, country, params);
                                    button.up('window').close();
                                }
                            }
                        },
                        '->',
                        {
                            xtype: 'button',
                            text: 'SMS-КБТ',
                            scale: 'medium',
                            icon: '/images/sms_chat-color-24x24.png',
                            handler: function (button) {
                                Ext.Ajax.request({
                                    url: '/handlers/send_kbtsms.php?phones=' + Ext.getCmp('phone_orig' + uuid).getValue(),
                                    success: function (response) {
                                        Ext.Msg.alert('Успех!', 'Сообщение успешно отправлено');
                                    },
                                    failure: function (response, opts) {
                                        Ext.Msg.alert('Ошибка!', 'Ошибка сохранения');
                                    }
                                });
                            }
                        }, {
                            xtype: 'button',
                            text: 'История звонков',
                            scale: 'medium',
                            icon: '/images/support-3_24x24.png',
                            handler: function (button) {
                                showRingsGrid(uuid, country);
                            }
                        }, {
                            xtype: 'button',
                            scale: 'medium',
                            icon: '/images/search_24x24.png',
                            text: 'Показать в доставке',
                            handler: function (button) {
                                console.log('START Показать в доставке');
                                showDeliveryTab(false);
                                var grid = Ext.getCmp('DostavkaGridId');
                                console.log('grid: DostavkaGridId');
                                console.log(grid);
                                if (grid) {
                                    console.log('grid.filters');
                                    console.log(grid.filters);
                                    var uuidFilter = grid.filters.getFilter('uuid');
                                    console.log('uuidFilter');
                                    console.log(uuidFilter);
                                    if (uuidFilter) {
                                        console.log('IF');
//                                        grid.filters.clearFilters();
                                        uuidFilter.setValue(uuid);
                                        grid.filters.reload();
                                        console.log('END');
                                    } else {
                                        console.log('else');
                                        grid.filters.addFilter({dataIndex: 'uuid', type: 'string', value: uuid, active: true});
                                    }
                                    // button.up('window').close();
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
                                        url: '/handlers/set_menu_clients.php?&uuid=' + uuid,
                                        waitMsg: 'Жди...',
                                        success: function (fp, action) {
                                            button.up('window').close();
                                            var grid = Ext.getCmp('ClientGridId');
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
        Ext.getCmp('ClientForm_' + uuid).getForm().load({
            success: function (form, action) {
                clientOrderHisroryStore.loadRawData(action.result.data.orders);
                // START call_me
                var callMeEl = Ext.getCmp('call_me' + uuid);
                var tmp_phone = action.result.data.phone;
                if (country === 'kzg') {
                    if (tmp_phone.length === 10) {
                        tmp_phone = '+996' + tmp_phone.substr(1);
                    } else if (tmp_phone.length === 11) {
                        tmp_phone = '+996' + tmp_phone.substr(2);
                    } else if (tmp_phone.length === 12) {
                        tmp_phone = '+996' + tmp_phone.substr(3);
                    } else {
                        tmp_phone = '+996' + tmp_phone.substr(4);
                    }
                    callMeEl.btnEl.dom.href = 'sip:58*' + uuid;
                } else if (country === 'am') {
                    if (tmp_phone.length === 9) {
                        tmp_phone = '+374' + tmp_phone.substr(1);
                    } else {
                        tmp_phone = '+3' + tmp_phone;
                    }
                    callMeEl.btnEl.dom.href = 'sip:23*' + tmp_phone + '#' + uuid;
                } else if (country === 'az') {
                    callMeEl.btnEl.dom.href = 'sip:16*' + tmp_phone + '#' + uuid;
                } else if (country === 'ae') {
                    callMeEl.btnEl.dom.href = 'sip:77*' + tmp_phone + '#' + uuid;
                } else if (country === 'uz') {
                    callMeEl.btnEl.dom.href = 'sip:47*' + tmp_phone + '#' + uuid;
                } else if (tmp_phone) {
                    tmp_phone = '8' + tmp_phone.substr(1);
                    var prefixKostyl = '5*';
                    if ([67402570, 66120779, 39997689, 14249018, 11001013].indexOf(globalStaffId) > -1 || session_operatorrecovery) {
                        prefixKostyl = '5*';
                    }
                    callMeEl.btnEl.dom.href = 'sip:' + prefixKostyl + tmp_phone + '#' + uuid;
                }
                // END call_me
            }
        });
    }

}

function get_StatClientsOperUse() {
    return false;
    var StatStoreHot = new Ext.data.JsonStore({
        autoDestroy: true,
        remoteSort: true,
        groupField: 'responsible',
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
                idProperty: 'id',
                root: 'data',
                messageProperty: 'message'
            }
        },
        storeId: 'StaffStoreHot',
        fields: [
            {name: 'id', type: 'numeric'},
            {name: 'responsible', type: 'numeric'},
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
        id: 'StatHotGroupingFeatureId',
        ftype: 'groupingsummary',
        enableGroupingMenu: true,
        startCollapsed: true,
        showSummaryRow: true,
        groupHeaderTpl: '{columnName}: {name}'
    };
    var StatGridHot = new Ext.grid.GridPanel({
        autoScroll: true,
        loadMask: true,
        id: 'Stat_gridHot',
        stateful: false,
        stateId: 'Stat_gridHot',
        store: StatStoreHot,
        columns: [
            {dataIndex: 'id', width: 100, header: 'ID Оператора', hidden: true},
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
        listeners: {
            sortchange: function () {
                var params = {
                    p1: Ext.getCmp('stat_CountryClients').getValue(),
                    p2: Ext.getCmp('stat_StartDateClients').getValue().format('Y-m-d'),
                    p3: Ext.getCmp('stat_EndDateClients').getValue().format('Y-m-d'),
                    p4: Ext.getCmp('stat_OfferClients').getValue()
                };
                StatStoreHot.load({method: 'post',
                    params: params
                });
            }
        },
        tbar: [
            {
                xtype: 'combo',
                editable: false,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                width: 160,
                anchor: '100%',
                id: 'stat_CountryClients',
                labelWidth: 45,
                value: 'kz',
                store: [['kz', 'Казахстан'], ["uz", "Узбекистан"], ['am', 'Армения'], ['az', 'Азербайджан'], ['md', 'Молдова'], ['kzg', 'Киргизия'], ['ru', 'Россия'], ['ae', 'OAE']],
                fieldLabel: 'Страна',
                valueField: 'value',
                displayField: 'value'
            }, {
                xtype: 'splitbutton',
                text: 'Даты',
                defaults: {
                    allowBlank: false
                },
                menu: [
                    {
                        xtype: 'datefield',
                        fieldLabel: 'Дата от',
                        startDay: 1,
                        width: 145,
                        format: 'Y-m-d',
                        labelWidth: 40,
                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        itemId: 'date',
                        id: 'stat_StartDateClients'
                    }, {
                        xtype: 'datefield',
                        fieldLabel: 'до',
                        startDay: 1,
                        width: 125,
                        format: 'Y-m-d',
                        labelWidth: 20,
                        value: Ext.Date.format(new Date(), 'Y-m-d'),
                        itemId: 'edate',
                        id: 'stat_EndDateClients'
                    }
                ]
            }, {
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 300,
                anchor: '100%',
                id: 'stat_ResponsibleClients',
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
                width: 250,
                anchor: '100%',
                id: 'stat_OfferClients',
                labelWidth: 40,
                store: globalOffersStore,
                fieldLabel: 'Товар',
                valueField: 'value',
                displayField: 'name'
            }, '->',
            {
                xtype: 'button',
                text: 'Построить',
                handler: function () {
                    var params = {
                        p1: Ext.getCmp('stat_CountryClients').getValue(),
                        p2: Ext.getCmp('stat_StartDateClients').getValue().format('Y-m-d'),
                        p3: Ext.getCmp('stat_EndDateClients').getValue().format('Y-m-d'),
                        p4: Ext.getCmp('stat_OfferClients').getValue(),
                        p100: Ext.getCmp('stat_ResponsibleClients').getValue()
                    };
                    StatStoreHot.load({method: 'post',
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
        height: 550,
        width: 900,
        autoScroll: true,
        spacing: 20,
        layout: 'fit',
        useXAxis: true,
        closable: true,
        id: 'win-statHot',
        slideInDuration: 800,
        slideBackDuration: 1500,
        slideInAnimation: 'elasticIn',
        slideBackAnimation: 'elasticIn',
        items: [StatGridHot]
    }).show();
}
