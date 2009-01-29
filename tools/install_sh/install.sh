#!/bin/sh

# Generate temporary file
TMP_FILE=`tempfile 2>/dev/null` || TMP_FILE=/tmp/ploopiinstall$$

# On exit delete temporary file
trap "rm -f $TMP_FILE" 0 1 2 5 15

export TEXTDOMAIN="ploopi_console"
export TEXTDOMAINDIR="./config"
#set -x

. gettext.sh


ret=0

# Location of the licence file
LICENCE="./doc/LICENSE"

# PARAMETERS that should be set with default values
_PLOOPI_PEARPATH="/usr/share/php"
_PLOOPI_INTERNETPROXY_HOST=""
_PLOOPI_INTERNETPROXY_PORT=""
_PLOOPI_INTERNETPROXY_USER=""
_PLOOPI_INTERNETPROXY_PASS=""
_PLOOPI_ADM_LOGIN="admin" 
_PLOOPI_ADM_PWD="admin" 
_PLOOPI_PATHDATA="./data" 
_PLOOPI_CGI_UPLOADTMP="/tmp"
_PLOOPI_CGI_PATH="./cgi"
_PLOOPI_SQL_LAYER="mysql"
_PLOOPI_DB_SERVER="localhost" 
_PLOOPI_DB_LOGIN="root"
_PLOOPI_DB_PASSWORD=""
_PLOOPI_DB_DATABASE=""
_PLOOPI_USE_CGIUPLOAD=""
_PLOOPI_USE_DBSESSION=""
_PLOOPI_ADMINMAIL=""
_PLOOPI_URL_ENCODE="true"
_PLOOPI_SECRETKEY="`eval_gettext \"_secret_key\"`"
_PLOOPI_FRONTOFFICE="true"
_PLOOPI_FRONTOFFICE_REWRITERULE="true"

# Load parameters set for this user for the previous installation
if [ -e $HOME/.ploopi/defaults ]; then
    . $HOME/.ploopi/defaults
fi

# I18N
LBL_DATA_PATH="`eval_gettext \"_data_directory: \"`"
LBL_TMP_PATH="`eval_gettext \"_temp_directory: \"`"
LBL_ADM_LOGIN="`eval_gettext \"_admin_login:\"`"
LBL_ADM_PWD="`eval_gettext \"_admin_password:\"`"
LBL_ADM_MAIL="`eval_gettext \"_admin_email:\"`"
LBL_SALT="`eval_gettext \"_secret_key:\"`"
LBL_URL_ENCODE="`eval_gettext \"_encode_visible_urls_?\"`"
LBL_BDD_SESSION="`eval_gettext \"_store_session_in_database_?\"`"
LBL_PLOOPI_PARAM="`eval_gettext \"_Ploopi_configuration\"`"
LBL_PLOOPI_CGI_PARAM="`eval_gettext \"_CGI_scripts_usage\"`"
LBL_PLOOPI_CGI="`eval_gettext \"_CGI_activation_?\"`"
LBL_PLOOPI_CGI_DIR="`eval_gettext \"_CGI_directory:\"`"
LBL_LICENCE_TITLE="`eval_gettext \"_GPL_licence\"`"
LBL_PEAR_PATH="`eval_gettext \"_PEAR_install_directory\"`"
LBL_PLOOPI_FRONTOFFICE_PARAM="`eval_gettext \"_FrontOffice_configuration\"`"
LBL_FRONTOFFICE_ACTIVATION="`eval_gettext \"_FrontOffice_activation_?\"`"
LBL_FRONTOFFICE_URL_REWRITE="`eval_gettext \"_URL rewrite_activation_?\"`"
LBL_SHOW_LICENCE="`eval_gettext \"_read_licence\"`"
LBL_LICENCE_QUESTION="`eval_gettext \"_\n\nPloopi_is_distributed_under_the_GNU_GPL.\n\ndo_you_accept_the_Ploopi licence_?\n\n\"`"
LBL_PLOOPI_ADVANCED_CONFIG="`eval_gettext \"_advanced_configuration\"`"
LBL_PLOOPI_PROXY_CONFIG="`eval_gettext \"_do_you_want_to_configure_a_proxy_?\"`"
LBL_PROXY_ADDRESS="`eval_gettext \"_address:\"`"
LBL_PROXY_PORT="`eval_gettext \"_port:\"`"
LBL_PROXY_LOGIN="`eval_gettext \"_user:\"`"
LBL_PROXY_PWD="`eval_gettext \"_password:\"`"
LBL_CONFIG_DB="`eval_gettext \"_database_configuration\"`"
LBL_CONFIG_DB_TYPE="`eval_gettext \"_database_type\"`"
LBL_DB_SERVER="`eval_gettext \"_address:\"`"
LBL_DB_LOGIN="`eval_gettext \"_user:\"`"
LBL_DB_PWD="`eval_gettext \"_password:\"`"
LBL_EXIT="`eval_gettext \"_do_you_really_want_to_stop_installation_?\"`"
LBL_CHOOSE_DB="`eval_gettext \"_select_a_database\"`"
LBL_NEW_DB="`eval_gettext \"_<new_database>\"`"
LBL_ERROR="`eval_gettext \"_error\"`"
LBL_ERROR_MANDATORY_FIELD="`eval_gettext \"_a_mandatory_field_was_left_blank\"`"
LBL_ERROR_DB_CONNECT="`eval_gettext \"_connexion_to_the_database_cannot_be_established\"`"
LBL_ERROR_DB_IS_PLOOPI="`eval_gettext \"_the_database_is_already_a_ploopi\"`"
LBL_ERROR_WRONG_DIR="`eval_gettext \"_the_install_script_must_be_executed_from_the_root_of_de_Ploopi_directory\"`"
LBL_ERROR_CONFIGW="`eval_gettext \"_you_do_not_have_write_access_to_the_config_directory\"`"
LBL_ERROR_INSTALL_FAILED="`eval_gettext \"_installation_failed\"`"
LBL_ERROR_INSTALL_SUCCEEDED="`eval_gettext \"_installation_succeeded\"`"

# Check that the script is launched in the ROOT directory
# of PLOOPI site
if [ ! -e ./include/start/constants.php ]; then
    dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_WRONG_DIR" 0 0
    exit 1
fi

# We should have write access in the configuration directory
if [ ! -w ./config ]; then
    dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_CONFIGW" 0 0
    exit 1
fi

# Get PLOOPI version
_PLOOPI_VERSION=`grep '_PLOOPI_VERSION' ./include/start/constants.php|cut -d ',' -f 2 |sed -e "s/[\'\(\); ]//g"`

# Back title for installation
TITLE="PLOOPI $_PLOOPI_VERSION"


# Debug: print all parameters
function print_config()
{
    echo "_PLOOPI_PEARPATH='$_PLOOPI_PEARPATH'"
    echo "_PLOOPI_INTERNETPROXY_HOST='$_PLOOPI_INTERNETPROXY_HOST'"
    echo "_PLOOPI_INTERNETPROXY_PORT='$_PLOOPI_INTERNETPROXY_PORT'"
    echo "_PLOOPI_INTERNETPROXY_USER='$_PLOOPI_INTERNETPROXY_USER'"
    echo "_PLOOPI_INTERNETPROXY_PASS='$_PLOOPI_INTERNETPROXY_PASS'"
    echo "_PLOOPI_ADM_LOGIN='$_PLOOPI_ADM_LOGIN'" 
    echo "_PLOOPI_ADM_PWD='$_PLOOPI_ADM_PWD'" 
    echo "_PLOOPI_PATHDATA='$_PLOOPI_PATHDATA'" 
    echo "_PLOOPI_CGI_UPLOADTMP='$_PLOOPI_CGI_UPLOADTMP'"
    echo "_PLOOPI_CGI_PATH='$_PLOOPI_CGI_PATH'"
    echo "_PLOOPI_SQL_LAYER='$_PLOOPI_SQL_LAYER'"
    echo "_PLOOPI_DB_SERVER='$_PLOOPI_DB_SERVER'" 
    echo "_PLOOPI_DB_LOGIN='$_PLOOPI_DB_LOGIN'"
    echo "_PLOOPI_DB_PASSWORD='$_PLOOPI_DB_PASSWORD'"
    echo "_PLOOPI_DB_DATABASE='$_PLOOPI_DB_DATABASE'"
    echo "_PLOOPI_USE_CGIUPLOAD='$_PLOOPI_USE_CGIUPLOAD'"
    echo "_PLOOPI_USE_DBSESSION='$_PLOOPI_USE_DBSESSION'"
    echo "_PLOOPI_ADMINMAIL='$_PLOOPI_ADMINMAIL'"
    echo "_PLOOPI_URL_ENCODE='$_PLOOPI_URL_ENCODE'"
    echo "_PLOOPI_SECRETKEY='$_PLOOPI_SECRETKEY'"
    echo "_PLOOPI_FRONTOFFICE='$_PLOOPI_FRONTOFFICE'"
    echo "_PLOOPI_FRONTOFFICE_REWRITERULE='$_PLOOPI_FRONTOFFICE_REWRITERULE'"
}

# Write parameters in a per-user file for later use
function write_config()
{
    mkdir -p $HOME/.ploopi
    echo "_PLOOPI_PEARPATH='$_PLOOPI_PEARPATH'">$HOME/.ploopi/defaults
    echo "_PLOOPI_INTERNETPROXY_HOST='$_PLOOPI_INTERNETPROXY_HOST'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_INTERNETPROXY_PORT='$_PLOOPI_INTERNETPROXY_PORT'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_INTERNETPROXY_USER='$_PLOOPI_INTERNETPROXY_USER'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_ADM_LOGIN='$_PLOOPI_ADM_LOGIN'" >>$HOME/.ploopi/defaults
    echo "_PLOOPI_PATHDATA='$_PLOOPI_PATHDATA'" >>$HOME/.ploopi/defaults
    echo "_PLOOPI_CGI_UPLOADTMP='$_PLOOPI_CGI_UPLOADTMP'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_CGI_PATH='$_PLOOPI_CGI_PATH'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_SQL_LAYER='$_PLOOPI_SQL_LAYER'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_DB_SERVER='$_PLOOPI_DB_SERVER'" >>$HOME/.ploopi/defaults
    echo "_PLOOPI_DB_LOGIN='$_PLOOPI_DB_LOGIN'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_DB_DATABASE='$_PLOOPI_DB_DATABASE'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_USE_CGIUPLOAD='$_PLOOPI_USE_CGIUPLOAD'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_USE_DBSESSION='$_PLOOPI_USE_DBSESSION'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_ADMINMAIL='$_PLOOPI_ADMINMAIL'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_URL_ENCODE='$_PLOOPI_URL_ENCODE'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_SECRETKEY='$_PLOOPI_SECRETKEY'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_FRONTOFFICE='$_PLOOPI_FRONTOFFICE'">>$HOME/.ploopi/defaults
    echo "_PLOOPI_FRONTOFFICE_REWRITERULE='$_PLOOPI_FRONTOFFICE_REWRITERULE'">>$HOME/.ploopi/defaults

}

function check_for_exit()
{
    dialog --backtitle "$TITLE" --yesno "$LBL_EXIT" 0 0
    ret=$?
    if [ $ret -eq 0 ]; then
        rm -f "$TMP_FILE"
        exit 1
    fi
}
function get_pear_path()
{
    PEAR_BIN=`which pear`
    if [ -n "$PEAR_BIN" ]; then
        PEAR_PATH=`pear config-get php_dir`
        if [ -n "$_PEAR_PATH" ]; then
            _PLOOPI_PEARPATH="$PEAR_PATH"
        fi
    fi
    ret=1
    while [ $ret -ne 0 ]
    do
        dialog --clear --backtitle "$TITLE" --inputbox "* $LBL_PEAR_PATH" 0 0 "${_PLOOPI_PEARPATH}" 2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            check_for_exit
            $ret=1
        else
            read -d '' _PLOOPI_PEARPATH < $TMP_FILE
            if [ -z "$_PLOOPI_PEARPATH" ]; then
                dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_MANDATORY_FIELD" 0 0
                ret=1
            fi
        fi
    done
}

function dialog_proxy_param()
{
    ret=1
    while [ $ret -ne 0 ]
    do
        dialog --clear --backtitle "$TITLE" --title "$LBL_PLOOPI_PARAM" --form \
            "" \
            0 0 0\
            "$LBL_PROXY_ADDRESS" 1 0 "$_PLOOPI_INTERNETPROXY_HOST" 1 `echo $LBL_PROXY_ADDRESS|wc -m` 20 200 \
            "$LBL_PROXY_PORT" 3 0 "$_PLOOPI_INTERNETPROXY_PORT" 3 `echo $LBL_PROXY_PORT|wc -m` 20 50 \
            "$LBL_PROXY_LOGIN" 5 0 "$_PLOOPI_INTERNETPROXY_USER" 5 `echo $LBL_PROXY_LOGIN|wc -m` 20 50 \
            "$LBL_PROXY_PWD" 7 0 "$_PLOOPI_INTERNETPROXY_PASS" 7 `echo $LBL_PROXY_PWD|wc -m` 20 50 \
            2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            check_for_exit
            $ret=1
        fi
    done
    _PLOOPI_INTERNETPROXY_HOST=`sed -n "1 p"<$TMP_FILE` 
    _PLOOPI_INTERNETPROXY_PORT=`sed -n "2 p"<$TMP_FILE` 
    _PLOOPI_INTERNETPROXY_USER=`sed -n "3 p"<$TMP_FILE` 
    _PLOOPI_INTERNETPROXY_PASS=`sed -n "4 p"<$TMP_FILE` 
    #read -d '' _PLOOPI_INTERNETPROXY_HOST _PLOOPI_INTERNETPROXY_PORT _PLOOPI_INTERNETPROXY_USER _PLOOPI_INTERNETPROXY_PASS < $TMP_FILE
}

function dialog_ploopi_param()
{
    ret=1
    while [ $ret -ne 0 ]
    do
        dialog --clear --backtitle "$TITLE" --title "$LBL_PLOOPI_PARAM" --form \
            "" \
            0 0 0\
            "* $LBL_ADM_LOGIN" 1 0 "$_PLOOPI_ADM_LOGIN" 1 `echo "* $LBL_ADM_LOGIN"|wc -m` 20 50 \
            "* $LBL_ADM_PWD" 3 0 "$_PLOOPI_ADM_PWD" 3 `echo "* $LBL_ADM_PWD"|wc -m` 20 50 \
            "* $LBL_SALT" 5 0 "$_PLOOPI_SECRETKEY" 5 `echo "* $LBL_SALT"|wc -m` 20 50 \
            "$LBL_ADM_MAIL" 7 0 "$_PLOOPI_ADMINMAIL" 7 `echo $LBL_ADM_MAIL|wc -m` 20 50 \
            "* $LBL_DATA_PATH" 9 0 "$_PLOOPI_PATHDATA" 9 `echo "* $LBL_DATA_PATH"|wc -m` 20 255 \
            "* $LBL_TMP_PATH" 11 0 "$_PLOOPI_CGI_UPLOADTMP" 11 `echo "* $LBL_TMP_PATH"|wc -m` 20 255 \
            2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            check_for_exit
            $ret=1
        else
            _PLOOPI_ADM_LOGIN=`sed -n '1 p'<$TMP_FILE` 
            _PLOOPI_ADM_PWD=`sed -n '2 p'<$TMP_FILE`  
            _PLOOPI_SECRETKEY=`sed -n '3 p'<$TMP_FILE`  
            _PLOOPI_ADMINMAIL=`sed -n '4 p'<$TMP_FILE`  
            _PLOOPI_PATHDATA=`sed -n '5 p'<$TMP_FILE`  
            _PLOOPI_CGI_UPLOADTMP=`sed -n '6 p'<$TMP_FILE`  
            if [ -z "$_PLOOPI_ADM_LOGIN" ]; then
                ret=1
            fi
            if [ -z "$_PLOOPI_ADM_PWD" ]; then
                ret=1
            fi
            if [ -z "$_PLOOPI_SECRETKEY" ]; then
                ret=1
            fi
            if [ -z "$_PLOOPI_PATHDATA" ]; then
                ret=1
            fi
            if [ -z "$_PLOOPI_CGI_UPLOADTMP" ]; then
                ret=1
            fi
            if [ $ret -ne 0 ]; then
                dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_MANDATORY_FIELD" 0 0
            fi
        fi
    done
   #tr '\n' '#' $TMP_FILE|read -d '#' _PLOOPI_ADM_LOGIN _PLOOPI_ADM_PWD _PLOOPI_SECRETKEY _PLOOPI_ADMINMAIL _PLOOPI_PATHDATA _PLOOPI_CGI_UPLOADTMP 
}

function dialog_ploopi_options()
{
    ret=1
    while [ $ret -ne 0 ]
    do
        dialog --clear --backtitle "$TITLE" --title "$LBL_PLOOPI_PARAM" --separate-output --checklist "" 0 0 0 \
            1 "$LBL_URL_ENCODE" "on" \
            2 "$LBL_BDD_SESSION" "on" \
            3 "$LBL_PLOOPI_CGI" "on" \
            4 "$LBL_FRONTOFFICE_ACTIVATION" "on" \
            5 "$LBL_FRONTOFFICE_URL_REWRITE" "on" \
            2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            check_for_exit
            $ret=1
        else
            grep '^1$' "$TMP_FILE"
            ret=$?
            if [ $ret -eq 0 ]; then
                _PLOOPI_URL_ENCODE="true"
            else
                _PLOOPI_URL_ENCODE="false"
            fi

            grep '^2$' "$TMP_FILE"
            ret=$?
            if [ $ret -eq 0 ]; then
                _PLOOPI_USE_DBSESSION="true"
            else
                _PLOOPI_USE_DBSESSION="false"
            fi

            grep '^3$' "$TMP_FILE"
            ret=$?
            if [ $ret -eq 0 ]; then
                _PLOOPI_USE_CGIUPLOAD="true"
            else
                _PLOOPI_USE_CGIUPLOAD="false"
            fi

            grep '^4$' "$TMP_FILE"
            ret=$?
            if [ $ret -eq 0 ]; then
                _PLOOPI_FRONTOFFICE="true"
            else
                _PLOOPI_FRONTOFFICE="false"
            fi

            grep '^5$' "$TMP_FILE"
            ret=$?
            if [ $ret -eq 0 ]; then
                _PLOOPI_FRONTOFFICE_REWRITERULE="true"
            else
                _PLOOPI_FRONTOFFICE_REWRITERULE="false"
            fi
        fi
    done

}

function dialog_ploopi_cgi_dir()
{
    ret=1
    while [ $ret -ne 0 ]
    do
        dialog --clear --backtitle "$TITLE" --title "$LBL_PLOOPI_CGI_PARAM" --inputbox "$LBL_PLOOPI_CGI_DIR" 0 0 "$_PLOOPI_CGI_PATH" 2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            check_for_exit
            $ret=1
        else
            read -d '' _PLOOPI_CGI_PATH < $TMP_FILE
            if [ -z "$_PLOOPI_CGI_PATH" ]; then
                dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_MANDATORY_FIELD" 0 0
            fi
        fi
    done
}

function ploopi_param()
{
    dialog_ploopi_param
 
    dialog_ploopi_options

    dialog_ploopi_cgi_dir

    dialog --clear --backtitle "$TITLE" --title "$LBL_PLOOPI_ADVANCED_CONFIG" --yesno "$LBL_PLOOPI_PROXY_CONFIG" 0 0
    ret=$?
    if [ $ret -eq 0 ]; then
        dialog_proxy_param
    fi 
}

function dialog_db_type()
{
    ret=1
    while [ $ret -ne 0 ]
    do
        dialog --clear --backtitle "$TITLE" --title "$LBL_CONFIG_DB" --radiolist "$LBL_CONFIG_DB_TYPE" 0 0 0 \
            1 "MySQL" "on" \
            2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            check_for_exit
            $ret=1
        fi
    done

    read DB_TYPE < $TMP_FILE
    case "$DB_TYPE" in 
        "1")
            _PLOOPI_SQL_LAYER="mysql"
        ;;
    esac


}

function dialog_db_config()
{
    ret=1
    while [ $ret -ne 0 ]
    do
        dialog --clear --backtitle "$TITLE" --title "$LBL_CONFIG_DB" --form \
            "" \
            0 0 0\
            "* $LBL_DB_SERVER" 1 0 "$_PLOOPI_DB_SERVER" 1 `echo "* $LBL_DB_SERVER"|wc -m` 20 200 \
            "* $LBL_DB_LOGIN" 3 0 "$_PLOOPI_DB_LOGIN" 3 `echo "* $LBL_DB_LOGIN"|wc -m` 20 50 \
            "* $LBL_DB_PWD" 5 0 "$_PLOOPI_DB_PASSWORD" 5 `echo "* $LBL_DB_PWD"|wc -m` 20 50 \
            2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            check_for_exit
            $ret=1
        else
            _PLOOPI_DB_SERVER=`sed -n "1 p"<$TMP_FILE` 
            _PLOOPI_DB_LOGIN=`sed -n "2 p"<$TMP_FILE`  
            _PLOOPI_DB_PASSWORD=`sed -n "3 p"<$TMP_FILE` 
            if [ -z "$_PLOOPI_DB_SERVER" ]; then
                ret=1
            fi
            if [ -z "$_PLOOPI_DB_LOGIN" ]; then
                ret=1
            fi
            if [ -z "$_PLOOPI_DB_PASSWORD" ]; then
                ret=1
            fi
            if [ $ret -ne 0 ]; then
                dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_MANDATORY_FIELD" 0 0
            fi
        fi
    done
    #read -d '' _PLOOPI_DB_SERVER _PLOOPI_DB_LOGIN _PLOOPI_DB_PASSWORD < "$TMP_FILE"

    MYSQL_CMD="mysql -u $_PLOOPI_DB_LOGIN -p$_PLOOPI_DB_PASSWORD -h $_PLOOPI_DB_SERVER"
}

function test_db_access()
{
    # if the database does not exist create it
    if [ $CREATE_NEW_DB -eq 0 ]; then
        $MYSQL_CMD -e "CREATE DATABASE $_PLOOPI_DB_DATABASE" 2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            return $ret
        fi
    fi
    
   
    # connect to database
    $MYSQL_CMD $_PLOOPI_DB_DATABASE -e 'SHOW TABLES' 2>$TMP_FILE
    ret=$?
    if [ $ret -ne 0 ]; then
        return $ret
    fi
    # Is database already a Ploopi
    $MYSQL_CMD $_PLOOPI_DB_DATABASE -e 'SELECT id FROM ploopi_user LIMIT 10' 2>$TMP_FILE
    ret=$?
    if [ $ret -eq 0 ]; then
        echo "$LBL_ERROR_DB_IS_PLOOPI" >$TMP_FILE
        return 1
    fi

 
    # create a table
    $MYSQL_CMD $_PLOOPI_DB_DATABASE -e 'CREATE TABLE ploopi_install_test( id INT(11) NULL )' 2>$TMP_FILE
    ret=$?
    if [ $ret -ne 0 ]; then
        return $ret
    fi

    # insert data in table
    $MYSQL_CMD $_PLOOPI_DB_DATABASE -e 'INSERT INTO ploopi_install_test VALUES ( 11 )' 2>$TMP_FILE
    ret=$?
    if [ $ret -ne 0 ]; then
        return $ret
    fi
    
    # update data in table
    $MYSQL_CMD $_PLOOPI_DB_DATABASE -e 'UPDATE ploopi_install_test SET id=12 WHERE id=11' 2>$TMP_FILE
    ret=$?
    if [ $ret -ne 0 ]; then
        return $ret
    fi
    # delete created entry
    $MYSQL_CMD $_PLOOPI_DB_DATABASE -e 'DELETE FROM ploopi_install_test WHERE id=12' 2>$TMP_FILE
    ret=$?
    if [ $ret -ne 0 ]; then
        return $ret
    fi
    # delete table
    $MYSQL_CMD $_PLOOPI_DB_DATABASE -e 'DROP TABLE ploopi_install_test' 2>$TMP_FILE
    ret=$?
    if [ $ret -ne 0 ]; then
        return $ret
    fi

    # delete database if it did not exist
    if [ $CREATE_NEW_DB -eq 0 ]; then
        $MYSQL_CMD -e "DROP DATABASE $_PLOOPI_DB_DATABASE"  2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            return $ret
        fi
    fi
 
    return 0
}
function dialog_create_new_db()
{
    ret=1
    _PLOOPI_DB_DATABASE=""
    while [ $ret -ne 0 ]
    do
        dialog --clear --backtitle "$TITLE" --title "$LBL_NEW_DB" --inputbox "" 0 0 2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            check_for_exit
            ret=1
        else
            read -d '' _PLOOPI_DB_DATABASE < $TMP_FILE
            if [ -z "$_PLOOPI_DB_DATABASE" ]; then
                dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_MANDATORY_FIELD" 0 0
                ret=1
            fi
        fi
    done
}

function check_db_connect()
{
    case "$_PLOOPI_SQL_LAYER" in
        "mysql")
            $MYSQL_CMD mysql -e 'show databases'
        ;;
    esac
    ret=$?
    if [ $ret -ne 0 ]; then
        dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_DB_CONNECT" 0 0
        return 1
    fi
}

function dialog_choose_db()
{
    case "$_PLOOPI_SQL_LAYER" in
        "mysql")
            dialog_menu=`$MYSQL_CMD mysql --skip-column-names -e 'SHOW DATABASES'|grep -v -E '(mysql|information_schema)'|awk '{ print  $1 " [] off" }' | tr '\t\n' ' '` 
        ;;
    esac

    ret=1
    while [ $ret -ne 0 ]
    do
        dialog --clear --backtitle "$TITLE" --radiolist "$LBL_CHOOSE_DB" 0 0 0 "$LBL_NEW_DB" [] on $dialog_menu 2>$TMP_FILE
        ret=$?
        if [ $ret -ne 0 ]; then
            check_for_exit
            ret=1
        else
            read -d '' _PLOOPI_DB_DATABASE < $TMP_FILE
            if [ -z "$_PLOOPI_DB_DATABASE" ]; then
                dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_MANDATORY_FIELD" 0 0
            fi
        fi
    done
    case $_PLOOPI_DB_DATABASE in
        "$LBL_NEW_DB")
            CREATE_NEW_DB=0
            dialog_create_new_db
        ;;
        *)
            CREATE_NEW_DB=1
        ;;
    esac
}

function database_param()
{
    dialog_db_type
    connect_test=1
    while [ $connect_test -ne 0 ]
    do
        dialog_db_config
        # Connectivity test
        check_db_connect
        connect_test=$?
    done
    
    access_test=1
    while [ $access_test -ne 0 ]
    do
        dialog_choose_db
        test_db_access
        access_test=$?
        if [ $access_test -ne 0 ]; then
            msg=`cat $TMP_FILE` 
            dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$msg" 0 0 
        fi
    done

}

function licence()
{
    dialog --clear --backtitle "$TITLE" --title "$LBL_LICENCE_TITLE" --textbox "$LICENCE" 23 76 
    $ret = $?
}

function install_ploopi()
{
	if [ -e "./config/config.php" ]; then
		cp ./config/config.php ./config/config.php.backup
	fi
	iconv --from ISO-8859-15 ./config/config.php.model > $TMP_FILE
    if [ $? -ne 0 ]; then
        return 1
    fi
    CONF_FILE="$TMP_FILE"

#    _PLOOPI_DB_PASSWORD=`echo "$_PLOOPI_DB_PASSWORD"|tr "/" "\\\\"`
#    _PLOOPI_PATHDATA=`echo "$_PLOOPI_PATHDATA"|tr "/" "\\\\"`
#    _PLOOPI_CGI_PATH=`echo "$_PLOOPI_CGI_PATH"|tr "/" "\\\\"`
#    _PLOOPI_CGI_UPLOADTMP=`echo "$_PLOOPI_CGI_UPLOADTMP"|tr "/" "\\\\"`
#    _PLOOPI_PEARPATH=`echo "$_PLOOPI_PEARPATH"|tr "/" "\\\\"`

    sed -i -r -e "s|<DB_TYPE>|$_PLOOPI_SQL_LAYER|" $CONF_FILE
    sed -i -r -e "s|<DB_SERVER>|$_PLOOPI_DB_SERVER|" $CONF_FILE
    sed -i -r -e "s|<DB_LOGIN>|$_PLOOPI_DB_LOGIN|" $CONF_FILE
    sed -i -r -e "s|<DB_PASSWORD>|$_PLOOPI_DB_PASSWORD|" $CONF_FILE
    sed -i -r -e "s|<DB_DATABASE>|$_PLOOPI_DB_DATABASE|" $CONF_FILE
    sed -i -r -e "s|<DATAPATH>|$_PLOOPI_PATHDATA|" $CONF_FILE
    sed -i -r -e "s|<CGI>|$_PLOOPI_USE_CGIUPLOAD|" $CONF_FILE
    sed -i -r -e "s|<CGIPATH>|$_PLOOPI_CGI_PATH|" $CONF_FILE
    sed -i -r -e "s|<TMPPATH>|$_PLOOPI_CGI_UPLOADTMP|" $CONF_FILE
    sed -i -r -e "s|<USE_DBSESSION>|$_PLOOPI_USE_DBSESSION|" $CONF_FILE
    sed -i -r -e "s|<ADMIN_MAIL>|$_PLOOPI_ADMINMAIL|" $CONF_FILE
    sed -i -r -e "s|<URL_ENCODE>|$_PLOOPI_URL_ENCODE|" $CONF_FILE
    sed -i -r -e "s|<SECRETKEY>|$_PLOOPI_SECRETKEY|" $CONF_FILE
    sed -i -r -e "s|<FRONTOFFICE>|$_PLOOPI_FRONTOFFICE|" $CONF_FILE
    sed -i -r -e "s|<REWRITERULE>|$_PLOOPI_FRONTOFFICE_REWRITERULE|" $CONF_FILE
    sed -i -r -e "s|<PEARPATH>|$_PLOOPI_PEARPATH|" $CONF_FILE
    sed -i -r -e "s|<INTERNETPROXY_HOST>|$_PLOOPI_INTERNETPROXY_HOST|" $CONF_FILE
    sed -i -r -e "s|<INTERNETPROXY_PORT>|$_PLOOPI_INTERNETPROXY_PORT|" $CONF_FILE
    sed -i -r -e "s|<INTERNETPROXY_USER>|$_PLOOPI_INTERNETPROXY_USER|" $CONF_FILE
    sed -i -r -e "s|<INTERNETPROXY_PASS>|$_PLOOPI_INTERNETPROXY_PASS|" $CONF_FILE

	iconv --to ISO-8859-15 -o ./config/config.php $CONF_FILE
    if [ $? -ne 0 ]; then
        return 1
    fi
    case "$_PLOOPI_SQL_LAYER" in
        "mysql")
            if [ $CREATE_NEW_DB -eq 0 ]; then
                $MYSQL_CMD mysql -e "CREATE DATABASE $_PLOOPI_DB_DATABASE"
                if [ $? -ne 0 ]; then
                    return 1
                fi
            fi
			echo "UPDATE ploopi_user SET login='$_PLOOPI_ADM_LOGIN',password=MD5(CONCAT('$_PLOOPI_SECRETKEY/$_PLOOPI_ADM_LOGIN/',MD5('$_PLOOPI_ADM_PWD'))) WHERE login='admin'" > $TMP_FILE

            $MYSQL_CMD "$_PLOOPI_DB_DATABASE" < ./install/system/ploopi.sql
            if [ $? -ne 0 ]; then
                return 1
            fi
            iconv --to ISO-8859-15 $TMP_FILE | $MYSQL_CMD "$_PLOOPI_DB_DATABASE"
            if [ $? -ne 0 ]; then
                return 1
            fi
        ;;
    esac
}
ret=3
while [ $ret -eq 3 ]; do
    dialog --clear --extra-button --extra-label "$LBL_SHOW_LICENCE" --backtitle "$TITLE" --title "$LBL_LICENCE_TITLE" --yesno "$LBL_LICENCE_QUESTION" 10 70
    ret=$?
    if [ $ret -eq 3 ]; then
        licence
    fi
done
case "$ret" in
    "0")
        get_pear_path
        ploopi_param
        database_param
        write_config
        install_ploopi
        ret=$?
        if [ $ret -ne 0 ]; then
            dialog --clear --backtitle "$TITLE" --title "$LBL_ERROR" --msgbox "$LBL_ERROR_INSTALL_FAILED" 0 0 
        else
            dialog --clear --backtitle "$TITLE" --title "$TITLE" --msgbox "$LBL_ERROR_INSTALL_SUCCEEDED" 0 0 
        fi
    ;;
    "1")
        echo "Bye"
        exit 0
    ;;
esac
exit 0



