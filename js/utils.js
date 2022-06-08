function qsa(q)  { return document.querySelectorAll(q); }
function qs(q) { return document.querySelector(q);}
function cl(msg) { console.log(msg); }
function byid(id) { return document.getElementById(id); }
function cree(ty) { return document.createElement(ty); }
function inht(oorid, s) {
    let e = oorid;
    if (typeof oorid === 'string') e = byid(oorid);
    if (!e) return;
    e.innerHTML = s;
}
function kwas(v, msg) {
	if (!v) {
		if (!msg) msg = 'unknown message';
		throw msg;
	}
}
function time() { return (new Date().getTime()); } 
function tzop() {
    const dob  = new Date();
    const minr =  dob.getTimezoneOffset();
    const hr   = parseInt(minr / 60);
    const rev  = hr * -1;
    return rev;
}

function tzName() {
    return Intl.DateTimeFormat().resolvedOptions().timeZone;
}


function getOKColor() { return 'rgb(153, 255, 153)'; }

class kwjss {
    
    static responseTextParse(t) {
        
        if (typeof t === 'object') return t;
        
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
        kwjss.sobf(url, sob, cb);
    }
    
    static sobf(url, sob, cb, prt, fdin) {
        if (1) {
            if (url.search(/\?/) >= 0) url += '&';
            else                     url += '?';
            url += 'XDEBUG_SESSION_START=netbeans-xdebug';
        }
        const XHR = new XMLHttpRequest(); 
        XHR.open('POST', url);
        XHR.onloadend = function() { 
            const rt = this.responseText;
            if (typeof cb === 'function') {
                if (prt === false) return cb(rt);
                cb(kwjss.responseTextParse(rt)); 
            }
        }

        if (!sob) sob = {};
        const poch = sob;

        
        if (fdin) for(const [key, value] of fdin.entries()) { 
            poch[key] = value; 
        } 
    
        
        const fdfinal = new FormData();
        fdfinal.append('POSTob', JSON.stringify(poch));
        XHR.send(fdfinal);       
    }
}

function isset(v) { return typeof v !== 'undefined'; }


function kwifs(a, ...ks) { // if defined return, else FALSE
	
    let i = 0;
    let b = a;
    while  (isset(ks[i])) {
        if (!isset(b) ||  !isset(b[ks[i]])) return false;
        b =                      b[ks[i]];
        i++;
    }

    return b;
}

// I am using this for a test version of a paid project 2022/02/06, but I'm not certain it works correctly

function is_numeric(x) {
    if (typeof x === 'undefined') return false;
    if (typeof x === 'string' && x.search(/\d/) < 0) return false;
    const t = x * 1;
    if (isNaN(t)) return false;
    if (Number.isNaN(t)) return false;
    const isn = typeof t === 'number';
    return isn;
}

function onDOMLoad(f) { window.addEventListener('DOMContentLoaded', f); }

function okswc(e) {
    e.style.backgroundColor = getOKColor();
    return setTimeout(() => { e.style.backgroundColor = 'white'; }, 4000);
    
}