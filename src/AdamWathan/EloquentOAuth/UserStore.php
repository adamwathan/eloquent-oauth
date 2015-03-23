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
        return $user;
    }

    public function store($user)
    {
        return $user->save();
    }

    public function findByIdentity($identity)
    {
        return $identity->belongsTo($this->model, 'user_id')->firstOrFail();
    }
}
