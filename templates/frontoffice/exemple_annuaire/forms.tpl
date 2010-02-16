<form action="{ACTION}" method="post" enctype="multipart/form-data" onsubmit="javascript:eval(form_validate);return(result);">
<div class="form">
    <!-- BEGIN formfields -->
        <!-- BEGIN switch_separator -->
            <h{formfields.switch_separator.LEVEL} style="margin-top:{formfields.switch_separator.INTERLINE}px;{formfields.switch_separator.STYLE}" >
            {formfields.switch_separator.NAME}
            </h{formfields.switch_separator.LEVEL}>
        <!-- END switch_separator -->
        <!-- BEGIN switch_captcha -->
            <p>
                <label for="{formfields.switch_captcha.LABELID}">
                    {formfields.switch_captcha.LABEL} (*)
                </label>
            
                <div style="margin: 0 5px 0 0; float: left; width: 130px; height: 45px; text-align: center;">
                    <img id="img_captcha_{formfields.switch_captcha.IDCAPTCHA}" align="center" src="./img/ajax-loader.gif"/>
                </div>
                <div style="float: left; padding: 0; margin: 0;">
                    <div style="padding: 2px 0 4px 0;">
                        <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="19" height="19" id="SecurImage_{formfields.switch_captcha.IDCAPTCHA}" align="top">
                            <param name="allowScriptAccess" value="sameDomain" />
                            <param name="allowFullScreen" value="false" />
                            <param name="movie" value="./img/captcha/securimage_play.swf?audio={formfields.switch_captcha.URLTOCAPTCHASOUND}&bgColor1=#286EA0&bgColor2=#fff&iconColor=#000&roundedCorner=5" />
                            <param name="quality" value="high" />
                            <param name="bgcolor" value="#ffffff" />
                            <embed src="./img/captcha/securimage_play.swf?audio={formfields.switch_captcha.URLTOCAPTCHASOUND}&bgColor1=#286EA0&bgColor2=#fff&iconColor=#000&roundedCorner=5" quality="high" bgcolor="#ffffff" width="19" height="19" name="SecurImage_{formfields.switch_captcha.IDCAPTCHA}" align="top" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
                        </object>
                    </div>
                    <div style="cursor: pointer;" onclick="javascript: $('img_captcha_{formfields.switch_captcha.IDCAPTCHA}').src = '{formfields.switch_captcha.URLTOCAPTCHA}&random='+Math.random(); return false;"><img src="{TEMPLATE_PATH}/img/refresh.png" alt="Reload Image" border="0" align="bottom" /></div>
                </div>
            </p>
            <p>
                {formfields.switch_captcha.DESCRIPTION}
                <label>Code :</label><input type="text" class="text" id="captcha_code_{formfields.switch_captcha.IDCAPTCHA}" name="captcha_code" maxlength="8" style="width: 140px;" />
            </p>
            <script type="text/javascript">
                Event.observe(window, 'load', function() { $('img_captcha_{formfields.switch_captcha.IDCAPTCHA}').src = '{formfields.switch_captcha.URLTOCAPTCHA}&random='+Math.random(); } );
            </script>
        <!-- END switch_captcha -->
        <!-- BEGIN switch_field -->
            <p style="margin-top:{formfields.switch_field.INTERLINE}px;">
                <label for="{formfields.switch_field.LABELID}">
                    {formfields.switch_field.LABEL}
                <!-- BEGIN switch_required -->
                (*)
                <!-- END switch_required -->
                </label>

                <span>
                    <!-- BEGIN switch_autoincrement -->
                    {formfields.switch_field.VALUE}
                    <!-- END switch_autoincrement -->

                    <!-- BEGIN switch_text -->
                    <input type="text" class="text" style="{formfields.switch_field.STYLE}" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" value="{formfields.switch_field.VALUE}" maxlength="{formfields.switch_field.MAXLENGTH}" tabindex="{formfields.switch_field.TABINDEX}" />
                    <!-- END switch_text -->

                    <!-- BEGIN switch_text_date -->
                    <input type="text" class="text" style="width:100px;{formfields.switch_field.STYLE}" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" value="{formfields.switch_field.VALUE}" maxlength="{formfields.switch_field.MAXLENGTH}" tabindex="{formfields.switch_field.TABINDEX}" />
                    <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('{formfields.switch_field.LABELID}', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                    <!-- END switch_text_date -->

                    <!-- BEGIN switch_textarea -->
                    <textarea id="{formfields.switch_field.LABELID}" style="{formfields.switch_field.STYLE}" name="{formfields.switch_field.NAME}" tabindex="{formfields.switch_field.TABINDEX}" rows="" cols="">{formfields.switch_field.VALUE}</textarea>
                    <!-- END switch_textarea -->

                    <!-- BEGIN switch_select -->
                    <select id="{formfields.switch_field.LABELID}" style="{formfields.switch_field.STYLE}" name="{formfields.switch_field.NAME}" tabindex="{formfields.switch_field.TABINDEX}">
                        <!-- BEGIN values -->
                        <option {formfields.switch_field.switch_select.values.SELECTED} value="{formfields.switch_field.switch_select.values.VALUE}">{formfields.switch_field.switch_select.values.VALUE}</option>
                        <!-- END values -->
                    </select>
                    <!-- END switch_select -->

                    <!-- BEGIN switch_checkbox -->
                        <!-- BEGIN columns -->
                        <span style="float:left;width:{formfields.switch_field.switch_checkbox.columns.WIDTH}%">
                            <!-- BEGIN values -->
                                <span class="checkbox" style="{formfields.switch_field.STYLE}" onclick="javascript:ploopi_checkbox_click(event, '{formfields.switch_field.LABELID}_{formfields.switch_field.switch_checkbox.columns.values.ID}');">
                                    <input {formfields.switch_field.switch_checkbox.columns.values.CHECKED} type="checkbox" style="width:14px;" name="{formfields.switch_field.switch_checkbox.columns.values.NAME}" id="{formfields.switch_field.LABELID}_{formfields.switch_field.switch_checkbox.columns.values.ID}" value="{formfields.switch_field.switch_checkbox.columns.values.VALUE}" />
                                    {formfields.switch_field.switch_checkbox.columns.values.VALUE}
                                </span>
                            <!-- END values -->
                        </span>
                        <!-- END columns -->
                    <!-- END switch_checkbox -->

                    <!-- BEGIN switch_radio -->
                        <!-- BEGIN columns -->
                        <span style="float:left;width:{formfields.switch_field.switch_radio.columns.WIDTH}%">
                            <!-- BEGIN values -->
                                <span class="checkbox" style="{formfields.switch_field.STYLE}" onclick="javascript:ploopi_checkbox_click(event, '{formfields.switch_field.LABELID}_{formfields.switch_field.switch_radio.columns.values.ID}');">
                                    <input {formfields.switch_field.switch_radio.columns.values.CHECKED} type="radio" style="width:14px;" name="{formfields.switch_field.switch_radio.columns.values.NAME}" id="{formfields.switch_field.LABELID}_{formfields.switch_field.switch_radio.columns.values.ID}" value="{formfields.switch_field.switch_radio.columns.values.VALUE}" />
                                    {formfields.switch_field.switch_radio.columns.values.VALUE}
                                </span>
                            <!-- END values -->
                        </span>
                        <!-- END columns -->
                    <!-- END switch_radio -->

                    <!-- BEGIN switch_file -->
                    <input type="file" style="{formfields.switch_field.STYLE}" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" value="{formfields.switch_field.VALUE}" tabindex="{formfields.switch_field.TABINDEX}" />
                    <!-- END switch_file -->

                    <!-- BEGIN switch_color -->
                    <select id="{formfields.switch_field.LABELID}" style="{formfields.switch_field.STYLE}" name="{formfields.switch_field.NAME}" onchange="this.style.backgroundColor=this.value;" tabindex="{formfields.switch_field.TABINDEX}">
                        <!-- BEGIN values -->
                        <option {formfields.switch_field.switch_color.values.SELECTED} value="{formfields.switch_field.switch_color.values.VALUE}" style="background-color:{formfields.switch_field.switch_color.values.VALUE};">&nbsp;</option>
                        <!-- END values -->
                    </select>
                    <!-- END switch_color -->
                </span>
            </p>
        <!-- END switch_field -->
    <!-- END formfields -->
</div>
<div class="form_validate">(*) Champs requis</div>
<div class="form_validate">
    <input type="hidden" name="op" value="saveform" />
    <input type="hidden" name="forms_id" value="{FORM_ID}" />
    <input type="reset" value="Annuler" />
    <input type="submit" value="Envoyer" />
</div>
</form>
