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
 * Polish strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * No 1.3.5 translation existed to follow, so terminology comes from Moodle's own
 * Polish pack — uczestnik, podejście, aktywność, napisy, podpowiedź.
 *
 * Polish is where the count-neutral phrasing adopted in the RC1 terminology
 * review earns its keep. Polish nouns take three different forms depending on
 * the number (1 segment, 2 segmenty, 5 segmentów), so a string like
 * "{$a} segment" is wrong for most values. Counts are therefore written as
 * "Segmenty: {$a}", which needs no agreement at all.
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

$string['allowedlanguages'] = 'Dozwolone języki treści';
$string['allowedlanguages_desc'] = 'Języki treści oferowane przy tworzeniu lub edytowaniu dyktanda wideo. Nie wybieraj żadnego, aby oferować pełną listę języków. Aktywność zachowuje zapisany język, nawet jeśli usuniesz go później stąd.';
$string['allowtranscriptdownload'] = 'Pobieranie transkrypcji przez uczestników';
$string['allowtranscriptdownload_help'] = 'Gdy ta opcja jest włączona, uczestnicy mogą pobrać kartę pracy transkrypcji, z ukrytą każdą luką, jako PDF, Word, OpenDocument lub tekst.

Domyślnie jest wyłączona. Uprawniona kadra dydaktyczna może pobrać transkrypcję zawsze, niezależnie od tego ustawienia.';
$string['allowtranscriptdownload_label'] = 'Uczestnicy mogą pobrać kartę pracy';
$string['completiondetail_completionfinishattempt'] = 'Zakończenie podejścia';
$string['completionfinishattempt'] = 'Uczestnik musi zakończyć podejście';
$string['cuepausemode'] = 'Pauza na końcu napisu';
$string['cuepausemode_auto'] = 'Automatycznie';
$string['cuepausemode_help'] = 'Czy medium zatrzymuje się na końcu napisu.

* Automatycznie — odtwarzanie trwa dalej i zatrzymuje się na końcu napisu tylko wtedy, gdy trwa praca nad tym napisem, czyli po kliknięciu go lub jednej z jego luk albo gdy fokus klawiatury znajduje się w którejś z nich.
* Zatrzymaj przy każdym napisie bez odpowiedzi — odtwarzanie zatrzymuje się na końcu każdego napisu, w którym pozostała pusta luka, i czeka na wznowienie.
* Nigdy nie zatrzymuj — odtwarzanie biegnie do końca medium.

Żaden z dwóch pierwszych trybów nie zatrzymuje się na napisie, w którym wszystkie luki są uzupełnione: to praca skończona, a zatrzymanie się tam wymagałoby naciśnięcia klawisza bez żadnego skutku. Oznacza to również, że drugie przejście przez ćwiczenie zatrzymuje się tylko tam, gdzie czegoś jeszcze brakuje.';
$string['cuepausemode_nostop'] = 'Nigdy nie zatrzymuj';
$string['cuepausemode_stop'] = 'Zatrzymaj przy każdym napisie bez odpowiedzi';
$string['editcontent'] = 'Edytuj treść';
$string['editor_addcue'] = 'Dodaj segment';
$string['editor_addgap'] = 'Utwórz lukę z zaznaczenia';
$string['editor_addhint'] = 'Dodaj podpowiedź';
$string['editor_addvariant'] = 'Dodaj wariant';
$string['editor_advanced'] = 'Ustawienia zaawansowane';
$string['editor_algoexact'] = 'Dokładna zgodność';
$string['editor_algorithm'] = 'Porównywanie odpowiedzi';
$string['editor_algowordrecognized'] = 'Akceptuj zbliżone odpowiedzi';
$string['editor_answers'] = 'Akceptowane warianty';
$string['editor_autosaved'] = 'Wszystkie zmiany zostały zapisane.';
$string['editor_autosaveerror'] = 'Automatyczny zapis się nie powiódł — użyj przycisku Zapisz, aby spróbować ponownie.';
$string['editor_captureend'] = 'Ustaw koniec z odtwarzania';
$string['editor_capturestart'] = 'Ustaw początek z odtwarzania';
$string['editor_cueactions'] = 'Działania na segmencie';
$string['editor_cuecount'] = 'Segmenty: {$a}';
$string['editor_currentmedia'] = 'Bieżące medium:';
$string['editor_deletecue'] = 'Usuń segment';
$string['editor_deletegap'] = 'Usuń lukę';
$string['editor_emptytranscript'] = '(brak tekstu)';
$string['editor_endtime'] = 'Czas końca';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Luki: {$a}';
$string['editor_gaprange'] = 'Pozycja luki (znaki)';
$string['editor_gotomedia'] = 'Przejdź do Mediów';
$string['editor_heading'] = 'Edytuj napisy i luki';
$string['editor_hints'] = 'Podpowiedzi';
$string['editor_hinttext'] = 'Treść podpowiedzi';
$string['editor_hinttype'] = 'Typ';
$string['editor_hinttype_firstletter'] = 'Pierwsza litera';
$string['editor_hinttype_partial'] = 'Częściowa';
$string['editor_hinttype_solution'] = 'Rozwiązanie';
$string['editor_hinttype_text'] = 'Dowolny tekst';
$string['editor_hinttype_translation'] = 'Tłumaczenie';
$string['editor_hinttype_wordlength'] = 'Długość słowa';
$string['editor_import'] = 'Importuj napisy';
$string['editor_importappend'] = 'Dołącz do istniejących segmentów';
$string['editor_importapply'] = 'Importuj';
$string['editor_importcancel'] = 'Anuluj';
$string['editor_importcheck'] = 'Sprawdź treść';
$string['editor_importchecking'] = 'Sprawdzanie…';
$string['editor_importcuecount'] = 'Znalezione segmenty';
$string['editor_importduration'] = 'Czas trwania';
$string['editor_importedcues'] = 'Zaimportowane segmenty: {$a}';
$string['editor_importfilehint'] = 'Wybierz plik WebVTT (.vtt) lub SubRip (.srt) z napisami.';
$string['editor_importformat'] = 'Format';
$string['editor_importfromfile'] = 'Wyślij plik';
$string['editor_importfromtext'] = 'Wklej tekst';
$string['editor_importgapcount'] = 'Znalezione luki';
$string['editor_importhint'] = 'Wklej treść WebVTT lub SubRip, a następnie zaimportuj ją jako segmenty.';
$string['editor_importparseerror'] = 'Nie udało się odczytać tej treści jako WebVTT ani SubRip.';
$string['editor_importpastedtext'] = 'Wklejony tekst';
$string['editor_importreaderror'] = 'Nie udało się odczytać pliku.';
$string['editor_importready'] = 'Gotowe do importu';
$string['editor_importreplace'] = 'Zastąp wszystkie segmenty';
$string['editor_importreplacedcues'] = 'Segmenty zastąpione; nowo zaimportowane: {$a}';
$string['editor_importsource'] = 'Źródło';
$string['editor_importsummary'] = 'Co znaleziono';
$string['editor_importtoolarge'] = 'Ten plik ma {$a->size}; import przyjmuje najwyżej {$a->max}.';
$string['editor_importwrongtype'] = 'Wybierz plik z napisami ({$a}).';
$string['editor_insertafter'] = 'Wstaw segment po';
$string['editor_insertbefore'] = 'Wstaw segment przed';
$string['editor_invalidtime'] = 'Podaj czas w formacie mm:ss.SSS, na przykład 01:05.400.';
$string['editor_linkurl'] = 'Odnośnik do sprawdzenia';
$string['editor_linkurl_help'] = 'Wyświetlany obok luki jako miejsce, w którym można sprawdzić słowo. Pozostaw puste, aby go nie oferować.';
$string['editor_loaderror'] = 'Nie udało się wczytać edytora. Odśwież stronę.';
$string['editor_loading'] = 'Wczytywanie edytora…';
$string['editor_maxlength'] = 'Maksymalna długość';
$string['editor_maxlength_help'] = 'Ogranicza, ile uczestnik może wpisać. 0 oznacza brak ograniczenia.';
$string['editor_media'] = 'Media';
$string['editor_mediafile'] = 'Wysłany plik';
$string['editor_mediakind'] = 'Rodzaj medium';
$string['editor_medianone'] = 'Brak';
$string['editor_mediaprovider'] = 'Dostawca';
$string['editor_mediaproviderref'] = 'Odniesienie dostawcy';
$string['editor_mediaproviderrefhint'] = 'Identyfikator lub odnośnik wideo w dowolnej typowej postaci (np. youtu.be/…).';
$string['editor_mediasaved'] = 'Medium zapisane.';
$string['editor_mediaurl'] = 'Bezpośredni adres URL medium';
$string['editor_nocues'] = 'Brak segmentów. Dodaj segment lub zaimportuj napisy.';
$string['editor_nocueselected'] = 'Wybierz segment z listy, aby go edytować.';
$string['editor_nocuesmatch'] = 'Żaden segment nie pasuje do tego wyszukiwania.';
$string['editor_nogaps'] = 'Brak luk';
$string['editor_nomedia'] = 'brak';
$string['editor_nomedianotice'] = 'Najpierw dodaj plik wideo lub audio na karcie Media. Napisy są synchronizowane z medium, więc edytor potrzebuje go, zanim będzie można pracować nad segmentami i lukami.';
$string['editor_novideotrack'] = 'Ta przeglądarka nie potrafi zdekodować ścieżki wideo tego medium (odtwarzany jest tylko dźwięk); uczestnicy zobaczyliby czarny obraz. Przekoduj plik jako H.264/MP4 (na przykład za pomocą ffmpeg lub HandBrake) i wyślij go ponownie.';
$string['editor_onboardinggaps'] = 'Zaznacz słowo w segmencie i zamień je w lukę.';
$string['editor_onboardingimport'] = 'Zaimportuj napisy WebVTT lub SubRip albo dodaj segmenty ręcznie.';
$string['editor_onboardingintro'] = 'Utwórz ćwiczenie w trzech krokach:';
$string['editor_onboardingmedia'] = 'Wybierz medium (plik, adres URL lub dostawca).';
$string['editor_onboardingtitle'] = 'Rozpocznij swoje ćwiczenie';
$string['editor_onlywarnings'] = 'Tylko segmenty z ostrzeżeniami';
$string['editor_parsegaps'] = 'Rozpoznawaj znaczniki luk: [słowo] tworzy lukę z dozwolonymi podpowiedziami, {słowo} bez nich.';
$string['editor_penalty'] = 'Potrącenie';
$string['editor_poster'] = 'Obraz plakatu';
$string['editor_preview'] = 'Podgląd uczestnika';
$string['editor_publish'] = 'Opublikuj';
$string['editor_published'] = 'Wersja opublikowana.';
$string['editor_removehint'] = 'Usuń podpowiedź';
$string['editor_removevariant'] = 'Usuń';
$string['editor_ruleapplied'] = 'Utworzono luki na podstawie reguły: %count%.';
$string['editor_ruleapply'] = 'Zastosuj luki: %count%';
$string['editor_ruleerror'] = 'Nie udało się wygenerować luk.';
$string['editor_ruleeverynth'] = 'Co n-te słowo';
$string['editor_rulefound'] = 'Reguła znalazła luki: %count%.';
$string['editor_rulegenerate'] = 'Wygeneruj luki';
$string['editor_ruleinterval'] = 'Odstęp (n)';
$string['editor_ruletype'] = 'Reguła luk';
$string['editor_rulewordlist'] = 'Słowa do ukrycia';
$string['editor_rulewords'] = 'Lista słów';
$string['editor_save'] = 'Zapisz wersję roboczą';
$string['editor_saved'] = 'Wersja robocza zapisana.';
$string['editor_saveerror'] = 'Nie udało się zapisać wersji roboczej.';
$string['editor_savemedia'] = 'Zapisz medium';
$string['editor_saving'] = 'Zapisywanie…';
$string['editor_searchcues'] = 'Przeszukaj segmenty';
$string['editor_selecttext'] = 'Najpierw zaznacz w transkrypcji słowo, które ma zostać ukryte.';
$string['editor_solution'] = 'Rozwiązanie';
$string['editor_starttime'] = 'Czas początku';
$string['editor_transcript'] = 'Transkrypcja';
$string['editor_unsaved'] = 'Niezapisane zmiany';
$string['editor_uploadmedia'] = 'Wyślij pliki multimedialne';
$string['editor_variantisregex'] = 'Traktuj {$a} jako wyrażenie regularne';
$string['editor_variantmatching'] = 'Jak porównywane są akceptowane warianty';
$string['editor_warnemptysolution'] = 'Luka bez rozwiązania';
$string['editor_warnnotranscript'] = 'Brak tekstu';
$string['editor_warntiming'] = 'Koniec nie następuje po początku';
$string['editor_waveform'] = 'Przebieg dźwięku';
$string['elang:addinstance'] = 'Dodawanie nowego dyktanda wideo';
$string['elang:attempt'] = 'Wykonywanie dyktanda wideo';
$string['elang:deleteattempts'] = 'Usuwanie podejść uczestników';
$string['elang:exportreports'] = 'Eksportowanie raportów z danymi osobowymi';
$string['elang:exportsolution'] = 'Eksportowanie pełnej transkrypcji z rozwiązaniami';
$string['elang:exporttranscript'] = 'Eksportowanie karty pracy jako dokumentu';
$string['elang:manage'] = 'Tworzenie i edytowanie treści ćwiczeń';
$string['elang:useregex'] = 'Używanie wyrażeń regularnych w akceptowanych odpowiedziach';
$string['elang:view'] = 'Przeglądanie dyktanda wideo';
$string['elang:viewreports'] = 'Przeglądanie raportów uczestników';
$string['error_attemptnotinprogress'] = 'To podejście nie jest już w toku.';
$string['error_couldnotobtainlock'] = 'Nie udało się uzyskać blokady dla tej operacji. Spróbuj ponownie.';
$string['error_draftrevisionmismatch'] = 'Ta wersja robocza zmieniła się od czasu jej wczytania. Odśwież ją i spróbuj ponownie.';
$string['error_duplicatecuekey'] = 'Dwa segmenty mają wspólny klucz „{$a}”; każdy segment potrzebuje niepowtarzalnego klucza.';
$string['error_duplicategapkey'] = 'Dwie luki w tym samym segmencie mają wspólny klucz „{$a}”; każda luka potrzebuje niepowtarzalnego klucza.';
$string['error_duplicatehintlevel'] = 'Luka ma dwie podpowiedzi na poziomie {$a}; każdy poziom musi być niepowtarzalny.';
$string['error_gapnotinattemptversion'] = 'Ta luka nie należy do wersji ćwiczenia z tego podejścia.';
$string['error_importnocues'] = 'Nie udało się odczytać żadnych napisów z tej treści. Plik WebVTT lub SubRip ma nad każdym napisem wiersz czasu, na przykład 00:00:01.000 --> 00:00:04.000.';
$string['error_importnotutf8'] = 'Ten plik nie jest poprawnym UTF-8. Prawdopodobnie zapisano go w starszym kodowaniu — otwórz go w edytorze tekstu i zapisz ponownie jako UTF-8.';
$string['error_importtoolarge'] = 'Ten plik ma {$a->size}; import przyjmuje najwyżej {$a->max}. Plik napisów do nagrania zajęć jest znacznie mniejszy, więc prawdopodobnie nie jest to plik napisów.';
$string['error_importtoomanycues'] = 'Ten plik zawiera napisy w liczbie {$a->count}; import przyjmuje najwyżej {$a->max}.';
$string['error_invalidcuepausemode'] = 'Wybierz jedną z dostępnych opcji pauzy na końcu napisu.';
$string['error_invalidgradingalgorithm'] = 'Algorytm oceniania „{$a}” to ani exact, ani wordrecognized.';
$string['error_invalidhinttype'] = 'Typ podpowiedzi „{$a}” nie jest jednym z dozwolonych typów.';
$string['error_invalidisregex'] = 'Znacznik wyrażenia regularnego wariantu musi wynosić 0 lub 1.';
$string['error_invalidmediakind'] = 'Wybrany rodzaj medium to ani file, ani url, ani provider.';
$string['error_invalidpenalty'] = 'Potrącenie za podpowiedź musi mieścić się między 0 a 1.';
$string['error_invalidproviderref'] = '„{$a}” nie jest rozpoznawanym identyfikatorem ani odnośnikiem wideo dla tego dostawcy.';
$string['error_invalidregexpattern'] = '„{$a}” nie jest poprawnym wyrażeniem regularnym.';
$string['error_invalidsolutionavailability'] = 'Wybierz jedną z dostępnych opcji określających, kiedy uczestnicy mogą zobaczyć transkrypcję z rozwiązaniami.';
$string['error_invalidsourceurl'] = 'Podaj pełny adres zaczynający się od http:// lub https://, albo odnośnik do YouTube lub Vimeo.';
$string['error_invalidsubtitleposition'] = 'Wybierz jedną z dostępnych opcji położenia napisów.';
$string['error_invalidv1cuejson'] = 'Nie udało się przetworzyć tego segmentu z wersji 1.';
$string['error_negativegapoffset'] = 'Pozycja i długość luki nie mogą być ujemne.';
$string['error_noaccesstoattempt'] = 'Nie masz dostępu do tego podejścia.';
$string['error_nomorehints'] = 'Dla tej luki nie ma już kolejnych podpowiedzi.';
$string['error_nopublishedversion'] = 'To ćwiczenie nie ma jeszcze opublikowanej treści.';
$string['error_responsetoolong'] = 'Twoja odpowiedź jest za długa. Maksimum dla tej luki to {$a} znaków.';
$string['error_solutionnotavailable'] = 'Transkrypcja z rozwiązaniami nie jest dla Ciebie dostępna w tej aktywności.';
$string['error_staleattemptstate'] = 'Twój widok tego podejścia jest nieaktualny. Odśwież bieżący stan i spróbuj ponownie.';
$string['error_transcriptnotavailable'] = 'W tej aktywności nie ma transkrypcji do pobrania.';
$string['error_unknowngaprule'] = 'Nieznany typ reguły luk „{$a}”.';
$string['error_unknownmediaprovider'] = '„{$a}” nie jest jednym z obsługiwanych dostawców mediów.';
$string['error_versionnotadraft'] = 'Edytować można tylko wersję o statusie roboczym.';
$string['error_versionnotfound'] = 'Ta wersja ćwiczenia już nie istnieje.';
$string['error_versionnotpublishable'] = 'Tej wersji nie można opublikować: {$a}';
$string['export_audienceaftersubmission'] = 'Uczestnicy mogą to pobrać po zakończeniu podejścia';
$string['export_audiencealways'] = 'Uczestnicy mogą to pobrać w dowolnej chwili';
$string['export_audiencestaff'] = 'Tylko uprawniona kadra dydaktyczna — niedostępne dla uczestników';
$string['export_docx'] = 'Pobierz jako Word (DOCX)';
$string['export_downloadpdf'] = 'Pobierz PDF';
$string['export_heading'] = 'Eksportuj transkrypcję';
$string['export_intro'] = 'Pobierz transkrypcję tego ćwiczenia w kilku formatach.';
$string['export_moreformats'] = 'Więcej formatów';
$string['export_nocontent'] = 'Nie ma jeszcze opublikowanej transkrypcji do wyeksportowania.';
$string['export_odt'] = 'Pobierz jako OpenDocument (ODT)';
$string['export_pdf'] = 'Pobierz jako PDF';
$string['export_solution'] = 'Transkrypcja z rozwiązaniami';
$string['export_solutionhint'] = 'Pełny tekst z widocznym rozwiązaniem każdej luki.';
$string['export_text'] = 'Pobierz jako tekst';
$string['export_versionnote'] = 'Eksporty opierają się na aktualnie opublikowanej wersji tego ćwiczenia.';
$string['export_worksheet'] = 'Karta pracy (luki ukryte)';
$string['export_worksheethint'] = 'Tekst z ukrytą każdą luką. Gotowy do rozdania jako materiał dla uczestników.';
$string['exporttranscript'] = 'Eksportuj transkrypcję';
$string['filearea_media'] = 'Media';
$string['filearea_poster'] = 'Obraz plakatu';
$string['gradingheading'] = 'Ocenianie odpowiedzi';
$string['import_badtiming'] = 'Nie udało się odczytać wiersza czasu: {$a}';
$string['import_emptytranscript'] = 'Pominięto segment bez tekstu.';
$string['import_warnlinetoolong'] = 'Pominięto blok {$a->block}: zawiera wiersz dłuższy niż {$a->max} znaków, co nie jest wierszem napisu.';
$string['jarothreshold'] = 'Próg podobieństwa';
$string['jarothreshold_help'] = 'Dla luk ustawionych na „Akceptuj zbliżone odpowiedzi” jest to minimalne podobieństwo Jaro między odpowiedzią oczekiwaną a wpisaną. Wartość 1 wymaga dokładnej zgodności po normalizacji właściwej dla języka; niższe wartości dopuszczają coraz bardziej odmienne zapisy.';
$string['jarothresholdrange'] = 'Próg musi mieścić się między 0 a 1.';
$string['language'] = 'Język treści';
$string['language_help'] = 'Wybierz język treści ćwiczenia. Decyduje on o sposobie porównywania odpowiedzi, w tym o wielkości liter i transliteracji. Wybierz „Ogólny (nieokreślony)”, jeśli nie ma być stosowane przetwarzanie właściwe dla języka. Nowe wersje treści przejmują to ustawienie.';
$string['language_none'] = 'Ogólny (nieokreślony)';
$string['media_cuenote'] = 'Istniejące napisy i luki są zachowywane przy zmianie medium. Ich czasy nie są dopasowywane, więc sprawdź je potem w edytorze.';
$string['media_current'] = 'Bieżące medium';
$string['media_heading'] = 'Media';
$string['media_intro'] = 'Wybierz wideo lub dźwięk, na którym opiera się to ćwiczenie. Napisy są z nim synchronizowane, więc to pierwszy krok.';
$string['media_none'] = 'Dla tego ćwiczenia nie ustawiono jeszcze medium.';
$string['media_othersource'] = 'Inne źródło';
$string['media_providerhint'] = 'Rozpoznawani dostawcy: {$a}. Każdy inny adres jest używany jako bezpośredni adres URL medium.';
$string['media_sourceurl'] = 'Adres URL medium';
$string['media_sourceurl_help'] = 'Wklej adres wideo zamiast wysyłać plik — odnośnik do YouTube lub Vimeo albo bezpośredni adres pliku multimedialnego.

Adres podany tutaj zastępuje wysłany plik. Pozostaw go pusty, aby użyć pliku wysłanego powyżej.

Wideo dostawcy jest odtwarzane w jego własnej ramce, która nie przekazuje czasu odtwarzania. Takie ćwiczenie zawsze wyświetla napisy pod medium i nigdy nie zatrzymuje się na końcu napisu.

**Dokąd trafiają dane.** Ramka YouTube lub Vimeo łączy przeglądarkę każdego uczestnika z tą firmą, która otrzymuje w ten sposób jego adres IP i dane urządzenia. Domyślnie ćwiczenie pyta o zgodę, zanim to nastąpi. Jeśli Twoja instytucja ma własny serwer multimediów — Opencast, Panopto, Kaltura lub podobny — wklej zamiast tego bezpośredni adres pliku z tego serwera: zostanie potraktowany jak zwykły adres URL medium, zachowa wybrane położenie napisów i ustawienie pauzy, a żadna strona trzecia nie bierze w tym udziału.';
$string['migratev1_approvalheading'] = 'Przeniesione, oczekują na weryfikację';
$string['migratev1_approvebutton'] = 'Zatwierdź to przeniesienie';
$string['migratev1_approved'] = 'Dyktando wideo {$a} zostało oznaczone jako zatwierdzone.';
$string['migratev1_colactivity'] = 'Aktywność';
$string['migratev1_colalgorithm'] = 'Algorytm oceniania';
$string['migratev1_colcues'] = 'Segmenty';
$string['migratev1_colgaps'] = 'Luki';
$string['migratev1_colissues'] = 'Problemy';
$string['migratev1_collearners'] = 'Uczestnicy';
$string['migratev1_confirmdecommission'] = 'Ta operacja NIEODWRACALNIE usuwa dawne tabele wersji 1 oraz elang.options. Nie da się tego cofnąć. Kontynuować?';
$string['migratev1_confirmmigrate'] = 'Ta operacja zakolejkuje zadanie w tle, które zapisze dane wersji 2 dla każdej aktywności wymienionej powyżej. Tabele wersji 1 oraz elang.options pozostają nienaruszone. Kontynuować?';
$string['migratev1_decommissionblocked'] = 'Usunięcie jest nadal zablokowane; zobacz listę poniżej.';
$string['migratev1_decommissionblockedintro'] = 'Usunięcie jest zablokowane, dopóki:';
$string['migratev1_decommissionbutton'] = 'Usuń dawne dane wersji 1';
$string['migratev1_decommissioned'] = 'Dawne dane wersji 1 zostały usunięte.';
$string['migratev1_decommissionheading'] = 'Wycofanie danych wersji 1';
$string['migratev1_decommissionready'] = 'Wszystkie aktywności wersji 1 zostały przeniesione i zatwierdzone. Dawne tabele oraz elang.options można teraz usunąć. Tej operacji nie da się cofnąć.';
$string['migratev1_heading'] = 'Przenieś aktywności wersji 1';
$string['migratev1_migratebutton'] = 'Przenieś te aktywności';
$string['migratev1_noissues'] = 'Brak';
$string['migratev1_nonepending'] = 'Żadna aktywność wersji 1 nie oczekuje na przeniesienie.';
$string['migratev1_nonependingapproval'] = 'Żadna przeniesiona aktywność nie oczekuje na weryfikację.';
$string['migratev1_notablespresent'] = 'Na tej stronie nie znaleziono dawnych tabel wersji 1. Nie ma nic do przeniesienia.';
$string['migratev1_parseerrorcount'] = 'Segmenty, których nie udało się przetworzyć: {$a}';
$string['migratev1_pendingheading'] = 'Jeszcze nieprzeniesione';
$string['migratev1_queued'] = 'Zadanie przeniesienia zostało zakolejkowane. Wykona się przy następnym przebiegu crona albo od razu poleceniem admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Zweryfikowano: przeniesione dane zgadzają się ze źródłem wersji 1 bez rozbieżności.';
$string['migratev1_verifieddiscrepancies'] = 'Weryfikacja wykryła rozbieżności wobec źródła wersji 1: {$a}';
$string['migratev1_verifyfailed'] = 'Nie udało się zweryfikować tej aktywności: {$a}';
$string['modulename'] = 'Dyktando wideo';
$string['modulename_help'] = 'Aktywność dyktando wideo pozwala uczestnikom uzupełniać luki w napisach z kodem czasowym, oglądając wideo lub go słuchając.

Prowadzący importują plik napisów WebVTT lub SubRip, oznaczają słowa albo wyrażenia jako luki i ustawiają, jak rygorystycznie porównywane są odpowiedzi. Uczestnicy przechodzą transkrypcję segment po segmencie, proszą o punktowane podpowiedzi i otrzymują natychmiastową informację zwrotną.';
$string['modulenameplural'] = 'Dyktanda wideo';
$string['nav_exportshort'] = 'Eksport';
$string['nav_media'] = 'Media';
$string['nav_reports'] = 'Podejścia';
$string['nav_subtitles'] = 'Napisy i luki';
$string['noinstances'] = 'W tym kursie nie ma dyktand wideo.';
$string['overview_attempts'] = 'Podejścia';
$string['playbackheading'] = 'Odtwarzanie i napisy';
$string['playbackoverlayhint'] = 'Napis nałożony na obraz pokazuje tylko ten napis, który jest właśnie odtwarzany, więc odtwarzanie zawsze zatrzymuje się na końcu napisu, w którym pozostały luki do uzupełnienia. Nie ma tu nic do wyboru.';
$string['playbackproviderhint'] = 'Wideo z YouTube lub Vimeo jest odtwarzane przez dostawcę w jego własnej ramce, która nie przekazuje czasu odtwarzania. Takie ćwiczenie zawsze wyświetla napisy pod medium i nigdy nie zatrzymuje się na końcu napisu, niezależnie od wyboru powyżej. Wysłane pliki i bezpośrednie adresy URL respektują oba ustawienia.';
$string['player_check'] = 'Sprawdź odpowiedź';
$string['player_consentaccept'] = 'Wczytaj wideo z {$a}';
$string['player_consentdetail'] = 'Odtworzenie łączy Twoją przeglądarkę z {$a}. {$a} otrzymuje Twój adres IP i informacje o urządzeniu oraz może odczytać ciasteczka, które wcześniej ustawił. Nic nie zostaje wysłane, dopóki nie zdecydujesz się wczytać wideo.';
$string['player_consentheading'] = 'To wideo udostępnia {$a}';
$string['player_finish'] = 'Zakończ podejście';
$string['player_finished'] = 'Podejście zakończone. Wynik: %score%%';
$string['player_finishincomplete'] = 'Puste luki: {$a}. Zakończyć podejście mimo to?';
$string['player_gaplabel'] = 'Luka %gap%';
$string['player_gaplink'] = 'Otwórz odnośnik';
$string['player_hint'] = 'Pokaż podpowiedź';
$string['player_loaderror'] = 'Nie udało się wczytać ćwiczenia. Odśwież stronę.';
$string['player_loading'] = 'Wczytywanie ćwiczenia…';
$string['player_nocontent'] = 'Nie opublikowano jeszcze treści ćwiczenia. Zajrzyj później.';
$string['player_novideotrack'] = 'Twoja przeglądarka nie potrafi wyświetlić ścieżki wideo tego medium; dźwięk będzie odtwarzany. Powiadom prowadzącego.';
$string['player_outdatedattempt'] = 'To ćwiczenie zostało zaktualizowane po rozpoczęciu tego podejścia. Kontynuujesz na wcześniejszej treści; zakończ to podejście, aby następnym razem pracować na zaktualizowanym ćwiczeniu.';
$string['player_progress'] = 'Odpowiedzi udzielono na {$a->done} z {$a->total} luk';
$string['player_ready'] = 'Ćwiczenie gotowe.';
$string['player_scorelabel'] = 'Wynik: %score%%';
$string['player_stateaccepted'] = 'Zaakceptowana';
$string['player_statecorrect'] = 'Poprawna';
$string['player_statehinted'] = 'Użyto podpowiedzi';
$string['player_stateincorrect'] = 'Niepoprawna';
$string['player_submitfailed'] = 'Nie udało się zapisać Twojej odpowiedzi. Spróbuj ponownie.';
$string['player_transcriptheading'] = 'Transkrypcja';
$string['pluginadministration'] = 'Administracja dyktandem wideo';
$string['pluginname'] = 'Dyktando wideo';
$string['privacy_metadata_elang'] = 'Dla każdej aktywności zapis tego, kto zatwierdził jednokierunkowe przeniesienie jej treści z wersji 1.x.';
$string['privacy_metadata_elang_attempt'] = 'Dla każdego podejścia do ćwiczenia aktywność przechowuje, kto je wykonał, kiedy, jak daleko zaszło i jak zostało ocenione.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Na ile luk uczestnik odpowiedział w tym podejściu.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Kolejny numer tego podejścia dla użytkownika i aktywności.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Ile luk zaakceptowano jako poprawne w tym podejściu.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Na ile luk odpowiedziano z dokładną zgodnością znaków w tym podejściu.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Dla ilu luk uczestnik poprosił o podpowiedź w tym podejściu.';
$string['privacy_metadata_elang_attempt_score'] = 'Wynik osiągnięty w tym podejściu.';
$string['privacy_metadata_elang_attempt_state'] = 'Czy podejście trwa, zostało zakończone czy porzucone.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'Czas zakończenia podejścia.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'Czas ostatniej aktualizacji podejścia.';
$string['privacy_metadata_elang_attempt_timestart'] = 'Czas rozpoczęcia podejścia.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Łączna liczba luk w wersji ćwiczenia, której dotyczy to podejście.';
$string['privacy_metadata_elang_attempt_userid'] = 'Identyfikator użytkownika, który wykonał podejście.';
$string['privacy_metadata_elang_attempt_versionid'] = 'Wersja ćwiczenia, wobec której wykonano to podejście.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'Użytkownik, który zatwierdził przeniesienie tej aktywności z mod_elang 1.x. Przechowywany, aby zatwierdzenie pozostało możliwe do zweryfikowania.';
$string['privacy_metadata_elang_response'] = 'Dla każdej luki, na którą uczestnik odpowiada w ramach podejścia, aktywność przechowuje treść odpowiedzi i sposób jej oceny.';
$string['privacy_metadata_elang_response_accepted'] = 'Czy odpowiedź została zaakceptowana jako poprawna dla tej luki.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Najwyższy poziom podpowiedzi ujawniony uczestnikowi dla tej luki.';
$string['privacy_metadata_elang_response_responsetext'] = 'Tekst, który uczestnik wpisał w tej luce.';
$string['privacy_metadata_elang_response_resultstate'] = 'Klasyfikacja przypisana tej odpowiedzi przez mechanizm oceniania (dokładna, rozpoznane słowo, niepoprawna lub pusta).';
$string['privacy_metadata_elang_response_score'] = 'Punkty wniesione przez tę odpowiedź, po ewentualnym potrąceniu za podpowiedź.';
$string['privacy_metadata_elang_response_timecreated'] = 'Czas pierwszego przesłania tej odpowiedzi.';
$string['privacy_metadata_elang_response_timemodified'] = 'Czas ostatniej aktualizacji tej odpowiedzi.';
$string['privacy_metadata_elang_response_tries'] = 'Ile razy uczestnik przesłał odpowiedź dla tej luki.';
$string['privacy_metadata_elang_version'] = 'Dla każdej wersji treści aktywność przechowuje, który użytkownik zmienił ją jako ostatni.';
$string['privacy_metadata_elang_version_usermodified'] = 'Użytkownik, który ostatni zmienił tę wersję treści. Przechowywany, aby można było sprawdzić, kto edytował treść ćwiczenia.';
$string['privacy_provider_externallink'] = 'Gdy ćwiczenie opiera się na wideo z YouTube lub Vimeo, jego otwarcie łączy przeglądarkę uczestnika z tym dostawcą. Wtyczka sama niczego nie wysyła, ale połączenie wywołuje aktywność. To, czy w ogóle do niego dojdzie, zależy od ustawienia strony dotyczącego zgody na dostawców oraz od zgody uczestnika.';
$string['privacy_provider_ipaddress'] = 'Adres IP, z którego łączy się przeglądarka uczestnika.';
$string['privacy_provider_useragent'] = 'Dane o przeglądarce i urządzeniu wysyłane przez przeglądarkę.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Pytaj przed osadzeniem YouTube lub Vimeo';
$string['providerconsent_desc'] = 'Ćwiczenia oparte na wideo z YouTube lub Vimeo pokazują komunikat zamiast wideo i osadzają je dopiero po zgodzie uczestnika. Bez tego dostawca otrzymuje adres IP i dane przeglądarki uczestnika już w chwili otwarcia strony — zanim ktokolwiek naciśnie odtwarzanie. Wyłącz tę opcję tylko wtedy, gdy Twoja instytucja uzyskuje tę zgodę w inny sposób.';
$string['report_actions'] = 'Działania';
$string['report_answered'] = 'Z odpowiedzią';
$string['report_attemptnumber'] = 'Podejście';
$string['report_back'] = 'Wróć do wszystkich podejść';
$string['report_correct'] = 'Poprawne';
$string['report_delete'] = 'Usuń';
$string['report_deleteconfirm'] = 'Trwale usunąć to podejście i wszystkie jego odpowiedzi? Tej operacji nie da się cofnąć.';
$string['report_deleted'] = 'Podejście zostało usunięte.';
$string['report_exact'] = 'Dokładne';
$string['report_export'] = 'Eksportuj';
$string['report_filterany'] = 'Wszystkie';
$string['report_filterapply'] = 'Zastosuj filtry';
$string['report_filterattempt'] = 'Numer podejścia';
$string['report_filterfrom'] = 'Rozpoczęte od';
$string['report_filterrangeerror'] = 'Koniec zakresu wypada przed jego początkiem.';
$string['report_filterreset'] = 'Wyczyść filtry';
$string['report_filterstate'] = 'Stan';
$string['report_filterto'] = 'Rozpoczęte do';
$string['report_filteruser'] = 'Uczestnik';
$string['report_finished'] = 'Zakończone';
$string['report_heading'] = 'Podejścia';
$string['report_hinted'] = 'Z podpowiedzią';
$string['report_hints'] = 'Poziom podpowiedzi';
$string['report_kpianswered'] = 'Z odpowiedzią';
$string['report_kpiattempts'] = 'Pokazane podejścia';
$string['report_kpiaverage'] = 'Średni wynik (zakończone)';
$string['report_kpicorrect'] = 'Zaakceptowane';
$string['report_kpiexact'] = 'Dokładnie poprawne';
$string['report_kpifinished'] = 'Zakończone';
$string['report_kpihinted'] = 'Skorzystali z podpowiedzi';
$string['report_kpihintedgaps'] = 'Potrzebowali podpowiedzi';
$string['report_noattempts'] = 'Brak podejść.';
$string['report_nogaps'] = 'Wersja, w której wykonano to podejście, nie ma luk.';
$string['report_nomatchingattempts'] = 'Żadne podejście nie pasuje do tych filtrów.';
$string['report_noresponse'] = 'Bez odpowiedzi';
$string['report_response'] = 'Odpowiedź';
$string['report_result'] = 'Wynik';
$string['report_result_empty'] = 'Pusta';
$string['report_result_exact'] = 'Dokładna';
$string['report_result_incorrect'] = 'Niepoprawna';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Rozpoznana';
$string['report_score'] = 'Wynik';
$string['report_solution'] = 'Rozwiązanie';
$string['report_started'] = 'Rozpoczęte';
$string['report_state'] = 'Stan';
$string['report_state_abandoned'] = 'Porzucone';
$string['report_state_finished'] = 'Zakończone';
$string['report_state_inprogress'] = 'W toku';
$string['report_transcript'] = 'Transkrypcja';
$string['report_tries'] = 'Próby odpowiedzi';
$string['report_user'] = 'Uczestnik';
$string['report_view'] = 'Pokaż';
$string['reports'] = 'Raporty';
$string['resetattempts'] = 'Usuń wszystkie podejścia i odpowiedzi uczestników';
$string['solutionavailability'] = 'Transkrypcja z rozwiązaniami dla uczestników';
$string['solutionavailability_aftersubmission'] = 'Po zakończeniu podejścia';
$string['solutionavailability_always'] = 'W dowolnej chwili';
$string['solutionavailability_help'] = 'Kiedy uczestnicy mogą pobrać pełną transkrypcję z widocznym rozwiązaniem każdej luki.

* Nigdy — pobrać ją mogą tylko prowadzący.
* Po zakończeniu podejścia — uczestnik może ją pobrać, gdy zakończy podejście w tej aktywności.
* W dowolnej chwili — uczestnik może ją pobrać także przed udzieleniem odpowiedzi.

Uprawniona kadra dydaktyczna może ją pobrać zawsze, niezależnie od tego ustawienia.';
$string['solutionavailability_never'] = 'Nigdy';
$string['subplugintype_elangscript'] = 'Obsługa pisma';
$string['subplugintype_elangscript_plural'] = 'Obsługa pism';
$string['subtitleposition'] = 'Wyświetlanie napisów';
$string['subtitleposition_below'] = 'Pod medium';
$string['subtitleposition_help'] = 'Gdzie wyświetlane są interaktywne napisy.

* Pod medium — cała transkrypcja znajduje się pod medium, we własnym obszarze przewijania, i podąża za odtwarzaniem.
* Na wideo, u dołu lub u góry — na medium rysowany jest tylko ten napis, który jest właśnie odtwarzany.

Medium zawierające wyłącznie dźwięk nie ma obrazu, na którym można by rysować, więc zawsze korzysta z wyświetlania pod medium. Samo ustawienie zostaje zachowane i obowiązuje ponownie, gdy tylko aktywność użyje wideo.';
$string['subtitleposition_overlaybottom'] = 'Na wideo — u dołu';
$string['subtitleposition_overlaytop'] = 'Na wideo — u góry';
$string['task_migratev1activities'] = 'Przenieś aktywności wersji 1';
$string['transcriptheading'] = 'Transkrypcja dla uczestników';
$string['validate_cueafterend'] = '{$a->where}: kończy się w {$a->endtime} ms, czyli po medium ({$a->duration} ms). Odtwarzanie nigdy tam nie dotrze.';
$string['validate_cueendbeforestart'] = '{$a}: koniec nie następuje po początku.';
$string['validate_cuewhere'] = 'Segment {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'Rozwiązanie dla {$a} jest puste.';
$string['validate_hintlevels'] = 'Poziomy podpowiedzi dla {$a} nie tworzą ciągłej sekwencji zaczynającej się od 1.';
$string['validate_negativetime'] = '{$a}: czas początku wypada przed początkiem nagrania.';
$string['validate_nocues'] = 'Wersja nie ma segmentów.';
$string['validate_nogaps'] = 'Wersja nie ma luk do uzupełnienia.';
$string['validate_nonpositivelength'] = 'Długość w znakach dla {$a} musi być dodatnia.';
$string['validate_rangeoutside'] = 'Zakres znaków dla {$a} wykracza poza jego transkrypcję.';
$string['validate_rangeoverlap'] = 'Zakres znaków dla {$a} nachodzi na inną lukę.';
$string['validate_unknownalgorithm'] = 'Algorytm oceniania „{$a->algorithm}” dla {$a->where} nie jest rozpoznawany.';
$string['validate_where'] = 'luka {$a->gapkey} w segmencie {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Luka {$a->gapkey}: algorytm oceniania to „{$a->actual}”, oczekiwano „{$a->expected}”.';
$string['verify_attemptcount'] = 'Liczba przeniesionych podejść to {$a->actual}, oczekiwano odrębnych uczestników wersji 1.x: {$a->expected}.';
$string['verify_jarothreshold'] = 'Próg porównywania odpowiedzi to {$a->actual}, oczekiwano {$a->expected}.';
$string['verify_missingattempt'] = 'Użytkownik {$a}: oczekiwano przeniesionego podejścia, nie znaleziono żadnego.';
$string['verify_missingcue'] = 'Segment {$a}: brakuje przeniesionego segmentu.';
$string['verify_missinggap'] = 'Luka {$a}: brakuje przeniesionej luki.';
$string['verify_missinghint'] = 'Luka {$a}: wersja 1 dopuszczała tu pomoc, ale nie przeniesiono żadnej podpowiedzi.';
$string['verify_orphancue'] = 'Segment {$a}: nie znaleziono odpowiadającego segmentu z wersji 1.';
$string['verify_orphangap'] = 'Luka {$a}: nie znaleziono odpowiadającej luki z wersji 1.';
$string['verify_rangemismatch'] = 'Luka {$a}: zakres znaków nie zgadza się ze źródłem wersji 1.';
$string['verify_responsecount'] = 'Użytkownik {$a->userid}: liczba przeniesionych odpowiedzi to {$a->actual}, oczekiwano {$a->expected}.';
$string['verify_solutionmismatch'] = 'Luka {$a->gapkey}: rozwiązanie to „{$a->actual}”, oczekiwano „{$a->expected}”.';
$string['verify_transcriptmismatch'] = 'Segment {$a}: transkrypcja nie zgadza się ze źródłem wersji 1.';
$string['verify_unexpectedhint'] = 'Luka {$a}: wersja 1 nie dopuszczała tu pomocy, ale przeniesiono podpowiedź.';
