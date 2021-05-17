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
        paging: {
            pageSize: 10
        },
        headerFilter: {
            visible: true
        },
        // pager: {
        //     showPageSizeSelector: true,
        //     allowedPageSizes: [10, 25, 50, 100]
        // },
        remoteOperations: false,
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
            mode: "virtual"
        },
        sorting: {
            mode: "none"
        },
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