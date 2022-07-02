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
        if (fdin) for(const [key, value] of Object.entries(fdin)) poch[key] = value;  
         
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

function kwtos(s) {
    if (!s) return '';
    return s;
    
}

function kwynn() { }

// ***************************
   
class delayedDo {

    static getRV() { return 'kwDDreplaceMe'; }

    constructor(dof, delayms, commitint) {
        this.clfan = 3; // should equal named arguments above
        this.comint = commitint;
        this.dof = dof;
        this.delayms = delayms;
        this.iargs = Array.prototype.slice.call(arguments);
        this.rpv = delayedDo.getRV();
        this.doneAt = 0;
    }

    doNow() { // later will come later
        const args =  Array.prototype.slice.call(arguments);
        const inia = this.iargs.slice(this.clfan).concat(args);

        let i=0, j=0;
        let fina = [];
        let jdo = false;
        
        do {
            jdo = false;
            
            if (isset(inia[i]) && inia[i] === this.rpv) {
                i++;
                if (isset(args[j])) fina.push(args[j++]);
            }
            else jdo = true;
            if (i >= inia.length) jdo = true;
            
            if (isset(inia[i])) fina.push(inia[i++]);
            
            if (jdo) if (isset(args[j])) fina.push(args[j++]);
   
        } while(i < inia.length || j < args.length);

        this.dof(... fina);
        this.doneAt = time();
    }

    doLater(args) {
       return setTimeout(() => { this.doNow(args); }, this.delayms);
    }

    doAtInterval(args) {

        clearTimeout(this.daistv);

        if (time() - this.doneAt >= this.comint) { 
            this.doNow(args); 
            return; 
        }

        this.daistv = this.doLater(args);

    }

} // do later class

// ***************************

class kwStandardTextIOCl {
    
    constructor(e, url, fin) {
        this.thee = e;
        this.url = url;
        this.isokevf = fin;
        this.okcolor = getOKColor();
        this.init10();
        return;
    }
    
    init10() {
        this.ddo = new delayedDo(kwjss.sobf, 307, 2000, this.url, delayedDo.getRV(), 
                                    (res) => {this.oninret(res); });
        this.thee.oninput = () => { this.oninput(this.thee); /* subclasses might need the element argument */};
    }
    
    oninput() {
        clearTimeout(this.okstov);
        const e = this.thee;
        e.style.backgroundColor = 'yellow';
        this.ddo.doAtInterval({'v' : e.value});
    }
    
    oninret(res) {
        if (this.isokevf && this.isokevf(res)) {
            const e = this.thee;            
            e.style.backgroundColor = this.okcolor;
            this.okstov = setTimeout(() => { e.style.backgroundColor = 'white'; }, 4000);
        }
    }
    
}

// ***************************

