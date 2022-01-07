function qsa(q)  { return document.querySelectorAll(q); }
function qs(q) { return document.querySelector(q);}
function cl(msg) { console.log(msg); }
function byid(id) { return document.getElementById(id); }
function kwas(v, msg) {
	if (!v) {
		if (!msg) msg = 'unknown message';
		throw msg;
	}
}
class jssendcl {
    
    static responseTextParse(t) {
	let o = false;
	try { o = JSON.parse(t); } catch(ex) {  }
	if (!o) o = { 'kwdbss' : 'ERROR', 'msg' : t };
	return o;
    }
    
    static sendEle(url, ein, cb, pageid) {
        const sob = {};
        sob.eid		= ein.id;
        if (Object.keys(ein.dataset).length) 
        sob.dataset = ein.dataset;
        sob.v       = ein.value;
        sob.pageid = pageid;
        sendcl.sobf(url, sob, cb);
    }
    
    static sobf(url, sob, cb, prt) {
        if (1) {
            if (url.search(/\?/) >= 0) url += '&';
            else                     url += '?';
            url += 'XDEBUG_SESSION_START=netbeans-xdebug';
        }
        const XHR = new XMLHttpRequest(); 
        XHR.open('POST', url);
        XHR.onloadend = function() { 
            const rt = this.responseText;
            if (prt === false) return cb(rt);
            if (typeof cb === 'function') cb(sendcl.responseTextParse(rt)); 
        }

        const formData = new FormData();
        if (sob) formData.append('POSTob', JSON.stringify(sob));
        XHR.send(formData);        
        
    }
}
