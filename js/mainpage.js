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
        minSize: 35,
        opened: true,
        revealMode: "expand",
        openedStateMode: "shrink",
        template: function() {
            const $list = $("<div/>").dxList({
                items: [
                    { id: 1, text: "Главная", icon: "home", filePath: "main" },
                    { id: 2, text: "Таблица", icon: "verticalaligntop", filePath: "table" },
   
                ],
                width: 150,
                selectionMode: "single",
                onSelectionChanged: function(e) {
                    $("#view").load( "./pages/" + e.addedItems[0].filePath + ".html" );
                }
            });
            return $list;
        }
    }).dxDrawer("instance");

    const width  = $(window).width();
    if(width < 600){
        drawer.hide();
    }

}); 