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
        filterRow: {
            visible: true
        },
        paging: {
            pageSize: 20
        },
        headerFilter: {
            visible: true
        },
        groupPanel: {
            visible: true
        },
        scrolling: {
            rowRenderingMode: 'virtual'
        },
        height: 600,
        showBorders: true,
        grouping: {
            autoExpandAll: false
        },
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