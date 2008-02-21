<? echo $skin->open_simplebloc($title.' / '._DIRECTORY_ADDNEWCONTACT,'100%'); ?>

<table cellpadding="2" cellspacing="1">
<form action="<? echo $scriptenv; ?>" method="post">
<input type="hidden" name="op" value="directory_save">
<tr>
	<td valign="top">
		<table cellpadding="2" cellspacing="1">
		<tr>
			<td><? echo _DIRECTORY_NAME; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_name" SIZE="30"></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_FIRSTNAME; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_firstname" SIZE="30"></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_SERVICE; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_service" SIZE="30"></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_FUNCTION; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_function" SIZE="30"></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_PHONE; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_phone" SIZE="30"></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_MOBILE; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_mobile" SIZE="30"></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_FAX; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_fax" SIZE="30"></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_EMAIL; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_email" SIZE="30"></td>
		</tr>
		<tr>
			<td valign="top"><? echo _DIRECTORY_COMMENTARY; ?>:&nbsp;</td>
			<td><TEXTAREA CLASS="text" NAME="directory_contact_commentary" COLS="30" ROWS="5"></TEXTAREA></td>
		</tr>
		</table>
	</td>
	<td valign="top">
		<table cellpadding="2" cellspacing="1">
		<tr>
			<td valign="top"><? echo _DIRECTORY_ADDRESS; ?>:&nbsp;</td>
			<td><TEXTAREA CLASS="text" NAME="directory_contact_address" COLS="30" ROWS="5"></TEXTAREA></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_POSTALCODE; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_postalcode" SIZE="30"></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_CITY; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_city" SIZE="30"></td>
		</tr>
		<tr>
			<td><? echo _DIRECTORY_COUNTRY; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_country" SIZE="30"></td>
		</tr>
		</table>
	</td>
</tr>

<tr>
	<td colspan="2" align="right"><input type="submit" class="button" value="<? echo _DIMS_SAVE; ?>"></td>
</tr>
</table>
<?
echo $skin->close_simplebloc();
?> 
