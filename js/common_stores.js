globalClosedStatusCurArr = [
    'выезд дважды',
    'ОПЛ располовинен',
    'ОПЛ дальний располовинен',
    'ОПЛ дальний',
    'ОПЛ',
    'ОПЛ транспортировка',
    'Выезд дважды Распол',
    'ОПЛ ДОП',
    'Продажа курьер',
    'ОПЛ карта',
    'ОПЛ тс Карта',
    'Продажа курьер распол',
    'ОПЛ карта Располовинен'
];


globalManagerStore = new Ext.data.Store({
    storeId: 'globalManagerStore',
    ValuesArr: [['-1', '-- Не установлен --']],
    ValuesJson: {},
    fields: [],
    listeners: {
        add: function (store, records) {
            Ext.each(records, function (rec) {
                store.ValuesArr.push([rec.get('id'), rec.get('value')]);
                store.ValuesJson[rec.get('id')] = rec.get('value');
            });
        }
    }
});

globalResponsibleStore = new Ext.data.Store({
    storeId: 'globalResponsibleStore',
    ValuesArr: [],
    ValuesJson: {},
    ValuesExtendedFiltersArr: [],
    ValuesOnlyMeArr: [],
    fields: [],
    listeners: {
        add: function (store, records) {
            Ext.each(records, function (rec) {

                if (parseInt(rec.get('id')) === globalStaffId) {
                    store.ValuesOnlyMeArr.push([rec.get('id'), rec.get('value')]);
                }

                if (rec.get('IsResponsible') > 0) {
                    store.ValuesArr.push([rec.get('id'), rec.get('value')]);
                    store.ValuesJson[rec.get('id')] = rec.get('value');

                    store.ValuesExtendedFiltersArr.push([rec.get('id'), rec.get('value')]);
                }
            });
        }
    }
});

globalCuratorStore = new Ext.data.Store({
    storeId: 'globalCuratorStore',
    ValuesArr: [],
    ValuesJson: {},
    fields: [],
    listeners: {
        add: function (store, records) {
            Ext.each(records, function (rec) {
                if (rec.get('IsCurator') > 0) {
                    store.ValuesArr.push([rec.get('id'), rec.get('value')]);
                    store.ValuesJson[rec.get('id')] = rec.get('value');
                }
            });
        }
    }
});

globalManagerAllStore = new Ext.data.Store({
    storeId: 'globalManagerAllStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    ValuesResponsibleJson: {},
    ValuesCuratorJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=manager&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
    fields: ['id', 'value', 'type', 'IsResponsible', 'Responsible', 'IsCurator', 'Curator'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');

                if (rec.data.type > 0) {
                    // globalManagerStore
                    globalManagerStore.add(rec);

                    if (rec.data.IsCurator > 0) {
                        // globalCuratorStore
                        globalCuratorStore.add(rec);
                        globalResponsibleStore.ValuesExtendedFiltersArr.push(['-' + rec.get('id'), ' - ' + rec.get('value') + ' - ']);
                    }
                    if (rec.get('Curator') > 0) {
                        // globalCuratorStore
                        s.ValuesCuratorJson[rec.get('id')] = rec.get('Curator');
                    }
                }
            });

            this.each(function (rec) {
                if (rec.data.type > 0) {
                    // globalResponsibleStore
                    if (rec.get('IsResponsible') > 0) {
                        globalResponsibleStore.add(rec);
                    }
                    if (rec.get('Responsible') > 0) {
                        s.ValuesResponsibleJson[rec.get('id')] = rec.get('Responsible');
                    }

                }
            });
        }
    }
});

globalYesNoStore = new Ext.data.Store({
    storeId: 'globalYesNoStore',
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
globalYesNoStore.loadRawData([
    {id: '0', value: 'Нет'},
    {id: '1', value: 'Да'}
]);

globalCommonInterestedCategoryStore = new Ext.data.Store({
    storeId: 'globalCommonInterestedCategoryStore',
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
globalCommonInterestedCategoryStore.loadRawData([
    {id: '0', value: 'Не указано'},
    {id: '1', value: 'Здоровье'},
    {id: '2', value: 'Омоложение'},
    {id: '3', value: 'Похудение'},
    {id: '4', value: 'Женское здоровье'},
    {id: '5', value: 'Мужское здоровье'},
    {id: '6', value: 'Уход за волосами'}
]);

globalRankStore = new Ext.data.Store({
    storeId: 'globalRankStore',
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
globalRankStore.loadRawData([
    {id: '0', value: '- Не назначен -'},
    {id: '1', value: 'Ранг A'},
    {id: '2', value: 'Ранг B'},
    {id: '3', value: 'Ранг C'},
    {id: '4', value: 'Ранг D'}
]);

gloalGameStatusStore = new Ext.data.Store({
    storeId: 'gloalGameStatusStore',
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
gloalGameStatusStore.loadRawData([
    {id: '0', value: '- Не определен -'},
    {id: '1', value: 'Не дозвонились'},
    {id: '2', value: 'Просит перезвонить'},
    {id: '3', value: 'Регистрируется'},
    {id: '4', value: 'Доступ дан'},
    {id: '5', value: 'Откладывает регистрацию'}
]);

globalControlStatusStore = new Ext.data.Store({
    storeId: 'globalControlStatusStore',
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

var rawData = [
    {id: '', value: '- Не установлено -'},
    {id: 'утверждено', value: 'утверждено'},
    {id: 'найдена ошибка', value: 'найдена ошибка'},
    {id: 'штраф', value: 'штраф'},
    {id: 'на проверке', value: 'на проверке'}
];

if ([11111111, 25937686, 87956601].indexOf(globalStaffId) > -1) {
    rawData.push(
            {id: 'Утверждено КЦ', value: 'Утверждено КЦ'},
    {id: 'Ошибка КЦ', value: 'Ошибка КЦ'}
    );
}
globalControlStatusStore.loadRawData(rawData);

globalCourierGroupStore = new Ext.data.Store({
    storeId: 'globalCourierGroupStore',
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
globalCourierGroupStore.loadRawData([
    {'id': '1', 'value': 'Оператор предоплаты 1'},
    {'id': '2', 'value': 'Оператор предоплаты 2'},
    {'id': '3', 'value': 'Оператор предоплаты 3'},
    {'id': '4', 'value': 'Оператор предоплаты 4'},
    {'id': '5', 'value': 'Оператор предоплаты 5'},
    {'id': '6', 'value': 'Оператор предоплаты 6'},
    {'id': '7', 'value': 'Оператор предоплаты 7'},
    {'id': '8', 'value': 'Оператор предоплаты 8'},
    {'id': '9', 'value': 'Оператор предоплаты 9'},
    {'id': '10', 'value': 'Оператор предоплаты 10'},
    {'id': '11', 'value': 'Оператор предоплаты 11'},
    {'id': '12', 'value': 'Оператор предоплаты 12'},
    {'id': '13', 'value': 'Оператор предоплаты 13'},
    {'id': '14', 'value': 'Оператор предоплаты 14'},
    {'id': '15', 'value': 'Оператор предоплаты 15'},
    {'id': '16', 'value': 'Оператор предоплаты 16'},
    {'id': '17', 'value': 'Оператор предоплаты 17'},
    {'id': '18', 'value': 'Оператор предоплаты 18'},
    {'id': '19', 'value': 'Оператор предоплаты 19'},
    {'id': '20', 'value': 'Оператор предоплаты 20'},
    {'id': '21', 'value': 'Оператор предоплаты 21'},
    {'id': '22', 'value': 'Оператор предоплаты 22'},
    {'id': '23', 'value': 'Оператор предоплаты 23'},
    {'id': '24', 'value': 'Оператор предоплаты 24'},
    {'id': '25', 'value': 'Оператор предоплаты 25'},
    {'id': '26', 'value': 'Оператор предоплаты 26'},
    {'id': '27', 'value': 'Оператор предоплаты 27'},
    {'id': '28', 'value': 'Оператор предоплаты 28'},
    {'id': '29', 'value': 'Оператор предоплаты 29'},
    {'id': '30', 'value': 'Оператор предоплаты 30'},
    {'id': '31', 'value': 'Оператор предоплаты 31'},
    {'id': '32', 'value': 'Оператор предоплаты 32'},
    {'id': '33', 'value': 'Оператор предоплаты 33'},
    {'id': '34', 'value': 'Оператор предоплаты 34'},
    {'id': '35', 'value': 'Оператор предоплаты 35'},
    {'id': '36', 'value': 'Оператор предоплаты 36'},
    {'id': '37', 'value': 'Оператор предоплаты 37'},
    {'id': '38', 'value': 'Оператор предоплаты 38'},
    {'id': '39', 'value': 'Оператор предоплаты 39'},
    {'id': '40', 'value': 'Оператор предоплаты 40'},
    {'id': '41', 'value': 'Оператор предоплаты 41'},
    {'id': '42', 'value': 'Оператор предоплаты 42'},
    {'id': '43', 'value': 'Оператор предоплаты 43'},
    {'id': '44', 'value': 'Оператор предоплаты 44'},
    {'id': '45', 'value': 'Оператор предоплаты 45'},
    {'id': '46', 'value': 'Оператор предоплаты 46'},
    {'id': '47', 'value': 'Оператор предоплаты 47'},
    {'id': '48', 'value': 'Оператор предоплаты 48'},
    {'id': '49', 'value': 'Оператор предоплаты 49'},
    {'id': '50', 'value': 'Оператор предоплаты 50'},
    {'id': '51', 'value': 'Оператор предоплаты 51'},
    {'id': '52', 'value': 'Оператор предоплаты 52'},
    {'id': '53', 'value': 'Оператор предоплаты 53'},
    {'id': '54', 'value': 'Оператор предоплаты 54'},
    {'id': '55', 'value': 'Оператор предоплаты 55'},
    {'id': '56', 'value': 'Оператор предоплаты 56'},
    {'id': '57', 'value': 'Оператор предоплаты 57'},
    {'id': '58', 'value': 'Оператор предоплаты 58'},
    {'id': '59', 'value': 'Оператор предоплаты 59'},
    {'id': '60', 'value': 'Оператор предоплаты 60'}
]);

globalChResponseStore = new Ext.data.Store({
    storeId: 'globalChResponseStore',
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
globalChResponseStore.loadRawData([
    {id: 'Kaspi (ПЛАТЕЖИ)', value: 'Kaspi (ПЛАТЕЖИ)'},
    {id: 'KaspiGold', value: 'KaspiGold'},
    {id: 'qiwi', value: 'qiwi'},
    {id: 'Beeline Card', value: 'Beeline Card'},
    {id: 'SIP номер-Баланс', value: 'SIP номер-Баланс'},
    {id: 'Почтовый перевод', value: 'Почтовый перевод'},
    {id: 'Kcell Card', value: 'Kcell Card'},
    {id: 'Оплатит РОП', value: 'Оплатит РОП'},
    {id: 'Экспресс почта', value: 'Экспресс почта'},
    {id: 'Халык', value: 'Халык'}
]);

globalStatusStore = new Ext.data.Store({
    storeId: 'globalStatusStore',
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
globalStatusStore.loadRawData([
    {id: 'новая', value: 'новая'},
    {id: 'Подтвержден', value: 'Подтвержден'},
    {id: 'Отменён', value: 'Отменён'},
    {id: 'Перезвонить', value: 'Перезвонить'},
    {id: 'Недозвон', value: 'Недозвон'},
    {id: 'Брак', value: 'Брак'},
    {id: 'Предварительно подтвержден', value: 'Предварительно подтвержден'},
    {id: 'недозвон_ночь', value: 'недозвон_ночь'}
]);

globalServicesCategoriesStore = new Ext.data.Store({
    storeId: 'globalServicesCategoriesStore',
    ValuesArr: [],
    ValuesJson: {},
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=services_categories&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
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

globalCitiesKZStore = new Ext.data.Store({
    storeId: 'globalCitiesKZStore',
    ValuesArr: [],
    ValuesJson: {},
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=cities_kz&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
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

globalKetKzOtpravitelStore = new Ext.data.Store({
    storeId: 'globalKetKzOtpravitelStore',
    ValuesArr: [],
    ValuesJson: {},
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=ketkz_otpravitels&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
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

globalCouriersStore = new Ext.data.Store({
    storeId: 'globalCouriersStore',
    ValuesArr: [],
    ValuesJson: {},
    ValuesJsonFull: {},
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=kz_couriers&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
    fields: ['id', 'name', 'sip', 'phone'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('name')]);
                s.ValuesJson[rec.get('id')] = rec.get('name');
                s.ValuesJsonFull[rec.get('id')] = rec.data;
            });
        }
    }
});

globalDeliveryCouriersStore = new Ext.data.Store({
    storeId: 'globalDeliveryCouriersStore',
    ValuesArr: [],
    ValuesJson: {},
    /////////
    ValuesCountryArr: [],
    ValuesCountryJson: {},
    /////////
    ValuesIndexArr: [],
    ValuesIndexJson: {},
    /////////
    ValuesCountryAllowedArr: [],
    ValuesCountryAllowedJson: {},
    /////////
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=delivery_couriers&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
    fields: ['id', 'index', 'city', 'country', 'value', 'custom_color'],
    listeners: {
        load: function (s) {
            var declinedObzvonDeliveryTypes = [
                'Аксу курьер',
                'Аксукент курьер',
                'Арысь курьер',
                'Есик курьер',
                'Жанакорган курьер',
                'Кандыагаш курьер',
                'Капшагай курьер',
                'Каскелен курьер',
                'Ленгер курьер',
                'Мангышлак курьер',
                'Мерке курьер',
                'Рудный курьер',
                'Талгар курьер',
                'Торетам курьер',
                'Узынагаш курьер',
                'Хромтау курьер',
                'Шамалган Село',
                'Шамалган курьер',
                'Шу курьер'
            ];

            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');
                ///////
                s.ValuesIndexArr.push([rec.get('index'), rec.get('value')]);
                s.ValuesIndexJson[rec.get('index')] = rec.get('value');
                ///////
                if (typeof s.ValuesCountryJson[rec.get('country')] === 'undefined') {
                    // Инициализация пустых данных для новой страны
                    s.ValuesCountryArr[rec.get('country')] = [];
                    s.ValuesCountryJson[rec.get('country')] = {};
                    ///////////////////
                    s.ValuesCountryAllowedArr[rec.get('country')] = [];
                    s.ValuesCountryAllowedJson[rec.get('country')] = {};
                    ///////////////////
                    s.ValuesCountryAllowedArr[rec.get('country')].push(['Почта', 'Почта']);
                    s.ValuesCountryAllowedJson[rec.get('country')]['Почта'] = 'Почта';
                }
                s.ValuesCountryArr[rec.get('country')].push([rec.get('id'), rec.get('value')]);
                s.ValuesCountryJson[rec.get('country')][rec.get('id')] = rec.get('value');

                if (declinedObzvonDeliveryTypes.indexOf(rec.get('value')) < 0) {
                    s.ValuesCountryAllowedArr[rec.get('country')].push([rec.get('id'), rec.get('value')]);
                    s.ValuesCountryAllowedJson[rec.get('country')][rec.get('id')] = rec.get('value');
                }
            });
        }
    }
});

globalOffersStore = new Ext.data.Store({
    storeId: 'globalOffersStore',
    ValuesArr: [],
    ValuesJson: {},
    /////////
    ValuesExtendedFiltersArr: [],
    ValuesExtendedFiltersJson: [],
    /////////
    ValuesIdArr: [],
    ValuesIdJson: {},
    /////////
    ValuesAllArr: [],
    /////////
    ValuesColdKzArr: [],
    ValuesColdKgzArr: [],
    ValuesColdUzArr: [],
    /////////
    ValuesPhotoJson: [],
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=offers&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
    fields: ['id', 'value', 'group', 'name', 'price', 'custom_color', 'offer_show_in_cold_kz', 'offer_show_in_cold_kgz', 'offer_show_in_cold_uz', 'offer_photo'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('value'), rec.get('name')]);
                s.ValuesJson[rec.get('value')] = rec.get('name');

                s.ValuesExtendedFiltersArr.push([rec.get('value'), rec.get('value')]);
                s.ValuesExtendedFiltersJson[rec.get('value')] = rec.get('value');

                s.ValuesIdArr.push([rec.get('id'), rec.get('name')]);
                s.ValuesIdJson[rec.get('id')] = rec.get('name');

                s.ValuesAllArr.push(rec.get('value'));
                if (rec.get('offer_show_in_cold_kz') > 0) {
                    s.ValuesColdKzArr.push(rec.get('value'));
                }
                if (rec.get('offer_show_in_cold_kgz') > 0) {
                    s.ValuesColdKgzArr.push(rec.get('value'));
                }
                if (rec.get('offer_show_in_cold_uz') > 0) {
                    s.ValuesColdUzArr.push(rec.get('value'));
                }
                if (rec.get('offer_photo').length > 0) {
                    s.ValuesPhotoJson[rec.get('value')] = rec.get('offer_photo');
                }
            });
        }
    }
});

globalOfferGroupsStore = new Ext.data.Store({
    storeId: 'globalOfferGroupsStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    ValuesGroupArr: [],
    ValuesGroupJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=offer_groups&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
    fields: ['group', 'offer'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.data.group, rec.data.offer]);
                s.ValuesJson[rec.data.offer] = rec.data.group;

                if (typeof s.ValuesGroupJson[rec.data.group] === 'undefined') {
                    s.ValuesGroupArr.push([rec.data.group, rec.data.group === '' ? '-' : rec.data.group]);
                    s.ValuesGroupJson[rec.data.group] = rec.data.group === '' ? '-' : rec.data.group;
                }
            });
        }
    }
});

globalOperatorLogistStore = new Ext.data.ArrayStore({
    storeId: 'globalOperatorLogistStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    ValuesExtendedFiltersArr: [],
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=operator_logist&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
    fields: ['id', 'value', 'kc'],
    listeners: {
        load: function (s) {
            s.ValuesExtendedFiltersArr.push(['-1', '- КС-1 -']);
            s.ValuesExtendedFiltersArr.push(['-2', '- Кроме КС-1 -']);

            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');
                s.ValuesExtendedFiltersArr.push([rec.get('id'), rec.get('value')]);
            });
        }
    }
});

globalIncomeOperStore = new Ext.data.ArrayStore({
    storeId: 'globalIncomeOperStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=incomeoper&key=id&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
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

globalKzAdminStore = new Ext.data.ArrayStore({
    storeId: 'globalKzAdminStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=kz_admin&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
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

globalQueueStore = new Ext.data.ArrayStore({
    storeId: 'globalQueueStore',
    autoLoad: false,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=queue_list&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
    fields: ['id', 'value'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('value'), rec.get('value')]);
                s.ValuesJson[rec.get('value')] = rec.get('value');
            });
        }
    }
});

globalControlAdminStore = new Ext.data.ArrayStore({
    storeId: 'globalControlAdminStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=control_admin&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
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

globalPositionStore = new Ext.data.ArrayStore({
    storeId: 'globalPositionStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=position_man&key=id&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
    fields: ['id', 'value'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('value'), rec.get('value')]);
                s.ValuesJson[rec.get('value')] = rec.get('value');
            });
        }
    }
});

globalObzvonProblemsStore = new Ext.data.ArrayStore({
    storeId: 'globalObzvonProblemsStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=obzvon_problems&key=id&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
    fields: ['id', 'value'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('value'), rec.get('value')]);
                s.ValuesJson[rec.get('value')] = rec.get('value');
            });
        }
    }
});

globalCallOperatorStore = new Ext.data.ArrayStore({
    storeId: 'globalCallOperatorStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=call_operator&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
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

globalCancelTypesStore = new Ext.data.ArrayStore({
    storeId: 'globalCancelTypesStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=cancel_types&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
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

globalDefectTypesStore = new Ext.data.ArrayStore({
    storeId: 'globalDefectTypesStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=defect_types&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
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

globalOtkazTypesStore = new Ext.data.ArrayStore({
    storeId: 'globalOtkazTypesStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=otkaz_types&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
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

globalColdOtkazReasonsStore = new Ext.data.ArrayStore({
    storeId: 'globalColdOtkazReasonsStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=cold_otkaz_reasons&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
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

globalStatusKzStore = new Ext.data.ArrayStore({
    storeId: 'globalStatusKzStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=status_kz&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
    fields: ['id', 'value', 'custom_color'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');
            });
        }
    }
});

globalStatusKzOtkazStore = new Ext.data.ArrayStore({
    storeId: 'globalStatusKzOtkazStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=status_kz_otkaz&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
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

globalSendStatusStore = new Ext.data.ArrayStore({
    storeId: 'globalSendStatusStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=send_status&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
    fields: ['id', 'value', 'custom_color'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');
            });
        }
    }
});

globalStatusCurStore = new Ext.data.ArrayStore({
    storeId: 'globalStatusCurStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    ValuesExtendedFiltersArr: [],
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=status_cur&full=1',
        reader: {
            root: 'data',
            type: 'json'
        }
    },
    fields: ['id', 'value', 'fin_type', 'class'],
    listeners: {
        load: function (s) {
            s.ValuesExtendedFiltersArr.push(['-1', '- ОПЛ Общие -']);
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');
                s.ValuesExtendedFiltersArr.push([rec.get('id'), rec.get('value')]);
            });
        }
    }
});

globalColdOperatorStore = new Ext.data.ArrayStore({
    storeId: 'globalColdOperatorStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    ValuesExtendedFiltersArr: [],
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=cold_operator&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
    fields: ['id', 'value'],
    listeners: {
        load: function (s) {
            s.ValuesExtendedFiltersArr.push(['-1', '- Без оператора -']);
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');
                s.ValuesExtendedFiltersArr.push([rec.get('id'), rec.get('value')]);
            });
        }
    }
});

globalAnketColdStatusStore = new Ext.data.ArrayStore({
    storeId: 'globalAnketColdStatusStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    fields: ['id', 'value'],
    listeners: {
        add: function (store, records) {
            Ext.each(records, function (rec) {
                store.ValuesArr.push([rec.get('id'), rec.get('value')]);
                store.ValuesJson[rec.get('id')] = rec.get('value');
            });
        }
    }
});

globalAnketClientStatusStore = new Ext.data.ArrayStore({
    storeId: 'globalAnketClientStatusStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    fields: ['id', 'value'],
    listeners: {
        add: function (store, records) {
            Ext.each(records, function (rec) {
                store.ValuesArr.push([rec.get('id'), rec.get('value')]);
                store.ValuesJson[rec.get('id')] = rec.get('value');
            });
        }
    }
});

globalColdStatusStore = new Ext.data.ArrayStore({
    storeId: 'globalColdStatusStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=cold_statuses&key=id&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
    fields: ['id', 'value'],
    listeners: {
        load: function (s) {
            var dataAnket = [];
            var dataClient = [];

            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');

                // globalAnketColdStatusStore
                if (['0', '1', '7', '10'].indexOf(rec.get('id')) === -1) {
                    dataAnket.push({
                        'id': rec.get('id'),
                        'value': rec.get('value')
                    });
                }

                // globalAnketClientStatusStore
                if (['5', '7', '10'].indexOf(rec.get('id')) === -1) {
                    dataClient.push({
                        'id': rec.get('id'),
                        'value': rec.get('value')
                    });
                }

            });

            globalAnketColdStatusStore.add(dataAnket);
            globalAnketClientStatusStore.add(dataClient);

        }
    }
});

globalCountriesStore = new Ext.data.Store({
    storeId: 'globalCountriesStore',
    ValuesArr: [],
    ValuesJson: {},
    //////////////
    ValuesLangArr: [],
    ValuesLangJson: {},
    fields: ['id', 'value', 'lang'],
    listeners: {
        load: function (s) {
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');

                s.ValuesLangArr.push([rec.get('id'), rec.get('lang')]);
                s.ValuesLangJson[rec.get('id')] = rec.get('lang');
            });
        }
    }
});
globalCountriesStore.loadRawData([
    {id: '', value: '-', lang: ''},
    {id: 'kz', value: 'Казахстан', lang: 'Казахский'},
    {id: 'kzg', value: 'Киргизия', lang: 'Киргизский'},
    {id: 'uz', value: 'Узбекистан', lang: 'Узбекистанский'},
    {id: 'am', value: 'Армения', lang: 'Армянский'},
    {id: 'az', value: 'Азербайджан', lang: 'Азербайджанский'},
    {id: 'md', value: 'Молдова', lang: 'Молдавский'},
    {id: 'ru', value: 'Россия', lang: 'Русский'},
    {id: 'ae', value: 'OAE', lang: 'Арабский'}
]);

globalStatusIncomeStore = new Ext.data.Store({
    storeId: 'globalStatusIncomeStore',
    ValuesArr: [],
    ValuesJson: {},
    //////////////
    ValuesLangArr: [],
    ValuesLangJson: {},
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
globalStatusIncomeStore.loadRawData([
    {id: '0', value: 'Не определен'},
    {id: '1', value: 'Поставил на доставку'},
    {id: '2', value: 'Поставил на од'},
    {id: '3', value: 'Поставил курьерский статус'},
    {id: '4', value: 'Перенаправил в ОП'},
    {id: '5', value: 'Не закрыл вопрос'},
    {id: '6', value: 'Закрыл вопрос'},
    {id: '7', value: 'Перенаправил в отдел Почты'},
    {id: '8', value: 'Перенаправил в отдел КС'},
    {id: '9', value: 'Перенаправил к Замене, Возврату'},
    {id: '10', value: 'Перенаправил в Кеткз'},
    {id: '11', value: 'Поставил отказ'},
    {id: '12', value: 'Убрал в черный список "Хол брак"'},
    {id: '13', value: 'Сброс от клиента'}
]);

globalPartnersStore = new Ext.data.ArrayStore({
    storeId: 'globalPartnersStore',
    autoLoad: true,
    ValuesArr: [],
    ValuesJson: {},
    ValuesExtendedFiltersArr: [],
    proxy: {
        type: 'ajax',
        url: '/handlers/get_StoreData.php?data=partners&key=id&store=1',
        reader: {
            root: 'data',
            type: 'array'
        }
    },
    fields: ['id', 'value'],
    listeners: {
        load: function (s) {
            s.ValuesExtendedFiltersArr.push(['-1', '- Кроме (Хол + Парфюм) -']);
            s.ValuesExtendedFiltersArr.push(['-2', '- Весь Холод -']);
            this.each(function (rec) {
                s.ValuesArr.push([rec.get('id'), rec.get('value')]);
                s.ValuesJson[rec.get('id')] = rec.get('value');
                s.ValuesExtendedFiltersArr.push([rec.get('id'), rec.get('value')]);
            });
        }
    }
});