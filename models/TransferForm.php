<?php

namespace app\models;

use Yii;
use yii\base\InvalidCallException;
use yii\base\Model;

/**
 * TransferForm is the model behind the transfer form.
 */
class TransferForm extends Model
{
    /**
     * @var string Unique name of user
     */
    public $username;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var User
     */
    private $currentUser;

    /**
     * @var User
     */
    private $destinationUser;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (Yii::$app->user->isGuest) {
            throw new InvalidCallException(
                'Transfer can be perform only by auth users.'
            );
        }
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'amount'], 'required'],
            [['username', 'amount'], 'trim'],
            ['username', 'string', 'max' => User::USERNAME_MAX_LENGTH],
            ['username', 'match', 'pattern' => User::USERNAME_VALIDATION_PATTERN],
            ['amount', 'double'],
            [
                'amount',
                'compare',
                'compareValue' => 0,
                'operator' => '>',
                'type' => 'number',
            ],
            ['amount', 'match', 'pattern' => '/^\-?\d+(\.\d{1,2})?$/'],
            ['username', 'validateTransferToYourself'],
            ['username', 'validateUserExists'],
            ['amount', 'validateFinalBalance'],
        ];
    }

    /**
     * Validates the case when someone tries to transfer to himself.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateTransferToYourself($attribute, $params)
    {
        if (false === $this->hasErrors()) {
            $currentUser = $this->getCurrentUser();
            if ($this->username === $currentUser->username) {
                $this->addError(
                    $attribute,
                    'You cannot transfer to yourself.',
                );
            }
        }
    }

    /**
     * Validates the case when someone tries to transfer to not existing User.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUserExists($attribute, $params)
    {
        if (false === $this->hasErrors()) {
            $user = $this->getDestinationUser();
            if (empty($user)) {
                $this->addError(
                    $attribute,
                    sprintf('User with provided username "%s" not found.', $this->username),
                );
            }
        }
    }

    /**
     * Validates the case when someone tries to transfer too many.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateFinalBalance($attribute, $params)
    {
        if (false === $this->hasErrors()) {
            $currentUser = $this->getCurrentUser();
            if ($currentUser->balance - $this->amount < User::MINIMUM_BALANCE) {
                $this->addError(
                    $attribute,
                    sprintf(
                        <<<MSG
Too many to transfer. Your balance after transfer could not be less then "%s".
You can transfer "%s".
MSG,
                        User::MINIMUM_BALANCE,
                        $currentUser->balance - User::MINIMUM_BALANCE
                    ),
                );
            }
        }
    }

    /**
     * Transfers provided amount to chosen User
     * @return bool whether the transfer finished successfully
     *
     * @throws InvalidCallException If method was called by non auth User
     */
    public function transfer()
    {
        if (false === $this->validate()) {
            return false;
        }

        $currentUser = $this->getCurrentUser();
        $destUser = $this->getDestinationUser();
        try {
            User::getDb()->transaction(
                function ($db) use ($currentUser, $destUser) {
                    $currentUser->balance -= $this->amount;
                    $destUser->balance += $this->amount;

                    $currentUser->save();
                    $destUser->save();
                }
            );
        } catch (\Throwable $e) {
            $this->addError(
                'amount',
                'Cannot perform specified transfer. Server error occur.'
            );
            // TODO : add event to error notification system
        }

        return true;
    }

    /**
     * @return User
     */
    private function getCurrentUser()
    {
        if (empty($this->currentUser)) {
            $this->currentUser = User::findIdentity(Yii::$app->user->getId());
        }
        return $this->currentUser;
    }

    /**
     * @return User|null
     */
    private function getDestinationUser()
    {
        if (empty($this->destinationUser) && $this->username) {
            $this->destinationUser = User::findByUsername($this->username);
        }
        return $this->destinationUser;
    }
}
