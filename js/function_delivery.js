
/**
 * Анкета "доставка" (логистика)
 * @param {Object} cdrData
 * @param {type} country
 * @param {type} is_status
 * @param {type} queue
 * @param {type} new_sim
 * @param {type} closed_level
 * @returns {undefined}
 */
function CreateMenuDelivery(cdrData, country, is_status, queue, new_sim, closed_level) {

    var predOplataCities = [
        'Талдыкорган курьер',
        'Павлодар курьер',
        'Кокшетау курьер',
        'Костанай курьер',
        'Кульсары курьер',
        'Жетысай курьер',
        'Петропавловск курьер',
        'Жезказган курьер',
        'Сатпаев курьер',
        'Темиртау курьер',
        'Балхаш курьер',
        'Экибастуз курьер',
        'Шиели курьер',
        'Кентау курьер',
        'Жаркен курьер',
        'Аксай курьер'
    ];

    globalOffersStore.clearFilter(true);

    var id = cdrData.order_id;

    console.log('Анкета "Доставка" (логистика): id=' + id + ', country=' + country + ', is_status=' + is_status + ', queue=' + queue + ', new_sim=' + new_sim + ', closed_level=' + closed_level);

    var callPrefix, offerLocked = true;
    console.log('offerLocked: ' + offerLocked);

    if (closed_level === 11113333) {
        closed_level = 0;
        offerLocked = false;
    }
    console.log('offerLocked: ' + offerLocked);

    if (closed_level === 15) {
        // Холоднвые заказы
        if (country === 'kzg') {
            var callPrefix = '25*';
            if (session_operator_bishkek) {
                callPrefix = '33*';
            }
        } else {
            callPrefix = session_operatorrecovery ? '5*' : '5*';
        }
    } else if (closed_level === 13) {
        // Холоднвые заказы NEW
        if (country === 'kzg') {
            var callPrefix = '25*';
            if (session_operator_bishkek) {
                callPrefix = '33*';
            }
        } else {
            callPrefix = session_operatorrecovery ? '5*' : '5*';
        }
    } else if (country === 'kzg') {
        var callPrefix = '25*';
        if (session_operator_bishkek) {
            callPrefix = '33*';
        }
    } else {
        callPrefix = session_operatorrecovery ? '5*' : '5*';
    }

    if (is_status) {
        var st_store = ['Подтвержден', 'Предварительно подтвержден', 'Перезвонить'];
    } else {
        var st_store = ['Подтвержден', 'Предварительно подтвержден', 'Перезвонить', 'Отменён', 'Брак'];
    }

    var wind = Ext.getCmp('windAnket_' + id);
    if (!wind) {
        // All cities list
        var aAllCities = [];

        // All regions list
        var aAllRegions = [];

        // Cities
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
                if (typeof this.labels[key] !== 'undefined') {
                    return this.labels[key];
                }
                return this.labels['attribute'];
            },
            create: function (name, type, id, options, value) {
                console.log('create myProperty => name: ' + name + ', type: ' + type + ', id: ' + id + ', options: ' + options + ', value: ' + value);
                switch (type) {

                    case 'name':
                        var tpl = {
                            xtype: 'textfield',
                            id: 'offer_property_' + type + id,
                            name: name + '[' + type + '][' + id + ']',
                            fieldLabel: this.getLabel(type),
                            allowBlank: true,
                            flex: 1
                        };

                        if (typeof value !== 'undefined') {
                            tpl.value = value;
                        }
                        break;

                    case 'description':
                        var tpl = {
                            xtype: 'textfield',
                            id: 'offer_property_' + type + id,
                            name: name + '[' + type + '][' + id + ']',
                            fieldLabel: this.getLabel(type),
                            allowBlank: true,
                            flex: 1
                        };

                        if (typeof value !== 'undefined') {
                            tpl.value = value;
                        }
                        break;

                    case 'gift_price':
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
                            }
                        ];
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

                        if (typeof value !== 'undefined') {
                            tpl.value = value;
                            tpl.rawValue = value;
                        }
                        break;
                }

                if (typeof tpl === 'undefined') {
                    return;
                }

                return tpl;
            },
            panel: function (attributes) {
                if (typeof attributes === 'undefined') {
                    var attributes = {
                        offer: '',
                        count: 0,
                        price: 0
                    };
                }

                if (typeof attributes.offer === 'undefined') {
                    attributes.offer = '';
                }

                if (typeof attributes.count === 'undefined') {
                    attributes.count = 0;
                }

                if (typeof attributes.price === 'undefined') {
                    attributes.price = 0;
                }

                // generate unique ID
                var fieldID = Ext.id();

                // Источник
                var tmp_staff_id = parseInt(Ext.getCmp('staff_id' + id).getValue());
//                var dopOfferStore = Ext.data.StoreManager.lookup('OffersStore');
                if (globalAllColdStaffArr.indexOf(tmp_staff_id) > -1) {
                    if (country === 'kz') {
                        console.log('filter: offer_show_in_cold_kz');
                        globalOffersStore.filter('offer_show_in_cold_kz', '1');
//                        dopOfferStore = globalOffersStore.ValuesColdKzArr;
                    } else if (country === 'kzg') {
//                        console.log('filter: offer_show_in_cold_kgz');
//                        globalOffersStore.filter('offer_show_in_cold_kgz', '1');
//                        dopOfferStore = globalOffersStore.ValuesColdKgzArr;
                    }
                }

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
                                    fields: ['id', 'value', 'price'],
                                    fieldLabel: 'Товар',
                                    allowBlank: false,
                                    editable: false,
                                    typeAhead: true,
                                    typeAheadDelay: 100,
                                    queryMode: 'local',
                                    id: 'dop_tovar_offer_' + fieldID,
                                    name: 'dop_tovar[offer][' + fieldID + ']',
                                    store: globalOffersStore,
                                    valueField: 'value',
                                    displayField: 'name',
                                    flex: 2,
                                    listConfig: {
                                        loadingText: 'Поиск...',
                                        emptyText: 'Оффер не найден'
                                    },
                                    listeners: {
                                        change: function (el, newValue) {
                                            var propertiesContainer = Ext.getCmp('offer-properties' + fieldID);
                                            propertiesContainer.removeAll();

                                            Ext.Object.each(offers_with_properies, function (offer, offer_data) {
                                                if (newValue === offer) {
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

                                                        if (typeof attributes[type] !== 'undefined') {
                                                            value = attributes[type];
                                                        }

                                                        var tpl = myProperty.create("dop_tovar", type, fieldID, options, value);

                                                        propertiesContainer.add(tpl);
                                                    });

                                                    if (typeof attributes.gift !== 'undefined') {
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

                                            var prev_price = this.ownerCt.items.items[2].getValue();

                                            var prod = str_replace('-', '_', this.ownerCt.items.items[0].getValue());

                                            if (eval('if (' + prod + '_' + country + ') ' + prod + '_' + country + '[' + col + ']') === 'undefined') {
                                                var razn = col - 5;
                                                pprice = eval(prod + '_' + country + '[5]') + razn * eval(prod + '_' + country + '[0]') * 0.7;
                                            } else
                                                var pprice = eval(prod + '_' + country + '[' + col + ']') * 0.7;
                                            var tprice = Ext.getCmp('price' + id).getValue();

                                            if (col) {
                                                this.ownerCt.items.items[2].setValue(pprice);
                                                var sprice = tprice + pprice - prev_price;
                                            } else {
                                                this.ownerCt.items.items[2].setValue('0');
                                                var sprice = tprice - prev_price;
                                            }
                                            Ext.getCmp('price' + id).setValue(sprice.toString());

                                            calculateTicketsCount(id, [13, 15].indexOf(closed_level) > -1);
                                        }
                                    }
                                }, {
                                    xtype: 'textfield',
                                    id: 'dop_tovar_price_' + fieldID,
                                    itemId: 'dop_tovar_price',
                                    name: 'dop_tovar[price][' + fieldID + ']',
                                    editable: false,
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

        var delivery_store = [];
        if (country.toLowerCase() === 'kz') {
            delivery_store = Ext.data.StoreManager.lookup('DeliveryCityStore');
        } else if (country.toLowerCase() === 'ru') {
            delivery_store = cur_ru;
        } else if (country === 'am') {
            delivery_store = cur_am;
        } else if (country === 'az') {
            delivery_store = cur_az;
        } else if (country === 'md') {
            delivery_store = cur_md;
        } else if (country === 'ae') {
            delivery_store = cur_ae;
        } else if (country === 'uz') {
            delivery_store = cur_uz;
        } else {
            delivery_store = cur_kg;
        }

        var dateDelivery = new Date();

        dateDelivery.setDate(dateDelivery.getDate() - 1);

        var formFieldsItems = [
            {
                xtype: 'hidden',
                id: 'have_orders' + id,
                name: 'have_orders'
            }, {
                xtype: 'hidden',
                value: cdrData.cdr_call ? cdrData.cdr_call : '',
                id: 'cdr_call' + id,
                name: 'cdr_call'
            }, {
                xtype: 'hidden',
                id: 'payed_curr_year_count' + id,
                name: 'payed_curr_year_count'
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
                fieldLabel: 'Вызов',
                id: 'call_me' + id,
                name: 'call_me'
            }, {
                layout: 'column',
                border: false,
                defaults: {
                    columnWidth: 0.5,
                    anchor: '100%',
                    margin: 3
                },
                items: [{
                        xtype: 'displayfield',
                        fieldLabel: 'Дата создания заказа',
                        id: 'date' + id,
                        name: 'date'
                    }, {
                        xtype: 'displayfield',
                        fieldLabel: 'Дата доставки1',
                        id: 'date_delivery_first' + id,
                        name: 'date_delivery_first',
                        renderer: Ext.util.Format.dateRenderer('Y-m-d')
                    }
                ]
            }, {
                xtype: 'linkfield',
                fieldLabel: 'Ленд',
                id: 'site' + id,
                name: 'site'
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Номер чека',
                id: 'check_number' + id,
                hideTrigger: true,
                name: 'check_number'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'ID',
                name: 'id'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'ID клиента',
                id: 'uuid' + id,
                name: 'uuid'
            }, {
                xtype: 'hidden',
                name: 'uuid_orig',
                id: 'uuid_orig' + id
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Группа клиента',
                id: 'client_group' + id,
                name: 'client_group',
                labelStyle: 'font-weight: bold; color: black;',
                fieldStyle: 'font-weight: bold; color: darkgreen;'
            }, {
                xtype: 'displayfield',
                hidden: session_operatorcold > 0,
                fieldLabel: 'Всего звонков',
                name: 'rings'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Источник',
                name: 'staff_id',
                id: 'staff_id' + id,
                hiddenName: 'staff_id'
            }, {
                xtype: 'textfield',
                fieldLabel: '№ Телефона',
                id: 'phone' + id,
                allowBlank: false,
                name: 'phone'
            }, {
                xtype: 'textfield',
                id: 'fio' + id,
                fieldLabel: 'ФИО',
                name: 'fio'
            }, {
                xtype: 'displayfield',
                name: 'common_interested_category_display',
                id: 'common_interested_category_display' + id,
                fieldLabel: 'Категория',
                hidden: true,
                labelStyle: 'font-weight: bold; color: black;',
                fieldStyle: 'font-weight: bold; color: darkgreen;'
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Цена',
                id: 'price' + id,
                editable: !is_status,
                hideTrigger: true,
                name: 'price',
                enableKeyEvents: true,
                listeners: {
                    'keyup': function () {
                        calcTotalPrice(id, true);
                    }
                }
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Цена частичной предоплаты',
                id: 'post_part_price' + id,
                editable: !is_status,
                hideTrigger: true,
                name: 'post_part_price'
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Цена предоплаты',
                id: 'post_price' + id,
                editable: false,
//                disabled: true,
                hideTrigger: true,
                name: 'post_price',
                enableKeyEvents: true,
                listeners: {
                    'keyup': function () {
                        calcTotalPrice(id, false);
                    }
                }
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Итоговая цена',
                id: 'total_price' + id,
                editable: true,
                hideTrigger: true,
                name: 'total_price'
            }, {
                xtype: 'displayfield',
                name: 'order_history',
                fieldLabel: 'История сохранений'
            }, {
                xtype: 'textfield',
                fieldLabel: 'Индекс',
                name: 'index'
            }, {
                xtype: 'textfield',
                fieldLabel: 'Адрес',
                allowBlank: false,
                name: 'addr'
            }, {
                xtype: 'textfield',
                fieldLabel: 'Район',
                name: 'district'
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
                        var uuid = Ext.getCmp('uuid_orig' + id).getValue();
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
                queryMode: 'local',
                editable: false,
                disabled: offerLocked,
                allowBlank: false,
                id: 'offer' + id,
                name: 'offer',
                valueField: 'value',
                displayField: 'name',
                listeners: {
                    change: function (el, newValue, oldValue) {
                        if (parseInt(id) === 0) {
                            Ext.Ajax.request({
                                url: '/handlers/get_OfferProperties.php',
                                method: 'GET',
                                params: {
                                    offer: newValue
                                },
                                scope: this,
                                timeout: 5000,
                                success: function (response, opts) {
                                    // свойства товара
                                    var container = Ext.getCmp('offer-property-fields' + id);
                                    container.removeAll();

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

                                        var tpl = myProperty.create("offer_property", type, id, options);
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
                xtype: 'numberfield',
                fieldLabel: 'К-во',
                id: 'package' + id,
                editable: false,
                maxValue: 100,
                enableKeyEvents: true,
                name: 'package',
                listeners: {
                    change: function () {
                        calculateTicketsCount(id, [13, 15].indexOf(closed_level) > -1);
                    }
                }
            }, {
                xtype: 'displayfield',
                name: 'otv',
                id: 'otv' + id,
                fieldLabel: 'Ответственный'
            }, {
                xtype: 'combo',
                name: 'kz_operator',
                fieldLabel: 'Оператор логист',
                editable: false,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                id: 'kz_operator' + id,
                allowBlank: true,
                store: globalOperatorLogistStore,
                valueField: 'id',
                displayField: 'value'
            }
        ];

        if ((session_postlogist + session_adminlogistpost + session_logistprepayment) > 0) {
            formFieldsItems.push({
                xtype: 'combo',
                name: 'courier_group',
                fieldLabel: 'Оператор предоплаты',
                disabled: [94448321].indexOf(globalStaffId) > -1,
                editable: false,
                forceSelection: true,
                triggerAction: 'all',
                queryMode: 'local',
                id: 'courier_group' + id,
                allowBlank: false,
                store: globalCourierGroupStore,
                valueField: 'id',
                displayField: 'value'
            });
        }

        formFieldsItems.push({
            xtype: 'combo',
            fieldLabel: 'Админ логист',
            editable: false,
            forceSelection: true,
            triggerAction: 'all',
            queryMode: 'local',
            name: 'kz_admin',
            hiddenName: 'kz_admin',
            id: 'kz_admin' + id,
            allowBlank: true,
            store: globalKzAdminStore,
            valueField: 'id',
            displayField: 'value'
        }, {
            xtype: 'combo',
            editable: false,
            forceSelection: true,
            triggerAction: 'all',
            queryMode: 'local',
            name: 'status',
            disabled: true,
            id: 'status' + id,
            hidden: is_status && session_adminsales < 1,
            store: st_store,
            fieldLabel: 'Статус',
            valueField: 'value',
            displayField: 'value',
            listeners: {
                change: function (el, newValue, oldValue) {
                    switch (newValue) {
                        // выбор статуса "Подтвержден"
                        case 'Подтвержден':
                        case 'Предварительно подтвержден':
                            // kz_delivery
                            var kz_delivery = Ext.getCmp('kz_delivery' + id);
                            kz_delivery.allowBlank = false;
                            kz_delivery.validate();

                            // show properties for main product
                            var container = Ext.getCmp('offer-property-fields' + id);
                            container.setDisabled(false);
                            container.show();
                            break;
                            // остальные варианты
                        case 'Отменён':
                            var item = Ext.getCmp('description' + id);
                            item.setEditable(false);
                            item.bindStore(globalCancelTypesStore.ValuesArr);
                            item.reset();
                            item.allowBlank = false;
                            item.validate();
                            // kz_delivery
                            var kz_delivery = Ext.getCmp('kz_delivery' + id);
                            kz_delivery.allowBlank = true;
                            kz_delivery.validate();

                            // hide properties for main product
                            var container = Ext.getCmp('offer-property-fields' + id);
                            container.setDisabled(true);
                            container.hide();
                            break;
                        default:
                            // kz_delivery
                            var kz_delivery = Ext.getCmp('kz_delivery' + id);
                            kz_delivery.allowBlank = true;
                            kz_delivery.validate();

                            // hide properties for main product
                            var container = Ext.getCmp('offer-property-fields' + id);
                            container.setDisabled(true);
                            container.hide();
                            break;
                    }
                }
            }
        }, {
            xtype: 'combo',
            editable: false,
            forceSelection: true,
            triggerAction: 'all',
            queryMode: 'local',
            name: 'send_status',
            id: 'send_status' + id,
            hidden: is_status || (session_postlogist + session_logist) > 0,
            disabled: [11111111, 88189675, 25937686, 63077972, 70623931, 63279961, 51651814, 12019085, 64395288].indexOf(globalStaffId) < 0,
            store: send_status_no_send,
            fieldLabel: 'Статус отправки',
            valueField: 'value',
            displayField: 'value',
            listeners: {
                change: function (el, newValue, oldValue) {
                    if (newValue === 'Отказ') {
                        setDeliveryVisible(true, id, globalOtkazTypesStore);
                    } else if (oldValue === 'Отказ') {
                        setDeliveryVisible(false, id);
                    }
                    calcTotalPrice(id, true);
                }
            }
        }, {
            xtype: 'combo',
            fieldLabel: 'Статус посылки',
            editable: false,
            forceSelection: true,
            triggerAction: 'all',
            queryMode: 'local',
            name: 'status_kz',
            id: 'status_kz' + id,
            store: globalStatusKzStore,
            valueField: 'value',
            displayField: 'value',
            listeners: {
                change: function (el, newValue, oldValue) {
                    var dateDeliveryEl = Ext.getCmp('date_delivery' + id);
                    var deferredDateEl = Ext.getCmp('deferred_date' + id);
                    var predoplataDateEl = Ext.getCmp('datetime_otl' + id);

                    if (newValue === 'Отказ') {
                        setDeliveryVisible(true, id, globalStatusKzOtkazStore);
                    } else if (oldValue === 'Отказ') {
                        setDeliveryVisible(false, id);
                    }

                    switch (newValue) {
                        case 'Груз в дороге':
                            if (dateDeliveryEl) {
                                dateDeliveryEl.allowBlank = true;
                                dateDeliveryEl.validate();
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
                                maxValue: new Date(new Date().getTime() + (1000 * 60 * 60 * 290)).format('Y-m-d'),
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
                        case 'Скинут предоплату':
                            var new_field = {
                                xtype: 'fieldset',
                                fieldset: true,
                                title: 'Скинут предоплату: дата - время',
                                collapsed: false,
                                autoScroll: true,
                                id: 'datetime_otl' + id,
                                defaults: {
                                    anchor: '100%',
                                    allowBlank: true
                                },
                                items: [
                                    {
                                        xtype: 'datefield',
                                        fieldLabel: 'Дата',
                                        name: 'date_otl',
                                        minValue: new Date().format('Y-m-d'),
                                        startDay: 1,
                                        format: 'Y-m-d'
                                    }, {
                                        xtype: 'timefield',
                                        fieldLabel: 'Время',
                                        name: 'time_otl',
                                        format: 'H:i:s'
                                    }
                                ]
                            };

                            var formed = Ext.getCmp('MenuForm_' + id);
                            for (var i = 0; i < formed.items.items.length; i++) {
                                if (formed.items.items[i].id === 'status_kz' + id) {
                                    formed.insert(i + 1, new_field);
                                }
                            }
                            break;
                        case 'На доставку':
                        case 'Вручить подарок':
                            checkWorkDelivDays(id);
                            break;
                        case 'Заберет':
                            if (dateDeliveryEl) {
                                dateDeliveryEl.allowBlank = true;
                                dateDeliveryEl.validate();
                            }

                            var new_field = Ext.create('Ext.form.field.Date', {
                                name: 'take_away_date',
                                anchor: '100%',
                                labelWidth: 150,
                                id: 'take_away_date' + id,
                                format: 'Y-m-d H:i:s',
                                minValue: new Date().format('Y-m-d'),
                                allowBlank: false,
                                fieldLabel: 'Дата заберет'
                            });
                            var formed = Ext.getCmp('MenuForm_' + id);
                            for (var i = 0; i < formed.items.items.length; i++) {
                                if (formed.items.items[i].id === 'status_kz' + id) {
                                    formed.insert(i + 1, new_field);
                                }
                            }
                            break;
                        default:
                            if (dateDeliveryEl) {
                                dateDeliveryEl.allowBlank = true;
                                dateDeliveryEl.validate();
                            }
                            if (deferredDateEl) {
                                deferredDateEl.hide();
                            }
                            if (predoplataDateEl) {
                                predoplataDateEl.hide();
                            }
                            break;
                    }
                }
            }
        }, {
            xtype: 'checkboxfield',
            id: 'pay_type' + id,
            name: 'pay_type',
            fieldLabel: 'Рассрочка?',
            margins: '0',
            inputValue: 1
        }, {
            xtype: 'checkboxfield',
            id: 'pay_status' + id,
            name: 'pay_status',
            fieldLabel: 'Оплачен?',
            margins: '0',
            inputValue: 1
        }, {
            xtype: 'datefield',
            id: 'date_delivery' + id,
            name: 'date_delivery',
            fieldLabel: 'Дата доставки',
            startDay: 1,
            format: 'Y-m-d',
            allowBlank: true,
            minValue: Ext.Date.add(new Date(), Ext.Date.DAY, (session_operatorcold + session_operatorrecovery + session_logist) > 0 && !session_admin ? 0 : -30)
        }, {
            xtype: 'displayfield',
            id: 'predOplataCitiesField' + id,
            fieldLabel: ' ',
            labelStyle: 'font-weight: bold; color: black;',
            fieldStyle: 'font-weight: bold; color: darkgreen;'
        }, {
            xtype: 'combo',
            id: 'kz_delivery' + id,
            fieldLabel: 'Тип доставки',
            name: 'kz_delivery',
            editable: false,
            allowBlank: false,
            disabled: session_adminsales,
            forceSelection: true,
            triggerAction: 'all',
            queryMode: 'local',
            store: delivery_store,
            displayField: 'city',
            valueField: 'city',
            listeners: {
                change: function (el, newValue) {
                    var aRegions = [];
                    if (newValue === 'Почта') {
                        Ext.getCmp('city_region' + id).allowBlank = true;
                    }
                    if (newValue === 'Москва') {
                        Ext.getCmp('city_region' + id).editable = true;
                    }
                    Ext.each(aAllCities, function (cval, cid) {
                        if (cval.city === newValue) {
                            Ext.each(aAllRegions, function (rval, rid) {
                                if (rval.zip.indexOf(cval.zip, 0) >= 0) {
                                    aRegions.push({
                                        zip: rval.zip,
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

                    checkWorkDelivDays(id);

                    Ext.getCmp('predOplataCitiesField' + id).setValue(predOplataCities.indexOf(newValue) > -1 ? 'Предоплатный город' : '');

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
            allowBlank: true,
            hidden: false,
            queryMode: 'local',
            store: Ext.data.StoreManager.lookup('DeliveryCityRegionStore'),
            valueField: 'region',
            displayField: 'region'
        }, {
            xtype: 'combo',
            queryMode: 'local',
            name: 'oper_use',
            id: 'oper_use' + id,
            store: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30'],
            fieldLabel: 'В работе у',
            valueField: 'value',
            displayField: 'value'
        }, {
            xtype: 'combo',
            editable: false,
            forceSelection: true,
            triggerAction: 'all',
            queryMode: 'local',
            name: 'status_cur',
            id: 'status_cur' + id,
            store: globalStatusCurStore,
            fieldLabel: 'Статус курьера',
            valueField: 'id',
            displayField: 'value',
            tpl: [
                '<ul class="x-list-plain">',
                '<tpl for=".">',
                '<li role="option" class="x-boundlist-item {class}">{value}</li>',
                '</tpl>',
                '</ul>'
            ],
            listeners: {
                change: function (el, newValue, oldValue, eOpts) {
                    // Подстатус (коммент)
                    if (['ОТКАЗ', 'отказ проплатить'].indexOf(newValue) > -1) {
                        setDeliveryVisible(true, id, globalOtkazTypesStore);
                    } else if (['ОТКАЗ', 'отказ проплатить'].indexOf(oldValue) > -1) {
                        setDeliveryVisible(false, id);
                    }

                }
            }
        }, {
            xtype: 'combo',
            editable: false,
            forceSelection: true,
            triggerAction: 'all',
            name: 'status_check',
            id: 'status_check' + id,
            queryMode: 'local',
            store: Ext.create('Ext.data.Store', {
                storeId: 'StatusCheckStore',
                fields: ['id', 'value'],
                data: []
            }),
            fieldLabel: 'Статус проверки',
            valueField: 'id',
            displayField: 'value',
            listeners: {
                render: function (el, eOpts) {
                    var storeData = [];
                    storeData.push({
                        'id': '',
                        'value': '---'
                    });
                    Ext.each(status_check, function (value, index) {
                        storeData.push({
                            'id': value,
                            'value': value
                        });
                    });

                    Ext.data.StoreManager.lookup('StatusCheckStore').add(storeData);
                }
            }
        }, {
            xtype: 'textfield',
            name: 'deliv_desc',
            id: 'deliv_desc' + id,
            fieldLabel: 'Примечание доставки'
        }, {
            xtype: 'displayfield',
            id: 'kz_curier_sip' + id,
            fieldLabel: '',
            hideEmptyLabel: false
        }, {
            xtype: 'combo',
            fieldLabel: 'Курьер',
            editable: false,
            forceSelection: true,
            triggerAction: 'all',
            queryMode: 'local',
            name: 'kz_curier',
            id: 'kz_curier' + id,
            allowBlank: true,
            store: globalCouriersStore,
            valueField: 'id',
            displayField: 'name',
            listeners: {
                change: function (el, newVal, oldVal, eOpts) {
                    if (globalCouriersStore.ValuesJsonFull[newVal]) {
                        var sip = 0;
                        var sip = globalCouriersStore.ValuesJsonFull[newVal].sip;
                        var cphone = globalCouriersStore.ValuesJsonFull[newVal].phone;
                        Ext.getCmp('kz_curier_sip' + id).setValue('SIP: ' + sip + ' / ID: ' + id);
                        Ext.getCmp('MenuForm_' + id).setTitle('SIP: ' + sip + ' / Phone: <a href="sip:8*' + cphone + '#' + id + '">' + cphone + '</a> / ID: ' + id);
                    }
                }
            }
        }, {
            xtype: 'textfield',
            fieldLabel: 'Код Почта',
            editable: false,
            name: 'kz_code'
        }, {
            xtype: 'displayfield',
            fieldLabel: 'Проверить',
            id: 'kz_check' + id,
            name: 'kz_check'
        }, {
            xtype: 'hidden',
            value: '0',
            id: 'del' + id,
            name: 'del'
        }, {
            xtype: 'hidden',
            value: queue,
            id: 'queue' + id,
            name: 'queue'
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
                            document.getElementById('phone_sms_button' + id).href = 'sip:' + buildHash((country === 'kz' ? '56*' : (country === 'kzg' ? '57*' : callPrefix)) + id);
                        },
                        change: function () {
                            document.getElementById('phone_sms_button' + id).href = 'sip:' + buildHash((country === 'kz' ? '56*' : (country === 'kzg' ? '57*' : callPrefix)) + id);
                        }
                    }
                }, {
                    xtype: 'displayfield',
                    hideLabel: true,
                    value: '<a id="phone_sms_button' + id + '" style="font-size: 28px; line-height: 22px; margin: 0 0 0 5px;" href="javascript:void(0)">&#9990;</a>'
                }]
        }, {
            xtype: 'numberfield',
            name: 'recall',
            id: 'recall' + id,
            minValue: 1,
            //allowBlank: false,
            maxValue: 190,
            fieldLabel: 'Перезвонить через(часов)'
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
            editable: false,
            forceSelection: true,
            triggerAction: 'all',
            hidden: [13, 15].indexOf(closed_level) > -1,
            queryMode: 'local',
            name: 'control_status',
            id: 'control_status' + id,
            store: globalControlStatusStore,
            fieldLabel: 'Статус контроля',
            valueField: 'id',
            displayField: 'value'
        }, {
            xtype: 'combo',
            editable: false,
            forceSelection: true,
            triggerAction: 'all',
            queryMode: 'local',
            name: 'control_admin',
            id: 'control_admin' + id,
            store: globalControlAdminStore,
            fieldLabel: 'Админ контроля',
            valueField: 'value',
            displayField: 'value'
        }, {
            xtype: 'combo',
            editable: false,
            hidden: ['IncomeMoroz', 'IncomeTorgKZ', 'incometele2', 'Income_Kcell'].indexOf(queue) < 0 && session_incomeoper < 1,
            queryMode: 'local',
            name: 'status_income',
            fieldLabel: 'Статус входящей линии',
            allowBlank: ['IncomeMoroz', 'IncomeTorgKZ', 'incometele2', 'Income_Kcell'].indexOf(queue) < 0 && session_incomeoper > 0,
            store: globalStatusIncomeStore,
            valueField: 'id',
            displayField: 'value'
        }, {
            xtype: 'combo',
            editable: false,
            hidden: [15].indexOf(closed_level) < 0,
            queryMode: 'local',
            name: 'is_cold',
            id: 'is_cold' + id,
            fieldLabel: 'Холодный статус',
            allowBlank: [15].indexOf(closed_level) < 0,
            store: globalAnketColdStatusStore,
            valueField: 'id',
            displayField: 'value',
            listeners: {
                change: function (combo, newValue, oldValue, eOpts) {

                    if (typeof oldValue !== 'undefined' && oldValue !== newValue || newValue === '2') {
                        var params = {
                            plus: 'cold',
                            is_cold: newValue
                        };

                        if (Ext.getCmp('description' + id).getValue()) {
                            params.description = Ext.getCmp('description' + id).getValue();
                        }

                        console.log(newValue);

                        if (newValue === '2') {
                            var new_field = {
                                xtype: 'fieldset',
                                fieldset: true,
                                hidden: [13, 15].indexOf(closed_level) < 0,
                                title: globalColdStatusStore.ValuesJson[newValue] + ': дата - время',
                                collapsed: false,
                                autoScroll: true,
                                id: 'common_recall' + id,
                                defaults: {
                                    anchor: '100%',
                                    allowBlank: false
                                },
                                items: [
                                    {
                                        xtype: 'datefield',
                                        fieldLabel: 'Дата',
                                        name: 'recall_date',
                                        allowBlank: [13, 15].indexOf(closed_level) < 0,
                                        startDay: 1,
                                        format: 'Y-m-d',
                                        id: 'common_recall_date' + id,
                                        minValue: new Date(),
                                        listeners: {
                                            select: function (field, newVal, oldVal, eOpts) {
                                                if (Ext.getCmp('is_cold' + id).getValue() && field.getRawValue() && Ext.getCmp('common_recall_time' + id).getRawValue()) {
                                                    params.common_recall_date = field.getRawValue() + ' ' + Ext.getCmp('common_recall_time' + id).getRawValue();
                                                    setPlusStatus(id, country, params);
                                                }
                                            }
                                        }
                                    }, {
                                        xtype: 'timefield',
                                        fieldLabel: 'Время',
                                        allowBlank: [13, 15].indexOf(closed_level) < 0,
                                        name: 'recall_time',
                                        id: 'common_recall_time' + id,
                                        format: 'H:i:s',
                                        listeners: {
                                            select: function (field, value, eOpts) {
                                                if (Ext.getCmp('is_cold' + id).getValue() && Ext.getCmp('common_recall_date' + id).getRawValue() && field.getRawValue()) {
                                                    params.common_recall_date = Ext.getCmp('common_recall_date' + id).getRawValue() + ' ' + field.getRawValue();
                                                    setPlusStatus(id, country, params);
                                                }
                                            }
                                        }
                                    }
                                ]
                            };

                            var formed = Ext.getCmp('MenuForm_' + id);
                            for (var i = 0; i < formed.items.items.length; i++) {
                                if (formed.items.items[i].id === 'is_cold' + id) {
                                    formed.insert(i + 1, new_field);
                                }
                            }
                            destroyElArr(['common_cancel_type' + id]);
                        } else if (newValue === '4') {
                            var new_field = {
                                xtype: 'combo',
                                queryMode: 'local',
                                name: 'common_cancel_type',
                                id: 'common_cancel_type' + id,
                                store: globalColdOtkazReasonsStore,
                                fieldLabel: 'Причина не согласия',
                                valueField: 'value',
                                displayField: 'value',
                                listeners: {
                                    select: function (field, value, eOpts) {
                                        if (Ext.getCmp('is_cold' + id).getValue() && Ext.getCmp('common_cancel_type' + id).getRawValue() && field.getRawValue()) {
                                            params.common_cancel_type = Ext.getCmp('common_cancel_type' + id).getRawValue() + ' ' + field.getRawValue();
                                            setPlusStatus(id, country, params);
                                        }
                                    }
                                }
                            };

                            var formed = Ext.getCmp('MenuForm_' + id);
                            for (var i = 0; i < formed.items.items.length; i++) {
                                if (formed.items.items[i].id === 'is_cold' + id) {
                                    formed.insert(i + 1, new_field);
                                }
                            }
                            destroyElArr(['common_recall' + id]);
                        } else {
                            destroyElArr(['common_recall' + id, 'common_cancel_type' + id]);

                            if (newValue === '5' && Ext.getCmp('have_orders' + id).getValue() > 0) {
                                Ext.Msg.alert('Внимание!', 'Запрет на оформление Заказа!!!<br/>ЕСТЬ ДУБЛЬ!!!');

//                                Ext.Msg.confirm('Внимание', 'Вы уверены что хотите оформить дубль по заказу? <br/>Так как будет штраф, если и по товару будет дубль!!!?',
//                                        function (btn) {
//                                            if (btn === 'yes') {
//                                                setPlusStatus(id, country, params);
//                                            } else {
//                                                this.setValue(oldValue);
//                                            }
//                                        }
//                                );
                            } else {
                                setPlusStatus(id, country, params);
                            }

                        }
                    }
                }
            }
        }, {
            xtype: 'combo',
            editable: false,
            hidden: [13].indexOf(closed_level) < 0,
            queryMode: 'local',
            name: 'is_cold_new',
            id: 'is_cold_new' + id,
            fieldLabel: 'Холодный статус',
            allowBlank: [13].indexOf(closed_level) < 0,
            store: globalAnketColdStatusStore,
            valueField: 'id',
            displayField: 'value',
            listeners: {
                change: function (combo, newValue, oldValue, eOpts) {

                    if (typeof oldValue !== 'undefined' && oldValue !== newValue || newValue === '2') {
                        var params = {
                            plus: 'cold_new',
                            is_cold_new: newValue
                        };

                        if (Ext.getCmp('description' + id).getValue()) {
                            params.description = Ext.getCmp('description' + id).getValue();
                        }

                        console.log(newValue);

                        if (newValue === '2') {
                            var new_field = {
                                xtype: 'fieldset',
                                fieldset: true,
                                hidden: [13, 15].indexOf(closed_level) < 0,
                                title: globalColdStatusStore.ValuesJson[newValue] + ': дата - время',
                                collapsed: false,
                                autoScroll: true,
                                id: 'common_recall' + id,
                                defaults: {
                                    anchor: '100%',
                                    allowBlank: false
                                },
                                items: [
                                    {
                                        xtype: 'datefield',
                                        fieldLabel: 'Дата',
                                        name: 'recall_date',
                                        allowBlank: [13, 15].indexOf(closed_level) < 0,
                                        startDay: 1,
                                        format: 'Y-m-d',
                                        id: 'common_recall_date' + id,
                                        minValue: new Date(),
                                        listeners: {
                                            select: function (field, newVal, oldVal, eOpts) {
                                                if (Ext.getCmp('is_cold_new' + id).getValue() && field.getRawValue() && Ext.getCmp('common_recall_time' + id).getRawValue()) {
                                                    params.common_recall_date = field.getRawValue() + ' ' + Ext.getCmp('common_recall_time' + id).getRawValue();
                                                    setPlusStatus(id, country, params);
                                                }
                                            }
                                        }
                                    }, {
                                        xtype: 'timefield',
                                        fieldLabel: 'Время',
                                        allowBlank: [13, 15].indexOf(closed_level) < 0,
                                        name: 'recall_time',
                                        id: 'common_recall_time' + id,
                                        format: 'H:i:s',
                                        listeners: {
                                            select: function (field, value, eOpts) {
                                                if (Ext.getCmp('is_cold_new' + id).getValue() && Ext.getCmp('common_recall_date' + id).getRawValue() && field.getRawValue()) {
                                                    params.common_recall_date = Ext.getCmp('common_recall_date' + id).getRawValue() + ' ' + field.getRawValue();
                                                    setPlusStatus(id, country, params);
                                                }
                                            }
                                        }
                                    }
                                ]
                            };

                            var formed = Ext.getCmp('MenuForm_' + id);
                            for (var i = 0; i < formed.items.items.length; i++) {
                                if (formed.items.items[i].id === 'is_cold_new' + id) {
                                    formed.insert(i + 1, new_field);
                                }
                            }
                            destroyElArr(['common_cancel_type' + id]);
                        } else if (newValue === '4') {
                            var new_field = {
                                xtype: 'combo',
                                queryMode: 'local',
                                name: 'common_cancel_type',
                                id: 'common_cancel_type' + id,
                                store: globalColdOtkazReasonsStore,
                                fieldLabel: 'Причина не согласия',
                                valueField: 'value',
                                displayField: 'value',
                                listeners: {
                                    select: function (field, value, eOpts) {
                                        if (Ext.getCmp('is_cold_new' + id).getValue() && Ext.getCmp('common_cancel_type' + id).getRawValue() && field.getRawValue()) {
                                            params.common_cancel_type = Ext.getCmp('common_cancel_type' + id).getRawValue() + ' ' + field.getRawValue();
                                            setPlusStatus(id, country, params);
                                        }
                                    }
                                }
                            };

                            var formed = Ext.getCmp('MenuForm_' + id);
                            for (var i = 0; i < formed.items.items.length; i++) {
                                if (formed.items.items[i].id === 'is_cold_new' + id) {
                                    formed.insert(i + 1, new_field);
                                }
                            }
                            destroyElArr(['common_recall' + id]);
                        } else {
                            destroyElArr(['common_recall' + id, 'common_cancel_type' + id]);

                            if (newValue === '5' && Ext.getCmp('have_orders' + id).getValue() > 0) {
                                Ext.Msg.alert('Внимание!', 'Запрет на оформление Заказа!!!<br/>ЕСТЬ ДУБЛЬ!!!');

//                                Ext.Msg.confirm('Внимание', 'Вы уверены что хотите оформить дубль по заказу? <br/>Так как будет штраф, если и по товару будет дубль!!!?',
//                                        function (btn) {
//                                            if (btn === 'yes') {
//                                                setPlusStatus(id, country, params);
//                                            } else {
//                                                this.setValue(oldValue);
//                                            }
//                                        }
//                                );
                            } else {
                                setPlusStatus(id, country, params);
                            }

                        }
                    }
                }
            }
        });

        // Window
        Ext.create('Ext.Window', {
            title: 'ID заказа - ' + id,
            id: 'windAnket_' + id,
            modal: true,
            maximized: true,
//            closable: (session_operatorcold + session_adminsales) < 1 || session_admin > 0,
//            closable: [13, 15].indexOf(closed_level) < 0,
            height: 520,
            width: 850,
            layout: 'border',
            listeners: {
                close: function () {
                    globalStatusKzStore.clearFilter(true);

                    Ext.Ajax.request({
                        url: '/handlers/clear_get.php?id=' + id,
                        success: function (response, opts) {
                        },
                        failure: function (response, opts) {
                        }
                    });
                }
            },
            items: [
                {
                    region: 'north',
                    id: 'reload' + id,
                    disabled: true,
                    tbar: Ext.create('Ext.toolbar.Toolbar', {
                        cls: 'crm-main-top-header',
                        items: [
                            {
                                xtype: 'button',
                                text: 'Открыть подтверждение',
                                hidden: closed_level > 3 || true,
                                handler: function () {
                                    if (country === 'kz' || country === 'KZ' || country === 'ae') {
                                        Ext.getCmp('windAnket_' + id).close();
                                        CreateMenuObzvon(id, country.toLowerCase(), closed_level);
                                    }
                                }
                            }, '->', {
                                xtype: 'displayfield',
                                name: 'oper',
                                id: 'opera' + id,
                                style: {
                                    color: 'red'
                                },
                                fieldLabel: 'Оператор'
                            }
                        ]
                    })
                }, {
                    xtype: 'panel',
                    region: 'center',
                    width: 620,
                    autoScroll: true,
                    fbar: [
                        {
                            xtype: 'button',
                            id: 'add_button_dop' + id,
                            text: 'Доп. товар',
                            hidden: closed_level >= 3,
                            handler: function () {
                                myProperty.panel();
                            }
                        }, {
                            xtype: 'button',
                            text: 'Проверить товар на складе',
                            hidden: closed_level >= 5,
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
                        }, {
                            xtype: 'button',
                            text: 'Новый',
                            icon: '/images/plus-circle-green.png',
                            hidden: closed_level > 3 || true,
                            handler: function (button) {
                                var params = {
                                    staff_id: 11112222
                                };
                                createNewOrder(id, country, params);
                            }
                        }, {
                            xtype: 'button',
                            text: 'Продажа курьер',
                            icon: '/images/plus-circle-blue.png',
                            hidden: closed_level > 3 || true,
                            handler: function (button) {
                                var params = {
                                    staff_id: 11113333
                                };
                                createNewOrder(id, country, params);
                            }
                        }, {
                            xtype: 'button',
                            id: 'create_2442' + id,
                            text: 'Новый &laquo;2442&raquo;',
                            hidden: true,
                            handler: function () {
                                Ext.getCmp('windAnket_' + id).close();
                                CreateMenuObzvon(id, country, true);
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
                            hidden: closed_level > 0,
                            handler: function (button) {
                                var fp = button.up('panel').child('form');
                                if (fp.getForm().isValid()) {
                                    fp.getForm().submit({
                                        url: '/handlers/set_menu_delivery.php?&id=' + id,
                                        waitMsg: 'Жди...',
                                        success: function (fp, action) {
                                            Ext.getCmp('windAnket_' + id).close();
                                            var grid = Ext.getCmp('DostavkaGridId');
                                            if (grid) {
                                                grid.getStore().reload();
                                            }
                                        }
                                    });
                                } else {
                                    console.log('Not valid');
                                    console.log(fp.query("field{isValid()==false}"));
                                }
                            }
                        }
                    ],
                    items: [
                        {
                            xtype: 'form',
                            id: 'MenuForm_' + id,
                            url: '/handlers/get_menu_delivery.php?id=' + id + '&closed_level=' + parseInt(closed_level),
                            border: false,
                            disabled: closed_level >= 20,
                            padding: 10,
                            defaults: {
                                anchor: '100%',
                                labelWidth: 150
                            },
                            items: formFieldsItems,
                            listeners: {
                                render: function () {
                                    Ext.Ajax.request({
                                        method: 'POST',
                                        url: '/handlers/get_city_regions.php?type=getAll',
                                        success: function (response, opts) {
                                            response = Ext.decode(response.responseText);
                                            if (!response.success) {
                                                return false;
                                            }
                                            aAllCities.push({
                                                zip: '000000',
                                                city: 'Почта'
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
                                                    zip: val.zip,
                                                    city: val.city
                                                });
                                            });

                                            Ext.data.StoreManager.lookup('DeliveryCityStore').add(aAllCities);
                                        }
                                    });
                                }
                            }
                        }
                    ]
                },
                {
                    xtype: 'panel',
                    id: 'east' + id,
                    width: 150,
                    region: 'east',
                    collapsible: true,
                    collapsed: true,
                    layout: 'fit',
                    margins: '5 0 0 5',
                    html: '<p><b>ПРЕД ОПЛАТА ПО ЧЕКАМ</b><br/>\n\
До19999-3000тнг<br/>\n\
20.000-25.999-4500тнг<br/>\n\
26.000-30.999-5500тнг<br/>\n\
31.000-35.999-6500тнг<br/>\n\
36.000-40.999-7500тнг<br/>\n\
41.000-45.999-8500тнг<br/>\n\
46.000-50.000-10500тнг<br/>\n\
<br/>\n\
<b>ИСКЛЮЧЕНИЕ ТОВАРЫ</b><br/>\n\
-original_parfume<br/>\n\
-complited_diet<br/>\n\
-minoxidil_woman<br/>\n\
-minoxidil<br/>\n\
<b>ВСЕГДА 30% ПРЕД.ОПЛАТЫ ОТ СУММЫ ЗАКАЗА</b></p>'
                }
            ]
        }).show();

        Ext.getCmp('MenuForm_' + id).getForm().load({
            success: function (form, action) {
                if (country === 'kzg') {
                    Ext.getCmp('callUpload' + id).setVisible(true);

                    Ext.Array.each(globalStatusCurStore.ValuesArr, function (val, key) {
                        var alailableStatuses = [];
                        if (['Продажа курьер', 'Продажа курьер распол'].indexOf(val[0]) < 0) {
                            alailableStatuses.push(val);
                        }
                        Ext.getCmp('status_cur' + id).bindStore(alailableStatuses);
                    });

                }

                if (session_incomeoper || globalStaffId === 11111111) {
                    Ext.getCmp('uuid' + id).setValue('<a href="javascript:CreateMenuClient(\'' + action.result.data.uuid + '\', \'' + country + '\')">' + action.result.data.uuid + '</a>');
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

                if ((session_postlogist) > 0) {
                    switch (action.result.data.send_status) {
                        case 'Отправлен':
                            globalStatusKzStore.filter('id', /Готов к консультации|Обработка|Проверен|Хранение|На контроль|Получен|Заберет|Скинут предоплату/);
                            break;
                        case 'Предоплата':
                            globalStatusKzStore.filter('id', /Получен|Хранение|Отложенная доставка|Заберет/);
                            break;
                        default:
                            globalStatusKzStore.clearFilter(true);
                            break;
                    }
                }

                Ext.getCmp('opera' + id).setValue(globalManagerStore.ValuesJson[action.result.data.last_edit] ? globalManagerStore.ValuesJson[action.result.data.last_edit] : '');

                if ((session_logist + session_incomeoper) > 0 && !session_admin) {
                    var dateDeliveryEl = Ext.getCmp('date_delivery' + id);

                    if (session_incomeoper > 0) {
//                        dateDeliveryEl.setMinValue(Ext.Date.add(new Date(), Ext.Date.DAY, 0));
                    } else {
                        dateDeliveryEl.setMinValue(Ext.Date.add(new Date(), Ext.Date.DAY, +1));
                    }
                    dateDeliveryEl.setMaxValue(Ext.Date.add(new Date(), Ext.Date.DAY, +4));
                }
                var last_ed = action.result.data.last_edit;
                var respFio = globalManagerAllStore.ValuesResponsibleJson[last_ed] ? globalManagerAllStore.ValuesJson[globalManagerAllStore.ValuesResponsibleJson[last_ed]] : '';
                Ext.getCmp('otv' + id).setValue(respFio);

                // свойства товара
                var properties = action.result.data.offer_properties;

                if (Object.keys(properties).length > 0) {
                    var container = Ext.getCmp('offer-property-fields' + id);

                    Ext.Object.each(properties, function (type, property) {
                        var options = [];

                        Ext.Array.each(property, function (val, key) {
                            options.push(val);
                        });

                        var value = '';

                        if (typeof action.result.data.offer_property[type] !== 'undefined') {
                            value = action.result.data.offer_property[type];
                        }

                        var tpl = myProperty.create("offer_property", type, id, options, value);
                        container.add(tpl);
                    });

                    // show properties for main product
                    if (action.result.data.status === "Подтвержден" || action.result.data.status === "Предварительно подтвержден") {
                        container.setDisabled(false);
                        container.show();
                    } else {
                        container.setDisabled(true);
                        container.hide();
                    }
                }

                // доп. товар
                var dop_tovar = action.result.data.dop_tovar;

                if (Object.keys(dop_tovar).length > 0) {
                    // add panels
                    Ext.Array.each(dop_tovar, function (item, index) {
                        myProperty.panel(item);
                    });
                }

                // Источник
                var tmp_staff_id = Ext.getCmp('staff_id' + id).getValue();
                if (globalAllColdStaffArr.indexOf(tmp_staff_id) > -1 || country === 'uz') {
                    if (country === 'kz') {
//                        Ext.getCmp('offer' + id).bindStore(globalOffersStore.ValuesColdKzArr);
                        console.log('filter: offer_show_in_cold_kz');
                        globalOffersStore.filter('offer_show_in_cold_kz', '1');
                    } else if (country === 'uz') {
//                        Ext.getCmp('offer' + id).bindStore(globalOffersStore.ValuesColdUzArr);
                        console.log('filter: offer_show_in_cold_uz');
                        globalOffersStore.filter('offer_show_in_cold_uz', '1');
                    } else if (country === 'kzg') {
//                        Ext.getCmp('offer' + id).bindStore(globalOffersStore.ValuesColdKgzArr);
//                        console.log('filter: offer_show_in_cold_kgz');
//                        globalOffersStore.filter('offer_show_in_cold_kgz', '1');
                    }
                }

                var st_name = globalPartnersStore.ValuesJson[tmp_staff_id] ? globalPartnersStore.ValuesJson[tmp_staff_id] : tmp_staff_id;

                if (parseInt(action.result.data.web_id) === 287) {
                    st_name = 'BrdMarket';
                }
                Ext.getCmp('staff_id' + id).setValue(st_name + ' - ' + action.result.data.web_id);

                if (action.result.data.kz_delivery === 'Почта') {
                    if (Ext.getCmp('east' + id).getCollapsed()) {
                        Ext.getCmp('east' + id).toggleCollapse();
                    }
                } else {
//                    console.log("Ext.getCmp('east' + id).setWidth(0)");
//                    Ext.getCmp('east' + id).setWidth(0);
                }

                if (action.result.data.common_recall_date && action.result.data.common_recall_time && Ext.getCmp('common_recall_date' + id)) {
                    Ext.getCmp('common_recall_date' + id).setValue(action.result.data.common_recall_date);
                    Ext.getCmp('common_recall_time' + id).setValue(action.result.data.common_recall_time);
                }
                if (action.result.data.common_cancel_type && Ext.getCmp('common_cancel_type' + id)) {
                    Ext.getCmp('common_cancel_type' + id).setValue(action.result.data.common_cancel_type);
                }

                var intCatVal = action.result.data.common_interested_category;
                if (intCatVal > 0 && Ext.getCmp('common_interested_category_display' + id)) {
                    Ext.getCmp('common_interested_category_display' + id).setValue(globalCommonInterestedCategoryStore.ValuesJson[intCatVal] ? globalCommonInterestedCategoryStore.ValuesJson[intCatVal] : v);
                    Ext.getCmp('common_interested_category_display' + id).show();
                }

                var tmp_phone = Ext.getCmp('phone' + id).getValue();
                var tmp_offer = Ext.getCmp('offer' + id).getValue();
                var send_st = Ext.getCmp('send_status' + id).getValue();
                var deli = Ext.getCmp('kz_delivery' + id).getValue();
                var aRegions = [];
                if (deli === 'Москва') {
                    Ext.getCmp('city_region' + id).editable = true;
                    var metro_store = ['Авиамоторная', 'Автозаводская', 'Академическая', 'Александровский сад', 'Алексеевская', 'Алма-Атинская', 'Алтуфьево', 'Аннино', 'Арбатская (Арбатско-Покровская линия)', 'Арбатская (Филевская линия)', 'Аэропорт', 'Бабушкинская', 'Багратионовская', 'Баррикадная', 'Бауманская', 'Беговая', 'Белорусская', 'Беляево', 'Бибирево', 'Библиотека имени Ленина', 'Борисово', 'Боровицкая', 'Ботанический сад', 'Братиславская', 'Бульвар адмирала Ушакова', 'Бульвар Дмитрия Донского', 'Бульвар Рокоссовского', 'Бунинская аллея', 'Варшавская', 'ВДНХ', 'Владыкино', 'Водный стадион', 'Войковская', 'Волгоградский проспект', 'Волжская', 'Волоколамская', 'Воробьевы горы', 'Выставочная', 'Выхино', 'Деловой центр', 'Динамо', 'Дмитровская', 'Добрынинская', 'Домодедовская', 'Достоевская', 'Дубровка', 'Жулебино', 'Зябликово', 'Измайловская', 'Калужская', 'Кантемировская', 'Каховская', 'Каширская', 'Киевская', 'Китай-город', 'Кожуховская', 'Коломенская', 'Комсомольская', 'Коньково', 'Красногвардейская', 'Краснопресненская', 'Красносельская', 'Красные ворота', 'Крестьянская застава', 'Кропоткинская', 'Крылатское', 'Кузнецкий мост', 'Кузьминки', 'Кунцевская', 'Курская', 'Кутузовская', 'Ленинский проспект', 'Лермонтовский проспект', 'Лубянка', 'Люблино', 'Марксистская', 'Марьина роща', 'Марьино', 'Маяковская', 'Медведково', 'Международная', 'Менделеевская', 'Митино', 'Молодежная', 'Монорельса Выставочный центр', 'Монорельса Телецентр', 'Монорельса Улица Академика Королева', 'Монорельса Улица Милашенкова', 'Монорельса Улица Сергея Эйзенштейна', 'Монорельсовой дороги Тимирязевская', 'Мякинино', 'Нагатинская', 'Нагорная', 'Нахимовский проспект', 'Новогиреево', 'Новокосино', 'Новокузнецкая', 'Новослободская', 'Новоясеневская', 'Новые Черемушки', 'Октябрьская', 'Октябрьское поле', 'Орехово', 'Отрадное', 'Охотныйряд', 'Павелецкая', 'Парк культуры', 'Парк Победы', 'Партизанская', 'Первомайская', 'Перово', 'Петровско-Разумовская', 'Печатники', 'Пионерская', 'Планерная', 'Площадь Ильича', 'Площадь Революции', 'Полежаевская', 'Полянка', 'Пражская', 'Преображенская площадь', 'Пролетарская', 'Проспект Вернадского', 'Проспект Мира', 'Профсоюзная', 'Пушкинская', 'Пятницкое шоссе', 'Речной вокзал', 'Рижская', 'Римская', 'Рязанский проспект', 'Савеловская', 'Свиблово', 'Севастопольская', 'Семеновская', 'Серпуховская', 'Славянский бульвар', 'Смоленская (Арбатско-Покровская линия)', 'Смоленская (Филевская линия)', 'Сокол', 'Сокольники', 'Спартак', 'Спортивная', 'Сретенский бульвар', 'Строгино', 'Студенческая', 'Сухаревская', 'Сходненская', 'Таганская', 'Тверская', 'Театральная', 'Текстильщики', 'Теплый стан', 'Тимирязевская', 'Третьяковская', 'Тропарево', 'Трубная', 'Тульская', 'Тургеневская', 'Тушинская', 'Улица Академика Янгеля', 'Улица Горчакова', 'Улица Скобелевская', 'Улица Старокачаловская', 'Улица 1905 года', 'Университет', 'Филевский парк', 'Фили', 'Фрунзенская', 'Царицыно', 'Цветной бульвар', 'Черкизовская', 'Чертановская', 'Чеховская', 'Чистые пруды', 'Чкаловская', 'Шаболовская', 'Шипиловская', 'Шоссе Энтузиастов', 'Щелковская', 'Щукинская', 'Электрозаводская', 'Юго-Западная', 'Южная', 'Ясенево'];

                    Ext.each(metro_store, function (rval, rid) {
                        aRegions.push({
                            zip: rid,
                            region: rval
                        });
                    });
                    var regionStore = Ext.data.StoreManager.lookup('DeliveryCityRegionStore');
                    regionStore.removeAll();
                    Ext.getCmp('city_region' + id).reset();

                    if (aRegions.length > 0) {
                        regionStore.add(aRegions);
                    }
                    Ext.getCmp('city_region' + id).setValue(action.result.data.city_region);
                }

                var s_st = Ext.getCmp('status' + id);
                if (s_st) {
                    s_st = s_st.getValue();
                    if (s_st === 'Подтвержден') {
                        Ext.getCmp('windAnket_' + id).addCls("defn_content");
                        if (s_st === 'Предварительно подтвержден') {
                            Ext.getCmp('status' + id).bindStore(["Подтвержден", "Отменён", "Перезвонить", "Недозвон", "Брак", "Уже получил заказ", "Черный список", "Заказано у конкурентов", "Заказ уже обработан"]);
                        }
                    } else {
                        Ext.getCmp('reload' + id).setDisabled(false);
                    }
                    if (send_st === 'Оплачен') {
                        Ext.getCmp('kz_admin' + id).destroy();
                    }
                    if (action.result.data.staff_id === '2442') {
                        Ext.getCmp('create_2442' + id).show();
                    }
                }

                if (['На доставку', 'Вручить подарок'].indexOf(action.result.data.status_kz) > -1 && action.result.data.kz_admin && Ext.getCmp('kz_admin' + id)) {
                    Ext.getCmp('kz_admin' + id).disable();
                }

                if (action.result.data.send_status === 'Отправлен' && ['На доставку', 'Вручить подарок'].indexOf(action.result.data.status_kz) > -1 && action.result.data.kz_operator > 0 && Ext.getCmp('kz_operator' + id)) {
                    Ext.getCmp('kz_operator' + id).disable();
                }

                ////////////////////////////////////////////
                // START call_me
                var callMeEl = Ext.getCmp('call_me' + id);
                if (country === 'kzg') {
                    Ext.getCmp('phone' + id).setVisible(false);
                    if (tmp_phone.length === 10) {
                        tmp_phone = '+996' + tmp_phone.substr(1);
                    } else if (tmp_phone.length === 11) {
                        tmp_phone = '+996' + tmp_phone.substr(2);
                    } else if (tmp_phone.length === 12) {
                        tmp_phone = '+996' + tmp_phone.substr(3);
                    } else {
                        tmp_phone = '+996' + tmp_phone.substr(4);
                    }

                    var prefix = '25*';
                    if (session_operator_bishkek) {
                        prefix = '33*';
                    }

                    callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:' + prefix + id + '">&#9990;</a>');

                } else if (country === 'ru' || country === 'RU') {
                    tmp_phone = '7' + tmp_phone.substr(1);
                    callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:7*' + tmp_phone + '#' + id + '">&#9990;</a>');
                } else if (country === 'am') {
                    tmp_phone = '+' + tmp_phone;
                    callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:23*' + tmp_phone + '#' + id + '">&#9990;</a>');
                } else if (country === 'ae') {
                    tmp_phone = '+' + tmp_phone;
                    callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:77*' + tmp_phone + '#' + id + '">&#9990;</a>');
                } else if (country === 'uz') {
                    tmp_phone = '+' + tmp_phone;
                    callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:47*' + tmp_phone + '#' + id + '">&#9990;</a>');
                } else if (country === 'az') {
                    //tmp_phone = '+'+tmp_phone;
                    callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:16*' + tmp_phone + '#' + id + '">&#9990;</a>');
                } else {
                    Ext.getCmp('phone' + id).setVisible(false);
                    tmp_phone = '8' + tmp_phone.substr(1);
                    tmp_phone2 = Ext.getCmp('phone_sms' + id).getValue();
                    if (new_sim) {
                        callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:18*' + id + '">&#9990;</a>');
                    } else if (tmp_phone2.length > 5) {
                        callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:' + buildHash(callPrefix + id) + '">&#9990;</a><a style="font-size:40px;text-decoration:none;" href="sip:' + buildHash(callPrefix + id) + '">&#9990;</a>');
                    } else {
                        callMeEl.setValue('<a style="font-size:40px;text-decoration:none;" href="sip:' + buildHash(callPrefix + id) + '">&#9990;</a>');
                    }
                }
                ////////
                if (session_whatsappoperator > 0) {
                    var currVal = callMeEl.getValue();
                    currVal += '&nbsp;<span style="font-size:40px;cursor:pointer;" href="javascript:void(0);" onClick="javascript:whatsAppChat(\'' + id + '\',\'' + action.result.data.phone_orig + '\');"><img src="/images/whatsapp_32x32.png"/></span>';
                    callMeEl.setValue(currVal);
                }

                if (((session_adminlogistpost + session_logistprepayment + session_adminsales) > 0) || [11111111, 25937686].indexOf(globalStaffId) > -1) {
                    var currVal = callMeEl.getValue();
                    currVal += '&nbsp;<span style="font-size:40px;cursor:pointer;" href="javascript:void(0);" onClick="javascript:approveSms(\'' + id + '\',\'' + action.result.data.kz_delivery + '\');"><img src="/images/sms_chat-color_32x32.png"/></span>';
                    callMeEl.setValue(currVal);
                }

                // END call_me
                ////////////////////////////////////////////

                if (is_status) {
                    Ext.getCmp('send_status' + id).bindStore([['Отказ', 'Отказ']]);

                    if (session_adminsales) {
                        Ext.getCmp('send_status' + id).bindStore([['Отказ', 'Отказ'], ['Отправлен', 'Отправлен']]);
                    }

                    if ((session_postlogist + session_logist) < 1) {
                        Ext.getCmp('send_status' + id).setVisible(true);
                    }
                }

                calcTotalPrice(id, false);

                calculateTicketsCount(id, [13, 15].indexOf(closed_level) > -1);
            }
        });
    }
}

function setPlusStatus(id, country, params) {

    params['id'] = id;
    params['fio'] = Ext.getCmp('fio' + id).getValue();
    params['cdr_call'] = Ext.getCmp('cdr_call' + id).getValue();
    params['kz_delivery'] = Ext.getCmp('kz_delivery' + id).getValue();

    console.log(params);
    Ext.Ajax.request({
        url: '/handlers/set_plus_status.php',
        method: 'POST',
        params: params,
        success: function (response, opts) {
            console.log(respObj);
            var respObj = Ext.decode(response.responseText);
            if (parseInt(respObj.is_cold) === 2) {
                Ext.MessageBox.alert('Success', 'Время перезвона успешно обновлено!');
                Ext.getCmp('windAnket_' + id).close();
            } else if (parseInt(respObj.is_cold) === 4) {
                Ext.MessageBox.alert('Success', 'Причина отказа успешно установлена!');
                Ext.getCmp('windAnket_' + id).close();
            } else if (respObj.new_id) {
                Ext.getCmp('windAnket_' + id).close();
                if (country === 'RU' || country === 'ru') {
                    CreateMenuObzvonRus(respObj.new_id, country, true);
                } else {
                    CreateMenuObzvon(respObj.new_id, country, true);
                }
            } else if (respObj.is_cold_staff_id || respObj.is_cold_new_staff_id) {
                Ext.getCmp('windAnket_' + id).close();
            } else {
                Ext.MessageBox.alert('Error', 'Error update status - обратитесь к программистам!');
            }
        },
        failure: function (response, opts) {
            Ext.MessageBox.alert('Error', 'Error occurred during request execution! Please try again!');
        }
    });
}

function setDeliveryVisible(mode, id, store) {
    var item = Ext.getCmp('description' + id);
    if (item && mode) {
        item.setEditable(false);
        item.bindStore(store.ValuesArr);
        item.reset();
        item.allowBlank = false;
        item.validate();
    } else {
        item.setEditable(true);
        item.store.removeAll();
        item.reset();
        item.allowBlank = true;
        item.validate();
    }
}

function calcTotalPrice(id, isChange) {

    if (Ext.getCmp('kz_delivery' + id).getValue() === 'Почта') {
        switch (Ext.getCmp('send_status' + id).getValue()) {
            case 'Предоплата':
            case 'Оплачен':
                if (isChange && [11111111, 24511553, 66629642, 61386417, 88189675, 12019085].indexOf(globalStaffId) < 0) {
                    Ext.getCmp('post_price' + id).setValue(3000);
                }

                if ([11111111, 24511553, 66629642, 61386417, 88189675, 12019085].indexOf(globalStaffId) > -1) {
                    Ext.getCmp('post_price' + id).setEditable(true);
                    Ext.getCmp('total_price' + id).setEditable(true);
                } else {
                    Ext.getCmp('post_price' + id).setEditable(false);
                    Ext.getCmp('total_price' + id).setEditable(false);
                    Ext.getCmp('total_price' + id).setValue(parseInt(Ext.getCmp('price' + id).getValue()) - Ext.getCmp('post_price' + id).getValue());
                }

                break;
            default :

                if (isChange) {
                    Ext.getCmp('post_price' + id).setValue(0);
                }


                break;
        }
    } else {
        Ext.getCmp('post_price' + id).setEditable(true);
        Ext.getCmp('total_price' + id).setEditable(true);
    }

}