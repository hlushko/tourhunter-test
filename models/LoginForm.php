<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidArgumentException;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    /**
     * @var string Unique name of user
     */
    public $username;

    /**
     * @var bool
     */
    public $rememberMe = true;

    /**
     * @var null|User
     */
    private $user = null;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['username', 'required'],
            ['username', 'trim'],
            ['username', 'match', 'pattern' => '/^[\w\-\.]+$/'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if (false === $this->validate()) {
            return false;
        }
        try {
            return Yii::$app->user->login(
                $this->getOrCreateUser(),
                $this->rememberMe ? 3600*24*30 : 0,
            );
        } catch (InvalidArgumentException $e) {
            $this->addError('username', $e->getMessage());
        }
        return false;
    }

    /**
     * Finds or creates user by [[username]]
     *
     * @return User
     *
     * @throws InvalidArgumentException
     */
    public function getOrCreateUser()
    {
        if ($this->user) {
            return $this->user;
        }
        $this->user = User::findByUsername($this->username);
        if ($this->user) {
            return $this->user;
        }
        $this->user = User::createByUsername($this->username);

        return $this->user;
    }
}
