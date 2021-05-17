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
        height: '600px',
        columns: [
            {
                dataField: "id",
                caption: "id",
                width: "100px"

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