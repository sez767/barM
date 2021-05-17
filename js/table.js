DevExpress.localization.locale(navigator.language);
$(function() {
    $("#gridCon").dxDataGrid({
        // dataSource: {
        //     store: {
        //         type: "odata",
        //         url: '/handlers/get_menu_obzvon.php',
        //     }
        // },
        dataSource: DevExpress.data.AspNet.createStore({
            key: "id",
            loadUrl: "/handlers/get_menu_obzvon.php"
        }),
        
        headerFilter: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        pager: {
            showPageSizeSelector: true,
            allowedPageSizes: [10, 20, 50],
            showInfo: true
        },
        remoteOperations: true,
        searchPanel: {
            visible: true,
            highlightCaseSensitive: true
        },
        groupPanel: { visible: true},
        allowColumnReordering: true,
        rowAlternationEnabled: true,
        showBorders: true,
        scrolling: {
            mode: "virtual",
        },
        
        columns: [
            {
                dataField: "id",
                caption: "id",

            },
            {
                dataField: "fio",
                caption: "ФИО",
            },
            {
                dataField: "phone",
                caption: "Телефон",
                dataType: "number",
            },
            {
                dataField: "addr",
                caption: "Адрес",

            },
            {
                dataField: "SaleDate",
                caption: "Дата",
                dataType: "date",
                hidingPriority: 4
            },
            {
                dataField: "Region",
                caption: "Регион",
                dataType: "string",
                hidingPriority: 1
            },
  
        ],
        onContentReady: function(e) {
            if(!collapsed) {
                collapsed = true;
                e.component.expandRow(["EnviroCare"]);
            }
        }
    });
});

var collapsed = false;