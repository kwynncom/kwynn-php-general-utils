if (typeof module === 'undefined') {  var module = {}; module.exports = {}; }

function UtoLocF(U) {
    const df = { year    : 'numeric', month  : 'short'  , day    : '2-digit',
                 weekday : 'short'  , hour   : '2-digit', minute : '2-digit', timeZoneName : 'short'};
    
    const dateo = new Date(U * 1000);
    const s     = dateo.toLocaleDateString([], df);
    return s;
}

// ****
function qsa(q)  { return document.querySelectorAll(q); }
function qs(q) { return document.querySelector(q);}
function cl(msg) { console.log(msg); }
module.exports.cl = cl;
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
};

module.exports.kwas = kwas;

function time() { return (new Date().getTime()); } 

module.exports.time = time;

const ignore2045 = false;

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
    
    static sendEle(url, ein, cb, pageid, exob) {
        let sob = {};
        if (typeof exob === 'object') sob = exob;
        sob.eid		= ein.id;
        if (Object.keys(ein.dataset).length) 
        sob.dataset = ein.dataset;
        sob.v       = ein.value;
        sob.checked = ein.checked;
        sob.pageid = pageid;
        sob.eleType = ein.type;
        kwjss.sobf(url, sob, cb);
    }
    
    static ccb(fin) {
        if (typeof fin === 'function') return { f : fin, isp : false};
        return { f : false, isp : true };
    }
  
    static ole10 (xht, cb, prt) {
       const rt = xht.responseText;
       
        if (prt === false) {
            if (cb) return cb(rt);
            else return rt;
        }
        
        const retv = kwjss.responseTextParse(rt);
        if (cb) return cb(retv); 
        return retv;
        
      }
       
    static sobf(url, sob, cbin, prt, fdin) {
        const cbo = kwjss.ccb(cbin);
        const cb  = cbo.f;
        const isp = cbo.isp;
        if (isp) {
            let resolvePH;
            const theResolve = new Promise((resolve) => { resolvePH = resolve; })
                                            .then((event) => { return kwjss.ole10(event.target, false, prt); });
            kwjss.sobf20(url, sob, prt, fdin, resolvePH);
            return theResolve;
        }
        
        kwjss.sobf20(url, sob, prt, fdin, (event) => { return kwjss.ole10(event.target, cb, prt);});
    }
    
    static sobf20(url, sob, prt, fdin, olcbf) {
        if (1) { // !*!*!*!*!*!****************************!!!!!*!*!*
            if (url.search(/\?/) >= 0) url += '&';
            else                     url += '?';
            
            url += 'XDEBUG_SESSION_START=netbeans-xdebug';
        }
        const XHR = new XMLHttpRequest(); 
        XHR.open('POST', url);
        XHR.onloadend = olcbf;    

        if (!sob) sob = {};
        const poch = sob;

        if (fdin) for(const [key, value] of fdin) poch[key] = value;   
         
        const fdfinal = new FormData();
        fdfinal.append('POSTob', JSON.stringify(poch));
        XHR.send(fdfinal);       
    }
} // class

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
            
            if (isset(inia[i])) fina.push(inia[i]);
            i++;
            
            if (jdo) {
                if (isset(args[j])) fina.push(args[j]);
                j++;
                
            }
 
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

class kwStdWebIOCl {
    
    constructor(e, url, fin) {
        this.thee = e;
        this.url = url;
        this.isokevf = fin;
        this.okcolor = getOKColor();
        this.init10();
        return;
    }
    
    init10() {
        
        let keystrokeDelay = 307;
        let constantTypingDelay = 2000;
        
        if (this.thee.tagName === 'SELECT') keystrokeDelay = constantTypingDelay = 0;
        
        
        this.ddo = new delayedDo((exob) => { kwjss.sendEle(this.url, this.thee, (res) => {this.oninret(this.thee, res); }, false, exob);}, // first param
                                            keystrokeDelay, constantTypingDelay); 
                                   
        this.thee.oninput = (dat) => { 
            this.oninput(this.thee, dat); /* subclasses might need the element argument */
        };
    }
    
    oninput(ein, dat) {
        this.clearTO();
        let e = this.thee;
        if (ein) e = ein;
        const ech = this.getColorE(e);
        ech.style.backgroundColor = 'yellow';
        this.ddo.doAtInterval(dat);
    }
    
    clearTO() { clearTimeout(this.okstov);  }
    
    oninret(e, res, subck) {
        if (res === 'subck' && subck === true) { this.dook(); return; }
        if (this.isokevf && this.isokevf(res)) this.dook();
    }
    
    getColorE(e) { return e.type === 'checkbox' ? e.parentNode : e; }
    
    dook() {
       const e = this.thee;            
       this.getColorE(e).style.backgroundColor = this.okcolor;
       this.okstov = setTimeout(() => { this.getColorE(e).style.backgroundColor = 'white'; }, 4000);       
    }
    
}

// ***************************

