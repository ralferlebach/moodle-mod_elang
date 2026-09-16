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
 * Danish strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * No 1.3.5 translation existed to follow, so terminology comes from Moodle's own
 * Danish pack — deltager, forsøg, aktivitet, undertekster, hjælp.
 *
 * Deliberately not derived from the Swedish pack. The two languages are close
 * enough that a machine could produce something plausible, and far enough apart
 * that it would be wrong in the places that matter — so this was written from
 * the English, with Danish Moodle conventions as the reference.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Tilladte indholdssprog';
$string['allowedlanguages_desc'] = 'De indholdssprog, der tilbydes, når et videodiktat oprettes eller redigeres. Vælg ingen for at tilbyde hele sproglisten. En aktivitet beholder sit gemte sprog, også selvom du senere fjerner det her.';
$string['allowtranscriptdownload'] = 'Deltageres download af transskriptionen';
$string['allowtranscriptdownload_help'] = 'Når dette er slået til, kan deltagere downloade transskriptionens arbejdsark med hvert hul skjult som PDF, Word, OpenDocument eller tekst.

Det er slået fra som standard. Undervisere med tilladelse kan altid downloade transskriptionen, uanset denne indstilling.';
$string['allowtranscriptdownload_label'] = 'Deltagere må downloade arbejdsarket';
$string['completiondetail_completionfinishattempt'] = 'Afslutte et forsøg';
$string['completionfinishattempt'] = 'Deltageren skal afslutte et forsøg';
$string['cuepausemode'] = 'Pause ved slutningen af en undertekst';
$string['cuepausemode_auto'] = 'Automatisk';
$string['cuepausemode_help'] = 'Om mediet holder pause ved slutningen af en undertekst.

* Automatisk — afspilningen fortsætter og holder kun pause ved slutningen af en undertekst, så længe der arbejdes på netop den undertekst, altså efter et klik på den eller på et af dens huller, eller når tastaturfokus står i et af dem.
* Stop ved hver ubesvaret undertekst — afspilningen holder pause ved slutningen af hver undertekst, der stadig har et tomt hul, og venter på at blive genoptaget.
* Stop aldrig — afspilningen kører til mediets slutning.

Ingen af de to første holder pause ved en undertekst, hvor alle huller er udfyldt: det er færdigt arbejde, og en pause dér ville kræve et tastetryk uden virkning. Det betyder også, at en anden gennemgang af en øvelse kun holder pause, hvor der stadig mangler noget.';
$string['cuepausemode_nostop'] = 'Stop aldrig';
$string['cuepausemode_stop'] = 'Stop ved hver ubesvaret undertekst';
$string['editcontent'] = 'Rediger indhold';
$string['editor_addcue'] = 'Tilføj segment';
$string['editor_addgap'] = 'Opret et hul ud fra markeringen';
$string['editor_addhint'] = 'Tilføj hjælp';
$string['editor_addvariant'] = 'Tilføj variant';
$string['editor_advanced'] = 'Avancerede indstillinger';
$string['editor_algoexact'] = 'Nøjagtigt match';
$string['editor_algorithm'] = 'Sammenligning af svar';
$string['editor_algowordrecognized'] = 'Accepter nærliggende svar';
$string['editor_answers'] = 'Accepterede varianter';
$string['editor_autosaved'] = 'Alle ændringer er gemt.';
$string['editor_autosaveerror'] = 'Automatisk lagring mislykkedes — brug Gem for at prøve igen.';
$string['editor_captureend'] = 'Sæt slutningen ud fra afspilningen';
$string['editor_capturestart'] = 'Sæt starten ud fra afspilningen';
$string['editor_cueactions'] = 'Handlinger for segmentet';
$string['editor_cuecount'] = 'Segmenter: {$a}';
$string['editor_cuenotsaved'] = 'Ikke gemt';
$string['editor_currentmedia'] = 'Nuværende medie:';
$string['editor_deletecue'] = 'Slet segment';
$string['editor_deletegap'] = 'Slet hul';
$string['editor_emptytranscript'] = '(ingen tekst endnu)';
$string['editor_endtime'] = 'Sluttidspunkt';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Huller: {$a}';
$string['editor_gaprange'] = 'Hullets placering (tegn)';
$string['editor_gotomedia'] = 'Gå til Medier';
$string['editor_heading'] = 'Rediger undertekster og huller';
$string['editor_hints'] = 'Hjælp';
$string['editor_hinttext'] = 'Hjælpetekst';
$string['editor_hinttype'] = 'Type';
$string['editor_hinttype_firstletter'] = 'Første bogstav';
$string['editor_hinttype_partial'] = 'Delvis';
$string['editor_hinttype_solution'] = 'Løsning';
$string['editor_hinttype_text'] = 'Fri tekst';
$string['editor_hinttype_translation'] = 'Oversættelse';
$string['editor_hinttype_wordlength'] = 'Ordlængde';
$string['editor_import'] = 'Importer undertekster';
$string['editor_importappend'] = 'Føj til eksisterende segmenter';
$string['editor_importapply'] = 'Importer';
$string['editor_importcancel'] = 'Annuller';
$string['editor_importcheck'] = 'Kontroller indholdet';
$string['editor_importchecking'] = 'Kontrollerer…';
$string['editor_importcuecount'] = 'Fundne segmenter';
$string['editor_importduration'] = 'Varighed';
$string['editor_importedcues'] = 'Importerede segmenter: {$a}';
$string['editor_importfilehint'] = 'Vælg en WebVTT-fil (.vtt) eller SubRip-fil (.srt) med undertekster.';
$string['editor_importformat'] = 'Format';
$string['editor_importfromfile'] = 'Upload en fil';
$string['editor_importfromtext'] = 'Indsæt tekst';
$string['editor_importgapcount'] = 'Fundne huller';
$string['editor_importhint'] = 'Indsæt WebVTT- eller SubRip-indhold, og importer det som segmenter.';
$string['editor_importparseerror'] = 'Dette indhold kunne ikke læses som WebVTT eller SubRip.';
$string['editor_importpastedtext'] = 'Indsat tekst';
$string['editor_importreaderror'] = 'Filen kunne ikke læses.';
$string['editor_importready'] = 'Klar til import';
$string['editor_importreplace'] = 'Erstat alle segmenter';
$string['editor_importreplacedcues'] = 'Segmenter erstattet; nyimporterede: {$a}';
$string['editor_importsource'] = 'Kilde';
$string['editor_importsummary'] = 'Hvad der blev fundet';
$string['editor_importtoolarge'] = 'Denne fil fylder {$a->size}; importen accepterer højst {$a->max}.';
$string['editor_importwrongtype'] = 'Vælg en undertekstfil ({$a}).';
$string['editor_insertafter'] = 'Indsæt segment efter';
$string['editor_insertbefore'] = 'Indsæt segment før';
$string['editor_invalidtime'] = 'Angiv et tidspunkt som mm:ss.SSS, for eksempel 01:05.400.';
$string['editor_linkurl'] = 'Link til opslag';
$string['editor_linkurl_help'] = 'Vises ved siden af hullet som et sted at slå ordet op. Lad feltet stå tomt, hvis der ikke skal være et link.';
$string['editor_loaderror'] = 'Editoren kunne ikke indlæses. Genindlæs siden.';
$string['editor_loading'] = 'Indlæser editoren…';
$string['editor_maxlength'] = 'Største længde';
$string['editor_maxlength_help'] = 'Begrænser, hvor meget en deltager kan skrive. 0 betyder ingen grænse.';
$string['editor_media'] = 'Medier';
$string['editor_mediafile'] = 'Uploadet fil';
$string['editor_mediakind'] = 'Medietype';
$string['editor_medianone'] = 'Ingen';
$string['editor_mediaprovider'] = 'Udbyder';
$string['editor_mediaproviderref'] = 'Udbyderens reference';
$string['editor_mediaproviderrefhint'] = 'Video-id eller link i en almindelig form (fx youtu.be/…).';
$string['editor_mediasaved'] = 'Mediet er gemt.';
$string['editor_mediaurl'] = 'Direkte medie-URL';
$string['editor_nocues'] = 'Ingen segmenter endnu. Tilføj et, eller importer undertekster.';
$string['editor_nocueselected'] = 'Vælg et segment i listen for at redigere det.';
$string['editor_nocuesmatch'] = 'Ingen segmenter passer til denne søgning.';
$string['editor_nogaps'] = 'Ingen huller';
$string['editor_nomedia'] = 'ingen';
$string['editor_nomedianotice'] = 'Tilføj først video- eller lydfilen under fanen Medier. Underteksterne times efter mediet, så editoren har brug for det, før du kan arbejde med segmenter og huller.';
$string['editor_novideotrack'] = 'Denne browser kan ikke afkode mediets videospor (kun lyden afspilles); deltagerne ville se et sort billede. Omkod filen til H.264/MP4 (for eksempel med ffmpeg eller HandBrake), og upload den igen.';
$string['editor_onboardinggaps'] = 'Markér et ord i et segment, og lav det om til et hul.';
$string['editor_onboardingimport'] = 'Importer WebVTT- eller SubRip-undertekster, eller tilføj segmenter manuelt.';
$string['editor_onboardingintro'] = 'Lav en øvelse i tre trin:';
$string['editor_onboardingmedia'] = 'Vælg et medie (upload, URL eller udbyder).';
$string['editor_onboardingtitle'] = 'Kom i gang med din øvelse';
$string['editor_onlywarnings'] = 'Kun segmenter med advarsler';
$string['editor_parsegaps'] = 'Genkend markeringer for huller: [ord] laver et hul med hjælp tilladt, {ord} et uden.';
$string['editor_penalty'] = 'Fradrag';
$string['editor_poster'] = 'Plakatbillede';
$string['editor_preview'] = 'Forhåndsvisning for deltageren';
$string['editor_problem_afterduration'] = 'Sluttidspunktet ligger efter mediets slutning.';
$string['editor_problem_endbeforestart'] = 'Sluttidspunktet er det samme som starttidspunktet eller ligger før det.';
$string['editor_problem_negativestart'] = 'Starttidspunktet ligger før optagelsens begyndelse.';
$string['editor_publish'] = 'Udgiv';
$string['editor_publishblocked'] = 'Nogle undertekster kan endnu ikke gemmes. Ret dem, før du udgiver.';
$string['editor_published'] = 'Versionen er udgivet.';
$string['editor_removehint'] = 'Fjern hjælpen';
$string['editor_removevariant'] = 'Fjern';
$string['editor_repaircue'] = 'Ret sluttidspunktet';
$string['editor_ruleapplied'] = 'Oprettede %count% huller ud fra reglen.';
$string['editor_ruleapply'] = 'Anvend %count% huller';
$string['editor_ruleerror'] = 'Hullerne kunne ikke oprettes.';
$string['editor_ruleeverynth'] = 'Hvert n-te ord';
$string['editor_rulefound'] = 'Reglen fandt %count% huller.';
$string['editor_rulegenerate'] = 'Opret huller';
$string['editor_ruleinterval'] = 'Interval (n)';
$string['editor_ruletype'] = 'Regel for huller';
$string['editor_rulewordlist'] = 'Ord der skal skjules';
$string['editor_rulewords'] = 'Ordliste';
$string['editor_save'] = 'Gem kladden';
$string['editor_saved'] = 'Kladden er gemt.';
$string['editor_savedwithproblems'] = 'Gemte det, der kunne gemmes; nogle undertekster kræver opmærksomhed.';
$string['editor_saveerror'] = 'Kladden kunne ikke gemmes.';
$string['editor_savemedia'] = 'Gem mediet';
$string['editor_saving'] = 'Gemmer…';
$string['editor_searchcues'] = 'Søg i segmenter';
$string['editor_selecttext'] = 'Markér først det ord i transskriptionen, der skal skjules.';
$string['editor_solution'] = 'Løsning';
$string['editor_starttime'] = 'Starttidspunkt';
$string['editor_transcript'] = 'Transskription';
$string['editor_unsaved'] = 'Ugemte ændringer';
$string['editor_uploadmedia'] = 'Upload mediefiler';
$string['editor_variantisregex'] = 'Behandl {$a} som et regulært udtryk';
$string['editor_variantmatching'] = 'Hvordan de accepterede varianter sammenlignes';
$string['editor_warnemptysolution'] = 'Et hul uden løsning';
$string['editor_warnnotranscript'] = 'Ingen tekst';
$string['editor_warntiming'] = 'Slutningen ligger ikke efter starten';
$string['editor_waveform'] = 'Lydkurve';
$string['elang:addinstance'] = 'Tilføje et nyt videodiktat';
$string['elang:attempt'] = 'Lave et videodiktat';
$string['elang:deleteattempts'] = 'Slette deltageres forsøg';
$string['elang:exportreports'] = 'Eksportere rapporter med personoplysninger';
$string['elang:exportsolution'] = 'Eksportere den fulde transskription med løsninger';
$string['elang:exporttranscript'] = 'Eksportere arbejdsarket som dokument';
$string['elang:manage'] = 'Oprette og redigere øvelsesindhold';
$string['elang:useregex'] = 'Bruge regulære udtryk i accepterede svar';
$string['elang:view'] = 'Se et videodiktat';
$string['elang:viewreports'] = 'Se deltagerrapporter';
$string['error_attemptnotinprogress'] = 'Dette forsøg er ikke længere i gang.';
$string['error_couldnotobtainlock'] = 'Der kunne ikke opnås en lås til denne handling. Prøv igen.';
$string['error_draftrevisionmismatch'] = 'Denne kladde er ændret, siden du indlæste den. Genindlæs den, og prøv igen.';
$string['error_duplicatecuekey'] = 'To segmenter deler nøglen »{$a}«; hvert segment skal have en unik nøgle.';
$string['error_duplicategapkey'] = 'To huller i samme segment deler nøglen »{$a}«; hvert hul skal have en unik nøgle.';
$string['error_duplicatehintlevel'] = 'Et hul har to hjælpetekster på niveau {$a}; hvert niveau skal være unikt.';
$string['error_gapnotinattemptversion'] = 'Dette hul hører ikke til øvelsesversionen for dette forsøg.';
$string['error_importnocues'] = 'Der kunne ikke læses undertekster ud af dette indhold. En WebVTT- eller SubRip-fil har en tidslinje som 00:00:01.000 --> 00:00:04.000 over hver undertekst.';
$string['error_importnotutf8'] = 'Denne fil er ikke gyldig UTF-8. Den er sandsynligvis gemt i en ældre tegnkodning — åbn den i en teksteditor, og gem den igen som UTF-8.';
$string['error_importtoolarge'] = 'Denne fil fylder {$a->size}; importen accepterer højst {$a->max}. En undertekstfil til en undervisningsoptagelse er meget mindre, så det er næppe en sådan.';
$string['error_importtoomanycues'] = 'Denne fil indeholder {$a->count} undertekster; importen accepterer højst {$a->max}.';
$string['error_invalidcuepausemode'] = 'Vælg en af de tilbudte muligheder for pause ved slutningen af en undertekst.';
$string['error_invalidgradingalgorithm'] = 'Bedømmelsesalgoritmen »{$a}« er hverken exact eller wordrecognized.';
$string['error_invalidhinttype'] = 'Hjælpetypen »{$a}« er ikke en af de tilladte typer.';
$string['error_invalidisregex'] = 'Regex-markeringen for en variant skal være 0 eller 1.';
$string['error_invalidmediakind'] = 'Den valgte medietype er ikke file, url eller provider.';
$string['error_invalidpenalty'] = 'Et fradrag for hjælp skal ligge mellem 0 og 1.';
$string['error_invalidproviderref'] = '»{$a}« er ikke et genkendt video-id eller link for denne udbyder.';
$string['error_invalidregexpattern'] = '»{$a}« er ikke et gyldigt regulært udtryk.';
$string['error_invalidsolutionavailability'] = 'Vælg en af de tilbudte muligheder for, hvornår deltagere må se transskriptionen med løsninger.';
$string['error_invalidsourceurl'] = 'Angiv en fuld adresse, der begynder med http:// eller https://, eller et YouTube- eller Vimeo-link.';
$string['error_invalidsubtitleposition'] = 'Vælg en af de tilbudte muligheder for, hvor underteksterne vises.';
$string['error_invalidv1cuejson'] = 'Dette segment fra version 1 kunne ikke behandles.';
$string['error_negativegapoffset'] = 'Et huls placering og længde må ikke være negative.';
$string['error_noaccesstoattempt'] = 'Du har ikke adgang til dette forsøg.';
$string['error_nomorehints'] = 'Der er ikke mere hjælp til dette hul.';
$string['error_nopublishedversion'] = 'Denne øvelse har endnu ikke udgivet indhold.';
$string['error_responsetoolong'] = 'Dit svar er for langt. Grænsen for dette hul er {$a} tegn.';
$string['error_solutionnotavailable'] = 'Transskriptionen med løsninger er ikke tilgængelig for dig i denne aktivitet.';
$string['error_staleattemptstate'] = 'Din visning af dette forsøg er forældet. Genindlæs den aktuelle tilstand, og prøv igen.';
$string['error_transcriptnotavailable'] = 'Der er ingen transskription at downloade i denne aktivitet.';
$string['error_unknowngaprule'] = 'Ukendt type regel for huller »{$a}«.';
$string['error_unknownmediaprovider'] = '»{$a}« er ikke en af de understøttede medieudbydere.';
$string['error_versionnotadraft'] = 'Kun en version med status kladde kan redigeres.';
$string['error_versionnotfound'] = 'Denne version af øvelsen findes ikke længere.';
$string['error_versionnotpublishable'] = 'Denne version kan ikke udgives: {$a}';
$string['export_audienceaftersubmission'] = 'Deltagere kan downloade den, når de har afsluttet et forsøg';
$string['export_audiencealways'] = 'Deltagere kan downloade den når som helst';
$string['export_audiencestaff'] = 'Kun undervisere med tilladelse — ikke tilgængelig for deltagere';
$string['export_docx'] = 'Download som Word (DOCX)';
$string['export_downloadpdf'] = 'Download PDF';
$string['export_heading'] = 'Eksporter transskriptionen';
$string['export_intro'] = 'Download transskriptionen af denne øvelse i flere formater.';
$string['export_moreformats'] = 'Flere formater';
$string['export_nocontent'] = 'Der er endnu ingen udgivet transskription at eksportere.';
$string['export_odt'] = 'Download som OpenDocument (ODT)';
$string['export_pdf'] = 'Download som PDF';
$string['export_solution'] = 'Transskription med løsninger';
$string['export_solutionhint'] = 'Den fulde tekst med løsningen på hvert hul synlig.';
$string['export_text'] = 'Download som tekst';
$string['export_versionnote'] = 'Eksporter tager udgangspunkt i den aktuelt udgivne version af denne øvelse.';
$string['export_worksheet'] = 'Arbejdsark (huller skjult)';
$string['export_worksheethint'] = 'Teksten med hvert hul skjult. Klar til at dele ud som deltagermateriale.';
$string['exporttranscript'] = 'Eksporter transskriptionen';
$string['filearea_media'] = 'Medier';
$string['filearea_poster'] = 'Plakatbillede';
$string['gradingheading'] = 'Bedømmelse af svar';
$string['import_badtiming'] = 'Tidslinjen kunne ikke læses: {$a}';
$string['import_emptytranscript'] = 'Et segment uden tekst blev sprunget over.';
$string['import_warnlinetoolong'] = 'Blok {$a->block} blev sprunget over: den indeholder en linje på mere end {$a->max} tegn, hvilket ikke er en undertekstlinje.';
$string['jarothreshold'] = 'Lighedstærskel';
$string['jarothreshold_help'] = 'For huller sat til »Accepter nærliggende svar« er dette den mindste Jaro-lighed mellem det forventede og det indtastede svar. Værdien 1 kræver et nøjagtigt match efter den sprogspecifikke normalisering; lavere værdier accepterer stadig mere afvigende stavemåder.';
$string['jarothresholdrange'] = 'Tærsklen skal ligge mellem 0 og 1.';
$string['language'] = 'Indholdets sprog';
$string['language_help'] = 'Vælg sproget for øvelsens indhold. Det styrer, hvordan svar sammenlignes, herunder store og små bogstaver og translitteration. Vælg »Generisk (ikke angivet)«, hvis der ikke skal bruges sprogspecifik behandling. Nye indholdsversioner tager udgangspunkt i denne indstilling.';
$string['language_none'] = 'Generisk (ikke angivet)';
$string['media_cuenote'] = 'Eksisterende undertekster og huller bevares, når du skifter medie. Deres tider justeres ikke, så kontroller dem bagefter i editoren.';
$string['media_current'] = 'Nuværende medie';
$string['media_heading'] = 'Medier';
$string['media_intro'] = 'Vælg den video eller lyd, som denne øvelse bygger på. Underteksterne times efter den, så det kommer først.';
$string['media_none'] = 'Der er endnu ikke angivet et medie til denne øvelse.';
$string['media_othersource'] = 'Anden kilde';
$string['media_providerhint'] = 'Genkendte udbydere: {$a}. Enhver anden adresse bruges som direkte medie-URL.';
$string['media_sourceurl'] = 'Medie-URL';
$string['media_sourceurl_help'] = 'Indsæt adressen på en video i stedet for at uploade en fil — et YouTube- eller Vimeo-link eller den direkte adresse på en mediefil.

En adresse, der angives her, erstatter en uploadet fil. Lad den stå tom for at bruge uploaden ovenfor.

En udbyders video afspilles i udbyderens egen ramme, som ikke melder sin afspilningstid. En sådan øvelse viser altid underteksterne under mediet og stopper aldrig ved slutningen af en undertekst.

**Hvor data havner.** En YouTube- eller Vimeo-ramme forbinder hver deltagers browser til det pågældende firma, som dermed modtager deltagerens IP-adresse og enhedsoplysninger. Som standard spørger øvelsen først. Har din institution sin egen medieserver — Opencast, Panopto, Kaltura eller lignende — så indsæt i stedet filens direkte adresse derfra: den behandles som en almindelig medie-URL, bevarer den valgte undertekstplacering og pauseindstilling, og ingen tredjepart er involveret.';
$string['migratev1_approvalheading'] = 'Migreret, afventer kontrol';
$string['migratev1_approvebutton'] = 'Godkend denne migrering';
$string['migratev1_approved'] = 'Videodiktatet {$a} er markeret som godkendt.';
$string['migratev1_colactivity'] = 'Aktivitet';
$string['migratev1_colalgorithm'] = 'Bedømmelsesalgoritme';
$string['migratev1_colcues'] = 'Segmenter';
$string['migratev1_colgaps'] = 'Huller';
$string['migratev1_colissues'] = 'Problemer';
$string['migratev1_collearners'] = 'Deltagere';
$string['migratev1_confirmdecommission'] = 'Dette sletter UIGENKALDELIGT de gamle tabeller fra version 1 og elang.options. Det kan ikke fortrydes. Fortsæt?';
$string['migratev1_confirmmigrate'] = 'Dette sætter en baggrundsopgave i kø, som skriver version 2-data for hver aktivitet ovenfor. Tabellerne fra version 1 og elang.options røres ikke. Fortsæt?';
$string['migratev1_decommissionblocked'] = 'Sletningen er stadig blokeret; se listen nedenfor.';
$string['migratev1_decommissionblockedintro'] = 'Sletningen er blokeret, indtil:';
$string['migratev1_decommissionbutton'] = 'Slet de gamle data fra version 1';
$string['migratev1_decommissioned'] = 'De gamle data fra version 1 er slettet.';
$string['migratev1_decommissionheading'] = 'Udfasning af data fra version 1';
$string['migratev1_decommissionready'] = 'Alle aktiviteter fra version 1 er migreret og godkendt. De gamle tabeller og elang.options kan nu slettes. Det kan ikke fortrydes.';
$string['migratev1_heading'] = 'Migrer aktiviteter fra version 1';
$string['migratev1_migratebutton'] = 'Migrer disse aktiviteter';
$string['migratev1_noissues'] = 'Ingen';
$string['migratev1_nonepending'] = 'Ingen aktiviteter fra version 1 venter på migrering.';
$string['migratev1_nonependingapproval'] = 'Ingen migrerede aktiviteter venter på kontrol.';
$string['migratev1_notablespresent'] = 'Der blev ikke fundet gamle tabeller fra version 1 på dette websted. Der er intet at migrere.';
$string['migratev1_parseerrorcount'] = 'Segmenter der ikke kunne behandles: {$a}';
$string['migratev1_pendingheading'] = 'Endnu ikke migreret';
$string['migratev1_queued'] = 'Migreringsopgaven er sat i kø. Den kører ved næste cron-kørsel eller straks via admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Kontrolleret: de migrerede data svarer til kilden fra version 1 uden afvigelser.';
$string['migratev1_verifieddiscrepancies'] = 'Kontrollen fandt afvigelser i forhold til kilden fra version 1: {$a}';
$string['migratev1_verifyfailed'] = 'Denne aktivitet kunne ikke kontrolleres: {$a}';
$string['modulename'] = 'Videodiktat';
$string['modulename_help'] = 'Aktiviteten videodiktat lader deltagere udfylde huller i tidskodede undertekster, mens de ser eller lytter til en video.

Undervisere importerer en WebVTT- eller SubRip-undertekstfil, markerer ord eller udtryk som huller og indstiller, hvor strengt svar sammenlignes. Deltagerne arbejder sig gennem transskriptionen segment for segment, beder om hjælp med fradrag og får svar med det samme.';
$string['modulenameplural'] = 'Videodiktater';
$string['nav_exportshort'] = 'Eksport';
$string['nav_media'] = 'Medier';
$string['nav_reports'] = 'Forsøg';
$string['nav_subtitles'] = 'Undertekster og huller';
$string['noinstances'] = 'Der er ingen videodiktater i dette kursus.';
$string['overview_attempts'] = 'Forsøg';
$string['playbackheading'] = 'Afspilning og undertekster';
$string['playbackoverlayhint'] = 'En undertekst lagt over billedet viser kun den undertekst, der afspilles lige nu, så afspilningen holder altid pause ved slutningen af en undertekst, der stadig har huller at udfylde. Der er intet at vælge her.';
$string['playbackproviderhint'] = 'En YouTube- eller Vimeo-video afspilles af udbyderen i udbyderens egen ramme, som ikke melder sin afspilningstid. En sådan øvelse viser altid underteksterne under mediet og stopper aldrig ved slutningen af en undertekst, uanset hvad der vælges ovenfor. Uploadede filer og direkte medie-URL\'er følger begge indstillinger.';
$string['player_check'] = 'Kontroller svaret';
$string['player_consentaccept'] = 'Indlæs videoen fra {$a}';
$string['player_consentdetail'] = 'Når videoen afspilles, forbinder din browser til {$a}. {$a} modtager din IP-adresse og oplysninger om din enhed og kan læse cookies, som allerede er sat. Der sendes intet, før du vælger at indlæse videoen.';
$string['player_consentheading'] = 'Denne video leveres af {$a}';
$string['player_exitfullscreen'] = 'Forlad fuld skærm';
$string['player_finish'] = 'Afslut forsøget';
$string['player_finished'] = 'Forsøget er afsluttet. Score: %score%%';
$string['player_finishincomplete'] = 'Tomme huller tilbage: {$a}. Afslut forsøget alligevel?';
$string['player_fullscreen'] = 'Fuld skærm';
$string['player_gaplabel'] = 'Hul %gap%';
$string['player_gaplink'] = 'Åbn linket';
$string['player_hint'] = 'Vis en hjælp';
$string['player_loaderror'] = 'Øvelsen kunne ikke indlæses. Genindlæs siden.';
$string['player_loading'] = 'Indlæser øvelsen…';
$string['player_nocontent'] = 'Der er endnu ikke udgivet øvelsesindhold. Prøv igen senere.';
$string['player_novideotrack'] = 'Din browser kan ikke vise mediets videospor; lyden afspilles alligevel. Giv din underviser besked.';
$string['player_outdatedattempt'] = 'Denne øvelse er opdateret, siden du begyndte på dette forsøg. Du fortsætter på det tidligere indhold; afslut dette forsøg for næste gang at arbejde med den opdaterede øvelse.';
$string['player_progress'] = '{$a->done} af {$a->total} huller besvaret';
$string['player_ready'] = 'Øvelsen er klar.';
$string['player_scorelabel'] = 'Score: %score%%';
$string['player_stateaccepted'] = 'Accepteret';
$string['player_statecorrect'] = 'Rigtigt';
$string['player_statehinted'] = 'Hjælp brugt';
$string['player_stateincorrect'] = 'Forkert';
$string['player_submitfailed'] = 'Dit svar kunne ikke gemmes. Prøv igen.';
$string['player_transcriptheading'] = 'Transskription';
$string['pluginadministration'] = 'Administration af videodiktatet';
$string['pluginname'] = 'Videodiktat';
$string['privacy_metadata_elang'] = 'For hver aktivitet registreringen af, hvem der godkendte den envejsmigrering af dens 1.x-indhold.';
$string['privacy_metadata_elang_attempt'] = 'For hvert forsøg på en øvelse gemmer aktiviteten, hvem der lavede det, hvornår, hvor langt det nåede, og hvordan det blev bedømt.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Hvor mange huller deltageren besvarede i dette forsøg.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Løbenummeret for dette forsøg for brugeren og aktiviteten.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Hvor mange huller der blev accepteret som rigtige i dette forsøg.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Hvor mange huller der blev besvaret med nøjagtigt tegnmatch i dette forsøg.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Hvor mange huller deltageren bad om hjælp til i dette forsøg.';
$string['privacy_metadata_elang_attempt_score'] = 'Den score, der blev opnået i dette forsøg.';
$string['privacy_metadata_elang_attempt_state'] = 'Om forsøget er i gang, afsluttet eller opgivet.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'Tidspunktet, hvor forsøget blev afsluttet.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'Tidspunktet for den seneste opdatering af forsøget.';
$string['privacy_metadata_elang_attempt_timestart'] = 'Tidspunktet, hvor forsøget blev påbegyndt.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Det samlede antal huller i den øvelsesversion, dette forsøg hører til.';
$string['privacy_metadata_elang_attempt_userid'] = 'Id for den bruger, der lavede forsøget.';
$string['privacy_metadata_elang_attempt_versionid'] = 'Den øvelsesversion, forsøget blev lavet på.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'Den bruger, der godkendte migreringen af denne aktivitet fra mod_elang 1.x. Gemmes, så godkendelsen fortsat kan spores.';
$string['privacy_metadata_elang_response'] = 'For hvert hul, en deltager besvarer inden for et forsøg, gemmer aktiviteten svarteksten og måden, den blev bedømt på.';
$string['privacy_metadata_elang_response_accepted'] = 'Om svaret blev accepteret som rigtigt for dette hul.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Det højeste hjælpeniveau, deltageren fik vist for dette hul.';
$string['privacy_metadata_elang_response_responsetext'] = 'Den tekst, deltageren skrev i dette hul.';
$string['privacy_metadata_elang_response_resultstate'] = 'Den klassificering, bedømmelsen gav dette svar (nøjagtigt, ord genkendt, forkert eller tomt).';
$string['privacy_metadata_elang_response_score'] = 'De point, dette svar bidrog med, efter et eventuelt fradrag for hjælp.';
$string['privacy_metadata_elang_response_timecreated'] = 'Tidspunktet, hvor dette svar blev indsendt første gang.';
$string['privacy_metadata_elang_response_timemodified'] = 'Tidspunktet for den seneste opdatering af dette svar.';
$string['privacy_metadata_elang_response_tries'] = 'Hvor mange gange deltageren indsendte et svar på dette hul.';
$string['privacy_metadata_elang_version'] = 'For hver indholdsversion gemmer aktiviteten, hvilken bruger der sidst ændrede den.';
$string['privacy_metadata_elang_version_usermodified'] = 'Den bruger, der sidst ændrede denne indholdsversion. Gemmes, så det kan spores, hvem der har redigeret øvelsesindholdet.';
$string['privacy_provider_externallink'] = 'Når en øvelse bygger på en YouTube- eller Vimeo-video, forbinder deltagerens browser til den udbyder, når øvelsen åbnes. Pluginnet sender selv ingenting, men forbindelsen skyldes aktiviteten. Om det overhovedet sker, afhænger af webstedets indstilling for samtykke til udbydere og af, at deltageren siger ja.';
$string['privacy_provider_ipaddress'] = 'Den IP-adresse, deltagerens browser forbinder fra.';
$string['privacy_provider_useragent'] = 'De browser- og enhedsoplysninger, browseren sender.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Spørg, før YouTube eller Vimeo indlejres';
$string['providerconsent_desc'] = 'Øvelser, der bygger på en YouTube- eller Vimeo-video, viser en meddelelse i stedet for videoen og indlejrer den først, når deltageren siger ja. Uden dette modtager udbyderen deltagerens IP-adresse og browseroplysninger, så snart siden åbnes — før nogen trykker på afspil. Slå det kun fra, hvis din institution indhenter dette samtykke et andet sted.';
$string['report_actions'] = 'Handlinger';
$string['report_answered'] = 'Besvaret';
$string['report_attemptnumber'] = 'Forsøg';
$string['report_back'] = 'Tilbage til alle forsøg';
$string['report_correct'] = 'Rigtige';
$string['report_delete'] = 'Slet';
$string['report_deleteconfirm'] = 'Slet dette forsøg og alle dets svar permanent? Det kan ikke fortrydes.';
$string['report_deleted'] = 'Forsøget er slettet.';
$string['report_exact'] = 'Nøjagtige';
$string['report_export'] = 'Eksporter';
$string['report_filterany'] = 'Alle';
$string['report_filterapply'] = 'Anvend filtre';
$string['report_filterattempt'] = 'Forsøgsnummer';
$string['report_filterfrom'] = 'Påbegyndt fra';
$string['report_filterrangeerror'] = 'Periodens slutning ligger før dens begyndelse.';
$string['report_filterreset'] = 'Ryd filtre';
$string['report_filterstate'] = 'Status';
$string['report_filterto'] = 'Påbegyndt til og med';
$string['report_filteruser'] = 'Deltager';
$string['report_finished'] = 'Afsluttede';
$string['report_heading'] = 'Forsøg';
$string['report_hinted'] = 'Med hjælp';
$string['report_hints'] = 'Hjælpeniveau';
$string['report_kpianswered'] = 'Besvaret';
$string['report_kpiattempts'] = 'Viste forsøg';
$string['report_kpiaverage'] = 'Gennemsnitlig score (afsluttede)';
$string['report_kpicorrect'] = 'Accepterede';
$string['report_kpiexact'] = 'Helt rigtige';
$string['report_kpifinished'] = 'Afsluttede';
$string['report_kpihinted'] = 'Brugte hjælp';
$string['report_kpihintedgaps'] = 'Havde brug for hjælp';
$string['report_noattempts'] = 'Ingen forsøg endnu.';
$string['report_nogaps'] = 'Den version, dette forsøg blev lavet på, har ingen huller.';
$string['report_nomatchingattempts'] = 'Ingen forsøg passer til disse filtre.';
$string['report_noresponse'] = 'Ikke besvaret';
$string['report_response'] = 'Svar';
$string['report_result'] = 'Resultat';
$string['report_result_empty'] = 'Tomt';
$string['report_result_exact'] = 'Nøjagtigt';
$string['report_result_incorrect'] = 'Forkert';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Genkendt';
$string['report_score'] = 'Score';
$string['report_solution'] = 'Løsning';
$string['report_started'] = 'Påbegyndt';
$string['report_state'] = 'Status';
$string['report_state_abandoned'] = 'Opgivet';
$string['report_state_finished'] = 'Afsluttet';
$string['report_state_inprogress'] = 'I gang';
$string['report_transcript'] = 'Transskription';
$string['report_tries'] = 'Svarforsøg';
$string['report_user'] = 'Deltager';
$string['report_view'] = 'Vis';
$string['reports'] = 'Rapporter';
$string['resetattempts'] = 'Slet alle deltageres forsøg og svar';
$string['solutionavailability'] = 'Transskription med løsninger til deltagere';
$string['solutionavailability_aftersubmission'] = 'Når forsøget er afsluttet';
$string['solutionavailability_always'] = 'Når som helst';
$string['solutionavailability_help'] = 'Hvornår deltagere må downloade den fulde transskription med løsningen på hvert hul synlig.

* Aldrig — kun undervisere kan downloade den.
* Når forsøget er afsluttet — en deltager må downloade den, når vedkommende har afsluttet et forsøg i denne aktivitet.
* Når som helst — en deltager må downloade den også før svarene gives.

Undervisere med tilladelse kan altid downloade den, uanset denne indstilling.';
$string['solutionavailability_never'] = 'Aldrig';
$string['subplugintype_elangscript'] = 'Skrifthåndtering';
$string['subplugintype_elangscript_plural'] = 'Skrifthåndteringer';
$string['subtitleposition'] = 'Visning af undertekster';
$string['subtitleposition_below'] = 'Under mediet';
$string['subtitleposition_help'] = 'Hvor de interaktive undertekster vises.

* Under mediet — hele transskriptionen står under mediet i sit eget rulleområde og følger afspilningen.
* I videoen, nederst eller øverst — kun den undertekst, der afspilles lige nu, tegnes hen over mediet.

Et medie med kun lyd har intet billede at tegne på og bruger derfor altid visningen under mediet. Selve indstillingen bevares og gælder igen, så snart aktiviteten bruger en video.';
$string['subtitleposition_overlaybottom'] = 'I videoen — nederst';
$string['subtitleposition_overlaytop'] = 'I videoen — øverst';
$string['task_migratev1activities'] = 'Migrer aktiviteter fra version 1';
$string['transcriptheading'] = 'Transskription til deltagere';
$string['validate_cueafterend'] = '{$a->where}: slutter ved {$a->endtime} ms, altså efter mediet ({$a->duration} ms). Afspilningen kan aldrig nå dertil.';
$string['validate_cueendbeforestart'] = '{$a}: slutningen ligger ikke efter starten.';
$string['validate_cuewhere'] = 'Segment {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'Løsningen til {$a} er tom.';
$string['validate_hintlevels'] = 'Hjælpeniveauerne for {$a} udgør ikke en sammenhængende række, der begynder ved 1.';
$string['validate_negativetime'] = '{$a}: starttidspunktet ligger før optagelsens begyndelse.';
$string['validate_nocues'] = 'Versionen har ingen segmenter.';
$string['validate_nogaps'] = 'Versionen har ingen huller at besvare.';
$string['validate_nonpositivelength'] = 'Længden i tegn for {$a} skal være positiv.';
$string['validate_rangeoutside'] = 'Tegnintervallet for {$a} ligger uden for den tilhørende transskription.';
$string['validate_rangeoverlap'] = 'Tegnintervallet for {$a} overlapper et andet hul.';
$string['validate_unknownalgorithm'] = 'Bedømmelsesalgoritmen »{$a->algorithm}« for {$a->where} genkendes ikke.';
$string['validate_where'] = 'hullet {$a->gapkey} i segment {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Hul {$a->gapkey}: bedømmelsesalgoritmen er »{$a->actual}«, forventet var »{$a->expected}«.';
$string['verify_attemptcount'] = 'Antallet af migrerede forsøg er {$a->actual}, forventet var {$a->expected} forskellige deltagere fra 1.x.';
$string['verify_jarothreshold'] = 'Tærsklen for sammenligning af svar er {$a->actual}, forventet var {$a->expected}.';
$string['verify_missingattempt'] = 'Bruger {$a}: et migreret forsøg var forventet, men der blev ikke fundet noget.';
$string['verify_missingcue'] = 'Segment {$a}: det migrerede segment mangler.';
$string['verify_missinggap'] = 'Hul {$a}: det migrerede hul mangler.';
$string['verify_missinghint'] = 'Hul {$a}: version 1 tillod hjælp her, men der blev ikke migreret nogen hjælp.';
$string['verify_orphancue'] = 'Segment {$a}: der blev ikke fundet et tilsvarende segment fra version 1.';
$string['verify_orphangap'] = 'Hul {$a}: der blev ikke fundet et tilsvarende hul fra version 1.';
$string['verify_rangemismatch'] = 'Hul {$a}: tegnintervallet svarer ikke til kilden fra version 1.';
$string['verify_responsecount'] = 'Bruger {$a->userid}: antallet af migrerede svar er {$a->actual}, forventet var {$a->expected}.';
$string['verify_solutionmismatch'] = 'Hul {$a->gapkey}: løsningen er »{$a->actual}«, forventet var »{$a->expected}«.';
$string['verify_transcriptmismatch'] = 'Segment {$a}: transskriptionen svarer ikke til kilden fra version 1.';
$string['verify_unexpectedhint'] = 'Hul {$a}: version 1 tillod ikke hjælp her, men der blev migreret hjælp.';
