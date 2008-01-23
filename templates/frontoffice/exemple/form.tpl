<form name="form_display" action="{ACTION}" method="post" enctype="multipart/form-data" onsubmit="javascript:return form_validate(this);">
<input type="hidden" name="opp" value="saveform">
<input type="hidden" name="forms_id" value="{FORM_ID}">
<!--div class="form_title">{FORM_TITLE}</div-->
<div class="form_description">{FORM_DESCRIPTION}</div>
<!-- BEGIN formfields -->
	<!-- BEGIN switch_field -->
	<div class="form_row" style="margin-top:{formfields.switch_field.INTERLINE}px;">
		<span class="label"><label for="{formfields.switch_field.LABELID}">{formfields.switch_field.LABEL}:</label></span>
		<span class="field">

			<!-- BEGIN switch_autoincrement -->
			{formfields.switch_field.VALUE}
			<!-- END switch_autoincrement -->

			<!-- BEGIN switch_text -->
			<input type="text" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" class="form_text" value="{formfields.switch_field.VALUE}">
			<!-- END switch_text -->

			<!-- BEGIN switch_textarea -->
			<textarea id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" class="form_textarea">{formfields.switch_field.VALUE}</textarea>
			<!-- END switch_textarea -->

			<!-- BEGIN switch_select -->
			<select id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" class="form_select">
				<!-- BEGIN values -->
				<option {formfields.switch_field.switch_select.values.SELECTED} value="{formfields.switch_field.switch_select.values.VALUE}">{formfields.switch_field.switch_select.values.VALUE}</option>
				<!-- END values -->
			</select>
			<!-- END switch_select -->

			<!-- BEGIN switch_checkbox -->
				<!-- BEGIN values -->
				<div>
					<div class="form_checkzone"><input {formfields.switch_field.switch_checkbox.values.CHECKED} type="checkbox" class="form_checkbox" name="{formfields.switch_field.switch_checkbox.values.NAME}" value="{formfields.switch_field.switch_checkbox.values.VALUE}"></div>
					<div class="form_checkvalue">{formfields.switch_field.switch_checkbox.values.VALUE}</div>
				</div>
				<!-- END values -->
			<!-- END switch_checkbox -->

			<!-- BEGIN switch_radio -->
				<!-- BEGIN values -->
				<div>
					<div class="form_checkzone"><input {formfields.switch_field.switch_radio.values.CHECKED} type="radio" class="form_checkbox" name="{formfields.switch_field.switch_radio.values.NAME}" value="{formfields.switch_field.switch_radio.values.VALUE}"></div>
					<div class="form_checkvalue">{formfields.switch_field.switch_radio.values.VALUE}</div>
				</div>
				<!-- END values -->
			<!-- END switch_radio -->

			<!-- BEGIN switch_file -->
			<input type="file" id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" class="form_text" value="{formfields.switch_field.VALUE}">
			<!-- END switch_file -->

			<!-- BEGIN switch_color -->
			<select id="{formfields.switch_field.LABELID}" name="{formfields.switch_field.NAME}" class="form_color" onchange="this.style.backgroundColor=this.value;">
				<!-- BEGIN values -->
				<option {formfields.switch_field.switch_color.values.SELECTED} value="{formfields.switch_field.switch_color.values.VALUE}" style="background-color:{formfields.switch_field.switch_color.values.VALUE};">&nbsp;</option>
				<!-- END values -->
			</select>
			<!-- END switch_color -->
		</span>
	</div>
	<div class="form_row_description">
		<span></span>
		<span>{formfields.switch_field.DESCRIPTION}</span>
	</div>
	<!-- END switch_field -->

	<!-- BEGIN switch_separator -->
	<div class="form_row">
		<div class="form_sep_level{formfields.switch_separator.LEVEL}">{formfields.switch_separator.NAME}</div>
	</div>
	<!-- END switch_separator -->
<!-- END formfields -->

<div class="form_row_validate">
	<input type="reset" value="Annuler">
	<input type="submit" value="Envoyer">
</div>
</form>
