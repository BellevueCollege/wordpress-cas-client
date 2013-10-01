function showPasswordField()
{
	var buttonReset = document.getElementById("reset");
	var ldapPasswordField = document.getElementById("ldap_password_inp");
	ldapPasswordField.style.display = "";
	 ldapPasswordField.disabled = false;
	 buttonReset.style.display = "none";
}