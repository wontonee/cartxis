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


namespace PhonePe\payments\v2\models\response\ResponseComponents;

class MetaInfo
{
	public $udf1;
	public $udf2;
	public $udf3;
	public $udf4;
	public $udf5;

	/**
	 * @param string|null $udf1
	 * @param string|null $udf2
	 * @param string|null $udf3
	 * @param string|null $udf4
	 * @param string|null $udf5
	 */
	public function __construct(string $udf1 = null, string $udf2 = null, string $udf3 = null, string $udf4 = null, string $udf5 = null)
	{
		$this->udf1 = $udf1;
		$this->udf2 = $udf2;
		$this->udf3 = $udf3;
		$this->udf4 = $udf4;
		$this->udf5 = $udf5;
	}

	public function getUdf1(): ?string
	{
		return $this->udf1;
	}

	public function getUdf2(): ?string
	{
		return $this->udf2;
	}

	public function getUdf3(): ?string
	{
		return $this->udf3;
	}

	public function getUdf4(): ?string
	{
		return $this->udf4;
	}

	public function getUdf5(): ?string
	{
		return $this->udf5;
	}



}