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
 * Upper Sorbian strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * Written from the English source, with German consulted only for
 * comprehension — not derived from the German pack. Terminology follows
 * Moodle's own Upper Sorbian conventions where they exist: wobdźělnik, pospyt,
 * aktiwita, podtitle, pokiw, kurs.
 *
 * Terminology follows the 25-term glossary compiled for this plugin against
 * WITAJ, the Sorbisches Institut and the Sprachzertifikat Sorbisch. An earlier
 * draft of this file used words chosen by guesswork; the glossary replaced most
 * of them, which is why it was worth asking for:
 *
 *   gap        mjezera        -> prózdne městno   (and it is neuter, so the
 *                                                  agreement changes with it)
 *   cue        segment        -> wotrězk
 *   subtitle   podtitl        -> podtitul, pl. podtitule
 *   score      dypki          -> ličba dypkow     (wuslědk is *result*, not a
 *                                                  number, and stays separate)
 *   playback   wothraće       -> wothrawanje
 *   worksheet  dźěłowy łopjeno-> dźěłowe łopjeno  (łopjeno is neuter)
 *   start/end  spočatny čas   -> Započatk / Kónc
 *
 * **Three decisions are still open and marked C in the glossary.** A native
 * reviewer should settle them before this reaches AMOS:
 *
 * 1. **Widejodiktat** — the activity name. The glossary says explicitly that it
 *    must not be composed mechanically from German or English, which is exactly
 *    what this draft does. It is the single most important open item.
 * 2. **wotličenje dypkow** for penalty. The word family is attested, but mostly
 *    in the sense of settling an account rather than deducting marks.
 * 3. **akceptowana warianta** for an accepted answer variant; something like
 *    dowolena warianta may read better.
 *
 * Also open: whether the export is best called `transkript z rozrisanjemi` or
 * simply `rozrisanje`.
 *
 * Upper Sorbian has a dual, so nouns take three number forms rather than two.
 * That makes the count-neutral phrasing adopted in the RC1 review load-bearing
 * here: "Segmenty: {$a}" needs no agreement, whereas "{$a} segment" would be
 * wrong for most values including every dual one.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Dowolene rěče wobsaha';
$string['allowedlanguages_desc'] = 'Rěče wobsaha, kotrež so při wutworjenju abo wobdźěłanju widejodiktata poskićuja. Njewubjerće žanu, zo by so cyła lisćina rěčow poskićała. Aktiwita swoju składowanu rěč wobchowa, samo hdyž ju pozdźišo wottud wotstroniće.';
$string['allowtranscriptdownload'] = 'Sćahnjenje transkripta přez wobdźělnikow';
$string['allowtranscriptdownload_help'] = 'Hdyž je zmóžnjene, móža wobdźělnicy dźěłowe łopjeno transkripta ze schowanej kóždym prózdnym městnom jako PDF, Word, OpenDocument abo tekst sćahnyć.

Standardnje je znjemóžnjene. Wučerski personal z prawom móže transkript přeco sćahnyć, njedźiwajo na tute nastajenje.';
$string['allowtranscriptdownload_label'] = 'Wobdźělnicy smědźa dźěłowe łopjeno sćahnyć';
$string['completiondetail_completionfinishattempt'] = 'Pospyt dokónčić';
$string['completionfinishattempt'] = 'Wobdźělnik dyrbi pospyt dokónčić';
$string['cuepausemode'] = 'Přestawka na kóncu podtitula';
$string['cuepausemode_auto'] = 'Awtomatisce';
$string['cuepausemode_help'] = 'Hač medij na kóncu podtitula zastawa.

* Awtomatisce — wothrawanje pokročuje a zastawa na kóncu podtitula jenož tak dołho, kaž so runje na tutym podtitulu dźěła, potajkim po kliknjenju na njón abo na jednu z jeho prózdnych městnow, abo hdyž fokus tastatury w jednej z nich steji.
* Při kóždym njewotmołwjenym podtitulu zastać — wothrawanje zastawa na kóncu kóždeho podtitula, w kotrymž hišće prózdne městno je, a čaka na pokročowanje.
* Ženje njezastać — wothrawanje běži hač do kónca medija.

Žadyn z prěnjeju dweju modusow na podtitulu njezastawa, kotrehož prózdne městna su wšě wupjelnjene: to je hotowe dźěło, a zastaće tam by tastowe kliknjenje bjez wuskutka žadało. To tež rěka, zo druhi přeběh zwučowanja jenož tam zastawa, hdźež hišće něšto faluje.';
$string['cuepausemode_nostop'] = 'Ženje njezastać';
$string['cuepausemode_stop'] = 'Při kóždym njewotmołwjenym podtitulu zastać';
$string['editcontent'] = 'Wobsah wobdźěłać';
$string['editor_addcue'] = 'Wotrězk přidać';
$string['editor_addgap'] = 'Prózdne městno z wubranki wutworić';
$string['editor_addhint'] = 'Pokiw přidać';
$string['editor_addvariant'] = 'Warianty přidać';
$string['editor_advanced'] = 'Rozšěrjene nastajenja';
$string['editor_algoexact'] = 'Dokładne přezjednosć';
$string['editor_algorithm'] = 'Přirunanje wotmołwow';
$string['editor_algowordrecognized'] = 'Podobne wotmołwy akceptować';
$string['editor_answers'] = 'Akceptowane warianty';
$string['editor_autosaved'] = 'Wšě změny su składowane.';
$string['editor_autosaveerror'] = 'Awtomatiske składowanje je so nimokuliło — wužijće „Składować“.';
$string['editor_captureend'] = 'Kónc z wothraća přewzać';
$string['editor_capturestart'] = 'Spočatk z wothraća přewzać';
$string['editor_cueactions'] = 'Akcije wotrězka';
$string['editor_cuecount'] = 'Wotrězki: {$a}';
$string['editor_cuenotsaved'] = 'Njeskładowane';
$string['editor_currentmedia'] = 'Aktualny medij:';
$string['editor_deletecue'] = 'Wotrězk zhašeć';
$string['editor_deletegap'] = 'Prózdne městno zhašeć';
$string['editor_emptytranscript'] = '(hišće žadyn tekst)';
$string['editor_endtime'] = 'Kónc';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Prózdne městna: {$a}';
$string['editor_gaphasalternatives'] = 'ma dowolene warianty';
$string['editor_gaphashints'] = 'ma pokiwy';
$string['editor_gapmode_exact'] = 'dokładna přezjednosć';
$string['editor_gapmode_wordrecognized'] = 'podobne wotmołwy dowolene';
$string['editor_gaprange'] = 'Pozicija prózdneho městna (znamješka)';
$string['editor_gapunplaceable_outofrange'] = 'Tute prózdne městno zwonka teksta leži a njeda so na swojim městnje pokazać.';
$string['editor_gapunplaceable_overlap'] = 'Tute prózdne městno so z druhim přerězuje a njeda so na swojim městnje pokazać.';
$string['editor_gotomedia'] = 'K „Medije“ hić';
$string['editor_heading'] = 'Podtitule a prózdne městna wobdźěłać';
$string['editor_hints'] = 'Pokiwy';
$string['editor_hinttext'] = 'Tekst pokiwa';
$string['editor_hinttype'] = 'Typ';
$string['editor_hinttype_firstletter'] = 'Prěnje pismik';
$string['editor_hinttype_partial'] = 'Dźělnje';
$string['editor_hinttype_solution'] = 'Rozrisanje';
$string['editor_hinttype_text'] = 'Swobodny tekst';
$string['editor_hinttype_translation'] = 'Přełožk';
$string['editor_hinttype_wordlength'] = 'Dołhosć słowa';
$string['editor_import'] = 'Podtitule importować';
$string['editor_importappend'] = 'K eksistowacym wotrězkam přidać';
$string['editor_importapply'] = 'Importować';
$string['editor_importcancel'] = 'Přetorhnyć';
$string['editor_importcheck'] = 'Wobsah pruwować';
$string['editor_importchecking'] = 'Pruwuje so…';
$string['editor_importcuecount'] = 'Namakane wotrězki';
$string['editor_importduration'] = 'Traće';
$string['editor_importedcues'] = 'Importowane wotrězki: {$a}';
$string['editor_importfilehint'] = 'Wubjerće dataju WebVTT (.vtt) abo SubRip (.srt) z podtitulemi.';
$string['editor_importformat'] = 'Format';
$string['editor_importfromfile'] = 'Dataju nahrać';
$string['editor_importfromtext'] = 'Tekst zasadźić';
$string['editor_importgapcount'] = 'Namakane prózdne městna';
$string['editor_importhint'] = 'Zasadźće wobsah WebVTT abo SubRip a importujće jón jako wotrězki.';
$string['editor_importparseerror'] = 'Tutón wobsah njeda so jako WebVTT abo SubRip čitać.';
$string['editor_importpastedtext'] = 'Zasadźeny tekst';
$string['editor_importreaderror'] = 'Dataja njeda so čitać.';
$string['editor_importready'] = 'Hotowe za import';
$string['editor_importreplace'] = 'Wšě wotrězki wuměnić';
$string['editor_importreplacedcues'] = 'Wotrězki wuměnjene; nowo importowane: {$a}';
$string['editor_importsource'] = 'Žórło';
$string['editor_importsummary'] = 'Što je so namakało';
$string['editor_importtoolarge'] = 'Tuta dataja ma {$a->size}; import akceptuje maksimalnje {$a->max}.';
$string['editor_importwrongtype'] = 'Wubjerće dataju z podtitulemi ({$a}).';
$string['editor_insertafter'] = 'Wotrězk za tym zasadźić';
$string['editor_insertbefore'] = 'Wotrězk před tym zasadźić';
$string['editor_invalidtime'] = 'Zapodajće čas w formje mm:ss.SSS, na přikład 01:05.400.';
$string['editor_linkurl'] = 'Wotkaz za pytanje';
$string['editor_linkurl_help'] = 'Pokazuje so pódla prózdne městna jako městno, hdźež hodźi so słowo pytać. Wostajće prózdne, jeli žadyn wotkaz njechaće.';
$string['editor_loaderror'] = 'Editor njeda so začitać. Začitajće stronu znowa.';
$string['editor_loading'] = 'Editor so začituje…';
$string['editor_maxlength'] = 'Maksimalna dołhosć';
$string['editor_maxlength_help'] = 'Wobmjezuje, kelko smě wobdźělnik zapodać. 0 rěka bjez wobmjezowanja.';
$string['editor_media'] = 'Medije';
$string['editor_mediafile'] = 'Nahrata dataja';
$string['editor_mediakind'] = 'Typ medija';
$string['editor_medianone'] = 'Žadyn';
$string['editor_mediaprovider'] = 'Poskićowar';
$string['editor_mediaproviderref'] = 'Poćah poskićowarja';
$string['editor_mediaproviderrefhint'] = 'ID abo wotkaz widea w zwučenej formje (na př. youtu.be/…).';
$string['editor_mediasaved'] = 'Medij je składowany.';
$string['editor_mediaurl'] = 'Direktny URL medija';
$string['editor_nocues'] = 'Hišće žane wotrězki. Přidajće jedyn abo importujće podtitule.';
$string['editor_nocueselected'] = 'Wubjerće wotrězk z lisćiny, zo byšće jón wobdźěłał.';
$string['editor_nocuesmatch'] = 'Žadyn wotrězk tutomu pytanju njewotpowěduje.';
$string['editor_nogaps'] = 'Žane prózdne městna';
$string['editor_nomedia'] = 'žadyn';
$string['editor_nomedianotice'] = 'Přidajće najprjedy widejowu abo awdijowu dataju w rajtarku „Medije“. Podtitule so na medij časuja, tohodla editor jón trjeba, prjedy hač móžeće ze wotrězkami a prózdnymi městnami dźěłać.';
$string['editor_novideotrack'] = 'Tutón wobhladowak njemóže widejowu stopu tutoho medija dekodować (jenož zwuk so wothrawa); wobdźělnicy bychu čorny wobraz widźeli. Kodujće dataju znowa jako H.264/MP4 (na přikład z ffmpeg abo HandBrake) a nahrajće ju znowa.';
$string['editor_onboardinggaps'] = 'Wubjerće słowo w wotrězku a wutworće z njeho prózdne městno.';
$string['editor_onboardingimport'] = 'Importujće podtitule WebVTT abo SubRip, abo přidajće wotrězki ručnje.';
$string['editor_onboardingintro'] = 'Wutworće zwučowanje w třoch krokach:';
$string['editor_onboardingmedia'] = 'Wubjerće medij (nahraće, URL abo poskićowar).';
$string['editor_onboardingtitle'] = 'Započńće swoje zwučowanje';
$string['editor_onlywarnings'] = 'Jenož wotrězki z warnowanjemi';
$string['editor_parsegaps'] = 'Znamjenja prózdnych městnow spóznać: [słowo] wutwori prózdne městno z dowolenymi pokiwami, {słowo} bjez nich.';
$string['editor_penalty'] = 'Wotličenje dypkow';
$string['editor_poster'] = 'Titulny wobraz';
$string['editor_preview'] = 'Přehlad wobdźělnika';
$string['editor_problem_afterduration'] = 'Kónc leži za kóncom medija.';
$string['editor_problem_endbeforestart'] = 'Kónc je samsny kaž spočatk abo před nim.';
$string['editor_problem_negativestart'] = 'Spočatk leži před spočatkom nahrawanja.';
$string['editor_publish'] = 'Publikować';
$string['editor_publishblocked'] = 'Někotre podtitule njedadźa so hišće składować. Porjedźće je před publikowanjom.';
$string['editor_published'] = 'Wersija je publikowana.';
$string['editor_removehint'] = 'Pokiw wotstronić';
$string['editor_removevariant'] = 'Wotstronić';
$string['editor_repaircue'] = 'Kónc porjedźić';
$string['editor_ruleapplied'] = 'Po prawidle su so prózdne městna wutworili: %count%.';
$string['editor_ruleapply'] = 'Prózdne městna nałožić: %count%';
$string['editor_ruleerror'] = 'Prózdne městna njedachu so wutworić.';
$string['editor_ruleeverynth'] = 'Kóžde n-te słowo';
$string['editor_rulefound'] = 'Prawidło je prózdne městna namakało: %count%.';
$string['editor_rulegenerate'] = 'Prózdne městna wutworić';
$string['editor_ruleinterval'] = 'Interwal (n)';
$string['editor_ruletype'] = 'Prawidło prózdnych městnow';
$string['editor_rulewordlist'] = 'Słowa, kotrež so schowaja';
$string['editor_rulewords'] = 'Lisćina słowow';
$string['editor_save'] = 'Naćisk składować';
$string['editor_saved'] = 'Naćisk je składowany.';
$string['editor_savedwithproblems'] = 'Składowane bu, štož da so składować; někotre podtitule trjebaja kedźbnosć.';
$string['editor_saveerror'] = 'Naćisk njeda so składować.';
$string['editor_savemedia'] = 'Medij składować';
$string['editor_saving'] = 'Składuje so…';
$string['editor_searchcues'] = 'W wotrězkach pytać';
$string['editor_selecttext'] = 'Wubjerće najprjedy słowo w transkripće, kotrež so ma schować.';
$string['editor_solution'] = 'Rozrisanje';
$string['editor_starttime'] = 'Započatk';
$string['editor_transcript'] = 'Transkript';
$string['editor_unsaved'] = 'Njeskładowane změny';
$string['editor_uploadmedia'] = 'Medijowe dataje nahrać';
$string['editor_variantisregex'] = '{$a} jako regularny wuraz wobchadźeć';
$string['editor_variantmatching'] = 'Kak so akceptowane warianty přirunuja';
$string['editor_warnemptysolution'] = 'Prózdne městno bjez rozrisanja';
$string['editor_warnnotranscript'] = 'Žadyn tekst';
$string['editor_warntiming'] = 'Kónc njeje po spočatku';
$string['editor_waveform'] = 'Zwukowa křiwka';
$string['elang:addinstance'] = 'Nowy widejodiktat přidać';
$string['elang:attempt'] = 'Widejodiktat wuwjesć';
$string['elang:deleteattempts'] = 'Pospyty wobdźělnikow zhašeć';
$string['elang:exportreports'] = 'Rozprawy z wosobinskimi datami eksportować';
$string['elang:exportsolution'] = 'Dospołny transkript z rozrisanjemi eksportować';
$string['elang:exporttranscript'] = 'Dźěłowe łopjeno jako dokument eksportować';
$string['elang:manage'] = 'Wobsah zwučowanjow wutworić a wobdźěłać';
$string['elang:useregex'] = 'Regularne wurazy w akceptowanych wotmołwach wužiwać';
$string['elang:view'] = 'Widejodiktat sej wobhladać';
$string['elang:viewreports'] = 'Rozprawy wobdźělnikow sej wobhladać';
$string['error_attemptnotinprogress'] = 'Tutón pospyt hižo njeběži.';
$string['error_couldnotobtainlock'] = 'Za tutu akciju njeda so zawrjenje dóstać. Spytajće hišće raz.';
$string['error_draftrevisionmismatch'] = 'Tutón naćisk je so změnił, po tym zo sće jón začitał. Začitajće jón znowa a spytajće hišće raz.';
$string['error_duplicatecuekey'] = 'Dwaj wotrězkaj matej samsny kluč „{$a}“; kóždy wotrězk trjeba jednozmyslny kluč.';
$string['error_duplicategapkey'] = 'Dwě prózdnej městnje w samsnym wotrězku matej samsny kluč „{$a}“; kóžda prózdne městno trjeba jednozmyslny kluč.';
$string['error_duplicatehintlevel'] = 'Prózdne městno ma dwaj pokiwaj na runinje {$a}; kóžda runina dyrbi jednozmyslna być.';
$string['error_gapnotinattemptversion'] = 'Tute prózdne městno k wersiji zwučowanja tutoho pospyta njesłuša.';
$string['error_importnocues'] = 'Z tutoho wobsaha njedachu so žane podtitule čitać. Dataja WebVTT abo SubRip ma nad kóždym podtitulom časowu linku, na přikład 00:00:01.000 --> 00:00:04.000.';
$string['error_importnotutf8'] = 'Tuta dataja płaćiwy UTF-8 njeje. Najskerje bu ze staršim kodowanjom składowana — wočińće ju w tekstowym editorje a składujće ju znowa jako UTF-8.';
$string['error_importtoolarge'] = 'Tuta dataja ma {$a->size}; import akceptuje maksimalnje {$a->max}. Dataja z podtitulemi za nahrawanje hodźiny je wjele mjeńša, tohodla to najskerje žana njeje.';
$string['error_importtoomanycues'] = 'Tuta dataja wobsahuje podtitule: {$a->count}; import akceptuje maksimalnje {$a->max}.';
$string['error_invalidcuepausemode'] = 'Wubjerće jednu z poskićenych możnosćow za přestawku na kóncu podtitula.';
$string['error_invalidgradingalgorithm'] = 'Algoritmus hódnoćenja „{$a}“ ani exact ani wordrecognized njeje.';
$string['error_invalidhinttype'] = 'Typ pokiwa „{$a}“ k dowolenym typam njesłuša.';
$string['error_invalidisregex'] = 'Znamjo regularneho wuraza warianty dyrbi 0 abo 1 być.';
$string['error_invalidmediakind'] = 'Wubrany typ medija ani file, ani url, ani provider njeje.';
$string['error_invalidpenalty'] = 'Wotličenje dypkow za pokiw dyrbi mjez 0 a 1 być.';
$string['error_invalidproviderref'] = '„{$a}“ njeje spóznany ID abo wotkaz widea za tutoho poskićowarja.';
$string['error_invalidregexpattern'] = '„{$a}“ płaćiwy regularny wuraz njeje.';
$string['error_invalidsolutionavailability'] = 'Wubjerće jednu z poskićenych możnosćow za to, hdy smědźa wobdźělnicy transkript z rozrisanjemi widźeć.';
$string['error_invalidsourceurl'] = 'Zapodajće dospołnu adresu, kotraž so z http:// abo https:// započina, abo wotkaz YouTube abo Vimeo.';
$string['error_invalidsubtitleposition'] = 'Wubjerće jednu z poskićenych możnosćow za poziciju podtitulow.';
$string['error_invalidv1cuejson'] = 'Tutón wotrězk z wersije 1 njeda so předźěłać.';
$string['error_negativegapoffset'] = 'Pozicija a dołhosć prózdneho městna njesmětej negatiwnej być.';
$string['error_noaccesstoattempt'] = 'Nimaće přistup k tutomu pospytej.';
$string['error_nomorehints'] = 'Za tute prózdne městno wjace pokiwow njeje.';
$string['error_nopublishedversion'] = 'Tute zwučowanje hišće publikowany wobsah nima.';
$string['error_responsetoolong'] = 'Waša wotmołwa je předołha. Maksimum za tute prózdne městno je {$a} znamješkow.';
$string['error_solutionnotavailable'] = 'Transkript z rozrisanjemi w tutej aktiwiće za was k dispoziciji njesteji.';
$string['error_staleattemptstate'] = 'Waš napohlad tutoho pospyta zestarjeny je. Začitajće aktualny staw znowa a spytajće hišće raz.';
$string['error_transcriptnotavailable'] = 'W tutej aktiwiće žadyn transkript za sćahnjenje njeje.';
$string['error_unknowngaprule'] = 'Njeznaty typ prawidła prózdnych městnow „{$a}“.';
$string['error_unknownmediaprovider'] = '„{$a}“ k podpěranym medijowym poskićowarjam njesłuša.';
$string['error_versionnotadraft'] = 'Jenož wersija w stawje naćiska da so wobdźěłać.';
$string['error_versionnotfound'] = 'Tuta wersija zwučowanja hižo njeeksistuje.';
$string['error_versionnotpublishable'] = 'Tuta wersija njeda so publikować: {$a}';
$string['export_audienceaftersubmission'] = 'Wobdźělnicy móža to sćahnyć, hdyž su pospyt dokónčili';
$string['export_audiencealways'] = 'Wobdźělnicy móža to kóždy čas sćahnyć';
$string['export_audiencestaff'] = 'Jenož wučerski personal z prawom — wobdźělnikam so njeposkića';
$string['export_docx'] = 'Jako Word (DOCX) sćahnyć';
$string['export_downloadpdf'] = 'PDF sćahnyć';
$string['export_heading'] = 'Transkript eksportować';
$string['export_intro'] = 'Sćahńće transkript tutoho zwučowanja we wjacorych formatach.';
$string['export_moreformats'] = 'Dalše formaty';
$string['export_nocontent'] = 'Hišće žadyn publikowany transkript za eksport njeje.';
$string['export_odt'] = 'Jako OpenDocument (ODT) sćahnyć';
$string['export_pdf'] = 'Jako PDF sćahnyć';
$string['export_solution'] = 'Transkript z rozrisanjemi';
$string['export_solutionhint'] = 'Dospołny tekst, w kotrymž je rozrisanje kóždeho prózdneho městna widźomne.';
$string['export_text'] = 'Jako tekst sćahnyć';
$string['export_versionnote'] = 'Eksporty bazuja na tuchwilu publikowanej wersiji tutoho zwučowanja.';
$string['export_worksheet'] = 'Dźěłowe łopjeno (prózdne městna schowane)';
$string['export_worksheethint'] = 'Tekst ze schowanej kóždym prózdnym městnom. Hotowy za rozdźělenje jako material za wobdźělnikow.';
$string['exporttranscript'] = 'Transkript eksportować';
$string['filearea_media'] = 'Medije';
$string['filearea_poster'] = 'Titulny wobraz';
$string['gradingheading'] = 'Hódnoćenje wotmołwow';
$string['import_badtiming'] = 'Časowa linka njeda so čitać: {$a}';
$string['import_emptytranscript'] = 'Wotrězk bjez teksta bu přeskočeny.';
$string['import_warnlinetoolong'] = 'Blok {$a->block} bu přeskočeny: wobsahuje linku dlěšu hač {$a->max} znamješkow, a to linka podtitula njeje.';
$string['jarothreshold'] = 'Prah podobnosće';
$string['jarothreshold_help'] = 'Za prózdne městna, kotrež su na „Podobne wotmołwy akceptować“ nastajene, je to najmjeńša podobnosć Jaro mjez wočakowanej a zapodatej wotmołwu. Hódnota 1 žada dokładnu přezjednosć po normalizaciji, kotraž je rěči swójska; nišie hódnoty akceptuja pisanja, kotrež so hdys a hdys wjace rozeznawaja.';
$string['jarothresholdrange'] = 'Prah dyrbi mjez 0 a 1 być.';
$string['language'] = 'Rěč wobsaha';
$string['language_help'] = 'Wubjerće rěč wobsaha zwučowanja. Wona postaja, kak so wotmołwy přirunuja, mjez druhim wulkopisanje a transliteraciju. Wubjerće „Powšitkowna (njepodata)“, jeli so rěči swójske předźěłanje njema wužiwać. Nowe wersije wobsaha z tutoho nastajenja wuchadźeja.';
$string['language_none'] = 'Powšitkowna (njepodata)';
$string['media_cuenote'] = 'Eksistowace podtitule a prózdne městna so wobchowaja, hdyž medij měnjeće. Jich časy so njepřiměrja, tohodla pruwujće je po tym w editorje.';
$string['media_current'] = 'Aktualny medij';
$string['media_heading'] = 'Medije';
$string['media_intro'] = 'Wubjerće widejo abo zwuk, na kotrymž tute zwučowanje bazuje. Podtitule so na njón časuja, tohodla to prěni krok je.';
$string['media_none'] = 'Za tute zwučowanje hišće žadyn medij postajeny njeje.';
$string['media_othersource'] = 'Druhe žórło';
$string['media_providerhint'] = 'Spóznani poskićowarjo: {$a}. Kóžda druha adresa so jako direktny URL medija wužiwa.';
$string['media_sourceurl'] = 'URL medija';
$string['media_sourceurl_help'] = 'Zasadźće adresu widea město toho, zo byšće dataju nahrali — wotkaz YouTube abo Vimeo, abo direktnu adresu medijoweje dataje.

Adresa, kotraž so tu zapodawa, nahradźa nahratu dataju. Wostajće ju prózdnu, zo byšće nahraće horjeka wužiwał.

Widejo poskićowarja so we wobłuku samoho poskićowarja wothrawa, kotryž čas wothraća njezdźěla. Tajke zwučowanje pokazuje podtitule přeco pod medijom a ženje na kóncu podtitula njezastawa.

**Hdźež daty dochadźeja.** Wobłuk YouTube abo Vimeo zwjazuje wobhladowak kóždeho wobdźělnika z tym předewzaćom, kotrež tak jeho IP-adresu a daty grata dóstawa. Standardnje so zwučowanje prjedy praša. Jeli waša institucija swójski medijowy serwer ma — Opencast, Panopto, Kaltura abo podobny — zasadźće město toho direktnu adresu dataje wottam: wona so jako zwučeny URL medija wobchadźa, wobchowa wubranu poziciju podtitulow a nastajenje přestawki, a žadyn třeći njeje zapřijaty.';
$string['migratev1_approvalheading'] = 'Přenjesene, čaka na pruwowanje';
$string['migratev1_approvebutton'] = 'Tute přenjesenje schwalić';
$string['migratev1_approved'] = 'Widejodiktat {$a} bu jako schwaleny woznamjenjeny.';
$string['migratev1_colactivity'] = 'Aktiwita';
$string['migratev1_colalgorithm'] = 'Algoritmus hódnoćenja';
$string['migratev1_colcues'] = 'Wotrězki';
$string['migratev1_colgaps'] = 'Prózdne městna';
$string['migratev1_colissues'] = 'Problemy';
$string['migratev1_collearners'] = 'Wobdźělnicy';
$string['migratev1_confirmdecommission'] = 'Tuta akcija zhaša NJEWOTWOLIWJENJE stare tabele wersije 1 a špaltu elang.options. Njeda so cofnyć. Dale?';
$string['migratev1_confirmmigrate'] = 'Tuta akcija staji nadawk do zarjadowanki, kotryž za kóždu horjeka naličenu aktiwitu daty wersije 2 napisa. Tabele wersije 1 a elang.options wostanu njedótknjene. Dale?';
$string['migratev1_decommissionblocked'] = 'Zhašenje je dale zablokowane; hlejće lisćinu deleka.';
$string['migratev1_decommissionblockedintro'] = 'Zhašenje je zablokowane, doniž:';
$string['migratev1_decommissionbutton'] = 'Stare daty wersije 1 zhašeć';
$string['migratev1_decommissioned'] = 'Stare daty wersije 1 su zhašene.';
$string['migratev1_decommissionheading'] = 'Wotstajenje datow wersije 1';
$string['migratev1_decommissionready'] = 'Wšě aktiwity wersije 1 su přenjesene a schwalene. Stare tabele a elang.options dadźa so nětko zhašeć. Tuta akcija so njeda cofnyć.';
$string['migratev1_heading'] = 'Aktiwity wersije 1 přenjesć';
$string['migratev1_migratebutton'] = 'Tute aktiwity přenjesć';
$string['migratev1_noissues'] = 'Žane';
$string['migratev1_nonepending'] = 'Žane aktiwity wersije 1 na přenjesenje nječakaja.';
$string['migratev1_nonependingapproval'] = 'Žane přenjesene aktiwity na pruwowanje nječakaja.';
$string['migratev1_notablespresent'] = 'Na tutej stronje njebuchu stare tabele wersije 1 namakane. Ničo za přenjesenje njeje.';
$string['migratev1_parseerrorcount'] = 'Wotrězki, kotrež njedachu so předźěłać: {$a}';
$string['migratev1_pendingheading'] = 'Hišće njepřenjesene';
$string['migratev1_queued'] = 'Nadawk přenjesenja je w zarjadowance. Wuwjedźe so při přichodnym běhu crona abo hnydom přez admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Pruwowane: přenjesene daty ze žórłom wersije 1 bjez wotchilenjow wotpowěduja.';
$string['migratev1_verifieddiscrepancies'] = 'Pruwowanje je wotchilenja napřećo žórłu wersije 1 namakało: {$a}';
$string['migratev1_verifyfailed'] = 'Tuta aktiwita njeda so pruwować: {$a}';
$string['modulename'] = 'Widejodiktat';
$string['modulename_help'] = 'Aktiwita Widejodiktat wobdźělnikam zmóžnja, prózdne městna w časowanych podtitulach wupjelnić, mjeztym zo widejo wobhladuja abo słuchaja.

Wučerjo importuja dataju z podtitulemi WebVTT abo SubRip, woznamjenja słowa abo wobroty jako prózdne městna a nastaja, kak kruće so wotmołwy přirunuja. Wobdźělnicy dźěłaja transkript wotrězk po wotrězku, prošeja wo hódnoćene pokiwy a dóstawaja hnydomnu wróćozdźělenku.';
$string['modulenameplural'] = 'Widejodiktaty';
$string['nav_exportshort'] = 'Eksport';
$string['nav_media'] = 'Medije';
$string['nav_reports'] = 'Pospyty';
$string['nav_subtitles'] = 'Podtitule a prózdne městna';
$string['noinstances'] = 'W tutym kursu žane widejodiktaty njejsu.';
$string['overview_attempts'] = 'Pospyty';
$string['playbackheading'] = 'Wothrawanje a podtitule';
$string['playbackoverlayhint'] = 'Podtitul, kotryž so na wobraz kładźe, pokazuje jenož tón podtitul, kotryž runje běži. Tohodla wothrawanje přeco na kóncu podtitula zastawa, w kotrymž hišće prózdne městna za wupjelnjenje su. Tu ničo za wubranje njeje.';
$string['playbackproviderhint'] = 'Widejo YouTube abo Vimeo wothrawa poskićowar we swojim swójskim wobłuku, kotryž čas wothraća njezdźěla. Tajke zwučowanje pokazuje podtitule přeco pod medijom a ženje na kóncu podtitula njezastawa, njedźiwajo na to, štož je horjeka wubrane. Nahrate dataje a direktne URL medijow wobě nastajeni respektujetej.';
$string['player_check'] = 'Wotmołwu pruwować';
$string['player_consentaccept'] = 'Widejo z {$a} začitać';
$string['player_consentdetail'] = 'Wothrawanje zwjazuje waš wobhladowak z {$a}. {$a} dóstawa wašu IP-adresu a informacije wo wašim graće a móže placki čitać, kotrež je hižo stajił. Ničo so njesćele, doniž sej wy začitanje widea njewuzwolće.';
$string['player_consentheading'] = 'Tute widejo poskića {$a}';
$string['player_exitfullscreen'] = 'Połnu wobrazowku wopušćić';
$string['player_finish'] = 'Pospyt dokónčić';
$string['player_finished'] = 'Pospyt je dokónčeny. Ličba dypkow: %score%%';
$string['player_finishincomplete'] = 'Hišće prózdne prózdne městna: {$a}. Pospyt najebać toho dokónčić?';
$string['player_fullscreen'] = 'Połna wobrazowka';
$string['player_gaplabel'] = 'Prózdne městno %gap%';
$string['player_gaplink'] = 'Wotkaz wočinić';
$string['player_hint'] = 'Pokiw pokazać';
$string['player_loaderror'] = 'Zwučowanje njeda so začitać. Začitajće stronu znowa.';
$string['player_loading'] = 'Zwučowanje so začituje…';
$string['player_nocontent'] = 'Hišće žadyn wobsah zwučowanja publikowany njeje. Přińdźće pozdźišo.';
$string['player_novideotrack'] = 'Waš wobhladowak njemóže widejowu stopu tutoho medija pokazać; zwuk so tola wothrawa. Zdźělće to swojemu wučerjej.';
$string['player_outdatedattempt'] = 'Tute zwučowanje bu zaktualizowane, po tym zo sće tutón pospyt započał. Pokročujeće ze staršim wobsahom; dokónčće tutón pospyt, zo byšće přichodny raz ze zaktualizowanym zwučowanjom dźěłał.';
$string['player_progress'] = 'Wotmołwjene: {$a->done} z {$a->total} prózdnych městnow';
$string['player_ready'] = 'Zwučowanje je hotowe.';
$string['player_scorelabel'] = 'Ličba dypkow: %score%%';
$string['player_stateaccepted'] = 'Akceptowane';
$string['player_statecorrect'] = 'Prawje';
$string['player_statehinted'] = 'Pokiw wužity';
$string['player_stateincorrect'] = 'Wopak';
$string['player_submitfailed'] = 'Waša wotmołwa njeda so składować. Spytajće hišće raz.';
$string['player_transcriptheading'] = 'Transkript';
$string['pluginadministration'] = 'Zarjadowanje widejodiktata';
$string['pluginname'] = 'Widejodiktat';
$string['privacy_metadata_elang'] = 'Za kóždu aktiwitu zapisk wo tym, štó je jednosměrne přenjesenje jeje wobsaha z 1.x schwalił.';
$string['privacy_metadata_elang_attempt'] = 'Za kóždy pospyt w zwučowanju aktiwita składuje, štó jón wuwjedźe, hdy, kak daloko dóńdźe a kak bu hódnoćeny.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Kelko prózdnych městnow je wobdźělnik w tutym pospyće wotmołwił.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Běžne čisło tutoho pospyta za wužiwarja a aktiwitu.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Kelko prózdnych městnow bu w tutym pospyće jako prawe akceptowane.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Kelko prózdnych městnow bu w tutym pospyće z dokładnej přezjednosću znamješkow wotmołwjene.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Za kelko prózdnych městnow je wobdźělnik w tutym pospyće pokiw požadał.';
$string['privacy_metadata_elang_attempt_score'] = 'Ličba dypkow, docpěte w tutym pospyće.';
$string['privacy_metadata_elang_attempt_state'] = 'Hač pospyt běži, je dokónčeny abo spušćeny.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'Čas, hdyž bu pospyt dokónčeny.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'Čas poslednjeje aktualizacije pospyta.';
$string['privacy_metadata_elang_attempt_timestart'] = 'Čas, hdyž pospyt započa.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Cyłkowna ličba prózdnych městnow we wersiji zwučowanja, ke kotrejž tutón pospyt słuša.';
$string['privacy_metadata_elang_attempt_userid'] = 'ID wužiwarja, kotryž je pospyt wuwjedł.';
$string['privacy_metadata_elang_attempt_versionid'] = 'Wersija zwučowanja, na kotrejž bu tutón pospyt wuwjedźeny.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'Wužiwar, kotryž je přenjesenje tuteje aktiwity z mod_elang 1.x schwalił. Składuje so, zo by schwalenje slědowajomne wostało.';
$string['privacy_metadata_elang_response'] = 'Za kóžde prózdne městno, kotruž wobdźělnik w pospyće wotmołwi, aktiwita tekst wotmołwy a jeje hódnoćenje składuje.';
$string['privacy_metadata_elang_response_accepted'] = 'Hač bu wotmołwa za tute prózdne městno jako prawa akceptowana.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Najwyša runina pokiwa, kotraž bu wobdźělnikej za tute prózdne městno pokazana.';
$string['privacy_metadata_elang_response_responsetext'] = 'Tekst, kotryž je wobdźělnik do tuteje prózdne městna zapisał.';
$string['privacy_metadata_elang_response_resultstate'] = 'Zaklasifikowanje, kotrež je hódnoćenje tutej wotmołwje dało (dokładna, spóznane słowo, wopačna abo prózdna).';
$string['privacy_metadata_elang_response_score'] = 'Ličba dypkow, kotrež je tuta wotmołwa po přiwzatym wotćahu za pokiw přinjesła.';
$string['privacy_metadata_elang_response_timecreated'] = 'Čas prěnjeho pósłanja tuteje wotmołwy.';
$string['privacy_metadata_elang_response_timemodified'] = 'Čas poslednjeje aktualizacije tuteje wotmołwy.';
$string['privacy_metadata_elang_response_tries'] = 'Kak husto je wobdźělnik za tute prózdne městno wotmołwu pósłał.';
$string['privacy_metadata_elang_version'] = 'Za kóždu wersiju wobsaha aktiwita składuje, kotry wužiwar ju jako posledni změni.';
$string['privacy_metadata_elang_version_usermodified'] = 'Wužiwar, kotryž je tutu wersiju wobsaha jako posledni změnił. Składuje so, zo by so slědować hodźało, štó je wobsah zwučowanja wobdźěłał.';
$string['privacy_provider_externallink'] = 'Hdyž zwučowanje na wideju YouTube abo Vimeo bazuje, zwjazuje jeho wočinjenje wobhladowak wobdźělnika z tym poskićowarjom. Tykač sam ničo njesćele, ale zwisk wuwoła aktiwita. Hač k tomu docyła dochadźa, wotwisuje wot nastajenja strony k přizwolenju poskićowarja a wot přihłosowanja wobdźělnika.';
$string['privacy_provider_ipaddress'] = 'IP-adresa, z kotrejež so wobhladowak wobdźělnika zwjazuje.';
$string['privacy_provider_useragent'] = 'Daty wo wobhladowaku a graće, kotrež wobhladowak sćele.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Prašeć so, prjedy hač so YouTube abo Vimeo zasadźi';
$string['providerconsent_desc'] = 'Zwučowanja, kotrež na wideju YouTube abo Vimeo bazuja, pokazuja zdźělenku město widea a zasadźa jo hakle po přihłosowanju wobdźělnika. Bjez toho dóstawa poskićowar IP-adresu a daty wobhladowaka wobdźělnika hnydom po wočinjenju strony — prjedy hač něchtó wothrawanje mačka. Znjemóžńće to jenož, hdyž waša institucija tute přizwolenje hinak wobstara.';
$string['report_actions'] = 'Akcije';
$string['report_answered'] = 'Wotmołwjene';
$string['report_attemptnumber'] = 'Pospyt';
$string['report_back'] = 'Wróćo ke wšěm pospytam';
$string['report_correct'] = 'Prawe';
$string['report_delete'] = 'Zhašeć';
$string['report_deleteconfirm'] = 'Tutón pospyt a wšě jeho wotmołwy na přeco zhašeć? Njeda so cofnyć.';
$string['report_deleted'] = 'Pospyt je zhašeny.';
$string['report_exact'] = 'Dokładne';
$string['report_export'] = 'Eksportować';
$string['report_filterany'] = 'Wšě';
$string['report_filterapply'] = 'Filtry nałožić';
$string['report_filterattempt'] = 'Čisło pospyta';
$string['report_filterfrom'] = 'Započate wot';
$string['report_filterrangeerror'] = 'Kónc časoweho wotrězka je před jeho spočatkom.';
$string['report_filterreset'] = 'Filtry wuprózdnić';
$string['report_filterstate'] = 'Staw';
$string['report_filterto'] = 'Započate do';
$string['report_filteruser'] = 'Wobdźělnik';
$string['report_finished'] = 'Dokónčene';
$string['report_heading'] = 'Pospyty';
$string['report_hinted'] = 'Z pokiwom';
$string['report_hints'] = 'Runina pokiwa';
$string['report_kpianswered'] = 'Wotmołwjene';
$string['report_kpiattempts'] = 'Pokazane pospyty';
$string['report_kpiaverage'] = 'Přerězna ličba dypkow (dokónčene)';
$string['report_kpicorrect'] = 'Akceptowane';
$string['report_kpiexact'] = 'Docyła prawe';
$string['report_kpifinished'] = 'Dokónčene';
$string['report_kpihinted'] = 'Su pokiw wužili';
$string['report_kpihintedgaps'] = 'Su pokiw trjebali';
$string['report_noattempts'] = 'Hišće žane pospyty.';
$string['report_nogaps'] = 'Wersija, na kotrejž bu tutón pospyt wuwjedźeny, žane prózdne městna nima.';
$string['report_nomatchingattempts'] = 'Žadyn pospyt tutym filtram njewotpowěduje.';
$string['report_noresponse'] = 'Bjez wotmołwy';
$string['report_response'] = 'Wotmołwa';
$string['report_result'] = 'Wuslědk';
$string['report_result_empty'] = 'Prózdna';
$string['report_result_exact'] = 'Dokładna';
$string['report_result_incorrect'] = 'Wopačna';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Spóznana';
$string['report_score'] = 'Ličba dypkow';
$string['report_solution'] = 'Rozrisanje';
$string['report_started'] = 'Započate';
$string['report_state'] = 'Staw';
$string['report_state_abandoned'] = 'Spušćeny';
$string['report_state_finished'] = 'Dokónčeny';
$string['report_state_inprogress'] = 'Běži';
$string['report_transcript'] = 'Transkript';
$string['report_tries'] = 'Pospyty wotmołwy';
$string['report_user'] = 'Wobdźělnik';
$string['report_view'] = 'Pokazać';
$string['reports'] = 'Rozprawy';
$string['resetattempts'] = 'Wšě pospyty a wotmołwy wobdźělnikow zhašeć';
$string['solutionavailability'] = 'Transkript z rozrisanjemi za wobdźělnikow';
$string['solutionavailability_aftersubmission'] = 'Po dokónčenju pospyta';
$string['solutionavailability_always'] = 'Kóždy čas';
$string['solutionavailability_help'] = 'Hdy smědźa wobdźělnicy dospołny transkript z rozrisanjom kóždeho prózdneho městna sćahnyć.

* Ženje — jenož wučerjo móža jón sćahnyć.
* Po dokónčenju pospyta — wobdźělnik móže jón sćahnyć, hdyž je w tutej aktiwiće pospyt dokónčił.
* Kóždy čas — wobdźělnik móže jón tež do wotmołwjenja sćahnyć.

Wučerski personal z prawom móže jón přeco sćahnyć, njedźiwajo na tute nastajenje.';
$string['solutionavailability_never'] = 'Ženje';
$string['subplugintype_elangscript'] = 'Předźěłar pisma';
$string['subplugintype_elangscript_plural'] = 'Předźěłarjo pisma';
$string['subtitleposition'] = 'Pokazanje podtitulow';
$string['subtitleposition_below'] = 'Pod medijom';
$string['subtitleposition_help'] = 'Hdźež so interaktiwne podtitule pokazuja.

* Pod medijom — cyły transkript steji pod medijom we swójskim suwanskim wobłuku a slěduje wothraću.
* We wideju, deleka abo horjeka — jenož tón podtitul so na medij rysuje, kotryž runje běži.

Medij jenož ze zwukom wobraz nima, na kotryž by so rysowało, tohodla přeco pokazanje pod medijom wužiwa. Nastajenje samo so wobchowa a płaći zaso, hdyž aktiwita widejo wužiwa.';
$string['subtitleposition_overlaybottom'] = 'We wideju — deleka';
$string['subtitleposition_overlaytop'] = 'We wideju — horjeka';
$string['task_migratev1activities'] = 'Aktiwity wersije 1 přenjesć';
$string['transcriptheading'] = 'Transkript za wobdźělnikow';
$string['validate_cueafterend'] = '{$a->where}: kónči so při {$a->endtime} ms, potajkim po mediju ({$a->duration} ms). Wothrawanje tam ženje njedóńdźe.';
$string['validate_cueendbeforestart'] = '{$a}: kónc njeje po spočatku.';
$string['validate_cuewhere'] = 'Wotrězk {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'Rozrisanje za {$a} je prózdne.';
$string['validate_hintlevels'] = 'Runiny pokiwow za {$a} zwjazany rjad njetworja, kotryž so z 1 započina.';
$string['validate_negativetime'] = '{$a}: spočatny čas je před spočatkom nahrawanja.';
$string['validate_nocues'] = 'Wersija žane wotrězki nima.';
$string['validate_nogaps'] = 'Wersija žane prózdne městna za wotmołwjenje nima.';
$string['validate_nonpositivelength'] = 'Dołhosć w znamješkach za {$a} dyrbi pozitiwna być.';
$string['validate_rangeoutside'] = 'Wobłuk znamješkow za {$a} zwonka jeho transkripta leži.';
$string['validate_rangeoverlap'] = 'Wobłuk znamješkow za {$a} so z druhim prózdnym městnom přerězuje.';
$string['validate_unknownalgorithm'] = 'Algoritmus hódnoćenja „{$a->algorithm}“ za {$a->where} so njespóznawa.';
$string['validate_where'] = 'prózdne městno {$a->gapkey} w wotrězku {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Prózdne městno {$a->gapkey}: algoritmus hódnoćenja je „{$a->actual}“, wočakowany bě „{$a->expected}“.';
$string['verify_attemptcount'] = 'Ličba přenjesenych pospytow je {$a->actual}, wočakowane běchu rozdźělni wobdźělnicy z 1.x: {$a->expected}.';
$string['verify_jarothreshold'] = 'Prah přirunowanja wotmołwow je {$a->actual}, wočakowany bě {$a->expected}.';
$string['verify_missingattempt'] = 'Wužiwar {$a}: wočakowany bě přenjeseny pospyt, žadyn njebu namakany.';
$string['verify_missingcue'] = 'Wotrězk {$a}: přenjeseny wotrězk faluje.';
$string['verify_missinggap'] = 'Prózdne městno {$a}: přenjesene prózdne městno faluje.';
$string['verify_missinghint'] = 'Prózdne městno {$a}: wersija 1 je tu pomoc dowoliła, ale žadyn pokiw njebu přenjeseny.';
$string['verify_orphancue'] = 'Wotrězk {$a}: wotpowědny wotrězk z wersije 1 njebu namakany.';
$string['verify_orphangap'] = 'Prózdne městno {$a}: wotpowědne prózdne městno z wersije 1 njebu namakana.';
$string['verify_rangemismatch'] = 'Prózdne městno {$a}: wobłuk znamješkow ze žórłom wersije 1 njewotpowěduje.';
$string['verify_responsecount'] = 'Wužiwar {$a->userid}: ličba přenjesenych wotmołwow je {$a->actual}, wočakowana bě {$a->expected}.';
$string['verify_solutionmismatch'] = 'Prózdne městno {$a->gapkey}: rozrisanje je „{$a->actual}“, wočakowane bě „{$a->expected}“.';
$string['verify_transcriptmismatch'] = 'Wotrězk {$a}: transkript ze žórłom wersije 1 njewotpowěduje.';
$string['verify_unexpectedhint'] = 'Prózdne městno {$a}: wersija 1 tu pomoc njedowoli, ale pokiw bu přenjeseny.';
