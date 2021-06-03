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
            pageSize: 25
        },
        headerFilter: {
            visible: true,
            allowSearch: true
        },
        wordWrapEnabled: true,
        showBorders: true,
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