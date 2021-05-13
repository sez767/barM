
function SipGant() {
    var win = Ext.getCmp('SipGantWindow');
    if (!win) {

        var win = new Ext.Window({
            id: 'SipGantWindow',
            width: 600,
            height: 400,
            maximized: true,
            title: 'Активность работы ',
            iconCls: 'staff-list',
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            flex: true,
            tbar: [{
                xtype: 'combo',
                editable: false,
                triggerAction: 'all',
                queryMode: 'local',
                width: 300,
                anchor: '100%',
                id: 'sipstat_ResponsibleClients',
                labelWidth: 55,
                store: globalResponsibleStore,
                fieldLabel: 'Ответств',
                valueField: 'id',
                displayField: 'value'
            },{
                xtype: 'datefield',
                fieldLabel: 'дата',
                startDay: 1,
                width: 160,
                value: new Date().getTime(),
                format: 'Y-m-d',
                labelWidth: 30,
                value: Ext.Date.format(new Date(), 'Y-m-d'),
                itemId: 'sipDate',
                id: 'sipDate'
            },{
                text: 'Reload Data',
                handler: function () {

                    Ext.getCmp('sipsrc').el.dom.contentWindow.location.replace('http://baribarda.com/gant.php?date='+Ext.getCmp('sipDate').getValue().format('Y-m-d')+'&owner='+Ext.getCmp('sipstat_ResponsibleClients').getValue());
                   // var the_iframe =  window.items.items[0].getEl().dom;
                   // the_iframe.contentWindow.location.href = 'http://oilan.org/';
                    //the_iframe.contentWindow.location.reload();

                    console.log( Ext.getCmp('sipsrc'));
                }
            }],
            stateful: true,
            stateId: 'SipGantWindow',
            items : [{
                xtype : "component",
                id:'sipsrc',
                autoEl : {
                    id:'siparc',
                    tag : "iframe",
                    src : "http://baribarda.com/gant.php"
                }
            }]
        });
    }
    win.show();
}
function SipSessions(date, sip) {
    var win = Ext.getCmp('SipSessionsWindow' + sip);
    if (!win) {
        Ext.define('Point', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'inuse', type: 'numeric'},
                {name: 'time', type: 'date', dateFormat: 'Y-m-d H:i:s'}
            ]
        });

        store = new Ext.data.JsonStore({
            autoDestroy: true,
            autoLoad: true,
            model: 'Point',
            proxy: {
                type: 'ajax',
                url: 'handlers/get_session.php?date=' + date + '&sip=' + sip,
                reader: {
                    type: 'json',
                    root: 'data',
                    idProperty: 'id',
                    totalProperty: 'total',
                }
            },
            remoteSort: true,
            sortInfo: {
                field: 'start_date',
                direction: 'ASC'
            },
            storeId: 'myStoreSessions',
            fields: [
                {name: 'time', type: 'date', dateFormat: 'Y-m-d H:i:s'},
                {name: 'inuse', type: 'string'}
            ]
        });
        var lineChart = Ext.create('Ext.chart.Chart', {
            xtype: 'chart',
            style: 'background:#fff',
            animate: true,
            flex: 1,
            store: store,
            shadow: true,
            legend: {
                position: 'right'
            },
            axes: [{
                title: 'в регистрации',
                type: 'Numeric',
                minimum: 0,
                maximum: 2,
                position: 'left',
                fields: ['inuse'],
                grid: true,
                grid: {
                    odd: {
                        opacity: 1,
                        fill: '#ddd',
                        stroke: '#bbb',
                        'stroke-width': 0.5
                    }
                }
            }, {
                grid: true,
                type: 'Time',
                position: 'bottom',
                fields: ['time'],
                dateFormat: 'H:i',
                step: [Ext.Date.HOUR, 1],
                title: 'Активность по сип ' + sip
            }],
            series: [{
                type: 'line',
                highlight: {
                    size: 7,
                    radius: 7
                },
                axis: 'left',
                xField: ['time'],
                yField: ['inuse'],
                tips: {
                    trackMouse: true,
                    width: 140,
                    height: 28,
                    renderer: function (storeItem, item) {
                        this.setTitle(storeItem.get('inuse') + ': ' + storeItem.get('time').format('H-i'));
                    }
                },
                markerConfig: {
                    type: 'cross',
                    size: 4,
                    radius: 4,
                    'stroke-width': 0
                }
            }]
        });

        var win = new Ext.Window({
            id: 'SipSessionsWindow' + sip,
            width: 600,
            height: 400,
            maximized: true,
            title: 'Активность работы по сипy ' + sip + ' за ' + date,
            iconCls: 'staff-list',
            constrainHeader: true,
            plain: true,
            layout: 'fit',
            flex: true,
            tbar: [{
                text: 'Save Chart',
                handler: function () {
                    SipGant();
                    Ext.MessageBox.confirm('Confirm Download', 'Would you like to download the chart AS an image?', function (choice) {
                        if (choice == 'yes') {
                            lineChart.save({
                                type: 'image/png'
                            });
                        }
                    });
                }
            }, {
                text: 'Reload Data',
                handler: function () {
                    store.loadData();
                }
            }],
            stateful: true,
            stateId: 'SipSessionsWindow' + sip,
            items: [lineChart]
        });
    }
    win.show();
}

