<?php

/*
*  Copyright (c) 2025 Original Author(s), PhonePe India Pvt. Ltd.
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*  http://www.apache.org/licenses/LICENSE-2.0
*
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License.
*/


namespace PhonePe\common\tokenHandler;

class OAuthToken
{
	private  $access_token;
	private  $encrypted_access_token;
	private  $refresh_token;
	private  $expires_in;
	private  $issued_at;
	private  $expires_at;
	private  $session_expires_at;
	private  $token_type;

	/**
	 * @param string|null $access_token
	 * @param string|null $encrypted_access_token
	 * @param string|null $refresh_token
	 * @param int|null $expires_in
	 * @param int|null $issued_at
	 * @param int|null $expires_at
	 * @param int|null $session_expires_at
	 * @param string|null $token_type
	 */
	public function __construct(?string $access_token = null, ?string $encrypted_access_token = null, ?string $refresh_token = null, ?int $expires_in = null, ?int $issued_at = null, ?int $expires_at = null, ?int $session_expires_at = null, ?string $token_type = null)
	{
		$this->access_token = $access_token;
		$this->encrypted_access_token = $encrypted_access_token;
		$this->refresh_token = $refresh_token;
		$this->expires_in = $expires_in;
		$this->issued_at = $issued_at;
		$this->expires_at = $expires_at;
		$this->session_expires_at = $session_expires_at;
		$this->token_type = $token_type;
	}


	/**
	 * @return string
	 */
	public function getAccessToken(): ?string
	{
		return $this->access_token;
	}

	/**
	 * @param mixed $access_token
	 */
	public function setAccessToken($access_token)
	{
		$this->access_token = $access_token;
	}

	/**
	 * @return mixed
	 */
	public function getEncryptedAccessToken()
	{
		return $this->encrypted_access_token;
	}

	/**
	 * @param mixed $encrypted_access_token
	 */
	public function setEncryptedAccessToken($encrypted_access_token)
	{
		$this->encrypted_access_token = $encrypted_access_token;
	}

	/**
	 * @return mixed
	 */
	public function getRefreshToken()
	{
		return $this->refresh_token;
	}

	/**
	 * @param mixed $refresh_token
	 */
	public function setRefreshToken($refresh_token)
	{
		$this->refresh_token = $refresh_token;
	}

	/**
	 * @return mixed
	 */
	public function getExpiresIn()
	{
		return $this->expires_in;
	}

	/**
	 * @param int $expires_in
	 */
	public function setExpiresIn($expires_in)
	{
		$this->expires_in = $expires_in;
	}

	/**
	 * @return int
	 */
	public function getIssuedAt(): ?int
	{
		return $this->issued_at;
	}

	/**
	 * @param mixed $issued_at
	 */
	public function setIssuedAt($issued_at)
	{
		$this->issued_at = $issued_at;
	}

	/**
	 * @return int
	 */
	public function getExpiresAt()
	{
		return $this->expires_at;
	}

	/**
	 * @param mixed $expires_at
	 */
	public function setExpiresAt($expires_at)
	{
		$this->expires_at = $expires_at;
	}

	/**
	 * @return mixed
	 */
	public function getSessionExpiresAt()
	{
		return $this->session_expires_at;
	}

	/**
	 * @param mixed $session_expires_at
	 */
	public function setSessionExpiresAt($session_expires_at)
	{
		$this->session_expires_at = $session_expires_at;
	}

	/**
	 * @return string
	 */
	public function getTokenType(): ?string
	{
		return $this->token_type;
	}

	/**
	 * @param mixed $token_type
	 */
	public function setTokenType($token_type)
	{
		$this->token_type = $token_type;
	}


}