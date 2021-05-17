DevExpress.localization.locale(navigator.language);
$(function() {
     $("#gridCon").dxDataGrid({
            dataSource: {
                store: {
                    type: "odata",
                    url: "https://js.devexpress.com/Demos/SalesViewer/odata/DaySaleDtoes",
                //     beforeSend: function(request) {
                //         request.params.startDate = "2020-05-10";
                //         request.params.endDate = "2020-05-25";
                //     }
                }
            },
            showBorders: true,
            customizeColumns: function (columns) {
                columns[0].width = 70;
            },
            loadPanel: {
                enabled: true
            },
            scrolling: {
                mode: "virtual"
            },
            sorting: {
                mode: "none"
            },
            onContentReady: function(e) {
                e.component.option("loadPanel.enabled", false);
            }
        });
    
    });