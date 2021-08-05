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
            placeholder: "Поиск..."
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
            caption: "ФИО",
            dataType: "string",
            width: 120
        },{
            dataField: "addr",
            caption: "Адрес",
            dataType: "string",
            hidingPriority: 2,
            width: 220
        },{
            dataField: "street",
            caption: "Улица",
            visible: false

        },{
            dataField: "offer",
            caption: "Продукт",
            hidingPriority: 2,
            width: 120
        },
        {
            dataField: "package",
            caption: "Количество",
            dataType: "number",
            hidingPriority: 2,
            width: 80,

        },
        {
            dataField: "dop_tovar",
            cellTemplate: function(element, info) {
                if (info.values[5]){
                // element.append("<div>" + info.values[5] + "</div>");
                element.append("<div>Да</div>");
                }else{
                    element.append("<div></div>");
                }
           },
            caption: "Доп товар",
            hidingPriority: 3,
            width: 80,

        },
        {
            dataField: "total_price",
            caption: "Цена",
            hidingPriority: 2,
            width: 80
        },
        {
            dataField: "status",
            caption: "Статус",
            dataType: "string",
            width: 100,
            hidingPriority: 4,
        },
        {
            dataField: "status_kz",
            caption: "Статус посылки",
            dataType: "string",
            width: 80,
            hidingPriority: 4,
        },
        {
            dataField: "kz_delivery",
            caption: "Тип доставки",
            dataType: "string",
            width: 80,
            hidingPriority: 4,
        },
        {
            dataField: "problem",
            caption: "Проблема",
            dataType: "string",
            hidingPriority: 4,
        },
        {
            dataField: "is_cold",
            caption: "Холодный статус",
            width: 50,
            hidingPriority: 4,
        },
        {
            dataField: "cold_group",
            caption: "Холодная група",
            width: 50,
            hidingPriority: 4,
        },
        {
            dataField: "country",
            caption: "Локация",
            dataType: "string",
            width: 80,
            hidingPriority: 2,
            visible: false
        },{
            dataField: "date",
            caption: "Дата",
            dataType: "date",
            hidingPriority: 3,
            format: "dd-MM-yyyy",
            visible: false
        },{
            dataField: "index",
            caption: "Индекс",
            visible: false

        },
        {
            dataField: "city_region",
            caption: "Район города",
            visible: false

        },
        {
            dataField: "is_cold_staff_id",
            caption: "Источник",
            visible: false

        },
        {
            dataField: "district",
            caption: "Район",
            visible: false

        },
        {
            dataField: "city",
            caption: "Город",
            visible: false

        },
        {
            dataField: "building",
            caption: "Дом",
            visible: false

        },
        {
            dataField: "flat",
            caption: "Квартира",
            visible: false

        },
        {
            dataField: "not_rus",
            caption: "Не понимает RU",
            visible: false

        },
        {
            dataField: "client_worries",
            caption: "Недуги/беспокойства клиента",
            visible: false

        },
        {
            dataField: "pre_price",
            caption: "Рассрочка",
            visible: false

        },
        {
            dataField: "post_price",
            caption: "Осстаток",
            visible: false

        },
        
    
    ]
    });
});


var collapsed = false;

var countries = [
    {
    Name: 'kz',
    Display: 'Казакстан'
    }, {
    Name: 'kzg',
    Display: 'Киргизия'
    }, {
    Name: 'uz',
    Display: 'Узбекистан'
    }, {
    Name: 'az',
    Display: 'Азербайджан'
    }, {
    Name: 'md',
    Display: 'Молдова'
    }, {
    Name: 'am',
    Display: 'OAE'
    }
]
