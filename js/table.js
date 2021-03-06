DevExpress.localization.locale(navigator.language);
$(function() {
    $("#gridCon").dxDataGrid({
        dataSource: DevExpress.data.AspNet.createStore({
            key: "id",
            loadUrl: "/handlers/get_menu_obzvon.php"
        }),
        editing: {
            mode: "form",
            form: {
                colCount: 1,
                items: [ 
                        { dataField: "id",
                            editorOptions: { 
                                readOnly: true
                            }
                        },
                        {dataField: "is_cold_staff_id",
                            editorOptions: { 
                                readOnly: true
                            }
                        },
                        { dataField: "fio" },
                        { dataField: "index"},
                        { dataField: "district" },
                        { dataField: "city_region" },
                        { dataField: "addr" },
                        { dataField: "street" },

                        { dataField: "building" },
                        { dataField: "flat" },
                        { dataField: "not_rus", editorType: "dxCheckBox"},
                        { dataField: "client_worries" },
                        { dataField: "status" },
                        { dataField: "offer",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: DevExpress.data.AspNet.createStore({
                                    key: "id",
                                    loadUrl: "/handlers/get_OfferProperties.php"
                                }), 
                                valueExpr: "Name",
                                displayExpr: "Display",
                                searchEnabled: true,
                                value: ""
                            },
                        },

                        { dataField: "country",
                            editorType: "dxSelectBox",
                            editorOptions: {
                                dataSource: countries,
                                valueExpr: "Name",
                                displayExpr: "Display",
                                searchEnabled: true,
                                value: ""
                            },
                        },
                        {dataField: "date"}    
                        
                ]
            }
        },
        selection: {
            mode: "single"
        },
        onRowClick: function(e) {
            if(e.rowType === "data") {
                e.component.editRow(e.rowIndex);
            }
        },
        remoteOperations: true,
        scrolling: {
            mode: "virtual",
            rowRenderingMode: "virtual"
        },
        paging: {
            pageSize: 100
        },
        wordWrapEnabled: true,
        showBorders: true,
        filterRow: {
            visible: true,
            applyFilter: "auto"
        },
        sorting: {
            mode: "single"
        },
        searchPanel: {
            visible: true,
            width: 300,
            placeholder: "??????????..."
        },
        onCellPrepared: function (e) {  
            if (e.column.dataField === "id") {  
                if (e.rowType == 'header') {  
                    e.cellElement.find(".dx-header-filter").hide();  
                }  
            }  
        },  
        columns: [{
            dataField: "id",
            caption: "ID",
            dataType: "number",
            width: 80, 
        }, {
            dataField: "fio",
            caption: "??????",
            dataType: "string",
            width: 120
        },{
            dataField: "addr",
            caption: "??????????",
            dataType: "string",
            hidingPriority: 2,
            width: 220
        },{
            dataField: "street",
            caption: "??????????",
            visible: false

        },{
            dataField: "offer",
            caption: "??????????????",
            hidingPriority: 2,
            width: 120
        },
        {
            dataField: "package",
            caption: "????????????????????",
            dataType: "number",
            hidingPriority: 2,
            width: 80,

        },
        {
            dataField: "dop_tovar",
            cellTemplate: function(element, info) {
                if (info.values[5]){
                // element.append("<div>" + info.values[5] + "</div>");
                element.append("<div>????</div>");
                }else{
                    element.append("<div></div>");
                }
           },
            caption: "?????? ??????????",
            hidingPriority: 3,
            width: 80,

        },
        {
            dataField: "total_price",
            caption: "????????",
            hidingPriority: 2,
            width: 80
        },
        {
            dataField: "status",
            caption: "????????????",
            dataType: "string",
            width: 100,
            hidingPriority: 4,
        },
        {
            dataField: "status_kz",
            caption: "???????????? ??????????????",
            dataType: "string",
            width: 80,
            hidingPriority: 4,
        },
        {
            dataField: "kz_delivery",
            caption: "?????? ????????????????",
            dataType: "string",
            width: 80,
            hidingPriority: 4,
        },
        {
            dataField: "problem",
            caption: "????????????????",
            dataType: "string",
            hidingPriority: 4,
        },
        {
            dataField: "is_cold",
            caption: "???????????????? ????????????",
            width: 50,
            hidingPriority: 4,
        },
        {
            dataField: "cold_group",
            caption: "???????????????? ??????????",
            width: 50,
            hidingPriority: 4,
        },
        {
            dataField: "country",
            caption: "??????????????",
            dataType: "string",
            width: 80,
            hidingPriority: 2,
            visible: false
        },{
            dataField: "date",
            caption: "????????",
            dataType: "date",
            hidingPriority: 3,
            format: "dd-MM-yyyy",
            visible: false
        },{
            dataField: "index",
            caption: "????????????",
            visible: false

        },
        {
            dataField: "city_region",
            caption: "?????????? ????????????",
            visible: false

        },
        {
            dataField: "is_cold_staff_id",
            caption: "????????????????",
            visible: false

        },
        {
            dataField: "district",
            caption: "??????????",
            visible: false

        },
        {
            dataField: "city",
            caption: "??????????",
            visible: false

        },
        {
            dataField: "building",
            caption: "??????",
            visible: false

        },
        {
            dataField: "flat",
            caption: "????????????????",
            visible: false

        },
        {
            dataField: "not_rus",
            caption: "???? ???????????????? RU",
            visible: false

        },
        {
            dataField: "client_worries",
            caption: "????????????/???????????????????????? ??????????????",
            visible: false

        },
        {
            dataField: "pre_price",
            caption: "??????????????????",
            visible: false

        },
        {
            dataField: "post_price",
            caption: "????????????????",
            visible: false

        },
        
    
    ]
    });
});


var collapsed = false;

var countries = [
    {
    Name: 'kz',
    Display: '??????????????????'
    }, {
    Name: 'kzg',
    Display: '????????????????'
    }, {
    Name: 'uz',
    Display: '????????????????????'
    }, {
    Name: 'az',
    Display: '??????????????????????'
    }, {
    Name: 'md',
    Display: '??????????????'
    }, {
    Name: 'am',
    Display: 'OAE'
    }
]
