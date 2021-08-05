$(function() {
    $("#view").load( "./pages/main.html" );
    const width  = $(window).width();
 
    if(width < 600){
        $("#toolbar").dxToolbar({
            items: [
                {
                    widget: "dxButton",
                    location: "after",
                    options: {
                        icon: "close",
                        onClick: function() {
                            window.location = 'logout.php';
                        }
                    }
                }
            ]
        });

        let drawer = $("#drawer").dxDrawer({
        minSize: 50,
        position: "bottom",
        revealMode: "expand",
        openedStateMode: "shrink",
        template: function() {
            const $list = $("<div/>").dxList({
                items: [
                    {id: 0, icon: "menu" },
                    { id: 1, text: "Главная", icon: "home", filePath: "main" },
                    { id: 2, text: "Таблица", icon: "verticalaligntop", filePath: "table" },
                    { id: 3, text: "Test1", icon: "home", filePath: "main" },
                    { id: 4, text: "Test2", icon: "home", filePath: "main" },
                    { id: 5, text: "Test3", icon: "home", filePath: "main" },
                    { id: 6, text: "Test4", icon: "home", filePath: "main" },                ],
                width: "100%",
                selectionMode: "single",
                onSelectionChanged: function(e) {
                    if(e.addedItems[0].id != 0){
                        $("#view").load( "./pages/" + e.addedItems[0].filePath + ".html" );
                        drawer.hide();
                        console.log('hideeeeee', e);
                    } else{
                        drawer.show();
                        console.log('showwwwwwwww', e);
                    }
                }
            });
            return $list;
        }
        }).dxDrawer("instance");
    }else{
        $("#toolbar").dxToolbar({
            items: [
                {
                    widget: "dxButton",
                    location: "before",
                    options: {
                        icon: "menu",
                        onClick: function() {
                            drawer.toggle();
                        }
                    }
                },
                {
                    widget: "dxButton",
                    location: "after",
                    options: {
                        icon: "close",
    
                        onClick: function() {
                            window.location = 'logout.php';
                        }
                    }
                }
            ]
        });
        let drawer = $("#drawer").dxDrawer({
            minSize: 35,
            opened: true,
            revealMode: "expand",
            openedStateMode: "shrink",
            template: function() {
                const $list = $("<div/>").dxList({
                    items: [
                        { id: 1, text: "Главная", icon: "home", filePath: "main" },
                        { id: 2, text: "Таблица", icon: "verticalaligntop", filePath: "table" },
                        { id: 3, text: "Test1", icon: "home", filePath: "main" },
                        { id: 4, text: "Test2", icon: "home", filePath: "main" },
                        { id: 5, text: "Test3", icon: "home", filePath: "main" },
                        { id: 6, text: "Test4", icon: "home", filePath: "main" },
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
    }


}); 