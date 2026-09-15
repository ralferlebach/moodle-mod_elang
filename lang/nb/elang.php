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
 * Norwegian Bokmål strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * No 1.3.5 translation existed to follow, so terminology comes from Moodle's own
 * Norwegian Bokmål pack — deltaker, forsøk, aktivitet, undertekster, hint.
 *
 * This is Bokmål (nb) only. Nynorsk is a separate language pack with its own
 * AMOS maintainers, and producing one by mechanically transforming this file
 * would be guesswork rather than translation.
 *
 * Deliberately not derived from the Danish or Swedish packs. The three are
 * close enough that a machine could produce something plausible, and far enough
 * apart that it would be wrong in the places that matter.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Tillatte innholdsspråk';
$string['allowedlanguages_desc'] = 'Innholdsspråkene som tilbys når et videodiktat opprettes eller redigeres. Ikke velg noen for å tilby hele språklisten. En aktivitet beholder det lagrede språket sitt selv om du fjerner det herfra senere.';
$string['allowtranscriptdownload'] = 'Deltakernes nedlasting av transkripsjonen';
$string['allowtranscriptdownload_help'] = 'Når dette er slått på, kan deltakere laste ned arbeidsarket til transkripsjonen med hvert hull skjult som PDF, Word, OpenDocument eller tekst.

Det er slått av som standard. Undervisere med tillatelse kan alltid laste ned transkripsjonen, uansett denne innstillingen.';
$string['allowtranscriptdownload_label'] = 'Deltakere kan laste ned arbeidsarket';
$string['completiondetail_completionfinishattempt'] = 'Fullføre et forsøk';
$string['completionfinishattempt'] = 'Deltakeren må fullføre et forsøk';
$string['cuepausemode'] = 'Pause ved slutten av en undertekst';
$string['cuepausemode_auto'] = 'Automatisk';
$string['cuepausemode_help'] = 'Om mediet stopper ved slutten av en undertekst.

* Automatisk — avspillingen fortsetter og stopper bare ved slutten av en undertekst så lenge det arbeides med akkurat den underteksten, altså etter et klikk på den eller på ett av hullene dens, eller når tastaturfokus står i ett av dem.
* Stopp ved hver ubesvart undertekst — avspillingen stopper ved slutten av hver undertekst som fortsatt har et tomt hull, og venter på å bli satt i gang igjen.
* Stopp aldri — avspillingen går til slutten av mediet.

Ingen av de to første stopper ved en undertekst der alle hullene er fylt ut: det er ferdig arbeid, og en stopp der ville kreve et tastetrykk uten virkning. Det betyr også at en andre gjennomgang av en øvelse bare stopper der noe fortsatt mangler.';
$string['cuepausemode_nostop'] = 'Stopp aldri';
$string['cuepausemode_stop'] = 'Stopp ved hver ubesvart undertekst';
$string['editcontent'] = 'Rediger innhold';
$string['editor_addcue'] = 'Legg til segment';
$string['editor_addgap'] = 'Lag et hull fra markeringen';
$string['editor_addhint'] = 'Legg til hint';
$string['editor_addvariant'] = 'Legg til variant';
$string['editor_advanced'] = 'Avanserte innstillinger';
$string['editor_algoexact'] = 'Nøyaktig treff';
$string['editor_algorithm'] = 'Sammenligning av svar';
$string['editor_algowordrecognized'] = 'Godta nærliggende svar';
$string['editor_answers'] = 'Godtatte varianter';
$string['editor_autosaved'] = 'Alle endringer er lagret.';
$string['editor_autosaveerror'] = 'Automatisk lagring mislyktes — bruk Lagre for å prøve på nytt.';
$string['editor_captureend'] = 'Sett slutten fra avspillingen';
$string['editor_capturestart'] = 'Sett starten fra avspillingen';
$string['editor_cueactions'] = 'Handlinger for segmentet';
$string['editor_cuecount'] = 'Segmenter: {$a}';
$string['editor_currentmedia'] = 'Gjeldende medium:';
$string['editor_deletecue'] = 'Slett segment';
$string['editor_deletegap'] = 'Slett hull';
$string['editor_emptytranscript'] = '(ingen tekst ennå)';
$string['editor_endtime'] = 'Sluttidspunkt';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Hull: {$a}';
$string['editor_gaprange'] = 'Hullets plassering (tegn)';
$string['editor_gotomedia'] = 'Gå til Medier';
$string['editor_heading'] = 'Rediger undertekster og hull';
$string['editor_hints'] = 'Hint';
$string['editor_hinttext'] = 'Hinttekst';
$string['editor_hinttype'] = 'Type';
$string['editor_hinttype_firstletter'] = 'Første bokstav';
$string['editor_hinttype_partial'] = 'Delvis';
$string['editor_hinttype_solution'] = 'Løsning';
$string['editor_hinttype_text'] = 'Fritekst';
$string['editor_hinttype_translation'] = 'Oversettelse';
$string['editor_hinttype_wordlength'] = 'Ordlengde';
$string['editor_import'] = 'Importer undertekster';
$string['editor_importappend'] = 'Legg til i eksisterende segmenter';
$string['editor_importapply'] = 'Importer';
$string['editor_importcancel'] = 'Avbryt';
$string['editor_importcheck'] = 'Kontroller innholdet';
$string['editor_importchecking'] = 'Kontrollerer…';
$string['editor_importcuecount'] = 'Funne segmenter';
$string['editor_importduration'] = 'Varighet';
$string['editor_importedcues'] = 'Importerte segmenter: {$a}';
$string['editor_importfilehint'] = 'Velg en WebVTT-fil (.vtt) eller SubRip-fil (.srt) med undertekster.';
$string['editor_importformat'] = 'Format';
$string['editor_importfromfile'] = 'Last opp en fil';
$string['editor_importfromtext'] = 'Lim inn tekst';
$string['editor_importgapcount'] = 'Funne hull';
$string['editor_importhint'] = 'Lim inn WebVTT- eller SubRip-innhold, og importer det som segmenter.';
$string['editor_importparseerror'] = 'Dette innholdet kunne ikke leses som WebVTT eller SubRip.';
$string['editor_importpastedtext'] = 'Innlimt tekst';
$string['editor_importreaderror'] = 'Filen kunne ikke leses.';
$string['editor_importready'] = 'Klar til import';
$string['editor_importreplace'] = 'Erstatt alle segmenter';
$string['editor_importreplacedcues'] = 'Segmenter erstattet; nylig importerte: {$a}';
$string['editor_importsource'] = 'Kilde';
$string['editor_importsummary'] = 'Hva som ble funnet';
$string['editor_importtoolarge'] = 'Denne filen er {$a->size}; importen godtar høyst {$a->max}.';
$string['editor_importwrongtype'] = 'Velg en undertekstfil ({$a}).';
$string['editor_insertafter'] = 'Sett inn segment etter';
$string['editor_insertbefore'] = 'Sett inn segment før';
$string['editor_invalidtime'] = 'Oppgi et tidspunkt som mm:ss.SSS, for eksempel 01:05.400.';
$string['editor_linkurl'] = 'Lenke til oppslag';
$string['editor_linkurl_help'] = 'Vises ved siden av hullet som et sted å slå opp ordet. La feltet stå tomt hvis det ikke skal være noen lenke.';
$string['editor_loaderror'] = 'Redigeringsverktøyet kunne ikke lastes. Last inn siden på nytt.';
$string['editor_loading'] = 'Laster redigeringsverktøyet…';
$string['editor_maxlength'] = 'Største lengde';
$string['editor_maxlength_help'] = 'Begrenser hvor mye en deltaker kan skrive. 0 betyr ingen grense.';
$string['editor_media'] = 'Medier';
$string['editor_mediafile'] = 'Opplastet fil';
$string['editor_mediakind'] = 'Medietype';
$string['editor_medianone'] = 'Ingen';
$string['editor_mediaprovider'] = 'Leverandør';
$string['editor_mediaproviderref'] = 'Leverandørens referanse';
$string['editor_mediaproviderrefhint'] = 'Video-ID eller lenke i en vanlig form (f.eks. youtu.be/…).';
$string['editor_mediasaved'] = 'Mediet er lagret.';
$string['editor_mediaurl'] = 'Direkte medie-URL';
$string['editor_nocues'] = 'Ingen segmenter ennå. Legg til ett, eller importer undertekster.';
$string['editor_nocueselected'] = 'Velg et segment fra listen for å redigere det.';
$string['editor_nocuesmatch'] = 'Ingen segmenter passer til dette søket.';
$string['editor_nogaps'] = 'Ingen hull';
$string['editor_nomedia'] = 'ingen';
$string['editor_nomedianotice'] = 'Legg først til video- eller lydfilen under fanen Medier. Undertekstene tidsettes mot mediet, så redigeringsverktøyet trenger det før du kan arbeide med segmenter og hull.';
$string['editor_novideotrack'] = 'Denne nettleseren kan ikke dekode videosporet i dette mediet (bare lyden spilles av); deltakerne ville se et svart bilde. Omkod filen til H.264/MP4 (for eksempel med ffmpeg eller HandBrake), og last den opp på nytt.';
$string['editor_onboardinggaps'] = 'Marker et ord i et segment, og gjør det om til et hull.';
$string['editor_onboardingimport'] = 'Importer WebVTT- eller SubRip-undertekster, eller legg til segmenter manuelt.';
$string['editor_onboardingintro'] = 'Lag en øvelse i tre trinn:';
$string['editor_onboardingmedia'] = 'Velg et medium (opplasting, URL eller leverandør).';
$string['editor_onboardingtitle'] = 'Kom i gang med øvelsen din';
$string['editor_onlywarnings'] = 'Bare segmenter med advarsler';
$string['editor_parsegaps'] = 'Gjenkjenn markører for hull: [ord] lager et hull med hint tillatt, {ord} ett uten.';
$string['editor_penalty'] = 'Fradrag';
$string['editor_poster'] = 'Plakatbilde';
$string['editor_preview'] = 'Forhåndsvisning for deltakeren';
$string['editor_publish'] = 'Publiser';
$string['editor_published'] = 'Versjonen er publisert.';
$string['editor_removehint'] = 'Fjern hintet';
$string['editor_removevariant'] = 'Fjern';
$string['editor_ruleapplied'] = 'Opprettet %count% hull ut fra regelen.';
$string['editor_ruleapply'] = 'Bruk %count% hull';
$string['editor_ruleerror'] = 'Hullene kunne ikke opprettes.';
$string['editor_ruleeverynth'] = 'Hvert n-te ord';
$string['editor_rulefound'] = 'Regelen fant %count% hull.';
$string['editor_rulegenerate'] = 'Opprett hull';
$string['editor_ruleinterval'] = 'Intervall (n)';
$string['editor_ruletype'] = 'Regel for hull';
$string['editor_rulewordlist'] = 'Ord som skal skjules';
$string['editor_rulewords'] = 'Ordliste';
$string['editor_save'] = 'Lagre utkastet';
$string['editor_saved'] = 'Utkastet er lagret.';
$string['editor_saveerror'] = 'Utkastet kunne ikke lagres.';
$string['editor_savemedia'] = 'Lagre mediet';
$string['editor_saving'] = 'Lagrer…';
$string['editor_searchcues'] = 'Søk i segmenter';
$string['editor_selecttext'] = 'Marker først ordet i transkripsjonen som skal skjules.';
$string['editor_solution'] = 'Løsning';
$string['editor_starttime'] = 'Starttidspunkt';
$string['editor_transcript'] = 'Transkripsjon';
$string['editor_unsaved'] = 'Ulagrede endringer';
$string['editor_uploadmedia'] = 'Last opp mediefiler';
$string['editor_variantisregex'] = 'Behandle {$a} som et regulært uttrykk';
$string['editor_variantmatching'] = 'Hvordan de godtatte variantene sammenlignes';
$string['editor_warnemptysolution'] = 'Et hull uten løsning';
$string['editor_warnnotranscript'] = 'Ingen tekst';
$string['editor_warntiming'] = 'Slutten ligger ikke etter starten';
$string['editor_waveform'] = 'Lydkurve';
$string['elang:addinstance'] = 'Legge til et nytt videodiktat';
$string['elang:attempt'] = 'Gjennomføre et videodiktat';
$string['elang:deleteattempts'] = 'Slette deltakernes forsøk';
$string['elang:exportreports'] = 'Eksportere rapporter med personopplysninger';
$string['elang:exportsolution'] = 'Eksportere den fullstendige transkripsjonen med løsninger';
$string['elang:exporttranscript'] = 'Eksportere arbeidsarket som dokument';
$string['elang:manage'] = 'Opprette og redigere øvingsinnhold';
$string['elang:useregex'] = 'Bruke regulære uttrykk i godtatte svar';
$string['elang:view'] = 'Se et videodiktat';
$string['elang:viewreports'] = 'Se deltakerrapporter';
$string['error_attemptnotinprogress'] = 'Dette forsøket pågår ikke lenger.';
$string['error_couldnotobtainlock'] = 'Fikk ikke låst denne operasjonen. Prøv på nytt.';
$string['error_draftrevisionmismatch'] = 'Dette utkastet er endret siden du lastet det. Last det inn på nytt, og prøv igjen.';
$string['error_duplicatecuekey'] = 'To segmenter deler nøkkelen «{$a}»; hvert segment trenger en unik nøkkel.';
$string['error_duplicategapkey'] = 'To hull i samme segment deler nøkkelen «{$a}»; hvert hull trenger en unik nøkkel.';
$string['error_duplicatehintlevel'] = 'Et hull har to hint på nivå {$a}; hvert nivå må være unikt.';
$string['error_gapnotinattemptversion'] = 'Dette hullet hører ikke til øvingsversjonen for dette forsøket.';
$string['error_importnocues'] = 'Fant ingen undertekster i dette innholdet. En WebVTT- eller SubRip-fil har en tidslinje som 00:00:01.000 --> 00:00:04.000 over hver undertekst.';
$string['error_importnotutf8'] = 'Denne filen er ikke gyldig UTF-8. Den er trolig lagret i en eldre tegnkoding — åpne den i et tekstredigeringsprogram, og lagre den på nytt som UTF-8.';
$string['error_importtoolarge'] = 'Denne filen er {$a->size}; importen godtar høyst {$a->max}. En undertekstfil til et undervisningsopptak er langt mindre, så dette er neppe en slik fil.';
$string['error_importtoomanycues'] = 'Denne filen inneholder {$a->count} undertekster; importen godtar høyst {$a->max}.';
$string['error_invalidcuepausemode'] = 'Velg ett av de tilbudte alternativene for pause ved slutten av en undertekst.';
$string['error_invalidgradingalgorithm'] = 'Vurderingsalgoritmen «{$a}» er verken exact eller wordrecognized.';
$string['error_invalidhinttype'] = 'Hinttypen «{$a}» er ikke en av de tillatte typene.';
$string['error_invalidisregex'] = 'Regex-markøren for en variant må være 0 eller 1.';
$string['error_invalidmediakind'] = 'Den valgte medietypen er ikke file, url eller provider.';
$string['error_invalidpenalty'] = 'Et fradrag for hint må ligge mellom 0 og 1.';
$string['error_invalidproviderref'] = '«{$a}» er ikke en gjenkjent video-ID eller lenke for denne leverandøren.';
$string['error_invalidregexpattern'] = '«{$a}» er ikke et gyldig regulært uttrykk.';
$string['error_invalidsolutionavailability'] = 'Velg ett av de tilbudte alternativene for når deltakere kan se transkripsjonen med løsninger.';
$string['error_invalidsourceurl'] = 'Oppgi en fullstendig adresse som begynner med http:// eller https://, eller en YouTube- eller Vimeo-lenke.';
$string['error_invalidsubtitleposition'] = 'Velg ett av de tilbudte alternativene for hvor undertekstene vises.';
$string['error_invalidv1cuejson'] = 'Dette segmentet fra versjon 1 kunne ikke behandles.';
$string['error_negativegapoffset'] = 'Et hulls plassering og lengde kan ikke være negative.';
$string['error_noaccesstoattempt'] = 'Du har ikke tilgang til dette forsøket.';
$string['error_nomorehints'] = 'Det er ingen flere hint for dette hullet.';
$string['error_nopublishedversion'] = 'Denne øvelsen har ennå ikke publisert innhold.';
$string['error_responsetoolong'] = 'Svaret ditt er for langt. Grensen for dette hullet er {$a} tegn.';
$string['error_solutionnotavailable'] = 'Transkripsjonen med løsninger er ikke tilgjengelig for deg i denne aktiviteten.';
$string['error_staleattemptstate'] = 'Visningen din av dette forsøket er utdatert. Last inn gjeldende tilstand på nytt, og prøv igjen.';
$string['error_transcriptnotavailable'] = 'Det finnes ingen transkripsjon å laste ned i denne aktiviteten.';
$string['error_unknowngaprule'] = 'Ukjent type regel for hull «{$a}».';
$string['error_unknownmediaprovider'] = '«{$a}» er ikke en av de støttede medieleverandørene.';
$string['error_versionnotadraft'] = 'Bare en versjon med status utkast kan redigeres.';
$string['error_versionnotfound'] = 'Denne versjonen av øvelsen finnes ikke lenger.';
$string['error_versionnotpublishable'] = 'Denne versjonen kan ikke publiseres: {$a}';
$string['export_audienceaftersubmission'] = 'Deltakere kan laste den ned når de har fullført et forsøk';
$string['export_audiencealways'] = 'Deltakere kan laste den ned når som helst';
$string['export_audiencestaff'] = 'Bare undervisere med tillatelse — ikke tilgjengelig for deltakere';
$string['export_docx'] = 'Last ned som Word (DOCX)';
$string['export_downloadpdf'] = 'Last ned PDF';
$string['export_heading'] = 'Eksporter transkripsjonen';
$string['export_intro'] = 'Last ned transkripsjonen av denne øvelsen i flere formater.';
$string['export_moreformats'] = 'Flere formater';
$string['export_nocontent'] = 'Det finnes ennå ingen publisert transkripsjon å eksportere.';
$string['export_odt'] = 'Last ned som OpenDocument (ODT)';
$string['export_pdf'] = 'Last ned som PDF';
$string['export_solution'] = 'Transkripsjon med løsninger';
$string['export_solutionhint'] = 'Den fullstendige teksten med løsningen på hvert hull synlig.';
$string['export_text'] = 'Last ned som tekst';
$string['export_versionnote'] = 'Eksportene tar utgangspunkt i den nå publiserte versjonen av denne øvelsen.';
$string['export_worksheet'] = 'Arbeidsark (hull skjult)';
$string['export_worksheethint'] = 'Teksten med hvert hull skjult. Klar til å deles ut som deltakermateriell.';
$string['exporttranscript'] = 'Eksporter transkripsjonen';
$string['filearea_media'] = 'Medier';
$string['filearea_poster'] = 'Plakatbilde';
$string['gradingheading'] = 'Vurdering av svar';
$string['import_badtiming'] = 'Tidslinjen kunne ikke leses: {$a}';
$string['import_emptytranscript'] = 'Et segment uten tekst ble hoppet over.';
$string['import_warnlinetoolong'] = 'Blokk {$a->block} ble hoppet over: den inneholder en linje på mer enn {$a->max} tegn, noe som ikke er en undertekstlinje.';
$string['jarothreshold'] = 'Likhetsterskel';
$string['jarothreshold_help'] = 'For hull satt til «Godta nærliggende svar» er dette den minste Jaro-likheten mellom det forventede og det innskrevne svaret. Verdien 1 krever nøyaktig treff etter den språkspesifikke normaliseringen; lavere verdier godtar stadig mer avvikende skrivemåter.';
$string['jarothresholdrange'] = 'Terskelen må ligge mellom 0 og 1.';
$string['language'] = 'Innholdets språk';
$string['language_help'] = 'Velg språket for innholdet i øvelsen. Det styrer hvordan svar sammenlignes, blant annet store og små bokstaver og translitterasjon. Velg «Generisk (ikke angitt)» hvis ingen språkspesifikk behandling skal brukes. Nye innholdsversjoner tar utgangspunkt i denne innstillingen.';
$string['language_none'] = 'Generisk (ikke angitt)';
$string['media_cuenote'] = 'Eksisterende undertekster og hull beholdes når du bytter medium. Tidene deres justeres ikke, så kontroller dem etterpå i redigeringsverktøyet.';
$string['media_current'] = 'Gjeldende medium';
$string['media_heading'] = 'Medier';
$string['media_intro'] = 'Velg videoen eller lyden denne øvelsen bygger på. Undertekstene tidsettes mot den, så dette kommer først.';
$string['media_none'] = 'Det er ennå ikke satt noe medium for denne øvelsen.';
$string['media_othersource'] = 'Annen kilde';
$string['media_providerhint'] = 'Gjenkjente leverandører: {$a}. Enhver annen adresse brukes som direkte medie-URL.';
$string['media_sourceurl'] = 'Medie-URL';
$string['media_sourceurl_help'] = 'Lim inn adressen til en video i stedet for å laste opp en fil — en YouTube- eller Vimeo-lenke, eller den direkte adressen til en mediefil.

En adresse som oppgis her, erstatter en opplastet fil. La den stå tom for å bruke opplastingen ovenfor.

En leverandørvideo spilles av i leverandørens egen ramme, som ikke melder fra om avspillingstiden. En slik øvelse viser alltid undertekstene under mediet og stopper aldri ved slutten av en undertekst.

**Hvor dataene havner.** En YouTube- eller Vimeo-ramme kobler hver deltakers nettleser til det selskapet, som dermed mottar IP-adressen og enhetsopplysningene. Som standard spør øvelsen først. Har institusjonen din en egen medieserver — Opencast, Panopto, Kaltura eller liknende — lim heller inn filens direkte adresse derfra: den behandles som en vanlig medie-URL, beholder undertekstplasseringen og pauseinnstillingen du har valgt, og ingen tredjepart er involvert.';
$string['migratev1_approvalheading'] = 'Migrert, venter på kontroll';
$string['migratev1_approvebutton'] = 'Godkjenn denne migreringen';
$string['migratev1_approved'] = 'Videodiktatet {$a} er merket som godkjent.';
$string['migratev1_colactivity'] = 'Aktivitet';
$string['migratev1_colalgorithm'] = 'Vurderingsalgoritme';
$string['migratev1_colcues'] = 'Segmenter';
$string['migratev1_colgaps'] = 'Hull';
$string['migratev1_colissues'] = 'Problemer';
$string['migratev1_collearners'] = 'Deltakere';
$string['migratev1_confirmdecommission'] = 'Dette sletter UGJENKALLELIG de gamle tabellene fra versjon 1 og elang.options. Det kan ikke angres. Fortsette?';
$string['migratev1_confirmmigrate'] = 'Dette setter en bakgrunnsoppgave i kø som skriver versjon 2-data for hver aktivitet nevnt ovenfor. Tabellene fra versjon 1 og elang.options røres ikke. Fortsette?';
$string['migratev1_decommissionblocked'] = 'Slettingen er fortsatt blokkert; se listen nedenfor.';
$string['migratev1_decommissionblockedintro'] = 'Slettingen er blokkert til:';
$string['migratev1_decommissionbutton'] = 'Slett de gamle dataene fra versjon 1';
$string['migratev1_decommissioned'] = 'De gamle dataene fra versjon 1 er slettet.';
$string['migratev1_decommissionheading'] = 'Utfasing av data fra versjon 1';
$string['migratev1_decommissionready'] = 'Alle aktiviteter fra versjon 1 er migrert og godkjent. De gamle tabellene og elang.options kan nå slettes. Dette kan ikke angres.';
$string['migratev1_heading'] = 'Migrer aktiviteter fra versjon 1';
$string['migratev1_migratebutton'] = 'Migrer disse aktivitetene';
$string['migratev1_noissues'] = 'Ingen';
$string['migratev1_nonepending'] = 'Ingen aktiviteter fra versjon 1 venter på migrering.';
$string['migratev1_nonependingapproval'] = 'Ingen migrerte aktiviteter venter på kontroll.';
$string['migratev1_notablespresent'] = 'Det ble ikke funnet gamle tabeller fra versjon 1 på dette nettstedet. Det er ingenting å migrere.';
$string['migratev1_parseerrorcount'] = 'Segmenter som ikke kunne behandles: {$a}';
$string['migratev1_pendingheading'] = 'Ennå ikke migrert';
$string['migratev1_queued'] = 'Migreringsoppgaven er satt i kø. Den kjøres ved neste cron-kjøring, eller straks via admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Kontrollert: de migrerte dataene stemmer med kilden fra versjon 1 uten avvik.';
$string['migratev1_verifieddiscrepancies'] = 'Kontrollen fant avvik fra kilden i versjon 1: {$a}';
$string['migratev1_verifyfailed'] = 'Denne aktiviteten kunne ikke kontrolleres: {$a}';
$string['modulename'] = 'Videodiktat';
$string['modulename_help'] = 'Aktiviteten videodiktat lar deltakere fylle ut hull i tidskodede undertekster mens de ser eller lytter til en video.

Undervisere importerer en WebVTT- eller SubRip-undertekstfil, merker ord eller uttrykk som hull og stiller inn hvor strengt svar sammenlignes. Deltakerne arbeider seg gjennom transkripsjonen segment for segment, ber om hint med fradrag og får tilbakemelding med én gang.';
$string['modulenameplural'] = 'Videodiktater';
$string['nav_exportshort'] = 'Eksport';
$string['nav_media'] = 'Medier';
$string['nav_reports'] = 'Forsøk';
$string['nav_subtitles'] = 'Undertekster og hull';
$string['noinstances'] = 'Det finnes ingen videodiktater i dette kurset.';
$string['overview_attempts'] = 'Forsøk';
$string['playbackheading'] = 'Avspilling og undertekster';
$string['playbackoverlayhint'] = 'En undertekst lagt over bildet viser bare den underteksten som spilles akkurat nå, så avspillingen stopper alltid ved slutten av en undertekst som fortsatt har hull å fylle. Det er ingenting å velge her.';
$string['playbackproviderhint'] = 'En YouTube- eller Vimeo-video spilles av av leverandøren i leverandørens egen ramme, som ikke melder fra om avspillingstiden. En slik øvelse viser alltid undertekstene under mediet og stopper aldri ved slutten av en undertekst, uansett hva som velges ovenfor. Opplastede filer og direkte medie-URL-er følger begge innstillingene.';
$string['player_check'] = 'Kontroller svaret';
$string['player_consentaccept'] = 'Last videoen fra {$a}';
$string['player_consentdetail'] = 'Når videoen spilles av, kobler nettleseren din seg til {$a}. {$a} mottar IP-adressen din og opplysninger om enheten din, og kan lese informasjonskapsler som allerede er satt. Ingenting sendes før du velger å laste videoen.';
$string['player_consentheading'] = 'Denne videoen leveres av {$a}';
$string['player_exitfullscreen'] = 'Avslutt fullskjerm';
$string['player_finish'] = 'Fullfør forsøket';
$string['player_finished'] = 'Forsøket er fullført. Poengsum: %score%%';
$string['player_finishincomplete'] = 'Tomme hull igjen: {$a}. Fullføre forsøket likevel?';
$string['player_fullscreen'] = 'Fullskjerm';
$string['player_gaplabel'] = 'Hull %gap%';
$string['player_gaplink'] = 'Åpne lenken';
$string['player_hint'] = 'Vis et hint';
$string['player_loaderror'] = 'Øvelsen kunne ikke lastes. Last inn siden på nytt.';
$string['player_loading'] = 'Laster øvelsen…';
$string['player_nocontent'] = 'Det er ennå ikke publisert noe øvingsinnhold. Prøv igjen senere.';
$string['player_novideotrack'] = 'Nettleseren din kan ikke vise videosporet i dette mediet; lyden spilles av likevel. Gi beskjed til underviseren din.';
$string['player_outdatedattempt'] = 'Denne øvelsen er oppdatert etter at du begynte på dette forsøket. Du fortsetter på det tidligere innholdet; fullfør dette forsøket for å arbeide med den oppdaterte øvelsen neste gang.';
$string['player_progress'] = '{$a->done} av {$a->total} hull besvart';
$string['player_ready'] = 'Øvelsen er klar.';
$string['player_scorelabel'] = 'Poengsum: %score%%';
$string['player_stateaccepted'] = 'Godtatt';
$string['player_statecorrect'] = 'Riktig';
$string['player_statehinted'] = 'Hint brukt';
$string['player_stateincorrect'] = 'Feil';
$string['player_submitfailed'] = 'Svaret ditt kunne ikke lagres. Prøv på nytt.';
$string['player_transcriptheading'] = 'Transkripsjon';
$string['pluginadministration'] = 'Administrasjon av videodiktatet';
$string['pluginname'] = 'Videodiktat';
$string['privacy_metadata_elang'] = 'For hver aktivitet registreringen av hvem som godkjente den enveis migreringen av 1.x-innholdet.';
$string['privacy_metadata_elang_attempt'] = 'For hvert forsøk på en øvelse lagrer aktiviteten hvem som gjorde det, når, hvor langt det kom, og hvordan det ble vurdert.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Hvor mange hull deltakeren besvarte i dette forsøket.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Løpenummeret for dette forsøket for brukeren og aktiviteten.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Hvor mange hull som ble godtatt som riktige i dette forsøket.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Hvor mange hull som ble besvart med nøyaktig tegntreff i dette forsøket.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Hvor mange hull deltakeren ba om hint til i dette forsøket.';
$string['privacy_metadata_elang_attempt_score'] = 'Poengsummen som ble oppnådd i dette forsøket.';
$string['privacy_metadata_elang_attempt_state'] = 'Om forsøket pågår, er fullført eller forlatt.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'Tidspunktet da forsøket ble fullført.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'Tidspunktet for siste oppdatering av forsøket.';
$string['privacy_metadata_elang_attempt_timestart'] = 'Tidspunktet da forsøket ble påbegynt.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Det samlede antallet hull i øvingsversjonen dette forsøket hører til.';
$string['privacy_metadata_elang_attempt_userid'] = 'ID-en til brukeren som gjorde forsøket.';
$string['privacy_metadata_elang_attempt_versionid'] = 'Øvingsversjonen forsøket ble gjort mot.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'Brukeren som godkjente migreringen av denne aktiviteten fra mod_elang 1.x. Lagres slik at godkjenningen fortsatt kan spores.';
$string['privacy_metadata_elang_response'] = 'For hvert hull en deltaker besvarer innenfor et forsøk, lagrer aktiviteten svarteksten og hvordan den ble vurdert.';
$string['privacy_metadata_elang_response_accepted'] = 'Om svaret ble godtatt som riktig for dette hullet.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Det høyeste hintnivået deltakeren fikk se for dette hullet.';
$string['privacy_metadata_elang_response_responsetext'] = 'Teksten deltakeren skrev i dette hullet.';
$string['privacy_metadata_elang_response_resultstate'] = 'Klassifiseringen vurderingen ga dette svaret (nøyaktig, ord gjenkjent, feil eller tomt).';
$string['privacy_metadata_elang_response_score'] = 'Poengene dette svaret bidro med, etter et eventuelt fradrag for hint.';
$string['privacy_metadata_elang_response_timecreated'] = 'Tidspunktet da dette svaret ble sendt inn første gang.';
$string['privacy_metadata_elang_response_timemodified'] = 'Tidspunktet for siste oppdatering av dette svaret.';
$string['privacy_metadata_elang_response_tries'] = 'Hvor mange ganger deltakeren sendte inn et svar på dette hullet.';
$string['privacy_metadata_elang_version'] = 'For hver innholdsversjon lagrer aktiviteten hvilken bruker som sist endret den.';
$string['privacy_metadata_elang_version_usermodified'] = 'Brukeren som sist endret denne innholdsversjonen. Lagres for å kunne spore hvem som har redigert øvingsinnholdet.';
$string['privacy_provider_externallink'] = 'Når en øvelse bygger på en YouTube- eller Vimeo-video, kobler deltakerens nettleser seg til den leverandøren når øvelsen åpnes. Tillegget sender ingenting selv, men forbindelsen skyldes aktiviteten. Om det i det hele tatt skjer, avhenger av nettstedets innstilling for samtykke til leverandører og av at deltakeren sier ja.';
$string['privacy_provider_ipaddress'] = 'IP-adressen deltakerens nettleser kobler til fra.';
$string['privacy_provider_useragent'] = 'Nettleser- og enhetsopplysningene nettleseren sender.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Spør før YouTube eller Vimeo bygges inn';
$string['providerconsent_desc'] = 'Øvelser som bygger på en YouTube- eller Vimeo-video, viser en melding i stedet for videoen og bygger den først inn etter at deltakeren har sagt ja. Uten dette mottar leverandøren deltakerens IP-adresse og nettleseropplysninger så snart siden åpnes — før noen trykker på avspilling. Slå det bare av hvis institusjonen din innhenter dette samtykket et annet sted.';
$string['report_actions'] = 'Handlinger';
$string['report_answered'] = 'Besvart';
$string['report_attemptnumber'] = 'Forsøk';
$string['report_back'] = 'Tilbake til alle forsøk';
$string['report_correct'] = 'Riktige';
$string['report_delete'] = 'Slett';
$string['report_deleteconfirm'] = 'Slette dette forsøket og alle svarene permanent? Det kan ikke angres.';
$string['report_deleted'] = 'Forsøket er slettet.';
$string['report_exact'] = 'Nøyaktige';
$string['report_export'] = 'Eksporter';
$string['report_filterany'] = 'Alle';
$string['report_filterapply'] = 'Bruk filtre';
$string['report_filterattempt'] = 'Forsøksnummer';
$string['report_filterfrom'] = 'Påbegynt fra';
$string['report_filterrangeerror'] = 'Slutten av perioden ligger før starten.';
$string['report_filterreset'] = 'Tøm filtre';
$string['report_filterstate'] = 'Status';
$string['report_filterto'] = 'Påbegynt til og med';
$string['report_filteruser'] = 'Deltaker';
$string['report_finished'] = 'Fullførte';
$string['report_heading'] = 'Forsøk';
$string['report_hinted'] = 'Med hint';
$string['report_hints'] = 'Hintnivå';
$string['report_kpianswered'] = 'Besvart';
$string['report_kpiattempts'] = 'Viste forsøk';
$string['report_kpiaverage'] = 'Gjennomsnittlig poengsum (fullførte)';
$string['report_kpicorrect'] = 'Godtatte';
$string['report_kpiexact'] = 'Helt riktige';
$string['report_kpifinished'] = 'Fullførte';
$string['report_kpihinted'] = 'Brukte hint';
$string['report_kpihintedgaps'] = 'Trengte hint';
$string['report_noattempts'] = 'Ingen forsøk ennå.';
$string['report_nogaps'] = 'Versjonen dette forsøket ble gjort på, har ingen hull.';
$string['report_nomatchingattempts'] = 'Ingen forsøk passer til disse filtrene.';
$string['report_noresponse'] = 'Ikke besvart';
$string['report_response'] = 'Svar';
$string['report_result'] = 'Resultat';
$string['report_result_empty'] = 'Tomt';
$string['report_result_exact'] = 'Nøyaktig';
$string['report_result_incorrect'] = 'Feil';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Gjenkjent';
$string['report_score'] = 'Poengsum';
$string['report_solution'] = 'Løsning';
$string['report_started'] = 'Påbegynt';
$string['report_state'] = 'Status';
$string['report_state_abandoned'] = 'Forlatt';
$string['report_state_finished'] = 'Fullført';
$string['report_state_inprogress'] = 'Pågår';
$string['report_transcript'] = 'Transkripsjon';
$string['report_tries'] = 'Svarforsøk';
$string['report_user'] = 'Deltaker';
$string['report_view'] = 'Vis';
$string['reports'] = 'Rapporter';
$string['resetattempts'] = 'Slett alle deltakeres forsøk og svar';
$string['solutionavailability'] = 'Transkripsjon med løsninger for deltakere';
$string['solutionavailability_aftersubmission'] = 'Når forsøket er fullført';
$string['solutionavailability_always'] = 'Når som helst';
$string['solutionavailability_help'] = 'Når deltakere kan laste ned den fullstendige transkripsjonen med løsningen på hvert hull synlig.

* Aldri — bare undervisere kan laste den ned.
* Når forsøket er fullført — en deltaker kan laste den ned så snart vedkommende har fullført et forsøk i denne aktiviteten.
* Når som helst — en deltaker kan laste den ned også før svarene gis.

Undervisere med tillatelse kan alltid laste den ned, uansett denne innstillingen.';
$string['solutionavailability_never'] = 'Aldri';
$string['subplugintype_elangscript'] = 'Skriftbehandler';
$string['subplugintype_elangscript_plural'] = 'Skriftbehandlere';
$string['subtitleposition'] = 'Visning av undertekster';
$string['subtitleposition_below'] = 'Under mediet';
$string['subtitleposition_help'] = 'Hvor de interaktive undertekstene vises.

* Under mediet — hele transkripsjonen står under mediet i sitt eget rulleområde og følger avspillingen.
* I videoen, nederst eller øverst — bare underteksten som spilles akkurat nå, tegnes over mediet.

Et medium med bare lyd har ikke noe bilde å tegne på, og bruker derfor alltid visningen under mediet. Selve innstillingen beholdes og gjelder igjen så snart aktiviteten bruker en video.';
$string['subtitleposition_overlaybottom'] = 'I videoen — nederst';
$string['subtitleposition_overlaytop'] = 'I videoen — øverst';
$string['task_migratev1activities'] = 'Migrer aktiviteter fra versjon 1';
$string['transcriptheading'] = 'Transkripsjon for deltakere';
$string['validate_cueafterend'] = '{$a->where}: slutter ved {$a->endtime} ms, altså etter mediet ({$a->duration} ms). Avspillingen kan aldri nå dit.';
$string['validate_cueendbeforestart'] = '{$a}: slutten ligger ikke etter starten.';
$string['validate_cuewhere'] = 'Segment {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'Løsningen for {$a} er tom.';
$string['validate_hintlevels'] = 'Hintnivåene for {$a} utgjør ikke en sammenhengende rekke som begynner på 1.';
$string['validate_negativetime'] = '{$a}: starttidspunktet ligger før opptakets begynnelse.';
$string['validate_nocues'] = 'Versjonen har ingen segmenter.';
$string['validate_nogaps'] = 'Versjonen har ingen hull å besvare.';
$string['validate_nonpositivelength'] = 'Lengden i tegn for {$a} må være positiv.';
$string['validate_rangeoutside'] = 'Tegnområdet for {$a} ligger utenfor transkripsjonen sin.';
$string['validate_rangeoverlap'] = 'Tegnområdet for {$a} overlapper et annet hull.';
$string['validate_unknownalgorithm'] = 'Vurderingsalgoritmen «{$a->algorithm}» for {$a->where} gjenkjennes ikke.';
$string['validate_where'] = 'hullet {$a->gapkey} i segment {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Hull {$a->gapkey}: vurderingsalgoritmen er «{$a->actual}», forventet var «{$a->expected}».';
$string['verify_attemptcount'] = 'Antallet migrerte forsøk er {$a->actual}, forventet var {$a->expected} ulike deltakere fra 1.x.';
$string['verify_jarothreshold'] = 'Terskelen for sammenligning av svar er {$a->actual}, forventet var {$a->expected}.';
$string['verify_missingattempt'] = 'Bruker {$a}: et migrert forsøk var forventet, men ingen ble funnet.';
$string['verify_missingcue'] = 'Segment {$a}: det migrerte segmentet mangler.';
$string['verify_missinggap'] = 'Hull {$a}: det migrerte hullet mangler.';
$string['verify_missinghint'] = 'Hull {$a}: versjon 1 tillot hjelp her, men ingen hint ble migrert.';
$string['verify_orphancue'] = 'Segment {$a}: fant ikke noe tilsvarende segment fra versjon 1.';
$string['verify_orphangap'] = 'Hull {$a}: fant ikke noe tilsvarende hull fra versjon 1.';
$string['verify_rangemismatch'] = 'Hull {$a}: tegnområdet stemmer ikke med kilden fra versjon 1.';
$string['verify_responsecount'] = 'Bruker {$a->userid}: antallet migrerte svar er {$a->actual}, forventet var {$a->expected}.';
$string['verify_solutionmismatch'] = 'Hull {$a->gapkey}: løsningen er «{$a->actual}», forventet var «{$a->expected}».';
$string['verify_transcriptmismatch'] = 'Segment {$a}: transkripsjonen stemmer ikke med kilden fra versjon 1.';
$string['verify_unexpectedhint'] = 'Hull {$a}: versjon 1 tillot ikke hjelp her, men et hint ble migrert.';
