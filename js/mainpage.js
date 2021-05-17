$(function() {
    $("#view").load( "./pages/main.html" );

    $("#toolbar").dxToolbar({
        items: [{
            widget: "dxButton",
            location: "before",
            options: {
                icon: "menu",
                onClick: function() {
                    drawer.toggle();
                }
            }
        }]
    });

    const drawer = $("#drawer").dxDrawer({
        minSize: 37,
        opened: true,
        revealMode: "expand",
        openedStateMode: "shrink",
        template: function() {
            const $list = $("<div/>").dxList({
                items: [
                    { id: 1, text: "Главная", icon: "check", filePath: "main" },
                    { id: 2, text: "Таблица", icon: "message", filePath: "table" },
   
                ],
                width: 200,
                height: "93vh",
                selectionMode: "single",
                onSelectionChanged: function(e) {
                    $("#view").load( "./pages/" + e.addedItems[0].filePath + ".html" );
                    // drawer.hide();
                }
            });
            return $list;
        }
    }).dxDrawer("instance");

}); 