/*
   Magic Zoom Plus v4.5.17 DEMO
   Copyright 2013 Magic Toolbox
   Buy a license: www.magictoolbox.com/magiczoomplus/
   License agreement: http://www.magictoolbox.com/license/
*/
(function () {
    if (window.magicJS) {
        return
    }
    var b = {
        version: "v2.7.4",
        UUID: 0,
        storage: {},
        $uuid: function (d) {
            return (d.$J_UUID || (d.$J_UUID = ++a.UUID))
        },
        getStorage: function (d) {
            return (a.storage[d] || (a.storage[d] = {}))
        },
        $F: function () {},
        $false: function () {
            return false
        },
        defined: function (d) {
            return (undefined != d)
        },
        exists: function (d) {
            return !!(d)
        },
        j1: function (d) {
            if (!a.defined(d)) {
                return false
            }
            if (d.$J_TYPE) {
                return d.$J_TYPE
            }
            if ( !! d.nodeType) {
                if (1 == d.nodeType) {
                    return "element"
                }
                if (3 == d.nodeType) {
                    return "textnode"
                }
            }
            if (d.length && d.item) {
                return "collection"
            }
            if (d.length && d.callee) {
                return "arguments"
            }
            if ((d instanceof window.Object || d instanceof window.Function) && d.constructor === a.Class) {
                return "class"
            }
            if (d instanceof window.Array) {
                return "array"
            }
            if (d instanceof window.Function) {
                return "function"
            }
            if (d instanceof window.String) {
                return "string"
            }
            if (a.j21.trident) {
                if (a.defined(d.cancelBubble)) {
                    return "event"
                }
            } else {
                if (d === window.event || d.constructor == window.Event || d.constructor == window.MouseEvent || d.constructor == window.UIEvent || d.constructor == window.KeyboardEvent || d.constructor == window.KeyEvent) {
                    return "event"
                }
            }
            if (d instanceof window.Date) {
                return "date"
            }
            if (d instanceof window.RegExp) {
                return "regexp"
            }
            if (d === window) {
                return "window"
            }
            if (d === document) {
                return "document"
            }
            return typeof(d)
        },
        extend: function (j, h) {
            if (!(j instanceof window.Array)) {
                j = [j]
            }
            for (var g = 0, e = j.length; g < e; g++) {
                if (!a.defined(j)) {
                    continue
                }
                for (var f in (h || {})) {
                    try {
                        j[g][f] = h[f]
                    } catch (d) {}
                }
            }
            return j[0]
        },
        implement: function (h, g) {
            if (!(h instanceof window.Array)) {
                h = [h]
            }
            for (var f = 0, d = h.length; f < d; f++) {
                if (!a.defined(h[f])) {
                    continue
                }
                if (!h[f].prototype) {
                    continue
                }
                for (var e in (g || {})) {
                    if (!h[f].prototype[e]) {
                        h[f].prototype[e] = g[e]
                    }
                }
            }
            return h[0]
        },
        nativize: function (f, e) {
            if (!a.defined(f)) {
                return f
            }
            for (var d in (e || {})) {
                if (!f[d]) {
                    f[d] = e[d]
                }
            }
            return f
        },
        $try: function () {
            for (var f = 0, d = arguments.length; f < d; f++) {
                try {
                    return arguments[f]()
                } catch (g) {}
            }
            return null
        },
        $A: function (f) {
            if (!a.defined(f)) {
                return $mjs([])
            }
            if (f.toArray) {
                return $mjs(f.toArray())
            }
            if (f.item) {
                var e = f.length || 0,
                    d = new Array(e);
                while (e--) {
                        d[e] = f[e]
                    }
                return $mjs(d)
            }
            return $mjs(Array.prototype.slice.call(f))
        },
        now: function () {
            return new Date().getTime()
        },
        detach: function (h) {
            var f;
            switch (a.j1(h)) {
            case "object":
                f = {};
                for (var g in h) {
                    f[g] = a.detach(h[g])
                }
                break;
            case "array":
                f = [];
                for (var e = 0, d = h.length; e < d; e++) {
                    f[e] = a.detach(h[e])
                }
                break;
            default:
                return h
            }
            return a.$(f)
        },
        $: function (e) {
            if (!a.defined(e)) {
                return null
            }
            if (e.$J_EXTENDED) {
                return e
            }
            switch (a.j1(e)) {
            case "array":
                e = a.nativize(e, a.extend(a.Array, {
                    $J_EXTENDED: a.$F
                }));
                e.j14 = e.forEach;
                e.contains = a.Array.contains;
                return e;
                break;
            case "string":
                var d = document.getElementById(e);
                if (a.defined(d)) {
                    return a.$(d)
                }
                return null;
                break;
            case "window":
            case "document":
                a.$uuid(e);
                e = a.extend(e, a.Doc);
                break;
            case "element":
                a.$uuid(e);
                e = a.extend(e, a.Element);
                break;
            case "event":
                e = a.extend(e, a.Event);
                break;
            case "textnode":
                return e;
                break;
            case "function":
            case "array":
            case "date":
            default:
                break
            }
            return a.extend(e, {
                $J_EXTENDED: a.$F
            })
        },
        $new: function (d, f, e) {
            return $mjs(a.doc.createElement(d)).setProps(f || {}).j6(e || {})
        },
        addCSS: function (e) {
            if (document.styleSheets && document.styleSheets.length) {
                document.styleSheets[0].insertRule(e, 0)
            } else {
                var d = $mjs(document.createElement("style"));
                d.update(e);
                document.getElementsByTagName("head")[0].appendChild(d)
            }
        }
    };
    var a = b;
    window.magicJS = b;
    window.$mjs = b.$;
    a.Array = {
        $J_TYPE: "array",
        indexOf: function (g, h) {
            var d = this.length;
            for (var e = this.length, f = (h < 0) ? Math.max(0, e + h) : h || 0; f < e; f++) {
                if (this[f] === g) {
                    return f
                }
            }
            return -1
        },
        contains: function (d, e) {
            return this.indexOf(d, e) != -1
        },
        forEach: function (d, g) {
            for (var f = 0, e = this.length; f < e; f++) {
                if (f in this) {
                    d.call(g, this[f], f, this)
                }
            }
        },
        filter: function (d, j) {
            var h = [];
            for (var g = 0, e = this.length; g < e; g++) {
                if (g in this) {
                    var f = this[g];
                    if (d.call(j, this[g], g, this)) {
                        h.push(f)
                    }
                }
            }
            return h
        },
        map: function (d, h) {
            var g = [];
            for (var f = 0, e = this.length; f < e; f++) {
                if (f in this) {
                    g[f] = d.call(h, this[f], f, this)
                }
            }
            return g
        }
    };
    a.implement(String, {
        $J_TYPE: "string",
        j26: function () {
            return this.replace(/^\s+|\s+$/g, "")
        },
        eq: function (d, e) {
            return (e || false) ? (this.toString() === d.toString()) : (this.toLowerCase().toString() === d.toLowerCase().toString())
        },
        j22: function () {
            return this.replace(/-\D/g, function (d) {
                return d.charAt(1).toUpperCase()
            })
        },
        dashize: function () {
            return this.replace(/[A-Z]/g, function (d) {
                return ("-" + d.charAt(0).toLowerCase())
            })
        },
        j17: function (d) {
            return parseInt(this, d || 10)
        },
        toFloat: function () {
            return parseFloat(this)
        },
        j18: function () {
            return !this.replace(/true/i, "").j26()
        },
        has: function (e, d) {
            d = d || "";
            return (d + this + d).indexOf(d + e + d) > -1
        }
    });
    b.implement(Function, {
        $J_TYPE: "function",
        j24: function () {
            var e = a.$A(arguments),
                d = this,
                f = e.shift();
            return function () {
                    return d.apply(f || null, e.concat(a.$A(arguments)))
                }
        },
        j16: function () {
            var e = a.$A(arguments),
                d = this,
                f = e.shift();
            return function (g) {
                    return d.apply(f || null, $mjs([g || window.event]).concat(e))
                }
        },
        j27: function () {
            var e = a.$A(arguments),
                d = this,
                f = e.shift();
            return window.setTimeout(function () {
                    return d.apply(d, e)
                }, f || 0)
        },
        j28: function () {
            var e = a.$A(arguments),
                d = this;
            return function () {
                    return d.j27.apply(d, e)
                }
        },
        interval: function () {
            var e = a.$A(arguments),
                d = this,
                f = e.shift();
            return window.setInterval(function () {
                    return d.apply(d, e)
                }, f || 0)
        }
    });
    var c = navigator.userAgent.toLowerCase();
    a.j21 = {
        features: {
            xpath: !! (document.evaluate),
            air: !! (window.runtime),
            query: !! (document.querySelector)
        },
        touchScreen: function () {
            return "ontouchstart" in window || (window.DocumentTouch && document instanceof DocumentTouch)
        }(),
        mobile: c.match(/android|tablet|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(j21|link)|vodafone|wap|windows (ce|phone)|xda|xiino/) ? true : false,
        engine: (window.opera) ? "presto" : !! (window.ActiveXObject) ? "trident" : (undefined != document.getBoxObjectFor || null != window.mozInnerScreenY) ? "gecko" : (null != window.WebKitPoint || !navigator.taintEnabled) ? "webkit" : "unknown",
        version: "",
        ieMode: 0,
        platform: c.match(/ip(?:ad|od|hone)/) ? "ios" : (c.match(/(?:webos|android)/) || navigator.platform.match(/mac|win|linux/i) || ["other"])[0].toLowerCase(),
        backCompat: document.compatMode && "backcompat" == document.compatMode.toLowerCase(),
        getDoc: function () {
            return (document.compatMode && "backcompat" == document.compatMode.toLowerCase()) ? document.body : document.documentElement
        },
        requestAnimationFrame: window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame || undefined,
        cancelAnimationFrame: window.cancelAnimationFrame || window.mozCancelAnimationFrame || window.mozCancelAnimationFrame || window.oCancelAnimationFrame || window.msCancelAnimationFrame || window.webkitCancelRequestAnimationFrame || undefined,
        ready: false,
        onready: function () {
            if (a.j21.ready) {
                return
            }
            a.j21.ready = true;
            a.body = $mjs(document.body);
            a.win = $mjs(window);
            (function () {
                a.j21.css3Transformations = {
                    capable: false,
                    prefix: ""
                };
                if (typeof document.body.style.transform !== "undefined") {
                    a.j21.css3Transformations.capable = true
                } else {
                    var f = "Webkit Moz O ms Khtml".split(" ");
                    for (var e = 0, d = f.length; e < d; e++) {
                        a.j21.css3Transformations.prefix = f[e];
                        if (typeof document.body.style[a.j21.css3Transformations.prefix + "Transform"] !== "undefined") {
                            a.j21.css3Transformations.capable = true;
                            break
                        }
                    }
                }
            })();
            (function () {
                a.j21.css3Animation = {
                    capable: false,
                    prefix: ""
                };
                if (typeof document.body.style.animationName !== "undefined") {
                    a.j21.css3Animation.capable = true
                } else {
                    var f = "Webkit Moz O ms Khtml".split(" ");
                    for (var e = 0, d = f.length; e < d; e++) {
                        a.j21.css3Animation.prefix = f[e];
                        if (typeof document.body.style[a.j21.css3Animation.prefix + "AnimationName"] !== "undefined") {
                            a.j21.css3Animation.capable = true;
                            break
                        }
                    }
                }
            })();
            $mjs(document).raiseEvent("domready")
        }
    };
    (function () {
        function d() {
            return !!(arguments.callee.caller)
        }
        a.j21.version = ("presto" == a.j21.engine) ? !! (document.head) ? 270 : !! (window.applicationCache) ? 260 : !! (window.localStorage) ? 250 : (a.j21.features.query) ? 220 : ((d()) ? 211 : ((document.getElementsByClassName) ? 210 : 200)) : ("trident" == a.j21.engine) ? !! (window.msPerformance || window.performance) ? 900 : !! (window.XMLHttpRequest && window.postMessage) ? 6 : ((window.XMLHttpRequest) ? 5 : 4) : ("webkit" == a.j21.engine) ? ((a.j21.features.xpath) ? ((a.j21.features.query) ? 525 : 420) : 419) : ("gecko" == a.j21.engine) ? !! (document.head) ? 200 : !! document.readyState ? 192 : !! (window.localStorage) ? 191 : ((document.getElementsByClassName) ? 190 : 181) : "";
        a.j21[a.j21.engine] = a.j21[a.j21.engine + a.j21.version] = true;
        if (window.chrome) {
            a.j21.chrome = true
        }
        a.j21.ieMode = (!a.j21.trident) ? 0 : (document.documentMode) ? document.documentMode : function () {
            var e = 0;
            if (a.j21.backCompat) {
                return 5
            }
            switch (a.j21.version) {
            case 4:
                e = 6;
                break;
            case 5:
                e = 7;
                break;
            case 6:
                e = 8;
                break;
            case 900:
                e = 9;
                break
            }
            return e
        }()
    })();
    (function () {
        a.j21.fullScreen = {
            capable: false,
            enabled: function () {
                return false
            },
            request: function () {},
            cancel: function () {},
            changeEventName: "",
            errorEventName: "",
            prefix: ""
        };
        if (typeof document.cancelFullScreen != "undefined") {
            a.j21.fullScreen.capable = true
        } else {
            var f = "webkit moz o ms khtml".split(" ");
            for (var e = 0, d = f.length; e < d; e++) {
                a.j21.fullScreen.prefix = f[e];
                if (typeof document[a.j21.fullScreen.prefix + "CancelFullScreen"] != "undefined") {
                    a.j21.fullScreen.capable = true;
                    break
                }
            }
        }
        if (a.j21.fullScreen.capable) {
            a.j21.fullScreen.changeEventName = a.j21.fullScreen.prefix + "fullscreenchange";
            a.j21.fullScreen.errorEventName = a.j21.fullScreen.prefix + "fullscreenerror";
            a.j21.fullScreen.enabled = function () {
                switch (this.prefix) {
                case "":
                    return document.fullScreen;
                case "webkit":
                    return document.webkitIsFullScreen;
                default:
                    return document[this.prefix + "FullScreen"]
                }
            };
            a.j21.fullScreen.request = function (g) {
                return (this.prefix === "") ? g.requestFullScreen() : g[this.prefix + "RequestFullScreen"]()
            };
            a.j21.fullScreen.cancel = function (g) {
                return (this.prefix === "") ? document.cancelFullScreen() : document[this.prefix + "CancelFullScreen"]()
            }
        }
    })();
    a.Element = {
        j13: function (d) {
            return this.className.has(d, " ")
        },
        j2: function (d) {
            if (d && !this.j13(d)) {
                this.className += (this.className ? " " : "") + d
            }
            return this
        },
        j3: function (d) {
            d = d || ".*";
            this.className = this.className.replace(new RegExp("(^|\\s)" + d + "(?:\\s|$)"), "$1").j26();
            return this
        },
        j4: function (d) {
            return this.j13(d) ? this.j3(d) : this.j2(d)
        },
        j5: function (f) {
            f = (f == "float" && this.currentStyle) ? "styleFloat" : f.j22();
            var d = null,
                e = null;
            if (this.currentStyle) {
                    d = this.currentStyle[f]
                } else {
                    if (document.defaultView && document.defaultView.getComputedStyle) {
                        e = document.defaultView.getComputedStyle(this, null);
                        d = e ? e.getPropertyValue([f.dashize()]) : null
                    }
                }
            if (!d) {
                    d = this.style[f]
                }
            if ("opacity" == f) {
                    return a.defined(d) ? parseFloat(d) : 1
                }
            if (/^(border(Top|Bottom|Left|Right)Width)|((padding|margin)(Top|Bottom|Left|Right))$/.test(f)) {
                    d = parseInt(d) ? d : "0px"
                }
            return ("auto" == d ? null : d)
        },
        j6Prop: function (f, d) {
            try {
                if ("opacity" == f) {
                    this.j23(d);
                    return this
                } else {
                    if ("float" == f) {
                        this.style[("undefined" === typeof(this.style.styleFloat)) ? "cssFloat" : "styleFloat"] = d;
                        return this
                    } else {
                        if (a.j21.css3Transformations && /transform/.test(f)) {}
                    }
                }
                this.style[f.j22()] = d + (("number" == a.j1(d) && !$mjs(["zIndex", "zoom"]).contains(f.j22())) ? "px" : "")
            } catch (g) {}
            return this
        },
        j6: function (e) {
            for (var d in e) {
                this.j6Prop(d, e[d])
            }
            return this
        },
        j19s: function () {
            var d = {};
            a.$A(arguments).j14(function (e) {
                d[e] = this.j5(e)
            }, this);
            return d
        },
        j23: function (h, e) {
            e = e || false;
            h = parseFloat(h);
            if (e) {
                if (h == 0) {
                    if ("hidden" != this.style.visibility) {
                        this.style.visibility = "hidden"
                    }
                } else {
                    if ("visible" != this.style.visibility) {
                        this.style.visibility = "visible"
                    }
                }
            }
            if (a.j21.trident) {
                if (!this.currentStyle || !this.currentStyle.hasLayout) {
                    this.style.zoom = 1
                }
                try {
                    var g = this.filters.item("DXImageTransform.Microsoft.Alpha");
                    g.enabled = (1 != h);
                    g.opacity = h * 100
                } catch (d) {
                    this.style.filter += (1 == h) ? "" : "progid:DXImageTransform.Microsoft.Alpha(enabled=true,opacity=" + h * 100 + ")"
                }
            }
            this.style.opacity = h;
            return this
        },
        setProps: function (d) {
            for (var e in d) {
                this.setAttribute(e, "" + d[e])
            }
            return this
        },
        hide: function () {
            return this.j6({
                display: "none",
                visibility: "hidden"
            })
        },
        show: function () {
            return this.j6({
                display: "block",
                visibility: "visible"
            })
        },
        j7: function () {
            return {
                width: this.offsetWidth,
                height: this.offsetHeight
            }
        },
        j10: function () {
            return {
                top: this.scrollTop,
                left: this.scrollLeft
            }
        },
        j11: function () {
            var d = this,
                e = {
                    top: 0,
                    left: 0
                };
            do {
                    e.left += d.scrollLeft || 0;
                    e.top += d.scrollTop || 0;
                    d = d.parentNode
                } while (d);
            return e
        },
        j8: function () {
            if (a.defined(document.documentElement.getBoundingClientRect)) {
                var d = this.getBoundingClientRect(),
                    f = $mjs(document).j10(),
                    h = a.j21.getDoc();
                return {
                        top: d.top + f.y - h.clientTop,
                        left: d.left + f.x - h.clientLeft
                    }
            }
            var g = this,
                e = t = 0;
            do {
                    e += g.offsetLeft || 0;
                    t += g.offsetTop || 0;
                    g = g.offsetParent
                } while (g && !(/^(?:body|html)$/i).test(g.tagName));
            return {
                    top: t,
                    left: e
                }
        },
        j9: function () {
            var e = this.j8();
            var d = this.j7();
            return {
                top: e.top,
                bottom: e.top + d.height,
                left: e.left,
                right: e.left + d.width
            }
        },
        changeContent: function (f) {
            try {
                this.innerHTML = f
            } catch (d) {
                this.innerText = f
            }
            return this
        },
        j33: function () {
            return (this.parentNode) ? this.parentNode.removeChild(this) : this
        },
        kill: function () {
            a.$A(this.childNodes).j14(function (d) {
                if (3 == d.nodeType || 8 == d.nodeType) {
                    return
                }
                $mjs(d).kill()
            });
            this.j33();
            this.je3();
            if (this.$J_UUID) {
                a.storage[this.$J_UUID] = null;
                delete a.storage[this.$J_UUID]
            }
            return null
        },
        append: function (g, e) {
            e = e || "bottom";
            var d = this.firstChild;
            ("top" == e && d) ? this.insertBefore(g, d) : this.appendChild(g);
            return this
        },
        j32: function (f, e) {
            var d = $mjs(f).append(this, e);
            return this
        },
        enclose: function (d) {
            this.append(d.parentNode.replaceChild(this, d));
            return this
        },
        hasChild: function (d) {
            if ("element" !== a.j1("string" == a.j1(d) ? d = document.getElementById(d) : d)) {
                return false
            }
            return (this == d) ? false : (this.contains && !(a.j21.webkit419)) ? (this.contains(d)) : (this.compareDocumentPosition) ? !! (this.compareDocumentPosition(d) & 16) : a.$A(this.byTag(d.tagName)).contains(d)
        }
    };
    a.Element.j19 = a.Element.j5;
    a.Element.j20 = a.Element.j6;
    if (!window.Element) {
        window.Element = a.$F;
        if (a.j21.engine.webkit) {
            window.document.createElement("iframe")
        }
        window.Element.prototype = (a.j21.engine.webkit) ? window["[[DOMElement.prototype]]"] : {}
    }
    a.implement(window.Element, {
        $J_TYPE: "element"
    });
    a.Doc = {
        j7: function () {
            if (a.j21.presto925 || a.j21.webkit419) {
                return {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            }
            return {
                width: a.j21.getDoc().clientWidth,
                height: a.j21.getDoc().clientHeight
            }
        },
        j10: function () {
            return {
                x: window.pageXOffset || a.j21.getDoc().scrollLeft,
                y: window.pageYOffset || a.j21.getDoc().scrollTop
            }
        },
        j12: function () {
            var d = this.j7();
            return {
                width: Math.max(a.j21.getDoc().scrollWidth, d.width),
                height: Math.max(a.j21.getDoc().scrollHeight, d.height)
            }
        }
    };
    a.extend(document, {
        $J_TYPE: "document"
    });
    a.extend(window, {
        $J_TYPE: "window"
    });
    a.extend([a.Element, a.Doc], {
        j29: function (g, e) {
            var d = a.getStorage(this.$J_UUID),
                f = d[g];
            if (undefined != e && undefined == f) {
                    f = d[g] = e
                }
            return (a.defined(f) ? f : null)
        },
        j30: function (f, e) {
            var d = a.getStorage(this.$J_UUID);
            d[f] = e;
            return this
        },
        j31: function (e) {
            var d = a.getStorage(this.$J_UUID);
            delete d[e];
            return this
        }
    });
    if (!(window.HTMLElement && window.HTMLElement.prototype && window.HTMLElement.prototype.getElementsByClassName)) {
        a.extend([a.Element, a.Doc], {
            getElementsByClassName: function (d) {
                return a.$A(this.getElementsByTagName("*")).filter(function (g) {
                    try {
                        return (1 == g.nodeType && g.className.has(d, " "))
                    } catch (f) {}
                })
            }
        })
    }
    a.extend([a.Element, a.Doc], {
        byClass: function () {
            return this.getElementsByClassName(arguments[0])
        },
        byTag: function () {
            return this.getElementsByTagName(arguments[0])
        }
    });
    if (a.j21.fullScreen.capable) {
        a.Element.requestFullScreen = function () {
            a.j21.fullScreen.request(this)
        }
    }
    a.Event = {
        $J_TYPE: "event",
        stop: function () {
            if (this.stopPropagation) {
                this.stopPropagation()
            } else {
                this.cancelBubble = true
            }
            if (this.preventDefault) {
                this.preventDefault()
            } else {
                this.returnValue = false
            }
            return this
        },
        j15: function () {
            var e, d;
            e = ((/touch/i).test(this.type)) ? this.changedTouches[0] : this;
            return (!a.defined(e)) ? {
                x: 0,
                y: 0
            } : {
                x: e.pageX || e.clientX + a.j21.getDoc().scrollLeft,
                y: e.pageY || e.clientY + a.j21.getDoc().scrollTop
            }
        },
        getTarget: function () {
            var d = this.target || this.srcElement;
            while (d && 3 == d.nodeType) {
                d = d.parentNode
            }
            return d
        },
        getRelated: function () {
            var e = null;
            switch (this.type) {
            case "mouseover":
                e = this.relatedTarget || this.fromElement;
                break;
            case "mouseout":
                e = this.relatedTarget || this.toElement;
                break;
            default:
                return e
            }
            try {
                while (e && 3 == e.nodeType) {
                    e = e.parentNode
                }
            } catch (d) {
                e = null
            }
            return e
        },
        getButton: function () {
            if (!this.which && this.button !== undefined) {
                return (this.button & 1 ? 1 : (this.button & 2 ? 3 : (this.button & 4 ? 2 : 0)))
            }
            return this.which
        }
    };
    a._event_add_ = "addEventListener";
    a._event_del_ = "removeEventListener";
    a._event_prefix_ = "";
    if (!document.addEventListener) {
        a._event_add_ = "attachEvent";
        a._event_del_ = "detachEvent";
        a._event_prefix_ = "on"
    }
    a.extend([a.Element, a.Doc], {
        je1: function (g, f) {
            var i = ("domready" == g) ? false : true,
                e = this.j29("events", {});
            e[g] = e[g] || {};
            if (e[g].hasOwnProperty(f.$J_EUID)) {
                    return this
                }
            if (!f.$J_EUID) {
                    f.$J_EUID = Math.floor(Math.random() * a.now())
                }
            var d = this,
                h = function (j) {
                    return f.call(d)
                };
            if ("domready" == g) {
                    if (a.j21.ready) {
                        f.call(this);
                        return this
                    }
                }
            if (i) {
                    h = function (j) {
                        j = a.extend(j || window.e, {
                            $J_TYPE: "event"
                        });
                        return f.call(d, $mjs(j))
                    };
                    this[a._event_add_](a._event_prefix_ + g, h, false)
                }
            e[g][f.$J_EUID] = h;
            return this
        },
        je2: function (g) {
            var i = ("domready" == g) ? false : true,
                e = this.j29("events");
            if (!e || !e[g]) {
                    return this
                }
            var h = e[g],
                f = arguments[1] || null;
            if (g && !f) {
                    for (var d in h) {
                        if (!h.hasOwnProperty(d)) {
                            continue
                        }
                        this.je2(g, d)
                    }
                    return this
                }
            f = ("function" == a.j1(f)) ? f.$J_EUID : f;
            if (!h.hasOwnProperty(f)) {
                    return this
                }
            if ("domready" == g) {
                    i = false
                }
            if (i) {
                    this[a._event_del_](a._event_prefix_ + g, h[f], false)
                }
            delete h[f];
            return this
        },
        raiseEvent: function (h, f) {
            var m = ("domready" == h) ? false : true,
                l = this,
                j;
            if (!m) {
                    var g = this.j29("events");
                    if (!g || !g[h]) {
                        return this
                    }
                    var i = g[h];
                    for (var d in i) {
                        if (!i.hasOwnProperty(d)) {
                            continue
                        }
                        i[d].call(this)
                    }
                    return this
                }
            if (l === document && document.createEvent && !l.dispatchEvent) {
                    l = document.documentElement
                }
            if (document.createEvent) {
                    j = document.createEvent(h);
                    j.initEvent(f, true, true)
                } else {
                    j = document.createEventObject();
                    j.eventType = h
                }
            if (document.createEvent) {
                    l.dispatchEvent(j)
                } else {
                    l.fireEvent("on" + f, j)
                }
            return j
        },
        je3: function () {
            var d = this.j29("events");
            if (!d) {
                return this
            }
            for (var e in d) {
                this.je2(e)
            }
            this.j31("events");
            return this
        }
    });
    (function () {
        if ("complete" === document.readyState) {
            return a.j21.onready.j27(1)
        }
        if (a.j21.webkit && a.j21.version < 420) {
            (function () {
                ($mjs(["loaded", "complete"]).contains(document.readyState)) ? a.j21.onready() : arguments.callee.j27(50)
            })()
        } else {
            if (a.j21.trident && a.j21.ieMode < 9 && window == top) {
                (function () {
                    (a.$try(function () {
                        a.j21.getDoc().doScroll("left");
                        return true
                    })) ? a.j21.onready() : arguments.callee.j27(50)
                })()
            } else {
                $mjs(document).je1("DOMContentLoaded", a.j21.onready);
                $mjs(window).je1("load", a.j21.onready)
            }
        }
    })();
    a.Class = function () {
        var h = null,
            e = a.$A(arguments);
        if ("class" == a.j1(e[0])) {
                h = e.shift()
            }
        var d = function () {
                for (var l in this) {
                    this[l] = a.detach(this[l])
                }
                if (this.constructor.$parent) {
                    this.$parent = {};
                    var o = this.constructor.$parent;
                    for (var n in o) {
                        var j = o[n];
                        switch (a.j1(j)) {
                        case "function":
                            this.$parent[n] = a.Class.wrap(this, j);
                            break;
                        case "object":
                            this.$parent[n] = a.detach(j);
                            break;
                        case "array":
                            this.$parent[n] = a.detach(j);
                            break
                        }
                    }
                }
                var i = (this.init) ? this.init.apply(this, arguments) : this;
                delete this.caller;
                return i
            };
        if (!d.prototype.init) {
                d.prototype.init = a.$F
            }
        if (h) {
                var g = function () {};
                g.prototype = h.prototype;
                d.prototype = new g;
                d.$parent = {};
                for (var f in h.prototype) {
                    d.$parent[f] = h.prototype[f]
                }
            } else {
                d.$parent = null
            }
        d.constructor = a.Class;
        d.prototype.constructor = d;
        a.extend(d.prototype, e[0]);
        a.extend(d, {
                $J_TYPE: "class"
            });
        return d
    };
    b.Class.wrap = function (d, e) {
        return function () {
            var g = this.caller;
            var f = e.apply(d, arguments);
            return f
        }
    };
    a.win = $mjs(window);
    a.doc = $mjs(document)
})();
(function (b) {
    if (!b) {
        throw "MagicJS not found";
        return
    }
    if (b.FX) {
        return
    }
    var a = b.$;
    b.FX = new b.Class({
        options: {
            fps: 60,
            duration: 500,
            transition: function (c) {
                return -(Math.cos(Math.PI * c) - 1) / 2
            },
            onStart: b.$F,
            onComplete: b.$F,
            onBeforeRender: b.$F,
            onAfterRender: b.$F,
            forceAnimation: false,
            roundCss: true
        },
        styles: null,
        init: function (d, c) {
            this.el = a(d);
            this.options = b.extend(this.options, c);
            this.timer = false
        },
        start: function (c) {
            this.styles = c;
            this.state = 0;
            this.curFrame = 0;
            this.startTime = b.now();
            this.finishTime = this.startTime + this.options.duration;
            this.loopBind = this.loop.j24(this);
            this.options.onStart.call();
            if (!this.options.forceAnimation && b.j21.requestAnimationFrame) {
                this.timer = b.j21.requestAnimationFrame.call(window, this.loopBind)
            } else {
                this.timer = this.loop.j24(this).interval(Math.round(1000 / this.options.fps))
            }
            return this
        },
        stopAnimation: function () {
            if (this.timer) {
                if (!this.options.forceAnimation && b.j21.requestAnimationFrame && b.j21.cancelAnimationFrame) {
                    b.j21.cancelAnimationFrame.call(window, this.timer)
                } else {
                    clearInterval(this.timer)
                }
                this.timer = false
            }
        },
        stop: function (c) {
            c = b.defined(c) ? c : false;
            this.stopAnimation();
            if (c) {
                this.render(1);
                this.options.onComplete.j27(10)
            }
            return this
        },
        calc: function (e, d, c) {
            return (d - e) * c + e
        },
        loop: function () {
            var d = b.now();
            if (d >= this.finishTime) {
                this.stopAnimation();
                this.render(1);
                this.options.onComplete.j27(10);
                return this
            }
            var c = this.options.transition((d - this.startTime) / this.options.duration);
            if (!this.options.forceAnimation && b.j21.requestAnimationFrame) {
                this.timer = b.j21.requestAnimationFrame.call(window, this.loopBind)
            }
            this.render(c)
        },
        render: function (c) {
            var d = {};
            for (var e in this.styles) {
                if ("opacity" === e) {
                    d[e] = Math.round(this.calc(this.styles[e][0], this.styles[e][1], c) * 100) / 100
                } else {
                    d[e] = this.calc(this.styles[e][0], this.styles[e][1], c);
                    if (this.options.roundCss) {
                        d[e] = Math.round(d[e])
                    }
                }
            }
            this.options.onBeforeRender(d);
            this.set(d);
            this.options.onAfterRender(d)
        },
        set: function (c) {
            return this.el.j6(c)
        }
    });
    b.FX.Transition = {
        linear: function (c) {
            return c
        },
        sineIn: function (c) {
            return -(Math.cos(Math.PI * c) - 1) / 2
        },
        sineOut: function (c) {
            return 1 - b.FX.Transition.sineIn(1 - c)
        },
        expoIn: function (c) {
            return Math.pow(2, 8 * (c - 1))
        },
        expoOut: function (c) {
            return 1 - b.FX.Transition.expoIn(1 - c)
        },
        quadIn: function (c) {
            return Math.pow(c, 2)
        },
        quadOut: function (c) {
            return 1 - b.FX.Transition.quadIn(1 - c)
        },
        cubicIn: function (c) {
            return Math.pow(c, 3)
        },
        cubicOut: function (c) {
            return 1 - b.FX.Transition.cubicIn(1 - c)
        },
        backIn: function (d, c) {
            c = c || 1.618;
            return Math.pow(d, 2) * ((c + 1) * d - c)
        },
        backOut: function (d, c) {
            return 1 - b.FX.Transition.backIn(1 - d)
        },
        elasticIn: function (d, c) {
            c = c || [];
            return Math.pow(2, 10 * --d) * Math.cos(20 * d * Math.PI * (c[0] || 1) / 3)
        },
        elasticOut: function (d, c) {
            return 1 - b.FX.Transition.elasticIn(1 - d, c)
        },
        bounceIn: function (e) {
            for (var d = 0, c = 1; 1; d += c, c /= 2) {
                if (e >= (7 - 4 * d) / 11) {
                    return c * c - Math.pow((11 - 6 * d - 11 * e) / 4, 2)
                }
            }
        },
        bounceOut: function (c) {
            return 1 - b.FX.Transition.bounceIn(1 - c)
        },
        none: function (c) {
            return 0
        }
    }
})(magicJS);
(function (a) {
    if (!a) {
        throw "MagicJS not found";
        return
    }
    if (!a.FX) {
        throw "MagicJS.FX not found";
        return
    }
    if (a.FX.Slide) {
        return
    }
    var b = a.$;
    a.FX.Slide = new a.Class(a.FX, {
        options: {
            mode: "vertical"
        },
        init: function (d, c) {
            this.el = $mjs(d);
            this.options = a.extend(this.$parent.options, this.options);
            this.$parent.init(d, c);
            this.wrapper = this.el.j29("slide:wrapper");
            this.wrapper = this.wrapper || a.$new("DIV").j6(a.extend(this.el.j19s("margin-top", "margin-left", "margin-right", "margin-bottom", "position", "top", "float"), {
                overflow: "hidden"
            })).enclose(this.el);
            this.el.j30("slide:wrapper", this.wrapper).j6({
                margin: 0
            })
        },
        vertical: function () {
            this.margin = "margin-top";
            this.layout = "height";
            this.offset = this.el.offsetHeight
        },
        horizontal: function (c) {
            this.margin = "margin-" + (c || "left");
            this.layout = "width";
            this.offset = this.el.offsetWidth
        },
        right: function () {
            this.horizontal()
        },
        left: function () {
            this.horizontal("right")
        },
        start: function (e, h) {
            this[h || this.options.mode]();
            var g = this.el.j5(this.margin).j17(),
                f = this.wrapper.j5(this.layout).j17(),
                c = {},
                i = {},
                d;
            c[this.margin] = [g, 0],
            c[this.layout] = [0, this.offset],
            i[this.margin] = [g, -this.offset],
            i[this.layout] = [f, 0];
            switch (e) {
                case "in":
                    d = c;
                    break;
                case "out":
                    d = i;
                    break;
                case "toggle":
                    d = (0 == f) ? c : i;
                    break
                }
            this.$parent.start(d);
            return this
        },
        set: function (c) {
            this.el.j6Prop(this.margin, c[this.margin]);
            this.wrapper.j6Prop(this.layout, c[this.layout]);
            return this
        },
        slideIn: function (c) {
            return this.start("in", c)
        },
        slideOut: function (c) {
            return this.start("out", c)
        },
        hide: function (d) {
            this[d || this.options.mode]();
            var c = {};
            c[this.layout] = 0,
            c[this.margin] = -this.offset;
            return this.set(c)
        },
        show: function (d) {
            this[d || this.options.mode]();
            var c = {};
            c[this.layout] = this.offset,
            c[this.margin] = 0;
            return this.set(c)
        },
        toggle: function (c) {
            return this.start("toggle", c)
        }
    })
})(magicJS);
(function (b) {
    if (!b) {
        throw "MagicJS not found";
        return
    }
    if (b.PFX) {
        return
    }
    var a = b.$;
    b.PFX = new b.Class(b.FX, {
        init: function (c, d) {
            this.el_arr = c;
            this.options = b.extend(this.options, d);
            this.timer = false
        },
        start: function (c) {
            this.$parent.start([]);
            this.styles_arr = c;
            return this
        },
        render: function (c) {
            for (var d = 0; d < this.el_arr.length; d++) {
                this.el = a(this.el_arr[d]);
                this.styles = this.styles_arr[d];
                this.$parent.render(c)
            }
        }
    })
})(magicJS);
var MagicZoomPlus = (function (g) {
    var i = g.$;
    g.$Ff = function (j) {
        $mjs(j).stop();
        return false
    };
    /*g.insertCSS = function (j, l, q) {
        var m, k, n, o = [],
            e = -1;
        q || (q = g.stylesId);
        m = g.$(q) || (document.head || document.body).appendChild(g.$new("style", {
                id: q,
                type: "text/css"
            }));
        k = m.sheet || m.styleSheet;
        if ("object" == g.j1(l)) {
                for (n in l) {
                    o.push(n + ":" + l[n])
                }
                l = o.join(";")
            }
        if (k.insertRule) {
                e = k.insertRule(j + " {" + l + "}", k.cssRules.length)
            } else {
                e = k.addRule(j, l)
            }
        return e
    };*/
    var c = {
        version: "v4.5.14",
        options: {},
        defaults: {
            opacity: 50,
            opacityReverse: false,
            smoothingSpeed: 40,
            fps: 25,
            zoomWidth: 300,
            zoomHeight: 300,
            zoomDistance: 15,
            zoomPosition: "right",
            zoomAlign: "top",
            zoomWindowEffect: "shadow",
            dragMode: false,
            moveOnClick: true,
            alwaysShowZoom: false,
            preservePosition: false,
            x: -1,
            y: -1,
            clickToActivate: false,
            clickToDeactivate: false,
            initializeOn: "load",
            smoothing: true,
            showTitle: "top",
            titleSource: "title",
            zoomFade: true,
            zoomFadeInSpeed: 400,
            zoomFadeOutSpeed: 200,
            hotspots: "",
            hint: true,
            hintText: "Zoom",
            hintPosition: "tl",
            hintOpacity: 75,
            hintClass: "MagicZoomHint",
            showLoading: true,
            loadingMsg: "Loading zoom...",
            loadingClass: "MagicZoomLoading",
            loadingOpacity: 75,
            loadingPositionX: -1,
            loadingPositionY: -1,
            selectorsChange: "click",
            selectorsMouseoverDelay: 60,
            selectorsEffect: "dissolve",
            selectorsEffectSpeed: 400,
            preloadSelectorsSmall: true,
            preloadSelectorsBig: false,
            selectorsClass: "",
            fitZoomWindow: true,
            entireImage: false,
            rightClick: false,
            disableZoom: false,
            onready: g.$F
        },
        z39: $mjs([/^(opacity)(\s+)?:(\s+)?(\d+)$/i, /^(opacity-reverse)(\s+)?:(\s+)?(true|false)$/i, /^(smoothing\-speed)(\s+)?:(\s+)?(\d+)$/i, /^(fps)(\s+)?:(\s+)?(\d+)$/i, /^(zoom\-width)(\s+)?:(\s+)?(\d+\%?)(px)?/i, /^(zoom\-height)(\s+)?:(\s+)?(\d+\%?)(px)?/i, /^(zoom\-distance)(\s+)?:(\s+)?(\d+)(px)?/i, /^(zoom\-position)(\s+)?:(\s+)?(right|left|top|bottom|custom|inner|#([a-z0-9_\-:\.]+))$/i, /^(zoom\-align)(\s+)?:(\s+)?(right|left|top|bottom|center)$/i, /^(zoom\-fit\-screen)(\s+)?:(\s+)?(true|false)$/i, /^(zoom\-window\-effect)(\s+)?:(\s+)?(shadow|glow|false)$/i, /^(drag\-mode)(\s+)?:(\s+)?(true|false)$/i, /^(move\-on\-click)(\s+)?:(\s+)?(true|false)$/i, /^(always\-show\-zoom)(\s+)?:(\s+)?(true|false)$/i, /^(preserve\-position)(\s+)?:(\s+)?(true|false)$/i, /^(x)(\s+)?:(\s+)?([\d.]+)(px)?/i, /^(y)(\s+)?:(\s+)?([\d.]+)(px)?/i, /^(click\-to\-activate)(\s+)?:(\s+)?(true|false)$/i, /^(click\-to\-deactivate)(\s+)?:(\s+)?(true|false)$/i, /^(initialize\-on)(\s+)?:(\s+)?(load|click|mouseover)$/i, /^(click\-to\-initialize)(\s+)?:(\s+)?(true|false)$/i, /^(smoothing)(\s+)?:(\s+)?(true|false)$/i, /^(show\-title)(\s+)?:(\s+)?(true|false|top|bottom)$/i, /^(title\-source)(\s+)?:(\s+)?(title|#([a-z0-9_\-:\.]+))$/i, /^(zoom\-fade)(\s+)?:(\s+)?(true|false)$/i, /^(zoom\-fade\-in\-speed)(\s+)?:(\s+)?(\d+)$/i, /^(zoom\-fade\-out\-speed)(\s+)?:(\s+)?(\d+)$/i, /^(hotspots)(\s+)?:(\s+)?([a-z0-9_\-:\.]+)$/i, /^(hint)(\s+)?:(\s+)?(true|false)/i, /^(hint\-text)(\s+)?:(\s+)?([^;]*)$/i, /^(hint\-opacity)(\s+)?:(\s+)?(\d+)$/i, /^(hint\-position)(\s+)?:(\s+)?(tl|tr|tc|bl|br|bc)/i, /^(show\-loading)(\s+)?:(\s+)?(true|false)$/i, /^(loading\-msg)(\s+)?:(\s+)?([^;]*)$/i, /^(loading\-opacity)(\s+)?:(\s+)?(\d+)$/i, /^(loading\-position\-x)(\s+)?:(\s+)?(\d+)(px)?/i, /^(loading\-position\-y)(\s+)?:(\s+)?(\d+)(px)?/i, /^(thumb\-change)(\s+)?:(\s+)?(click|mouseover)$/i, /^(selectors\-change)(\s+)?:(\s+)?(click|mouseover)$/i, /^(selectors\-mouseover\-delay)(\s+)?:(\s+)?(\d+)$/i, /^(selectors\-effect)(\s+)?:(\s+)?(dissolve|fade|pounce|false)$/i, /^(selectors\-effect\-speed)(\s+)?:(\s+)?(\d+)$/i, /^(selectors\-class)(\s+)?:(\s+)?([a-z0-9_\-:\.]+)$/i, /^(fit\-zoom\-window)(\s+)?:(\s+)?(true|false)$/i, /^(preload\-selectors\-small)(\s+)?:(\s+)?(true|false)$/i, /^(preload\-selectors\-big)(\s+)?:(\s+)?(true|false)$/i, /^(entire\-image)(\s+)?:(\s+)?(true|false)$/i, /^(right\-click)(\s+)?:(\s+)?(true|false)$/i, /^(disable\-zoom)(\s+)?:(\s+)?(true|false)$/i]),
        zooms: $mjs([]),
        z8: function (l) {
            var k = /(click|mouseover)/i;
            for (var j = 0; j < c.zooms.length; j++) {
                if (c.zooms[j].z30 && !c.zooms[j].activatedEx) {
                    c.zooms[j].pause()
                } else {
                    if (k.test(c.zooms[j].options.initializeOn) && c.zooms[j].initMouseEvent) {
                        c.zooms[j].initMouseEvent = l
                    }
                }
            }
        },
        stop: function (j) {
            var e = $mjs([]);
            if (j) {
                if ((j = $mjs(j)) && j.zoom) {
                    e.push(j)
                } else {
                    return false
                }
            } else {
                e = $mjs(g.$A(g.body.byTag("A")).filter(function (k) {
                    return ((" " + k.className + " ").match(/\sMagicZoom\s/) && k.zoom)
                }))
            }
            e.j14(function (k) {
                k.zoom && k.zoom.stop()
            }, this)
        },
        start: function (e) {
            if (0 == arguments.length) {
                c.refresh();
                return true
            }
            e = $mjs(e);
            if (!e || !(" " + e.className + " ").match(/\s(MagicZoom|MagicZoomPlus)\s/)) {
                return false
            }
            if (!e.zoom) {
                var j = null;
                while (j = e.firstChild) {
                    if (j.tagName == "IMG") {
                        break
                    }
                    e.removeChild(j)
                }
                while (j = e.lastChild) {
                    if (j.tagName == "IMG") {
                        break
                    }
                    e.removeChild(j)
                }
                if (!e.firstChild || e.firstChild.tagName != "IMG") {
                    throw "Invalid Magic Zoom"
                }
                c.zooms.push(new c.zoom(e, (arguments.length > 1) ? arguments[1] : undefined))
            } else {
                e.zoom.start()
            }
        },
        update: function (l, e, k, j) {
            if ((l = $mjs(l)) && l.zoom) {
                (null === e || "" === e) && (e = undefined);
                (null === k || "" === k) && (k = undefined);
                l.zoom.update(e, k, j);
                return true
            }
            return false
        },
        refresh: function () {
            g.$A(window.document.getElementsByTagName("A")).j14(function (e) {
                if (e.className.has("MagicZoom", " ")) {
                    if (c.stop(e)) {
                        c.start.j27(100, e)
                    } else {
                        c.start(e)
                    }
                }
            }, this)
        },
        show: function (e) {
            return c.zoomIn(e)
        },
        zoomIn: function (e) {
            if ((e = $mjs(e)) && e.zoom) {
                return e.zoom.activate()
            }
            return false
        },
        zoomOut: function (e) {
            if ((e = $mjs(e)) && e.zoom) {
                return e.zoom.pause()
            }
            return false
        },
        getXY: function (e) {
            if ((e = $mjs(e)) && e.zoom) {
                return {
                    x: e.zoom.options.x,
                    y: e.zoom.options.y
                }
            }
        },
        x7: function (k) {
            var j, e;
            j = "";
            for (e = 0; e < k.length; e++) {
                j += String.fromCharCode(14 ^ k.charCodeAt(e))
            }
            return j
        }
    };
    c.z48 = function () {
        this.init.apply(this, arguments)
    };
    c.z48.prototype = {
        init: function (e) {
            this.cb = null;
            this.z9 = null;
            this.onErrorHandler = this.onError.j16(this);
            this.z10 = null;
            this.width = 0;
            this.height = 0;
            this.naturalWidth = 0;
            this.naturalHeight = 0;
            this.border = {
                left: 0,
                right: 0,
                top: 0,
                bottom: 0
            };
            this.padding = {
                left: 0,
                right: 0,
                top: 0,
                bottom: 0
            };
            this.ready = false;
            this._tmpp = null;
            if ("string" == g.j1(e)) {
                this._tmpp = g.$new("div").j2("magic-temporary-img").j6({
                    position: "absolute",
                    top: "-10000px",
                    width: "1px",
                    height: "1px",
                    overflow: "hidden"
                }).j32(g.body);
                this.self = g.$new("img").j32(this._tmpp);
                this.z11();
                this.self.src = e
            } else {
                this.self = $mjs(e);
                this.z11();
                this.self.src = e.src
            }
        },
        _cleanup: function () {
            if (this._tmpp) {
                if (this.self.parentNode == this._tmpp) {
                    this.self.j33().j6({
                        position: "static",
                        top: "auto"
                    })
                }
                this._tmpp.kill();
                this._tmpp = null
            }
        },
        onError: function (j) {
            if (j) {
                $mjs(j).stop()
            }
            if (this.cb) {
                this._cleanup();
                this.cb.call(this, false)
            }
            this.unload()
        },
        z11: function (e) {
            this.z9 = null;
            if (e == true || !(this.self.src && (this.self.complete || this.self.readyState == "complete"))) {
                this.z9 = function (j) {
                    if (j) {
                        $mjs(j).stop()
                    }
                    if (this.ready) {
                        return
                    }
                    this.ready = true;
                    this.z13();
                    if (this.cb) {
                        this._cleanup();
                        this.cb.call()
                    }
                }.j16(this);
                this.self.je1("load", this.z9);
                $mjs(["abort", "error"]).j14(function (j) {
                    this.self.je1(j, this.onErrorHandler)
                }, this)
            } else {
                this.ready = true
            }
        },
        update: function (j, l) {
            var k = this.ready;
            this.unload();
            var e = g.$new("a", {
                href: j
            });
            if (true !== l && this.self.src.has(e.href) && 0 !== this.self.width) {
                this.ready = k
            } else {
                this.z11(true);
                this.self.src = j
            }
            e = null
        },
        z13: function () {
            this.naturalWidth = this.self.naturalWidth || this.self.width;
            this.naturalHeight = this.self.naturalHeight || this.self.height;
            this.width = this.self.width;
            this.height = this.self.height;
            if (this.width == 0 && this.height == 0 && g.j21.webkit) {
                this.width = this.self.naturalWidth;
                this.height = this.self.naturalHeight
            }
            $mjs(["Left", "Right", "Top", "Bottom"]).j14(function (j) {
                this.padding[j.toLowerCase()] = this.self.j19("padding" + j).j17();
                this.border[j.toLowerCase()] = this.self.j19("border" + j + "Width").j17()
            }, this);
            if (g.j21.presto || (g.j21.trident && !g.j21.backCompat)) {
                this.width -= this.padding.left + this.padding.right;
                this.height -= this.padding.top + this.padding.bottom
            }
        },
        getBox: function () {
            var e = null;
            e = this.self.j9();
            return {
                top: e.top + this.border.top,
                bottom: e.bottom - this.border.bottom,
                left: e.left + this.border.left,
                right: e.right - this.border.right
            }
        },
        z12: function () {
            if (this.z10) {
                this.z10.src = this.self.src;
                this.self = null;
                this.self = this.z10
            }
        },
        load: function (e) {
            if (this.ready) {
                if (!this.width) {
                    (function () {
                        this.z13();
                        this._cleanup();
                        e.call()
                    }).j24(this).j27(1)
                } else {
                    this._cleanup();
                    e.call()
                }
            } else {
                if (!this.z9) {
                    e.call(this, false);
                    return
                }
                this.cb = e
            }
        },
        unload: function () {
            if (this.z9) {
                this.self.je2("load", this.z9)
            }
            $mjs(["abort", "error"]).j14(function (e) {
                this.self.je2(e, this.onErrorHandler)
            }, this);
            this.z9 = null;
            this.cb = null;
            this.width = null;
            this.ready = false;
            this._new = false
        }
    };
    c.zoom = function () {
        this.construct.apply(this, arguments)
    };
    c.zoom.prototype = {
        construct: function (l, j, k) {
            var e = {};
            this.z28 = -1;
            this.z30 = false;
            this.ddx = 0;
            this.ddy = 0;
            this.firstRun = !(this.z47);
            this.exOptions = this.firstRun ? {} : this.exOptions || {};
            this.activatedEx = false;
            this.z44 = null;
            this.z1Holder = $mjs(window).j29("magiczoom:holder") || $mjs(window).j29("magiczoom:holder", g.$new("div").j6({
                position: "absolute",
                top: -10000,
                width: 10,
                height: 10,
                overflow: "hidden"
            }).j32(g.body));
            this.options = g.detach(c.defaults);
            if (l) {
                this.c = $mjs(l)
            }
            this.divTag = ("div" == this.c.tagName.toLowerCase());
            e = g.extend(e, this.z37());
            e = g.extend(e, this.z37(this.c.rel));
            e = g.extend(e, this.exOptions);
            if (j) {
                e = g.extend(e, g.extend(true === k ? this.exOptions : {}, this.z37(j)))
            }
            if (e.dragMode && !e.clickToActivate && undefined === e.alwaysShowZoom) {
                e.alwaysShowZoom = true
            }
            g.extend(this.options, e);
            this.options.hotspots += "";
            if ("load" == this.options.initializeOn && g.defined(this.options.clickToInitialize) && "true" == this.options.clickToInitialize.toString()) {
                this.options.initializeOn = "click"
            }
            if (g.defined(this.options.thumbChange) && this.options.thumbChange != this.options.selectorsChange) {
                this.options.selectorsChange = this.options.thumbChange
            }
            if (this.firstRun && !this.divTag) {
                this.id = this.originId = this.c.id || "";
                if (!this.c.id) {
                    this.c.id = this.id = "zoom-" + Math.floor(Math.random() * g.now())
                }
            }
            if ("inner" == this.options.zoomPosition && this.options.dragMode) {
                this.options.moveOnClick = true
            }
            if (this.options.disableZoom) {
                this.z30 = false;
                this.options.clickToActivate = true;
                this.options.hint = false
            }("string" === g.j1(this.options.onready)) && ("function" === g.j1(window[this.options.onready])) && (this.options.onready = window[this.options.onready]);
            if (l) {
                this.lastSelector = null;
                this.z14 = this.mousedown.j16(this);
                this.z15 = this.mouseup.j16(this);
                this.z16 = this.show.j24(this, true);
                this.z17 = this.z29.j24(this);
                this.z43Bind = this.z43.j16(this);
                this.resizeBind = function (o) {
                    var n = $mjs(this.c).j29("magiczoom:window:size"),
                        m = $mjs(window).j7();
                    if (n.width !== m.width || n.height !== m.height) {
                            clearTimeout(this.resizeTimer);
                            this.resizeTimer = this.onresize.j24(this).j27(10);
                            $mjs(this.c).j30("magiczoom:window:size", m)
                        }
                }.j16(this);
                if (!this.divTag) {
                    this.c.je1("click", function (n) {
                        var m = n.getButton();
                        if (3 == m) {
                            return true
                        }
                        $mjs(n).stop();
                        if (!g.j21.trident) {
                            this.blur()
                        }
                        return false
                    })
                }
                this.c.je1("mousedown", this.z14);
                this.c.je1("mouseup", this.z15);
                if ("mouseover" == this.options.initializeOn) {
                    this.c.je1("mouseover", this.z14)
                }
                if (g.j21.touchScreen) {
                    this.c.j6({
                        "-webkit-user-select": "none",
                        "-webkit-touch-callout": "none",
                        "-webkit-tap-highlight-color": "transparent"
                    });
                    if (!this.options.disableZoom) {
                        this.c.je1("touchstart", this.z14);
                        this.c.je1("touchend", this.z15)
                    } else {
                        this.c.je1("click", function (m) {
                            m.preventDefault()
                        })
                    }
                }
                this.c.unselectable = "on";
                this.c.style.MozUserSelect = "none";
                this.c.je1("selectstart", g.$Ff);
                if (!this.divTag) {
                    this.c.j6({
                        position: "relative",
                        display: (g.j21.gecko181) ? "block" : "inline-block",
                        textDecoration: "none",
                        outline: "0",
                        cursor: "hand",
                        overflow: "hidden"
                    });
                    if (g.j21.ieMode) {
                        this.c.j2("magic-for-ie" + g.j21.ieMode)
                    }
                    if (this.c.j5("textAlign") == "center") {
                        this.c.j6({
                            margin: "auto auto"
                        })
                    }
                }
                this.c.zoom = this
            } else {
                this.options.initializeOn = "load"
            }
            if (!this.options.rightClick) {
                this.c.je1("contextmenu", g.$Ff)
            }
            if ("load" == this.options.initializeOn) {
                this.z18()
            } else {
                if ("" !== this.originId) {
                    this.z26(true)
                }
            }
        },
        z18: function () {
            var l, o, n, m, j;
            //j = ["^bko}k.{~i|ojk.za.h{bb.xk|}ga`.ah.Coigm.Taac(-6:6<5", "#ff0000", 10, "bold", "center", "100%"];
            //j = ["^bko}k.{~i|ojk.za.h{bb.xk|}ga`.ah.Coigm.Taac.^b{}(-6:6<5", "#ff0000", 10, "bold", "center", "100%"];
            if (!this.z7) {
                this.z7 = new c.z48(this.c.firstChild);
                this.z1 = new c.z48(this.c.href)
            } else {
                this.z1.update(this.c.href)
            }
            if (!this.z47) {
                this.z47 = {
                    self: $mjs(document.createElement("DIV"))[(this.divTag) ? "j3" : "j2"]("MagicZoomBigImageCont").j6({
                        overflow: "hidden",
                        zIndex: this.options.zoomPosition == "inner" ? 100 : 10002,
                        top: "-100000px",
                        position: "absolute",
                        width: this.options.zoomWidth + "px",
                        height: this.options.zoomHeight + "px"
                    }),
                    zoom: this,
                    z21: "0px",
                    lastLeftPos: "0px",
                    initTopPos: 0,
                    initLeftPos: 0,
                    adjustX: {
                        edge: "left",
                        ratio: 1
                    },
                    adjustY: {
                        edge: "top",
                        ratio: 1
                    },
                    custom: false,
                    initWidth: this.options.zoomWidth,
                    initHeight: this.options.zoomHeight
                };
                if (!(g.j21.trident900 && g.j21.ieMode < 9)) {
                    switch (this.options.zoomWindowEffect) {
                    case "shadow":
                        this.z47.self.j2("MagicBoxShadow");
                        break;
                    case "glow":
                        this.z47.self.j2("MagicBoxGlow");
                        break;
                    default:
                        break
                    }
                }
                this.z47.hide = function () {
                    if (this.self.style.top != "-100000px" && this.zoom.z4 && !this.zoom.z4.z38) {
                        this.self.style.top = "-100000px"
                    }
                    if (this.self.parentNode === g.body) {
                        this.self.j32(this.zoom.z1Holder)
                    }
                };
                this.z47.z22 = this.z47.hide.j24(this.z47);
                if (g.j21.trident4) {
                    l = $mjs(document.createElement("IFRAME"));
                    l.src = "javascript:''";
                    l.j6({
                        left: "0px",
                        top: "0px",
                        position: "absolute",
                        "z-index": -1
                    }).frameBorder = 0;
                    this.z47.z23 = this.z47.self.appendChild(l)
                }
                this.z47.z41 = $mjs(document.createElement("DIV")).j2("MagicZoomHeader").j6({
                    position: "relative",
                    zIndex: 10,
                    left: "0px",
                    top: "0px",
                    padding: "3px"
                }).hide();
                o = g.$new("DIV", {}, {
                    overflow: "hidden"
                });
                o.appendChild(this.z1.self);
                this.z1.self.j6({
                    padding: "0px",
                    margin: "0px",
                    border: "0px",
                    width: "auto",
                    height: "auto"
                });
                if (this.options.showTitle == "bottom") {
                    this.z47.self.appendChild(o);
                    this.z47.self.appendChild(this.z47.z41)
                } else {
                    this.z47.self.appendChild(this.z47.z41);
                    this.z47.self.appendChild(o)
                }
                this.z47.self.j32(this.z1Holder);
                if ("undefined" !== typeof(j)) {
                    this.z47.g = $mjs(document.createElement("div")).j6({
                        color: j[1],
                        fontSize: j[2] + "px",
                        fontWeight: j[3],
                        fontFamily: "Tahoma",
                        position: "absolute",
                        "z-index": 10 + ("" + (this.z1.self.j5("z-index") || 0)).j17(),
                        width: j[5],
                        textAlign: j[4],
                        "line-height": "2em",
                        left: "0px"
                    }).changeContent(c.x7(j[0])).j32(this.z47.self, ((Math.floor(Math.random() * 101) + 1) % 2) ? "top" : "bottom")
                }
            }
            this.z47.custom = false;
            if (this.options.zoomPosition == "custom" && $mjs(this.c.id + "-big")) {
                this.z47.custom = true;
                $mjs(this.c.id + "-big").appendChild(this.z47.self)
            } else {
                if (this.options.zoomPosition.has("#")) {
                    var q = this.options.zoomPosition.replace(/^#/, "");
                    if ($mjs(q)) {
                        this.z47.custom = true;
                        $mjs(q).appendChild(this.z47.self)
                    }
                } else {
                    if (this.options.zoomPosition == "inner") {
                        this.c.appendChild(this.z47.self)
                    }
                }
            }
            this.z47.initWidth = this.options.zoomWidth;
            this.z47.initHeight = this.options.zoomHeight;
            if (this.options.showTitle != "false" && this.options.showTitle != false) {
                var k = this.z47.z41;
                k.hide();
                while (n = k.firstChild) {
                    k.removeChild(n)
                }
                if (this.options.titleSource == "title" && "" != this.c.title) {
                    k.appendChild(document.createTextNode(this.c.title));
                    k.show()
                } else {
                    if (this.options.titleSource.has("#")) {
                        var q = this.options.titleSource.replace(/^#/, "");
                        if ($mjs(q)) {
                            k.changeContent($mjs(q).innerHTML);
                            k.show()
                        }
                    }
                }
            } else {
                this.z47.z41.hide()
            }
            this.c.z46 = this.c.title;
            this.c.title = "";
            this.z7.load(this.z19.j24(this))
        },
        z19: function (e) {
            if (!e && e !== undefined) {
                return
            }
            if (!this.z7) {
                return
            }
            if (!this.options.opacityReverse) {
                this.z7.self.j23(1)
            }
            if (!this.divTag) {
                this.c.j6({
                    width: "auto",
                    height: "auto"
                })
            }
            if (this.options.showLoading && !this.options.disableZoom) {
                this.z24 = setTimeout(this.z17, 400)
            }
            if (this.options.hotspots != "" && $mjs(this.options.hotspots)) {
                this.z25()
            }
            if (this.c.id != "") {
                this.z26()
            }
            this.z1.load(this.z20.j24(this))
        },
        z20: function (l) {
            var k, j, e;
            if (!l && l !== undefined) {
                clearTimeout(this.z24);
                if (this.options.showLoading && this.z3) {
                    this.z3.hide()
                }
                this.z28 = g.now();
                return
            }
            if (!this.z7 || !this.z1) {
                return
            }
            j = this.z7.self.j9();
            this.z7Rect = j;
            if (j.bottom == j.top) {
                this.z20.j24(this).j27(500);
                return
            }
            if (this.z7.width == 0 && g.j21.trident) {
                this.z7.z13();
                this.z1.z13();
                !this.divTag && this.c.j6({
                    width: this.z7.width + "px"
                })
            }
            k = this.z47.z41.j7();
            if (/%$/i.test(this.options.zoomWidth)) {
                this.options.zoomWidth = (parseInt(this.options.zoomWidth) / 100) * this.z7.width
            }
            if (/%$/i.test(this.options.zoomHeight)) {
                this.options.zoomHeight = (parseInt(this.options.zoomHeight) / 100) * this.z7.height
            }
            this.z47.self.j6({
                width: this.options.zoomWidth
            });
            k = this.z47.z41.j7();
            if (this.options.fitZoomWindow || this.options.entireImage) {
                if ((this.z1.width < this.options.zoomWidth) || this.options.entireImage) {
                    this.options.zoomWidth = this.z1.width;
                    this.z47.self.j6({
                        width: this.options.zoomWidth
                    });
                    k = this.z47.z41.j7()
                }
                if ((this.z1.height < this.options.zoomHeight) || this.options.entireImage) {
                    this.options.zoomHeight = this.z1.height + k.height
                }
            }
            switch (this.options.zoomPosition) {
            case "right":
                this.z47.self.style.left = j.right + this.options.zoomDistance + "px";
                this.z47.adjustX.edge = "right";
                break;
            case "left":
                this.z47.self.style.left = j.left - this.options.zoomDistance - this.options.zoomWidth + "px";
                break;
            case "top":
                this.z47.z21 = j.top - (this.options.zoomDistance + this.options.zoomHeight) + "px";
                break;
            case "bottom":
                this.z47.z21 = j.bottom + this.options.zoomDistance + "px";
                this.z47.adjustY.edge = "bottom";
                break;
            case "inner":
                this.z47.self.j6({
                    left: "0px",
                    height: "100%",
                    width: "100%"
                });
                this.options.zoomWidth = this.z7.width;
                this.options.zoomHeight = this.z7.height;
                this.z47.z21 = "0px";
                k = this.z47.z41.j7();
                break;
            default:
                if (this.z47.custom) {
                    e = $mjs(this.z47.self.parentNode).j7();
                    if (/%$/i.test(this.z47.initWidth)) {
                        this.options.zoomWidth = (parseInt(this.z47.initWidth) / 100) * e.width
                    }
                    if (/%$/i.test(this.z47.initHeight)) {
                        this.options.zoomHeight = (parseInt(this.z47.initHeight) / 100) * e.height
                    }
                    this.z47.self.j6({
                        left: "0px",
                        width: this.options.zoomWidth
                    });
                    this.z47.z21 = "0px";
                    k = this.z47.z41.j7()
                }
                break
            }
            if (this.options.showTitle == "bottom") {
                $mjs(this.z1.self.parentNode).j6Prop("height", this.options.zoomHeight - k.height)
            }
            this.z47.self.j6("inner" == this.options.zoomPosition ? {} : {
                height: this.options.zoomHeight + "px",
                width: this.options.zoomWidth + "px"
            }).j23(1);
            if (g.j21.trident4 && this.z47.z23) {
                this.z47.z23.j6({
                    width: this.options.zoomWidth + "px",
                    height: this.options.zoomHeight + "px"
                })
            }
            if (this.options.zoomPosition == "right" || this.options.zoomPosition == "left") {
                if (this.options.zoomAlign == "center") {
                    this.z47.z21 = (j.bottom - (j.bottom - j.top) / 2 - this.options.zoomHeight / 2) + "px";
                    this.z47.adjustY = {
                        edge: "bottom",
                        ratio: 2
                    }
                } else {
                    if (this.options.zoomAlign == "bottom") {
                        this.z47.z21 = (j.bottom - this.options.zoomHeight) + "px";
                        this.z47.adjustY.edge = "bottom"
                    } else {
                        this.z47.z21 = j.top + "px"
                    }
                }
            } else {
                if (this.options.zoomPosition == "top" || this.options.zoomPosition == "bottom") {
                    if (this.options.zoomAlign == "center") {
                        this.z47.self.style.left = (j.right - (j.right - j.left) / 2 - this.options.zoomWidth / 2) + "px";
                        this.z47.adjustX = {
                            edge: "right",
                            ratio: 2
                        }
                    } else {
                        if (this.options.zoomAlign == "right") {
                            this.z47.self.style.left = (j.right - this.options.zoomWidth) + "px";
                            this.z47.adjustX.edge = "right"
                        } else {
                            this.z47.self.style.left = j.left + "px"
                        }
                    }
                }
            }
            this.z47.initTopPos = parseInt(this.z47.z21, 10);
            this.z47.initLeftPos = parseInt(this.z47.self.style.left, 10);
            this.z47.lastLeftPos = this.z47.initLeftPos;
            this.z47.z21 = this.z47.initTopPos;
            this.zoomViewHeight = this.options.zoomHeight - k.height;
            if (this.z47.g) {
                this.z47.g.j6({
                    top: this.options.showTitle == "bottom" ? 0 : "auto",
                    bottom: this.options.showTitle == "bottom" ? "auto" : 0
                })
            }
            this.z1.self.j6({
                position: "relative",
                borderWidth: "0px",
                padding: "0px",
                left: "0px",
                top: "0px"
            });
            this.z27();
            if (this.options.alwaysShowZoom) {
                if (this.options.x == -1) {
                    this.options.x = this.z7.width / 2
                }
                if (this.options.y == -1) {
                    this.options.y = this.z7.height / 2
                }
                this.show()
            } else {
                if (this.options.zoomFade) {
                    this.z2 = new g.FX(this.z47.self, {
                        forceAnimation: "ios" === g.j21.platform
                    })
                }
                this.z47.self.j6({
                    top: "-100000px"
                })
            }
            if (this.options.showLoading && this.z3) {
                this.z3.hide()
            }
            this.c.je1("mousemove", this.z43Bind);
            this.c.je1("mouseout", this.z43Bind);
            if (g.j21.touchScreen) {
                this.c.je1("touchmove", this.z43Bind);
                this.c.je1("touchend", this.z43Bind)
            }
            this.setupHint();
            $mjs(this.c).j29("magiczoom:window:size", $mjs(window).j7());
            $mjs(window).je1("resize", this.resizeBind);
            if (!this.options.disableZoom && (!this.options.clickToActivate || "click" == this.options.initializeOn)) {
                this.z30 = true
            }
            if ("click" == this.options.initializeOn && this.initMouseEvent) {
                this.z43(this.initMouseEvent)
            }
            if (this.activatedEx) {
                this.activate()
            }
            this.z28 = g.now();
            !this.divTag && ("function" == g.j1(this.options.onready)) && this.options.onready.call(null, this.id, !this.firstRun)
        },
        setupHint: function () {
            var m = /tr|br/i,
                e = /bl|br|bc/i,
                j = /bc|tc/i,
                l = null;
            this.hintVisible = undefined;
            if (!this.options.hint) {
                    if (this.hint) {
                        this.hint.kill();
                        this.hint = undefined
                    }
                    return
                }
            if (!this.hint) {
                    this.hint = $mjs(document.createElement("DIV")).j2(this.options.hintClass).j6({
                        display: "block",
                        overflow: "hidden",
                        position: "absolute",
                        visibility: "hidden",
                        "z-index": 1
                    });
                    if (this.options.hintText != "") {
                        this.hint.appendChild(document.createTextNode(this.options.hintText))
                    }
                    this.c.appendChild(this.hint)
                } else {
                    if (this.options.hintText != "") {
                        l = this.hint[(this.hint.firstChild) ? "replaceChild" : "appendChild"](document.createTextNode(this.options.hintText), this.hint.firstChild);
                        l = null
                    }
                }
            this.hint.j6({
                    left: "auto",
                    right: "auto",
                    top: "auto",
                    bottom: "auto",
                    display: "block",
                    opacity: (this.options.hintOpacity / 100),
                    "max-width": (this.z7.width - 4)
                });
            var k = this.hint.j7();
            this.hint.j6Prop((m.test(this.options.hintPosition) ? "right" : "left"), (j.test(this.options.hintPosition) ? (this.z7.width - k.width) / 2 : 2)).j6Prop((e.test(this.options.hintPosition) ? "bottom" : "top"), 2);
            this.hintVisible = true;
            this.hint.show()
        },
        z29: function () {
            if (this.z1.ready) {
                return
            }
            this.z3 = $mjs(document.createElement("DIV")).j2(this.options.loadingClass).j23(this.options.loadingOpacity / 100).j6({
                display: "block",
                overflow: "hidden",
                position: "absolute",
                visibility: "hidden",
                "z-index": 20,
                "max-width": (this.z7.width - 4)
            });
            this.z3.appendChild(document.createTextNode(this.options.loadingMsg));
            this.c.appendChild(this.z3);
            var e = this.z3.j7();
            this.z3.j6({
                left: (this.options.loadingPositionX == -1 ? ((this.z7.width - e.width) / 2) : (this.options.loadingPositionX)) + "px",
                top: (this.options.loadingPositionY == -1 ? ((this.z7.height - e.height) / 2) : (this.options.loadingPositionY)) + "px"
            });
            this.z3.show()
        },
        z25: function () {
            $mjs(this.options.hotspots).z31 = $mjs(this.options.hotspots).parentNode;
            $mjs(this.options.hotspots).z32 = $mjs(this.options.hotspots).nextSibling;
            this.c.appendChild($mjs(this.options.hotspots));
            $mjs(this.options.hotspots).j6({
                position: "absolute",
                left: "0px",
                top: "0px",
                width: this.z7.width + "px",
                height: this.z7.height + "px",
                zIndex: 15
            }).show();
            if (g.j21.trident) {
                this.c.z33 = this.c.appendChild($mjs(document.createElement("DIV")).j6({
                    position: "absolute",
                    left: "0px",
                    top: "0px",
                    width: this.z7.width + "px",
                    height: this.z7.height + "px",
                    zIndex: 14,
                    background: "#ccc"
                }).j23(0.00001))
            }
            g.$A($mjs(this.options.hotspots).getElementsByTagName("A")).j14(function (j) {
                var k = j.coords.split(","),
                    e = null;
                $mjs(j).j6({
                        position: "absolute",
                        left: k[0] + "px",
                        top: k[1] + "px",
                        width: (k[2] - k[0]) + "px",
                        height: (k[3] - k[1]) + "px",
                        zIndex: 15
                    }).show();
                if (j.j13("MagicThumb")) {
                        if (e = j.j29("thumb")) {
                            e.group = this.options.hotspots
                        } else {
                            j.rel += ";group: " + this.options.hotspots + ";"
                        }
                    }
            }, this)
        },
        z26: function (k) {
            var e, l, j = new RegExp("zoom\\-id(\\s+)?:(\\s+)?" + this.c.id + "($|;)");
            this.selectors = $mjs([]);
            g.$A(document.getElementsByTagName("A")).j14(function (n) {
                if (j.test(n.rel)) {
                    if (!$mjs(n).z36) {
                        n.z36 = function (o) {
                            if (!g.j21.trident) {
                                this.blur()
                            }
                            $mjs(o).stop();
                            return false
                        };
                        n.je1("click", n.z36)
                    }
                    if (k) {
                        if (("mouseover" == this.options.initializeOn || "click" == this.options.initializeOn) && !$mjs(n).clickInitZoom) {
                            n.clickInitZoom = function (p, o) {
                                o.je2("click", o.clickInitZoom);
                                if ( !! this.z7) {
                                    return
                                }
                                $mjs(p).stop();
                                this.c.href = o.href;
                                this.c.firstChild.src = o.rev;
                                this.start(o.rel);
                                if (this.c.j29("thumb")) {
                                    this.c.j29("thumb").start()
                                }
                            }.j16(this, n);
                            n.je1("click", n.clickInitZoom)
                        }
                        return
                    }
                    var m = g.$new("a", {
                        href: n.rev
                    });
                    (this.options.selectorsClass != "") && $mjs(n)[this.z1.self.src.has(n.href) && this.z7.self.src.has(m.href) ? "j2" : "j3"](this.options.selectorsClass);
                    if (this.z1.self.src.has(n.href) && this.z7.self.src.has(m.href)) {
                        this.lastSelector = n
                    }
                    m = null;
                    if (!n.z34) {
                        n.z34 = function (q, p) {
                            p = q.currentTarget || q.getTarget();
                            try {
                                while ("a" != p.tagName.toLowerCase()) {
                                    p = p.parentNode
                                }
                            } catch (o) {
                                return
                            }
                            if (p.hasChild(q.getRelated())) {
                                return
                            }
                            if (q.type == "mouseout") {
                                if (this.z35) {
                                    clearTimeout(this.z35)
                                }
                                this.z35 = false;
                                return
                            }
                            if (p.title != "") {
                                this.c.title = p.title
                            }
                            if (q.type == "mouseover") {
                                this.z35 = setTimeout(this.update.j24(this, p.href, p.rev, p.rel, p), this.options.selectorsMouseoverDelay)
                            } else {
                                this.update(p.href, p.rev, p.rel, p)
                            }
                        }.j16(this);
                        n.je1(this.options.selectorsChange, n.z34);
                        if (this.options.selectorsChange == "mouseover") {
                            n.je1("mouseout", n.z34)
                        }
                    }
                    n.j6({
                        outline: "0",
                        display: "inline-block"
                    });
                    if (this.options.preloadSelectorsSmall) {
                        l = new Image();
                        l.src = n.rev
                    }
                    if (this.options.preloadSelectorsBig) {
                        e = new Image();
                        e.src = n.href
                    }
                    this.selectors.push(n)
                }
            }, this)
        },
        stop: function (j) {
            try {
                this.pause();
                this.c.je2("mousemove", this.z43Bind);
                this.c.je2("mouseout", this.z43Bind);
                if (g.j21.touchScreen) {
                    this.c.je2("touchmove", this.z43Bind);
                    this.c.je2("touchend", this.z43Bind)
                }
                if (undefined === j && this.z4) {
                    this.z4.self.hide()
                }
                if (this.z2) {
                    this.z2.stop()
                }
                this.z6 = null;
                this.z30 = false;
                if (this.selectors !== undefined) {
                    this.selectors.j14(function (e) {
                        if (this.options.selectorsClass != "") {
                            e.j3(this.options.selectorsClass)
                        }
                        if (undefined === j) {
                            e.je2(this.options.selectorsChange, e.z34);
                            if (this.options.selectorsChange == "mouseover") {
                                e.je2("mouseout", e.z34)
                            }
                            e.z34 = null;
                            e.je2("click", e.z36);
                            e.z36 = null
                        }
                    }, this)
                }
                if (this.options.hotspots != "" && $mjs(this.options.hotspots)) {
                    $mjs(this.options.hotspots).hide();
                    $mjs(this.options.hotspots).z31.insertBefore($mjs(this.options.hotspots), $mjs(this.options.hotspots).z32);
                    if (this.c.z33) {
                        this.c.removeChild(this.c.z33)
                    }
                }
                if (this.options.opacityReverse) {
                    this.c.j3("MagicZoomPup");
                    this.z7.self.j23(1)
                }
                this.z2 = null;
                if (this.z3) {
                    this.c.removeChild(this.z3)
                }
                if (this.hint) {
                    this.hint.hide()
                }
                if (undefined === j) {
                    if (this.hint) {
                        this.c.removeChild(this.hint)
                    }
                    this.hint = null;
                    this.z1.unload();
                    this.z7.unload();
                    (this.z4 && this.z4.self) && this.c.removeChild(this.z4.self);
                    (this.z47 && this.z47.self) && this.z47.self.parentNode.removeChild(this.z47.self);
                    this.z4 = null;
                    this.z47 = null;
                    this.z1 = null;
                    this.z7 = null;
                    if (!this.options.rightClick) {
                        this.c.je2("contextmenu", g.$Ff)
                    }
                    if ("" === this.originId) {
                        this.c.removeAttribute("id")
                    } else {
                        this.c.id = this.originId
                    }
                    $mjs(window).je2("resize", this.resizeBind)
                }
                if (this.z24) {
                    clearTimeout(this.z24);
                    this.z24 = null
                }
                this.z44 = null;
                this.c.z33 = null;
                this.z3 = null;
                if (this.c.title == "") {
                    this.c.title = this.c.z46
                }
                this.z28 = -1
            } catch (k) {}
        },
        start: function (j, e) {
            if (this.z28 != -1) {
                return
            }
            this.construct(false, j, (null === e || undefined === e))
        },
        update: function (A, p, j, z) {
            var k, D, e, m, v, l, F = null,
                x = null,
                n = this.lastSelector,
                o, q, C, w, s, u, G, E, r;
            z = z || null;
            if (g.now() - this.z28 < 300 || this.z28 == -1 || this.ufx) {
                    this.z35 && clearTimeout(this.z35);
                    k = 300 - g.now() + this.z28;
                    if (this.z28 == -1) {
                        k = 300
                    }
                    this.z35 = setTimeout(this.update.j24(this, A, p, j, z), k);
                    return
                }
            if (z && this.lastSelector == z) {
                    return

                } else {
                    this.lastSelector = z
                }
            D = function (H) {
                    if (undefined != A) {
                        this.c.href = A
                    }
                    if (undefined === j) {
                        j = ""
                    }
                    if (this.options.preservePosition) {
                        j = "x: " + this.options.x + "; y: " + this.options.y + "; " + j
                    }
                    if (undefined != p) {
                        this.z7.update(p)
                    }
                    if (H !== undefined) {
                        this.z7.load(H)
                    }
                };
            x = this.c.j29("thumb");
            if (x) {
                    x.ready && x.restore(null, true);
                    x.state = "updating";
                    F = function () {
                        x.state = "inz30";
                        x.update(this.c.href, null, j)
                    }.j24(this)
                }
            this.z7.z13();
            m = this.z7.width;
            v = this.z7.height;
            this.stop(true);
            if (this.options.selectorsEffect != "false" && undefined !== p) {
                    this.ufx = true;
                    var B = $mjs(this.c.cloneNode(true)).j6({
                        position: "absolute",
                        top: 0,
                        left: 0,
                        width: ""
                    });
                    var y = g.$new("div", {
                        id: this.c.parentNode.id,
                        "class": this.c.parentNode.className
                    }).j2("mz-tmp-clone").j6({
                        width: $mjs(this.c.parentNode).j5("width"),
                        "max-width": $mjs(this.c.parentNode).j5("max-width")
                    });
                    if ("td" === this.c.parentNode.tagName.toLocaleLowerCase()) {
                        this.c.parentNode.insertBefore(y, this.c)
                    } else {
                        this.c.parentNode.parentNode.insertBefore(y, this.c.parentNode)
                    }
                    y.append(B);
                    g.j21.chrome && y.j7();
                    if (g.j21.ieMode && g.j21.ieMode < 8) {
                        $mjs(B.firstChild).j23(1)
                    }
                    l = new c.z48(B.firstChild);
                    l.update(p);
                    if ("pounce" == this.options.selectorsEffect) {
                        r = this.c.href;
                        o = this.selectors.filter(function (H) {
                            return H.href.has(r)
                        });
                        o = (o[0]) ? $mjs(o[0].byTag("img")[0] || o[0]) : this.z7.self;
                        q = this.selectors.filter(function (H) {
                            return H.href.has(A)
                        });
                        q = (q[0]) ? $mjs(q[0].byTag("img")[0] || q[0]) : null;
                        if (null == q) {
                            q = this.z7.self;
                            o = this.z7.self
                        }
                        w = this.z7.self.j8(),
                        s = o.j8(),
                        u = q.j8(),
                        E = o.j7(),
                        G = q.j7()
                    }
                    e = function (J) {
                        var H = {},
                            L = {},
                            K = {},
                            M = null,
                            I = null;
                        if (false === J) {
                                l.unload();
                                $mjs(l.self).j33();
                                l = null;
                                y.j33();
                                this.ufx = false;
                                if (x) {
                                    x.state = "inz30"
                                }
                                this.lastSelector = n;
                                this.start(null, n);
                                return
                            }
                        if (g.j21.ieMode && g.j21.ieMode < 8 && (m === l.width || 0 === l.width)) {
                                l.self.j6Prop("zoom", 1);
                                y.j7();
                                l.z13()
                            }
                        if ("pounce" == this.options.selectorsEffect) {
                                H.width = [m, E.width];
                                H.height = [v, E.height];
                                H.top = [w.top, s.top];
                                H.left = [w.left, s.left];
                                L.width = [G.width, l.width];
                                L.height = [G.height, l.height];
                                L.top = [u.top, w.top];
                                y.j6({
                                    padding: ""
                                });
                                B.j23(0).j6({
                                    height: 0,
                                    width: l.width,
                                    position: "relative"
                                });
                                L.left = [u.left, B.j8().left];
                                K.width = [m, l.width];
                                l.self.j32(g.body).j6({
                                    position: "absolute",
                                    "z-index": 5001,
                                    left: L.left[0],
                                    top: L.top[0],
                                    width: L.width[0],
                                    height: L.height[0]
                                });
                                M = $mjs(this.c.firstChild.cloneNode(false)).j32(g.body).j6({
                                    position: "absolute",
                                    "z-index": 5000,
                                    left: H.left[0],
                                    top: H.top[0],
                                    visibility: "visible"
                                });
                                $mjs(this.c.firstChild).j6({
                                    visibility: "hidden"
                                });
                                y.j33();
                                I = this.c.j5("border-width");
                                this.c.j6Prop("border-width", 0)
                            } else {
                                l.self.j32(this.c).j6({
                                    position: "absolute",
                                    "z-index": 5001,
                                    opacity: 0,
                                    left: "0px",
                                    top: "0px",
                                    height: "auto"
                                });
                                M = $mjs(this.c.firstChild.cloneNode(false)).j32(this.c).j6({
                                    position: "absolute",
                                    "z-index": 5000,
                                    left: "0px",
                                    top: "0px",
                                    visibility: "visible",
                                    height: "auto"
                                });
                                $mjs(this.c.firstChild).j6({
                                    visibility: "hidden"
                                });
                                y.j33();
                                L = {
                                    opacity: [0, 1]
                                };
                                if (m != l.width || v != l.height) {
                                    K.width = L.width = H.width = [m, l.width];
                                    K.height = L.height = H.height = [v, l.height]
                                }
                                if (this.options.selectorsEffect == "fade") {
                                    H.opacity = [1, 0]
                                }
                            }
                        new g.PFX([this.c, l.self, (M || this.c.firstChild)], {
                                duration: this.options.selectorsEffectSpeed,
                                onComplete: function () {
                                    if (M) {
                                        M.j33();
                                        M = null
                                    }
                                    if (null !== I) {
                                        this.c.j6Prop("border-width", I)
                                    }
                                    D.call(this, function () {
                                        l.unload();
                                        $mjs(this.c.firstChild).j6({
                                            visibility: "visible"
                                        });
                                        $mjs(l.self).j33();
                                        l = null;
                                        if (H.opacity) {
                                            $mjs(this.c.firstChild).j6({
                                                opacity: 1
                                            })
                                        }
                                        this.ufx = false;
                                        this.start(j, z);
                                        if (F) {
                                            F.j27(10)
                                        }
                                    }.j24(this))
                                }.j24(this)
                            }).start([K, L, H])
                    };
                    l.load(e.j24(this))
                } else {
                    D.call(this, function () {
                        this.c.j6({
                            width: this.z7.width + "px",
                            height: this.z7.height + "px"
                        });
                        this.start(j, z);
                        if (F) {
                            F.j27(10)
                        }
                    }.j24(this))
                }
        },
        z37: function (j) {
            var e, n, l, k;
            e = null;
            n = [];
            j = j || "";
            if ("" == j) {
                for (k in c.options) {
                    e = c.options[k];
                    switch (g.j1(c.defaults[k.j22()])) {
                    case "boolean":
                        e = e.toString().j18();
                        break;
                    case "number":
                        if (!("zoomWidth" === k.j22() || "zoomHeight" === k.j22()) || !/\%$/i.test(e)) {
                            e = parseFloat(e)
                        }
                        break;
                    default:
                        break
                    }
                    n[k.j22()] = e
                }
            } else {
                l = $mjs(j.split(";"));
                l.j14(function (m) {
                    c.z39.j14(function (o) {
                        e = o.exec(m.j26());
                        if (e) {
                            switch (g.j1(c.defaults[e[1].j22()])) {
                            case "boolean":
                                n[e[1].j22()] = e[4] === "true";
                                break;
                            case "number":
                                n[e[1].j22()] = (("zoomWidth" === e[1].j22() || "zoomHeight" === e[1].j22()) && /\%$/.test(e[4])) ? e[4] : parseFloat(e[4]);
                                break;
                            default:
                                n[e[1].j22()] = e[4]
                            }
                        }
                    }, this)
                }, this)
            }
            if (false === n.selectorsEffect) {
                n.selectorsEffect = "false"
            }
            return n
        },
        z27: function () {
            var j, e;
            if (!this.z4) {
                this.z4 = {
                    self: $mjs(document.createElement("DIV")).j2("MagicZoomPup").j6({
                        zIndex: 10,
                        position: "absolute",
                        overflow: "hidden"
                    }).hide(),
                    width: 20,
                    height: 20,
                    bgColor: ""
                };
                this.c.appendChild(this.z4.self);
                this.z4.bgColor = this.z4.self.j5("background-color")
            }
            if (e = this.c.j29("thumb")) {
                this.z4.self.j6({
                    cursor: (e._o.disableExpand) ? "move" : ""
                })
            }
            if (this.options.entireImage) {
                this.z4.self.j6({
                    "border-width": "0px",
                    cursor: "default"
                })
            }
            this.z4.z38 = false;
            this.z4.height = this.zoomViewHeight / (this.z1.height / this.z7.height);
            this.z4.width = this.options.zoomWidth / (this.z1.width / this.z7.width);
            if (this.z4.width > this.z7.width) {
                this.z4.width = this.z7.width
            }
            if (this.z4.height > this.z7.height) {
                this.z4.height = this.z7.height
            }
            this.z4.width = Math.round(this.z4.width);
            this.z4.height = Math.round(this.z4.height);
            this.z4.borderWidth = this.z4.self.j19("borderLeftWidth").j17();
            this.z4.self.j6({
                width: (this.z4.width - 2 * (g.j21.backCompat ? 0 : this.z4.borderWidth)) + "px",
                height: (this.z4.height - 2 * (g.j21.backCompat ? 0 : this.z4.borderWidth)) + "px"
            });
            if (!this.options.opacityReverse && !this.options.rightClick) {
                this.z4.self.j23(parseFloat(this.options.opacity / 100));
                if (this.z4.z42) {
                    this.z4.self.removeChild(this.z4.z42);
                    this.z4.z42 = null
                }
            } else {
                if (this.z4.z42) {
                    this.z4.z42.src = this.z7.self.src
                } else {
                    j = this.z7.self.cloneNode(false);
                    j.unselectable = "on";
                    this.z4.z42 = $mjs(this.z4.self.appendChild(j)).j6({
                        position: "absolute",
                        zIndex: 5
                    })
                }
                if (this.options.opacityReverse) {
                    this.z4.z42.j6(this.z7.self.j7());
                    this.z4.self.j23(1);
                    if (g.j21.ieMode && g.j21.ieMode < 9) {
                        this.z4.z42.j23(1)
                    }
                } else {
                    if (this.options.rightClick) {
                        this.z4.z42.j23(0.009)
                    }
                    this.z4.self.j23(parseFloat(this.options.opacity / 100))
                }
            }
        },
        z43: function (l, j) {
            if (!this.z30 || l === undefined || l.skipAnimation) {
                return false
            }
            if (!this.z4) {
                return false
            }
            var m = (/touch/i).test(l.type) && l.touches.length > 1;
            var k = ("touchend" == l.type && !l.continueAnimation);
            if ((!this.divTag || l.type != "mouseout") && !m) {
                $mjs(l).stop()
            }
            if (j === undefined) {
                j = $mjs(l).j15()
            }
            if (this.z6 === null || this.z6 === undefined) {
                this.z6 = this.z7.getBox()
            }
            if (k || ("mouseout" == l.type && !this.c.hasChild(l.getRelated())) || m || j.x > this.z6.right || j.x < this.z6.left || j.y > this.z6.bottom || j.y < this.z6.top) {
                this.pause();
                return false
            }
            this.activatedEx = false;
            if (l.type == "mouseout" || l.type == "touchend") {
                return false
            }
            if (this.options.dragMode && !this.z45) {
                return false
            }
            if (!this.options.moveOnClick) {
                j.x -= this.ddx;
                j.y -= this.ddy
            }
            if ((j.x + this.z4.width / 2) >= this.z6.right) {
                j.x = this.z6.right - this.z4.width / 2
            }
            if ((j.x - this.z4.width / 2) <= this.z6.left) {
                j.x = this.z6.left + this.z4.width / 2
            }
            if ((j.y + this.z4.height / 2) >= this.z6.bottom) {
                j.y = this.z6.bottom - this.z4.height / 2
            }
            if ((j.y - this.z4.height / 2) <= this.z6.top) {
                j.y = this.z6.top + this.z4.height / 2
            }
            this.options.x = j.x - this.z6.left;
            this.options.y = j.y - this.z6.top;
            if (this.z44 === null) {
                this.z44 = setTimeout(this.z16, 10)
            }
            if (g.defined(this.hintVisible) && this.hintVisible) {
                this.hintVisible = false;
                this.hint.hide()
            }
            return true
        },
        show: function (m) {
            if (m && !this.z44) {
                return
            }
            var s, p, l, k, r, q, o, n, j, e = this.options,
                u = this.z4;
            s = u.width / 2;
            p = u.height / 2;
            u.self.style.left = e.x - s + this.z7.border.left + "px";
            u.self.style.top = e.y - p + this.z7.border.top + "px";
            if (this.options.opacityReverse) {
                    u.z42.style.left = "-" + (parseFloat(u.self.style.left) + u.borderWidth) + "px";
                    u.z42.style.top = "-" + (parseFloat(u.self.style.top) + u.borderWidth) + "px"
                }
            l = (this.options.x - s) * (this.z1.width / this.z7.width);
            k = (this.options.y - p) * (this.z1.height / this.z7.height);
            if (this.z1.width - l < e.zoomWidth) {
                    l = this.z1.width - e.zoomWidth;
                    if (l < 0) {
                        l = 0
                    }
                }
            if (this.z1.height - k < this.zoomViewHeight) {
                    k = this.z1.height - this.zoomViewHeight;
                    if (k < 0) {
                        k = 0
                    }
                }
            if (document.documentElement.dir == "rtl") {
                    l = (e.x + u.width / 2 - this.z7.width) * (this.z1.width / this.z7.width)
                }
            l = Math.round(l);
            k = Math.round(k);
            if (e.smoothing === false || (!u.z38)) {
                    this.z1.self.style.left = (-l) + "px";
                    this.z1.self.style.top = (-k) + "px"
                } else {
                    r = parseInt(this.z1.self.style.left);
                    q = parseInt(this.z1.self.style.top);
                    o = (-l - r);
                    n = (-k - q);
                    if (!o && !n) {
                        this.z44 = null;
                        return
                    }
                    o *= e.smoothingSpeed / 100;
                    if (o < 1 && o > 0) {
                        o = 1
                    } else {
                        if (o > -1 && o < 0) {
                            o = -1
                        }
                    }
                    r += o;
                    n *= e.smoothingSpeed / 100;
                    if (n < 1 && n > 0) {
                        n = 1
                    } else {
                        if (n > -1 && n < 0) {
                            n = -1
                        }

                    }
                    q += n;
                    this.z1.self.style.left = r + "px";
                    this.z1.self.style.top = q + "px"
                }
            if (!u.z38) {
                    if (this.z2) {
                        this.z2.stop();
                        this.z2.options.onComplete = g.$F;
                        this.z2.options.duration = e.zoomFadeInSpeed;
                        this.z47.self.j23(0);
                        this.z2.start({
                            opacity: [0, 1]
                        })
                    }
                    if (/^(left|right|top|bottom)$/i.test(e.zoomPosition)) {
                        this.z47.self.j32(g.body)
                    }
                    if (e.zoomPosition != "inner") {
                        u.self.show()
                    }
                    this.z47.self.j6(this.adjustPosition(/^(left|right|top|bottom)$/i.test(e.zoomPosition) && !this.options.alwaysShowZoom));
                    if (e.opacityReverse) {
                        this.c.j6Prop("background-color", this.z4.bgColor);
                        this.z7.self.j23(parseFloat((100 - e.opacity) / 100))
                    }
                    u.z38 = true
                }
            if (this.z44) {
                    this.z44 = setTimeout(this.z16, 1000 / e.fps)
                }
        },
        adjustPosition: function (q) {
            var j = this.t13(5),
                e = this.z7.self.j9(),
                n = this.options.zoomPosition,
                m = this.z47,
                k = this.options.zoomDistance,
                u = m.self.j7(),
                p = m.initTopPos,
                l = m.initLeftPos,
                o = {
                    left: m.initLeftPos,
                    top: m.initTopPos
                };
            if ("inner" === n || this.z47.custom) {
                    return o
                }
            q || (q = false);
            m.lastLeftPos += (e[m.adjustX.edge] - this.z7Rect[m.adjustX.edge]) / m.adjustX.ratio;
            m.z21 += (e[m.adjustY.edge] - this.z7Rect[m.adjustY.edge]) / m.adjustY.ratio;
            this.z7Rect = e;
            o.left = l = m.lastLeftPos;
            o.top = p = m.z21;
            if (q) {
                    if ("left" == n || "right" == n) {
                        if ("left" == n && j.left > l) {
                            o.left = (e.left - j.left >= u.width) ? (e.left - u.width - 2) : (j.right - e.right - 2 > e.left - j.left - 2) ? (e.right + 2) : (e.left - u.width - 2)
                        } else {
                            if ("right" == n && j.right < l + u.width) {
                                o.left = (j.right - e.right >= u.width) ? (e.right + 2) : (e.left - j.left - 2 > j.right - e.right - 2) ? (e.left - u.width - 2) : (e.right + 2)
                            }
                        }
                    } else {
                        if ("top" == n || "bottom" == n) {
                            o.left = Math.max(j.left + 2, Math.min(j.right, l + u.width) - u.width);
                            if ("top" == n && j.top > p) {
                                o.top = (e.top - j.top >= u.height) ? (e.top - u.height - 2) : (j.bottom - e.bottom - 2 > e.top - j.top - 2) ? (e.bottom + 2) : (e.top - u.height - 2)
                            } else {
                                if ("bottom" == n && j.bottom < p + u.height) {
                                    o.top = (j.bottom - e.bottom >= u.height) ? (e.bottom + 2) : (e.top - j.top - 2 > j.bottom - e.bottom - 2) ? (e.top - u.height - 2) : (e.bottom + 2)
                                }
                            }
                        }
                    }
                }
            return o
        },
        t13: function (k) {
            k = k || 0;
            var j = (g.j21.touchScreen) ? {
                width: window.innerWidth,
                height: window.innerHeight
            } : $mjs(window).j7(),
                e = $mjs(window).j10();
            return {
                    left: e.x + k,
                    right: e.x + j.width - k,
                    top: e.y + k,
                    bottom: e.y + j.height - k
                }
        },
        onresize: function (m) {
            if (!this.z7 || !this.z7.ready) {
                return
            }
            var k, j, l = {
                width: this.z7.width,
                height: this.z7.height
            };
            this.z7.z13();
            if (this.z47.custom) {
                j = $mjs(this.z47.self.parentNode).j7();
                if (/%$/i.test(this.z47.initWidth)) {
                    this.options.zoomWidth = (parseInt(this.z47.initWidth) / 100) * j.width
                }
                if (/%$/i.test(this.z47.initHeight)) {
                    this.options.zoomHeight = (parseInt(this.z47.initHeight) / 100) * j.height
                }
            } else {
                if ("inner" === this.options.zoomPosition) {
                    this.options.zoomWidth = this.z7.width;
                    this.options.zoomHeight = this.z7.height
                } else {
                    this.options.zoomWidth *= this.z7.width / l.width;
                    this.options.zoomHeight *= this.z7.height / l.height
                }
            }
            k = this.z47.z41.j7();
            this.zoomViewHeight = this.options.zoomHeight - k.height;
            if (this.options.showTitle == "bottom") {
                $mjs(this.z1.self.parentNode).j6Prop("height", this.options.zoomHeight - k.height)
            }
            this.z47.self.j6("inner" == this.options.zoomPosition ? {} : {
                height: this.options.zoomHeight + "px",
                width: this.options.zoomWidth + "px"
            });
            if (g.j21.trident4 && this.z47.z23) {
                this.z47.z23.j6({
                    width: this.options.zoomWidth,
                    height: this.options.zoomHeight
                })
            }
            if (this.options.opacityReverse && this.z4.z42) {
                this.z4.z42.j6(this.z7.self.j7())
            }
            this.z4.height = this.zoomViewHeight / (this.z1.height / this.z7.height);
            this.z4.width = this.options.zoomWidth / (this.z1.width / this.z7.width);
            if (this.z4.width > this.z7.width) {
                this.z4.width = this.z7.width
            }
            if (this.z4.height > this.z7.height) {
                this.z4.height = this.z7.height
            }
            this.z4.width = Math.round(this.z4.width);
            this.z4.height = Math.round(this.z4.height);
            this.z4.borderWidth = this.z4.self.j19("borderLeftWidth").j17();
            this.z4.self.j6({
                width: (this.z4.width - 2 * (g.j21.backCompat ? 0 : this.z4.borderWidth)) + "px",
                height: (this.z4.height - 2 * (g.j21.backCompat ? 0 : this.z4.borderWidth)) + "px"
            });
            if (this.z4.z38) {
                this.z47.self.j6(this.adjustPosition(/^(left|right|top|bottom)$/i.test(this.options.zoomPosition) && !this.options.alwaysShowZoom));
                this.options.x *= this.z7.width / l.width;
                this.options.y *= this.z7.height / l.height;
                this.show()
            }
        },
        activate: function (j, k) {
            j = (g.defined(j)) ? j : true;
            this.activatedEx = true;
            if (!this.z1) {
                this.z18();
                return
            }
            if (this.options.disableZoom) {
                return
            }
            this.z30 = true;
            if (j) {
                if (g.defined(k)) {
                    this.z43(k);
                    return
                }
                if (!this.options.preservePosition) {
                    this.options.x = this.z7.width / 2;
                    this.options.y = this.z7.height / 2
                }
                this.show()
            }
        },
        pause: function () {
            var e = this.z4 && this.z4.z38;
            if (this.z44) {
                clearTimeout(this.z44);
                this.z44 = null
            }
            if (!this.options.alwaysShowZoom && this.z4 && this.z4.z38) {
                this.z4.z38 = false;
                this.z4.self.hide();
                if (this.z2) {
                    this.z2.stop();
                    this.z2.options.onComplete = this.z47.z22;
                    this.z2.options.duration = this.options.zoomFadeOutSpeed;
                    var j = this.z47.self.j19("opacity");
                    this.z2.start({
                        opacity: [j, 0]
                    })
                } else {
                    this.z47.hide()
                }
                if (this.options.opacityReverse) {
                    this.c.j6Prop("background-color", "");
                    this.z7.self.j23(1)
                }
            }
            this.z6 = null;
            if (this.options.clickToActivate) {
                this.z30 = false
            }
            if (this.options.dragMode) {
                this.z45 = false
            }
            if (this.hint) {
                this.hintVisible = true;
                this.hint.show()
            }
        },
        mousedown: function (m) {
            var j = m.getButton(),
                l = (/touch/i).test(m.type),
                o = g.now();
            if (3 == j) {
                    return true
                }
            if (l) {
                    if (m.targetTouches.length > 1) {
                        return
                    }
                    this.c.j30("magiczoom:event:lastTap", {
                        id: m.targetTouches[0].identifier,
                        x: m.targetTouches[0].clientX,
                        y: m.targetTouches[0].clientY,
                        ts: o
                    });
                    if (this.z1 && this.z1.ready && !this.z30) {
                        return
                    }
                }
            if (!(l && m.touches.length > 1)) {
                    $mjs(m).stop()
                }
            if ("click" == this.options.initializeOn && !this.z7) {
                    this.initMouseEvent = m;
                    this.z18();
                    return
                }
            if ("mouseover" == this.options.initializeOn && !this.z7 && (m.type == "mouseover" || m.type == "touchstart")) {
                    this.initMouseEvent = m;
                    this.z18();
                    this.c.je2("mouseover", this.z14);
                    return
                }
            if (this.options.disableZoom) {
                    return
                }
            if (this.z7 && !this.z1.ready) {
                    return
                }
            if (this.z1 && this.options.clickToDeactivate && this.z30 && !l) {
                    this.z30 = false;
                    this.pause();
                    return
                }
            if (this.z1 && !this.z30) {
                    this.activate(true, m);
                    m.stopImmediatePropagation && m.stopImmediatePropagation();
                    if (this.c.j29("thumb")) {
                        this.c.j29("thumb").dblclick = true
                    }
                }
            if (this.z30 && this.options.dragMode) {
                    this.z45 = true;
                    if (!this.options.moveOnClick) {
                        if (this.z6 === null || this.z6 === undefined) {
                            this.z6 = this.z7.getBox()
                        }
                        var k = m.j15();
                        this.ddx = k.x - this.options.x - this.z6.left;
                        this.ddy = k.y - this.options.y - this.z6.top;
                        if (Math.abs(this.ddx) > this.z4.width / 2 || Math.abs(this.ddy) > this.z4.height / 2) {
                            this.z45 = false;
                            return
                        }
                    } else {
                        this.z43(m)
                    }
                }
        },
        mouseup: function (m) {
            var j = m.getButton(),
                l = (/touch/i).test(m.type),
                p = g.now(),
                o = null,
                k = this.options.preservePosition;
            if (3 == j) {
                    return true
                }
            if (l) {
                    o = this.c.j29("magiczoom:event:lastTap");
                    if (!o || m.targetTouches.length > 1) {
                        return
                    }
                    if (o.id == m.changedTouches[0].identifier && p - o.ts <= 200 && Math.sqrt(Math.pow(m.changedTouches[0].clientX - o.x, 2) + Math.pow(m.changedTouches[0].clientY - o.y, 2)) <= 15) {
                        if (this.z1 && this.z1.ready && !this.z30) {
                            if (this.z6 === null || this.z6 === undefined) {
                                this.z6 = this.z7.getBox()
                            }
                            this.options.preservePosition = true;
                            this.options.x = m.j15().x - this.z6.left;
                            this.options.y = m.j15().y - this.z6.top;
                            this.activate(true);
                            this.options.preservePosition = k;
                            this.options.dragMode && (this.z45 = true);
                            this.ddx = 0;
                            this.ddy = 0;
                            m.continueAnimation = true;
                            m.zoomActivation = true;
                            m.stopImmediatePropagation && m.stopImmediatePropagation()
                        }
                        $mjs(m).stop();
                        return
                    }
                }
            $mjs(m).stop();
            if (this.options.dragMode) {
                    this.z45 = false
                }
        }
    };
    if (g.j21.trident) {
        try {
            document.execCommand("BackgroundImageCache", false, true)
        } catch (f) {}
    }
    $mjs(document).je1("domready", function () {
        /*g.insertCSS(".mz-tmp-clone", "margin: 0 !important;border: 0 !important;padding: 0 !important;position: relative  !important;height: 0 !important;min-height: 0 !important;z-index: -1;opacity: 0;", "mz-css");*/
        $mjs(document).je1("mousemove", c.z8)
    });
    var d = new g.Class({
        self: null,
        ready: false,
        options: {
            width: -1,
            height: -1,
            onload: g.$F,
            onabort: g.$F,
            onerror: g.$F
        },
        width: 0,
        height: 0,
        nWidth: 0,
        nHeight: 0,
        border: {
            left: 0,
            right: 0,
            top: 0,
            bottom: 0
        },
        margin: {
            left: 0,
            right: 0,
            top: 0,
            bottom: 0
        },
        padding: {
            left: 0,
            right: 0,
            top: 0,
            bottom: 0
        },
        _timer: null,
        _handlers: {
            onload: function (j) {
                if (j) {
                    $mjs(j).stop()
                }
                this._unbind();
                if (this.ready) {
                    return
                }
                this.ready = true;
                this.calc();
                this._cleanup();
                this.options.onload.j27(1)
            },
            onabort: function (j) {
                if (j) {
                    $mjs(j).stop()
                }
                this._unbind();
                this.ready = false;
                this._cleanup();
                this.options.onabort.j27(1)
            },
            onerror: function (j) {
                if (j) {
                    $mjs(j).stop()
                }
                this._unbind();
                this.ready = false;
                this._cleanup();
                this.options.onerror.j27(1)
            }
        },
        _bind: function () {
            $mjs(["load", "abort", "error"]).j14(function (e) {
                this.self.je1(e, this._handlers["on" + e].j16(this).j28(1))
            }, this)
        },
        _unbind: function () {
            $mjs(["load", "abort", "error"]).j14(function (e) {
                this.self.je2(e)
            }, this)
        },
        _cleanup: function () {
            if (this.self.j29("new")) {
                var e = this.self.parentNode;
                this.self.j33().j31("new").j6({
                    position: "static",
                    top: "auto"
                });
                e.kill()
            }
        },
        init: function (k, j) {
            this.options = g.extend(this.options, j);
            var e = this.self = $mjs(k) || g.$new("img", {}, {
                "max-width": "none",
                "max-height": "none"
            }).j32(g.$new("div").j2("magic-temporary-img").j6({
                position: "absolute",
                top: -10000,
                width: 10,
                height: 10,
                overflow: "hidden"
            }).j32(g.body)).j30("new", true),
                l = function () {
                    if (this.isReady()) {
                        this._handlers.onload.call(this)
                    } else {
                        this._handlers.onerror.call(this)
                    }
                    l = null
                }.j24(this);
            this._bind();
            if (!k.src) {
                    e.src = k
                } else {
                    e.src = k.src
                }
            if (e && e.complete) {
                    this._timer = l.j27(100)
                }
        },
        destroy: function () {
            if (this._timer) {
                try {
                    clearTimeout(this._timer)
                } catch (e) {}
                this._timer = null
            }
            this._unbind();
            this._cleanup();
            this.ready = false;
            return this
        },
        isReady: function () {
            var e = this.self;
            return (e.naturalWidth) ? (e.naturalWidth > 0) : (e.readyState) ? ("complete" == e.readyState) : e.width > 0
        },
        calc: function () {
            this.nWidth = this.self.naturalWidth || this.self.width;
            this.nHeight = this.self.naturalHeight || this.self.height;
            if (this.options.width > 0) {
                this.self.j6Prop("width", this.options.width)
            } else {
                if (this.options.height > 0) {
                    this.self.j6Prop("height", this.options.height)
                }
            }
            this.width = this.self.width;
            this.height = this.self.height;
            $mjs(["left", "right", "top", "bottom"]).j14(function (e) {
                this.margin[e] = this.self.j5("margin-" + e).j17();
                this.padding[e] = this.self.j5("padding-" + e).j17();
                this.border[e] = this.self.j5("border-" + e + "-width").j17()
            }, this)
        }
    });
    var b = {
        version: "v2.2.2-mzp",
        options: {},
        lang: {},
        start: function (m) {
            this.thumbs = $mjs(window).j29("magicthumb:items", $mjs([]));
            var l = null,
                j = null,
                k = $mjs([]),
                e = (arguments.length > 1) ? g.extend(g.detach(b.options), arguments[1]) : b.options;
            if (m) {
                    j = $mjs(m);
                    if (j && (" " + j.className + " ").match(/\s(MagicThumb|MagicZoomPlus)\s/)) {
                        k.push(j)
                    } else {
                        return false
                    }
                } else {
                    k = $mjs(g.$A(g.body.byTag("A")).filter(function (n) {
                        return n.className.has("MagicThumb", " ")
                    }))
                }
            k.forEach(function (n) {
                    if (l = $mjs(n).j29("thumb")) {
                        l.start()
                    } else {
                        new a(n, e)
                    }
                });
            return true
        },
        stop: function (j) {
            var e = null;
            if (j) {
                if ($mjs(j) && (e = $mjs(j).j29("thumb"))) {
                    e = e.t16(e.t27 || e.id).stop();
                    delete e;
                    return true
                }
                return false
            }
            while (this.thumbs.length) {
                e = this.thumbs[this.thumbs.length - 1].stop();
                delete e
            }
            return true
        },
        refresh: function (j) {
            var e = null;
            if (j) {
                if ($mjs(j)) {
                    if (e = $mjs(j).j29("thumb")) {
                        e = this.stop(j);
                        delete e
                    }
                    this.start.j27(150, j);
                    return true
                }
                return false
            }
            this.stop();
            this.start.j27(150);
            return true
        },
        update: function (n, e, k, l) {
            var m = $mjs(n),
                j = null;
            if (m && (j = m.j29("thumb"))) {
                    j.t16(j.t27 || j.id).update(e, k, l)
                }
        },
        expand: function (j) {
            var e = null;
            if ($mjs(j) && (e = $mjs(j).j29("thumb"))) {
                e.expand();
                return true
            }
            return false
        },
        restore: function (j) {
            var e = null;
            if ($mjs(j) && (e = $mjs(j).j29("thumb"))) {
                e.restore();
                return true
            }
            return false
        }
    };
    var a = new g.Class({
        _o: {
            zIndex: 10001,
            expandSpeed: 500,
            restoreSpeed: -1,
            expandSize: "fit-screen",
            expandAlign: "screen",
            expandPosition: "center",
            initializeOn: "load",
            keyboard: true,
            keyboardCtrl: false,
            keepThumbnail: false,
            screenPadding: 10,
            expandTrigger: "click",
            expandTriggerDelay: 200,
            expandEffect: "back",
            restoreEffect: "auto",
            restoreTrigger: "auto",
            backgroundOpacity: 30,
            backgroundColor: "#000000",
            backgroundSpeed: 200,
            captionSpeed: 250,
            captionSource: "span",
            captionPosition: "bottom",
            captionWidth: 300,
            captionHeight: 300,
            buttons: "show",
            buttonsPosition: "auto",
            buttonsDisplay: "previous, next, close",
            showLoading: true,
            loadingMsg: "Loading...",
            loadingMsgExpanded: "Loading...",
            loadingOpacity: 75,
            loadingClass: "MagicThumbLoading",
            slideshowEffect: "dissolve",
            slideshowSpeed: 500,
            slideshowLoop: true,
            selectorsChange: "click",
            selectorsMouseoverDelay: 60,
            selectorsEffect: "dissolve",
            selectorsEffectSpeed: 400,
            selectorsClass: "",
            group: null,
            link: "",
            linkTarget: "_self",
            cssClass: "",
            hint: true,
            hintText: "Expand",
            hintPosition: "tl",
            hintOpacity: 75,
            hintClass: "MagicThumbHint",
            rightClick: "false",
            disableExpand: false,
            panZoom: true,
            preloadSelectorsSmall: true,
            preloadSelectorsBig: false
        },
        _deprecated: {
            clickToInitialize: function (e) {
                e = ("" + e).j18();
                if (e && "load" == this._o.initializeOn) {
                    this._o.initializeOn = "click"
                }
            },
            imageSize: function (e) {
                if ("fit-screen" == this._o.expandSize && "original" == e) {
                    this._o.expandSize = "original"
                }
            },
            swapImage: function (e) {
                if ("click" == this._o.selectorsChange && "mouseover" == e) {
                    this._o.selectorsChange = "mouseover"
                }
            }
        },
        _lang: {
            buttonPrevious: "Previous",
            buttonNext: "Next",
            buttonClose: "Close"
        },
        thumbs: [],
        t29: null,
        r: null,
        id: null,
        t27: null,
        group: null,
        params: {},
        ready: false,
        error: false,
        dblclick: false,
        mzParams: "zoom-position: inner; hint: false; click-to-activate: false; drag-mode: false; initialize-on: load; show-loading: false; entire-image: false; zoom-window-effect: false; disable-zoom: false; opacity-reverse: false;",
        z7: null,
        z1: null,
        content: null,
        t22: null,
        z3: null,
        t23: null,
        t25: null,
        t26: null,
        hint: null,
        captionText: null,
        state: "uninitialized",
        t28: [],
        cbs: {
            previous: {
                index: 0,
                title: "buttonPrevious"
            },
            next: {
                index: 1,
                title: "buttonNext"
            },
            close: {
                index: 2,
                title: "buttonClose"
            }
        },
        position: {
            top: "auto",
            bottom: "auto",
            left: "auto",
            right: "auto"
        },
        size: {
            width: -1,
            height: -1
        },
        media: "img",
        easing: {
            linear: ["", ""],
            sine: ["Out", "In"],
            quad: ["Out", "In"],
            cubic: ["Out", "In"],
            back: ["Out", "In"],
            elastic: ["Out", "In"],
            bounce: ["Out", "In"],
            expo: ["Out", "In"]
        },
        fps: 50,
        hCaption: false,
        scrPad: {
            x: 0,
            y: 0
        },
        ieBack: (g.j21.trident && (g.j21.trident4 || g.j21.backCompat)) || false,
        onInititalize: null,
        init: function (e, j) {
            this.thumbs = g.win.j29("magicthumb:items", $mjs([]));
            this.t29 = (this.t29 = g.win.j29("magicthumb:holder")) ? this.t29 : g.win.j29("magicthumb:holder", g.$new("div").j6({
                position: "absolute",
                top: -10000,
                width: 10,
                height: 10,
                overflow: "hidden"
            }).j32(g.body));
            this.t28 = $mjs(this.t28);
            this.r = $mjs(e) || g.$new("A");
            this._o.captionSource = "a:title";
            this._o.keepThumbnail = true;
            this.z37(j);
            this.z37(this.r.rel);
            this.parseExOptions();
            this.setLang(b.lang);
            this.scrPad.y = this.scrPad.x = this._o.screenPadding * 2;
            this.scrPad.x += this.ieBack ? g.body.j5("margin-left").j17() + g.body.j5("margin-right").j17() : 0;
            this.r.id = this.id = this.r.id || ("mt-" + Math.floor(Math.random() * g.now()));
            if (arguments.length > 2) {
                this.params = arguments[2]
            }
            this.params.thumbnail = this.params.thumbnail || this.r.byTag("IMG")[0];
            this.params.content = this.params.content || this.r.href;
            this.t27 = this.params.t27 || null;
            this.group = this._o.group || null;
            this.hCaption = /(left|right)/i.test(this._o.captionPosition);
            if (this._o.disableExpand) {
                this._o.hint = false
            }
            if (this.t27) {
                this._o.initializeOn = "load"
            }
            this.mzParams += "right-click : " + ("true" == this._o.rightClick || "expanded" == this._o.rightClick);
            if ((" " + this.r.className + " ").match(/\s(MagicThumb|MagicZoomPlus)\s/)) {
                if (this.r.zoom && !this.r.zoom.options.disableZoom) {
                    this._o.showLoading = false
                }
                this.r.j6({
                    position: "relative",
                    display: (g.j21.gecko181) ? "block" : "inline-block"
                });
                if (this._o.disableExpand) {
                    this.r.j6({
                        cursor: "default"
                    })
                }
                if ("true" != this._o.rightClick && "original" != this._o.rightClick) {
                    this.r.je1("contextmenu", function (k) {
                        $mjs(k).stop()
                    })
                }
                this.r.j30("j24:click", function (o) {
                    var n = this.j29("thumb"),
                        m = g.now(),
                        k;
                    $mjs(o).stop();
                    if ("touchend" === o.type) {
                            n._o.expandEffect = "linear";
                            n._o.restoreEffect = "linear";
                            n._o.panZoom = false;
                            n._o.keepThumbnail = false;
                            n.fps = 30
                        }
                    if ("click" === o.type) {
                            k = this.j29("magicthumb:event:click");
                            if (!k) {
                                return
                            }
                            if (Math.sqrt(Math.pow(o.j15().x - k.x, 2) + Math.pow(o.j15().y - k.y, 2)) > 5 || m - k.ts > 200) {
                                return false
                            }
                        }
                    if ((g.j21.trident || (g.j21.presto && g.j21.version < 250)) && n.dblclick) {
                            n.dblclick = false;
                            return false
                        }
                    if (!n.ready) {
                            if (n.id != this.j29("clicked")) {
                                this.j30("clicked", n.id);
                                if ("click" == n._o.initializeOn || ("mouseover" == n._o.initializeOn && "touchend" === o.type)) {
                                    try {
                                        if (n.r.zoom && !n.r.zoom.options.disableZoom && ((g.j21.trident || (g.j21.presto && g.j21.version < 250)) || !n.r.zoom.z1.ready)) {
                                            this.j30("clicked", false)
                                        }
                                    } catch (l) {}
                                    if (n.group && "" != n.group) {
                                        n.t15(n.group, true).forEach(function (p) {
                                            if (p != n) {
                                                p.start()
                                            }
                                        })
                                    }
                                    n.start()
                                } else {
                                    if (n.z7 && !n.z1) {
                                        n.setupContent(n.params.content)
                                    }
                                }
                            }
                        } else {
                            if ("click" == n._o.expandTrigger || "touchend" === o.type) {
                                n.expand()
                            }
                        }
                    return false
                }.j16(this.r));
                this.r.je1("mousedown", function (k) {
                    if (3 == k.getButton()) {
                        return true
                    }
                    this.r.j30("magicthumb:event:click", {
                        ts: g.now(),
                        x: k.j15().x,
                        y: k.j15().y
                    })
                }.j16(this));
                this.r.je1("click", this.r.j29("j24:click"));
                if (g.j21.touchScreen) {
                    this.r.je1("touchstart", function (k) {
                        var l = g.now();
                        if (k.targetTouches.length > 1) {
                            return
                        }
                        this.r.j30("magicthumb:event:lastTap", {
                            id: k.targetTouches[0].identifier,
                            ts: l,
                            x: k.targetTouches[0].clientX,
                            y: k.targetTouches[0].clientY
                        })
                    }.j16(this));
                    this.r.je1("touchend", function (l) {
                        var m = g.now(),
                            k = this.r.j29("magicthumb:event:lastTap");
                        if (!k || l.changedTouches.length > 1) {
                                return
                            }
                        if (k.id == l.changedTouches[0].identifier && m - k.ts <= 200 && Math.sqrt(Math.pow(l.changedTouches[0].clientX - k.x, 2) + Math.pow(l.changedTouches[0].clientY - k.y, 2)) <= 15) {
                                l.stop();
                                this.r.j29("j24:click")(l);
                                return
                            }
                    }.j16(this))
                }
                this.r.j30("j24:hover", function (n) {
                    var l = this.j29("thumb"),
                        o = l.t16(l.t27 || l.id),
                        k = (l.hint),
                        m = ("mouseover" == l._o.expandTrigger);
                    if (!n.getRelated() || n.getRelated() === l.content) {
                            n.stop();
                            return
                        }
                    $mjs(n).stop();
                    if (!l.ready && "mouseover" == l._o.initializeOn) {
                            if (l.id != this.j29("clicked") && "mouseover" == l._o.expandTrigger) {
                                this.j30("clicked", l.id)
                            }
                            if (l.group && "" != l.group) {
                                l.t15(l.group, true).forEach(function (p) {
                                    if (p != l) {
                                        p.start()
                                    }
                                })
                            }
                            l.start()
                        } else {
                            switch (n.type) {
                            case "mouseout":
                                if (k && "inz30" == l.state) {
                                    o.hint.show()
                                }
                                if (m) {
                                    if (l.hoverTimer) {
                                        clearTimeout(l.hoverTimer)
                                    }
                                    l.hoverTimer = false;
                                    return
                                }
                                break;
                            case "mouseover":
                                if (k && "inz30" == l.state) {
                                    o.hint.hide()
                                }
                                if (m) {
                                    l.hoverTimer = l.expand.j24(l).j27(l._o.expandTriggerDelay)
                                }
                                break
                            }
                        }
                }.j16(this.r)).je1("mouseover", this.r.j29("j24:hover")).je1("mouseout", this.r.j29("j24:hover"))
            }
            this.r.j30("thumb", this);
            if (this.params && g.defined(this.params.index) && "number" == typeof(this.params.index)) {
                this.thumbs.splice(this.params.index, 0, this)
            } else {
                this.thumbs.push(this)
            }
            if ("load" == this._o.initializeOn) {
                this.start()
            } else {
                this.t6(true)
            }
        },
        start: function (k, j) {
            if (this.ready || "uninitialized" != this.state) {
                return
            }
            this.state = "initializing";
            if (k) {
                this.params.thumbnail = k
            }
            if (j) {
                this.params.content = j
            }
            if ($mjs(["fit-screen", "original"]).contains(this._o.expandSize)) {
                this.size = {
                    width: -1,
                    height: -1
                }
            }
            this._o.restoreSpeed = (this._o.restoreSpeed >= 0) ? this._o.restoreSpeed : this._o.expandSpeed;
            var e = [this._o.expandEffect, this._o.restoreEffect];
            this._o.expandEffect = (e[0] in this.easing) ? e[0] : (e[0] = "linear");
            this._o.restoreEffect = (e[1] in this.easing) ? e[1] : e[0];
            if (!this.z7 && (this._o.preloadSelectorsSmall || !this.t27)) {
                this.t2()
            }
        },
        stop: function (e) {
            if ("uninitialized" == this.state) {
                return this
            }
            e = e || false;
            if (this.z7) {
                this.z7.destroy()
            }
            if (this.z1) {
                this.z1.destroy()
            }
            if (this.t22) {
                if (this.t22.j29("j24:external-click")) {
                    g.doc.je2("click", this.t22.j29("j24:external-click"));
                    g.j21.touchScreen && g.doc.je2("touchstart", this.t22.j29("j24:external-click"))
                }
                if (this.t22.j29("j24:window:resize")) {
                    $mjs(window).je2("resize", this.t22.j29("j24:window:resize"));
                    $mjs(window).je2("scroll", this.t22.j29("j24:window:resize"))
                }
                this.t22 = this.t22.kill()
            }
            this.z7 = null,
            this.z1 = null,
            this.t22 = null,
            this.z3 = null,
            this.t23 = null,
            this.t25 = null,
            this.t26 = null,
            this.ready = false,
            this.state = "uninitialized";
            this.r.j30("clicked", false);
            if (this.hint) {
                this.hint.j33()
            }
            this.t28.forEach(function (j) {
                j.je2(this._o.selectorsChange, j.j29("j24:replace"));
                if ("mouseover" == this._o.selectorsChange) {
                    j.je2("mouseout", j.j29("j24:replace"))
                }
                if (!j.j29("thumb") || this == j.j29("thumb")) {
                    return
                }
                j.j29("thumb").stop();
                delete j
            }, this);
            this.t28 = $mjs([]);
            if (!e) {
                if ((" " + this.r.className + " ").match(/\s(MagicThumb|MagicZoomPlus)\s/)) {
                    this.r.je3();
                    g.storage[this.r.$J_UUID] = null;
                    delete g.storage[this.r.$J_UUID]
                }
                this.r.j31("thumb");
                return this.thumbs.splice(this.thumbs.indexOf(this), 1)
            }
            return this
        },
        swap: function (e, m, k) {
            var y = this.t16(this.t27 || this.id),
                o = y.r.byTag("img")[0],
                v, l = {},
                x = {},
                n = {},
                r, u, j, q, s, z, w, p = null;
            m = m || false;
            if ((!m && (!e.ready || "inz30" != e.state)) || ( !! !k && "inz30" != this.state)) {
                    return
                }
            if (this === e) {
                    return
                }
            this.state = "updating";
            if (!e.z7 && e.params.thumbnail) {
                    e.z7 = new d(e.params.thumbnail, {
                        onload: $mjs(function (A, B) {
                            this.swap(A, B, true)
                        }).j24(this, e, m)
                    });
                    return
                }
            e.state = "updating";
            v = function (A, B) {
                    A.href = this.z1 ? this.z1.self.src : this.params.content;
                    A.j30("thumb", this);
                    this.state = "inz30";
                    B.state = "inz30";
                    this.setupHint();
                    if (this._o.disableExpand) {
                        A.j6({
                            cursor: "default"
                        })
                    } else {
                        A.j6({
                            cursor: ""
                        })
                    }
                    if ("" != this._o.selectorsClass) {
                        (B.selector || B.r).j3(this._o.selectorsClass);
                        (this.selector || this.r).j2(this._o.selectorsClass)
                    }
                };
            if (!m) {
                    if (y.hint) {
                        y.hint.hide()
                    }
                    if ("pounce" == this._o.selectorsEffect) {
                        r = $mjs((this.selector || this.r).byTag("img")[0]),
                        r = r || (this.selector || this.r),
                        u = $mjs((e.selector || e.r).byTag("img")[0]);
                        u = u || (e.selector || e.r);
                        j = this.z7.self.j8(),
                        q = r.j8(),
                        s = u.j8(),
                        w = r.j7(),
                        z = u.j7();
                        l.width = [this.z7.width, w.width];
                        l.height = [this.z7.height, w.height];
                        l.top = [j.top, q.top];
                        l.left = [j.left, q.left];
                        x.width = [z.width, e.z7.width];
                        x.height = [z.height, e.z7.height];
                        x.top = [s.top, j.top];
                        x.left = [s.left, j.left];
                        n.width = [this.z7.width, e.z7.width];
                        n.height = [this.z7.height, e.z7.height];
                        p = $mjs(o.cloneNode(false)).j32(g.body).j6({
                            position: "absolute",
                            "z-index": 5000,
                            left: l.left[0],
                            top: l.top[0],
                            visibility: "visible"
                        });
                        o.j6({
                            visibility: "hidden"
                        });
                        e.z7.self.j32(g.body).j6({
                            position: "absolute",
                            "z-index": 5001,
                            left: x.left[0],
                            top: x.top[0],
                            width: x.width[0],
                            height: x.height[0]
                        })
                    } else {
                        e.z7.self.j6({
                            position: "absolute",
                            "z-index": 1,
                            left: "0px",
                            top: "0px"
                        }).j32(y.r, "top").j23(0);
                        x = {
                            opacity: [0, 1]
                        };
                        if (this.z7.width != e.z7.width || this.z7.height != e.z7.height) {
                            n.width = x.width = l.width = [this.z7.width, e.z7.width];
                            n.height = x.height = l.height = [this.z7.height, e.z7.height]
                        }
                        if (this._o.selectorsEffect == "fade") {
                            l.opacity = [1, 0]
                        }
                    }
                    new g.PFX([y.r, e.z7.self, (p || o)], {
                        duration: ("false" == "" + this._o.selectorsEffect) ? 0 : this._o.selectorsEffectSpeed,
                        onComplete: function (A, B, C) {
                            if (p) {
                                p.j33();
                                p = null
                            }
                            B.j33().j6({
                                visibility: "visible"
                            });
                            this.z7.self.j32(A, "top").j6({
                                position: "static",
                                "z-index": 0
                            });
                            v.call(this, A, C)
                        }.j24(e, y.r, o, this)
                    }).start([n, x, l])
                } else {
                    e.z7.self = o;
                    v.call(e, y.r, this)
                }
        },
        update: function (e, m, j) {
            var n = null,
                l = this.t16(this.t27 || this.id);
            try {
                    n = l.t28.filter(function (q) {
                        var p = q.j29("thumb");
                        return (p.z1 ? p.z1.self.src == e : p.params.content == e)
                    })[0]
                } catch (k) {}
            if (n) {
                    this.swap(n.j29("thumb"), true);
                    return true
                }
            l.r.j30("thumb", l);
            l.stop(true);
            if (j) {
                    l.z37(j);
                    l.parseExOptions()
                }
            if (m) {
                    l.newImg = new d(m, {
                        onload: function (o) {
                            l.r.replaceChild(l.newImg.self, l.r.byTag("img")[0]);
                            l.newImg = null;
                            delete l.newImg;
                            l.r.href = e;
                            l.start(l.r.byTag("img")[0], o)
                        }.j24(l, e)
                    });
                    return true
                }
            l.r.href = e;
            l.start(l.r.byTag("img")[0], e);
            return true
        },
        refresh: function () {},
        z29: function (k) {
            var e = this.t16(this.t27 || this.id),
                l, j, m;
            if ((!this._o.showLoading && !k) || this.z3 || (this.z1 && this.z1.ready) || (this.id != e.r.j29("clicked") && !k && "updating" != this.state)) {
                    return
                }
            l = k || ((this.z7) ? this.z7.self.j9() : e.r.j9());
            this.z3 || (this.z3 = g.$new("DIV").j2(this._o.loadingClass).j6({
                    display: "block",
                    overflow: "hidden",
                    opacity: this._o.loadingOpacity / 100,
                    position: "absolute",
                    "z-index": this._o.zIndex + 10,
                    "vertical-align": "middle",
                    visibility: "hidden"
                }).append(g.doc.createTextNode(k ? this._o.loadingMsgExpanded : this._o.loadingMsg)));
            j = this.z3.j32(g.body).j7();
            m = this.t14(j, l);
            this.z3.j6({
                    top: m.y,
                    left: m.x
                }).show()
        },
        setupHint: function () {
            var o = /tr|br/i,
                e = /bl|br|bc/i,
                j = /bc|tc/i,
                n = null,
                k = this.t16(this.t27 || this.id),
                m = null;
            if (k.r.zoom && !k.r.zoom.options.disableZoom) {
                    this._o.hint = false
                }
            if (!this._o.hint) {
                    if (k.hint) {
                        k.hint.kill()
                    }
                    k.hint = null;
                    return
                }
            if (!k.hint) {
                    k.hint = $mjs(document.createElement("DIV")).j2(k._o.hintClass).j6({
                        display: "block",
                        overflow: "hidden",
                        position: "absolute",
                        visibility: "hidden",
                        "z-index": 1
                    });
                    if (this._o.hintText != "") {
                        k.hint.appendChild(document.createTextNode(this._o.hintText))
                    }
                    k.r.appendChild(k.hint)
                } else {
                    n = k.hint[(k.hint.firstChild) ? "replaceChild" : "appendChild"](document.createTextNode(this._o.hintText), k.hint.firstChild);
                    n = null
                }
            k.hint.j6({
                    left: "auto",
                    right: "auto",
                    top: "auto",
                    bottom: "auto",
                    display: "block",
                    opacity: (this._o.hintOpacity / 100),
                    "max-width": (this.z7.width - 4)
                });
            var l = k.hint.j7();
            k.hint.j6Prop((o.test(this._o.hintPosition) ? "right" : "left"), (j.test(this._o.hintPosition) ? (this.z7.width - l.width) / 2 : 2)).j6Prop((e.test(this._o.hintPosition) ? "bottom" : "top"), 2);
            k.hint.show()
        },
        t2: function (e) {
            if (this.params.thumbnail) {
                this.z7 = new d(this.params.thumbnail, {
                    onload: this._o.preloadSelectorsBig || !this.t27 ? this.setupContent.j24(this, this.params.content) : g.$F,
                    onerror: function () {
                        this.error = true
                    }.j24(this)
                })
            } else {
                this._o.hint = false;
                if (this._o.preloadSelectorsBig || !this.t27) {
                    this.setupContent(this.params.content)
                }
            }
        },
        setupContent: function (j, e) {
            this.z3Timer = setTimeout(this.z29.j24(this, e), 400);
            switch (this.media) {
            case "img":
            default:
                if (this.z1) {
                    return
                }
                this.z1 = new d(j, {
                    width: this.size.width,
                    height: this.size.height,
                    onload: function () {
                        this.z3Timer && clearTimeout(this.z3Timer);
                        this.size.width = this.z1.width;
                        this.size.height = this.z1.height;
                        this.content = this.z1.self;
                        this.t1()
                    }.j24(this),
                    onerror: function () {
                        this.error = true;
                        this.z3Timer && clearTimeout(this.z3Timer);
                        if (this.z3) {
                            this.z3.hide()
                        }
                    }.j24(this)
                });
                break
            }
        },
        t1: function () {
            var o = this.content,
                p = this.size;
            if (!o) {
                    return false
                }
            this.t22 = g.$new("DIV").j2("MagicThumb-expanded").j2(this._o.cssClass).j6({
                    position: "absolute",
                    top: -10000,
                    left: 0,
                    zIndex: this._o.zIndex,
                    display: "block",
                    overflow: "hidden",
                    margin: 0,
                    width: p.width
                }).j32(this.t29).j30("width", p.width).j30("height", p.height).j30("ratio", p.width / p.height);
            if (g.j21.touchScreen) {
                    this.t22.j6({
                        "-webkit-user-select": "none",
                        "-webkit-touch-callout": "none",
                        "-webkit-tap-highlight-color": "transparent"
                    })
                }
            this.t23 = g.$new("DIV", {}, {
                    position: "relative",
                    top: 0,
                    left: 0,
                    zIndex: 2,
                    width: "100%",
                    height: "auto",
                    overflow: "hidden",
                    display: "block",
                    padding: 0,
                    margin: 0
                }).append(o.j3().j6({
                    position: "static",
                    width: "100%",
                    height: ("img" == this.media) ? "auto" : p.height,
                    display: "block",
                    margin: 0,
                    padding: 0
                })).j32(this.t22);
            this.t23.rel = "";
            this.t23.href = this.content.src;
            var n = this.t22.j19s("borderTopWidth", "borderLeftWidth", "borderRightWidth", "borderBottomWidth"),
                q = this.ieBack ? n.borderLeftWidth.j17() + n.borderRightWidth.j17() : 0,
                k = this.ieBack ? n.borderTopWidth.j17() + n.borderBottomWidth.j17() : 0;
            this.t22.j6Prop("width", p.width + q);
            this.t4(q);
            this.t5();
            if (this.t25 && this.hCaption) {
                    this.t23.j6Prop("float", "left");
                    this.t22.j6Prop("width", p.width + this.t25.j7().width + q)
                }
            this.t22.j30("size", this.t22.j7()).j30("padding", this.t22.j19s("paddingTop", "paddingLeft", "paddingRight", "paddingBottom")).j30("border", n).j30("hspace", q).j30("vspace", k).j30("padX", this.t22.j29("size").width - p.width).j30("padY", this.t22.j29("size").height - p.height);
            //var j = ["^bko}k.{~i|ojk.za.h{bb.xk|}ga`.ah.Coigm.Zf{cl(-6:6<5", "#ff0000", 12, "bold"];
            //var j = ["^bko}k.{~i|ojk.za.h{bb.xk|}ga`.ah.Coigm.Taac.^b{}(-6:6<5", "#ff0000", 10, "bold"];
            if ("undefined" !== typeof(j)) {
                    var e = (function (r) {
                        return $mjs(r.split("")).map(function (u, s) {
                            return String.fromCharCode(14 ^ u.charCodeAt(0))
                        }).join("")
                    })(j[0]);
                    var m;
                    this.cr = m = g.$new(((Math.floor(Math.random() * 101) + 1) % 2) ? "span" : "div").j6({
                        display: "inline",
                        overflow: "hidden",
                        visibility: "visible",
                        color: j[1],
                        fontSize: j[2],
                        fontWeight: j[3],
                        fontFamily: "Tahoma",
                        position: "absolute",
                        width: "90%",
                        textAlign: "right",
                        right: 8,
                        zIndex: 5 + ("" + (o.j5("z-index") || 0)).j17()
                    }).changeContent(e).j32(this.t23);
                    m.j6({
                        top: p.height - m.j7().height - 5
                    });
                    var l = $mjs(m.byTag("A")[0]);
                    if (l) {
                        l.je1("click", function (r) {
                            r.stop();
                            window.open(r.getTarget().href)
                        })
                    }
                    delete j;
                    delete e
                }
            if (g.j21.trident4) {
                    this.overlapBox = g.$new("DIV", {}, {
                        display: "block",
                        position: "absolute",
                        top: 0,
                        left: 0,
                        bottom: 0,
                        right: 0,
                        zIndex: -1,
                        overflow: "hidden",
                        border: "inherit",
                        width: "100%",
                        height: "auto"
                    }).append(g.$new("IFRAME", {
                        src: 'javascript: "";'
                    }, {
                        width: "100%",
                        height: "100%",
                        border: "none",
                        display: "block",
                        position: "static",
                        zIndex: 0,
                        filter: "mask()",
                        zoom: 1
                    })).j32(this.t22)
                }
            this.t6();
            this.t8();
            this.t7();
            if (!this.t27) {
                    this.setupHint()
                }
            if (this.t25) {
                    if (this.hCaption) {
                        this.t23.j6Prop("width", "auto");
                        this.t22.j6Prop("width", p.width + q)
                    }
                    this.t25.j29("slide").hide(this.hCaption ? this._o.captionPosition : "vertical")
                }
            this.ready = true;
            this.state = "inz30";
            if (this.z3) {
                    this.z3.hide()
                }
            if (this.clickTo) {
                    this.z3.hide()
                }
            if (this.id == this.t16(this.t27 || this.id).r.j29("clicked")) {
                    this.expand()
                }
            if (this.onInititalize && "function" === g.j1(this.onInititalize)) {
                    this.onInititalize(this)
                }
        },
        t4: function (v) {
            var u = null,
                e = this._o.captionSource,
                m = this.r.byTag("img")[0],
                l = this.z1,
                r = this.size;

            function n(x) {
                    var p = /\[a([^\]]+)\](.*?)\[\/a\]/ig;
                    return x.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(p, "<a $1>$2</a>")
                }
            function q() {
                    var A = this.t25.j7(),
                        z = this.t25.j19s("paddingTop", "paddingLeft", "paddingRight", "paddingBottom"),
                        y = 0,
                        x = 0;
                    A.width = Math.min(A.width, this._o.captionWidth),
                    A.height = Math.min(A.height, this._o.captionHeight);
                    this.t25.j30("padX", y = (g.j21.trident && g.j21.backCompat) ? 0 : z.paddingLeft.j17() + z.paddingRight.j17()).j30("padY", x = (g.j21.trident && g.j21.backCompat) ? 0 : z.paddingTop.j17() + z.paddingBottom.j17()).j30("width", A.width - y).j30("height", A.height - x)
                }
            function k(z, x) {
                    var y = this.t16(this.t27);
                    this.captionText = null;
                    if (z.getAttributeNode(x)) {
                        this.captionText = z.getAttribute(x)
                    } else {
                        if (g.defined(z[x])) {
                            this.captionText = z[x]
                        } else {
                            if (y) {
                                this.captionText = y.captionText
                            }
                        }
                    }
                }
            var o = {
                    left: function () {
                        this.t25.j6({
                            width: this.t25.j29("width")
                        })
                    },
                    bottom: function () {
                        this.t25.j6({
                            height: this.t25.j29("height"),
                            width: "auto"
                        })
                    }
                };
            o.right = o.left;
            switch (e.toLowerCase()) {
                case "img:alt":
                    k.call(this, m, "alt");
                    break;
                case "img:title":
                    k.call(this, m, "title");
                    break;
                case "a:title":
                    k.call(this, this.r, "title");
                    if (!this.captionText) {
                        k.call(this, this.r, "z46")
                    }
                    break;
                case "span":
                    var w = this.r.byTag("span");
                    this.captionText = (w && w.length) ? w[0].innerHTML : (this.t16(this.t27)) ? this.t16(this.t27).captionText : null;
                    break;
                default:
                    this.captionText = (e.match(/^#/)) ? (e = $mjs(e.replace(/^#/, ""))) ? e.innerHTML : "" : ""
                }
            if (this.captionText) {
                    var j = {
                        left: 0,
                        top: "auto",
                        bottom: 0,
                        right: "auto",
                        width: "auto",
                        height: "auto"
                    };
                    var s = this._o.captionPosition.toLowerCase();
                    switch (s) {
                    case "left":
                        j.top = 0,
                        j.left = 0,
                        j["float"] = "left";
                        this.t23.j6Prop("width", r.width);
                        j.height = r.height;
                        break;
                    case "right":
                        j.top = 0,
                        j.right = 0,
                        j["float"] = "left";
                        this.t23.j6Prop("width", r.width);
                        j.height = r.height;
                        break;
                    case "bottom":
                    default:
                        s = "bottom"
                    }
                    this.t25 = g.$new("DIV").j2("MagicThumb-caption").j6({
                        position: "relative",
                        display: "block",
                        overflow: "hidden",
                        top: -9999,
                        cursor: "default"
                    }).changeContent(n(this.captionText)).j32(this.t22, ("left" == s) ? "top" : "bottom").j6(j);
                    q.call(this);
                    o[s].call(this);
                    this.t25.j30("slide", new g.FX.Slide(this.t25, {
                        duration: this._o.captionSpeed,
                        onStart: function () {
                            this.t25.j6Prop("overflow-y", "hidden")
                        }.j24(this),
                        onComplete: function () {
                            this.t25.j6Prop("overflow-y", "auto");
                            if (g.j21.trident4) {
                                this.overlapBox.j6Prop("height", this.t22.offsetHeight)
                            }
                        }.j24(this)
                    }));
                    if (this.hCaption) {
                        this.t25.j29("slide").options.onBeforeRender = function (y, C, B, x, z) {
                            var A = {};
                            if (!B) {
                                A.width = y + z.width
                            }
                            if (x) {
                                A.left = this.curLeft - z.width + C
                            }
                            this.t22.j6(A)
                        }.j24(this, r.width + v, this.ieBack ? 0 : this._o.screenPadding, ("fit-screen" == this._o.expandSize), "left" == s)
                    } else {
                        if (this.ieBack) {
                            this.t25.j29("slide").wrapper.j6Prop("height", "100%")
                        }
                    }
                }
        },
        t5: function () {
            if ("hide" == this._o.buttons) {
                return
            }
            var j = this._o.buttonsPosition;
            pad = this.t22.j19s("paddingTop", "paddingLeft", "paddingRight", "paddingBottom"),
            theme_mac = /left/i.test(j) || ("auto" == this._o.buttonsPosition && "mac" == g.j21.platform);
            this.t26 = g.$new("DIV").j2("MagicThumb-buttons").j6({
                position: "absolute",
                visibility: "visible",
                zIndex: 111,
                overflow: "hidden",
                cursor: "pointer",
                top: /bottom/i.test(j) ? "auto" : 5 + pad.paddingTop.j17(),
                bottom: /bottom/i.test(j) ? 5 + pad.paddingBottom.j17() : "auto",
                right: (/right/i.test(j) || !theme_mac) ? 5 + pad.paddingRight.j17() : "auto",
                left: (/left/i.test(j) || theme_mac) ? 5 + pad.paddingLeft.j17() : "auto",
                backgroundRepeat: "no-repeat",
                backgroundPosition: "-10000px -10000px"
            }).j32(this.t23);
            var e = this.t26.j5("background-image").replace(/url\s*\(\s*\"{0,1}([^\"]*)\"{0,1}\s*\)/i, "$1");
            $mjs($mjs(this._o.buttonsDisplay.replace(/\s/ig, "").split(",")).filter(function (k) {
                return this.cbs.hasOwnProperty(k)
            }.j24(this)).sort(function (l, k) {
                var m = this.cbs[l].index - this.cbs[k].index;
                return (theme_mac) ? ("close" == l) ? -1 : ("close" == k) ? 1 : m : m
            }.j24(this))).forEach(function (k) {
                k = k.j26();
                var m = g.$new("A", {
                    title: this._lang[this.cbs[k].title],
                    href: "#",
                    rel: k
                }, {
                    display: "block",
                    "float": "left"
                }).j32(this.t26),
                    l = (l = m.j5("width")) ? l.j17() : 0,
                    q = (q = m.j5("height")) ? q.j17() : 0;
                m.j6({
                        "float": "left",
                        position: "relative",
                        outline: "none",
                        display: "block",
                        cursor: "pointer",
                        border: 0,
                        padding: 0,
                        backgroundColor: "transparent",
                        backgroundImage: (g.j21.trident4) ? "none" : "inherit",
                        backgroundPosition: "" + -(this.cbs[k].index * l) + "px 0px"
                    });
                if (g.j21.trident && (g.j21.version > 4)) {
                        m.j6(this.t26.j19s("background-image"))
                    }
                if (g.j21.trident4) {
                        this.t26.j6Prop("background-image", "none");
                        try {
                            if (!g.doc.namespaces.length || !g.doc.namespaces.item("mt_vml_")) {
                                g.doc.namespaces.add("mt_vml_", "urn:schemas-microsoft-com:vml")
                            }
                        } catch (o) {
                            try {
                                g.doc.namespaces.add("mt_vml_", "urn:schemas-microsoft-com:vml")
                            } catch (o) {}
                        }
                        if (!g.doc.styleSheets.magicthumb_ie_ex) {
                            var p = g.doc.createStyleSheet();
                            p.owningElement.id = "magicthumb_ie_ex";
                            p.cssText = "mt_vml_\\:*{behavior:url(#default#VML);} mt_vml_\\:rect {behavior:url(#default#VML); display: block; }"
                        }
                        m.j6({
                            backgroundImage: "none",
                            overflow: "hidden",
                            display: "block"
                        });
                        var n = '<mt_vml_:rect stroked="false"><mt_vml_:fill type="tile" src="' + e + '"></mt_vml_:fill></mt_vml_:rect>';
                        m.insertAdjacentHTML("beforeEnd", n);
                        $mjs(m.firstChild).j6({
                            display: "block",
                            width: (l * 3) + "px",
                            height: q * 2
                        });
                        m.scrollLeft = (this.cbs[k].index * l) + 1;
                        m.scrollTop = 1;
                        m.j30("bg-position", {
                            l: m.scrollLeft,
                            t: m.scrollTop
                        })
                    }
            }, this)
        },
        t6: function (e) {
            var j = this.thumbs.indexOf(this);
            $mjs(g.$A(g.doc.byTag("A")).filter(function (l) {
                var k = new RegExp("(^|;)\\s*(zoom|thumb)\\-id\\s*:\\s*" + this.id.replace(/\-/, "-") + "(;|$)");
                return k.test(l.rel.j26())
            }, this)).forEach(function (m, k) {
                this.group = this.id;
                m = $mjs(m);
                if (!$mjs(m).j29("j24:prevent")) {
                    $mjs(m).j30("j24:prevent", function (n) {
                        $mjs(n).stop();
                        return false
                    }).je1("click", m.j29("j24:prevent"))
                }
                if (e) {
                    return
                }
                $mjs(m).j30("j24:replace", function (r, n) {
                    var p = this.j29("thumb"),
                        o = n.j29("thumb"),
                        q = p.t16(p.t27 || p.id);
                    if ((" " + q.r.className + " ").match(/\sMagicZoom(?:Plus){0,1}\s/) && q.r.zoom) {
                            return true
                        }
                    $mjs(r).stop();
                    if (!p.ready || "inz30" != p.state || !o.ready || "inz30" != o.state || p == o) {
                            return
                        }
                    switch (r.type) {
                        case "mouseout":
                            if (p.swapTimer) {
                                clearTimeout(p.swapTimer)
                            }
                            p.swapTimer = false;
                            return;
                            break;
                        case "mouseover":
                            p.swapTimer = p.swap.j24(p, o).j27(p._o.selectorsMouseoverDelay);
                            break;
                        default:
                            p.swap(o);
                            return
                        }
                }.j16(this.r, m)).je1(this._o.selectorsChange, m.j29("j24:replace"));
                if ("mouseover" == this._o.selectorsChange) {
                    m.je1("mouseout", m.j29("j24:replace"))
                }
                if (m.href != this.z1.self.src) {
                    var l = $mjs(this.thumbs.filter(function (n) {
                        return (m.href == n.params.content && this.group == n.group)
                    }, this))[0];
                    if (l) {
                        m.j30("thumb", l)
                    } else {
                        new a(m, g.extend(g.detach(this._o), {
                            initializeOn: "load",
                            group: this.group
                        }), {
                            thumbnail: m.rev,
                            t27: this.id,
                            index: j + k
                        })
                    }
                } else {
                    this.selector = m;
                    m.j30("thumb", this);
                    if ("" != this._o.selectorsClass) {
                        m.j2(this._o.selectorsClass)
                    }
                }
                m.j6({
                    outline: "none"
                }).j2("MagicThumb-swap");
                this.t28.push(m)
            }, this)
        },
        t7: function () {
            var e;
            if ("true" != this._o.rightClick && "expanded" != this._o.rightClick) {
                this.content.je1("contextmenu", function (m) {
                    $mjs(m).stop()
                })
            }
            if (("auto" == this._o.restoreTrigger && "mouseover" == this._o.expandTrigger && "image" == this._o.expandAlign) || "mouseout" == this._o.restoreTrigger) {
                this.t22.je1("mouseout", function (n) {
                    var m = $mjs(n).stop().getTarget();
                    if ("expanded" != this.state) {
                        return
                    }
                    if (this.t22 == n.getRelated() || this.t22.hasChild(n.getRelated())) {
                        return
                    }
                    this.restore(null)
                }.j16(this))
            }
            this.t23.je1("mouseup", function (n) {
                var m = n.getButton();
                if (3 == m) {
                    return
                }
                if (this._o.link) {
                    $mjs(n).stop();
                    g.win.open(this._o.link, (2 == m) ? "_blank" : this._o.linkTarget)
                } else {
                    if (1 == m && "img" == this.media) {
                        $mjs(n).stop();
                        this.restore(null)
                    }
                }
            }.j16(this));
            if (g.j21.touchScreen) {
                this.t23.je1("touchstart", function (m) {
                    var o = g.now();
                    if (m.targetTouches.length > 1) {
                        return
                    }
                    this.t23.j30("magicthumb:event:lastTap", {
                        id: m.targetTouches[0].identifier,
                        ts: o,
                        x: m.targetTouches[0].clientX,
                        y: m.targetTouches[0].clientY
                    })
                }.j16(this));
                this.t23.je1("touchend", function (o) {
                    var p = g.now(),
                        m = this.t23.j29("magicthumb:event:lastTap");
                    if (!m || o.touches.length > 1) {
                            return
                        }
                    if (m.id == o.changedTouches[0].identifier && p - m.ts <= 200 && Math.sqrt(Math.pow(o.changedTouches[0].clientX - m.x, 2) + Math.pow(o.changedTouches[0].clientY - m.y, 2)) <= 15) {
                            if (this._o.link) {
                                $mjs(o).stop();
                                g.win.open(this._o.link, this._o.linkTarget);
                                return
                            }
                            o.stop();
                            this.restore(null);
                            return
                        }
                }.j16(this))
            }
            if (this.t26) {
                var k, l, j;
                this.t26.j30("j24:hover", k = this.cbHover.j16(this)).j30("j24:click", l = this.cbClick.j16(this));
                this.t26.je1("mouseover", k).je1("mouseout", k).je1("mouseup", l).je1("click", function (m) {
                    $mjs(m).stop()
                });
                g.j21.touchScreen && this.t26.je1("touchend", l);
                if ("autohide" == this._o.buttons) {
                    this.t22.j30("j24:cbhover", j = function (n) {
                        var m = $mjs(n).stop().getTarget();
                        if ("expanded" != this.state) {
                            return
                        }
                        if (this.t22 == n.getRelated() || this.t22.hasChild(n.getRelated())) {
                            return
                        }
                        this.t10(("mouseout" == n.type))
                    }.j16(this)).je1("mouseover", j).je1("mouseout", j)
                }
            }
            this.t22.j30("j24:external-click", e = function (m) {
                if (this.t22.hasChild(m.getTarget())) {
                    return
                }
                if ((/touch/i).test(m.type) || ((1 == m.getButton() || 0 == m.getButton()) && "expanded" == this.state)) {
                    this.restore(null, true)
                }
            }.j16(this));
            g.doc.je1("click", e);
            g.j21.touchScreen && g.doc.je1("touchstart", e);
            this.t22.j30("j24:window:resize", function (m) {
                clearTimeout(this.resizeTimer);
                this.resizeTimer = this.onresize.j24(this).j27(100)
            }.j16(this));
            $mjs(window).je1("resize", this.t22.j29("j24:window:resize"));
            if ("original" !== this._o.expandSize) {
                $mjs(window).je1("scroll", this.t22.j29("j24:window:resize"))
            }
        },
        t8: function () {
            this.t30 = new g.FX(this.t22, {
                transition: g.FX.Transition[this._o.expandEffect + this.easing[this._o.expandEffect][0]],
                duration: this._o.expandSpeed,
                fps: this.fps,
                onStart: function () {
                    var l = this.t16(this.t27 || this.id);
                    this.t22.j6Prop("width", this.t30.styles.width[0]);
                    this.t22.j32(g.body);
                    if (!l.r.j29("magicthumb:event:lastTap")) {
                        this.toggleMZ(false)
                    }
                    this.t10(true, true);
                    if (this.t26 && g.j21.trident && g.j21.version < 6) {
                        this.t26.hide()
                    }
                    if (!this._o.keepThumbnail && !(this.prevItem && "expand" != this._o.slideshowEffect)) {
                        var j = {};
                        for (var e in this.t30.styles) {
                            j[e] = this.t30.styles[e][0]
                        }
                        this.t22.j6(j);
                        if ((" " + l.r.className + " ").match(/\s(MagicThumb|MagicZoomPlus)\s/)) {
                            l.r.j23(0, true)
                        }
                    }
                    if (this.t25) {
                        if (g.j21.trident && g.j21.backCompat && this.hCaption) {
                            this.t25.j6Prop("display", "none")
                        }
                        this.t25.parentNode.j6Prop("height", 0)
                    }
                    this.t22.j6({
                        zIndex: this._o.zIndex + 1,
                        opacity: 1
                    })
                }.j24(this),
                onComplete: function () {
                    var j = this.t16(this.t27 || this.id);
                    if (this._o.link) {
                        this.t22.j6({
                            cursor: "pointer"
                        })
                    }
                    if (!(this.prevItem && "expand" != this._o.slideshowEffect)) {
                        j.r.j2("MagicThumb-expanded-thumbnail")
                    }
                    if ("hide" != this._o.buttons) {
                        if (this.t26 && g.j21.trident && g.j21.version < 6) {
                            this.t26.show();
                            if (g.j21.trident4) {
                                g.$A(this.t26.byTag("A")).j14(function (l) {
                                    var m = l.j29("bg-position");
                                    l.scrollLeft = m.l;
                                    l.scrollTop = m.t
                                })
                            }
                        }
                        this.t10()
                    }
                    if (this.t25) {
                        if (this.hCaption) {
                            var e = this.t22.j29("border"),
                                k = this.adjBorder(this.t22, this.t22.j7().height, e.borderTopWidth.j17() + e.borderBottomWidth.j17());
                            this.t23.j6(this.t22.j19s("width"));
                            this.t25.j6Prop("height", k - this.t25.j29("padY")).parentNode.j6Prop("height", k);
                            this.t22.j6Prop("width", "auto");
                            this.curLeft = this.t22.j8().left
                        }
                        this.t25.j6Prop("display", "block");
                        this.t12()
                    }
                    this.state = "expanded";
                    g.doc.je1("keydown", this.onKey.j16(this));
                    if (this._o.panZoom && this.t23.j7().width < this.z1.nWidth) {
                        if (!this.t23.zoom) {
                            this.zoomItem = new c.zoom(this.t23, this.mzParams)
                        } else {
                            this.t23.zoom.start(this.mzParams)
                        }
                    }
                }.j24(this)
            });
            this.t31 = new g.FX(this.t22, {
                transition: g.FX.Transition.linear,
                duration: this._o.restoreSpeed,
                fps: this.fps,
                onStart: function () {
                    if (this._o.panZoom) {
                        c.stop(this.t23)
                    }
                    this.t10(true, true);
                    if (this.t26 && g.j21.trident4) {
                        this.t26.hide()
                    }
                    this.t22.j6({
                        zIndex: this._o.zIndex
                    });
                    if (this.t25 && this.hCaption) {
                        this.t22.j6(this.t23.j19s("width"));
                        this.t23.j6Prop("width", "auto")
                    }
                }.j24(this),
                onComplete: function () {
                    if (!this.prevItem || (this.prevItem && !this.t27 && !this.t28.length)) {
                        var e = this.t16(this.t27 || this.id);
                        if (!e.r.j29("magicthumb:event:lastTap")) {
                            e.toggleMZ(true)
                        }
                        e.r.j3("MagicThumb-expanded-thumbnail").j23(1, true);
                        if (e.hint) {
                            e.hint.show()
                        }
                    }
                    this.t22.j6({
                        top: -10000
                    }).j32(this.t29);
                    this.state = "inz30"
                }.j24(this)
            });
            if (g.j21.trident4) {
                this.t30.options.onBeforeRender = this.t31.options.onBeforeRender = function (l, e, m, k) {
                    var j = k.width + e;
                    this.overlapBox.j6({
                        width: j,
                        height: Math.ceil(j / l) + m
                    });
                    if (k.opacity) {
                        this.t23.j23(k.opacity)
                    }
                }.j24(this, this.t22.j29("ratio"), this.t22.j29("padX"), this.t22.j29("padY"))
            }
        },
        expand: function (w, q) {
            if (this._o.disableExpand) {
                return
            }
            if ("inz30" != this.state) {
                if ("uninitialized" == this.state) {
                    this.r.j30("clicked", this.id);
                    this.start()
                }
                return
            }
            this.state = "busy-expand";
            this.prevItem = w = w || false;
            this.t21().forEach(function (p) {
                if (p == this || this.prevItem) {
                    return
                }
                switch (p.state) {
                case "busy-restore":
                    p.t31.stop(true);
                    break;
                case "busy-expand":
                    p.t30.stop();
                    p.state = "expanded";
                default:
                    p.restore(null, true)
                }
            }, this);
            var z = this.t16(this.t27 || this.id).r.j29("thumb"),
                e = (z.z7) ? z.z7.self.j9() : z.r.j9(),
                v = (z.z7) ? z.z7.self.j8() : z.r.j8(),
                x = ("fit-screen" == this._o.expandSize) ? this.resize() : {
                    width: this.t22.j29("size").width - this.t22.j29("padX") + this.t22.j29("hspace"),
                    height: this.t22.j29("size").height - this.t22.j29("padY") + this.t22.j29("vspace")
                },
                r = {
                    width: x.width + this.t22.j29("padX"),
                    height: x.height + this.t22.j29("padY")
                },
                s = {},
                l = [this.t22.j19s("paddingTop", "paddingLeft", "paddingRight", "paddingBottom"), this.t22.j29("padding")],
                k = {
                    width: [e.right - e.left, x.width]
                };
            $mjs(["Top", "Bottom", "Left", "Right"]).forEach(function (p) {
                    k["padding" + p] = [l[0]["padding" + p].j17(), l[1]["padding" + p].j17()]
                });
            var j = this.position;
            var y = ("image" == this._o.expandAlign) ? e : this.t13();
            switch (this._o.expandPosition) {
                case "center":
                    s = this.t14(r, y);
                    break;
                default:
                    if ("fit-screen" == this._o.expandSize) {
                        x = this.resize({
                            x: (parseInt(j.left)) ? 0 + j.left : (parseInt(j.right)) ? 0 + j.right : 0,
                            y: (parseInt(j.top)) ? 0 + j.top : (parseInt(j.bottom)) ? 0 + j.bottom : 0
                        });
                        r = {
                            width: x.width + this.t22.j29("padX"),
                            height: x.height + this.t22.j29("padY")
                        };
                        k.width[1] = x.width
                    }
                    y.top = (y.top += parseInt(j.top)) ? y.top : (y.bottom -= parseInt(j.bottom)) ? y.bottom - r.height : y.top;
                    y.bottom = y.top + r.height;
                    y.left = (y.left += parseInt(j.left)) ? y.left : (y.right -= parseInt(j.right)) ? y.right - r.width : y.left;
                    y.right = y.left + r.width;
                    s = this.t14(r, y);
                    break
                }
            k.top = [v.top, s.y];
            k.left = [v.left, s.x + ((this.t25 && "left" == this._o.captionPosition) ? this.t25.j29("width") : 0)];
            if (w && "expand" != this._o.slideshowEffect) {
                    k.width = [x.width, x.width];
                    k.top[0] = k.top[1];
                    k.left[0] = k.left[1];
                    k.opacity = [0, 1];
                    this.t30.options.duration = this._o.slideshowSpeed;
                    this.t30.options.transition = g.FX.Transition.linear
                } else {
                    this.t30.options.transition = g.FX.Transition[this._o.expandEffect + this.easing[this._o.expandEffect][0]];
                    this.t30.options.duration = this._o.expandSpeed;
                    if (g.j21.trident4) {
                        this.t23.j23(1)
                    }
                    if (this._o.keepThumbnail) {
                        k.opacity = [0, 1]
                    }
                }
            if (this.t26) {
                    g.$A(this.t26.byTag("A")).forEach(function (A) {
                        var p = A.j5("background-position").split(" ");
                        if (g.j21.trident4) {
                            A.scrollTop = 1
                        } else {
                            p[1] = "0px";
                            A.j6({
                                "background-position": p.join(" ")
                            })
                        }
                    });
                    var m = g.$A(this.t26.byTag("A")).filter(function (p) {
                        return "previous" == p.rel
                    })[0],
                        o = g.$A(this.t26.byTag("A")).filter(function (p) {
                            return "next" == p.rel
                        })[0],
                        u = this.t19(this.group),
                        n = this.t20(this.group);
                    if (m) {
                            (this == u && (u == n || !this._o.slideshowLoop)) ? m.hide() : m.show()
                        }
                    if (o) {
                            (this == n && (u == n || !this._o.slideshowLoop)) ? o.hide() : o.show()
                        }
                }
            this.t30.start(k);
            this.t11()
        },
        restore: function (e, n) {
            if (!e && "busy-expand" == this.state) {
                this.t30.stop();
                this.state = "expanded"
            }
            if ("expanded" != this.state) {
                return
            }
            if (e && !e.ready && "initializing" == e.state) {
                e.onInititalize = this.restore.j24(this, e);
                if (!e._o.preloadSelectorsBig) {
                    e.setupContent(e.params.content, this.t23.j9())
                }
                this.nextItem = e;
                return
            }
            if (this.nextItem) {
                this.nextItem.onInititalize = null;
                this.nextItem.z3 && this.nextItem.z3.hide()
            }
            this.nextItem = null;
            var m = {},
                p = this.t22.j9();
            this.state = "busy-restore";
            this.prevItem = e = e || null;
            n = n || false;
            g.doc.je2("keydown");
            if (this.t25) {
                    this.t12("hide");
                    this.t25.parentNode.j6Prop("height", 0);
                    if (g.j21.trident && g.j21.backCompat && this.hCaption) {
                        this.t25.j6Prop("display", "none")
                    }
                }
            m = g.detach(this.t30.styles);
            m.width[1] = this.t23.j7().width;
            m.top[1] = this.t22.j8().top;
            m.left[1] = this.t22.j8().left;
            if (e && "expand" != this._o.slideshowEffect) {
                    if ("fade" == this._o.slideshowEffect) {
                        m.opacity = [1, 0]
                    }
                    m.width[0] = m.width[1];
                    m.top = m.top[1];
                    m.left = m.left[1];
                    this.t31.options.duration = this._o.slideshowSpeed;
                    this.t31.options.transition = g.FX.Transition.linear
                } else {
                    this.t31.options.duration = (n) ? 0 : this._o.restoreSpeed;
                    this.t31.options.transition = g.FX.Transition[this._o.restoreEffect + this.easing[this._o.restoreEffect][1]];
                    for (var j in m) {
                        if ("array" != g.j1(m[j])) {
                            continue
                        }
                        m[j].reverse()
                    }
                    if (!this._o.keepThumbnail) {
                        delete m.opacity
                    }
                    var l = this.t16(this.t27 || this.id).r.j29("thumb"),
                        q = (l.z7) ? l.z7.self : l.r;
                    m.width[1] = q.j7().width;
                    m.top[1] = q.j8().top;
                    m.left[1] = q.j8().left
                }
            this.t31.start(m);
            if (e) {
                    e.expand(this, p)
                }
            var o = g.doc.j29("bg:t32");
            if (!e && o) {
                    if ("hidden" != o.el.j5("visibility")) {
                        this.t11(true)
                    }
                }
        },
        t12: function (j) {
            if (!this.t25) {
                return
            }
            var e = this.t25.j29("slide");
            this.t25.j6Prop("overflow-y", "hidden");
            e.stop();
            e[j || "toggle"](this.hCaption ? this._o.captionPosition : "vertical")
        },
        t10: function (j, l) {
            var n = this.t26;
            if (!n) {
                return
            }
            j = j || false;
            l = l || false;
            var k = n.j29("cb:t32"),
                e = {};
            if (!k) {
                    n.j30("cb:t32", k = new g.FX(n, {
                        transition: g.FX.Transition.linear,
                        duration: 250
                    }))
                } else {
                    k.stop()
                }
            if (l) {
                    n.j6Prop("opacity", (j) ? 0 : 1);
                    return
                }
            var m = n.j5("opacity");
            e = (j) ? {
                    opacity: [m, 0]
                } : {
                    opacity: [m, 1]
                };
            k.start(e)
        },
        cbHover: function (m) {
            var k = $mjs(m).stop().getTarget();
            if ("expanded" != this.state) {
                return
            }
            try {
                while ("a" != k.tagName.toLowerCase() && k != this.t26) {
                    k = k.parentNode
                }
                if ("a" != k.tagName.toLowerCase() || k.hasChild(m.getRelated())) {
                    return
                }
            } catch (l) {
                return
            }
            var j = k.j5("background-position").split(" ");
            switch (m.type) {
            case "mouseover":
                j[1] = k.j5("height");
                break;
            case "mouseout":
                j[1] = "0px";
                break
            }
            if (g.j21.trident4) {
                k.scrollTop = j[1].j17() + 1
            } else {
                k.j6({
                    "background-position": j.join(" ")
                })
            }
        },
        cbClick: function (k) {
            var j = $mjs(k).stop().getTarget();
            while ("a" != j.tagName.toLowerCase() && j != this.t26) {
                j = j.parentNode
            }
            if ("a" != j.tagName.toLowerCase()) {
                return
            }
            switch (j.rel) {
            case "previous":
                this.restore(this.t18(this, this._o.slideshowLoop));
                break;
            case "next":
                this.restore(this.t17(this, this._o.slideshowLoop));
                break;
            case "close":
                this.restore(null);
                break
            }
        },
        t11: function (j) {
            j = j || false;
            var k = g.doc.j29("bg:t32"),
                e = {},
                m = 0;
            if (!k) {
                    var l = g.$new("DIV").j2("MagicThumb-background").j6({
                        position: "fixed",
                        display: "block",
                        top: 0,
                        bottom: 0,
                        left: 0,
                        right: 0,
                        zIndex: (this._o.zIndex - 1),
                        overflow: "hidden",
                        backgroundColor: this._o.backgroundColor,
                        opacity: 0,
                        border: 0,
                        margin: 0,
                        padding: 0
                    }).j32(g.body).hide();
                    if (g.j21.trident4) {
                        l.append(g.$new("IFRAME", {
                            src: 'javascript:"";'
                        }, {
                            width: "100%",
                            height: "100%",
                            display: "block",
                            filter: "mask()",
                            top: 0,
                            lef: 0,
                            position: "absolute",
                            zIndex: -1,
                            border: "none"
                        }))
                    }
                    g.doc.j30("bg:t32", k = new g.FX(l, {
                        transition: g.FX.Transition.linear,
                        duration: this._o.backgroundSpeed,
                        onStart: function (n) {
                            if (n) {
                                this.j6(g.extend(g.doc.j12(), {
                                    position: "absolute"
                                }))
                            }
                        }.j24(l, this.ieBack),
                        onComplete: function () {
                            this.j23(this.j5("opacity"), true)
                        }.j24(l)
                    }));
                    e = {
                        opacity: [0, this._o.backgroundOpacity / 100]
                    }
                } else {
                    k.stop();
                    m = k.el.j5("opacity");
                    k.el.j6Prop("background-color", this._o.backgroundColor);
                    e = (j) ? {
                        opacity: [m, 0]
                    } : {
                        opacity: [m, this._o.backgroundOpacity / 100]
                    };
                    k.options.duration = this._o.backgroundSpeed
                }
            k.el.show();
            k.start(e)
        },
        toggleMZ: function (j) {
            j = j || false;
            var e = this.t16(this.t27 || this.id);
            if (e.r.zoom && -1 != e.r.zoom.z28) {
                if (!j) {
                    e.r.zoom.pause();
                    e.r.zoom.z30 = false;
                    e.r.zoom.z4.z38 = false;
                    e.r.zoom.z4.self.hide();
                    e.r.zoom.z47.hide()
                } else {
                    e.r.zoom.activate(e.r.zoom.options.alwaysShowZoom)
                }
            }
        },
        t13: function (k) {
            k = k || 0;
            var j = (g.j21.touchScreen) ? {
                width: window.innerWidth,
                height: window.innerHeight
            } : $mjs(window).j7(),
                e = $mjs(window).j10();
            return {
                    left: e.x + k,
                    right: e.x + j.width - k,
                    top: e.y + k,
                    bottom: e.y + j.height - k
                }
        },
        t14: function (k, l) {
            var j = this.t13(this._o.screenPadding),
                e = $mjs(window).j12();
            l = l || j;
            return {
                    y: Math.max(j.top, Math.min(("fit-screen" == this._o.expandSize) ? j.bottom : e.height + k.height, l.bottom - (l.bottom - l.top - k.height) / 2) - k.height),
                    x: Math.max(j.left, Math.min(j.right, l.right - (l.right - l.left - k.width) / 2) - k.width)
                }
        },
        resize: function (m, j) {
            var n = (g.j21.touchScreen) ? {
                width: window.innerWidth,
                height: window.innerHeight
            } : $mjs(window).j7(),
                s = this.t22.j29("size"),
                o = this.t22.j29("ratio"),
                l = this.t22.j29("padX"),
                k = this.t22.j29("padY"),
                r = this.t22.j29("hspace"),
                e = this.t22.j29("vspace"),
                q = 0,
                p = 0;
            if (m) {
                    n.width -= m.x;
                    n.height -= m.y
                }
            q = Math.min(this.size.width + r, Math.min(s.width, n.width - l - this.scrPad.x)),
            p = Math.min(this.size.height + e, Math.min(s.height, n.height - k - this.scrPad.y));
            if (q / p > o) {
                    q = p * o
                } else {
                    if (q / p < o) {
                        p = q / o
                    }
                }
            if (!j) {
                    this.t22.j6Prop("width", q);
                    if (this.cr) {
                        this.cr.j6({
                            top: (this.z1.self.j7().height - this.cr.j7().height)
                        })
                    }
                }
            return {
                    width: Math.ceil(q),
                    height: Math.ceil(p)
                }
        },
        onresize: function () {
            if ("expanded" !== this.state) {
                return
            }
            var n = this.t22.j7();
            var r = this.t16(this.t27 || this.id).r.j29("thumb"),
                e = (r.z7) ? r.z7.self.j9() : r.r.j9(),
                s = ("image" == this._o.expandAlign) ? e : this.t13(),
                j = this.position,
                o = ("fit-screen" == this._o.expandSize) ? this.resize(null, true) : {
                    width: this.t22.j29("size").width - this.t22.j29("padX") + this.t22.j29("hspace"),
                    height: this.t22.j29("size").height - this.t22.j29("padY") + this.t22.j29("vspace")
                },
                l = {
                    width: o.width + this.t22.j29("padX"),
                    height: o.height + this.t22.j29("padY")
                },
                q = this.t22.j8(),
                k = (this.t25 && this.hCaption) ? this.t25.j29("width") + this.t25.j29("padX") : 0,
                m;
            n.width -= this.t22.j29("padX");
            n.height -= this.t22.j29("padY");
            switch (this._o.expandPosition) {
                case "center":
                    m = this.t14(l, s);
                    break;
                default:
                    if ("fit-screen" == this._o.expandSize) {
                        o = this.resize({
                            x: (parseInt(j.left)) ? 0 + j.left : (parseInt(j.right)) ? 0 + j.right : 0,
                            y: (parseInt(j.top)) ? 0 + j.top : (parseInt(j.bottom)) ? 0 + j.bottom : 0
                        }, true);
                        l = {
                            width: o.width + this.t22.j29("padX"),
                            height: o.height + this.t22.j29("padY")
                        }
                    }
                    s.top = (s.top += parseInt(j.top)) ? s.top : (s.bottom -= parseInt(j.bottom)) ? s.bottom - l.height : s.top;
                    s.bottom = s.top + l.height;
                    s.left = (s.left += parseInt(j.left)) ? s.left : (s.right -= parseInt(j.right)) ? s.right - l.width : s.left;
                    s.right = s.left + l.width;
                    m = this.t14(l, s);
                    break
                }
            new g.FX(this.t22, {
                    duration: 250,
                    onAfterRender: function (p, u) {
                        var v;
                        if (p > 0) {
                            this.t23.j6Prop("width", u.width - p);
                            v = this.t23.j7().height;
                            this.t25.j6Prop("height", v - this.t25.j29("padY")).parentNode.j6Prop("height", v)
                        }
                        if (this.cr) {
                            this.cr.j6({
                                top: (this.z1.self.j7().height - this.cr.j7().height)
                            })
                        }
                    }.j24(this, k),
                    onComplete: function () {
                        if (this.zoomItem) {
                            this.zoomItem.onresize()
                        }
                    }.j24(this)
                }).start({
                    width: [n.width + k, o.width + k],
                    top: [q.top, m.y],
                    left: [q.left, m.x]
                })
        },
        adjBorder: function (l, j, e) {
            var k = false;
            switch (g.j21.engine) {
            case "gecko":
                k = "content-box" != (l.j5("box-sizing") || l.j5("-moz-box-sizing"));
                break;
            case "webkit":
                k = "content-box" != (l.j5("box-sizing") || l.j5("-webkit-box-sizing"));
                break;
            case "trident":
                k = g.j21.backCompat || "content-box" != (l.j5("box-sizing") || l.j5("-ms-box-sizing") || "content-box");
                break;
            default:
                k = "content-box" != l.j5("box-sizing");
                break
            }
            return (k) ? j : j - e
        },
        z37: function (o) {
            function l(r) {
                var q = [];
                if ("string" == g.j1(r)) {
                    return r
                }
                for (var m in r) {
                    q.push(m.dashize() + ":" + r[m])
                }
                return q.join(";")
            }
            var k = l(o).j26(),
                p = $mjs(k.split(";")),
                n = null,
                j = null;
            p.forEach(function (q) {
                    for (var m in this._o) {
                        j = new RegExp("^" + m.dashize().replace(/\-/, "\\-") + "\\s*:\\s*([^;]" + (("hintText" == m) ? "*" : "+") + ")$", "i").exec(q.j26());
                        if (j) {
                            switch (g.j1(this._o[m])) {
                            case "boolean":
                                this._o[m] = j[1].j18();
                                break;
                            case "number":
                                this._o[m] = (j[1].has(".")) ? (j[1].toFloat() * ((m.toLowerCase().has("opacity")) ? 100 : 1000)) : j[1].j17();
                                break;
                            default:
                                this._o[m] = j[1].j26()
                            }
                        }
                    }
                }, this);
            for (var e in this._deprecated) {
                    if (!this._deprecated.hasOwnProperty(e)) {
                        continue
                    }
                    j = new RegExp("(^|;)\\s*" + e.dashize().replace(/\-/, "\\-") + "\\s*:\\s*([^;]+)\\s*(;|$)", "i").exec(k);
                    if (j) {
                        this._deprecated[e].call(this, j[2])
                    }
                }
        },
        parseExOptions: function () {
            var e = null,
                l = this.position,
                k = this.size;
            for (var j in l) {
                    e = new RegExp("" + j + "\\s*=\\s*([^,]+)", "i").exec(this._o.expandPosition);
                    if (e) {
                        l[j] = (isFinite(l[j] = e[1].j17())) ? l[j] : "auto"
                    }
                }
            if ((isNaN(l.top) && isNaN(l.bottom)) || (isNaN(l.left) && isNaN(l.right))) {
                    this._o.expandPosition = "center"
                }
            if (!$mjs(["fit-screen", "original"]).contains(this._o.expandSize)) {
                    for (var j in k) {
                        e = new RegExp("" + j + "\\s*=\\s*([^,]+)", "i").exec(this._o.expandSize);
                        if (e) {
                            k[j] = (isFinite(k[j] = e[1].j17())) ? k[j] : -1
                        }
                    }
                    if (isNaN(k.width) && isNaN(k.height)) {
                        this._o.expandSize = "fit-screen"
                    }
                }
        },
        setLang: function (e) {
            var j, l;
            for (var j in e) {
                if (this._lang.hasOwnProperty(l = j.j22())) {
                    this._lang[l] = e[j]
                }
            }
        },
        t16: function (e) {
            return $mjs(this.thumbs.filter(function (j) {
                return (e == j.id)
            }))[0]
        },
        t15: function (e, j) {
            e = e || null;
            j = j || false;
            return $mjs(this.thumbs.filter(function (k) {
                return (e == k.group && !k.error && (j || k.ready) && (j || "uninitialized" != k.state) && (j || !k._o.disableExpand))
            }))
        },
        t17: function (m, e) {
            e = e || false;
            var j = this.t15(m.group, true),
                k = j.indexOf(m) + 1;
            return (k >= j.length) ? (!e || 1 >= j.length) ? undefined : j[0] : j[k]
        },
        t18: function (m, e) {
            e = e || false;
            var j = this.t15(m.group, true),
                k = j.indexOf(m) - 1;
            return (k < 0) ? (!e || 1 >= j.length) ? undefined : j[j.length - 1] : j[k]
        },
        t19: function (j) {
            j = j || null;
            var e = this.t15(j, true);
            return (e.length) ? e[0] : undefined
        },
        t20: function (j) {
            j = j || null;
            var e = this.t15(j, true);
            return (e.length) ? e[e.length - 1] : undefined
        },
        t21: function () {
            return $mjs(this.thumbs.filter(function (e) {
                return ("expanded" == e.state || "busy-expand" == e.state || "busy-restore" == e.state)
            }))
        },
        onKey: function (k) {
            var j = this._o.slideshowLoop,
                m = null;
            if (!this._o.keyboard) {
                    g.doc.je2("keydown");
                    return true
                }
            k = $mjs(k);
            if (this._o.keyboardCtrl && !(k.ctrlKey || k.metaKey)) {
                    return false
                }
            switch (k.keyCode) {
                case 27:
                    k.stop();
                    this.restore(null);
                    break;
                case 32:
                case 34:
                case 39:
                case 40:
                    m = this.t17(this, j || 32 == k.keyCode);
                    break;
                case 33:
                case 37:
                case 38:
                    m = this.t18(this, j);
                    break;
                default:
                }
            if (m) {
                    k.stop();
                    this.restore(m)
                }
        }
    });
    var h = {
        version: "v4.5.17",
        options: {},
        lang: {},
        _o: {
            disableZoom: false,
            disableExpand: false,
            preloadSelectorsSmall: true,
            preloadSelectorsBig: true,
            hintClass: "MagicZoomPlusHint",
            hintText: "Zoom",
            loadingClass: "MagicZoomPlusLoading",
            loadingMsg: "Loading zoom...",
            rightClick: "false"
        },
        start: function (l) {
            this.items = $mjs(window).j29("magiczoomplus:items", $mjs([]));
            var e = null,
                j = $mjs([]),
                k = {};
            this.options = g.extend(window.MagicZoomPlusOptions || {}, this.options);
            this._o = g.extend(this._o, this._z37());
            c.options = g.detach(this._o);
            b.options = g.detach(this._o);
            c.options.rightClick = ("original" == this._o.rightClick || "true" == this._o.rightClick);
            b.lang = this.lang;
            if (l) {
                    e = $mjs(l);
                    if (e && (" " + e.className + " ").match(/\s(MagicZoom(?:Plus){0,1}|MagicThumb)\s/)) {
                        j.push(e)
                    } else {
                        return false
                    }
                } else {
                    j = $mjs(g.$A(g.body.byTag("A")).filter(function (m) {
                        return (" " + m.className + " ").match(/\s(MagicZoom(?:Plus){0,1}|MagicThumb)\s/)
                    }))
                }
            j.forEach(function (p) {
                    p = $mjs(p);
                    var m = p.byTag("span"),
                        n = null;
                    k = g.extend(g.detach(this._o), this._z37(p.rel || " "));
                    if (p.j13("MagicZoom") || (p.j13("MagicZoomPlus"))) {
                            if (m && m.length) {
                                n = p.removeChild(m[0])
                            }
                            c.start(p, "right-click: " + ("original" == k.rightClick || "true" == k.rightClick));
                            if (n) {
                                p.append(n)
                            }
                        }
                    if (p.j13("MagicThumb") || (p.j13("MagicZoomPlus"))) {
                            b.start(p)
                        } else {
                            p.style.cursor = "pointer"
                        }
                    this.items.push(p)
                }, this);
            return true
        },
        stop: function (m) {
            var e = null,
                l = null,
                j = $mjs([]);
            if (m) {
                    e = $mjs(m);
                    if (e && (" " + e.className + " ").match(/\s(MagicZoom(?:Plus){0,1}|MagicThumb)\s/)) {
                        j = $mjs(this.items.splice(this.items.indexOf(e), 1))
                    } else {
                        return false
                    }
                } else {
                    j = $mjs(this.items)
                }
            while (j && j.length) {
                    l = $mjs(j[j.length - 1]);
                    if (l.zoom) {
                        l.zoom.stop();
                        c.zooms.splice(c.zooms.indexOf(l.zoom), 1);
                        l.zoom = undefined
                    }
                    b.stop(l);
                    var k = j.splice(j.indexOf(l), 1);
                    delete k
                }
            return true
        },
        refresh: function (j) {
            var e = null;
            if (j) {
                this.stop(j);
                this.start.j24(this).j27(150, j)
            } else {
                this.stop();
                this.start.j24(this).j27(150)
            }
            return true
        },
        update: function (n, e, k, l) {
            var m = $mjs(n),
                j = null;
            if (m) {
                    if ((j = m.j29("thumb"))) {
                        j.t16(j.t27 || j.id).state = "updating"
                    }
                    if (!c.update(m, e, k, l)) {
                        b.update(m, e, k, l)
                    }
                }
        },
        expand: function (e) {
            return b.expand(e)
        },
        restore: function (e) {
            return b.restore(e)
        },
        zoomIn: function (e) {
            return c.zoomIn(e)
        },
        zoomOut: function (e) {
            return c.zoomOut(e)
        },
        _z37: function (j) {
            var e, p, l, k, n;
            e = null;
            p = {};
            n = [];
            if (j) {
                l = $mjs(j.split(";"));
                l.j14(function (o) {
                    for (var m in this._o) {
                        e = new RegExp("^" + m.dashize().replace(/\-/, "\\-") + "\\s*:\\s*([^;]+)$", "i").exec(o.j26());
                        if (e) {
                            switch (g.j1(this._o[m])) {
                            case "boolean":
                                p[m] = e[1].j18();
                                break;
                            case "number":
                                p[m] = parseFloat(e[1]);
                                break;
                            default:
                                p[m] = e[1].j26()
                            }
                        }
                    }
                }, this)
            } else {
                for (k in this.options) {
                    e = this.options[k];
                    switch (g.j1(this._o[k.j22()])) {
                    case "boolean":
                        e = e.toString().j18();
                        break;
                    case "number":
                        e = parseFloat(e);
                        break;
                    default:
                        break
                    }
                    p[k.j22()] = e
                }
            }
            return p
        }
    };
    $mjs(document).je1("domready", function () {
        h.start()
    });
    return h
})(magicJS);
