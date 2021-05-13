
function CreateMenuObzvonRus(id, country, editable) {
    console.log('Анкета "Обзвон Rus" id=' + id + ', country = ' + country + ', editable=' + editable);

    if (parseInt(id) === 0 || editable === true) {
        offer_store = offer_ar;
    } else {
        offer_store = [];
    }

    country = country.toLowerCase();
    var status_kz = ['На доставку', 'Отложенная доставка', 'Свежий'];

    // offer property creator
    var myProperty = {
        labels: {
            attribute: 'Атрибут',
            color: 'Цвет',
            size: 'Размер',
            type: 'Тип',
            vendor: 'Модель',
            name: 'Название',
            description: 'Описание'
        },
        getLabel: function (key) {
            if (typeof this.labels[key] !== 'undefined') {
                return this.labels[key];
            }

            return this.labels['attribute'];
        },
        create: function (name, type, id, options) {
            if (type === 'name' || type == 'description') {
                var tpl = {
                    xtype: 'textfield',
                    id: 'offer_property_' + type + id,
                    name: name + '[' + type + '][' + id + ']',
                    fieldLabel: this.getLabel(type),
                    allowBlank: false,
                    flex: 1
                };

                if (type === 'description') {
                    tpl.allowBlank = true;
                }
            } else {
                var tpl = {
                    xtype: 'combo',
                    id: 'offer_property_' + type + id,
                    name: name + '[' + type + '][' + id + ']',
                    fieldLabel: this.getLabel(type),
                    editable: false,
                    triggerAction: 'all',
                    queryMode: 'local',
                    allowBlank: false,
                    store: Ext.create('Ext.data.Store', {
                        fields: [
                            'id',
                            'name',
                            'value'
                        ],
                        data: options
                    }),
                    valueField: 'value',
                    displayField: 'value',
                    flex: 1
                };
            }

            if (typeof value != 'undefined') {
                tpl.value = value;
            }

            return tpl;
        }
    };

    var curmas = [];

    var street = Ext.create('Ext.data.ArrayStore', {
        storeId: 'streets' + id,
        remoteFilter: false,
        remoteSort: false,
        remoteGroup: false,
        autoLoad: false,
        // autoSync: true,
        proxy: {
            type: 'ajax',
            url: 'get_Address.php',
            reader: {type: 'array'}
        },
        listeners: {
            load: function (store, records, options) {
                var it = Ext.getCmp('street' + id).getValue();
                if (store.data.items.length < 1)
                    Ext.getCmp('street' + id).setValue(it);
            }
        },
        fields: ['id', 'value', 'index', 'name', 'short_name']
    });

    //street.load();

    var house = Ext.create('Ext.data.ArrayStore', {
        storeId: 'houses' + id,
        remoteFilter: false,
        remoteSort: false,
        remoteGroup: false,
        autoLoad: false,
        // autoSync: true,
        proxy: {
            type: 'ajax',
            url: 'get_Address.php',
            reader: {
                type: 'array'
            }
        },
        listeners: {
            load: function (store, records, options) {
                var it = Ext.getCmp('house' + id).getValue();
                if (store.data.items.length < 1)
                    Ext.getCmp('house' + id).setValue(it);
            }
        },
        fields: ['id', 'value', 'index', 'name', 'short_name']
    });

    //house.load();

    var storereg = [[0, ""], [1, "Адыгея республика"], [2, "Алтай республика"], [3, "Алтайский край"], [4, "Амурская область"], [5, "Архангельская область"], [6, "Архангельская область"], [7, "Астраханская область"], [8, "Башкортостан республика"], [9, "Башкортостан республика"], [10, "Башкортостан республика"], [11, "Белгородская область"], [12, "Белгородская область"], [13, "Брянская область"], [14, "Брянская область"], [15, "Бурятия республика"], [16, "Владимирская область"], [17, "Владимирская область"], [18, "Волгоградская область"], [19, "Волгоградская область"], [20, "Воронежская область"], [21, "Дагестан республика"], [22, "Еврейская Автономная область"], [23, "Забайкальский край"], [24, "Забайкальский край"], [25, "Ивановская область"], [26, "Ингушетия республика"], [27, "Ингушетия республика"], [28, "Иркутская область"], [29, "Иркутская область"], [30, "Иркутская область"], [31, "Кабардино-Балкарская республика"], [32, "Калининградская область"], [33, "Калмыкия республика"], [34, "Калужская область"], [35, "Калужская область"], [36, "Камчатский край"], [37, "Камчатский край"], [38, "Карелия республика"], [39, "Кемеровская область"], [40, "Кемеровская область"], [41, "Кемеровская область"], [42, "Кемеровская область"], [43, "Кировская область"], [44, "Коми республика"], [45, "Костромская область"], [46, "Костромская область"], [47, "Краснодарский край"], [48, "Краснодарский край"], [49, "Краснодарский край"], [50, "Краснодарский край"], [51, "Краснодарский край"], [52, "Краснодарский край"], [53, "Красноярский край"], [54, "Красноярский край"], [55, "Красноярский край"], [56, "Красноярский край"], [57, "Курганская область"], [58, "Курганская область"], [59, "Курская область"], [60, "Курская область"], [61, "Ленинградская область"], [62, "Ленинградская область"], [63, "Липецкая область"], [64, "Магаданская область"], [65, "Марий Эл республик"], [66, "Мордовия республика"], [67, "Мордовия республика"], [68, "Московская область"], [69, "Московская область"], [70, "Московская область"], [71, "Мурманская область"], [72, "Нижегородская область"], [73, "Новгородская область"], [74, "Новосибирская область"], [75, "Омская область"], [76, "Оренбургская область"], [77, "Оренбургская область"], [78, "Орловская область"], [79, "Пензенская область"], [80, "Пермский край"], [81, "Пермский край"], [82, "Приморский край"], [83, "Приморский край"], [84, "Псковская область"], [85, "Псковская область"], [86, "Ростовская область"], [87, "Рязанская область"], [88, "Самарская область"], [89, "Самарская область"], [90, "Саратовская область"], [91, "Сахалинская область"], [92, "Свердловская область"], [93, "Свердловская область"], [94, "Северная Осетия — Алания республика"], [95, "Смоленская область"], [96, "Ставропольский край"], [97, "Ставропольский край"], [98, "Ставропольский край"], [99, "Тамбовская область"], [100, "Татарстан республика"], [101, "Татарстан республика"], [102, "Татарстан республика"], [103, "Татарстан республика"], [104, "Тверская область"], [105, "Томская область"], [106, "Тульская область"], [107, "Тыва (Тува) республика"], [108, "Тыва (Тува) республика"], [109, "Тюменская область"], [110, "Тюменская область"], [111, "Тюменская область"], [112, "Тюменская область"], [113, "Тюменская область"], [114, "Тюменская область"], [115, "Удмуртская республик"], [116, "Удмуртская республика"], [117, "Удмуртская республика"], [118, "Удмуртская республика"], [119, "Ульяновская область"], [120, "Хабаровский край"], [121, "Хакасия республика"], [122, "Ханты-Мансийский Автономный округ"], [123, "Челябинская область"], [124, "Чеченская республика"], [125, "Чувашская республика"], [126, "Чукотский Автономный округ"], [127, "Ямало-Ненецкий Автономный округ"], [128, "Ярославская область"], [129, "Саха (Якутия)"]];

    var t_type = 'displayfield';
    if (parseInt(id) === 0) {
        t_type = 'textfield';
    }

    var wind = Ext.getCmp('sendm_' + id);

    if (!wind) {
        wind = Ext.create('Ext.Window', {
            title: 'ID заказа - ' + id,
            id: 'sendm_' + id,
            modal: true,
            height: 600,
            width: 850,
            layout: 'border',
            listeners: {
                close: function () {
                    Ext.Ajax.request({
                        url: '/handlers/clear_get.php?id=' + id,
                        success: function (response, opts) {
                        },
                        failure: function (response, opts) {
                        }
                    });
                }
            },
            items: [{
                    xtype: 'panel',
                    region: 'center',
                    width: 570,
                    autoScroll: true,
                    fbar: [{
                            xtype: 'button',
                            id: 'add_button_dop' + id,
                            text: 'Доп. товар',
                            handler: function () {
                                // generate unique ID
                                var fieldID = Ext.id();

                                var field = Ext.create('Ext.form.Panel', {
                                    frame: true,
                                    title: 'Дополнительный товар',
                                    padding: '0 5 0 5',
                                    margin: '5 0 0 0',
                                    closable: true,
                                    items: [{
                                            xtype: 'container',
                                            layout: {
                                                type: 'hbox',
                                                align: 'top'
                                            },
                                            defaults: {
                                                margins: '0 5 0 0',
                                                labelWidth: 80
                                            },
                                            items: [
                                                {
                                                    xtype: 'combo',
                                                    fields: ['id', 'value', 'price'],
                                                    fieldLabel: 'Товар',
                                                    lazyRender: true,
                                                    allowBlank: false,
                                                    editable: false,
                                                    mode: 'local',
                                                    name: 'dop_tovar[offer][' + fieldID + ']',
                                                    store: offer_ar,
                                                    valueField: 'id',
                                                    displayField: 'value',
                                                    flex: 2,
                                                    listeners: {
                                                        change: function (el, offerValue) {
                                                            var propertiesContainer = Ext.getCmp('offer-properties' + fieldID);
                                                            propertiesContainer.removeAll();
                                                            Ext.Ajax.request({
                                                                url: '/handlers/get_OfferProperties.php',
                                                                method: 'GET',
                                                                params: {
                                                                    offer: offerValue
                                                                },
                                                                scope: this,
                                                                timeout: 5000,
                                                                success: function (response, opts) {
                                                                    // received response from the server and decoding it
                                                                    response = Ext.decode(response.responseText);

                                                                    if (response['data'].length === 0) {
                                                                        return;
                                                                    }

                                                                    Ext.Object.each(response['data'], function (type, properties) {
                                                                        var options = [];

                                                                        Ext.Array.each(properties, function (property, index) {
                                                                            if (property.country === country) {
                                                                                options.push(property);
                                                                            }
                                                                        });

                                                                        var tpl = myProperty.create('dop_tovar', type, fieldID, options);

                                                                        propertiesContainer.add(tpl);
                                                                    });
                                                                },
                                                                failure: function (response, opts) {
                                                                    //Alert the user about communication error
                                                                    Ext.MessageBox.alert('Error', 'Error occurred during request execution! Please try again!');
                                                                }
                                                            });
                                                        }
                                                    }
                                                }, {
                                                    xtype: 'numberfield',
                                                    fieldLabel: 'Количество',
                                                    allowBlank: false,
                                                    name: 'dop_tovar[count][' + fieldID + ']',
                                                    enableKeyEvents: true,
                                                    minValue: 0,
                                                    listeners: {
                                                        keyup: function () {
                                                            var col = this.getValue();

                                                            prev_price = this.ownerCt.ownerCt.items.items[2].items.items[0].getValue();
                                                            //
                                                            var prod = str_replace('-', '_', this.ownerCt.ownerCt.items.items[0].items.items[0].getValue());

                                                            if (eval(prod + '_' + country + '[' + col + ']') === 'undefined') {
                                                                razn = col - 5;
                                                                pprice = eval(prod + '_' + country + '[5]') + razn * eval(prod + '_' + country + '[0]') * 0.7;
                                                            } else
                                                                var pprice = eval(prod + '_' + country + '[' + col + ']') * 0.7;
                                                            var tprice = Ext.getCmp('price' + id).getValue();

                                                            if (col) {
                                                                this.ownerCt.ownerCt.items.items[2].items.items[0].setValue(pprice);
                                                                var sprice = tprice + pprice - prev_price;
                                                            } else {
                                                                this.ownerCt.ownerCt.items.items[2].items.items[0].setValue('0');
                                                                var sprice = tprice - prev_price;
                                                            }
                                                            Ext.getCmp('price' + id).setValue(sprice.toString());
                                                        }
                                                    },
                                                    valueField: 'id',
                                                    displayField: 'value',
                                                    flex: 1
                                                }, {
                                                    xtype: 'textfield',
                                                    name: 'dop_tovar[price][' + fieldID + ']',
                                                    editable: false,
                                                    fieldLabel: 'Цена',
                                                    value: '0',
                                                    margins: '0',
                                                    flex: 1
                                                }
                                            ]
                                        }, {
                                            xtype: 'container',
                                            id: 'offer-properties' + fieldID,
                                            layout: {
                                                type: 'hbox',
                                                align: 'top'
                                            },
                                            defaults: {
                                                margins: '0 5 0 0',
                                                labelWidth: 80
                                            },
                                            items: []
                                        }]
                                });

                                Ext.getCmp('MenuForm_' + id).add(field);
                            }
                        }, {
                            xtype: 'button',
                            text: 'Проверить товар на складе',
                            handler: function () {
                                var form = Ext.getCmp('MenuForm_' + id);
                                var form_values = form.getForm().getValues();

                                var offer_property = {};
                                var dop_tovar = {};

                                Ext.Object.each(form_values, function (index, value) {
                                    // offer_property
                                    var offer_property_matches = index.match(/^offer_property\[(.+)\]\[(.+)\]$/);

                                    if (offer_property_matches) {
                                        if (['color', 'size', 'type', 'vendor'].indexOf(offer_property_matches[1]) > -1) {
                                            offer_property_matches.push(value);

                                            if (typeof offer_property[offer_property_matches[1]] === 'undefined') {
                                                offer_property[offer_property_matches[1]] = '';
                                            }

                                            offer_property[offer_property_matches[1]] = offer_property_matches[3];
                                        }
                                    }

                                    // dop_tovar
                                    var dop_tovar_matches = index.match(/^dop_tovar\[(.+)\]\[(.+)\]$/);

                                    if (dop_tovar_matches) {
                                        if (['offer', 'color', 'size', 'type', 'vendor'].indexOf(dop_tovar_matches[1]) > -1) {
                                            iteration_id = dop_tovar_matches[2];
                                            dop_tovar_matches.push(value);

                                            if (typeof dop_tovar[iteration_id] === 'undefined') {
                                                dop_tovar[iteration_id] = {};
                                            }

                                            if (typeof dop_tovar[iteration_id][dop_tovar_matches[1]] === 'undefined') {
                                                dop_tovar[iteration_id][dop_tovar_matches[1]] = '';
                                            }

                                            dop_tovar[iteration_id][dop_tovar_matches[1]] = dop_tovar_matches[3];
                                        }
                                    }
                                });

                                var data = [];

                                data.push({
                                    offer: form_values.offer,
                                    attributes: offer_property,
                                    delivery: form_values.kz_delivery
                                });

                                Ext.Object.each(dop_tovar, function (index, value) {
                                    offer = value['offer'];

                                    delete value['offer'];

                                    data.push({
                                        offer: offer,
                                        attributes: value,
                                        delivery: form_values.kz_delivery
                                    });
                                });

                                Ext.Ajax.request({
                                    url: '/handlers/get_StorageCount.php',
                                    method: 'POST',
                                    params: {
                                        data: Ext.encode(data)
                                    },
                                    scope: this,
                                    timeout: 5000,
                                    success: function (response, opts) {
                                        // received response from the server and decoding it
                                        response = Ext.decode(response.responseText);

                                        if (response['data'].length === 0) {
                                            return;
                                        }

                                        var str = '';

                                        Ext.Object.each(response['data'], function (hash, result) {
                                            str = str + result.offer + ': ' + result.count + ' шт.<br/>';
                                        });

                                        Ext.MessageBox.alert('Отчет по товару', str);
                                    },
                                    failure: function (response, opts) {
                                        // Alert the user about communication error
                                        Ext.MessageBox.alert('Error', 'Error occurred during request execution! Please try again!');
                                    }
                                });
                            }
                        }, '->', {
                            xtype: 'button',
                            text: 'Сохранить',
                            id: 'sbutton' + id,
                            handler: function (button) {
                                fp = Ext.getCmp('MenuForm_' + id);
                                if (fp.getForm().isValid()) {
                                    fp.getForm().submit({
                                        url: '/handlers/set_menu_obzvon.php?id=' + id + '&country=' + country,
                                        waitMsg: 'Жди...',
                                        success: function (fp, action) {
                                            Ext.getCmp('sendm_' + id).close();
                                            Ext.getCmp('DostavkaGridId').store.reload();
                                        }
                                    });
                                }
                            }
                        }],
                    items: [{
                            xtype: 'form',
                            id: 'MenuForm_' + id,
                            url: '/handlers/get_menu_obzvon.php?id=' + id,
                            border: false,
                            padding: 10,
                            layout: 'anchor',
                            defaults: {
                                anchor: '100%',
                                labelWidth: 150
                            },
                            items: [
                                {
                                    xtype: 'displayfield',
                                    name: 'history',
                                    fieldLabel: 'История заказов'
                                }, {
                                    xtype: 'fieldcontainer',
                                    layout: 'hbox',
                                    hideEmptyLabel: false,
                                    defaults: {
                                        labelWidth: 100
                                    },
                                    items: [{
                                            xtype: 'displayfield',
                                            fieldLabel: 'Позвони мне пожалуйста',
                                            id: 'call_me' + id,
                                            name: 'call_me'
                                        }, {
                                            xtype: 'displayfield',
                                            fieldLabel: 'Текущее время',
                                            id: 'time' + id,
                                            name: 'timezone'
                                        }]
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'ID',
                                    name: 'id'
                                }, {
                                    xtype: t_type,
                                    fieldLabel: '№ Телефона',
                                    allowBlank: false,
                                    id: 'phone' + id,
                                    name: 'phone'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'ФИО',
                                    name: 'fio'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Индекс',
                                    minLength: 5,
                                    id: 'index' + id,
                                    allowBlank: false,
                                    name: 'index'
                                }, {
                                    xtype: 'combo',
                                    itemId: 'post' + id,
                                    lazyRender: true,
                                    editable: false,
                                    valueField: 'value',
                                    displayField: 'value',
                                    triggerAction: 'all',
                                    store: storereg,
                                    id: 'region' + id,
                                    fieldLabel: 'Регион',
                                    allowBlank: false,
                                    hiddenName: 'region',
                                    name: 'region'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Район',
                                    id: 'district' + id,
                                    name: 'district'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Город',
                                    queryMode: 'remote',
                                    store: Ext.data.StoreManager.lookup('towns' + id),
                                    hideTrigger: true,
                                    typeAhead: false,
                                    editable: true,
                                    id: 'dcity' + id,
                                    name: 'city',
                                    minChars: 3,
                                    hiddenName: 'city',
                                    valueField: 'id',
                                    displayField: 'value',
                                    disableKeyFilter: true,
                                    enableKeyEvents: true,
                                    allowBlank: false,
                                    listConfig: {
                                        loadingText: 'Searching...',
                                        emptyText: 'Not found...'
                                    },
                                    queryParam: 'searchString',
                                    listeners: {
                                        // on keyup
                                        keyup: {
                                            element: 'el',
                                            fn: function (el, e, eOpts) {
                                                vart = e.value;

                                                if (vart) {
                                                    if (vart.length > 2) {
                                                        var che = Ext.Array.contains(cur_ru, vart);

                                                        if (che) {
                                                            Ext.getCmp('kz_delivery' + id).setValue(vart);
                                                        } else {
                                                            Ext.getCmp('kz_delivery' + id).setValue('Почта');
                                                        }

                                                        var vas = Ext.getCmp('dcity' + id).getValue();

                                                        Ext.data.StoreManager.lookup('towns' + id).getProxy().extraParams =
                                                                {
                                                                    searchType: 'city'
                                                                };
                                                    }
                                                }
                                            }
                                        },
                                        // on change
                                        change: function (el, value, oldValue, eOpts) {
                                            var data = false;

                                            store = Ext.data.StoreManager.lookup('towns' + id);
                                            store.each(function (record, index) {
                                                if (record.get('id') == value) {
                                                    data = record;
                                                    return;
                                                }
                                            });

                                            if (!data) {
                                                return;
                                            }

                                            if (value == '7700000000000') {
                                                Ext.getCmp('city_region' + id).editable = true;
                                                var metro_store = ['Авиамоторная', 'Автозаводская', 'Академическая', 'Александровский сад', 'Алексеевская', 'Алма-Атинская', 'Алтуфьево', 'Аннино', 'Арбатская (Арбатско-Покровская линия)', 'Арбатская (Филевская линия)', 'Аэропорт', 'Бабушкинская', 'Багратионовская', 'Баррикадная', 'Бауманская', 'Беговая', 'Белорусская', 'Беляево', 'Бибирево', 'Библиотека имени Ленина', 'Борисово', 'Боровицкая', 'Ботанический сад', 'Братиславская', 'Бульвар адмирала Ушакова', 'Бульвар Дмитрия Донского', 'Бульвар Рокоссовского', 'Бунинская аллея', 'Варшавская', 'ВДНХ', 'Владыкино', 'Водный стадион', 'Войковская', 'Волгоградский проспект', 'Волжская', 'Волоколамская', 'Воробьевы горы', 'Выставочная', 'Выхино', 'Деловой центр', 'Динамо', 'Дмитровская', 'Добрынинская', 'Домодедовская', 'Достоевская', 'Дубровка', 'Жулебино', 'Зябликово', 'Измайловская', 'Калужская', 'Кантемировская', 'Каховская', 'Каширская', 'Киевская', 'Китай-город', 'Кожуховская', 'Коломенская', 'Комсомольская', 'Коньково', 'Красногвардейская', 'Краснопресненская', 'Красносельская', 'Красные ворота', 'Крестьянская застава', 'Кропоткинская', 'Крылатское', 'Кузнецкий мост', 'Кузьминки', 'Кунцевская', 'Курская', 'Кутузовская', 'Ленинский проспект', 'Лермонтовский проспект', 'Лубянка', 'Люблино', 'Марксистская', 'Марьина роща', 'Марьино', 'Маяковская', 'Медведково', 'Международная', 'Менделеевская', 'Митино', 'Молодежная', 'Монорельса Выставочный центр', 'Монорельса Телецентр', 'Монорельса Улица Академика Королева', 'Монорельса Улица Милашенкова', 'Монорельса Улица Сергея Эйзенштейна', 'Монорельсовой дороги Тимирязевская', 'Мякинино', 'Нагатинская', 'Нагорная', 'Нахимовский проспект', 'Новогиреево', 'Новокосино', 'Новокузнецкая', 'Новослободская', 'Новоясеневская', 'Новые Черемушки', 'Октябрьская', 'Октябрьское поле', 'Орехово', 'Отрадное', 'Охотныйряд', 'Павелецкая', 'Парк культуры', 'Парк Победы', 'Партизанская', 'Первомайская', 'Перово', 'Петровско-Разумовская', 'Печатники', 'Пионерская', 'Планерная', 'Площадь Ильича', 'Площадь Революции', 'Полежаевская', 'Полянка', 'Пражская', 'Преображенская площадь', 'Пролетарская', 'Проспект Вернадского', 'Проспект Мира', 'Профсоюзная', 'Пушкинская', 'Пятницкое шоссе', 'Речной вокзал', 'Рижская', 'Римская', 'Рязанский проспект', 'Савеловская', 'Свиблово', 'Севастопольская', 'Семеновская', 'Серпуховская', 'Славянский бульвар', 'Смоленская (Арбатско-Покровская линия)', 'Смоленская (Филевская линия)', 'Сокол', 'Сокольники', 'Спартак', 'Спортивная', 'Сретенский бульвар', 'Строгино', 'Студенческая', 'Сухаревская', 'Сходненская', 'Таганская', 'Тверская', 'Театральная', 'Текстильщики', 'Теплый стан', 'Тимирязевская', 'Третьяковская', 'Тропарево', 'Трубная', 'Тульская', 'Тургеневская', 'Тушинская', 'Улица Академика Янгеля', 'Улица Горчакова', 'Улица Скобелевская', 'Улица Старокачаловская', 'Улица 1905 года', 'Университет', 'Филевский парк', 'Фили', 'Фрунзенская', 'Царицыно', 'Цветной бульвар', 'Черкизовская', 'Чертановская', 'Чеховская', 'Чистые пруды', 'Чкаловская', 'Шаболовская', 'Шипиловская', 'Шоссе Энтузиастов', 'Щелковская', 'Щукинская', 'Электрозаводская', 'Юго-Западная', 'Южная', 'Ясенево'];
                                                Ext.getCmp('city_region' + id).bindStore(metro_store);
                                            }

                                            var che = Ext.Array.contains(cur_ru, data.get('name'));

                                            if (che) {
                                                Ext.getCmp('kz_delivery' + id).setValue(data.get('name'));
                                            } else {
                                                Ext.getCmp('kz_delivery' + id).setValue('Почта');
                                            }

                                            Ext.getCmp('city_short' + id).setValue(data.get('short_name'));

                                            if (value == '7700000000000' || value == '7800000000000') {
                                                Ext.getCmp('region' + id).allowBlank = true;
                                                Ext.getCmp('region' + id).validate();
                                                Ext.getCmp('region' + id).setValue('');
                                                Ext.getCmp('region' + id).setReadOnly('readOnly');
                                                Ext.getCmp('district' + id).setValue('');
                                                Ext.getCmp('district' + id).setReadOnly('readOnly');
                                            }

                                            var sval = value;
                                            var newval = data.get('index');

                                            if (curmas.indexOf(parseInt(sval)) >= 0 || curmas.indexOf(parseInt(newval)) >= 0) {
                                                Ext.getCmp('date_delivery' + id).setVisible(true);
                                                Ext.getCmp('date_delivery' + id).allowBlank = false;
                                                Ext.getCmp('date_delivery' + id).validate();
                                                Ext.getCmp('deliv_minute' + id).setVisible(true);
                                                Ext.getCmp('deliv_minute' + id).allowBlank = false;
                                                Ext.getCmp('deliv_minute' + id).validate();
                                            } else {
                                                Ext.getCmp('date_delivery' + id).setVisible(false);
                                                Ext.getCmp('date_delivery' + id).allowBlank = true;
                                                Ext.getCmp('date_delivery' + id).validate();
                                                Ext.getCmp('deliv_minute' + id).allowBlank = true;
                                                Ext.getCmp('deliv_minute' + id).validate();
                                                Ext.getCmp('deliv_minute' + id).setVisible(false);
                                            }

                                            Ext.getCmp('idcity' + id).setValue(value);
                                            this.setValue(data.get('name'));
                                            this.setRawValue(data.get('name'));
                                            var ind = data.get('index');
                                            var reg = data.get('region');
                                            var dist = data.get('district');
                                            Ext.getCmp('street' + id).setValue('');
                                            Ext.getCmp('house' + id).setValue('');

                                            if (ind) {
                                                Ext.getCmp('index' + id).setValue(ind);
                                            } else {
                                                Ext.getCmp('index' + id).setValue('');
                                            }

                                            if (reg) {
                                                Ext.getCmp('region' + id).setValue(reg);
                                                Ext.getCmp('region' + id).setReadOnly('readOnly');
                                            } else {
                                                Ext.getCmp('region' + id).setValue('');
                                            }

                                            if (dist) {
                                                Ext.getCmp('district' + id).setValue(dist);
                                                Ext.getCmp('district' + id).setReadOnly('readOnly');
                                            } else {
                                                Ext.getCmp('district' + id).setValue('');
                                            }
                                        }
                                    }
                                }, {
                                    xtype: 'fieldcontainer',
                                    layout: 'hbox',
                                    hideEmptyLabel: false,
                                    defaults: {
                                        labelWidth: 120,
                                        labelAlign: 'top',
                                        flex: 1
                                    },
                                    items: [{
                                            xtype: 'combo',
                                            fieldLabel: 'Улица',
                                            queryMode: 'remote',
                                            store: street,
                                            hideTrigger: true,
                                            typeAhead: false,
                                            // typeAheadDelay: 1000,
                                            flex: 2,
                                            margin: '3 5 0 0',
                                            // autoSelect: false,
                                            // autoShow: false,
                                            // autoFitErrors: false,
                                            // trackResetOnLoad: false,
                                            editable: true,
                                            id: 'street' + id,
                                            name: 'street',
                                            valueField: 'id',
                                            displayField: 'value',
                                            enableKeyEvents: true,
                                            allowBlank: false,
                                            queryParam: 'searchString',
                                            listeners: {
                                                keyup: function () {
                                                    if (this.getValue()) {
                                                        if (this.getValue().length > 1) {
                                                            street.getProxy().extraParams = {
                                                                // searchString: this.getValue(),
                                                                searchType: 'street',
                                                                id: Ext.getCmp('idcity' + id).getValue()
                                                            };
                                                            // Ext.getCmp('street' + id).store.load();
                                                        }
                                                    }
                                                },
                                                change: function (el, value, oldValue, eOpts) {
                                                    var data = false;

                                                    store = Ext.data.StoreManager.lookup('streets' + id);
                                                    store.each(function (record, index) {
                                                        if (record.get('id') == value) {
                                                            data = record;
                                                            return;
                                                        }
                                                    });

                                                    if (!data) {
                                                        return;
                                                    }

                                                    // console.dir(data);
                                                    Ext.getCmp('street_short' + id).setValue(data.get('short_name'));
                                                    Ext.getCmp('idstreet' + id).setValue(data.get('id'));
                                                    Ext.getCmp('street' + id).setValue(data.get('name'));
                                                    var ind = data.get('index');

                                                    if (ind) {
                                                        Ext.getCmp('index' + id).setValue(ind);
                                                    } else {
                                                        Ext.getCmp('index' + id).setValue('');
                                                    }

                                                    Ext.getCmp('house' + id).setValue('');
                                                }
                                            }
                                        }, {
                                            xtype: 'combo',
                                            fieldLabel: 'Дом',
                                            queryMode: 'remote',
                                            store: house,
                                            minChars: 1,
                                            hideTrigger: true,
                                            typeAhead: false,
                                            margin: '3 5 0 0',
                                            editable: true,
                                            id: 'house' + id,
                                            name: 'building',
                                            hiddenName: 'building',
                                            valueField: 'name',
                                            displayField: 'name',
                                            enableKeyEvents: true,
                                            allowBlank: false,
                                            queryParam: 'searchString',
                                            listeners: {
                                                keyup: function () {
                                                    if (this.getValue()) {
                                                        if (this.getValue().length > 0) {
                                                            Ext.data.StoreManager.lookup('houses' + id).getProxy().extraParams =
                                                                    {
                                                                        searchType: 'building',
                                                                        id: Ext.getCmp('idstreet' + id).getValue()
                                                                    };
                                                        }
                                                    }
                                                },
                                                change: function (el, value, oldValue, eOpts) {
                                                    var data = false;

                                                    store = Ext.data.StoreManager.lookup('houses' + id);
                                                    store.each(function (record, index) {
                                                        if (record.get('name') == value) {
                                                            data = record;
                                                            return false;
                                                        }
                                                    });

                                                    if (!data) {
                                                        return;
                                                    }

                                                    Ext.getCmp('building_short' + id).setValue(data.get('short_name'));

                                                    var ind = data.get('index');
                                                    if (ind) {
                                                        Ext.getCmp('index' + id).setValue(ind);
                                                    }
                                                }
                                            }
                                        }, {
                                            xtype: 'textfield',
                                            fieldLabel: 'Квартира',
                                            name: 'flat',
                                            margin: '3 0 0 0'
                                        }
                                    ]
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Адрес',
                                    name: 'addr'
                                }, {
                                    xtype: 'combo',
                                    id: 'city_region' + id,
                                    fieldLabel: 'Район города',
                                    name: 'city_region',
                                    editable: false,
                                    triggerAction: 'all',
                                    allowBlank: true,
                                    hidden: false,
                                    queryMode: 'local',
                                    store: [],
                                    valueField: 'region',
                                    displayField: 'region'
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: 'Цена',
                                    id: 'price' + id,
                                    minValue: 1,
                                    editable: true,
                                    name: 'price'
                                }, {
                                    xtype: 'combo',
                                    editable: false,
                                    allowBlank: false,
                                    name: 'offer',
                                    id: 'offer' + id,
                                    store: offer_store,
                                    fieldLabel: 'Продукт',
                                    listeners: {
                                        change: function (el, offerValue, oldValue) {
                                            if (id == 0) {
                                                Ext.Ajax.request({
                                                    url: '/handlers/get_OfferProperties.php',
                                                    method: 'GET',
                                                    params: {
                                                        offer: offerValue
                                                    },
                                                    scope: this,
                                                    timeout: 5000,
                                                    success: function (response, opts) {
                                                        // received response from the server and decoding it
                                                        response = Ext.decode(response.responseText);

                                                        if (response['data'].length == 0) {
                                                            return;
                                                        }

                                                        // свойства товара
                                                        var container = Ext.getCmp('offer-property-fields' + id);

                                                        Ext.Object.each(response['data'], function (type, properties) {
                                                            var options = [];

                                                            Ext.Array.each(properties, function (property, index) {
                                                                if (property.country == country) {
                                                                    options.push(property);
                                                                }
                                                            });

                                                            var tpl = myProperty.create('offer_property', type, id, options);

                                                            container.add(tpl);
                                                        });
                                                    },
                                                    failure: function (response, opts) {
                                                        //Alert the user about communication error
                                                        Ext.MessageBox.alert('Error', 'Error occurred during request execution! Please try again!');
                                                    }
                                                });
                                            }
                                        }
                                    }
                                }, {
                                    xtype: 'numberfield',
                                    fieldLabel: 'К-во',
                                    id: 'package' + id,
                                    maxValue: 20,
                                    enableKeyEvents: true,
                                    mouseWheelEnabled: false,
                                    hideTrigger: true,
                                    allowBlank: false,
                                    spinEnabled: false,
                                    listeners: {
                                        'keyup': function () {
                                            var col = Ext.getCmp('package' + id).getValue();
                                            var prod = str_replace('-', '_', Ext.getCmp('offer' + id).getValue());
                                            if (!parseInt(eval(prod + '_' + country + '[' + col + ']'))) {
                                                razn = col - 5;
                                                pprice = eval(prod + '_' + country + '[5]') + razn * eval(prod + '_' + country + '[0]');
                                            } else {
                                                var pprice = eval(prod + '_' + country + '[' + col + ']');
                                            }
                                            Ext.getCmp('price' + id).setValue(pprice);
                                        }
                                    },
                                    name: 'package'
                                }, {
                                    xtype: 'container',
                                    id: 'offer-property-fields' + id,
                                    defaults: {
                                        anchor: '100%',
                                        labelWidth: 150
                                    },
                                    disabled: true,
                                    hidden: true,
                                    items: []
                                }, {
                                    xtype: 'combo',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    queryMode: 'local',
                                    name: 'status',
                                    id: 'status' + id,
                                    allowBlank: false,
                                    store: globalStatusStore,
                                    fieldLabel: 'Статус',
                                    listeners: {
                                        change: function (el, newValue, oldValue) {
                                            switch (newValue) {
                                                case 'Подтвержден':
                                                    new_field = Ext.create('Ext.form.field.ComboBox', {
                                                        lazyRender: true,
                                                        editable: true,
                                                        mode: 'local',
                                                        name: 'description',
                                                        id: 'description' + id,
                                                        store: [],
                                                        fieldLabel: 'Подстатус (коммент)',
                                                        labelWidth: 150,
                                                        valueField: 'value',
                                                        displayField: 'value'
                                                    });
                                                    Ext.getCmp('MenuForm_' + id).add(new_field);
                                                    Ext.getCmp('index' + id).allowBlank = false;
                                                    Ext.getCmp('index' + id).validate();
                                                    Ext.getCmp('street' + id).allowBlank = false;
                                                    Ext.getCmp('street' + id).validate();
                                                    Ext.getCmp('house' + id).allowBlank = false;
                                                    Ext.getCmp('house' + id).validate();

                                                    // show properties for main product
                                                    var container = Ext.getCmp('offer-property-fields' + id);
                                                    container.setDisabled(false);
                                                    container.show();
                                                    break
                                                case 'Отменён':
                                                case 'Уже получил заказ':
                                                case 'Черный список':
                                                case 'Заказано у конкурентов':
                                                case 'Заказ уже обработан':
                                                    new_field = Ext.create('Ext.form.field.ComboBox', {
                                                        lazyRender: true,
                                                        editable: true,
                                                        mode: 'local',
                                                        name: 'description',
                                                        id: 'description' + id,
                                                        store: globalCancelTypesStore,
                                                        fieldLabel: 'Подстатус (коммент)',
                                                        labelWidth: 150,
                                                        valueField: 'value',
                                                        displayField: 'value'
                                                    });
                                                    Ext.getCmp('MenuForm_' + id).add(new_field);
                                                    Ext.getCmp('index' + id).allowBlank = true;
                                                    Ext.getCmp('index' + id).validate();
                                                    Ext.getCmp('district' + id).allowBlank = true;
                                                    Ext.getCmp('district' + id).validate();
                                                    Ext.getCmp('region' + id).allowBlank = true;
                                                    Ext.getCmp('region' + id).validate();
                                                    Ext.getCmp('dcity' + id).allowBlank = true;
                                                    Ext.getCmp('dcity' + id).validate();
                                                    Ext.getCmp('street' + id).allowBlank = true;
                                                    Ext.getCmp('street' + id).validate();
                                                    Ext.getCmp('house' + id).allowBlank = true;
                                                    Ext.getCmp('house' + id).validate();
                                                    Ext.getCmp('description' + id).allowBlank = false;
                                                    Ext.getCmp('description' + id).validate();

                                                    // hide properties for main product
                                                    var container = Ext.getCmp('offer-property-fields' + id);
                                                    container.setDisabled(true);
                                                    container.hide();
                                                    break
                                                case 'Перезвонить':
                                                    new_fieldn = Ext.create('Ext.form.field.Number', {
                                                        name: 'recall_date',
                                                        id: 'recall_date' + id,
                                                        minValue: 1,
                                                        fieldLabel: 'Перезвонить через(часов)',
                                                        labelWidth: 150
                                                    });
                                                    new_field = Ext.create('Ext.form.field.Date', {
                                                        name: 'recall_dates',
                                                        id: 'recall_dates' + id,
                                                        anchor: '100%',
                                                        labelWidth: 150,
                                                        allowBlank: true,
                                                        //maxValue: 300,
                                                        minValue: new Date().format('Y-m-d'),
                                                        maxValue: new Date(new Date().getTime() + (1000 * 60 * 60 * 100)).format('Y-m-d'),
                                                        format: 'Y-m-d H:i',
                                                        fieldLabel: 'Перезвонить дата/время'
                                                    });
                                                    Ext.getCmp('MenuForm_' + id).add(new_field);

                                                    new_field2 = Ext.create('Ext.form.field.ComboBox', {
                                                        lazyRender: true,
                                                        editable: true,
                                                        mode: 'local',
                                                        name: 'description',
                                                        id: 'description' + id,
                                                        store: [],
                                                        fieldLabel: 'Подстатус (коммент)',
                                                        labelWidth: 150,
                                                        valueField: 'value',
                                                        displayField: 'value'
                                                    });
                                                    Ext.getCmp('MenuForm_' + id).add(new_field2);
                                                    Ext.getCmp('index' + id).allowBlank = true;
                                                    Ext.getCmp('index' + id).validate();
                                                    Ext.getCmp('district' + id).allowBlank = true;
                                                    Ext.getCmp('district' + id).validate();
                                                    Ext.getCmp('region' + id).allowBlank = true;
                                                    Ext.getCmp('region' + id).validate();
                                                    Ext.getCmp('dcity' + id).allowBlank = true;
                                                    Ext.getCmp('dcity' + id).validate();
                                                    Ext.getCmp('street' + id).allowBlank = true;
                                                    Ext.getCmp('street' + id).validate();
                                                    Ext.getCmp('house' + id).allowBlank = true;
                                                    Ext.getCmp('house' + id).validate();

                                                    // hide properties for main product
                                                    var container = Ext.getCmp('offer-property-fields' + id);
                                                    container.setDisabled(true);
                                                    container.hide();
                                                    break
                                                case 'Недозвон':
                                                    Ext.getCmp('index' + id).allowBlank = true;
                                                    Ext.getCmp('index' + id).validate();
                                                    Ext.getCmp('district' + id).allowBlank = true;
                                                    Ext.getCmp('district' + id).validate();
                                                    Ext.getCmp('region' + id).allowBlank = true;
                                                    Ext.getCmp('region' + id).validate();
                                                    Ext.getCmp('dcity' + id).allowBlank = true;
                                                    Ext.getCmp('dcity' + id).validate();
                                                    Ext.getCmp('street' + id).allowBlank = true;
                                                    Ext.getCmp('street' + id).validate();
                                                    Ext.getCmp('house' + id).allowBlank = true;
                                                    Ext.getCmp('house' + id).validate();

                                                    // hide properties for main product
                                                    var container = Ext.getCmp('offer-property-fields' + id);
                                                    container.setDisabled(true);
                                                    container.hide();
                                                    break
                                                case 'недозвон_ночь':
                                                    Ext.getCmp('index' + id).allowBlank = true;
                                                    Ext.getCmp('index' + id).validate();
                                                    Ext.getCmp('district' + id).allowBlank = true;
                                                    Ext.getCmp('district' + id).validate();
                                                    Ext.getCmp('region' + id).allowBlank = true;
                                                    Ext.getCmp('region' + id).validate();
                                                    Ext.getCmp('dcity' + id).allowBlank = true;
                                                    Ext.getCmp('dcity' + id).validate();
                                                    Ext.getCmp('street' + id).allowBlank = true;
                                                    Ext.getCmp('street' + id).validate();
                                                    Ext.getCmp('house' + id).allowBlank = true;
                                                    Ext.getCmp('house' + id).validate();

                                                    // hide properties for main product
                                                    var container = Ext.getCmp('offer-property-fields' + id);
                                                    container.setDisabled(true);
                                                    container.hide();
                                                    break
                                                default:
                                                    Ext.getCmp('description' + id).store.removeAll();

                                                    // hide properties for main product
                                                    var container = Ext.getCmp('offer-property-fields' + id);
                                                    container.setDisabled(true);
                                                    container.hide();
                                                    break
                                            }

                                            if (newValue === 'Брак') {
                                                var item = Ext.getCmp('description' + id);
                                                item.setEditable(false);
                                                item.bindStore(globalDefectTypesStore.ValuesArr);
                                                item.reset();
                                                item.allowBlank = false;
                                                item.validate();

                                                var city = Ext.getCmp('dcity' + id);
                                                if (city) {
                                                    city.allowBlank = true;
                                                    city.validate();
                                                }
                                            } else if (oldValue === 'Брак') {
                                                var item = Ext.getCmp('description' + id);
                                                item.setEditable(true);
                                                item.store.removeAll();
                                                item.reset();
                                                item.allowBlank = true;
                                                item.validate();
                                            }

                                        }
                                    },
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Статус посылки',
                                    store: status_kz,
                                    editable: false,
                                    forceSelection: true,
                                    allowBlank: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    queryMode: 'local',
                                    name: 'status_kz',
                                    id: 'status_kz' + id,
                                    listeners: {
                                        change: function (el, newValue, oldValue) {
                                            Ext.getCmp('date_delivery' + id).allowBlank = true;
                                            Ext.getCmp('date_delivery' + id).validate();
                                            if (Ext.getCmp('deferred_date' + id)) {
                                                Ext.getCmp('deferred_date' + id).destroy();
                                            }

                                            switch (newValue) {
                                                case 'Отложенная доставка':
                                                    var new_field = Ext.create('Ext.form.field.Date', {
                                                        name: 'deferred_date',
                                                        anchor: '100%',
                                                        labelWidth: 150,
                                                        id: 'deferred_date' + id,
                                                        format: 'Y-m-d H:i:s',
                                                        minValue: new Date().format('Y-m-d'),
                                                        maxValue: new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 7)).format('Y-m-d'),
                                                        allowBlank: false,
                                                        fieldLabel: 'Дата отложенной доставки'
                                                    });
                                                    var formed = Ext.getCmp('MenuForm_' + id);
                                                    for (var i = 0; i < formed.items.items.length; i++) {
                                                        if (formed.items.items[i].id === 'status_kz' + id) {
                                                            formed.insert(i + 1, new_field);
                                                        }
                                                    }
                                                    break;
                                            }
                                        }
                                    },
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    lazyRender: true,
                                    editable: false,
                                    mode: 'local',
                                    name: 'kz_delivery',
                                    id: 'kz_delivery' + id,
                                    allowBlank: true,
                                    minLength: 2,
                                    store: cur_ru,
                                    fieldLabel: 'Доставка',
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'fieldcontainer',
                                    id: 'curier' + id,
                                    hidden: false,
                                    hideEmptyLabel: false,
                                    layout: {
                                        type: 'hbox',
                                        align: 'stretch',
                                        labelAlign: 'top'
                                    },
                                    items: [{
                                            xtype: 'datefield',
                                            lazyRender: true,
                                            fieldLabel: 'Дата доставки',
                                            startDay: 1,
                                            margin: '3 5 0 0',
                                            format: 'Y-m-d',
                                            id: 'date_delivery' + id,
                                            name: 'date_delivery'
                                        }, {
                                            xtype: 'combo',
                                            lazyRender: true,
                                            hideLabel: true,
                                            id: 'deliv_minute' + id,
                                            editable: false,
                                            fieldLabel: 'Время доставки',
                                            margin: '3 0 0 0',
                                            name: 'deliv_minute',
                                            value: '09:00',
                                            triggerAction: 'all',
                                            store: ['09:00', '13:00', '17:00', '21:00']
                                        }]
                                }, {
                                    xtype: 'combo',
                                    lazyRender: true,
                                    mode: 'local',
                                    name: 'description',
                                    id: 'description' + id,
                                    store: [],
                                    fieldLabel: 'Подстатус (коммент)',
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    mode: 'local',
                                    name: 'deliv_desc',
                                    id: 'deliv_desc' + id,
                                    store: [],
                                    fieldLabel: 'Описание доставки',
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    mode: 'local',
                                    name: 'oper_use',
                                    id: 'oper_use' + id,
                                    store: ['0', '1', '2', '3', '4', '5', '6', '7'],
                                    fieldLabel: 'В работе у',
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Доп номер',
                                    maxLength: 11,
                                    allowBlank: false,
                                    name: 'phone_sms'
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Наличие доп. товара',
                                    allowBlank: false,
                                    name: 'dop_tovar'
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'штрих',
                                    allowBlank: false,
                                    name: 'kz_code'
                                }, {
                                    xtype: 'hidden',
                                    name: 'ext_id',
                                    hiddenName: 'ext_id'
                                }, {
                                    xtype: 'hidden',
                                    name: 'country',
                                    hiddenName: 'country'
                                }, {
                                    xtype: 'hidden',
                                    name: 'staff_id',
                                    id: 'staff_id' + id,
                                    hiddenName: 'staff_id'
                                }, {
                                    xtype: 'hidden',
                                    name: 'idcity',
                                    id: 'idcity' + id
                                }, {
                                    xtype: 'hidden',
                                    name: 'city_short',
                                    id: 'city_short' + id
                                }, {
                                    xtype: 'hidden',
                                    name: 'idstreet',
                                    id: 'idstreet' + id
                                }, {
                                    xtype: 'hidden',
                                    name: 'street_short',
                                    id: 'street_short' + id
                                }, {
                                    xtype: 'hidden',
                                    name: 'building_short',
                                    id: 'building_short' + id
                                }
                            ]
                        }]
                }, {
                    region: 'east',
                    id: 'west-panel',
                    title: 'Данные по товару',
                    split: true,
                    width: 270,
                    minSize: 175,
                    maxSize: 400,
                    collapsible: true,
                    collapsed: true,
                    autoScroll: true,
                    margins: '3 0 5 5',
                    paddings: '3 5 5 5',
                    cmargins: '3 5 5 5',
                    layout: 'accordion',
                    layoutConfig: {
                        animate: true
                    }
                }]
        }).show();

        if (id > 0) {
            Ext.getCmp('MenuForm_' + id).getForm().load({
                success: function (form, action) {
                    // свойства товара
                    var properties = action.result.data.offer_properties;

                    if (Object.keys(properties).length > 0) {
                        var container = Ext.getCmp('offer-property-fields' + id);

                        Ext.Object.each(properties, function (type, property) {
                            var options = [];

                            Ext.Array.each(property, function (val, key) {
                                if (action.result.data.country === val.country) {
                                    options.push(val);
                                }
                            });

                            var value = '';

                            if (typeof action.result.data.offer_property[type] !== 'undefined') {
                                value = action.result.data.offer_property[type];
                            }

                            var tpl = myProperty.create('offer_property', type, id, options, value);

                            container.add(tpl);
                        });

                        // show properties for main product
                        if (action.result.data.status === 'Подтвержден') {
                            container.setDisabled(false);
                            container.show();
                        } else {
                            container.setDisabled(true);
                            container.hide();
                        }
                    }

                    Ext.getCmp('index' + id).allowBlank = false;
                    Ext.getCmp('index' + id).validate();
                    var tmp_phone = Ext.getCmp('phone' + id).getValue();
                    var tmp_staff = Ext.getCmp('staff_id' + id).getValue();
                    var tmp_offer = Ext.getCmp('offer' + id).getValue();
                    var tmp_zone = Ext.getCmp('time' + id).getValue();
                    now_date = new Date();
                    now_hour = now_date.getUTCHours();
                    now_dates = now_date.getDate();
                    itog_hour = parseInt(now_hour) + parseInt(tmp_zone) - 1;
                    if (itog_hour > 24) {
                        itog_hour = itog_hour - 24;
                        itog_date = parseInt(now_dates) + 1;
                    } else
                        itog_date = parseInt(now_dates);
                    now_date.setHours(itog_hour);
                    now_date.setDate(itog_date);
                    Ext.getCmp('time' + id).setValue(now_date);
                    Ext.Ajax.request({
                        method: 'GET',
                        url: '/handlers/get_projectInfo.php?project=66629642&offer=' + tmp_offer,
                        success: function (response) {
                            offer_data = response.responseText;
                            Ext.getCmp('west-panel').update(offer_data);
                            Ext.getCmp('west-panel').autoScroll = true;
                            Ext.getCmp('west-panel').doLayout();
                            if (offer_data.length > 30) {
                                Ext.getCmp('west-panel').expand();
                            }
                        }
                    });

                    var link = '<a style="font-size:40px;" href="sip:7*' + tmp_phone + '#' + id + '">&#9990;</a>';
                    Ext.getCmp('call_me' + id).setValue(link);
                    if (action.result.data.is_get > 0) {
                        Ext.getCmp('sendm_' + id).close();
                        alert("Заявка занята другим оператором");
                    }
                },
                failure: function (response, opts) {
                    Ext.getCmp('sendm_' + id).close();
                    alert("Заявка занята другим оператором");
                }
            });
        }
    } else {
        wind.show();
    }
}
