//<script>
shipmentStatisticStore = new Ext.data.JsonStore({
    autoDestroy: true,
    remoteSort: false,
    pageSize: 100,
    autoSync: true,
    proxy: {
        type: 'ajax',
        url: '/handlers/get_shipmentStatistic.php?s=1',
        simpleSortMode: true,
        reader: {
            type: 'json',
            successProperty: 'success',
            idProperty: 'offer',
            root: 'datad',
            messageProperty: 'message'
        }
    },
    storeId: 'StaffStore',
    fields: [
        {
            name: 'offer',
            type: 'string'
        }, {
            name: 'price_otpravka',
            type: 'string'
        }, {
            name: 'price_otpravka_per',
            type: 'numeric'
        }, {
            name: 'price_otpravlen',
            type: 'string'
        }, {
            name: 'price_otpravlen_per',
            type: 'numeric'
        }, {
            name: 'price_otkaz',
            type: 'string'
        }, {
            name: 'price_otkaz_per',
            type: 'numeric'
        }, {
            name: 'price_bablo',
            type: 'string'
        }, {
            name: 'price_bablo_per',
            type: 'numeric'
        }, {
            name: 'price_net',
            type: 'string'
        }, {
            name: 'itogo',
            type: 'numeric'
        }
    ]
});

shipmentStatisticStore.load();

StatPaystore = new Ext.data.JsonStore({
    autoDestroy: true,
    autoLoad: true,
    proxy: {
        type: 'ajax',
        url: '/handlers/get_shipmentStatistic.php',
        reader: {
            type: 'json',
            root: 'data2',
            idProperty: 'id',
            totalProperty: 'total2'
        }
    },
    remoteSort: true,
    storeId: 'StatPaystore',
    fields: [
        {
            name: 'money',
            type: 'numeric'
        }, {
            name: 'status',
            type: 'string'
        }
    ]
});

Paychart = Ext.create('Ext.chart.Chart', {
    xtype: 'chart',
    animate: true,
    width: 300,
    height: 280,
    store: StatPaystore,	id:'StatPayChart',
    shadow: true,	insetPadding: 10,theme: 'Base:gradients',
    series: [{	type: 'pie', field: 'money', donut: false,
        tips: {  trackMouse: true,  width: 200,	  height: 50,
          renderer: function(storeItem, item) {	var total = 0; 	StatPaystore.each(function(rec) {	total += rec.get('money');	});
            this.setTitle(storeItem.get('status') + '('+storeItem.get('money')+'): ' + Math.round(storeItem.get('money') / total * 100) + '%');
          }
        },
        highlight: {  segment: { margin: 20 }},
        label: { field: 'status', display: 'rotate', contrast: true, font: '14px Arial'	}
    }]
});

var DostGrid = new Ext.grid.GridPanel({
    frame: true,
    autoScroll: false,
    loadMask: true,
    height: 280,
    width: 880,
    id:'Dost_grid',
    store: shipmentStatisticStore,
    columns : [
         {dataIndex: 'offer', 				width:120,header: 'Значение'				}
        ,{dataIndex: 'price_otpravka', 		width:40,header: 'На отправку'			}
        ,{dataIndex: 'price_otpravka_per', 		width:60,header: 'На отправку'		}
        ,{dataIndex: 'price_otpravlen',		width:50, header: 'Груз отпрален'		}
        ,{dataIndex: 'price_otpravlen_per',		width:60, header: 'Груз отпрален'		}
        ,{dataIndex: 'price_otkaz', 		width:50, header: 'Отказ'	}
        ,{dataIndex: 'price_otkaz_per', 		width:60, header: 'Отказ'	}
        ,{dataIndex: 'price_bablo', 		width:50, header: 'Оплачен' 			}
        ,{dataIndex: 'price_bablo_per', 		width:60, header: 'Оплачен' 			}
        ,{dataIndex: 'price_net', 			width:70, header: 'Нет товара' 			}
        ,{dataIndex: 'itogo', 				width:70, header: 'Итого' 			}
    ],
    tbar:[{
        xtype:'combo',
        editable: false,
        forceSelection:true,
        triggerAction: 'all',
        lazyRender:true,
        mode: 'local',
        width:120,
        name:'offer',
        anchor:'25%',
        id:'stat_Country',
        labelWidth:40,
        store: [['kz','Казахстан'],['kzg','Киргизия']],
        fieldLabel: 'Страна:',
        valueField: 'value',
        displayField: 'value'
    },{
       xtype:'datefield',
       fieldLabel:'Дата от',
       width:180,
       format:'Y-m-d',
       labelWidth:50,
       value:'<?php echo date('Y-m-d'); ?>',
       itemId:'date',
       id:'stat_StartDate',
       allowBlank: false
    },{
       xtype:'datefield',
       fieldLabel:'до',
       width:150,
       format:'Y-m-d',
       labelWidth:20,
       value:'<?php echo date('Y-m-d'); ?>',
       itemId:'edate',
       id:'stat_EndDate',
       allowBlank: false
    },{
        xtype:'combo',
        editable: false,
        forceSelection:true,
        triggerAction: 'all',
        lazyRender:true,
        mode: 'local',
        width:180,
        name:'offer',
        anchor:'25%',
        id:'stat_offer',
        labelWidth:70,
        store: [['offer','по товару'],['day','по дням'],['month','по месяцам'],['curcity','Курьер\Город'],['delivery','по типам доставки'],['web','по вебам'],['webctr','по вебам CTR']],
        fieldLabel: 'Группировка',
        valueField: 'value',
        displayField: 'value'
    },{
        xtype:'combo',
        editable: false,
        forceSelection:true,
        triggerAction: 'all',
        lazyRender:true,
        mode: 'local',
        width:130,
        name:'dost',
        anchor:'25%',
        id:'stat_dost',
        labelWidth:50,
        store: ['','Почта','курьер'],
        fieldLabel: ' Доставка',
        valueField: 'value',
        displayField: 'value'
    }, '->', {
        xtype:'button',
        text:'Построить',
        handler:function(){
            start_date = Ext.getCmp('stat_StartDate').getValue();
            end_date = Ext.getCmp('stat_EndDate').getValue();
            offer = Ext.getCmp('stat_offer').getValue();
            country = Ext.getCmp('stat_Country').getValue();
            logist = Ext.getCmp('stat_dost').getValue();
            params = {
                    p1: country,
                    p2: start_date.format('Y-m-d'),
                    p3: end_date.format('Y-m-d'),
                    p4: offer,
                    p5: logist
            }
            shipmentStatisticStore.load({
                method:'post',
               params:params
            });
            StatPaystore.load({
                method:'post',
                params:params
            });
        }
    }],
    viewConfig: {
        forceFit: true,
        enableTextSelection: true,
        showPreview: true, // custom property
        enableRowBody: true, // required to create a second, full-width row to show expanded Record data
        getRowClass: function(record, rowIndex, rp, ds){ // rp = rowParams
            //return (record.data.Ban == '0') ? 'price-fall' : 'price-red';
        }
    }
});

Ext.create('widget.uxNotification', {
    title: 'Данные для <?php echo $_SESSION['Logged_StaffId']; ?>',
    position: 'tc',
    modal: true,
    manager: 'demo1',
    iconCls: 'ux-notification-icon-error',
    autoCloseDelay: 10000000,
    height:650,
    autoScroll:true,
    spacing: 20,
    useXAxis: true,
    closable: true,
    id: 'win-dost',
    slideInDuration: 800,
    slideBackDuration: 1500,
    slideInAnimation: 'elasticIn',
    slideBackAnimation: 'elasticIn',
    items: [DostGrid, Paychart]
}).show();
