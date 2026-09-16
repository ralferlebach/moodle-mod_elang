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
 * Czech strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * No 1.3.5 translation existed to follow, so terminology comes from Moodle's own
 * Czech pack — účastník, pokus, činnost, titulky, nápověda.
 *
 * Czech shares Polish's difficulty with counts: nouns take different forms for
 * 1, for 2–4 and for 5 and above, so "{$a} segment" is wrong for most values.
 * Counts are therefore written as "Segmenty: {$a}", which needs no agreement.
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

$string['allowedlanguages'] = 'Povolené jazyky obsahu';
$string['allowedlanguages_desc'] = 'Jazyky obsahu nabízené při vytváření nebo úpravě videodiktátu. Nevyberte žádný, aby se nabízel úplný seznam jazyků. Činnost si uložený jazyk ponechá, i když jej zde později odeberete.';
$string['allowtranscriptdownload'] = 'Stahování přepisu účastníky';
$string['allowtranscriptdownload_help'] = 'Když je zapnuto, mohou si účastníci stáhnout pracovní list přepisu se skrytou každou mezerou jako PDF, Word, OpenDocument nebo text.

Ve výchozím stavu je vypnuto. Oprávnění vyučující si přepis mohou stáhnout vždy, bez ohledu na toto nastavení.';
$string['allowtranscriptdownload_label'] = 'Účastníci mohou stáhnout pracovní list';
$string['completiondetail_completionfinishattempt'] = 'Dokončit pokus';
$string['completionfinishattempt'] = 'Účastník musí dokončit pokus';
$string['cuepausemode'] = 'Pauza na konci titulku';
$string['cuepausemode_auto'] = 'Automaticky';
$string['cuepausemode_help'] = 'Zda se médium zastaví na konci titulku.

* Automaticky — přehrávání pokračuje a zastaví se na konci titulku jen po dobu, kdy se na tomto titulku pracuje, tedy po kliknutí na něj nebo na některou z jeho mezer, případně když je v některé z nich klávesové zaměření.
* Zastavit u každého nezodpovězeného titulku — přehrávání se zastaví na konci každého titulku, v němž zbývá prázdná mezera, a čeká na pokračování.
* Nikdy nezastavovat — přehrávání běží až do konce média.

Ani jeden z prvních dvou režimů se nezastaví u titulku, jehož mezery jsou všechny vyplněné: to je hotová práce a zastavení by si vyžádalo stisk klávesy bez jakéhokoli účinku. Znamená to také, že druhý průchod cvičením se zastaví jen tam, kde ještě něco chybí.';
$string['cuepausemode_nostop'] = 'Nikdy nezastavovat';
$string['cuepausemode_stop'] = 'Zastavit u každého nezodpovězeného titulku';
$string['editcontent'] = 'Upravit obsah';
$string['editor_addcue'] = 'Přidat segment';
$string['editor_addgap'] = 'Vytvořit mezeru z výběru';
$string['editor_addhint'] = 'Přidat nápovědu';
$string['editor_addvariant'] = 'Přidat variantu';
$string['editor_advanced'] = 'Pokročilá nastavení';
$string['editor_algoexact'] = 'Přesná shoda';
$string['editor_algorithm'] = 'Porovnávání odpovědí';
$string['editor_algowordrecognized'] = 'Přijímat blízké odpovědi';
$string['editor_answers'] = 'Přijímané varianty';
$string['editor_autosaved'] = 'Všechny změny byly uloženy.';
$string['editor_autosaveerror'] = 'Automatické uložení selhalo — použijte tlačítko Uložit a zkuste to znovu.';
$string['editor_captureend'] = 'Nastavit konec z přehrávání';
$string['editor_capturestart'] = 'Nastavit začátek z přehrávání';
$string['editor_cueactions'] = 'Akce se segmentem';
$string['editor_cuecount'] = 'Segmenty: {$a}';
$string['editor_cuenotsaved'] = 'Neuloženo';
$string['editor_currentmedia'] = 'Aktuální médium:';
$string['editor_deletecue'] = 'Smazat segment';
$string['editor_deletegap'] = 'Smazat mezeru';
$string['editor_emptytranscript'] = '(zatím žádný text)';
$string['editor_endtime'] = 'Čas konce';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Mezery: {$a}';
$string['editor_gaprange'] = 'Pozice mezery (znaky)';
$string['editor_gotomedia'] = 'Přejít na Média';
$string['editor_heading'] = 'Upravit titulky a mezery';
$string['editor_hints'] = 'Nápovědy';
$string['editor_hinttext'] = 'Text nápovědy';
$string['editor_hinttype'] = 'Typ';
$string['editor_hinttype_firstletter'] = 'První písmeno';
$string['editor_hinttype_partial'] = 'Částečná';
$string['editor_hinttype_solution'] = 'Řešení';
$string['editor_hinttype_text'] = 'Volný text';
$string['editor_hinttype_translation'] = 'Překlad';
$string['editor_hinttype_wordlength'] = 'Délka slova';
$string['editor_import'] = 'Importovat titulky';
$string['editor_importappend'] = 'Připojit ke stávajícím segmentům';
$string['editor_importapply'] = 'Importovat';
$string['editor_importcancel'] = 'Zrušit';
$string['editor_importcheck'] = 'Zkontrolovat obsah';
$string['editor_importchecking'] = 'Kontroluje se…';
$string['editor_importcuecount'] = 'Nalezené segmenty';
$string['editor_importduration'] = 'Délka';
$string['editor_importedcues'] = 'Importované segmenty: {$a}';
$string['editor_importfilehint'] = 'Vyberte soubor WebVTT (.vtt) nebo SubRip (.srt) s titulky.';
$string['editor_importformat'] = 'Formát';
$string['editor_importfromfile'] = 'Nahrát soubor';
$string['editor_importfromtext'] = 'Vložit text';
$string['editor_importgapcount'] = 'Nalezené mezery';
$string['editor_importhint'] = 'Vložte obsah WebVTT nebo SubRip a importujte jej jako segmenty.';
$string['editor_importparseerror'] = 'Tento obsah se nepodařilo přečíst jako WebVTT ani SubRip.';
$string['editor_importpastedtext'] = 'Vložený text';
$string['editor_importreaderror'] = 'Soubor se nepodařilo přečíst.';
$string['editor_importready'] = 'Připraveno k importu';
$string['editor_importreplace'] = 'Nahradit všechny segmenty';
$string['editor_importreplacedcues'] = 'Segmenty nahrazeny; nově importované: {$a}';
$string['editor_importsource'] = 'Zdroj';
$string['editor_importsummary'] = 'Co bylo nalezeno';
$string['editor_importtoolarge'] = 'Tento soubor má {$a->size}; import přijímá nejvýše {$a->max}.';
$string['editor_importwrongtype'] = 'Vyberte soubor s titulky ({$a}).';
$string['editor_insertafter'] = 'Vložit segment za';
$string['editor_insertbefore'] = 'Vložit segment před';
$string['editor_invalidtime'] = 'Zadejte čas ve tvaru mm:ss.SSS, například 01:05.400.';
$string['editor_linkurl'] = 'Odkaz k dohledání';
$string['editor_linkurl_help'] = 'Zobrazuje se vedle mezery jako místo, kde lze slovo dohledat. Ponechte prázdné, pokud žádné nabízet nechcete.';
$string['editor_loaderror'] = 'Editor se nepodařilo načíst. Načtěte stránku znovu.';
$string['editor_loading'] = 'Načítá se editor…';
$string['editor_maxlength'] = 'Maximální délka';
$string['editor_maxlength_help'] = 'Omezuje, kolik může účastník napsat. 0 znamená bez omezení.';
$string['editor_media'] = 'Média';
$string['editor_mediafile'] = 'Nahraný soubor';
$string['editor_mediakind'] = 'Typ média';
$string['editor_medianone'] = 'Žádné';
$string['editor_mediaprovider'] = 'Poskytovatel';
$string['editor_mediaproviderref'] = 'Odkaz poskytovatele';
$string['editor_mediaproviderrefhint'] = 'Identifikátor nebo odkaz videa v běžném tvaru (např. youtu.be/…).';
$string['editor_mediasaved'] = 'Médium uloženo.';
$string['editor_mediaurl'] = 'Přímá adresa URL média';
$string['editor_nocues'] = 'Zatím žádné segmenty. Přidejte segment nebo importujte titulky.';
$string['editor_nocueselected'] = 'Vyberte segment ze seznamu a upravte jej.';
$string['editor_nocuesmatch'] = 'Tomuto hledání neodpovídá žádný segment.';
$string['editor_nogaps'] = 'Žádné mezery';
$string['editor_nomedia'] = 'žádné';
$string['editor_nomedianotice'] = 'Nejprve přidejte video nebo zvukový soubor na kartě Média. Titulky se časují podle média, editor jej tedy potřebuje dřív, než budete moci pracovat se segmenty a mezerami.';
$string['editor_novideotrack'] = 'Tento prohlížeč neumí dekódovat video stopu tohoto média (přehrává se jen zvuk); účastníci by viděli černý obraz. Překódujte soubor do H.264/MP4 (například pomocí ffmpeg nebo HandBrake) a nahrajte jej znovu.';
$string['editor_onboardinggaps'] = 'Označte slovo v segmentu a udělejte z něj mezeru.';
$string['editor_onboardingimport'] = 'Importujte titulky WebVTT nebo SubRip, případně přidejte segmenty ručně.';
$string['editor_onboardingintro'] = 'Vytvořte cvičení ve třech krocích:';
$string['editor_onboardingmedia'] = 'Vyberte médium (nahrání, adresa URL nebo poskytovatel).';
$string['editor_onboardingtitle'] = 'Začněte své cvičení';
$string['editor_onlywarnings'] = 'Jen segmenty s upozorněními';
$string['editor_parsegaps'] = 'Rozpoznávat značky mezer: [slovo] vytvoří mezeru s povolenými nápovědami, {slovo} bez nich.';
$string['editor_penalty'] = 'Srážka';
$string['editor_poster'] = 'Úvodní obrázek';
$string['editor_preview'] = 'Náhled pro účastníka';
$string['editor_problem_afterduration'] = 'Konec leží za koncem média.';
$string['editor_problem_endbeforestart'] = 'Konec je shodný se začátkem nebo mu předchází.';
$string['editor_problem_negativestart'] = 'Začátek předchází začátku nahrávky.';
$string['editor_publish'] = 'Publikovat';
$string['editor_publishblocked'] = 'Některé titulky zatím nelze uložit. Opravte je před zveřejněním.';
$string['editor_published'] = 'Verze publikována.';
$string['editor_removehint'] = 'Odebrat nápovědu';
$string['editor_removevariant'] = 'Odebrat';
$string['editor_repaircue'] = 'Opravit konec';
$string['editor_ruleapplied'] = 'Podle pravidla vytvořeny mezery: %count%.';
$string['editor_ruleapply'] = 'Použít mezery: %count%';
$string['editor_ruleerror'] = 'Mezery se nepodařilo vygenerovat.';
$string['editor_ruleeverynth'] = 'Každé n-té slovo';
$string['editor_rulefound'] = 'Pravidlo našlo mezery: %count%.';
$string['editor_rulegenerate'] = 'Vygenerovat mezery';
$string['editor_ruleinterval'] = 'Interval (n)';
$string['editor_ruletype'] = 'Pravidlo mezer';
$string['editor_rulewordlist'] = 'Slova ke skrytí';
$string['editor_rulewords'] = 'Seznam slov';
$string['editor_save'] = 'Uložit koncept';
$string['editor_saved'] = 'Koncept uložen.';
$string['editor_savedwithproblems'] = 'Uloženo, co uložit šlo; některé titulky vyžadují pozornost.';
$string['editor_saveerror'] = 'Koncept se nepodařilo uložit.';
$string['editor_savemedia'] = 'Uložit médium';
$string['editor_saving'] = 'Ukládá se…';
$string['editor_searchcues'] = 'Prohledat segmenty';
$string['editor_selecttext'] = 'Nejprve v přepisu označte slovo, které se má skrýt.';
$string['editor_solution'] = 'Řešení';
$string['editor_starttime'] = 'Čas začátku';
$string['editor_transcript'] = 'Přepis';
$string['editor_unsaved'] = 'Neuložené změny';
$string['editor_uploadmedia'] = 'Nahrát mediální soubory';
$string['editor_variantisregex'] = 'Považovat {$a} za regulární výraz';
$string['editor_variantmatching'] = 'Jak se porovnávají přijímané varianty';
$string['editor_warnemptysolution'] = 'Mezera bez řešení';
$string['editor_warnnotranscript'] = 'Žádný text';
$string['editor_warntiming'] = 'Konec nenásleduje po začátku';
$string['editor_waveform'] = 'Průběh zvuku';
$string['elang:addinstance'] = 'Přidávat nové videodiktáty';
$string['elang:attempt'] = 'Vypracovávat videodiktát';
$string['elang:deleteattempts'] = 'Mazat pokusy účastníků';
$string['elang:exportreports'] = 'Exportovat sestavy s osobními údaji';
$string['elang:exportsolution'] = 'Exportovat úplný přepis s řešeními';
$string['elang:exporttranscript'] = 'Exportovat pracovní list jako dokument';
$string['elang:manage'] = 'Vytvářet a upravovat obsah cvičení';
$string['elang:useregex'] = 'Používat regulární výrazy v přijímaných odpovědích';
$string['elang:view'] = 'Zobrazit videodiktát';
$string['elang:viewreports'] = 'Zobrazit sestavy účastníků';
$string['error_attemptnotinprogress'] = 'Tento pokus již neprobíhá.';
$string['error_couldnotobtainlock'] = 'Pro tuto operaci se nepodařilo získat zámek. Zkuste to znovu.';
$string['error_draftrevisionmismatch'] = 'Tento koncept se od jeho načtení změnil. Načtěte jej znovu a zkuste to ještě jednou.';
$string['error_duplicatecuekey'] = 'Dva segmenty sdílejí klíč „{$a}“; každý segment potřebuje jedinečný klíč.';
$string['error_duplicategapkey'] = 'Dvě mezery v jednom segmentu sdílejí klíč „{$a}“; každá mezera potřebuje jedinečný klíč.';
$string['error_duplicatehintlevel'] = 'Mezera má dvě nápovědy na úrovni {$a}; každá úroveň musí být jedinečná.';
$string['error_gapnotinattemptversion'] = 'Tato mezera nepatří k verzi cvičení tohoto pokusu.';
$string['error_importnocues'] = 'Z tohoto obsahu se nepodařilo přečíst žádné titulky. Soubor WebVTT nebo SubRip má nad každým titulkem časový řádek, například 00:00:01.000 --> 00:00:04.000.';
$string['error_importnotutf8'] = 'Tento soubor není platné UTF-8. Pravděpodobně byl uložen ve starším kódování — otevřete jej v textovém editoru a uložte znovu jako UTF-8.';
$string['error_importtoolarge'] = 'Tento soubor má {$a->size}; import přijímá nejvýše {$a->max}. Soubor s titulky k záznamu výuky bývá mnohem menší, takže o titulky nejspíš nejde.';
$string['error_importtoomanycues'] = 'Tento soubor obsahuje titulky v počtu {$a->count}; import přijímá nejvýše {$a->max}.';
$string['error_invalidcuepausemode'] = 'Vyberte jednu z nabízených možností pauzy na konci titulku.';
$string['error_invalidgradingalgorithm'] = 'Algoritmus hodnocení „{$a}“ není ani exact, ani wordrecognized.';
$string['error_invalidhinttype'] = 'Typ nápovědy „{$a}“ není jedním z povolených typů.';
$string['error_invalidisregex'] = 'Značka regulárního výrazu u varianty musí být 0 nebo 1.';
$string['error_invalidmediakind'] = 'Zvolený typ média není file, url ani provider.';
$string['error_invalidpenalty'] = 'Srážka za nápovědu musí být mezi 0 a 1.';
$string['error_invalidproviderref'] = '„{$a}“ není rozpoznaný identifikátor ani odkaz videa pro tohoto poskytovatele.';
$string['error_invalidregexpattern'] = '„{$a}“ není platný regulární výraz.';
$string['error_invalidsolutionavailability'] = 'Vyberte jednu z nabízených možností, kdy mohou účastníci vidět přepis s řešeními.';
$string['error_invalidsourceurl'] = 'Zadejte úplnou adresu začínající http:// nebo https://, případně odkaz na YouTube či Vimeo.';
$string['error_invalidsubtitleposition'] = 'Vyberte jednu z nabízených možností umístění titulků.';
$string['error_invalidv1cuejson'] = 'Tento segment z verze 1 se nepodařilo zpracovat.';
$string['error_negativegapoffset'] = 'Pozice a délka mezery nesmí být záporné.';
$string['error_noaccesstoattempt'] = 'K tomuto pokusu nemáte přístup.';
$string['error_nomorehints'] = 'Pro tuto mezeru už nejsou další nápovědy.';
$string['error_nopublishedversion'] = 'Toto cvičení zatím nemá publikovaný obsah.';
$string['error_responsetoolong'] = 'Vaše odpověď je příliš dlouhá. Maximum pro tuto mezeru je {$a} znaků.';
$string['error_solutionnotavailable'] = 'Přepis s řešeními pro vás v této činnosti není dostupný.';
$string['error_staleattemptstate'] = 'Váš pohled na tento pokus je zastaralý. Načtěte aktuální stav a zkuste to znovu.';
$string['error_transcriptnotavailable'] = 'V této činnosti není ke stažení žádný přepis.';
$string['error_unknowngaprule'] = 'Neznámý typ pravidla mezer „{$a}“.';
$string['error_unknownmediaprovider'] = '„{$a}“ není jedním z podporovaných poskytovatelů médií.';
$string['error_versionnotadraft'] = 'Upravovat lze pouze verzi ve stavu konceptu.';
$string['error_versionnotfound'] = 'Tato verze cvičení již neexistuje.';
$string['error_versionnotpublishable'] = 'Tuto verzi nelze publikovat: {$a}';
$string['export_audienceaftersubmission'] = 'Účastníci si to mohou stáhnout po dokončení pokusu';
$string['export_audiencealways'] = 'Účastníci si to mohou stáhnout kdykoli';
$string['export_audiencestaff'] = 'Jen oprávnění vyučující — účastníkům není k dispozici';
$string['export_docx'] = 'Stáhnout jako Word (DOCX)';
$string['export_downloadpdf'] = 'Stáhnout PDF';
$string['export_heading'] = 'Exportovat přepis';
$string['export_intro'] = 'Stáhněte si přepis tohoto cvičení v několika formátech.';
$string['export_moreformats'] = 'Další formáty';
$string['export_nocontent'] = 'Zatím není publikován žádný přepis k exportu.';
$string['export_odt'] = 'Stáhnout jako OpenDocument (ODT)';
$string['export_pdf'] = 'Stáhnout jako PDF';
$string['export_solution'] = 'Přepis s řešeními';
$string['export_solutionhint'] = 'Úplný text s viditelným řešením každé mezery.';
$string['export_text'] = 'Stáhnout jako text';
$string['export_versionnote'] = 'Exporty vycházejí z aktuálně publikované verze tohoto cvičení.';
$string['export_worksheet'] = 'Pracovní list (mezery skryté)';
$string['export_worksheethint'] = 'Text se skrytou každou mezerou. Připraveno k rozdání jako materiál pro účastníky.';
$string['exporttranscript'] = 'Exportovat přepis';
$string['filearea_media'] = 'Média';
$string['filearea_poster'] = 'Úvodní obrázek';
$string['gradingheading'] = 'Hodnocení odpovědí';
$string['import_badtiming'] = 'Časový řádek se nepodařilo přečíst: {$a}';
$string['import_emptytranscript'] = 'Segment bez textu byl přeskočen.';
$string['import_warnlinetoolong'] = 'Blok {$a->block} byl přeskočen: obsahuje řádek delší než {$a->max} znaků, což není řádek titulku.';
$string['jarothreshold'] = 'Práh podobnosti';
$string['jarothreshold_help'] = 'U mezer nastavených na „Přijímat blízké odpovědi“ jde o minimální Jarovu podobnost mezi očekávanou a napsanou odpovědí. Hodnota 1 vyžaduje přesnou shodu po normalizaci dané jazykem; nižší hodnoty připouštějí čím dál odlišnější zápisy.';
$string['jarothresholdrange'] = 'Práh musí být mezi 0 a 1.';
$string['language'] = 'Jazyk obsahu';
$string['language_help'] = 'Vyberte jazyk obsahu cvičení. Určuje, jak se porovnávají odpovědi, včetně velikosti písmen a transliterace. Zvolte „Obecný (neurčeno)“, pokud se nemá použít zpracování závislé na jazyce. Nové verze obsahu z tohoto nastavení vycházejí.';
$string['language_none'] = 'Obecný (neurčeno)';
$string['media_cuenote'] = 'Stávající titulky a mezery se při změně média zachovají. Jejich časy se neupravují, zkontrolujte je proto poté v editoru.';
$string['media_current'] = 'Aktuální médium';
$string['media_heading'] = 'Média';
$string['media_intro'] = 'Vyberte video nebo zvuk, na kterém toto cvičení stojí. Titulky se podle něj časují, proto je na řadě první.';
$string['media_none'] = 'Pro toto cvičení zatím nebylo nastaveno médium.';
$string['media_othersource'] = 'Jiný zdroj';
$string['media_providerhint'] = 'Rozpoznaní poskytovatelé: {$a}. Jakákoli jiná adresa se použije jako přímá adresa URL média.';
$string['media_sourceurl'] = 'Adresa URL média';
$string['media_sourceurl_help'] = 'Vložte adresu videa místo nahrání souboru — odkaz na YouTube či Vimeo, nebo přímou adresu mediálního souboru.

Adresa zadaná zde nahradí nahraný soubor. Ponechte ji prázdnou, chcete-li použít nahrání výše.

Video poskytovatele se přehrává v jeho vlastním rámci, který nehlásí čas přehrávání. Takové cvičení vždy zobrazuje titulky pod médiem a nikdy se nezastavuje na konci titulku.

**Kam údaje putují.** Rámec YouTube nebo Vimea spojí prohlížeč každého účastníka s touto společností, která tak obdrží jeho adresu IP a údaje o zařízení. Ve výchozím nastavení se cvičení předtím zeptá. Má-li vaše instituce vlastní mediální server — Opencast, Panopto, Kaltura či podobný — vložte místo toho přímou adresu souboru odtud: bude brána jako běžná adresa URL média, zachová zvolené umístění titulků i nastavení pauzy a nezapojí se žádná třetí strana.';
$string['migratev1_approvalheading'] = 'Převedeno, čeká na kontrolu';
$string['migratev1_approvebutton'] = 'Schválit tento převod';
$string['migratev1_approved'] = 'Videodiktát {$a} byl označen jako schválený.';
$string['migratev1_colactivity'] = 'Činnost';
$string['migratev1_colalgorithm'] = 'Algoritmus hodnocení';
$string['migratev1_colcues'] = 'Segmenty';
$string['migratev1_colgaps'] = 'Mezery';
$string['migratev1_colissues'] = 'Problémy';
$string['migratev1_collearners'] = 'Účastníci';
$string['migratev1_confirmdecommission'] = 'Tato operace NEVRATNĚ odstraní původní tabulky verze 1 a elang.options. Nelze ji vzít zpět. Pokračovat?';
$string['migratev1_confirmmigrate'] = 'Tato operace zařadí do fronty úlohu na pozadí, která zapíše data verze 2 pro každou činnost uvedenou výše. Tabulky verze 1 a elang.options zůstanou nedotčené. Pokračovat?';
$string['migratev1_decommissionblocked'] = 'Odstranění je stále zablokováno; viz seznam níže.';
$string['migratev1_decommissionblockedintro'] = 'Odstranění je zablokováno, dokud:';
$string['migratev1_decommissionbutton'] = 'Odstranit původní data verze 1';
$string['migratev1_decommissioned'] = 'Původní data verze 1 byla odstraněna.';
$string['migratev1_decommissionheading'] = 'Vyřazení dat verze 1';
$string['migratev1_decommissionready'] = 'Všechny činnosti verze 1 byly převedeny a schváleny. Původní tabulky a elang.options lze nyní odstranit. Tuto operaci nelze vzít zpět.';
$string['migratev1_heading'] = 'Převést činnosti verze 1';
$string['migratev1_migratebutton'] = 'Převést tyto činnosti';
$string['migratev1_noissues'] = 'Žádné';
$string['migratev1_nonepending'] = 'Na převod nečekají žádné činnosti verze 1.';
$string['migratev1_nonependingapproval'] = 'Na kontrolu nečekají žádné převedené činnosti.';
$string['migratev1_notablespresent'] = 'Na těchto stránkách nebyly nalezeny žádné původní tabulky verze 1. Není co převádět.';
$string['migratev1_parseerrorcount'] = 'Segmenty, které se nepodařilo zpracovat: {$a}';
$string['migratev1_pendingheading'] = 'Zatím nepřevedeno';
$string['migratev1_queued'] = 'Úloha převodu byla zařazena do fronty. Provede se při dalším běhu cronu, nebo ihned přes admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Ověřeno: převedená data odpovídají zdroji verze 1 bez odchylek.';
$string['migratev1_verifieddiscrepancies'] = 'Ověření našlo odchylky oproti zdroji verze 1: {$a}';
$string['migratev1_verifyfailed'] = 'Tuto činnost se nepodařilo ověřit: {$a}';
$string['modulename'] = 'Videodiktát';
$string['modulename_help'] = 'Činnost videodiktát umožňuje účastníkům vyplňovat mezery v časovaných titulcích, zatímco sledují video nebo mu naslouchají.

Vyučující importují soubor titulků WebVTT nebo SubRip, označí slova či slovní spojení jako mezery a nastaví, jak přísně se odpovědi porovnávají. Účastníci procházejí přepis segment po segmentu, žádají o bodově zohledněné nápovědy a dostávají okamžitou zpětnou vazbu.';
$string['modulenameplural'] = 'Videodiktáty';
$string['nav_exportshort'] = 'Export';
$string['nav_media'] = 'Média';
$string['nav_reports'] = 'Pokusy';
$string['nav_subtitles'] = 'Titulky a mezery';
$string['noinstances'] = 'V tomto kurzu nejsou žádné videodiktáty.';
$string['overview_attempts'] = 'Pokusy';
$string['playbackheading'] = 'Přehrávání a titulky';
$string['playbackoverlayhint'] = 'Titulek vložený do obrazu zobrazuje jen ten titulek, který právě běží, takže přehrávání se vždy zastaví na konci titulku, v němž ještě zbývají mezery k vyplnění. Není tu co vybírat.';
$string['playbackproviderhint'] = 'Video z YouTube nebo Vimea přehrává poskytovatel ve vlastním rámci, který nehlásí čas přehrávání. Takové cvičení vždy zobrazuje titulky pod médiem a nikdy se nezastavuje na konci titulku, ať je výše zvoleno cokoli. Nahrané soubory a přímé adresy URL obě nastavení respektují.';
$string['player_check'] = 'Zkontrolovat odpověď';
$string['player_consentaccept'] = 'Načíst video z {$a}';
$string['player_consentdetail'] = 'Přehrání spojí váš prohlížeč s {$a}. {$a} obdrží vaši adresu IP a údaje o vašem zařízení a může číst soubory cookie, které už dříve nastavil. Nic se neodesílá, dokud se nerozhodnete video načíst.';
$string['player_consentheading'] = 'Toto video poskytuje {$a}';
$string['player_exitfullscreen'] = 'Ukončit celou obrazovku';
$string['player_finish'] = 'Dokončit pokus';
$string['player_finished'] = 'Pokus dokončen. Skóre: %score%%';
$string['player_finishincomplete'] = 'Prázdné mezery: {$a}. Dokončit pokus i tak?';
$string['player_fullscreen'] = 'Celá obrazovka';
$string['player_gaplabel'] = 'Mezera %gap%';
$string['player_gaplink'] = 'Otevřít odkaz';
$string['player_hint'] = 'Zobrazit nápovědu';
$string['player_loaderror'] = 'Cvičení se nepodařilo načíst. Načtěte stránku znovu.';
$string['player_loading'] = 'Načítá se cvičení…';
$string['player_nocontent'] = 'Zatím nebyl publikován žádný obsah cvičení. Zkuste to později.';
$string['player_novideotrack'] = 'Váš prohlížeč neumí zobrazit video stopu tohoto média; zvuk se přesto přehraje. Informujte svého vyučujícího.';
$string['player_outdatedattempt'] = 'Toto cvičení bylo od zahájení tohoto pokusu aktualizováno. Pokračujete na dřívějším obsahu; dokončete tento pokus, abyste příště pracovali s aktualizovaným cvičením.';
$string['player_progress'] = 'Zodpovězeno {$a->done} z {$a->total} mezer';
$string['player_ready'] = 'Cvičení připraveno.';
$string['player_scorelabel'] = 'Skóre: %score%%';
$string['player_stateaccepted'] = 'Přijato';
$string['player_statecorrect'] = 'Správně';
$string['player_statehinted'] = 'Použita nápověda';
$string['player_stateincorrect'] = 'Chybně';
$string['player_submitfailed'] = 'Vaši odpověď se nepodařilo uložit. Zkuste to znovu.';
$string['player_transcriptheading'] = 'Přepis';
$string['pluginadministration'] = 'Správa videodiktátu';
$string['pluginname'] = 'Videodiktát';
$string['privacy_metadata_elang'] = 'Pro každou činnost záznam o tom, kdo schválil jednosměrný převod jejího obsahu z verze 1.x.';
$string['privacy_metadata_elang_attempt'] = 'U každého pokusu o cvičení činnost ukládá, kdo jej provedl, kdy, jak daleko došel a jak byl ohodnocen.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Kolik mezer účastník v tomto pokusu zodpověděl.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Pořadové číslo tohoto pokusu pro uživatele a činnost.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Kolik mezer bylo v tomto pokusu přijato jako správné.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Kolik mezer bylo v tomto pokusu zodpovězeno s přesnou shodou znaků.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'U kolika mezer si účastník v tomto pokusu vyžádal nápovědu.';
$string['privacy_metadata_elang_attempt_score'] = 'Skóre dosažené v tomto pokusu.';
$string['privacy_metadata_elang_attempt_state'] = 'Zda pokus probíhá, je dokončen nebo opuštěn.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'Čas dokončení pokusu.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'Čas poslední aktualizace pokusu.';
$string['privacy_metadata_elang_attempt_timestart'] = 'Čas zahájení pokusu.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Celkový počet mezer ve verzi cvičení, k níž se tento pokus vztahuje.';
$string['privacy_metadata_elang_attempt_userid'] = 'Identifikátor uživatele, který pokus provedl.';
$string['privacy_metadata_elang_attempt_versionid'] = 'Verze cvičení, vůči které byl tento pokus proveden.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'Uživatel, který schválil převod této činnosti z mod_elang 1.x. Ukládá se, aby schválení zůstalo dohledatelné.';
$string['privacy_metadata_elang_response'] = 'U každé mezery, kterou účastník v rámci pokusu zodpoví, činnost ukládá text odpovědi a způsob jejího vyhodnocení.';
$string['privacy_metadata_elang_response_accepted'] = 'Zda byla odpověď u této mezery přijata jako správná.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Nejvyšší úroveň nápovědy odhalená účastníkovi u této mezery.';
$string['privacy_metadata_elang_response_responsetext'] = 'Text, který účastník do této mezery napsal.';
$string['privacy_metadata_elang_response_resultstate'] = 'Zařazení, které vyhodnocení této odpovědi přiřadilo (přesná, rozpoznané slovo, chybná nebo prázdná).';
$string['privacy_metadata_elang_response_score'] = 'Body, kterými tato odpověď přispěla, po případné srážce za nápovědu.';
$string['privacy_metadata_elang_response_timecreated'] = 'Čas prvního odeslání této odpovědi.';
$string['privacy_metadata_elang_response_timemodified'] = 'Čas poslední aktualizace této odpovědi.';
$string['privacy_metadata_elang_response_tries'] = 'Kolikrát účastník odeslal odpověď na tuto mezeru.';
$string['privacy_metadata_elang_version'] = 'U každé verze obsahu činnost ukládá, který uživatel ji naposledy změnil.';
$string['privacy_metadata_elang_version_usermodified'] = 'Uživatel, který naposledy změnil tuto verzi obsahu. Ukládá se, aby bylo možné dohledat, kdo obsah cvičení upravoval.';
$string['privacy_provider_externallink'] = 'Když cvičení stojí na videu z YouTube nebo Vimea, jeho otevření spojí prohlížeč účastníka s tímto poskytovatelem. Zásuvný modul sám nic neodesílá, ale spojení vyvolává činnost. Zda k němu vůbec dojde, závisí na nastavení stránek pro souhlas s poskytovateli a na souhlasu účastníka.';
$string['privacy_provider_ipaddress'] = 'Adresa IP, ze které se prohlížeč účastníka připojuje.';
$string['privacy_provider_useragent'] = 'Údaje o prohlížeči a zařízení, které prohlížeč odesílá.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Zeptat se před vložením YouTube nebo Vimea';
$string['providerconsent_desc'] = 'Cvičení stojící na videu z YouTube nebo Vimea zobrazí místo videa upozornění a vloží jej teprve poté, co účastník souhlasí. Bez toho poskytovatel obdrží adresu IP a údaje o prohlížeči účastníka hned při otevření stránky — dříve, než kdokoli stiskne přehrávání. Vypněte to jen tehdy, získává-li vaše instituce tento souhlas jinou cestou.';
$string['report_actions'] = 'Akce';
$string['report_answered'] = 'Zodpovězené';
$string['report_attemptnumber'] = 'Pokus';
$string['report_back'] = 'Zpět na všechny pokusy';
$string['report_correct'] = 'Správné';
$string['report_delete'] = 'Smazat';
$string['report_deleteconfirm'] = 'Trvale smazat tento pokus a všechny jeho odpovědi? Tuto akci nelze vzít zpět.';
$string['report_deleted'] = 'Pokus byl smazán.';
$string['report_exact'] = 'Přesné';
$string['report_export'] = 'Exportovat';
$string['report_filterany'] = 'Vše';
$string['report_filterapply'] = 'Použít filtry';
$string['report_filterattempt'] = 'Číslo pokusu';
$string['report_filterfrom'] = 'Zahájeno od';
$string['report_filterrangeerror'] = 'Konec rozsahu leží před jeho začátkem.';
$string['report_filterreset'] = 'Vymazat filtry';
$string['report_filterstate'] = 'Stav';
$string['report_filterto'] = 'Zahájeno do';
$string['report_filteruser'] = 'Účastník';
$string['report_finished'] = 'Dokončené';
$string['report_heading'] = 'Pokusy';
$string['report_hinted'] = 'S nápovědou';
$string['report_hints'] = 'Úroveň nápovědy';
$string['report_kpianswered'] = 'Zodpovězené';
$string['report_kpiattempts'] = 'Zobrazené pokusy';
$string['report_kpiaverage'] = 'Průměrné skóre (dokončené)';
$string['report_kpicorrect'] = 'Přijaté';
$string['report_kpiexact'] = 'Zcela správné';
$string['report_kpifinished'] = 'Dokončené';
$string['report_kpihinted'] = 'Použili nápovědu';
$string['report_kpihintedgaps'] = 'Potřebovali nápovědu';
$string['report_noattempts'] = 'Zatím žádné pokusy.';
$string['report_nogaps'] = 'Verze, ve které byl tento pokus proveden, nemá mezery.';
$string['report_nomatchingattempts'] = 'Těmto filtrům neodpovídá žádný pokus.';
$string['report_noresponse'] = 'Nezodpovězeno';
$string['report_response'] = 'Odpověď';
$string['report_result'] = 'Výsledek';
$string['report_result_empty'] = 'Prázdná';
$string['report_result_exact'] = 'Přesná';
$string['report_result_incorrect'] = 'Chybná';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Rozpoznaná';
$string['report_score'] = 'Skóre';
$string['report_solution'] = 'Řešení';
$string['report_started'] = 'Zahájeno';
$string['report_state'] = 'Stav';
$string['report_state_abandoned'] = 'Opuštěno';
$string['report_state_finished'] = 'Dokončeno';
$string['report_state_inprogress'] = 'Probíhá';
$string['report_transcript'] = 'Přepis';
$string['report_tries'] = 'Pokusy o odpověď';
$string['report_user'] = 'Účastník';
$string['report_view'] = 'Zobrazit';
$string['reports'] = 'Sestavy';
$string['resetattempts'] = 'Smazat všechny pokusy a odpovědi účastníků';
$string['solutionavailability'] = 'Přepis s řešeními pro účastníky';
$string['solutionavailability_aftersubmission'] = 'Po dokončení pokusu';
$string['solutionavailability_always'] = 'Kdykoli';
$string['solutionavailability_help'] = 'Kdy si účastníci mohou stáhnout úplný přepis s viditelným řešením každé mezery.

* Nikdy — stáhnout si jej mohou jen vyučující.
* Po dokončení pokusu — účastník si jej může stáhnout, jakmile v této činnosti dokončí pokus.
* Kdykoli — účastník si jej může stáhnout i před odpovídáním.

Oprávnění vyučující si jej mohou stáhnout vždy, bez ohledu na toto nastavení.';
$string['solutionavailability_never'] = 'Nikdy';
$string['subplugintype_elangscript'] = 'Obsluha písma';
$string['subplugintype_elangscript_plural'] = 'Obsluhy písma';
$string['subtitleposition'] = 'Zobrazení titulků';
$string['subtitleposition_below'] = 'Pod médiem';
$string['subtitleposition_help'] = 'Kde se zobrazují interaktivní titulky.

* Pod médiem — celý přepis je pod médiem ve vlastní posuvné oblasti a sleduje přehrávání.
* Ve videu, dole nebo nahoře — přes médium se vykresluje jen titulek, který právě běží.

Médium obsahující jen zvuk nemá obraz, do kterého by se dalo kreslit, a proto vždy použije zobrazení pod médiem. Samotné nastavení zůstane zachováno a uplatní se znovu, jakmile činnost použije video.';
$string['subtitleposition_overlaybottom'] = 'Ve videu — dole';
$string['subtitleposition_overlaytop'] = 'Ve videu — nahoře';
$string['task_migratev1activities'] = 'Převést činnosti verze 1';
$string['transcriptheading'] = 'Přepis pro účastníky';
$string['validate_cueafterend'] = '{$a->where}: končí v {$a->endtime} ms, tedy až za médiem ({$a->duration} ms). Přehrávání se tam nikdy nedostane.';
$string['validate_cueendbeforestart'] = '{$a}: konec nenásleduje po začátku.';
$string['validate_cuewhere'] = 'Segment {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'Řešení pro {$a} je prázdné.';
$string['validate_hintlevels'] = 'Úrovně nápovědy pro {$a} netvoří souvislou řadu začínající jedničkou.';
$string['validate_negativetime'] = '{$a}: čas začátku leží před začátkem nahrávky.';
$string['validate_nocues'] = 'Verze nemá žádné segmenty.';
$string['validate_nogaps'] = 'Verze nemá žádné mezery k vyplnění.';
$string['validate_nonpositivelength'] = 'Délka ve znacích u {$a} musí být kladná.';
$string['validate_rangeoutside'] = 'Rozsah znaků u {$a} leží mimo jeho přepis.';
$string['validate_rangeoverlap'] = 'Rozsah znaků u {$a} se překrývá s jinou mezerou.';
$string['validate_unknownalgorithm'] = 'Algoritmus hodnocení „{$a->algorithm}“ u {$a->where} není rozpoznán.';
$string['validate_where'] = 'mezera {$a->gapkey} v segmentu {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Mezera {$a->gapkey}: algoritmus hodnocení je „{$a->actual}“, očekávalo se „{$a->expected}“.';
$string['verify_attemptcount'] = 'Počet převedených pokusů je {$a->actual}, očekávaný počet odlišných účastníků verze 1.x: {$a->expected}.';
$string['verify_jarothreshold'] = 'Práh porovnávání odpovědí je {$a->actual}, očekávalo se {$a->expected}.';
$string['verify_missingattempt'] = 'Uživatel {$a}: očekával se převedený pokus, žádný nebyl nalezen.';
$string['verify_missingcue'] = 'Segment {$a}: převedený segment chybí.';
$string['verify_missinggap'] = 'Mezera {$a}: převedená mezera chybí.';
$string['verify_missinghint'] = 'Mezera {$a}: verze 1 zde povolovala pomoc, ale žádná nápověda nebyla převedena.';
$string['verify_orphancue'] = 'Segment {$a}: nebyl nalezen odpovídající segment z verze 1.';
$string['verify_orphangap'] = 'Mezera {$a}: nebyla nalezena odpovídající mezera z verze 1.';
$string['verify_rangemismatch'] = 'Mezera {$a}: rozsah znaků neodpovídá zdroji verze 1.';
$string['verify_responsecount'] = 'Uživatel {$a->userid}: počet převedených odpovědí je {$a->actual}, očekávalo se {$a->expected}.';
$string['verify_solutionmismatch'] = 'Mezera {$a->gapkey}: řešení je „{$a->actual}“, očekávalo se „{$a->expected}“.';
$string['verify_transcriptmismatch'] = 'Segment {$a}: přepis neodpovídá zdroji verze 1.';
$string['verify_unexpectedhint'] = 'Mezera {$a}: verze 1 zde pomoc nepovolovala, ale nápověda byla převedena.';
