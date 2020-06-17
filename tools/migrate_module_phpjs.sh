#!/bin/sh

module='module'

for f in $( find modules/$module -type f \( -name "*.php" -or -name "*.js" -or -name "*.css" -or -name "*.xml" -or -name "*.sql" -or -name "*.txt" \) )
do
    echo $f
    iconv -f ISO-8859-1 -t UTF-8 $f > "${f}.utf8"
    mv "${f}.utf8" $f
done


for f in $( find install/$module -type f \( -name "*.xml" -or -name "*.sql" -or -name "*.txt" \) )
do
    echo $f
    iconv -f ISO-8859-1 -t UTF-8 $f > "${f}.utf8"
    mv "${f}.utf8" $f
done

find install/$module -type f -name "*.xml" -exec sed -i 's/iso-8859-1/utf-8/g' {} \;
find install/$module -type f -name "*.xml" -exec sed -i 's/ISO-8859-1/UTF-8/g' {} \;
find install/$module -type f -name "*.sql" -exec sed -i 's/CHARSET=latin1/CHARSET=utf8/g' {} \;

find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_createdatetime/ploopi\\date::createdatetime/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_createtimestamp/ploopi\\date::createtimestamp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_datetime2local/ploopi\\date::datetime2local/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_datetime2timestamp/ploopi\\date::datetime2timestamp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_datetime2unixtimestamp/ploopi\\date::datetime2unixtimestamp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_datetime_add/ploopi\\date::datetime_add/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_dateverify/ploopi\\date::dateverify/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getdate/ploopi\\date::getdate/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_gettime/ploopi\\date::gettime/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_gettimestampdetail/ploopi\\date::gettimestampdetail/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_holiday/ploopi\\date::holiday/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_local2datetime/ploopi\\date::local2datetime/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_local2timestamp/ploopi\\date::local2timestamp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_numweek2unixtimestamp/ploopi\\date::numweek2unixtimestamp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_opencalendar/ploopi\\date::opencalendar/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_open_calendar/ploopi\\date::open_calendar/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_timestamp2local/ploopi\\date::timestamp2local/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_timestamp2unixtimestamp/ploopi\\date::timestamp2unixtimestamp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_timestamp2xls/ploopi\\date::timestamp2xls/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_timestamp_add/ploopi\\date::timestamp_add/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_timeverify/ploopi\\date::timeverify/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_tz_createtimestamp/ploopi\\date::tz_createtimestamp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_tz_getutc/ploopi\\date::tz_getutc/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_tz_timestamp2timestamp/ploopi\\date::tz_timestamp2timestamp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_unixtimestamp2datetime/ploopi\\date::unixtimestamp2datetime/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_unixtimestamp2local/ploopi\\date::unixtimestamp2local/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_unixtimestamp2timestamp/ploopi\\date::unixtimestamp2timestamp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_listfolders_rec/ploopi\\documents::_listfolders_rec/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_browser/ploopi\\documents::browser/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_countelements/ploopi\\documents::countelements/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_getfiles/ploopi\\documents::getfiles/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_getfolders/ploopi\\documents::getfolders/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_getid/ploopi\\documents::getid/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_getopenfilejs/ploopi\\documents::getopenfilejs/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_getselectfilejs/ploopi\\documents::getselectfilejs/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_getpath/ploopi\\documents::getpath/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_getselectfilejs/ploopi\\documents::getselectfilejs/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_insert/ploopi\\documents::insert/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_listfolders/ploopi\\documents::listfolders/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_savefile/ploopi\\documents::savefile/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_documents_savefolder/ploopi\\documents::savefolder/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_nl2br/ploopi\\str::nl2br/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_strcut/ploopi\\str::cut/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_convertaccents/ploopi\\str::convertaccents/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_rawurlencode/ploopi\\str::rawurlencode/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_string2url/ploopi\\str::tourl/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_urlrewrite/ploopi\\str::urlrewrite/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_strtr/ploopi\\str::strtr/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_str_split/ploopi\\str::str_split/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_xmlentities/ploopi\\str::xmlentities/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_htmlentities/ploopi\\str::htmlentities/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_html_entity_decode/ploopi\\str::html_entity_decode/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_utf8encode/ploopi\\str::utf8encode/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_iso8859_clean/ploopi\\str::iso8859_clean/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_make_links/ploopi\\str::make_links/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_print_json/ploopi\\str::print_json/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_htmlpurifier/ploopi\\str::htmlpurifier/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_color_hex2rgb/ploopi\\str::color_hex2rgb/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_is_url/ploopi\\str::is_url/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_clean_filename/ploopi\\str::clean_filename/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_htpasswd/ploopi\\crypt::htpasswd/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_urltoken/ploopi\\crypt::urltoken/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_urlencode_trusted/ploopi\\crypt::urlencode_trusted/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_urlencode/ploopi\\crypt::urlencode/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_queryencode_trusted/ploopi\\crypt::queryencode_trusted/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_queryencode/ploopi\\crypt::queryencode/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_base64_encode/ploopi\\crypt::base64_encode/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_base64_decode/ploopi\\crypt::base64_decode/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_serialize/ploopi\\crypt::serialize/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_unserialize/ploopi\\crypt::unserialize/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_die/ploopi\\system::kill/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_accepts_gzip/ploopi\\system::accepts_gzip/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_viewworkspaces/ploopi\\system::viewworkspaces/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_viewworkspaces_inv/ploopi\\system::viewworkspaces_inv/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getavailabletemplates/ploopi\\system::getavailabletemplates/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_logout/ploopi\\system::logout/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getnbcore/ploopi\\system::getnbcore/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_exec/ploopi\\system::exec/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_ob_callback/ploopi\\buffer::callback/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_ob_clean/ploopi\\buffer::clean/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_h404/ploopi\\output::h404/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_print_r/ploopi\\output::print_r/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_redirect/ploopi\\output::redirect/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_redirect_trusted/ploopi\\output::redirect_trusted/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_loadparams/ploopi\\param::load/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getparam/ploopi\\param::get/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getsessionvar/ploopi\\session::getvar/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_setsessionvar/ploopi\\session::setvar/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_set_flag/ploopi\\session::setflag/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array_map/ploopi\\arr::map/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_init_module/ploopi\\module::init/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getmoduleid/ploopi\\module::getid/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array2json/ploopi\\arr::tojson/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array2xml/ploopi\\arr::toxml/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array2csv/ploopi\\arr::tocsv/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array2html/ploopi\\arr::tohtml/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array2excel/ploopi\\arr::toexcel/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array2ods/ploopi\\arr::toods/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array_cleankeys/ploopi\\arr::cleankeys/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array_page/ploopi\\arr::page/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_array_getpages/ploopi\\arr::getpages/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_send_mail/ploopi\\mail::send/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_send_mail_smtp/ploopi\\mail::send_smtp/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_form2html/ploopi\\mail::form2html/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_send_form/ploopi\\mail::send_form/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_checkemail/ploopi\\mail::check/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getip/ploopi\\ip::get/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getiprules/ploopi\\ip::getrules/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_isipvalid/ploopi\\ip::isvalid/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_create_user_action_log/ploopi\\user_action_log::record/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_get_user_action_log/ploopi\\user_action_log::get/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_actions_getusers/ploopi\\acl::actions_getusers/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_error_handler/ploopi\\error::handler/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_set_error_handler/ploopi\\error::set_handler/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_unset_error_handler/ploopi\\error::unset_handler/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_syslog/ploopi\\error::syslog/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_annotation_refresh(/ploopi\\annotation::display_refresh(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_annotation_getnb(/ploopi\\annotation::getnb(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_annotation(/ploopi\\annotation::display(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_copydir(/ploopi\\fs::copydir(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_deletedir(/ploopi\\fs::deletedir(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_makedir(/ploopi\\fs::makedir(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getmimetype(/ploopi\\fs::getmimetype(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_downloadfile(/ploopi\\fs::downloadfile(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_file_getextension(/ploopi\\fs::file_getextension(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_resizeimage(/ploopi\\image::resize(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_image_wordwrap(/ploopi\\image::wordwrap(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_search_getdb(/ploopi\\search_index::getdb(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_search_get_id(/ploopi\\search_index::getid(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_search_remove_index(/ploopi\\search_index::remove(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_search_remove_index_module(/ploopi\\search_index::remove_module(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_search_create_index(/ploopi\\search_index::add(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_search_get_index(/ploopi\\search_index::get(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_search(/ploopi\\search_index::search(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_getwords(/ploopi\\str::getwords(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_highlight(/ploopi\\str::highlight(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_isadmin(/ploopi\\acl::isadmin(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_ismanager(/ploopi\\acl::ismanager(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_isactionallowed(/ploopi\\acl::isactionallowed(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_ismoduleallowed(/ploopi\\acl::ismoduleallowed(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_checkpasswordvalidity(/ploopi\\security::checkpasswordvalidity(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_generatepassword(/ploopi\\security::generatepassword(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_filtervar(/ploopi\\security::filtervar(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_share_generateid(/ploopi\\share::generateid(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_share_selectusers(/ploopi\\share::selectusers(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_share_save(/ploopi\\share::add(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_share_get(/ploopi\\share::get(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_subscription(/ploopi\\subscription::display(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_subscription_refresh(/ploopi\\subscription::display_refresh(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_subscription_subscribed(/ploopi\\subscription::subscribed(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_subscription_getusers(/ploopi\\subscription::getusers(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_subscription_notify(/ploopi\\subscription::notify(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_validation_generateid(/ploopi\\validation::generateid(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_validation_selectusers(/ploopi\\validation::selectusers(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_validation_save(/ploopi\\validation::add(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_validation_get(/ploopi\\validation::get(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_validation_delete(/ploopi\\validation::remove(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_tickets_displayusers(/ploopi\\ticket::displayusers(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_tickets_getnew(/ploopi\\ticket::getnew(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_tickets_send(/ploopi\\ticket::send(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_tickets_selectusers(/ploopi\\ticket::selectusers(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_filexplorer_init(/ploopi\\fs::filexplorer_init(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_loader::/ploopi\\loader::/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/extends data_object/extends ploopi\\data_object/g' {} \;

find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_query_select/ploopi\\query_select/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_query_delete/ploopi\\query_delete/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_query_insert/ploopi\\query_insert/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi_query_update/ploopi\\query_update/g' {} \;

find modules/$module -type f -name "*.php" -exec sed -i 's/new form(/new ploopi\\form(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_button(/new ploopi\\form_button(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_checkbox(/new ploopi\\form_checkbox(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_checkbox_list(/new ploopi\\form_checkbox_list(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_selection_option(/new ploopi\\form_selection_option(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_datetime(/new ploopi\\form_datetime(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_hidden(/new ploopi\\form_hidden(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_radio_list(/new ploopi\\form_radio_list(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_panel(/new ploopi\\form_panel(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_element(/new ploopi\\form_element(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_htmlfield(/new ploopi\\form_htmlfield(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_richtext(/new ploopi\\form_richtext(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_text(/new ploopi\\form_text(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_html(/new ploopi\\form_html(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_field(/new ploopi\\form_field(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_select(/new ploopi\\form_select(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new form_radio(/new ploopi\\form_radio(/g' {} \;

find modules/$module -type f -name "*.php" -exec sed -i 's/new mb_field(/new ploopi\\mb_field(/g' {} \;

find modules/$module -type f -name "*.php" -exec sed -i 's/new workspace(/new ploopi\\workspace(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new user(/new ploopi\\user(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new group(/new ploopi\\group(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new data_object_collection(/new ploopi\\data_object_collection(/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/new ploopi_cache(/new ploopi\\cache(/g' {} \;


find modules/$module -type f -name "*.php" -exec sed -i 's/ploopi\\system::die/ploopi\\system::kill/g' {} \;

find modules/$module -type f -name "*.php" -exec sed -i 's/new calendar/new ploopi\\calendar/g' {} \;



find modules/$module -type f -name "*.php" -exec sed -i 's/<?$/<?php/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/<? /<?php /g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/global \$db;/db::get();/g' {} \;
find modules/$module -type f -name "*.php" -exec sed -i 's/\$db/ploopi\\db::get()/g' {} \;

find modules/$module -type f -name "*.php" -exec sed -i 's/$skin/ploopi\\skin::get()/g' {} \;



find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_xmlhttprequest_tofunction/ploopi.xhr.tocb/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_xmlhttprequest_todiv/ploopi.xhr.todiv/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_xmlhttprequest_topopup/ploopi.xhr.topopup/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_xmlhttprequest_submitform/ploopi.xhr.submit/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_xmlhttprequest/ploopi.xhr.send/g' {} \;

find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_openwin/ploopi.openwin/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_confirmlink/ploopi.confirmlink/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_confirmform/ploopi.confirmform/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_base64_encode/ploopi.base64_encode/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_base64_decode/ploopi.base64_decode/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_addslashes/ploopi.addslashes/g' {} \;

find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_openwin/ploopi.openwin/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_confirmform/ploopi.confirmform/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_confirmlink/ploopi.confirmlink/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_switchdisplay/ploopi.switchdisplay/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_getelem/ploopi.getelem/g' {} \;

find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_showpopup/ploopi.popup.show/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_hidepopup/ploopi.popup.hide/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_movepopup/ploopi.popup.move/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_popupize/ploopi.popup.ize/g' {} \;

find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_tickets_new(/ploopi.tickets.create/g' {} \;

find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_dispatch_onchange/ploopi.event.dispatch_onchange/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_dispatch_onclick/ploopi.event.dispatch_onclick/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_checkbox_click/ploopi.checkbox_click/g' {} \;


find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_documents_openfolder/ploopi.documents.openfolder/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_documents_openfile/ploopi.documents.openfile/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_documents_deletefile/ploopi.documents.deletefile/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_documents_deletefolder/ploopi.documents.deletefolder/g' {} \;

find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_calendar_open/ploopi.calendar.open/g' {} \;

find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_ajaxloader_content/ploopi.xhr.ajaxloader_content/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_ajaxloader/ploopi.xhr.ajaxloader/g' {} \;

find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_annotation_\([a-z_]*\)(/ploopi.annotations.\1(/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi.annotations.delete/ploopi.annotations.remove/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js"  -or -name "*.tpl" \) -exec sed -i 's/ploopi_validatefield/ploopi.validatefield/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_rgbcolor/ploopi.rgbcolor/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/ploopi_validatereset/ploopi.validatereset/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/.addClassName(/.addClass(/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/.removeClassName(/.removeClass(/g' {} \;
find modules/$module -type f \( -name "*.php" -or -name "*.js" \) -exec sed -i 's/$('"'"'\([a-z0-9A-Z_]*\)'"'"')/jQuery('"'"'#\1'"'"')[0]/g' {} \;
