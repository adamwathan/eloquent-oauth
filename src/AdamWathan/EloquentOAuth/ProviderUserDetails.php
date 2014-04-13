<?php namespace AdamWathan\EloquentOAuth;

class ProviderUserDetails
{
	protected $details = array(
		'accessToken' => null,
		'userId' => null,
		'nickname' => null,
		'firstName' => null,
		'lastName' => null,
		'email' => null,
		'imageUrl' => null,
	);

	public function __construct($details)
	{
		$this->addDetails($details);
	}

	protected function addDetails($details = array())
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
