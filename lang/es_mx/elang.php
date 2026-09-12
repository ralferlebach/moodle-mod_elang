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
 * Mexican Spanish strings for mod_elang — differences from es only.
 *
 * Moodle resolves es_mx against es, so anything absent here comes from the base
 * pack. Only the strings that genuinely read differently in Mexico are
 * overridden; copying all 422 would mean every later change to es had to be
 * repeated here, and the two would drift apart without anyone noticing.
 *
 * The differences are systematic rather than a matter of taste:
 *
 * - vídeo → video (no accent in Mexican usage)
 * - fichero → archivo
 * - introduzca → ingrese
 * - pulsar → hacer clic
 *
 * They were derived from the es pack by applying exactly those substitutions,
 * so a string appears here only because one of them actually occurred in it.
 * That is also why this file is short: most of the interface reads identically
 * on both sides of the Atlantic.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on — and where a native reviewer may
 * well find further differences these four rules do not catch.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages_desc'] = 'Los idiomas de contenido que se ofrecen al crear o editar un dictado en video. No seleccione ninguno para ofrecer la lista completa. Una actividad conserva el idioma almacenado aunque después lo quite de aquí.';
$string['cuepausemode_help'] = 'Si el medio se detiene al final de un subtítulo.

* Automático — la reproducción continúa y solo se detiene al final de un subtítulo mientras se está trabajando en él, es decir, tras hacer clic en él o en uno de sus huecos, o al situar en uno de ellos el foco del teclado.
* Detenerse en cada subtítulo sin responder — la reproducción se detiene al final de cada subtítulo que aún tenga un hueco vacío y espera a que se reanude.
* No detenerse nunca — la reproducción continúa hasta el final del medio.

Ninguna de las dos primeras se detiene en un subtítulo cuyos huecos estén todos rellenos: eso es trabajo terminado, y detenerse ahí pediría una pulsación de tecla sin efecto. Esto significa también que un segundo recorrido por un ejercicio solo se detiene donde todavía falta algo.';
$string['editor_importfilehint'] = 'Elija un archivo WebVTT (.vtt) o SubRip (.srt) con subtítulos.';
$string['editor_importfromfile'] = 'Subir archivo';
$string['editor_importreaderror'] = 'No se ha podido leer el archivo.';
$string['editor_importtoolarge'] = 'Este archivo ocupa {$a->size}; la importación admite como máximo {$a->max}.';
$string['editor_importwrongtype'] = 'Elija un archivo de subtítulos ({$a}).';
$string['editor_invalidtime'] = 'Ingrese un tiempo con el formato mm:ss.SSS, por ejemplo 01:05.400.';
$string['editor_mediafile'] = 'Archivo subido';
$string['editor_mediaproviderrefhint'] = 'Identificador o enlace del video en cualquier forma habitual (p. ej. youtu.be/…).';
$string['editor_nomedianotice'] = 'Añada primero el archivo de video o audio en la pestaña Medios. Los subtítulos se sincronizan con el medio, así que el editor lo necesita antes de que pueda trabajar con segmentos y huecos.';
$string['editor_novideotrack'] = 'Este navegador no puede descodificar la pista de video de este medio (solo suena el audio); los estudiantes verían una imagen en negro. Vuelva a codificar el archivo como H.264/MP4 (por ejemplo con ffmpeg o HandBrake) y súbalo de nuevo.';
$string['editor_uploadmedia'] = 'Subir archivos de medios';
$string['elang:addinstance'] = 'Añadir un nuevo dictado en video';
$string['elang:attempt'] = 'Realizar un dictado en video';
$string['elang:view'] = 'Ver un dictado en video';
$string['error_importnocues'] = 'No se ha podido leer ningún subtítulo de este contenido. Un archivo WebVTT o SubRip tiene una línea de tiempo como 00:00:01.000 --> 00:00:04.000 encima de cada subtítulo.';
$string['error_importnotutf8'] = 'Este archivo no es UTF-8 válido. Probablemente se guardó con una codificación antigua — ábralo en un editor de texto y vuelva a guardarlo como UTF-8.';
$string['error_importtoolarge'] = 'Este archivo ocupa {$a->size}; la importación admite como máximo {$a->max}. Un archivo de subtítulos de la grabación de una clase es mucho menor, así que es poco probable que lo sea.';
$string['error_importtoomanycues'] = 'Este archivo contiene {$a->count} subtítulos; la importación admite como máximo {$a->max}.';
$string['error_invalidproviderref'] = '«{$a}» no es un identificador o enlace de video reconocido para este proveedor.';
$string['error_invalidsourceurl'] = 'Ingrese una dirección completa que empiece por http:// o https://, o un enlace de YouTube o Vimeo.';
$string['media_intro'] = 'Elija el video o audio en el que se basa este ejercicio. Los subtítulos se sincronizan con él, así que esto va primero.';
$string['media_sourceurl_help'] = 'Pegue la dirección de un video en lugar de subir un archivo — un enlace de YouTube o Vimeo, o la dirección directa de un archivo de medio.

Una dirección introducida aquí sustituye al archivo subido. Déjela vacía para usar la subida de arriba.

Un video de proveedor se reproduce en el marco del propio proveedor, que no informa de su tiempo de reproducción. Un ejercicio así muestra siempre los subtítulos debajo del medio y nunca se detiene en los límites de los subtítulos.

**Adónde van los datos.** Un marco de YouTube o Vimeo conecta el navegador de cada estudiante con esa empresa, que recibe entonces su dirección IP y los datos de su dispositivo. Por omisión, el ejercicio pregunta antes de hacerlo. Si su institución dispone de su propio servidor de medios — Opencast, Panopto, Kaltura o similar — pegue en su lugar la dirección directa del archivo desde allí: se trata como una dirección de medio normal, conserva la posición de los subtítulos y el ajuste de pausa que haya elegido, y no interviene ningún tercero.';
$string['modulename'] = 'Dictado en video';
$string['modulename_help'] = 'La actividad de dictado con video permite a los estudiantes rellenar huecos en subtítulos sincronizados mientras ven o escuchan un video.

Los profesores importan un archivo de subtítulos WebVTT o SubRip, marcan palabras o expresiones como huecos y configuran con qué rigor se comparan las respuestas. Los estudiantes recorren la transcripción segmento a segmento, piden pistas penalizadas y reciben una respuesta inmediata.';
$string['modulenameplural'] = 'Dictados en video';
$string['noinstances'] = 'No hay dictados en video en este curso.';
$string['playbackproviderhint'] = 'Un video de YouTube o Vimeo lo reproduce el proveedor en su propio marco, que no informa de su tiempo de reproducción. Un ejercicio así muestra siempre los subtítulos debajo del medio y nunca se detiene en los límites de los subtítulos, sea cual sea la opción elegida arriba. Los archivos subidos y las direcciones de medio directas respetan ambos ajustes.';
$string['player_consentaccept'] = 'Cargar el video desde {$a}';
$string['player_consentdetail'] = 'Reproducirlo conecta su navegador con {$a}. {$a} recibe su dirección IP e información sobre su dispositivo, y puede leer las cookies que ya haya establecido. No se envía nada hasta que usted decida cargar el video.';
$string['player_consentheading'] = 'Este video lo proporciona {$a}';
$string['player_novideotrack'] = 'Su navegador no puede mostrar la pista de video de este medio; el audio se reproducirá igualmente. Informe a su profesor.';
$string['pluginadministration'] = 'Administración del dictado en video';
$string['pluginname'] = 'Dictado en video';
$string['privacy_provider_externallink'] = 'Cuando un ejercicio se basa en un video de YouTube o Vimeo, abrirlo conecta el navegador del estudiante con ese proveedor. El plugin no envía nada por sí mismo, pero la conexión la provoca la actividad. Que ocurra o no depende del ajuste del sitio sobre el consentimiento al proveedor y de que el estudiante lo acepte.';
$string['providerconsent_desc'] = 'Los ejercicios basados en un video de YouTube o Vimeo muestran un aviso en lugar del video y solo lo incrustan tras la aceptación del estudiante. Sin esto, el proveedor recibe la dirección IP y los datos del navegador del estudiante en cuanto se abre la página — antes de que nadie haga clic en reproducir. Desactívelo solo si su institución obtiene este consentimiento por otra vía.';
$string['subtitleposition_help'] = 'Dónde se muestran los subtítulos interactivos.

* Debajo del medio — toda la transcripción queda bajo el medio, en su propia zona de desplazamiento, siguiendo la reproducción.
* Sobre el medio, abajo o arriba — solo se dibuja sobre el medio el subtítulo que se está reproduciendo.

Un medio solo de audio no tiene imagen sobre la que dibujar, así que siempre usa la visualización debajo del medio. El ajuste en sí se conserva y vuelve a aplicarse en cuanto la actividad use un video.';
