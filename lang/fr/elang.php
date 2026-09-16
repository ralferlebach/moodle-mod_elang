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
 * French strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * Terminology follows the existing AMOS translation of mod_elang 1.3.5 so that
 * teachers who know the old version meet the same words: séquence for a cue,
 * sous-titres, vidéos, exercice. The string ids themselves are new — 2.0
 * rebuilt the interface — so nothing could be carried over automatically.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Langues de contenu autorisées';
$string['allowedlanguages_desc'] = 'Les langues de contenu proposées lors de la création ou de la modification d\'une dictée vidéo. N\'en sélectionnez aucune pour proposer la liste complète. Une activité conserve la langue enregistrée même si vous la retirez ensuite d\'ici.';
$string['allowtranscriptdownload'] = 'Téléchargement de la transcription par les participants';
$string['allowtranscriptdownload_help'] = 'Lorsque ce réglage est activé, les participants peuvent télécharger la fiche de travail de la transcription, avec chaque blanc masqué, au format PDF, Word, OpenDocument ou texte.

Il est désactivé par défaut. Les enseignants peuvent toujours télécharger la transcription, quel que soit ce réglage.';
$string['allowtranscriptdownload_label'] = 'Les participants peuvent télécharger la fiche de travail';
$string['completiondetail_completionfinishattempt'] = 'Terminer une tentative';
$string['completionfinishattempt'] = 'Le participant doit terminer une tentative';
$string['cuepausemode'] = 'Pause aux limites des sous-titres';
$string['cuepausemode_auto'] = 'Automatique';
$string['cuepausemode_help'] = 'Si le média s\'arrête à la fin d\'un sous-titre.

* Automatique — la lecture se poursuit et ne s\'arrête à la fin d\'un sous-titre que tant que celui-ci est en cours de traitement, c\'est-à-dire après un clic dessus ou sur l\'un de ses blancs, ou lorsque le focus clavier s\'y trouve.
* S\'arrêter à chaque sous-titre non répondu — la lecture s\'arrête à la fin de chaque sous-titre comportant encore un blanc vide, et attend d\'être reprise.
* Ne jamais s\'arrêter — la lecture se poursuit jusqu\'à la fin du média.

Aucun des deux premiers modes ne s\'arrête sur un sous-titre dont tous les blancs sont complétés : c\'est du travail terminé, et s\'y arrêter demanderait une frappe sans effet. Cela signifie aussi qu\'un second passage dans un exercice ne s\'arrête que là où il manque encore quelque chose.';
$string['cuepausemode_nostop'] = 'Ne jamais s\'arrêter';
$string['cuepausemode_stop'] = 'S\'arrêter à chaque sous-titre non répondu';
$string['editcontent'] = 'Modifier le contenu';
$string['editor_addcue'] = 'Ajouter une séquence';
$string['editor_addgap'] = 'Créer un blanc depuis la sélection';
$string['editor_addhint'] = 'Ajouter un indice';
$string['editor_addvariant'] = 'Ajouter une variante';
$string['editor_advanced'] = 'Réglages avancés';
$string['editor_algoexact'] = 'Correspondance exacte';
$string['editor_algorithm'] = 'Comparaison';
$string['editor_algowordrecognized'] = 'Accepter les réponses proches';
$string['editor_answers'] = 'Variantes acceptées';
$string['editor_autosaved'] = 'Toutes les modifications sont enregistrées.';
$string['editor_autosaveerror'] = 'L\'enregistrement automatique a échoué — utilisez « Enregistrer ».';
$string['editor_captureend'] = 'Définir la fin depuis la lecture';
$string['editor_capturestart'] = 'Définir le début depuis la lecture';
$string['editor_cueactions'] = 'Actions sur la séquence';
$string['editor_cuecount'] = 'Séquences : {$a}';
$string['editor_cuenotsaved'] = 'Non enregistré';
$string['editor_currentmedia'] = 'Média actuel :';
$string['editor_deletecue'] = 'Supprimer la séquence';
$string['editor_deletegap'] = 'Supprimer le blanc';
$string['editor_emptytranscript'] = '(pas encore de texte)';
$string['editor_endtime'] = 'Fin';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Blancs : {$a}';
$string['editor_gaprange'] = 'Position du blanc (caractères)';
$string['editor_gotomedia'] = 'Aller aux médias';
$string['editor_heading'] = 'Éditeur de contenu de l\'exercice';
$string['editor_hints'] = 'Indices';
$string['editor_hinttext'] = 'Texte de l\'indice';
$string['editor_hinttype'] = 'Type';
$string['editor_hinttype_firstletter'] = 'Première lettre';
$string['editor_hinttype_partial'] = 'Partiel';
$string['editor_hinttype_solution'] = 'Solution';
$string['editor_hinttype_text'] = 'Texte libre';
$string['editor_hinttype_translation'] = 'Traduction';
$string['editor_hinttype_wordlength'] = 'Longueur du mot';
$string['editor_import'] = 'Importer des sous-titres';
$string['editor_importappend'] = 'Ajouter aux séquences existantes';
$string['editor_importapply'] = 'Importer';
$string['editor_importcancel'] = 'Annuler';
$string['editor_importcheck'] = 'Vérifier le contenu';
$string['editor_importchecking'] = 'Vérification…';
$string['editor_importcuecount'] = 'Séquences trouvées';
$string['editor_importduration'] = 'Durée';
$string['editor_importedcues'] = 'Séquences importées : {$a}';
$string['editor_importfilehint'] = 'Choisissez un fichier WebVTT (.vtt) ou SubRip (.srt) contenant des sous-titres.';
$string['editor_importformat'] = 'Format';
$string['editor_importfromfile'] = 'Déposer un fichier';
$string['editor_importfromtext'] = 'Coller du texte';
$string['editor_importgapcount'] = 'Blancs trouvés';
$string['editor_importhint'] = 'Collez du contenu WebVTT ou SubRip, puis importez-le comme séquences.';
$string['editor_importparseerror'] = 'Ce contenu n\'a pas pu être lu comme WebVTT ou SubRip.';
$string['editor_importpastedtext'] = 'Texte collé';
$string['editor_importreaderror'] = 'Le fichier n\'a pas pu être lu.';
$string['editor_importready'] = 'Prêt à importer';
$string['editor_importreplace'] = 'Remplacer toutes les séquences';
$string['editor_importreplacedcues'] = 'Séquences remplacées ; nouvellement importées : {$a}';
$string['editor_importsource'] = 'Source';
$string['editor_importsummary'] = 'Ce qui a été trouvé';
$string['editor_importtoolarge'] = 'Ce fichier fait {$a->size} ; l\'import accepte au maximum {$a->max}.';
$string['editor_importwrongtype'] = 'Choisissez un fichier de sous-titres ({$a}).';
$string['editor_insertafter'] = 'Insérer une séquence après';
$string['editor_insertbefore'] = 'Insérer une séquence avant';
$string['editor_invalidtime'] = 'Saisissez une durée au format mm:ss.SSS, par exemple 01:05.400.';
$string['editor_linkurl'] = 'Lien de référence';
$string['editor_linkurl_help'] = 'Affiché à côté du blanc comme point de recherche du mot. Laissez vide pour n\'en proposer aucun.';
$string['editor_loaderror'] = 'L\'éditeur n\'a pas pu être chargé. Veuillez recharger la page.';
$string['editor_loading'] = 'Chargement de l\'éditeur…';
$string['editor_maxlength'] = 'Longueur maximale';
$string['editor_maxlength_help'] = 'Limite ce qu\'un participant peut saisir. 0 signifie sans limite.';
$string['editor_media'] = 'Médias';
$string['editor_mediafile'] = 'Fichier déposé';
$string['editor_mediakind'] = 'Type de média';
$string['editor_medianone'] = 'Aucun';
$string['editor_mediaprovider'] = 'Fournisseur';
$string['editor_mediaproviderref'] = 'Référence du fournisseur';
$string['editor_mediaproviderrefhint'] = 'Identifiant ou lien de la vidéo sous une forme courante (p. ex. youtu.be/…).';
$string['editor_mediasaved'] = 'Média enregistré.';
$string['editor_mediaurl'] = 'Adresse directe';
$string['editor_nocues'] = 'Aucune séquence pour l\'instant. Ajoutez-en une ou importez des sous-titres.';
$string['editor_nocueselected'] = 'Sélectionnez une séquence dans la liste pour la modifier.';
$string['editor_nocuesmatch'] = 'Aucune séquence ne correspond à cette recherche.';
$string['editor_nogaps'] = 'Aucun blanc';
$string['editor_nomedia'] = 'aucun';
$string['editor_nomedianotice'] = 'Ajoutez d\'abord le fichier vidéo ou audio dans l\'onglet Médias. Les sous-titres sont calés sur le média, l\'éditeur en a donc besoin avant que vous puissiez travailler sur les séquences et les blancs.';
$string['editor_novideotrack'] = 'Ce navigateur ne peut pas décoder la piste vidéo de ce média (seul l\'audio est lu) ; les participants verraient une image noire. Veuillez ré-encoder le fichier en H.264/MP4 (par exemple avec ffmpeg ou HandBrake) et le déposer à nouveau.';
$string['editor_onboardinggaps'] = 'Sélectionnez un mot dans une séquence et marquez-le comme blanc.';
$string['editor_onboardingimport'] = 'Importez des sous-titres WebVTT/SubRip, ou ajoutez des séquences à la main.';
$string['editor_onboardingintro'] = 'Créez un exercice en trois étapes :';
$string['editor_onboardingmedia'] = 'Choisissez un média (dépôt, adresse ou fournisseur).';
$string['editor_onboardingtitle'] = 'Commencez votre exercice';
$string['editor_onlywarnings'] = 'Seulement les séquences avec avertissement';
$string['editor_parsegaps'] = 'Reconnaître les marqueurs de blanc : [mot] crée un blanc avec indices autorisés, {mot} un blanc sans indices.';
$string['editor_penalty'] = 'Pénalité';
$string['editor_poster'] = 'Image d\'affiche';
$string['editor_preview'] = 'Aperçu participant';
$string['editor_problem_afterduration'] = 'La fin est postérieure à la fin du média.';
$string['editor_problem_endbeforestart'] = 'La fin est à la même position que le début ou avant lui.';
$string['editor_problem_negativestart'] = 'Le début est antérieur au début de l’enregistrement.';
$string['editor_publish'] = 'Publier';
$string['editor_publishblocked'] = 'Certains sous-titres ne peuvent pas encore être enregistrés. Corrigez-les avant de publier.';
$string['editor_published'] = 'Version publiée.';
$string['editor_removehint'] = 'Retirer l\'indice';
$string['editor_removevariant'] = 'Retirer';
$string['editor_repaircue'] = 'Corriger la fin';
$string['editor_ruleapplied'] = '%count% blancs créés à partir de la règle.';
$string['editor_ruleapply'] = 'Appliquer %count% blancs';
$string['editor_ruleerror'] = 'Les blancs n\'ont pas pu être générés.';
$string['editor_ruleeverynth'] = 'Tous les n mots';
$string['editor_rulefound'] = 'La règle a trouvé %count% blancs.';
$string['editor_rulegenerate'] = 'Générer les blancs';
$string['editor_ruleinterval'] = 'Intervalle (n)';
$string['editor_ruletype'] = 'Règle de blancs';
$string['editor_rulewordlist'] = 'Mots à masquer';
$string['editor_rulewords'] = 'Liste de mots';
$string['editor_save'] = 'Enregistrer le brouillon';
$string['editor_saved'] = 'Brouillon enregistré.';
$string['editor_savedwithproblems'] = 'Ce qui pouvait être enregistré l’a été ; certains sous-titres demandent votre attention.';
$string['editor_saveerror'] = 'Le brouillon n\'a pas pu être enregistré.';
$string['editor_savemedia'] = 'Enregistrer le média';
$string['editor_saving'] = 'Enregistrement…';
$string['editor_searchcues'] = 'Rechercher des séquences';
$string['editor_selecttext'] = 'Sélectionnez d\'abord le mot à masquer dans la transcription.';
$string['editor_solution'] = 'Solution';
$string['editor_starttime'] = 'Début';
$string['editor_transcript'] = 'Transcription';
$string['editor_unsaved'] = 'Modifications non enregistrées';
$string['editor_uploadmedia'] = 'Déposer des fichiers médias';
$string['editor_variantisregex'] = 'Traiter {$a} comme une expression régulière';
$string['editor_variantmatching'] = 'Comment les variantes acceptées sont comparées';
$string['editor_warnemptysolution'] = 'Un blanc sans solution';
$string['editor_warnnotranscript'] = 'Pas de texte';
$string['editor_warntiming'] = 'La fin n\'est pas après le début';
$string['editor_waveform'] = 'Forme d\'onde audio';
$string['elang:addinstance'] = 'Ajouter une nouvelle dictée vidéo';
$string['elang:attempt'] = 'Effectuer une dictée vidéo';
$string['elang:deleteattempts'] = 'Supprimer les tentatives des participants';
$string['elang:exportreports'] = 'Exporter des rapports contenant des données personnelles';
$string['elang:exportsolution'] = 'Exporter la transcription complète avec les solutions';
$string['elang:exporttranscript'] = 'Exporter la fiche de travail sous forme de document';
$string['elang:manage'] = 'Créer et modifier le contenu des exercices';
$string['elang:useregex'] = 'Utiliser des expressions régulières dans les réponses acceptées';
$string['elang:view'] = 'Consulter une dictée vidéo';
$string['elang:viewreports'] = 'Consulter les rapports des participants';
$string['error_attemptnotinprogress'] = 'Cette tentative n\'est plus en cours.';
$string['error_couldnotobtainlock'] = 'Impossible d\'obtenir un verrou pour cette opération. Veuillez réessayer.';
$string['error_draftrevisionmismatch'] = 'Ce brouillon a été modifié depuis son chargement. Veuillez recharger et réessayer.';
$string['error_duplicatecuekey'] = 'Deux séquences partagent la clé « {$a} » ; chaque séquence a besoin d\'une clé unique.';
$string['error_duplicategapkey'] = 'Deux blancs d\'une même séquence partagent la clé « {$a} » ; chaque blanc a besoin d\'une clé unique.';
$string['error_duplicatehintlevel'] = 'Un blanc possède deux indices au niveau {$a} ; chaque niveau doit être unique.';
$string['error_gapnotinattemptversion'] = 'Ce blanc n\'appartient pas à la version de l\'exercice de cette tentative.';
$string['error_importnocues'] = 'Aucun sous-titre n\'a pu être lu dans ce contenu. Un fichier WebVTT ou SubRip comporte une ligne de temps telle que 00:00:01.000 --> 00:00:04.000 au-dessus de chaque sous-titre.';
$string['error_importnotutf8'] = 'Ce fichier n\'est pas de l\'UTF-8 valide. Il a probablement été enregistré dans un encodage plus ancien — ouvrez-le dans un éditeur de texte et enregistrez-le à nouveau en UTF-8.';
$string['error_importtoolarge'] = 'Ce fichier fait {$a->size} ; l\'import accepte au maximum {$a->max}. Un fichier de sous-titres pour l\'enregistrement d\'un cours est bien plus petit, il est donc peu probable que ce soit le cas ici.';
$string['error_importtoomanycues'] = 'Ce fichier contient {$a->count} sous-titres ; l\'import en accepte {$a->max} au maximum.';
$string['error_invalidcuepausemode'] = 'Choisissez l\'une des options proposées pour la pause aux limites des sous-titres.';
$string['error_invalidgradingalgorithm'] = 'L\'algorithme d\'évaluation « {$a} » n\'est ni exact ni wordrecognized.';
$string['error_invalidhinttype'] = 'Le type d\'indice « {$a} » ne fait pas partie des types autorisés.';
$string['error_invalidisregex'] = 'Le marqueur d\'expression régulière d\'une variante doit valoir 0 ou 1.';
$string['error_invalidmediakind'] = 'Le type de média choisi n\'est ni file, ni url, ni provider.';
$string['error_invalidpenalty'] = 'Une pénalité d\'indice doit être comprise entre 0 et 1.';
$string['error_invalidproviderref'] = '« {$a} » n\'est pas un identifiant ou un lien de vidéo reconnu pour ce fournisseur.';
$string['error_invalidregexpattern'] = '« {$a} » n\'est pas une expression régulière valide.';
$string['error_invalidsolutionavailability'] = 'Choisissez l\'une des options proposées pour déterminer quand les participants peuvent voir la transcription avec les solutions.';
$string['error_invalidsourceurl'] = 'Saisissez une adresse complète commençant par http:// ou https://, ou un lien YouTube ou Vimeo.';
$string['error_invalidsubtitleposition'] = 'Choisissez l\'une des options proposées pour l\'emplacement des sous-titres.';
$string['error_invalidv1cuejson'] = 'Cette séquence de la version 1 n\'a pas pu être analysée.';
$string['error_negativegapoffset'] = 'La position et la longueur d\'un blanc ne doivent pas être négatives.';
$string['error_noaccesstoattempt'] = 'Vous n\'avez pas accès à cette tentative.';
$string['error_nomorehints'] = 'Aucun autre indice n\'est disponible pour ce blanc.';
$string['error_nopublishedversion'] = 'Cet exercice n\'a pas encore de contenu publié.';
$string['error_responsetoolong'] = 'Votre réponse est trop longue. Le maximum pour ce blanc est de {$a} caractères.';
$string['error_solutionnotavailable'] = 'La transcription avec les solutions ne vous est pas accessible dans cette activité.';
$string['error_staleattemptstate'] = 'Votre vue de cette tentative n\'est plus à jour. Veuillez recharger l\'état actuel et réessayer.';
$string['error_transcriptnotavailable'] = 'Aucune transcription n\'est disponible au téléchargement dans cette activité.';
$string['error_unknowngaprule'] = 'Type de règle de blancs inconnu « {$a} ».';
$string['error_unknownmediaprovider'] = '« {$a} » ne fait pas partie des fournisseurs de médias pris en charge.';
$string['error_versionnotadraft'] = 'Seule une version à l\'état de brouillon peut être modifiée.';
$string['error_versionnotfound'] = 'Cette version de l\'exercice n\'existe plus.';
$string['error_versionnotpublishable'] = 'Cette version ne peut pas être publiée : {$a}';
$string['export_audienceaftersubmission'] = 'Les participants peuvent le télécharger une fois une tentative terminée';
$string['export_audiencealways'] = 'Les participants peuvent le télécharger à tout moment';
$string['export_audiencestaff'] = 'Réservé au personnel enseignant disposant de l\'autorisation — non proposé aux participants';
$string['export_docx'] = 'Télécharger au format Word (DOCX)';
$string['export_downloadpdf'] = 'Télécharger le PDF';
$string['export_heading'] = 'Exporter la transcription';
$string['export_intro'] = 'Téléchargez la transcription de cet exercice dans plusieurs formats.';
$string['export_moreformats'] = 'Autres formats';
$string['export_nocontent'] = 'Aucune transcription publiée n\'est encore disponible à l\'export.';
$string['export_odt'] = 'Télécharger au format OpenDocument (ODT)';
$string['export_pdf'] = 'Télécharger au format PDF';
$string['export_solution'] = 'Transcription avec solutions';
$string['export_solutionhint'] = 'Le texte complet avec la solution de chaque blanc affichée.';
$string['export_text'] = 'Télécharger en texte';
$string['export_versionnote'] = 'Les exports se basent sur la version actuellement publiée de cet exercice.';
$string['export_worksheet'] = 'Fiche de travail (blancs masqués)';
$string['export_worksheethint'] = 'Le texte avec chaque blanc masqué. Prêt à être distribué comme support.';
$string['exporttranscript'] = 'Exporter la transcription';
$string['filearea_media'] = 'Médias';
$string['filearea_poster'] = 'Image d\'affiche';
$string['gradingheading'] = 'Évaluation des réponses';
$string['import_badtiming'] = 'Impossible de lire la ligne de temps : {$a}';
$string['import_emptytranscript'] = 'Une séquence sans texte a été ignorée.';
$string['import_warnlinetoolong'] = 'Le bloc {$a->block} a été ignoré : il contient une ligne de plus de {$a->max} caractères, ce qui n\'est pas une ligne de sous-titre.';
$string['jarothreshold'] = 'Seuil de similarité';
$string['jarothreshold_help'] = 'Pour les blancs réglés sur « Accepter les réponses proches », il s\'agit de la similarité de Jaro minimale entre la réponse attendue et celle qui est saisie. La valeur 1 exige une correspondance exacte après la normalisation propre à la langue ; des valeurs plus basses acceptent des graphies de plus en plus éloignées.';
$string['jarothresholdrange'] = 'Le seuil doit être compris entre 0 et 1.';
$string['language'] = 'Langue du contenu';
$string['language_help'] = 'Choisissez la langue du contenu de l\'exercice. Elle détermine la manière dont les réponses sont comparées, notamment la casse et la translittération. Choisissez « Générique (non précisée) » si aucun traitement propre à une langue ne doit être appliqué. Les nouvelles versions de contenu reprennent ce réglage.';
$string['language_none'] = 'Générique (non précisée)';
$string['media_cuenote'] = 'Les sous-titres et les blancs existants sont conservés lorsque vous changez de média. Leurs minutages ne sont pas ajustés, vérifiez-les donc ensuite dans l\'éditeur.';
$string['media_current'] = 'Média actuel';
$string['media_heading'] = 'Médias';
$string['media_intro'] = 'Choisissez la vidéo ou l\'audio sur lequel repose cet exercice. Les sous-titres y sont calés, c\'est donc la première étape.';
$string['media_none'] = 'Aucun média n\'a encore été défini pour cet exercice.';
$string['media_othersource'] = 'Autre source';
$string['media_providerhint'] = 'Fournisseurs reconnus : {$a}. Toute autre adresse est utilisée comme adresse média directe.';
$string['media_sourceurl'] = 'Adresse de la source';
$string['media_sourceurl_help'] = 'Collez l\'adresse d\'une vidéo au lieu de déposer un fichier — un lien YouTube ou Vimeo, ou l\'adresse directe d\'un fichier média.

Une adresse saisie ici remplace un fichier déposé. Laissez-la vide pour utiliser le dépôt ci-dessus.

Une vidéo de fournisseur est lue dans son propre cadre, qui ne communique pas son temps de lecture. Un tel exercice affiche toujours les sous-titres sous le média et ne s\'arrête jamais aux limites des sous-titres.

**Où vont les données.** Un cadre YouTube ou Vimeo connecte le navigateur de chaque participant à cette entreprise, qui reçoit alors son adresse IP et les informations de son appareil. Par défaut, l\'exercice demande son accord au préalable. Si votre établissement dispose de son propre serveur de médias — Opencast, Panopto, Kaltura ou équivalent — collez plutôt l\'adresse directe du fichier depuis celui-ci : elle est traitée comme une simple adresse média, conserve la position des sous-titres et le réglage de pause que vous avez choisis, et aucun tiers n\'est impliqué.';
$string['migratev1_approvalheading'] = 'Migrés, en attente de vérification';
$string['migratev1_approvebutton'] = 'Approuver cette migration';
$string['migratev1_approved'] = 'La dictée vidéo {$a} a été marquée comme approuvée.';
$string['migratev1_colactivity'] = 'Activité';
$string['migratev1_colalgorithm'] = 'Algorithme d\'évaluation';
$string['migratev1_colcues'] = 'Séquences';
$string['migratev1_colgaps'] = 'Blancs';
$string['migratev1_colissues'] = 'Problèmes';
$string['migratev1_collearners'] = 'Participants';
$string['migratev1_confirmdecommission'] = 'Cette opération supprime DÉFINITIVEMENT les tables héritées de la version 1 et la colonne elang.options. Il n\'y a pas de retour en arrière. Continuer ?';
$string['migratev1_confirmmigrate'] = 'Cette opération planifie une tâche en arrière-plan qui écrit les données de la version 2 pour chaque activité listée ci-dessus. Les tables de la version 1 et elang.options restent intactes. Continuer ?';
$string['migratev1_decommissionblocked'] = 'La suppression est encore bloquée ; voir la liste ci-dessous.';
$string['migratev1_decommissionblockedintro'] = 'La suppression est bloquée tant que :';
$string['migratev1_decommissionbutton'] = 'Supprimer les données héritées de la version 1';
$string['migratev1_decommissioned'] = 'Les données héritées de la version 1 ont été supprimées.';
$string['migratev1_decommissionheading'] = 'Mise hors service des données de la version 1';
$string['migratev1_decommissionready'] = 'Toutes les activités de la version 1 ont été migrées et approuvées. Les tables héritées et elang.options peuvent maintenant être supprimées. Cette opération est irréversible.';
$string['migratev1_heading'] = 'Migrer les activités de la version 1';
$string['migratev1_migratebutton'] = 'Migrer ces activités';
$string['migratev1_noissues'] = 'Aucun';
$string['migratev1_nonepending'] = 'Aucune activité de la version 1 n\'attend d\'être migrée.';
$string['migratev1_nonependingapproval'] = 'Aucune activité migrée n\'attend de vérification.';
$string['migratev1_notablespresent'] = 'Aucune table héritée de la version 1 n\'a été trouvée sur ce site. Il n\'y a rien à migrer.';
$string['migratev1_parseerrorcount'] = 'Séquences non analysables : {$a}';
$string['migratev1_pendingheading'] = 'Pas encore migrées';
$string['migratev1_queued'] = 'La tâche de migration a été planifiée. Elle s\'exécutera au prochain passage du cron, ou immédiatement via admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Vérifié : les données migrées correspondent à la source de la version 1, sans écart.';
$string['migratev1_verifieddiscrepancies'] = 'La vérification a relevé des écarts par rapport à la source de la version 1 : {$a}';
$string['migratev1_verifyfailed'] = 'Impossible de vérifier cette activité : {$a}';
$string['modulename'] = 'Dictée vidéo';
$string['modulename_help'] = 'L\'activité dictée vidéo permet aux participants de compléter des blancs dans des sous-titres minutés tout en regardant ou en écoutant une vidéo.

Les enseignants importent un fichier de sous-titres WebVTT ou SubRip, marquent des mots ou des expressions comme blancs, et règlent la rigueur de la comparaison des réponses. Les participants travaillent la transcription séquence par séquence, demandent des indices pénalisés et reçoivent un retour immédiat.';
$string['modulenameplural'] = 'Dictées vidéo';
$string['nav_exportshort'] = 'Export';
$string['nav_media'] = 'Médias';
$string['nav_reports'] = 'Tentatives';
$string['nav_subtitles'] = 'Sous-titres et blancs';
$string['noinstances'] = 'Il n\'y a aucune dictée vidéo dans ce cours.';
$string['overview_attempts'] = 'Tentatives';
$string['playbackheading'] = 'Lecture et sous-titres';
$string['playbackoverlayhint'] = 'Un sous-titre incrusté sur l\'image n\'affiche que le sous-titre en cours de lecture ; la lecture s\'arrête donc toujours à la fin d\'un sous-titre comportant encore des blancs à compléter. Il n\'y a rien à choisir ici.';
$string['playbackproviderhint'] = 'Une vidéo YouTube ou Vimeo est lue par le fournisseur dans son propre cadre, qui ne communique pas son temps de lecture. Un tel exercice affiche toujours les sous-titres sous le média et ne s\'arrête jamais aux limites des sous-titres, quel que soit le choix ci-dessus. Les fichiers déposés et les adresses média directes respectent les deux réglages.';
$string['player_check'] = 'Vérifier la réponse';
$string['player_consentaccept'] = 'Charger la vidéo depuis {$a}';
$string['player_consentdetail'] = 'La lecture connecte votre navigateur à {$a}. {$a} reçoit votre adresse IP et des informations sur votre appareil, et peut lire les cookies qu\'il a déjà déposés. Rien n\'est transmis avant que vous choisissiez de charger la vidéo.';
$string['player_consentheading'] = 'Cette vidéo est fournie par {$a}';
$string['player_exitfullscreen'] = 'Quitter le plein écran';
$string['player_finish'] = 'Terminer la tentative';
$string['player_finished'] = 'Tentative terminée. Score : %score%%';
$string['player_finishincomplete'] = 'Blancs encore vides : {$a}. Terminer quand même la tentative ?';
$string['player_fullscreen'] = 'Plein écran';
$string['player_gaplabel'] = 'Blanc %gap%';
$string['player_gaplink'] = 'Ouvrir le lien';
$string['player_hint'] = 'Afficher un indice';
$string['player_loaderror'] = 'L\'exercice n\'a pas pu être chargé. Veuillez recharger la page.';
$string['player_loading'] = 'Chargement de l\'exercice…';
$string['player_nocontent'] = 'Aucun contenu d\'exercice n\'a encore été publié. Veuillez revenir plus tard.';
$string['player_novideotrack'] = 'Votre navigateur ne peut pas afficher la piste vidéo de ce média ; l\'audio sera tout de même lu. Veuillez en informer votre enseignant.';
$string['player_outdatedattempt'] = 'Cet exercice a été mis à jour depuis le début de cette tentative. Vous poursuivez sur le contenu précédent ; terminez cette tentative pour travailler la prochaine fois avec l\'exercice mis à jour.';
$string['player_progress'] = '{$a->done} blancs sur {$a->total} répondus';
$string['player_ready'] = 'Exercice prêt.';
$string['player_scorelabel'] = 'Score : %score%%';
$string['player_stateaccepted'] = 'Acceptée';
$string['player_statecorrect'] = 'Correcte';
$string['player_statehinted'] = 'Indice utilisé';
$string['player_stateincorrect'] = 'Incorrecte';
$string['player_submitfailed'] = 'Votre réponse n\'a pas pu être enregistrée. Veuillez réessayer.';
$string['player_transcriptheading'] = 'Transcription';
$string['pluginadministration'] = 'Administration de la dictée vidéo';
$string['pluginname'] = 'Dictée vidéo';
$string['privacy_metadata_elang'] = 'Pour chaque activité, la trace de la personne ayant validé la migration à sens unique de son contenu 1.x.';
$string['privacy_metadata_elang_attempt'] = 'Pour chaque tentative d\'un exercice, l\'activité enregistre qui l\'a effectuée, quand, jusqu\'où elle est allée et comment elle a été évaluée.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Le nombre de blancs auxquels le participant a répondu dans cette tentative.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'Le numéro d\'ordre de cette tentative pour l\'utilisateur et l\'activité.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Le nombre de blancs acceptés comme corrects dans cette tentative.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Le nombre de blancs répondus avec une correspondance exacte dans cette tentative.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Le nombre de blancs pour lesquels le participant a demandé un indice.';
$string['privacy_metadata_elang_attempt_score'] = 'Le score obtenu dans cette tentative.';
$string['privacy_metadata_elang_attempt_state'] = 'Si la tentative est en cours, terminée ou abandonnée.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'L\'heure à laquelle la tentative a été terminée.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'L\'heure de la dernière mise à jour de la tentative.';
$string['privacy_metadata_elang_attempt_timestart'] = 'L\'heure à laquelle la tentative a commencé.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'Le nombre total de blancs dans la version de l\'exercice de cette tentative.';
$string['privacy_metadata_elang_attempt_userid'] = 'L\'identifiant de l\'utilisateur ayant effectué la tentative.';
$string['privacy_metadata_elang_attempt_versionid'] = 'La version de l\'exercice sur laquelle cette tentative a été effectuée.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'L\'utilisateur ayant approuvé la migration de cette activité depuis mod_elang 1.x. Conservé pour que la validation reste vérifiable.';
$string['privacy_metadata_elang_response'] = 'Pour chaque blanc auquel un participant répond au cours d\'une tentative, l\'activité enregistre le texte de la réponse et la manière dont elle a été évaluée.';
$string['privacy_metadata_elang_response_accepted'] = 'Si la réponse a été acceptée comme correcte pour ce blanc.';
$string['privacy_metadata_elang_response_hintlevel'] = 'Le niveau d\'indice le plus élevé révélé au participant pour ce blanc.';
$string['privacy_metadata_elang_response_responsetext'] = 'Le texte saisi par le participant pour ce blanc.';
$string['privacy_metadata_elang_response_resultstate'] = 'Le classement retenu par l\'évaluateur pour cette réponse (exacte, mot reconnu, incorrecte ou vide).';
$string['privacy_metadata_elang_response_score'] = 'Les points apportés par cette réponse, après déduction d\'une éventuelle pénalité d\'indice.';
$string['privacy_metadata_elang_response_timecreated'] = 'L\'heure de la première soumission de cette réponse.';
$string['privacy_metadata_elang_response_timemodified'] = 'L\'heure de la dernière mise à jour de cette réponse.';
$string['privacy_metadata_elang_response_tries'] = 'Le nombre de fois où le participant a soumis une réponse pour ce blanc.';
$string['privacy_metadata_elang_version'] = 'Pour chaque version de contenu, l\'activité enregistre quel utilisateur l\'a modifiée en dernier.';
$string['privacy_metadata_elang_version_usermodified'] = 'L\'utilisateur ayant modifié cette version de contenu en dernier. Conservé pour pouvoir vérifier qui a modifié le contenu de l\'exercice.';
$string['privacy_provider_externallink'] = 'Lorsqu\'un exercice repose sur une vidéo YouTube ou Vimeo, son ouverture connecte le navigateur du participant à ce fournisseur. Le plugin n\'envoie rien par lui-même, mais la connexion est provoquée par l\'activité. Qu\'elle ait lieu ou non dépend du réglage du site concernant le consentement au fournisseur et de l\'accord du participant.';
$string['privacy_provider_ipaddress'] = 'L\'adresse IP depuis laquelle le navigateur du participant se connecte.';
$string['privacy_provider_useragent'] = 'Les informations de navigateur et d\'appareil envoyées par le navigateur.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Demander avant d\'intégrer YouTube ou Vimeo';
$string['providerconsent_desc'] = 'Les exercices reposant sur une vidéo YouTube ou Vimeo affichent un avis à la place de la vidéo et ne l\'intègrent qu\'après l\'accord du participant. Sans cela, le fournisseur reçoit l\'adresse IP et les informations de navigateur du participant dès l\'ouverture de la page — avant que quiconque n\'appuie sur lecture. Ne le désactivez que si votre établissement recueille ce consentement ailleurs.';
$string['report_actions'] = 'Actions';
$string['report_answered'] = 'Répondus';
$string['report_attemptnumber'] = 'Tentative';
$string['report_back'] = 'Retour à toutes les tentatives';
$string['report_correct'] = 'Corrects';
$string['report_delete'] = 'Supprimer';
$string['report_deleteconfirm'] = 'Supprimer définitivement cette tentative et toutes ses réponses ? Cette action est irréversible.';
$string['report_deleted'] = 'La tentative a été supprimée.';
$string['report_exact'] = 'Exacts';
$string['report_export'] = 'Exporter';
$string['report_filterany'] = 'Tous';
$string['report_filterapply'] = 'Appliquer les filtres';
$string['report_filterattempt'] = 'Numéro de tentative';
$string['report_filterfrom'] = 'Commencée à partir du';
$string['report_filterrangeerror'] = 'La fin de la période précède son début.';
$string['report_filterreset'] = 'Effacer les filtres';
$string['report_filterstate'] = 'État';
$string['report_filterto'] = 'Commencée jusqu\'au';
$string['report_filteruser'] = 'Personne';
$string['report_finished'] = 'Terminées';
$string['report_heading'] = 'Tentatives';
$string['report_hinted'] = 'Avec indice';
$string['report_hints'] = 'Niveau d\'indice';
$string['report_kpianswered'] = 'Répondus';
$string['report_kpiattempts'] = 'Tentatives affichées';
$string['report_kpiaverage'] = 'Score moyen (terminées)';
$string['report_kpicorrect'] = 'Acceptés';
$string['report_kpiexact'] = 'Exactement justes';
$string['report_kpifinished'] = 'Terminées';
$string['report_kpihinted'] = 'Ont utilisé un indice';
$string['report_kpihintedgaps'] = 'Ont nécessité un indice';
$string['report_noattempts'] = 'Aucune tentative pour l\'instant.';
$string['report_nogaps'] = 'La version sur laquelle cette tentative a été faite ne comporte aucun blanc.';
$string['report_nomatchingattempts'] = 'Aucune tentative ne correspond à ces filtres.';
$string['report_noresponse'] = 'Sans réponse';
$string['report_response'] = 'Réponse';
$string['report_result'] = 'Résultat';
$string['report_result_empty'] = 'Vide';
$string['report_result_exact'] = 'Exacte';
$string['report_result_incorrect'] = 'Incorrecte';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Reconnue';
$string['report_score'] = 'Score';
$string['report_solution'] = 'Solution';
$string['report_started'] = 'Commencée';
$string['report_state'] = 'État';
$string['report_state_abandoned'] = 'Abandonnée';
$string['report_state_finished'] = 'Terminée';
$string['report_state_inprogress'] = 'En cours';
$string['report_transcript'] = 'Transcription';
$string['report_tries'] = 'Essais';
$string['report_user'] = 'Personne';
$string['report_view'] = 'Consulter';
$string['reports'] = 'Rapports';
$string['resetattempts'] = 'Supprimer toutes les tentatives et réponses des participants';
$string['solutionavailability'] = 'Transcription avec solutions pour les participants';
$string['solutionavailability_aftersubmission'] = 'Après la fin de la tentative';
$string['solutionavailability_always'] = 'À tout moment';
$string['solutionavailability_help'] = 'Quand les participants peuvent télécharger la transcription complète avec la solution de chaque blanc.

* Jamais — seuls les enseignants peuvent la télécharger.
* Après la fin de la tentative — un participant peut la télécharger une fois qu\'il a terminé une tentative dans cette activité.
* À tout moment — un participant peut la télécharger avant même de répondre.

Les enseignants peuvent toujours la télécharger, quel que soit ce réglage.';
$string['solutionavailability_never'] = 'Jamais';
$string['subplugintype_elangscript'] = 'Gestionnaire d\'écriture';
$string['subplugintype_elangscript_plural'] = 'Gestionnaires d\'écriture';
$string['subtitleposition'] = 'Affichage des sous-titres';
$string['subtitleposition_below'] = 'Sous le média';
$string['subtitleposition_help'] = 'Où sont affichés les sous-titres interactifs.

* Sous le média — toute la transcription se trouve sous le média, dans sa propre zone de défilement, et suit la lecture.
* Sur le média, en bas ou en haut — seul le sous-titre en cours de lecture est incrusté sur le média.

Un média uniquement audio n\'a pas d\'image sur laquelle incruster ; il utilise donc toujours l\'affichage sous le média. Le réglage lui-même est conservé et s\'applique à nouveau dès que l\'activité utilise une vidéo.';
$string['subtitleposition_overlaybottom'] = 'Sur le média — en bas';
$string['subtitleposition_overlaytop'] = 'Sur le média — en haut';
$string['task_migratev1activities'] = 'Migrer les activités de la version 1';
$string['transcriptheading'] = 'Transcription pour les participants';
$string['validate_cueafterend'] = '{$a->where} : se termine à {$a->endtime} ms, après le média ({$a->duration} ms). La lecture ne peut jamais l\'atteindre.';
$string['validate_cueendbeforestart'] = '{$a} : la fin n\'est pas après le début.';
$string['validate_cuewhere'] = 'Séquence {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'La solution de {$a} est vide.';
$string['validate_hintlevels'] = 'Les niveaux d\'indice de {$a} ne forment pas une suite continue commençant à 1.';
$string['validate_negativetime'] = '{$a} : l\'heure de début précède le début de l\'enregistrement.';
$string['validate_nocues'] = 'La version ne comporte aucune séquence.';
$string['validate_nogaps'] = 'La version ne comporte aucun blanc à compléter.';
$string['validate_nonpositivelength'] = 'La longueur en caractères de {$a} doit être positive.';
$string['validate_rangeoutside'] = 'La plage de caractères de {$a} se situe en dehors de sa transcription.';
$string['validate_rangeoverlap'] = 'La plage de caractères de {$a} chevauche un autre blanc.';
$string['validate_unknownalgorithm'] = 'L\'algorithme d\'évaluation « {$a->algorithm} » pour {$a->where} n\'est pas reconnu.';
$string['validate_where'] = 'le blanc {$a->gapkey} dans la séquence {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Blanc {$a->gapkey} : l\'algorithme d\'évaluation est « {$a->actual} », « {$a->expected} » était attendu.';
$string['verify_attemptcount'] = 'Le nombre de tentatives migrées est {$a->actual}, {$a->expected} participants distincts de la 1.x étaient attendus.';
$string['verify_jarothreshold'] = 'Le seuil de comparaison des réponses est {$a->actual}, {$a->expected} était attendu.';
$string['verify_missingattempt'] = 'Utilisateur {$a} : une tentative migrée était attendue, aucune n\'a été trouvée.';
$string['verify_missingcue'] = 'Séquence {$a} : la séquence migrée est manquante.';
$string['verify_missinggap'] = 'Blanc {$a} : le blanc migré est manquant.';
$string['verify_missinghint'] = 'Blanc {$a} : la version 1 autorisait l\'aide ici, mais aucun indice n\'a été migré.';
$string['verify_orphancue'] = 'Séquence {$a} : aucune séquence correspondante de la version 1 n\'a été trouvée.';
$string['verify_orphangap'] = 'Blanc {$a} : aucun blanc correspondant de la version 1 n\'a été trouvé.';
$string['verify_rangemismatch'] = 'Blanc {$a} : la plage de caractères ne correspond pas à la source de la version 1.';
$string['verify_responsecount'] = 'Utilisateur {$a->userid} : le nombre de réponses migrées est {$a->actual}, {$a->expected} était attendu.';
$string['verify_solutionmismatch'] = 'Blanc {$a->gapkey} : la solution est « {$a->actual} », « {$a->expected} » était attendu.';
$string['verify_transcriptmismatch'] = 'Séquence {$a} : la transcription ne correspond pas à la source de la version 1.';
$string['verify_unexpectedhint'] = 'Blanc {$a} : la version 1 n\'autorisait pas l\'aide ici, mais un indice a été migré.';
