class dragKwVisClass {
    onOver(ede, dir, ove) {
        if (this.doe) this.doe.style.borderTop = this.doe.style.borderBottom = 'none';
        if (dir === 'clear') return;
        if (dir === 'above') 
             ove.style.borderTop    = 'black dashed 4px';
        else ove.style.borderBottom = 'black dashed 4px';
        this.doe = ove;
    }
    
    setState(st) { if (st === 'dropBFNet') this.thee.style.opacity = 0.4;  }
    ok() { this.thee.style.opacity = 1;   }
    setEle(e) { this.thee = e; }
}

class dragKwOrdClass {
    
    constructor() {
        this.maxx = 0;
        this.orden = 0;
    }
    
    getn() { return this.orden; }
    
    inite(e, ordx, interval) {
        this.interval = interval;
        ordx = parseFloat(ordx);
        kwas(ordx > 0, 'order x must be > 0');
        this.orden++;
        e.dataset.kwOrdx = ordx;
        if (ordx > this.maxx) this.maxx = ordx;
    }
    
    set (ae, be, ce) {
        const es = [ae, be, ce];
        const xs = [];
        
        for (let i=0; i < es.length; i++) {
            if (i === 1) continue;
            xs[i] = this.getxInternal(es[i], i);
        }
        
        const ordx = (xs[0] + xs[2]) / 2;
        kwas(ordx > 0, 'ordx <= 0 set kwdrag');
        be.dataset.kwOrdx = ordx;
    }
    
    getxInternal(e, i) {
        if (!e && i === 0) return 0;
        if (!e) return this.maxx + this.interval;
        const ordx = this.getvox(e);
       return ordx;
    }
    
    getvox(e) {
        const raw = e.dataset.kwOrdx;
        const to = typeof raw;
        kwas(raw && is_numeric(raw), 'ordx not numeric kwdrag');
        const ordx = parseFloat(raw);       
        kwas(ordx > 0, 'ordx <= 0 getxInternal ordx dragkw');
        return ordx;
    }
    
    getOrdx(e) {
        const ordx = this.getvox(e);
        return ordx;
    }
    
    netOKOrDie(e, rordx) {
        kwas(dragKwOrdClass.eq(this.getOrdx(e), rordx), 'net reponse versus current element ordx match fail');
        return true;
    }
                                            // 1 23456789012
    static eq(a, b) { return Math.abs(a - b) < 0.00000000001;   }
}

class dragKwNetClass {
    send(id, ordx) {
        const ino = {'_id' : id, 'ordx' : ordx, 'action' : 'setOrder'};
        kwjss.sobf(this.serverURL, ino, (res) => { this.doResponse(res, ino); });
    }
    
    doResponse(r, ino) {
        kwas(r['postOrdxSave'] === 'OK', 'save non-OK response ordx');
        kwas(r['_id'] === ino['_id'], 'drag in and out result mismatch - id');
        kwas(dragKwOrdClass.eq(r['ordx'], ino['ordx']), 'ordx in out mismatch');
        const dat = {};
        dat['response_ordx'] = r['ordx'];
        dat['response_id'] = r['_id'];
        dat['netStatus'] = 'OK';
        this.cbr(dat);
    }
    
    constructor(url, cb) { this.serverURL = url; this.cbr = cb; }
}

class dragKwBaseClass {
    
    send(e) {
       const newordx = this.ordo.getOrdx(e);        
       const id = this.getIDDB(e);
       this.neto.send(id, newordx);
    }
    
    constructor() {
        this.dragKwInit10();
        this.setDocumentLevelDrag();
    }
    
    setServerURL(u) { 
        this.ordServerURL = u; 
        this.neto = new dragKwNetClass(this.ordServerURL, (arg) => { this.doResponse(arg); });        
    }
    
    dragKwInit10() {
        this.ableEs = [];
        this.eidpre = 'e_dragKwP_';
        this.viso = new dragKwVisClass();
        this.ordo = new dragKwOrdClass();

    }
    
    doResponse(res) {
        const e = byid(this.eidpre + res['response_id']);
        this.ordo.netOKOrDie(e, res['response_ordx']);
        this.able(true);
        this.viso.ok();
    }
    
    cmp(a, b) {
        const ai = this.getI(a);
        const bi = this.getI(b);
        if (ai < bi) return 'below';
        return 'above';
    }
    
    calcNewOrd(drr) {
        const dri = this.getI(drr);
        const prev = this.getRowByI(dri - 1);
        const next = this.getRowByI(dri + 1);
        this.ordo.set(prev, drr, next);
        this.reordSanityCheck();
        
    }
    
    getRowByI(iin) {
        if (iin < 0) return false;
        const ch = this.thegpe.childNodes;
        const chn = ch.length;        
        if (iin >= chn) return false;
        
        let di = -1;
        for (let i=0; i < chn; i++) {
            const che = ch[i];
            if (kwifs(che, 'dataset', 'dragKwIamP')) ++di;
            if (di === iin) return che;
        }
        
        return false;
    }
    
    reordSanityCheck() {
        const ch = this.thegpe.childNodes;
        const chn = ch.length;
        let maxx = 0;
        let oki = 0;
        for (let i=0; i < chn; i++) {
            const che = ch[i];
            if (kwifs(che, 'dataset', 'dragKwIamP')) {
                const ordx = this.ordo.getOrdx(che);
                kwas(ordx > maxx, 'reorder sanity check fail kwdrag');
                oki++;
                maxx = ordx;
            }
        }
        
        const orden = this.ordo.getn();
        kwas(orden === oki, 'sanity check count fail kwdrag');
        
        return true;
        
    }
    
    getI(e) {
        const row = this.getRow(e);
        const ch = this.thegpe.childNodes;
        const chn = ch.length;
        let di = -1;
        for (let i=0; i < chn; i++) {
            const che = ch[i];
            if (kwifs(che, 'dataset', 'dragKwIamP')) ++di;
            if (        this.getIDDB(che) 
                    === this.getIDDB(row)) return di;
        }
        
        kwas(false, 'getI() no result');
    }
    
    getIDDB(e) {
        return kwifs(this.getRow(e), 'dataset', 'dragKwUqID');
    }
    
    setDocumentLevelDrag() {

        document.addEventListener('dragstart', (ev) => { 
            this.draggedE = ev.target;
        });

        document.addEventListener('dragenter', (ev) => { 
            const ovr = this.getRow(ev.target);
            if (!ovr) return;
            this.viso.onOver(this.draggedE, this.cmp(this.draggedE, ev.target), ovr);
            this.dorow = ovr;
           
        }); 
        document.addEventListener('drop'	 , (ev) => { 
            
            this.able(false);
            
            this.viso.setState('dropBFNet');
            
            const dir = this.cmp(this.draggedE, this.dorow);
            const edr = this.getRow(this.draggedE);
            if (dir === 'above') 
                 this.insertBefore(this.dorow, edr);
            else this.insertAfter (this.dorow, edr);
            
            this.calcNewOrd(edr);

            this.send(edr);
            
            this.viso.onOver(false, 'clear');
            
        });
        
        document.addEventListener('dragover' , (ev) => { ev.preventDefault(); });        
    }
    

    insertBefore(stayingEle, movingEle) { stayingEle.parentNode.insertBefore(movingEle, stayingEle); /* insertBefore is a JS function */ }
    insertAfter (stayingEle, movingEle) { var se2 = stayingEle.nextSibling; if (se2) this.insertBefore(se2, movingEle); else movingEle.parentNode.append(movingEle);}    
    
    
    able(isen) {
        for (let i=0; i < this.ableEs.length; i++) {
            const e = this.ableEs[i];
            e.draggable = isen;
            e.style.opacity = isen ? 1 : 0;
        }
        
    }
    
    setEleDraggable(e) { 
        e.draggable = true; 
        this.ableEs.push(e);
    }
    
    setDragParent(e, uq, ordx, interval) {
        e.dataset.dragKwIamP = true;
        if (uq) e.dataset.dragKwUqID = uq;
        e.id = this.eidpre + uq;
        this.ordo.inite(e, ordx, interval);
            
         
    }
    
    getRow(e) {
      if (kwifs(e,'dataset','dragKwIamP')) return e;
      if (e.parentNode) return this.getRow(e.parentNode);
      return false;     
    }
    
    dragKwSetGrandParentE(e) {  
        this.thegpe = e;  
        this.viso.setEle(e);
    }
    
}
