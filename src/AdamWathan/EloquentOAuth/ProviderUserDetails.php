<?php namespace AdamWathan\EloquentOAuth;

class ProviderUserDetails
{
	protected $details = array();

	public function __construct($details)
	{
		$this->details = $details;
	}

	public function addDetails($details = array())
	{
		foreach ($details as $key => $value) {
			$this->details[$key] = $value;
		}
	}

	public function __get($key)
	{
		return isset($this->details[$key]) ? $this->details[$key] : null;
	}
}