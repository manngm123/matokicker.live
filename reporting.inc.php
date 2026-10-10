<?php
/**
 * MaTo Kicker – gemeinsame Reporting-Bausteine
 * Eingebunden von backend.php (Reiter 1 „Reporting“) und reporter.php (freigegebener Spieltag),
 * damit beide immer denselben Funktionsstand haben.
 *   mk_css()          – Stylesheet (Inhalt des <style>-Blocks)
 *   mk_shared_js()    – gemeinsame Helfer (API, Zeiten, Spieler, Mannschaften)
 *   mk_reporting_js() – Reporting-Logik (Karten, Tore, Halbzeiten, Elfmeter, Live-Abgleich)
 */
function mk_css() { ?>
  :root{
    --red:#960000;--red-l:rgb(190,0,0);--red-p:#ffeaea;
    --blue:#1565c0;--blue-p:#e3f2fd;
    --green:#2e7d32;--green-p:#e8f5e9;
    --orange:#e65100;--orange-p:#fff3e0;
    --gray:#546e7a;--gray-l:#eceff1;--gray-p:#fafafa;
    --border:#cfd8dc;--text:#263238;--shadow:0 2px 8px rgba(0,0,0,.12);
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html,body{max-width:100%;overflow-x:hidden}
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4f8;color:var(--text);min-height:100vh}
  /* Layout-Schutz: nichts darf breiter als der sichtbare Bereich werden */
  .content,.card{max-width:100%}
  th,td{overflow-wrap:anywhere;word-break:break-word}
  .table-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
  select,input,textarea{max-width:100%}
  .login-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh}
  .login-box{background:#fff;border-radius:12px;box-shadow:var(--shadow);padding:40px 36px;width:320px;text-align:center}
  .login-box img{height:80px;margin-bottom:12px}
  .login-box h1{font-size:1.4rem;color:var(--red);margin-bottom:6px}
  .login-box p{color:var(--gray);font-size:.9rem;margin-bottom:24px}
  .login-box input{width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:1rem;margin-bottom:12px}
  .login-box button{width:100%;padding:11px;background:var(--red);color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer}
  .login-box button:hover{background:var(--red-l)}
  .error-msg{color:var(--red);font-size:.9rem;margin-bottom:10px}
  header{background:linear-gradient(135deg,#640000,#960000);color:#fff;padding:10px 16px;display:flex;align-items:center;justify-content:space-between}
  .hdr-l{display:flex;align-items:center;gap:10px}
  .hdr-logo{height:44px;width:auto}
  header h1{font-size:1.1rem}
  header a.logout{color:#fff;font-size:.85rem;text-decoration:none;opacity:.8}
  .online-badge{color:#fff;font-size:.85rem;background:rgba(255,255,255,.15);padding:4px 10px;border-radius:20px;white-space:nowrap}
  .btn-install{background:#ffcc00;color:#6a0000;border:1px solid #ffcc00;font-weight:700;border-radius:6px;padding:5px 11px;cursor:pointer;font-size:.82rem;white-space:nowrap}
  .btn-install:hover{background:#ffd633}
  .ios-help{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:2000;align-items:center;justify-content:center;padding:20px}
  .ios-card{background:#fff;border-radius:14px;max-width:340px;width:100%;padding:20px 20px 22px;position:relative;box-shadow:0 12px 44px rgba(0,0,0,.35);color:#263238}
  .ios-card h3{color:#960000;margin-bottom:8px;font-size:1.1rem}
  .ios-card p{font-size:.9rem;margin:6px 0}
  .ios-card ol{margin:10px 0 6px 20px;font-size:.9rem}
  .ios-card ol li{margin:6px 0}
  .ios-note{font-size:.8rem;color:#546e7a}
  .ios-close{position:absolute;top:8px;right:10px;border:none;background:none;font-size:1.15rem;color:#546e7a;cursor:pointer;line-height:1}
  header a.logout:hover{opacity:1}
  nav.tabs{background:#fff;border-bottom:2px solid var(--red);display:flex;overflow-x:auto}
  nav.tabs a{padding:12px 14px;font-size:.85rem;color:var(--gray);text-decoration:none;white-space:nowrap;border-bottom:3px solid transparent;margin-bottom:-2px;transition:.15s}
  nav.tabs a:hover{color:var(--red);background:var(--red-p)}
  nav.tabs a.active{color:var(--red);border-bottom-color:var(--red);font-weight:600}
  .content{max-width:900px;margin:16px auto;padding:0 12px}
  .card{background:#fff;border-radius:10px;box-shadow:var(--shadow);padding:18px;margin-bottom:18px}
  .card h2{font-size:1rem;color:var(--red);margin-bottom:14px;border-bottom:1px solid var(--gray-l);padding-bottom:8px}
  .card h3{font-size:.9rem;color:var(--gray);margin:14px 0 8px}
  .row{display:flex;align-items:center;gap:6px;margin-bottom:6px;flex-wrap:wrap}
  input[type=text],input[type=date],select{padding:7px 10px;border:1px solid var(--border);border-radius:6px;font-size:.9rem;background:#fff;max-width:100%}
  input:focus,select:focus{outline:none;border-color:var(--red);box-shadow:0 0 0 2px rgba(150,0,0,.12)}
  .inp-name{flex:1;min-width:140px}
  .inp-sm{width:130px}
  .center{text-align:center}
  .btn{padding:6px 14px;border:none;border-radius:6px;cursor:pointer;font-size:.88rem;font-weight:500;transition:.15s;display:inline-flex;align-items:center;gap:4px}
  .btn-red {background:var(--red);color:#fff}       .btn-red:hover {background:var(--red-l)}
  .btn-del {background:var(--red-p);color:var(--red)}.btn-del:hover{background:var(--red);color:#fff}
  .btn-blue{background:var(--blue-p);color:var(--blue)}.btn-blue:hover{background:var(--blue);color:#fff}
  .btn-gray{background:var(--gray-l);color:var(--gray)}.btn-gray:hover{background:var(--border)}
  .btn-sm  {padding:4px 10px;font-size:.82rem}
  .btn-save{margin-top:12px;width:100%;padding:10px;font-size:.95rem}
  table{width:100%;border-collapse:collapse;font-size:.88rem}
  th{background:var(--red-p);color:var(--red);padding:8px 10px;text-align:left;font-weight:600}
  td{padding:7px 10px;border-bottom:1px solid var(--gray-l)}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:var(--gray-p)}
  .notice{background:var(--orange-p);border-left:4px solid var(--orange);padding:9px 12px;border-radius:0 6px 6px 0;font-size:.85rem;margin-bottom:12px;color:var(--orange)}
  .toast{position:fixed;bottom:16px;right:16px;background:var(--red);color:#fff;padding:10px 18px;border-radius:8px;box-shadow:var(--shadow);font-size:.9rem;z-index:9999;display:none}

  /* REPORTING CARDS */
  .match-cards{display:flex;flex-direction:column;gap:12px}
  .mc{background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden}
  .mc-head{display:flex;align-items:flex-start;gap:6px;padding:8px 12px;background:var(--gray-p);border-bottom:1px solid var(--border);flex-wrap:nowrap}
  .mc-head-left{display:flex;align-items:center;gap:6px;flex:1;flex-wrap:wrap}
  .mc-head-center{display:flex;align-items:center;justify-content:center;min-width:54px;height:30px}
  .mc-head-right{display:flex;align-items:center;gap:4px;height:30px}
  .sel-status{font-size:.82rem;padding:5px 8px;border-radius:6px;border:1px solid var(--border);cursor:pointer;font-weight:600}
  .sel-status.ns {background:#e3f2fd;color:#1565c0;border-color:#90caf9}
  .sel-status.run{background:#e8f5e9;color:#2e7d32;border-color:#a5d6a7}
  .sel-status.half{background:var(--orange-p);color:var(--orange);border-color:#ffb74d}
  .sel-status.fin{background:var(--gray-l);color:var(--gray);border-color:#b0bec5}
  .sel-status.pen{background:#ede7f6;color:#5e35b1;border-color:#b39ddb}
  .half-badge{font-size:.68rem;font-weight:700;padding:2px 6px;border-radius:5px;background:var(--orange-p);color:var(--orange);white-space:nowrap}
  .btn-half{background:var(--orange-p);color:var(--orange);border:1px solid #ffb74d}
  .btn-half:hover{background:var(--orange);color:#fff}
  /* Karten */
  .mc-card-input{padding:6px 12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;flex-wrap:wrap;background:#fffdf7}
  .sel-card{font-size:.88rem;flex:1;min-width:130px}
  .btn-card{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;border-radius:6px;border:1px solid var(--border);background:#fff;font-size:1rem;cursor:pointer;padding:0 6px;transition:.15s}
  .btn-card:disabled{opacity:.32;cursor:not-allowed}
  .btn-card:hover:not(:disabled){background:var(--gray-l)}
  .mc-cards{padding:2px 12px 8px;font-size:.8rem;color:var(--gray);display:flex;flex-wrap:wrap;gap:6px}
  .card-chip{display:inline-flex;align-items:center;gap:3px;background:#f5f5f5;border:1px solid var(--border);border-radius:12px;padding:1px 8px;cursor:pointer}
  .card-chip:hover{background:var(--red-p);border-color:var(--red);color:var(--red)}
  .card-chip .ck-home{color:var(--red);font-weight:600}
  .card-chip .ck-guest{color:#1565c0;font-weight:600}
  .sel-field{font-size:.8rem;padding:4px 7px;border-radius:6px;border:1px solid #90caf9;background:#e3f2fd;color:#1565c0;cursor:pointer;font-weight:500}
  .inp-kickoff{font-size:.85rem;padding:4px 7px;border-radius:6px;border:1px solid #ce93d8;background:#f3e5f5;color:#6a1b9a;font-weight:500;width:96px;text-align:center}
  .timer{font-size:.82rem;font-weight:700;font-family:monospace;padding:4px 9px;border-radius:5px;min-width:54px;text-align:center}
  .timer.run{background:#e8f5e9;color:#2e7d32}
  .timer.half{background:var(--orange-p);color:var(--orange)}
  .timer.fin{background:var(--gray-l);color:var(--gray)}
  .timer.pen{background:#ede7f6;color:#5e35b1}
  /* Pokal: Halbzeitstand, Elfmeter */
  .ht-sm{font-size:.72rem;font-weight:600;color:var(--gray);text-align:center;margin-top:3px}
  .pen-sm.pen-live{white-space:nowrap;font-size:1.05rem;background:#ede7f6;border-radius:6px;padding:2px 8px;margin-top:5px}
  .pen-sm{font-size:.85rem;font-weight:800;color:#5e35b1;text-align:center;margin-top:3px}
  .pen-tg{display:flex;align-items:center;justify-content:center;gap:4px;font-size:.75rem;font-weight:600;color:#5e35b1;margin-top:5px;cursor:pointer;user-select:none}
  .pen-tg input{width:16px;height:16px;accent-color:#5e35b1}
  .pen-mode{display:flex;flex-direction:column;align-items:flex-start;gap:2px;margin-top:4px}
  .pen-mode .pen-tg{margin-top:0}
  .pen-tg-hit{color:#2e7d32}
  .pen-tg-hit input{accent-color:#2e7d32}
  .pen-tg-miss{color:#c62828}
  .pen-tg-miss input{accent-color:#c62828}
  .btn-goal.btn-miss{color:#c62828;border-color:#c62828;background:#ffebee;font-size:1.05rem}
  .btn-goal.btn-miss:hover:not(:disabled){background:#c62828;color:#fff}
  .pen-tag{display:inline-block;font-size:.62rem;font-weight:700;padding:0 4px;border-radius:4px;background:#ede7f6;color:#5e35b1;margin-left:3px;vertical-align:1px}
  .mc-pen{padding:8px 12px;border-bottom:1px solid var(--border);background:#f6f2fc}
  .pen-head{font-size:.85rem;font-weight:700;color:#5e35b1;margin-bottom:6px;display:flex;justify-content:space-between}
  .pen-row{display:flex;flex-direction:column;gap:6px;margin:6px 0 10px}
  .pen-row+.pen-row{border-top:1px dashed #d1c4e9;padding-top:10px}
  .pen-line{display:flex;align-items:center;gap:8px}
  .pen-line .sel-scorer{flex:1;min-width:0}
  .pen-btns{display:flex;gap:8px}
  .pen-btns .btn-card{flex:1;height:38px;font-size:.88rem;background:#fff}
  .pen-row .pen-lbl{font-size:.78rem;font-weight:600;width:76px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .btn-card.pen-ok{color:var(--green);border-color:var(--green);font-weight:700}
  .btn-card.pen-miss{color:var(--red);border-color:var(--red);font-weight:700}
  .btn-card.pen-miss-btn{font-size:.75rem;font-weight:700;color:#5e35b1;border-color:#b39ddb;padding:0 8px;white-space:nowrap}
  .pen-tag.pen-tag-miss{background:#ffebee;color:#c62828;margin-left:0}
  .mc-pens{padding:2px 12px 8px;display:flex;flex-wrap:wrap;gap:6px;font-size:.8rem}
  .timer.ns{visibility:hidden}
  .mc-team-row{padding:6px 12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;flex-wrap:wrap}
  .mc-scorer{padding:6px 12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;flex-wrap:wrap}
  .sel-scorer{font-size:.88rem;flex:1;min-width:130px}
  .scorer-guest-named{color:#1565c0;font-weight:600}
  .sel-home-team{font-size:.88rem;flex:1;min-width:140px}
  .sel-guest{flex:1;font-size:.88rem;min-width:140px}
  .mc-score{padding:10px 12px;border-bottom:1px solid var(--border);display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);align-items:center;gap:6px}
  .mc-score-home{display:flex;flex-direction:column;align-items:center;gap:6px;min-width:0}
  .mc-score-guest{display:flex;flex-direction:column;align-items:center;gap:6px;min-width:0}
  .sel-scorer.sel-col{width:100%;min-width:0;font-size:.82rem;padding:6px 4px}
  .mc-score-mid{display:flex;align-items:center;justify-content:center}
  .team-label{font-size:.78rem;font-weight:600;color:var(--gray);text-align:center;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .score-box{font-size:1.5rem;font-weight:900;min-width:80px;text-align:center;color:var(--red);background:var(--red-p);border-radius:8px;padding:5px 10px}
  .btn-row{display:flex;gap:6px;align-items:center;justify-content:center}
  .btn-goal{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;border:2px solid;background:transparent;font-size:1.3rem;font-weight:700;line-height:1;cursor:pointer;transition:.15s;flex-shrink:0;padding:0}
  .btn-goal:disabled{opacity:.32;cursor:not-allowed}
  .btn-plus {color:var(--green);border-color:var(--green);background:var(--green-p)}.btn-plus:hover:not(:disabled){background:var(--green);color:#fff}
  .btn-minus{color:var(--red);border-color:var(--red);background:var(--red-p)}.btn-minus:hover:not(:disabled){background:var(--red);color:#fff}
  .mc-guest{padding:6px 12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px}
  .mc-scorers{padding:6px 12px;font-size:.8rem;color:var(--gray);min-height:24px}
  .scorer-home{color:var(--red);font-weight:600}
  .scorer-guest{color:#999}
  .goal-min{font-size:.75rem;color:var(--gray);font-variant-numeric:tabular-nums}
  /* Spieler-Matrix (Reiter 4) */
  .pm-table th.pm-col{text-align:center;font-size:.8rem;min-width:58px;white-space:nowrap;word-break:normal}
  .pm-table td{vertical-align:middle}
  .pm-table td.pm-c{text-align:center;padding:4px}
  .pm-table input[type=checkbox]{width:20px;height:20px;accent-color:var(--red);cursor:pointer}
  .pm-table .p-name{width:100%;min-width:120px;padding:6px 8px;border:1px solid var(--border);border-radius:6px;font-size:.9rem}
  .pm-table .p-name.pm-err{border-color:var(--red);background:var(--red-p)}
  .pm-table th:first-child,.pm-table td:first-child{position:sticky;left:0;z-index:1;background:#fff}
  .pm-table th:first-child{background:var(--red-p)}
  .pm-table tr.pm-orphan td{background:var(--orange-p)}
  .pm-extra{font-size:.72rem;color:var(--gray);margin-top:3px}
  .pm-count{font-size:.8rem;color:var(--gray);margin:8px 0 0}
  /* Spieltagsliste (Reiter 2) */
  .md-tbl{table-layout:auto}
  .md-tbl th,.md-tbl td{overflow-wrap:normal;word-break:normal;vertical-align:middle}
  .md-tbl td.md-date{white-space:nowrap;font-variant-numeric:tabular-nums;width:1%}
  .md-tbl .md-time{display:block;font-size:.75rem;color:var(--gray)}
  .md-tbl td.md-ev{overflow-wrap:break-word;hyphens:auto;line-height:1.35}
  .md-tbl td.md-act{white-space:nowrap;text-align:right;width:1%}
  .md-tbl td.md-act .btn{margin-left:2px;padding:5px 7px;min-width:0}
  .md-tbl th,.md-tbl td{padding-left:6px;padding-right:6px}
  .md-tbl th:first-child,.md-tbl td:first-child{padding-left:8px}
  .md-tbl tr.md-today td{background:#fff8e1}
  .md-tbl tr.md-today td.md-date{font-weight:700;color:var(--red)}
  .md-tbl tr.md-sep td{background:var(--gray-l);color:var(--gray);font-size:.78rem;font-weight:600;padding:5px 10px}
  .md-tbl td.md-empty{color:var(--gray);text-align:center}
  .md-past-btn{margin-top:10px}
  /* Reporter-Freigabe (Reiter 2) */
  .btn-rp-on{background:#2e7d32;color:#fff}
  .btn:disabled{opacity:.4;cursor:not-allowed}
  .rp-row td{background:#e8f5e9;border-bottom:1px solid var(--gray-l)}
  .rp-box{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;font-size:.85rem;color:#2e7d32;font-weight:600}
  .rp-btns{display:flex;gap:6px}
  /* Kader (Reiter 2) */
  .sq-block{border:1px solid var(--border);border-radius:8px;background:#fff;padding:10px 12px;margin-bottom:10px}
  .sq-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;margin-bottom:8px}
  .sq-head b{color:var(--red)}
  .sq-cnt{font-size:.8rem;color:var(--gray);margin-left:6px}
  .sq-sub{font-size:.75rem;color:var(--gray);margin:8px 0 4px}
  .sq-list{display:flex;flex-wrap:wrap;gap:6px}
  .sq-chip{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border:1px solid var(--border);border-radius:16px;font-size:.85rem;cursor:pointer;background:#fafafa;user-select:none}
  .sq-chip:has(input:checked){background:var(--red-p);border-color:var(--red);color:var(--red)}
  .sq-chip input{accent-color:var(--red)}
  .sq-empty{font-size:.82rem;color:var(--gray)}
  /* Editor (Reiter 5) */
  .ed-card{border:1px solid var(--border);border-radius:10px;background:#fff;margin-bottom:14px;overflow:hidden}
  .ed-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;padding:10px 12px;background:var(--red-p)}
  .ed-head b{color:var(--red)}
  .ed-score{font-size:1.05rem;font-weight:700;font-variant-numeric:tabular-nums;margin-left:6px}
  .ed-sec{padding:10px 12px;border-top:1px solid var(--gray-l)}
  .ed-sec h4{font-size:.82rem;color:var(--gray);margin-bottom:6px;font-weight:600}
  .ed-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px}
  .ed-grid label{display:flex;flex-direction:column;font-size:.75rem;color:var(--gray);gap:3px}
  .ed-card input,.ed-card select{padding:6px 8px;border:1px solid var(--border);border-radius:6px;font-size:.88rem;background:#fff;min-width:0;max-width:100%}
  .ed-card input[type=checkbox]{width:18px;height:18px;accent-color:var(--red)}
  .ed-tbl td{padding:4px;border-bottom:1px solid var(--gray-l)}
  .ed-tbl th{padding:6px 4px;font-size:.78rem}
  .ed-tbl td.pm-c{text-align:center}
  .ed-tbl input.g-name,.ed-tbl input.c-name{min-width:130px;width:100%}
  .ed-tbl input[type=number]{width:64px}
  .ed-stand{font-variant-numeric:tabular-nums;color:var(--gray);font-size:.8rem;white-space:nowrap}
  .ed-actions{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
  .ed-hint{font-size:.75rem;color:var(--gray);margin-top:6px}
<?php }

function mk_shared_js() { ?>
const API='api.php';
let allTeams=[],allPlayers=[],allMatchdays=[],reportResults=[];
let currentMd=null,currentMdId='';
const timerIntervals={};

// Reporter-Modus (reporter.php): fester Spieltag + Freigabe-Schlüssel, sonst Backend (Admin)
const RP_TOKEN=window.RP_TOKEN||'';
const RP_FIXED_MD=window.RP_FIXED_MD||'';
let apiAuthWarned=false;
async function api(action,body=null){
  const opts={method:body?'POST':'GET',headers:{'Content-Type':'application/json'},cache:'no-store'};
  if(RP_TOKEN)opts.headers['X-Reporter-Token']=RP_TOKEN;
  if(body)opts.body=JSON.stringify(body);
  const res=await fetch(`${API}?action=${action}`,opts);
  let data;
  try{data=await res.json();}catch(e){data={error:`Serverfehler (${res.status})`};}
  if((res.status===401||res.status===403)&&!apiAuthWarned){
    apiAuthWarned=true;
    alert(RP_TOKEN?'Die Reporter-Freigabe wurde beendet oder ist abgelaufen. Änderungen werden nicht mehr gespeichert.'
                  :'Du bist nicht (mehr) angemeldet. Bitte neu anmelden – die Seite wird neu geladen.');
    if(!RP_TOKEN)location.reload();
  }
  return data;
}
function toast(msg='✓ Gespeichert!'){
  const t=document.getElementById('toast');t.textContent=msg;t.style.display='block';
  setTimeout(()=>t.style.display='none',2200);
}
function uid(){return 'tmp_'+Math.random().toString(36).substr(2,9);}
function normDate(d){return d?d.replace(/\//g,'-').substring(0,10):'';}
function fmtDate(d){const p=normDate(d).split('-');if(p.length<3)return d;const z=n=>String(n).padStart(2,'0');return z(p[2])+'.'+z(p[1])+'.'+p[0];}
function halfLenSec(){return ((currentMd&&+currentMd.half_length)||30)*60;}
// Gespielte Zeit (Sekunden). In der 2. Halbzeit startet die Uhr bei der Halbzeitlänge
// (z. B. 30:00) und läuft weiter; während der Halbzeit-Pause ist sie eingefroren.
function elapsedSec(r){
  if(!r.started_at)return 0;
  const now=Math.floor(Date.now()/1000);
  const half=r.current_half||1;
  const st=r.status||'not_started';
  if(half>=3){
    // Verlängerung: startet bei 2× Halbzeitlänge; vor dem Anpfiff eingefroren
    const base=2*(halfLenSec());
    if(!r.et_start_at)return base;
    return base+Math.max(0,(r.et_end_at||r.ended_at||now)-r.et_start_at);
  }
  if(half>=2){
    const base=halfLenSec();
    const s2=r.second_half_start_at||now;
    return base+Math.max(0,(r.half2_end_at||r.ended_at||now)-s2);
  }
  if(st==='halftime')return Math.max(0,(r.half1_end_at||now)-r.started_at);
  return Math.max(0,(r.ended_at||now)-r.started_at);
}
// ── Pokal-Helfer: Halbzeitstand, Elfmeterschießen, Live-Status ──
function isGuestScorer(s){return s==='__gast__'||String(s||'').startsWith('guest:');}
// Torminute als Text, Nachspielzeit als „30+2'"; leer bei Altdaten ohne Minute
function goalMinTxt(info,halfMin){
  if(!info||info.minute==null||info.minute==='')return '';
  const m=+info.minute,h=+halfMin||30;
  if(info.phase===1&&m>h)return `${h}+${m-h}'`;
  if(info.phase===2&&m>2*h)return `${2*h}+${m-2*h}'`;
  return `${m}'`;
}
// Halbzeitstand aus den Tor-Phasen; null, wenn Altdaten ohne Phase vorhanden sind
function htScore(r){
  const sc=r.scorers||[],info=r.scorer_info||[];let h=0,g=0;
  for(let i=0;i<sc.length;i++){const f=info[i];if(!f||!f.phase)return null;if(f.phase===1){isGuestScorer(sc[i])?g++:h++;}}
  return {h,g};
}
function penScore(r){let h=0,g=0;(r.pen_shots||[]).forEach(p=>{if(p.scored){p.team==='guest'?g++:h++;}});return {h,g};}
function hasPens(r){return (r.status==='penalties')||((r.pen_shots||[]).length>0);}
function isLive(r){return r.status==='running'||(r.status==='extra_time'&&!!r.et_start_at);}
function isActive(r){return ['running','extra_time','penalties'].includes(r.status);}
function fmtTime(sec){const m=Math.floor(sec/60),s=sec%60;return String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');}
function startLiveTimer(rid){stopLiveTimer(rid);timerIntervals[rid]=setInterval(()=>{const el=document.querySelector(`.timer[data-rid="${rid}"]`);const r=reportResults.find(x=>x.id===rid);if(el&&r)el.textContent=fmtTime(elapsedSec(r));},1000);}
function stopLiveTimer(rid){if(timerIntervals[rid]){clearInterval(timerIntervals[rid]);delete timerIntervals[rid];}}

// ── Spieler & MaTo-Mannschaften ──────────────────────────────────────────
// Pseudo-Spieler aus Altdaten: werden als feste Auswahloptionen angeboten, nicht als Spieler
const RESERVED_NAMES=['eigentor','noname'];
function isReservedName(n){return RESERVED_NAMES.includes(String(n||'').trim().toLowerCase());}
function byName(a,b){return a.name.localeCompare(b.name,'de',{sensitivity:'base'});}
function playerTeamIds(p){return Array.isArray(p.team_id)?p.team_id:[p.team_id].filter(Boolean);}
function isHomeTeam(id){return !!id&&allTeams.some(t=>t.id===id&&t.is_home);}
function getHomeTeams(){return allTeams.filter(t=>t.is_home);}
// Stammspieler einer Mannschaft (ohne Pseudo-Spieler), alphabetisch
function getPlayersForTeam(teamId){
  if(!teamId)return [];
  return allPlayers.filter(p=>!isReservedName(p.name)&&playerTeamIds(p).includes(teamId)).sort(byName);
}
// Aushilfe-Pool: Spieler anderer MaTo-Mannschaften, ohne die Stammspieler der übergebenen Teams
function getMatoPool(excludeTeamIds){
  const ex=(excludeTeamIds||[]).filter(Boolean);
  const homeIds=getHomeTeams().map(t=>t.id);
  return allPlayers.filter(p=>{
    if(isReservedName(p.name))return false;
    const tids=playerTeamIds(p);
    return tids.some(id=>homeIds.includes(id))&&!tids.some(id=>ex.includes(id));
  }).sort(byName);
}

// Online-Zähler: fragt den Heartbeat-Endpunkt ab (ohne cid → registriert sich nicht selbst)
async function pollOnline(){
  try{
    const res=await api('heartbeat',{cid:''});
    const el=document.getElementById('onlineCnt');
    if(el&&typeof res.count==='number')el.textContent=res.count;
  }catch(e){/* Zähler ist unkritisch */}
}
pollOnline();setInterval(pollOnline,30000);

// Standard-Heimmannschaft: MaTo-Team des Spieltags, sonst erstes MaTo-Team überhaupt
function getMatchdayHomeTeams(md){
  if(!md)return [];
  return (md.team_ids||[]).map(id=>allTeams.find(t=>t.id===id)).filter(t=>t&&t.is_home);
}
function getDefaultHomeTeam(md){
  const mdHome=getMatchdayHomeTeams(md===undefined?currentMd:md);
  return mdHome[0]||getHomeTeams()[0]||null;
}
// <option>-Liste für Spieler; val(p) liefert den gespeicherten Wert
function playerOpts(list,val,selected,suffix=''){
  return list.map(p=>{const v=val(p);return `<option value="${v}" ${selected===v?'selected':''}>${p.name}${suffix}</option>`;}).join('');
}
function optGroup(label,inner){return inner?`<optgroup label="${label}">${inner}</optgroup>`:'';}
// Kader eines MaTo-Teams am Spieltag (Spieler-Objekte) oder null, wenn kein (bzw. ein leerer) Kader hinterlegt ist
function squadPlayers(md,teamId){
  const ids=md&&md.squads&&Array.isArray(md.squads[teamId])?md.squads[teamId]:null;
  if(!ids||!ids.length)return null;
  return allPlayers.filter(p=>ids.includes(p.id)&&!isReservedName(p.name)).sort(byName);
}
// Auswahlgruppen für ein Team: mit Kader → nur der Kader (andere Spieler können in dem Spiel nicht treffen),
// ohne Kader → Stammspieler + Aushilfen aus anderen MaTo-Teams
function teamGroups(md,teamId,otherId,teamLbl){
  const otherMato=isHomeTeam(otherId);
  const sq=squadPlayers(md,teamId);
  if(sq)return [{label:teamLbl+' – Kader',list:sq}];
  return [{label:teamLbl,list:getPlayersForTeam(teamId)},
          {label:'Andere MaTo-Spieler',list:getMatoPool([teamId,otherMato?otherId:null])}];
}
function groupOpts(groups,val,selected,suffix=''){return groups.map(g=>optGroup(g.label,playerOpts(g.list,val,selected,suffix))).join('');}

<?php }

function mk_reporting_js() { ?>
/* ═══ TAB 1 – REPORTING ════════════════════════════════════════════════════ */
const STATUS_OPTS=[
  {v:'not_started',label:'Nicht gestartet'},
  {v:'running',    label:'Läuft'},
  {v:'halftime',   label:'Halbzeit'},
  {v:'extra_time', label:'Verlängerung'},
  {v:'penalties',  label:'Elfmeterschießen'},
  {v:'finished',   label:'Beendet'}
];
const FIELD_OPTS=['Feld 1','Feld 2','Feld 3','Feld 4','Feld 5','Feld 6'];
const CARD_TYPES={yellow:'🟨',yellow_red:'🟨🟥',red:'🟥'};

async function initReporting(){
  allTeams=await api('get_teams');allPlayers=await api('get_players');allMatchdays=await api('get_matchdays');
  if(RP_FIXED_MD){await loadReporting(RP_FIXED_MD);return;}
  const sel=document.getElementById('reportMd');
  function isUpcomingOrToday(d){
    if(!d)return true;
    const p=normDate(d).split('-');
    if(p.length<3)return true;
    const dt=new Date(+p[0],+p[1]-1,+p[2]);
    const today=new Date(); today.setHours(0,0,0,0);
    return dt>=today;
  }
  const filtered=[...allMatchdays]
    .filter(m=>isUpcomingOrToday(m.date))
    .sort((a,b)=>normDate(a.date).localeCompare(normDate(b.date)));
  if(!filtered.length){
    const o=document.createElement('option');
    o.textContent='– Kein aktueller Spieltag vorhanden –';
    o.disabled=true; sel.appendChild(o);
  }
  filtered.forEach(md=>{
    const o=document.createElement('option');o.value=md.id;
    o.textContent=`${fmtDate(md.date)} – ${md.event}`;
    sel.appendChild(o);
  });
}

async function loadReporting(forcedId){
  currentMdId=forcedId||document.getElementById('reportMd').value;
  Object.keys(timerIntervals).forEach(rid=>stopLiveTimer(rid));
  const wrap=document.getElementById('matchCards');
  if(!currentMdId){wrap.innerHTML='';document.getElementById('addMatchBtn').style.display='none';return;}
  currentMd=allMatchdays.find(m=>m.id===currentMdId);
  reportResults=await api(`get_results&matchday_id=${currentMdId}`);
  if(!Array.isArray(reportResults))reportResults=[];
  rpSig=rpSignature(reportResults);
  renderCards();
  document.getElementById('addMatchBtn').style.display='block';
}

// ── Live-Abgleich: mehrere Geräte am selben Spieltag (Backend + Reporter) ──
// Jede Begegnung trägt eine Revisionsnummer (rev). Geänderte Stände anderer Geräte werden
// automatisch übernommen; ein Speichern auf veraltetem Stand lehnt der Server ab (Konflikt).
let rpSig='',rpSaving=0,rpPollBusy=false;
const PENDING_KEYS=['pending_scorer','pending_guest_scorer','pending_card','pending_pen','pending_pen_miss','pending_pen_home','pending_pen_guest'];
function rpSignature(list){return (list||[]).map(r=>`${r.id}:${r.rev||0}`).sort().join('|');}
// Nutzer wählt gerade etwas aus → nicht neu zeichnen (sonst springt die Auswahl weg)
function rpUserBusy(){
  const a=document.activeElement;
  return !!(a&&a.closest&&a.closest('#matchCards')&&/^(SELECT|INPUT|TEXTAREA)$/.test(a.tagName));
}
async function rpRefresh(force=false){
  if(!currentMdId||rpPollBusy)return;
  if(!force&&(rpSaving||rpUserBusy()||document.hidden))return;
  rpPollBusy=true;
  try{
    if(!force){
      const st=await api(`get_last_update&matchday_id=${currentMdId}`);
      if(!st||st.sig===undefined||st.sig===rpSig)return;
    }
    const fresh=await api(`get_results&matchday_id=${currentMdId}`);
    if(!Array.isArray(fresh))return;
    // Lokale, noch nicht gespeicherte Auswahl (Torschütze, Karte, Elfmeter) behalten
    fresh.forEach(n=>{const o=reportResults.find(x=>x.id===n.id);if(o)PENDING_KEYS.forEach(k=>{if(o[k]!==undefined)n[k]=o[k];});});
    reportResults=fresh;rpSig=rpSignature(fresh);renderCards();
  }finally{rpPollBusy=false;}
}
setInterval(()=>rpRefresh(false),5000);
document.addEventListener('visibilitychange',()=>{if(!document.hidden)rpRefresh(true);});

function getMatchdayTeams(){
  if(!currentMd)return allTeams;
  return (currentMd.team_ids||[]).map(id=>allTeams.find(t=>t.id===id)).filter(Boolean);
}

function statusClass(s,r){return s==='running'?'run':s==='extra_time'?((r&&r.et_start_at)?'run':'half'):s==='halftime'?'half':s==='penalties'?'pen':s==='finished'?'fin':'ns';}
// Spielphase als kurzes Label
function halfLabel(r){
  const st=r.status||'not_started';
  if(st==='halftime')return 'Pause';
  if(st==='running')return (r.current_half===2?'2. HZ':'1. HZ');
  if(st==='extra_time')return r.et_start_at?'Verl.':'Pause';
  if(st==='penalties')return 'i.E.';
  if(st==='finished')return hasPens(r)?'i.E.':((r.current_half||1)>=3?'n.V.':'');
  return '';
}

function renderCards(){
  const wrap=document.getElementById('matchCards');
  Object.keys(timerIntervals).forEach(rid=>stopLiveTimer(rid));
  wrap.innerHTML='';
  // Sortierung: läuft zuerst, dann nicht gestartet, dann beendet
  const order={running:0,extra_time:0,penalties:0,halftime:1,not_started:2,finished:3};
  const sorted=[...reportResults].sort((a,b)=>{
    const oa=order[a.status||'not_started']??2;
    const ob=order[b.status||'not_started']??2;
    return oa-ob;
  });
  sorted.forEach(r=>wrap.appendChild(buildCard(r)));
  sorted.forEach(r=>{if(isLive(r))startLiveTimer(r.id);});
}

function buildCard(r){
  const mdTeams=getMatchdayTeams();
  const st=r.status||'not_started';
  const locked=st==='finished';
  const sc=statusClass(st,r);
  const goalLock=locked||st==='penalties';
  const ht=(r.started_at&&(r.current_half||1)>=2)?htScore(r):null;
  const ps=hasPens(r)?penScore(r):null;
  const elapsed=elapsedSec(r);
  const field=r.field||'Feld 1';
  const kickoff=r.kickoff||'';

  // Heimmannschaft dieser Begegnung (überschreibbar)
  const defaultHome=getDefaultHomeTeam(currentMd);
  const homeTeamId=r.home_team_id||(defaultHome?defaultHome.id:null);
  const homeTeam=allTeams.find(t=>t.id===homeTeamId)||defaultHome;

  const guestTeam=allTeams.find(t=>t.id===r.guest_team_id);
  // Gastspieler nur bei MaTo gegen MaTo; sonst reichen „Gast" und „Eigentor"
  const guestTeamId=r.guest_team_id;
  const guestIsMato=isHomeTeam(guestTeamId);
  const homeLbl=homeTeam?homeTeam.name:'Heim';
  const guestLbl=guestTeam?guestTeam.name:'Gast';
  // Auswahlgruppen: Kader des Spieltags (falls hinterlegt), sonst Stammspieler + Aushilfen
  // Spielerlisten nur für MaTo-Teams; ein Gegner (egal ob Heim oder Gast) hat nur „Unbekannt“/„Eigentor“
  const homeIsMato=isHomeTeam(homeTeamId);
  const hG=homeIsMato?teamGroups(currentMd,homeTeamId,guestTeamId,homeLbl):[];
  const gG=guestIsMato?teamGroups(currentMd,guestTeamId,homeTeamId,guestLbl):[];
  const scorersList=(r.scorers||[]).map((s,i)=>{
    const gm=goalMinTxt(r.scorer_info?.[i],halfLenSec()/60);
    const score=(r.scorer_scores?.[i]?` (${r.scorer_scores[i]})`:'')+(r.scorer_info?.[i]?.pen?'<span class="pen-tag" title="Elfmeter">11m</span>':'')+(gm?` <span class="goal-min">${gm}</span>`:'');
    if(s==='__gast__') return `<span class="scorer-guest">Gast${score}</span>`;
    // Gast-Torschütze mit Name (Format: "guest:Name")
    if(s.startsWith('guest:')) return `<span class="scorer-guest-named">${s.slice(6)}${score}</span>`;
    if(s==='Noname') return `<span class="scorer-home">Unbekannt${score}</span>`;
    return `<span class="scorer-home">${s}${score}</span>`;
  }).join(', ');

  const statusOpts=STATUS_OPTS.map(o=>`<option value="${o.v}" ${st===o.v?'selected':''}>${o.label}</option>`).join('');
  // Heim-Auswahl: alle Mannschaften des Spieltags
  const homeOpts=mdTeams.map(t=>`<option value="${t.id}" ${homeTeamId===t.id?'selected':''}>${t.name}</option>`).join('');
  const guestOpts=mdTeams.map(t=>`<option value="${t.id}" ${r.guest_team_id===t.id?'selected':''}>${t.name}</option>`).join('');
  // Torschützen Heim: Stammspieler → Aushilfen → feste Optionen
  const scoreSfx=` (${r.score_home??0}:${r.score_guest??0})`;
  const idName=p=>p.name;
  const sonst=(val,sel,lbl)=>`<option value="${val}" ${sel===val?'selected':''}>${lbl}</option>`;
  const scorerOpts=`<option value="">– Torschütze –</option>`
    +groupOpts(hG,idName,r.pending_scorer,scoreSfx)
    +optGroup('Sonstiges',
       sonst('Noname',r.pending_scorer,'Unbekannt')
      +sonst('Eigentor',r.pending_scorer,`Eigentor ${guestLbl}`));

  // Torschützen Gast: Spieler nur bei MaTo gegen MaTo, sonst Gast / Eigentor
  const guestVal=p=>'guest:'+p.name;
  const guestScorerOpts=`<option value="">– Torschütze –</option>`
    +groupOpts(gG,guestVal,r.pending_guest_scorer,scoreSfx)
    +optGroup('Sonstiges',
       sonst('__gast__',r.pending_guest_scorer,'Unbekannt')
      +sonst('guest:Eigentor',r.pending_guest_scorer,`Eigentor ${homeLbl}`));

  // Karten: Spielerauswahl (Heim + Gast) inkl. anonymer Fallbacks
  const cardPlayerOpts=`<option value="">– Spieler wählen –</option>`
    +groupOpts(hG,p=>'home|'+p.name,r.pending_card)
    +groupOpts(gG,p=>'guest|'+p.name,r.pending_card)
    +sonst('home|__unbekannt__',r.pending_card,`${homeLbl} – unbekannt`)
    +sonst('guest|__unbekannt__',r.pending_card,`${guestLbl} – unbekannt`);
  // Verschossene Elfmeter im Spiel (nicht Elfmeterschießen)
  const penMissList=(r.pen_missed||[]).map((m,i)=>{
    const nm=(!m.player||m.player==='__unbekannt__')?(m.team==='guest'?'Gast':'Heim'):m.player;
    const min=(m.minute!=null&&m.minute!=='')?` ${m.minute}'`:'';
    return `<span class="card-chip" title="Eintrag entfernen" onclick="removePenMiss('${r.id}',${i})"><span class="pen-tag pen-tag-miss">11m ✗</span> <span class="${m.team==='guest'?'ck-guest':'ck-home'}">${nm}</span>${min}</span>`;
  }).join('');
  const cardsList=(r.cards||[]).map((c,i)=>{
    const sym=CARD_TYPES[c.type]||'🟨';
    const nm=(!c.player||c.player==='__unbekannt__')?(c.team==='guest'?'Gast':'Heim'):c.player;
    const cls=c.team==='guest'?'ck-guest':'ck-home';
    const min=(c.minute!=null&&c.minute!=='')?` ${c.minute}'`:'';
    return `<span class="card-chip" title="Karte entfernen" onclick="removeCard('${r.id}',${i})">${sym} <span class="${cls}">${nm}</span>${min}</span>`;
  }).join('');

  // Elfmeterschießen: Eingabe direkt im Torzähler (✓ getroffen / ✗ verschossen), Schütze aus den Torschützen-Feldern
  const isPen=st==='penalties';
  const goalBtns=side=>isPen
    ?`<button class="btn-goal btn-plus"  title="Elfmeter getroffen" onclick="addPenShot('${r.id}','${side}',true)">✓</button>
      <button class="btn-goal btn-minus" title="Elfmeter verschossen / gehalten" onclick="addPenShot('${r.id}','${side}',false)">✗</button>`
    :`<button class="btn-goal btn-plus${r.pending_pen_miss?' btn-miss':''}"  onclick="addGoal('${r.id}','${side}')"   ${goalLock?'disabled':''} title="${r.pending_pen_miss?'Elfmeter verschossen eintragen':'Tor'}">${r.pending_pen_miss?'✗':'+'}</button>
      <button class="btn-goal btn-minus" onclick="removeGoal('${r.id}','${side}')" ${goalLock?'disabled':''}>−</button>`;
  const penChips=(r.pen_shots||[]).map((p,i)=>{
    const nm=(!p.player||p.player==='__unbekannt__')?(p.team==='guest'?'Gast':'Heim'):p.player;
    return `<span class="card-chip" title="Schuss entfernen" onclick="removePenShot('${r.id}',${i})"><span class="${p.team==='guest'?'ck-guest':'ck-home'}">${nm}</span> ${p.scored?'✅':'❌'}</span>`;
  }).join('');

  const div=document.createElement('div');div.className='mc';div.dataset.rid=r.id;
  div.innerHTML=`
    <div class="mc-head">
      <div class="mc-head-left">
        <select class="sel-status ${sc}" onchange="changeStatus('${r.id}',this.value)">${statusOpts}</select>
        <span style="display:flex;align-items:center;gap:6px;flex-wrap:nowrap">
          <select class="sel-field" onchange="changeField('${r.id}',this.value)">
            ${FIELD_OPTS.map(f=>`<option value="${f}" ${field===f?'selected':''}>${f}</option>`).join('')}
          </select>
          <input class="inp-kickoff" type="time" value="${kickoff}" placeholder="hh:mm" onchange="changeKickoff('${r.id}',this.value)" title="Anpfiff">
        </span>
      </div>
      <div class="mc-head-center" style="flex-direction:column;gap:2px;height:auto">
        ${halfLabel(r)?`<span class="half-badge">${halfLabel(r)}</span>`:''}
        <span class="timer ${sc}" data-rid="${r.id}">${elapsed>0?fmtTime(elapsed):''}</span>
      </div>
      <div class="mc-head-right">
        <button class="btn btn-blue btn-sm" onclick="addMatchRow()" title="Neue Begegnung">+</button>
        ${RP_TOKEN?'':`<button class="btn btn-del btn-sm" onclick="deleteResult('${r.id}')" title="Löschen">✕</button>`}
      </div>
    </div>
    ${st==='halftime'?`<div style="padding:8px 12px;border-bottom:1px solid var(--border);background:var(--orange-p)"><button class="btn btn-half btn-save" style="margin-top:0" onclick="startSecondHalf('${r.id}')">▶ 2. Halbzeit starten</button></div>`:''}
    ${st==='extra_time'&&!r.et_start_at?`<div style="padding:8px 12px;border-bottom:1px solid var(--border);background:var(--orange-p)"><button class="btn btn-half btn-save" style="margin-top:0" onclick="startExtraTime('${r.id}')">▶ Verlängerung starten</button></div>`:''}
    <!-- Heim-Mannschaft wählbar -->
    <div class="mc-team-row">
      <span style="font-size:.8rem;color:var(--gray);white-space:nowrap;width:76px">Heimteam:</span>
      <select class="sel-home-team" onchange="changeHomeTeam('${r.id}',this.value)" ${locked?'disabled':''}>
        <option value="">– Heimteam wählen –</option>${homeOpts}
      </select>
    </div>
    <!-- Gastmannschaft direkt darunter -->
    <div class="mc-guest">
      <span style="font-size:.8rem;color:var(--gray);white-space:nowrap;width:76px">Gegner:</span>
      <select class="sel-guest" onchange="changeGuest('${r.id}',this.value)" ${locked?'disabled':''}>
        <option value="">– Gastmannschaft –</option>${guestOpts}
      </select>
    </div>
    <!-- Torzähler -->
    <div class="mc-score">
      <div class="mc-score-home">
        <span class="team-label" title="${homeLbl}">${homeLbl}</span>
        <select class="sel-scorer sel-col" title="Torschütze ${homeLbl}" onchange="setScorerPending('${r.id}',this.value)" ${locked?'disabled':''}>${scorerOpts}</select>
        <div class="btn-row">
          ${goalBtns('home')}
        </div>
      </div>
      <div class="mc-score-mid" style="flex-direction:column">
        <div class="score-box">${r.score_home??0}&nbsp;:&nbsp;${r.score_guest??0}</div>
        ${ps?`<div class="pen-sm${isPen?' pen-live':''}">${isPen?'🥅 ':''}i.E. ${ps.h}:${ps.g}</div>`:''}
        ${ht?`<div class="ht-sm">HZ ${ht.h}:${ht.g}</div>`:''}
        <div class="pen-mode"${isPen?' style="display:none"':''}>
          <label class="pen-tg pen-tg-hit" title="11m getroffen – nächstes „+“ ist ein Elfmetertor"><input type="checkbox" ${r.pending_pen?'checked':''} onchange="setPenMode('${r.id}','hit',this.checked)" ${goalLock?'disabled':''}> 11m</label>
          <label class="pen-tg pen-tg-miss" title="11m verschossen – nächstes „✗“ trägt den Fehlschuss ein (kein Tor)"><input type="checkbox" ${r.pending_pen_miss?'checked':''} onchange="setPenMode('${r.id}','miss',this.checked)" ${goalLock?'disabled':''}> 11m</label>
        </div>
      </div>
      <div class="mc-score-guest">
        <span class="team-label" title="${guestLbl}">${guestLbl}</span>
        <select class="sel-scorer sel-col" title="Torschütze ${guestLbl}" onchange="setGuestScorerPending('${r.id}',this.value)" ${locked?'disabled':''}>${guestScorerOpts}</select>
        <div class="btn-row">
          ${goalBtns('guest')}
        </div>
      </div>
    </div>
    <!-- Karten -->
    <div class="mc-card-input">
      <span style="font-size:.8rem;color:var(--gray);white-space:nowrap;width:76px">🟨 Karte:</span>
      <select class="sel-card" onchange="setCardPending('${r.id}',this.value)" ${locked?'disabled':''}>${cardPlayerOpts}</select>
      <button class="btn-card" title="Gelbe Karte" onclick="addCard('${r.id}','yellow')" ${locked?'disabled':''}>🟨</button>
      <button class="btn-card" title="Gelb-Rote Karte" onclick="addCard('${r.id}','yellow_red')" ${locked?'disabled':''}>🟨🟥</button>
      <button class="btn-card" title="Rote Karte" onclick="addCard('${r.id}','red')" ${locked?'disabled':''}>🟥</button>
    </div>
    <div class="mc-scorers">${scorersList?`⚽ ${scorersList}`:''}</div>
    ${cardsList||penMissList?`<div class="mc-cards">${penMissList}${cardsList}</div>`:''}
    ${penChips?`<div class="mc-pens">🥅 ${penChips}</div>`:''}`;
  return div;
}

function setScorerPending(rid,val){const r=reportResults.find(x=>x.id===rid);if(r)r.pending_scorer=val;}
function setGuestScorerPending(rid,val){const r=reportResults.find(x=>x.id===rid);if(r)r.pending_guest_scorer=val;}

async function changeHomeTeam(rid,teamId){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  r.home_team_id=teamId;r.pending_scorer='';r.pending_guest_scorer='';
  await saveResult(r);renderCards();
}

async function addGoal(rid,side){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  if(r.pending_pen_miss)return addPenMiss(rid,side);   // Modus „11m verschossen“: kein Tor
  if(side==='home'&&!r.pending_scorer){
    const hid=r.home_team_id||(getDefaultHomeTeam(currentMd)||{}).id;
    if(isHomeTeam(hid)){alert('Bitte zuerst einen Torschützen auswählen!');return;}
    r.pending_scorer='Noname';   // Heimteam ist der Gegner → Torschütze unbekannt
  }
  if((r.status||'not_started')==='not_started'){
    r.status='running';r.started_at=Math.floor(Date.now()/1000);
    r.current_half=1;r.half1_end_at=null;r.second_half_start_at=null;
    if(!r.kickoff)r.kickoff=nowAsHHMM();
    r.ended_at=null;
  }
  r.scorers=r.scorers||[];r.scorer_scores=r.scorer_scores||[];r.scorer_info=r.scorer_info||[];
  // Parallel-Array an Altdaten angleichen, dann Phase + Elfmeter-Flag für dieses Tor
  while(r.scorer_info.length<r.scorers.length)r.scorer_info.push(null);
  const ch=r.current_half||1;
  const phase=(ch>=3&&!r.et_start_at)?2:Math.min(ch,3);
  // Torminute (Fußball-Zählweise: 0:00–0:59 = 1.) + Zeitstempel für spätere Korrekturen im Editor
  r.scorer_info.push({phase:phase,pen:!!r.pending_pen,minute:Math.floor(elapsedSec(r)/60)+1,at:Math.floor(Date.now()/1000)});
  r.pending_pen=false;
  if(side==='home'){
    r.score_home=(r.score_home||0)+1;
    r.scorers.push(r.pending_scorer);
    r.scorer_scores.push(`${r.score_home}:${r.score_guest??0}`);
    r.pending_scorer='';
  } else {
    r.score_guest=(r.score_guest||0)+1;
    // Gast-Torschütze: benannter Spieler oder anonym
    const gs=r.pending_guest_scorer||'__gast__';
    r.scorers.push(gs);
    r.scorer_scores.push(`${r.score_home??0}:${r.score_guest}`);
    r.pending_guest_scorer='';
    r.pending_scorer='';
  }
  await saveResult(r);renderCards();if(isLive(r))startLiveTimer(r.id);
}

async function removeGoal(rid,side){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  if(side==='home'){
    if((r.score_home||0)===0)return;
    r.score_home-=1;
    for(let i=(r.scorers||[]).length-1;i>=0;i--){if(!isGuestScorer(r.scorers[i])){r.scorers.splice(i,1);if(r.scorer_scores)r.scorer_scores.splice(i,1);if(r.scorer_info)r.scorer_info.splice(i,1);break;}}
  } else {
    if((r.score_guest||0)===0)return;
    r.score_guest-=1;
    for(let i=(r.scorers||[]).length-1;i>=0;i--){if(isGuestScorer(r.scorers[i])){r.scorers.splice(i,1);if(r.scorer_scores)r.scorer_scores.splice(i,1);if(r.scorer_info)r.scorer_info.splice(i,1);break;}}
  }
  await saveResult(r);renderCards();
}

function nowAsHHMM(){
  const d=new Date();
  return String(d.getHours()).padStart(2,'0')+':'+String(d.getMinutes()).padStart(2,'0');
}

async function changeStatus(rid,st){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  const prev=r.status||'not_started';const now=Math.floor(Date.now()/1000);
  if((st==='extra_time'||st==='penalties')&&!r.started_at){alert('Das Spiel wurde noch nicht angepfiffen.');renderCards();return;}
  r.status=st;
  if(st==='running'){
    if(prev==='not_started'||!r.started_at){
      // 1. Halbzeit anpfeifen
      r.started_at=now;r.current_half=1;r.half1_end_at=null;r.second_half_start_at=null;r.ended_at=null;
      r.half2_end_at=null;r.et_start_at=null;r.et_end_at=null;
      if(!r.kickoff)r.kickoff=nowAsHHMM();
    }else if(prev==='halftime'){
      // Aus der Halbzeit heraus: 2. Halbzeit starten
      r.current_half=2;r.second_half_start_at=now;r.ended_at=null;
    }else{
      // Fortsetzen der zuletzt gespielten Phase (z. B. versehentlich beendet)
      r.ended_at=null;
      if((r.current_half||1)>=3){r.status='extra_time';r.et_end_at=null;}
      else if(r.current_half===2){r.half2_end_at=null;}
    }
  }else if(st==='halftime'){
    // 1. Halbzeit anhalten – Uhr wird eingefroren
    if(r.current_half!==2){r.current_half=1;if(!r.half1_end_at)r.half1_end_at=now;}
    stopLiveTimer(rid);
  }else if(st==='extra_time'){
    // Reguläre Spielzeit einfrieren → Pause vor Verlängerung (Start per Button)
    if((r.current_half||1)<3){
      if(r.current_half===2&&!r.half2_end_at)r.half2_end_at=now;
      r.current_half=3;r.et_start_at=null;r.et_end_at=null;
    }else{
      r.et_end_at=null; // Verlängerung fortsetzen
    }
    r.ended_at=null;
    stopLiveTimer(rid);
  }else if(st==='penalties'){
    // Uhr der aktuellen Phase einfrieren
    if((r.current_half||1)>=3){if(r.et_start_at&&!r.et_end_at)r.et_end_at=now;}
    else if(r.current_half===2){if(!r.half2_end_at)r.half2_end_at=now;}
    else if(!r.ended_at){r.ended_at=now;}
    r.pen_shots=r.pen_shots||[];
    stopLiveTimer(rid);
  }else if(st==='finished'){
    if(!r.ended_at)r.ended_at=now;
    stopLiveTimer(rid);
  }else if(st==='not_started'){
    r.started_at=null;r.ended_at=null;r.half1_end_at=null;r.second_half_start_at=null;r.current_half=1;
    r.half2_end_at=null;r.et_start_at=null;r.et_end_at=null;
    stopLiveTimer(rid);
  }
  r.locked=(r.status==='finished');
  await saveResult(r);renderCards();if(isLive(r))startLiveTimer(r.id);
}
// Pause vor Verlängerung → Verlängerung läuft
async function startExtraTime(rid){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  r.status='extra_time';r.current_half=3;r.et_start_at=Math.floor(Date.now()/1000);r.et_end_at=null;r.ended_at=null;r.locked=false;
  await saveResult(r);renderCards();startLiveTimer(r.id);
}
// ── Elfmeter ──────────────────────────────────────────────────────────────
// Verschossener Elfmeter im Spiel: Schütze aus der Torschützen-Auswahl der jeweiligen Seite
async function addPenMiss(rid,side){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  if(!r.started_at){alert('Das Spiel wurde noch nicht angepfiffen.');return;}
  const raw=side==='home'?(r.pending_scorer||''):(r.pending_guest_scorer||'');
  let player=side==='guest'&&raw.startsWith('guest:')?raw.slice(6):raw;
  if(!player||player==='__gast__'||isReservedName(player))player='__unbekannt__';
  const ch=r.current_half||1;
  const phase=(ch>=3&&!r.et_start_at)?2:Math.min(ch,3);
  r.pen_missed=r.pen_missed||[];
  r.pen_missed.push({team:side,player:player,minute:Math.floor(elapsedSec(r)/60)+1,phase:phase,at:Math.floor(Date.now()/1000)});
  if(side==='home')r.pending_scorer='';else r.pending_guest_scorer='';
  r.pending_pen=false;r.pending_pen_miss=false;
  await saveResult(r);renderCards();
  toast('11m verschossen eingetragen');
}
async function removePenMiss(rid,idx){
  const r=reportResults.find(x=>x.id===rid);if(!r||!Array.isArray(r.pen_missed))return;
  if(idx<0||idx>=r.pen_missed.length)return;
  if(!confirm('Verschossenen Elfmeter entfernen?'))return;
  r.pen_missed.splice(idx,1);
  await saveResult(r);renderCards();if(isLive(r))startLiveTimer(r.id);
}
function setPenPending(rid,val){const r=reportResults.find(x=>x.id===rid);if(r)r.pending_pen=!!val;}
// 11m-Modus: „getroffen“ (nächstes Tor = Elfmetertor) oder „verschossen“ (nächstes ✗ = verschossener Elfmeter)
function setPenMode(rid,mode,on){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  r.pending_pen=(mode==='hit')&&on;
  r.pending_pen_miss=(mode==='miss')&&on;
  renderCards();
}
function setPenShooter(rid,team,val){const r=reportResults.find(x=>x.id===rid);if(!r)return;if(team==='guest')r.pending_pen_guest=val;else r.pending_pen_home=val;}
async function addPenShot(rid,team,scored){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  r.pen_shots=r.pen_shots||[];
  // Schütze aus dem Torschützen-Feld der Seite (Gast: „guest:Name“); Sonderwerte → unbekannt
  const raw=team==='guest'?(r.pending_guest_scorer||''):(r.pending_scorer||'');
  let pl=team==='guest'&&raw.startsWith('guest:')?raw.slice(6):raw;
  if(!pl||pl==='__gast__'||isReservedName(pl))pl='__unbekannt__';
  r.pen_shots.push({team:team,player:pl,scored:!!scored});
  if(team==='guest')r.pending_guest_scorer='';else r.pending_scorer='';
  await saveResult(r);renderCards();
}
async function removePenShot(rid,idx){
  const r=reportResults.find(x=>x.id===rid);if(!r||!Array.isArray(r.pen_shots))return;
  if(r.status==='finished'){alert('Spiel ist beendet – zum Korrigieren Status zurück auf „Elfmeterschießen" setzen.');return;}
  if(idx<0||idx>=r.pen_shots.length)return;
  if(!confirm('Elfmeter-Schuss entfernen?'))return;
  r.pen_shots.splice(idx,1);
  await saveResult(r);renderCards();
}
// Übergang Halbzeit → läuft (2. Halbzeit)
async function startSecondHalf(rid){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  r.status='running';r.current_half=2;r.second_half_start_at=Math.floor(Date.now()/1000);r.ended_at=null;r.locked=false;
  await saveResult(r);renderCards();startLiveTimer(r.id);
}
// ── Karten (Spielereignisse) ──────────────────────────────────────────────
function setCardPending(rid,val){const r=reportResults.find(x=>x.id===rid);if(r)r.pending_card=val;}
async function addCard(rid,type){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  if(!r.pending_card){alert('Bitte zuerst einen Spieler auswählen!');return;}
  const sep=r.pending_card.indexOf('|');
  const team=r.pending_card.slice(0,sep)==='guest'?'guest':'home';
  const name=r.pending_card.slice(sep+1);
  r.cards=r.cards||[];
  const minute=Math.floor(elapsedSec(r)/60);
  r.cards.push({player:name,type:type,team:team,minute:minute});
  r.pending_card='';
  await saveResult(r);renderCards();if(isLive(r))startLiveTimer(r.id);
}
async function removeCard(rid,idx){
  const r=reportResults.find(x=>x.id===rid);if(!r||!Array.isArray(r.cards))return;
  if(idx<0||idx>=r.cards.length)return;
  if(!confirm('Karte entfernen?'))return;
  r.cards.splice(idx,1);
  await saveResult(r);renderCards();if(isLive(r))startLiveTimer(r.id);
}
async function changeGuest(rid,teamId){
  const r=reportResults.find(x=>x.id===rid);if(!r)return;
  r.guest_team_id=teamId;r.pending_guest_scorer='';r.pending_pen_guest='';r.pending_card='';
  await saveResult(r);renderCards();if(isLive(r))startLiveTimer(r.id);
}
async function changeField(rid,field){const r=reportResults.find(x=>x.id===rid);if(!r)return;r.field=field;await saveResult(r);}
async function changeKickoff(rid,val){const r=reportResults.find(x=>x.id===rid);if(!r)return;r.kickoff=val;await saveResult(r);}
async function saveResult(r){
  rpSaving++;
  try{
    const body={...r};
    if(r.id)body._expected_rev=r.rev||0;   // Konfliktschutz: nur auf dem zuletzt bekannten Stand speichern
    const res=await api('save_result',body);
    if(!res||res.error){
      if(res&&res.conflict){
        toast('⚠ Auf einem anderen Gerät geändert – aktueller Stand geladen. Bitte Eingabe wiederholen.');
        await rpRefresh(true);
      }else if(res&&res.error&&!apiAuthWarned)alert(res.error);
      return false;
    }
    r.id=res.id||r.id;if(res.rev)r.rev=res.rev;if(res.updated_at)r.updated_at=res.updated_at;
    rpSig=rpSignature(reportResults);
    return true;
  }finally{rpSaving--;}
}
async function addMatchRow(){
  if(!currentMdId)return;
  const defHome=getDefaultHomeTeam(currentMd);
  // Gegner vorbelegen, wenn nach Abzug des Heimteams genau eine Mannschaft übrig bleibt
  const others=getMatchdayTeams().filter(t=>!defHome||t.id!==defHome.id);
  const defGuest=others.length===1?others[0].id:'';
  // Anstoßzeit: erste Begegnung des Spieltags übernimmt die Spieltagszeit (weitere bleiben leer → Anpfiff setzt sie)
  const defKick=(!reportResults.length&&currentMd&&/^\d{1,2}:\d{2}$/.test(currentMd.time||''))?currentMd.time.padStart(5,'0'):'';
  const nr={id:'',matchday_id:currentMdId,score_home:0,score_guest:0,guest_team_id:defGuest,
            home_team_id:defHome?defHome.id:null,
            status:'not_started',locked:false,scorers:[],scorer_scores:[],
            started_at:null,ended_at:null,field:'Feld 1',kickoff:defKick,
            current_half:1,half1_end_at:null,second_half_start_at:null,cards:[],
            half2_end_at:null,et_start_at:null,et_end_at:null,scorer_info:[],pen_shots:[],pen_missed:[],
            pending_guest_scorer:''};
  const res=await api('save_result',nr);
  if(!res||res.error){if(res&&res.error&&!apiAuthWarned)alert(res.error);return;}
  nr.id=res.id;nr.rev=res.rev||1;nr.updated_at=res.updated_at;reportResults.push(nr);rpSig=rpSignature(reportResults);renderCards();
}
async function deleteResult(rid){
  const r=reportResults.find(x=>x.id===rid);
  const mid=r?r.matchday_id:currentMdId;
  if(!confirm('Begegnung löschen?'))return;
  stopLiveTimer(rid);await api('delete_result',{id:rid,matchday_id:mid});
  reportResults=reportResults.filter(r=>r.id!==rid);renderCards();
}
async function saveAllResults(){for(const r of reportResults)await saveResult(r);toast();}
initReporting();

<?php }
