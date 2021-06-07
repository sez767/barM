DevExpress.localization.locale(navigator.language);
$(function() {
    $("#gridCon").dxDataGrid({
        dataSource: DevExpress.data.AspNet.createStore({
            key: "id",
            loadUrl: "/handlers/get_menu_obzvon.php"
        }),
        remoteOperations: true,
        scrolling: {
            mode: "virtual",
            rowRenderingMode: "virtual"
        },
        paging: {
            pageSize: 100
        },
        headerFilter: {
            visible: true,
            allowSearch: true
        },
        wordWrapEnabled: true,
        showBorders: true,
        filterRow: {
            visible: true,
            applyFilter: "auto"
        },
        searchPanel: {
            visible: true,
            width: 240,
            placeholder: "Поиск..."
        },
        headerFilter: {
            visible: true
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
            width: 100, 
        }, {
            dataField: "fio",
            caption: "ФИО",
            width: 160
        }, {
            dataField: "status",
            caption: "Статус",
            dataType: "number",
            width: 80,
            hidingPriority: 4,
        }, {
            dataField: "addr",
            caption: "Адрес",
            hidingPriority: 2,
        },{
            dataField: "country",
            caption: "Локация",
            hidingPriority: 2,
        },
        {
            dataField: "date",
            caption: "Дата",
            dataType: "date",
            hidingPriority: 3,
            format: "dd-MM-yyyy",
        }]
    });
});


var collapsed = false;