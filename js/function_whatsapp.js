/////////////////////////////////
// whatsApp

function whatsAppChat(id, phone) {
    if (globalStaffId === 11111111) {
//         DOBRIK
//        phone = '380971888840'; // Serega
//        phone = '380637411787'; // Kolya
        phone = '380634591717'; // Dobrik
//        phone = '380631812992'; // Vlad
    }

    var wChatContainer = Ext.getCmp('WhatsAppPanel');
    var wChatPan = Ext.getCmp('whatschat_' + phone);
    if (!wChatPan) {
        if (wChatContainer) {
            wChatContainer.insert(0, {
                title: 'Panel: <b>' + id + '</b>',
                id: 'whatschat_' + phone,
                orderId: id,
                html: '<iframe style="overflow:auto;width:100%;height:100%;" frameborder="0"  src="http://call.baribarda.com/call.baribarda.com/web-sockets/Applications/WhatsApp/Web/index.php?userName=' + session_Logged_StaffName + '&hash=' + session_Logged_StaffName.md5() + '&id=' + globalStaffId + '&room_id=' + phone + '"></iframe>'
            });
        }
    }

    wChatPan = Ext.getCmp('whatschat_' + phone);
    if (wChatPan.getCollapsed()) {
        wChatPan.toggleCollapse();
    }

    if (wChatContainer.getCollapsed()) {
        wChatContainer.toggleCollapse();
    }
}

function storeWhatsAppDialogs() {
    var whatsChatObj = {};
    var wChatContainer = Ext.getCmp('WhatsAppPanel');
    if (wChatContainer) {
        Ext.Array.each(Ext.ComponentQuery.query('panel', wChatContainer), function (item) {
            var phoneArr = item.id.split('_');
            whatsChatObj[item.orderId] = phoneArr[1];
        });
        Ext.util.Cookies.set('WhatsAppDialogs', Ext.JSON.encode(whatsChatObj));
    }
}

// Обработка события из iframe
var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
eventer = window[eventMethod];
var messageEvent = eventMethod === "attachEvent" ? "onmessage" : "message";
eventer(messageEvent, function (e) {
    if (typeof e.data === 'string') {
        if (e.data.indexOf('chatEvent') > -1) {
            var chatPanel = Ext.getCmp('chatPanel');
            if (chatPanel.collapsed) {
                if (globalChatTaskStatus === false) {
                    Ext.TaskManager.start(chatTask);
                    globalChatTaskStatus = true;
                }
            } else {
                if (globalChatTaskStatus === true) {
                    Ext.TaskManager.stop(chatTask);
                    globalChatTaskStatus = false;
                }
            }
        } else if (e.data.indexOf('whatsAppEvent') > -1) {
            if (e.data.indexOf('_') > -1) {
                var phoneArr = e.data.split('_');
                var wChatPan = Ext.getCmp('whatschat_' + phoneArr[1]);
                if (wChatPan) {
                    if (wChatPan.title.indexOf('#') < 0) {
                        wChatPan.setTitle('##' + wChatPan.title + '##');
                        return;
                    }
                }
            } else if (e.data === 'whatsAppEvent') {
                storeWhatsAppDialogs();
            }
        }
    }
});
// Обработка события из iframe END
// whatsApp END
///////////////////////////////


WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf";
WEB_SOCKET_DEBUG = true;
var ws, client_list = {};
var name = session_Logged_StaffName;

function connect() {
    ws = new WebSocket('ws://call.baribarda.com:7575');
    ws.onopen = onopen;
    ws.onmessage = onmessage;
    ws.onclose = function () {
        console.log("Соединение закрыто, пересоединение");
        connect();
    };
    ws.onerror = function () {
        console.log("Произошла ошибка");
    };
}

function onopen() {
    var login_data = '{"type":"login","client_name":"' + name.replace(/"/g, '\\"') + '","ext_client_id":"' + globalStaffId + '","room_id":"-1"}';
//    console.log("WebSocket регистрация, данные:" + login_data);
    ws.send(login_data);
}

function onmessage(e) {
    var data = JSON.parse(e.data);
    switch (data['type']) {
        case 'ping':
            ws.send('{"type":"pong"}');
            break;
        case 'login':
            if (data['client_list']) {
                client_list = data['client_list'];
            } else {
                client_list[data['client_id']] = data['client_name'];
            }
            if (data['message_history']) {
            }
            break;
        case 'say':
            if (data['to_giper_group']) {
                whatsAppChat(-1, data['to_giper_group']);
            }
            if (!data['from_client_name'].match(/\D/)) {
            }
            break;
        case 'logout':
            delete client_list[data['from_client_id']];
            break;
    }
}

if (session_whatsappoperator) {
    connect();
}