# E-Piccion

E-Piccion è un'applicazione web sperimentale che rappresenta messaggi tramite sequenze di emoji. Il progetto è in evoluzione: il ramo `main` contiene la versione precedente, mentre `feature/base1024-v0.3` ospita il lavoro sperimentale per la codifica Base1024.

> **Stato attuale:** Base1024 è in fase di verifica. Non considerare questa versione pronta per la produzione finché non sono stati completati i test con l'alfabeto reale, l'interfaccia web e l'ambiente di hosting. Non pubblicare il ramo sperimentale direttamente sul sito in uso.

## Indice

- [Requisiti](#requisiti)
- [Struttura del progetto](#struttura-del-progetto)
- [Prima installazione](#prima-installazione)
- [Preparazione dell'alfabeto emoji](#preparazione-dellalfabeto-emoji)
- [Uso e funzionamento](#uso-e-funzionamento)
- [Formato legacy e Base1024](#formato-legacy-e-base1024)
- [Test prima della pubblicazione](#test-prima-della-pubblicazione)
- [Sicurezza e limiti noti](#sicurezza-e-limiti-noti)
- [Sviluppi previsti](#sviluppi-previsti)

## Requisiti

- Server web con PHP.
- **PHP 8.0 o superiore** per gli strumenti di generazione dell'alfabeto, che usano `str_contains()`.
- Estensioni PHP standard per stringhe, hash e Unicode/PCRE.
- Browser moderno con supporto Unicode ed emoji.

Il motore usa proprietà tipizzate e altre caratteristiche PHP moderne. Verificare la versione PHP effettivamente usata dal server web, che può essere diversa da quella della riga di comando.

## Struttura del progetto

- `engine/EmojiCrypt.php`: motore di codifica e decodifica.
- `engine/EmojiAlphabet.php`: alfabeto di 1.024 emoji generato; non modificarlo manualmente.
- `web/index.php`: interfaccia web.
- `web/install.php`: pagina di prima inizializzazione.
- `web/script.js` e `web/style.css`: interazione e stile dell'interfaccia.
- `tools/generateAlphabet.php`: genera l'alfabeto a partire dalla risorsa Unicode.
- `build/generateAlphabet.php`: copia dello strumento di generazione.
- `resources/emoji-test.txt`: elenco emoji Unicode usato per generare l'alfabeto.

## Prima installazione

1. Copiare il progetto sul server mantenendo la struttura delle cartelle.
2. Configurare il server web affinché la directory pubblica sia `web/` (oppure configurare il percorso equivalente usato dal proprio hosting). La cartella `engine/` deve restare raggiungibile dagli script PHP tramite il percorso relativo previsto dal progetto, ma non è necessario renderla una directory pubblica autonoma.
3. Verificare la versione PHP del server.
4. Controllare che `engine/EmojiAlphabet.php` esista e sia leggibile dall'utente PHP.
5. Aprire `web/install.php` nel browser. Se l'alfabeto è già presente, la pagina reindirizza a `index.php`; altrimenti mostra l'interfaccia di inizializzazione.

La pagina di installazione richiama la procedura JavaScript presente in `web/script.js`. Il comportamento dell'installazione deve essere verificato sul server: non assumere che un messaggio di successo confermi da solo che il file sia stato creato correttamente.

### Installazione in un sottopercorso

Se l'applicazione viene pubblicata in un percorso come `https://example.org/epiccion/web/`, verificare che gli URL relativi usati da HTML e JavaScript puntino al percorso corretto. Non cambiare la directory pubblica o i percorsi di inclusione senza verificare prima tutti i riferimenti.

## Preparazione dell'alfabeto emoji

Normalmente il repository contiene già `engine/EmojiAlphabet.php`: **in questo caso non rigenerarlo durante l'installazione**.

Se il file manca e si deve ricostruire l'ambiente:

1. Scaricare il file ufficiale Unicode `emoji-test.txt` da:
   https://www.unicode.org/Public/emoji/latest/emoji-test.txt
2. Copiarlo in `resources/emoji-test.txt`.
3. Dalla directory principale del repository, eseguire:
   ```sh
   php tools/generateAlphabet.php
   ```
4. Controllare che il comando segnali la creazione di un alfabeto di 1.024 emoji e che il file generato sia presente.

**Attenzione alla compatibilità:** la selezione delle emoji dipende dalla risorsa Unicode e dal seme di mescolamento del generatore. Rigenerare l'alfabeto con una versione diversa di `emoji-test.txt` può cambiare la corrispondenza fra emoji e indici e rendere illeggibili messaggi precedenti. Conservare una copia dell'alfabeto distribuito con ogni versione e non rigenerarlo su un sito in produzione senza un piano di migrazione.

## Uso e funzionamento

L'utente sceglie una password condivisa con il destinatario, inserisce il testo e usa la funzione di codifica. Il destinatario deve usare la stessa password e una copia identica dell'alfabeto per decodificare il messaggio.

Il motore opera sui byte della stringa, non sui caratteri Unicode astratti. Di conseguenza, una lettera accentata o un'emoji nel testo originale può occupare più byte ed essere rappresentata da più simboli in uscita.

## Formato legacy e Base1024

### Versione legacy v0.2a

Il formato precedente produce un'emoji per ogni byte del testo. La trasformazione usa un byte derivato da SHA-256 e seleziona uno dei primi 256 indici dell'alfabeto. Il formato non contiene un'intestazione esplicita di versione.

### Base1024 v0.3 — sperimentale

Il ramo `feature/base1024-v0.3` introduce una codifica a blocchi da 10 bit, così da poter usare tutti i 1.024 indici dell'alfabeto. Il messaggio comprende un marcatore emoji e una lunghezza codificata in emoji, seguiti dai simboli del contenuto. La decodifica riconosce il marcatore e mantiene un percorso per i messaggi legacy.

Questa è una modifica del formato: il testo cifrato Base1024 non è uguale a quello legacy. Per interpretare un messaggio, mittente e destinatario devono usare la stessa versione del motore, la stessa password e lo stesso alfabeto. La compatibilità legacy deve essere verificata con messaggi reali della versione precedente prima della pubblicazione.

## Test prima della pubblicazione

Prima di distribuire una nuova versione:

- provare codifica e decodifica con la stessa password;
- verificare frasi italiane con accenti, punteggiatura ed emoji;
- verificare spazi iniziali/finali, tabulazioni e nuove righe;
- provare messaggi vuoti e messaggi lunghi;
- controllare che input incompleti o emoji sconosciute producano un errore gestito;
- verificare la decodifica di messaggi creati dalla versione precedente;
- verificare il funzionamento nell'interfaccia web e sul server reale;
- controllare che non siano stati modificati accidentalmente l'alfabeto o i file della versione in produzione.

I test locali con un alfabeto sintetico sono utili per controllare l'algoritmo, ma non sostituiscono i test con `engine/EmojiAlphabet.php` e con il sito effettivo.

## Sicurezza e limiti noti

- Il sistema attuale con password è un prototipo di trasformazione reversibile, **non va considerato una soluzione crittografica moderna per proteggere informazioni sensibili**.
- La trasformazione è deterministica: a parità di testo e password si ottiene lo stesso risultato.
- Il formato attuale non fornisce autenticazione o integrità crittografica del messaggio e non segnala in modo affidabile una password errata.
- Una password condivisa deve essere comunicata tramite un canale sicuro. Non inserirla in URL, log o strumenti di debug.
- La modalità Base1024 migliora la densità della rappresentazione, ma **non aumenta di per sé la sicurezza crittografica**.
- Non usare la versione sperimentale per messaggi sensibili e non pubblicarla senza revisione e test.

## Sviluppi previsti

L'evoluzione pianificata è aggiungere una modalità OpenPGP che consenta di generare coppie di chiavi, importare chiavi esistenti, scegliere i destinatari e decifrare con la chiave privata. L'obiettivo è mantenere E-Piccion focalizzato sulla gestione delle chiavi e sulla conversione del contenuto cifrato in emoji.

La modalità OpenPGP non è ancora descritta come disponibile in questa versione: richiederà una libreria affidabile, test di interoperabilità e una gestione attenta delle chiavi private nel browser.
