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
        showBorders: true,
            loadPanel: {
                enabled: true
            },
            scrolling: {
                mode: "virtual"
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
            {
                dataField: "Sector",
                caption: "Вид",
                dataType: "string",
                hidingPriority: 3
            },
            {
                dataField: "Channel",
                caption: "Канал",
                dataType: "string",
                hidingPriority: 2
            },
            {
                dataField: "Customer",
                caption: "Клиент",
                dataType: "string",
                width: 150
            }
        ],
        onContentReady: function(e) {
            if(!collapsed) {
                collapsed = true;
                e.component.expandRow(["EnviroCare"]);
            }
        }
    });
});

var discountCellTemplate = function(container, options) {
    $("<div/>").dxBullet({
        onIncidentOccurred: null,
        size: {
            width: 150,
            height: 35
        },
        margin: {
            top: 5,
            bottom: 0,
            left: 5
        },
        showTarget: false,
        showZeroLevel: true,
        value: options.value * 100,
        startScaleValue: 0,
        endScaleValue: 100,
        tooltip: {
            enabled: true,
            font: {
                size: 18
            },
            paddingTopBottom: 2,
            customizeTooltip: function() {
                return { text: options.text };
            },
            zIndex: 5
        }
    }).appendTo(container);
};

var collapsed = false;