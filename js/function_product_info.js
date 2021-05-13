var Ext;
// Вкладка "Опросник"
function ProductInfoTab(tabs) {
//    return false;

    var tab = tabs.queryById('OfferInfoTab');
    if (tab) {
        tab.show();
    } else {

        // Хранилище "Инфо о товарах"
        var OffersInfoStore = new Ext.data.JsonStore({
            autoDestroy: true,
            remoteSort: true,
            autoLoad: true,
            storeId: 'OffersInfoStore',
            pageSize: 100,
            autoSync: true,
            proxy: {
                type: 'ajax',
                url: 'handlers/get_Offers.php?t=1',
                simpleSortMode: true,
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    idProperty: 'id',
                    root: 'data',
                    messageProperty: 'message'
                }
            },
            fields: [
                {name: 'id', type: 'numeric'},
                {name: 'offer_name', type: 'string'},
                {name: 'offer_desc', type: 'string'},
                {name: 'offer_date', type: 'date', dateFormat: 'Y-m-d H:i:s'}
            ]
        });

        // Грид "Инфо о товарах"
        var offerInfo = new Ext.grid.GridPanel({
            frame: true,
            autoScroll: false,
            loadMask: true,
            height: 580,
            id: 'OffersInfo_grid',
            store: OffersInfoStore,
            columns: [
                {dataIndex: 'id', width: 50, header: 'ID'},
                {dataIndex: 'offer_name', width: 140, header: 'Название товара'},
                {dataIndex: 'offer_desc', width: 190, header: 'Описание товара'},
                {dataIndex: 'offer_date', width: 140, header: 'Дата добавления', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')}
            ],
            tbar: [{
                    text: 'Внести информацию о товаре',
                    handler: function () {
                        var rec = offerInfo.getSelectionModel().getSelection();
                        if (!rec[0])
                            alert("Выбери товар");
                        else {
                            var prompt;
                            if (!prompt) {
                                prompt = new Ext.Window({width: 750, modal: true, layout: 'form', resizable: false, autoScroll: true,
                                    title: 'Редактор информации о товарах - ' + rec[0].data.offer_name,
                                    height: 700,
                                    items: [{
                                            xtype: 'combo',
                                            store: [['kz', 'Казахстан'], ['kzg', 'Киргизия'], ['ru', 'раша'], ['am', 'Армения'], ['az', 'Азербайджан'], ['md', 'Молдова'], ['ae', 'OAE']],
                                            itemId: 'combo_staff',
                                            valueField: 'id',
                                            displayField: 'value',
                                            queryMode: 'local',
                                            fieldLabel: 'Проект',
                                            anchor: '50%',
                                            width: 150,
                                            id: 'projects',
                                            allowBlank: false,
                                            editable: false,
                                            listeners: {
                                                select: {
                                                    fn: function (a) {
                                                        Ext.Ajax.request({
                                                            method: "GET",
                                                            url: 'handlers/get_projectInfo.php?project=' + Ext.getCmp('projects').getValue() + '&offer=' + rec[0].data.offer_name,
                                                            success: function (response) {
                                                                Ext.getCmp('offer_longdescs').setValue(response.responseText);
                                                            }
                                                        });
                                                        Ext.Ajax.request({
                                                            method: "GET",
                                                            url: 'handlers/get_projectInfo.php?log=1&project=' + Ext.getCmp('projects').getValue() + '&offer=' + rec[0].data.offer_name,
                                                            success: function (response) {
                                                                Ext.getCmp('offer_logdesc').setValue(response.responseText);
                                                            }
                                                        });
                                                        Ext.Ajax.request({
                                                            method: "GET",
                                                            url: 'handlers/get_projectInfo.php?nocall=1&project=' + Ext.getCmp('projects').getValue() + '&offer=' + rec[0].data.offer_name,
                                                            success: function (response) {
                                                                Ext.getCmp('offer_nocall').setValue(response.responseText);
                                                            }
                                                        });
                                                        Ext.Ajax.request({
                                                            method: "GET",
                                                            url: 'handlers/get_projectInfo.php?recall=1&project=' + Ext.getCmp('projects').getValue() + '&offer=' + rec[0].data.offer_name,
                                                            success: function (response) {
                                                                Ext.getCmp('offer_recall').setValue(response.responseText);
                                                            }
                                                        });
                                                    }
                                                }
                                            },
                                            typeAhead: true,
                                            triggerAction: 'all'
                                        }, {
                                            xtype: 'htmleditor',
                                            fieldLabel: 'Описание',
                                            anchor: '100%',
                                            height: 300,
                                            id: 'offer_longdescs',
                                            name: 'offer_longdescs'
                                        }, {
                                            xtype: 'htmleditor',
                                            fieldLabel: 'Описание Логистика',
                                            anchor: '100%',
                                            height: 200,
                                            id: 'offer_logdesc',
                                            name: 'offer_logdesc'
                                        }, {
                                            xtype: 'htmleditor',
                                            fieldLabel: 'Описание Недозвон',
                                            anchor: '100%',
                                            height: 200,
                                            id: 'offer_nocall',
                                            name: 'offer_nocall'
                                        }, {
                                            xtype: 'htmleditor',
                                            fieldLabel: 'Описание Перезвон',
                                            anchor: '100%',
                                            height: 200,
                                            id: 'offer_recall',
                                            name: 'offer_recall'
                                        }],
                                    buttons: [{
                                            text: 'Сохранить',
                                            handler: function () {
                                                var project = Ext.getCmp('projects').getValue();
                                                Ext.Ajax.request({
                                                    url: 'handlers/saveDesc.php?project=' + project + '&offer=' + rec[0].data.offer_name,
                                                    method: 'POST',
                                                    params: {
                                                        text: Ext.getCmp('offer_longdescs').getValue(),
                                                        logtext: Ext.getCmp('offer_logdesc').getValue(),
                                                        nocalltext: Ext.getCmp('offer_nocall').getValue(),
                                                        recalltext: Ext.getCmp('offer_recall').getValue()
                                                    },
                                                    success: function (response) {
                                                        alert(response.responseText);
                                                    },
                                                    failure: function () {
                                                    }
                                                });
                                            }
                                        }]
                                });
                            }
                            prompt.show();
                        }
                    }}],
            bbar: new Ext.PagingToolbar({
                store: OffersInfoStore,
                beforePageText: 'Страница',
                displayMsg: 'Отображается {0} - {1} из {2}',
                afterPageText: 'из {0}',
                displayInfo: true
            }),
            viewConfig: {
                forceFit: true,
                enableTextSelection: true,
                showPreview: true,
                enableRowBody: true
            }
        });


        tabs.add({
            id: 'OfferInfoTab',
            iconCls: 'fa fa-1x fa-info-circle',
            closable: true,
            layout: {
                type: 'card'
            },
            title: '<div style="font-size: 18px; padding-left:15px;">Информация о товарах</div>',
            items: [offerInfo]
        }).show();

    }

}