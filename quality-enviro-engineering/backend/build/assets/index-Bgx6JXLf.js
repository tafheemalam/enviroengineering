const __vite__mapDeps=(i,m=__vite__mapDeps,d=(m.f||(m.f=["assets/About-DE6MsHel.js","assets/NotFound-BLV8mo9U.js","assets/react-Cb41LPxT.js","assets/Services-BzpeOeJg.js","assets/ServiceDetail-BFbgYmgv.js","assets/Contact-CSESOrHD.js","assets/Insights-yXtoAPJN.js","assets/InsightDetail-BMr0_Mq3.js","assets/LegalPage-O6h3OTkc.js"])))=>i.map(i=>d[i]);
import{r as h,a as fe,u as D,L as p,N as O,O as ve,H as ke,R as we,b as v,c as Ne,B as _e}from"./react-Cb41LPxT.js";(function(){const a=document.createElement("link").relList;if(a&&a.supports&&a.supports("modulepreload"))return;for(const l of document.querySelectorAll('link[rel="modulepreload"]'))i(l);new MutationObserver(l=>{for(const s of l)if(s.type==="childList")for(const c of s.addedNodes)c.tagName==="LINK"&&c.rel==="modulepreload"&&i(c)}).observe(document,{childList:!0,subtree:!0});function n(l){const s={};return l.integrity&&(s.integrity=l.integrity),l.referrerPolicy&&(s.referrerPolicy=l.referrerPolicy),l.crossOrigin==="use-credentials"?s.credentials="include":l.crossOrigin==="anonymous"?s.credentials="omit":s.credentials="same-origin",s}function i(l){if(l.ep)return;l.ep=!0;const s=n(l);fetch(l.href,s)}})();var le={exports:{}},I={};/**
 * @license React
 * react-jsx-runtime.production.min.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */var Me=h,ze=Symbol.for("react.element"),Se=Symbol.for("react.fragment"),Ee=Object.prototype.hasOwnProperty,Ce=Me.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,Ae={key:!0,ref:!0,__self:!0,__source:!0};function ce(t,a,n){var i,l={},s=null,c=null;n!==void 0&&(s=""+n),a.key!==void 0&&(s=""+a.key),a.ref!==void 0&&(c=a.ref);for(i in a)Ee.call(a,i)&&!Ae.hasOwnProperty(i)&&(l[i]=a[i]);if(t&&t.defaultProps)for(i in a=t.defaultProps,a)l[i]===void 0&&(l[i]=a[i]);return{$$typeof:ze,type:t,key:s,ref:c,props:l,_owner:Ce.current}}I.Fragment=Se;I.jsx=ce;I.jsxs=ce;le.exports=I;var e=le.exports,oe,K=fe;oe=K.createRoot,K.hydrateRoot;const Le="/api".replace(/\/$/,""),de=60*1e3,H=new Map;class X extends Error{constructor(a,n,i){super(a),this.status=n,this.errors=i||{}}}async function he(t,a={}){let n;try{n=await fetch(Le+t,{headers:{Accept:"application/json",...a.body?{"Content-Type":"application/json"}:{}},credentials:"same-origin",...a})}catch{throw new X("Unable to reach the server. Please check your connection and try again.",0)}let i=null;try{i=await n.json()}catch{}if(!n.ok||!i||i.success===!1)throw new X((i==null?void 0:i.message)||`Request failed (${n.status})`,n.status,i==null?void 0:i.errors);return i}async function _(t){const a=H.get(t);if(a&&Date.now()-a.t<de)return a.data;const n=await he(t);return H.set(t,{t:Date.now(),data:n.data}),n.data}function ee(t){const a=H.get(t);return a&&Date.now()-a.t<de?a.data:void 0}function qe(t,a){H.set(t,{t:Date.now(),data:a})}async function te(t,a){return he(t,{method:"POST",body:JSON.stringify(a)})}const me={bootstrap:()=>_("/bootstrap"),home:()=>_("/home"),page:t=>_(`/pages/${encodeURIComponent(t)}`),services:()=>_("/services"),service:t=>_(`/services/${encodeURIComponent(t)}`),posts:(t=1,a=9)=>_(`/posts?page=${t}&limit=${a}`),post:t=>_(`/posts/${encodeURIComponent(t)}`),contact:t=>te("/contact",t),quote:t=>te("/quote",t)},pe=h.createContext(null),ae={settings:{company_name:"Quality Enviro & Engineering Services",company_short:"QES",tagline:"Quality Solutions. Environmental Responsibility. Engineering Excellence.",show_insights:!0,show_testimonials:!1,show_whatsapp:!0},social:[],nav_services:[]};function Re({children:t}){const a=typeof window<"u"?window.__QES_BOOT__:null,[n,i]=h.useState(a||null),[l,s]=h.useState(null);h.useEffect(()=>{if(a){qe("/bootstrap",a);return}me.bootstrap().then(i).catch(s)},[]);const c={...n||ae,settings:{...ae.settings,...(n==null?void 0:n.settings)||{}},ready:!!n,error:l};return e.jsx(pe.Provider,{value:c,children:t})}function w(){const t=h.useContext(pe);if(!t)throw new Error("useSite must be used inside <SiteProvider>");return t}const He="modulepreload",$e=function(t){return"/build/"+t},se={},N=function(a,n,i){let l=Promise.resolve();if(n&&n.length>0){document.getElementsByTagName("link");const c=document.querySelector("meta[property=csp-nonce]"),m=(c==null?void 0:c.nonce)||(c==null?void 0:c.getAttribute("nonce"));l=Promise.allSettled(n.map(u=>{if(u=$e(u),u in se)return;se[u]=!0;const x=u.endsWith(".css"),y=x?'[rel="stylesheet"]':"";if(document.querySelector(`link[href="${u}"]${y}`))return;const o=document.createElement("link");if(o.rel=x?"stylesheet":He,x||(o.as="script"),o.crossOrigin="",o.href=u,m&&o.setAttribute("nonce",m),document.head.appendChild(o),x)return new Promise((j,b)=>{o.addEventListener("load",j),o.addEventListener("error",()=>b(new Error(`Unable to preload CSS for ${u}`)))})}))}function s(c){const m=new Event("vite:preloadError",{cancelable:!0});if(m.payload=c,window.dispatchEvent(m),!m.defaultPrevented)throw c}return l.then(c=>{for(const m of c||[])m.status==="rejected"&&s(m.reason);return a().catch(s)})};/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ie=t=>t.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase(),ue=(...t)=>t.filter((a,n,i)=>!!a&&a.trim()!==""&&i.indexOf(a)===n).join(" ").trim();/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var Te={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor",strokeWidth:2,strokeLinecap:"round",strokeLinejoin:"round"};/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Pe=h.forwardRef(({color:t="currentColor",size:a=24,strokeWidth:n=2,absoluteStrokeWidth:i,className:l="",children:s,iconNode:c,...m},u)=>h.createElement("svg",{ref:u,...Te,width:a,height:a,stroke:t,strokeWidth:i?Number(n)*24/Number(a):n,className:ue("lucide",l),...m},[...c.map(([x,y])=>h.createElement(x,y)),...Array.isArray(s)?s:[s]]));/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const r=(t,a)=>{const n=h.forwardRef(({className:i,...l},s)=>h.createElement(Pe,{ref:s,iconNode:a,className:ue(`lucide-${Ie(t)}`,i),...l}));return n.displayName=`${t}`,n};/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Oe=r("Activity",[["path",{d:"M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2",key:"169zse"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const k=r("ArrowRight",[["path",{d:"M5 12h14",key:"1ays0h"}],["path",{d:"m12 5 7 7-7 7",key:"xquz4c"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ve=r("ArrowUp",[["path",{d:"m5 12 7-7 7 7",key:"hav0vg"}],["path",{d:"M12 19V5",key:"x0mq9r"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const De=r("Award",[["path",{d:"m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526",key:"1yiouv"}],["circle",{cx:"12",cy:"8",r:"6",key:"1vp47v"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ue=r("BadgeCheck",[["path",{d:"M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z",key:"3c2336"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Be=r("Building2",[["path",{d:"M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z",key:"1b4qmf"}],["path",{d:"M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2",key:"i71pzd"}],["path",{d:"M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2",key:"10jefs"}],["path",{d:"M10 6h4",key:"1itunk"}],["path",{d:"M10 10h4",key:"tcdvrf"}],["path",{d:"M10 14h4",key:"kelpxr"}],["path",{d:"M10 18h4",key:"1ulq68"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Fe=r("Calendar",[["path",{d:"M8 2v4",key:"1cmpym"}],["path",{d:"M16 2v4",key:"4m81vk"}],["rect",{width:"18",height:"18",x:"3",y:"4",rx:"2",key:"1hopcy"}],["path",{d:"M3 10h18",key:"8toen8"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ze=r("ChevronDown",[["path",{d:"m6 9 6 6 6-6",key:"qrunsl"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const We=r("ChevronRight",[["path",{d:"m9 18 6-6-6-6",key:"mthhwq"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const z=r("CircleCheck",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Qe=r("ClipboardCheck",[["rect",{width:"8",height:"4",x:"8",y:"2",rx:"1",ry:"1",key:"tgr4d6"}],["path",{d:"M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2",key:"116196"}],["path",{d:"m9 14 2 2 4-4",key:"df797q"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ge=r("ClipboardList",[["rect",{width:"8",height:"4",x:"8",y:"2",rx:"1",ry:"1",key:"tgr4d6"}],["path",{d:"M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2",key:"116196"}],["path",{d:"M12 11h4",key:"1jrz19"}],["path",{d:"M12 16h4",key:"n85exb"}],["path",{d:"M8 11h.01",key:"1dfujw"}],["path",{d:"M8 16h.01",key:"18s6g9"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const U=r("Clock",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["polyline",{points:"12 6 12 12 16 14",key:"68esgv"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ye=r("Cog",[["path",{d:"M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z",key:"sobvz5"}],["path",{d:"M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z",key:"11i496"}],["path",{d:"M12 2v2",key:"tus03m"}],["path",{d:"M12 22v-2",key:"1osdcq"}],["path",{d:"m17 20.66-1-1.73",key:"eq3orb"}],["path",{d:"M11 10.27 7 3.34",key:"16pf9h"}],["path",{d:"m20.66 17-1.73-1",key:"sg0v6f"}],["path",{d:"m3.34 7 1.73 1",key:"1ulond"}],["path",{d:"M14 12h8",key:"4f43i9"}],["path",{d:"M2 12h2",key:"1t8f8n"}],["path",{d:"m20.66 7-1.73 1",key:"1ow05n"}],["path",{d:"m3.34 17 1.73-1",key:"nuk764"}],["path",{d:"m17 3.34-1 1.73",key:"2wel8s"}],["path",{d:"m11 13.73-4 6.93",key:"794ttg"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Je=r("Droplets",[["path",{d:"M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z",key:"1ptgy4"}],["path",{d:"M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97",key:"1sl1rz"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ke=r("Eye",[["path",{d:"M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0",key:"1nclc0"}],["circle",{cx:"12",cy:"12",r:"3",key:"1v7zrd"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Xe=r("Facebook",[["path",{d:"M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z",key:"1jg4f8"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const et=r("Factory",[["path",{d:"M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z",key:"159hny"}],["path",{d:"M17 18h1",key:"uldtlt"}],["path",{d:"M12 18h1",key:"s9uhes"}],["path",{d:"M7 18h1",key:"1neino"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const tt=r("FileText",[["path",{d:"M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z",key:"1rqfz7"}],["path",{d:"M14 2v4a2 2 0 0 0 2 2h4",key:"tnqrlb"}],["path",{d:"M10 9H8",key:"b1mrlr"}],["path",{d:"M16 13H8",key:"t4e002"}],["path",{d:"M16 17H8",key:"z1uh3a"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const at=r("Flame",[["path",{d:"M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z",key:"96xj49"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const st=r("FlaskConical",[["path",{d:"M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2",key:"pzvekw"}],["path",{d:"M8.5 2h7",key:"csnxdl"}],["path",{d:"M7 16h10",key:"wp8him"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const nt=r("Gauge",[["path",{d:"m12 14 4-4",key:"9kzdfg"}],["path",{d:"M3.34 19a10 10 0 1 1 17.32 0",key:"19p75a"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const B=r("Globe",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20",key:"13o1zl"}],["path",{d:"M2 12h20",key:"9i4pu4"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const it=r("Hammer",[["path",{d:"m15 12-8.373 8.373a1 1 0 1 1-3-3L12 9",key:"eefl8a"}],["path",{d:"m18 15 4-4",key:"16gjal"}],["path",{d:"m21.5 11.5-1.914-1.914A2 2 0 0 1 19 8.172V7l-2.26-2.26a6 6 0 0 0-4.202-1.756L9 2.96l.92.82A6.18 6.18 0 0 1 12 8.4V10l2 2h1.172a2 2 0 0 1 1.414.586L18.5 14.5",key:"b7pghm"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const rt=r("Handshake",[["path",{d:"m11 17 2 2a1 1 0 1 0 3-3",key:"efffak"}],["path",{d:"m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4",key:"9pr0kb"}],["path",{d:"m21 3 1 11h-2",key:"1tisrp"}],["path",{d:"M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3",key:"1uvwmv"}],["path",{d:"M3 4h8",key:"1ep09j"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const lt=r("HardHat",[["path",{d:"M10 10V5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5",key:"1p9q5i"}],["path",{d:"M14 6a6 6 0 0 1 6 6v3",key:"1hnv84"}],["path",{d:"M4 15v-3a6 6 0 0 1 6-6",key:"9ciidu"}],["rect",{x:"2",y:"15",width:"20",height:"4",rx:"1",key:"g3x8cw"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const ct=r("HeartHandshake",[["path",{d:"M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z",key:"c3ymky"}],["path",{d:"M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08c.82.82 2.13.85 3 .07l2.07-1.9a2.82 2.82 0 0 1 3.79 0l2.96 2.66",key:"4oyue0"}],["path",{d:"m18 15-2-2",key:"60u0ii"}],["path",{d:"m15 18-2-2",key:"6p76be"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const ot=r("House",[["path",{d:"M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8",key:"5wwlr5"}],["path",{d:"M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z",key:"1d0kgt"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const dt=r("Instagram",[["rect",{width:"20",height:"20",x:"2",y:"2",rx:"5",ry:"5",key:"2e1cvw"}],["path",{d:"M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z",key:"9exkf1"}],["line",{x1:"17.5",x2:"17.51",y1:"6.5",y2:"6.5",key:"r4j83e"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const ht=r("Leaf",[["path",{d:"M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z",key:"nnexq3"}],["path",{d:"M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12",key:"mt58a7"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const mt=r("Lightbulb",[["path",{d:"M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5",key:"1gvzjb"}],["path",{d:"M9 18h6",key:"x1upvd"}],["path",{d:"M10 22h4",key:"ceow96"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const pt=r("Linkedin",[["path",{d:"M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z",key:"c2jq9f"}],["rect",{width:"4",height:"12",x:"2",y:"9",key:"mk3on5"}],["circle",{cx:"4",cy:"4",r:"2",key:"bt5ra8"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const $=r("Mail",[["rect",{width:"20",height:"16",x:"2",y:"4",rx:"2",key:"18n3k1"}],["path",{d:"m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7",key:"1ocrg3"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const xe=r("MapPin",[["path",{d:"M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0",key:"1r0f0z"}],["circle",{cx:"12",cy:"10",r:"3",key:"ilqhr7"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const ut=r("MapPinned",[["path",{d:"M18 8c0 3.613-3.869 7.429-5.393 8.795a1 1 0 0 1-1.214 0C9.87 15.429 6 11.613 6 8a6 6 0 0 1 12 0",key:"11u0oz"}],["circle",{cx:"12",cy:"8",r:"2",key:"1822b1"}],["path",{d:"M8.714 14h-3.71a1 1 0 0 0-.948.683l-2.004 6A1 1 0 0 0 3 22h18a1 1 0 0 0 .948-1.316l-2-6a1 1 0 0 0-.949-.684h-3.712",key:"q8zwxj"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const xt=r("Menu",[["line",{x1:"4",x2:"20",y1:"12",y2:"12",key:"1e0a9i"}],["line",{x1:"4",x2:"20",y1:"6",y2:"6",key:"1owob3"}],["line",{x1:"4",x2:"20",y1:"18",y2:"18",key:"yk5zj1"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const F=r("MessageCircle",[["path",{d:"M7.9 20A9 9 0 1 0 4 16.1L2 22Z",key:"vv11sd"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const yt=r("MessagesSquare",[["path",{d:"M14 9a2 2 0 0 1-2 2H6l-4 4V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2z",key:"p1xzt8"}],["path",{d:"M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1",key:"1cx29u"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const jt=r("Microscope",[["path",{d:"M6 18h8",key:"1borvv"}],["path",{d:"M3 22h18",key:"8prr45"}],["path",{d:"M14 22a7 7 0 1 0 0-14h-1",key:"1jwaiy"}],["path",{d:"M9 14h2",key:"197e7h"}],["path",{d:"M9 12a2 2 0 0 1-2-2V6h6v4a2 2 0 0 1-2 2Z",key:"1bmzmy"}],["path",{d:"M12 6V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3",key:"1drr47"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const E=r("Phone",[["path",{d:"M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z",key:"foiqr5"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const gt=r("Quote",[["path",{d:"M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z",key:"rib7q0"}],["path",{d:"M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z",key:"1ymkrd"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const bt=r("Recycle",[["path",{d:"M7 19H4.815a1.83 1.83 0 0 1-1.57-.881 1.785 1.785 0 0 1-.004-1.784L7.196 9.5",key:"x6z5xu"}],["path",{d:"M11 19h8.203a1.83 1.83 0 0 0 1.556-.89 1.784 1.784 0 0 0 0-1.775l-1.226-2.12",key:"1x4zh5"}],["path",{d:"m14 16-3 3 3 3",key:"f6jyew"}],["path",{d:"M8.293 13.596 7.196 9.5 3.1 10.598",key:"wf1obh"}],["path",{d:"m9.344 5.811 1.093-1.892A1.83 1.83 0 0 1 11.985 3a1.784 1.784 0 0 1 1.546.888l3.943 6.843",key:"9tzpgr"}],["path",{d:"m13.378 9.633 4.096 1.098 1.097-4.096",key:"1oe83g"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const ft=r("Ruler",[["path",{d:"M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.41 2.41 0 0 1 0-3.4l2.6-2.6a2.41 2.41 0 0 1 3.4 0Z",key:"icamh8"}],["path",{d:"m14.5 12.5 2-2",key:"inckbg"}],["path",{d:"m11.5 9.5 2-2",key:"fmmyf7"}],["path",{d:"m8.5 6.5 2-2",key:"vc6u1g"}],["path",{d:"m17.5 15.5 2-2",key:"wo5hmg"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const vt=r("Scale",[["path",{d:"m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z",key:"7g6ntu"}],["path",{d:"m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z",key:"ijws7r"}],["path",{d:"M7 21h10",key:"1b0cd5"}],["path",{d:"M12 3v18",key:"108xh3"}],["path",{d:"M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2",key:"3gwbw2"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const kt=r("Search",[["circle",{cx:"11",cy:"11",r:"8",key:"4ej97u"}],["path",{d:"m21 21-4.3-4.3",key:"1qie3q"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const wt=r("Settings",[["path",{d:"M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z",key:"1qme2f"}],["circle",{cx:"12",cy:"12",r:"3",key:"1v7zrd"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Nt=r("ShieldAlert",[["path",{d:"M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z",key:"oel41y"}],["path",{d:"M12 8v4",key:"1got3b"}],["path",{d:"M12 16h.01",key:"1drbdi"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const _t=r("ShieldCheck",[["path",{d:"M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z",key:"oel41y"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Mt=r("Smile",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"M8 14s1.5 2 4 2 4-2 4-2",key:"1y1vjs"}],["line",{x1:"9",x2:"9.01",y1:"9",y2:"9",key:"yxxnd0"}],["line",{x1:"15",x2:"15.01",y1:"9",y2:"9",key:"1p4y9e"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const zt=r("Sprout",[["path",{d:"M7 20h10",key:"e6iznv"}],["path",{d:"M10 20c5.5-2.5.8-6.4 3-10",key:"161w41"}],["path",{d:"M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z",key:"9gtqwd"}],["path",{d:"M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z",key:"bkxnd2"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const ye=r("Star",[["path",{d:"M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z",key:"r04s7s"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const St=r("Target",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["circle",{cx:"12",cy:"12",r:"6",key:"1vlfrh"}],["circle",{cx:"12",cy:"12",r:"2",key:"1c9p78"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Et=r("Thermometer",[["path",{d:"M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z",key:"17jzev"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ct=r("ThumbsUp",[["path",{d:"M7 10v12",key:"1qc93n"}],["path",{d:"M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z",key:"emmmcr"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const At=r("TreePine",[["path",{d:"m17 14 3 3.3a1 1 0 0 1-.7 1.7H4.7a1 1 0 0 1-.7-1.7L7 14h-.3a1 1 0 0 1-.7-1.7L9 9h-.2A1 1 0 0 1 8 7.3L12 3l4 4.3a1 1 0 0 1-.8 1.7H15l3 3.3a1 1 0 0 1-.7 1.7H17Z",key:"cpyugq"}],["path",{d:"M12 22v-3",key:"kmzjlo"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Lt=r("TriangleAlert",[["path",{d:"m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3",key:"wmoenq"}],["path",{d:"M12 9v4",key:"juzpu7"}],["path",{d:"M12 17h.01",key:"p32p05"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const qt=r("Truck",[["path",{d:"M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2",key:"wrbu53"}],["path",{d:"M15 18H9",key:"1lyqi6"}],["path",{d:"M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14",key:"lysw3i"}],["circle",{cx:"17",cy:"18",r:"2",key:"332jqn"}],["circle",{cx:"7",cy:"18",r:"2",key:"19iecd"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const ne=r("Twitter",[["path",{d:"M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z",key:"pff0z6"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Rt=r("Users",[["path",{d:"M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2",key:"1yyitq"}],["circle",{cx:"9",cy:"7",r:"4",key:"nufk8"}],["path",{d:"M22 21v-2a4 4 0 0 0-3-3.87",key:"kshegd"}],["path",{d:"M16 3.13a4 4 0 0 1 0 7.75",key:"1da9ce"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ht=r("Waves",[["path",{d:"M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1",key:"knzxuh"}],["path",{d:"M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1",key:"2jd2cc"}],["path",{d:"M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1",key:"rd2r6e"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const $t=r("Wind",[["path",{d:"M12.8 19.6A2 2 0 1 0 14 16H2",key:"148xed"}],["path",{d:"M17.5 8a2.5 2.5 0 1 1 2 4H2",key:"1u4tom"}],["path",{d:"M9.8 4.4A2 2 0 1 1 11 8H2",key:"75valh"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const It=r("Wrench",[["path",{d:"M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z",key:"cbrjhi"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Tt=r("X",[["path",{d:"M18 6 6 18",key:"1bl5f8"}],["path",{d:"m6 6 12 12",key:"d8bk6v"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Pt=r("Youtube",[["path",{d:"M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17",key:"1q2vi4"}],["path",{d:"m10 15 5-3-5-3z",key:"1jp15x"}]]);/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Ot=r("Zap",[["path",{d:"M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z",key:"1xq2db"}]]);function T(t){return t.page_type==="detail"?`/services/${t.slug}`:`/contact?service=${encodeURIComponent(t.slug)}`}function Vt(t){if(!t)return"";const a=new Date(String(t).replace(" ","T")+"Z");return Number.isNaN(a.getTime())?"":a.toLocaleDateString(void 0,{year:"numeric",month:"long",day:"numeric"})}function C(t){return"tel:"+String(t||"").replace(/[^\d+]/g,"")}function je(t,a=""){const n=String(t||"").replace(/\D/g,"");return n?`https://wa.me/${n}${a?`?text=${encodeURIComponent(a)}`:""}`:null}function Dt(t){return typeof t=="string"&&t.startsWith("/")&&!t.startsWith("//")}const Ut={facebook:Xe,linkedin:pt,instagram:dt,youtube:Pt,x:ne,twitter:ne,whatsapp:F};function ge({size:t=18,className:a=""}){const{social:n}=w();return n!=null&&n.length?e.jsx("ul",{className:`social-links ${a}`,children:n.map(i=>{const l=Ut[i.platform]||B;return e.jsx("li",{children:e.jsx("a",{href:i.url,target:"_blank",rel:"noopener noreferrer","aria-label":`${i.label} (opens in a new tab)`,title:i.label,children:e.jsx(l,{size:t,"aria-hidden":"true"})})},i.platform)})}):null}function Bt(){const{settings:t,nav_services:a}=w(),[n,i]=h.useState(!1),[l,s]=h.useState(!1),[c,m]=h.useState(!1),u=D(),x=h.useRef(null);h.useEffect(()=>{const o=()=>m(window.scrollY>10);return o(),window.addEventListener("scroll",o,{passive:!0}),()=>window.removeEventListener("scroll",o)},[]),h.useEffect(()=>{i(!1),s(!1)},[u.pathname,u.search]),h.useEffect(()=>(document.body.style.overflow=n?"hidden":"",()=>document.body.style.overflow=""),[n]),h.useEffect(()=>{const o=b=>b.key==="Escape"&&(i(!1),s(!1)),j=b=>x.current&&!x.current.contains(b.target)&&s(!1);return document.addEventListener("keydown",o),document.addEventListener("click",j),()=>{document.removeEventListener("keydown",o),document.removeEventListener("click",j)}},[]);const y=[{to:"/",label:"Home",end:!0},{to:"/about",label:"About Us"},{to:"/services",label:"Services",dropdown:!0},...t.show_insights?[{to:"/insights",label:"Insights"}]:[],{to:"/contact",label:"Contact Us"}];return e.jsxs("header",{className:`site-header ${c?"is-scrolled":""}`,children:[e.jsx("div",{className:"topbar d-none d-lg-block",children:e.jsxs("div",{className:"container d-flex justify-content-between align-items-center",children:[e.jsxs("div",{className:"d-flex gap-4",children:[t.phone&&e.jsxs("a",{href:C(t.phone),children:[e.jsx(E,{size:14,"aria-hidden":"true"})," ",t.phone]}),t.company_email&&e.jsxs("a",{href:`mailto:${t.company_email}`,children:[e.jsx($,{size:14,"aria-hidden":"true"})," ",t.company_email]})]}),e.jsx(ge,{size:14,className:"topbar-social"})]})}),e.jsx("nav",{className:"mainnav","aria-label":"Main navigation",children:e.jsxs("div",{className:"container d-flex align-items-center justify-content-between",children:[e.jsxs(p,{to:"/",className:"brand","aria-label":`${t.company_name} – Home`,children:[t.logo_url&&e.jsx("img",{src:t.logo_url,alt:"",width:"52",height:"46"}),e.jsxs("span",{className:"brand-text",children:[e.jsx("strong",{children:t.company_short||"QES"}),e.jsx("small",{children:"Enviro & Engineering Services"})]})]}),e.jsx("ul",{className:"nav-links d-none d-lg-flex",children:y.map(o=>o.dropdown&&a.length?e.jsxs("li",{className:`has-dropdown ${l?"open":""}`,ref:x,children:[e.jsx(O,{to:o.to,className:"nav-link-item",children:o.label}),e.jsx("button",{type:"button",className:"dropdown-toggle-btn","aria-expanded":l,"aria-label":"Show services menu",onClick:j=>{j.stopPropagation(),s(b=>!b)},children:e.jsx(Ze,{size:16})}),e.jsxs("div",{className:"nav-dropdown",role:"menu",children:[a.map(j=>e.jsx(p,{to:T(j),role:"menuitem",children:j.title},j.id)),e.jsxs(p,{to:"/services",className:"all",role:"menuitem",children:["View all services ",e.jsx(k,{size:14})]})]})]},o.to):e.jsx("li",{children:e.jsx(O,{to:o.to,end:o.end,className:"nav-link-item",children:o.label})},o.to))}),e.jsxs("div",{className:"d-flex align-items-center gap-2",children:[e.jsx(p,{to:"/request-a-quote",className:"btn-brand d-none d-md-inline-flex",children:"Request a Quote"}),e.jsx("button",{type:"button",className:"menu-toggle d-lg-none","aria-label":n?"Close menu":"Open menu","aria-expanded":n,"aria-controls":"mobile-menu",onClick:()=>i(o=>!o),children:n?e.jsx(Tt,{size:26}):e.jsx(xt,{size:26})})]})]})}),e.jsxs("div",{id:"mobile-menu",className:`mobile-menu ${n?"open":""}`,hidden:!n,children:[e.jsx("ul",{children:y.map(o=>e.jsx("li",{children:e.jsx(O,{to:o.to,end:o.end,children:o.label})},o.to))}),e.jsx(p,{to:"/request-a-quote",className:"btn-brand w-100 justify-content-center",children:"Request a Quote"}),e.jsxs("div",{className:"mobile-contact",children:[t.phone&&e.jsxs("a",{href:C(t.phone),children:[e.jsx(E,{size:16})," ",t.phone]}),t.company_email&&e.jsxs("a",{href:`mailto:${t.company_email}`,children:[e.jsx($,{size:16})," ",t.company_email]})]})]})]})}function Ft(){const{settings:t,nav_services:a}=w(),n=new Date().getFullYear();return e.jsxs("footer",{className:"site-footer",children:[e.jsx("div",{className:"container",children:e.jsxs("div",{className:"row g-5",children:[e.jsxs("div",{className:"col-lg-4 col-md-6",children:[e.jsxs(p,{to:"/",className:"brand brand-footer","aria-label":"Home",children:[t.logo_url&&e.jsx("img",{src:t.logo_url,alt:"",width:"60",height:"53",loading:"lazy"}),e.jsxs("span",{className:"brand-text",children:[e.jsx("strong",{children:t.company_short||"QES"}),e.jsx("small",{children:"Enviro & Engineering Services"})]})]}),t.footer_description&&e.jsx("p",{className:"mt-3",children:t.footer_description}),e.jsx(ge,{size:18,className:"footer-social"})]}),e.jsxs("div",{className:"col-lg-2 col-md-6 col-6",children:[e.jsx("h2",{className:"footer-title",children:"Quick Links"}),e.jsxs("ul",{className:"footer-links",children:[e.jsx("li",{children:e.jsx(p,{to:"/",children:"Home"})}),e.jsx("li",{children:e.jsx(p,{to:"/about",children:"About Us"})}),e.jsx("li",{children:e.jsx(p,{to:"/services",children:"Services"})}),t.show_insights&&e.jsx("li",{children:e.jsx(p,{to:"/insights",children:"Insights"})}),e.jsx("li",{children:e.jsx(p,{to:"/request-a-quote",children:"Request a Quote"})}),e.jsx("li",{children:e.jsx(p,{to:"/contact",children:"Contact Us"})})]})]}),e.jsxs("div",{className:"col-lg-3 col-md-6 col-6",children:[e.jsx("h2",{className:"footer-title",children:"Our Services"}),e.jsx("ul",{className:"footer-links",children:a.slice(0,6).map(i=>e.jsx("li",{children:e.jsx(p,{to:T(i),children:i.title})},i.id))})]}),e.jsxs("div",{className:"col-lg-3 col-md-6",children:[e.jsx("h2",{className:"footer-title",children:"Contact Info"}),e.jsxs("ul",{className:"footer-contact",children:[t.address&&e.jsxs("li",{children:[e.jsx(xe,{size:18,"aria-hidden":"true"}),e.jsx("span",{children:t.address})]}),t.phone&&e.jsxs("li",{children:[e.jsx(E,{size:18,"aria-hidden":"true"}),e.jsx("a",{href:C(t.phone),children:t.phone})]}),t.phone_secondary&&e.jsxs("li",{children:[e.jsx(E,{size:18,"aria-hidden":"true"}),e.jsx("a",{href:C(t.phone_secondary),children:t.phone_secondary})]}),t.company_email&&e.jsxs("li",{children:[e.jsx($,{size:18,"aria-hidden":"true"}),e.jsx("a",{href:`mailto:${t.company_email}`,children:t.company_email})]}),t.business_hours&&e.jsxs("li",{children:[e.jsx(U,{size:18,"aria-hidden":"true"}),e.jsx("span",{children:t.business_hours})]})]})]})]})}),e.jsx("div",{className:"footer-bottom",children:e.jsxs("div",{className:"container d-flex flex-column flex-md-row justify-content-between gap-2",children:[e.jsxs("p",{className:"mb-0",children:["© ",n," ",t.copyright_text||t.company_name]}),e.jsxs("ul",{className:"footer-legal",children:[e.jsx("li",{children:e.jsx(p,{to:"/privacy-policy",children:"Privacy Policy"})}),e.jsx("li",{children:e.jsx(p,{to:"/terms-and-conditions",children:"Terms & Conditions"})})]})]})})]})}function Zt(){const{settings:t}=w(),a=je(t.whatsapp,`Hello ${t.company_name}, I would like to know more about your services.`);return!t.show_whatsapp||!a?null:e.jsx("a",{className:"whatsapp-float",href:a,target:"_blank",rel:"noopener noreferrer","aria-label":"Chat with us on WhatsApp (opens in a new tab)",children:e.jsx(F,{size:28,"aria-hidden":"true"})})}function Wt(){const[t,a]=h.useState(!1);return h.useEffect(()=>{const n=()=>a(window.scrollY>600);return window.addEventListener("scroll",n,{passive:!0}),()=>window.removeEventListener("scroll",n)},[]),e.jsx("button",{type:"button",className:`back-to-top ${t?"show":""}`,onClick:()=>window.scrollTo({top:0,behavior:"smooth"}),"aria-label":"Back to top",tabIndex:t?0:-1,children:e.jsx(Ve,{size:20,"aria-hidden":"true"})})}function Qt(){const{pathname:t,search:a}=D(),n=h.useRef(!0);return h.useEffect(()=>{if(window.scrollTo(0,0),n.current){n.current=!1;return}typeof window.gtag=="function"&&window.gtag("event","page_view",{page_path:t+a,page_location:window.location.href})},[t,a]),null}function Gt(){return e.jsxs(e.Fragment,{children:[e.jsx("a",{href:"#main",className:"skip-link",children:"Skip to main content"}),e.jsx(Qt,{}),e.jsx(Bt,{}),e.jsx("main",{id:"main",tabIndex:-1,children:e.jsx(ve,{})}),e.jsx(Ft,{}),e.jsx(Zt,{}),e.jsx(Wt,{})]})}function Yt(t,a=[],n){const i=n?ee(n):void 0,[l,s]=h.useState({data:i??null,loading:i===void 0,error:null}),[c,m]=h.useState(0);h.useEffect(()=>{let x=!0;const y=n?ee(n):void 0;return s({data:y??null,loading:y===void 0,error:null}),t().then(o=>x&&s({data:o,loading:!1,error:null})).catch(o=>x&&s({data:null,loading:!1,error:o})),()=>{x=!1}},[...a,c]);const u=h.useCallback(()=>m(x=>x+1),[]);return{...l,retry:u}}function Jt({seo:t={},title:a,description:n,image:i,type:l="website",robots:s,jsonLd:c=[]}){const{settings:m}=w(),{pathname:u}=D(),x=(m.site_url||window.location.origin).replace(/\/$/,""),y=a||t.title||m.company_name,o=n||t.description||m.tagline||"",j=t.canonical||x+(u==="/"?"/":u.replace(/\/$/,"")),b=i||t.og_image||(m.logo_url?x+m.logo_url:void 0);return e.jsxs(ke,{prioritizeSeoTags:!0,children:[e.jsx("title",{children:y}),e.jsx("meta",{name:"description",content:o}),t.keywords&&e.jsx("meta",{name:"keywords",content:t.keywords}),e.jsx("meta",{name:"robots",content:s||t.robots||"index, follow"}),e.jsx("link",{rel:"canonical",href:j}),e.jsx("meta",{property:"og:type",content:l}),e.jsx("meta",{property:"og:site_name",content:m.company_name}),e.jsx("meta",{property:"og:title",content:t.og_title||y}),e.jsx("meta",{property:"og:description",content:t.og_description||o}),e.jsx("meta",{property:"og:url",content:j}),b&&e.jsx("meta",{property:"og:image",content:b}),e.jsx("meta",{name:"twitter:card",content:"summary_large_image"}),e.jsx("meta",{name:"twitter:title",content:t.og_title||y}),e.jsx("meta",{name:"twitter:description",content:t.og_description||o}),b&&e.jsx("meta",{name:"twitter:image",content:b}),c.map((L,q)=>e.jsx("script",{type:"application/ld+json",children:JSON.stringify(L)},q))]})}function xa(t,a){return{"@context":"https://schema.org","@type":"BreadcrumbList",itemListElement:a.map((n,i)=>({"@type":"ListItem",position:i+1,name:n.label,item:t.replace(/\/$/,"")+n.to}))}}const Kt={Leaf:ht,Activity:Oe,ShieldCheck:_t,ClipboardCheck:Qe,HardHat:lt,Wrench:It,Recycle:bt,Droplets:Je,Wind:$t,Target:St,Eye:Ke,Award:De,BadgeCheck:Ue,ThumbsUp:Ct,Smile:Mt,Cog:Ye,MessagesSquare:yt,Search:kt,ClipboardList:Ge,Scale:vt,Users:Rt,Lightbulb:mt,FileText:tt,Clock:U,Factory:et,Gauge:nt,Building2:Be,Globe:B,Sprout:zt,FlaskConical:st,Zap:Ot,Settings:wt,Handshake:rt,TreePine:At,Waves:Ht,Hammer:it,Ruler:ft,HeartHandshake:ct,Microscope:jt,Truck:qt,CheckCircle2:z,Star:ye,Flame:at,ShieldAlert:Nt,Thermometer:Et};function A(t,a=z){return Kt[t]||a}function f({as:t="div",delay:a=0,className:n="",children:i,...l}){const s=h.useRef(null),[c,m]=h.useState(!1);return h.useEffect(()=>{const u=s.current;if(!u||!("IntersectionObserver"in window)){m(!0);return}const x=new IntersectionObserver(([y])=>{y.isIntersecting&&(m(!0),x.disconnect())},{rootMargin:"0px 0px -60px 0px",threshold:.05});return x.observe(u),()=>x.disconnect()},[]),e.jsx(t,{ref:s,className:`reveal ${c?"is-visible":""} ${n}`,style:{transitionDelay:`${a}ms`},...l,children:i})}function M({eyebrow:t,title:a,subtitle:n,center:i=!0,as:l="h2",light:s=!1}){return!a&&!t?null:e.jsxs(f,{className:`section-heading ${i?"text-center mx-auto":""} ${s?"light":""}`,children:[t&&e.jsx("span",{className:"eyebrow",children:t}),a&&e.jsx(l,{children:a}),n&&e.jsx("p",{className:"lead-sub",children:n})]})}function S({href:t,className:a="btn-brand",children:n,arrow:i=!1}){if(!t)return null;const l=e.jsxs(e.Fragment,{children:[n,i&&e.jsx(k,{size:18,"aria-hidden":"true"})]});return Dt(t)?e.jsx(p,{to:t,className:a,children:l}):e.jsx("a",{href:t,className:a,target:"_blank",rel:"noopener noreferrer",children:l})}function R({name:t,size:a=26,className:n=""}){const i=A(t);return e.jsx("span",{className:`icon-badge ${n}`,"aria-hidden":"true",children:e.jsx(i,{size:a})})}function be({label:t="Loading…",full:a=!1}){return e.jsxs("div",{className:`loader-wrap ${a?"full":""}`,role:"status","aria-live":"polite",children:[e.jsx("span",{className:"spinner","aria-hidden":"true"}),e.jsx("span",{className:"visually-hidden",children:t})]})}function Xt({error:t,onRetry:a}){return e.jsxs("div",{className:"error-state container",role:"alert",children:[e.jsx(Lt,{size:40,"aria-hidden":"true"}),e.jsx("h2",{children:"Something went wrong"}),e.jsx("p",{children:(t==null?void 0:t.message)||"We could not load this content."}),a&&e.jsx("button",{type:"button",className:"btn-brand",onClick:a,children:"Try again"})]})}function ya({eyebrow:t,title:a,subtitle:n,crumbs:i=[]}){return e.jsx("section",{className:"page-header",children:e.jsxs("div",{className:"container",children:[i.length>0&&e.jsx("nav",{"aria-label":"Breadcrumb",children:e.jsxs("ol",{className:"breadcrumbs",children:[e.jsx("li",{children:e.jsxs(p,{to:"/",children:[e.jsx(ot,{size:14,"aria-hidden":"true"})," ",e.jsx("span",{children:"Home"})]})}),i.map((l,s)=>e.jsxs("li",{children:[e.jsx(We,{size:14,"aria-hidden":"true"}),s===i.length-1?e.jsx("span",{"aria-current":"page",children:l.label}):e.jsx(p,{to:l.to,children:l.label})]},l.to))]})}),t&&e.jsx("span",{className:"eyebrow light",children:t}),e.jsx("h1",{children:a}),n&&e.jsx("p",{className:"lead-sub",children:n})]})})}function V({items:t=[],className:a=""}){return t.map((n,i)=>e.jsx("p",{className:a,children:n},i))}const ea={title:"Looking for Reliable Environmental & Engineering Solutions?",subtitle:"Tell us about your project and our team will get back to you with the right approach.",button_text:"Request a Quote",button_link:"/request-a-quote",button2_text:"Contact Us",button2_link:"/contact"};function ta({section:t}){const a={...ea,...t||{}};return e.jsx("section",{className:"cta-section","aria-labelledby":"cta-title",children:e.jsx("div",{className:"container",children:e.jsxs(f,{className:"cta-inner",children:[e.jsxs("div",{children:[e.jsx("h2",{id:"cta-title",children:a.title}),a.subtitle&&e.jsx("p",{children:a.subtitle})]}),e.jsxs("div",{className:"cta-actions",children:[a.button_text&&e.jsx(S,{href:a.button_link,className:"btn-accent",arrow:!0,children:a.button_text}),a.button2_text&&e.jsx(S,{href:a.button2_link,className:"btn-outline-light-brand",children:a.button2_text})]})]})})})}function aa({service:t,delay:a=0,headingLevel:n="h3"}){const i=n,l=A(t.icon),s=t.page_type==="detail",c=T(t);return e.jsxs(f,{as:"article",className:"service-card",delay:a,children:[e.jsx(p,{to:c,className:"service-card-media",tabIndex:-1,"aria-hidden":"true",children:t.image?e.jsx("img",{src:t.image,alt:t.image_alt,loading:"lazy",width:"800",height:"520"}):e.jsx("span",{className:"media-fallback"})}),e.jsxs("div",{className:"service-card-body",children:[e.jsx("span",{className:"service-card-icon","aria-hidden":"true",children:e.jsx(l,{size:24})}),e.jsx(i,{className:"service-card-title",children:e.jsx(p,{to:c,children:t.title})}),e.jsx("p",{children:t.short_description}),e.jsxs(p,{to:c,className:"link-arrow","aria-label":`${s?"Read more about":"Enquire about"} ${t.title}`,children:[s?"Read More":"Contact Us"," ",e.jsx(k,{size:16,"aria-hidden":"true"})]})]})]})}function sa({post:t,delay:a=0}){return e.jsxs(f,{as:"article",className:"post-card",delay:a,children:[e.jsx(p,{to:`/insights/${t.slug}`,className:"post-card-media",tabIndex:-1,"aria-hidden":"true",children:t.image?e.jsx("img",{src:t.image,alt:t.image_alt,loading:"lazy",width:"800",height:"520"}):e.jsx("span",{className:"media-fallback"})}),e.jsxs("div",{className:"post-card-body",children:[t.published_at&&e.jsxs("span",{className:"post-meta",children:[e.jsx(Fe,{size:14,"aria-hidden":"true"})," ",e.jsx("time",{dateTime:t.published_at,children:Vt(t.published_at)})]}),e.jsx("h3",{children:e.jsx(p,{to:`/insights/${t.slug}`,children:t.title})}),e.jsx("p",{children:t.excerpt}),e.jsxs(p,{to:`/insights/${t.slug}`,className:"link-arrow","aria-label":`Read article: ${t.title}`,children:["Read Article ",e.jsx(k,{size:16,"aria-hidden":"true"})]})]})]})}function na({t,delay:a=0}){return e.jsxs(f,{as:"figure",className:"testimonial-card",delay:a,children:[e.jsx(gt,{size:32,className:"quote-icon","aria-hidden":"true"}),e.jsx("div",{className:"stars","aria-label":`Rated ${t.rating} out of 5`,children:Array.from({length:5}).map((n,i)=>e.jsx(ye,{size:16,"aria-hidden":"true",fill:i<t.rating?"currentColor":"none"},i))}),e.jsx("blockquote",{children:t.content}),e.jsxs("figcaption",{children:[e.jsx("strong",{children:t.client_name}),e.jsx("span",{children:[t.client_role,t.company].filter(Boolean).join(", ")})]})]})}function ia({compact:t=!1}){const{settings:a}=w(),n=je(a.whatsapp),i=[a.phone&&{icon:E,label:"Phone",value:a.phone,href:C(a.phone)},a.phone_secondary&&{icon:E,label:"Alternate Phone",value:a.phone_secondary,href:C(a.phone_secondary)},a.company_email&&{icon:$,label:"Email",value:a.company_email,href:`mailto:${a.company_email}`},n&&{icon:F,label:"WhatsApp",value:a.whatsapp,href:n,external:!0},a.address&&{icon:xe,label:"Office Address",value:a.address},a.service_area&&{icon:B,label:"Service Area",value:a.service_area},!t&&a.business_hours&&{icon:U,label:"Business Hours",value:a.business_hours}].filter(Boolean);return e.jsx("ul",{className:"contact-info-list",children:i.map(({icon:l,label:s,value:c,href:m,external:u})=>e.jsxs("li",{children:[e.jsx("span",{className:"ci-icon","aria-hidden":"true",children:e.jsx(l,{size:20})}),e.jsxs("span",{children:[e.jsx("small",{children:s}),m?e.jsx("a",{href:m,...u?{target:"_blank",rel:"noopener noreferrer"}:{},children:c}):e.jsx("span",{className:"ci-value",children:c})]})]},s))})}function ja(){const{settings:t}=w();return t.map_embed_url?e.jsx("div",{className:"map-embed",children:e.jsx("iframe",{src:t.map_embed_url,title:`${t.company_name} office location on Google Maps`,loading:"lazy",referrerPolicy:"no-referrer-when-downgrade",allowFullScreen:!0})}):e.jsxs("div",{className:"map-placeholder",children:[e.jsx(ut,{size:40,"aria-hidden":"true"}),e.jsx("p",{children:"Our office location map will appear here."}),t.address&&e.jsx("p",{className:"small",children:t.address})]})}function ra(){var u,x,y,o,j,b,L,q,Z,W,Q,G,Y,J;const{settings:t}=w(),{data:a,loading:n,error:i,retry:l}=Yt(()=>me.home(),[],"/home");if(n)return e.jsx(be,{full:!0});if(i)return e.jsx(Xt,{error:i,onRetry:l});const s=a.sections||{},c=s.hero||{},m=a.services||[];return e.jsxs(e.Fragment,{children:[e.jsx(Jt,{seo:a.seo}),e.jsxs("section",{className:"hero","aria-labelledby":"hero-title",children:[c.image&&e.jsx("img",{className:"hero-bg",src:c.image,alt:"",fetchpriority:"high"}),e.jsx("div",{className:"hero-overlay","aria-hidden":"true"}),e.jsx("div",{className:"container position-relative",children:e.jsxs("div",{className:"row align-items-center g-5",children:[e.jsxs("div",{className:"col-lg-7",children:[c.eyebrow&&e.jsx("span",{className:"eyebrow light hero-eyebrow",children:c.eyebrow}),e.jsx("h1",{id:"hero-title",children:c.title||t.company_name}),c.subtitle&&e.jsx("p",{className:"hero-tagline",children:c.subtitle}),e.jsx(V,{items:c.paragraphs,className:"hero-text"}),e.jsxs("div",{className:"hero-actions",children:[e.jsx(S,{href:c.button_link||"/services",className:"btn-brand btn-lg-brand",arrow:!0,children:c.button_text||"Explore Our Services"}),e.jsx(S,{href:c.button2_link||"/contact",className:"btn-outline-light-brand btn-lg-brand",children:c.button2_text||"Contact Us"})]})]}),e.jsx("div",{className:"col-lg-5 d-none d-lg-block",children:e.jsxs("div",{className:"hero-panel",children:[e.jsxs("div",{className:"hero-panel-head",children:[t.logo_url&&e.jsx("img",{src:t.logo_url,alt:`${t.company_name} logo`,width:"88",height:"78"}),e.jsxs("div",{children:[e.jsx("strong",{children:"What we deliver"}),e.jsx("span",{children:"Environmental & engineering expertise"})]})]}),e.jsx("ul",{children:m.slice(0,5).map(d=>{const g=A(d.icon);return e.jsx("li",{children:e.jsxs(p,{to:T(d),children:[e.jsx("span",{className:"hp-icon",children:e.jsx(g,{size:18,"aria-hidden":"true"})}),d.title,e.jsx(k,{size:16,className:"ms-auto","aria-hidden":"true"})]})},d.id)})})]})})]})})]}),s.about&&e.jsx("section",{className:"section","aria-labelledby":"about-title",children:e.jsx("div",{className:"container",children:e.jsxs("div",{className:"row align-items-center g-5",children:[e.jsx(f,{className:"col-lg-6",children:e.jsxs("div",{className:"about-media",children:[s.about.image&&e.jsx("img",{src:s.about.image,alt:s.about.image_alt,loading:"lazy",width:"800",height:"520"}),e.jsxs("div",{className:"about-badge",children:[e.jsx(R,{name:"Leaf",size:22}),e.jsx("span",{children:t.tagline})]})]})}),e.jsxs(f,{className:"col-lg-6",delay:120,children:[s.about.eyebrow&&e.jsx("span",{className:"eyebrow",children:s.about.eyebrow}),e.jsx("h2",{id:"about-title",children:s.about.title}),e.jsx(V,{items:s.about.paragraphs}),e.jsx("div",{className:"row g-3 my-2",children:s.about.items.map(d=>e.jsx("div",{className:"col-sm-6",children:e.jsxs("div",{className:"mv-card",children:[e.jsx(R,{name:d.icon,size:22}),e.jsx("h3",{children:d.title}),e.jsx("p",{children:d.text})]})},d.title))}),e.jsx(S,{href:s.about.button_link||"/about",className:"btn-outline-brand",arrow:!0,children:s.about.button_text||"Read More"})]})]})})}),e.jsx("section",{className:"section bg-soft","aria-labelledby":"services-title",children:e.jsxs("div",{className:"container",children:[e.jsx(M,{eyebrow:((u=s.services)==null?void 0:u.eyebrow)||"What We Do",title:e.jsx("span",{id:"services-title",children:((x=s.services)==null?void 0:x.title)||"Our Services"}),subtitle:(y=s.services)==null?void 0:y.subtitle}),e.jsx("div",{className:"row g-4",children:m.map((d,g)=>e.jsx("div",{className:"col-md-6 col-lg-4 d-flex",children:e.jsx(aa,{service:d,delay:g%3*90})},d.id))}),e.jsx("div",{className:"text-center mt-5",children:e.jsxs(p,{to:"/services",className:"btn-brand",children:["View All Services ",e.jsx(k,{size:18,"aria-hidden":"true"})]})})]})}),s.spotlight&&e.jsx("section",{className:"section section-navy","aria-labelledby":"spotlight-title",children:e.jsx("div",{className:"container position-relative",children:e.jsxs("div",{className:"row align-items-center g-5",children:[e.jsxs(f,{className:"col-lg-6",children:[s.spotlight.eyebrow&&e.jsx("span",{className:"eyebrow light",children:s.spotlight.eyebrow}),e.jsx("h2",{id:"spotlight-title",className:"text-white",children:s.spotlight.title}),e.jsx(V,{items:s.spotlight.paragraphs,className:"spotlight-text"}),s.spotlight.items.length>0&&e.jsx("ul",{className:"spotlight-list",children:s.spotlight.items.map(d=>e.jsxs("li",{children:[e.jsx(z,{size:18,"aria-hidden":"true"})," ",d.title]},d.title))}),s.spotlight.button_text&&e.jsx(S,{href:s.spotlight.button_link||"/services",className:"btn-brand",arrow:!0,children:s.spotlight.button_text})]}),s.spotlight.image&&e.jsx(f,{className:"col-lg-6",delay:120,children:e.jsx("div",{className:"spotlight-media",children:e.jsx("img",{src:s.spotlight.image,alt:s.spotlight.image_alt,loading:"lazy",width:"800",height:"560"})})})]})})}),s.calibration&&s.calibration.items.length>0&&e.jsx("section",{className:"section bg-soft","aria-labelledby":"calibration-title",children:e.jsxs("div",{className:"container",children:[e.jsx(M,{eyebrow:s.calibration.eyebrow,title:e.jsx("span",{id:"calibration-title",children:s.calibration.title}),subtitle:s.calibration.subtitle}),e.jsx("ul",{className:"capability-grid",children:s.calibration.items.map((d,g)=>{const P=A(d.icon,z);return e.jsxs(f,{as:"li",delay:g%4*60,children:[e.jsx(P,{size:18,"aria-hidden":"true"}),e.jsx("span",{children:d.title})]},d.title)})}),s.calibration.button_text&&e.jsx("div",{className:"text-center mt-4",children:e.jsx(S,{href:s.calibration.button_link||"/contact",className:"btn-outline-brand",arrow:!0,children:s.calibration.button_text})})]})}),s.why&&e.jsx("section",{className:"section","aria-labelledby":"why-title",children:e.jsxs("div",{className:"container",children:[e.jsx(M,{eyebrow:s.why.eyebrow,title:e.jsx("span",{id:"why-title",children:s.why.title}),subtitle:s.why.subtitle}),e.jsx("div",{className:"row g-4",children:s.why.items.map((d,g)=>e.jsx("div",{className:"col-md-6 col-lg-4",children:e.jsxs(f,{className:"feature-card",delay:g%3*90,children:[e.jsx(R,{name:d.icon}),e.jsx("h3",{children:d.title}),e.jsx("p",{children:d.text})]})},d.title))})]})}),s.process&&e.jsx("section",{className:"section section-navy","aria-labelledby":"process-title",children:e.jsxs("div",{className:"container",children:[e.jsx(M,{light:!0,eyebrow:s.process.eyebrow,title:e.jsx("span",{id:"process-title",children:s.process.title}),subtitle:s.process.subtitle}),e.jsx("ol",{className:"process-steps",children:s.process.items.map((d,g)=>{const P=A(d.icon);return e.jsxs(f,{as:"li",delay:g*90,children:[e.jsx("span",{className:"step-num","aria-hidden":"true",children:String(g+1).padStart(2,"0")}),e.jsx("span",{className:"step-icon","aria-hidden":"true",children:e.jsx(P,{size:26})}),e.jsx("h3",{children:d.title}),e.jsx("p",{children:d.text})]},d.title)})})]})}),s.credentials&&s.credentials.items.length>0&&e.jsx("section",{className:"section bg-soft","aria-labelledby":"credentials-title",children:e.jsxs("div",{className:"container",children:[e.jsx(M,{eyebrow:s.credentials.eyebrow,title:e.jsx("span",{id:"credentials-title",children:s.credentials.title}),subtitle:s.credentials.subtitle}),e.jsx("div",{className:"row g-4 justify-content-center",children:s.credentials.items.map((d,g)=>e.jsx("div",{className:"col-md-6 col-lg-5",children:e.jsxs(f,{className:"credential-card",delay:g*90,children:[e.jsx(R,{name:d.icon||"Award",size:24}),e.jsxs("div",{children:[e.jsx("h3",{children:d.title}),d.text&&e.jsx("p",{children:d.text})]})]})},d.title))})]})}),((o=a.testimonials)==null?void 0:o.length)>0&&e.jsx("section",{className:"section bg-soft","aria-labelledby":"testimonials-title",children:e.jsxs("div",{className:"container",children:[e.jsx(M,{eyebrow:(j=s.testimonials)==null?void 0:j.eyebrow,title:e.jsx("span",{id:"testimonials-title",children:((b=s.testimonials)==null?void 0:b.title)||"What Our Clients Say"}),subtitle:(L=s.testimonials)==null?void 0:L.subtitle}),e.jsx("div",{className:"row g-4",children:a.testimonials.map((d,g)=>e.jsx("div",{className:"col-md-6 col-lg-4 d-flex",children:e.jsx(na,{t:d,delay:g%3*90})},d.id))})]})}),e.jsx(ta,{section:s.cta}),((q=a.posts)==null?void 0:q.length)>0&&e.jsx("section",{className:"section","aria-labelledby":"insights-title",children:e.jsxs("div",{className:"container",children:[e.jsx(M,{eyebrow:(Z=s.insights)==null?void 0:Z.eyebrow,title:e.jsx("span",{id:"insights-title",children:((W=s.insights)==null?void 0:W.title)||"Latest Insights"}),subtitle:(Q=s.insights)==null?void 0:Q.subtitle}),e.jsx("div",{className:"row g-4",children:a.posts.map((d,g)=>e.jsx("div",{className:"col-md-6 col-lg-4 d-flex",children:e.jsx(sa,{post:d,delay:g*90})},d.id))}),e.jsx("div",{className:"text-center mt-5",children:e.jsxs(p,{to:"/insights",className:"btn-outline-brand",children:["View All Insights ",e.jsx(k,{size:18,"aria-hidden":"true"})]})})]})}),e.jsx("section",{className:"section bg-soft","aria-labelledby":"contact-preview-title",children:e.jsx("div",{className:"container",children:e.jsxs("div",{className:"row g-5 align-items-center",children:[e.jsxs(f,{className:"col-lg-5",children:[((G=s.contact)==null?void 0:G.eyebrow)&&e.jsx("span",{className:"eyebrow",children:s.contact.eyebrow}),e.jsx("h2",{id:"contact-preview-title",children:((Y=s.contact)==null?void 0:Y.title)||"Get In Touch"}),((J=s.contact)==null?void 0:J.subtitle)&&e.jsx("p",{className:"lead-sub",children:s.contact.subtitle}),e.jsxs("ul",{className:"check-list",children:[e.jsxs("li",{children:[e.jsx(z,{size:18,"aria-hidden":"true"})," Prompt response to every inquiry"]}),e.jsxs("li",{children:[e.jsx(z,{size:18,"aria-hidden":"true"})," Clear scope and transparent proposals"]}),e.jsxs("li",{children:[e.jsx(z,{size:18,"aria-hidden":"true"})," Solutions tailored to your site"]})]}),e.jsxs(p,{to:"/contact",className:"btn-brand mt-2",children:["Contact Us ",e.jsx(k,{size:18,"aria-hidden":"true"})]})]}),e.jsx(f,{className:"col-lg-7",delay:120,children:e.jsx("div",{className:"contact-preview-card",children:e.jsx(ia,{compact:!0})})})]})})})]})}const la=h.lazy(()=>N(()=>import("./About-DE6MsHel.js"),__vite__mapDeps([0,1,2]))),ca=h.lazy(()=>N(()=>import("./Services-BzpeOeJg.js"),__vite__mapDeps([3,2]))),oa=h.lazy(()=>N(()=>import("./ServiceDetail-BFbgYmgv.js"),__vite__mapDeps([4,2,1]))),ie=h.lazy(()=>N(()=>import("./Contact-CSESOrHD.js"),__vite__mapDeps([5,2]))),da=h.lazy(()=>N(()=>import("./Insights-yXtoAPJN.js"),__vite__mapDeps([6,2,1]))),ha=h.lazy(()=>N(()=>import("./InsightDetail-BMr0_Mq3.js"),__vite__mapDeps([7,2,1]))),re=h.lazy(()=>N(()=>import("./LegalPage-O6h3OTkc.js"),__vite__mapDeps([8,1,2]))),ma=h.lazy(()=>N(()=>import("./NotFound-BLV8mo9U.js"),__vite__mapDeps([1,2])));function pa(){return e.jsx(h.Suspense,{fallback:e.jsx(be,{full:!0}),children:e.jsx(we,{children:e.jsxs(v,{element:e.jsx(Gt,{}),children:[e.jsx(v,{index:!0,element:e.jsx(ra,{})}),e.jsx(v,{path:"about",element:e.jsx(la,{})}),e.jsx(v,{path:"services",element:e.jsx(ca,{})}),e.jsx(v,{path:"services/:slug",element:e.jsx(oa,{})}),e.jsx(v,{path:"contact",element:e.jsx(ie,{mode:"contact"})}),e.jsx(v,{path:"request-a-quote",element:e.jsx(ie,{mode:"quote"},"quote")}),e.jsx(v,{path:"insights",element:e.jsx(da,{})}),e.jsx(v,{path:"insights/:slug",element:e.jsx(ha,{})}),e.jsx(v,{path:"privacy-policy",element:e.jsx(re,{pageKey:"privacy",path:"/privacy-policy"})}),e.jsx(v,{path:"terms-and-conditions",element:e.jsx(re,{pageKey:"terms",path:"/terms-and-conditions"},"terms")}),e.jsx(v,{path:"*",element:e.jsx(ma,{})})]})})})}document.querySelectorAll('head meta[name="description"], head meta[name="keywords"], head meta[name="robots"], head link[rel="canonical"], head meta[property^="og:"], head meta[name^="twitter:"], head script[type="application/ld+json"]:not([data-keep])').forEach(t=>t.remove());oe(document.getElementById("root")).render(e.jsx(h.StrictMode,{children:e.jsx(Ne,{children:e.jsx(_e,{future:{v7_startTransition:!0,v7_relativeSplatPath:!0},children:e.jsx(Re,{children:e.jsx(pa,{})})})})}));export{k as A,ta as C,Xt as E,ot as H,R as I,be as L,$ as M,ya as P,f as R,Jt as S,Lt as T,Yt as a,xa as b,V as c,M as d,me as e,aa as f,z as g,E as h,r as i,e as j,ia as k,ge as l,ja as m,sa as n,Fe as o,Vt as p,T as s,C as t,w as u};
