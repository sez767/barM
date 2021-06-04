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
        headerFilter: {
            visible: true,
            allowSearch: true
        },
        wordWrapEnabled: true,
        showBorders: true,
        paging: {
            pageSize: 10
        },
        filterRow: {
            visible: true,
            applyFilter: "auto"
        },
        searchPanel: {
            visible: true,
            width: 240,
            placeholder: "Search..."
        },
        headerFilter: {
            visible: true
        },
        pager: {
            showPageSizeSelector: true,
            allowedPageSizes: [10, 25, 50, 100]
        },


        // dataSource: {
        //     store: {
        //         type: "odata",
        //         url: "/handlers/get_menu_obzvon.php",
        //     }
        // },
        // paging: {
        //     pageSize: 10
        // },
        // filterRow: {
        //     visible: true,
        //     applyFilter: "auto"
        // },
        // searchPanel: {
        //     visible: true,
        //     width: 240,
        //     placeholder: "Search..."
        // },
        // headerFilter: {
        //     visible: true
        // },
        // pager: {
        //     showPageSizeSelector: true,
        //     allowedPageSizes: [10, 25, 50, 100]
        // },
        // remoteOperations: false,
        // searchPanel: {
        //     visible: true,
        //     highlightCaseSensitive: true
        // },
        // groupPanel: { visible: true },
        // grouping: {
        //     autoExpandAll: false
        // },
        // allowColumnReordering: true,
        // rowAlternationEnabled: true,
        // showBorders: true,




        columns: [{
            dataField: "id",
            caption: "ID",
            width: 100
        }, {
            dataField: "fio",
            caption: "ФИО",
            width: 200
        }, {
            dataField: "status",
            caption: "Статус",
            dataType: "number",
            width: 100
        }, {
            dataField: "phone",
            caption: "Телефон",
            dataType: "number",
        },{
            dataField: "date",
            caption: "Дата",
            dataType: "date",
            hidingPriority: 4,
            format: "dd-MM-yyyy",
        }]
    });
});


var collapsed = false;