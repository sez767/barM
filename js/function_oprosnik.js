
// Вкладка "Опросник"
function OprosnikTab(tabs) {
//    return false;

    var tab = tabs.queryById('OprosnikTab');
    if (tab) {
        tab.show();
    } else {

        Ext.define('OprosnikModel', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'country', type: 'string'},
                {name: 'question', type: 'string'},
                {name: 'status', type: 'boolean'},
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
                field: 'name',
                direction: 'ASC'
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
                {dataIndex: 'question', type: 'string'},
                {dataIndex: 'status', type: 'boolean', yesText: 'Да', noText: 'Нет'},
                {dataIndex: 'created_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'created_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr},
                {dataIndex: 'deleted_at', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {dataIndex: 'created_by', type: 'list', phpMode: true, options: globalManagerStore.ValuesArr}
            ]
        });
        
        var groupingSummaryFeature = {
            id: 'oprosnikGroupingFeatureId',
            ftype: 'groupingsummary',
            enableGroupingMenu: true,
            groupHeaderTpl: '{columnName}: {name}'
        };
        
        var oprosnikRowEditing = new Ext.grid.plugin.RowEditing({
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
            store: OprosnikStore,
            forceFit: true,
            autoScroll: true,
            loadMask: true,
            layout: 'border',
            height: 500,
            plugins: [oprosnikRowEditing],
            features: [
                groupingSummaryFeature,
                OprosnikFilters
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
            },
            columns: [
                {header: 'Id', dataIndex: 'id', width: 90, sortable: true, hidden: true},
                {header: 'Активен', dataIndex: 'status', width: 30, sortable: true, editor: {xtype: 'checkbox'},
                    renderer: function (v) {
                        return (v > 0) ? 'Да' : 'Нет';
                    }
                },
                {header: 'Страна', dataIndex: 'country', width: 140, sortable: true,
                    editor: new Ext.form.ComboBox({
                        editable: false,
                        triggerAction: 'all',
                        lazyRender: true,
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
                {header: 'Вопрос', dataIndex: 'question', width: 200, sortable: true, editor: new Ext.form.TextField({allowBlank: false})},
                {header: 'Дата создания', dataIndex: 'created_at', width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s'), hidden: true},
                {header: 'Кто создал', dataIndex: 'created_by', width: 150, sortable: true, hidden: true,
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
                                OprosnikStore.insert(0, new OprosnikModel());
                                oprosnikRowEditing.startEdit(0, 0);
                            }
                        }, {
                            itemId: 'delete',
                            text: 'Удалить',
                            iconCls: 'fa fa-minus-circle',
                            disabled: true,
                            handler: function (b) {
                                var dataModel = b.up('grid').getSelectionModel().getLastSelected();
                                if (dataModel) {
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
            ]
        });
        tabs.add({
            id: 'OprosnikTab',
            constrainHeader: true,
            closable: true,
            layout: {type: 'card'},
            iconCls: 'fa fa-1x fa-question',
            title: '<div style="font-size: 18px; padding-left:15px;">Опросник</div>',
            items: [OprosnikGrid]
        }).show();
    }

}