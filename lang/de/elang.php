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
 * German language strings for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Erlaubte Inhaltssprachen';
$string['allowedlanguages_desc'] = 'Die Inhaltssprachen, die beim Anlegen oder Bearbeiten einer eLang-Aktivität angeboten werden. Ohne Auswahl wird die vollständige Sprachliste angeboten. Eine Aktivität behält ihre gespeicherte Sprache, auch wenn Sie diese hier später entfernen.';
$string['allowtranscriptdownload'] = 'Transkript-Download für Lernende';
$string['allowtranscriptdownload_help'] = 'Wenn aktiviert, können Lernende das Transkript als Arbeitsblatt — mit allen Lücken leer — als PDF-, Word-, OpenDocument- oder Textdatei herunterladen.

Standardmäßig ist das ausgeschaltet. Lehrende können das Transkript unabhängig von dieser Einstellung immer herunterladen.';
$string['allowtranscriptdownload_label'] = 'Lernende dürfen das Arbeitsblatt herunterladen';
$string['completiondetail:completionfinishattempt'] = 'Einen Versuch abschließen';
$string['completionfinishattempt'] = 'Die Person muss einen Versuch abschließen';
$string['cuepausemode'] = 'Wiedergabe an Untertitelgrenzen';
$string['cuepausemode:auto'] = 'Automatisch';
$string['cuepausemode:nostop'] = 'Nicht anhalten';
$string['cuepausemode:stop'] = 'Immer anhalten';
$string['cuepausemode_help'] = 'Ob das Medium am Ende eines Untertitels anhält.

* Automatisch — die Wiedergabe läuft durch und hält am Ende eines Untertitels nur an, solange dieser gerade bearbeitet wird, also nach einem Klick auf ihn oder eine seiner Lücken oder wenn der Tastaturfokus in einer davon steht.
* Immer anhalten — die Wiedergabe hält am Ende jedes Untertitels an und wartet auf das Fortsetzen.
* Nicht anhalten — die Wiedergabe läuft bis zum Ende des Mediums durch.';
$string['editcontent'] = 'Inhalt bearbeiten';
$string['editor:addcue'] = 'Cue hinzufügen';
$string['editor:addgap'] = 'Lücke aus Auswahl markieren';
$string['editor:addhint'] = 'Hinweis hinzufügen';
$string['editor:addvariant'] = 'Variante hinzufügen';
$string['editor:algoexact'] = 'Exakter Abgleich';
$string['editor:algorithm'] = 'Abgleich';
$string['editor:algowordrecognized'] = 'Ähnliche Antworten erkennen';
$string['editor:answers'] = 'Akzeptierte Varianten';
$string['editor:autosaved'] = 'Alle Änderungen gespeichert.';
$string['editor:autosaveerror'] = 'Automatisches Speichern fehlgeschlagen – zum erneuten Versuch „Speichern“ nutzen.';
$string['editor:captureend'] = 'Ende aus Wiedergabe';
$string['editor:capturestart'] = 'Start aus Wiedergabe';
$string['editor:currentmedia'] = 'Aktuelles Medium:';
$string['editor:deletecue'] = 'Cue löschen';
$string['editor:deletegap'] = 'Lücke löschen';
$string['editor:endtime'] = 'Ende (ms)';
$string['editor:formatsubrip'] = 'SubRip (.srt)';
$string['editor:formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor:gaprange'] = 'Lückenposition (Zeichen)';
$string['editor:gotomedia'] = 'Zu den Medien';
$string['editor:heading'] = 'Übungsinhalt-Editor';
$string['editor:hints'] = 'Hinweise';
$string['editor:hinttext'] = 'Hinweistext';
$string['editor:hinttype'] = 'Typ';
$string['editor:hinttype_firstletter'] = 'Erster Buchstabe';
$string['editor:hinttype_partial'] = 'Teilweise';
$string['editor:hinttype_solution'] = 'Lösung';
$string['editor:hinttype_text'] = 'Freitext';
$string['editor:hinttype_translation'] = 'Übersetzung';
$string['editor:hinttype_wordlength'] = 'Wortlänge';
$string['editor:import'] = 'Untertitel importieren';
$string['editor:importappend'] = 'An vorhandene Cues anhängen';
$string['editor:importapply'] = 'Importieren';
$string['editor:importcancel'] = 'Abbrechen';
$string['editor:importcheck'] = 'Inhalt prüfen';
$string['editor:importchecking'] = 'Wird geprüft …';
$string['editor:importcuecount'] = 'Cues erkannt';
$string['editor:importduration'] = 'Dauer';
$string['editor:importedcues'] = '{$a} Cue(s) importiert.';
$string['editor:importfilehint'] = 'Wählen Sie eine WebVTT- (.vtt) oder SubRip-Datei (.srt) mit Untertiteln.';
$string['editor:importformat'] = 'Format';
$string['editor:importfromfile'] = 'Datei hochladen';
$string['editor:importfromtext'] = 'Text einfügen';
$string['editor:importgapcount'] = 'Lücken erkannt';
$string['editor:importhint'] = 'WebVTT- oder SubRip-Inhalt einfügen und als Cues importieren.';
$string['editor:importparseerror'] = 'Dieser Inhalt konnte nicht als WebVTT oder SubRip gelesen werden.';
$string['editor:importpastedtext'] = 'Eingefügter Text';
$string['editor:importreaderror'] = 'Die Datei konnte nicht gelesen werden.';
$string['editor:importready'] = 'Bereit zum Import';
$string['editor:importreplace'] = 'Alle Cues ersetzen';
$string['editor:importreplacedcues'] = 'Cues durch {$a} importierte Cue(s) ersetzt.';
$string['editor:importsource'] = 'Quelle';
$string['editor:importsummary'] = 'Gefundener Inhalt';
$string['editor:loaderror'] = 'Der Editor konnte nicht geladen werden. Bitte laden Sie die Seite neu.';
$string['editor:loading'] = 'Editor wird geladen …';
$string['editor:media'] = 'Medium';
$string['editor:mediafile'] = 'Hochgeladene Datei';
$string['editor:mediakind'] = 'Medienart';
$string['editor:medianone'] = 'Keins';
$string['editor:mediaprovider'] = 'Anbieter';
$string['editor:mediaproviderref'] = 'Anbieter-Referenz';
$string['editor:mediaproviderrefhint'] = 'Video-ID oder Link in gängiger Form (z. B. youtu.be/…).';
$string['editor:mediasaved'] = 'Medium gespeichert.';
$string['editor:mediaurl'] = 'Direkte URL';
$string['editor:nocues'] = 'Noch keine Cues. Fügen Sie einen hinzu oder importieren Sie Untertitel.';
$string['editor:nogaps'] = 'Keine Lücken';
$string['editor:nomedia'] = 'keins';
$string['editor:nomedianotice'] = 'Legen Sie zuerst im Reiter „Medien" die Video- oder Audiodatei an. Untertitel werden gegen das Medium getimt, deshalb braucht der Editor eines, bevor Sie Cues und Lücken bearbeiten können.';
$string['editor:novideotrack'] = 'Dieser Browser kann die Videospur dieses Mediums nicht dekodieren (nur der Ton läuft); Lernende sähen ein schwarzes Bild. Bitte die Datei als H.264/MP4 neu kodieren (z. B. mit ffmpeg oder HandBrake) und erneut hochladen.';
$string['editor:onboardinggaps'] = 'Ein Wort in einem Cue markieren und als Lücke festlegen.';
$string['editor:onboardingimport'] = 'WebVTT-/SubRip-Untertitel importieren oder Cues von Hand anlegen.';
$string['editor:onboardingintro'] = 'Erstellen Sie eine Übung in drei Schritten:';
$string['editor:onboardingmedia'] = 'Ein Medium wählen (Upload, URL oder Anbieter).';
$string['editor:onboardingtitle'] = 'Übung beginnen';
$string['editor:parsegaps'] = 'Lücken-Markierungen erkennen: [Wort] erzeugt eine Lücke mit Hilfen, {Wort} eine ohne.';
$string['editor:penalty'] = 'Abzug';
$string['editor:poster'] = 'Vorschaubild';
$string['editor:preview'] = 'Lernenden-Vorschau';
$string['editor:publish'] = 'Veröffentlichen';
$string['editor:published'] = 'Version veröffentlicht.';
$string['editor:removehint'] = 'Hinweis entfernen';
$string['editor:removevariant'] = 'Entfernen';
$string['editor:ruleapplied'] = '%count% Lücken aus der Regel erzeugt.';
$string['editor:ruleapply'] = '%count% Lücken übernehmen';
$string['editor:ruleerror'] = 'Die Lücken konnten nicht erzeugt werden.';
$string['editor:ruleeverynth'] = 'Jedes n-te Wort';
$string['editor:rulefound'] = 'Die Regel fand %count% Lücken.';
$string['editor:rulegenerate'] = 'Lücken erzeugen';
$string['editor:ruleinterval'] = 'Intervall (n)';
$string['editor:ruletype'] = 'Lückenregel';
$string['editor:rulewordlist'] = 'Auszublendende Wörter';
$string['editor:rulewords'] = 'Wortliste';
$string['editor:save'] = 'Entwurf speichern';
$string['editor:saved'] = 'Entwurf gespeichert.';
$string['editor:saveerror'] = 'Der Entwurf konnte nicht gespeichert werden.';
$string['editor:savemedia'] = 'Medium speichern';
$string['editor:saving'] = 'Wird gespeichert …';
$string['editor:selecttext'] = 'Markieren Sie zuerst im Transkript das auszublendende Wort.';
$string['editor:solution'] = 'Lösung';
$string['editor:starttime'] = 'Start (ms)';
$string['editor:transcript'] = 'Transkript';
$string['editor:unsaved'] = 'Nicht gespeicherte Änderungen';
$string['editor:uploadmedia'] = 'Mediendateien hochladen';
$string['editor:waveform'] = 'Audio-Wellenform';
$string['elang:addinstance'] = 'Neues Video-Diktat anlegen';
$string['elang:attempt'] = 'Video-Diktat bearbeiten';
$string['elang:deleteattempts'] = 'Versuche von Lernenden löschen';
$string['elang:exportreports'] = 'Berichte mit personenbezogenen Daten exportieren';
$string['elang:exportsolution'] = 'Vollständiges Lösungstranskript exportieren';
$string['elang:exporttranscript'] = 'Transkript-Arbeitsblatt als Dokument exportieren';
$string['elang:manage'] = 'Übungsinhalte erstellen und bearbeiten';
$string['elang:useregex'] = 'Reguläre Ausdrücke in akzeptierten Antworten verwenden';
$string['elang:view'] = 'Video-Diktat ansehen';
$string['elang:viewreports'] = 'Berichte zu Lernenden ansehen';
$string['error:attemptnotinprogress'] = 'Dieser Versuch läuft nicht mehr.';
$string['error:couldnotobtainlock'] = 'Für diesen Vorgang konnte keine Sperre erlangt werden. Bitte erneut versuchen.';
$string['error:draftrevisionmismatch'] = 'Dieser Entwurf wurde seit dem Laden verändert. Bitte neu laden und erneut versuchen.';
$string['error:duplicatecuekey'] = 'Zwei Cues teilen sich den Schlüssel \'{$a}\'; jeder Cue braucht einen eindeutigen Schlüssel.';
$string['error:duplicategapkey'] = 'Zwei Lücken eines Cues teilen sich den Schlüssel \'{$a}\'; jede Lücke braucht einen eindeutigen Schlüssel.';
$string['error:duplicatehintlevel'] = 'Eine Lücke hat zwei Hinweise auf Stufe {$a}; jede Hinweisstufe muss eindeutig sein.';
$string['error:gapnotinattemptversion'] = 'Diese Lücke gehört nicht zur Übungsversion dieses Versuchs.';
$string['error:invalidcuepausemode'] = 'Wählen Sie eine der angebotenen Optionen für die Wiedergabe an Untertitelgrenzen.';
$string['error:invalidgradingalgorithm'] = 'Der Bewertungsalgorithmus \'{$a}\' ist weder exact noch wordrecognized.';
$string['error:invalidhinttype'] = 'Der Hinweistyp \'{$a}\' gehört nicht zu den erlaubten Hinweistypen.';
$string['error:invalidisregex'] = 'Der Regex-Marker einer Antwortvariante muss 0 oder 1 sein.';
$string['error:invalidmediakind'] = 'Die gewählte Medienart ist keine von file, url oder provider.';
$string['error:invalidpenalty'] = 'Ein Hinweis-Punktabzug muss zwischen 0 und 1 liegen.';
$string['error:invalidproviderref'] = '\'{$a}\' ist keine erkennbare Video-ID und kein erkennbarer Link für diesen Anbieter.';
$string['error:invalidregexpattern'] = '\'{$a}\' ist kein gültiger regulärer Ausdruck.';
$string['error:invalidsolutionavailability'] = 'Wählen Sie eine der angebotenen Optionen dafür, wann Lernende die Musterlösung sehen dürfen.';
$string['error:invalidsourceurl'] = 'Geben Sie eine vollständige Adresse mit http:// oder https:// an, oder einen YouTube- bzw. Vimeo-Link.';
$string['error:invalidsubtitleposition'] = 'Wählen Sie eine der angebotenen Optionen dafür, wo die Untertitel angezeigt werden.';
$string['error:invalidv1cuejson'] = 'Dieser Version-1-Cue konnte nicht verarbeitet werden.';
$string['error:negativegapoffset'] = 'Position und Länge einer Lücke dürfen nicht negativ sein.';
$string['error:noaccesstoattempt'] = 'Sie haben keinen Zugriff auf diesen Versuch.';
$string['error:nomorehints'] = 'Für diese Lücke sind keine weiteren Hilfen verfügbar.';
$string['error:nopublishedversion'] = 'Für diese Übung ist noch kein Inhalt veröffentlicht.';
$string['error:responsetoolong'] = 'Ihre Antwort ist zu lang. Das Maximum für diese Lücke beträgt {$a} Zeichen.';
$string['error:solutionnotavailable'] = 'Die Musterlösung steht Ihnen in dieser Aktivität nicht zur Verfügung.';
$string['error:staleattemptstate'] = 'Ihre Ansicht dieses Versuchs ist veraltet. Bitte laden Sie den aktuellen Stand neu und versuchen Sie es erneut.';
$string['error:transcriptnotavailable'] = 'In dieser Aktivität steht Ihnen kein Transkript zum Herunterladen zur Verfügung.';
$string['error:unknowngaprule'] = 'Unbekannter Lückenregel-Typ \'{$a}\'.';
$string['error:unknownmediaprovider'] = '\'{$a}\' gehört nicht zu den unterstützten Medienanbietern.';
$string['error:versionnotadraft'] = 'Nur eine Entwurfsversion kann bearbeitet werden.';
$string['error:versionnotfound'] = 'Diese Übungsversion existiert nicht mehr.';
$string['error:versionnotpublishable'] = 'Diese Version kann nicht veröffentlicht werden: {$a}';
$string['export:audienceaftersubmission'] = 'Lernende können dies nach einem abgeschlossenen Versuch herunterladen';
$string['export:audiencealways'] = 'Lernende können dies jederzeit herunterladen';
$string['export:audiencestaff'] = 'Nur Lehrkräfte und Tutor/innen — für Lernende nicht sichtbar';
$string['export:docx'] = 'Als Word (DOCX) herunterladen';
$string['export:downloadpdf'] = 'PDF herunterladen';
$string['export:heading'] = 'Transkript exportieren';
$string['export:intro'] = 'Laden Sie das Transkript dieser Übung in verschiedenen Formaten herunter.';
$string['export:moreformats'] = 'Weitere Formate';
$string['export:nocontent'] = 'Es ist noch kein veröffentlichtes Transkript zum Export vorhanden.';
$string['export:odt'] = 'Als OpenDocument (ODT) herunterladen';
$string['export:pdf'] = 'Als PDF herunterladen';
$string['export:solution'] = 'Lösungstranskript';
$string['export:solutionhint'] = 'Der vollständige Text mit allen Lückenlösungen.';
$string['export:text'] = 'Als Text herunterladen';
$string['export:versionnote'] = 'Exporte basieren auf der aktuell veröffentlichten Version dieser Übung.';
$string['export:worksheet'] = 'Arbeitsblatt (Lücken ausgeblendet)';
$string['export:worksheethint'] = 'Der Text mit ausgeblendeten Lücken. Ideal für Lernende als Arbeitsmaterial.';
$string['exporttranscript'] = 'Transkript exportieren';
$string['filearea_media'] = 'Medien';
$string['filearea_poster'] = 'Vorschaubild';
$string['gradingheading'] = 'Antwortbewertung';
$string['import:badtiming'] = 'Die Zeitangabe konnte nicht gelesen werden: {$a}';
$string['import:emptytranscript'] = 'Ein Cue ohne Transkripttext wurde übersprungen.';
$string['jarothreshold'] = 'Schwellwert für unscharfen Abgleich';
$string['jarothreshold_help'] = 'Für Lücken, die ähnliche Antworten anerkennen (Algorithmus „Wort erkannt“), ist dies die minimale Jaro-Ähnlichkeit von 0 bis 1 zwischen den reduzierten Formen, damit eine nicht identische Antwort noch als richtig zählt. 1 verlangt eine identische Reduktion – keine Unschärfe –, niedrigere Werte akzeptieren nähere Beinahe-Treffer. Neue Versionen dieser Aktivität übernehmen diesen Wert.';
$string['jarothresholdrange'] = 'Der Schwellwert muss zwischen 0 und 1 liegen.';
$string['language'] = 'Inhaltssprache';
$string['language_help'] = 'Der Sprach- oder Skriptcode des Übungsinhalts, zum Beispiel de, fr, zh-Hans oder ja. Er steuert, wie Antworten verglichen werden, einschließlich Groß-/Kleinschreibung und Transliteration. Für generische Behandlung leer lassen. Neue Versionen dieser Aktivität übernehmen diesen Wert.';
$string['language_none'] = 'Generisch (nicht angegeben)';
$string['media:cuenote'] = 'Vorhandene Untertitel und Lücken bleiben beim Wechsel des Mediums erhalten. Ihre Zeiten werden nicht angepasst — prüfen Sie sie danach im Editor.';
$string['media:current'] = 'Aktuell eingestelltes Medium';
$string['media:heading'] = 'Medien';
$string['media:intro'] = 'Wählen Sie das Video oder Audio, auf dem diese Übung aufbaut. Untertitel werden dagegen getimt, deshalb steht dies am Anfang.';
$string['media:none'] = 'Für diese Übung ist noch kein Medium eingestellt.';
$string['media:othersource'] = 'Andere Quelle';
$string['media:providerhint'] = 'Erkannte Anbieter: {$a}. Jede andere Adresse wird als direkte Medien-URL verwendet.';
$string['media:sourceurl'] = 'Adresse der Quelle';
$string['media:sourceurl_help'] = 'Fügen Sie die Adresse eines Videos ein, statt eine Datei hochzuladen — einen YouTube- oder Vimeo-Link oder die direkte Adresse einer Mediendatei.

Eine hier eingetragene Adresse ersetzt eine hochgeladene Datei. Lassen Sie das Feld leer, um den Upload oben zu verwenden.

Ein Anbietervideo wird im Rahmen des Anbieters abgespielt, der seine Wiedergabezeit nicht meldet. Eine solche Übung zeigt die Untertitel immer unter dem Medium und hält nie an Untertitelgrenzen an.';
$string['migratev1:approvalheading'] = 'Migriert, wartet auf Prüfung';
$string['migratev1:approvebutton'] = 'Diese Migration freigeben';
$string['migratev1:approved'] = 'elang {$a} wurde als freigegeben markiert.';
$string['migratev1:colactivity'] = 'Aktivität';
$string['migratev1:colalgorithm'] = 'Bewertungsalgorithmus';
$string['migratev1:colcues'] = 'Cues';
$string['migratev1:colgaps'] = 'Lücken';
$string['migratev1:colissues'] = 'Befunde';
$string['migratev1:collearners'] = 'Lernende';
$string['migratev1:confirmdecommission'] = 'Dies entfernt UNUMKEHRBAR die Version-1-Legacy-Tabellen und elang.options. Es gibt kein Zurück. Fortfahren?';
$string['migratev1:confirmmigrate'] = 'Dies reiht einen Hintergrund-Task ein, der für jede oben aufgeführte Aktivität neue Version-2-Daten schreibt. Die Version-1-Tabellen und elang.options bleiben unangetastet. Fortfahren?';
$string['migratev1:decommissionblocked'] = 'Der Abbau ist weiterhin blockiert; siehe die Liste unten.';
$string['migratev1:decommissionblockedintro'] = 'Der Abbau ist noch blockiert, bis:';
$string['migratev1:decommissionbutton'] = 'Version-1-Legacy-Daten entfernen';
$string['migratev1:decommissioned'] = 'Die Version-1-Legacy-Daten wurden entfernt.';
$string['migratev1:decommissionheading'] = 'Version-1-Daten abbauen';
$string['migratev1:decommissionready'] = 'Jede Version-1-Aktivität wurde migriert und freigegeben. Die Version-1-Legacy-Tabellen und elang.options können jetzt entfernt werden. Das ist unumkehrbar.';
$string['migratev1:heading'] = 'Version-1-Aktivitäten migrieren';
$string['migratev1:migratebutton'] = 'Diese Aktivitäten migrieren';
$string['migratev1:noissues'] = 'Keine';
$string['migratev1:nonepending'] = 'Es warten keine Version-1-Aktivitäten auf Migration.';
$string['migratev1:nonependingapproval'] = 'Es warten keine migrierten Aktivitäten auf Prüfung.';
$string['migratev1:notablespresent'] = 'Auf dieser Seite wurden keine Version-1-Legacy-Tabellen gefunden. Es gibt nichts zu migrieren.';
$string['migratev1:parseerrorcount'] = '{$a} Cue(s) konnten nicht verarbeitet werden';
$string['migratev1:pendingheading'] = 'Noch nicht migriert';
$string['migratev1:queued'] = 'Der Migrations-Task wurde eingereiht. Er läuft beim nächsten Cron-Durchlauf oder sofort über admin/cli/adhoc_task.php --execute.';
$string['migratev1:verifiedclean'] = 'Verifiziert: Die migrierten Daten stimmen ohne Abweichungen mit der Version-1-Quelle überein.';
$string['migratev1:verifieddiscrepancies'] = 'Die Verifikation fand {$a} Abweichung(en) gegenüber der Version-1-Quelle:';
$string['migratev1:verifyfailed'] = 'Diese Aktivität konnte nicht verifiziert werden: {$a}';
$string['modulename'] = 'Video-Diktat';
$string['modulename_help'] = 'Die Aktivität Video-Diktat lässt Lernende Lücken in zeitcodierten Untertiteln ausfüllen, während sie ein Video ansehen oder anhören.

Lehrende importieren eine WebVTT- oder SubRip-Datei, markieren Wörter oder Wendungen als Lücken und legen fest, wie streng Antworten verglichen werden. Lernende arbeiten das Transkript Segment für Segment durch, können abgestufte Hilfen anfordern und erhalten unmittelbare Rückmeldung.';
$string['modulenameplural'] = 'Video-Diktate';
$string['nav:exportshort'] = 'Export';
$string['nav:media'] = 'Medien';
$string['nav:reports'] = 'Versuche';
$string['nav:subtitles'] = 'Untertitel & Lücken';
$string['noinstances'] = 'In diesem Kurs gibt es keine Video-Diktate.';
$string['overview:attempts'] = 'Versuche';
$string['playbackheading'] = 'Wiedergabe und Untertitel';
$string['playbackproviderhint'] = 'Ein YouTube- oder Vimeo-Video wird vom Anbieter in einem eigenen Rahmen abgespielt, der seine Wiedergabezeit nicht meldet. Eine solche Übung zeigt die Untertitel immer unter dem Medium und hält nie an Untertitelgrenzen an, unabhängig von der Auswahl oben. Hochgeladene Dateien und direkte Medien-URLs berücksichtigen beide Einstellungen.';
$string['player:check'] = 'Antwort prüfen';
$string['player:finish'] = 'Versuch abschließen';
$string['player:finished'] = 'Versuch abgeschlossen. Ergebnis: %score%%';
$string['player:gaplabel'] = 'Lücke %gap%';
$string['player:gaplink'] = 'Link öffnen';
$string['player:hint'] = 'Hinweis anzeigen';
$string['player:loaderror'] = 'Die Übung konnte nicht geladen werden. Bitte laden Sie die Seite neu.';
$string['player:loading'] = 'Übung wird geladen …';
$string['player:nocontent'] = 'Es wurden noch keine Übungsinhalte veröffentlicht. Bitte später erneut vorbeischauen.';
$string['player:novideotrack'] = 'Ihr Browser kann die Videospur dieses Mediums nicht anzeigen; der Ton läuft weiter. Bitte informieren Sie Ihre Lehrkraft.';
$string['player:outdatedattempt'] = 'Diese Übung wurde aktualisiert, seit dieser Versuch begonnen wurde. Sie arbeiten auf dem früheren Stand weiter; schließen Sie den Versuch ab, um beim nächsten Mal die aktualisierte Übung zu nutzen.';
$string['player:ready'] = 'Übung bereit.';
$string['player:scorelabel'] = 'Ergebnis: %score%%';
$string['player:stateaccepted'] = 'Akzeptiert';
$string['player:statecorrect'] = 'Richtig';
$string['player:statehinted'] = 'Hinweis benutzt';
$string['player:stateincorrect'] = 'Falsch';
$string['player:submitfailed'] = 'Ihre Antwort konnte nicht gespeichert werden. Bitte versuchen Sie es erneut.';
$string['player:transcriptheading'] = 'Transkript';
$string['pluginadministration'] = 'Video-Diktat-Administration';
$string['pluginname'] = 'Video-Diktat';
$string['privacy:metadata:elang'] = 'Zu jeder Aktivität wird festgehalten, wer die einmalige Migration der 1.x-Inhalte freigegeben hat.';
$string['privacy:metadata:elang:migrationapproveduserid'] = 'Die Person, die die Migration dieser Aktivität aus mod_elang 1.x freigegeben hat. Wird gespeichert, damit die Freigabe nachvollziehbar bleibt.';
$string['privacy:metadata:elang_attempt'] = 'Für jeden Versuch an einer Übung speichert die Aktivität, wer ihn unternommen hat, wann, wie weit er kam und wie er bewertet wurde.';
$string['privacy:metadata:elang_attempt:answeredgaps'] = 'Wie viele Lücken die lernende Person in diesem Versuch beantwortet hat.';
$string['privacy:metadata:elang_attempt:attemptnumber'] = 'Die laufende Nummer dieses Versuchs für die Person und die Aktivität.';
$string['privacy:metadata:elang_attempt:correctgaps'] = 'Wie viele Lücken in diesem Versuch als richtig akzeptiert wurden.';
$string['privacy:metadata:elang_attempt:exactgaps'] = 'Wie viele Lücken in diesem Versuch zeichengenau beantwortet wurden.';
$string['privacy:metadata:elang_attempt:hintedgaps'] = 'Für wie viele Lücken die lernende Person in diesem Versuch eine Hilfe angefordert hat.';
$string['privacy:metadata:elang_attempt:score'] = 'Die in diesem Versuch erreichte Punktzahl.';
$string['privacy:metadata:elang_attempt:state'] = 'Ob der Versuch läuft, abgeschlossen oder abgebrochen ist.';
$string['privacy:metadata:elang_attempt:timefinish'] = 'Der Zeitpunkt, zu dem der Versuch abgeschlossen wurde.';
$string['privacy:metadata:elang_attempt:timemodified'] = 'Der Zeitpunkt der letzten Aktualisierung des Versuchs.';
$string['privacy:metadata:elang_attempt:timestart'] = 'Der Zeitpunkt, zu dem der Versuch begonnen wurde.';
$string['privacy:metadata:elang_attempt:totalgaps'] = 'Die Gesamtzahl der Lücken in der Übungsversion dieses Versuchs.';
$string['privacy:metadata:elang_attempt:userid'] = 'Die ID der Person, die den Versuch unternommen hat.';
$string['privacy:metadata:elang_attempt:versionid'] = 'Die Übungsversion, gegen die dieser Versuch unternommen wurde.';
$string['privacy:metadata:elang_response'] = 'Für jede Lücke, die eine lernende Person innerhalb eines Versuchs beantwortet, speichert die Aktivität den Antworttext und dessen Bewertung.';
$string['privacy:metadata:elang_response:accepted'] = 'Ob die Antwort für diese Lücke als richtig akzeptiert wurde.';
$string['privacy:metadata:elang_response:hintlevel'] = 'Die höchste der lernenden Person offengelegte Hilfestufe für diese Lücke.';
$string['privacy:metadata:elang_response:responsetext'] = 'Der von der lernenden Person für diese Lücke eingegebene Text.';
$string['privacy:metadata:elang_response:resultstate'] = 'Die vom Evaluator ermittelte Einstufung dieser Antwort (exakt, Wort erkannt, falsch oder leer).';
$string['privacy:metadata:elang_response:score'] = 'Die von dieser Antwort beigetragenen Punkte, nach Abzug etwaiger Hilfestrafen.';
$string['privacy:metadata:elang_response:timecreated'] = 'Der Zeitpunkt, zu dem diese Antwort erstmals abgegeben wurde.';
$string['privacy:metadata:elang_response:timemodified'] = 'Der Zeitpunkt der letzten Aktualisierung dieser Antwort.';
$string['privacy:metadata:elang_response:tries'] = 'Wie oft die lernende Person eine Antwort auf diese Lücke abgegeben hat.';
$string['privacy:metadata:elang_version'] = 'Für jede Inhaltsversion speichert die Aktivität, welche Person sie zuletzt geändert hat.';
$string['privacy:metadata:elang_version:usermodified'] = 'Die Person, die diese Inhaltsversion zuletzt geändert hat. Gespeichert, um nachvollziehen zu können, wer den Übungsinhalt bearbeitet hat.';
$string['provider:vimeo'] = 'Vimeo';
$string['provider:youtube'] = 'YouTube';
$string['report:answered'] = 'Beantwortet';
$string['report:attemptnumber'] = 'Versuch';
$string['report:back'] = 'Zurück zu allen Versuchen';
$string['report:correct'] = 'Richtig';
$string['report:delete'] = 'Löschen';
$string['report:deleteconfirm'] = 'Diesen Versuch und alle zugehörigen Antworten endgültig löschen? Das kann nicht rückgängig gemacht werden.';
$string['report:deleted'] = 'Der Versuch wurde gelöscht.';
$string['report:exact'] = 'Exakt';
$string['report:export'] = 'Exportieren';
$string['report:finished'] = 'Abgeschlossen';
$string['report:heading'] = 'Versuchsberichte';
$string['report:hinted'] = 'Mit Hinweis';
$string['report:hints'] = 'Hinweisstufe';
$string['report:noattempts'] = 'Noch keine Versuche.';
$string['report:response'] = 'Antwort';
$string['report:result'] = 'Ergebnis';
$string['report:result_empty'] = 'Leer';
$string['report:result_exact'] = 'Exakt';
$string['report:result_incorrect'] = 'Falsch';
$string['report:result_none'] = '—';
$string['report:result_wordrecognized'] = 'Erkannt';
$string['report:score'] = 'Ergebnis';
$string['report:solution'] = 'Lösung';
$string['report:state'] = 'Status';
$string['report:state_abandoned'] = 'Abgebrochen';
$string['report:state_finished'] = 'Abgeschlossen';
$string['report:state_inprogress'] = 'Läuft';
$string['report:transcript'] = 'Transkript';
$string['report:tries'] = 'Versuche';
$string['report:user'] = 'Person';
$string['report:view'] = 'Ansehen';
$string['reports'] = 'Berichte';
$string['solutionavailability'] = 'Musterlösung für Lernende';
$string['solutionavailability:aftersubmission'] = 'Nach Abgabe';
$string['solutionavailability:always'] = 'Jederzeit';
$string['solutionavailability:never'] = 'Keine Musterlösung';
$string['solutionavailability_help'] = 'Wann Lernende das vollständige Transkript mit allen Lückenlösungen herunterladen dürfen.

* Keine Musterlösung — nur Lehrende können sie herunterladen.
* Nach Abgabe — Lernende dürfen sie herunterladen, sobald sie einen Versuch in dieser Aktivität abgeschlossen haben.
* Jederzeit — Lernende dürfen sie auch schon vor dem Bearbeiten herunterladen.

Lehrende können sie unabhängig von dieser Einstellung immer herunterladen.';
$string['subplugintype_elangscript'] = 'Schrift-Handler';
$string['subplugintype_elangscript_plural'] = 'Schrift-Handler';
$string['subtitleposition'] = 'Darstellung der Untertitel';
$string['subtitleposition:below'] = 'Unter dem Medium';
$string['subtitleposition:overlaybottom'] = 'Auf dem Medium — unten';
$string['subtitleposition:overlaytop'] = 'Auf dem Medium — oben';
$string['subtitleposition_help'] = 'Wo die interaktiven Untertitel angezeigt werden.

* Unter dem Medium — das gesamte Transkript steht unter dem Medium in einem eigenen Scrollbereich und folgt der Wiedergabe.
* Auf dem Medium, unten / oben — nur der gerade laufende Untertitel wird über dem Medium angezeigt.

Ein reines Audiomedium hat kein Bild, auf dem etwas liegen könnte, und verwendet deshalb immer die Darstellung unter dem Medium. Die Einstellung bleibt erhalten und greift wieder, sobald die Aktivität ein Video verwendet.';
$string['task:migratev1activities'] = 'Version-1-Aktivitäten migrieren';
$string['transcriptheading'] = 'Transkript für Lernende';
$string['validate:emptysolution'] = 'Die Lösung für {$a} ist leer.';
$string['validate:hintlevels'] = 'Die Hinweisstufen für {$a} bilden keine lückenlose Folge ab 1.';
$string['validate:nocues'] = 'Die Version enthält keine Untertitelblöcke.';
$string['validate:nogaps'] = 'Die Version enthält keine zu beantwortenden Lücken.';
$string['validate:nonpositivelength'] = 'Die Zeichenlänge von {$a} muss positiv sein.';
$string['validate:rangeoutside'] = 'Der Zeichenbereich von {$a} liegt außerhalb des Transkripts.';
$string['validate:rangeoverlap'] = 'Der Zeichenbereich von {$a} überlappt eine andere Lücke.';
$string['validate:unknownalgorithm'] = 'Der Bewertungsalgorithmus „{$a->algorithm}" für {$a->where} ist unbekannt.';
$string['validate:where'] = 'Lücke {$a->gapkey} in Block {$a->cuekey}';
$string['verify:algorithmmismatch'] = 'Lücke {$a->gapkey}: Bewertungsalgorithmus ist „{$a->actual}", erwartet „{$a->expected}".';
$string['verify:attemptcount'] = 'Die Anzahl migrierter Versuche ist {$a->actual}, erwartet {$a->expected} verschiedene 1.x-Lernende.';
$string['verify:jarothreshold'] = 'Der Schwellenwert für den Antwortvergleich ist {$a->actual}, erwartet {$a->expected}.';
$string['verify:missingattempt'] = 'Nutzer/in {$a}: ein migrierter Versuch wurde erwartet, aber keiner gefunden.';
$string['verify:missingcue'] = 'Block {$a}: der migrierte Untertitelblock fehlt.';
$string['verify:missinggap'] = 'Lücke {$a}: die migrierte Lücke fehlt.';
$string['verify:missinghint'] = 'Lücke {$a}: Version 1 erlaubte hier Hilfe, es wurde aber kein Hinweis migriert.';
$string['verify:orphancue'] = 'Block {$a}: kein zugehöriger Block aus Version 1 gefunden.';
$string['verify:orphangap'] = 'Lücke {$a}: keine zugehörige Lücke aus Version 1 gefunden.';
$string['verify:rangemismatch'] = 'Lücke {$a}: der Zeichenbereich stimmt nicht mit der Quelle aus Version 1 überein.';
$string['verify:responsecount'] = 'Nutzer/in {$a->userid}: die Anzahl migrierter Antworten ist {$a->actual}, erwartet {$a->expected}.';
$string['verify:solutionmismatch'] = 'Lücke {$a->gapkey}: die Lösung ist „{$a->actual}", erwartet „{$a->expected}".';
$string['verify:transcriptmismatch'] = 'Block {$a}: das Transkript stimmt nicht mit der Quelle aus Version 1 überein.';
$string['verify:unexpectedhint'] = 'Lücke {$a}: Version 1 erlaubte hier keine Hilfe, es wurde aber ein Hinweis migriert.';
