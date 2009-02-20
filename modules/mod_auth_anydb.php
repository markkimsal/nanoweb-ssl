<?php

/*


auth module for PEAR::DB / ADOdb / dbx_() supported RDBMS


#-- usage

AnydbLoadInterface = .../share/php/ado/adodb.inc.php

AuthRealm      = your auth realm name here
AuthRequire    = ANYDB
AuthAnydb      = mysql://dbuser:password@localhost/database_name/table_name
AuthAnydbLoginColumn = login_field_name
AuthAnydbPasswordColumn = password_field_name

# * beware the additional "table_name" in the dsn://, and that the RDBMS 
#   type (URL sheme) differs between PEAR, ADO and dbx_
# * the password encryption is auto-probed (md5, crypt, plain)


*/


class mod_auth_anydb {

	function mod_auth_anydb() {

		$this->modtype="auth_anydb";
		$this->modname="ANY database authentication (ADO, PEAR, dbx)";

		if (!function_exists("newadoconnection") && !class_exists("PEAR_DB") && ($load_include = access_query("anydbloadinterface", 0))) {

			include_once($load_include);
			techo("loaded : $load_include");

		}

	}



	function auth($user, $pass, $args) {

		$r = $db_pw = false;

		$dsn = access_query("authanydb", 0);
		($col_login = access_query("authanydblogincolumn", 0)) or ($col_login = "login");
		($col_pass = access_query("authanydbpasswordcolumn", 0)) or ($col_pass = "password");

		$desc = parse_url($dsn);
		$desc["database"] = strtok($desc["path"], "/");
		$table = strtok("/");

		$dsn = substr($dsn, 0, strrpos($dsn, "/"));

		if (function_exists("newadoconnection") && ($db = NewAdoConnection($desc["scheme"])) && ($db->connect($desc["host"], $desc["user"], $desc["pass"], $desc["database"])) ) {

			$user = $db->qstr($user);
			$SQL = "SELECT $col_pass FROM $table WHERE $col_login=$user";

			if ($row = $db->GetRow($SQL)) {

				$db_pw = $row[0];
			}

			$db->Close();

		}
		elseif (class_exists("DB")) {

			$db = DB::connect($dsn);

			$user = $db->quoteString($user);
			$SQL = "SELECT $col_pass FROM $table WHERE $col_login='$user'";

			if ($row = $db->getRow($SQL)) {

				$db_pw = $row[0];
			}

		}
		elseif (function_exists("dbx_connect") && ($db = dbx_connect($desc["scheme"],$desc["host"],$desc["database"],$desc["user"],$desc["pass"])) ) {

			$user = dbx_escape_string($db, $user);
			$SQL = "SELECT $col_pass FROM $table WHERE $col_login='$user'";

			if ($result = dbx_query($db, $SQL)) {

				$db_pw = $result->data[0][0];
			}

			dbx_close($db);

		}
		else {
			techo("mod_auth_anydb: no database interface used (db auth problem?)", NW_EL_WARNING);

			return($r = false);
		}


		$r = strlen($db_pw) && strlen($pass) && (
			($db_pw == $pass) or
			($db_pw == crypt($pass, substr($db_pw, 0, 2))) or
			($db_pw == md5($pass))
		);

		return($r);
	}

}

?>
