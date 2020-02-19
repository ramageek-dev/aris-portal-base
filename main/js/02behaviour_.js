function getAllChildren(a) {
    return a.all ? a.all : a.getElementsByTagName("*")
}
var Behaviour = {
    list: new Array,
    register: function (a) {
        Behaviour.list.push(a)
    },
    start: function () {
        Behaviour.addLoadEvent(function () {
            Behaviour.apply()
        })
    },
    apply: function () {
        for (h = 0; sheet = Behaviour.list[h]; h++) {
            for (selector in sheet) {
                list = document.getElementsBySelector(selector);
                if (!list) {
                    continue
                }
                for (i = 0; element = list[i]; i++) {
                    sheet[selector](element)
                }
            }
        }
    },
    addLoadEvent: function (a) {
        var b = window.onload;
        if (typeof window.onload != "function") {
            window.onload = a
        } else {
            window.onload = function () {
                b();
                a()
            }
        }
    }
};
Behaviour.start();
document.getElementsBySelector = function (a) {
    if (!document.getElementsByTagName) {
        return new Array
    }
    var b = a.split(" ");
    var c = new Array(document);
    for (var d = 0; d < b.length; d++) {
        token = b[d].replace(/^\s+/, "").replace(/\s+$/, "");
        if (token.indexOf("#") > -1) {
            var e = token.split("#");
            var f = e[0];
            var g = e[1];
            var h = document.getElementById(g);
            if (f && h.nodeName.toLowerCase() != f) {
                return new Array
            }
            c = new Array(h);
            continue
        }
        if (token.indexOf(".") > -1) {
            var e = token.split(".");
            var f = e[0];
            var i = e[1];
            if (!f) {
                f = "*"
            }
            var j = new Array;
            var k = 0;
            for (var l = 0; l < c.length; l++) {
                var m;
                if (f == "*") {
                    m = getAllChildren(c[l])
                } else {
                    m = c[l].getElementsByTagName(f)
                }
                for (var n = 0; n < m.length; n++) {
                    j[k++] = m[n]
                }
            }
            c = new Array;
            var o = 0;
            var bypassClass = ["SVGAnimatedString"];
            for (var p = 0; p < j.length; p++) {
            	if(bypassClass.some(function(v){ return Object.prototype.toString.call(j[p].className).indexOf(v) >= 0;})) continue;
        		if (j[p].className && j[p].className.match(new RegExp("\\b" + i + "\\b"))) {
        			c[o++] = j[p]
        		}
            }
            continue
        }
        if (token.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/)) {
            var f = RegExp.$1;
            var q = RegExp.$2;
            var r = RegExp.$3;
            var s = RegExp.$4;
            if (!f) {
                f = "*"
            }
            var j = new Array;
            var k = 0;
            for (var l = 0; l < c.length; l++) {
                var m;
                if (f == "*") {
                    m = getAllChildren(c[l])
                } else {
                    m = c[l].getElementsByTagName(f)
                }
                for (var n = 0; n < m.length; n++) {
                    j[k++] = m[n]
                }
            }
            c = new Array;
            var o = 0;
            var t;
            switch (r) {
            case "=":
                t = function (a) {
                    return a.getAttribute(q) == s
                };
                break;
            case "~":
                t = function (a) {
                    return a.getAttribute(q).match(new RegExp("\\b" + s + "\\b"))
                };
                break;
            case "|":
                t = function (a) {
                    return a.getAttribute(q).match(new RegExp("^" + s + "-?"))
                };
                break;
            case "^":
                t = function (a) {
                    return a.getAttribute(q).indexOf(s) == 0
                };
                break;
            case "$":
                t = function (a) {
                    return a.getAttribute(q).lastIndexOf(s) == a.getAttribute(q).length - s.length
                };
                break;
            case "*":
                t = function (a) {
                    return a.getAttribute(q).indexOf(s) > -1
                };
                break;
            default:
                t = function (a) {
                    return a.getAttribute(q)
                }
            }
            c = new Array;
            var o = 0;
            for (var p = 0; p < j.length; p++) {
                if (t(j[p])) {
                    c[o++] = j[p]
                }
            }
            continue
        }
        if (!c[0]) {
            return
        }
        f = token;
        var j = new Array;
        var k = 0;
        for (var l = 0; l < c.length; l++) {
            var m = c[l].getElementsByTagName(f);
            for (var n = 0; n < m.length; n++) {
                j[k++] = m[n]
            }
        }
        c = j
    }
    return c
};