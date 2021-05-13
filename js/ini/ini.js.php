<?php
require_once (dirname(__FILE__) . "/../../lib/db.php");

header('Content-type:application/javascript; charset=utf-8');

echo PHP_EOL . "var globalStaffId = {$_SESSION['Logged_StaffId']};";
echo PHP_EOL . 'var globalPresentInQuues = ' . (array_key_exists($_SESSION['Sip'], $GLOBAL_SIP_QUEUES_ASS) ? 'true' : 'false') . ';';
echo PHP_EOL . 'var globalSipId = ' . (int) $_SESSION['Sip'] . ';';
echo PHP_EOL . 'var globalProjectName = "' . SITE_NAME . '"; ' . PHP_EOL;
?>

// <script type="javascript">

    String.prototype.toHHMMSS = function () {
        var sec_num = parseInt(this, 10); // don't forget the second param
        var hours = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);
        if (hours < 10) {
            hours = "0" + hours;
        }
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        if (seconds < 10) {
            seconds = "0" + seconds;
        }
        var time = hours + ':' + minutes + ':' + seconds;
        return time;
    };
    // Array.unique( strict ) - Remove duplicate values
    //based on new indexOf defined above
    uniqueArr = function (b) {
        var a = [], i, len = b.length;
        for (i = 0; i < len; i++) {
            if (a.indexOf(b[i], 0, b) < 0) {
                a.push(b[i]);
            }
        }
        return a;
    };
    function nl2br(str) {
        return str.replace(/\r?\n/g, '$1<br/>');
        //return str.replace(/([^>])\n/g, '$1<br/>');
    }

    function in_array(value, array) {
        for (var i = 0; i < array.length; i++) {
            if (array[i] === value) {
                return true;
            }
        }
        return false;
    }

    objectKeys = function (obj) {
        if (typeof obj !== "object" && typeof obj !== "function" || obj === null) {
            throw TypeError("Object.keys called on non-object");
        }
        var keys = [];
        for (var p in obj) {
            obj.hasOwnProperty(p) && keys.push(p);
        }
        return keys;
    };
    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    function secondsToTime(secs) {
        var hours = Math.floor(secs / (60 * 60));
        var divisor_for_minutes = secs % (60 * 60);
        var minutes = Math.floor(divisor_for_minutes / 60);
        var divisor_for_seconds = divisor_for_minutes % 60;
        var seconds = Math.ceil(divisor_for_seconds);
        var obj = '' + hours + ':' + minutes + ':' + seconds;
        return obj;
    }

    function minuteToTime(minute) {
        var hours = Math.floor(minute / (60));
        var divisor_for_minutes = minute % (60);
        var minutes = Math.floor(divisor_for_minutes);
        var obj = '' + hours + ':' + minutes + '';
        return obj;
    }

    function sleep(n) {
        today = new Date();
        today2 = today;
        while ((today2 - today) <= n) {
            today2 = new Date();
        }
    }

    function explode(delimiter, string) {
        var emptyArray = {0: ''};
        if (arguments.length !== 2 || typeof arguments[0] === 'undefined' || typeof arguments[1] === 'undefined') {
            return null;
        }
        if (delimiter === '' || delimiter === false || delimiter === null) {
            return false;
        }
        if (typeof delimiter === 'function' || typeof delimiter === 'object' || typeof string === 'function' || typeof string === 'object') {
            return emptyArray;
        }
        if (delimiter === true) {
            delimiter = '1';
        }
        return string.toString().split(delimiter.toString());
    }

    function strpos(haystack, needle, offset) {
        var i = haystack.indexOf(needle, offset); // returns -1
        return i >= 0 ? i : false;
    }

    function openInNewTab(url) {
        var win = window.open(url, '_blank');
//        win.focus();
    }

    String.prototype.replaceAll = function (search, replacement) {
        var target = this;
        return target.split(search).join(replacement);
    };
    /////////////////
    String.prototype.capitalize = function (index) {
        index = isNaN(parseInt(index)) ? 0 : parseInt(index);
        return this.substr(0, index) + this.charAt(index).toUpperCase() + this.substr(index + 1);
    };
    function ucFirst(str, index) {
        return str.capitalize(index);
    }
    /////////////////
    String.prototype.unCapitalize = function (index) {
        index = isNaN(parseInt(index)) ? 0 : parseInt(index);
        return this.substr(0, index) + this.charAt(index).toLowerCase() + this.substr(index + 1);
    };
    function lcFirst(str, index) {
        return str.unCapitalize(index);
    }
    /////////////////
    String.prototype.isUpper = function (index) {
        index = isNaN(parseInt(index)) ? 0 : parseInt(index);
        return this.charAt(index).toUpperCase() === this.charAt(index);
    };
    function isUpper(str, index) {
        return str.isUpper(index);
    }
    /////////////////
    String.prototype.isLower = function (index) {
        index = isNaN(parseInt(index)) ? 0 : parseInt(index);
        return this.charAt(index).toLowerCase() === this.charAt(index);
    };
    function isLower(str, index) {
        return str.isLower(index);
    }
    /////////////////
    String.prototype.nl2br = function (is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (this + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    };
    function nl2br(str) {
        return str.nl2br();
    }
    /////////////////
    String.prototype.buildHash = function () {
        var ret = this;
        if (this.substring(0, 2) === '5*') {
            ret += '#' + md5(this).substring(0, 8).replace(/\D/g, '');
        }
        return ret;
    };
    function buildHash(str) {
        return str.buildHash();
    }
    /////////////////
    function md5(s){function L(k, d){return(k << d) | (k >>> (32 - d))}function K(G, k){var I, d, F, H, x; F = (G & 2147483648); H = (k & 2147483648); I = (G & 1073741824); d = (k & 1073741824); x = (G & 1073741823) + (k & 1073741823); if (I & d){return(x ^ 2147483648 ^ F ^ H)}if (I | d){if (x & 1073741824){return(x ^ 3221225472 ^ F ^ H)} else{return(x ^ 1073741824 ^ F ^ H)}} else{return(x ^ F ^ H)}}function r(d, F, k){return(d & F) | ((~d) & k)}function q(d, F, k){return(d & k) | (F & (~k))}function p(d, F, k){return(d ^ F ^ k)}function n(d, F, k){return(F ^ (d | (~k)))}function u(G, F, aa, Z, k, H, I){G = K(G, K(K(r(F, aa, Z), k), I)); return K(L(G, H), F)}function f(G, F, aa, Z, k, H, I){G = K(G, K(K(q(F, aa, Z), k), I)); return K(L(G, H), F)}function D(G, F, aa, Z, k, H, I){G = K(G, K(K(p(F, aa, Z), k), I)); return K(L(G, H), F)}function t(G, F, aa, Z, k, H, I){G = K(G, K(K(n(F, aa, Z), k), I)); return K(L(G, H), F)}function e(G){var Z; var F = G.length; var x = F + 8; var k = (x - (x % 64)) / 64; var I = (k + 1) * 16; var aa = Array(I - 1); var d = 0; var H = 0; while (H < F){Z = (H - (H % 4)) / 4; d = (H % 4) * 8; aa[Z] = (aa[Z] | (G.charCodeAt(H) << d)); H++}Z = (H - (H % 4)) / 4; d = (H % 4) * 8; aa[Z] = aa[Z] | (128 << d); aa[I - 2] = F << 3; aa[I - 1] = F >>> 29; return aa}function B(x){var k = "", F = "", G, d; for (d = 0; d <= 3; d++){G = (x >>> (d * 8)) & 255; F = "0" + G.toString(16); k = k + F.substr(F.length - 2, 2)}return k}function J(k){k = k.replace(/rn/g, "n"); var d = ""; for (var F = 0; F < k.length; F++){var x = k.charCodeAt(F); if (x < 128){d += String.fromCharCode(x)} else{if ((x > 127) && (x < 2048)){d += String.fromCharCode((x >> 6) | 192); d += String.fromCharCode((x & 63) | 128)} else{d += String.fromCharCode((x >> 12) | 224); d += String.fromCharCode(((x >> 6) & 63) | 128); d += String.fromCharCode((x & 63) | 128)}}}return d}var C = Array(); var P, h, E, v, g, Y, X, W, V; var S = 7, Q = 12, N = 17, M = 22; var A = 5, z = 9, y = 14, w = 20; var o = 4, m = 11, l = 16, j = 23; var U = 6, T = 10, R = 15, O = 21; s = J(s); C = e(s); Y = 1732584193; X = 4023233417; W = 2562383102; V = 271733878; for (P = 0; P < C.length; P += 16){h = Y; E = X; v = W; g = V; Y = u(Y, X, W, V, C[P + 0], S, 3614090360); V = u(V, Y, X, W, C[P + 1], Q, 3905402710); W = u(W, V, Y, X, C[P + 2], N, 606105819); X = u(X, W, V, Y, C[P + 3], M, 3250441966); Y = u(Y, X, W, V, C[P + 4], S, 4118548399); V = u(V, Y, X, W, C[P + 5], Q, 1200080426); W = u(W, V, Y, X, C[P + 6], N, 2821735955); X = u(X, W, V, Y, C[P + 7], M, 4249261313); Y = u(Y, X, W, V, C[P + 8], S, 1770035416); V = u(V, Y, X, W, C[P + 9], Q, 2336552879); W = u(W, V, Y, X, C[P + 10], N, 4294925233); X = u(X, W, V, Y, C[P + 11], M, 2304563134); Y = u(Y, X, W, V, C[P + 12], S, 1804603682); V = u(V, Y, X, W, C[P + 13], Q, 4254626195); W = u(W, V, Y, X, C[P + 14], N, 2792965006); X = u(X, W, V, Y, C[P + 15], M, 1236535329); Y = f(Y, X, W, V, C[P + 1], A, 4129170786); V = f(V, Y, X, W, C[P + 6], z, 3225465664); W = f(W, V, Y, X, C[P + 11], y, 643717713); X = f(X, W, V, Y, C[P + 0], w, 3921069994); Y = f(Y, X, W, V, C[P + 5], A, 3593408605); V = f(V, Y, X, W, C[P + 10], z, 38016083); W = f(W, V, Y, X, C[P + 15], y, 3634488961); X = f(X, W, V, Y, C[P + 4], w, 3889429448); Y = f(Y, X, W, V, C[P + 9], A, 568446438); V = f(V, Y, X, W, C[P + 14], z, 3275163606); W = f(W, V, Y, X, C[P + 3], y, 4107603335); X = f(X, W, V, Y, C[P + 8], w, 1163531501); Y = f(Y, X, W, V, C[P + 13], A, 2850285829); V = f(V, Y, X, W, C[P + 2], z, 4243563512); W = f(W, V, Y, X, C[P + 7], y, 1735328473); X = f(X, W, V, Y, C[P + 12], w, 2368359562); Y = D(Y, X, W, V, C[P + 5], o, 4294588738); V = D(V, Y, X, W, C[P + 8], m, 2272392833); W = D(W, V, Y, X, C[P + 11], l, 1839030562); X = D(X, W, V, Y, C[P + 14], j, 4259657740); Y = D(Y, X, W, V, C[P + 1], o, 2763975236); V = D(V, Y, X, W, C[P + 4], m, 1272893353); W = D(W, V, Y, X, C[P + 7], l, 4139469664); X = D(X, W, V, Y, C[P + 10], j, 3200236656); Y = D(Y, X, W, V, C[P + 13], o, 681279174); V = D(V, Y, X, W, C[P + 0], m, 3936430074); W = D(W, V, Y, X, C[P + 3], l, 3572445317); X = D(X, W, V, Y, C[P + 6], j, 76029189); Y = D(Y, X, W, V, C[P + 9], o, 3654602809); V = D(V, Y, X, W, C[P + 12], m, 3873151461); W = D(W, V, Y, X, C[P + 15], l, 530742520); X = D(X, W, V, Y, C[P + 2], j, 3299628645); Y = t(Y, X, W, V, C[P + 0], U, 4096336452); V = t(V, Y, X, W, C[P + 7], T, 1126891415); W = t(W, V, Y, X, C[P + 14], R, 2878612391); X = t(X, W, V, Y, C[P + 5], O, 4237533241); Y = t(Y, X, W, V, C[P + 12], U, 1700485571); V = t(V, Y, X, W, C[P + 3], T, 2399980690); W = t(W, V, Y, X, C[P + 10], R, 4293915773); X = t(X, W, V, Y, C[P + 1], O, 2240044497); Y = t(Y, X, W, V, C[P + 8], U, 1873313359); V = t(V, Y, X, W, C[P + 15], T, 4264355552); W = t(W, V, Y, X, C[P + 6], R, 2734768916); X = t(X, W, V, Y, C[P + 13], O, 1309151649); Y = t(Y, X, W, V, C[P + 4], U, 4149444226); V = t(V, Y, X, W, C[P + 11], T, 3174756917); W = t(W, V, Y, X, C[P + 2], R, 718787259); X = t(X, W, V, Y, C[P + 9], O, 3951481745); Y = K(Y, h); X = K(X, E); W = K(W, v); V = K(V, g)}var i = B(Y) + B(X) + B(W) + B(V); return i.toLowerCase()};
    String.prototype.md5 = function () {
        return md5(this);
    };
    /////////////////
    Array.prototype.clone = function () {
        return this.slice(0);
    };
