# Newsletter

A phpBB 3.3 extension for sending newsletters to forum members. Features opt-in subscriptions via the User Control Panel, throttled batch delivery, and comprehensive delivery logging.

- **Version:** 2.8.1
- **phpBB:** 3.3.17 (3.3.0 or later, not compatible with 4.x)
- **PHP:** 8.2.30 (7.1 or later)
- **License:** GPL-2.0-only
- **Languages:** Italian, English

---

## 🌟 Key Features

### 👤 For Users
* **UCP Subscription Management:** Adds a **Newsletter** tab in the User Control Panel where users can manage their subscription preferences.
* **Instant Confirmation:** Upon subscribing, users receive an immediate confirmation email detailing their subscription status with a one-click unsubscribe link.
* **One-Click Unsubscribe (No Login Required):** Unsubscribe links work seamlessly without requiring users to log in, reducing spam reports.
* **HMAC Security:** Unsubscribe links are secured with HMAC tokens to prevent unauthorized subscription alterations.
* **Global Sync:** Unsubscribing automatically turns off the "Receive emails from administrator" profile option to ensure user preferences are strictly respected across group emails.

### 🛠️ For Administrators

* **✍️ Newsletter Composer:** Write plain text or HTML newsletters with support for optional CSS stylesheets, highlighted forum topics, custom placeholders, live preview, and single-recipient test emails.
* **🎯 Flexible Targeting:** Select target groups with optional inclusion of UCP subscribers. Groups and opt-in subscribers are merged without duplicates, with built-in language filtering.
* **📧 Custom Headers:** Configure priority levels (1 to 5), importance, sensitivity/privacy settings, sender name/email, and custom Reply-To addresses.
* **⏱️ Throttled Delivery:** Customize batch sizes (10–100 emails) and interval delays (`hh:mm:ss`), with support for scheduled future dispatch. Includes pre-send confirmations with precise target counts, total batches, estimated runtime, and host limit warnings.
* **📝 Multiple Formats (Text, BBCode, HTML):** Support for plain text, full phpBB BBCode (processed via phpBB's native `text_formatter` supporting smilies, nested quotes, and custom tags at dispatch time), or full HTML layout with automatic relative-path to absolute-path conversion for embedded images.
* **🖼️ Header Image Support:** Upload custom HTML header banners (JPG, PNG, GIF) up to a defined height constraint (200–260px). Banners are centered, linked to the board home, and safely managed inside `images/newsletter/`. Option to bypass headers on individual sends while preserving stored banners.
* **📋 Subscriber Management:** View and search opt-in subscribers (by username or email), sort by sign-up timestamp or IP address, and manually remove individual subscribers. Users who opted out of admin emails are flagged.
* **🔔 Tailored Unsubscribe Notices:** When manually removing a user, administrators can opt to send a dedicated notice explaining the administrative action rather than sending standard self-unsubscribe copy.
* **🔍 Diagnostic SMTP Test Tool:** Built-in connection tester in ACP settings to test SMTP handshakes step-by-step (DNS resolution, socket connection, EHLO, auth mechanisms, and message delivery) with full raw server responses. Suggests optimal SSL/TLS prefixes when needed.
* **🏛️ Public Forum Archive:** Option to publish completed newsletter campaigns to a dedicated, paginated public forum archive accessible via navigation menu. Features a "View in browser" escape link and renders HTML campaigns safely inside sandboxed `iframe` containers to prevent style leaks. Campaigns can be published or withdrawn at any time.
* **📊 Comprehensive Delivery Logs:** Detailed real-time activity logs displaying sent, queued, and failed email metrics per campaign. Drill down to inspect individual recipients, retry counts, error messages, pause/resume/cancel workflows, failed queue retries, and manual batch execution.

---

## 🚀 Installation

1. Upload/copy the extension directory to `ext/salvo/newsletter/` (or your configured vendor namespace).
2. Go to **ACP → Customize → Manage extensions**.
3. Locate **Newsletter** and click **Enable**.
4. Navigate to **ACP → Newsletter → Settings** to configure sender settings, default footers, and batch parameters.

> **Note:** The administrative permission `a_newsletter` is automatically granted to the Administrators group upon installation. To assign permissions to other groups, visit **Permissions → Group Permissions → Miscellaneous**.

### 🗑️ Uninstallation

From **Manage extensions**:
* **Disable:** Disables the extension while keeping all settings and logs intact in the database.
* **Delete data:** Completely removes all tables, configurations, permissions, and ACP menu entries.

---

## ⚙️ Delivery Engine Mechanics

No messages bypass the queue system. Upon campaign confirmation, the delivery queue populates with individual recipient records. Each batch execution processes a chunk of records, updating statuses in real-time. If interrupted by a timeout or server reboot, dispatch resumes seamlessly from the exact point of interruption without duplicate sends.

Subsequent batches are triggered via **phpBB's scheduled task system (cron)**. For low-traffic forums, configuring a system-level cron job is strongly recommended:

```bash
*/5 * * * * php /path/to/your/forum/bin/phpbbcli.php cron:run
```

Without a system cron, batch execution relies on board visits, which may delay scheduled intervals.

### 💡 Why Throttled Delivery?

Most shared hosting providers enforce strict hourly email limits (e.g., 40–50 emails/hour). Exceeding these limits can cause server-side blocks affecting all board emails, including registration and password reset notifications. Small batch sizes with conservative intervals ensure smooth and compliant delivery.

### 🏷️ Template Placeholders

| Placeholder | Description |
|---|---|
| `{USERNAME}` | Recipient's board username |
| `{EMAIL}` | Recipient's email address |
| `{USER_ID}` | Recipient's user ID |
| `{BOARD_NAME}` | Forum name |
| `{BOARD_URL}` | Forum URL |
| `{DATE}` | Dispatch date |
| `{UNSUBSCRIBE_URL}` | Unique recipient unsubscribe link |
| `{UNSUBSCRIBE_LINK}` | Pre-formatted HTML unsubscribe link (HTML format only) |

---

## 🛠️ Technical Specifications

* **Single Body Architecture:** The campaign record stores message content once; individual queue records track recipient metrics only, optimizing database storage efficiency.
* **Inline CSS Conversion:** HTML emails pass through DOMDocument and XPath transformations to inline CSS rules directly into style attributes, guaranteeing consistent rendering across restrictive email clients like Gmail.
* **Custom MIME Construction:** Bypasses native phpBB MIME limits to support multipart/alternative HTML without conflicting `Content-Type` headers, while fully leveraging native `phpbb_mail` and `smtpmail` transport mechanisms.
* **Base64 Encoding:** Message bodies are base64 encoded prior to delivery, preserving HTML structure and prevents line-wrapping breaks caused by wordwrap functions.
* **Concurrency Locking:** Employs atomic `campaign_last_run` timestamp locking to prevent concurrent cron execution from generating duplicate batch dispatches.
* **HTML Sanitization:** Input HTML is cleaned prior to preview and dispatch, removing malicious scripts, iframes, event handlers, and `javascript:` URIs.

### 🗄️ Database Schema

| Table | Description |
|---|---|
| `phpbb_newsletter_campaigns` | Stores campaign data: body content, target settings, dispatch state, and counters. |
| `phpbb_newsletter_queue` | Stores queued messages per recipient: status, attempt counts, and error details. |
| `phpbb_newsletter_subs` | Stores voluntary UCP subscriptions: user ID, email, registration date, and IP. |

*Orphaned subscriber entries from deleted board accounts are cleaned up automatically during routine maintenance tasks.*

---

## ❓ Troubleshooting

* **Messages not sending:** Verify that board email functionality is enabled in **ACP → General → Email Settings**, ensure the extension is active, and confirm that phpBB cron tasks are executing. Check campaign status in the **Logs** tab.
* **All recipients fail:** Run the **SMTP Connection Test** in extension settings to inspect raw handshake responses. Common causes include misconfigured SMTP credentials or domain sender mismatch.
* **Emails landing in Spam:** Ensure the sender email matches your forum domain and verify that SPF and DKIM DNS records are properly configured.
* **Slow delivery rate:** Throttling is intentional to comply with server thresholds. Adjust batch size or interval frequency in settings if your host allows higher limits.
* **Campaign stuck in "In Progress":** Next batch is waiting for the next cron execution. Use **"Send next batch now"** in the detailed campaign logs to force immediate processing.

---

## 📄 License
Distributed under the **GNU General Public License v2.0 only**. See `license.txt` for details.

# Newsletter

Estensione per phpBB 3.3 che invia newsletter agli iscritti del forum, con iscrizione volontaria dal pannello utente, invio a lotti temporizzato e registro dettagliato dei recapiti.

- **Versione:** 2.8.1
- **phpBB:** 3.3.0 o successivo (non compatibile con la 4.x)
- **PHP:** 7.1 o successivo
- **Licenza:** GPL-2.0-only
- **Lingue:** italiano, inglese

---

## Che cosa fa

### Per l'utente

Nel pannello di controllo utente compare una voce **Newsletter** dove ciascuno decide se ricevere i messaggi. Chi si iscrive riceve subito una email di conferma che ricorda a quale forum si è iscritto e contiene un collegamento per cancellarsi.

Quel collegamento funziona **senza bisogno di accedere**: chi non vuole più ricevere i messaggi non ha voglia di ricordare la parola d'ordine, e ogni ostacolo in più si traduce in una segnalazione di posta indesiderata invece che in una cancellazione. Il collegamento è firmato con un HMAC, quindi nessuno può disiscrivere gli altri cambiando il numero nell'indirizzo.

La cancellazione toglie l'iscrizione e disattiva anche la casella «Ricevi email dall'amministratore» del profilo, così ha effetto anche per chi riceve la newsletter perché appartiene a un gruppo.

### Per l'amministratore

**Scrivi una newsletter** — oggetto e corpo in testo semplice o HTML, foglio di stile facoltativo, argomenti del forum da mettere in evidenza, segnaposto personalizzati, anteprima e invio di prova a un solo indirizzo.

**Destinatari** — selezione multipla dei gruppi, con l'aggiunta facoltativa di chi si è iscritto dal pannello utente. Gruppi e iscritti si sommano senza duplicati. Filtro per lingua del destinatario.

**Intestazioni** — priorità da 1 a 5, importanza, riservatezza, nome e indirizzo del mittente, indirizzo per le risposte.

**Consegna** — numero di email per lotto (da 10 a 100) e intervallo fra i lotti in hh:mm:ss, più la possibilità di programmare l'invio a una data e ora. Prima di partire viene mostrata una pagina di conferma con il numero esatto di destinatari, il numero di lotti, la durata stimata e l'ora prevista di fine. Oltre una soglia configurabile compare l'avviso sui limiti di invio degli host condivisi.

**Tre formati** — testo semplice, BBCode o HTML. Il BBCode è quello del forum: viene convertito dal `text_formatter` di phpBB, quindi riconosce tutti i tag definiti sul tuo forum, faccine comprese, e gestisce correttamente liste annidate e citazioni dentro citazioni. La conversione avviene al momento dell'invio, così una bozza scritta oggi tiene conto dei tag che ci saranno domani. I percorsi relativi delle faccine e delle immagini vengono resi assoluti, perché dentro un messaggio di posta un percorso relativo non ha nulla a cui riferirsi.

**Immagine di intestazione** — per le newsletter in HTML si può caricare un banner (JPG, PNG o GIF) dalla pagina di composizione. Viene mostrato in anteprima, si può sostituire o eliminare, e finisce in cima a ogni messaggio, centrato e collegato al forum. Le misure vengono controllate al caricamento: un banner è una striscia larga, e l'altezza ammessa (200-260 pixel come predefinito, modificabile) impedisce che una fotografia riempia l'intera prima schermata del messaggio spingendo il testo fuori dalla vista. Il file è conservato in `images/newsletter/`: una cartella dedicata e non `images/` direttamente, così la rimozione non può toccare risorse di phpBB o degli stili. Una casella permette di mandare un singolo messaggio senza intestazione conservando l'immagine per i prossimi.

**Iscritti** — l'elenco di chi si è iscritto dal pannello utente, con l'indirizzo usato, la data e l'ora dell'iscrizione per esteso e l'IP di provenienza. Si può cercare per nome o indirizzo, ordinare per le tre colonne principali e togliere un singolo utente dall'elenco. Chi si è iscritto ma ha poi rifiutato le email dell'amministratore nel proprio profilo viene segnalato, perché non riceverà i messaggi.

Quando togli qualcuno dall'elenco, la richiesta di conferma ti chiede se avvisarlo. Il messaggio è scritto apposta per quel caso — spiega che è stato un amministratore a rimuovere l'iscrizione e che ci si può iscrivere di nuovo da soli — e non riusa il testo di addio, che parla a chi ha appena premuto un pulsante. La casella parte spuntata o meno secondo l'impostazione, ma la scelta resta tua a ogni rimozione.

**Prova di invio** — nelle impostazioni, sezione Consegna della posta, un pulsante manda un messaggio di prova rifacendo la connessione al server SMTP un passo alla volta: risoluzione del nome, apertura della connessione con codice di errore del sistema, saluto del server, risposta a EHLO, metodi di autenticazione offerti, e infine l'invio vero. Ogni riga riporta la risposta testuale del server così com'è. Quando la porta è una di quelle cifrate e il nome del server non ha prefisso, la prova viene ripetuta con `ssl://` o `tls://` e, se quella riesce, il resoconto dice esattamente cosa scrivere nelle impostazioni email del forum.

**Archivio pubblico** — i numeri conclusi possono restare consultabili sul forum, con elenco paginato, pagina del singolo numero e voce nella barra di navigazione. In cima a ogni messaggio pubblico compare il collegamento «se non vedi correttamente questo messaggio, aprilo nel browser», che è la sola via d'uscita quando un lettore di posta rovina la formattazione. La visibilità è configurabile fra chiuso, solo registrati e chiunque; nell'archivio finiscono soltanto le campagne concluse e marcate come pubbliche, mai le bozze né gli invii in corso. I messaggi HTML vengono resi dentro un riquadro isolato con `sandbox`, così una marcatura arbitraria non può interferire con la pagina del forum.

La pubblicazione si decide prima dell'invio con una casella nel modulo di composizione, ma si può cambiare idea dopo: nel dettaglio della campagna conclusa ci sono i pulsanti «Pubblica nell'archivio» e «Ritira dall'archivio».

**Registro** — ogni newsletter con quante email sono state recapitate, quante sono in attesa e quante fallite. Aprendone una si vedono i destinatari uno per uno, il numero di tentativi e il motivo di ogni fallimento. Si può mettere in pausa, riprendere, annullare, forzare il lotto successivo, rimettere in coda i falliti, cancellare la singola voce o svuotare l'intero registro.

---

## Installazione

1. Copiare la cartella in `ext/salvocortesiano/newsletter/`
2. Pannello di amministrazione → Personalizza → Gestisci estensioni → **Abilita** su «Newsletter»
3. Newsletter → Impostazioni: controllare mittente, piè di pagina e valori predefiniti dei lotti

Il permesso `a_newsletter` viene assegnato automaticamente al gruppo Amministratori. Per darlo ad altri: Permessi → Permessi dei gruppi → categoria «Varie».

### Disinstallazione

Da Gestisci estensioni: **Disabilita** lascia tutto nel database, **Elimina i dati** rimuove tabelle, configurazione, permessi e voci di menu.

---

## Come funziona l'invio

Nessun messaggio parte fuori dalla coda, nemmeno il primo. Alla conferma la coda viene riempita con una riga per destinatario, poi ogni lotto ne preleva un blocco e aggiorna riga per riga l'esito. Se il processo viene interrotto — un timeout, un riavvio, un errore SMTP — l'invio riparte esattamente da dove si era fermato invece di ricominciare da capo e scrivere due volte agli stessi indirizzi.

I lotti successivi partono con l'**attività pianificata** di phpBB. Su un forum poco frequentato conviene impostare un cron di sistema:

```
*/5 * * * * php /percorso/del/forum/bin/phpbbcli.php cron:run
```

Senza, i lotti partono quando qualcuno visita il forum, e l'intervallo reale può risultare più lungo di quello impostato.

### Perché i lotti

Quasi tutti i fornitori di hosting condiviso rifiutano più di 40 o 50 messaggi all'ora. Superare quel limite non fa fallire solo la newsletter: fa bloccare la posta dell'intero forum, comprese le notifiche e le email di registrazione. Lotti piccoli con un intervallo generoso tengono la media sotto qualsiasi soglia.

### Segnaposto

| Segnaposto | Sostituito con |
|---|---|
| `{USERNAME}` | Nome del destinatario |
| `{EMAIL}` | Indirizzo del destinatario |
| `{USER_ID}` | Numero identificativo |
| `{BOARD_NAME}` | Nome del forum |
| `{BOARD_URL}` | Indirizzo del forum |
| `{DATE}` | Data dell'invio |
| `{UNSUBSCRIBE_URL}` | Indirizzo di cancellazione, valido per quel solo destinatario |
| `{UNSUBSCRIBE_LINK}` | Collegamento di cancellazione già pronto (solo in HTML) |

---

## Note tecniche

**Il corpo sta una volta sola.** La campagna conserva il messaggio; la coda tiene solo l'indirizzo e l'esito. Con quattromila iscritti e un messaggio da venti kilobyte, duplicare il corpo per ogni riga costerebbe ottanta megabyte di tabella per un solo invio.

**Il CSS viene reso inline.** Gmail e la maggior parte dei lettori di posta scartano il blocco `<style>`: senza questo passaggio un messaggio formattato con classi arriverebbe senza formattazione. Le regole vengono riscritte nell'attributo `style` di ogni elemento con DOMDocument e XPath.

**Il MIME è costruito a mano.** Il messenger di phpBB dichiara sempre `text/plain` e non c'è modo di inviare HTML senza due intestazioni `Content-Type` in conflitto. La consegna resta però affidata a `phpbb_mail` e `smtpmail`, così le impostazioni SMTP del pannello continuano a valere.

**Il corpo è codificato in base64.** `phpbb_mail` fa passare il messaggio da `wordwrap()` e `smtpmail` lo riscrive riga per riga: un corpo in chiaro con parole lunghe ne uscirebbe spezzato a metà di un tag. L'alfabeto base64 non contiene spazi né il punto, quindi nessuna delle due funzioni ha appigli.

**Ogni lotto è prenotato.** Se due processi arrivano insieme — il cron di sistema e una visita al forum — solo quello che riesce a spostare `campaign_last_run` prosegue. Senza, lo stesso lotto partirebbe due volte.

**L'HTML in ingresso viene ripulito.** Script, riquadri, gestori di evento e URL `javascript:` vengono tolti prima dell'anteprima e prima dell'invio.

### Tabelle

| Tabella | Contenuto |
|---|---|
| `phpbb_newsletter_campaigns` | Una riga per newsletter: corpo, destinatari, cadenza, stato, contatori |
| `phpbb_newsletter_queue` | Una riga per destinatario: stato, tentativi, errore |
| `phpbb_newsletter_subs` | Iscrizioni volontarie: utente, indirizzo, data, IP |

Le iscrizioni di profili cancellati vengono rimosse da sole con la pulizia periodica: phpBB non avvisa le estensioni quando un profilo sparisce, e senza quel passaggio il conteggio degli iscritti conterebbe persone che non esistono più.

---

## Risoluzione dei problemi

**Non parte niente.** Controllare che la posta sia attiva in Generali → Impostazioni email, che la newsletter sia attiva nelle sue impostazioni e che l'attività pianificata giri. La pagina Registro mostra a che punto è ogni campagna.

**Tutti i destinatari falliscono.** Provare l'invio di prova a un solo indirizzo: il motivo dell'errore compare per esteso. Di solito è la configurazione SMTP o un indirizzo mittente su un dominio diverso da quello del forum.

**I messaggi finiscono nella posta indesiderata.** Usare come mittente un indirizzo del dominio del forum e verificare i record SPF e DKIM del dominio.

**L'invio è lentissimo.** È il comportamento previsto: con lotti da 25 ogni dieci minuti servono più di ventisette ore per quattromila iscritti. La pagina di conferma lo dice prima di partire. Se l'host lo consente si possono alzare le email per lotto o accorciare l'intervallo.

**La campagna resta ferma su «in corso».** Il lotto successivo aspetta l'attività pianificata. Dal dettaglio del registro si può forzarne uno con «Invia subito il lotto successivo».

---

## Licenza

GNU General Public License v2.0 only. Vedere il file `license.txt`.
