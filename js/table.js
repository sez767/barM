DevExpress.localization.locale(navigator.language);
$(function() {
    $("#gridContainer").dxDataGrid({
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
        columns: [{
            dataField: "Id",
            width: 75
        }, {
            dataField: "fio",
            caption: "ФИО",
            width: 150
        }, {
            dataField: "phone",
            caption: "Телефон",
            dataType: "number",
            width: 120
        },{
            dataField: "SaleDate",
            caption: "Дата",
            dataType: "date",
            hidingPriority: 4,
            format: "yyyy-MM-dd",
            width: 100
        }]
    });
});


var collapsed = false;