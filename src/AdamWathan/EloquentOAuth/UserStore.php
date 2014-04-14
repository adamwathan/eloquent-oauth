<?php namespace AdamWathan\EloquentOAuth;

class UserStore
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function create()
    {
        $user = new $this->model;
        $user->save();
    }

    public function findByIdentity($identity)
    {
        return $identity->belongsTo($this->model, 'user_id')->first();
    }
}
