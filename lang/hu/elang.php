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
 * Hungarian strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * No 1.3.5 translation existed to follow, so terminology comes from Moodle's own
 * Hungarian pack — résztvevő, kísérlet, tevékenység, feliratok, tipp.
 *
 * Hungarian has its own reason for the count-neutral phrasing adopted in the
 * RC1 terminology review: after a numeral the noun stays singular, so
 * "{$a} szegmensek" would be wrong in a way that looks like a typo. Counts are
 * written as "Szegmensek: {$a}", which needs no agreement at all.
 *
 * One term is worth a native reviewer's attention: a gap is rendered as
 * **kihagyás**. Hungarian has no single settled word for a blank in a
 * fill-in-the-blank exercise — "hézag" is more a physical gap, "üres hely" is
 * descriptive rather than a term. kihagyás is the closest, but it is a choice.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Engedélyezett tartalomnyelvek';
$string['allowedlanguages_desc'] = 'Azok a tartalomnyelvek, amelyeket a rendszer videódiktálás létrehozásakor vagy szerkesztésekor felkínál. Ha egyet sem választ ki, a teljes nyelvlista jelenik meg. A tevékenység megtartja a mentett nyelvét akkor is, ha később eltávolítja innen.';
$string['allowtranscriptdownload'] = 'Az átirat letöltése a résztvevők által';
$string['allowtranscriptdownload_help'] = 'Ha be van kapcsolva, a résztvevők letölthetik az átirat munkalapját, amelyben minden kihagyás rejtve van, PDF, Word, OpenDocument vagy szöveg formátumban.

Alapértelmezés szerint ki van kapcsolva. Az erre jogosult oktatói munkatársak mindig letölthetik az átiratot, függetlenül ettől a beállítástól.';
$string['allowtranscriptdownload_label'] = 'A résztvevők letölthetik a munkalapot';
$string['completiondetail_completionfinishattempt'] = 'Kísérlet befejezése';
$string['completionfinishattempt'] = 'A résztvevőnek be kell fejeznie egy kísérletet';
$string['cuepausemode'] = 'Szünet a felirat végén';
$string['cuepausemode_auto'] = 'Automatikus';
$string['cuepausemode_help'] = 'Megáll-e a médium a felirat végén.

* Automatikus — a lejátszás folytatódik, és csak addig áll meg a felirat végén, amíg éppen azon a feliraton dolgoznak, vagyis miután rákattintottak arra vagy valamelyik kihagyására, illetve amikor a billentyűzetfókusz valamelyikükön áll.
* Álljon meg minden megválaszolatlan feliratnál — a lejátszás minden olyan felirat végén megáll, amelyben még van üres kihagyás, és megvárja a folytatást.
* Soha ne álljon meg — a lejátszás a médium végéig fut.

Az első kettő egyike sem áll meg olyan feliratnál, amelynek minden kihagyása ki van töltve: az kész munka, és az ottani megállás hatás nélküli billentyűleütést kérne. Ez azt is jelenti, hogy a gyakorlat második átfutása csak ott áll meg, ahol még hiányzik valami.';
$string['cuepausemode_nostop'] = 'Soha ne álljon meg';
$string['cuepausemode_stop'] = 'Álljon meg minden megválaszolatlan feliratnál';
$string['editcontent'] = 'Tartalom szerkesztése';
$string['editor_addcue'] = 'Szegmens hozzáadása';
$string['editor_addgap'] = 'Kihagyás létrehozása a kijelölésből';
$string['editor_addhint'] = 'Tipp hozzáadása';
$string['editor_addvariant'] = 'Változat hozzáadása';
$string['editor_advanced'] = 'Haladó beállítások';
$string['editor_algoexact'] = 'Pontos egyezés';
$string['editor_algorithm'] = 'Válaszok összehasonlítása';
$string['editor_algowordrecognized'] = 'Közeli válaszok elfogadása';
$string['editor_answers'] = 'Elfogadott változatok';
$string['editor_autosaved'] = 'Minden változtatás mentve.';
$string['editor_autosaveerror'] = 'Az automatikus mentés nem sikerült — próbálja újra a Mentés gombbal.';
$string['editor_captureend'] = 'Vége beállítása a lejátszásból';
$string['editor_capturestart'] = 'Kezdet beállítása a lejátszásból';
$string['editor_cueactions'] = 'A szegmens műveletei';
$string['editor_cuecount'] = 'Szegmensek: {$a}';
$string['editor_currentmedia'] = 'Jelenlegi médium:';
$string['editor_deletecue'] = 'Szegmens törlése';
$string['editor_deletegap'] = 'Kihagyás törlése';
$string['editor_emptytranscript'] = '(még nincs szöveg)';
$string['editor_endtime'] = 'Befejezés időpontja';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Kihagyások: {$a}';
$string['editor_gaprange'] = 'A kihagyás helye (karakterben)';
$string['editor_gotomedia'] = 'Ugrás a Médiához';
$string['editor_heading'] = 'Feliratok és kihagyások szerkesztése';
$string['editor_hints'] = 'Tippek';
$string['editor_hinttext'] = 'A tipp szövege';
$string['editor_hinttype'] = 'Típus';
$string['editor_hinttype_firstletter'] = 'Első betű';
$string['editor_hinttype_partial'] = 'Részleges';
$string['editor_hinttype_solution'] = 'Megoldás';
$string['editor_hinttype_text'] = 'Szabad szöveg';
$string['editor_hinttype_translation'] = 'Fordítás';
$string['editor_hinttype_wordlength'] = 'Szóhossz';
$string['editor_import'] = 'Feliratok importálása';
$string['editor_importappend'] = 'Hozzáfűzés a meglévő szegmensekhez';
$string['editor_importapply'] = 'Importálás';
$string['editor_importcancel'] = 'Mégse';
$string['editor_importcheck'] = 'Tartalom ellenőrzése';
$string['editor_importchecking'] = 'Ellenőrzés…';
$string['editor_importcuecount'] = 'Talált szegmensek';
$string['editor_importduration'] = 'Hossz';
$string['editor_importedcues'] = 'Importált szegmensek: {$a}';
$string['editor_importfilehint'] = 'Válasszon feliratokat tartalmazó WebVTT- (.vtt) vagy SubRip-fájlt (.srt).';
$string['editor_importformat'] = 'Formátum';
$string['editor_importfromfile'] = 'Fájl feltöltése';
$string['editor_importfromtext'] = 'Szöveg beillesztése';
$string['editor_importgapcount'] = 'Talált kihagyások';
$string['editor_importhint'] = 'Illessze be a WebVTT- vagy SubRip-tartalmat, majd importálja szegmensként.';
$string['editor_importparseerror'] = 'Ez a tartalom nem volt olvasható WebVTT- vagy SubRip-formátumként.';
$string['editor_importpastedtext'] = 'Beillesztett szöveg';
$string['editor_importreaderror'] = 'A fájl nem volt olvasható.';
$string['editor_importready'] = 'Importálásra kész';
$string['editor_importreplace'] = 'Minden szegmens cseréje';
$string['editor_importreplacedcues'] = 'A szegmensek lecserélve; újonnan importálva: {$a}';
$string['editor_importsource'] = 'Forrás';
$string['editor_importsummary'] = 'Amit a rendszer talált';
$string['editor_importtoolarge'] = 'Ez a fájl {$a->size} méretű; az importálás legfeljebb {$a->max} méretet fogad el.';
$string['editor_importwrongtype'] = 'Válasszon feliratfájlt ({$a}).';
$string['editor_insertafter'] = 'Szegmens beszúrása utána';
$string['editor_insertbefore'] = 'Szegmens beszúrása elé';
$string['editor_invalidtime'] = 'Adjon meg időt mm:ss.SSS formátumban, például 01:05.400.';
$string['editor_linkurl'] = 'Hivatkozás a kikereséshez';
$string['editor_linkurl_help'] = 'A kihagyás mellett jelenik meg, ahol a szó kikereshető. Hagyja üresen, ha nem kínál ilyen hivatkozást.';
$string['editor_loaderror'] = 'A szerkesztőt nem sikerült betölteni. Töltse újra az oldalt.';
$string['editor_loading'] = 'A szerkesztő betöltése…';
$string['editor_maxlength'] = 'Legnagyobb hossz';
$string['editor_maxlength_help'] = 'Korlátozza, mennyit írhat be a résztvevő. A 0 azt jelenti, hogy nincs korlát.';
$string['editor_media'] = 'Média';
$string['editor_mediafile'] = 'Feltöltött fájl';
$string['editor_mediakind'] = 'A médium típusa';
$string['editor_medianone'] = 'Nincs';
$string['editor_mediaprovider'] = 'Szolgáltató';
$string['editor_mediaproviderref'] = 'A szolgáltató hivatkozása';
$string['editor_mediaproviderrefhint'] = 'Videóazonosító vagy hivatkozás szokásos alakban (például youtu.be/…).';
$string['editor_mediasaved'] = 'A médium mentve.';
$string['editor_mediaurl'] = 'A médium közvetlen URL-címe';
$string['editor_nocues'] = 'Még nincsenek szegmensek. Adjon hozzá egyet, vagy importáljon feliratokat.';
$string['editor_nocueselected'] = 'Válasszon szegmenst a listából a szerkesztéshez.';
$string['editor_nocuesmatch'] = 'Egyetlen szegmens sem felel meg ennek a keresésnek.';
$string['editor_nogaps'] = 'Nincsenek kihagyások';
$string['editor_nomedia'] = 'nincs';
$string['editor_nomedianotice'] = 'Először adja hozzá a videó- vagy hangfájlt a Média lapon. A feliratok a médiumhoz igazodnak időben, ezért a szerkesztőnek szüksége van rá, mielőtt szegmensekkel és kihagyásokkal dolgozhatna.';
$string['editor_novideotrack'] = 'Ez a böngésző nem tudja dekódolni a médium videósávját (csak a hang szól); a résztvevők fekete képet látnának. Kódolja át a fájlt H.264/MP4 formátumra (például az ffmpeg vagy a HandBrake segítségével), és töltse fel újra.';
$string['editor_onboardinggaps'] = 'Jelöljön ki egy szót egy szegmensben, és alakítsa kihagyássá.';
$string['editor_onboardingimport'] = 'Importáljon WebVTT- vagy SubRip-feliratokat, vagy vegye fel a szegmenseket kézzel.';
$string['editor_onboardingintro'] = 'Készítsen gyakorlatot három lépésben:';
$string['editor_onboardingmedia'] = 'Válasszon médiumot (feltöltés, URL-cím vagy szolgáltató).';
$string['editor_onboardingtitle'] = 'Kezdje el a gyakorlatot';
$string['editor_onlywarnings'] = 'Csak a figyelmeztetést tartalmazó szegmensek';
$string['editor_parsegaps'] = 'Kihagyásjelölések felismerése: a [szó] tippet engedélyező kihagyást hoz létre, a {szó} pedig anélkülit.';
$string['editor_penalty'] = 'Levonás';
$string['editor_poster'] = 'Borítókép';
$string['editor_preview'] = 'Résztvevői előnézet';
$string['editor_publish'] = 'Közzététel';
$string['editor_published'] = 'A változat közzétéve.';
$string['editor_removehint'] = 'Tipp eltávolítása';
$string['editor_removevariant'] = 'Eltávolítás';
$string['editor_ruleapplied'] = 'A szabály alapján %count% kihagyás jött létre.';
$string['editor_ruleapply'] = '%count% kihagyás alkalmazása';
$string['editor_ruleerror'] = 'A kihagyásokat nem sikerült létrehozni.';
$string['editor_ruleeverynth'] = 'Minden n-edik szó';
$string['editor_rulefound'] = 'A szabály %count% kihagyást talált.';
$string['editor_rulegenerate'] = 'Kihagyások létrehozása';
$string['editor_ruleinterval'] = 'Lépésköz (n)';
$string['editor_ruletype'] = 'Kihagyási szabály';
$string['editor_rulewordlist'] = 'Elrejtendő szavak';
$string['editor_rulewords'] = 'Szólista';
$string['editor_save'] = 'Piszkozat mentése';
$string['editor_saved'] = 'A piszkozat mentve.';
$string['editor_saveerror'] = 'A piszkozatot nem sikerült menteni.';
$string['editor_savemedia'] = 'A médium mentése';
$string['editor_saving'] = 'Mentés…';
$string['editor_searchcues'] = 'Keresés a szegmensekben';
$string['editor_selecttext'] = 'Először jelölje ki az átiratban azt a szót, amelyet el kell rejteni.';
$string['editor_solution'] = 'Megoldás';
$string['editor_starttime'] = 'Kezdés időpontja';
$string['editor_transcript'] = 'Átirat';
$string['editor_unsaved'] = 'Nem mentett változtatások';
$string['editor_uploadmedia'] = 'Médiafájlok feltöltése';
$string['editor_variantisregex'] = 'A(z) {$a} kezelése reguláris kifejezésként';
$string['editor_variantmatching'] = 'Hogyan hasonlítja össze a rendszer az elfogadott változatokat';
$string['editor_warnemptysolution'] = 'Megoldás nélküli kihagyás';
$string['editor_warnnotranscript'] = 'Nincs szöveg';
$string['editor_warntiming'] = 'A vége nem a kezdés után van';
$string['editor_waveform'] = 'A hang hullámformája';
$string['elang:addinstance'] = 'Új videódiktálás hozzáadása';
$string['elang:attempt'] = 'Videódiktálás elvégzése';
$string['elang:deleteattempts'] = 'Résztvevői kísérletek törlése';
$string['elang:exportreports'] = 'Személyes adatokat tartalmazó jelentések exportálása';
$string['elang:exportsolution'] = 'A teljes átirat exportálása a megoldásokkal';
$string['elang:exporttranscript'] = 'A munkalap exportálása dokumentumként';
$string['elang:manage'] = 'Gyakorlatok tartalmának létrehozása és szerkesztése';
$string['elang:useregex'] = 'Reguláris kifejezések használata az elfogadott válaszokban';
$string['elang:view'] = 'Videódiktálás megtekintése';
$string['elang:viewreports'] = 'Résztvevői jelentések megtekintése';
$string['error_attemptnotinprogress'] = 'Ez a kísérlet már nincs folyamatban.';
$string['error_couldnotobtainlock'] = 'A művelethez nem sikerült zárolást szerezni. Próbálja újra.';
$string['error_draftrevisionmismatch'] = 'Ez a piszkozat megváltozott, mióta betöltötte. Töltse be újra, és próbálja meg ismét.';
$string['error_duplicatecuekey'] = 'Két szegmens ugyanazt a „{$a}” kulcsot használja; minden szegmensnek egyedi kulcs kell.';
$string['error_duplicategapkey'] = 'Ugyanannak a szegmensnek két kihagyása ugyanazt a „{$a}” kulcsot használja; minden kihagyásnak egyedi kulcs kell.';
$string['error_duplicatehintlevel'] = 'Egy kihagyáshoz két tipp tartozik a(z) {$a}. szinten; minden szintnek egyedinek kell lennie.';
$string['error_gapnotinattemptversion'] = 'Ez a kihagyás nem ehhez a kísérlethez tartozó gyakorlatváltozathoz tartozik.';
$string['error_importnocues'] = 'Ebből a tartalomból nem sikerült feliratot beolvasni. A WebVTT- vagy SubRip-fájlban minden felirat fölött időbélyegsor áll, például 00:00:01.000 --> 00:00:04.000.';
$string['error_importnotutf8'] = 'Ez a fájl nem érvényes UTF-8. Valószínűleg régebbi kódolással mentették — nyissa meg szövegszerkesztőben, és mentse újra UTF-8 formátumban.';
$string['error_importtoolarge'] = 'Ez a fájl {$a->size} méretű; az importálás legfeljebb {$a->max} méretet fogad el. Egy órafelvétel feliratfájlja ennél jóval kisebb, így ez aligha az.';
$string['error_importtoomanycues'] = 'Ez a fájl {$a->count} feliratot tartalmaz; az importálás legfeljebb {$a->max} feliratot fogad el.';
$string['error_invalidcuepausemode'] = 'Válasszon a felkínált lehetőségek közül a felirat végi szünethez.';
$string['error_invalidgradingalgorithm'] = 'A(z) „{$a}” értékelési algoritmus nem exact és nem is wordrecognized.';
$string['error_invalidhinttype'] = 'A(z) „{$a}” tipptípus nem tartozik az engedélyezett típusok közé.';
$string['error_invalidisregex'] = 'Egy változat reguláris kifejezés jelölőjének 0-nak vagy 1-nek kell lennie.';
$string['error_invalidmediakind'] = 'A kiválasztott médiumtípus nem file, url vagy provider.';
$string['error_invalidpenalty'] = 'A tipphez tartozó levonásnak 0 és 1 között kell lennie.';
$string['error_invalidproviderref'] = 'A(z) „{$a}” nem felismert videóazonosító vagy hivatkozás ennél a szolgáltatónál.';
$string['error_invalidregexpattern'] = 'A(z) „{$a}” nem érvényes reguláris kifejezés.';
$string['error_invalidsolutionavailability'] = 'Válasszon a felkínált lehetőségek közül arra, mikor láthatják a résztvevők a megoldásokat tartalmazó átiratot.';
$string['error_invalidsourceurl'] = 'Adjon meg teljes címet, amely http:// vagy https:// előtaggal kezdődik, vagy YouTube-, illetve Vimeo-hivatkozást.';
$string['error_invalidsubtitleposition'] = 'Válasszon a felkínált lehetőségek közül a feliratok helyéhez.';
$string['error_invalidv1cuejson'] = 'Ezt az 1-es változatból származó szegmenst nem sikerült feldolgozni.';
$string['error_negativegapoffset'] = 'A kihagyás helye és hossza nem lehet negatív.';
$string['error_noaccesstoattempt'] = 'Nincs hozzáférése ehhez a kísérlethez.';
$string['error_nomorehints'] = 'Ehhez a kihagyáshoz nincs több tipp.';
$string['error_nopublishedversion'] = 'Ennek a gyakorlatnak még nincs közzétett tartalma.';
$string['error_responsetoolong'] = 'A válasza túl hosszú. Ehhez a kihagyáshoz legfeljebb {$a} karakter tartozhat.';
$string['error_solutionnotavailable'] = 'A megoldásokat tartalmazó átirat ebben a tevékenységben nem érhető el az Ön számára.';
$string['error_staleattemptstate'] = 'A kísérlet megjelenített állapota elavult. Töltse be újra a jelenlegi állapotot, és próbálja meg ismét.';
$string['error_transcriptnotavailable'] = 'Ebben a tevékenységben nincs letölthető átirat.';
$string['error_unknowngaprule'] = 'Ismeretlen kihagyási szabálytípus: „{$a}”.';
$string['error_unknownmediaprovider'] = 'A(z) „{$a}” nem tartozik a támogatott médiaszolgáltatók közé.';
$string['error_versionnotadraft'] = 'Csak piszkozat állapotú változat szerkeszthető.';
$string['error_versionnotfound'] = 'A gyakorlat ezen változata már nem létezik.';
$string['error_versionnotpublishable'] = 'Ez a változat nem tehető közzé: {$a}';
$string['export_audienceaftersubmission'] = 'A résztvevők letölthetik, miután befejeztek egy kísérletet';
$string['export_audiencealways'] = 'A résztvevők bármikor letölthetik';
$string['export_audiencestaff'] = 'Csak az erre jogosult oktatói munkatársak — a résztvevők számára nem érhető el';
$string['export_docx'] = 'Letöltés Word formátumban (DOCX)';
$string['export_downloadpdf'] = 'PDF letöltése';
$string['export_heading'] = 'Átirat exportálása';
$string['export_intro'] = 'Töltse le ennek a gyakorlatnak az átiratát több formátumban.';
$string['export_moreformats'] = 'További formátumok';
$string['export_nocontent'] = 'Még nincs közzétett átirat, amelyet exportálni lehetne.';
$string['export_odt'] = 'Letöltés OpenDocument formátumban (ODT)';
$string['export_pdf'] = 'Letöltés PDF formátumban';
$string['export_solution'] = 'Átirat a megoldásokkal';
$string['export_solutionhint'] = 'A teljes szöveg, amelyben minden kihagyás megoldása látszik.';
$string['export_text'] = 'Letöltés szövegként';
$string['export_versionnote'] = 'Az exportálás a gyakorlat jelenleg közzétett változatán alapul.';
$string['export_worksheet'] = 'Munkalap (kihagyások elrejtve)';
$string['export_worksheethint'] = 'A szöveg, amelyben minden kihagyás rejtve van. Készen áll a résztvevői anyagként való kiosztásra.';
$string['exporttranscript'] = 'Átirat exportálása';
$string['filearea_media'] = 'Média';
$string['filearea_poster'] = 'Borítókép';
$string['gradingheading'] = 'A válaszok értékelése';
$string['import_badtiming'] = 'Az időbélyegsort nem sikerült beolvasni: {$a}';
$string['import_emptytranscript'] = 'A rendszer kihagyott egy szöveg nélküli szegmenst.';
$string['import_warnlinetoolong'] = 'A rendszer kihagyta a(z) {$a->block}. blokkot: {$a->max} karakternél hosszabb sort tartalmaz, ami nem feliratsor.';
$string['jarothreshold'] = 'Hasonlósági küszöb';
$string['jarothreshold_help'] = 'A „Közeli válaszok elfogadása” beállítású kihagyásoknál ez a legkisebb Jaro-hasonlóság az elvárt és a beírt válasz között. Az 1 érték a nyelvspecifikus normalizálás utáni pontos egyezést követeli meg; az alacsonyabb értékek egyre eltérőbb írásmódokat is elfogadnak.';
$string['jarothresholdrange'] = 'A küszöbnek 0 és 1 között kell lennie.';
$string['language'] = 'A tartalom nyelve';
$string['language_help'] = 'Válassza ki a gyakorlat tartalmának nyelvét. Ez határozza meg a válaszok összehasonlítását, többek között a kis- és nagybetűk kezelését és az átírást. Válassza az „Általános (nincs megadva)” lehetőséget, ha nem kell nyelvspecifikus feldolgozás. Az új tartalomváltozatok ebből a beállításból indulnak.';
$string['language_none'] = 'Általános (nincs megadva)';
$string['media_cuenote'] = 'A meglévő feliratok és kihagyások megmaradnak, amikor médiumot vált. Az időzítésük nem igazodik automatikusan, ezért utána ellenőrizze őket a szerkesztőben.';
$string['media_current'] = 'Jelenlegi médium';
$string['media_heading'] = 'Média';
$string['media_intro'] = 'Válassza ki a videót vagy a hangot, amelyre ez a gyakorlat épül. A feliratok ehhez igazodnak időben, ezért ez az első lépés.';
$string['media_none'] = 'Ehhez a gyakorlathoz még nincs médium beállítva.';
$string['media_othersource'] = 'Más forrás';
$string['media_providerhint'] = 'Felismert szolgáltatók: {$a}. Minden más címet a rendszer közvetlen média-URL-ként használ.';
$string['media_sourceurl'] = 'A médium URL-címe';
$string['media_sourceurl_help'] = 'Fájl feltöltése helyett illessze be egy videó címét — YouTube- vagy Vimeo-hivatkozást, vagy egy médiafájl közvetlen címét.

Az itt megadott cím felülírja a feltöltött fájlt. Hagyja üresen, ha a fenti feltöltést szeretné használni.

A szolgáltatói videót a szolgáltató a saját keretében játssza le, amely nem jelzi a lejátszási időt. Az ilyen gyakorlat mindig a médium alatt mutatja a feliratokat, és sosem áll meg a felirat végén.

**Hová kerülnek az adatok.** A YouTube- vagy Vimeo-keret minden résztvevő böngészőjét összeköti az adott céggel, amely így megkapja a résztvevő IP-címét és eszközadatait. Alapértelmezés szerint a gyakorlat előbb rákérdez. Ha az intézményének saját médiaszervere van — Opencast, Panopto, Kaltura vagy hasonló —, inkább onnan illessze be a fájl közvetlen címét: a rendszer szokásos média-URL-ként kezeli, megőrzi a választott feliratpozíciót és szünetbeállítást, és nem kerül bele harmadik fél.';
$string['migratev1_approvalheading'] = 'Átköltöztetve, ellenőrzésre vár';
$string['migratev1_approvebutton'] = 'A költöztetés jóváhagyása';
$string['migratev1_approved'] = 'A(z) {$a} videódiktálás jóváhagyottként lett megjelölve.';
$string['migratev1_colactivity'] = 'Tevékenység';
$string['migratev1_colalgorithm'] = 'Értékelési algoritmus';
$string['migratev1_colcues'] = 'Szegmensek';
$string['migratev1_colgaps'] = 'Kihagyások';
$string['migratev1_colissues'] = 'Problémák';
$string['migratev1_collearners'] = 'Résztvevők';
$string['migratev1_confirmdecommission'] = 'Ez VISSZAVONHATATLANUL törli az 1-es változat régi tábláit és az elang.options oszlopot. A művelet nem vonható vissza. Folytatja?';
$string['migratev1_confirmmigrate'] = 'Ez sorba állít egy háttérfeladatot, amely a fent felsorolt minden tevékenységhez megírja a 2-es változat adatait. Az 1-es változat táblái és az elang.options érintetlenek maradnak. Folytatja?';
$string['migratev1_decommissionblocked'] = 'A törlés még mindig le van tiltva; lásd az alábbi listát.';
$string['migratev1_decommissionblockedintro'] = 'A törlés addig van letiltva, amíg:';
$string['migratev1_decommissionbutton'] = 'Az 1-es változat régi adatainak törlése';
$string['migratev1_decommissioned'] = 'Az 1-es változat régi adatai törölve lettek.';
$string['migratev1_decommissionheading'] = 'Az 1-es változat adatainak kivonása';
$string['migratev1_decommissionready'] = 'Az 1-es változat minden tevékenysége át lett költöztetve és jóvá lett hagyva. A régi táblák és az elang.options most törölhetők. A művelet nem vonható vissza.';
$string['migratev1_heading'] = 'Az 1-es változat tevékenységeinek átköltöztetése';
$string['migratev1_migratebutton'] = 'Ezek a tevékenységek átköltöztetése';
$string['migratev1_noissues'] = 'Nincs';
$string['migratev1_nonepending'] = 'Nincs átköltöztetésre váró tevékenység az 1-es változatból.';
$string['migratev1_nonependingapproval'] = 'Nincs ellenőrzésre váró átköltöztetett tevékenység.';
$string['migratev1_notablespresent'] = 'Ezen az oldalon nem található az 1-es változat régi táblája. Nincs mit átköltöztetni.';
$string['migratev1_parseerrorcount'] = 'Feldolgozhatatlan szegmensek: {$a}';
$string['migratev1_pendingheading'] = 'Még nincs átköltöztetve';
$string['migratev1_queued'] = 'Az átköltöztetési feladat sorba lett állítva. A következő cron-futáskor fut le, vagy azonnal az admin/cli/adhoc_task.php --execute paranccsal.';
$string['migratev1_verifiedclean'] = 'Ellenőrizve: az átköltöztetett adatok eltérés nélkül megegyeznek az 1-es változat forrásával.';
$string['migratev1_verifieddiscrepancies'] = 'Az ellenőrzés eltéréseket talált az 1-es változat forrásához képest: {$a}';
$string['migratev1_verifyfailed'] = 'Ezt a tevékenységet nem sikerült ellenőrizni: {$a}';
$string['modulename'] = 'Videódiktálás';
$string['modulename_help'] = 'A videódiktálás tevékenységben a résztvevők időkóddal ellátott feliratok kihagyásait töltik ki, miközben videót néznek vagy hallgatnak.

Az oktatók WebVTT- vagy SubRip-feliratfájlt importálnak, szavakat vagy kifejezéseket jelölnek meg kihagyásként, és beállítják, mennyire szigorúan hasonlítsa össze a rendszer a válaszokat. A résztvevők szegmensről szegmensre haladnak az átiraton, pontlevonással járó tippeket kérhetnek, és azonnal visszajelzést kapnak.';
$string['modulenameplural'] = 'Videódiktálások';
$string['nav_exportshort'] = 'Exportálás';
$string['nav_media'] = 'Média';
$string['nav_reports'] = 'Kísérletek';
$string['nav_subtitles'] = 'Feliratok és kihagyások';
$string['noinstances'] = 'Ebben a kurzusban nincsenek videódiktálások.';
$string['overview_attempts'] = 'Kísérletek';
$string['playbackheading'] = 'Lejátszás és feliratok';
$string['playbackoverlayhint'] = 'A képre helyezett felirat csak az éppen futó feliratot mutatja, ezért a lejátszás mindig megáll annak a feliratnak a végén, amelyben még van kitöltendő kihagyás. Itt nincs mit választani.';
$string['playbackproviderhint'] = 'A YouTube- vagy Vimeo-videót a szolgáltató játssza le a saját keretében, amely nem jelzi a lejátszási időt. Az ilyen gyakorlat mindig a médium alatt mutatja a feliratokat, és sosem áll meg a felirat végén, bármit is választ fent. A feltöltött fájlok és a közvetlen média-URL-ek mindkét beállítást követik.';
$string['player_check'] = 'Válasz ellenőrzése';
$string['player_consentaccept'] = 'A videó betöltése innen: {$a}';
$string['player_consentdetail'] = 'A lejátszás kapcsolatot létesít a böngészője és a(z) {$a} között. A(z) {$a} megkapja az IP-címét és az eszközére vonatkozó adatokat, és olvashatja a korábban elhelyezett sütiket. Semmi sem kerül elküldésre, amíg Ön nem dönt a videó betöltéséről.';
$string['player_consentheading'] = 'Ezt a videót a(z) {$a} szolgáltatja';
$string['player_finish'] = 'Kísérlet befejezése';
$string['player_finished'] = 'A kísérlet befejezve. Pontszám: %score%%';
$string['player_finishincomplete'] = 'Még üres kihagyások: {$a}. Mégis befejezi a kísérletet?';
$string['player_gaplabel'] = '%gap%. kihagyás';
$string['player_gaplink'] = 'Hivatkozás megnyitása';
$string['player_hint'] = 'Tipp megjelenítése';
$string['player_loaderror'] = 'A gyakorlatot nem sikerült betölteni. Töltse újra az oldalt.';
$string['player_loading'] = 'A gyakorlat betöltése…';
$string['player_nocontent'] = 'Még nincs közzétett gyakorlattartalom. Térjen vissza később.';
$string['player_novideotrack'] = 'A böngészője nem tudja megjeleníteni a médium videósávját; a hang ettől még szól. Jelezze az oktatójának.';
$string['player_outdatedattempt'] = 'Ezt a gyakorlatot frissítették, mióta elkezdte ezt a kísérletet. A korábbi tartalommal folytatja; fejezze be ezt a kísérletet, hogy legközelebb a frissített gyakorlaton dolgozhasson.';
$string['player_progress'] = '{$a->total} kihagyásból {$a->done} megválaszolva';
$string['player_ready'] = 'A gyakorlat készen áll.';
$string['player_scorelabel'] = 'Pontszám: %score%%';
$string['player_stateaccepted'] = 'Elfogadva';
$string['player_statecorrect'] = 'Helyes';
$string['player_statehinted'] = 'Tipp felhasználva';
$string['player_stateincorrect'] = 'Helytelen';
$string['player_submitfailed'] = 'A válaszát nem sikerült menteni. Próbálja újra.';
$string['player_transcriptheading'] = 'Átirat';
$string['pluginadministration'] = 'A videódiktálás kezelése';
$string['pluginname'] = 'Videódiktálás';
$string['privacy_metadata_elang'] = 'Minden tevékenységnél annak a feljegyzése, ki hagyta jóvá az 1.x tartalom egyirányú átköltöztetését.';
$string['privacy_metadata_elang_attempt'] = 'Egy gyakorlat minden kísérleténél a tevékenység tárolja, ki végezte, mikor, meddig jutott és hogyan értékelték.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Hány kihagyást válaszolt meg a résztvevő ebben a kísérletben.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'A kísérlet sorszáma az adott felhasználónál és tevékenységnél.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Hány kihagyást fogadott el a rendszer helyesként ebben a kísérletben.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Hány kihagyásra érkezett karakterre pontos válasz ebben a kísérletben.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Hány kihagyáshoz kért a résztvevő tippet ebben a kísérletben.';
$string['privacy_metadata_elang_attempt_score'] = 'Az ebben a kísérletben elért pontszám.';
$string['privacy_metadata_elang_attempt_state'] = 'Hogy a kísérlet folyamatban van, befejeződött vagy félbemaradt.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'A kísérlet befejezésének időpontja.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'A kísérlet legutóbbi frissítésének időpontja.';
$string['privacy_metadata_elang_attempt_timestart'] = 'A kísérlet megkezdésének időpontja.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'A kihagyások teljes száma abban a gyakorlatváltozatban, amelyhez ez a kísérlet tartozik.';
$string['privacy_metadata_elang_attempt_userid'] = 'A kísérletet végző felhasználó azonosítója.';
$string['privacy_metadata_elang_attempt_versionid'] = 'Az a gyakorlatváltozat, amelyen ez a kísérlet készült.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'Az a felhasználó, aki jóváhagyta ennek a tevékenységnek az átköltöztetését a mod_elang 1.x változatból. Azért tárolódik, hogy a jóváhagyás visszakövethető maradjon.';
$string['privacy_metadata_elang_response'] = 'Minden kihagyásnál, amelyet a résztvevő egy kísérleten belül megválaszol, a tevékenység tárolja a válasz szövegét és azt, hogyan értékelték.';
$string['privacy_metadata_elang_response_accepted'] = 'Hogy a választ helyesként fogadta-e el a rendszer ennél a kihagyásnál.';
$string['privacy_metadata_elang_response_hintlevel'] = 'A legmagasabb tippszint, amelyet a résztvevő ennél a kihagyásnál látott.';
$string['privacy_metadata_elang_response_responsetext'] = 'A szöveg, amelyet a résztvevő ebbe a kihagyásba írt.';
$string['privacy_metadata_elang_response_resultstate'] = 'Az a besorolás, amelyet az értékelés adott ennek a válasznak (pontos, felismert szó, helytelen vagy üres).';
$string['privacy_metadata_elang_response_score'] = 'Az a pontszám, amellyel ez a válasz hozzájárult, az esetleges tipplevonás után.';
$string['privacy_metadata_elang_response_timecreated'] = 'A válasz első beküldésének időpontja.';
$string['privacy_metadata_elang_response_timemodified'] = 'A válasz legutóbbi frissítésének időpontja.';
$string['privacy_metadata_elang_response_tries'] = 'Hányszor küldött be a résztvevő választ erre a kihagyásra.';
$string['privacy_metadata_elang_version'] = 'Minden tartalomváltozatnál a tevékenység tárolja, melyik felhasználó módosította utoljára.';
$string['privacy_metadata_elang_version_usermodified'] = 'Az a felhasználó, aki utoljára módosította ezt a tartalomváltozatot. Azért tárolódik, hogy visszakövethető legyen, ki szerkesztette a gyakorlat tartalmát.';
$string['privacy_provider_externallink'] = 'Ha egy gyakorlat YouTube- vagy Vimeo-videóra épül, a megnyitása kapcsolatot létesít a résztvevő böngészője és az adott szolgáltató között. A bővítmény maga nem küld semmit, de a kapcsolatot a tevékenység váltja ki. Hogy egyáltalán sor kerül-e rá, az oldal szolgáltatói hozzájárulásra vonatkozó beállításától és a résztvevő beleegyezésétől függ.';
$string['privacy_provider_ipaddress'] = 'Az IP-cím, amelyről a résztvevő böngészője kapcsolódik.';
$string['privacy_provider_useragent'] = 'A böngésző által küldött böngésző- és eszközadatok.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Kérdezzen rá a YouTube vagy a Vimeo beágyazása előtt';
$string['providerconsent_desc'] = 'A YouTube- vagy Vimeo-videóra épülő gyakorlatok a videó helyett értesítést mutatnak, és csak a résztvevő beleegyezése után ágyazzák be. Enélkül a szolgáltató az oldal megnyitásakor azonnal megkapja a résztvevő IP-címét és böngészőadatait — még mielőtt bárki a lejátszásra kattintana. Csak akkor kapcsolja ki, ha az intézménye máshol szerzi be ezt a hozzájárulást.';
$string['report_actions'] = 'Műveletek';
$string['report_answered'] = 'Megválaszolva';
$string['report_attemptnumber'] = 'Kísérlet';
$string['report_back'] = 'Vissza az összes kísérlethez';
$string['report_correct'] = 'Helyes';
$string['report_delete'] = 'Törlés';
$string['report_deleteconfirm'] = 'Véglegesen törli ezt a kísérletet és az összes válaszát? A művelet nem vonható vissza.';
$string['report_deleted'] = 'A kísérlet törölve lett.';
$string['report_exact'] = 'Pontos';
$string['report_export'] = 'Exportálás';
$string['report_filterany'] = 'Mind';
$string['report_filterapply'] = 'Szűrők alkalmazása';
$string['report_filterattempt'] = 'A kísérlet száma';
$string['report_filterfrom'] = 'Kezdve ettől';
$string['report_filterrangeerror'] = 'Az időszak vége korábbi, mint a kezdete.';
$string['report_filterreset'] = 'Szűrők törlése';
$string['report_filterstate'] = 'Állapot';
$string['report_filterto'] = 'Kezdve eddig';
$string['report_filteruser'] = 'Résztvevő';
$string['report_finished'] = 'Befejezve';
$string['report_heading'] = 'Kísérletek';
$string['report_hinted'] = 'Tippel';
$string['report_hints'] = 'Tippszint';
$string['report_kpianswered'] = 'Megválaszolva';
$string['report_kpiattempts'] = 'Megjelenített kísérletek';
$string['report_kpiaverage'] = 'Átlagos pontszám (befejezett)';
$string['report_kpicorrect'] = 'Elfogadva';
$string['report_kpiexact'] = 'Teljesen helyes';
$string['report_kpifinished'] = 'Befejezve';
$string['report_kpihinted'] = 'Tippet használt';
$string['report_kpihintedgaps'] = 'Tippre volt szüksége';
$string['report_noattempts'] = 'Még nincsenek kísérletek.';
$string['report_nogaps'] = 'Annak a változatnak, amelyen ez a kísérlet készült, nincsenek kihagyásai.';
$string['report_nomatchingattempts'] = 'Egyetlen kísérlet sem felel meg ezeknek a szűrőknek.';
$string['report_noresponse'] = 'Nincs válasz';
$string['report_response'] = 'Válasz';
$string['report_result'] = 'Eredmény';
$string['report_result_empty'] = 'Üres';
$string['report_result_exact'] = 'Pontos';
$string['report_result_incorrect'] = 'Helytelen';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Felismert';
$string['report_score'] = 'Pontszám';
$string['report_solution'] = 'Megoldás';
$string['report_started'] = 'Megkezdve';
$string['report_state'] = 'Állapot';
$string['report_state_abandoned'] = 'Félbemaradt';
$string['report_state_finished'] = 'Befejezett';
$string['report_state_inprogress'] = 'Folyamatban';
$string['report_transcript'] = 'Átirat';
$string['report_tries'] = 'Válaszkísérletek';
$string['report_user'] = 'Résztvevő';
$string['report_view'] = 'Megtekintés';
$string['reports'] = 'Jelentések';
$string['resetattempts'] = 'Az összes résztvevői kísérlet és válasz törlése';
$string['solutionavailability'] = 'Megoldásokat tartalmazó átirat a résztvevőknek';
$string['solutionavailability_aftersubmission'] = 'A kísérlet befejezése után';
$string['solutionavailability_always'] = 'Bármikor';
$string['solutionavailability_help'] = 'Mikor tölthetik le a résztvevők a teljes átiratot, amelyben minden kihagyás megoldása látszik.

* Soha — csak az oktatók tölthetik le.
* A kísérlet befejezése után — a résztvevő akkor töltheti le, ha befejezett egy kísérletet ebben a tevékenységben.
* Bármikor — a résztvevő a válaszadás előtt is letöltheti.

Az erre jogosult oktatói munkatársak mindig letölthetik, függetlenül ettől a beállítástól.';
$string['solutionavailability_never'] = 'Soha';
$string['subplugintype_elangscript'] = 'Írásrendszer-kezelő';
$string['subplugintype_elangscript_plural'] = 'Írásrendszer-kezelők';
$string['subtitleposition'] = 'A feliratok megjelenítése';
$string['subtitleposition_below'] = 'A médium alatt';
$string['subtitleposition_help'] = 'Hol jelennek meg az interaktív feliratok.

* A médium alatt — a teljes átirat a médium alatt áll saját görgethető területén, és követi a lejátszást.
* A videóban, lent vagy fent — csak az éppen futó felirat rajzolódik a médium fölé.

A csak hangot tartalmazó médiumnak nincs képe, amelyre rajzolni lehetne, ezért mindig a médium alatti megjelenítést használja. Maga a beállítás megmarad, és újra érvénybe lép, amint a tevékenység videót használ.';
$string['subtitleposition_overlaybottom'] = 'A videóban — lent';
$string['subtitleposition_overlaytop'] = 'A videóban — fent';
$string['task_migratev1activities'] = 'Az 1-es változat tevékenységeinek átköltöztetése';
$string['transcriptheading'] = 'Átirat a résztvevőknek';
$string['validate_cueafterend'] = '{$a->where}: {$a->endtime} ms-nál ér véget, tehát a médium után ({$a->duration} ms). A lejátszás sosem jut el odáig.';
$string['validate_cueendbeforestart'] = '{$a}: a vége nem a kezdés után van.';
$string['validate_cuewhere'] = '{$a->sortorder}. szegmens ({$a->cuekey})';
$string['validate_emptysolution'] = 'A(z) {$a} megoldása üres.';
$string['validate_hintlevels'] = 'A(z) {$a} tippszintjei nem alkotnak egytől induló, folytonos sorozatot.';
$string['validate_negativetime'] = '{$a}: a kezdési időpont a felvétel kezdete elé esik.';
$string['validate_nocues'] = 'A változatnak nincsenek szegmensei.';
$string['validate_nogaps'] = 'A változatnak nincsenek megválaszolandó kihagyásai.';
$string['validate_nonpositivelength'] = 'A(z) {$a} karakterhosszának pozitívnak kell lennie.';
$string['validate_rangeoutside'] = 'A(z) {$a} karaktertartománya az átiratán kívül esik.';
$string['validate_rangeoverlap'] = 'A(z) {$a} karaktertartománya átfed egy másik kihagyással.';
$string['validate_unknownalgorithm'] = 'A(z) {$a->where} „{$a->algorithm}” értékelési algoritmusa ismeretlen.';
$string['validate_where'] = '{$a->gapkey} kihagyás a(z) {$a->cuekey} szegmensben';
$string['verify_algorithmmismatch'] = '{$a->gapkey} kihagyás: az értékelési algoritmus „{$a->actual}”, a várt „{$a->expected}” volt.';
$string['verify_attemptcount'] = 'Az átköltöztetett kísérletek száma {$a->actual}, a várt érték {$a->expected} különböző résztvevő az 1.x változatból.';
$string['verify_jarothreshold'] = 'A válaszok összehasonlításának küszöbe {$a->actual}, a várt érték {$a->expected} volt.';
$string['verify_missingattempt'] = '{$a} felhasználó: átköltöztetett kísérlet volt várható, de egy sem található.';
$string['verify_missingcue'] = '{$a} szegmens: az átköltöztetett szegmens hiányzik.';
$string['verify_missinggap'] = '{$a} kihagyás: az átköltöztetett kihagyás hiányzik.';
$string['verify_missinghint'] = '{$a} kihagyás: az 1-es változat itt engedett tippet, de egy sem lett átköltöztetve.';
$string['verify_orphancue'] = '{$a} szegmens: nem található megfelelő szegmens az 1-es változatból.';
$string['verify_orphangap'] = '{$a} kihagyás: nem található megfelelő kihagyás az 1-es változatból.';
$string['verify_rangemismatch'] = '{$a} kihagyás: a karaktertartomány nem egyezik az 1-es változat forrásával.';
$string['verify_responsecount'] = '{$a->userid} felhasználó: az átköltöztetett válaszok száma {$a->actual}, a várt érték {$a->expected} volt.';
$string['verify_solutionmismatch'] = '{$a->gapkey} kihagyás: a megoldás „{$a->actual}”, a várt „{$a->expected}” volt.';
$string['verify_transcriptmismatch'] = '{$a} szegmens: az átirat nem egyezik az 1-es változat forrásával.';
$string['verify_unexpectedhint'] = '{$a} kihagyás: az 1-es változat itt nem engedett tippet, mégis átköltöztetésre került egy.';
