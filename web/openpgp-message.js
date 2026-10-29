"use strict";
(() => {
 const $ = id => document.getElementById(id);
 const CONTACTS_KEY = "epiccion.openpgp.contacts.v1";
 let alphabet = null;
 let openpgpReady = false;
 function status(text, error=false) {
   const el=$("status"); el.className="notice"+(error?" error":""); el.textContent=text;
 }
 function contacts() {
   try {
     const x=JSON.parse(localStorage.getItem(CONTACTS_KEY)||"[]");
     return Array.isArray(x)?x.filter(c=>c&&typeof c.name==="string"&&typeof c.publicKey==="string"&&typeof c.fingerprint==="string"):[];
   } catch (_) { return []; }
 }
 function renderContacts() {
   const select=$("recipient"); select.replaceChildren();
   const empty=document.createElement("option"); empty.value=""; empty.textContent="Seleziona un destinatario"; select.appendChild(empty);
   contacts().forEach((c,i)=>{const o=document.createElement("option");o.value=String(i);o.textContent=c.name+" — "+c.fingerprint.slice(0,16);select.appendChild(o);});
 }
 function encodeEmoji(text) {
   const bytes=new TextEncoder().encode(text);
   const out=[alphabet[1023]];
   let len=bytes.length;
   for(let shift=50;shift>=0;shift-=10) out.push(alphabet[(Math.floor(len/2**shift))&1023]);
   for(let i=0;i<Math.ceil(bytes.length*8/10);i++){
     let value=0;
     for(let j=0;j<10;j++){
       const bit=i*10+j; value<<=1;
       if(bit<bytes.length*8) value|=(bytes[Math.floor(bit/8)]>>(7-bit%8))&1;
     }
     out.push(alphabet[value]);
   }
   return out.join(" ");
 }
 function decodeEmoji(text) {
   const symbols=text.trim().split(/\s+/u);
   if(!text.trim()) throw new Error("Incolla un messaggio emoji.");
   if(symbols.length<7 || symbols[0]!==alphabet[1023]) throw new Error("Il messaggio non ha il marcatore OpenPGP-emoji riconosciuto.");
   const index=new Map(alphabet.map((e,i)=>[e,i]));
   let len=0;
   for(let i=1;i<=6;i++){
     if(!index.has(symbols[i])) throw new Error("Il messaggio contiene un'emoji sconosciuta.");
     len=len*1024+index.get(symbols[i]);
   }
   if(!Number.isSafeInteger(len)||len<0||len>10000000) throw new Error("Lunghezza del messaggio non valida o troppo grande.");
   const payload=symbols.slice(7), expected=Math.ceil(len*8/10);
   if(payload.length!==expected) throw new Error("Il numero di emoji non corrisponde alla lunghezza dichiarata.");
   const bytes=new Uint8Array(len); let bitRead=0,byte=0,bits=0;
   for(const symbol of payload){
     if(!index.has(symbol)) throw new Error("Il messaggio contiene un'emoji sconosciuta.");
     const value=index.get(symbol);
     for(let j=9;j>=0&&bitRead<len*8;j--){
       byte=(byte<<1)|((value>>j)&1); bits++; bitRead++;
       if(bits===8){bytes[Math.floor(bitRead/8)-1]=byte;byte=0;bits=0;}
     }
   }
   if(bitRead!==len*8) throw new Error("Messaggio incompleto.");
   return new TextDecoder("utf-8",{fatal:true}).decode(bytes);
 }
 async function encryptMessage() {
   const list=contacts(), selected=$("recipient").value;
   if(selected==="") throw new Error("Scegli un destinatario.");
   const contact=list[Number(selected)];
   if(!contact) throw new Error("Il contatto selezionato non esiste più. Ricarica la pagina.");
   const text=$("plainMessage").value;
   if(!text) throw new Error("Scrivi prima un messaggio.");
   status("Cifratura OpenPGP in corso nel browser…");
   const publicKey=await openpgp.readKey({armoredKey:contact.publicKey});
   const message=await openpgp.createMessage({text});
   const armored=await openpgp.encrypt({message,encryptionKeys:publicKey,format:"armored"});
   $("cipherOutput").value=encodeEmoji(armored);
   status("Messaggio cifrato per "+contact.name+" e convertito in emoji. Invia il testo completo senza modificarlo.");
 }
 async function decryptMessage() {
   const encoded=$("cipherInput").value;
   const armored=decodeEmoji(encoded);
   if(!armored.includes("-----BEGIN PGP MESSAGE-----")) throw new Error("Il contenuto decodificato non sembra un messaggio OpenPGP.");
   const privateArmored=$("privateKeyInput").value.trim();
   const passphrase=$("privatePassphrase").value;
   if(!privateArmored) throw new Error("Incolla la tua chiave privata OpenPGP.");
   if(!passphrase) throw new Error("Inserisci la passphrase della chiave privata.");
   status("Verifica della chiave e decifratura in corso…");
   const encryptedPrivate=await openpgp.readPrivateKey({armoredKey:privateArmored});
   const privateKey=await openpgp.decryptKey({privateKey:encryptedPrivate,passphrase});
   const message=await openpgp.readMessage({armoredMessage:armored});
   const result=await openpgp.decrypt({message,decryptionKeys:privateKey,format:"utf8"});
   $("plainOutput").value=result.data;
   $("privatePassphrase").value="";
   status("Messaggio decifrato correttamente.");
 }
 function downloadText(text, filename) {
   const blob=new Blob([text],{type:"text/plain;charset=utf-8"});
   const url=URL.createObjectURL(blob); const a=document.createElement("a");
   a.href=url;a.download=filename;document.body.appendChild(a);a.click();a.remove();URL.revokeObjectURL(url);
 }
 document.addEventListener("DOMContentLoaded",async()=>{
   renderContacts();
   try {
     const response=await fetch("emoji-alphabet.php",{cache:"no-store"});
     if(!response.ok) throw new Error("Impossibile caricare l'alfabeto emoji.");
     alphabet=await response.json();
     if(!Array.isArray(alphabet)||alphabet.length!==1024||new Set(alphabet).size!==1024) throw new Error("L'alfabeto deve contenere 1.024 emoji distinte.");
     if(window.openpgp&&typeof window.openpgp.encrypt==="function") openpgpReady=true;
     else throw new Error("OpenPGP.js non è disponibile. Controlla la connessione e ricarica.");
     $("encryptMessage").disabled=false; $("decryptMessage").disabled=false;
     status("OpenPGP.js e alfabeto caricati. Pronto per cifrare e decifrare.");
   } catch(error){status(error.message||"Errore di inizializzazione.",true);}
   $("encryptMessage").addEventListener("click",async()=>{
     if(!openpgpReady)return;
     try{await encryptMessage();}catch(e){status(e.message||"Errore durante la cifratura.",true);}
   });
   $("decryptMessage").addEventListener("click",async()=>{
     if(!openpgpReady)return;
     try{await decryptMessage();}catch(e){$("privatePassphrase").value="";status(e.message||"Errore durante la decifratura.",true);}
   });
   $("importPrivateKey").addEventListener("click",()=>{$("privateKeyFile").click();});
   $("privateKeyFile").addEventListener("change",async()=>{
     const file=$("privateKeyFile").files&&$("privateKeyFile").files[0];
     if(!file)return;
     try{
       const content=await file.text();
       if(!content.includes("-----BEGIN PGP PRIVATE KEY BLOCK-----")) throw new Error("Il file selezionato non sembra contenere una chiave privata OpenPGP ASCII-armored.");
       $("privateKeyInput").value=content.trim();
       status("Chiave privata importata nel campo di decifratura dal file locale. Inserisci la passphrase se richiesta.");
     }catch(error){status(error.message||"Impossibile leggere il file della chiave privata.",true);}
     finally{$("privateKeyFile").value="";}
   });
   $("clearSensitive").addEventListener("click",()=>{
     $("privateKeyInput").value="";$("privatePassphrase").value="";$("cipherInput").value="";$("plainOutput").value="";
     status("Campi sensibili svuotati. La rubrica pubblica non è stata modificata.");
   });
   document.querySelectorAll("[data-copy]").forEach(btn=>btn.addEventListener("click",async()=>{
     const value=$(btn.dataset.copy).value;if(!value){status("Non c'è nulla da copiare.",true);return;}
     try{await navigator.clipboard.writeText(value);status("Testo copiato negli appunti.");}catch(_){status("Accesso agli appunti negato: copia manualmente il testo.",true);}
   }));
   document.querySelectorAll("[data-download]").forEach(btn=>btn.addEventListener("click",()=>{
     const value=$(btn.dataset.download).value;if(!value){status("Non c'è nulla da scaricare.",true);return;}
     downloadText(value,btn.dataset.filename);status("Download avviato.");
   }));
 });
})();
