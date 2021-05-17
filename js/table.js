DevExpress.localization.locale(navigator.language);
$(function() {
    $("#gridCon").dxDataGrid({
        dataSource: {
            store: {
                type: "odata",
                url: '/handlers/get_menu_obzvon.php',
                // beforeSend: function(request) {
                //     request.params.startDate = "2020-05-10";
                // }
            }
        },
        // dataSource: DevExpress.data.AspNet.createStore({
        //     key: "id",
        //     loadUrl: "/handlers/get_menu_obzvon.php"
        // }),
        paging: {
            pageSize: 10
        },
        headerFilter: {
            visible: true
        },
        pager: {
            showPageSizeSelector: true,
            allowedPageSizes: [10, 25, 50, 100]
        },
        remoteOperations: true,
        searchPanel: {
            visible: true,
            highlightCaseSensitive: true
        },
        groupPanel: { visible: false},
        grouping: {
            autoExpandAll: false
        },
        allowColumnReordering: true,
        rowAlternationEnabled: true,
        showBorders: true,
        scrolling: {
            mode: "virtual",
            rowRenderingMode: "virtual"
        },
        sorting: {
            mode: "none"
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