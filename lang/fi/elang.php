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
 * Finnish strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * No 1.3.5 translation existed to follow, so terminology comes from Moodle's own
 * Finnish pack — osallistuja, yritys, aktiviteetti, tekstitykset, vihje.
 *
 * Finnish inflects nouns through a large set of cases and attaches them as
 * suffixes, so a count like "{$a} segmenttiä" needs the partitive and is still
 * wrong for some values. The count-neutral form adopted in the RC1 terminology
 * review — "Segmentit: {$a}" — sidesteps the question entirely, which matters
 * more here than in any language done so far.
 *
 * One term is worth a native reviewer's attention: transcript is rendered as
 * **tekstivastine** rather than the loanword "transkriptio". Both are used;
 * this one is the plainer Finnish word, but it is a choice.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Sallitut sisältökielet';
$string['allowedlanguages_desc'] = 'Sisältökielet, joita tarjotaan videosanelua luotaessa tai muokattaessa. Älä valitse yhtään, niin tarjolla on koko kieliluettelo. Aktiviteetti säilyttää tallennetun kielensä, vaikka poistaisit sen myöhemmin täältä.';
$string['allowtranscriptdownload'] = 'Osallistujien tekstivastineen lataus';
$string['allowtranscriptdownload_help'] = 'Kun tämä on käytössä, osallistujat voivat ladata tekstivastineen tehtäväpohjan, jossa jokainen aukko on piilotettu, muodossa PDF, Word, OpenDocument tai teksti.

Oletuksena se on pois käytöstä. Opetushenkilöstö, jolla on oikeus, voi ladata tekstivastineen aina tästä asetuksesta riippumatta.';
$string['allowtranscriptdownload_label'] = 'Osallistujat saavat ladata tehtäväpohjan';
$string['completiondetail_completionfinishattempt'] = 'Suorita yritys loppuun';
$string['completionfinishattempt'] = 'Osallistujan on suoritettava yritys loppuun';
$string['cuepausemode'] = 'Tauko tekstityksen lopussa';
$string['cuepausemode_auto'] = 'Automaattinen';
$string['cuepausemode_help'] = 'Pysähtyykö media tekstityksen loppuun.

* Automaattinen — toisto jatkuu ja pysähtyy tekstityksen loppuun vain niin kauan kuin kyseistä tekstitystä työstetään, eli sen tai jonkin sen aukon napsauttamisen jälkeen tai kun näppäimistön kohdistus on jossakin niistä.
* Pysähdy jokaisen vastaamattoman tekstityksen kohdalla — toisto pysähtyy jokaisen sellaisen tekstityksen loppuun, jossa on vielä tyhjä aukko, ja odottaa jatkamista.
* Älä pysähdy koskaan — toisto jatkuu median loppuun asti.

Kumpikaan kahdesta ensimmäisestä ei pysähdy tekstitykseen, jonka kaikki aukot on täytetty: se on valmista työtä, ja pysähtyminen siihen vaatisi näppäinpainalluksen ilman vaikutusta. Se tarkoittaa myös, että harjoituksen toinen läpikäynti pysähtyy vain siellä, missä jotakin vielä puuttuu.';
$string['cuepausemode_nostop'] = 'Älä pysähdy koskaan';
$string['cuepausemode_stop'] = 'Pysähdy jokaisen vastaamattoman tekstityksen kohdalla';
$string['editcontent'] = 'Muokkaa sisältöä';
$string['editor_addcue'] = 'Lisää segmentti';
$string['editor_addgap'] = 'Tee valinnasta aukko';
$string['editor_addhint'] = 'Lisää vihje';
$string['editor_addvariant'] = 'Lisää muunnos';
$string['editor_advanced'] = 'Lisäasetukset';
$string['editor_algoexact'] = 'Tarkka osuma';
$string['editor_algorithm'] = 'Vastausten vertailu';
$string['editor_algowordrecognized'] = 'Hyväksy lähes oikeat vastaukset';
$string['editor_answers'] = 'Hyväksytyt muunnokset';
$string['editor_autosaved'] = 'Kaikki muutokset on tallennettu.';
$string['editor_autosaveerror'] = 'Automaattinen tallennus epäonnistui — yritä uudelleen Tallenna-painikkeella.';
$string['editor_captureend'] = 'Aseta loppu toistosta';
$string['editor_capturestart'] = 'Aseta alku toistosta';
$string['editor_cueactions'] = 'Segmentin toiminnot';
$string['editor_cuecount'] = 'Segmentit: {$a}';
$string['editor_currentmedia'] = 'Nykyinen media:';
$string['editor_deletecue'] = 'Poista segmentti';
$string['editor_deletegap'] = 'Poista aukko';
$string['editor_emptytranscript'] = '(ei vielä tekstiä)';
$string['editor_endtime'] = 'Loppuaika';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Aukot: {$a}';
$string['editor_gaprange'] = 'Aukon sijainti (merkkeinä)';
$string['editor_gotomedia'] = 'Siirry Mediaan';
$string['editor_heading'] = 'Muokkaa tekstityksiä ja aukkoja';
$string['editor_hints'] = 'Vihjeet';
$string['editor_hinttext'] = 'Vihjeen teksti';
$string['editor_hinttype'] = 'Tyyppi';
$string['editor_hinttype_firstletter'] = 'Ensimmäinen kirjain';
$string['editor_hinttype_partial'] = 'Osittainen';
$string['editor_hinttype_solution'] = 'Ratkaisu';
$string['editor_hinttype_text'] = 'Vapaa teksti';
$string['editor_hinttype_translation'] = 'Käännös';
$string['editor_hinttype_wordlength'] = 'Sanan pituus';
$string['editor_import'] = 'Tuo tekstitykset';
$string['editor_importappend'] = 'Lisää olemassa oleviin segmentteihin';
$string['editor_importapply'] = 'Tuo';
$string['editor_importcancel'] = 'Peruuta';
$string['editor_importcheck'] = 'Tarkista sisältö';
$string['editor_importchecking'] = 'Tarkistetaan…';
$string['editor_importcuecount'] = 'Löytyneet segmentit';
$string['editor_importduration'] = 'Kesto';
$string['editor_importedcues'] = 'Tuodut segmentit: {$a}';
$string['editor_importfilehint'] = 'Valitse WebVTT- (.vtt) tai SubRip-tiedosto (.srt), jossa on tekstitykset.';
$string['editor_importformat'] = 'Muoto';
$string['editor_importfromfile'] = 'Lähetä tiedosto';
$string['editor_importfromtext'] = 'Liitä teksti';
$string['editor_importgapcount'] = 'Löytyneet aukot';
$string['editor_importhint'] = 'Liitä WebVTT- tai SubRip-sisältö ja tuo se segmentteinä.';
$string['editor_importparseerror'] = 'Tätä sisältöä ei voitu lukea WebVTT- eikä SubRip-muodossa.';
$string['editor_importpastedtext'] = 'Liitetty teksti';
$string['editor_importreaderror'] = 'Tiedostoa ei voitu lukea.';
$string['editor_importready'] = 'Valmis tuotavaksi';
$string['editor_importreplace'] = 'Korvaa kaikki segmentit';
$string['editor_importreplacedcues'] = 'Segmentit korvattu; juuri tuodut: {$a}';
$string['editor_importsource'] = 'Lähde';
$string['editor_importsummary'] = 'Mitä löytyi';
$string['editor_importtoolarge'] = 'Tämä tiedosto on {$a->size}; tuonti hyväksyy enintään {$a->max}.';
$string['editor_importwrongtype'] = 'Valitse tekstitystiedosto ({$a}).';
$string['editor_insertafter'] = 'Lisää segmentti jälkeen';
$string['editor_insertbefore'] = 'Lisää segmentti ennen';
$string['editor_invalidtime'] = 'Anna aika muodossa mm:ss.SSS, esimerkiksi 01:05.400.';
$string['editor_linkurl'] = 'Linkki hakuun';
$string['editor_linkurl_help'] = 'Näytetään aukon vieressä paikkana, josta sanan voi hakea. Jätä tyhjäksi, jos linkkiä ei tarjota.';
$string['editor_loaderror'] = 'Muokkainta ei voitu ladata. Lataa sivu uudelleen.';
$string['editor_loading'] = 'Ladataan muokkainta…';
$string['editor_maxlength'] = 'Enimmäispituus';
$string['editor_maxlength_help'] = 'Rajoittaa sitä, kuinka paljon osallistuja voi kirjoittaa. 0 tarkoittaa ei rajaa.';
$string['editor_media'] = 'Media';
$string['editor_mediafile'] = 'Lähetetty tiedosto';
$string['editor_mediakind'] = 'Median tyyppi';
$string['editor_medianone'] = 'Ei mitään';
$string['editor_mediaprovider'] = 'Palveluntarjoaja';
$string['editor_mediaproviderref'] = 'Palveluntarjoajan viite';
$string['editor_mediaproviderrefhint'] = 'Videon tunnus tai linkki tavallisessa muodossa (esim. youtu.be/…).';
$string['editor_mediasaved'] = 'Media tallennettu.';
$string['editor_mediaurl'] = 'Median suora URL-osoite';
$string['editor_nocues'] = 'Ei vielä segmenttejä. Lisää yksi tai tuo tekstitykset.';
$string['editor_nocueselected'] = 'Valitse segmentti luettelosta muokataksesi sitä.';
$string['editor_nocuesmatch'] = 'Mikään segmentti ei vastaa tätä hakua.';
$string['editor_nogaps'] = 'Ei aukkoja';
$string['editor_nomedia'] = 'ei mitään';
$string['editor_nomedianotice'] = 'Lisää ensin video- tai äänitiedosto Media-välilehdellä. Tekstitykset ajastetaan mediaan, joten muokkain tarvitsee sen ennen kuin voit työstää segmenttejä ja aukkoja.';
$string['editor_novideotrack'] = 'Tämä selain ei osaa purkaa tämän median videoraitaa (vain ääni toistuu); osallistujat näkisivät mustan kuvan. Koodaa tiedosto uudelleen muotoon H.264/MP4 (esimerkiksi ffmpeg- tai HandBrake-ohjelmalla) ja lähetä se uudelleen.';
$string['editor_onboardinggaps'] = 'Valitse sana segmentistä ja tee siitä aukko.';
$string['editor_onboardingimport'] = 'Tuo WebVTT- tai SubRip-tekstitykset tai lisää segmentit käsin.';
$string['editor_onboardingintro'] = 'Luo harjoitus kolmessa vaiheessa:';
$string['editor_onboardingmedia'] = 'Valitse media (lähetys, URL-osoite tai palveluntarjoaja).';
$string['editor_onboardingtitle'] = 'Aloita harjoituksesi';
$string['editor_onlywarnings'] = 'Vain segmentit, joissa on varoituksia';
$string['editor_parsegaps'] = 'Tunnista aukkomerkinnät: [sana] luo aukon, jossa vihjeet ovat sallittuja, {sana} ilman niitä.';
$string['editor_penalty'] = 'Vähennys';
$string['editor_poster'] = 'Esittelykuva';
$string['editor_preview'] = 'Osallistujan esikatselu';
$string['editor_publish'] = 'Julkaise';
$string['editor_published'] = 'Versio julkaistu.';
$string['editor_removehint'] = 'Poista vihje';
$string['editor_removevariant'] = 'Poista';
$string['editor_ruleapplied'] = 'Säännön perusteella luotiin %count% aukkoa.';
$string['editor_ruleapply'] = 'Käytä %count% aukkoa';
$string['editor_ruleerror'] = 'Aukkoja ei voitu luoda.';
$string['editor_ruleeverynth'] = 'Joka n:s sana';
$string['editor_rulefound'] = 'Sääntö löysi %count% aukkoa.';
$string['editor_rulegenerate'] = 'Luo aukot';
$string['editor_ruleinterval'] = 'Väli (n)';
$string['editor_ruletype'] = 'Aukkosääntö';
$string['editor_rulewordlist'] = 'Piilotettavat sanat';
$string['editor_rulewords'] = 'Sanaluettelo';
$string['editor_save'] = 'Tallenna luonnos';
$string['editor_saved'] = 'Luonnos tallennettu.';
$string['editor_saveerror'] = 'Luonnosta ei voitu tallentaa.';
$string['editor_savemedia'] = 'Tallenna media';
$string['editor_saving'] = 'Tallennetaan…';
$string['editor_searchcues'] = 'Hae segmenteistä';
$string['editor_selecttext'] = 'Valitse ensin tekstivastineesta sana, joka piilotetaan.';
$string['editor_solution'] = 'Ratkaisu';
$string['editor_starttime'] = 'Alkuaika';
$string['editor_transcript'] = 'Tekstivastine';
$string['editor_unsaved'] = 'Tallentamattomia muutoksia';
$string['editor_uploadmedia'] = 'Lähetä mediatiedostot';
$string['editor_variantisregex'] = 'Käsittele {$a} säännöllisenä lausekkeena';
$string['editor_variantmatching'] = 'Miten hyväksytyt muunnokset vertaillaan';
$string['editor_warnemptysolution'] = 'Aukko ilman ratkaisua';
$string['editor_warnnotranscript'] = 'Ei tekstiä';
$string['editor_warntiming'] = 'Loppu ei ole alun jälkeen';
$string['editor_waveform'] = 'Äänen aaltomuoto';
$string['elang:addinstance'] = 'Lisätä uusi videosanelu';
$string['elang:attempt'] = 'Tehdä videosanelu';
$string['elang:deleteattempts'] = 'Poistaa osallistujien yrityksiä';
$string['elang:exportreports'] = 'Viedä raportteja, joissa on henkilötietoja';
$string['elang:exportsolution'] = 'Viedä täydellinen tekstivastine ratkaisuineen';
$string['elang:exporttranscript'] = 'Viedä tehtäväpohja asiakirjana';
$string['elang:manage'] = 'Luoda ja muokata harjoitusten sisältöä';
$string['elang:useregex'] = 'Käyttää säännöllisiä lausekkeita hyväksytyissä vastauksissa';
$string['elang:view'] = 'Katsella videosanelua';
$string['elang:viewreports'] = 'Katsella osallistujaraportteja';
$string['error_attemptnotinprogress'] = 'Tämä yritys ei ole enää kesken.';
$string['error_couldnotobtainlock'] = 'Tälle toiminnolle ei saatu lukitusta. Yritä uudelleen.';
$string['error_draftrevisionmismatch'] = 'Tämä luonnos on muuttunut sen jälkeen kun latasit sen. Lataa se uudelleen ja yritä uudestaan.';
$string['error_duplicatecuekey'] = 'Kahdella segmentillä on sama avain ”{$a}”; jokainen segmentti tarvitsee yksilöllisen avaimen.';
$string['error_duplicategapkey'] = 'Saman segmentin kahdella aukolla on sama avain ”{$a}”; jokainen aukko tarvitsee yksilöllisen avaimen.';
$string['error_duplicatehintlevel'] = 'Aukolla on kaksi vihjettä tasolla {$a}; jokaisen tason on oltava yksilöllinen.';
$string['error_gapnotinattemptversion'] = 'Tämä aukko ei kuulu tämän yrityksen harjoitusversioon.';
$string['error_importnocues'] = 'Tästä sisällöstä ei voitu lukea yhtään tekstitystä. WebVTT- tai SubRip-tiedostossa on jokaisen tekstityksen yläpuolella aikarivi, esimerkiksi 00:00:01.000 --> 00:00:04.000.';
$string['error_importnotutf8'] = 'Tämä tiedosto ei ole kelvollista UTF-8-muotoa. Se on todennäköisesti tallennettu vanhemmalla merkistöllä — avaa se tekstimuokkaimessa ja tallenna uudelleen UTF-8-muodossa.';
$string['error_importtoolarge'] = 'Tämä tiedosto on {$a->size}; tuonti hyväksyy enintään {$a->max}. Oppitunnin tallenteen tekstitystiedosto on paljon pienempi, joten tämä tuskin on sellainen.';
$string['error_importtoomanycues'] = 'Tässä tiedostossa on {$a->count} tekstitystä; tuonti hyväksyy enintään {$a->max}.';
$string['error_invalidcuepausemode'] = 'Valitse jokin tarjotuista vaihtoehdoista tekstityksen lopun taukoa varten.';
$string['error_invalidgradingalgorithm'] = 'Arviointialgoritmi ”{$a}” ei ole exact eikä wordrecognized.';
$string['error_invalidhinttype'] = 'Vihjetyyppi ”{$a}” ei ole yksi sallituista tyypeistä.';
$string['error_invalidisregex'] = 'Muunnoksen säännöllisen lausekkeen merkinnän on oltava 0 tai 1.';
$string['error_invalidmediakind'] = 'Valittu median tyyppi ei ole file, url eikä provider.';
$string['error_invalidpenalty'] = 'Vihjeen vähennyksen on oltava välillä 0–1.';
$string['error_invalidproviderref'] = '”{$a}” ei ole tunnistettu videon tunnus eikä linkki tälle palveluntarjoajalle.';
$string['error_invalidregexpattern'] = '”{$a}” ei ole kelvollinen säännöllinen lauseke.';
$string['error_invalidsolutionavailability'] = 'Valitse jokin tarjotuista vaihtoehdoista sille, milloin osallistujat saavat nähdä tekstivastineen ratkaisuineen.';
$string['error_invalidsourceurl'] = 'Anna täydellinen osoite, joka alkaa http:// tai https://, tai YouTube- tai Vimeo-linkki.';
$string['error_invalidsubtitleposition'] = 'Valitse jokin tarjotuista vaihtoehdoista tekstitysten sijainnille.';
$string['error_invalidv1cuejson'] = 'Tätä version 1 segmenttiä ei voitu käsitellä.';
$string['error_negativegapoffset'] = 'Aukon sijainti ja pituus eivät saa olla negatiivisia.';
$string['error_noaccesstoattempt'] = 'Sinulla ei ole pääsyä tähän yritykseen.';
$string['error_nomorehints'] = 'Tälle aukolle ei ole enempää vihjeitä.';
$string['error_nopublishedversion'] = 'Tällä harjoituksella ei ole vielä julkaistua sisältöä.';
$string['error_responsetoolong'] = 'Vastauksesi on liian pitkä. Tämän aukon enimmäismäärä on {$a} merkkiä.';
$string['error_solutionnotavailable'] = 'Tekstivastine ratkaisuineen ei ole sinun käytettävissäsi tässä aktiviteetissa.';
$string['error_staleattemptstate'] = 'Näkymäsi tästä yrityksestä on vanhentunut. Lataa nykyinen tila uudelleen ja yritä uudestaan.';
$string['error_transcriptnotavailable'] = 'Tässä aktiviteetissa ei ole ladattavaa tekstivastinetta.';
$string['error_unknowngaprule'] = 'Tuntematon aukkosäännön tyyppi ”{$a}”.';
$string['error_unknownmediaprovider'] = '”{$a}” ei ole yksi tuetuista mediapalveluntarjoajista.';
$string['error_versionnotadraft'] = 'Vain luonnostilassa olevaa versiota voi muokata.';
$string['error_versionnotfound'] = 'Tätä harjoituksen versiota ei ole enää olemassa.';
$string['error_versionnotpublishable'] = 'Tätä versiota ei voi julkaista: {$a}';
$string['export_audienceaftersubmission'] = 'Osallistujat voivat ladata sen suoritettuaan yrityksen loppuun';
$string['export_audiencealways'] = 'Osallistujat voivat ladata sen milloin tahansa';
$string['export_audiencestaff'] = 'Vain opetushenkilöstö, jolla on oikeus — ei osallistujien käytettävissä';
$string['export_docx'] = 'Lataa Word-muodossa (DOCX)';
$string['export_downloadpdf'] = 'Lataa PDF';
$string['export_heading'] = 'Vie tekstivastine';
$string['export_intro'] = 'Lataa tämän harjoituksen tekstivastine useassa muodossa.';
$string['export_moreformats'] = 'Lisää muotoja';
$string['export_nocontent'] = 'Vietävää julkaistua tekstivastinetta ei ole vielä.';
$string['export_odt'] = 'Lataa OpenDocument-muodossa (ODT)';
$string['export_pdf'] = 'Lataa PDF-muodossa';
$string['export_solution'] = 'Tekstivastine ratkaisuineen';
$string['export_solutionhint'] = 'Koko teksti, jossa jokaisen aukon ratkaisu näkyy.';
$string['export_text'] = 'Lataa tekstinä';
$string['export_versionnote'] = 'Viennit perustuvat tämän harjoituksen nyt julkaistuun versioon.';
$string['export_worksheet'] = 'Tehtäväpohja (aukot piilotettuina)';
$string['export_worksheethint'] = 'Teksti, jossa jokainen aukko on piilotettu. Valmis jaettavaksi osallistujien materiaalina.';
$string['exporttranscript'] = 'Vie tekstivastine';
$string['filearea_media'] = 'Media';
$string['filearea_poster'] = 'Esittelykuva';
$string['gradingheading'] = 'Vastausten arviointi';
$string['import_badtiming'] = 'Aikariviä ei voitu lukea: {$a}';
$string['import_emptytranscript'] = 'Segmentti ilman tekstiä ohitettiin.';
$string['import_warnlinetoolong'] = 'Lohko {$a->block} ohitettiin: siinä on rivi, joka on yli {$a->max} merkkiä pitkä, eikä se ole tekstitysrivi.';
$string['jarothreshold'] = 'Samankaltaisuuden kynnysarvo';
$string['jarothreshold_help'] = 'Aukoissa, joiden asetuksena on ”Hyväksy lähes oikeat vastaukset”, tämä on pienin Jaro-samankaltaisuus odotetun ja kirjoitetun vastauksen välillä. Arvo 1 vaatii tarkan osuman kielikohtaisen normalisoinnin jälkeen; pienemmät arvot hyväksyvät yhä poikkeavampia kirjoitusasuja.';
$string['jarothresholdrange'] = 'Kynnysarvon on oltava välillä 0–1.';
$string['language'] = 'Sisällön kieli';
$string['language_help'] = 'Valitse harjoituksen sisällön kieli. Se ohjaa vastausten vertailua, muun muassa kirjainkoon käsittelyä ja translitterointia. Valitse ”Yleinen (ei määritetty)”, jos kielikohtaista käsittelyä ei pidä käyttää. Uudet sisältöversiot lähtevät tästä asetuksesta.';
$string['language_none'] = 'Yleinen (ei määritetty)';
$string['media_cuenote'] = 'Olemassa olevat tekstitykset ja aukot säilyvät, kun vaihdat mediaa. Niiden aikoja ei säädetä, joten tarkista ne jälkeenpäin muokkaimessa.';
$string['media_current'] = 'Nykyinen media';
$string['media_heading'] = 'Media';
$string['media_intro'] = 'Valitse video tai ääni, johon tämä harjoitus perustuu. Tekstitykset ajastetaan siihen, joten tämä tulee ensin.';
$string['media_none'] = 'Tälle harjoitukselle ei ole vielä määritetty mediaa.';
$string['media_othersource'] = 'Muu lähde';
$string['media_providerhint'] = 'Tunnistetut palveluntarjoajat: {$a}. Mitä tahansa muuta osoitetta käytetään suorana median URL-osoitteena.';
$string['media_sourceurl'] = 'Median URL-osoite';
$string['media_sourceurl_help'] = 'Liitä videon osoite tiedoston lähettämisen sijaan — YouTube- tai Vimeo-linkki tai mediatiedoston suora osoite.

Tähän annettu osoite korvaa lähetetyn tiedoston. Jätä se tyhjäksi, jos haluat käyttää yllä olevaa lähetystä.

Palveluntarjoajan videon toistaa tarjoaja omassa kehyksessään, joka ei kerro toistoaikaansa. Tällainen harjoitus näyttää tekstitykset aina median alla eikä pysähdy koskaan tekstityksen loppuun.

**Minne tiedot menevät.** YouTube- tai Vimeo-kehys yhdistää jokaisen osallistujan selaimen kyseiseen yritykseen, joka saa näin osallistujan IP-osoitteen ja laitetiedot. Oletuksena harjoitus kysyy sitä ennen. Jos organisaatiollasi on oma mediapalvelin — Opencast, Panopto, Kaltura tai vastaava — liitä sen sijaan tiedoston suora osoite sieltä: sitä käsitellään tavallisena median URL-osoitteena, se säilyttää valitsemasi tekstitysten sijainnin ja taukoasetuksen, eikä kolmatta osapuolta ole mukana.';
$string['migratev1_approvalheading'] = 'Siirretty, odottaa tarkistusta';
$string['migratev1_approvebutton'] = 'Hyväksy tämä siirto';
$string['migratev1_approved'] = 'Videosanelu {$a} on merkitty hyväksytyksi.';
$string['migratev1_colactivity'] = 'Aktiviteetti';
$string['migratev1_colalgorithm'] = 'Arviointialgoritmi';
$string['migratev1_colcues'] = 'Segmentit';
$string['migratev1_colgaps'] = 'Aukot';
$string['migratev1_colissues'] = 'Ongelmat';
$string['migratev1_collearners'] = 'Osallistujat';
$string['migratev1_confirmdecommission'] = 'Tämä poistaa PERUUTTAMATTOMASTI version 1 vanhat taulut ja elang.options-sarakkeen. Toimintoa ei voi kumota. Jatketaanko?';
$string['migratev1_confirmmigrate'] = 'Tämä asettaa jonoon taustatehtävän, joka kirjoittaa version 2 tiedot jokaiselle yllä luetellulle aktiviteetille. Version 1 taulut ja elang.options jäävät koskemattomiksi. Jatketaanko?';
$string['migratev1_decommissionblocked'] = 'Poisto on yhä estetty; katso luettelo alta.';
$string['migratev1_decommissionblockedintro'] = 'Poisto on estetty, kunnes:';
$string['migratev1_decommissionbutton'] = 'Poista version 1 vanhat tiedot';
$string['migratev1_decommissioned'] = 'Version 1 vanhat tiedot on poistettu.';
$string['migratev1_decommissionheading'] = 'Version 1 tietojen käytöstä poisto';
$string['migratev1_decommissionready'] = 'Kaikki version 1 aktiviteetit on siirretty ja hyväksytty. Vanhat taulut ja elang.options voidaan nyt poistaa. Toimintoa ei voi kumota.';
$string['migratev1_heading'] = 'Siirrä version 1 aktiviteetit';
$string['migratev1_migratebutton'] = 'Siirrä nämä aktiviteetit';
$string['migratev1_noissues'] = 'Ei mitään';
$string['migratev1_nonepending'] = 'Yksikään version 1 aktiviteetti ei odota siirtoa.';
$string['migratev1_nonependingapproval'] = 'Yksikään siirretty aktiviteetti ei odota tarkistusta.';
$string['migratev1_notablespresent'] = 'Tältä sivustolta ei löytynyt version 1 vanhoja tauluja. Siirrettävää ei ole.';
$string['migratev1_parseerrorcount'] = 'Segmentit, joita ei voitu käsitellä: {$a}';
$string['migratev1_pendingheading'] = 'Ei vielä siirretty';
$string['migratev1_queued'] = 'Siirtotehtävä on asetettu jonoon. Se suoritetaan seuraavalla cron-ajolla tai heti komennolla admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Tarkistettu: siirretyt tiedot vastaavat version 1 lähdettä ilman poikkeamia.';
$string['migratev1_verifieddiscrepancies'] = 'Tarkistus löysi poikkeamia version 1 lähteeseen nähden: {$a}';
$string['migratev1_verifyfailed'] = 'Tätä aktiviteettia ei voitu tarkistaa: {$a}';
$string['modulename'] = 'Videosanelu';
$string['modulename_help'] = 'Videosanelu-aktiviteetissa osallistujat täyttävät aukkoja aikakoodatuissa tekstityksissä katsellessaan tai kuunnellessaan videota.

Opettajat tuovat WebVTT- tai SubRip-tekstitystiedoston, merkitsevät sanoja tai ilmauksia aukoiksi ja säätävät, kuinka tiukasti vastauksia verrataan. Osallistujat etenevät tekstivastineessa segmentti kerrallaan, pyytävät pisteitä vähentäviä vihjeitä ja saavat palautteen heti.';
$string['modulenameplural'] = 'Videosanelut';
$string['nav_exportshort'] = 'Vienti';
$string['nav_media'] = 'Media';
$string['nav_reports'] = 'Yritykset';
$string['nav_subtitles'] = 'Tekstitykset ja aukot';
$string['noinstances'] = 'Tällä kurssilla ei ole videosaneluja.';
$string['overview_attempts'] = 'Yritykset';
$string['playbackheading'] = 'Toisto ja tekstitykset';
$string['playbackoverlayhint'] = 'Kuvan päälle asetettu tekstitys näyttää vain sen tekstityksen, joka on parhaillaan käynnissä, joten toisto pysähtyy aina sellaisen tekstityksen loppuun, jossa on vielä täytettäviä aukkoja. Täällä ei ole mitään valittavaa.';
$string['playbackproviderhint'] = 'YouTube- tai Vimeo-videon toistaa palveluntarjoaja omassa kehyksessään, joka ei kerro toistoaikaansa. Tällainen harjoitus näyttää tekstitykset aina median alla eikä pysähdy koskaan tekstityksen loppuun, riippumatta siitä mitä yllä on valittu. Lähetetyt tiedostot ja suorat media-URL-osoitteet noudattavat molempia asetuksia.';
$string['player_check'] = 'Tarkista vastaus';
$string['player_consentaccept'] = 'Lataa video palvelusta {$a}';
$string['player_consentdetail'] = 'Toistaminen yhdistää selaimesi palveluun {$a}. {$a} saa IP-osoitteesi ja tietoja laitteestasi, ja voi lukea aiemmin asettamiaan evästeitä. Mitään ei lähetetä ennen kuin valitset videon lataamisen.';
$string['player_consentheading'] = 'Tämän videon tarjoaa {$a}';
$string['player_exitfullscreen'] = 'Poistu koko näytöstä';
$string['player_finish'] = 'Päätä yritys';
$string['player_finished'] = 'Yritys päätetty. Pisteet: %score%%';
$string['player_finishincomplete'] = 'Tyhjiä aukkoja jäljellä: {$a}. Päätetäänkö yritys silti?';
$string['player_fullscreen'] = 'Koko näyttö';
$string['player_gaplabel'] = 'Aukko %gap%';
$string['player_gaplink'] = 'Avaa linkki';
$string['player_hint'] = 'Näytä vihje';
$string['player_loaderror'] = 'Harjoitusta ei voitu ladata. Lataa sivu uudelleen.';
$string['player_loading'] = 'Ladataan harjoitusta…';
$string['player_nocontent'] = 'Harjoituksen sisältöä ei ole vielä julkaistu. Palaa myöhemmin.';
$string['player_novideotrack'] = 'Selaimesi ei osaa näyttää tämän median videoraitaa; ääni toistuu silti. Kerro asiasta opettajallesi.';
$string['player_outdatedattempt'] = 'Tätä harjoitusta on päivitetty sen jälkeen kun aloitit tämän yrityksen. Jatkat aiemmalla sisällöllä; päätä tämä yritys, niin työskentelet ensi kerralla päivitetyn harjoituksen parissa.';
$string['player_progress'] = 'Vastattu {$a->done} / {$a->total} aukkoon';
$string['player_ready'] = 'Harjoitus on valmis.';
$string['player_scorelabel'] = 'Pisteet: %score%%';
$string['player_stateaccepted'] = 'Hyväksytty';
$string['player_statecorrect'] = 'Oikein';
$string['player_statehinted'] = 'Vihje käytetty';
$string['player_stateincorrect'] = 'Väärin';
$string['player_submitfailed'] = 'Vastaustasi ei voitu tallentaa. Yritä uudelleen.';
$string['player_transcriptheading'] = 'Tekstivastine';
$string['pluginadministration'] = 'Videosanelun hallinta';
$string['pluginname'] = 'Videosanelu';
$string['privacy_metadata_elang'] = 'Jokaisesta aktiviteetista merkintä siitä, kuka hyväksyi sen 1.x-sisällön yksisuuntaisen siirron.';
$string['privacy_metadata_elang_attempt'] = 'Jokaisesta harjoituksen yrityksestä aktiviteetti tallentaa, kuka sen teki, milloin, kuinka pitkälle se eteni ja miten se arvioitiin.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Kuinka moneen aukkoon osallistuja vastasi tässä yrityksessä.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Tämän yrityksen järjestysnumero käyttäjälle ja aktiviteetille.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Kuinka moni aukko hyväksyttiin oikeaksi tässä yrityksessä.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Kuinka moneen aukkoon vastattiin merkilleen tarkasti tässä yrityksessä.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Kuinka moneen aukkoon osallistuja pyysi vihjettä tässä yrityksessä.';
$string['privacy_metadata_elang_attempt_score'] = 'Tässä yrityksessä saavutetut pisteet.';
$string['privacy_metadata_elang_attempt_state'] = 'Onko yritys kesken, päätetty vai hylätty.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'Aika, jolloin yritys päätettiin.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'Yrityksen viimeisimmän päivityksen aika.';
$string['privacy_metadata_elang_attempt_timestart'] = 'Aika, jolloin yritys aloitettiin.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Aukkojen kokonaismäärä siinä harjoitusversiossa, johon tämä yritys kuuluu.';
$string['privacy_metadata_elang_attempt_userid'] = 'Yrityksen tehneen käyttäjän tunnus.';
$string['privacy_metadata_elang_attempt_versionid'] = 'Harjoitusversio, jota vasten tämä yritys tehtiin.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'Käyttäjä, joka hyväksyi tämän aktiviteetin siirron versiosta mod_elang 1.x. Tallennetaan, jotta hyväksyntä pysyy jäljitettävissä.';
$string['privacy_metadata_elang_response'] = 'Jokaisesta aukosta, johon osallistuja vastaa yrityksen aikana, aktiviteetti tallentaa vastauksen tekstin ja sen, miten se arvioitiin.';
$string['privacy_metadata_elang_response_accepted'] = 'Hyväksyttiinkö vastaus oikeaksi tälle aukolle.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Korkein vihjetaso, joka osallistujalle näytettiin tälle aukolle.';
$string['privacy_metadata_elang_response_responsetext'] = 'Teksti, jonka osallistuja kirjoitti tähän aukkoon.';
$string['privacy_metadata_elang_response_resultstate'] = 'Luokitus, jonka arviointi antoi tälle vastaukselle (tarkka, sana tunnistettu, väärin tai tyhjä).';
$string['privacy_metadata_elang_response_score'] = 'Pisteet, jotka tämä vastaus tuotti mahdollisen vihjevähennyksen jälkeen.';
$string['privacy_metadata_elang_response_timecreated'] = 'Aika, jolloin tämä vastaus lähetettiin ensimmäisen kerran.';
$string['privacy_metadata_elang_response_timemodified'] = 'Tämän vastauksen viimeisimmän päivityksen aika.';
$string['privacy_metadata_elang_response_tries'] = 'Kuinka monta kertaa osallistuja lähetti vastauksen tähän aukkoon.';
$string['privacy_metadata_elang_version'] = 'Jokaisesta sisältöversiosta aktiviteetti tallentaa, kuka käyttäjä muutti sitä viimeksi.';
$string['privacy_metadata_elang_version_usermodified'] = 'Käyttäjä, joka muutti tätä sisältöversiota viimeksi. Tallennetaan, jotta voidaan jäljittää, kuka harjoituksen sisältöä muokkasi.';
$string['privacy_provider_externallink'] = 'Kun harjoitus perustuu YouTube- tai Vimeo-videoon, sen avaaminen yhdistää osallistujan selaimen kyseiseen palveluntarjoajaan. Liitännäinen ei lähetä itse mitään, mutta yhteyden aiheuttaa aktiviteetti. Tapahtuuko sitä lainkaan, riippuu sivuston asetuksesta palveluntarjoajien suostumuksesta ja osallistujan hyväksynnästä.';
$string['privacy_provider_ipaddress'] = 'IP-osoite, josta osallistujan selain yhdistää.';
$string['privacy_provider_useragent'] = 'Selain- ja laitetiedot, jotka selain lähettää.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Kysy ennen YouTuben tai Vimeon upottamista';
$string['providerconsent_desc'] = 'YouTube- tai Vimeo-videoon perustuvat harjoitukset näyttävät videon sijasta ilmoituksen ja upottavat sen vasta osallistujan hyväksynnän jälkeen. Ilman tätä palveluntarjoaja saa osallistujan IP-osoitteen ja selaintiedot heti sivun avautuessa — ennen kuin kukaan painaa toistoa. Poista tämä käytöstä vain, jos organisaatiosi hankkii suostumuksen muualla.';
$string['report_actions'] = 'Toiminnot';
$string['report_answered'] = 'Vastattu';
$string['report_attemptnumber'] = 'Yritys';
$string['report_back'] = 'Takaisin kaikkiin yrityksiin';
$string['report_correct'] = 'Oikein';
$string['report_delete'] = 'Poista';
$string['report_deleteconfirm'] = 'Poistetaanko tämä yritys ja kaikki sen vastaukset pysyvästi? Toimintoa ei voi kumota.';
$string['report_deleted'] = 'Yritys on poistettu.';
$string['report_exact'] = 'Tarkat';
$string['report_export'] = 'Vie';
$string['report_filterany'] = 'Kaikki';
$string['report_filterapply'] = 'Käytä suodattimia';
$string['report_filterattempt'] = 'Yrityksen numero';
$string['report_filterfrom'] = 'Aloitettu alkaen';
$string['report_filterrangeerror'] = 'Aikavälin loppu on ennen sen alkua.';
$string['report_filterreset'] = 'Tyhjennä suodattimet';
$string['report_filterstate'] = 'Tila';
$string['report_filterto'] = 'Aloitettu enintään';
$string['report_filteruser'] = 'Osallistuja';
$string['report_finished'] = 'Päätetyt';
$string['report_heading'] = 'Yritykset';
$string['report_hinted'] = 'Vihjeen kanssa';
$string['report_hints'] = 'Vihjetaso';
$string['report_kpianswered'] = 'Vastattu';
$string['report_kpiattempts'] = 'Näytetyt yritykset';
$string['report_kpiaverage'] = 'Keskimääräiset pisteet (päätetyt)';
$string['report_kpicorrect'] = 'Hyväksytyt';
$string['report_kpiexact'] = 'Täysin oikein';
$string['report_kpifinished'] = 'Päätetyt';
$string['report_kpihinted'] = 'Käyttivät vihjettä';
$string['report_kpihintedgaps'] = 'Tarvitsivat vihjeen';
$string['report_noattempts'] = 'Ei vielä yrityksiä.';
$string['report_nogaps'] = 'Versiossa, johon tämä yritys tehtiin, ei ole aukkoja.';
$string['report_nomatchingattempts'] = 'Yksikään yritys ei vastaa näitä suodattimia.';
$string['report_noresponse'] = 'Ei vastausta';
$string['report_response'] = 'Vastaus';
$string['report_result'] = 'Tulos';
$string['report_result_empty'] = 'Tyhjä';
$string['report_result_exact'] = 'Tarkka';
$string['report_result_incorrect'] = 'Väärin';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Tunnistettu';
$string['report_score'] = 'Pisteet';
$string['report_solution'] = 'Ratkaisu';
$string['report_started'] = 'Aloitettu';
$string['report_state'] = 'Tila';
$string['report_state_abandoned'] = 'Hylätty';
$string['report_state_finished'] = 'Päätetty';
$string['report_state_inprogress'] = 'Kesken';
$string['report_transcript'] = 'Tekstivastine';
$string['report_tries'] = 'Vastausyritykset';
$string['report_user'] = 'Osallistuja';
$string['report_view'] = 'Näytä';
$string['reports'] = 'Raportit';
$string['resetattempts'] = 'Poista kaikkien osallistujien yritykset ja vastaukset';
$string['solutionavailability'] = 'Tekstivastine ratkaisuineen osallistujille';
$string['solutionavailability_aftersubmission'] = 'Kun yritys on päätetty';
$string['solutionavailability_always'] = 'Milloin tahansa';
$string['solutionavailability_help'] = 'Milloin osallistujat saavat ladata koko tekstivastineen, jossa jokaisen aukon ratkaisu näkyy.

* Ei koskaan — vain opettajat voivat ladata sen.
* Kun yritys on päätetty — osallistuja saa ladata sen suoritettuaan yrityksen tässä aktiviteetissa.
* Milloin tahansa — osallistuja saa ladata sen myös ennen vastaamista.

Opetushenkilöstö, jolla on oikeus, voi ladata sen aina tästä asetuksesta riippumatta.';
$string['solutionavailability_never'] = 'Ei koskaan';
$string['subplugintype_elangscript'] = 'Kirjoitusjärjestelmän käsittelijä';
$string['subplugintype_elangscript_plural'] = 'Kirjoitusjärjestelmän käsittelijät';
$string['subtitleposition'] = 'Tekstitysten näyttäminen';
$string['subtitleposition_below'] = 'Median alla';
$string['subtitleposition_help'] = 'Missä vuorovaikutteiset tekstitykset näytetään.

* Median alla — koko tekstivastine on median alla omassa vierityskentässään ja seuraa toistoa.
* Videossa, alhaalla tai ylhäällä — median päälle piirretään vain se tekstitys, joka on parhaillaan käynnissä.

Pelkkää ääntä sisältävässä mediassa ei ole kuvaa, jonka päälle piirtää, joten se käyttää aina median alla olevaa näyttötapaa. Asetus itsessään säilyy ja tulee taas voimaan heti kun aktiviteetti käyttää videota.';
$string['subtitleposition_overlaybottom'] = 'Videossa — alhaalla';
$string['subtitleposition_overlaytop'] = 'Videossa — ylhäällä';
$string['task_migratev1activities'] = 'Siirrä version 1 aktiviteetit';
$string['transcriptheading'] = 'Tekstivastine osallistujille';
$string['validate_cueafterend'] = '{$a->where}: päättyy kohtaan {$a->endtime} ms eli median jälkeen ({$a->duration} ms). Toisto ei pääse sinne koskaan.';
$string['validate_cueendbeforestart'] = '{$a}: loppu ei ole alun jälkeen.';
$string['validate_cuewhere'] = 'Segmentti {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'Kohteen {$a} ratkaisu on tyhjä.';
$string['validate_hintlevels'] = 'Kohteen {$a} vihjetasot eivät muodosta yhtenäistä sarjaa, joka alkaa ykkösestä.';
$string['validate_negativetime'] = '{$a}: alkuaika on ennen tallenteen alkua.';
$string['validate_nocues'] = 'Versiossa ei ole segmenttejä.';
$string['validate_nogaps'] = 'Versiossa ei ole vastattavia aukkoja.';
$string['validate_nonpositivelength'] = 'Kohteen {$a} merkkipituuden on oltava positiivinen.';
$string['validate_rangeoutside'] = 'Kohteen {$a} merkkiväli on sen tekstivastineen ulkopuolella.';
$string['validate_rangeoverlap'] = 'Kohteen {$a} merkkiväli menee päällekkäin toisen aukon kanssa.';
$string['validate_unknownalgorithm'] = 'Kohteen {$a->where} arviointialgoritmia ”{$a->algorithm}” ei tunnisteta.';
$string['validate_where'] = 'aukko {$a->gapkey} segmentissä {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Aukko {$a->gapkey}: arviointialgoritmi on ”{$a->actual}”, odotettiin ”{$a->expected}”.';
$string['verify_attemptcount'] = 'Siirrettyjen yritysten määrä on {$a->actual}, odotettiin {$a->expected} eri osallistujaa versiosta 1.x.';
$string['verify_jarothreshold'] = 'Vastausten vertailun kynnysarvo on {$a->actual}, odotettiin {$a->expected}.';
$string['verify_missingattempt'] = 'Käyttäjä {$a}: odotettiin siirrettyä yritystä, yhtään ei löytynyt.';
$string['verify_missingcue'] = 'Segmentti {$a}: siirretty segmentti puuttuu.';
$string['verify_missinggap'] = 'Aukko {$a}: siirretty aukko puuttuu.';
$string['verify_missinghint'] = 'Aukko {$a}: versio 1 salli tässä vihjeen, mutta yhtään ei siirretty.';
$string['verify_orphancue'] = 'Segmentti {$a}: vastaavaa version 1 segmenttiä ei löytynyt.';
$string['verify_orphangap'] = 'Aukko {$a}: vastaavaa version 1 aukkoa ei löytynyt.';
$string['verify_rangemismatch'] = 'Aukko {$a}: merkkiväli ei vastaa version 1 lähdettä.';
$string['verify_responsecount'] = 'Käyttäjä {$a->userid}: siirrettyjen vastausten määrä on {$a->actual}, odotettiin {$a->expected}.';
$string['verify_solutionmismatch'] = 'Aukko {$a->gapkey}: ratkaisu on ”{$a->actual}”, odotettiin ”{$a->expected}”.';
$string['verify_transcriptmismatch'] = 'Segmentti {$a}: tekstivastine ei vastaa version 1 lähdettä.';
$string['verify_unexpectedhint'] = 'Aukko {$a}: versio 1 ei sallinut tässä vihjettä, mutta sellainen siirrettiin.';
