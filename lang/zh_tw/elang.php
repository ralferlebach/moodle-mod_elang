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
 * Traditional Chinese strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * No 1.3.5 translation existed to follow, so terminology comes from Moodle's own
 * Traditional Chinese pack — 參與者, 嘗試, 活動, 字幕, 提示, 課程.
 *
 * Counts keep the shape used everywhere else: "片段：{$a}".
 *
 * **Written independently of zh_cn, not converted from it.** The two differ in
 * vocabulary as well as script: 影片 against 视频, 檔案 against 文件, 軟體
 * against 软件, 預設 against 默认, 逐字稿 against 文字稿. Running a character
 * converter over the Simplified pack would produce text that is technically
 * readable and obviously foreign to a reader in Taiwan.
 *
 * A gap is 空格; transcript is 逐字稿, which is the term used in Taiwan for a
 * verbatim written record of speech.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = '允許的內容語言';
$string['allowedlanguages_desc'] = '建立或編輯影片聽寫時提供的內容語言。一個都不選，就會列出完整語言清單。即使你之後從這裡移除某個語言，活動仍會保留已儲存的語言。';
$string['allowtranscriptdownload'] = '參與者下載逐字稿';
$string['allowtranscriptdownload_help'] = '開啟後，參與者可以把隱藏了每個空格的逐字稿練習單下載為 PDF、Word、OpenDocument 或純文字。

預設為關閉。有權限的教學人員一律可以下載逐字稿，與此設定無關。';
$string['allowtranscriptdownload_label'] = '參與者可以下載練習單';
$string['completiondetail_completionfinishattempt'] = '完成一次嘗試';
$string['completionfinishattempt'] = '參與者必須完成一次嘗試';
$string['cuepausemode'] = '字幕結束時暫停';
$string['cuepausemode_auto'] = '自動';
$string['cuepausemode_help'] = '媒體是否在字幕結束處停下。

* 自動 —— 播放會繼續，只有在正處理該段字幕時才會在其結束處停下，也就是點了該字幕或它的某個空格之後，或鍵盤焦點落在其中之一時。
* 在每段未作答的字幕處停止 —— 只要字幕中還有空著的空格，播放就會在其結束處停下，等待繼續。
* 從不停止 —— 播放一直進行到媒體結束。

前兩種都不會在空格已全部填好的字幕處停下：那是已完成的工作，停在那裡只會要求一次毫無作用的按鍵。這也表示第二遍做練習時，只會在仍有缺漏的地方停下。';
$string['cuepausemode_nostop'] = '從不停止';
$string['cuepausemode_stop'] = '在每段未作答的字幕處停止';
$string['editcontent'] = '編輯內容';
$string['editor_addcue'] = '新增片段';
$string['editor_addgap'] = '用所選文字建立空格';
$string['editor_addhint'] = '新增提示';
$string['editor_addvariant'] = '新增變體';
$string['editor_advanced'] = '進階設定';
$string['editor_algoexact'] = '完全相符';
$string['editor_algorithm'] = '答案比對';
$string['editor_algowordrecognized'] = '接受相近的答案';
$string['editor_answers'] = '接受的變體';
$string['editor_autosaved'] = '所有變更皆已儲存。';
$string['editor_autosaveerror'] = '自動儲存失敗，請用「儲存」重試。';
$string['editor_captureend'] = '以播放位置設定結束時間';
$string['editor_capturestart'] = '以播放位置設定開始時間';
$string['editor_cueactions'] = '片段操作';
$string['editor_cuecount'] = '片段：{$a}';
$string['editor_cuenotsaved'] = '未儲存';
$string['editor_currentmedia'] = '目前的媒體：';
$string['editor_deletecue'] = '刪除片段';
$string['editor_deletegap'] = '刪除空格';
$string['editor_emptytranscript'] = '（尚無文字）';
$string['editor_endtime'] = '結束時間';
$string['editor_formatsubrip'] = 'SubRip (.srt)';
$string['editor_formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor_gapcount'] = '空格：{$a}';
$string['editor_gaphasalternatives'] = '有可接受的變體';
$string['editor_gaphashints'] = '有提示';
$string['editor_gapmode_exact'] = '完全相符';
$string['editor_gapmode_wordrecognized'] = '接受相近答案';
$string['editor_gaprange'] = '空格位置（字元）';
$string['editor_gapunplaceable_outofrange'] = '該空格位於文字之外，無法在原位顯示。';
$string['editor_gapunplaceable_overlap'] = '該空格與另一個重疊，無法在原位顯示。';
$string['editor_gotomedia'] = '前往「媒體」';
$string['editor_heading'] = '編輯字幕與空格';
$string['editor_hints'] = '提示';
$string['editor_hinttext'] = '提示文字';
$string['editor_hinttype'] = '類型';
$string['editor_hinttype_firstletter'] = '首字母';
$string['editor_hinttype_partial'] = '部分顯示';
$string['editor_hinttype_solution'] = '答案';
$string['editor_hinttype_text'] = '自由文字';
$string['editor_hinttype_translation'] = '翻譯';
$string['editor_hinttype_wordlength'] = '詞長';
$string['editor_import'] = '匯入字幕';
$string['editor_importappend'] = '附加到現有片段';
$string['editor_importapply'] = '匯入';
$string['editor_importcancel'] = '取消';
$string['editor_importcheck'] = '檢查內容';
$string['editor_importchecking'] = '檢查中…';
$string['editor_importcuecount'] = '找到的片段';
$string['editor_importduration'] = '長度';
$string['editor_importedcues'] = '已匯入片段：{$a}';
$string['editor_importfilehint'] = '請選擇含有字幕的 WebVTT (.vtt) 或 SubRip (.srt) 檔案。';
$string['editor_importformat'] = '格式';
$string['editor_importfromfile'] = '上傳檔案';
$string['editor_importfromtext'] = '貼上文字';
$string['editor_importgapcount'] = '找到的空格';
$string['editor_importhint'] = '貼上 WebVTT 或 SubRip 內容，再以片段匯入。';
$string['editor_importparseerror'] = '無法將此內容讀取為 WebVTT 或 SubRip。';
$string['editor_importpastedtext'] = '貼上的文字';
$string['editor_importreaderror'] = '無法讀取該檔案。';
$string['editor_importready'] = '可以匯入';
$string['editor_importreplace'] = '取代所有片段';
$string['editor_importreplacedcues'] = '片段已取代；新匯入：{$a}';
$string['editor_importsource'] = '來源';
$string['editor_importsummary'] = '找到的內容';
$string['editor_importtoolarge'] = '此檔案為 {$a->size}；匯入最多接受 {$a->max}。';
$string['editor_importwrongtype'] = '請選擇字幕檔案（{$a}）。';
$string['editor_insertafter'] = '在其後插入片段';
$string['editor_insertbefore'] = '在其前插入片段';
$string['editor_invalidtime'] = '請以 mm:ss.SSS 格式輸入時間，例如 01:05.400。';
$string['editor_linkurl'] = '查詢用連結';
$string['editor_linkurl_help'] = '顯示在空格旁，作為查詢該詞的去處。若不提供連結，請留空。';
$string['editor_loaderror'] = '無法載入編輯器，請重新整理頁面。';
$string['editor_loading'] = '正在載入編輯器…';
$string['editor_maxlength'] = '最大長度';
$string['editor_maxlength_help'] = '限制參與者可以輸入多少內容。0 表示不限。';
$string['editor_media'] = '媒體';
$string['editor_mediafile'] = '已上傳的檔案';
$string['editor_mediakind'] = '媒體類型';
$string['editor_medianone'] = '無';
$string['editor_mediaprovider'] = '提供者';
$string['editor_mediaproviderref'] = '提供者參照';
$string['editor_mediaproviderrefhint'] = '影片 ID 或連結，常見形式皆可（例如 youtu.be/…）。';
$string['editor_mediasaved'] = '媒體已儲存。';
$string['editor_mediaurl'] = '媒體直接網址';
$string['editor_nocues'] = '尚無片段。請新增一個，或匯入字幕。';
$string['editor_nocueselected'] = '請從清單中選擇要編輯的片段。';
$string['editor_nocuesmatch'] = '沒有符合此搜尋的片段。';
$string['editor_nogaps'] = '沒有空格';
$string['editor_nomedia'] = '無';
$string['editor_nomedianotice'] = '請先在「媒體」分頁新增影片或音訊檔案。字幕依媒體計時，因此編輯器必須先有媒體，才能處理片段與空格。';
$string['editor_novideotrack'] = '此瀏覽器無法解碼該媒體的視訊軌（只播放聲音），參與者會看到黑畫面。請將檔案重新編碼為 H.264/MP4（例如使用 ffmpeg 或 HandBrake），再重新上傳。';
$string['editor_onboardinggaps'] = '在片段中選取一個詞，把它變成空格。';
$string['editor_onboardingimport'] = '匯入 WebVTT 或 SubRip 字幕，或手動新增片段。';
$string['editor_onboardingintro'] = '三個步驟建立一份練習：';
$string['editor_onboardingmedia'] = '選擇媒體（上傳、網址或提供者）。';
$string['editor_onboardingtitle'] = '開始建立練習';
$string['editor_onlywarnings'] = '只顯示有警告的片段';
$string['editor_parsegaps'] = '辨識空格標記：[詞] 產生允許提示的空格，{詞} 產生不含提示的空格。';
$string['editor_penalty'] = '扣分';
$string['editor_poster'] = '封面圖片';
$string['editor_preview'] = '參與者預覽';
$string['editor_problem_afterduration'] = '結束時間晚於媒體的結尾。';
$string['editor_problem_endbeforestart'] = '結束時間與開始時間相同或早於開始時間。';
$string['editor_problem_negativestart'] = '開始時間早於錄製的開頭。';
$string['editor_publish'] = '發布';
$string['editor_publishblocked'] = '有些字幕還無法儲存。請在發布前修正。';
$string['editor_published'] = '該版本已發布。';
$string['editor_removehint'] = '移除提示';
$string['editor_removevariant'] = '移除';
$string['editor_repaircue'] = '修正結束時間';
$string['editor_ruleapplied'] = '依規則建立了 %count% 個空格。';
$string['editor_ruleapply'] = '套用 %count% 個空格';
$string['editor_ruleerror'] = '無法產生空格。';
$string['editor_ruleeverynth'] = '每隔 n 個詞';
$string['editor_rulefound'] = '規則找到 %count% 個空格。';
$string['editor_rulegenerate'] = '產生空格';
$string['editor_ruleinterval'] = '間隔（n）';
$string['editor_ruletype'] = '空格規則';
$string['editor_rulewordlist'] = '要隱藏的詞';
$string['editor_rulewords'] = '詞彙清單';
$string['editor_save'] = '儲存草稿';
$string['editor_saved'] = '草稿已儲存。';
$string['editor_savedwithproblems'] = '能儲存的已儲存；有些字幕需要處理。';
$string['editor_saveerror'] = '無法儲存草稿。';
$string['editor_savemedia'] = '儲存媒體';
$string['editor_saving'] = '正在儲存…';
$string['editor_searchcues'] = '搜尋片段';
$string['editor_selecttext'] = '請先在逐字稿中選取要隱藏的詞。';
$string['editor_solution'] = '答案';
$string['editor_starttime'] = '開始時間';
$string['editor_transcript'] = '逐字稿';
$string['editor_unsaved'] = '有未儲存的變更';
$string['editor_uploadmedia'] = '上傳媒體檔案';
$string['editor_variantisregex'] = '將 {$a} 視為正規表示式';
$string['editor_variantmatching'] = '接受的變體如何比對';
$string['editor_warnemptysolution'] = '有空格沒有答案';
$string['editor_warnnotranscript'] = '沒有文字';
$string['editor_warntiming'] = '結束時間未晚於開始時間';
$string['editor_waveform'] = '音訊波形';
$string['elang:addinstance'] = '新增影片聽寫';
$string['elang:attempt'] = '進行影片聽寫';
$string['elang:deleteattempts'] = '刪除參與者的嘗試';
$string['elang:exportreports'] = '匯出含個人資料的報表';
$string['elang:exportsolution'] = '匯出含答案的完整逐字稿';
$string['elang:exporttranscript'] = '將練習單匯出為文件';
$string['elang:manage'] = '建立與編輯練習內容';
$string['elang:useregex'] = '在接受的答案中使用正規表示式';
$string['elang:view'] = '檢視影片聽寫';
$string['elang:viewreports'] = '檢視參與者報表';
$string['error_attemptnotinprogress'] = '此次嘗試已不在進行中。';
$string['error_couldnotobtainlock'] = '未能取得此操作的鎖定，請重試。';
$string['error_draftrevisionmismatch'] = '此草稿在你載入之後已變更。請重新載入後再試。';
$string['error_duplicatecuekey'] = '兩個片段共用鍵值「{$a}」；每個片段都需要唯一的鍵值。';
$string['error_duplicategapkey'] = '同一片段中的兩個空格共用鍵值「{$a}」；每個空格都需要唯一的鍵值。';
$string['error_duplicatehintlevel'] = '某個空格在第 {$a} 級有兩則提示；每一級都必須唯一。';
$string['error_gapnotinattemptversion'] = '此空格不屬於本次嘗試所對應的練習版本。';
$string['error_importnocues'] = '無法從此內容讀出任何字幕。WebVTT 或 SubRip 檔案在每段字幕上方都有一行時間，例如 00:00:01.000 --> 00:00:04.000。';
$string['error_importnotutf8'] = '此檔案不是有效的 UTF-8。很可能是以較舊的編碼儲存的——請用文字編輯器開啟並另存為 UTF-8。';
$string['error_importtoolarge'] = '此檔案為 {$a->size}；匯入最多接受 {$a->max}。一堂課錄影的字幕檔要小得多，因此這多半不是字幕檔。';
$string['error_importtoomanycues'] = '此檔案含有 {$a->count} 段字幕；匯入最多接受 {$a->max} 段。';
$string['error_invalidcuepausemode'] = '請在提供的選項中選擇字幕結束時的暫停方式。';
$string['error_invalidgradingalgorithm'] = '評分演算法「{$a}」既不是 exact 也不是 wordrecognized。';
$string['error_invalidhinttype'] = '提示類型「{$a}」不屬於允許的類型。';
$string['error_invalidisregex'] = '變體的正規表示式標記必須為 0 或 1。';
$string['error_invalidmediakind'] = '所選媒體類型不是 file、url 或 provider。';
$string['error_invalidpenalty'] = '提示扣分必須介於 0 與 1 之間。';
$string['error_invalidproviderref'] = '「{$a}」不是此提供者可辨識的影片 ID 或連結。';
$string['error_invalidregexpattern'] = '「{$a}」不是有效的正規表示式。';
$string['error_invalidsolutionavailability'] = '請在提供的選項中選擇參與者何時可以看到含答案的逐字稿。';
$string['error_invalidsourceurl'] = '請輸入以 http:// 或 https:// 開頭的完整網址，或 YouTube、Vimeo 連結。';
$string['error_invalidsubtitleposition'] = '請在提供的選項中選擇字幕的顯示位置。';
$string['error_invalidv1cuejson'] = '無法處理版本 1 的此片段。';
$string['error_negativegapoffset'] = '空格的位置與長度不得為負數。';
$string['error_noaccesstoattempt'] = '你沒有存取此次嘗試的權限。';
$string['error_nomorehints'] = '此空格沒有更多提示了。';
$string['error_nopublishedversion'] = '此練習尚無已發布的內容。';
$string['error_responsetoolong'] = '你的答案太長了。此空格最多 {$a} 個字元。';
$string['error_solutionnotavailable'] = '在此活動中，你無法檢視含答案的逐字稿。';
$string['error_staleattemptstate'] = '你看到的這次嘗試已不是最新狀態。請重新載入目前狀態後再試。';
$string['error_transcriptnotavailable'] = '此活動沒有可下載的逐字稿。';
$string['error_unknowngaprule'] = '未知的空格規則類型「{$a}」。';
$string['error_unknownmediaprovider'] = '「{$a}」不屬於支援的媒體提供者。';
$string['error_versionnotadraft'] = '只有處於草稿狀態的版本才能編輯。';
$string['error_versionnotfound'] = '此練習版本已不存在。';
$string['error_versionnotpublishable'] = '此版本無法發布：{$a}';
$string['export_audienceaftersubmission'] = '參與者完成一次嘗試後可以下載';
$string['export_audiencealways'] = '參與者隨時可以下載';
$string['export_audiencestaff'] = '僅限有權限的教學人員，不提供給參與者';
$string['export_docx'] = '下載為 Word（DOCX）';
$string['export_downloadpdf'] = '下載 PDF';
$string['export_heading'] = '匯出逐字稿';
$string['export_intro'] = '以多種格式下載此練習的逐字稿。';
$string['export_moreformats'] = '更多格式';
$string['export_nocontent'] = '尚無可匯出的已發布逐字稿。';
$string['export_odt'] = '下載為 OpenDocument（ODT）';
$string['export_pdf'] = '下載為 PDF';
$string['export_solution'] = '含答案的逐字稿';
$string['export_solutionhint'] = '顯示每個空格答案的完整文字。';
$string['export_text'] = '下載為純文字';
$string['export_versionnote'] = '匯出以此練習目前已發布的版本為準。';
$string['export_worksheet'] = '練習單（隱藏空格）';
$string['export_worksheethint'] = '隱藏了每個空格的文字，可直接作為參與者教材發放。';
$string['exporttranscript'] = '匯出逐字稿';
$string['filearea_media'] = '媒體';
$string['filearea_poster'] = '封面圖片';
$string['gradingheading'] = '答案評分';
$string['import_badtiming'] = '無法讀取時間行：{$a}';
$string['import_emptytranscript'] = '已略過一個沒有文字的片段。';
$string['import_warnlinetoolong'] = '已略過第 {$a->block} 區塊：其中有一行超過 {$a->max} 個字元，這並不是字幕行。';
$string['jarothreshold'] = '相似度門檻';
$string['jarothreshold_help'] = '對於設為「接受相近的答案」的空格，這是預期答案與所輸入答案之間的最低 Jaro 相似度。設為 1 表示在依語言正規化之後要求完全相符；數值越低，越能接受差異較大的寫法。';
$string['jarothresholdrange'] = '門檻必須介於 0 與 1 之間。';
$string['language'] = '內容語言';
$string['language_help'] = '選擇練習內容的語言。它決定答案如何比對，包括大小寫處理與音譯。若不需要依語言處理，請選擇「通用（未指定）」。新的內容版本以此設定為起點。';
$string['language_none'] = '通用（未指定）';
$string['media_cuenote'] = '更換媒體時，既有的字幕與空格會保留。它們的時間不會自動調整，請隨後在編輯器中檢查。';
$string['media_current'] = '目前的媒體';
$string['media_heading'] = '媒體';
$string['media_intro'] = '請選擇此練習所依據的影片或音訊。字幕依它計時，因此這是第一步。';
$string['media_none'] = '此練習尚未設定媒體。';
$string['media_othersource'] = '其他來源';
$string['media_providerhint'] = '可辨識的提供者：{$a}。其他網址會當作媒體直接網址使用。';
$string['media_sourceurl'] = '媒體網址';
$string['media_sourceurl_help'] = '貼上影片網址，而不是上傳檔案——可以是 YouTube 或 Vimeo 連結，也可以是媒體檔案的直接網址。

在此填入的網址會取代已上傳的檔案。若要使用上面的上傳，請留空。

提供者的影片會在提供者自己的框架中播放，而該框架不會回報播放時間。因此這類練習一律把字幕顯示在媒體下方，也不會在字幕結束處停下。

**資料會送到哪裡。** YouTube 或 Vimeo 的框架會讓每位參與者的瀏覽器連到該公司，該公司因此取得參與者的 IP 位址與裝置資訊。預設情況下，練習會先徵求同意。如果你所屬機構自建媒體伺服器（Opencast、Panopto、Kaltura 或類似系統），請改為貼上其中檔案的直接網址：它會被當作一般媒體網址處理，保留你選擇的字幕位置與暫停設定，也不會有第三方介入。';
$string['migratev1_approvalheading'] = '已移轉，待查核';
$string['migratev1_approvebutton'] = '核准這次移轉';
$string['migratev1_approved'] = '影片聽寫 {$a} 已標記為已核准。';
$string['migratev1_colactivity'] = '活動';
$string['migratev1_colalgorithm'] = '評分演算法';
$string['migratev1_colcues'] = '片段';
$string['migratev1_colgaps'] = '空格';
$string['migratev1_colissues'] = '問題';
$string['migratev1_collearners'] = '參與者';
$string['migratev1_confirmdecommission'] = '此操作會無法復原地刪除版本 1 的舊資料表與 elang.options 欄位。無法復原。要繼續嗎？';
$string['migratev1_confirmmigrate'] = '此操作會排入一個背景工作，為上列每個活動寫入版本 2 的資料。版本 1 的資料表與 elang.options 維持不變。要繼續嗎？';
$string['migratev1_decommissionblocked'] = '刪除仍被阻擋，請看下面的清單。';
$string['migratev1_decommissionblockedintro'] = '在滿足以下條件之前，刪除會被阻擋：';
$string['migratev1_decommissionbutton'] = '刪除版本 1 的舊資料';
$string['migratev1_decommissioned'] = '版本 1 的舊資料已刪除。';
$string['migratev1_decommissionheading'] = '停用版本 1 的資料';
$string['migratev1_decommissionready'] = '版本 1 的所有活動都已移轉並獲核准。現在可以刪除舊資料表與 elang.options。此操作無法復原。';
$string['migratev1_heading'] = '移轉版本 1 的活動';
$string['migratev1_migratebutton'] = '移轉這些活動';
$string['migratev1_noissues'] = '無';
$string['migratev1_nonepending'] = '沒有等待移轉的版本 1 活動。';
$string['migratev1_nonependingapproval'] = '沒有等待查核的已移轉活動。';
$string['migratev1_notablespresent'] = '在本網站找不到版本 1 的舊資料表，沒有需要移轉的內容。';
$string['migratev1_parseerrorcount'] = '無法處理的片段：{$a}';
$string['migratev1_pendingheading'] = '尚未移轉';
$string['migratev1_queued'] = '移轉工作已排入佇列。它會在下一次 cron 執行時執行，或透過 admin/cli/adhoc_task.php --execute 立即執行。';
$string['migratev1_verifiedclean'] = '已查核：移轉後的資料與版本 1 的原始資料完全一致，沒有差異。';
$string['migratev1_verifieddiscrepancies'] = '查核發現與版本 1 原始資料有差異：{$a}';
$string['migratev1_verifyfailed'] = '無法查核此活動：{$a}';
$string['modulename'] = '影片聽寫';
$string['modulename_help'] = '影片聽寫活動讓參與者一邊觀看或聆聽影片，一邊填寫帶時間碼字幕中的空格。

教師匯入 WebVTT 或 SubRip 字幕檔案，把詞或片語標記為空格，並設定答案比對的嚴格程度。參與者逐段完成逐字稿，可以要求計分的提示，並立即得到回饋。';
$string['modulenameplural'] = '影片聽寫';
$string['nav_exportshort'] = '匯出';
$string['nav_media'] = '媒體';
$string['nav_reports'] = '嘗試';
$string['nav_subtitles'] = '字幕與空格';
$string['noinstances'] = '本課程中沒有影片聽寫。';
$string['overview_attempts'] = '嘗試';
$string['playbackheading'] = '播放與字幕';
$string['playbackoverlayhint'] = '疊在畫面上的字幕只會顯示正在播放的那一段，因此在仍有空格待填的字幕結束處，播放一定會停下。這裡沒有可選項目。';
$string['playbackproviderhint'] = 'YouTube 或 Vimeo 的影片由提供者在自己的框架中播放，而該框架不會回報播放時間。因此這類練習一律把字幕顯示在媒體下方，不論上面選了什麼都不會在字幕結束處停下。上傳的檔案與媒體直接網址則會遵循這兩項設定。';
$string['player_check'] = '檢查答案';
$string['player_consentaccept'] = '從 {$a} 載入影片';
$string['player_consentdetail'] = '播放會讓你的瀏覽器連到 {$a}。{$a} 會取得你的 IP 位址與裝置資訊，也可能讀取它先前設下的 Cookie。在你選擇載入影片之前，不會送出任何內容。';
$string['player_consentheading'] = '此影片由 {$a} 提供';
$string['player_exitfullscreen'] = '離開全螢幕';
$string['player_finish'] = '結束本次嘗試';
$string['player_finished'] = '本次嘗試已結束。得分：%score%%';
$string['player_finishincomplete'] = '仍有空格未填：{$a}。仍要結束本次嘗試嗎？';
$string['player_fullscreen'] = '全螢幕';
$string['player_gaplabel'] = '空格 %gap%';
$string['player_gaplink'] = '開啟連結';
$string['player_hint'] = '顯示提示';
$string['player_loaderror'] = '無法載入練習，請重新整理頁面。';
$string['player_loading'] = '正在載入練習…';
$string['player_nocontent'] = '尚未發布練習內容，請稍後再來。';
$string['player_novideotrack'] = '你的瀏覽器無法顯示該媒體的視訊軌，聲音仍會播放。請告知你的老師。';
$string['player_outdatedattempt'] = '自你開始這次嘗試之後，此練習已更新。你正在舊內容上繼續；結束這次嘗試後，下次就會使用更新後的練習。';
$string['player_progress'] = '已作答 {$a->total} 個空格中的 {$a->done} 個';
$string['player_ready'] = '練習已就緒。';
$string['player_scorelabel'] = '得分：%score%%';
$string['player_stateaccepted'] = '已接受';
$string['player_statecorrect'] = '正確';
$string['player_statehinted'] = '已用提示';
$string['player_stateincorrect'] = '錯誤';
$string['player_submitfailed'] = '無法儲存你的答案，請重試。';
$string['player_transcriptheading'] = '逐字稿';
$string['pluginadministration'] = '影片聽寫管理';
$string['pluginname'] = '影片聽寫';
$string['privacy_metadata_elang'] = '對每個活動，記錄是誰核准了其 1.x 內容的單向移轉。';
$string['privacy_metadata_elang_attempt'] = '對練習的每次嘗試，活動會記錄由誰在何時完成、進行到哪裡，以及如何評分。';
$string['privacy_metadata_elang_attempt_answeredgaps'] = '本次嘗試中參與者作答的空格數量。';
$string['privacy_metadata_elang_attempt_attemptnumber'] = '本次嘗試在該使用者與該活動下的序號。';
$string['privacy_metadata_elang_attempt_correctgaps'] = '本次嘗試中被判為正確的空格數量。';
$string['privacy_metadata_elang_attempt_exactgaps'] = '本次嘗試中以字元完全相符作答的空格數量。';
$string['privacy_metadata_elang_attempt_hintedgaps'] = '本次嘗試中參與者要求提示的空格數量。';
$string['privacy_metadata_elang_attempt_score'] = '本次嘗試取得的分數。';
$string['privacy_metadata_elang_attempt_state'] = '嘗試處於進行中、已結束或已放棄。';
$string['privacy_metadata_elang_attempt_timefinish'] = '嘗試結束的時間。';
$string['privacy_metadata_elang_attempt_timemodified'] = '嘗試最後一次更新的時間。';
$string['privacy_metadata_elang_attempt_timestart'] = '嘗試開始的時間。';
$string['privacy_metadata_elang_attempt_totalgaps'] = '本次嘗試所屬練習版本中的空格總數。';
$string['privacy_metadata_elang_attempt_userid'] = '完成該嘗試的使用者 ID。';
$string['privacy_metadata_elang_attempt_versionid'] = '本次嘗試所針對的練習版本。';
$string['privacy_metadata_elang_migrationapproveduserid'] = '核准此活動自 mod_elang 1.x 移轉的使用者。保存下來以便日後查核該核准。';
$string['privacy_metadata_elang_response'] = '對參與者在一次嘗試中作答的每個空格，活動會保存答案文字及其評分結果。';
$string['privacy_metadata_elang_response_accepted'] = '該空格的答案是否被判為正確。';
$string['privacy_metadata_elang_response_hintlevel'] = '該空格曾向參與者顯示的最高提示層級。';
$string['privacy_metadata_elang_response_responsetext'] = '參與者在該空格輸入的文字。';
$string['privacy_metadata_elang_response_resultstate'] = '評分給這則答案的歸類（完全相符、辨識出詞、錯誤或留空）。';
$string['privacy_metadata_elang_response_score'] = '扣除提示分後，這則答案貢獻的分數。';
$string['privacy_metadata_elang_response_timecreated'] = '該答案首次送出的時間。';
$string['privacy_metadata_elang_response_timemodified'] = '該答案最後一次更新的時間。';
$string['privacy_metadata_elang_response_tries'] = '參與者為該空格送出答案的次數。';
$string['privacy_metadata_elang_version'] = '對每個內容版本，活動會記錄最後修改它的使用者。';
$string['privacy_metadata_elang_version_usermodified'] = '最後修改此內容版本的使用者。保存下來以便查核是誰編輯了練習內容。';
$string['privacy_provider_externallink'] = '當練習以 YouTube 或 Vimeo 的影片為基礎時，開啟它會讓參與者的瀏覽器連到該提供者。外掛本身不會送出任何內容，但這個連線是由活動引起的。是否真的發生，取決於網站的提供者同意設定以及參與者是否同意。';
$string['privacy_provider_ipaddress'] = '參與者瀏覽器所使用的 IP 位址。';
$string['privacy_provider_useragent'] = '瀏覽器送出的瀏覽器與裝置資訊。';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = '嵌入 YouTube 或 Vimeo 前先詢問';
$string['providerconsent_desc'] = '以 YouTube 或 Vimeo 影片為基礎的練習，會先顯示一段說明而不是影片，等參與者同意後才嵌入。若沒有這一步，頁面一開啟，提供者就會取得參與者的 IP 位址與瀏覽器資訊——在任何人按下播放之前。只有當你所屬機構以其他方式取得該同意時，才關閉此項。';
$string['report_actions'] = '操作';
$string['report_answered'] = '已作答';
$string['report_attemptnumber'] = '嘗試';
$string['report_back'] = '返回所有嘗試';
$string['report_correct'] = '正確';
$string['report_delete'] = '刪除';
$string['report_deleteconfirm'] = '要永久刪除這次嘗試及其所有答案嗎？此操作無法復原。';
$string['report_deleted'] = '該嘗試已刪除。';
$string['report_exact'] = '完全相符';
$string['report_export'] = '匯出';
$string['report_filterany'] = '全部';
$string['report_filterapply'] = '套用篩選';
$string['report_filterattempt'] = '嘗試編號';
$string['report_filterfrom'] = '開始於（之後）';
$string['report_filterrangeerror'] = '時間範圍的結束早於開始。';
$string['report_filterreset'] = '清除篩選';
$string['report_filterstate'] = '狀態';
$string['report_filterto'] = '開始於（之前）';
$string['report_filteruser'] = '參與者';
$string['report_finished'] = '已結束';
$string['report_heading'] = '嘗試';
$string['report_hinted'] = '用過提示';
$string['report_hints'] = '提示層級';
$string['report_kpianswered'] = '已作答';
$string['report_kpiattempts'] = '顯示的嘗試';
$string['report_kpiaverage'] = '平均分數（已結束）';
$string['report_kpicorrect'] = '已接受';
$string['report_kpiexact'] = '完全正確';
$string['report_kpifinished'] = '已結束';
$string['report_kpihinted'] = '使用了提示';
$string['report_kpihintedgaps'] = '需要提示';
$string['report_noattempts'] = '還沒有嘗試。';
$string['report_nogaps'] = '本次嘗試所用的版本沒有空格。';
$string['report_nomatchingattempts'] = '沒有符合這些篩選條件的嘗試。';
$string['report_noresponse'] = '未作答';
$string['report_response'] = '答案';
$string['report_result'] = '結果';
$string['report_result_empty'] = '留空';
$string['report_result_exact'] = '完全相符';
$string['report_result_incorrect'] = '錯誤';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = '辨識出詞';
$string['report_score'] = '分數';
$string['report_solution'] = '答案';
$string['report_started'] = '開始時間';
$string['report_state'] = '狀態';
$string['report_state_abandoned'] = '已放棄';
$string['report_state_finished'] = '已結束';
$string['report_state_inprogress'] = '進行中';
$string['report_transcript'] = '逐字稿';
$string['report_tries'] = '作答次數';
$string['report_user'] = '參與者';
$string['report_view'] = '檢視';
$string['reports'] = '報表';
$string['resetattempts'] = '刪除所有參與者的嘗試與答案';
$string['solutionavailability'] = '提供給參與者的含答案逐字稿';
$string['solutionavailability_aftersubmission'] = '結束本次嘗試之後';
$string['solutionavailability_always'] = '隨時';
$string['solutionavailability_help'] = '參與者何時可以下載顯示每個空格答案的完整逐字稿。

* 從不 —— 只有教師可以下載。
* 結束本次嘗試之後 —— 參與者在此活動中完成一次嘗試後即可下載。
* 隨時 —— 參與者在作答前也可以下載。

有權限的教學人員一律可以下載，與此設定無關。';
$string['solutionavailability_never'] = '從不';
$string['subplugintype_elangscript'] = '文字系統處理器';
$string['subplugintype_elangscript_plural'] = '文字系統處理器';
$string['subtitleposition'] = '字幕顯示方式';
$string['subtitleposition_below'] = '媒體下方';
$string['subtitleposition_help'] = '互動式字幕顯示在哪裡。

* 媒體下方 —— 整份逐字稿位於媒體下方的獨立捲動區域，並跟著播放走。
* 影片內（上方或下方）—— 只把正在播放的那段字幕疊在媒體上。

只有音訊的媒體沒有可供疊加的畫面，因此一律使用媒體下方的顯示方式。設定本身會保留，活動一旦改用影片就會再次生效。';
$string['subtitleposition_overlaybottom'] = '影片內 — 下方';
$string['subtitleposition_overlaytop'] = '影片內 — 上方';
$string['task_migratev1activities'] = '移轉版本 1 的活動';
$string['transcriptheading'] = '提供給參與者的逐字稿';
$string['validate_cueafterend'] = '{$a->where}：在 {$a->endtime} 毫秒結束，晚於媒體（{$a->duration} 毫秒）。播放永遠到不了那裡。';
$string['validate_cueendbeforestart'] = '{$a}：結束時間未晚於開始時間。';
$string['validate_cuewhere'] = '片段 {$a->sortorder}（{$a->cuekey}）';
$string['validate_emptysolution'] = '{$a} 的答案是空的。';
$string['validate_hintlevels'] = '{$a} 的提示層級不是從 1 開始的連續序列。';
$string['validate_negativetime'] = '{$a}：開始時間早於錄音的起點。';
$string['validate_nocues'] = '此版本沒有片段。';
$string['validate_nogaps'] = '此版本沒有可作答的空格。';
$string['validate_nonpositivelength'] = '{$a} 的字元長度必須為正數。';
$string['validate_rangeoutside'] = '{$a} 的字元範圍超出其逐字稿。';
$string['validate_rangeoverlap'] = '{$a} 的字元範圍與另一個空格重疊。';
$string['validate_unknownalgorithm'] = '無法辨識 {$a->where} 的評分演算法「{$a->algorithm}」。';
$string['validate_where'] = '片段 {$a->cuekey} 中的空格 {$a->gapkey}';
$string['verify_algorithmmismatch'] = '空格 {$a->gapkey}：評分演算法為「{$a->actual}」，預期為「{$a->expected}」。';
$string['verify_attemptcount'] = '已移轉的嘗試數量為 {$a->actual}，預期為 1.x 中的 {$a->expected} 名不同參與者。';
$string['verify_jarothreshold'] = '答案比對門檻為 {$a->actual}，預期為 {$a->expected}。';
$string['verify_missingattempt'] = '使用者 {$a}：預期有已移轉的嘗試，但沒有找到。';
$string['verify_missingcue'] = '片段 {$a}：缺少已移轉的片段。';
$string['verify_missinggap'] = '空格 {$a}：缺少已移轉的空格。';
$string['verify_missinghint'] = '空格 {$a}：版本 1 在此允許提示，但沒有移轉任何提示。';
$string['verify_orphancue'] = '片段 {$a}：找不到對應的版本 1 片段。';
$string['verify_orphangap'] = '空格 {$a}：找不到對應的版本 1 空格。';
$string['verify_rangemismatch'] = '空格 {$a}：字元範圍與版本 1 的原始資料不一致。';
$string['verify_responsecount'] = '使用者 {$a->userid}：已移轉的答案數量為 {$a->actual}，預期為 {$a->expected}。';
$string['verify_solutionmismatch'] = '空格 {$a->gapkey}：答案為「{$a->actual}」，預期為「{$a->expected}」。';
$string['verify_transcriptmismatch'] = '片段 {$a}：逐字稿與版本 1 的原始資料不一致。';
$string['verify_unexpectedhint'] = '空格 {$a}：版本 1 在此不允許提示，卻仍移轉了一則提示。';
