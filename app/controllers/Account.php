<?php
namespace Controller;


use Core\Access;
use Core\App;
use Core\Auth;
use Core\Category;
use Core\Config;
use Core\Controller\Authenticated_Page;
use Core\Current_User;
use Core\Form;
use Core\GPC;
use Core\Helper;
use Core\HTML;
use Core\Lang;
use Core\Menu;
use Core\Print_Util;
use Core\String;
use Core\Template;
use Core\URL;
use Core\User;
use Core\Utility;


class Account extends Authenticated_Page
{
	function __construct($params)
	{
		parent::__construct($params);
		
		App::queue_css(URL::get('css/status_config.php'));
	}
	
	function action_index()
	{
		if ($_POST && Form::security_validate( 'account_update' ))
		{
			$t_user_id = \Core\Auth::get_current_user_id();
			
			\Core\Current_User::ensure_unprotected();
			
			$f_email           	= \Core\GPC::get_string( 'email', '' );
			$f_realname        	= \Core\GPC::get_string( 'realname', '' );
			$f_password_current = \Core\GPC::get_string( 'password_current', '' );
			$f_password        	= \Core\GPC::get_string( 'password', '' );
			$f_password_confirm	= \Core\GPC::get_string( 'password_confirm', '' );
			
			$t_email_updated = false;
			$t_password_updated = false;
			$t_realname_updated = false;
			
			$t_ldap = ( LDAP == \Core\Config::mantis_get( 'login_method' ) );
			
			# Update email (but only if LDAP isn't being used)
			if( !( $t_ldap && \Core\Config::mantis_get( 'use_ldap_email' ) ) ) {
				\Core\Email::ensure_valid( $f_email );
				\Core\Email::ensure_not_disposable( $f_email );
			
				if( $f_email != \Core\User::get_email( $t_user_id ) ) {
					\Core\User::set_email( $t_user_id, $f_email );
					$t_email_updated = true;
				}
			}
			
			# Update real name (but only if LDAP isn't being used)
			if( !( $t_ldap && \Core\Config::mantis_get( 'use_ldap_realname' ) ) ) {
				# strip extra spaces from real name
				$t_realname = \Core\String::normalize( $f_realname );
				if( $t_realname != \Core\User::get_field( $t_user_id, 'realname' ) ) {
					# checks for problems with realnames
					$t_username = \Core\User::get_field( $t_user_id, 'username' );
					\Core\User::ensure_realname_unique( $t_username, $t_realname );
					\Core\User::set_realname( $t_user_id, $t_realname );
					$t_realname_updated = true;
				}
			}
			
			# Update password if the two match and are not empty
			if( !\Core\Utility::is_blank( $f_password ) ) {
				if( $f_password != $f_password_confirm ) {
					trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
				} else {
					if( !$t_account_verification && !\Core\Auth::does_password_match( $t_user_id, $f_password_current ) ) {
						trigger_error( ERROR_USER_CURRENT_PASSWORD_MISMATCH, ERROR );
					}
			
					if( !\Core\Auth::does_password_match( $t_user_id, $f_password ) ) {
						\Core\User::set_password( $t_user_id, $f_password );
						$t_password_updated = true;
					}
				}
			}
			
			\Core\Form::security_purge( 'account_update' );
			
			# Clear the verification token
			if( $t_account_verification ) {
				\Core\Token::delete( TOKEN_ACCOUNT_VERIFY, $t_user_id );
			}
			
			$t_message = '';
			
			if( $t_email_updated ) {
				$t_message .= \Core\Lang::get( 'email_updated' );
			}
			
			if( $t_password_updated ) {
				$t_message = \Core\Utility::is_blank( $t_message ) ? '' : $t_message . '<br />';
				$t_message .= \Core\Lang::get( 'password_updated' );
			}
			
			if( $t_realname_updated ) {
				$t_message = \Core\Utility::is_blank( $t_message ) ? '' : $t_message . '<br />';
				$t_message .= \Core\Lang::get( 'realname_updated' );
			}
			
			$this->message = /*$t_message ? $t_messsage : */Lang::get( 'operation_successful' );
		}
		
		$this->set([
			'page_title'	=> Lang::get( 'edit_account_title' ),
			'view'			=> 'Pages/Account/Edit',
		]);
	}
	
	function action_preferences()
	{
		$this->set([
			'page_title'	=> Lang::get( 'change_preferences_link' ),
			'view'			=> 'Pages/Account/Edit_Preferences',
		]);
	}
	
	function action_columns()
	{
		if ($_POST && Form::security_validate( 'manage_config_columns_reset' ))
		{
			$t_user_id = Auth::get_current_user_id();
			
			Config::delete_for_user( 'view_issues_page_columns', $t_user_id );
			Config::delete_for_user( 'print_issues_page_columns', $t_user_id );
			Config::delete_for_user( 'csv_columns', $t_user_id );
			Config::delete_for_user( 'excel_columns', $t_user_id );
			
			Form::security_purge( 'manage_config_columns_reset' );
			
			$this->message = Lang::get( 'operation_successful' );
		}

		$this->set([
			'page_title'	=> Lang::get( 'manage_columns_config' ),
			'view'			=> 'Pages/Account/Columns',
			'project_id'	=> Helper::get_current_project(),
			'user_id'		=> Auth::get_current_user_id(),
		]);
	}
	
	function action_profiles()
	{
		$this->set([
			'page_title'	=> Lang::get( 'manage_profiles_link' ),
			'view'			=> 'Pages/Account/Profiles',
		]);
	}
}