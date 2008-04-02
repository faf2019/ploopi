<div class="ploopi_overflowauto" style="width:{FORM_WIDTH};">
    <div style="padding:4px;">
        <form name="form_display" action="{FORM_ACTION}" method="post" target="{FORM_TARGET}" enctype="multipart/form-data" onsubmit="{FORM_ONSUBMIT}">
        <!-- BEGIN formhiddenvars -->
            <input type="hidden" name="{formhiddenvars.NAME}" value="{formhiddenvars.VALUE}">
        <!-- END formhiddenvars -->
        <div class="ploopi_form">
            <!-- BEGIN formfields -->
                <!-- BEGIN switch_field -->
                    <p>
                        <label for="{formfields.switch_field.LABELID}">
                            {formfields.switch_field.LABEL}:
                        <!-- BEGIN switch_required -->
                        (*)
                        <!-- END switch_required -->
                        </label>

                        <!-- BEGIN switch_autoincrement -->
                        <span>{formfields.switch_field.VALUE}</span>
                        <!-- END switch_autoincrement -->

                        <!-- BEGIN switch_text -->
                        <input type="text" class="text" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" value="{formfields.switch_field.VALUE}" maxlength="{formfields.switch_field.MAXLENGTH}" tabindex="{formfields.switch_field.TABINDEX}" />
                        <!-- END switch_text -->

                        <!-- BEGIN switch_text_date -->
                        <input type="text" class="text" style="width:70px;" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" value="{formfields.switch_field.VALUE}" tabindex="{formfields.switch_field.TABINDEX}" />
                        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('{formfields.switch_field.LABELID}', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
                        <!-- END switch_text_date -->

                        <!-- BEGIN switch_textarea -->
                        <textarea class="text" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" tabindex="{formfields.switch_field.TABINDEX}">{formfields.switch_field.VALUE}</textarea>
                        <!-- END switch_textarea -->

                        <!-- BEGIN switch_select -->
                        <select class="select" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" tabindex="{formfields.switch_field.TABINDEX}">
                            <!-- BEGIN values -->
                            <option {formfields.switch_field.switch_select.values.SELECTED} value="{formfields.switch_field.switch_select.values.VALUE}">{formfields.switch_field.switch_select.values.VALUE}</option>
                            <!-- END values -->
                        </select>
                        <!-- END switch_select -->

                        <!-- BEGIN switch_checkbox -->
                            <span>
                            <!-- BEGIN values -->
                                <div>
                                    <input {formfields.switch_field.switch_checkbox.values.CHECKED} type="checkbox" name="{formfields.switch_field.switch_checkbox.values.NAME}" value="{formfields.switch_field.switch_checkbox.values.VALUE}" />
                                    {formfields.switch_field.switch_checkbox.values.VALUE}
                                </div>
                            <!-- END values -->
                            </span>
                        <!-- END switch_checkbox -->

                        <!-- BEGIN switch_radio -->
                            <span>
                            <!-- BEGIN values -->
                                <div>
                                    <input {formfields.switch_field.switch_radio.values.CHECKED} type="radio" name="{formfields.switch_field.switch_radio.values.NAME}" value="{formfields.switch_field.switch_radio.values.VALUE}" />
                                    {formfields.switch_field.switch_radio.values.VALUE}
                                </div>
                            <!-- END values -->
                            </span>
                        <!-- END switch_radio -->

                        <!-- BEGIN switch_file -->
                        <input type="file" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" value="{formfields.switch_field.VALUE}" tabindex="{formfields.switch_field.TABINDEX}" />
                        <!-- END switch_file -->

                        <!-- BEGIN switch_color -->
                        <select class="select" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" onchange="this.style.backgroundColor=this.value;" tabindex="{formfields.switch_field.TABINDEX}">
                            <!-- BEGIN values -->
                            <option {formfields.switch_field.switch_color.values.SELECTED} value="{formfields.switch_field.switch_color.values.VALUE}" style="background-color:{formfields.switch_field.switch_color.values.VALUE};">&nbsp;</option>
                            <!-- END values -->
                        </select>
                        <!-- END switch_color -->
                    </p>
                <!-- END switch_field -->
                <!-- BEGIN switch_separator -->
                <h{formfields.switch_separator.LEVEL}>{formfields.switch_separator.NAME}</h{formfields.switch_separator.LEVEL}>
                <!-- END switch_separator -->
            <!-- END formfields -->
        </div>
        <div class="ploopi_overflowauto">
            <div style="float:right;">
                <!-- BEGIN formbuttons -->
                <input class="button" type="{formbuttons.TYPE}" value="{formbuttons.VALUE}" {formbuttons.OPTION}>
                <!-- END formbuttons -->
                <!-- BEGIN switch_formvalidation -->
                <input class="button" type="reset" value="Réinitialiser">
                <input class="button" type="submit" value="Envoyer">
                <!-- END switch_formvalidation -->
            </div>
            <div style="float:left;">(*) Champs requis</div>
        </div>
        <iframe style="display:none;" name="iframe_form" src="./img/blank.gif"></iframe>
        </form>
    </div>
</div>
