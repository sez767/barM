DevExpress.localization.locale(navigator.language);
$(function() {
    $("#gridCon").dxDataGrid({
        dataSource: {
            store: {
                type: "odata",
                url: '/handlers/get_menu_obzvon.php',
                // beforeSend: function(request) {
                //     request.params.startDate = "2020-05-10";
                //     request.params.endDate = "2020-05-13";
                // }
            }
        },
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
        remoteOperations: false,
        searchPanel: {
            visible: true,
            highlightCaseSensitive: true
        },
        groupPanel: { visible: true },
        grouping: {
            autoExpandAll: false
        },
        allowColumnReordering: true,
        rowAlternationEnabled: true,
        showBorders: true,

        export: {
            enabled: true,
            allowExportSelectedData: true
          },
          onExporting: function(e) {
            var workbook = new ExcelJS.Workbook();
            var worksheet = workbook.addWorksheet('Employees');
            
            DevExpress.excelExporter.exportDataGrid({
              component: e.component,
              worksheet: worksheet,
              autoFilterEnabled: true
            }).then(function() {
              workbook.xlsx.writeBuffer().then(function(buffer) {
                saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'Employees.xlsx');
              });
            });
            e.cancel = true;
          },
        columns: [
            {
                dataField: "id",
                caption: "id",
            },
            {
                dataField: "Product",
                caption: "Товар",
            },
            {
                dataField: "Amount",
                caption: "Цена",
                dataType: "number",
                format: "currency",
                alignment: "right",
            },
            {
                dataField: "Discount",
                caption: "Скидка %",
                dataType: "number",
                format: "percent",
                alignment: "right",
                allowGrouping: false,
                cellTemplate: discountCellTemplate,
                cssClass: "bullet",
                hidingPriority: 0
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