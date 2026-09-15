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
 * Lower Sorbian strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * Written from the English source, with German consulted only for
 * comprehension — not derived from the German pack, and not derived from the
 * Upper Sorbian one either. Terminology follows the dsb column of the 25-term
 * glossary compiled against WITAJ, the Sorbisches Institut and dolnoserbski.de.
 *
 * **A full pack, deliberately, not a differential against `de`.** A differential
 * was proposed and would be smaller, but it fails on two counts. The dsb→de
 * parent chain could not be confirmed, and Moodle falls back to *English*, not
 * to the assumed parent, when `parentlanguage` is unset in AMOS. More
 * importantly, if the chain did work, every untranslated string would appear in
 * German — and an interface for a minority language that shows the majority
 * language wherever it is incomplete defeats the purpose of having it at all.
 *
 * Lower Sorbian is a separate language from Upper Sorbian, not a spelling
 * variant, and the glossary is explicit that the two must not be unified:
 *
 *   gap        hsb prózdne městno  dsb **proznotka**
 *   cue        hsb wotrězk         dsb **wótrězk**
 *   subtitle   hsb podtitul        dsb **pódtitel**, pl. pódtitele
 *   attempt    hsb pospyt          dsb **wopyt**   (wopyt means *visit* in hsb,
 *                                                   so this one cannot be
 *                                                   shared in either direction)
 *   draft      hsb naćisk          dsb **nacerjenje**
 *   solution   hsb rozrisanje      dsb **rozwězanje**
 *   answer     hsb wotmołwa        dsb **wótegrono**
 *   video      hsb widejo          dsb **wideo**
 *   audio      hsb awdijo          dsb **awdio**
 *   playback   hsb wothrawanje     dsb **wótgraśe**
 *
 * **Open decisions, marked C in the glossary**, for a native reviewer before
 * this reaches AMOS:
 *
 * 1. **Wideodiktat** — the activity name, composed here from German, which the
 *    glossary explicitly warns against. The most important open item.
 * 2. **wótlicenje dypkow** for penalty; the word family leans towards settling
 *    an account rather than deducting marks.
 * 3. **akceptěrowana warianta** for an accepted answer variant.
 * 4. **wopyt** as the Moodle label for attempt — a strong recommendation in the
 *    glossary, but flagged for native confirmation.
 *
 * Lower Sorbian has a dual, so the count-neutral phrasing adopted in the RC1
 * review is load-bearing here: "Wótrězki: {$a}" needs no agreement.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Dowólone rěcy wopśimjeśa';
$string['allowedlanguages_desc'] = 'Rěcy wopśimjeśa, kótarež se pśi napóranju abo wobźěłanju wideodiktata póbituju. Njewubjeŕśo žednu, aby se cela lisćina rěcow póbitowała. Aktiwita swóju składowanu rěc wobchowa, samo gaž ju pózdźej wótsud wótpórajośo.';
$string['allowtranscriptdownload'] = 'Ześěgnjenje transkripta pśez wobźělnikow';
$string['allowtranscriptdownload_help'] = 'Gaž jo zmóžnjone, mógu wobźělniki źěłowe łopjeno transkripta ze schowaneju kuždeju proznotku ako PDF, Word, OpenDocument abo tekst ześěgnuś.

Standardnje jo znjemóžnjone. Wucabnicki personal z pšawom móžo transkript pśecej ześěgnuś, njeglědajucy na toś to nastajenje.';
$string['allowtranscriptdownload_label'] = 'Wobźělniki směju źěłowe łopjeno ześěgnuś';
$string['completiondetail_completionfinishattempt'] = 'Wopyt dokóńcyś';
$string['completionfinishattempt'] = 'Wobźělnik musy wopyt dokóńcyś';
$string['cuepausemode'] = 'Pśestawka na kóńcu pódtitela';
$string['cuepausemode_auto'] = 'Awtomatiski';
$string['cuepausemode_help'] = 'Lěc medium na kóńcu pódtitela zastawa.

* Awtomatiski — wótgraśe póstupujo a zastawa na kóńcu pódtitela jano tak dłujko, kak se rowno na tom pódtitelu źěła, togodla pó kliknjenju na njen abo na jadnu z jogo proznotkow, abo gaž fokus tastatury w jadnej z nich stoj.
* Pśi kuždem njewótegronjonem pódtitelu zastaś — wótgraśe zastawa na kóńcu kuždego pódtitela, w kótaremž hyšći prozna proznotka jo, a caka na póstupowanje.
* Nigda njezastaś — wótgraśe běžy až do kóńca medija.

Žeden z prědnyma dwěma modusoma na pódtitelu njezastawa, kótaregož proznotki su wšykne wupołnjone: to jo gótowe źěło, a zastaśe tam by tastowe kliknjenje bźez wustatka žedało. To teke groni, až drugi pśeběg zwucowanja jano tam zastawa, źož hyšći něco felujo.';
$string['cuepausemode_nostop'] = 'Nigda njezastaś';
$string['cuepausemode_stop'] = 'Pśi kuždem njewótegronjonem pódtitelu zastaś';
$string['editcontent'] = 'Wopśimjeśe wobźěłaś';
$string['editor_addcue'] = 'Wótrězk pśidaś';
$string['editor_addgap'] = 'Proznotku z wubranki napóraś';
$string['editor_addhint'] = 'Pókiw pśidaś';
$string['editor_addvariant'] = 'Wariantu pśidaś';
$string['editor_advanced'] = 'Rozšyrjone nastajenja';
$string['editor_algoexact'] = 'Dokradne wótpowědowanje';
$string['editor_algorithm'] = 'Pśirownowanje wótegronow';
$string['editor_algowordrecognized'] = 'Pódobne wótegrona akceptěrowaś';
$string['editor_answers'] = 'Akceptěrowane warianty';
$string['editor_autosaved'] = 'Wšykne změny su składowane.';
$string['editor_autosaveerror'] = 'Awtomatiske składowanje jo se njeraźiło — wužyjśo „Składowaś“.';
$string['editor_captureend'] = 'Kóńc z wótgraśa pśewześ';
$string['editor_capturestart'] = 'Zachopjeńk z wótgraśa pśewześ';
$string['editor_cueactions'] = 'Akcije wótrězka';
$string['editor_cuecount'] = 'Wótrězki: {$a}';
$string['editor_currentmedia'] = 'Aktualny medium:';
$string['editor_deletecue'] = 'Wótrězk lašowaś';
$string['editor_deletegap'] = 'Proznotku lašowaś';
$string['editor_emptytranscript'] = '(hyšći žeden tekst)';
$string['editor_endtime'] = 'Kóńc';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Proznotki: {$a}';
$string['editor_gaprange'] = 'Pozicija proznotki (znamuška)';
$string['editor_gotomedia'] = 'K „Medije“ hyś';
$string['editor_heading'] = 'Pódtitele a proznotki wobźěłaś';
$string['editor_hints'] = 'Pókiwy';
$string['editor_hinttext'] = 'Tekst pókiwa';
$string['editor_hinttype'] = 'Typ';
$string['editor_hinttype_firstletter'] = 'Prědne pismiko';
$string['editor_hinttype_partial'] = 'Źělnje';
$string['editor_hinttype_solution'] = 'Rozwězanje';
$string['editor_hinttype_text'] = 'Lichy tekst';
$string['editor_hinttype_translation'] = 'Pśełožk';
$string['editor_hinttype_wordlength'] = 'Dłujkosć słowa';
$string['editor_import'] = 'Pódtitele importěrowaś';
$string['editor_importappend'] = 'K eksistěrujucym wótrězkam pśidaś';
$string['editor_importapply'] = 'Importěrowaś';
$string['editor_importcancel'] = 'Pśetergnuś';
$string['editor_importcheck'] = 'Wopśimjeśe kontrolěrowaś';
$string['editor_importchecking'] = 'Kontrolěrujo se…';
$string['editor_importcuecount'] = 'Namakane wótrězki';
$string['editor_importduration'] = 'Traśe';
$string['editor_importedcues'] = 'Importěrowane wótrězki: {$a}';
$string['editor_importfilehint'] = 'Wubjeŕśo dataju WebVTT (.vtt) abo SubRip (.srt) z pódtitelami.';
$string['editor_importformat'] = 'Format';
$string['editor_importfromfile'] = 'Dataju nagraś';
$string['editor_importfromtext'] = 'Tekst zasajźiś';
$string['editor_importgapcount'] = 'Namakane proznotki';
$string['editor_importhint'] = 'Zasajźćo wopśimjeśe WebVTT abo SubRip a importěrujśo jo ako wótrězki.';
$string['editor_importparseerror'] = 'To wopśimjeśe njedajo se ako WebVTT abo SubRip cytaś.';
$string['editor_importpastedtext'] = 'Zasajźony tekst';
$string['editor_importreaderror'] = 'Dataja njedajo se cytaś.';
$string['editor_importready'] = 'Gótowe za import';
$string['editor_importreplace'] = 'Wšykne wótrězki wuměniś';
$string['editor_importreplacedcues'] = 'Wótrězki wuměnjone; nowo importěrowane: {$a}';
$string['editor_importsource'] = 'Žrědło';
$string['editor_importsummary'] = 'Což jo se namakało';
$string['editor_importtoolarge'] = 'Ta dataja ma {$a->size}; import akceptěrujo maksimalnje {$a->max}.';
$string['editor_importwrongtype'] = 'Wubjeŕśo dataju z pódtitelami ({$a}).';
$string['editor_insertafter'] = 'Wótrězk pó tom zasajźiś';
$string['editor_insertbefore'] = 'Wótrězk pśed tym zasajźiś';
$string['editor_invalidtime'] = 'Zapódajśo cas w formje mm:ss.SSS, na pśikład 01:05.400.';
$string['editor_linkurl'] = 'Wótkaz za pytanje';
$string['editor_linkurl_help'] = 'Pokazujo se pódla proznotki ako městno, źož dajo se słowo pytaś. Wóstajśo prozne, jolic žeden wótkaz njocośo.';
$string['editor_loaderror'] = 'Editor njedajo se zacytaś. Zacytajśo bok znowa.';
$string['editor_loading'] = 'Editor se zacytujo…';
$string['editor_maxlength'] = 'Maksimalna dłujkosć';
$string['editor_maxlength_help'] = 'Wobgranicujo, wjele smějo wobźělnik zapódaś. 0 groni bźez wobgranicowanja.';
$string['editor_media'] = 'Medije';
$string['editor_mediafile'] = 'Nagrata dataja';
$string['editor_mediakind'] = 'Typ medija';
$string['editor_medianone'] = 'Žeden';
$string['editor_mediaprovider'] = 'Póbitowaŕ';
$string['editor_mediaproviderref'] = 'Póśěg póbitowarja';
$string['editor_mediaproviderrefhint'] = 'ID abo wótkaz wideo w zwuconej formje (na pś. youtu.be/…).';
$string['editor_mediasaved'] = 'Medium jo składowany.';
$string['editor_mediaurl'] = 'Direktny URL medija';
$string['editor_nocues'] = 'Hyšći žedne wótrězki. Pśidajśo jaden abo importěrujśo pódtitele.';
$string['editor_nocueselected'] = 'Wubjeŕśo wótrězk z lisćiny, aby jen wobźěłał.';
$string['editor_nocuesmatch'] = 'Žeden wótrězk tomu pytanjeju njewótpowědujo.';
$string['editor_nogaps'] = 'Žedne proznotki';
$string['editor_nomedia'] = 'žeden';
$string['editor_nomedianotice'] = 'Pśidajśo nejpjerwjej wideo- abo awdiodataju w rejtarku „Medije“. Pódtitele se na medium casuju, togodla editor jen trjeba, nježli až móžośo z wótrězkami a proznotkami źěłaś.';
$string['editor_novideotrack'] = 'Toś ten wobglědowak njamóžo wideosled toś togo medija dekoděrowaś (jano zuk se wótgrawa); wobźělniki by carny wobraz wiźeli. Koděrujśo dataju znowa ako H.264/MP4 (na pśikład z ffmpeg abo HandBrake) a nagrajśo ju znowa.';
$string['editor_onboardinggaps'] = 'Wubjeŕśo słowo we wótrězku a napórajśo z njogo proznotku.';
$string['editor_onboardingimport'] = 'Importěrujśo pódtitele WebVTT abo SubRip, abo pśidajśo wótrězki z ruku.';
$string['editor_onboardingintro'] = 'Napórajśo zwucowanje w tśich kšacach:';
$string['editor_onboardingmedia'] = 'Wubjeŕśo medium (nagraśe, URL abo póbitowaŕ).';
$string['editor_onboardingtitle'] = 'Zachopśo swójo zwucowanje';
$string['editor_onlywarnings'] = 'Jano wótrězki z warnowanjami';
$string['editor_parsegaps'] = 'Znamjenja proznotkow spóznaś: [słowo] napórajo proznotku z dowólonymi pókiwami, {słowo} bźez nich.';
$string['editor_penalty'] = 'Wótlicenje dypkow';
$string['editor_poster'] = 'Titelny wobraz';
$string['editor_preview'] = 'Pśeglěd wobźělnika';
$string['editor_publish'] = 'Wózjawiś';
$string['editor_published'] = 'Wersija jo wózjawjona.';
$string['editor_removehint'] = 'Pókiw wótpóraś';
$string['editor_removevariant'] = 'Wótpóraś';
$string['editor_ruleapplied'] = 'Pó pšawidle su se proznotki napórali: %count%.';
$string['editor_ruleapply'] = 'Proznotki nałožyś: %count%';
$string['editor_ruleerror'] = 'Proznotki njedaju se napóraś.';
$string['editor_ruleeverynth'] = 'Kužde n-te słowo';
$string['editor_rulefound'] = 'Pšawidło jo proznotki namakało: %count%.';
$string['editor_rulegenerate'] = 'Proznotki napóraś';
$string['editor_ruleinterval'] = 'Interwal (n)';
$string['editor_ruletype'] = 'Pšawidło proznotkow';
$string['editor_rulewordlist'] = 'Słowa, kótarež se schowaju';
$string['editor_rulewords'] = 'Lisćina słowow';
$string['editor_save'] = 'Nacerjenje składowaś';
$string['editor_saved'] = 'Nacerjenje jo składowane.';
$string['editor_saveerror'] = 'Nacerjenje njedajo se składowaś.';
$string['editor_savemedia'] = 'Medium składowaś';
$string['editor_saving'] = 'Składujo se…';
$string['editor_searchcues'] = 'We wótrězkach pytaś';
$string['editor_selecttext'] = 'Wubjeŕśo nejpjerwjej słowo w transkrypśe, kótarež se ma schowaś.';
$string['editor_solution'] = 'Rozwězanje';
$string['editor_starttime'] = 'Zachopjeńk';
$string['editor_transcript'] = 'Transkript';
$string['editor_unsaved'] = 'Njeskładowane změny';
$string['editor_uploadmedia'] = 'Medijowe dataje nagraś';
$string['editor_variantisregex'] = '{$a} ako regularny wuraz wobchadaś';
$string['editor_variantmatching'] = 'Kak se akceptěrowane warianty pśirownuju';
$string['editor_warnemptysolution'] = 'Proznotka bźez rozwězanja';
$string['editor_warnnotranscript'] = 'Žeden tekst';
$string['editor_warntiming'] = 'Kóńc njejo pó zachopjeńku';
$string['editor_waveform'] = 'Zukowa krivka';
$string['elang:addinstance'] = 'Nowy wideodiktat pśidaś';
$string['elang:attempt'] = 'Wideodiktat wugbaś';
$string['elang:deleteattempts'] = 'Wopyty wobźělnikow lašowaś';
$string['elang:exportreports'] = 'Rozpšawy z wósobinskimi datami eksportěrowaś';
$string['elang:exportsolution'] = 'Dopołny transkript z rozwězanjami eksportěrowaś';
$string['elang:exporttranscript'] = 'Źěłowe łopjeno ako dokument eksportěrowaś';
$string['elang:manage'] = 'Wopśimjeśe zwucowanjow napóraś a wobźěłaś';
$string['elang:useregex'] = 'Regularne wuraze w akceptěrowanych wótegronach wužywaś';
$string['elang:view'] = 'Wideodiktat se woglědaś';
$string['elang:viewreports'] = 'Rozpšawy wobźělnikow se woglědaś';
$string['error_attemptnotinprogress'] = 'Toś ten wopyt južo njeběžy.';
$string['error_couldnotobtainlock'] = 'Za toś tu akciju njedajo se zastajenje dostaś. Wopytajśo hyšći raz.';
$string['error_draftrevisionmismatch'] = 'Toś to nacerjenje jo se změniło, pótom až sćo jo zacytał. Zacytajśo jo znowa a wopytajśo hyšći raz.';
$string['error_duplicatecuekey'] = 'Dwa wótrězka matej samski kluc „{$a}“; kuždy wótrězk trjeba jadnoznamjenity kluc.';
$string['error_duplicategapkey'] = 'Dwě proznotce w samskem wótrězku matej samski kluc „{$a}“; kužda proznotka trjeba jadnoznamjenity kluc.';
$string['error_duplicatehintlevel'] = 'Proznotka ma dwa pókiwa na rowninje {$a}; kužda rownina musy jadnoznamjenita byś.';
$string['error_gapnotinattemptversion'] = 'Toś ta proznotka k wersiji zwucowanja toś togo wopyta njesłuša.';
$string['error_importnocues'] = 'Z toś togo wopśimjeśa njedaju se žedne pódtitele cytaś. Dataja WebVTT abo SubRip ma nad kuždym pódtitelom casowu smužku, na pśikład 00:00:01.000 --> 00:00:04.000.';
$string['error_importnotutf8'] = 'Toś ta dataja płaśiwy UTF-8 njejo. Nejskerjej jo se ze starstym koděrowanim składowała — wócyńśo ju w tekstowem editorje a składujśo ju znowa ako UTF-8.';
$string['error_importtoolarge'] = 'Ta dataja ma {$a->size}; import akceptěrujo maksimalnje {$a->max}. Dataja z pódtitelami za nagraśe góźiny jo wjele mjeńša, togodla to nejskerjej žedna njejo.';
$string['error_importtoomanycues'] = 'Ta dataja wopśimujo pódtitele: {$a->count}; import akceptěrujo maksimalnje {$a->max}.';
$string['error_invalidcuepausemode'] = 'Wubjeŕśo jadnu z póbitowanych móžnosćow za pśestawku na kóńcu pódtitela.';
$string['error_invalidgradingalgorithm'] = 'Algoritmus pógódnośenja „{$a}“ ani exact ani wordrecognized njejo.';
$string['error_invalidhinttype'] = 'Typ pókiwa „{$a}“ k dowólonym typam njesłuša.';
$string['error_invalidisregex'] = 'Znamje regularnego wuraza warianty musy 0 abo 1 byś.';
$string['error_invalidmediakind'] = 'Wubrany typ medija ani file, ani url, ani provider njejo.';
$string['error_invalidpenalty'] = 'Wótlicenje dypkow za pókiw musy mjazy 0 a 1 byś.';
$string['error_invalidproviderref'] = '„{$a}“ njejo spóznany ID abo wótkaz wideo za toś togo póbitowarja.';
$string['error_invalidregexpattern'] = '„{$a}“ płaśiwy regularny wuraz njejo.';
$string['error_invalidsolutionavailability'] = 'Wubjeŕśo jadnu z póbitowanych móžnosćow za to, ga směju wobźělniki transkript z rozwězanjami wiźeś.';
$string['error_invalidsourceurl'] = 'Zapódajśo dopołnu adresu, kótaraž se z http:// abo https:// zachopina, abo wótkaz YouTube abo Vimeo.';
$string['error_invalidsubtitleposition'] = 'Wubjeŕśo jadnu z póbitowanych móžnosćow za poziciju pódtitelow.';
$string['error_invalidv1cuejson'] = 'Toś ten wótrězk z wersije 1 njedajo se pśeźěłaś.';
$string['error_negativegapoffset'] = 'Pozicija a dłujkosć proznotki njesmějotej negatiwnej byś.';
$string['error_noaccesstoattempt'] = 'Njamaśo pśistup k toś tomu wopytoju.';
$string['error_nomorehints'] = 'Za toś tu proznotku wěcej pókiwow njejo.';
$string['error_nopublishedversion'] = 'Toś to zwucowanje hyšći wózjawjone wopśimjeśe njama.';
$string['error_responsetoolong'] = 'Wašo wótegrono jo pśedłujke. Maksimum za toś tu proznotku jo {$a} znamuškow.';
$string['error_solutionnotavailable'] = 'Transkript z rozwězanjami w toś tej aktiwiśe za was k dispoziciji njestoj.';
$string['error_staleattemptstate'] = 'Wašo wiźenje toś togo wopyta jo zestarjete. Zacytajśo aktualny staw znowa a wopytajśo hyšći raz.';
$string['error_transcriptnotavailable'] = 'W toś tej aktiwiśe žeden transkript za ześěgnjenje njejo.';
$string['error_unknowngaprule'] = 'Njeznaty typ pšawidła proznotkow „{$a}“.';
$string['error_unknownmediaprovider'] = '„{$a}“ k pódpěranym medijowym póbitowarjam njesłuša.';
$string['error_versionnotadraft'] = 'Jano wersija w stawje nacerjenja dajo se wobźěłaś.';
$string['error_versionnotfound'] = 'Toś ta wersija zwucowanja južo njeeksistěrujo.';
$string['error_versionnotpublishable'] = 'Toś ta wersija njedajo se wózjawiś: {$a}';
$string['export_audienceaftersubmission'] = 'Wobźělniki mógu to ześěgnuś, gaž su wopyt dokóńcyli';
$string['export_audiencealways'] = 'Wobźělniki mógu to kuždy cas ześěgnuś';
$string['export_audiencestaff'] = 'Jano wucabnicki personal z pšawom — wobźělnikam se njepóbituje';
$string['export_docx'] = 'Ako Word (DOCX) ześěgnuś';
$string['export_downloadpdf'] = 'PDF ześěgnuś';
$string['export_heading'] = 'Transkript eksportěrowaś';
$string['export_intro'] = 'Ześěgniśo transkript toś togo zwucowanja we wěcejnych formatach.';
$string['export_moreformats'] = 'Dalšne formaty';
$string['export_nocontent'] = 'Hyšći žeden wózjawjony transkript za eksport njejo.';
$string['export_odt'] = 'Ako OpenDocument (ODT) ześěgnuś';
$string['export_pdf'] = 'Ako PDF ześěgnuś';
$string['export_solution'] = 'Transkript z rozwězanjami';
$string['export_solutionhint'] = 'Dopołny tekst, w kótaremž jo rozwězanje kuždeje proznotki widobne.';
$string['export_text'] = 'Ako tekst ześěgnuś';
$string['export_versionnote'] = 'Eksporty bazěruju na tuchylu wózjawjonej wersiji toś togo zwucowanja.';
$string['export_worksheet'] = 'Źěłowe łopjeno (proznotki schowane)';
$string['export_worksheethint'] = 'Tekst ze schowaneju kuždeju proznotku. Gótowy za rozdźělenje ako material za wobźělnikow.';
$string['exporttranscript'] = 'Transkript eksportěrowaś';
$string['filearea_media'] = 'Medije';
$string['filearea_poster'] = 'Titelny wobraz';
$string['gradingheading'] = 'Pógódnośenje wótegronow';
$string['import_badtiming'] = 'Casowa smužka njedajo se cytaś: {$a}';
$string['import_emptytranscript'] = 'Wótrězk bźez teksta jo se pśeskócył.';
$string['import_warnlinetoolong'] = 'Blok {$a->block} jo se pśeskócył: wopśimujo smužku dlejšu ako {$a->max} znamuškow, a to smužka pódtitela njejo.';
$string['jarothreshold'] = 'Proch pódobnosći';
$string['jarothreshold_help'] = 'Za proznotki, kótarež su na „Pódobne wótegrona akceptěrowaś“ nastajone, jo to nejmjeńša pódobnosć Jaro mjazy wócakanym a zapódanym wótegronom. Gódnota 1 žeda dokradne wótpowědowanje pó normalizěrowanju, kótarež jo rěcy swójske; nižše gódnoty akceptěruju pisanja, kótarež se wěcej rozeznawaju.';
$string['jarothresholdrange'] = 'Proch musy mjazy 0 a 1 byś.';
$string['language'] = 'Rěc wopśimjeśa';
$string['language_help'] = 'Wubjeŕśo rěc wopśimjeśa zwucowanja. Wóna póstaja, kak se wótegrona pśirownuju, mjazy drugim wjelikopisanje a transliteraciju. Wubjeŕśo „Powšykna (njepódana)“, jolic se rěcy swójske pśeźěłanje njama wužywaś. Nowe wersije wopśimjeśa z toś togo nastajenja wuchadaju.';
$string['language_none'] = 'Powšykna (njepódana)';
$string['media_cuenote'] = 'Eksistěrujuce pódtitele a proznotki se wobchowaju, gaž medium změnijośo. Jich casy se njepśiměriju, togodla kontrolěrujśo je pótom w editorje.';
$string['media_current'] = 'Aktualny medium';
$string['media_heading'] = 'Medije';
$string['media_intro'] = 'Wubjeŕśo wideo abo zuk, na kótaremž toś to zwucowanje bazěrujo. Pódtitele se na njen casuju, togodla to prědny kšac jo.';
$string['media_none'] = 'Za toś to zwucowanje hyšći žeden medium póstajony njejo.';
$string['media_othersource'] = 'Druge žrědło';
$string['media_providerhint'] = 'Spóznane póbitowarje: {$a}. Kužda druga adresa se ako direktny URL medija wužywa.';
$string['media_sourceurl'] = 'URL medija';
$string['media_sourceurl_help'] = 'Zasajźćo adresu wideo město togo, aby dataju nagrał — wótkaz YouTube abo Vimeo, abo direktnu adresu medijoweje dataje.

Adresa, kótaraž se how zapódawa, narownajo nagratu dataju. Wóstajśo ju prozne, aby nagraśe górjejce wužywał.

Wideo póbitowarja se we wobłuku samogo póbitowarja wótgrawa, kótaryž cas wótgraśa njezdźěla. Take zwucowanje pokazujo pódtitele pśecej pód medijom a nigda na kóńcu pódtitela njezastawa.

**Źož daty dochadaju.** Wobłuk YouTube abo Vimeo zwězujo wobglědowak kuždego wobźělnika z tym pśedewześim, kótarež tak jogo IP-adresu a daty rěda dostawa. Standardnje se zwucowanje pjerwjej pšaša. Jolic waša institucija swójski medijowy serwer ma — Opencast, Panopto, Kaltura abo pódobny — zasajźćo město togo direktnu adresu dataje wótsud: wóna se ako zwucony URL medija wobchada, wobchowa wubranu poziciju pódtitelow a nastajenje pśestawki, a žeden tśeśi njejo zapśěgnjony.';
$string['migratev1_approvalheading'] = 'Pśenjasone, caka na kontrolu';
$string['migratev1_approvebutton'] = 'Toś to pśenjasenje wobkšuśiś';
$string['migratev1_approved'] = 'Wideodiktat {$a} jo se ako wobkšuśony markěrował.';
$string['migratev1_colactivity'] = 'Aktiwita';
$string['migratev1_colalgorithm'] = 'Algoritmus pógódnośenja';
$string['migratev1_colcues'] = 'Wótrězki';
$string['migratev1_colgaps'] = 'Proznotki';
$string['migratev1_colissues'] = 'Problemy';
$string['migratev1_collearners'] = 'Wobźělniki';
$string['migratev1_confirmdecommission'] = 'Toś ta akcija lašujo NJEWÓTWÓLIWJENJE stare tabele wersije 1 a słupk elang.options. Njedajo se anulěrowaś. Dalej?';
$string['migratev1_confirmmigrate'] = 'Toś ta akcija staja nadawk do rědownje, kótaryž za kuždu górjejce naliconu aktiwitu daty wersije 2 napišo. Tabele wersije 1 a elang.options wóstanu njedotknjone. Dalej?';
$string['migratev1_decommissionblocked'] = 'Lašowanje jo dalej zablokěrowane; glejśo lisćinu dołojce.';
$string['migratev1_decommissionblockedintro'] = 'Lašowanje jo zablokěrowane, dokulaž:';
$string['migratev1_decommissionbutton'] = 'Stare daty wersije 1 lašowaś';
$string['migratev1_decommissioned'] = 'Stare daty wersije 1 su lašowane.';
$string['migratev1_decommissionheading'] = 'Wótstajenje datow wersije 1';
$string['migratev1_decommissionready'] = 'Wšykne aktiwity wersije 1 su pśenjasone a wobkšuśone. Stare tabele a elang.options daju se něnto lašowaś. Toś ta akcija se njedajo anulěrowaś.';
$string['migratev1_heading'] = 'Aktiwity wersije 1 pśenjasć';
$string['migratev1_migratebutton'] = 'Toś te aktiwity pśenjasć';
$string['migratev1_noissues'] = 'Žedne';
$string['migratev1_nonepending'] = 'Žedne aktiwity wersije 1 na pśenjasenje njecakaju.';
$string['migratev1_nonependingapproval'] = 'Žedne pśenjasone aktiwity na kontrolu njecakaju.';
$string['migratev1_notablespresent'] = 'Na toś tom boku njejsu se stare tabele wersije 1 namakali. Nic za pśenjasenje njejo.';
$string['migratev1_parseerrorcount'] = 'Wótrězki, kótarež njedaju se pśeźěłaś: {$a}';
$string['migratev1_pendingheading'] = 'Hyšći njepśenjasone';
$string['migratev1_queued'] = 'Nadawk pśenjasenja jo w rědowni. Wugbajo se pśi pśiducem běgu crona abo ned pśez admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Kontrolěrowane: pśenjasone daty ze žrědłom wersije 1 bźez wótchylenjow wótpowěduju.';
$string['migratev1_verifieddiscrepancies'] = 'Kontrola jo wótchylenja napśeśiwo žrědłoju wersije 1 namakała: {$a}';
$string['migratev1_verifyfailed'] = 'Toś ta aktiwita njedajo se kontrolěrowaś: {$a}';
$string['modulename'] = 'Wideodiktat';
$string['modulename_help'] = 'Aktiwita Wideodiktat wobźělnikam zmóžnja, proznotki w casowanych pódtitelach wupołniś, mjaztym až wideo se woglěduju abo słuchaju.

Wucabniki importěruju dataju z pódtitelami WebVTT abo SubRip, markěruju słowa abo wobrośa ako proznotki a nastaju, kak wóstro se wótegrona pśirownuju. Wobźělniki źěłaju transkript wótrězk pó wótrězku, pšose wó pógódnośone pókiwy a dostawaju ned slědkgroniło.';
$string['modulenameplural'] = 'Wideodiktaty';
$string['nav_exportshort'] = 'Eksport';
$string['nav_media'] = 'Medije';
$string['nav_reports'] = 'Wopyty';
$string['nav_subtitles'] = 'Pódtitele a proznotki';
$string['noinstances'] = 'W toś tom kursu žedne wideodiktaty njejsu.';
$string['overview_attempts'] = 'Wopyty';
$string['playbackheading'] = 'Wótgraśe a pódtitele';
$string['playbackoverlayhint'] = 'Pódtitel, kótaryž se na wobraz kładujo, pokazujo jano ten pódtitel, kótaryž rowno běžy. Togodla wótgraśe pśecej na kóńcu pódtitela zastawa, w kótaremž hyšći proznotki za wupołnjenje su. How nic za wuběranje njejo.';
$string['playbackproviderhint'] = 'Wideo YouTube abo Vimeo wótgrawa póbitowaŕ w swójom swójskem wobłuku, kótaryž cas wótgraśa njezdźěla. Take zwucowanje pokazujo pódtitele pśecej pód medijom a nigda na kóńcu pódtitela njezastawa, njeglědajucy na to, což jo górjejce wubrane. Nagrate dataje a direktne URL medijow wobej nastajeni respektěrujotej.';
$string['player_check'] = 'Wótegrono kontrolěrowaś';
$string['player_consentaccept'] = 'Wideo z {$a} zacytaś';
$string['player_consentdetail'] = 'Wótgraśe zwězujo waš wobglědowak z {$a}. {$a} dostawa wašu IP-adresu a informacije wó wašom rěźe a móžo cookieje cytaś, kótarež jo južo stajił. Nic se njesćelo, dokulaž sebje wy zacytanje wideo njewuzwólijośo.';
$string['player_consentheading'] = 'Toś to wideo póbitujo {$a}';
$string['player_exitfullscreen'] = 'Połnu wobrazowku spušćiś';
$string['player_finish'] = 'Wopyt dokóńcyś';
$string['player_finished'] = 'Wopyt jo dokóńcony. Licba dypkow: %score%%';
$string['player_finishincomplete'] = 'Hyšći prozne proznotki: {$a}. Wopyt weto dokóńcyś?';
$string['player_fullscreen'] = 'Połna wobrazowka';
$string['player_gaplabel'] = 'Proznotka %gap%';
$string['player_gaplink'] = 'Wótkaz wócyniś';
$string['player_hint'] = 'Pókiw pokazaś';
$string['player_loaderror'] = 'Zwucowanje njedajo se zacytaś. Zacytajśo bok znowa.';
$string['player_loading'] = 'Zwucowanje se zacytujo…';
$string['player_nocontent'] = 'Hyšći žedno wopśimjeśe zwucowanja wózjawjone njejo. Pśiźćo pózdźej.';
$string['player_novideotrack'] = 'Waš wobglědowak njamóžo wideosled toś togo medija pokazaś; zuk se weto wótgrawa. Powěsćo to swójomu wucabnikoju.';
$string['player_outdatedattempt'] = 'Toś to zwucowanje jo se aktualizěrowało, pótom až sćo toś ten wopyt zachopił. Póstupujośo ze staršym wopśimjeśim; dokóńcćo toś ten wopyt, aby pśiducy raz z aktualizěrowanym zwucowanim źěłał.';
$string['player_progress'] = 'Wótegronjone: {$a->done} z {$a->total} proznotkow';
$string['player_ready'] = 'Zwucowanje jo gótowe.';
$string['player_scorelabel'] = 'Licba dypkow: %score%%';
$string['player_stateaccepted'] = 'Akceptěrowane';
$string['player_statecorrect'] = 'Pšawje';
$string['player_statehinted'] = 'Pókiw wužyty';
$string['player_stateincorrect'] = 'Wopak';
$string['player_submitfailed'] = 'Wašo wótegrono njedajo se składowaś. Wopytajśo hyšći raz.';
$string['player_transcriptheading'] = 'Transkript';
$string['pluginadministration'] = 'Zastojanje wideodiktata';
$string['pluginname'] = 'Wideodiktat';
$string['privacy_metadata_elang'] = 'Za kuždu aktiwitu zapisk wó tom, chto jo jadnosměrne pśenjasenje jeje wopśimjeśa z 1.x wobkšuśił.';
$string['privacy_metadata_elang_attempt'] = 'Za kuždy wopyt w zwucowanju aktiwita składujo, chto jo jen wugbał, ga, kak daloko jo dojšeł a kak jo se pógódnośił.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Wjele proznotkow jo wobźělnik w toś tom wopyśe wótegronił.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Běžne cysło toś togo wopyta za wužywarja a aktiwitu.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Wjele proznotkow jo se w toś tom wopyśe ako pšawe akceptěrowało.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Wjele proznotkow jo se w toś tom wopyśe z dokradnym wótpowědowanim znamuškow wótegroniło.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Za wjele proznotkow jo wobźělnik w toś tom wopyśe pókiw póžedał.';
$string['privacy_metadata_elang_attempt_score'] = 'Licba dypkow, dojśpěta w toś tom wopyśe.';
$string['privacy_metadata_elang_attempt_state'] = 'Lěc wopyt běžy, jo dokóńcony abo wóstajony.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'Cas, gaž jo se wopyt dokóńcył.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'Cas slědnego aktualizěrowanja wopyta.';
$string['privacy_metadata_elang_attempt_timestart'] = 'Cas, gaž jo wopyt zachopił.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Cełkowna licba proznotkow we wersiji zwucowanja, ku kótarejž toś ten wopyt słuša.';
$string['privacy_metadata_elang_attempt_userid'] = 'ID wužywarja, kótaryž jo wopyt wugbał.';
$string['privacy_metadata_elang_attempt_versionid'] = 'Wersija zwucowanja, na kótarejž jo se toś ten wopyt wugbał.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'Wužywaŕ, kótaryž jo pśenjasenje toś teje aktiwity z mod_elang 1.x wobkšuśił. Składujo se, aby wobkšuśenje slědujobne wóstało.';
$string['privacy_metadata_elang_response'] = 'Za kuždu proznotku, kótaruž wobźělnik we wopyśe wótegroni, aktiwita tekst wótegrona a jogo pógódnośenje składujo.';
$string['privacy_metadata_elang_response_accepted'] = 'Lěc jo se wótegrono za toś tu proznotku ako pšawe akceptěrowało.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Nejwušej rownina pókiwa, kótaraž jo se wobźělnikoju za toś tu proznotku pokazała.';
$string['privacy_metadata_elang_response_responsetext'] = 'Tekst, kótaryž jo wobźělnik do toś teje proznotki zapisał.';
$string['privacy_metadata_elang_response_resultstate'] = 'Zaklasifikěrowanje, kótarež jo pógódnośenje toś tomu wótegronoju dało (dokradne, spóznane słowo, wopacne abo prozne).';
$string['privacy_metadata_elang_response_score'] = 'Dypki, kótarež jo toś to wótegrono pó móžnem wótlicenju za pókiw pśinjasło.';
$string['privacy_metadata_elang_response_timecreated'] = 'Cas prědnego pósłanja toś togo wótegrona.';
$string['privacy_metadata_elang_response_timemodified'] = 'Cas slědnego aktualizěrowanja toś togo wótegrona.';
$string['privacy_metadata_elang_response_tries'] = 'Kak cesto jo wobźělnik za toś tu proznotku wótegrono pósłał.';
$string['privacy_metadata_elang_version'] = 'Za kuždu wersiju wopśimjeśa aktiwita składujo, kótary wužywaŕ jo ju ako slědny změnił.';
$string['privacy_metadata_elang_version_usermodified'] = 'Wužywaŕ, kótaryž jo toś tu wersiju wopśimjeśa ako slědny změnił. Składujo se, aby se slědowaś dało, chto jo wopśimjeśe zwucowanja wobźěłał.';
$string['privacy_provider_externallink'] = 'Gaž zwucowanje na wideo YouTube abo Vimeo bazěrujo, zwězujo jogo wócynjenje wobglědowak wobźělnika z tym póbitowarjom. Tykac sam nic njesćelo, ale zwisk wuwołajo aktiwita. Lěc k tomu docyła dojźo, wótwisujo wót nastajenja boka k pśizwólenju póbitowarja a wót pśigłosowanja wobźělnika.';
$string['privacy_provider_ipaddress'] = 'IP-adresa, z kótarejež se wobglědowak wobźělnika zwězujo.';
$string['privacy_provider_useragent'] = 'Daty wó wobglědowaku a rěźe, kótarež wobglědowak sćelo.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Pšašaś se, nježli až se YouTube abo Vimeo zasajźijo';
$string['providerconsent_desc'] = 'Zwucowanja, kótarež na wideo YouTube abo Vimeo bazěruju, pokazuju powěsć město wideo a zasajźiju jo akle pó pśigłosowanju wobźělnika. Bźez togo dostawa póbitowaŕ IP-adresu a daty wobglědowaka wobźělnika ned pó wócynjenju boka — nježli až něchten wótgraśe tłocy. Znjemóžńśo to jano, gaž waša institucija toś to pśizwólenje hynac wobstarajo.';
$string['report_actions'] = 'Akcije';
$string['report_answered'] = 'Wótegronjone';
$string['report_attemptnumber'] = 'Wopyt';
$string['report_back'] = 'Slědk ku wšym wopytam';
$string['report_correct'] = 'Pšawe';
$string['report_delete'] = 'Lašowaś';
$string['report_deleteconfirm'] = 'Toś ten wopyt a wšykne jogo wótegrona na pśecej lašowaś? Njedajo se anulěrowaś.';
$string['report_deleted'] = 'Wopyt jo lašowany.';
$string['report_exact'] = 'Dokradne';
$string['report_export'] = 'Eksportěrowaś';
$string['report_filterany'] = 'Wšykne';
$string['report_filterapply'] = 'Filtry nałožyś';
$string['report_filterattempt'] = 'Cysło wopyta';
$string['report_filterfrom'] = 'Zachopjone wót';
$string['report_filterrangeerror'] = 'Kóńc casowego wótrězka jo pśed jogo zachopjeńkom.';
$string['report_filterreset'] = 'Filtry wuprozniś';
$string['report_filterstate'] = 'Staw';
$string['report_filterto'] = 'Zachopjone do';
$string['report_filteruser'] = 'Wobźělnik';
$string['report_finished'] = 'Dokóńcone';
$string['report_heading'] = 'Wopyty';
$string['report_hinted'] = 'Z pókiwom';
$string['report_hints'] = 'Rownina pókiwa';
$string['report_kpianswered'] = 'Wótegronjone';
$string['report_kpiattempts'] = 'Pokazane wopyty';
$string['report_kpiaverage'] = 'Pśerězna licba dypkow (dokóńcone)';
$string['report_kpicorrect'] = 'Akceptěrowane';
$string['report_kpiexact'] = 'Docyła pšawe';
$string['report_kpifinished'] = 'Dokóńcone';
$string['report_kpihinted'] = 'Su pókiw wužyli';
$string['report_kpihintedgaps'] = 'Su pókiw trjebali';
$string['report_noattempts'] = 'Hyšći žedne wopyty.';
$string['report_nogaps'] = 'Wersija, na kótarejž jo se toś ten wopyt wugbał, žedne proznotki njama.';
$string['report_nomatchingattempts'] = 'Žeden wopyt toś tym filtram njewótpowědujo.';
$string['report_noresponse'] = 'Bźez wótegrona';
$string['report_response'] = 'Wótegrono';
$string['report_result'] = 'Wuslědk';
$string['report_result_empty'] = 'Prozne';
$string['report_result_exact'] = 'Dokradne';
$string['report_result_incorrect'] = 'Wopacne';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Spóznane';
$string['report_score'] = 'Licba dypkow';
$string['report_solution'] = 'Rozwězanje';
$string['report_started'] = 'Zachopjone';
$string['report_state'] = 'Staw';
$string['report_state_abandoned'] = 'Wóstajony';
$string['report_state_finished'] = 'Dokóńcony';
$string['report_state_inprogress'] = 'Běžy';
$string['report_transcript'] = 'Transkript';
$string['report_tries'] = 'Wopyty wótegrona';
$string['report_user'] = 'Wobźělnik';
$string['report_view'] = 'Pokazaś';
$string['reports'] = 'Rozpšawy';
$string['resetattempts'] = 'Wšykne wopyty a wótegrona wobźělnikow lašowaś';
$string['solutionavailability'] = 'Transkript z rozwězanjami za wobźělnikow';
$string['solutionavailability_aftersubmission'] = 'Pó dokóńcenju wopyta';
$string['solutionavailability_always'] = 'Kuždy cas';
$string['solutionavailability_help'] = 'Ga směju wobźělniki dopołny transkript z rozwězanim kuždeje proznotki ześěgnuś.

* Nigda — jano wucabniki mógu jen ześěgnuś.
* Pó dokóńcenju wopyta — wobźělnik móžo jen ześěgnuś, gaž jo w toś tej aktiwiśe wopyt dokóńcył.
* Kuždy cas — wobźělnik móžo jen teke pśed wótegronjenim ześěgnuś.

Wucabnicki personal z pšawom móžo jen pśecej ześěgnuś, njeglědajucy na toś to nastajenje.';
$string['solutionavailability_never'] = 'Nigda';
$string['subplugintype_elangscript'] = 'Pśeźěłaŕ pisma';
$string['subplugintype_elangscript_plural'] = 'Pśeźěłarje pisma';
$string['subtitleposition'] = 'Pokazanje pódtitelow';
$string['subtitleposition_below'] = 'Pód medijom';
$string['subtitleposition_help'] = 'Źož se interaktiwne pódtitele pokazuju.

* Pód medijom — ceły transkript stoj pód medijom w swójom suwańskem wobłuku a slědujo wótgraśeju.
* We wideo, dołojce abo górjejce — jano ten pódtitel se na medium kresli, kótaryž rowno běžy.

Medium jano ze zukom wobraz njama, na kótaryž by se kresliło, togodla pśecej pokazanje pód medijom wužywa. Nastajenje samo se wobchowa a płaśi zasej, gaž aktiwita wideo wužywa.';
$string['subtitleposition_overlaybottom'] = 'We wideo — dołojce';
$string['subtitleposition_overlaytop'] = 'We wideo — górjejce';
$string['task_migratev1activities'] = 'Aktiwity wersije 1 pśenjasć';
$string['transcriptheading'] = 'Transkript za wobźělnikow';
$string['validate_cueafterend'] = '{$a->where}: kóńcy se pśi {$a->endtime} ms, togodla pó mediju ({$a->duration} ms). Wótgraśe tam nigda njedojźo.';
$string['validate_cueendbeforestart'] = '{$a}: kóńc njejo pó zachopjeńku.';
$string['validate_cuewhere'] = 'Wótrězk {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'Rozwězanje za {$a} jo prozne.';
$string['validate_hintlevels'] = 'Rowniny pókiwow za {$a} zwězany rěd njetwórje, kótaryž se z 1 zachopina.';
$string['validate_negativetime'] = '{$a}: cas zachopjeńka jo pśed zachopjeńkom nagraśa.';
$string['validate_nocues'] = 'Wersija žedne wótrězki njama.';
$string['validate_nogaps'] = 'Wersija žedne proznotki za wótegronjenje njama.';
$string['validate_nonpositivelength'] = 'Dłujkosć w znamuškach za {$a} musy pozitiwna byś.';
$string['validate_rangeoutside'] = 'Wobłuk znamuškow za {$a} zwenka jogo transkripta lažy.';
$string['validate_rangeoverlap'] = 'Wobłuk znamuškow za {$a} se z drugeju proznotku pśekšywa.';
$string['validate_unknownalgorithm'] = 'Algoritmus pógódnośenja „{$a->algorithm}“ za {$a->where} se njespóznawa.';
$string['validate_where'] = 'proznotka {$a->gapkey} we wótrězku {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Proznotka {$a->gapkey}: algoritmus pógódnośenja jo „{$a->actual}“, wócakany jo był „{$a->expected}“.';
$string['verify_attemptcount'] = 'Licba pśenjasonych wopytow jo {$a->actual}, wócakane su byli rozdźělne wobźělniki z 1.x: {$a->expected}.';
$string['verify_jarothreshold'] = 'Proch pśirownowanja wótegronow jo {$a->actual}, wócakany jo był {$a->expected}.';
$string['verify_missingattempt'] = 'Wužywaŕ {$a}: wócakany jo był pśenjasony wopyt, žeden njejo se namakał.';
$string['verify_missingcue'] = 'Wótrězk {$a}: pśenjasony wótrězk felujo.';
$string['verify_missinggap'] = 'Proznotka {$a}: pśenjasona proznotka felujo.';
$string['verify_missinghint'] = 'Proznotka {$a}: wersija 1 jo how pomoc dowóliła, ale žeden pókiw njejo se pśenjasł.';
$string['verify_orphancue'] = 'Wótrězk {$a}: wótpowědny wótrězk z wersije 1 njejo se namakał.';
$string['verify_orphangap'] = 'Proznotka {$a}: wótpowědna proznotka z wersije 1 njejo se namakała.';
$string['verify_rangemismatch'] = 'Proznotka {$a}: wobłuk znamuškow ze žrědłom wersije 1 njewótpowědujo.';
$string['verify_responsecount'] = 'Wužywaŕ {$a->userid}: licba pśenjasonych wótegronow jo {$a->actual}, wócakana jo była {$a->expected}.';
$string['verify_solutionmismatch'] = 'Proznotka {$a->gapkey}: rozwězanje jo „{$a->actual}“, wócakane jo było „{$a->expected}“.';
$string['verify_transcriptmismatch'] = 'Wótrězk {$a}: transkript ze žrědłom wersije 1 njewótpowědujo.';
$string['verify_unexpectedhint'] = 'Proznotka {$a}: wersija 1 jo how pomoc njedowóliła, ale pókiw jo se pśenjasł.';
