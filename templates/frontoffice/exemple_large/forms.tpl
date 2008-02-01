<form name="form_display" action="{ACTION}" method="post" enctype="multipart/form-data" onsubmit="javascript:eval(form_validate);return(result);">
<input type="hidden" name="op" value="saveform">
<input type="hidden" name="forms_id" value="{FORM_ID}">
<div class="form">
	<!-- BEGIN formfields -->
		<!-- BEGIN switch_field -->
			<p>
				<label for="{formfields.switch_field.LABELID}">
					{formfields.switch_field.LABEL}
				<!-- BEGIN switch_required -->
				(*)
				<!-- END switch_required -->
				</label>
				<!-- BEGIN switch_autoincrement -->
				<span>{formfields.switch_field.VALUE}</span>
				<!-- END switch_autoincrement -->

				<!-- BEGIN switch_text -->
				<input type="text" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" value="{formfields.switch_field.VALUE}" tabindex="{formfields.switch_field.TABINDEX}" />
				<!-- END switch_text -->

				<!-- BEGIN switch_text_date -->
				<input type="text" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" value="{formfields.switch_field.VALUE}" tabindex="{formfields.switch_field.TABINDEX}" />
				<!-- END switch_text_date -->

				<!-- BEGIN switch_textarea -->
				<textarea id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" tabindex="{formfields.switch_field.TABINDEX}">{formfields.switch_field.VALUE}</textarea>
				<!-- END switch_textarea -->

				<!-- BEGIN switch_select -->
				<select id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" tabindex="{formfields.switch_field.TABINDEX}">
					<!-- BEGIN values -->
					<option {formfields.switch_field.switch_select.values.SELECTED} value="{formfields.switch_field.switch_select.values.VALUE}">{formfields.switch_field.switch_select.values.VALUE}</option>
					<!-- END values -->
				</select>
				<!-- END switch_select -->

				<!-- BEGIN switch_checkbox -->
					<span class="check">
					<!-- BEGIN values -->
						<div>
							<input {formfields.switch_field.switch_checkbox.values.CHECKED} type="checkbox" class="checkbox" name="{formfields.switch_field.switch_checkbox.values.NAME}" value="{formfields.switch_field.switch_checkbox.values.VALUE}" />
							{formfields.switch_field.switch_checkbox.values.VALUE}
						</div>
					<!-- END values -->
					</span>
				<!-- END switch_checkbox -->

				<!-- BEGIN switch_radio -->
					<span class="check">
					<!-- BEGIN values -->
						<div>
							<input {formfields.switch_field.switch_radio.values.CHECKED} type="radio" class="radio" name="{formfields.switch_field.switch_radio.values.NAME}" value="{formfields.switch_field.switch_radio.values.VALUE}" />
							{formfields.switch_field.switch_radio.values.VALUE}
						</div>
					<!-- END values -->
					</span>
				<!-- END switch_radio -->

				<!-- BEGIN switch_file -->
				<input type="file" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" value="{formfields.switch_field.VALUE}" tabindex="{formfields.switch_field.TABINDEX}" />
				<!-- END switch_file -->

				<!-- BEGIN switch_color -->
				<select id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" onchange="this.style.backgroundColor=this.value;" tabindex="{formfields.switch_field.TABINDEX}">
					<!-- BEGIN values -->
					<option {formfields.switch_field.switch_color.values.SELECTED} value="{formfields.switch_field.switch_color.values.VALUE}" style="background-color:{formfields.switch_field.switch_color.values.VALUE};">&nbsp;</option>
					<!-- END values -->
				</select>
				<!-- END switch_color -->
			</p>
		<!-- END switch_field -->
	<!-- END formfields -->
</div>
<div class="form_validate">(*) Champs requis</div>
<div class="form_validate">
	<input type="reset" value="Annuler">
	<input type="submit" value="Envoyer">
</div>
</form>
