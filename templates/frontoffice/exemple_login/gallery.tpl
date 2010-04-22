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
<!-- END gallery1 -->

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
<!-- END gallery2 -->

<!-- BEGIN gallery3 -->
<div id="dewslider_content_{gallery3.ID_UNIQ}"></div>
<script type="text/javascript">
    var dewslider = new SWFObject("{TEMPLATE_PATH}/img/dewslider.swf", "dewslider_embed_{gallery3.ID_UNIQ}", "{gallery3.VIEW_WIDTH}", "{gallery3.VIEW_HEIGHT}", "9", "#ffffff");
    dewslider.addParam("wmode", "transparent");
    dewslider.addVariable("xml", "gallery/dewslider-g{gallery3.ID_GALLERY}-sb1-st1-rs1-t10-atb-abb-trb-s20.xml");
    dewslider.write("dewslider_content_{gallery3.ID_UNIQ}");
</script>
<!-- END gallery3 -->

<!-- BEGIN gallery4 -->
<div id="flip_content_{gallery4.ID_UNIQ}"></div>

<div style="display: none;">
    <!-- BEGIN line -->
        <!-- BEGIN col -->
            <a id="{gallery4.ID_GALLERY}_{gallery4.line.col.CPT}" href="{gallery4.line.col.URL_VIEW}" rel="lightbox[{gallery4.ID_UNIQ}]"><img src="{gallery4.line.col.URL_THUMB}" alt="{gallery4.line.col.NAME}"/></a>
        <!-- END col -->
    <!-- END line -->
</div>

<script type="text/javascript">
    var flip = new SWFObject("{TEMPLATE_PATH}/img/Flip.swf", "flip_embed_{gallery4.ID_UNIQ}", "{gallery4.THUMB_WIDTH}", "{gallery4.THUMB_HEIGHT}", "9");
    flip.addParam("wmode", "transparent");
    flip.addVariable("xmlfile", "gallery/flip-g{gallery4.ID_GALLERY}-lightbox-transp1-friction5-fullscreen0-fieldofview55-margin0-0-20-0-flipm-vertical1-speed180-default_speed45-reset_delay30-amount40-blur2-distance0-alpha50.xml");
    flip.write("flip_content_{gallery4.ID_UNIQ}");
</script>
<!-- END gallery4 -->

<!-- BEGIN gallery5 -->
<div id="caroussel_content_{gallery5.ID_UNIQ}"></div>

<div style="display: none;">
    <!-- BEGIN line -->
        <!-- BEGIN col -->
            <a id="{gallery5.ID_GALLERY}_{gallery5.line.col.CPT}" href="{gallery5.line.col.URL_VIEW}" rel="lightbox[{gallery5.ID_UNIQ}]"><img src="{gallery5.line.col.URL_THUMB}" alt="{gallery5.line.col.NAME}"/></a>
        <!-- END col -->
    <!-- END line -->
</div>

<script type="text/javascript">
    var caroussel = new SWFObject("{TEMPLATE_PATH}/img/Carousel.swf", "caroussel_embed_{gallery5.ID_UNIQ}", "500", "100", "9");
    caroussel.addParam("wmode", "transparent");
    caroussel.addVariable("xmlfile", "/gallery/carousel-g{gallery5.ID_GALLERY}-lightbox-transp1-friction5-fullscreen0-margin0-0-0-0-33-50-rotationm-view_pointm-speed90-default_speed45-default_view_point20-reset_delay30-size50-amount50-blur10-blur_quality3-amount100-blur2-distance0-alpha50.xml");
    caroussel.write("caroussel_content_{gallery5.ID_UNIQ}");
</script>
<!-- END gallery5 -->

<!-- BEGIN gallery_no_pict -->
<h2 style="text-align: center;">
    {gallery_no_pict.MESS}
</h2>
<!-- END gallery_no_pict -->