<?php

namespace app\models;

use yii\base\InvalidArgumentException;

/**
 * @property int id
 * @property string username
 * @property float balance
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findByUsername($token);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()
            ->where(['username' => $username])
            ->limit(1)
            ->one()
        ;
    }

    /**
     * Creates instance by username
     *
     * @param string $username
     *
     * @return static Instance of created User
     *
     * @throws InvalidArgumentException If User already exists
     */
    public static function createByUsername($username)
    {
        $exists = static::find()
            ->where(['username' => $username])
            ->limit(1)
            ->exists()
        ;
        if ($exists) {
            throw new InvalidArgumentException(sprintf(
                'User with "username"="%s" already exists in Db.',
                $username,
            ));
        }

        $instance = new self(['username' => $username]);
        $instance->loadDefaultValues();

        return $instance->save() ? $instance : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->username === $authKey;
    }
}
