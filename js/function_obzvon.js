
status_kz = ['На доставку', 'Отложенная доставка', 'Свежий', 'Нет товара', 'Обратная доставка отправлена', 'Готов к консультации', 'Вручить подарок', 'Скинут предоплату'];

//
// Анкета "Обзвон"
//
function CreateMenuObzvon(id, country, closed_level, params) {
    console.log('Анкета "Обзвон" id=' + id + ', country = ' + country + ', closed_level=' + closed_level + ', params=' + params);

    globalOffersStore.clearFilter(true);
    if (closed_level) {
//        globalOffersStore.clearFilter(true);
    } else if (parseInt(id) === 0) {
//        globalOffersStore.clearFilter(true);
    } else {
//        globalOffersStore.filter('group', '##Пусто##');
    }

    if (session_operatorrecovery > 0) {
        console.log(session_operatorrecovery + 'filter Общая медицина');
        globalOffersStore.filter('group', 'Общая медицина');
    }

    // All cities list
    var aAllCities = [];

    // All regions list
    var aAllRegions = [];

    Ext.define('DeliveryCityModel', {
        extend: 'Ext.data.Model',
        fields: [
            'zip',
            'city'
        ]
    });

    Ext.create('Ext.data.Store', {
        storeId: 'DeliveryCityStore',
        model: 'DeliveryCityModel',
        data: []
    });

    // Regions
    Ext.define('DeliveryCityRegionModel', {
        extend: 'Ext.data.Model',
        fields: [
            'zip',
            'region'
        ]
    });

    Ext.create('Ext.data.Store', {
        storeId: 'DeliveryCityRegionStore',
        model: 'DeliveryCityRegionModel',
        data: []
    });

    // Offers
    Ext.define('OffersModel', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'name', type: 'string'},
            {name: 'value', type: 'string'}
        ]
    });

    Ext.create('Ext.data.Store', {
        storeId: 'OffersStore',
        model: 'OffersModel',
        data: []
    });

    // Potential Earnings
    Ext.create('Ext.data.Store', {
        storeId: 'potentialEarningsStore',
        fields: [
            'count',
            'price',
            'earnings'
        ],
        data: []
    });

    var dateDelivery = new Date();
    dateDelivery.setDate(dateDelivery.getDate() - 0);
    country = country.toLowerCase();

    var delivery_store;
    if (country === 'kzg') {
        delivery_store = globalDeliveryCouriersStore.ValuesCountryAllowedArr['kg'];
    } else if (country === 'md') {
        delivery_store = [];
    } else {
        delivery_store = globalDeliveryCouriersStore.ValuesCountryAllowedArr[country];
    }

    // offer property creator
    var myProperty = {
        labels: {
            attribute: "Атрибут",
            color: "Цвет",
            size: "Размер",
            type: "Тип",
            vendor: "Модель",
            name: "Название",
            description: "Описание"
        },
        getLabel: function (key) {
            if (typeof this.labels[key] !== "undefined") {
                return this.labels[key];
            }

            return this.labels['attribute'];
        },
        create: function (name, type, id, options, value) {
            console.log('create myProperty => name: ' + name + ', type: ' + type + ', id: ' + id + ', options: ' + options + ', value: ' + value);
            switch (type) {
                //
                case "name":
                    var tpl = {
                        xtype: 'textfield',
                        id: 'offer_property_' + type + id,
                        name: name + '[' + type + '][' + id + ']',
                        fieldLabel: this.getLabel(type),
                        allowBlank: false,
                        flex: 1
                    };

                    if (typeof value !== "undefined") {
                        tpl.value = value;
                    }
                    break;
                case "description":
                    var tpl = {
                        xtype: 'textfield',
                        id: 'offer_property_' + type + id,
                        name: name + '[' + type + '][' + id + ']',
                        fieldLabel: this.getLabel(type),
                        allowBlank: true,
                        flex: 1
                    };

                    if (typeof value !== "undefined") {
                        tpl.value = value;
                    }
                    break;
                case "gift_price":
                    var tpl = [{
                            xtype: 'checkboxfield',
                            id: 'offer_property_gift' + id,
                            name: name + '[gift][' + id + ']',
                            fieldLabel: 'Подарок',
                            margins: '0',
                            flex: 1,
                            listeners: {
                                change: function (el, newValue, oldValue, eOpts) {
                                    if (newValue) {
                                        var gift_price = Ext.getCmp('offer_property_' + type + id)
                                                .getValue();

                                        Ext.getCmp('dop_tovar_count_' + id)
                                                .setValue(1)
                                                .setReadOnly(true);

                                        Ext.getCmp('dop_tovar_price_' + id)
                                                .setValue(gift_price)
                                                .setReadOnly(true);
                                    } else {
                                        Ext.getCmp('dop_tovar_count_' + id)
                                                .setValue(1)
                                                .setReadOnly(false);

                                        Ext.getCmp('dop_tovar_price_' + id)
                                                .setValue(0)
                                                .setReadOnly(false);
                                    }
                                }
                            }
                        }, {
                            xtype: 'hidden',
                            id: 'offer_property_' + type + id,
                            name: name + '[' + type + '][' + id + ']',
                            value: options[0].value
                        }];
                    break;
                default:
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

                    if (typeof value !== "undefined") {
                        tpl.value = value;
                    }
                    break;
            }

            if (typeof tpl === "undefined") {
                return;
            }

            return tpl;
        },
        panel: function (attributes) {
            if (typeof attributes === "undefined") {
                var attributes = {
                    offer: "",
                    count: 0,
                    price: 0
                };
            }

            if (typeof attributes.offer === "undefined") {
                attributes.offer = '';
            }

            if (typeof attributes.count === "undefined") {
                attributes.count = 0;
            }

            if (typeof attributes.price === "undefined") {
                attributes.price = 0;
            }

            // generate unique ID
            var fieldID = Ext.id();

            // Источник
//            var tmp_staff_id = parseInt(Ext.getCmp('staff_id' + id).getValue());
//            var dopOfferStore = Ext.data.StoreManager.lookup('OffersStore');
//            if (globalAllColdStaffArr.indexOf(tmp_staff_id) > -1) {
//                if (country === 'kz') {
//                    console.log('filter: offer_show_in_cold_kz');
//                    globalOffersStore.filter('offer_show_in_cold_kz', '1');
//                    dopOfferStore = globalOffersStore.ValuesColdKzArr;
//                } else if (country === 'kzg') {
//                    console.log('filter: offer_show_in_cold_kgz');
//                    globalOffersStore.filter('offer_show_in_cold_kgz', '1');
//                    dopOfferStore = globalOffersStore.ValuesColdKgzArr;
//                }
//            }

            var panel = Ext.create('Ext.form.Panel', {
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
                                id: 'dop_tovar_offer_' + fieldID,
                                name: 'dop_tovar[offer][' + fieldID + ']',
                                fields: ['id', 'value', 'price'],
                                fieldLabel: 'Товар',
                                lazyRender: true,
                                allowBlank: false,
                                editable: false,
                                typeAhead: true,
                                typeAheadDelay: 100,
                                queryMode: 'local',
                                store: globalOffersStore,
                                valueField: 'value',
                                displayField: 'name',
                                flex: 2,
                                listeners: {
                                    change: function (el, offerValue) {
                                        var propertiesContainer = Ext.getCmp('offer-properties' + fieldID);
                                        propertiesContainer.removeAll();

                                        Ext.Object.each(offers_with_properies, function (offer, offer_data) {
                                            if (offerValue === offer) {
                                                if (Object.keys(offer_data.properties).length === 0) {
                                                    return false;
                                                }

                                                Ext.Object.each(offer_data.properties, function (type, properties) {
                                                    var options = [];

                                                    Ext.Array.each(properties, function (property, index) {
                                                        options.push({
                                                            'name': property,
                                                            'value': property
                                                        });
                                                    });

                                                    var value = '';

                                                    if (typeof attributes[type] !== "undefined") {
                                                        value = attributes[type];
                                                    }

                                                    var tpl = myProperty.create("dop_tovar", type, fieldID, options, value);

                                                    propertiesContainer.add(tpl);
                                                });

                                                if (typeof attributes.gift !== "undefined") {
                                                    var property_gift = Ext.getCmp("offer_property_gift" + fieldID);
                                                    if (property_gift) {
                                                        property_gift.setValue(true);
                                                    }
                                                }

                                                return false;
                                            }
                                        });
                                    },
                                    blur: function () {
                                        Ext.data.StoreManager.lookup('OffersStore').clearFilter();
                                    }
                                }
                            }, {
                                xtype: 'numberfield',
                                fieldLabel: 'Количество',
                                allowBlank: false,
                                id: 'dop_tovar_count_' + fieldID,
                                itemId: 'dop_tovar_count',
                                name: 'dop_tovar[count][' + fieldID + ']',
                                value: attributes.count < 1 ? 1 : attributes.count,
                                enableKeyEvents: true,
                                mouseWheelEnabled: false,
                                hideTrigger: true,
                                minValue: 1,
                                maxValue: 99,
                                valueField: 'id',
                                displayField: 'value',
                                flex: 1,
                                listeners: {
                                    change: function () {
                                        var col = this.getValue();
                                        var razn = col - 5;
                                        var prev_price = this.ownerCt.items.items[2].getValue();
                                        var prod = str_replace('-', '_', this.ownerCt.items.items[0].getValue());
                                        var offer_count = Ext.getCmp('package' + id).getValue();
                                        var staff_id = parseInt(Ext.getCmp('staff_id' + id).getValue());

                                        var evalPriceStr = 'if (' + prod + '_' + country + ') ' + prod + '_' + country;

                                        var pprice = 0;
                                        if (globalAllColdStaffArr.indexOf(staff_id) > -1) {
                                            var dp = 1000;
                                            Ext.Array.each(Ext.ComponentQuery.query('#dop_tovar_price'), function (item) {
                                                if (item.getValue() % 3000 !== 0) {
                                                    dp = 0;
                                                }
                                            });
                                            if (offer_count > 1)
                                                pprice = col * 3000;
                                            else
                                                pprice = (col * 3000) + dp;
                                        } else {
                                            if (eval(evalPriceStr + '[' + col + ']') === 'undefined') {
                                                pprice = eval(evalPriceStr + '[5]') + razn * eval(evalPriceStr + '[0]') * 0.7;
                                            } else {
                                                pprice = eval(evalPriceStr + '[' + col + ']') * 0.7;
                                            }
                                        }
                                        var tprice = Ext.getCmp('price' + id).getValue();
                                        pprice = Math.round(pprice / 10) * 10;
                                        if (col) {
                                            this.ownerCt.items.items[2].setValue(pprice);
                                            var sprice = tprice + pprice - prev_price;
                                        } else {
                                            this.ownerCt.items.items[2].setValue('0');
                                            var sprice = tprice - prev_price;
                                        }
                                        Ext.getCmp('price' + id).setValue(sprice.toString());

                                        calculateTicketsCount(id, true);
                                    }
                                }
                            }, {
                                xtype: 'textfield',
                                id: 'dop_tovar_price_' + fieldID,
                                name: 'dop_tovar[price][' + fieldID + ']',
                                editable: false,
                                itemId: 'dop_tovar_price',
                                fieldLabel: 'Цена',
                                value: attributes.price,
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

            Ext.getCmp('MenuForm_' + id).add(panel);

            Ext.getCmp("dop_tovar_offer_" + fieldID).setValue(attributes.offer);
        }
    };

    //
    var tprice = 0;
    var town = Ext.create('Ext.data.ArrayStore', {
        storeId: 'town' + id,
        proxy: {
            type: 'ajax',
            url: 'get_AddressKz.php?c=' + country,
            reader: {type: 'array'}
        },
        listeners: {
            load: function (store, records, options) {
                if (store.data.items.length < 1) {
                    Ext.getCmp('index' + id).setValue('');
                }
            }
        },
        fields: ['id', 'value', 'short', 'obl', 'rayon'],
        initComponent: function () {
            this.addEvents('ready');
        },
        is_ready: function () {
            this.fireEvent('ready', this);
        }
    });
    town.load();

    var wind = Ext.getCmp('windAnket_' + id);

    if (!wind) {

        wind = Ext.create('Ext.Window', {
            title: 'ID заказа - ' + id,
            id: 'windAnket_' + id,
            modal: false,
            height: 520,
            closable: closed_level || [11111111, 11119999, 78945378].indexOf(globalStaffId) > -1,
            width: 950,
            maximized: true,
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
                    region: 'north',
                    id: 'cert' + id,
                    hidden: true,
                    tbar: Ext.create('Ext.toolbar.Toolbar', {
                        cls: 'crm-main-top-header',
                        items: [
                            {xtype: 'button',
                                text: 'Отправить сертификат',
                                handler: function () {
                                    if (country === 'kz' || country === 'KZ') {
                                        var mail = Ext.getCmp('mail' + id).getValue();
                                        var offer = Ext.getCmp('offer' + id).getValue();
                                        Ext.Ajax.request({
                                            method: "GET",
                                            url: '/handlers/send_cert.php?mail=' + mail + '&offer=' + offer,
                                            success: function (response) {
                                                data = response.responseText;
                                                alert(data);
                                            }
                                        });
                                    }
                                }
                            },
                            {xtype: 'textfield',
                                id: 'mail' + id,
                                emptyText: 'Введите email клиента',
                                regex: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                            }, '->', {
                                xtype: 'combo',
                                id: 'subids' + id,
                                fieldLabel: 'Закрепить за',
                                name: 'subids',
                                editable: false,
                                forceSelection: true,
                                triggerAction: 'all',
                                lazyRender: true,
                                allowBlank: true,
                                queryMode: 'local',
                                store: [],
                                valueField: 'id',
                                displayField: 'id'
                            }
                        ]
                    })
                }, {
                    xtype: 'panel',
                    autoScroll: true,
                    region: 'center',
                    width: 670,
                    fbar: [{
                            xtype: 'button',
                            id: 'add_button_dop' + id,
                            text: 'Доп. товар',
                            hidden: closed_level >= 3,
                            handler: function () {
                                // add panel
                                myProperty.panel();
                            }
                        }, {
                            xtype: 'button',
                            hidden: closed_level >= 5,
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
                                        if (["color", "size", "type", "vendor"].indexOf(offer_property_matches[1]) > -1) {
                                            offer_property_matches.push(value);

                                            if (typeof offer_property[offer_property_matches[1]] === "undefined") {
                                                offer_property[offer_property_matches[1]] = '';
                                            }

                                            // offer_property[offer_property_matches[1]] = offer_property_matches[3];
                                            offer_property[offer_property_matches[1]] = offer_property_matches[3];
                                        }
                                    }

                                    // dop_tovar
                                    var dop_tovar_matches = index.match(/^dop_tovar\[(.+)\]\[(.+)\]$/);

                                    if (dop_tovar_matches) {
                                        if (["offer", "color", "size", "type", "vendor"].indexOf(dop_tovar_matches[1]) > -1) {
                                            iteration_id = dop_tovar_matches[2];
                                            dop_tovar_matches.push(value);

                                            if (typeof dop_tovar[iteration_id] === "undefined") {
                                                dop_tovar[iteration_id] = {};
                                            }

                                            if (typeof dop_tovar[iteration_id][dop_tovar_matches[1]] === "undefined") {
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
                                    var offer = value['offer'];

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
                                            str = str + result.offer + ": " + result.count + " шт.<br/>";
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
                            xtype: 'displayfield',
                            id: 'prognosis_tickets_count' + id,
                            style: {color: 'red'},
                            fieldLabel: 'К-во билетов',
                            labelStyle: 'font-weight: bold; color: darkgreen;',
                            fieldStyle: 'font-weight: bold; color: red;',
                            value: 0
                        }, {
                            xtype: 'button',
                            text: 'Сохранить',
                            id: 'sbutton' + id,
                            handler: function (button) {
                                var fp = button.up('panel').child('form');
                                console.log('-111');
                                if (fp.getForm().isValid()) {
                                    console.log('000');
                                    if (
                                            Ext.getCmp('status' + id).getValue() === 'Подтвержден' && parseInt(Ext.getCmp('have_orders' + id).getValue()) > 0 &&
                                            parseInt(Ext.getCmp('staff_id' + id).getValue()) !== 53095975 && Ext.getCmp('offer' + id).getValue() !== 'gentlemen'
                                            ) {

                                        Ext.Msg.alert('Внимание!', 'Запрет на оформление Заказа!!!<br/>ЕСТЬ ДУБЛЬ!!!');

//                                        Ext.Msg.confirm('Внимание', 'Вы уверены что хотите оформить дубль по заказу? <br/>Так как будет штраф, если и по товару будет дубль!!!?',
//                                                function (btn) {
//                                                    if (btn === 'yes') {
//                                                        setPlusStatus(id, country, params);
//                                                    } else {
//                                                        this.setValue(oldValue);
//                                                    }
//                                                }
//                                        );
                                    } else {
                                        console.log('111');
                                        fixed = Ext.getCmp('subids' + id).getValue();
                                        console.log('222');
                                        console.log(fp);
                                        console.log(fp.getForm());
                                        console.log('/handlers/set_menu_obzvon.php?ext_id=1&country=' + country + '&id=' + id);
                                        fp.getForm().submit({
                                            url: '/handlers/set_menu_obzvon.php?ext_id=1&country=' + country + '&id=' + id,
                                            waitMsg: 'Жди...',
                                            success: function (fp, action) {
                                                Ext.getCmp('windAnket_' + id).close();
                                                progressAction();
                                            }
                                        });
                                    }
                                } else {
                                    console.log('Not valid');
                                    console.log(fp.query("field{isValid()==false}"));
                                }
                            }
                        }],
                    items: [{
                            xtype: 'form',
                            id: 'MenuForm_' + id,
                            url: '/handlers/get_menu_obzvon.php?id=' + id,
                            border: false,
                            padding: 10,
                            fileUpload: true,
                            layout: 'anchor',
                            defaults: {
                                anchor: '100%',
                                labelWidth: 150
                            },
                            items: [
                                {
                                    xtype: 'hidden',
                                    id: 'have_orders' + id,
                                    name: 'have_orders'
                                }, {
                                    xtype: 'fileuploadfield',
                                    name: 'callrecord',
                                    submitValue: true,
                                    emptyText: 'Добавьте файл записи для загрузки',
                                    fieldLabel: 'Файл' + ' .m4a',
                                    buttonText: 'Выбрать',
                                    hidden: true,
                                    regex: /^.*\.m4a$/,
                                    id: 'callUpload' + id
                                }, {
                                    xtype: 'displayfield',
                                    name: 'history',
                                    fieldLabel: 'История заказов'
                                }, {
                                    xtype: 'displayfield',
                                    html: 'НЕт',
                                    id: 'act' + id,
                                    fieldLabel: 'Акция'
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Вызов',
                                    id: 'call_me' + id,
                                    name: 'call_me'
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'ID',
                                    name: 'id'
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Всего звонков',
                                    name: 'rings'
                                }, {
                                    xtype: 'linkfield',
                                    fieldLabel: 'Ленд',
                                    id: 'site' + id,
                                    name: 'site'
                                }, {
                                    xtype: 'hidden',
                                    id: 'payed_curr_year_count' + id,
                                    name: 'payed_curr_year_count'
                                }, {
                                    xtype: 'hidden',
                                    name: 'uuid',
                                    id: 'uuid' + id
                                }, {
                                    xtype: 'hidden',
                                    name: 'staff_id',
                                    id: 'staff_id' + id,
                                    hiddenName: 'staff_id',
                                    value: (id === 0 && typeof (params) !== 'undefined' && params.staff_id) ? params.staff_id : ''
                                }, {
                                    xtype: 'hidden',
                                    name: 'web_id',
                                    id: 'web_id' + id,
                                    hiddenName: 'web_id',
                                    value: (id === 0 && typeof (params) !== 'undefined' && params.web_id) ? params.web_id : ''
                                }, {
                                    xtype: 'displayfield',
                                    fieldLabel: 'Источник',
                                    id: 'display_staff_id' + id
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: '№ Телефона',
                                    id: 'phone' + id,
                                    minLength: 9,
                                    maxLength: 13,
                                    regex: /^\d+$/,
                                    allowBlank: false,
                                    name: 'phone'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'ФИО',
                                    name: 'fio'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Индекс',
                                    id: 'index' + id,
                                    name: 'index'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Район',
                                    id: 'district' + id,
                                    name: 'district'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Город',
                                    queryMode: 'remote',
                                    store: town,
                                    hideTrigger: true,
                                    typeAhead: true,
                                    typeAheadDelay: 1000,
                                    autoSelect: false,
                                    allowBlank: false,
                                    autoShow: false,
                                    autoFitErrors: false,
                                    trackResetOnLoad: false,
                                    editable: true,
                                    id: 'dcity' + id,
                                    name: 'city',
                                    minChars: 2,
                                    hiddenName: 'city',
                                    valueField: 'id',
                                    displayField: 'value',
                                    enableKeyEvents: true,
                                    listeners: {
                                        keyup: function () {
                                            var vart = this.getValue();
                                            if (vart) {
                                                if (vart.length > 1) {
                                                    town.getProxy().extraParams = {
                                                        searchString: vart,
                                                        searchType: 'city'
                                                    };
                                                    town.load({params: {clearOnPageLoad: false}});
                                                }
                                            }
                                        },
                                        select: function () {
                                            var va = this.getValue();
                                            if (va) {

                                                checkStatusKz(id);

                                                var itemd = town.find('id', va);

                                                this.setValue(town.data.items[itemd].data.value);
                                                this.setRawValue(town.data.items[itemd].data.short);

                                                Ext.getCmp('index' + id).setValue(va);
                                                var cu = globalDeliveryCouriersStore.ValuesIndexJson[va] ? globalDeliveryCouriersStore.ValuesIndexJson[va] : '';
                                                if (cu && globalDeliveryCouriersStore.ValuesCountryAllowedJson[country] && globalDeliveryCouriersStore.ValuesCountryAllowedJson[country][cu]) {

                                                    Ext.getCmp('kz_delivery' + id).setValue(cu);

                                                    var aRegions = [];
                                                    Ext.each(aAllCities, function (cval, cid) {
                                                        if (cval.city == cu) {
                                                            Ext.each(aAllRegions, function (rval, rid) {
                                                                if (rval.zip.indexOf(cval.zip.trim(), 0) >= 0) {
                                                                    aRegions.push({
                                                                        zip: rval.zip.trim(),
                                                                        region: rval.region
                                                                    });
                                                                }
                                                            });
                                                            return false;
                                                        }
                                                    });

                                                    var regionStore = Ext.data.StoreManager.lookup('DeliveryCityRegionStore');
                                                    regionStore.removeAll();
                                                    Ext.getCmp('city_region' + id).reset();
                                                    if (aRegions.length > 0) {
                                                        regionStore.add(aRegions);
                                                    }

                                                    if (cu !== 'Почта') {
                                                        Ext.getCmp('status_kz' + id).allowBlank = false;
                                                        Ext.getCmp('status_kz' + id).validate();
                                                    }
                                                }
                                                Ext.getCmp('addr' + id).setValue(town.data.items[itemd].data.obl + ', ' + town.data.items[itemd].data.rayon + ', ' + town.data.items[itemd].data.short + ' ');
                                                Ext.getCmp('district' + id).setValue(town.data.items[itemd].data.rayon);
                                            } else {
                                                Ext.getCmp('index' + id).setValue('');
                                                Ext.getCmp('kz_delivery' + id).setValue('Почта');
                                                Ext.getCmp('addr' + id).setValue('');
                                                Ext.getCmp('district' + id).setValue('');
                                            }
                                        }
                                    }
                                }, {
                                    xtype: 'combo',
                                    id: 'city_region' + id,
                                    fieldLabel: 'Район города',
                                    name: 'city_region',
                                    editable: false,
                                    forceSelection: true,
                                    triggerAction: 'all',
                                    lazyRender: true,
                                    allowBlank: true,
                                    hidden: false,
                                    queryMode: 'local',
                                    store: Ext.data.StoreManager.lookup('DeliveryCityRegionStore'),
                                    valueField: 'region',
                                    displayField: 'region'
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Адрес',
                                    id: 'addr' + id,
                                    name: 'addr'
                                }, {
                                    xtype: 'fieldcontainer',
                                    layout: 'hbox',
                                    labelWidth: 50,
                                    fieldLabel: ' ',
                                    defaults: {
                                        //flex: 1,
                                        hideLabel: false
                                    },
                                    items: [{
                                            xtype: 'textfield',
                                            fieldLabel: 'Улица',
                                            id: 'street' + id,
                                            name: 'street',
                                            hiddenName: 'street',
                                            labelWidth: 90,
                                            width: 250,
                                            anchor: '100%'
                                        }, {
                                            xtype: 'textfield',
                                            fieldLabel: 'Дом',
                                            name: 'building',
                                            hiddenName: 'building',
                                            id: 'building' + id,
                                            labelWidth: 50,
                                            width: 120,
                                            anchor: '60%'
                                        }, {
                                            xtype: 'textfield',
                                            fieldLabel: 'Квартира',
                                            hiddenName: 'flat',
                                            name: 'flat',
                                            id: 'flat' + id,
                                            labelWidth: 90,
                                            width: 150,
                                            anchor: '60%'
                                        }
                                    ]
                                }, {
                                    xtype: 'checkbox',
                                    fieldLabel: 'Не понимает RU',
                                    name: 'not_rus',
                                    inputValue: 1
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: 'Недуги/беспокойства клиента',
                                    name: 'client_worries',
                                    maxLength: 96,
                                    allowBlank: false,
                                    labelStyle: 'font-weight: bold; color: black;',
                                    checkChangeBuffer: 1000,
                                    listeners: {
                                        'change': function (e, newValue) {
                                            var uuid = Ext.getCmp('uuid' + id).getValue();
                                            console.log('client_worries: change UUID =>' + uuid);

                                            Ext.Ajax.request({
                                                url: '/handlers/handler_clients.php',
                                                method: 'POST',
                                                params: {
                                                    method: 'update_worries',
                                                    uuid: uuid,
                                                    client_worries: newValue
                                                },
                                                success: function (response, opts) {
                                                    console.log(response);
                                                },
                                                failure: function (response, opts) {
                                                    Ext.Msg.alert('Ошибка!', 'Ошибка сохранения недуг/беспокойств клиента');
                                                }
                                            });
                                        }
                                    }
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Продукт',
                                    store: globalOffersStore,
                                    editable: false,
                                    allowBlank: false,
                                    id: 'offer' + id,
                                    name: 'offer',
                                    valueField: 'value',
                                    regex: /.{3,}/,
                                    displayField: 'name',
                                    listeners: {
                                        change: function (el, offerValue, oldValue) {

                                            if (parseInt(id) === 0 || true) {

                                                checkStatusKz(id);

                                                Ext.Ajax.request({
                                                    url: '/handlers/get_OfferProperties.php',
                                                    method: 'GET',
                                                    params: {
                                                        offer: offerValue
                                                    },
                                                    scope: this,
                                                    timeout: 5000,
                                                    success: function (response, opts) {
                                                        // свойства товара
                                                        var container = Ext.getCmp('offer-property-fields' + id);
                                                        if (container) {
                                                            container.removeAll();
                                                        }

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

                                                            console.log(options);
                                                            var tpl = myProperty.create('offer_property', type, id, options);
                                                            container.add(tpl);
                                                            container.setDisabled(false);
                                                            container.show();
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
                                    maxValue: 100,
                                    minValue: 1,
                                    mouseWheelEnabled: false,
                                    autoScroll: false,
                                    id: 'package' + id,
                                    hideTrigger: true,
                                    enableKeyEvents: true,
                                    name: 'package',
                                    listeners: {
                                        keyup: function () {
                                            var col = this.getValue();
                                            var razn = col - 5;
                                            var prod = Ext.getCmp('offer' + id).getValue() === '0' ? '' : str_replace('-', '_', Ext.getCmp('offer' + id).getValue());
                                            var tmp_staff_id = parseInt(Ext.getCmp('staff_id' + id).getValue());

                                            var evalPriceStr = prod + '_' + country;

                                            var usePrices = [0];
                                            console.log(evalPriceStr);
                                            console.log('if (typeof(' + evalPriceStr + ') !== "undefined") usePrices = ' + evalPriceStr + ';');
                                            eval('if (typeof(' + evalPriceStr + ') !== "undefined") usePrices = ' + evalPriceStr + ';');
                                            if (globalAllColdStaffArr.indexOf(tmp_staff_id) > -1) {
                                                console.log('cold');
                                                eval('if (typeof(' + evalPriceStr + '_cold) !== "undefined") usePrices = ' + evalPriceStr + '_cold;');
                                            }
                                            var pprice = usePrices[col] ? usePrices[col] : usePrices[5] + razn * usePrices[0];
                                            Ext.getCmp('price' + id).setValue(pprice);
                                        },
                                        change: function () {
                                            calculateTicketsCount(id, true);
                                        }
                                    }
                                }, {
                                    layout: 'column',
                                    border: true,
                                    defaults: {
                                        anchor: '100%',
                                        labelWidth: 50,
                                        margin: 2
                                    },
                                    items: [
                                        {
                                            xtype: 'numberfield',
                                            name: 'price',
                                            fieldLabel: 'Цена',
                                            id: 'price' + id,
                                            width: 150,
                                            allowBlank: false,
                                            minValue: country === 'kz' ? 7979 : 1,
                                            editable: true
                                        }, {
                                            xtype: 'numberfield',
                                            name: 'pre_price',
                                            fieldLabel: 'рассрочка',
                                            id: 'pre_price' + id,
                                            width: 150,
                                            editable: true
                                        }, {
                                            xtype: 'numberfield',
                                            name: 'post_price',
                                            fieldLabel: 'осстаток',
                                            id: 'post_price' + id,
                                            width: 150,
                                            editable: true
                                        }
                                    ]
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
                                    layout: 'column',
                                    border: true,
                                    defaults: {
                                        anchor: '100%',
                                        labelWidth: 50,
                                        margin: 2,
                                        allowBlank: false
                                    },
                                    items: [
                                        {
                                            xtype: 'combo',
                                            fieldLabel: 'Пол',
                                            id: 'sex' + id,
                                            editable: false,
                                            allowBlank: session_offline_island > 0,
                                            columnWidth: 0.5,
                                            name: 'sex',
                                            valueField: 'id',
                                            displayField: 'value',
                                            store: [['0', '-Не указан-'], ['1', 'Мужчина'], ['2', 'Женщина']]
                                        }, {
                                            xtype: 'combo',
                                            fieldLabel: 'Возраст',
                                            editable: false,
                                            allowBlank: session_offline_island > 0,
                                            columnWidth: 0.5,
                                            name: 'age',
                                            valueField: 'id',
                                            displayField: 'value',
                                            store: [
                                                ['0', '-Не указан-'], ['18', '18'], ['19', '19'], ['20', '20'], ['21', '21'], ['22', '22'], ['23', '23'], ['24', '24'], ['25', '25'], ['26', '26'],
                                                ['27', '27'], ['28', '28'], ['29', '29'], ['30', '30'], ['31', '31'], ['32', '32'], ['33', '33'], ['34', '34'], ['35', '35'], ['36', '36'], ['37', '37'],
                                                ['38', '38'], ['39', '39'], ['40', '40'], ['41', '41'], ['42', '42'], ['43', '43'], ['44', '44'], ['45', '45'], ['46', '46'], ['47', '47'],
                                                ['48', '48'], ['49', '49'], ['50', '50'], ['51', '51'], ['52', '52'], ['53', '53'], ['54', '54'], ['55', '55'], ['56', '56'], ['57', '57'], ['58', '58'],
                                                ['59', '59'], ['60', '60'], ['61', '61'], ['62', '62'], ['63', '63'], ['64', '64'], ['65', '65']
                                            ]
                                        }, {
                                            xtype: 'datefield',
                                            lazyRender: true,
                                            fieldLabel: 'День рождения',
                                            format: 'Y-m-d',
                                            startDay: 1,
                                            allowBlank: true,
                                            name: 'birthday'
                                        }
                                    ]
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
                                            console.log('status cange => oldValue: ' + oldValue + ', newValue: ' + newValue);

                                            switch (newValue) {
                                                // выбор статуса "Перезвонить"
                                                case 'Перезвонить':
                                                    new_field = Ext.create('Ext.form.field.Number', {
                                                        name: 'recall_date',
                                                        id: 'recall_date' + id,
                                                        anchor: '100%',
                                                        labelWidth: 150,
                                                        minValue: 1,
                                                        allowBlank: false,
                                                        maxValue: 300,
                                                        fieldLabel: 'Перезвонить через (часов)'
                                                    });
                                                    new_field2 = Ext.create('Ext.form.field.Date', {
                                                        name: 'recall_dates',
                                                        id: 'recall_dates' + id,
                                                        anchor: '100%',
                                                        labelWidth: 150,
                                                        allowBlank: true,
                                                        minValue: new Date().format('Y-m-d'),
                                                        maxValue: new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 4)).format('Y-m-d'),
                                                        format: 'Y-m-d H:i',
                                                        fieldLabel: 'Перезвонить дата/время'
                                                    });
                                                    //Ext.getCmp('MenuForm_' + id).add(new_field);
                                                    Ext.getCmp('MenuForm_' + id).add(new_field2);
                                                    // kz_delivery
                                                    var kz_delivery = Ext.getCmp('kz_delivery' + id);
                                                    kz_delivery.allowBlank = true;
                                                    kz_delivery.validate();

                                                    var item = Ext.getCmp('description' + id);
                                                    item.setEditable(false);
                                                    item.bindStore(globalCancelTypesStore.ValuesArr);
                                                    item.reset();
                                                    item.allowBlank = false;
                                                    item.validate();

                                                    // show properties for main product
                                                    var container = Ext.getCmp('offer-property-fields' + id);
                                                    container.setDisabled(true);
                                                    container.hide();
                                                    break;
                                                    // выбор статуса 'Подтвержден'
                                                case 'Подтвержден':
                                                case 'Предварительно подтвержден':
                                                    // kz_delivery
                                                    var kz_delivery = Ext.getCmp('kz_delivery' + id);
                                                    kz_delivery.allowBlank = false;
                                                    kz_delivery.validate();
                                                    if (country === 'kzg') {
                                                        var send_st = Ext.getCmp('status_kz' + id);
                                                        send_st.allowBlank = false;
                                                        send_st.validate();
                                                    }
                                                    var item = Ext.getCmp('description' + id);

                                                    item.setEditable(true);
                                                    item.reset();
                                                    item.allowBlank = true;
                                                    item.validate();
                                                    // show properties for main product
                                                    var container = Ext.getCmp('offer-property-fields' + id);
                                                    container.setDisabled(false);
                                                    container.show();
                                                    break;
                                                    // остальные варианты
                                                default:
                                                    // kz_delivery
                                                    var kz_delivery = Ext.getCmp('kz_delivery' + id);
                                                    kz_delivery.allowBlank = true;
                                                    kz_delivery.validate();

                                                    var item = Ext.getCmp('description' + id);

                                                    item.setEditable(true);
                                                    item.reset();
                                                    item.allowBlank = true;
                                                    item.validate();
                                                    // hide properties for main product
                                                    var container = Ext.getCmp('offer-property-fields' + id);
                                                    container.setDisabled(true);
                                                    container.hide();
                                                    break;
                                            }

                                            if (newValue === 'Отменён') {
                                                var item = Ext.getCmp('description' + id);
                                                item.setEditable(false);
                                                item.bindStore(globalCancelTypesStore.ValuesArr);
                                                item.reset();
                                                item.allowBlank = false;
                                                item.validate();

                                                var city = Ext.getCmp('dcity' + id);
                                                if (city) {
                                                    city.allowBlank = true;
                                                    city.validate();
                                                }
                                            } else if (oldValue === 'Отменён') {
                                                var item = Ext.getCmp('description' + id);
                                                item.setEditable(true);
                                                item.store.removeAll();
                                                item.reset();
                                                item.allowBlank = true;
                                                item.validate();
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
                                                case 'Скинут предоплату':
                                                    var new_field = Ext.create('Ext.form.field.Date', {
                                                        name: 'datetime_otl',
                                                        anchor: '100%',
                                                        labelWidth: 150,
                                                        id: 'datetime_otl' + id,
                                                        format: 'Y-m-d H:i:s',
                                                        minValue: new Date().format('Y-m-d'),
                                                        maxValue: new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 4)).format('Y-m-d'),
                                                        allowBlank: false,
                                                        fieldLabel: 'Скинут предоплату: дата'
                                                    });
                                                    var formed = Ext.getCmp('MenuForm_' + id);
                                                    for (var i = 0; i < formed.items.items.length; i++) {
                                                        if (formed.items.items[i].id === 'status_kz' + id) {
                                                            formed.insert(i + 1, new_field);
                                                        }
                                                    }
                                                    break;
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
                                                case 'Готов к консультации':
                                                    var date_delivery = Ext.getCmp('date_delivery' + id);
                                                    date_delivery.setMaxValue(new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 14)).format('Y-m-d'));
                                                    date_delivery.allowBlank = false;
                                                    date_delivery.validate();
                                                    break;
                                                case 'На доставку':
                                                case 'Вручить подарок':
                                                    checkWorkDelivDays(id, true);
                                                    break;
                                            }

                                        }
                                    },
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'checkboxfield',
                                    id: 'pay_type' + id,
                                    name: 'pay_type',
                                    fieldLabel: 'Рассрочка?',
                                    margins: '0',
                                    inputValue: 1,
                                    listeners: {
                                        change: function (el, newValue, oldValue, eOpts) {
                                            var price = Ext.getCmp('price' + id).getValue();

                                            if (newValue) {
                                                var pprice = Math.floor((price / 100) * 0.7) * 100;
                                                Ext.getCmp('pre_price' + id).setValue(pprice);
                                                Ext.getCmp('post_price' + id).setValue(price - pprice);
                                            } else {
                                                Ext.getCmp('pre_price' + id).setValue(0);
                                                Ext.getCmp('post_price' + id).setValue(0);
                                            }
                                        }
                                    }
                                }, {
                                    xtype: 'checkboxfield',
                                    id: 'pay_status' + id,
                                    name: 'pay_status',
                                    fieldLabel: 'Оплачен?',
                                    margins: '0',
                                    inputValue: 1
                                }, {
                                    xtype: 'datefield',
                                    lazyRender: true,
                                    fieldLabel: 'Дата доставки',
                                    format: 'Y-m-d',
                                    startDay: 1,
                                    allowBlank: true,
                                    minValue: dateDelivery.format('Y-m-d'),
                                    maxValue: new Date(new Date().getTime() + (1000 * 60 * 60 * 24 * 2)).format('Y-m-d'),
                                    id: 'date_delivery' + id,
                                    name: 'date_delivery'
                                }, {
                                    xtype: 'hidden',
                                    name: 'ext_id',
                                    id: 'ext_id' + id,
                                    hiddenName: 'ext_id'
                                }, {
                                    xtype: 'hidden',
                                    name: 'konkus_bilet_count',
                                    id: 'konkus_bilet_count' + id
                                }, {
                                    xtype: 'hidden',
                                    name: 'order_payed_2020_count',
                                    id: 'order_payed_2020_count' + id
                                }, {
                                    xtype: 'hidden',
                                    name: 'order_payed_2020_total',
                                    id: 'order_payed_2020_total' + id
                                }, {
                                    xtype: 'combo',
                                    queryMode: 'local',
                                    name: 'description',
                                    id: 'description' + id,
                                    store: [],
                                    fieldLabel: 'Подстатус (коммент)',
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    queryMode: 'local',
                                    name: 'deliv_desc',
                                    id: 'deliv_desc' + id,
                                    store: [],
                                    fieldLabel: 'Описание доставки',
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'В работе у',
                                    queryMode: 'local',
                                    name: 'oper_use',
                                    id: 'oper_use' + id,
                                    store: [
                                        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25',
                                        '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '44', '44', '45',
                                        '46', '47', '48', '49', '50'
                                    ],
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Тип доставки',
                                    store: delivery_store,
                                    lazyRender: true,
                                    editable: false,
                                    queryMode: 'local',
                                    name: 'kz_delivery',
                                    id: 'kz_delivery' + id,
                                    allowBlank: true,
                                    minLength: 4,
                                    listeners: {
                                        change: function (el, newValue) {
                                            if (newValue === 'Астана Курьер' || newValue === 'Алматы Курьер') {
                                            } else {

                                                var aRegions = [];
                                                Ext.each(aAllCities, function (cval, cid) {
                                                    if (cval.city == newValue) {
                                                        Ext.each(aAllRegions, function (rval, rid) {
                                                            if (rval.zip.indexOf(cval.zip.trim(), 0) >= 0) {
                                                                aRegions.push({
                                                                    zip: rval.zip.trim(),
                                                                    region: rval.region
                                                                });
                                                            }
                                                        });
                                                        return false;
                                                    }
                                                });

                                                var regionStore = Ext.data.StoreManager.lookup('DeliveryCityRegionStore');
                                                regionStore.removeAll();
                                                Ext.getCmp('city_region' + id).reset();
                                                if (aRegions.length > 0) {
                                                    regionStore.add(aRegions);
                                                }

                                                checkWorkDelivDays(id, true);
                                            }

                                        }
                                    }
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: 'Проблема',
                                    queryMode: 'local',
                                    name: 'problem',
                                    id: 'problem' + id,
                                    store: globalObzvonProblemsStore,
                                    valueField: 'value',
                                    displayField: 'value'
                                }, {
                                    xtype: 'container',
                                    layout: 'hbox',
                                    defaults: {
                                        labelWidth: 150
                                    },
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            fieldLabel: 'Доп. телефон',
                                            name: 'phone_sms',
                                            regex: /^\d+$/,
                                            regexText: 'Допустимы только цифры',
                                            id: 'phone_sms' + id,
                                            enableKeyEvents: true,
                                            listeners: {
                                                keyup: function (e, t, eOpts) {
                                                    document.getElementById("phone_sms_button" + id).href = 'sip:' + (country === 'kz' ? '56*' : (country === 'kzg' ? '57*' : '8*')) + id;
                                                },
                                                change: function () {
                                                    document.getElementById("phone_sms_button" + id).href = 'sip:' + (country === 'kz' ? '56*' : (country === 'kzg' ? '57*' : '8*')) + id;
                                                }
                                            }
                                        }, {
                                            xtype: 'displayfield',
                                            hideLabel: true,
                                            value: '<a id="phone_sms_button' + id + '" style="font-size: 28px; line-height: 22px; margin: 0 0 0 5px;" href="javascript:void(0)">&#9990;</a>'
                                        }]
                                }
                            ]
                        }]
                }, {
                    region: 'east',
                    id: 'west-panel' + id,
                    title: 'Данные по товару',
                    split: true,
                    width: 230,
                    minSize: 175,
                    maxSize: 300,
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
                }, {
                    region: 'west',
                    id: 'est-panel' + id,
                    title: 'Поиск адреса',
                    split: true,
                    width: 280,
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
                    },
                    items: [
                        {
                            xtype: 'form',
                            id: 'AddrForm_' + id,
                            url: '/handlers/get_menu_obzvon.php?id=' + id,
                            border: false,
                            autoScroll: true,
                            labelWidth: 80,
                            padding: 5,
                            items: [{
                                    xtype: 'combo',
                                    lazyRender: true,
                                    editable: true,
                                    queryMode: 'local',
                                    name: 'oblast',
                                    anchor: '100%',
                                    id: 'oblast' + id,
                                    labelWidth: 80,
                                    width: 290,
                                    store: Ext.create('Ext.data.ArrayStore', {
                                        storeId: 'oblstore',
                                        proxy: {
                                            type: 'ajax',
                                            url: '/get_AddressKz.php?searchType=obl',
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
                                    fieldLabel: 'Область',
                                    valueField: 'id',
                                    displayField: 'value',
                                    listeners: {
                                        change: function () {
                                            var rayon = Ext.getCmp('oblast' + id).getValue();
                                            Ext.Ajax.request({
                                                method: "GET",
                                                url: '/get_AddressKz.php?searchType=rayon&id=' + rayon,
                                                success: function (response) {
                                                    nstore = Ext.decode(response.responseText);
                                                    Ext.getCmp('rayon' + id).bindStore(nstore);
                                                }
                                            });
                                        }
                                    }
                                }, {
                                    xtype: 'combo',
                                    lazyRender: true,
                                    editable: true,
                                    queryMode: 'local',
                                    name: 'rayon',
                                    anchor: '100%',
                                    id: 'rayon' + id,
                                    labelWidth: 80,
                                    width: 290,
                                    store: [],
                                    fieldLabel: 'Район',
                                    valueField: 'id',
                                    displayField: 'value',
                                    listeners: {
                                        change: function () {
                                            var rayon = Ext.getCmp('rayon' + id).getValue();
                                            Ext.Ajax.request({
                                                method: "GET",
                                                url: '/get_AddressKz.php?searchType=gorod&id=' + rayon,
                                                success: function (response) {
                                                    // Ext.getCmp('gorod'+id).update(response.responseText);
                                                    // Ext.getCmp('gorod'+id).autoScroll = true;
                                                    // Ext.getCmp('gorod'+id).doLayout();

                                                    var city = Ext.getCmp('gorod' + id);

                                                    if (city) {
                                                        city.update(response.responseText);
                                                        city.autoScroll = true;
                                                        city.doLayout();
                                                    }
                                                }
                                            });
                                        }
                                    }
                                }, {
                                    xtype: 'displayfield',
                                    editable: false,
                                    autoScroll: true,
                                    fieldLabel: '',
                                    id: 'gorod' + id
                                }
                            ]
                        }]
                }, {
                    region: 'west',
                    id: 'zp-panel' + id,
                    title: 'Таблица заработка',
                    split: true,
                    width: 130,
                    minSize: 100,
                    maxSize: 155,
                    collapsible: true,
                    collapsed: false,
                    autoScroll: true,
                    margins: '3 0 5 5',
                    paddings: '3 5 5 5',
                    cmargins: '3 5 5 5',
                    layout: 'accordion',
                    layoutConfig: {
                        animate: true
                    },
                    items: [
                        Ext.create('Ext.grid.Panel', {
                            title: "Потенциальный заработок",
                            store: Ext.data.StoreManager.lookup('potentialEarningsStore'),
                            columns: [
                                {
                                    header: 'К-во',
                                    dataIndex: 'count',
                                    width: 20
                                }, {
                                    header: 'Цена',
                                    dataIndex: 'price',
                                    flex: 1
                                }, {
                                    header: 'Зароботок',
                                    dataIndex: 'earnings',
                                    flex: 1,
                                    renderer: function (value) {
                                        return "<span style='color:red; font-weight:bold;'>" + value + "</span>";
                                    }
                                }
                            ]
                        })
                    ]
                }, {
                    region: 'north',
                    id: 'OprosnikPanel_' + id,
                    title: 'Опросник',
                    split: true,
                    hidden: false || [11111111].indexOf(globalStaffId) > -1,
                    minHeight: 200,
                    maxHeight: 200,
                    collapsible: true,
                    collapsed: false,
                    autoScroll: true,
                    autoDestroy: false,
                    layoutConfig: {
                        animate: true
                    },
                    items: [
                        {
                            xtype: 'form',
                            id: 'OprosnikForm_' + id,
                            url: '/handlers/get_Message.php?id=' + id,
                            border: false,
                            defaults: {
                                anchor: '100%',
                                labelWidth: 400
                            },
                            bodyStyle: {background: '#ffc', padding: '10px'},
                            items: [],
                            fbar: [{
                                    xtype: 'button',
                                    text: 'Сохранить',
                                    style: {
                                        background: '#00D800'
                                    },
                                    handler: function (button) {
                                        var fp = this.up('form');
                                        if (fp.getForm().isValid()) {
                                            var values = fp.getForm().getValues();
                                            values['id'] = id;
                                            fp.getForm().submit({
                                                url: '/handlers/set_Oprosnik.php?id=' + id,
                                                params: values,
//                                                waitMsg: 'Жди...',
                                                success: function (r, o) {
                                                    Ext.getCmp('OprosnikPanel_' + id).toggleCollapse();
                                                },
                                                failure: function (r, o) {
                                                    if (o.response.responseText) {
                                                        Ext.Msg.alert('Ошибка', Ext.JSON.decode(o.response.responseText).msg);
                                                    }
                                                    Ext.Msg.alert('Ошибка', Ext.JSON.decode(o.response.responseText).msg);
                                                }
                                            });
                                        }

                                    }
                                }
                            ]

                        }
                    ]
                }
            ]
        }).show();

        if (session_offline_island) {
            console.log();
            delFormElem(['status_kz' + id]);
        }

        if (id > 0) {
            Ext.getCmp('MenuForm_' + id).getForm().load({
                success: function (form, action) {

                    console.log(action.result.data);

                    if (action.result.data.is_get == '1') {
                        Ext.getCmp('windAnket_' + id).close();
                        alert("Заявка занята другим оператором");
                        return false;
                    }
                    var ideses = eval(action.result.data.ids);
                    if (Ext.getCmp('subids' + id)) {
                        Ext.getCmp('subids' + id).bindStore(ideses);
                    }
                    Ext.each(ideses, function (idval, idid) {
                        if (idval == action.result.data.fixed)
                            Ext.getCmp('subids' + id).setValue(idval);
                    });
                    if (action.result.data.country === 'kzg') {
                        Ext.getCmp('callUpload' + id).setVisible(true);
                        Ext.getCmp('callUpload' + id).validate();
                    }
                    if (Object.keys(action.result.data.offers_with_properies).length > 0) {
                        offers_with_properies = action.result.data.offers_with_properies;

                        Ext.Object.each(offers_with_properies, function (offer, offer_data) {
                            var offer = Ext.create('OffersModel', {
                                name: offer_data.name,
                                value: offer_data.title
                            });

                            // fill offers
                            Ext.data.StoreManager.lookup('OffersStore').add(offer);
                        });
                    }
                    if (eval('offer_discount.' + country + '___' + str_replace('-', '_', action.result.data.offer))) {
                        var act = eval('offer_discount.' + country + '___' + str_replace('-', '_', action.result.data.offer));
                        Ext.getCmp('act' + id).setValue(act[0] + ' предлагаем ' + act[2] + ' за ' + act[1] + ' тг.');
                        Ext.getCmp('act' + id).setFieldStyle("color:red;");
                    }

                    // район города
                    Ext.Ajax.request({
                        method: "POST",
                        url: '/handlers/get_city_regions.php?type=getAll',
                        success: function (response, opts) {
                            response = Ext.decode(response.responseText);

                            if (!response.success) {
                                return false;
                            }

                            aAllCities.push({
                                zip: "000000",
                                city: "Почта"
                            });

                            Ext.each(response.data, function (val, id) {
                                if (val.children.length > 0) {
                                    Ext.each(val.children, function (cval, cid) {
                                        aAllRegions.push({
                                            zip: cval.zip,
                                            region: cval.region
                                        });
                                    });
                                }

                                aAllCities.push({
                                    zip: val.zip.trim(),
                                    city: val.city
                                });

                            });

                            Ext.data.StoreManager.lookup('DeliveryCityStore')
                                    .add(aAllCities);
                        },
                        failure: function (response, opts) {
                        }
                    });

                    // свойства товара
                    var properties = action.result.data.offer_properties;

                    if (action.result.data.cert == true) {
                        Ext.getCmp('cert' + id).setVisible(true);
                    }

                    if (action.result.data.status === 'Подтвержден') {
                        Ext.getCmp('status' + id).setDisabled(true);
                    }

                    if (properties && Object.keys(properties).length > 0) {
                        var container = Ext.getCmp('offer-property-fields' + id);

                        Ext.Object.each(properties, function (type, property) {
                            var options = [];
                            Ext.Array.each(property, function (val, key) {
                                if (action.result.data.country === val.country) {
                                    options.push(val);
                                }
                            });
                            var value = '';
                            if (typeof action.result.data.offer_property[type] !== "undefined") {
                                value = action.result.data.offer_property[type];
                            }
                            var tpl = myProperty.create("offer_property", type, id, options, value);
                            container.add(tpl);
                        });

                        // show properties for main product
                        if (action.result.data.status === 'Подтвержден' || action.result.data.status === 'Предварительно подтвержден') {
                            container.setDisabled(false);
                            container.show();
                        } else {
                            container.setDisabled(true);
                            container.hide();
                        }
                    }

                    // доп. товар
                    var dop_tovar = action.result.data.dop_tovar;

                    if (dop_tovar && Object.keys(dop_tovar).length > 0) {
                        // add panels
                        Ext.Array.each(dop_tovar, function (item, index) {
                            myProperty.panel(item);
                        });
                    }

                    // other
                    if (Ext.getCmp('dcity' + id)) {
                        var gor = Ext.getCmp('dcity' + id).getValue();
                    }

                    if (Ext.getCmp('dcity' + id)) {
                        Ext.getCmp('dcity' + id).setValue('');
                        Ext.getCmp('dcity' + id).emptyText = gor;
                        Ext.getCmp('dcity' + id).applyEmptyText();
                    }

                    if (action.result.data.is_get == '1') {
                        Ext.getCmp('windAnket_' + id).close();
                        alert("Заявка занята другим оператором");
                        return false;
                    }


                    // START call_me
                    var callMeEl = Ext.getCmp('call_me' + id);
                    var tmp_phone = action.result.data.phone;
                    var tmp2_phone = action.result.data.phone_sms;

                    if (country === 'kzg') {
                        Ext.getCmp('phone' + id).setVisible(false);
                        if (tmp_phone.length === 10) {
                            var tmp_phone = '+996' + tmp_phone.substr(1);
                        } else if (tmp_phone.length === 11) {
                            var tmp_phone = '+996' + tmp_phone.substr(2);
                        } else if (tmp_phone.length === 12) {
                            var tmp_phone = '+996' + tmp_phone.substr(3);
                        } else {
                            var tmp_phone = '+996' + tmp_phone.substr(4);
                        }

                        var prefix = '25*';
                        if (session_operator_bishkek) {
                            prefix = '33*';
                        }

                        callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:' + prefix + id + '">&#9990;</a>');
                    } else if (country === 'am') {
                        if (tmp_phone.length === 9) {
                            var tmp_phone = '+374' + tmp_phone.substr(1);
                        } else {
                            var tmp_phone = '+3' + tmp_phone;
                        }
                        callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:23*' + tmp_phone + '#' + id + '">&#9990;</a>');
                    } else if (country === 'az') {
                        callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:16*' + tmp_phone + '#' + id + '">&#9990;</a>');
                    } else if (country === 'ae') {
                        callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:77*+' + tmp_phone + '#' + id + '">&#9990;</a>');
                    } else if (country === 'uz') {
                        callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:47*+' + tmp_phone + '#' + id + '">&#9990;</a>');
                    } else if (tmp_phone) {
                        Ext.getCmp('phone' + id).setVisible(false);
                        var tmp_phone = '8' + tmp_phone.substr(1);
                        var tmp2_phone = '8' + tmp2_phone.substr(1);
                        if (tmp_phone !== tmp2_phone) {
                            var prefix = '5*';
                            if (session_operator_bishkek) {
                                prefix = '33*';
                            } else if (session_operatorrecovery) {
                                prefix = '5*';
                            }
                            callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:' + buildHash(prefix + id) + '">&#9990;</a><a style="font-size:40px;text-decoration:none;" href="sip:' + buildHash(prefix + id) + '">&#9990;</a>');
                        } else {
                            callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:' + buildHash(prefix + id) + '">&#9990;</a>');
                        }
                    }
                    ////////
                    if (session_whatsappoperator > 0) {
                        var currVal = callMeEl.getValue();
                        currVal += '&nbsp;<span style="font-size:40px;cursor:pointer;" href="javascript:void(0);" onClick="javascript:whatsAppChat(\'' + id + '\',\'' + action.result.data.phone_orig + '\');"><img src="/images/whatsapp_32x32.png"/></span>';
                        callMeEl.setValue(currVal);
                    }
                    // END call_me

                    ////////
                    if (action.result.data.oprosnik) {
                        var oprosnikForm = Ext.getCmp('OprosnikForm_' + id);
                        if (oprosnikForm) {
                            oprosnikForm.add(eval(action.result.data.oprosnik));
                        }
                    } else {
                        var oprosnikPanel = Ext.getCmp('OprosnikPanel_' + id);
                        if (oprosnikPanel) {
                            oprosnikPanel.toggleCollapse();
                        }
                    }

                    // Источник
                    var tmp_staff_id = parseInt(Ext.getCmp('staff_id' + id).getValue());
                    if (globalAllColdStaffArr.indexOf(tmp_staff_id) > -1 || country === 'uz') {
                        if (country === 'kz') {
//                            Ext.getCmp('offer' + id).bindStore(globalOffersStore.ValuesColdKzArr);
                            console.log('filter: offer_show_in_cold_kz');
                            globalOffersStore.filter('offer_show_in_cold_kz', '1');
                        } else if (country === 'uz') {
//                            Ext.getCmp('offer' + id).bindStore(globalOffersStore.ValuesColdUzArr);
                            console.log('filter: offer_show_in_cold_uz');
                            globalOffersStore.filter('offer_show_in_cold_uz', '1');
                        } else if (country === 'kzg') {
//                            Ext.getCmp('offer' + id).bindStore(globalOffersStore.ValuesColdKgzArr);
                            console.log('filter: offer_show_in_cold_kgz');
                            globalOffersStore.filter('offer_show_in_cold_kgz', '1');
                        }
                    }

                    var st_name = globalPartnersStore.ValuesJson[tmp_staff_id] ? globalPartnersStore.ValuesJson[tmp_staff_id] : tmp_staff_id;
                    Ext.getCmp('display_staff_id' + id).setValue(st_name + ' - ' + action.result.data.web_id);

                    var offerVal = Ext.getCmp('offer' + id).getValue();

                    // Title
                    var twin = Ext.getCmp('windAnket_' + id);
                    var titleOffer = str_replace('-', '_', offerVal);
                    if (offer_name[titleOffer]) {
                        twin.setTitle(twin.title + ' - ' + offer_name[titleOffer]);
                    }

                    var nc = 0;
                    var rc = 0;

                    if (action.result.data.status === 'Недозвон') {
                        nc = 1;
                    }
                    if (action.result.data.status === 'Перезвонить') {
                        rc = 1;
                    }

                    Ext.Ajax.request({
                        method: "GET",
                        url: '/handlers/get_projectInfo.php?nocall=' + nc + '&recall=' + rc + '&project=' + country + '&inf=1&offer=' + offerVal,
                        success: function (response) {
                            offer_data = response.responseText;
                            Ext.getCmp('west-panel' + id).update(offer_data);
                            Ext.getCmp('west-panel' + id).autoScroll = true;
                            Ext.getCmp('west-panel' + id).doLayout();
                            if (offer_data.length > 30) {
                                Ext.getCmp('west-panel' + id).expand();
                            }
                        }
                    });

                    var usePrices = [];
                    var evalPriceStr = action.result.data.offer + '_' + country;
                    if (action.result.data.offer !== '0') {
                        eval('if (typeof(' + evalPriceStr + ') !== "undefined") usePrices = ' + evalPriceStr + ';');
                    }
                    if (globalAllColdStaffArr.indexOf(action.result.data.offer.staff_id) > -1) {
                        eval('if (typeof(' + evalPriceStr + '_cold) !== "undefined") usePrices = ' + evalPriceStr + '_cold;');
                    }

                    for (var count = 5; count >= 1; count--) {
                        var price = 0;
                        var earning = 0;
                        if (usePrices[count]) {
                            price = usePrices[count];
                            if (count === 1) {
                                earning = parseInt(price * 0.007);
                            }
                            if (count === 2) {
                                earning = parseInt(price * 0.028);
                            }
                            if (count >= 3) {
                                earning = parseInt(price * 0.036);
                            }
                            if (count === 4) {
                                earning = parseInt(price * 0.036);
                            }
                            if (count === 5) {
                                earning = parseInt(price * 0.036);
                            }
                        }
                        if (xoxol > 0) {
                            earning = parseInt(earning * 0.65);
                        }
                        Ext.data.StoreManager.lookup('potentialEarningsStore').add({
                            'count': count,
                            'price': price,
                            'earnings': earning
                        });
                    }

                    calculateTicketsCount(id, true);
                },
                failure: function (response, opts) {
                    Ext.getCmp('windAnket_' + id).close();
                    alert("Заявка занята другим оператором");
                }
            });
        } else {
            Ext.getCmp('MenuForm_' + id).getForm().load({
                success: function (form, action) {
                    // 
                    Ext.getCmp('status_kz' + id).allowBlank = true;
                    Ext.getCmp('status_kz' + id).validate();
                    if (Object.keys(action.result.data.offers_with_properies).length > 0) {
                        offers_with_properies = action.result.data.offers_with_properies;

                        Ext.Object.each(offers_with_properies, function (offer, offer_data) {
                            var offer = Ext.create('OffersModel', {
                                name: offer_data.name,
                                value: offer_data.title
                            });

                            // fill offers
                            Ext.data.StoreManager.lookup('OffersStore').add(offer);
                        });
                    }
                }
            });
        }
    }
}

function checkStatusKz(id) {

//    var offerVal = Ext.getCmp('offer' + id).getValue();
//    var cityVal = Ext.getCmp('dcity' + id).getValue();

//    var freshStatusKzArr = [
//        '010000', // Астана 
//        '020400', // Атбасар
//        '021500', // Степногорск
//        '110300', // Аркалык
//        '050000', // Алма-Ата 
//        '040900', // Каскелен
//        '040600', // Узынагаш
//        '041600', // Талгар
//        '040800', // Капчагай / Капшагай
//        '040400', // Есик 
//        '040928', // Шамалган село
//        '040930' // Шамалган станция
//    ];
//
//    if (offerVal === 'original_parfume' && cityVal && freshStatusKzArr.indexOf(cityVal) < 0) {
//        // Костыль по задаче 
//        // https://www.wunderlist.com/webapp#/tasks/4857242648/title/focus
//        Ext.getCmp('status_kz' + id).bindStore(['Свежий']);
//        Ext.getCmp('status_kz' + id).setValue('');
//    } else {
//        Ext.getCmp('status_kz' + id).bindStore(status_kz);
//        Ext.getCmp('status_kz' + id).setValue('');
//    }

    return true;


}


function str_replace(search, replace, subject) {	// Replace all occurrences of the search string with the replacement string
    // 
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Gabriel Paderni

    if (!(replace instanceof Array)) {
        replace = new Array(replace);
        if (search instanceof Array) {//If search	is an array and replace	is a string, then this replacement string is used for every value of search
            while (search.length > replace.length) {
                replace[replace.length] = replace[0];
            }
        }
    }

    if (!(search instanceof Array))
        search = new Array(search);
    while (search.length > replace.length) {//If replace	has fewer values than search , then an empty string is used for the rest of replacement values
        replace[replace.length] = '';
    }

    if (subject instanceof Array) {//If subject is an array, then the search and replace is performed with every entry of subject , and the return value is an array as well.
        for (k in subject) {
            subject[k] = str_replace(search, replace, subject[k]);
        }
        return subject;
    }

    for (var k = 0; k < search.length; k++) {
        if (subject !== undefined) {
            var i = subject.indexOf(search[k]);
            while (i > -1) {
                subject = subject.replace(search[k], replace[k]);
                i = subject.indexOf(search[k], i);
            }
        }
    }

    return subject;

}
