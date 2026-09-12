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
 * Spanish strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * This is the base Spanish pack. es_mx inherits from it and carries only the
 * strings that genuinely differ, so anything not overridden there comes from
 * here — which is why this file uses peninsular conventions (fichero, vídeo
 * with the accent, introducir) and leaves the Mexican variants to es_mx.
 *
 * Terminology follows the existing AMOS translation of mod_elang 1.3.5 where it
 * exists: huecos for gaps, subtítulos, ejercicio. The string ids themselves are
 * new — 2.0 rebuilt the interface — so nothing could be carried over
 * automatically.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'Idiomas de contenido permitidos';
$string['allowedlanguages_desc'] = 'Los idiomas de contenido que se ofrecen al crear o editar una actividad eLang. No seleccione ninguno para ofrecer la lista completa. Una actividad conserva el idioma almacenado aunque después lo quite de aquí.';
$string['allowtranscriptdownload'] = 'Descarga de la transcripción por los estudiantes';
$string['allowtranscriptdownload_help'] = 'Cuando está activado, los estudiantes pueden descargar la hoja de trabajo de la transcripción, con cada hueco oculto, como PDF, Word, OpenDocument o texto.

Está desactivado por omisión. Los profesores pueden descargar la transcripción siempre, sea cual sea este ajuste.';
$string['allowtranscriptdownload_label'] = 'Los estudiantes pueden descargar la hoja de trabajo';
$string['completiondetail_completionfinishattempt'] = 'Finalizar un intento';
$string['completionfinishattempt'] = 'El estudiante debe finalizar un intento';
$string['cuepausemode'] = 'Pausa en los límites de los subtítulos';
$string['cuepausemode_auto'] = 'Automático';
$string['cuepausemode_help'] = 'Si el medio se detiene al final de un subtítulo.

* Automático — la reproducción continúa y solo se detiene al final de un subtítulo mientras se está trabajando en él, es decir, tras pulsar en él o en uno de sus huecos, o al situar en uno de ellos el foco del teclado.
* Detenerse en cada subtítulo sin responder — la reproducción se detiene al final de cada subtítulo que aún tenga un hueco vacío y espera a que se reanude.
* No detenerse nunca — la reproducción continúa hasta el final del medio.

Ninguna de las dos primeras se detiene en un subtítulo cuyos huecos estén todos rellenos: eso es trabajo terminado, y detenerse ahí pediría una pulsación de tecla sin efecto. Esto significa también que un segundo recorrido por un ejercicio solo se detiene donde todavía falta algo.';
$string['cuepausemode_nostop'] = 'No detenerse nunca';
$string['cuepausemode_stop'] = 'Detenerse en cada subtítulo sin responder';
$string['editcontent'] = 'Editar contenido';
$string['editor_addcue'] = 'Añadir segmento';
$string['editor_addgap'] = 'Crear hueco a partir de la selección';
$string['editor_addhint'] = 'Añadir pista';
$string['editor_addvariant'] = 'Añadir variante';
$string['editor_advanced'] = 'Ajustes avanzados';
$string['editor_algoexact'] = 'Coincidencia exacta';
$string['editor_algorithm'] = 'Comparación';
$string['editor_algowordrecognized'] = 'Aceptar respuestas próximas';
$string['editor_answers'] = 'Variantes aceptadas';
$string['editor_autosaved'] = 'Todos los cambios se han guardado.';
$string['editor_autosaveerror'] = 'No se pudo guardar automáticamente — use Guardar para reintentarlo.';
$string['editor_captureend'] = 'Fijar el final desde la reproducción';
$string['editor_capturestart'] = 'Fijar el inicio desde la reproducción';
$string['editor_cueactions'] = 'Acciones del segmento';
$string['editor_cuecount'] = 'Segmentos: {$a}';
$string['editor_currentmedia'] = 'Medio actual:';
$string['editor_deletecue'] = 'Eliminar segmento';
$string['editor_deletegap'] = 'Eliminar hueco';
$string['editor_emptytranscript'] = '(aún no hay texto)';
$string['editor_endtime'] = 'Fin';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = 'Huecos: {$a}';
$string['editor_gaprange'] = 'Posición del hueco (caracteres)';
$string['editor_gotomedia'] = 'Ir a Medios';
$string['editor_heading'] = 'Editor de contenido del ejercicio';
$string['editor_hints'] = 'Pistas';
$string['editor_hinttext'] = 'Texto de la pista';
$string['editor_hinttype'] = 'Tipo';
$string['editor_hinttype_firstletter'] = 'Primera letra';
$string['editor_hinttype_partial'] = 'Parcial';
$string['editor_hinttype_solution'] = 'Solución';
$string['editor_hinttype_text'] = 'Texto libre';
$string['editor_hinttype_translation'] = 'Traducción';
$string['editor_hinttype_wordlength'] = 'Longitud de la palabra';
$string['editor_import'] = 'Importar subtítulos';
$string['editor_importappend'] = 'Añadir a los segmentos existentes';
$string['editor_importapply'] = 'Importar';
$string['editor_importcancel'] = 'Cancelar';
$string['editor_importcheck'] = 'Comprobar el contenido';
$string['editor_importchecking'] = 'Comprobando…';
$string['editor_importcuecount'] = 'Segmentos encontrados';
$string['editor_importduration'] = 'Duración';
$string['editor_importedcues'] = 'Segmentos importados: {$a}';
$string['editor_importfilehint'] = 'Elija un fichero WebVTT (.vtt) o SubRip (.srt) con subtítulos.';
$string['editor_importformat'] = 'Formato';
$string['editor_importfromfile'] = 'Subir fichero';
$string['editor_importfromtext'] = 'Pegar texto';
$string['editor_importgapcount'] = 'Huecos encontrados';
$string['editor_importhint'] = 'Pegue contenido WebVTT o SubRip y luego impórtelo como segmentos.';
$string['editor_importparseerror'] = 'Este contenido no se ha podido leer como WebVTT ni SubRip.';
$string['editor_importpastedtext'] = 'Texto pegado';
$string['editor_importreaderror'] = 'No se ha podido leer el fichero.';
$string['editor_importready'] = 'Listo para importar';
$string['editor_importreplace'] = 'Sustituir todos los segmentos';
$string['editor_importreplacedcues'] = 'Segmentos sustituidos; importados de nuevo: {$a}';
$string['editor_importsource'] = 'Origen';
$string['editor_importsummary'] = 'Lo que se ha encontrado';
$string['editor_importtoolarge'] = 'Este fichero ocupa {$a->size}; la importación admite como máximo {$a->max}.';
$string['editor_importwrongtype'] = 'Elija un fichero de subtítulos ({$a}).';
$string['editor_insertafter'] = 'Insertar segmento después';
$string['editor_insertbefore'] = 'Insertar segmento antes';
$string['editor_invalidtime'] = 'Introduzca un tiempo con el formato mm:ss.SSS, por ejemplo 01:05.400.';
$string['editor_linkurl'] = 'Enlace de referencia';
$string['editor_linkurl_help'] = 'Se muestra junto al hueco como lugar donde consultar la palabra. Déjelo vacío para no ofrecer ninguno.';
$string['editor_loaderror'] = 'No se ha podido cargar el editor. Vuelva a cargar la página.';
$string['editor_loading'] = 'Cargando el editor…';
$string['editor_maxlength'] = 'Longitud máxima';
$string['editor_maxlength_help'] = 'Limita cuánto puede escribir un estudiante. 0 significa sin límite.';
$string['editor_media'] = 'Medios';
$string['editor_mediafile'] = 'Fichero subido';
$string['editor_mediakind'] = 'Tipo de medio';
$string['editor_medianone'] = 'Ninguno';
$string['editor_mediaprovider'] = 'Proveedor';
$string['editor_mediaproviderref'] = 'Referencia del proveedor';
$string['editor_mediaproviderrefhint'] = 'Identificador o enlace del vídeo en cualquier forma habitual (p. ej. youtu.be/…).';
$string['editor_mediasaved'] = 'Medio guardado.';
$string['editor_mediaurl'] = 'Dirección directa';
$string['editor_nocues'] = 'Aún no hay segmentos. Añada uno o importe subtítulos.';
$string['editor_nocueselected'] = 'Seleccione un segmento de la lista para editarlo.';
$string['editor_nocuesmatch'] = 'Ningún segmento coincide con esta búsqueda.';
$string['editor_nogaps'] = 'Sin huecos';
$string['editor_nomedia'] = 'ninguno';
$string['editor_nomedianotice'] = 'Añada primero el fichero de vídeo o audio en la pestaña Medios. Los subtítulos se sincronizan con el medio, así que el editor lo necesita antes de que pueda trabajar con segmentos y huecos.';
$string['editor_novideotrack'] = 'Este navegador no puede descodificar la pista de vídeo de este medio (solo suena el audio); los estudiantes verían una imagen en negro. Vuelva a codificar el fichero como H.264/MP4 (por ejemplo con ffmpeg o HandBrake) y súbalo de nuevo.';
$string['editor_onboardinggaps'] = 'Seleccione una palabra en un segmento y márquela como hueco.';
$string['editor_onboardingimport'] = 'Importe subtítulos WebVTT/SubRip, o añada segmentos a mano.';
$string['editor_onboardingintro'] = 'Cree un ejercicio en tres pasos:';
$string['editor_onboardingmedia'] = 'Elija un medio (subida, dirección o proveedor).';
$string['editor_onboardingtitle'] = 'Comience su ejercicio';
$string['editor_onlywarnings'] = 'Solo los segmentos con avisos';
$string['editor_parsegaps'] = 'Reconocer las marcas de hueco: [palabra] crea un hueco con pistas permitidas, {palabra} uno sin ellas.';
$string['editor_penalty'] = 'Penalización';
$string['editor_poster'] = 'Imagen de portada';
$string['editor_preview'] = 'Vista previa del estudiante';
$string['editor_publish'] = 'Publicar';
$string['editor_published'] = 'Versión publicada.';
$string['editor_removehint'] = 'Quitar la pista';
$string['editor_removevariant'] = 'Quitar';
$string['editor_ruleapplied'] = 'Se han creado %count% huecos a partir de la regla.';
$string['editor_ruleapply'] = 'Aplicar %count% huecos';
$string['editor_ruleerror'] = 'No se han podido generar los huecos.';
$string['editor_ruleeverynth'] = 'Cada n palabras';
$string['editor_rulefound'] = 'La regla ha encontrado %count% huecos.';
$string['editor_rulegenerate'] = 'Generar huecos';
$string['editor_ruleinterval'] = 'Intervalo (n)';
$string['editor_ruletype'] = 'Regla de huecos';
$string['editor_rulewordlist'] = 'Palabras que ocultar';
$string['editor_rulewords'] = 'Lista de palabras';
$string['editor_save'] = 'Guardar el borrador';
$string['editor_saved'] = 'Borrador guardado.';
$string['editor_saveerror'] = 'No se ha podido guardar el borrador.';
$string['editor_savemedia'] = 'Guardar el medio';
$string['editor_saving'] = 'Guardando…';
$string['editor_searchcues'] = 'Buscar segmentos';
$string['editor_selecttext'] = 'Seleccione primero la palabra que se ocultará en la transcripción.';
$string['editor_solution'] = 'Solución';
$string['editor_starttime'] = 'Inicio';
$string['editor_transcript'] = 'Transcripción';
$string['editor_unsaved'] = 'Cambios sin guardar';
$string['editor_uploadmedia'] = 'Subir ficheros de medios';
$string['editor_variantisregex'] = 'Tratar {$a} como una expresión regular';
$string['editor_variantmatching'] = 'Cómo se comparan las variantes aceptadas';
$string['editor_warnemptysolution'] = 'Un hueco sin solución';
$string['editor_warnnotranscript'] = 'Sin texto';
$string['editor_warntiming'] = 'El final no es posterior al inicio';
$string['editor_waveform'] = 'Forma de onda del audio';
$string['elang:addinstance'] = 'Añadir un nuevo dictado en vídeo';
$string['elang:attempt'] = 'Realizar un dictado en vídeo';
$string['elang:deleteattempts'] = 'Eliminar los intentos de los estudiantes';
$string['elang:exportreports'] = 'Exportar informes con datos personales';
$string['elang:exportsolution'] = 'Exportar la transcripción completa con las soluciones';
$string['elang:exporttranscript'] = 'Exportar la hoja de trabajo como documento';
$string['elang:manage'] = 'Crear y editar el contenido de los ejercicios';
$string['elang:useregex'] = 'Usar expresiones regulares en las respuestas aceptadas';
$string['elang:view'] = 'Ver un dictado en vídeo';
$string['elang:viewreports'] = 'Ver los informes de los estudiantes';
$string['error_attemptnotinprogress'] = 'Este intento ya no está en curso.';
$string['error_couldnotobtainlock'] = 'No se ha podido obtener un bloqueo para esta operación. Inténtelo de nuevo.';
$string['error_draftrevisionmismatch'] = 'Este borrador ha cambiado desde que lo cargó. Vuelva a cargarlo e inténtelo de nuevo.';
$string['error_duplicatecuekey'] = 'Dos segmentos comparten la clave «{$a}»; cada segmento necesita una clave única.';
$string['error_duplicategapkey'] = 'Dos huecos del mismo segmento comparten la clave «{$a}»; cada hueco necesita una clave única.';
$string['error_duplicatehintlevel'] = 'Un hueco tiene dos pistas en el nivel {$a}; cada nivel debe ser único.';
$string['error_gapnotinattemptversion'] = 'Este hueco no pertenece a la versión del ejercicio de este intento.';
$string['error_importnocues'] = 'No se ha podido leer ningún subtítulo de este contenido. Un fichero WebVTT o SubRip tiene una línea de tiempo como 00:00:01.000 --> 00:00:04.000 encima de cada subtítulo.';
$string['error_importnotutf8'] = 'Este fichero no es UTF-8 válido. Probablemente se guardó con una codificación antigua — ábralo en un editor de texto y vuelva a guardarlo como UTF-8.';
$string['error_importtoolarge'] = 'Este fichero ocupa {$a->size}; la importación admite como máximo {$a->max}. Un fichero de subtítulos de la grabación de una clase es mucho menor, así que es poco probable que lo sea.';
$string['error_importtoomanycues'] = 'Este fichero contiene {$a->count} subtítulos; la importación admite como máximo {$a->max}.';
$string['error_invalidcuepausemode'] = 'Elija una de las opciones ofrecidas para la pausa en los límites de los subtítulos.';
$string['error_invalidgradingalgorithm'] = 'El algoritmo de evaluación «{$a}» no es ni exact ni wordrecognized.';
$string['error_invalidhinttype'] = 'El tipo de pista «{$a}» no es uno de los tipos permitidos.';
$string['error_invalidisregex'] = 'El marcador de expresión regular de una variante debe ser 0 o 1.';
$string['error_invalidmediakind'] = 'El tipo de medio elegido no es file, url ni provider.';
$string['error_invalidpenalty'] = 'La penalización de una pista debe estar entre 0 y 1.';
$string['error_invalidproviderref'] = '«{$a}» no es un identificador o enlace de vídeo reconocido para este proveedor.';
$string['error_invalidregexpattern'] = '«{$a}» no es una expresión regular válida.';
$string['error_invalidsolutionavailability'] = 'Elija una de las opciones ofrecidas para cuándo pueden ver los estudiantes la transcripción con las soluciones.';
$string['error_invalidsourceurl'] = 'Introduzca una dirección completa que empiece por http:// o https://, o un enlace de YouTube o Vimeo.';
$string['error_invalidsubtitleposition'] = 'Elija una de las opciones ofrecidas para dónde se muestran los subtítulos.';
$string['error_invalidv1cuejson'] = 'No se ha podido analizar este segmento de la versión 1.';
$string['error_negativegapoffset'] = 'La posición y la longitud de un hueco no pueden ser negativas.';
$string['error_noaccesstoattempt'] = 'No tiene acceso a este intento.';
$string['error_nomorehints'] = 'No hay más pistas disponibles para este hueco.';
$string['error_nopublishedversion'] = 'Este ejercicio todavía no tiene contenido publicado.';
$string['error_responsetoolong'] = 'Su respuesta es demasiado larga. El máximo para este hueco es de {$a} caracteres.';
$string['error_solutionnotavailable'] = 'La transcripción con las soluciones no está disponible para usted en esta actividad.';
$string['error_staleattemptstate'] = 'Su vista de este intento no está actualizada. Vuelva a cargar el estado actual e inténtelo de nuevo.';
$string['error_transcriptnotavailable'] = 'No hay ninguna transcripción disponible para descargar en esta actividad.';
$string['error_unknowngaprule'] = 'Tipo de regla de huecos desconocido «{$a}».';
$string['error_unknownmediaprovider'] = '«{$a}» no es uno de los proveedores de medios admitidos.';
$string['error_versionnotadraft'] = 'Solo se puede editar una versión que sea borrador.';
$string['error_versionnotfound'] = 'Esa versión del ejercicio ya no existe.';
$string['error_versionnotpublishable'] = 'Esta versión no se puede publicar: {$a}';
$string['export_audienceaftersubmission'] = 'Los estudiantes pueden descargarlo una vez finalizado un intento';
$string['export_audiencealways'] = 'Los estudiantes pueden descargarlo en cualquier momento';
$string['export_audiencestaff'] = 'Solo personal docente con permiso — no se ofrece a los estudiantes';
$string['export_docx'] = 'Descargar como Word (DOCX)';
$string['export_downloadpdf'] = 'Descargar el PDF';
$string['export_heading'] = 'Exportar la transcripción';
$string['export_intro'] = 'Descargue la transcripción de este ejercicio en varios formatos.';
$string['export_moreformats'] = 'Más formatos';
$string['export_nocontent'] = 'Todavía no hay ninguna transcripción publicada que exportar.';
$string['export_odt'] = 'Descargar como OpenDocument (ODT)';
$string['export_pdf'] = 'Descargar como PDF';
$string['export_solution'] = 'Transcripción con soluciones';
$string['export_solutionhint'] = 'El texto completo con la solución de cada hueco visible.';
$string['export_text'] = 'Descargar como texto';
$string['export_versionnote'] = 'Las exportaciones se basan en la versión actualmente publicada de este ejercicio.';
$string['export_worksheet'] = 'Hoja de trabajo (huecos ocultos)';
$string['export_worksheethint'] = 'El texto con cada hueco oculto. Listo para repartir como material del estudiante.';
$string['exporttranscript'] = 'Exportar la transcripción';
$string['filearea_media'] = 'Medios';
$string['filearea_poster'] = 'Imagen de portada';
$string['gradingheading'] = 'Evaluación de las respuestas';
$string['import_badtiming'] = 'No se ha podido leer la línea de tiempo: {$a}';
$string['import_emptytranscript'] = 'Se ha omitido un segmento sin texto.';
$string['import_warnlinetoolong'] = 'Se ha omitido el bloque {$a->block}: contiene una línea de más de {$a->max} caracteres, lo que no es una línea de subtítulo.';
$string['jarothreshold'] = 'Umbral de similitud';
$string['jarothreshold_help'] = 'Para los huecos configurados como «Aceptar respuestas próximas», este es el parecido de Jaro mínimo entre la respuesta esperada y la escrita. El valor 1 exige una coincidencia exacta tras la normalización propia del idioma; los valores menores aceptan grafías cada vez más distintas.';
$string['jarothresholdrange'] = 'El umbral debe estar entre 0 y 1.';
$string['language'] = 'Idioma del contenido';
$string['language_help'] = 'Elija el idioma del contenido del ejercicio. Determina cómo se comparan las respuestas, incluidas las mayúsculas y la transliteración. Elija «Genérico (sin especificar)» si no debe aplicarse ningún tratamiento propio de un idioma. Las nuevas versiones de contenido parten de este ajuste.';
$string['language_none'] = 'Genérico (sin especificar)';
$string['media_cuenote'] = 'Los subtítulos y huecos existentes se conservan al cambiar de medio. Sus tiempos no se ajustan, así que revíselos después en el editor.';
$string['media_current'] = 'Medio actual';
$string['media_heading'] = 'Medios';
$string['media_intro'] = 'Elija el vídeo o audio en el que se basa este ejercicio. Los subtítulos se sincronizan con él, así que esto va primero.';
$string['media_none'] = 'Todavía no se ha definido ningún medio para este ejercicio.';
$string['media_othersource'] = 'Otro origen';
$string['media_providerhint'] = 'Proveedores reconocidos: {$a}. Cualquier otra dirección se usa como dirección de medio directa.';
$string['media_sourceurl'] = 'Dirección del origen';
$string['media_sourceurl_help'] = 'Pegue la dirección de un vídeo en lugar de subir un fichero — un enlace de YouTube o Vimeo, o la dirección directa de un fichero de medio.

Una dirección introducida aquí sustituye al fichero subido. Déjela vacía para usar la subida de arriba.

Un vídeo de proveedor se reproduce en el marco del propio proveedor, que no informa de su tiempo de reproducción. Un ejercicio así muestra siempre los subtítulos debajo del medio y nunca se detiene en los límites de los subtítulos.

**Adónde van los datos.** Un marco de YouTube o Vimeo conecta el navegador de cada estudiante con esa empresa, que recibe entonces su dirección IP y los datos de su dispositivo. Por omisión, el ejercicio pregunta antes de hacerlo. Si su institución dispone de su propio servidor de medios — Opencast, Panopto, Kaltura o similar — pegue en su lugar la dirección directa del fichero desde allí: se trata como una dirección de medio normal, conserva la posición de los subtítulos y el ajuste de pausa que haya elegido, y no interviene ningún tercero.';
$string['migratev1_approvalheading'] = 'Migradas, pendientes de revisión';
$string['migratev1_approvebutton'] = 'Aprobar esta migración';
$string['migratev1_approved'] = 'La actividad elang {$a} se ha marcado como aprobada.';
$string['migratev1_colactivity'] = 'Actividad';
$string['migratev1_colalgorithm'] = 'Algoritmo de evaluación';
$string['migratev1_colcues'] = 'Segmentos';
$string['migratev1_colgaps'] = 'Huecos';
$string['migratev1_colissues'] = 'Incidencias';
$string['migratev1_collearners'] = 'Estudiantes';
$string['migratev1_confirmdecommission'] = 'Esto elimina DE FORMA IRREVERSIBLE las tablas heredadas de la versión 1 y elang.options. No hay vuelta atrás. ¿Continuar?';
$string['migratev1_confirmmigrate'] = 'Esto pondrá en cola una tarea en segundo plano que escribirá los datos de la versión 2 para cada actividad de la lista anterior. Las tablas de la versión 1 y elang.options quedan intactas. ¿Continuar?';
$string['migratev1_decommissionblocked'] = 'La eliminación sigue bloqueada; vea la lista siguiente.';
$string['migratev1_decommissionblockedintro'] = 'La eliminación está bloqueada hasta que:';
$string['migratev1_decommissionbutton'] = 'Eliminar los datos heredados de la versión 1';
$string['migratev1_decommissioned'] = 'Se han eliminado los datos heredados de la versión 1.';
$string['migratev1_decommissionheading'] = 'Retirar los datos de la versión 1';
$string['migratev1_decommissionready'] = 'Todas las actividades de la versión 1 se han migrado y aprobado. Las tablas heredadas y elang.options ya pueden eliminarse. Esta operación es irreversible.';
$string['migratev1_heading'] = 'Migrar las actividades de la versión 1';
$string['migratev1_migratebutton'] = 'Migrar estas actividades';
$string['migratev1_noissues'] = 'Ninguna';
$string['migratev1_nonepending'] = 'No hay actividades de la versión 1 pendientes de migrar.';
$string['migratev1_nonependingapproval'] = 'No hay actividades migradas pendientes de revisión.';
$string['migratev1_notablespresent'] = 'No se han encontrado tablas heredadas de la versión 1 en este sitio. No hay nada que migrar.';
$string['migratev1_parseerrorcount'] = 'Segmentos que no se han podido analizar: {$a}';
$string['migratev1_pendingheading'] = 'Todavía sin migrar';
$string['migratev1_queued'] = 'La tarea de migración se ha puesto en cola. Se ejecutará en la siguiente pasada del cron, o de inmediato mediante admin/cli/adhoc_task.php --execute.';
$string['migratev1_verifiedclean'] = 'Verificado: los datos migrados coinciden con el origen de la versión 1 sin discrepancias.';
$string['migratev1_verifieddiscrepancies'] = 'La verificación ha encontrado discrepancias respecto al origen de la versión 1: {$a}';
$string['migratev1_verifyfailed'] = 'No se ha podido verificar esta actividad: {$a}';
$string['modulename'] = 'Dictado en vídeo';
$string['modulename_help'] = 'La actividad de dictado con vídeo permite a los estudiantes rellenar huecos en subtítulos sincronizados mientras ven o escuchan un vídeo.

Los profesores importan un fichero de subtítulos WebVTT o SubRip, marcan palabras o expresiones como huecos y configuran con qué rigor se comparan las respuestas. Los estudiantes recorren la transcripción segmento a segmento, piden pistas penalizadas y reciben una respuesta inmediata.';
$string['modulenameplural'] = 'Dictados en vídeo';
$string['nav_exportshort'] = 'Exportar';
$string['nav_media'] = 'Medios';
$string['nav_reports'] = 'Intentos';
$string['nav_subtitles'] = 'Subtítulos y huecos';
$string['noinstances'] = 'No hay dictados en vídeo en este curso.';
$string['overview_attempts'] = 'Intentos';
$string['playbackheading'] = 'Reproducción y subtítulos';
$string['playbackoverlayhint'] = 'Un subtítulo sobre la imagen muestra solo el que se está reproduciendo, por lo que la reproducción siempre se detiene al final de un subtítulo que aún tiene huecos por rellenar. Aquí no hay nada que elegir.';
$string['playbackproviderhint'] = 'Un vídeo de YouTube o Vimeo lo reproduce el proveedor en su propio marco, que no informa de su tiempo de reproducción. Un ejercicio así muestra siempre los subtítulos debajo del medio y nunca se detiene en los límites de los subtítulos, sea cual sea la opción elegida arriba. Los ficheros subidos y las direcciones de medio directas respetan ambos ajustes.';
$string['player_check'] = 'Comprobar la respuesta';
$string['player_consentaccept'] = 'Cargar el vídeo desde {$a}';
$string['player_consentdetail'] = 'Reproducirlo conecta su navegador con {$a}. {$a} recibe su dirección IP e información sobre su dispositivo, y puede leer las cookies que ya haya establecido. No se envía nada hasta que usted decida cargar el vídeo.';
$string['player_consentheading'] = 'Este vídeo lo proporciona {$a}';
$string['player_finish'] = 'Finalizar el intento';
$string['player_finished'] = 'Intento finalizado. Puntuación: %score%%';
$string['player_finishincomplete'] = 'Huecos todavía vacíos: {$a}. ¿Finalizar el intento de todos modos?';
$string['player_gaplabel'] = 'Hueco %gap%';
$string['player_gaplink'] = 'Abrir el enlace';
$string['player_hint'] = 'Mostrar una pista';
$string['player_loaderror'] = 'No se ha podido cargar el ejercicio. Vuelva a cargar la página.';
$string['player_loading'] = 'Cargando el ejercicio…';
$string['player_nocontent'] = 'Todavía no se ha publicado ningún contenido de ejercicio. Vuelva más tarde.';
$string['player_novideotrack'] = 'Su navegador no puede mostrar la pista de vídeo de este medio; el audio se reproducirá igualmente. Informe a su profesor.';
$string['player_outdatedattempt'] = 'Este ejercicio se ha actualizado desde que inició este intento. Está continuando con el contenido anterior; finalice este intento para trabajar la próxima vez con el ejercicio actualizado.';
$string['player_progress'] = '{$a->done} de {$a->total} huecos respondidos';
$string['player_ready'] = 'Ejercicio listo.';
$string['player_scorelabel'] = 'Puntuación: %score%%';
$string['player_stateaccepted'] = 'Aceptada';
$string['player_statecorrect'] = 'Correcta';
$string['player_statehinted'] = 'Pista utilizada';
$string['player_stateincorrect'] = 'Incorrecta';
$string['player_submitfailed'] = 'No se ha podido guardar su respuesta. Inténtelo de nuevo.';
$string['player_transcriptheading'] = 'Transcripción';
$string['pluginadministration'] = 'Administración del dictado en vídeo';
$string['pluginname'] = 'Dictado en vídeo';
$string['privacy_metadata_elang'] = 'Para cada actividad, el registro de quién aprobó la migración unidireccional de su contenido de la 1.x.';
$string['privacy_metadata_elang_attempt'] = 'Para cada intento de un ejercicio, la actividad almacena quién lo hizo, cuándo, hasta dónde llegó y cómo se puntuó.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'Cuántos huecos ha respondido el estudiante en este intento.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'El número de orden de este intento para el usuario y la actividad.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'Cuántos huecos se aceptaron como correctos en este intento.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'Cuántos huecos se respondieron con una coincidencia exacta de caracteres en este intento.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'Para cuántos huecos pidió el estudiante una pista en este intento.';
$string['privacy_metadata_elang_attempt_score'] = 'La puntuación obtenida en este intento.';
$string['privacy_metadata_elang_attempt_state'] = 'Si el intento está en curso, finalizado o abandonado.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'El momento en que se finalizó el intento.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'El momento de la última actualización del intento.';
$string['privacy_metadata_elang_attempt_timestart'] = 'El momento en que se inició el intento.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'El número total de huecos de la versión del ejercicio de este intento.';
$string['privacy_metadata_elang_attempt_userid'] = 'El identificador del usuario que realizó el intento.';
$string['privacy_metadata_elang_attempt_versionid'] = 'La versión del ejercicio sobre la que se realizó este intento.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'El usuario que aprobó la migración de esta actividad desde mod_elang 1.x. Se almacena para que la aprobación siga siendo auditable.';
$string['privacy_metadata_elang_response'] = 'Para cada hueco que un estudiante responde dentro de un intento, la actividad almacena el texto de la respuesta y cómo se evaluó.';
$string['privacy_metadata_elang_response_accepted'] = 'Si la respuesta se aceptó como correcta para este hueco.';
$string['privacy_metadata_elang_response_hintlevel'] = 'El nivel de pista más alto revelado al estudiante para este hueco.';
$string['privacy_metadata_elang_response_responsetext'] = 'El texto que escribió el estudiante para este hueco.';
$string['privacy_metadata_elang_response_resultstate'] = 'La clasificación que dio el evaluador a esta respuesta (exacta, palabra reconocida, incorrecta o vacía).';
$string['privacy_metadata_elang_response_score'] = 'Los puntos que aportó esta respuesta, tras aplicar cualquier penalización por pista.';
$string['privacy_metadata_elang_response_timecreated'] = 'El momento en que se envió esta respuesta por primera vez.';
$string['privacy_metadata_elang_response_timemodified'] = 'El momento de la última actualización de esta respuesta.';
$string['privacy_metadata_elang_response_tries'] = 'Cuántas veces envió el estudiante una respuesta para este hueco.';
$string['privacy_metadata_elang_version'] = 'Para cada versión de contenido, la actividad almacena qué usuario la modificó por última vez.';
$string['privacy_metadata_elang_version_usermodified'] = 'El usuario que modificó por última vez esta versión de contenido. Se almacena para poder auditar quién editó el contenido del ejercicio.';
$string['privacy_provider_externallink'] = 'Cuando un ejercicio se basa en un vídeo de YouTube o Vimeo, abrirlo conecta el navegador del estudiante con ese proveedor. El plugin no envía nada por sí mismo, pero la conexión la provoca la actividad. Que ocurra o no depende del ajuste del sitio sobre el consentimiento al proveedor y de que el estudiante lo acepte.';
$string['privacy_provider_ipaddress'] = 'La dirección IP desde la que se conecta el navegador del estudiante.';
$string['privacy_provider_useragent'] = 'Los datos de navegador y dispositivo que envía el navegador.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'Preguntar antes de incrustar YouTube o Vimeo';
$string['providerconsent_desc'] = 'Los ejercicios basados en un vídeo de YouTube o Vimeo muestran un aviso en lugar del vídeo y solo lo incrustan tras la aceptación del estudiante. Sin esto, el proveedor recibe la dirección IP y los datos del navegador del estudiante en cuanto se abre la página — antes de que nadie pulse reproducir. Desactívelo solo si su institución obtiene este consentimiento por otra vía.';
$string['report_actions'] = 'Acciones';
$string['report_answered'] = 'Respondidos';
$string['report_attemptnumber'] = 'Intento';
$string['report_back'] = 'Volver a todos los intentos';
$string['report_correct'] = 'Correctos';
$string['report_delete'] = 'Eliminar';
$string['report_deleteconfirm'] = '¿Eliminar definitivamente este intento y todas sus respuestas? No se puede deshacer.';
$string['report_deleted'] = 'El intento se ha eliminado.';
$string['report_exact'] = 'Exactos';
$string['report_export'] = 'Exportar';
$string['report_filterany'] = 'Todos';
$string['report_filterapply'] = 'Aplicar los filtros';
$string['report_filterattempt'] = 'Número de intento';
$string['report_filterfrom'] = 'Iniciado desde';
$string['report_filterrangeerror'] = 'El final del intervalo es anterior a su inicio.';
$string['report_filterreset'] = 'Borrar los filtros';
$string['report_filterstate'] = 'Estado';
$string['report_filterto'] = 'Iniciado hasta';
$string['report_filteruser'] = 'Persona';
$string['report_finished'] = 'Finalizados';
$string['report_heading'] = 'Intentos';
$string['report_hinted'] = 'Con pista';
$string['report_hints'] = 'Nivel de pista';
$string['report_kpianswered'] = 'Respondidos';
$string['report_kpiattempts'] = 'Intentos mostrados';
$string['report_kpiaverage'] = 'Puntuación media (finalizados)';
$string['report_kpicorrect'] = 'Aceptados';
$string['report_kpiexact'] = 'Exactamente correctos';
$string['report_kpifinished'] = 'Finalizados';
$string['report_kpihinted'] = 'Usaron una pista';
$string['report_kpihintedgaps'] = 'Necesitaron una pista';
$string['report_noattempts'] = 'Todavía no hay intentos.';
$string['report_nogaps'] = 'La versión sobre la que se hizo este intento no tiene huecos.';
$string['report_nomatchingattempts'] = 'Ningún intento coincide con estos filtros.';
$string['report_noresponse'] = 'Sin responder';
$string['report_response'] = 'Respuesta';
$string['report_result'] = 'Resultado';
$string['report_result_empty'] = 'Vacía';
$string['report_result_exact'] = 'Exacta';
$string['report_result_incorrect'] = 'Incorrecta';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'Reconocida';
$string['report_score'] = 'Puntuación';
$string['report_solution'] = 'Solución';
$string['report_started'] = 'Iniciado';
$string['report_state'] = 'Estado';
$string['report_state_abandoned'] = 'Abandonado';
$string['report_state_finished'] = 'Finalizado';
$string['report_state_inprogress'] = 'En curso';
$string['report_transcript'] = 'Transcripción';
$string['report_tries'] = 'Intentos';
$string['report_user'] = 'Persona';
$string['report_view'] = 'Ver';
$string['reports'] = 'Informes';
$string['resetattempts'] = 'Eliminar todos los intentos y respuestas de los estudiantes';
$string['solutionavailability'] = 'Transcripción con soluciones para los estudiantes';
$string['solutionavailability_aftersubmission'] = 'Tras finalizar el intento';
$string['solutionavailability_always'] = 'En cualquier momento';
$string['solutionavailability_help'] = 'Cuándo pueden los estudiantes descargar la transcripción completa con la solución de cada hueco visible.

* Nunca — solo los profesores pueden descargarla.
* Tras finalizar el intento — un estudiante puede descargarla una vez que haya finalizado un intento en esta actividad.
* En cualquier momento — un estudiante puede descargarla incluso antes de responder.

Los profesores pueden descargarla siempre, sea cual sea este ajuste.';
$string['solutionavailability_never'] = 'Nunca';
$string['subplugintype_elangscript'] = 'Gestor de escritura';
$string['subplugintype_elangscript_plural'] = 'Gestores de escritura';
$string['subtitleposition'] = 'Visualización de los subtítulos';
$string['subtitleposition_below'] = 'Debajo del medio';
$string['subtitleposition_help'] = 'Dónde se muestran los subtítulos interactivos.

* Debajo del medio — toda la transcripción queda bajo el medio, en su propia zona de desplazamiento, siguiendo la reproducción.
* Sobre el medio, abajo o arriba — solo se dibuja sobre el medio el subtítulo que se está reproduciendo.

Un medio solo de audio no tiene imagen sobre la que dibujar, así que siempre usa la visualización debajo del medio. El ajuste en sí se conserva y vuelve a aplicarse en cuanto la actividad use un vídeo.';
$string['subtitleposition_overlaybottom'] = 'Sobre el medio — abajo';
$string['subtitleposition_overlaytop'] = 'Sobre el medio — arriba';
$string['task_migratev1activities'] = 'Migrar las actividades de la versión 1';
$string['transcriptheading'] = 'Transcripción para los estudiantes';
$string['validate_cueafterend'] = '{$a->where}: termina en {$a->endtime} ms, después del medio ({$a->duration} ms). La reproducción nunca puede llegar hasta ahí.';
$string['validate_cueendbeforestart'] = '{$a}: el final no es posterior al inicio.';
$string['validate_cuewhere'] = 'Segmento {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'La solución de {$a} está vacía.';
$string['validate_hintlevels'] = 'Los niveles de pista de {$a} no forman una secuencia continua que empiece en 1.';
$string['validate_negativetime'] = '{$a}: el tiempo de inicio es anterior al comienzo de la grabación.';
$string['validate_nocues'] = 'La versión no tiene segmentos.';
$string['validate_nogaps'] = 'La versión no tiene huecos que responder.';
$string['validate_nonpositivelength'] = 'La longitud en caracteres de {$a} debe ser positiva.';
$string['validate_rangeoutside'] = 'El intervalo de caracteres de {$a} queda fuera de su transcripción.';
$string['validate_rangeoverlap'] = 'El intervalo de caracteres de {$a} se solapa con otro hueco.';
$string['validate_unknownalgorithm'] = 'El algoritmo de evaluación «{$a->algorithm}» de {$a->where} no se reconoce.';
$string['validate_where'] = 'el hueco {$a->gapkey} del segmento {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'Hueco {$a->gapkey}: el algoritmo de evaluación es «{$a->actual}», se esperaba «{$a->expected}».';
$string['verify_attemptcount'] = 'El número de intentos migrados es {$a->actual}, se esperaban {$a->expected} estudiantes distintos de la 1.x.';
$string['verify_jarothreshold'] = 'El umbral de comparación de respuestas es {$a->actual}, se esperaba {$a->expected}.';
$string['verify_missingattempt'] = 'Usuario {$a}: se esperaba un intento migrado, no se ha encontrado ninguno.';
$string['verify_missingcue'] = 'Segmento {$a}: falta el segmento migrado.';
$string['verify_missinggap'] = 'Hueco {$a}: falta el hueco migrado.';
$string['verify_missinghint'] = 'Hueco {$a}: la versión 1 permitía ayuda aquí, pero no se ha migrado ninguna pista.';
$string['verify_orphancue'] = 'Segmento {$a}: no se ha encontrado ningún segmento correspondiente de la versión 1.';
$string['verify_orphangap'] = 'Hueco {$a}: no se ha encontrado ningún hueco correspondiente de la versión 1.';
$string['verify_rangemismatch'] = 'Hueco {$a}: el intervalo de caracteres no coincide con el origen de la versión 1.';
$string['verify_responsecount'] = 'Usuario {$a->userid}: el número de respuestas migradas es {$a->actual}, se esperaba {$a->expected}.';
$string['verify_solutionmismatch'] = 'Hueco {$a->gapkey}: la solución es «{$a->actual}», se esperaba «{$a->expected}».';
$string['verify_transcriptmismatch'] = 'Segmento {$a}: la transcripción no coincide con el origen de la versión 1.';
$string['verify_unexpectedhint'] = 'Hueco {$a}: la versión 1 no permitía ayuda aquí, pero se ha migrado una pista.';
