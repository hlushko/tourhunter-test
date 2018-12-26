<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidArgumentException;

/**
 * LoginForm is the model behind the login form.
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
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['username', 'required'],
            ['username', 'trim'],
            ['username', 'string', 'max' => User::USERNAME_MAX_LENGTH],
            ['username', 'match', 'pattern' => User::USERNAME_VALIDATION_PATTERN],
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
        $user = User::findByUsername($this->username);
        if ($user) {
            return $user;
        }
        $user = User::createByUsername($this->username);

        return $user;
    }
}
