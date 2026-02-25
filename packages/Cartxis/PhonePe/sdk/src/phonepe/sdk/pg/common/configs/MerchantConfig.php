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


namespace PhonePe\common\configs;

class MerchantConfig
{

	/**
	 * @desc The Client Id provided by PhonePe team
	 * @var string
	 */
	private $clientId;

	/**
	 * @desc The Client Version provided by PhonePe team
	 * @var int
	 */
	private $clientVersion;

	/**
	 * @desc The Client Secret provided by PhonePe team
	 * @var string
	 */
	private $clientSecret;

	/**
	 * @param string $clientId
	 * @param int $clientVersion
	 * @param string $clientSecret
	 */

	public function __construct($clientId, $clientVersion, $clientSecret)
	{
		$this->clientId = $clientId;
		$this->clientVersion = $clientVersion;
		$this->clientSecret = $clientSecret;
	}

	/**
	 * @return string
	 */
	public function getClientId()
	{
		return $this->clientId;
	}

	/**
	 * @param string $clientId
	 */
	public function setClientId($clientId)
	{
		$this->clientId = $clientId;
	}

	/**
	 * @return int
	 */
	public function getClientVersion()
	{
		return $this->clientVersion;
	}

	/**
	 * @param int $clientVersion
	 */
	public function setClientVersion($clientVersion)
	{
		$this->clientVersion = $clientVersion;
	}

	/**
	 * @return string
	 */
	public function getClientSecret()
	{
		return $this->clientSecret;
	}

	/**
	 * @param string $clientSecret
	 */
	public function setClientSecret($clientSecret)
	{
		$this->clientSecret = $clientSecret;
	}

}