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
        // headerFilter: {
        //     visible: true,
        // },
        // grouping: {
        //     contextMenuEnabled: true
        // },
        wordWrapEnabled: true,
        showBorders: true,
        filterRow: {
            visible: true,
            applyFilter: "auto"
        },
        sorting: {
            mode: "single" // or "multiple" | "none"
        },
        searchPanel: {
            visible: true,
            width: 240,
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
            width: 100, 
        }, {
            dataField: "fio",
            caption: "ФИО",
            dataType: "string",
            width: 160
        }, {
            dataField: "status",
            caption: "Статус",
            dataType: "string",
            width: 100,
            hidingPriority: 4,
        }, {
            dataField: "addr",
            caption: "Адрес",
            dataType: "string",
            hidingPriority: 1,
            width: 300
        },{
            dataField: "country",
            caption: "Локация",
            dataType: "string",
            width: 100,
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