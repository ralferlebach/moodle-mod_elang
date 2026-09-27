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
 * Persian strings for mod_elang.
 *
 * Not shipped: .gitattributes keeps every language pack except English out of
 * the release archive. This file exists to be uploaded to AMOS, where the
 * translator community owns it from then on.
 *
 * No 1.3.5 translation existed to follow, so terminology comes from Moodle's own
 * Persian pack — شرکت‌کننده, تلاش, فعالیت, زیرنویس, راهنمایی, درس.
 *
 * Persian is right-to-left like Arabic but a different language; nothing here
 * was carried over from the Arabic pack. Where a string mixes Latin tokens —
 * WebVTT, H.264/MP4, a timestamp — a right-to-left mark precedes them so the
 * bidirectional layout does not flip, the same care taken in the Arabic pack.
 *
 * A gap is جای خالی; transcript is رونوشت.
 *
 * Strings not yet translated fall back to English, which is Moodle's normal
 * behaviour and makes a partial pack usable rather than broken.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedlanguages'] = 'زبان‌های مجاز محتوا';
$string['allowedlanguages_desc'] = 'زبان‌های محتوایی که هنگام ساختن یا ویرایش یک دیکتهٔ ویدیویی پیشنهاد می‌شوند. هیچ‌کدام را برنگزینید تا فهرست کامل زبان‌ها پیشنهاد شود. فعالیت زبان ذخیره‌شده‌اش را نگه می‌دارد، حتی اگر بعداً آن را از اینجا بردارید.';
$string['allowtranscriptdownload'] = 'دانلود رونوشت توسط شرکت‌کنندگان';
$string['allowtranscriptdownload_help'] = 'هنگامی که روشن باشد، شرکت‌کنندگان می‌توانند برگهٔ کار رونوشت را با پنهان‌بودن هر جای خالی به شکل ‏PDF، ‏Word، ‏OpenDocument یا متن دانلود کنند.

به‌صورت پیش‌فرض خاموش است. کادر آموزشی دارای اجازه همیشه می‌تواند رونوشت را دانلود کند، جدا از این تنظیم.';
$string['allowtranscriptdownload_label'] = 'شرکت‌کنندگان می‌توانند برگهٔ کار را دانلود کنند';
$string['completiondetail_completionfinishattempt'] = 'به پایان رساندن یک تلاش';
$string['completionfinishattempt'] = 'شرکت‌کننده باید یک تلاش را به پایان برساند';
$string['cuepausemode'] = 'توقف در پایان زیرنویس';
$string['cuepausemode_auto'] = 'خودکار';
$string['cuepausemode_help'] = 'اینکه رسانه در پایان زیرنویس بایستد یا نه.

* خودکار — پخش پی گرفته می‌شود و تنها تا زمانی که روی همان زیرنویس کار می‌شود در پایانش می‌ایستد، یعنی پس از کلیک روی آن یا روی یکی از جاهای خالی‌اش، یا هنگامی که تمرکز صفحه‌کلید در یکی از آنهاست.
* در هر زیرنویس بی‌پاسخ متوقف شود — پخش در پایان هر زیرنویسی که هنوز جای خالی نانوشته دارد می‌ایستد و چشم‌به‌راه ازسرگیری می‌ماند.
* هرگز متوقف نشود — پخش تا پایان رسانه پی گرفته می‌شود.

هیچ‌یک از دو حالت نخست در زیرنویسی که همهٔ جاهای خالی‌اش پر شده نمی‌ایستد: آن کار به پایان رسیده است و ایستادن در آنجا کلیدفشاری بی‌اثر می‌طلبد. این یعنی گذر دوم از تمرین تنها جایی می‌ایستد که هنوز چیزی کم است.';
$string['cuepausemode_nostop'] = 'هرگز متوقف نشود';
$string['cuepausemode_stop'] = 'در هر زیرنویس بی‌پاسخ متوقف شود';
$string['editcontent'] = 'ویرایش محتوا';
$string['editor_addcue'] = 'افزودن بخش';
$string['editor_addgap'] = 'ساختن جای خالی از متن انتخاب‌شده';
$string['editor_addhint'] = 'افزودن راهنمایی';
$string['editor_addvariant'] = 'افزودن گونه';
$string['editor_advanced'] = 'تنظیمات پیشرفته';
$string['editor_algoexact'] = 'تطابق دقیق';
$string['editor_algorithm'] = 'مقایسهٔ پاسخ‌ها';
$string['editor_algowordrecognized'] = 'پذیرفتن پاسخ‌های نزدیک';
$string['editor_answers'] = 'گونه‌های پذیرفته‌شده';
$string['editor_autosaved'] = 'همهٔ تغییرات ذخیره شد.';
$string['editor_autosaveerror'] = 'ذخیرهٔ خودکار انجام نشد — با دکمهٔ «ذخیره» دوباره تلاش کنید.';
$string['editor_captureend'] = 'تعیین پایان از روی پخش';
$string['editor_capturestart'] = 'تعیین آغاز از روی پخش';
$string['editor_cueactions'] = 'کارهای بخش';
$string['editor_cuecount'] = 'بخش‌ها: {$a}';
$string['editor_cuenotsaved'] = 'ذخیره نشد';
$string['editor_currentmedia'] = 'رسانهٔ کنونی:';
$string['editor_deletecue'] = 'حذف بخش';
$string['editor_deletegap'] = 'حذف جای خالی';
$string['editor_emptytranscript'] = '(هنوز متنی نیست)';
$string['editor_endtime'] = 'زمان پایان';
$string['editor_formatsubrip'] = '‏SubRip ‏(.srt)';
$string['editor_formatwebvtt'] = '‏WebVTT ‏(.vtt)';
$string['editor_gapcount'] = 'جاهای خالی: {$a}';
$string['editor_gaphasalternatives'] = 'گونه‌های پذیرفته دارد';
$string['editor_gaphashints'] = 'راهنمایی دارد';
$string['editor_gapmode_exact'] = 'تطابق دقیق';
$string['editor_gapmode_wordrecognized'] = 'پاسخ‌های نزدیک پذیرفته می‌شود';
$string['editor_gaprange'] = 'جایگاه جای خالی (نویسه)';
$string['editor_gapunplaceable_outofrange'] = 'این جای خالی بیرون از متن است و نمی‌توان آن را در جای خود نشان داد.';
$string['editor_gapunplaceable_overlap'] = 'این جای خالی با جای خالی دیگری هم‌پوشانی دارد و نمی‌توان آن را در جای خود نشان داد.';
$string['editor_gotomedia'] = 'رفتن به «رسانه»';
$string['editor_heading'] = 'ویرایش زیرنویس‌ها و جاهای خالی';
$string['editor_hints'] = 'راهنمایی‌ها';
$string['editor_hinttext'] = 'متن راهنمایی';
$string['editor_hinttype'] = 'نوع';
$string['editor_hinttype_firstletter'] = 'نخستین حرف';
$string['editor_hinttype_partial'] = 'بخشی';
$string['editor_hinttype_solution'] = 'پاسخ';
$string['editor_hinttype_text'] = 'متن آزاد';
$string['editor_hinttype_translation'] = 'ترجمه';
$string['editor_hinttype_wordlength'] = 'طول واژه';
$string['editor_import'] = 'درون‌ریزی زیرنویس‌ها';
$string['editor_importappend'] = 'افزودن به بخش‌های موجود';
$string['editor_importapply'] = 'درون‌ریزی';
$string['editor_importcancel'] = 'انصراف';
$string['editor_importcheck'] = 'بررسی محتوا';
$string['editor_importchecking'] = 'در حال بررسی…';
$string['editor_importcuecount'] = 'بخش‌های یافت‌شده';
$string['editor_importduration'] = 'مدت';
$string['editor_importedcues'] = 'بخش‌های درون‌ریزی‌شده: {$a}';
$string['editor_importfilehint'] = 'پرونده‌ای با قالب ‏WebVTT ‏(.vtt) یا ‏SubRip ‏(.srt) که زیرنویس دارد برگزینید.';
$string['editor_importformat'] = 'قالب';
$string['editor_importfromfile'] = 'بارگذاری پرونده';
$string['editor_importfromtext'] = 'چسباندن متن';
$string['editor_importgapcount'] = 'جاهای خالی یافت‌شده';
$string['editor_importhint'] = 'محتوای ‏WebVTT یا ‏SubRip را بچسبانید و آن را همچون بخش درون‌ریزی کنید.';
$string['editor_importparseerror'] = 'این محتوا نه همچون ‏WebVTT و نه همچون ‏SubRip خوانده نشد.';
$string['editor_importpastedtext'] = 'متن چسبانده‌شده';
$string['editor_importreaderror'] = 'پرونده خوانده نشد.';
$string['editor_importready'] = 'آمادهٔ درون‌ریزی';
$string['editor_importreplace'] = 'جایگزینی همهٔ بخش‌ها';
$string['editor_importreplacedcues'] = 'بخش‌ها جایگزین شد؛ تازه درون‌ریزی‌شده: {$a}';
$string['editor_importsource'] = 'خاستگاه';
$string['editor_importsummary'] = 'آنچه یافت شد';
$string['editor_importtoolarge'] = 'این پرونده {$a->size} است؛ درون‌ریزی دست‌بالا {$a->max} می‌پذیرد.';
$string['editor_importwrongtype'] = 'یک پروندهٔ زیرنویس برگزینید ({$a}).';
$string['editor_insertafter'] = 'درج بخش پس از آن';
$string['editor_insertbefore'] = 'درج بخش پیش از آن';
$string['editor_invalidtime'] = 'زمان را به شکل mm:ss.SSS وارد کنید، برای نمونه 01:05.400.';
$string['editor_linkurl'] = 'پیوند برای جست‌وجو';
$string['editor_linkurl_help'] = 'کنار جای خالی نمایش داده می‌شود تا واژه را آنجا بجویید. اگر پیوندی نمی‌خواهید، خالی بگذارید.';
$string['editor_loaderror'] = 'ویرایشگر بارگذاری نشد. صفحه را دوباره بارگذاری کنید.';
$string['editor_loading'] = 'در حال بارگذاری ویرایشگر…';
$string['editor_maxlength'] = 'بیشترین طول';
$string['editor_maxlength_help'] = 'محدود می‌کند شرکت‌کننده چقدر می‌تواند بنویسد. ۰ یعنی بدون محدودیت.';
$string['editor_media'] = 'رسانه';
$string['editor_mediafile'] = 'پروندهٔ بارگذاری‌شده';
$string['editor_mediakind'] = 'نوع رسانه';
$string['editor_medianone'] = 'هیچ';
$string['editor_mediaprovider'] = 'فراهم‌کننده';
$string['editor_mediaproviderref'] = 'ارجاع فراهم‌کننده';
$string['editor_mediaproviderrefhint'] = 'شناسه یا پیوند ویدیو به هر شکل رایج (برای نمونه ‎youtu.be/…).';
$string['editor_mediasaved'] = 'رسانه ذخیره شد.';
$string['editor_mediaurl'] = 'نشانی مستقیم رسانه';
$string['editor_nocues'] = 'هنوز بخشی نیست. یکی بیفزایید یا زیرنویس درون‌ریزی کنید.';
$string['editor_nocueselected'] = 'برای ویرایش، بخشی را از فهرست برگزینید.';
$string['editor_nocuesmatch'] = 'هیچ بخشی با این جست‌وجو همخوان نیست.';
$string['editor_nogaps'] = 'بدون جای خالی';
$string['editor_nomedia'] = 'هیچ';
$string['editor_nomedianotice'] = 'نخست پروندهٔ ویدیو یا صدا را در زبانهٔ «رسانه» بیفزایید. زیرنویس‌ها بر پایهٔ رسانه زمان‌بندی می‌شوند، پس ویرایشگر پیش از کار با بخش‌ها و جاهای خالی به آن نیاز دارد.';
$string['editor_novideotrack'] = 'این مرورگر نمی‌تواند باند ویدیویی این رسانه را رمزگشایی کند (تنها صدا پخش می‌شود)؛ شرکت‌کنندگان تصویری سیاه می‌بینند. پرونده را به ‏H.264/MP4 بازکدگذاری کنید (برای نمونه با ‏ffmpeg یا ‏HandBrake) و دوباره بارگذاری کنید.';
$string['editor_onboardinggaps'] = 'واژه‌ای را در یک بخش برگزینید و آن را به جای خالی تبدیل کنید.';
$string['editor_onboardingimport'] = 'زیرنویس ‏WebVTT یا ‏SubRip درون‌ریزی کنید، یا بخش‌ها را دستی بیفزایید.';
$string['editor_onboardingintro'] = 'در سه گام یک تمرین بسازید:';
$string['editor_onboardingmedia'] = 'رسانه‌ای برگزینید (بارگذاری، نشانی یا فراهم‌کننده).';
$string['editor_onboardingtitle'] = 'تمرین خود را آغاز کنید';
$string['editor_onlywarnings'] = 'تنها بخش‌های دارای هشدار';
$string['editor_parsegaps'] = 'شناسایی نشانه‌های جای خالی: ‏[واژه] جای خالی با راهنمایی می‌سازد و ‏{واژه} بدون آن.';
$string['editor_penalty'] = 'کسر امتیاز';
$string['editor_poster'] = 'تصویر پوستر';
$string['editor_preview'] = 'پیش‌نمایش شرکت‌کننده';
$string['editor_problem_afterduration'] = 'زمان پایان پس از پایان رسانه است.';
$string['editor_problem_endbeforestart'] = 'زمان پایان با زمان آغاز برابر است یا پیش از آن.';
$string['editor_problem_negativestart'] = 'زمان آغاز پیش از آغاز ضبط است.';
$string['editor_publish'] = 'انتشار';
$string['editor_publishblocked'] = 'برخی زیرنویس‌ها هنوز ذخیره نمی‌شوند. پیش از انتشار آنها را اصلاح کنید.';
$string['editor_published'] = 'نسخه منتشر شد.';
$string['editor_removehint'] = 'برداشتن راهنمایی';
$string['editor_removevariant'] = 'برداشتن';
$string['editor_repaircue'] = 'اصلاح زمان پایان';
$string['editor_ruleapplied'] = 'بر پایهٔ قاعده %count% جای خالی ساخته شد.';
$string['editor_ruleapply'] = 'به‌کارگیری %count% جای خالی';
$string['editor_ruleerror'] = 'جاهای خالی ساخته نشد.';
$string['editor_ruleeverynth'] = 'هر n واژه';
$string['editor_rulefound'] = 'قاعده %count% جای خالی یافت.';
$string['editor_rulegenerate'] = 'ساختن جاهای خالی';
$string['editor_ruleinterval'] = 'فاصله (n)';
$string['editor_ruletype'] = 'قاعدهٔ جاهای خالی';
$string['editor_rulewordlist'] = 'واژه‌هایی که پنهان شوند';
$string['editor_rulewords'] = 'فهرست واژه‌ها';
$string['editor_save'] = 'ذخیرهٔ پیش‌نویس';
$string['editor_saved'] = 'پیش‌نویس ذخیره شد.';
$string['editor_savedwithproblems'] = 'آنچه ذخیره‌شدنی بود ذخیره شد؛ برخی زیرنویس‌ها به رسیدگی نیاز دارند.';
$string['editor_saveerror'] = 'پیش‌نویس ذخیره نشد.';
$string['editor_savemedia'] = 'ذخیرهٔ رسانه';
$string['editor_saving'] = 'در حال ذخیره…';
$string['editor_searchcues'] = 'جست‌وجو در بخش‌ها';
$string['editor_selecttext'] = 'نخست واژه‌ای را که باید در رونوشت پنهان شود برگزینید.';
$string['editor_solution'] = 'پاسخ';
$string['editor_starttime'] = 'زمان آغاز';
$string['editor_transcript'] = 'رونوشت';
$string['editor_unsaved'] = 'تغییرات ذخیره‌نشده';
$string['editor_uploadmedia'] = 'بارگذاری پرونده‌های رسانه‌ای';
$string['editor_variantisregex'] = 'رفتار با {$a} همچون عبارت باقاعده';
$string['editor_variantmatching'] = 'گونه‌های پذیرفته‌شده چگونه مقایسه می‌شوند';
$string['editor_warnemptysolution'] = 'جای خالی بدون پاسخ';
$string['editor_warnnotranscript'] = 'بدون متن';
$string['editor_warntiming'] = 'پایان پس از آغاز نیست';
$string['editor_waveform'] = 'شکل موج صدا';
$string['elang:addinstance'] = 'افزودن دیکتهٔ ویدیویی تازه';
$string['elang:attempt'] = 'انجام دیکتهٔ ویدیویی';
$string['elang:deleteattempts'] = 'حذف تلاش‌های شرکت‌کنندگان';
$string['elang:exportreports'] = 'برون‌ریزی گزارش‌های دارای داده‌های شخصی';
$string['elang:exportsolution'] = 'برون‌ریزی رونوشت کامل همراه با پاسخ‌ها';
$string['elang:exporttranscript'] = 'برون‌ریزی برگهٔ کار همچون سند';
$string['elang:manage'] = 'ساختن و ویرایش محتوای تمرین‌ها';
$string['elang:useregex'] = 'به‌کارگیری عبارت باقاعده در پاسخ‌های پذیرفته‌شده';
$string['elang:view'] = 'دیدن دیکتهٔ ویدیویی';
$string['elang:viewreports'] = 'دیدن گزارش‌های شرکت‌کنندگان';
$string['error_attemptnotinprogress'] = 'این تلاش دیگر در جریان نیست.';
$string['error_couldnotobtainlock'] = 'برای این کار قفلی به دست نیامد. دوباره تلاش کنید.';
$string['error_draftrevisionmismatch'] = 'این پیش‌نویس از زمانی که آن را بارگذاری کردید تغییر کرده است. دوباره بارگذاری کنید و از نو تلاش کنید.';
$string['error_duplicatecuekey'] = 'دو بخش کلید «{$a}» را با هم دارند؛ هر بخش به کلیدی یکتا نیاز دارد.';
$string['error_duplicategapkey'] = 'دو جای خالی در یک بخش کلید «{$a}» را با هم دارند؛ هر جای خالی به کلیدی یکتا نیاز دارد.';
$string['error_duplicatehintlevel'] = 'یک جای خالی در سطح {$a} دو راهنمایی دارد؛ هر سطح باید یکتا باشد.';
$string['error_gapnotinattemptversion'] = 'این جای خالی به نسخهٔ تمرین این تلاش وابسته نیست.';
$string['error_importnocues'] = 'از این محتوا هیچ زیرنویسی خوانده نشد. پروندهٔ ‏WebVTT یا ‏SubRip بالای هر زیرنویس خطی زمانی مانند 00:00:01.000 --> 00:00:04.000 دارد.';
$string['error_importnotutf8'] = 'این پرونده ‏UTF-8 معتبر نیست. به احتمال زیاد با کدگذاری قدیمی‌تری ذخیره شده — آن را در یک ویرایشگر متن باز کنید و دوباره با ‏UTF-8 ذخیره کنید.';
$string['error_importtoolarge'] = 'این پرونده {$a->size} است؛ درون‌ریزی دست‌بالا {$a->max} می‌پذیرد. پروندهٔ زیرنویس یک ضبط کلاس بسیار کوچک‌تر است، پس به‌احتمال زیاد این پروندهٔ زیرنویس نیست.';
$string['error_importtoomanycues'] = 'این پرونده {$a->count} زیرنویس دارد؛ درون‌ریزی دست‌بالا {$a->max} می‌پذیرد.';
$string['error_invalidcuepausemode'] = 'برای توقف در پایان زیرنویس یکی از گزینه‌های پیشنهادی را برگزینید.';
$string['error_invalidgradingalgorithm'] = 'الگوریتم ارزیابی «{$a}» نه ‏exact است و نه ‏wordrecognized.';
$string['error_invalidhinttype'] = 'نوع راهنمایی «{$a}» از نوع‌های مجاز نیست.';
$string['error_invalidisregex'] = 'نشانهٔ عبارت باقاعده برای یک گونه باید ۰ یا ۱ باشد.';
$string['error_invalidmediakind'] = 'نوع رسانهٔ برگزیده نه ‏file است، نه ‏url و نه ‏provider.';
$string['error_invalidpenalty'] = 'کسر امتیاز راهنمایی باید میان ۰ و ۱ باشد.';
$string['error_invalidproviderref'] = '«{$a}» شناسه یا پیوند شناخته‌شدهٔ ویدیو برای این فراهم‌کننده نیست.';
$string['error_invalidregexpattern'] = '«{$a}» عبارت باقاعدهٔ معتبری نیست.';
$string['error_invalidsolutionavailability'] = 'برای اینکه شرکت‌کنندگان چه هنگام رونوشت همراه با پاسخ‌ها را ببینند، یکی از گزینه‌های پیشنهادی را برگزینید.';
$string['error_invalidsourceurl'] = 'نشانی کاملی که با ‏http:// یا ‏https:// آغاز شود، یا پیوند ‏YouTube یا ‏Vimeo وارد کنید.';
$string['error_invalidsubtitleposition'] = 'برای جایگاه نمایش زیرنویس‌ها یکی از گزینه‌های پیشنهادی را برگزینید.';
$string['error_invalidv1cuejson'] = 'این بخش از نسخهٔ ۱ پردازش نشد.';
$string['error_negativegapoffset'] = 'جایگاه و طول جای خالی نباید منفی باشد.';
$string['error_noaccesstoattempt'] = 'شما به این تلاش دسترسی ندارید.';
$string['error_nomorehints'] = 'برای این جای خالی راهنمایی دیگری نیست.';
$string['error_nopublishedversion'] = 'این تمرین هنوز محتوای منتشرشده ندارد.';
$string['error_responsetoolong'] = 'پاسخ شما بسیار بلند است. بیشینه برای این جای خالی {$a} نویسه است.';
$string['error_solutionnotavailable'] = 'رونوشت همراه با پاسخ‌ها در این فعالیت برای شما در دسترس نیست.';
$string['error_staleattemptstate'] = 'نمای شما از این تلاش به‌روز نیست. وضعیت کنونی را دوباره بارگذاری کنید و از نو تلاش کنید.';
$string['error_transcriptnotavailable'] = 'در این فعالیت رونوشتی برای دانلود نیست.';
$string['error_unknowngaprule'] = 'نوع ناشناختهٔ قاعدهٔ جاهای خالی «{$a}».';
$string['error_unknownmediaprovider'] = '«{$a}» از فراهم‌کننده‌های رسانه‌ای پشتیبانی‌شده نیست.';
$string['error_versionnotadraft'] = 'تنها نسخه‌ای که در وضعیت پیش‌نویس است ویرایش‌پذیر است.';
$string['error_versionnotfound'] = 'این نسخهٔ تمرین دیگر وجود ندارد.';
$string['error_versionnotpublishable'] = 'این نسخه منتشرشدنی نیست: {$a}';
$string['export_audienceaftersubmission'] = 'شرکت‌کنندگان پس از به پایان رساندن یک تلاش می‌توانند آن را دانلود کنند';
$string['export_audiencealways'] = 'شرکت‌کنندگان هر زمان می‌توانند آن را دانلود کنند';
$string['export_audiencestaff'] = 'تنها کادر آموزشی دارای اجازه — به شرکت‌کنندگان ارائه نمی‌شود';
$string['export_docx'] = 'دانلود به شکل ‏Word ‏(DOCX)';
$string['export_downloadpdf'] = 'دانلود ‏PDF';
$string['export_heading'] = 'برون‌ریزی رونوشت';
$string['export_intro'] = 'رونوشت این تمرین را در چند قالب دانلود کنید.';
$string['export_moreformats'] = 'قالب‌های بیشتر';
$string['export_nocontent'] = 'هنوز رونوشت منتشرشده‌ای برای برون‌ریزی نیست.';
$string['export_odt'] = 'دانلود به شکل ‏OpenDocument ‏(ODT)';
$string['export_pdf'] = 'دانلود به شکل ‏PDF';
$string['export_solution'] = 'رونوشت همراه با پاسخ‌ها';
$string['export_solutionhint'] = 'متن کامل که پاسخ هر جای خالی در آن دیده می‌شود.';
$string['export_text'] = 'دانلود همچون متن';
$string['export_versionnote'] = 'برون‌ریزی‌ها بر پایهٔ نسخهٔ هم‌اکنون منتشرشدهٔ این تمرین است.';
$string['export_worksheet'] = 'برگهٔ کار (جاهای خالی پنهان)';
$string['export_worksheethint'] = 'متن با پنهان‌بودن هر جای خالی. آمادهٔ پخش همچون برگهٔ شرکت‌کنندگان.';
$string['exporttranscript'] = 'برون‌ریزی رونوشت';
$string['filearea_media'] = 'رسانه';
$string['filearea_poster'] = 'تصویر پوستر';
$string['gradingheading'] = 'ارزیابی پاسخ‌ها';
$string['import_badtiming'] = 'خط زمانی خوانده نشد: {$a}';
$string['import_emptytranscript'] = 'بخشی بدون متن نادیده گرفته شد.';
$string['import_warnlinetoolong'] = 'بلوک {$a->block} نادیده گرفته شد: خطی بلندتر از {$a->max} نویسه دارد که خط زیرنویس نیست.';
$string['jarothreshold'] = 'آستانهٔ شباهت';
$string['jarothreshold_help'] = 'برای جاهای خالی‌ای که روی «پذیرفتن پاسخ‌های نزدیک» تنظیم شده‌اند، این کمترین شباهت ‏Jaro میان پاسخ انتظاری و پاسخ نوشته‌شده است. مقدار ۱ پس از هنجارسازی ویژهٔ زبان تطابق دقیق می‌خواهد؛ مقدارهای کمتر نوشتارهای هرچه ناهمسان‌تر را می‌پذیرند.';
$string['jarothresholdrange'] = 'آستانه باید میان ۰ و ۱ باشد.';
$string['language'] = 'زبان محتوا';
$string['language_help'] = 'زبان محتوای تمرین را برگزینید. این زبان تعیین می‌کند پاسخ‌ها چگونه مقایسه شوند، از جمله بزرگی و کوچکی حروف و حرف‌نویسی. اگر پردازش ویژهٔ زبان نمی‌خواهید، «عمومی (تعیین‌نشده)» را برگزینید. نسخه‌های تازهٔ محتوا از این تنظیم آغاز می‌کنند.';
$string['language_none'] = 'عمومی (تعیین‌نشده)';
$string['media_cuenote'] = 'هنگام تغییر رسانه، زیرنویس‌ها و جاهای خالی موجود نگه داشته می‌شوند. زمان‌بندی آنها تنظیم نمی‌شود، پس پس از آن در ویرایشگر بررسی‌شان کنید.';
$string['media_current'] = 'رسانهٔ کنونی';
$string['media_heading'] = 'رسانه';
$string['media_intro'] = 'ویدیو یا صدایی را که این تمرین بر آن استوار است برگزینید. زیرنویس‌ها بر پایهٔ آن زمان‌بندی می‌شوند، پس این گام نخست است.';
$string['media_none'] = 'برای این تمرین هنوز رسانه‌ای تعیین نشده است.';
$string['media_othersource'] = 'خاستگاه دیگر';
$string['media_providerhint'] = 'فراهم‌کننده‌های شناخته‌شده: {$a}. هر نشانی دیگری همچون نشانی مستقیم رسانه به کار می‌رود.';
$string['media_sourceurl'] = 'نشانی رسانه';
$string['media_sourceurl_help'] = 'به‌جای بارگذاری پرونده، نشانی یک ویدیو را بچسبانید — پیوند ‏YouTube یا ‏Vimeo، یا نشانی مستقیم یک پروندهٔ رسانه‌ای.

نشانی‌ای که اینجا وارد شود جای پروندهٔ بارگذاری‌شده را می‌گیرد. برای به‌کاربردن بارگذاری بالا، آن را خالی بگذارید.

ویدیوی فراهم‌کننده در قاب خود فراهم‌کننده پخش می‌شود و آن قاب زمان پخش را گزارش نمی‌دهد. چنین تمرینی همیشه زیرنویس‌ها را زیر رسانه نشان می‌دهد و در پایان زیرنویس نمی‌ایستد.

**داده‌ها به کجا می‌روند.** قاب ‏YouTube یا ‏Vimeo مرورگر هر شرکت‌کننده را به آن شرکت پیوند می‌دهد و آن شرکت نشانی ‏IP و داده‌های دستگاه او را دریافت می‌کند. به‌صورت پیش‌فرض تمرین پیش از آن می‌پرسد. اگر نهاد شما سرور رسانه‌ای خود را دارد — ‏Opencast، ‏Panopto، ‏Kaltura یا مانند آن — به‌جایش نشانی مستقیم پرونده را از آنجا بچسبانید: همچون نشانی رسانهٔ معمولی رفتار می‌شود، جایگاه زیرنویس و تنظیم توقفی را که برگزیده‌اید نگه می‌دارد و هیچ سوی سومی در کار نیست.';
$string['migratev1_approvalheading'] = 'کوچانده‌شده، در انتظار بررسی';
$string['migratev1_approvebutton'] = 'تأیید این کوچ';
$string['migratev1_approved'] = 'دیکتهٔ ویدیویی {$a} تأییدشده نشانه‌گذاری شد.';
$string['migratev1_colactivity'] = 'فعالیت';
$string['migratev1_colalgorithm'] = 'الگوریتم ارزیابی';
$string['migratev1_colcues'] = 'بخش‌ها';
$string['migratev1_colgaps'] = 'جاهای خالی';
$string['migratev1_colissues'] = 'مشکل‌ها';
$string['migratev1_collearners'] = 'شرکت‌کنندگان';
$string['migratev1_confirmdecommission'] = 'این کار جدول‌های کهنهٔ نسخهٔ ۱ و ستون ‏elang.options را به‌گونه‌ای بازگشت‌ناپذیر حذف می‌کند. بازگشتی در کار نیست. ادامه می‌دهید؟';
$string['migratev1_confirmmigrate'] = 'این کار وظیفه‌ای پس‌زمینه‌ای در صف می‌گذارد که برای هر فعالیت بالا داده‌های نسخهٔ ۲ را می‌نویسد. جدول‌های نسخهٔ ۱ و ‏elang.options دست‌نخورده می‌مانند. ادامه می‌دهید؟';
$string['migratev1_decommissionblocked'] = 'حذف هنوز بسته است؛ فهرست زیر را ببینید.';
$string['migratev1_decommissionblockedintro'] = 'حذف تا این هنگام بسته است:';
$string['migratev1_decommissionbutton'] = 'حذف داده‌های کهنهٔ نسخهٔ ۱';
$string['migratev1_decommissioned'] = 'داده‌های کهنهٔ نسخهٔ ۱ حذف شد.';
$string['migratev1_decommissionheading'] = 'از کار انداختن داده‌های نسخهٔ ۱';
$string['migratev1_decommissionready'] = 'همهٔ فعالیت‌های نسخهٔ ۱ کوچانده و تأیید شده‌اند. جدول‌های کهنه و ‏elang.options اکنون حذف‌شدنی‌اند. این کار بازگشت‌ناپذیر است.';
$string['migratev1_heading'] = 'کوچاندن فعالیت‌های نسخهٔ ۱';
$string['migratev1_migratebutton'] = 'کوچاندن این فعالیت‌ها';
$string['migratev1_noissues'] = 'هیچ';
$string['migratev1_nonepending'] = 'هیچ فعالیت نسخهٔ ۱ در انتظار کوچ نیست.';
$string['migratev1_nonependingapproval'] = 'هیچ فعالیت کوچانده‌شده‌ای در انتظار بررسی نیست.';
$string['migratev1_notablespresent'] = 'در این سایت جدول کهنه‌ای از نسخهٔ ۱ یافت نشد. چیزی برای کوچاندن نیست.';
$string['migratev1_parseerrorcount'] = 'بخش‌هایی که پردازش نشدند: {$a}';
$string['migratev1_pendingheading'] = 'هنوز کوچانده‌نشده';
$string['migratev1_queued'] = 'وظیفهٔ کوچ در صف گذاشته شد. در اجرای بعدی ‏cron یا بی‌درنگ با ‏admin/cli/adhoc_task.php --execute اجرا می‌شود.';
$string['migratev1_verifiedclean'] = 'بررسی‌شده: داده‌های کوچانده‌شده بی‌هیچ اختلافی با خاستگاه نسخهٔ ۱ همخوان‌اند.';
$string['migratev1_verifieddiscrepancies'] = 'بررسی، اختلاف‌هایی نسبت به خاستگاه نسخهٔ ۱ یافت: {$a}';
$string['migratev1_verifyfailed'] = 'این فعالیت بررسی نشد: {$a}';
$string['modulename'] = 'دیکتهٔ ویدیویی';
$string['modulename_help'] = 'فعالیت دیکتهٔ ویدیویی به شرکت‌کنندگان امکان می‌دهد هنگام دیدن یا شنیدن ویدیو، جاهای خالی زیرنویس‌های زمان‌دار را پر کنند.

مدرسان پروندهٔ زیرنویس ‏WebVTT یا ‏SubRip را درون‌ریزی می‌کنند، واژه‌ها یا عبارت‌هایی را جای خالی نشانه‌گذاری می‌کنند و تعیین می‌کنند پاسخ‌ها چقدر سخت‌گیرانه مقایسه شوند. شرکت‌کنندگان رونوشت را بخش‌به‌بخش پیش می‌برند، راهنمایی‌های امتیازدار می‌خواهند و بی‌درنگ بازخورد می‌گیرند.';
$string['modulenameplural'] = 'دیکته‌های ویدیویی';
$string['nav_exportshort'] = 'برون‌ریزی';
$string['nav_media'] = 'رسانه';
$string['nav_reports'] = 'تلاش‌ها';
$string['nav_subtitles'] = 'زیرنویس‌ها و جاهای خالی';
$string['noinstances'] = 'در این درس دیکتهٔ ویدیویی نیست.';
$string['overview_attempts'] = 'تلاش‌ها';
$string['playbackheading'] = 'پخش و زیرنویس‌ها';
$string['playbackoverlayhint'] = 'زیرنویسی که روی تصویر نشانده می‌شود تنها همان زیرنویسی را نشان می‌دهد که هم‌اکنون پخش می‌شود، پس پخش همیشه در پایان زیرنویسی که هنوز جای خالی پرنشده دارد می‌ایستد. اینجا چیزی برای گزینش نیست.';
$string['playbackproviderhint'] = 'ویدیوی ‏YouTube یا ‏Vimeo را فراهم‌کننده در قاب خودش پخش می‌کند و آن قاب زمان پخش را گزارش نمی‌دهد. چنین تمرینی همیشه زیرنویس‌ها را زیر رسانه نشان می‌دهد و هرگز در پایان زیرنویس نمی‌ایستد، هر گزینه‌ای هم که بالا برگزیده شود. پرونده‌های بارگذاری‌شده و نشانی‌های مستقیم رسانه هر دو تنظیم را پاس می‌دارند.';
$string['player_check'] = 'بررسی پاسخ';
$string['player_consentaccept'] = 'بارگذاری ویدیو از {$a}';
$string['player_consentdetail'] = 'پخش، مرورگر شما را به {$a} پیوند می‌دهد. {$a} نشانی ‏IP و آگاهی‌هایی دربارهٔ دستگاه شما دریافت می‌کند و می‌تواند کوکی‌هایی را که پیش‌تر نهاده بخواند. تا زمانی که خودتان بارگذاری ویدیو را برنگزینید، چیزی فرستاده نمی‌شود.';
$string['player_consentheading'] = 'این ویدیو را {$a} فراهم می‌کند';
$string['player_exitfullscreen'] = 'خروج از تمام‌صفحه';
$string['player_finish'] = 'به پایان رساندن تلاش';
$string['player_finished'] = 'تلاش به پایان رسید. امتیاز: %score%%';
$string['player_finishincomplete'] = 'جاهای خالی هنوز نانوشته: {$a}. با این حال تلاش را به پایان می‌رسانید؟';
$string['player_fullscreen'] = 'تمام‌صفحه';
$string['player_gaplabel'] = 'جای خالی %gap%';
$string['player_gaplink'] = 'گشودن پیوند';
$string['player_hint'] = 'نمایش راهنمایی';
$string['player_loaderror'] = 'تمرین بارگذاری نشد. صفحه را دوباره بارگذاری کنید.';
$string['player_loading'] = 'در حال بارگذاری تمرین…';
$string['player_nocontent'] = 'هنوز محتوای تمرینی منتشر نشده است. بعداً سر بزنید.';
$string['player_novideotrack'] = 'مرورگر شما نمی‌تواند باند ویدیویی این رسانه را نشان دهد؛ صدا با این حال پخش می‌شود. به مدرس خود خبر دهید.';
$string['player_outdatedattempt'] = 'این تمرین پس از آغاز این تلاش به‌روز شده است. شما بر محتوای پیشین ادامه می‌دهید؛ این تلاش را به پایان برسانید تا دفعهٔ بعد با تمرین به‌روزشده کار کنید.';
$string['player_progress'] = 'به {$a->done} از {$a->total} جای خالی پاسخ داده شد';
$string['player_ready'] = 'تمرین آماده است.';
$string['player_scorelabel'] = 'امتیاز: %score%%';
$string['player_stateaccepted'] = 'پذیرفته‌شده';
$string['player_statecorrect'] = 'درست';
$string['player_statehinted'] = 'راهنمایی به کار رفت';
$string['player_stateincorrect'] = 'نادرست';
$string['player_submitfailed'] = 'پاسخ شما ذخیره نشد. دوباره تلاش کنید.';
$string['player_transcriptheading'] = 'رونوشت';
$string['pluginadministration'] = 'مدیریت دیکتهٔ ویدیویی';
$string['pluginname'] = 'دیکتهٔ ویدیویی';
$string['privacy_metadata_elang'] = 'برای هر فعالیت، ثبت اینکه چه کسی کوچ یک‌سویهٔ محتوای ‏1.x آن را تأیید کرده است.';
$string['privacy_metadata_elang_attempt'] = 'برای هر تلاش در یک تمرین، فعالیت ذخیره می‌کند چه کسی آن را انجام داده، چه هنگام، تا کجا پیش رفته و چگونه ارزیابی شده است.';
$string['privacy_metadata_elang_attempt_answeredgaps'] = 'شرکت‌کننده در این تلاش به چند جای خالی پاسخ داده است.';
$string['privacy_metadata_elang_attempt_attemptnumber'] = 'شمارهٔ پیاپی این تلاش برای آن کاربر و فعالیت.';
$string['privacy_metadata_elang_attempt_correctgaps'] = 'در این تلاش چند جای خالی درست پذیرفته شده است.';
$string['privacy_metadata_elang_attempt_exactgaps'] = 'در این تلاش به چند جای خالی با تطابق دقیق نویسه‌ها پاسخ داده شده است.';
$string['privacy_metadata_elang_attempt_hintedgaps'] = 'شرکت‌کننده در این تلاش برای چند جای خالی راهنمایی خواسته است.';
$string['privacy_metadata_elang_attempt_score'] = 'امتیاز به‌دست‌آمده در این تلاش.';
$string['privacy_metadata_elang_attempt_state'] = 'اینکه تلاش در جریان است، به پایان رسیده یا رها شده.';
$string['privacy_metadata_elang_attempt_timefinish'] = 'زمانی که تلاش به پایان رسید.';
$string['privacy_metadata_elang_attempt_timemodified'] = 'زمان واپسین به‌روزرسانی تلاش.';
$string['privacy_metadata_elang_attempt_timestart'] = 'زمانی که تلاش آغاز شد.';
$string['privacy_metadata_elang_attempt_totalgaps'] = 'شمار همهٔ جاهای خالی در نسخهٔ تمرینی که این تلاش به آن وابسته است.';
$string['privacy_metadata_elang_attempt_userid'] = 'شناسهٔ کاربری که تلاش را انجام داده است.';
$string['privacy_metadata_elang_attempt_versionid'] = 'نسخهٔ تمرینی که این تلاش بر پایهٔ آن انجام شده است.';
$string['privacy_metadata_elang_migrationapproveduserid'] = 'کاربری که کوچ این فعالیت از ‏mod_elang 1.x را تأیید کرده است. ذخیره می‌شود تا تأیید پیگیری‌پذیر بماند.';
$string['privacy_metadata_elang_response'] = 'برای هر جای خالی که شرکت‌کننده در یک تلاش پاسخ می‌دهد، فعالیت متن پاسخ و شیوهٔ ارزیابی آن را ذخیره می‌کند.';
$string['privacy_metadata_elang_response_accepted'] = 'اینکه پاسخ برای این جای خالی درست پذیرفته شده است یا نه.';
$string['privacy_metadata_elang_response_hintlevel'] = 'بالاترین سطح راهنمایی که برای این جای خالی به شرکت‌کننده نشان داده شده است.';
$string['privacy_metadata_elang_response_responsetext'] = 'متنی که شرکت‌کننده در این جای خالی نوشته است.';
$string['privacy_metadata_elang_response_resultstate'] = 'رده‌بندی‌ای که ارزیابی به این پاسخ داده است (دقیق، واژهٔ شناخته‌شده، نادرست یا خالی).';
$string['privacy_metadata_elang_response_score'] = 'امتیازی که این پاسخ پس از کسر احتمالی راهنمایی آورده است.';
$string['privacy_metadata_elang_response_timecreated'] = 'زمان نخستین فرستادن این پاسخ.';
$string['privacy_metadata_elang_response_timemodified'] = 'زمان واپسین به‌روزرسانی این پاسخ.';
$string['privacy_metadata_elang_response_tries'] = 'شرکت‌کننده چند بار برای این جای خالی پاسخ فرستاده است.';
$string['privacy_metadata_elang_version'] = 'برای هر نسخهٔ محتوا، فعالیت ذخیره می‌کند کدام کاربر آن را واپسین بار تغییر داده است.';
$string['privacy_metadata_elang_version_usermodified'] = 'کاربری که این نسخهٔ محتوا را واپسین بار تغییر داده است. ذخیره می‌شود تا بتوان پیگیری کرد چه کسی محتوای تمرین را ویرایش کرده است.';
$string['privacy_provider_externallink'] = 'هنگامی که تمرینی بر ویدیوی ‏YouTube یا ‏Vimeo استوار باشد، گشودن آن مرورگر شرکت‌کننده را به آن فراهم‌کننده پیوند می‌دهد. افزونه خودش چیزی نمی‌فرستد، اما این پیوند را فعالیت پدید می‌آورد. اینکه اصلاً رخ دهد یا نه، به تنظیم سایت دربارهٔ رضایت فراهم‌کننده و به پذیرش شرکت‌کننده بستگی دارد.';
$string['privacy_provider_ipaddress'] = 'نشانی ‏IP که مرورگر شرکت‌کننده از آن پیوند می‌گیرد.';
$string['privacy_provider_useragent'] = 'داده‌های مرورگر و دستگاه که مرورگر می‌فرستد.';
$string['provider_vimeo'] = 'Vimeo';
$string['provider_youtube'] = 'YouTube';
$string['providerconsent'] = 'پیش از جاسازی ‏YouTube یا ‏Vimeo بپرس';
$string['providerconsent_desc'] = 'تمرین‌هایی که بر ویدیوی ‏YouTube یا ‏Vimeo استوارند، به‌جای ویدیو آگاهی‌ای نشان می‌دهند و تنها پس از پذیرش شرکت‌کننده آن را جاسازی می‌کنند. بدون آن، فراهم‌کننده به‌محض گشوده‌شدن صفحه نشانی ‏IP و داده‌های مرورگر شرکت‌کننده را دریافت می‌کند — پیش از آنکه کسی پخش را بزند. تنها هنگامی این را خاموش کنید که نهاد شما این رضایت را از راه دیگری می‌گیرد.';
$string['report_actions'] = 'کارها';
$string['report_answered'] = 'پاسخ‌داده‌شده';
$string['report_attemptnumber'] = 'تلاش';
$string['report_back'] = 'بازگشت به همهٔ تلاش‌ها';
$string['report_correct'] = 'درست';
$string['report_delete'] = 'حذف';
$string['report_deleteconfirm'] = 'این تلاش و همهٔ پاسخ‌هایش برای همیشه حذف شود؟ بازگشتی در کار نیست.';
$string['report_deleted'] = 'تلاش حذف شد.';
$string['report_exact'] = 'دقیق';
$string['report_export'] = 'برون‌ریزی';
$string['report_filterany'] = 'همه';
$string['report_filterapply'] = 'به‌کارگیری پالایه‌ها';
$string['report_filterattempt'] = 'شمارهٔ تلاش';
$string['report_filterfrom'] = 'آغازشده از';
$string['report_filterrangeerror'] = 'پایان بازه پیش از آغاز آن است.';
$string['report_filterreset'] = 'پاک‌کردن پالایه‌ها';
$string['report_filterstate'] = 'وضعیت';
$string['report_filterto'] = 'آغازشده تا';
$string['report_filteruser'] = 'شرکت‌کننده';
$string['report_finished'] = 'به‌پایان‌رسیده';
$string['report_heading'] = 'تلاش‌ها';
$string['report_hinted'] = 'با راهنمایی';
$string['report_hints'] = 'سطح راهنمایی';
$string['report_kpianswered'] = 'پاسخ‌داده‌شده';
$string['report_kpiattempts'] = 'تلاش‌های نمایش‌داده‌شده';
$string['report_kpiaverage'] = 'میانگین امتیاز (به‌پایان‌رسیده)';
$string['report_kpicorrect'] = 'پذیرفته‌شده';
$string['report_kpiexact'] = 'کاملاً درست';
$string['report_kpifinished'] = 'به‌پایان‌رسیده';
$string['report_kpihinted'] = 'از راهنمایی بهره بردند';
$string['report_kpihintedgaps'] = 'به راهنمایی نیاز داشتند';
$string['report_noattempts'] = 'هنوز تلاشی نیست.';
$string['report_nogaps'] = 'نسخه‌ای که این تلاش بر آن انجام شده جای خالی ندارد.';
$string['report_nomatchingattempts'] = 'هیچ تلاشی با این پالایه‌ها همخوان نیست.';
$string['report_noresponse'] = 'بی‌پاسخ';
$string['report_response'] = 'پاسخ';
$string['report_result'] = 'نتیجه';
$string['report_result_empty'] = 'خالی';
$string['report_result_exact'] = 'دقیق';
$string['report_result_incorrect'] = 'نادرست';
$string['report_result_none'] = '—';
$string['report_result_wordrecognized'] = 'شناخته‌شده';
$string['report_score'] = 'امتیاز';
$string['report_solution'] = 'پاسخ';
$string['report_started'] = 'آغاز';
$string['report_state'] = 'وضعیت';
$string['report_state_abandoned'] = 'رهاشده';
$string['report_state_finished'] = 'به‌پایان‌رسیده';
$string['report_state_inprogress'] = 'در جریان';
$string['report_transcript'] = 'رونوشت';
$string['report_tries'] = 'تلاش‌های پاسخ';
$string['report_user'] = 'شرکت‌کننده';
$string['report_view'] = 'دیدن';
$string['reports'] = 'گزارش‌ها';
$string['resetattempts'] = 'حذف همهٔ تلاش‌ها و پاسخ‌های شرکت‌کنندگان';
$string['solutionavailability'] = 'رونوشت همراه با پاسخ‌ها برای شرکت‌کنندگان';
$string['solutionavailability_aftersubmission'] = 'پس از به پایان رسیدن تلاش';
$string['solutionavailability_always'] = 'هر زمان';
$string['solutionavailability_help'] = 'اینکه شرکت‌کنندگان چه هنگام می‌توانند رونوشت کامل را با پاسخ هر جای خالی دانلود کنند.

* هرگز — تنها مدرسان می‌توانند آن را دانلود کنند.
* پس از به پایان رسیدن تلاش — شرکت‌کننده پس از به پایان رساندن تلاشی در این فعالیت می‌تواند آن را دانلود کند.
* هر زمان — شرکت‌کننده حتی پیش از پاسخ‌دادن هم می‌تواند آن را دانلود کند.

کادر آموزشی دارای اجازه همیشه می‌تواند آن را دانلود کند، جدا از این تنظیم.';
$string['solutionavailability_never'] = 'هرگز';
$string['subplugintype_elangscript'] = 'پردازندهٔ خط';
$string['subplugintype_elangscript_plural'] = 'پردازنده‌های خط';
$string['subtitleposition'] = 'نمایش زیرنویس‌ها';
$string['subtitleposition_below'] = 'زیر رسانه';
$string['subtitleposition_help'] = 'اینکه زیرنویس‌های تعاملی کجا نشان داده شوند.

* زیر رسانه — همهٔ رونوشت زیر رسانه در ناحیهٔ پیمایش خودش می‌ایستد و پخش را دنبال می‌کند.
* در ویدیو، بالا یا پایین — تنها زیرنویسی که هم‌اکنون پخش می‌شود روی رسانه کشیده می‌شود.

رسانه‌ای که تنها صداست تصویری برای کشیدن ندارد، پس همیشه نمایش زیر رسانه را به کار می‌برد. خود تنظیم نگه داشته می‌شود و به‌محض اینکه فعالیت ویدیویی به کار برد دوباره کار می‌کند.';
$string['subtitleposition_overlaybottom'] = 'در ویدیو — پایین';
$string['subtitleposition_overlaytop'] = 'در ویدیو — بالا';
$string['task_migratev1activities'] = 'کوچاندن فعالیت‌های نسخهٔ ۱';
$string['transcriptheading'] = 'رونوشت برای شرکت‌کنندگان';
$string['validate_cueafterend'] = '{$a->where}: در {$a->endtime} میلی‌ثانیه پایان می‌یابد، یعنی پس از رسانه ({$a->duration} میلی‌ثانیه). پخش هرگز به آنجا نمی‌رسد.';
$string['validate_cueendbeforestart'] = '{$a}: پایان پس از آغاز نیست.';
$string['validate_cuewhere'] = 'بخش {$a->sortorder} ({$a->cuekey})';
$string['validate_emptysolution'] = 'پاسخ {$a} خالی است.';
$string['validate_hintlevels'] = 'سطح‌های راهنمایی {$a} دنباله‌ای پیوسته که از ۱ آغاز شود نمی‌سازند.';
$string['validate_negativetime'] = '{$a}: زمان آغاز پیش از آغاز ضبط است.';
$string['validate_nocues'] = 'نسخه هیچ بخشی ندارد.';
$string['validate_nogaps'] = 'نسخه هیچ جای خالی برای پاسخ ندارد.';
$string['validate_nonpositivelength'] = 'طول نویسه‌ای {$a} باید مثبت باشد.';
$string['validate_rangeoutside'] = 'بازهٔ نویسه‌ای {$a} بیرون از رونوشت آن است.';
$string['validate_rangeoverlap'] = 'بازهٔ نویسه‌ای {$a} با جای خالی دیگری هم‌پوشانی دارد.';
$string['validate_unknownalgorithm'] = 'الگوریتم ارزیابی «{$a->algorithm}» برای {$a->where} شناخته نشد.';
$string['validate_where'] = 'جای خالی {$a->gapkey} در بخش {$a->cuekey}';
$string['verify_algorithmmismatch'] = 'جای خالی {$a->gapkey}: الگوریتم ارزیابی «{$a->actual}» است، «{$a->expected}» انتظار می‌رفت.';
$string['verify_attemptcount'] = 'شمار تلاش‌های کوچانده‌شده {$a->actual} است، {$a->expected} شرکت‌کنندهٔ جداگانه از ‏1.x انتظار می‌رفت.';
$string['verify_jarothreshold'] = 'آستانهٔ مقایسهٔ پاسخ‌ها {$a->actual} است، {$a->expected} انتظار می‌رفت.';
$string['verify_missingattempt'] = 'کاربر {$a}: تلاشی کوچانده‌شده انتظار می‌رفت، هیچ‌یک یافت نشد.';
$string['verify_missingcue'] = 'بخش {$a}: بخش کوچانده‌شده نیست.';
$string['verify_missinggap'] = 'جای خالی {$a}: جای خالی کوچانده‌شده نیست.';
$string['verify_missinghint'] = 'جای خالی {$a}: نسخهٔ ۱ اینجا راهنمایی را روا می‌دانست، اما هیچ راهنمایی‌ای کوچانده نشد.';
$string['verify_orphancue'] = 'بخش {$a}: بخش هم‌ارزی از نسخهٔ ۱ یافت نشد.';
$string['verify_orphangap'] = 'جای خالی {$a}: جای خالی هم‌ارزی از نسخهٔ ۱ یافت نشد.';
$string['verify_rangemismatch'] = 'جای خالی {$a}: بازهٔ نویسه‌ای با خاستگاه نسخهٔ ۱ همخوان نیست.';
$string['verify_responsecount'] = 'کاربر {$a->userid}: شمار پاسخ‌های کوچانده‌شده {$a->actual} است، {$a->expected} انتظار می‌رفت.';
$string['verify_solutionmismatch'] = 'جای خالی {$a->gapkey}: پاسخ «{$a->actual}» است، «{$a->expected}» انتظار می‌رفت.';
$string['verify_transcriptmismatch'] = 'بخش {$a}: رونوشت با خاستگاه نسخهٔ ۱ همخوان نیست.';
$string['verify_unexpectedhint'] = 'جای خالی {$a}: نسخهٔ ۱ اینجا راهنمایی را روا نمی‌دانست، اما راهنمایی‌ای کوچانده شد.';
