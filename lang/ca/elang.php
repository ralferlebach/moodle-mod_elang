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
 * Catalan strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * Terminology comes from Moodle's own Catalan pack — participant, intent,
 * activitat, subtítols, pista, curs.
 *
 * Deliberately not derived from the Spanish pack. Catalan is its own language,
 * not a variant of Castilian, and a mechanical transformation would produce
 * exactly the kind of text this project has been avoiding: buit rather than
 * hueco, fitxer rather than fichero, desar rather than guardar — none of which
 * a substitution rule would have found.
 *
 * A gap is **buit**, the ordinary word for a blank to fill in.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Llengües de contingut permeses';
$string['allowedlanguages_desc'] = 'Les llengües de contingut que s\'ofereixen en crear o editar un dictat en vídeo. No en triïs cap perquè s\'ofereixi la llista completa. Una activitat conserva la llengua desada encara que després la treguis d\'aquí.';
$string['allowtranscriptdownload'] = 'Baixada de la transcripció pels participants';
$string['allowtranscriptdownload_help'] = 'Quan està activat, els participants poden baixar el full de treball de la transcripció, amb cada buit amagat, en format PDF, Word, OpenDocument o text.

Per defecte està desactivat. El personal docent amb permís pot baixar la transcripció sempre, sigui quin sigui aquest paràmetre.';
$string['allowtranscriptdownload_label'] = 'Els participants poden baixar el full de treball';
$string['completiondetail_completionfinishattempt'] = 'Acabar un intent';
$string['completionfinishattempt'] = 'El participant ha d\'acabar un intent';
$string['cuepausemode'] = 'Pausa al final del subtítol';
$string['cuepausemode_auto'] = 'Automàtic';
$string['cuepausemode_help'] = 'Si el mitjà s\'atura al final d\'un subtítol.

* Automàtic — la reproducció continua i només s\'atura al final d\'un subtítol mentre s\'hi estigui treballant, és a dir, després de fer clic en ell o en un dels seus buits, o quan el focus del teclat hi és.
* Aturar-se a cada subtítol sense resposta — la reproducció s\'atura al final de cada subtítol que encara tingui un buit sense omplir, i espera que la reprenguis.
* No aturar mai — la reproducció continua fins al final del mitjà.

Cap dels dos primers no s\'atura en un subtítol amb tots els buits omplerts: això és feina acabada, i aturar-s\'hi demanaria una pulsació de tecla sense cap efecte. Això vol dir també que una segona passada per l\'exercici només s\'atura on encara falta alguna cosa.';
$string['cuepausemode_nostop'] = 'No aturar mai';
$string['cuepausemode_stop'] = 'Aturar-se a cada subtítol sense resposta';
$string['editcontent'] = 'Edita el contingut';
$string['editor_addcue'] = 'Afegeix un segment';
$string['editor_addgap'] = 'Crea un buit a partir de la selecció';
$string['editor_addhint'] = 'Afegeix una pista';
$string['editor_addvariant'] = 'Afegeix una variant';
$string['editor_advanced'] = 'Paràmetres avançats';
$string['editor_algoexact'] = 'Coincidència exacta';
$string['editor_algorithm'] = 'Comparació de respostes';
$string['editor_algowordrecognized'] = 'Accepta respostes properes';
$string['editor_answers'] = 'Variants acceptades';
$string['editor_autosaved'] = 'S\'han desat tots els canvis.';
$string['editor_autosaveerror'] = 'El desament automàtic ha fallat: fes servir «Desa» per tornar-ho a provar.';
$string['editor_captureend'] = 'Fixa el final des de la reproducció';
$string['editor_capturestart'] = 'Fixa l\'inici des de la reproducció';
$string['editor_cueactions'] = 'Accions del segment';
$string['editor_cuecount'] = 'Segments: {$a}';
$string['editor_currentmedia'] = 'Mitjà actual:';
$string['editor_deletecue'] = 'Suprimeix el segment';
$string['editor_deletegap'] = 'Suprimeix el buit';
$string['editor_emptytranscript'] = '(encara no hi ha text)';
$string['editor_endtime'] = 'Final';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Buits: {$a}';
$string['editor_gaprange'] = 'Posició del buit (caràcters)';
$string['editor_gotomedia'] = 'Vés a Mitjans';
$string['editor_heading'] = 'Edita els subtítols i els buits';
$string['editor_hints'] = 'Pistes';
$string['editor_hinttext'] = 'Text de la pista';
$string['editor_hinttype'] = 'Tipus';
$string['editor_hinttype_firstletter'] = 'Primera lletra';
$string['editor_hinttype_partial'] = 'Parcial';
$string['editor_hinttype_solution'] = 'Solució';
$string['editor_hinttype_text'] = 'Text lliure';
$string['editor_hinttype_translation'] = 'Traducció';
$string['editor_hinttype_wordlength'] = 'Longitud del mot';
$string['editor_import'] = 'Importa subtítols';
$string['editor_importappend'] = 'Afegeix als segments existents';
$string['editor_importapply'] = 'Importa';
$string['editor_importcancel'] = 'Cancel·la';
$string['editor_importcheck'] = 'Comprova el contingut';
$string['editor_importchecking'] = 'S\'està comprovant…';
$string['editor_importcuecount'] = 'Segments trobats';
$string['editor_importduration'] = 'Durada';
$string['editor_importedcues'] = 'Segments importats: {$a}';
$string['editor_importfilehint'] = 'Tria un fitxer WebVTT (.vtt) o SubRip (.srt) amb subtítols.';
$string['editor_importformat'] = 'Format';
$string['editor_importfromfile'] = 'Puja un fitxer';
$string['editor_importfromtext'] = 'Enganxa text';
$string['editor_importgapcount'] = 'Buits trobats';
$string['editor_importhint'] = 'Enganxa contingut WebVTT o SubRip i importa\'l com a segments.';
$string['editor_importparseerror'] = 'Aquest contingut no s\'ha pogut llegir com a WebVTT ni com a SubRip.';
$string['editor_importpastedtext'] = 'Text enganxat';
$string['editor_importreaderror'] = 'No s\'ha pogut llegir el fitxer.';
$string['editor_importready'] = 'A punt per importar';
$string['editor_importreplace'] = 'Substitueix tots els segments';
$string['editor_importreplacedcues'] = 'Segments substituïts; importats de nou: {$a}';
$string['editor_importsource'] = 'Origen';
$string['editor_importsummary'] = 'Què s\'ha trobat';
$string['editor_importtoolarge'] = 'Aquest fitxer ocupa {$a->size}; la importació accepta com a màxim {$a->max}.';
$string['editor_importwrongtype'] = 'Tria un fitxer de subtítols ({$a}).';
$string['editor_insertafter'] = 'Insereix un segment després';
$string['editor_insertbefore'] = 'Insereix un segment abans';
$string['editor_invalidtime'] = 'Introdueix un temps amb el format mm:ss.SSS, per exemple 01:05.400.';
$string['editor_linkurl'] = 'Enllaç de consulta';
$string['editor_linkurl_help'] = 'Es mostra al costat del buit com a lloc on consultar el mot. Deixa-ho buit si no en vols oferir cap.';
$string['editor_loaderror'] = 'No s\'ha pogut carregar l\'editor. Torna a carregar la pàgina.';
$string['editor_loading'] = 'S\'està carregant l\'editor…';
$string['editor_maxlength'] = 'Longitud màxima';
$string['editor_maxlength_help'] = 'Limita quant pot escriure un participant. 0 vol dir sense límit.';
$string['editor_media'] = 'Mitjans';
$string['editor_mediafile'] = 'Fitxer pujat';
$string['editor_mediakind'] = 'Tipus de mitjà';
$string['editor_medianone'] = 'Cap';
$string['editor_mediaprovider'] = 'Proveïdor';
$string['editor_mediaproviderref'] = 'Referència del proveïdor';
$string['editor_mediaproviderrefhint'] = 'Identificador o enllaç del vídeo en qualsevol forma habitual (p. ex. youtu.be/…).';
$string['editor_mediasaved'] = 'S\'ha desat el mitjà.';
$string['editor_mediaurl'] = 'Adreça directa del mitjà';
$string['editor_nocues'] = 'Encara no hi ha segments. Afegeix-ne un o importa subtítols.';
$string['editor_nocueselected'] = 'Tria un segment de la llista per editar-lo.';
$string['editor_nocuesmatch'] = 'Cap segment no coincideix amb aquesta cerca.';
$string['editor_nogaps'] = 'Sense buits';
$string['editor_nomedia'] = 'cap';
$string['editor_nomedianotice'] = 'Afegeix primer el fitxer de vídeo o d\'àudio a la pestanya Mitjans. Els subtítols se sincronitzen amb el mitjà, de manera que l\'editor el necessita abans no puguis treballar amb segments i buits.';
$string['editor_novideotrack'] = 'Aquest navegador no pot descodificar la pista de vídeo d\'aquest mitjà (només se sent l\'àudio); els participants veurien una imatge negra. Torna a codificar el fitxer com a H.264/MP4 (per exemple amb ffmpeg o HandBrake) i puja\'l de nou.';
$string['editor_onboardinggaps'] = 'Selecciona un mot d\'un segment i converteix-lo en un buit.';
$string['editor_onboardingimport'] = 'Importa subtítols WebVTT o SubRip, o afegeix segments a mà.';
$string['editor_onboardingintro'] = 'Crea un exercici en tres passos:';
$string['editor_onboardingmedia'] = 'Tria un mitjà (pujada, adreça o proveïdor).';
$string['editor_onboardingtitle'] = 'Comença el teu exercici';
$string['editor_onlywarnings'] = 'Només els segments amb avisos';
$string['editor_parsegaps'] = 'Reconeix les marques de buit: [mot] crea un buit amb pistes permeses, {mot} un sense.';
$string['editor_penalty'] = 'Penalització';
$string['editor_poster'] = 'Imatge de portada';
$string['editor_preview'] = 'Previsualització del participant';
$string['editor_publish'] = 'Publica';
$string['editor_published'] = 'S\'ha publicat la versió.';
$string['editor_removehint'] = 'Suprimeix la pista';
$string['editor_removevariant'] = 'Suprimeix';
$string['editor_ruleapplied'] = 'S\'han creat %count% buits a partir de la regla.';
$string['editor_ruleapply'] = 'Aplica %count% buits';
$string['editor_ruleerror'] = 'No s\'han pogut generar els buits.';
$string['editor_ruleeverynth'] = 'Cada n mots';
$string['editor_rulefound'] = 'La regla ha trobat %count% buits.';
$string['editor_rulegenerate'] = 'Genera els buits';
$string['editor_ruleinterval'] = 'Interval (n)';
$string['editor_ruletype'] = 'Regla de buits';
$string['editor_rulewordlist'] = 'Mots que s\'han d\'amagar';
$string['editor_rulewords'] = 'Llista de mots';
$string['editor_save'] = 'Desa l\'esborrany';
$string['editor_saved'] = 'S\'ha desat l\'esborrany.';
$string['editor_saveerror'] = 'No s\'ha pogut desar l\'esborrany.';
$string['editor_savemedia'] = 'Desa el mitjà';
$string['editor_saving'] = 'S\'està desant…';
$string['editor_searchcues'] = 'Cerca als segments';
$string['editor_selecttext'] = 'Selecciona primer el mot que s\'ha d\'amagar a la transcripció.';
$string['editor_solution'] = 'Solució';
$string['editor_starttime'] = 'Inici';
$string['editor_transcript'] = 'Transcripció';
$string['editor_unsaved'] = 'Canvis sense desar';
$string['editor_uploadmedia'] = 'Puja fitxers multimèdia';
$string['editor_variantisregex'] = 'Tracta {$a} com una expressió regular';
$string['editor_variantmatching'] = 'Com es comparen les variants acceptades';
$string['editor_warnemptysolution'] = 'Un buit sense solució';
$string['editor_warnnotranscript'] = 'Sense text';
$string['editor_warntiming'] = 'El final no és posterior a l\'inici';
$string['editor_waveform'] = 'Forma d\'ona de l\'àudio';
$string['elang:addinstance'] = 'Afegir un dictat en vídeo nou';
$string['elang:attempt'] = 'Fer un dictat en vídeo';
$string['elang:deleteattempts'] = 'Suprimir els intents dels participants';
$string['elang:exportreports'] = 'Exportar informes amb dades personals';
$string['elang:exportsolution'] = 'Exportar la transcripció completa amb les solucions';
$string['elang:exporttranscript'] = 'Exportar el full de treball com a document';
$string['elang:manage'] = 'Crear i editar el contingut dels exercicis';
$string['elang:useregex'] = 'Fer servir expressions regulars a les respostes acceptades';
$string['elang:view'] = 'Veure un dictat en vídeo';
$string['elang:viewreports'] = 'Veure els informes dels participants';
$string['error_attemptnotinprogress'] = 'Aquest intent ja no està en curs.';
$string['error_couldnotobtainlock'] = 'No s\'ha pogut obtenir un bloqueig per a aquesta operació. Torna-ho a provar.';
$string['error_draftrevisionmismatch'] = 'Aquest esborrany ha canviat des que el vas carregar. Torna\'l a carregar i prova-ho de nou.';
$string['error_duplicatecuekey'] = 'Dos segments comparteixen la clau «{$a}»; cada segment necessita una clau única.';
$string['error_duplicategapkey'] = 'Dos buits del mateix segment comparteixen la clau «{$a}»; cada buit necessita una clau única.';
$string['error_duplicatehintlevel'] = 'Un buit té dues pistes al nivell {$a}; cada nivell ha de ser únic.';
$string['error_gapnotinattemptversion'] = 'Aquest buit no pertany a la versió de l\'exercici d\'aquest intent.';
$string['error_importnocues'] = 'No s\'ha pogut llegir cap subtítol d\'aquest contingut. Un fitxer WebVTT o SubRip té una línia de temps com ara 00:00:01.000 --> 00:00:04.000 damunt de cada subtítol.';
$string['error_importnotutf8'] = 'Aquest fitxer no és UTF-8 vàlid. Probablement es va desar amb una codificació antiga: obre\'l en un editor de text i desa\'l de nou com a UTF-8.';
$string['error_importtoolarge'] = 'Aquest fitxer ocupa {$a->size}; la importació accepta com a màxim {$a->max}. Un fitxer de subtítols de l\'enregistrament d\'una classe és molt més petit, de manera que és poc probable que ho sigui.';
$string['error_importtoomanycues'] = 'Aquest fitxer conté {$a->count} subtítols; la importació n\'accepta com a màxim {$a->max}.';
$string['error_invalidcuepausemode'] = 'Tria una de les opcions ofertes per a la pausa al final del subtítol.';
$string['error_invalidgradingalgorithm'] = 'L\'algorisme d\'avaluació «{$a}» no és ni exact ni wordrecognized.';
$string['error_invalidhinttype'] = 'El tipus de pista «{$a}» no és cap dels tipus permesos.';
$string['error_invalidisregex'] = 'El marcador d\'expressió regular d\'una variant ha de ser 0 o 1.';
$string['error_invalidmediakind'] = 'El tipus de mitjà triat no és file, url ni provider.';
$string['error_invalidpenalty'] = 'La penalització d\'una pista ha d\'estar entre 0 i 1.';
$string['error_invalidproviderref'] = '«{$a}» no és cap identificador ni enllaç de vídeo reconegut per a aquest proveïdor.';
$string['error_invalidregexpattern'] = '«{$a}» no és una expressió regular vàlida.';
$string['error_invalidsolutionavailability'] = 'Tria una de les opcions ofertes per determinar quan poden veure els participants la transcripció amb les solucions.';
$string['error_invalidsourceurl'] = 'Introdueix una adreça completa que comenci per http:// o https://, o bé un enllaç de YouTube o Vimeo.';
$string['error_invalidsubtitleposition'] = 'Tria una de les opcions ofertes per a la ubicació dels subtítols.';
$string['error_invalidv1cuejson'] = 'No s\'ha pogut processar aquest segment de la versió 1.';
$string['error_negativegapoffset'] = 'La posició i la longitud d\'un buit no poden ser negatives.';
$string['error_noaccesstoattempt'] = 'No tens accés a aquest intent.';
$string['error_nomorehints'] = 'No hi ha més pistes per a aquest buit.';
$string['error_nopublishedversion'] = 'Aquest exercici encara no té contingut publicat.';
$string['error_responsetoolong'] = 'La teva resposta és massa llarga. El màxim per a aquest buit és de {$a} caràcters.';
$string['error_solutionnotavailable'] = 'La transcripció amb les solucions no està disponible per a tu en aquesta activitat.';
$string['error_staleattemptstate'] = 'La teva vista d\'aquest intent no està actualitzada. Torna a carregar l\'estat actual i prova-ho de nou.';
$string['error_transcriptnotavailable'] = 'En aquesta activitat no hi ha cap transcripció per baixar.';
$string['error_unknowngaprule'] = 'Tipus de regla de buits desconegut «{$a}».';
$string['error_unknownmediaprovider'] = '«{$a}» no és cap dels proveïdors de mitjans admesos.';
$string['error_versionnotadraft'] = 'Només es pot editar una versió en estat d\'esborrany.';
$string['error_versionnotfound'] = 'Aquesta versió de l\'exercici ja no existeix.';
$string['error_versionnotpublishable'] = 'Aquesta versió no es pot publicar: {$a}';
$string['export_audienceaftersubmission'] = 'Els participants la poden baixar un cop hagin acabat un intent';
$string['export_audiencealways'] = 'Els participants la poden baixar en qualsevol moment';
$string['export_audiencestaff'] = 'Només personal docent amb permís: no s\'ofereix als participants';
$string['export_docx'] = 'Baixa com a Word (DOCX)';
$string['export_downloadpdf'] = 'Baixa el PDF';
$string['export_heading'] = 'Exporta la transcripció';
$string['export_intro'] = 'Baixa la transcripció d\'aquest exercici en diversos formats.';
$string['export_moreformats'] = 'Més formats';
$string['export_nocontent'] = 'Encara no hi ha cap transcripció publicada per exportar.';
$string['export_odt'] = 'Baixa com a OpenDocument (ODT)';
$string['export_pdf'] = 'Baixa com a PDF';
$string['export_solution'] = 'Transcripció amb les solucions';
$string['export_solutionhint'] = 'El text complet amb la solució de cada buit visible.';
$string['export_text'] = 'Baixa com a text';
$string['export_versionnote'] = 'Les exportacions es basen en la versió publicada actualment d\'aquest exercici.';
$string['export_worksheet'] = 'Full de treball (buits amagats)';
$string['export_worksheethint'] = 'El text amb cada buit amagat. A punt per repartir com a material per als participants.';
$string['exporttranscript'] = 'Exporta la transcripció';
$string['filearea_media'] = 'Mitjans';
$string['filearea_poster'] = 'Imatge de portada';
$string['gradingheading'] = 'Avaluació de les respostes';
$string['import_badtiming'] = 'No s\'ha pogut llegir la línia de temps: {$a}';
$string['import_emptytranscript'] = 'S\'ha omès un segment sense text.';
$string['import_warnlinetoolong'] = 'S\'ha omès el bloc {$a->block}: conté una línia de més de {$a->max} caràcters, cosa que no és una línia de subtítol.';
$string['jarothreshold'] = 'Llindar de similitud';
$string['jarothreshold_help'] = 'Per als buits configurats com a «Accepta respostes properes», aquest és el valor mínim de similitud de Jaro entre la resposta esperada i la que s\'escriu. El valor 1 exigeix una coincidència exacta després de la normalització pròpia de la llengua; els valors més baixos accepten grafies cada cop més diferents.';
$string['jarothresholdrange'] = 'El llindar ha d\'estar entre 0 i 1.';
$string['language'] = 'Llengua del contingut';
$string['language_help'] = 'Tria la llengua del contingut de l\'exercici. Determina com es comparen les respostes, incloent-hi les majúscules i la transliteració. Tria «Genèrica (sense especificar)» si no s\'ha d\'aplicar cap tractament propi d\'una llengua. Les noves versions de contingut parteixen d\'aquest paràmetre.';
$string['language_none'] = 'Genèrica (sense especificar)';
$string['media_cuenote'] = 'Els subtítols i els buits existents es mantenen quan canvies de mitjà. Els seus temps no s\'ajusten, de manera que revisa\'ls després a l\'editor.';
$string['media_current'] = 'Mitjà actual';
$string['media_heading'] = 'Mitjans';
$string['media_intro'] = 'Tria el vídeo o l\'àudio en què es basa aquest exercici. Els subtítols s\'hi sincronitzen, de manera que aquest és el primer pas.';
$string['media_none'] = 'Encara no s\'ha definit cap mitjà per a aquest exercici.';
$string['media_othersource'] = 'Un altre origen';
$string['media_providerhint'] = 'Proveïdors reconeguts: {$a}. Qualsevol altra adreça es fa servir com a adreça directa del mitjà.';
$string['media_sourceurl'] = 'Adreça del mitjà';
$string['media_sourceurl_help'] = 'Enganxa l\'adreça d\'un vídeo en comptes de pujar un fitxer: un enllaç de YouTube o Vimeo, o l\'adreça directa d\'un fitxer multimèdia.

Una adreça introduïda aquí substitueix el fitxer pujat. Deixa-la buida per fer servir la pujada de més amunt.

El vídeo d\'un proveïdor es reprodueix dins del marc del mateix proveïdor, que no informa del temps de reproducció. Un exercici així mostra sempre els subtítols sota el mitjà i no s\'atura mai al final d\'un subtítol.

**On van les dades.** Un marc de YouTube o Vimeo connecta el navegador de cada participant amb aquella empresa, que rep així la seva adreça IP i les dades del dispositiu. Per defecte, l\'exercici ho demana abans. Si la teva institució té un servidor multimèdia propi —Opencast, Panopto, Kaltura o semblant—, enganxa-hi l\'adreça directa del fitxer: es tracta com una adreça de mitjà normal, conserva la posició dels subtítols i el paràmetre de pausa que hagis triat, i no hi intervé cap tercer.';
$string['migratev1_approvalheading'] = 'Migrades, pendents de revisió';
$string['migratev1_approvebutton'] = 'Aprova aquesta migració';
$string['migratev1_approved'] = 'El dictat en vídeo {$a} s\'ha marcat com a aprovat.';
$string['migratev1_colactivity'] = 'Activitat';
$string['migratev1_colalgorithm'] = 'Algorisme d\'avaluació';
$string['migratev1_colcues'] = 'Segments';
$string['migratev1_colgaps'] = 'Buits';
$string['migratev1_colissues'] = 'Problemes';
$string['migratev1_collearners'] = 'Participants';
$string['migratev1_confirmdecommission'] = 'Aquesta operació suprimeix DE MANERA IRREVERSIBLE les taules antigues de la versió 1 i la columna elang.options. No es pot desfer. Vols continuar?';
$string['migratev1_confirmmigrate'] = 'Aquesta operació posa a la cua una tasca en segon pla que escriu les dades de la versió 2 per a cada activitat de la llista anterior. Les taules de la versió 1 i elang.options queden intactes. Vols continuar?';
$string['migratev1_decommissionblocked'] = 'La supressió encara està bloquejada; mira la llista de sota.';
$string['migratev1_decommissionblockedintro'] = 'La supressió està bloquejada fins que:';
$string['migratev1_decommissionbutton'] = 'Suprimeix les dades antigues de la versió 1';
$string['migratev1_decommissioned'] = 'S\'han suprimit les dades antigues de la versió 1.';
$string['migratev1_decommissionheading'] = 'Retirada de les dades de la versió 1';
$string['migratev1_decommissionready'] = 'Totes les activitats de la versió 1 s\'han migrat i aprovat. Ara es poden suprimir les taules antigues i elang.options. Aquesta operació és irreversible.';
$string['migratev1_heading'] = 'Migra les activitats de la versió 1';
$string['migratev1_migratebutton'] = 'Migra aquestes activitats';
$string['migratev1_noissues'] = 'Cap';
$string['migratev1_nonepending'] = 'No hi ha activitats de la versió 1 pendents de migrar.';
$string['migratev1_nonependingapproval'] = 'No hi ha activitats migrades pendents de revisió.';
$string['migratev1_notablespresent'] = 'En aquest lloc no s\'han trobat taules antigues de la versió 1. No hi ha res a migrar.';
$string['migratev1_parseerrorcount'] = 'Segments que no s\'han pogut processar: {$a}';
$string['migratev1_pendingheading'] = 'Encara sense migrar';
$string['migratev1_queued'] = 'La tasca de migració s\'ha posat a la cua. S\'executarà en la propera passada del cron, o de seguida amb admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Verificat: les dades migrades coincideixen amb l\'origen de la versió 1 sense discrepàncies.';
$string['migratev1_verifieddiscrepancies'] = 'La verificació ha trobat discrepàncies respecte a l\'origen de la versió 1: {$a}';
$string['migratev1_verifyfailed'] = 'No s\'ha pogut verificar aquesta activitat: {$a}';
$string['modulename'] = 'Dictat en vídeo';
$string['modulename_help'] = 'L\'activitat dictat en vídeo permet als participants omplir buits en subtítols sincronitzats mentre miren o escolten un vídeo.

Els docents importen un fitxer de subtítols WebVTT o SubRip, marquen mots o expressions com a buits i configuren amb quin rigor es comparen les respostes. Els participants recorren la transcripció segment a segment, demanen pistes penalitzades i reben resposta immediata.';
$string['modulenameplural'] = 'Dictats en vídeo';
$string['nav_exportshort'] = 'Exporta';
$string['nav_media'] = 'Mitjans';
$string['nav_reports'] = 'Intents';
$string['nav_subtitles'] = 'Subtítols i buits';
$string['noinstances'] = 'No hi ha dictats en vídeo en aquest curs.';
$string['overview_attempts'] = 'Intents';
$string['playbackheading'] = 'Reproducció i subtítols';
$string['playbackoverlayhint'] = 'Un subtítol sobreposat a la imatge només mostra el que s\'està reproduint, de manera que la reproducció sempre s\'atura al final d\'un subtítol que encara té buits per omplir. Aquí no hi ha res a triar.';
$string['playbackproviderhint'] = 'Un vídeo de YouTube o Vimeo el reprodueix el proveïdor dins del seu propi marc, que no informa del temps de reproducció. Un exercici així mostra sempre els subtítols sota el mitjà i no s\'atura mai al final d\'un subtítol, sigui quina sigui l\'opció triada més amunt. Els fitxers pujats i les adreces directes respecten tots dos paràmetres.';
$string['player_check'] = 'Comprova la resposta';
$string['player_consentaccept'] = 'Carrega el vídeo des de {$a}';
$string['player_consentdetail'] = 'Reproduir-lo connecta el teu navegador amb {$a}. {$a} rep la teva adreça IP i informació sobre el teu dispositiu, i pot llegir galetes que ja hagi posat. No s\'envia res fins que no decideixis carregar el vídeo.';
$string['player_consentheading'] = 'Aquest vídeo el proporciona {$a}';
$string['player_finish'] = 'Acaba l\'intent';
$string['player_finished'] = 'Intent acabat. Puntuació: %score%%';
$string['player_finishincomplete'] = 'Buits que encara són buits: {$a}. Vols acabar l\'intent igualment?';
$string['player_gaplabel'] = 'Buit %gap%';
$string['player_gaplink'] = 'Obre l\'enllaç';
$string['player_hint'] = 'Mostra una pista';
$string['player_loaderror'] = 'No s\'ha pogut carregar l\'exercici. Torna a carregar la pàgina.';
$string['player_loading'] = 'S\'està carregant l\'exercici…';
$string['player_nocontent'] = 'Encara no s\'ha publicat cap contingut d\'exercici. Torna-hi més tard.';
$string['player_novideotrack'] = 'El teu navegador no pot mostrar la pista de vídeo d\'aquest mitjà; l\'àudio es reproduirà igualment. Avisa\'n el teu docent.';
$string['player_outdatedattempt'] = 'Aquest exercici s\'ha actualitzat des que vas començar aquest intent. Continues amb el contingut anterior; acaba aquest intent per treballar la propera vegada amb l\'exercici actualitzat.';
$string['player_progress'] = 'S\'han respost {$a->done} de {$a->total} buits';
$string['player_ready'] = 'L\'exercici és a punt.';
$string['player_scorelabel'] = 'Puntuació: %score%%';
$string['player_stateaccepted'] = 'Acceptada';
$string['player_statecorrect'] = 'Correcta';
$string['player_statehinted'] = 'Pista utilitzada';
$string['player_stateincorrect'] = 'Incorrecta';
$string['player_submitfailed'] = 'No s\'ha pogut desar la teva resposta. Torna-ho a provar.';
$string['player_transcriptheading'] = 'Transcripció';
$string['pluginadministration'] = 'Administració del dictat en vídeo';
$string['pluginname'] = 'Dictat en vídeo';
$string['privacy_metadata_elang'] = 'Per a cada activitat, el registre de qui va aprovar la migració unidireccional del seu contingut de l\'1.x.';
$string['privacy_metadata_elang_attempt'] = 'Per a cada intent d\'un exercici, l\'activitat desa qui el va fer, quan, fins on va arribar i com es va puntuar.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Quants buits ha respost el participant en aquest intent.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'El número d\'ordre d\'aquest intent per a l\'usuari i l\'activitat.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Quants buits s\'han acceptat com a correctes en aquest intent.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Quants buits s\'han respost amb coincidència exacta de caràcters en aquest intent.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Per a quants buits ha demanat una pista el participant en aquest intent.';
$string['privacy_metadata_elang_attempt_score'] = 'La puntuació obtinguda en aquest intent.';
$string['privacy_metadata_elang_attempt_state'] = 'Si l\'intent està en curs, acabat o abandonat.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'El moment en què es va acabar l\'intent.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'El moment de l\'última actualització de l\'intent.';
$string['privacy_metadata_elang_attempt_timestart'] = 'El moment en què va començar l\'intent.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'El nombre total de buits de la versió de l\'exercici d\'aquest intent.';
$string['privacy_metadata_elang_attempt_userid'] = 'L\'identificador de l\'usuari que va fer l\'intent.';
$string['privacy_metadata_elang_attempt_versionid'] = 'La versió de l\'exercici sobre la qual es va fer aquest intent.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'L\'usuari que va aprovar la migració d\'aquesta activitat des de mod_elang 1.x. Es desa perquè l\'aprovació continuï sent verificable.';
$string['privacy_metadata_elang_response'] = 'Per a cada buit que un participant respon dins d\'un intent, l\'activitat desa el text de la resposta i com es va avaluar.';
$string['privacy_metadata_elang_response_accepted'] = 'Si la resposta s\'ha acceptat com a correcta per a aquest buit.';
$string['privacy_metadata_elang_response_hintlevel'] = 'El nivell de pista més alt mostrat al participant per a aquest buit.';
$string['privacy_metadata_elang_response_responsetext'] = 'El text que el participant ha escrit en aquest buit.';
$string['privacy_metadata_elang_response_resultstate'] = 'La classificació que l\'avaluació ha donat a aquesta resposta (exacta, mot reconegut, incorrecta o buida).';
$string['privacy_metadata_elang_response_score'] = 'Els punts que ha aportat aquesta resposta, un cop aplicada qualsevol penalització per pista.';
$string['privacy_metadata_elang_response_timecreated'] = 'El moment en què es va enviar aquesta resposta per primer cop.';
$string['privacy_metadata_elang_response_timemodified'] = 'El moment de l\'última actualització d\'aquesta resposta.';
$string['privacy_metadata_elang_response_tries'] = 'Quantes vegades ha enviat el participant una resposta per a aquest buit.';
$string['privacy_metadata_elang_version'] = 'Per a cada versió de contingut, l\'activitat desa quin usuari la va modificar per última vegada.';
$string['privacy_metadata_elang_version_usermodified'] = 'L\'usuari que va modificar aquesta versió de contingut per última vegada. Es desa per poder verificar qui va editar el contingut de l\'exercici.';
$string['privacy_provider_externallink'] = 'Quan un exercici es basa en un vídeo de YouTube o Vimeo, obrir-lo connecta el navegador del participant amb aquell proveïdor. El connector no envia res per si mateix, però la connexió la provoca l\'activitat. Que arribi a passar o no depèn del paràmetre del lloc sobre el consentiment als proveïdors i de l\'acord del participant.';
$string['privacy_provider_ipaddress'] = 'L\'adreça IP des de la qual es connecta el navegador del participant.';
$string['privacy_provider_useragent'] = 'Les dades de navegador i dispositiu que envia el navegador.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Pregunta abans d\'incrustar YouTube o Vimeo';
$string['providerconsent_desc'] = 'Els exercicis basats en un vídeo de YouTube o Vimeo mostren un avís en comptes del vídeo i només l\'incrusten després que el participant hi doni el seu acord. Sense això, el proveïdor rep l\'adreça IP i les dades del navegador del participant tan bon punt s\'obre la pàgina, abans que ningú premi reproduir. Desactiva-ho només si la teva institució obté aquest consentiment per una altra via.';
$string['report_actions'] = 'Accions';
$string['report_answered'] = 'Respostos';
$string['report_attemptnumber'] = 'Intent';
$string['report_back'] = 'Torna a tots els intents';
$string['report_correct'] = 'Correctes';
$string['report_delete'] = 'Suprimeix';
$string['report_deleteconfirm'] = 'Vols suprimir definitivament aquest intent i totes les seves respostes? No es pot desfer.';
$string['report_deleted'] = 'S\'ha suprimit l\'intent.';
$string['report_exact'] = 'Exactes';
$string['report_export'] = 'Exporta';
$string['report_filterany'] = 'Tots';
$string['report_filterapply'] = 'Aplica els filtres';
$string['report_filterattempt'] = 'Número d\'intent';
$string['report_filterfrom'] = 'Començat a partir de';
$string['report_filterrangeerror'] = 'El final del període és anterior al seu inici.';
$string['report_filterreset'] = 'Esborra els filtres';
$string['report_filterstate'] = 'Estat';
$string['report_filterto'] = 'Començat fins a';
$string['report_filteruser'] = 'Participant';
$string['report_finished'] = 'Acabats';
$string['report_heading'] = 'Intents';
$string['report_hinted'] = 'Amb pista';
$string['report_hints'] = 'Nivell de pista';
$string['report_kpianswered'] = 'Respostos';
$string['report_kpiattempts'] = 'Intents mostrats';
$string['report_kpiaverage'] = 'Puntuació mitjana (acabats)';
$string['report_kpicorrect'] = 'Acceptats';
$string['report_kpiexact'] = 'Exactament correctes';
$string['report_kpifinished'] = 'Acabats';
$string['report_kpihinted'] = 'Van fer servir una pista';
$string['report_kpihintedgaps'] = 'Van necessitar una pista';
$string['report_noattempts'] = 'Encara no hi ha intents.';
$string['report_nogaps'] = 'La versió sobre la qual s\'ha fet aquest intent no té buits.';
$string['report_nomatchingattempts'] = 'Cap intent no coincideix amb aquests filtres.';
$string['report_noresponse'] = 'Sense resposta';
$string['report_response'] = 'Resposta';
$string['report_result'] = 'Resultat';
$string['report_result_empty'] = 'Buida';
$string['report_result_exact'] = 'Exacta';
$string['report_result_incorrect'] = 'Incorrecta';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Reconeguda';
$string['report_score'] = 'Puntuació';
$string['report_solution'] = 'Solució';
$string['report_started'] = 'Començat';
$string['report_state'] = 'Estat';
$string['report_state_abandoned'] = 'Abandonat';
$string['report_state_finished'] = 'Acabat';
$string['report_state_inprogress'] = 'En curs';
$string['report_transcript'] = 'Transcripció';
$string['report_tries'] = 'Intents de resposta';
$string['report_user'] = 'Participant';
$string['report_view'] = 'Mostra';
$string['reports'] = 'Informes';
$string['resetattempts'] = 'Suprimeix tots els intents i respostes dels participants';
$string['solutionavailability'] = 'Transcripció amb les solucions per als participants';
$string['solutionavailability_aftersubmission'] = 'Un cop acabat l\'intent';
$string['solutionavailability_always'] = 'En qualsevol moment';
$string['solutionavailability_help'] = 'Quan poden baixar els participants la transcripció completa amb la solució de cada buit visible.

* Mai — només els docents la poden baixar.
* Un cop acabat l\'intent — un participant la pot baixar quan hagi acabat un intent en aquesta activitat.
* En qualsevol moment — un participant la pot baixar fins i tot abans de respondre.

El personal docent amb permís la pot baixar sempre, sigui quin sigui aquest paràmetre.';
$string['solutionavailability_never'] = 'Mai';
$string['subplugintype_elangscript'] = 'Gestor d\'escriptura';
$string['subplugintype_elangscript_plural'] = 'Gestors d\'escriptura';
$string['subtitleposition'] = 'Visualització dels subtítols';
$string['subtitleposition_below'] = 'Sota el mitjà';
$string['subtitleposition_help'] = 'On es mostren els subtítols interactius.

* Sota el mitjà — tota la transcripció queda sota el mitjà, en una àrea de desplaçament pròpia, i segueix la reproducció.
* Al vídeo, a dalt o a baix — només es dibuixa sobre el mitjà el subtítol que s\'està reproduint.

Un mitjà només d\'àudio no té imatge sobre la qual dibuixar, de manera que sempre fa servir la visualització sota el mitjà. El paràmetre es conserva i torna a aplicar-se tan bon punt l\'activitat faci servir un vídeo.';
$string['subtitleposition_overlaybottom'] = 'Al vídeo — a baix';
$string['subtitleposition_overlaytop'] = 'Al vídeo — a dalt';
$string['task_migratev1activities'] = 'Migra les activitats de la versió 1';
$string['transcriptheading'] = 'Transcripció per als participants';
$string['validate_cueafterend'] = '{$a->where}: acaba a {$a->endtime} ms, després del mitjà ({$a->duration} ms). La reproducció no hi pot arribar mai.';
$string['validate_cueendbeforestart'] = '{$a}: el final no és posterior a l\'inici.';
$string['validate_cuewhere'] = 'Segment {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'La solució de {$a} és buida.';
$string['validate_hintlevels'] = 'Els nivells de pista de {$a} no formen una seqüència contínua que comenci per 1.';
$string['validate_negativetime'] = '{$a}: el temps d\'inici és anterior al començament de l\'enregistrament.';
$string['validate_nocues'] = 'La versió no té segments.';
$string['validate_nogaps'] = 'La versió no té buits per respondre.';
$string['validate_nonpositivelength'] = 'La longitud en caràcters de {$a} ha de ser positiva.';
$string['validate_rangeoutside'] = 'L\'interval de caràcters de {$a} queda fora de la seva transcripció.';
$string['validate_rangeoverlap'] = 'L\'interval de caràcters de {$a} se superposa amb un altre buit.';
$string['validate_unknownalgorithm'] = 'L\'algorisme d\'avaluació «{$a->algorithm}» de {$a->where} no es reconeix.';
$string['validate_where'] = 'el buit {$a->gapkey} del segment {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Buit {$a->gapkey}: l\'algorisme d\'avaluació és «{$a->actual}», s\'esperava «{$a->expected}».';
$string['verify_attemptcount'] = 'El nombre d\'intents migrats és {$a->actual}, s\'esperaven {$a->expected} participants diferents de l\'1.x.';
$string['verify_jarothreshold'] = 'El llindar de comparació de respostes és {$a->actual}, s\'esperava {$a->expected}.';
$string['verify_missingattempt'] = 'Usuari {$a}: s\'esperava un intent migrat, no se n\'ha trobat cap.';
$string['verify_missingcue'] = 'Segment {$a}: falta el segment migrat.';
$string['verify_missinggap'] = 'Buit {$a}: falta el buit migrat.';
$string['verify_missinghint'] = 'Buit {$a}: la versió 1 permetia ajuda aquí, però no s\'ha migrat cap pista.';
$string['verify_orphancue'] = 'Segment {$a}: no s\'ha trobat cap segment corresponent de la versió 1.';
$string['verify_orphangap'] = 'Buit {$a}: no s\'ha trobat cap buit corresponent de la versió 1.';
$string['verify_rangemismatch'] = 'Buit {$a}: l\'interval de caràcters no coincideix amb l\'origen de la versió 1.';
$string['verify_responsecount'] = 'Usuari {$a->userid}: el nombre de respostes migrades és {$a->actual}, s\'esperava {$a->expected}.';
$string['verify_solutionmismatch'] = 'Buit {$a->gapkey}: la solució és «{$a->actual}», s\'esperava «{$a->expected}».';
$string['verify_transcriptmismatch'] = 'Segment {$a}: la transcripció no coincideix amb l\'origen de la versió 1.';
$string['verify_unexpectedhint'] = 'Buit {$a}: la versió 1 no permetia ajuda aquí, però s\'ha migrat una pista.';
