<!-- BEGIN gallery1 -->
<div class="gallerymain">
    <div class="gallerycontent" style="position:relative;">
        <a href="javascript:void(0);" id="arrowprev-{gallery1.ID_UNIQ}" class="arrowprev" title="Image Précédente" onclick="javascript:objGal{gallery1.ID_UNIQ}.click_arrow('-');return false;"></a>
        <a href="javascript:void(0);" id="arrownext-{gallery1.ID_UNIQ}" class="arrownext" title="Image Suivante" onclick="javascript:objGal{gallery1.ID_UNIQ}.click_arrow('+');return false;"></a>
        <div id="galleryview-{gallery1.ID_UNIQ}">
            <img id="galleryimage-{gallery1.ID_UNIQ}" src="{gallery1.URL_VIEW}" width="{gallery1.VIEW_WIDTH}" height="{gallery1.VIEW_HEIGHT}" class="galleryimage" />
        </div>
        <div id="gallerythumbscroller-{gallery1.ID_UNIQ}" class="gallerythumbscroller">
            <div id="gallerythumbs-{gallery1.ID_UNIQ}" class="gallerythumbs" onmouseout="javascript:objGal{gallery1.ID_UNIQ}.show_tooltip('');return false;">
                <!-- BEGIN line -->
                    <!-- BEGIN col -->
                        <div class="gallerythumbsdetail" id="thumb-{gallery1.ID_UNIQ}-{gallery1.line.col.CPT}" onclick="javascript:objGal{gallery1.ID_UNIQ}.click_thumb('{gallery1.line.col.CPT}');return false;" onmouseover="javascript:objGal{gallery1.ID_UNIQ}.show_tooltip('{gallery1.line.col.CPT}');return false;">
                            <img src="{gallery1.line.col.URL_THUMB}" class="thumbnail"  width="{gallery1.THUMB_WIDTH}" height="{gallery1.THUMB_HEIGHT}"/>
                            <p class="tooltip">{gallery1.line.col.NAME}</p>
                        </div>
                    <!-- END col -->
                <!-- END line -->
            </div>
        </div>
    </div>
</div>

<!-- BEGIN switch_once -->
<script type="text/javascript" src="{TEMPLATE_PATH}/js/gallery.js"></script>
<!-- END switch_once -->
<script type="text/javascript">
    $('thumb-{gallery1.ID_UNIQ}-00').className = 'active';

    objGal{gallery1.ID_UNIQ} = new ClassGallerySlide('{gallery1.ID_UNIQ}',100);

    // Tableau des URL des images
    <!-- BEGIN line -->
        <!-- BEGIN col -->
            objGal{gallery1.ID_UNIQ}.add_URL('{gallery1.line.col.CPT}','{gallery1.line.col.URL_VIEW}');
        <!-- END col -->
    <!-- END line -->
</script>
<!-- END gallery -->


<!-- BEGIN gallery2 -->
{gallery2.PAGE_CUT_TOP}
<div class="gallery2main">
    <!-- BEGIN line -->
    <div class="gallery2line">
        <!-- BEGIN col -->
        <a href="{gallery2.line.col.URL_VIEW}" rel="lightbox[{gallery2.ID_UNIQ}]" title="{gallery2.line.col.NAME}" class="image" style="width: {gallery2.line.col.THUMB_WIDTH}px; height: {gallery2.line.col.THUMB_HEIGHT}px;"><img src="{gallery2.line.col.URL_THUMB}" class="thumbnail"  width="{gallery2.line.col.THUMB_WIDTH}" height="{gallery2.line.col.THUMB_HEIGHT}" alt="{gallery2.line.col.NAME}"/></a>
        <!-- END col -->
    </div>
    <!-- END line -->
</div>
{gallery2.PAGE_CUT_BOTTOM}

<!-- BEGIN switch_once -->
    <script type="text/javascript" src="{TEMPLATE_PATH}/js/lightbox.js"></script>
<!-- END switch_once -->
<!-- END gallery2 -->

<!-- BEGIN gallery3 -->
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" type="application/x-shockwave-flash" data="{TEMPLATE_PATH}/img/dewslider.swf?xml=gallery/dewslider-g{gallery3.ID_GALLERY}-sb1-st1-rs1-t10-atb-abb-trb-s20.xml" width="{gallery3.VIEW_WIDTH}" height="{gallery3.VIEW_HEIGHT}">
<param name="movie" value="{TEMPLATE_PATH}/img/dewslider.swf?xml=gallery/dewslider-g{gallery3.ID_GALLERY}-sb1-st1-rs1-t10-atb-abb-trb-s20.xml" />
<param name="quality" value="high"/> 
<embed src="{TEMPLATE_PATH}/img/dewslider.swf?xml=gallery/dewslider-g{gallery3.ID_GALLERY}-sb1-st1-rs1-t10-atb-abb-trb-s20.xml" type="application/x-shockwave-flash" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" width="{gallery3.VIEW_WIDTH}" height="{gallery3.VIEW_HEIGHT}"></embed>
</object>
<!-- END gallery3 -->

<!-- BEGIN gallery_no_pict -->
<h2 style="text-align: center;">
    {gallery_no_pict.MESS}
</h2>
<!-- END gallery_no_pict -->