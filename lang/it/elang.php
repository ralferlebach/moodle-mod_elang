<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Italian strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * Like the Swedish pack, this one has no 1.3.5 translation to follow: the AMOS
 * export covered fr, es_mx, el and ar only. Terminology therefore follows
 * Moodle's own Italian pack — partecipante, tentativo, attività, sottotitoli —
 * rather than an earlier translation of this plugin, and is the part most worth
 * a second pair of eyes.
 *
 * Written after the 2.0.0-RC1 terminology review, so it already follows its
 * conclusions: the activity is a video dictation rather than a generic language
 * exercise, time labels carry no (ms) because the field shows mm:ss.SSS, the
 * content language is chosen from a list rather than typed, counts avoid
 * pseudo-plurals, and wording about who may do what names the permission rather
 * than a role a site may not have.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Lingue del contenuto consentite';
$string['allowedlanguages_desc'] = 'Le lingue del contenuto offerte quando si crea o si modifica un dettato video. Non selezionarne nessuna per offrire l\'elenco completo. Un\'attività mantiene la lingua salvata anche se in seguito la rimuovi da qui.';
$string['allowtranscriptdownload'] = 'Download della trascrizione per gli studenti';
$string['allowtranscriptdownload_help'] = 'Quando è attivo, gli studenti possono scaricare la scheda di lavoro della trascrizione, con ogni spazio nascosto, come PDF, Word, OpenDocument o testo.

È disattivato per impostazione predefinita. Il personale docente autorizzato può scaricare la trascrizione sempre, qualunque sia questa impostazione.';
$string['allowtranscriptdownload_label'] = 'Gli studenti possono scaricare la scheda di lavoro';
$string['completiondetail_completionfinishattempt'] = 'Completare un tentativo';
$string['completionfinishattempt'] = 'Il partecipante deve completare un tentativo';
$string['cuepausemode'] = 'Pause alla fine dei sottotitoli';
$string['cuepausemode_auto'] = 'Automatico';
$string['cuepausemode_help'] = 'Se il media si ferma alla fine di un sottotitolo.

* Automatico — la riproduzione prosegue e si ferma alla fine di un sottotitolo solo finché si sta lavorando su quel sottotitolo, cioè dopo aver fatto clic su di esso o su uno dei suoi spazi, oppure quando il focus della tastiera si trova in uno di essi.
* Fermarsi a ogni sottotitolo senza risposta — la riproduzione si ferma alla fine di ogni sottotitolo che ha ancora uno spazio vuoto e attende di essere ripresa.
* Non fermarsi mai — la riproduzione prosegue fino alla fine del media.

Nessuna delle prime due si ferma su un sottotitolo i cui spazi sono tutti compilati: quello è lavoro finito, e fermarsi lì chiederebbe una pressione di tasto senza effetto. Significa anche che un secondo passaggio nell\'esercizio si ferma solo dove manca ancora qualcosa.';
$string['cuepausemode_nostop'] = 'Non fermarsi mai';
$string['cuepausemode_stop'] = 'Fermarsi a ogni sottotitolo senza risposta';
$string['editcontent'] = 'Modifica il contenuto';
$string['editor_addcue'] = 'Aggiungi segmento';
$string['editor_addgap'] = 'Crea uno spazio dalla selezione';
$string['editor_addhint'] = 'Aggiungi suggerimento';
$string['editor_addvariant'] = 'Aggiungi variante';
$string['editor_advanced'] = 'Impostazioni avanzate';
$string['editor_algoexact'] = 'Corrispondenza esatta';
$string['editor_algorithm'] = 'Confronto delle risposte';
$string['editor_algowordrecognized'] = 'Accetta risposte simili';
$string['editor_answers'] = 'Varianti accettate';
$string['editor_autosaved'] = 'Tutte le modifiche sono state salvate.';
$string['editor_autosaveerror'] = 'Salvataggio automatico non riuscito — usa Salva per riprovare.';
$string['editor_captureend'] = 'Imposta la fine dalla riproduzione';
$string['editor_capturestart'] = 'Imposta l\'inizio dalla riproduzione';
$string['editor_cueactions'] = 'Azioni sul segmento';
$string['editor_cuecount'] = 'Segmenti: {$a}';
$string['editor_cuenotsaved'] = 'Non salvato';
$string['editor_currentmedia'] = 'Media attuale:';
$string['editor_deletecue'] = 'Elimina segmento';
$string['editor_deletegap'] = 'Elimina spazio';
$string['editor_emptytranscript'] = '(ancora nessun testo)';
$string['editor_endtime'] = 'Fine';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Spazi: {$a}';
$string['editor_gaprange'] = 'Posizione dello spazio (caratteri)';
$string['editor_gotomedia'] = 'Vai a Media';
$string['editor_heading'] = 'Modifica sottotitoli e spazi';
$string['editor_hints'] = 'Suggerimenti';
$string['editor_hinttext'] = 'Testo del suggerimento';
$string['editor_hinttype'] = 'Tipo';
$string['editor_hinttype_firstletter'] = 'Prima lettera';
$string['editor_hinttype_partial'] = 'Parziale';
$string['editor_hinttype_solution'] = 'Soluzione';
$string['editor_hinttype_text'] = 'Testo libero';
$string['editor_hinttype_translation'] = 'Traduzione';
$string['editor_hinttype_wordlength'] = 'Lunghezza della parola';
$string['editor_import'] = 'Importa sottotitoli';
$string['editor_importappend'] = 'Aggiungi ai segmenti esistenti';
$string['editor_importapply'] = 'Importa';
$string['editor_importcancel'] = 'Annulla';
$string['editor_importcheck'] = 'Controlla il contenuto';
$string['editor_importchecking'] = 'Controllo in corso…';
$string['editor_importcuecount'] = 'Segmenti trovati';
$string['editor_importduration'] = 'Durata';
$string['editor_importedcues'] = 'Segmenti importati: {$a}';
$string['editor_importfilehint'] = 'Scegli un file WebVTT (.vtt) o SubRip (.srt) con i sottotitoli.';
$string['editor_importformat'] = 'Formato';
$string['editor_importfromfile'] = 'Carica un file';
$string['editor_importfromtext'] = 'Incolla il testo';
$string['editor_importgapcount'] = 'Spazi trovati';
$string['editor_importhint'] = 'Incolla contenuto WebVTT o SubRip e importalo come segmenti.';
$string['editor_importparseerror'] = 'Non è stato possibile leggere questo contenuto come WebVTT o SubRip.';
$string['editor_importpastedtext'] = 'Testo incollato';
$string['editor_importreaderror'] = 'Non è stato possibile leggere il file.';
$string['editor_importready'] = 'Pronto per l\'importazione';
$string['editor_importreplace'] = 'Sostituisci tutti i segmenti';
$string['editor_importreplacedcues'] = 'Segmenti sostituiti; importati di nuovo: {$a}';
$string['editor_importsource'] = 'Origine';
$string['editor_importsummary'] = 'Che cosa è stato trovato';
$string['editor_importtoolarge'] = 'Questo file occupa {$a->size}; l\'importazione accetta al massimo {$a->max}.';
$string['editor_importwrongtype'] = 'Scegli un file di sottotitoli ({$a}).';
$string['editor_insertafter'] = 'Inserisci un segmento dopo';
$string['editor_insertbefore'] = 'Inserisci un segmento prima';
$string['editor_invalidtime'] = 'Inserisci un tempo nel formato mm:ss.SSS, per esempio 01:05.400.';
$string['editor_linkurl'] = 'Link di riferimento';
$string['editor_linkurl_help'] = 'Mostrato accanto allo spazio come punto in cui cercare la parola. Lascia vuoto per non offrirne nessuno.';
$string['editor_loaderror'] = 'Non è stato possibile caricare l\'editor. Ricarica la pagina.';
$string['editor_loading'] = 'Caricamento dell\'editor…';
$string['editor_maxlength'] = 'Lunghezza massima';
$string['editor_maxlength_help'] = 'Limita quanto può digitare uno studente. 0 significa nessun limite.';
$string['editor_media'] = 'Media';
$string['editor_mediafile'] = 'File caricato';
$string['editor_mediakind'] = 'Tipo di media';
$string['editor_medianone'] = 'Nessuno';
$string['editor_mediaprovider'] = 'Fornitore';
$string['editor_mediaproviderref'] = 'Riferimento del fornitore';
$string['editor_mediaproviderrefhint'] = 'Identificativo o link del video in una delle forme consuete (es. youtu.be/…).';
$string['editor_mediasaved'] = 'Media salvato.';
$string['editor_mediaurl'] = 'URL diretto del media';
$string['editor_nocues'] = 'Ancora nessun segmento. Aggiungine uno o importa i sottotitoli.';
$string['editor_nocueselected'] = 'Seleziona un segmento dall\'elenco per modificarlo.';
$string['editor_nocuesmatch'] = 'Nessun segmento corrisponde a questa ricerca.';
$string['editor_nogaps'] = 'Nessuno spazio';
$string['editor_nomedia'] = 'nessuno';
$string['editor_nomedianotice'] = 'Aggiungi prima il file video o audio nella scheda Media. I sottotitoli sono sincronizzati sul media, quindi l\'editor ne ha bisogno prima che tu possa lavorare su segmenti e spazi.';
$string['editor_novideotrack'] = 'Questo browser non riesce a decodificare la traccia video di questo media (viene riprodotto solo l\'audio); gli studenti vedrebbero un\'immagine nera. Ricodifica il file come H.264/MP4 (per esempio con ffmpeg o HandBrake) e caricalo di nuovo.';
$string['editor_onboardinggaps'] = 'Seleziona una parola in un segmento e trasformala in uno spazio.';
$string['editor_onboardingimport'] = 'Importa sottotitoli WebVTT/SubRip oppure aggiungi i segmenti a mano.';
$string['editor_onboardingintro'] = 'Crea un esercizio in tre passi:';
$string['editor_onboardingmedia'] = 'Scegli un media (caricamento, URL o fornitore).';
$string['editor_onboardingtitle'] = 'Inizia il tuo esercizio';
$string['editor_onlywarnings'] = 'Solo i segmenti con avvisi';
$string['editor_parsegaps'] = 'Riconosci i marcatori degli spazi: [parola] crea uno spazio con suggerimenti consentiti, {parola} uno senza.';
$string['editor_penalty'] = 'Penalità';
$string['editor_poster'] = 'Immagine di copertina';
$string['editor_preview'] = 'Anteprima per lo studente';
$string['editor_problem_afterduration'] = 'La fine è successiva alla fine del contenuto.';
$string['editor_problem_endbeforestart'] = 'La fine coincide con l’inizio o lo precede.';
$string['editor_problem_negativestart'] = 'L’inizio precede l’inizio della registrazione.';
$string['editor_publish'] = 'Pubblica';
$string['editor_publishblocked'] = 'Alcuni sottotitoli non si possono ancora salvare. Correggili prima di pubblicare.';
$string['editor_published'] = 'Versione pubblicata.';
$string['editor_removehint'] = 'Rimuovi il suggerimento';
$string['editor_removevariant'] = 'Rimuovi';
$string['editor_repaircue'] = 'Correggi la fine';
$string['editor_ruleapplied'] = 'Creati %count% spazi a partire dalla regola.';
$string['editor_ruleapply'] = 'Applica %count% spazi';
$string['editor_ruleerror'] = 'Non è stato possibile generare gli spazi.';
$string['editor_ruleeverynth'] = 'Ogni n parole';
$string['editor_rulefound'] = 'La regola ha trovato %count% spazi.';
$string['editor_rulegenerate'] = 'Genera gli spazi';
$string['editor_ruleinterval'] = 'Intervallo (n)';
$string['editor_ruletype'] = 'Regola per gli spazi';
$string['editor_rulewordlist'] = 'Parole da nascondere';
$string['editor_rulewords'] = 'Elenco di parole';
$string['editor_save'] = 'Salva la bozza';
$string['editor_saved'] = 'Bozza salvata.';
$string['editor_savedwithproblems'] = 'Salvato ciò che si poteva salvare; alcuni sottotitoli richiedono attenzione.';
$string['editor_saveerror'] = 'Non è stato possibile salvare la bozza.';
$string['editor_savemedia'] = 'Salva il media';
$string['editor_saving'] = 'Salvataggio…';
$string['editor_searchcues'] = 'Cerca nei segmenti';
$string['editor_selecttext'] = 'Seleziona prima la parola da nascondere nella trascrizione.';
$string['editor_solution'] = 'Soluzione';
$string['editor_starttime'] = 'Inizio';
$string['editor_transcript'] = 'Trascrizione';
$string['editor_unsaved'] = 'Modifiche non salvate';
$string['editor_uploadmedia'] = 'Carica i file multimediali';
$string['editor_variantisregex'] = 'Tratta {$a} come espressione regolare';
$string['editor_variantmatching'] = 'Come vengono confrontate le varianti accettate';
$string['editor_warnemptysolution'] = 'Uno spazio senza soluzione';
$string['editor_warnnotranscript'] = 'Nessun testo';
$string['editor_warntiming'] = 'La fine non è successiva all\'inizio';
$string['editor_waveform'] = 'Forma d\'onda dell\'audio';
$string['elang:addinstance'] = 'Aggiungere un nuovo dettato video';
$string['elang:attempt'] = 'Svolgere un dettato video';
$string['elang:deleteattempts'] = 'Eliminare i tentativi degli studenti';
$string['elang:exportreports'] = 'Esportare report contenenti dati personali';
$string['elang:exportsolution'] = 'Esportare la trascrizione completa con le soluzioni';
$string['elang:exporttranscript'] = 'Esportare la scheda di lavoro come documento';
$string['elang:manage'] = 'Creare e modificare il contenuto degli esercizi';
$string['elang:useregex'] = 'Usare espressioni regolari nelle risposte accettate';
$string['elang:view'] = 'Visualizzare un dettato video';
$string['elang:viewreports'] = 'Visualizzare i report degli studenti';
$string['error_attemptnotinprogress'] = 'Questo tentativo non è più in corso.';
$string['error_couldnotobtainlock'] = 'Non è stato possibile ottenere un blocco per questa operazione. Riprova.';
$string['error_draftrevisionmismatch'] = 'Questa bozza è cambiata da quando l\'hai caricata. Ricaricala e riprova.';
$string['error_duplicatecuekey'] = 'Due segmenti condividono la chiave «{$a}»; ogni segmento ha bisogno di una chiave univoca.';
$string['error_duplicategapkey'] = 'Due spazi dello stesso segmento condividono la chiave «{$a}»; ogni spazio ha bisogno di una chiave univoca.';
$string['error_duplicatehintlevel'] = 'Uno spazio ha due suggerimenti al livello {$a}; ogni livello deve essere univoco.';
$string['error_gapnotinattemptversion'] = 'Questo spazio non appartiene alla versione dell\'esercizio di questo tentativo.';
$string['error_importnocues'] = 'Non è stato possibile leggere alcun sottotitolo da questo contenuto. Un file WebVTT o SubRip ha una riga di tempo come 00:00:01.000 --> 00:00:04.000 sopra ogni sottotitolo.';
$string['error_importnotutf8'] = 'Questo file non è UTF-8 valido. Probabilmente è stato salvato con una codifica più vecchia — aprilo in un editor di testo e salvalo di nuovo come UTF-8.';
$string['error_importtoolarge'] = 'Questo file occupa {$a->size}; l\'importazione accetta al massimo {$a->max}. Un file di sottotitoli per la registrazione di una lezione è molto più piccolo, quindi è improbabile che lo sia.';
$string['error_importtoomanycues'] = 'Questo file contiene {$a->count} sottotitoli; l\'importazione ne accetta al massimo {$a->max}.';
$string['error_invalidcuepausemode'] = 'Scegli una delle opzioni proposte per le pause alla fine dei sottotitoli.';
$string['error_invalidgradingalgorithm'] = 'L\'algoritmo di valutazione «{$a}» non è né exact né wordrecognized.';
$string['error_invalidhinttype'] = 'Il tipo di suggerimento «{$a}» non è uno di quelli consentiti.';
$string['error_invalidisregex'] = 'Il marcatore di espressione regolare di una variante deve essere 0 o 1.';
$string['error_invalidmediakind'] = 'Il tipo di media scelto non è file, url né provider.';
$string['error_invalidpenalty'] = 'La penalità di un suggerimento deve essere compresa tra 0 e 1.';
$string['error_invalidproviderref'] = '«{$a}» non è un identificativo o un link video riconosciuto per questo fornitore.';
$string['error_invalidregexpattern'] = '«{$a}» non è un\'espressione regolare valida.';
$string['error_invalidsolutionavailability'] = 'Scegli una delle opzioni proposte per stabilire quando gli studenti possono vedere la trascrizione con le soluzioni.';
$string['error_invalidsourceurl'] = 'Inserisci un indirizzo completo che inizi con http:// o https://, oppure un link YouTube o Vimeo.';
$string['error_invalidsubtitleposition'] = 'Scegli una delle opzioni proposte per la posizione dei sottotitoli.';
$string['error_invalidv1cuejson'] = 'Non è stato possibile analizzare questo segmento della versione 1.';
$string['error_negativegapoffset'] = 'La posizione e la lunghezza di uno spazio non possono essere negative.';
$string['error_noaccesstoattempt'] = 'Non hai accesso a questo tentativo.';
$string['error_nomorehints'] = 'Non ci sono altri suggerimenti per questo spazio.';
$string['error_nopublishedversion'] = 'Questo esercizio non ha ancora contenuti pubblicati.';
$string['error_responsetoolong'] = 'La tua risposta è troppo lunga. Il massimo per questo spazio è di {$a} caratteri.';
$string['error_solutionnotavailable'] = 'La trascrizione con le soluzioni non è disponibile per te in questa attività.';
$string['error_staleattemptstate'] = 'La tua visualizzazione di questo tentativo non è aggiornata. Ricarica lo stato attuale e riprova.';
$string['error_transcriptnotavailable'] = 'In questa attività non è disponibile alcuna trascrizione da scaricare.';
$string['error_unknowngaprule'] = 'Tipo di regola per gli spazi sconosciuto «{$a}».';
$string['error_unknownmediaprovider'] = '«{$a}» non è uno dei fornitori di media supportati.';
$string['error_versionnotadraft'] = 'Si può modificare solo una versione in stato di bozza.';
$string['error_versionnotfound'] = 'Questa versione dell\'esercizio non esiste più.';
$string['error_versionnotpublishable'] = 'Questa versione non può essere pubblicata: {$a}';
$string['export_audienceaftersubmission'] = 'Gli studenti possono scaricarla dopo aver completato un tentativo';
$string['export_audiencealways'] = 'Gli studenti possono scaricarla in qualsiasi momento';
$string['export_audiencestaff'] = 'Solo personale docente autorizzato — non disponibile per gli studenti';
$string['export_docx'] = 'Scarica come Word (DOCX)';
$string['export_downloadpdf'] = 'Scarica il PDF';
$string['export_heading'] = 'Esporta la trascrizione';
$string['export_intro'] = 'Scarica la trascrizione di questo esercizio in vari formati.';
$string['export_moreformats'] = 'Altri formati';
$string['export_nocontent'] = 'Non c\'è ancora alcuna trascrizione pubblicata da esportare.';
$string['export_odt'] = 'Scarica come OpenDocument (ODT)';
$string['export_pdf'] = 'Scarica come PDF';
$string['export_solution'] = 'Trascrizione con le soluzioni';
$string['export_solutionhint'] = 'Il testo completo con la soluzione di ogni spazio visibile.';
$string['export_text'] = 'Scarica come testo';
$string['export_versionnote'] = 'Le esportazioni si basano sulla versione attualmente pubblicata di questo esercizio.';
$string['export_worksheet'] = 'Scheda di lavoro (spazi nascosti)';
$string['export_worksheethint'] = 'Il testo con ogni spazio nascosto. Pronto da distribuire come materiale per gli studenti.';
$string['exporttranscript'] = 'Esporta la trascrizione';
$string['filearea_media'] = 'Media';
$string['filearea_poster'] = 'Immagine di copertina';
$string['gradingheading'] = 'Valutazione delle risposte';
$string['import_badtiming'] = 'Non è stato possibile leggere la riga di tempo: {$a}';
$string['import_emptytranscript'] = 'Un segmento senza testo è stato saltato.';
$string['import_warnlinetoolong'] = 'Il blocco {$a->block} è stato saltato: contiene una riga di più di {$a->max} caratteri, che non è una riga di sottotitolo.';
$string['jarothreshold'] = 'Soglia di similarità';
$string['jarothreshold_help'] = 'Per gli spazi impostati su «Accetta risposte simili», questa è la similarità di Jaro minima tra la risposta attesa e quella digitata. Il valore 1 richiede una corrispondenza esatta dopo la normalizzazione specifica della lingua; valori più bassi accettano grafie via via più diverse.';
$string['jarothresholdrange'] = 'La soglia deve essere compresa tra 0 e 1.';
$string['language'] = 'Lingua del contenuto';
$string['language_help'] = 'Scegli la lingua del contenuto dell\'esercizio. Determina come vengono confrontate le risposte, comprese maiuscole e minuscole e la traslitterazione. Scegli «Generica (non specificata)» se non deve essere applicato alcun trattamento specifico per lingua. Le nuove versioni del contenuto partono da questa impostazione.';
$string['language_none'] = 'Generica (non specificata)';
$string['media_cuenote'] = 'I sottotitoli e gli spazi esistenti vengono mantenuti quando cambi media. I loro tempi non vengono adattati, quindi controllali poi nell\'editor.';
$string['media_current'] = 'Media attuale';
$string['media_heading'] = 'Media';
$string['media_intro'] = 'Scegli il video o l\'audio su cui si basa questo esercizio. I sottotitoli vi sono sincronizzati, quindi questo viene per primo.';
$string['media_none'] = 'Per questo esercizio non è ancora stato definito alcun media.';
$string['media_othersource'] = 'Altra origine';
$string['media_providerhint'] = 'Fornitori riconosciuti: {$a}. Qualsiasi altro indirizzo viene usato come URL diretto del media.';
$string['media_sourceurl'] = 'URL del media';
$string['media_sourceurl_help'] = 'Incolla l\'indirizzo di un video invece di caricare un file — un link YouTube o Vimeo, oppure l\'indirizzo diretto di un file multimediale.

Un indirizzo inserito qui sostituisce il file caricato. Lascialo vuoto per usare il caricamento qui sopra.

Un video di un fornitore viene riprodotto nel frame del fornitore stesso, che non comunica il tempo di riproduzione. Un esercizio di questo tipo mostra sempre i sottotitoli sotto il media e non si ferma mai alla fine dei sottotitoli.

**Dove vanno i dati.** Un frame YouTube o Vimeo collega il browser di ogni studente a quell\'azienda, che riceve così il suo indirizzo IP e i dati del dispositivo. Per impostazione predefinita l\'esercizio lo chiede prima di farlo. Se la tua istituzione dispone di un proprio server multimediale — Opencast, Panopto, Kaltura o simili — incolla invece l\'indirizzo diretto del file da lì: viene trattato come un normale URL di media, mantiene la posizione dei sottotitoli e l\'impostazione delle pause che hai scelto, e non è coinvolto alcun terzo.';
$string['migratev1_approvalheading'] = 'Migrate, in attesa di verifica';
$string['migratev1_approvebutton'] = 'Approva questa migrazione';
$string['migratev1_approved'] = 'Il dettato video {$a} è stato contrassegnato come approvato.';
$string['migratev1_colactivity'] = 'Attività';
$string['migratev1_colalgorithm'] = 'Algoritmo di valutazione';
$string['migratev1_colcues'] = 'Segmenti';
$string['migratev1_colgaps'] = 'Spazi';
$string['migratev1_colissues'] = 'Problemi';
$string['migratev1_collearners'] = 'Studenti';
$string['migratev1_confirmdecommission'] = 'Questa operazione elimina IN MODO IRREVERSIBILE le tabelle legacy della versione 1 e elang.options. Non si può annullare. Continuare?';
$string['migratev1_confirmmigrate'] = 'Questa operazione mette in coda un compito in background che scrive i dati della versione 2 per ogni attività elencata sopra. Le tabelle della versione 1 e elang.options restano intatte. Continuare?';
$string['migratev1_decommissionblocked'] = 'L\'eliminazione è ancora bloccata; vedi l\'elenco qui sotto.';
$string['migratev1_decommissionblockedintro'] = 'L\'eliminazione è bloccata finché:';
$string['migratev1_decommissionbutton'] = 'Elimina i dati legacy della versione 1';
$string['migratev1_decommissioned'] = 'I dati legacy della versione 1 sono stati eliminati.';
$string['migratev1_decommissionheading'] = 'Dismissione dei dati della versione 1';
$string['migratev1_decommissionready'] = 'Tutte le attività della versione 1 sono state migrate e approvate. Le tabelle legacy e elang.options possono ora essere eliminate. L\'operazione è irreversibile.';
$string['migratev1_heading'] = 'Migra le attività della versione 1';
$string['migratev1_migratebutton'] = 'Migra queste attività';
$string['migratev1_noissues'] = 'Nessuno';
$string['migratev1_nonepending'] = 'Non ci sono attività della versione 1 in attesa di migrazione.';
$string['migratev1_nonependingapproval'] = 'Non ci sono attività migrate in attesa di verifica.';
$string['migratev1_notablespresent'] = 'In questo sito non sono state trovate tabelle legacy della versione 1. Non c\'è nulla da migrare.';
$string['migratev1_parseerrorcount'] = 'Segmenti che non è stato possibile analizzare: {$a}';
$string['migratev1_pendingheading'] = 'Non ancora migrate';
$string['migratev1_queued'] = 'Il compito di migrazione è stato messo in coda. Verrà eseguito al prossimo passaggio del cron, oppure subito tramite admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Verificato: i dati migrati corrispondono all\'origine della versione 1 senza scostamenti.';
$string['migratev1_verifieddiscrepancies'] = 'La verifica ha rilevato scostamenti rispetto all\'origine della versione 1: {$a}';
$string['migratev1_verifyfailed'] = 'Non è stato possibile verificare questa attività: {$a}';
$string['modulename'] = 'Dettato video';
$string['modulename_help'] = 'L\'attività dettato video permette agli studenti di compilare spazi in sottotitoli sincronizzati mentre guardano o ascoltano un video.

I docenti importano un file di sottotitoli WebVTT o SubRip, contrassegnano parole o espressioni come spazi e impostano quanto rigorosamente vengono confrontate le risposte. Gli studenti percorrono la trascrizione segmento per segmento, chiedono suggerimenti con penalità e ricevono un riscontro immediato.';
$string['modulenameplural'] = 'Dettati video';
$string['nav_exportshort'] = 'Esporta';
$string['nav_media'] = 'Media';
$string['nav_reports'] = 'Tentativi';
$string['nav_subtitles'] = 'Sottotitoli e spazi';
$string['noinstances'] = 'In questo corso non ci sono dettati video.';
$string['overview_attempts'] = 'Tentativi';
$string['playbackheading'] = 'Riproduzione e sottotitoli';
$string['playbackoverlayhint'] = 'Un sottotitolo sovrapposto all\'immagine mostra solo quello in riproduzione, quindi la riproduzione si ferma sempre alla fine di un sottotitolo che ha ancora spazi da compilare. Qui non c\'è nulla da scegliere.';
$string['playbackproviderhint'] = 'Un video YouTube o Vimeo viene riprodotto dal fornitore nel proprio frame, che non comunica il tempo di riproduzione. Un esercizio di questo tipo mostra sempre i sottotitoli sotto il media e non si ferma mai alla fine dei sottotitoli, qualunque sia la scelta qui sopra. I file caricati e gli URL diretti rispettano entrambe le impostazioni.';
$string['player_check'] = 'Verifica la risposta';
$string['player_consentaccept'] = 'Carica il video da {$a}';
$string['player_consentdetail'] = 'Riprodurlo collega il tuo browser a {$a}. {$a} riceve il tuo indirizzo IP e informazioni sul tuo dispositivo, e può leggere i cookie che ha già impostato. Non viene inviato nulla finché non scegli di caricare il video.';
$string['player_consentheading'] = 'Questo video è fornito da {$a}';
$string['player_exitfullscreen'] = 'Esci da schermo intero';
$string['player_finish'] = 'Completa il tentativo';
$string['player_finished'] = 'Tentativo completato. Punteggio: %score%%';
$string['player_finishincomplete'] = 'Spazi ancora vuoti: {$a}. Completare comunque il tentativo?';
$string['player_fullscreen'] = 'Schermo intero';
$string['player_gaplabel'] = 'Spazio %gap%';
$string['player_gaplink'] = 'Apri il link';
$string['player_hint'] = 'Mostra un suggerimento';
$string['player_loaderror'] = 'Non è stato possibile caricare l\'esercizio. Ricarica la pagina.';
$string['player_loading'] = 'Caricamento dell\'esercizio…';
$string['player_nocontent'] = 'Non è ancora stato pubblicato alcun contenuto per l\'esercizio. Riprova più tardi.';
$string['player_novideotrack'] = 'Il tuo browser non riesce a mostrare la traccia video di questo media; l\'audio verrà comunque riprodotto. Informa il tuo docente.';
$string['player_outdatedattempt'] = 'Questo esercizio è stato aggiornato da quando hai iniziato il tentativo. Stai proseguendo sul contenuto precedente; completa questo tentativo per lavorare la prossima volta sull\'esercizio aggiornato.';
$string['player_progress'] = '{$a->done} spazi su {$a->total} con risposta';
$string['player_ready'] = 'Esercizio pronto.';
$string['player_scorelabel'] = 'Punteggio: %score%%';
$string['player_stateaccepted'] = 'Accettata';
$string['player_statecorrect'] = 'Corretta';
$string['player_statehinted'] = 'Suggerimento usato';
$string['player_stateincorrect'] = 'Errata';
$string['player_submitfailed'] = 'Non è stato possibile salvare la tua risposta. Riprova.';
$string['player_transcriptheading'] = 'Trascrizione';
$string['pluginadministration'] = 'Amministrazione del dettato video';
$string['pluginname'] = 'Dettato video';
$string['privacy_metadata_elang'] = 'Per ogni attività, la registrazione di chi ha approvato la migrazione unidirezionale dei suoi contenuti 1.x.';
$string['privacy_metadata_elang_attempt'] = 'Per ogni tentativo di un esercizio, l\'attività memorizza chi lo ha svolto, quando, fin dove è arrivato e come è stato valutato.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Quanti spazi lo studente ha compilato in questo tentativo.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Il numero progressivo di questo tentativo per l\'utente e l\'attività.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Quanti spazi sono stati accettati come corretti in questo tentativo.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Quanti spazi hanno ricevuto una risposta con corrispondenza esatta dei caratteri in questo tentativo.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Per quanti spazi lo studente ha chiesto un suggerimento in questo tentativo.';
$string['privacy_metadata_elang_attempt_score'] = 'Il punteggio ottenuto in questo tentativo.';
$string['privacy_metadata_elang_attempt_state'] = 'Se il tentativo è in corso, completato o abbandonato.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'Il momento in cui il tentativo è stato completato.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'Il momento dell\'ultimo aggiornamento del tentativo.';
$string['privacy_metadata_elang_attempt_timestart'] = 'Il momento in cui il tentativo è iniziato.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Il numero totale di spazi nella versione dell\'esercizio di questo tentativo.';
$string['privacy_metadata_elang_attempt_userid'] = 'L\'identificativo dell\'utente che ha svolto il tentativo.';
$string['privacy_metadata_elang_attempt_versionid'] = 'La versione dell\'esercizio su cui è stato svolto questo tentativo.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'L\'utente che ha approvato la migrazione di questa attività da mod_elang 1.x. Viene memorizzato affinché l\'approvazione resti verificabile.';
$string['privacy_metadata_elang_response'] = 'Per ogni spazio a cui uno studente risponde all\'interno di un tentativo, l\'attività memorizza il testo della risposta e come è stata valutata.';
$string['privacy_metadata_elang_response_accepted'] = 'Se la risposta è stata accettata come corretta per questo spazio.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Il livello di suggerimento più alto mostrato allo studente per questo spazio.';
$string['privacy_metadata_elang_response_responsetext'] = 'Il testo digitato dallo studente per questo spazio.';
$string['privacy_metadata_elang_response_resultstate'] = 'La classificazione attribuita dal valutatore a questa risposta (esatta, parola riconosciuta, errata o vuota).';
$string['privacy_metadata_elang_response_score'] = 'I punti apportati da questa risposta, dopo l\'eventuale penalità per il suggerimento.';
$string['privacy_metadata_elang_response_timecreated'] = 'Il momento del primo invio di questa risposta.';
$string['privacy_metadata_elang_response_timemodified'] = 'Il momento dell\'ultimo aggiornamento di questa risposta.';
$string['privacy_metadata_elang_response_tries'] = 'Quante volte lo studente ha inviato una risposta per questo spazio.';
$string['privacy_metadata_elang_version'] = 'Per ogni versione del contenuto, l\'attività memorizza quale utente l\'ha modificata per ultimo.';
$string['privacy_metadata_elang_version_usermodified'] = 'L\'utente che ha modificato per ultimo questa versione del contenuto. Viene memorizzato per poter verificare chi ha modificato il contenuto dell\'esercizio.';
$string['privacy_provider_externallink'] = 'Quando un esercizio si basa su un video YouTube o Vimeo, aprirlo collega il browser dello studente a quel fornitore. Il plugin non invia nulla di suo, ma il collegamento è causato dall\'attività. Che avvenga o meno dipende dall\'impostazione del sito sul consenso al fornitore e dall\'assenso dello studente.';
$string['privacy_provider_ipaddress'] = 'L\'indirizzo IP da cui si collega il browser dello studente.';
$string['privacy_provider_useragent'] = 'I dati di browser e dispositivo inviati dal browser.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Chiedi prima di incorporare YouTube o Vimeo';
$string['providerconsent_desc'] = 'Gli esercizi basati su un video YouTube o Vimeo mostrano un avviso al posto del video e lo incorporano solo dopo l\'assenso dello studente. Senza questo, il fornitore riceve l\'indirizzo IP e i dati del browser dello studente non appena la pagina si apre — prima che qualcuno prema play. Disattivalo solo se la tua istituzione raccoglie questo consenso altrove.';
$string['report_actions'] = 'Azioni';
$string['report_answered'] = 'Con risposta';
$string['report_attemptnumber'] = 'Tentativo';
$string['report_back'] = 'Torna a tutti i tentativi';
$string['report_correct'] = 'Corretti';
$string['report_delete'] = 'Elimina';
$string['report_deleteconfirm'] = 'Eliminare definitivamente questo tentativo e tutte le sue risposte? Non si può annullare.';
$string['report_deleted'] = 'Il tentativo è stato eliminato.';
$string['report_exact'] = 'Esatti';
$string['report_export'] = 'Esporta';
$string['report_filterany'] = 'Tutti';
$string['report_filterapply'] = 'Applica i filtri';
$string['report_filterattempt'] = 'Numero del tentativo';
$string['report_filterfrom'] = 'Iniziato dal';
$string['report_filterrangeerror'] = 'La fine dell\'intervallo precede il suo inizio.';
$string['report_filterreset'] = 'Azzera i filtri';
$string['report_filterstate'] = 'Stato';
$string['report_filterto'] = 'Iniziato fino al';
$string['report_filteruser'] = 'Partecipante';
$string['report_finished'] = 'Completati';
$string['report_heading'] = 'Tentativi';
$string['report_hinted'] = 'Con suggerimento';
$string['report_hints'] = 'Livello del suggerimento';
$string['report_kpianswered'] = 'Con risposta';
$string['report_kpiattempts'] = 'Tentativi mostrati';
$string['report_kpiaverage'] = 'Punteggio medio (completati)';
$string['report_kpicorrect'] = 'Accettati';
$string['report_kpiexact'] = 'Esattamente corretti';
$string['report_kpifinished'] = 'Completati';
$string['report_kpihinted'] = 'Hanno usato un suggerimento';
$string['report_kpihintedgaps'] = 'Hanno richiesto un suggerimento';
$string['report_noattempts'] = 'Ancora nessun tentativo.';
$string['report_nogaps'] = 'La versione su cui è stato svolto questo tentativo non ha spazi.';
$string['report_nomatchingattempts'] = 'Nessun tentativo corrisponde a questi filtri.';
$string['report_noresponse'] = 'Senza risposta';
$string['report_response'] = 'Risposta';
$string['report_result'] = 'Risultato';
$string['report_result_empty'] = 'Vuota';
$string['report_result_exact'] = 'Esatta';
$string['report_result_incorrect'] = 'Errata';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Riconosciuta';
$string['report_score'] = 'Punteggio';
$string['report_solution'] = 'Soluzione';
$string['report_started'] = 'Iniziato';
$string['report_state'] = 'Stato';
$string['report_state_abandoned'] = 'Abbandonato';
$string['report_state_finished'] = 'Completato';
$string['report_state_inprogress'] = 'In corso';
$string['report_transcript'] = 'Trascrizione';
$string['report_tries'] = 'Tentativi di risposta';
$string['report_user'] = 'Partecipante';
$string['report_view'] = 'Visualizza';
$string['reports'] = 'Report';
$string['resetattempts'] = 'Elimina tutti i tentativi e le risposte degli studenti';
$string['solutionavailability'] = 'Trascrizione con le soluzioni per gli studenti';
$string['solutionavailability_aftersubmission'] = 'Dopo il completamento del tentativo';
$string['solutionavailability_always'] = 'In qualsiasi momento';
$string['solutionavailability_help'] = 'Quando gli studenti possono scaricare la trascrizione completa con la soluzione di ogni spazio visibile.

* Mai — solo i docenti possono scaricarla.
* Dopo il completamento del tentativo — uno studente può scaricarla una volta completato un tentativo in questa attività.
* In qualsiasi momento — uno studente può scaricarla anche prima di rispondere.

Il personale docente autorizzato può scaricarla sempre, qualunque sia questa impostazione.';
$string['solutionavailability_never'] = 'Mai';
$string['subplugintype_elangscript'] = 'Gestore di scrittura';
$string['subplugintype_elangscript_plural'] = 'Gestori di scrittura';
$string['subtitleposition'] = 'Visualizzazione dei sottotitoli';
$string['subtitleposition_below'] = 'Sotto il media';
$string['subtitleposition_help'] = 'Dove vengono mostrati i sottotitoli interattivi.

* Sotto il media — l\'intera trascrizione si trova sotto il media, in una propria area scorrevole, e segue la riproduzione.
* Nel video, in basso o in alto — sopra il media viene disegnato solo il sottotitolo in riproduzione.

Un media di solo audio non ha un\'immagine su cui disegnare, quindi usa sempre la visualizzazione sotto il media. L\'impostazione viene comunque conservata e torna valida non appena l\'attività usa un video.';
$string['subtitleposition_overlaybottom'] = 'Nel video — in basso';
$string['subtitleposition_overlaytop'] = 'Nel video — in alto';
$string['task_migratev1activities'] = 'Migra le attività della versione 1';
$string['transcriptheading'] = 'Trascrizione per gli studenti';
$string['validate_cueafterend'] = '{$a->where}: termina a {$a->endtime} ms, dopo il media ({$a->duration} ms). La riproduzione non può mai arrivarci.';
$string['validate_cueendbeforestart'] = '{$a}: la fine non è successiva all\'inizio.';
$string['validate_cuewhere'] = 'Segmento {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'La soluzione di {$a} è vuota.';
$string['validate_hintlevels'] = 'I livelli di suggerimento di {$a} non formano una sequenza continua che inizia da 1.';
$string['validate_negativetime'] = '{$a}: il tempo di inizio precede l\'inizio della registrazione.';
$string['validate_nocues'] = 'La versione non ha segmenti.';
$string['validate_nogaps'] = 'La versione non ha spazi a cui rispondere.';
$string['validate_nonpositivelength'] = 'La lunghezza in caratteri di {$a} deve essere positiva.';
$string['validate_rangeoutside'] = 'L\'intervallo di caratteri di {$a} si trova al di fuori della sua trascrizione.';
$string['validate_rangeoverlap'] = 'L\'intervallo di caratteri di {$a} si sovrappone a un altro spazio.';
$string['validate_unknownalgorithm'] = 'L\'algoritmo di valutazione «{$a->algorithm}» di {$a->where} non è riconosciuto.';
$string['validate_where'] = 'lo spazio {$a->gapkey} nel segmento {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Spazio {$a->gapkey}: l\'algoritmo di valutazione è «{$a->actual}», era atteso «{$a->expected}».';
$string['verify_attemptcount'] = 'Il numero di tentativi migrati è {$a->actual}, erano attesi {$a->expected} studenti distinti della 1.x.';
$string['verify_jarothreshold'] = 'La soglia di confronto delle risposte è {$a->actual}, era attesa {$a->expected}.';
$string['verify_missingattempt'] = 'Utente {$a}: era atteso un tentativo migrato, non ne è stato trovato nessuno.';
$string['verify_missingcue'] = 'Segmento {$a}: il segmento migrato manca.';
$string['verify_missinggap'] = 'Spazio {$a}: lo spazio migrato manca.';
$string['verify_missinghint'] = 'Spazio {$a}: la versione 1 consentiva l\'aiuto qui, ma non è stato migrato alcun suggerimento.';
$string['verify_orphancue'] = 'Segmento {$a}: non è stato trovato alcun segmento corrispondente della versione 1.';
$string['verify_orphangap'] = 'Spazio {$a}: non è stato trovato alcuno spazio corrispondente della versione 1.';
$string['verify_rangemismatch'] = 'Spazio {$a}: l\'intervallo di caratteri non corrisponde all\'origine della versione 1.';
$string['verify_responsecount'] = 'Utente {$a->userid}: il numero di risposte migrate è {$a->actual}, era atteso {$a->expected}.';
$string['verify_solutionmismatch'] = 'Spazio {$a->gapkey}: la soluzione è «{$a->actual}», era attesa «{$a->expected}».';
$string['verify_transcriptmismatch'] = 'Segmento {$a}: la trascrizione non corrisponde all\'origine della versione 1.';
$string['verify_unexpectedhint'] = 'Spazio {$a}: la versione 1 non consentiva l\'aiuto qui, ma è stato migrato un suggerimento.';
