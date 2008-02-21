<?
	include_once ('./modules/system/class_user.php');
	$user = new user();
	$user->open($user_id);
?>


<? echo $skin->open_simplebloc($title.' / '._DIRECTORY_VIEWCONTACT,'100%'); ?>

<table cellpadding="2" cellspacing="1">
<tr>
	<td valign="top">
	<table cellpadding="2" cellspacing="1">
		<tr>
			<td align="right"><b><? echo _DIRECTORY_NAME; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['lastname']); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_FIRSTNAME; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['firstname']); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_SERVICE; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['service']); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_FUNCTION; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['function']); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_PHONE; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['phone']); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_MOBILE; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['mobile']); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_FAX; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['fax']); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_EMAIL; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['email']); ?></td>
		</tr>
		<tr>
			<td align="right" valign="top"><b><? echo _DIRECTORY_COMMENTARY; ?>:&nbsp;</b></td>
			<td><? echo nl2br(htmlentities($user->fields['comments'])); ?></td>
		</tr>
		</table>
	</td>

	<td valign="top">
		<table cellpadding="2" cellspacing="1">
		<tr>
			<td align="right" valign="top"><b><? echo _DIRECTORY_ADDRESS; ?>:&nbsp;</b></td>
			<td><? echo nl2br(htmlentities($user->fields['address'])); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_POSTALCODE; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['postalcode']); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_CITY; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['city']); ?></td>
		</tr>
		<tr>
			<td align="right"><b><? echo _DIRECTORY_COUNTRY; ?>:&nbsp;</b></td>
			<td><? echo htmlentities($user->fields['country']); ?></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td colspan="2" align="right"><input type="button" class="button" onclick="javascript:document.location.href='<? echo $scriptenv; ?>'" value="<? echo _DIRECTORY_BACK; ?>"></td>
</tr>
</table>
<?
echo $skin->close_simplebloc();
?> 
