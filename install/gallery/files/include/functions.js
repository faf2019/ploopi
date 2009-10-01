function gallery_show_preview_rep(id_directories)
{
    ploopi_ajaxloader('id_gallery_photos');
    
    $$('div.gallery_treeview_dir').each(
		function(myDiv) {
			
			if(myDiv.id != undefined && myDiv.id != '')
			{
				if(myDiv.id == 'gallery_treeview_dir_'+id_directories)
				{
					$(myDiv.id).style.fontWeight = 'bold';
				}
				else
				{
					$(myDiv.id).style.fontWeight = 'normal';
				}
			}
		}
	)
    
	//$('id_gallery_photos').innerHTML = '';
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=gallery_get_preview_photos_directory&id_directories='+id_directories,'id_gallery_photos');
}

function gallery_refresh_photo(id_photo)
{
    ploopi_ajaxloader('photo_preview_'+id_photo);
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=gallery_refresh_photo&id_preview='+id_photo,'photo_preview_'+id_photo);
}