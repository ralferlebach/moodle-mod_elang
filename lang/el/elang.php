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
 * Greek strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * Terminology follows the existing AMOS translation of mod_elang 1.3.5 so that
 * learners who know the old version meet the same words: Υπότιτλοι for
 * subtitles, κενά for gaps, Βίντεο for video, Άσκηση for exercise. The string
 * ids themselves are new — 2.0 rebuilt the interface — so nothing could be
 * carried over automatically.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Επιτρεπόμενες γλώσσες περιεχομένου';
$string['allowedlanguages_desc'] = 'Οι γλώσσες περιεχομένου που προσφέρονται κατά τη δημιουργία ή επεξεργασία μιας βιντεοϋπαγόρευσης. Μην επιλέξετε καμία για να προσφέρεται ολόκληρη η λίστα. Μια δραστηριότητα διατηρεί την αποθηκευμένη γλώσσα της ακόμη κι αν την αφαιρέσετε αργότερα από εδώ.';
$string['allowtranscriptdownload'] = 'Λήψη απομαγνητοφώνησης από τους μαθητές';
$string['allowtranscriptdownload_help'] = 'Όταν είναι ενεργό, οι μαθητές μπορούν να κατεβάσουν το φύλλο εργασίας της απομαγνητοφώνησης, με κρυμμένο κάθε κενό, ως αρχείο PDF, Word, OpenDocument ή κειμένου.

Είναι ανενεργό από προεπιλογή. Οι διδάσκοντες μπορούν πάντα να κατεβάσουν την απομαγνητοφώνηση, ανεξάρτητα από αυτή τη ρύθμιση.';
$string['allowtranscriptdownload_label'] = 'Οι μαθητές μπορούν να κατεβάσουν το φύλλο εργασίας';
$string['completiondetail_completionfinishattempt'] = 'Ολοκλήρωση προσπάθειας';
$string['completionfinishattempt'] = 'Ο μαθητής πρέπει να ολοκληρώσει μια προσπάθεια';
$string['cuepausemode'] = 'Παύση στα όρια των υπότιτλων';
$string['cuepausemode_auto'] = 'Αυτόματα';
$string['cuepausemode_help'] = 'Αν το μέσο σταματά στο τέλος ενός υπότιτλου.

* Αυτόματα — η αναπαραγωγή συνεχίζεται και σταματά στο τέλος ενός υπότιτλου μόνο όσο δουλεύεται αυτός ο υπότιτλος, δηλαδή μετά από κλικ σε αυτόν ή σε ένα από τα κενά του, ή όταν η εστίαση του πληκτρολογίου βρίσκεται σε ένα από αυτά.
* Παύση σε κάθε αναπάντητο υπότιτλο — η αναπαραγωγή σταματά στο τέλος κάθε υπότιτλου που έχει ακόμη άδειο κενό και περιμένει να συνεχιστεί.
* Ποτέ — η αναπαραγωγή συνεχίζεται ως το τέλος του μέσου.

Καμία από τις δύο πρώτες δεν σταματά σε υπότιτλο του οποίου όλα τα κενά είναι συμπληρωμένα: αυτό είναι τελειωμένη δουλειά, και η παύση εκεί θα ζητούσε ένα πάτημα πλήκτρου χωρίς νόημα. Αυτό σημαίνει επίσης ότι το δεύτερο πέρασμα μιας άσκησης σταματά μόνο εκεί όπου λείπει ακόμη κάτι.';
$string['cuepausemode_nostop'] = 'Ποτέ';
$string['cuepausemode_stop'] = 'Παύση σε κάθε αναπάντητο υπότιτλο';
$string['editcontent'] = 'Επεξεργασία περιεχομένου';
$string['editor_addcue'] = 'Προσθήκη τμήματος';
$string['editor_addgap'] = 'Δημιουργία κενού από την επιλογή';
$string['editor_addhint'] = 'Προσθήκη υπόδειξης';
$string['editor_addvariant'] = 'Προσθήκη παραλλαγής';
$string['editor_advanced'] = 'Προχωρημένες ρυθμίσεις';
$string['editor_algoexact'] = 'Ακριβής αντιστοίχιση';
$string['editor_algorithm'] = 'Αντιστοίχιση';
$string['editor_algowordrecognized'] = 'Αποδοχή παρόμοιων απαντήσεων';
$string['editor_answers'] = 'Αποδεκτές παραλλαγές';
$string['editor_autosaved'] = 'Όλες οι αλλαγές αποθηκεύτηκαν.';
$string['editor_autosaveerror'] = 'Η αυτόματη αποθήκευση απέτυχε — χρησιμοποιήστε «Αποθήκευση».';
$string['editor_captureend'] = 'Ορισμός λήξης από την αναπαραγωγή';
$string['editor_capturestart'] = 'Ορισμός έναρξης από την αναπαραγωγή';
$string['editor_cueactions'] = 'Ενέργειες τμήματος';
$string['editor_cuecount'] = 'Τμήματα: {$a}';
$string['editor_currentmedia'] = 'Τρέχον μέσο:';
$string['editor_deletecue'] = 'Διαγραφή τμήματος';
$string['editor_deletegap'] = 'Διαγραφή κενού';
$string['editor_emptytranscript'] = '(δεν υπάρχει κείμενο ακόμη)';
$string['editor_endtime'] = 'Λήξη';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Κενά: {$a}';
$string['editor_gaprange'] = 'Θέση κενού (χαρακτήρες)';
$string['editor_gotomedia'] = 'Μετάβαση στα Μέσα';
$string['editor_heading'] = 'Επεξεργαστής περιεχομένου άσκησης';
$string['editor_hints'] = 'Υποδείξεις';
$string['editor_hinttext'] = 'Κείμενο υπόδειξης';
$string['editor_hinttype'] = 'Τύπος';
$string['editor_hinttype_firstletter'] = 'Πρώτο γράμμα';
$string['editor_hinttype_partial'] = 'Μερική';
$string['editor_hinttype_solution'] = 'Λύση';
$string['editor_hinttype_text'] = 'Ελεύθερο κείμενο';
$string['editor_hinttype_translation'] = 'Μετάφραση';
$string['editor_hinttype_wordlength'] = 'Μήκος λέξης';
$string['editor_import'] = 'Εισαγωγή υπότιτλων';
$string['editor_importappend'] = 'Προσθήκη στα υπάρχοντα τμήματα';
$string['editor_importapply'] = 'Εισαγωγή';
$string['editor_importcancel'] = 'Ακύρωση';
$string['editor_importcheck'] = 'Έλεγχος περιεχομένου';
$string['editor_importchecking'] = 'Γίνεται έλεγχος…';
$string['editor_importcuecount'] = 'Τμήματα που βρέθηκαν';
$string['editor_importduration'] = 'Διάρκεια';
$string['editor_importedcues'] = 'Τμήματα που εισήχθησαν: {$a}';
$string['editor_importfilehint'] = 'Επιλέξτε αρχείο WebVTT (.vtt) ή SubRip (.srt) με υπότιτλους.';
$string['editor_importformat'] = 'Μορφότυπος';
$string['editor_importfromfile'] = 'Μεταφόρτωση αρχείου';
$string['editor_importfromtext'] = 'Επικόλληση κειμένου';
$string['editor_importgapcount'] = 'Κενά που βρέθηκαν';
$string['editor_importhint'] = 'Επικολλήστε περιεχόμενο WebVTT ή SubRip και εισαγάγετέ το ως τμήματα.';
$string['editor_importparseerror'] = 'Το περιεχόμενο δεν διαβάστηκε ως WebVTT ή SubRip.';
$string['editor_importpastedtext'] = 'Επικολλημένο κείμενο';
$string['editor_importreaderror'] = 'Το αρχείο δεν μπόρεσε να διαβαστεί.';
$string['editor_importready'] = 'Έτοιμο για εισαγωγή';
$string['editor_importreplace'] = 'Αντικατάσταση όλων των τμημάτων';
$string['editor_importreplacedcues'] = 'Τα τμήματα αντικαταστάθηκαν· νέα εισαγωγή: {$a}';
$string['editor_importsource'] = 'Πηγή';
$string['editor_importsummary'] = 'Τι βρέθηκε';
$string['editor_importtoolarge'] = 'Το αρχείο είναι {$a->size}· η εισαγωγή δέχεται το πολύ {$a->max}.';
$string['editor_importwrongtype'] = 'Επιλέξτε αρχείο υπότιτλων ({$a}).';
$string['editor_insertafter'] = 'Εισαγωγή τμήματος μετά';
$string['editor_insertbefore'] = 'Εισαγωγή τμήματος πριν';
$string['editor_invalidtime'] = 'Δώστε χρόνο ως mm:ss.SSS, για παράδειγμα 01:05.400.';
$string['editor_linkurl'] = 'Σύνδεσμος αναφοράς';
$string['editor_linkurl_help'] = 'Εμφανίζεται δίπλα στο κενό ως σημείο αναζήτησης της λέξης. Αφήστε το κενό για κανέναν σύνδεσμο.';
$string['editor_loaderror'] = 'Ο επεξεργαστής δεν φορτώθηκε. Επαναφορτώστε τη σελίδα.';
$string['editor_loading'] = 'Φόρτωση επεξεργαστή…';
$string['editor_maxlength'] = 'Μέγιστο μήκος';
$string['editor_maxlength_help'] = 'Περιορίζει πόσα μπορεί να πληκτρολογήσει ο μαθητής. Το 0 σημαίνει χωρίς όριο.';
$string['editor_media'] = 'Μέσα';
$string['editor_mediafile'] = 'Μεταφορτωμένο αρχείο';
$string['editor_mediakind'] = 'Τύπος μέσου';
$string['editor_medianone'] = 'Κανένα';
$string['editor_mediaprovider'] = 'Πάροχος';
$string['editor_mediaproviderref'] = 'Αναφορά παρόχου';
$string['editor_mediaproviderrefhint'] = 'Αναγνωριστικό ή σύνδεσμος βίντεο σε οποιαδήποτε συνήθη μορφή (π.χ. youtu.be/…).';
$string['editor_mediasaved'] = 'Το μέσο αποθηκεύτηκε.';
$string['editor_mediaurl'] = 'Άμεσος σύνδεσμος';
$string['editor_nocues'] = 'Δεν υπάρχουν τμήματα ακόμη. Προσθέστε ένα ή εισαγάγετε υπότιτλους.';
$string['editor_nocueselected'] = 'Επιλέξτε ένα τμήμα από τη λίστα για επεξεργασία.';
$string['editor_nocuesmatch'] = 'Κανένα τμήμα δεν ταιριάζει σε αυτή την αναζήτηση.';
$string['editor_nogaps'] = 'Χωρίς κενά';
$string['editor_nomedia'] = 'κανένα';
$string['editor_nomedianotice'] = 'Προσθέστε πρώτα το αρχείο βίντεο ή ήχου στην καρτέλα Μέσα. Οι υπότιτλοι χρονίζονται πάνω στο μέσο, οπότε ο επεξεργαστής το χρειάζεται πριν δουλέψετε με τμήματα και κενά.';
$string['editor_novideotrack'] = 'Αυτό το πρόγραμμα περιήγησης δεν μπορεί να αποκωδικοποιήσει το κανάλι βίντεο αυτού του μέσου (παίζει μόνο ο ήχος)· οι μαθητές θα έβλεπαν μαύρη εικόνα. Επανακωδικοποιήστε το αρχείο ως H.264/MP4 (π.χ. με ffmpeg ή HandBrake) και ανεβάστε το ξανά.';
$string['editor_onboardinggaps'] = 'Επιλέξτε μια λέξη σε ένα τμήμα και ορίστε την ως κενό.';
$string['editor_onboardingimport'] = 'Εισαγάγετε υπότιτλους WebVTT/SubRip ή προσθέστε τμήματα χειροκίνητα.';
$string['editor_onboardingintro'] = 'Δημιουργήστε μια άσκηση σε τρία βήματα:';
$string['editor_onboardingmedia'] = 'Επιλέξτε μέσο (μεταφόρτωση, σύνδεσμο ή πάροχο).';
$string['editor_onboardingtitle'] = 'Ξεκινήστε την άσκησή σας';
$string['editor_onlywarnings'] = 'Μόνο τμήματα με προειδοποιήσεις';
$string['editor_parsegaps'] = 'Αναγνώριση σημαδιών κενού: το [λέξη] δημιουργεί κενό με υποδείξεις, το {λέξη} χωρίς.';
$string['editor_penalty'] = 'Ποινή';
$string['editor_poster'] = 'Εικόνα αφίσας';
$string['editor_preview'] = 'Προεπισκόπηση μαθητή';
$string['editor_publish'] = 'Δημοσίευση';
$string['editor_published'] = 'Η έκδοση δημοσιεύτηκε.';
$string['editor_removehint'] = 'Αφαίρεση υπόδειξης';
$string['editor_removevariant'] = 'Αφαίρεση';
$string['editor_ruleapplied'] = 'Δημιουργήθηκαν %count% κενά από τον κανόνα.';
$string['editor_ruleapply'] = 'Εφαρμογή %count% κενών';
$string['editor_ruleerror'] = 'Τα κενά δεν μπόρεσαν να δημιουργηθούν.';
$string['editor_ruleeverynth'] = 'Κάθε ν-οστή λέξη';
$string['editor_rulefound'] = 'Ο κανόνας βρήκε %count% κενά.';
$string['editor_rulegenerate'] = 'Δημιουργία κενών';
$string['editor_ruleinterval'] = 'Βήμα (ν)';
$string['editor_ruletype'] = 'Κανόνας κενών';
$string['editor_rulewordlist'] = 'Λέξεις προς απόκρυψη';
$string['editor_rulewords'] = 'Λίστα λέξεων';
$string['editor_save'] = 'Αποθήκευση προσχεδίου';
$string['editor_saved'] = 'Το προσχέδιο αποθηκεύτηκε.';
$string['editor_saveerror'] = 'Το προσχέδιο δεν αποθηκεύτηκε.';
$string['editor_savemedia'] = 'Αποθήκευση μέσου';
$string['editor_saving'] = 'Αποθήκευση…';
$string['editor_searchcues'] = 'Αναζήτηση τμημάτων';
$string['editor_selecttext'] = 'Επιλέξτε πρώτα τη λέξη που θα αποκρυφτεί στο κείμενο.';
$string['editor_solution'] = 'Λύση';
$string['editor_starttime'] = 'Έναρξη';
$string['editor_transcript'] = 'Απομαγνητοφώνηση';
$string['editor_unsaved'] = 'Μη αποθηκευμένες αλλαγές';
$string['editor_uploadmedia'] = 'Μεταφόρτωση αρχείων μέσων';
$string['editor_variantisregex'] = 'Χρήση του {$a} ως κανονική έκφραση';
$string['editor_variantmatching'] = 'Πώς αντιστοιχίζονται οι αποδεκτές παραλλαγές';
$string['editor_warnemptysolution'] = 'Κενό χωρίς λύση';
$string['editor_warnnotranscript'] = 'Χωρίς κείμενο';
$string['editor_warntiming'] = 'Η λήξη δεν είναι μετά την έναρξη';
$string['editor_waveform'] = 'Κυματομορφή ήχου';
$string['elang:addinstance'] = 'Προσθήκη νέας βιντεοϋπαγόρευσης';
$string['elang:attempt'] = 'Εκτέλεση βιντεοϋπαγόρευσης';
$string['elang:deleteattempts'] = 'Διαγραφή προσπαθειών μαθητών';
$string['elang:exportreports'] = 'Εξαγωγή αναφορών με προσωπικά δεδομένα';
$string['elang:exportsolution'] = 'Εξαγωγή πλήρους κειμένου λύσης';
$string['elang:exporttranscript'] = 'Εξαγωγή φύλλου εργασίας ως εγγράφου';
$string['elang:manage'] = 'Δημιουργία και επεξεργασία περιεχομένου άσκησης';
$string['elang:useregex'] = 'Χρήση κανονικών εκφράσεων στις αποδεκτές απαντήσεις';
$string['elang:view'] = 'Προβολή βιντεοϋπαγόρευσης';
$string['elang:viewreports'] = 'Προβολή αναφορών μαθητών';
$string['error_attemptnotinprogress'] = 'Αυτή η προσπάθεια δεν βρίσκεται πλέον σε εξέλιξη.';
$string['error_couldnotobtainlock'] = 'Δεν ήταν δυνατή η δέσμευση κλειδώματος για αυτή την ενέργεια. Δοκιμάστε ξανά.';
$string['error_draftrevisionmismatch'] = 'Αυτό το προσχέδιο άλλαξε από τότε που το φορτώσατε. Επαναφορτώστε και δοκιμάστε ξανά.';
$string['error_duplicatecuekey'] = 'Δύο τμήματα μοιράζονται το κλειδί «{$a}»· κάθε τμήμα χρειάζεται μοναδικό κλειδί.';
$string['error_duplicategapkey'] = 'Δύο κενά στο ίδιο τμήμα μοιράζονται το κλειδί «{$a}»· κάθε κενό χρειάζεται μοναδικό κλειδί.';
$string['error_duplicatehintlevel'] = 'Ένα κενό έχει δύο υποδείξεις στο επίπεδο {$a}· κάθε επίπεδο πρέπει να είναι μοναδικό.';
$string['error_gapnotinattemptversion'] = 'Αυτό το κενό δεν ανήκει στην έκδοση της άσκησης αυτής της προσπάθειας.';
$string['error_importnocues'] = 'Δεν διαβάστηκαν υπότιτλοι από αυτό το περιεχόμενο. Ένα αρχείο WebVTT ή SubRip έχει μια γραμμή χρόνου όπως 00:00:01.000 --> 00:00:04.000 πάνω από κάθε υπότιτλο.';
$string['error_importnotutf8'] = 'Αυτό το αρχείο δεν είναι έγκυρο UTF-8. Πιθανότατα αποθηκεύτηκε σε παλαιότερη κωδικοποίηση — ανοίξτε το σε έναν επεξεργαστή κειμένου και αποθηκεύστε το ξανά ως UTF-8.';
$string['error_importtoolarge'] = 'Το αρχείο είναι {$a->size}· η εισαγωγή δέχεται το πολύ {$a->max}. Ένα αρχείο υπότιτλων για ηχογράφηση μαθήματος είναι πολύ μικρότερο, οπότε μάλλον δεν πρόκειται για τέτοιο.';
$string['error_importtoomanycues'] = 'Αυτό το αρχείο περιέχει {$a->count} υπότιτλους· η εισαγωγή δέχεται το πολύ {$a->max}.';
$string['error_invalidcuepausemode'] = 'Επιλέξτε μία από τις προσφερόμενες επιλογές για την παύση στα όρια των υπότιτλων.';
$string['error_invalidgradingalgorithm'] = 'Ο αλγόριθμος βαθμολόγησης «{$a}» δεν είναι ούτε exact ούτε wordrecognized.';
$string['error_invalidhinttype'] = 'Ο τύπος υπόδειξης «{$a}» δεν είναι ένας από τους επιτρεπόμενους.';
$string['error_invalidisregex'] = 'Ο δείκτης κανονικής έκφρασης μιας παραλλαγής πρέπει να είναι 0 ή 1.';
$string['error_invalidmediakind'] = 'Ο επιλεγμένος τύπος μέσου δεν είναι file, url ή provider.';
$string['error_invalidpenalty'] = 'Η ποινή υπόδειξης πρέπει να είναι μεταξύ 0 και 1.';
$string['error_invalidproviderref'] = 'Το «{$a}» δεν είναι αναγνωρίσιμο αναγνωριστικό ή σύνδεσμος βίντεο για αυτόν τον πάροχο.';
$string['error_invalidregexpattern'] = 'Το «{$a}» δεν είναι έγκυρη κανονική έκφραση.';
$string['error_invalidsolutionavailability'] = 'Επιλέξτε μία από τις προσφερόμενες επιλογές για το πότε οι μαθητές βλέπουν το κείμενο λύσης.';
$string['error_invalidsourceurl'] = 'Δώστε πλήρη διεύθυνση που ξεκινά με http:// ή https://, ή σύνδεσμο YouTube ή Vimeo.';
$string['error_invalidsubtitleposition'] = 'Επιλέξτε μία από τις προσφερόμενες επιλογές για το πού εμφανίζονται οι υπότιτλοι.';
$string['error_invalidv1cuejson'] = 'Αυτό το τμήμα της έκδοσης 1 δεν μπόρεσε να αναλυθεί.';
$string['error_negativegapoffset'] = 'Η θέση και το μήκος ενός κενού δεν επιτρέπεται να είναι αρνητικά.';
$string['error_noaccesstoattempt'] = 'Δεν έχετε πρόσβαση σε αυτή την προσπάθεια.';
$string['error_nomorehints'] = 'Δεν υπάρχουν άλλες υποδείξεις για αυτό το κενό.';
$string['error_nopublishedversion'] = 'Αυτή η άσκηση δεν έχει ακόμη δημοσιευμένο περιεχόμενο.';
$string['error_responsetoolong'] = 'Η απάντησή σας είναι πολύ μεγάλη. Το μέγιστο για αυτό το κενό είναι {$a} χαρακτήρες.';
$string['error_solutionnotavailable'] = 'Το κείμενο λύσης δεν είναι διαθέσιμο σε εσάς για αυτή τη δραστηριότητα.';
$string['error_staleattemptstate'] = 'Η προβολή αυτής της προσπάθειας δεν είναι ενημερωμένη. Επαναφορτώστε την τρέχουσα κατάσταση και δοκιμάστε ξανά.';
$string['error_transcriptnotavailable'] = 'Δεν υπάρχει διαθέσιμη απομαγνητοφώνηση για λήψη σε αυτή τη δραστηριότητα.';
$string['error_unknowngaprule'] = 'Άγνωστος τύπος κανόνα κενών «{$a}».';
$string['error_unknownmediaprovider'] = 'Το «{$a}» δεν είναι ένας από τους υποστηριζόμενους παρόχους μέσων.';
$string['error_versionnotadraft'] = 'Μόνο μια έκδοση σε μορφή προσχεδίου μπορεί να επεξεργαστεί.';
$string['error_versionnotfound'] = 'Αυτή η έκδοση της άσκησης δεν υπάρχει πλέον.';
$string['error_versionnotpublishable'] = 'Αυτή η έκδοση δεν μπορεί να δημοσιευτεί: {$a}';
$string['export_audienceaftersubmission'] = 'Οι μαθητές μπορούν να το κατεβάσουν αφού ολοκληρώσουν μια προσπάθεια';
$string['export_audiencealways'] = 'Οι μαθητές μπορούν να το κατεβάσουν οποτεδήποτε';
$string['export_audiencestaff'] = 'Μόνο για διδακτικό προσωπικό με δικαίωμα — δεν προσφέρεται στους μαθητές';
$string['export_docx'] = 'Λήψη ως Word (DOCX)';
$string['export_downloadpdf'] = 'Λήψη PDF';
$string['export_heading'] = 'Εξαγωγή απομαγνητοφώνησης';
$string['export_intro'] = 'Κατεβάστε την απομαγνητοφώνηση αυτής της άσκησης σε διάφορες μορφές.';
$string['export_moreformats'] = 'Περισσότερες μορφές';
$string['export_nocontent'] = 'Δεν υπάρχει ακόμη δημοσιευμένη απομαγνητοφώνηση για εξαγωγή.';
$string['export_odt'] = 'Λήψη ως OpenDocument (ODT)';
$string['export_pdf'] = 'Λήψη ως PDF';
$string['export_solution'] = 'Κείμενο λύσης';
$string['export_solutionhint'] = 'Το πλήρες κείμενο με εμφανή τη λύση κάθε κενού.';
$string['export_text'] = 'Λήψη ως κείμενο';
$string['export_versionnote'] = 'Οι εξαγωγές βασίζονται στην τρέχουσα δημοσιευμένη έκδοση αυτής της άσκησης.';
$string['export_worksheet'] = 'Φύλλο εργασίας (με κρυμμένα κενά)';
$string['export_worksheethint'] = 'Το κείμενο με κρυμμένο κάθε κενό. Έτοιμο για διανομή ως υλικό μαθητών.';
$string['exporttranscript'] = 'Εξαγωγή απομαγνητοφώνησης';
$string['filearea_media'] = 'Μέσα';
$string['filearea_poster'] = 'Εικόνα αφίσας';
$string['gradingheading'] = 'Βαθμολόγηση απαντήσεων';
$string['import_badtiming'] = 'Δεν διαβάστηκε η γραμμή χρόνου: {$a}';
$string['import_emptytranscript'] = 'Παραλείφθηκε τμήμα χωρίς κείμενο.';
$string['import_warnlinetoolong'] = 'Το μπλοκ {$a->block} παραλείφθηκε: περιέχει γραμμή μεγαλύτερη από {$a->max} χαρακτήρες, που δεν είναι γραμμή υπότιτλου.';
$string['jarothreshold'] = 'Κατώφλι ομοιότητας';
$string['jarothreshold_help'] = 'Για τα κενά που είναι ρυθμισμένα σε «Αποδοχή παρόμοιων απαντήσεων», αυτή είναι η ελάχιστη ομοιότητα Jaro ανάμεσα στην αναμενόμενη και στη δοσμένη απάντηση. Η τιμή 1 απαιτεί ακριβή αντιστοιχία μετά την κανονικοποίηση της γλώσσας· χαμηλότερες τιμές δέχονται ολοένα πιο διαφορετικές γραφές.';
$string['jarothresholdrange'] = 'Το κατώφλι πρέπει να είναι μεταξύ 0 και 1.';
$string['language'] = 'Γλώσσα περιεχομένου';
$string['language_help'] = 'Επιλέξτε τη γλώσσα του περιεχομένου της άσκησης. Καθορίζει πώς συγκρίνονται οι απαντήσεις, μεταξύ άλλων τα κεφαλαία και τη μεταγραφή. Επιλέξτε «Γενική (μη καθορισμένη)» αν δεν πρέπει να εφαρμοστεί ειδική ανά γλώσσα επεξεργασία. Οι νέες εκδόσεις περιεχομένου ξεκινούν από αυτή τη ρύθμιση.';
$string['language_none'] = 'Γενική (μη καθορισμένη)';
$string['media_cuenote'] = 'Οι υπάρχοντες υπότιτλοι και τα κενά διατηρούνται όταν αλλάζετε το μέσο. Οι χρονισμοί τους δεν προσαρμόζονται, οπότε ελέγξτε τους στον επεξεργαστή μετά.';
$string['media_current'] = 'Τρέχον μέσο';
$string['media_heading'] = 'Μέσα';
$string['media_intro'] = 'Επιλέξτε το βίντεο ή τον ήχο πάνω στον οποίο χτίζεται αυτή η άσκηση. Οι υπότιτλοι χρονίζονται πάνω του, οπότε αυτό έρχεται πρώτο.';
$string['media_none'] = 'Δεν έχει οριστεί ακόμη μέσο για αυτή την άσκηση.';
$string['media_othersource'] = 'Άλλη πηγή';
$string['media_providerhint'] = 'Αναγνωρισμένοι πάροχοι: {$a}. Οποιαδήποτε άλλη διεύθυνση χρησιμοποιείται ως άμεσος σύνδεσμος μέσου.';
$string['media_sourceurl'] = 'Διεύθυνση πηγής';
$string['media_sourceurl_help'] = 'Επικολλήστε τη διεύθυνση ενός βίντεο αντί να μεταφορτώσετε αρχείο — σύνδεσμο YouTube ή Vimeo, ή άμεση διεύθυνση ενός αρχείου μέσου.

Μια διεύθυνση που δίνεται εδώ αντικαθιστά το μεταφορτωμένο αρχείο. Αφήστε την κενή για να χρησιμοποιηθεί η μεταφόρτωση παραπάνω.

Ένα βίντεο παρόχου αναπαράγεται στο δικό του πλαίσιο, το οποίο δεν αναφέρει τον χρόνο αναπαραγωγής. Μια τέτοια άσκηση δείχνει πάντα τους υπότιτλους κάτω από το μέσο και δεν σταματά ποτέ στα όρια των υπότιτλων.

**Πού πηγαίνουν τα δεδομένα.** Ένα πλαίσιο YouTube ή Vimeo συνδέει το πρόγραμμα περιήγησης κάθε μαθητή με την εταιρεία αυτή, η οποία λαμβάνει τη διεύθυνση IP και τα στοιχεία της συσκευής του. Από προεπιλογή η άσκηση ρωτά πριν το κάνει. Αν το ίδρυμά σας διαθέτει δικό του διακομιστή μέσων — Opencast, Panopto, Kaltura ή παρόμοιο — επικολλήστε αντ\' αυτού την άμεση διεύθυνση του αρχείου από εκεί: αντιμετωπίζεται ως απλός σύνδεσμος μέσου, διατηρεί τη θέση υπότιτλων και τη ρύθμιση παύσης που επιλέξατε, και δεν εμπλέκεται τρίτο μέρος.';
$string['migratev1_approvalheading'] = 'Μεταφέρθηκαν, αναμένουν έλεγχο';
$string['migratev1_approvebutton'] = 'Έγκριση αυτής της μεταφοράς';
$string['migratev1_approved'] = 'Η βιντεοϋπαγόρευση {$a} σημειώθηκε ως εγκεκριμένη.';
$string['migratev1_colactivity'] = 'Δραστηριότητα';
$string['migratev1_colalgorithm'] = 'Αλγόριθμος βαθμολόγησης';
$string['migratev1_colcues'] = 'Τμήματα';
$string['migratev1_colgaps'] = 'Κενά';
$string['migratev1_colissues'] = 'Ζητήματα';
$string['migratev1_collearners'] = 'Μαθητές';
$string['migratev1_confirmdecommission'] = 'Αυτό διαγράφει ΟΡΙΣΤΙΚΑ τους παλαιούς πίνακες της έκδοσης 1 και το elang.options. Δεν υπάρχει αναίρεση. Συνέχεια;';
$string['migratev1_confirmmigrate'] = 'Αυτό θα προγραμματίσει εργασία παρασκηνίου που γράφει νέα δεδομένα έκδοσης 2 για κάθε δραστηριότητα παραπάνω. Οι πίνακες της έκδοσης 1 και το elang.options παραμένουν ανέπαφα. Συνέχεια;';
$string['migratev1_decommissionblocked'] = 'Η διαγραφή εξακολουθεί να εμποδίζεται· δείτε τη λίστα παρακάτω.';
$string['migratev1_decommissionblockedintro'] = 'Η διαγραφή εμποδίζεται μέχρι:';
$string['migratev1_decommissionbutton'] = 'Διαγραφή παλαιών δεδομένων έκδοσης 1';
$string['migratev1_decommissioned'] = 'Τα παλαιά δεδομένα της έκδοσης 1 διαγράφηκαν.';
$string['migratev1_decommissionheading'] = 'Απόσυρση δεδομένων έκδοσης 1';
$string['migratev1_decommissionready'] = 'Κάθε δραστηριότητα της έκδοσης 1 έχει μεταφερθεί και εγκριθεί. Οι παλαιοί πίνακες και το elang.options μπορούν τώρα να διαγραφούν. Αυτό είναι μη αναστρέψιμο.';
$string['migratev1_heading'] = 'Μεταφορά δραστηριοτήτων έκδοσης 1';
$string['migratev1_migratebutton'] = 'Μεταφορά αυτών των δραστηριοτήτων';
$string['migratev1_noissues'] = 'Κανένα';
$string['migratev1_nonepending'] = 'Δεν υπάρχουν δραστηριότητες έκδοσης 1 που να αναμένουν μεταφορά.';
$string['migratev1_nonependingapproval'] = 'Δεν υπάρχουν μεταφερμένες δραστηριότητες που να αναμένουν έλεγχο.';
$string['migratev1_notablespresent'] = 'Δεν βρέθηκαν παλαιοί πίνακες της έκδοσης 1 σε αυτόν τον ιστότοπο. Δεν υπάρχει τίποτα προς μεταφορά.';
$string['migratev1_parseerrorcount'] = 'Τμήματα που δεν αναλύθηκαν: {$a}';
$string['migratev1_pendingheading'] = 'Δεν έχουν μεταφερθεί ακόμη';
$string['migratev1_queued'] = 'Η εργασία μεταφοράς προγραμματίστηκε. Θα εκτελεστεί στο επόμενο πέρασμα του cron ή αμέσως μέσω admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Επαληθεύτηκε: τα μεταφερμένα δεδομένα ταιριάζουν με την πηγή της έκδοσης 1 χωρίς αποκλίσεις.';
$string['migratev1_verifieddiscrepancies'] = 'Η επαλήθευση βρήκε αποκλίσεις σε σχέση με την πηγή της έκδοσης 1: {$a}';
$string['migratev1_verifyfailed'] = 'Δεν ήταν δυνατή η επαλήθευση αυτής της δραστηριότητας: {$a}';
$string['modulename'] = 'Βιντεοϋπαγόρευση';
$string['modulename_help'] = 'Η δραστηριότητα βιντεοϋπαγόρευσης επιτρέπει στους μαθητές να συμπληρώνουν κενά σε χρονισμένους υπότιτλους ενώ παρακολουθούν ή ακούν ένα βίντεο.

Οι διδάσκοντες εισάγουν ένα αρχείο υπότιτλων WebVTT ή SubRip, ορίζουν λέξεις ή φράσεις ως κενά και ρυθμίζουν πόσο αυστηρά συγκρίνονται οι απαντήσεις. Οι μαθητές δουλεύουν το κείμενο τμήμα προς τμήμα, ζητούν βαθμολογούμενες υποδείξεις και λαμβάνουν άμεση ανατροφοδότηση.';
$string['modulenameplural'] = 'Βιντεοϋπαγορεύσεις';
$string['nav_exportshort'] = 'Εξαγωγή';
$string['nav_media'] = 'Μέσα';
$string['nav_reports'] = 'Προσπάθειες';
$string['nav_subtitles'] = 'Υπότιτλοι και κενά';
$string['noinstances'] = 'Δεν υπάρχουν βιντεοϋπαγορεύσεις σε αυτό το μάθημα.';
$string['overview_attempts'] = 'Προσπάθειες';
$string['playbackheading'] = 'Αναπαραγωγή και υπότιτλοι';
$string['playbackoverlayhint'] = 'Ένας υπότιτλος πάνω στην εικόνα δείχνει μόνο τον υπότιτλο που παίζει εκείνη τη στιγμή, οπότε η αναπαραγωγή σταματά πάντα στο τέλος ενός υπότιτλου που έχει ακόμη κενά προς συμπλήρωση. Δεν υπάρχει επιλογή εδώ.';
$string['playbackproviderhint'] = 'Ένα βίντεο YouTube ή Vimeo αναπαράγεται από τον πάροχο στο δικό του πλαίσιο, το οποίο δεν αναφέρει τον χρόνο αναπαραγωγής. Μια τέτοια άσκηση δείχνει πάντα τους υπότιτλους κάτω από το μέσο και δεν σταματά ποτέ στα όρια των υπότιτλων, ό,τι κι αν επιλεγεί παραπάνω. Τα μεταφορτωμένα αρχεία και οι άμεσοι σύνδεσμοι μέσων τηρούν και τις δύο ρυθμίσεις.';
$string['player_check'] = 'Έλεγχος απάντησης';
$string['player_consentaccept'] = 'Φόρτωση του βίντεο από {$a}';
$string['player_consentdetail'] = 'Η αναπαραγωγή συνδέει το πρόγραμμα περιήγησής σας με το {$a}. Το {$a} λαμβάνει τη διεύθυνση IP σας και πληροφορίες για τη συσκευή σας, και μπορεί να διαβάσει cookies που έχει ήδη θέσει. Τίποτα δεν αποστέλλεται πριν επιλέξετε να φορτώσετε το βίντεο.';
$string['player_consentheading'] = 'Αυτό το βίντεο παρέχεται από {$a}';
$string['player_finish'] = 'Ολοκλήρωση προσπάθειας';
$string['player_finished'] = 'Η προσπάθεια ολοκληρώθηκε. Βαθμός: %score%%';
$string['player_finishincomplete'] = 'Κενά που παραμένουν άδεια: {$a}. Ολοκλήρωση της προσπάθειας ούτως ή άλλως;';
$string['player_gaplabel'] = 'Κενό %gap%';
$string['player_gaplink'] = 'Άνοιγμα συνδέσμου';
$string['player_hint'] = 'Εμφάνιση υπόδειξης';
$string['player_loaderror'] = 'Η άσκηση δεν φορτώθηκε. Επαναφορτώστε τη σελίδα.';
$string['player_loading'] = 'Φόρτωση άσκησης…';
$string['player_nocontent'] = 'Δεν έχει δημοσιευτεί ακόμη περιεχόμενο άσκησης. Δοκιμάστε αργότερα.';
$string['player_novideotrack'] = 'Το πρόγραμμα περιήγησής σας δεν μπορεί να εμφανίσει το κανάλι βίντεο αυτού του μέσου· ο ήχος θα συνεχίσει να παίζει. Ενημερώστε τον διδάσκοντά σας.';
$string['player_outdatedattempt'] = 'Αυτή η άσκηση ενημερώθηκε αφότου ξεκινήσατε αυτή την προσπάθεια. Συνεχίζετε στο προηγούμενο περιεχόμενο· ολοκληρώστε αυτή την προσπάθεια για να δουλέψετε με την ενημερωμένη άσκηση την επόμενη φορά.';
$string['player_progress'] = 'Απαντήθηκαν {$a->done} από {$a->total} κενά';
$string['player_ready'] = 'Η άσκηση είναι έτοιμη.';
$string['player_scorelabel'] = 'Βαθμός: %score%%';
$string['player_stateaccepted'] = 'Αποδεκτή';
$string['player_statecorrect'] = 'Σωστή';
$string['player_statehinted'] = 'Χρησιμοποιήθηκε υπόδειξη';
$string['player_stateincorrect'] = 'Λανθασμένη';
$string['player_submitfailed'] = 'Η απάντησή σας δεν αποθηκεύτηκε. Δοκιμάστε ξανά.';
$string['player_transcriptheading'] = 'Απομαγνητοφώνηση';
$string['pluginadministration'] = 'Διαχείριση βιντεοϋπαγόρευσης';
$string['pluginname'] = 'Βιντεοϋπαγόρευση';
$string['privacy_metadata_elang'] = 'Για κάθε δραστηριότητα, η καταγραφή του ποιος ενέκρινε τη μονόδρομη μεταφορά του περιεχομένου της από την 1.x.';
$string['privacy_metadata_elang_attempt'] = 'Για κάθε προσπάθεια σε μια άσκηση, η δραστηριότητα αποθηκεύει ποιος την έκανε, πότε, πόσο προχώρησε και πώς βαθμολογήθηκε.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Πόσα κενά απάντησε ο μαθητής σε αυτή την προσπάθεια.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Ο αύξων αριθμός αυτής της προσπάθειας για τον χρήστη και τη δραστηριότητα.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Πόσα κενά έγιναν δεκτά ως σωστά σε αυτή την προσπάθεια.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Πόσα κενά απαντήθηκαν με απόλυτη αντιστοίχιση χαρακτήρων σε αυτή την προσπάθεια.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Για πόσα κενά ζήτησε ο μαθητής υπόδειξη σε αυτή την προσπάθεια.';
$string['privacy_metadata_elang_attempt_score'] = 'Ο βαθμός που επιτεύχθηκε σε αυτή την προσπάθεια.';
$string['privacy_metadata_elang_attempt_state'] = 'Αν η προσπάθεια είναι σε εξέλιξη, ολοκληρωμένη ή εγκαταλελειμμένη.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'Η ώρα ολοκλήρωσης της προσπάθειας.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'Η ώρα της τελευταίας ενημέρωσης της προσπάθειας.';
$string['privacy_metadata_elang_attempt_timestart'] = 'Η ώρα έναρξης της προσπάθειας.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Ο συνολικός αριθμός κενών στην έκδοση της άσκησης αυτής της προσπάθειας.';
$string['privacy_metadata_elang_attempt_userid'] = 'Το αναγνωριστικό του χρήστη που έκανε την προσπάθεια.';
$string['privacy_metadata_elang_attempt_versionid'] = 'Η έκδοση της άσκησης στην οποία έγινε αυτή η προσπάθεια.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'Ο χρήστης που ενέκρινε τη μεταφορά αυτής της δραστηριότητας από το mod_elang 1.x. Αποθηκεύεται ώστε η έγκριση να παραμένει ελέγξιμη.';
$string['privacy_metadata_elang_response'] = 'Για κάθε κενό που απαντά ένας μαθητής μέσα σε μια προσπάθεια, η δραστηριότητα αποθηκεύει το κείμενο της απάντησης και τον τρόπο αξιολόγησής της.';
$string['privacy_metadata_elang_response_accepted'] = 'Αν η απάντηση έγινε δεκτή ως σωστή για αυτό το κενό.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Το υψηλότερο επίπεδο υπόδειξης που αποκαλύφθηκε στον μαθητή για αυτό το κενό.';
$string['privacy_metadata_elang_response_responsetext'] = 'Το κείμενο που πληκτρολόγησε ο μαθητής για αυτό το κενό.';
$string['privacy_metadata_elang_response_resultstate'] = 'Η κατηγοριοποίηση που έδωσε ο αξιολογητής σε αυτή την απάντηση (ακριβής, αναγνωρισμένη λέξη, λανθασμένη ή κενή).';
$string['privacy_metadata_elang_response_score'] = 'Οι μονάδες που συνεισέφερε αυτή η απάντηση, μετά από τυχόν ποινή υπόδειξης.';
$string['privacy_metadata_elang_response_timecreated'] = 'Η ώρα της πρώτης υποβολής αυτής της απάντησης.';
$string['privacy_metadata_elang_response_timemodified'] = 'Η ώρα της τελευταίας ενημέρωσης αυτής της απάντησης.';
$string['privacy_metadata_elang_response_tries'] = 'Πόσες φορές υπέβαλε ο μαθητής απάντηση σε αυτό το κενό.';
$string['privacy_metadata_elang_version'] = 'Για κάθε έκδοση περιεχομένου, η δραστηριότητα αποθηκεύει ποιος χρήστης την τροποποίησε τελευταίος.';
$string['privacy_metadata_elang_version_usermodified'] = 'Ο χρήστης που τροποποίησε τελευταίος αυτή την έκδοση περιεχομένου. Αποθηκεύεται για τον έλεγχο του ποιος επεξεργάστηκε το περιεχόμενο.';
$string['privacy_provider_externallink'] = 'Όταν μια άσκηση βασίζεται σε βίντεο YouTube ή Vimeo, το άνοιγμά της συνδέει το πρόγραμμα περιήγησης του μαθητή με αυτόν τον πάροχο. Το πρόσθετο δεν στέλνει τίποτα από μόνο του, αλλά η σύνδεση προκαλείται από τη δραστηριότητα. Το αν συμβαίνει καθόλου εξαρτάται από τη ρύθμιση του ιστότοπου για τη συγκατάθεση παρόχου και από τη συμφωνία του μαθητή.';
$string['privacy_provider_ipaddress'] = 'Η διεύθυνση IP από την οποία συνδέεται το πρόγραμμα περιήγησης του μαθητή.';
$string['privacy_provider_useragent'] = 'Τα στοιχεία προγράμματος περιήγησης και συσκευής που αποστέλλονται.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Ερώτηση πριν την ενσωμάτωση YouTube ή Vimeo';
$string['providerconsent_desc'] = 'Οι ασκήσεις που βασίζονται σε βίντεο YouTube ή Vimeo εμφανίζουν μια ειδοποίηση αντί του βίντεο και το ενσωματώνουν μόνο αφού συμφωνήσει ο μαθητής. Χωρίς αυτό, ο πάροχος λαμβάνει τη διεύθυνση IP και τα στοιχεία προγράμματος περιήγησης του μαθητή μόλις ανοίξει η σελίδα — πριν πατήσει κανείς αναπαραγωγή. Απενεργοποιήστε το μόνο αν το ίδρυμά σας λαμβάνει αυτή τη συγκατάθεση αλλού.';
$string['report_actions'] = 'Ενέργειες';
$string['report_answered'] = 'Απαντήθηκαν';
$string['report_attemptnumber'] = 'Προσπάθεια';
$string['report_back'] = 'Πίσω σε όλες τις προσπάθειες';
$string['report_correct'] = 'Σωστά';
$string['report_delete'] = 'Διαγραφή';
$string['report_deleteconfirm'] = 'Οριστική διαγραφή αυτής της προσπάθειας και όλων των απαντήσεών της; Δεν μπορεί να αναιρεθεί.';
$string['report_deleted'] = 'Η προσπάθεια διαγράφηκε.';
$string['report_exact'] = 'Ακριβώς';
$string['report_export'] = 'Εξαγωγή';
$string['report_filterany'] = 'Όλα';
$string['report_filterapply'] = 'Εφαρμογή φίλτρων';
$string['report_filterattempt'] = 'Αριθμός προσπάθειας';
$string['report_filterfrom'] = 'Έναρξη από';
$string['report_filterrangeerror'] = 'Το τέλος του διαστήματος προηγείται της αρχής του.';
$string['report_filterreset'] = 'Καθαρισμός φίλτρων';
$string['report_filterstate'] = 'Κατάσταση';
$string['report_filterto'] = 'Έναρξη έως';
$string['report_filteruser'] = 'Άτομο';
$string['report_finished'] = 'Ολοκληρωμένες';
$string['report_heading'] = 'Προσπάθειες';
$string['report_hinted'] = 'Με υπόδειξη';
$string['report_hints'] = 'Επίπεδο υπόδειξης';
$string['report_kpianswered'] = 'Απαντήθηκαν';
$string['report_kpiattempts'] = 'Εμφανιζόμενες προσπάθειες';
$string['report_kpiaverage'] = 'Μέσος βαθμός (ολοκληρωμένες)';
$string['report_kpicorrect'] = 'Αποδεκτά';
$string['report_kpiexact'] = 'Απολύτως σωστά';
$string['report_kpifinished'] = 'Ολοκληρωμένες';
$string['report_kpihinted'] = 'Χρησιμοποίησαν υπόδειξη';
$string['report_kpihintedgaps'] = 'Χρειάστηκαν υπόδειξη';
$string['report_noattempts'] = 'Δεν υπάρχουν προσπάθειες ακόμη.';
$string['report_nogaps'] = 'Η έκδοση στην οποία έγινε αυτή η προσπάθεια δεν έχει κενά.';
$string['report_nomatchingattempts'] = 'Καμία προσπάθεια δεν ταιριάζει σε αυτά τα φίλτρα.';
$string['report_noresponse'] = 'Χωρίς απάντηση';
$string['report_response'] = 'Απάντηση';
$string['report_result'] = 'Αποτέλεσμα';
$string['report_result_empty'] = 'Κενή';
$string['report_result_exact'] = 'Ακριβής';
$string['report_result_incorrect'] = 'Λανθασμένη';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Αναγνωρισμένη';
$string['report_score'] = 'Βαθμός';
$string['report_solution'] = 'Λύση';
$string['report_started'] = 'Έναρξη';
$string['report_state'] = 'Κατάσταση';
$string['report_state_abandoned'] = 'Εγκαταλελειμμένη';
$string['report_state_finished'] = 'Ολοκληρωμένη';
$string['report_state_inprogress'] = 'Σε εξέλιξη';
$string['report_transcript'] = 'Απομαγνητοφώνηση';
$string['report_tries'] = 'Προσπάθειες';
$string['report_user'] = 'Άτομο';
$string['report_view'] = 'Προβολή';
$string['reports'] = 'Αναφορές';
$string['resetattempts'] = 'Διαγραφή όλων των προσπαθειών και απαντήσεων των μαθητών';
$string['solutionavailability'] = 'Κείμενο λύσης για τους μαθητές';
$string['solutionavailability_aftersubmission'] = 'Μετά την ολοκλήρωση της προσπάθειας';
$string['solutionavailability_always'] = 'Οποτεδήποτε';
$string['solutionavailability_help'] = 'Πότε μπορούν οι μαθητές να κατεβάσουν το πλήρες κείμενο με εμφανή τη λύση κάθε κενού.

* Ποτέ — μόνο οι διδάσκοντες μπορούν να το κατεβάσουν.
* Μετά την ολοκλήρωση της προσπάθειας — ο μαθητής μπορεί να το κατεβάσει αφού ολοκληρώσει μια προσπάθεια σε αυτή τη δραστηριότητα.
* Οποτεδήποτε — ο μαθητής μπορεί να το κατεβάσει και πριν απαντήσει.

Οι διδάσκοντες μπορούν πάντα να το κατεβάσουν, ανεξάρτητα από αυτή τη ρύθμιση.';
$string['solutionavailability_never'] = 'Ποτέ';
$string['subplugintype_elangscript'] = 'Χειριστής γραφής';
$string['subplugintype_elangscript_plural'] = 'Χειριστές γραφής';
$string['subtitleposition'] = 'Εμφάνιση υπότιτλων';
$string['subtitleposition_below'] = 'Κάτω από το μέσο';
$string['subtitleposition_help'] = 'Πού εμφανίζονται οι διαδραστικοί υπότιτλοι.

* Κάτω από το μέσο — ολόκληρη η απομαγνητοφώνηση βρίσκεται κάτω από το μέσο σε δική της περιοχή κύλισης και ακολουθεί την αναπαραγωγή.
* Πάνω στο μέσο, κάτω ή επάνω — μόνο ο υπότιτλος που παίζει εκείνη τη στιγμή σχεδιάζεται πάνω στο μέσο.

Ένα μέσο μόνο με ήχο δεν έχει εικόνα για να σχεδιαστεί επάνω της, οπότε χρησιμοποιεί πάντα την εμφάνιση κάτω από το μέσο. Η ίδια η ρύθμιση διατηρείται και ισχύει ξανά μόλις η δραστηριότητα χρησιμοποιήσει βίντεο.';
$string['subtitleposition_overlaybottom'] = 'Πάνω στο μέσο — κάτω';
$string['subtitleposition_overlaytop'] = 'Πάνω στο μέσο — επάνω';
$string['task_migratev1activities'] = 'Μεταφορά δραστηριοτήτων έκδοσης 1';
$string['transcriptheading'] = 'Απομαγνητοφώνηση για τους μαθητές';
$string['validate_cueafterend'] = '{$a->where}: τελειώνει στα {$a->endtime} ms, μετά το μέσο ({$a->duration} ms). Η αναπαραγωγή δεν το φτάνει ποτέ.';
$string['validate_cueendbeforestart'] = '{$a}: η λήξη δεν είναι μετά την έναρξη.';
$string['validate_cuewhere'] = 'Τμήμα {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'Η λύση για {$a} είναι κενή.';
$string['validate_hintlevels'] = 'Τα επίπεδα υπόδειξης για {$a} δεν αποτελούν συνεχή ακολουθία που ξεκινά από το 1.';
$string['validate_negativetime'] = '{$a}: ο χρόνος έναρξης προηγείται της αρχής της ηχογράφησης.';
$string['validate_nocues'] = 'Η έκδοση δεν έχει τμήματα.';
$string['validate_nogaps'] = 'Η έκδοση δεν έχει κενά προς απάντηση.';
$string['validate_nonpositivelength'] = 'Το μήκος χαρακτήρων του {$a} πρέπει να είναι θετικό.';
$string['validate_rangeoutside'] = 'Το εύρος χαρακτήρων του {$a} βρίσκεται εκτός του κειμένου του.';
$string['validate_rangeoverlap'] = 'Το εύρος χαρακτήρων του {$a} επικαλύπτεται με άλλο κενό.';
$string['validate_unknownalgorithm'] = 'Ο αλγόριθμος βαθμολόγησης «{$a->algorithm}» για {$a->where} δεν αναγνωρίζεται.';
$string['validate_where'] = 'κενό {$a->gapkey} στο τμήμα {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Κενό {$a->gapkey}: ο αλγόριθμος βαθμολόγησης είναι «{$a->actual}», αναμενόταν «{$a->expected}».';
$string['verify_attemptcount'] = 'Ο αριθμός μεταφερμένων προσπαθειών είναι {$a->actual}, αναμένονταν {$a->expected} διακριτοί μαθητές της 1.x.';
$string['verify_jarothreshold'] = 'Το κατώφλι σύγκρισης απαντήσεων είναι {$a->actual}, αναμενόταν {$a->expected}.';
$string['verify_missingattempt'] = 'Χρήστης {$a}: αναμενόταν μεταφερμένη προσπάθεια, δεν βρέθηκε καμία.';
$string['verify_missingcue'] = 'Τμήμα {$a}: το μεταφερμένο τμήμα λείπει.';
$string['verify_missinggap'] = 'Κενό {$a}: το μεταφερμένο κενό λείπει.';
$string['verify_missinghint'] = 'Κενό {$a}: η έκδοση 1 επέτρεπε βοήθεια εδώ, αλλά δεν μεταφέρθηκε υπόδειξη.';
$string['verify_orphancue'] = 'Τμήμα {$a}: δεν βρέθηκε αντίστοιχο τμήμα της έκδοσης 1.';
$string['verify_orphangap'] = 'Κενό {$a}: δεν βρέθηκε αντίστοιχο κενό της έκδοσης 1.';
$string['verify_rangemismatch'] = 'Κενό {$a}: το εύρος χαρακτήρων δεν ταιριάζει με την πηγή της έκδοσης 1.';
$string['verify_responsecount'] = 'Χρήστης {$a->userid}: ο αριθμός μεταφερμένων απαντήσεων είναι {$a->actual}, αναμενόταν {$a->expected}.';
$string['verify_solutionmismatch'] = 'Κενό {$a->gapkey}: η λύση είναι «{$a->actual}», αναμενόταν «{$a->expected}».';
$string['verify_transcriptmismatch'] = 'Τμήμα {$a}: η απομαγνητοφώνηση δεν ταιριάζει με την πηγή της έκδοσης 1.';
$string['verify_unexpectedhint'] = 'Κενό {$a}: η έκδοση 1 δεν επέτρεπε βοήθεια εδώ, αλλά μεταφέρθηκε υπόδειξη.';
