<?php
/**
 * Facebook OAuth2 Provider
 *
 * @package    CodeIgniter/OAuth2
 * @category   Provider
 * @author     Phil Sturgeon
 * @copyright  (c) 2012 HappyNinjas Ltd
 * @license    http://philsturgeon.co.uk/code/dbad-license
 */

class OAuth2_Provider_Facebook extends OAuth2_Provider
{
	protected $scope = array('publish_actions', 'email');

	public function url_authorize()
	{
		return 'https://www.facebook.com/dialog/oauth';
	}

	public function url_access_token()
	{
		return 'https://graph.facebook.com/oauth/access_token';
	}

	public function get_user_info(OAuth2_Token_Access $token)
	{
		$url = 'https://graph.facebook.com/me?'.http_build_query(array(
			'access_token' => $token->access_token,
			'fields' => 'id,name,email,first_name,last_name'
		));

		$user = json_decode(file_get_contents($url));

		// Create a response from the request
//		var_dump($user);
		return array(
			'uid' => $user->id,
			'name' => $user->name,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'email' => isset($user->email) ? $user->email : $user->id,
			'image' => 'https://graph.facebook.com/me/picture?type=normal&access_token='.$token->access_token,
			'urls' => array(
			  'Facebook' => isset($user->link) ? $user->link : $user->id,
			),
			'facebookID' => $user->id,
		);
	}
}