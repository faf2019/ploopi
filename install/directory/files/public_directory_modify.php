<?
	$directory_contact = new directory_contact();
	$directory_contact->open($contact_id);
?>


<? echo $skin->open_simplebloc($title.' / '._DIRECTORY_MODIFYCONTACT,'100%'); ?>

<table cellpadding="2" cellspacing="1">
<form action="<? echo $scriptenv; ?>" method="post">
<input type="hidden" name="op" value="directory_save">
<input type="hidden" name="contact_id" value="<? echo $contact_id; ?>">
<tr>
	<td valign="top">
	<table cellpadding="2" cellspacing="1">
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_NAME; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_name" VALUE="<? echo htmlentities($directory_contact->fields['name']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_FIRSTNAME; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_firstname" VALUE="<? echo htmlentities($directory_contact->fields['firstname']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_SERVICE; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_service" VALUE="<? echo htmlentities($directory_contact->fields['service']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_FUNCTION; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_function" VALUE="<? echo htmlentities($directory_contact->fields['function']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_PHONE; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_phone" VALUE="<? echo htmlentities($directory_contact->fields['phone']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_MOBILE; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_mobile" VALUE="<? echo htmlentities($directory_contact->fields['mobile']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_FAX; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_fax" VALUE="<? echo htmlentities($directory_contact->fields['fax']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_EMAIL; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_email" VALUE="<? echo htmlentities($directory_contact->fields['email']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT" valign="top"><? echo _DIRECTORY_COMMENTARY; ?>:&nbsp;</td>
			<td><TEXTAREA CLASS="text" NAME="directory_contact_commentary" COLS="30" ROWS="5"><? echo htmlentities($directory_contact->fields['commentary']); ?></TEXTAREA></td>
		</tr>
		</table>
	</td>

	<td valign="top">
		<table cellpadding="2" cellspacing="1">
		<tr>
			<td align="RIGHT" valign="top"><? echo _DIRECTORY_ADDRESS; ?>:&nbsp;</td>
			<td><TEXTAREA CLASS="text" NAME="directory_contact_address" COLS="30" ROWS="5"><? echo htmlentities($directory_contact->fields['address']); ?></TEXTAREA></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_POSTALCODE; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_postalcode" VALUE="<? echo htmlentities($directory_contact->fields['postalcode']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_CITY; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_city" VALUE="<? echo htmlentities($directory_contact->fields['city']); ?>" SIZE="30"></td>
		</tr>
		<tr>
			<td align="RIGHT"><? echo _DIRECTORY_COUNTRY; ?>:&nbsp;</td>
			<td><INPUT TYPE="TEXT" CLASS="text" NAME="directory_contact_country" VALUE="<? echo htmlentities($directory_contact->fields['country']); ?>" SIZE="30"></td>
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
