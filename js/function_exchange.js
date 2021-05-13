
// Вкладка "Опросник"
function ExchangeTab(tabs) {
//    return false;

    var tab = tabs.queryById('ExchangeTab');
    if (tab) {
        tab.show();
    } else {

        Ext.define('ExchangeModel', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'what', type: 'string'},
                {name: 'for_what', type: 'string'},
                {name: 'pick_what', type: 'string'},
                {name: 'change_date', type: 'date'},
                {name: 'reason', type: 'string'},
                {name: 'kz_delivery', type: 'string'},
                {name: 'status', type: 'bool'},
                {name: 'delay_reason', type: 'string'},
                {name: 'created_at', type: 'date'},
                {name: 'created_by'},
                {name: 'updated_at', type: 'date'},
                {name: 'updated_by'},
                {name: 'deleted_at', type: 'date'},
                {name: 'deleted_by'}
            ]
        });
        // store "Опросник"
        var ExchangeStore = new Ext.data.JsonStore({
            storeId: 'ExchangeStore',
            model: 'ExchangeModel',
            autoDestroy: true,
            remoteSort: true,
            pageSize: 50,
            groupField: 'country',
            autoLoad: true,
            autoSync: true,
            proxy: {
                type: 'ajax',
                api: {
                    read: '/handlers/handler_exchange.php?method=read',
                    update: '/handlers/handler_exchange.php?method=update',
                    create: '/handlers/handler_exchange.php?method=insert',
                    destroy: '/handlers/handler_exchange.php?method=delete'
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
            sortInfo: {field: 'id', direction: 'DESC'},
            listeners: {
                write: function (store, operation, eOpts) {
                }
            }
        });
        var ExchangeFilters = new Ext.ux.grid.FiltersFeature({
            encode: false,
            local: false,
            filters: [
                {dataIndex: 'id', type: 'string'},
                {dataIndex: 'what', type: 'string'},
                {dataIndex: 'for_what', type: 'string'},
                {dataIndex: 'pick_what', type: 'string'},
                {dataIndex: 'change_date', type: 'date', dateFormat: 'Y-m-d'},
                {dataIndex: 'reason', type: 'string'},
                {dataIndex: 'kz_delivery', type: 'list', phpMode: true, options: globalDeliveryCouriersStore.ValuesArr},
                {dataIndex: 'status', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'delay_reason', type: 'string'},
                {dataIndex: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'created_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr},
                {dataIndex: 'updated_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'updated_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr},
                {dataIndex: 'deleted_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'deleted_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr}

            ]
        });

        var exchangeRowEditing = new Ext.grid.plugin.RowEditing({
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
                }
            }
        });
        var ExchangeGrid = new Ext.grid.GridPanel({
            id: 'ExchangeGrid',
            store: ExchangeStore,
            forceFit: false,
            autoScroll: true,
            loadMask: true,
            layout: 'border',
            height: 500,
            plugins: [exchangeRowEditing],
            features: [ExchangeFilters],
            listeners: {
                viewready: function (grid, eOpts) {
                    var groupingFeature = grid.getView().getFeature('exchangeGroupingFeatureId');
                    if (groupingFeature) {
                        groupingFeature.disable();
                    }
                },
                selectionchange: function (selRowModel, dataModels) {
                    ExchangeGrid.down('#delete').setDisabled(dataModels.length === 0);
                },
                itemdblclick: function (grid, record, item, index, event) {
                    CreateMenuDelivery({order_id: record.data.what}, 'kz', false, 'LogistKZ_P', false, 0);
                }
            },
            columns: [
                {dataIndex: 'ID', header: 'Id', width: 90, sortable: true, hidden: true},
                {dataIndex: 'what', header: 'ID заказа', width: 200, sortable: true, editor: new Ext.form.TextField({})},
                {dataIndex: 'for_what', header: 'На что заменить', width: 200, sortable: true, editor: new Ext.form.TextField({})},
                {dataIndex: 'pick_what', header: 'Что забрать у клиента', width: 200, sortable: true, editor: new Ext.form.TextField({})},
                {dataIndex: 'change_date', header: 'Дата замены', width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d'),
                    editor: new Ext.form.DateField({
                        dateFormat: 'Y-m-d',
                        startDay: 1
                    })
                },
                {dataIndex: 'kz_delivery', width: 150, header: 'Тип доставки',
                    editor: new Ext.form.ComboBox({
                        editable: false,
                        triggerAction: 'all',
                        lazyRender: true,
                        mode: 'local',
                        store: globalDeliveryCouriersStore,
                        valueField: 'id',
                        displayField: 'value'
                    })
                },
                {dataIndex: 'status', header: 'Замена сделана', xtype: 'booleancolumn', editor: {xtype: 'checkbox'}, trueText: 'Да', falseText: 'Нет'},
                {dataIndex: 'reason', header: 'Причина замены', width: 200, sortable: true, editor: new Ext.form.TextField({})},
                {dataIndex: 'delay_reason', header: 'Причина переноса замены', width: 200, sortable: true, editor: new Ext.form.TextField({})},
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
            viewConfig: {
                forceFit: true,
                enableTextSelection: true,
                showPreview: true,
                enableRowBody: true
            },
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
                                ExchangeStore.insert(0, new ExchangeModel());
                                exchangeRowEditing.startEdit(0, 0);
                            }
                        }, {
                            itemId: 'delete',
                            text: 'Удалить',
                            iconCls: 'fa fa-minus-circle',
                            disabled: true,
                            handler: function (b) {
                                var dataModel = b.up('grid').getSelectionModel().getLastSelected();
                                if (dataModel) {
                                    ExchangeStore.remove(dataModel);
                                }
                            }
                        }
                    ]
                }, {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        new Ext.PagingToolbar({
                            store: ExchangeStore,
                            displayInfo: true,
                            plugins: [new Ext.create('Ext.ux.PagingToolbarResizer', {options: [10, 20, 50, 100, 200, 300, 400, 500, 1000]})]
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
            ]
        });
        tabs.add({
            id: 'ExchangeTab',
            constrainHeader: true,
            closable: true,
            layout: {type: 'card'},
            iconCls: 'fa fa-1x fa-exchange-alt',
            title: 'Замены',
            items: [ExchangeGrid]
        }).show();
    }

}