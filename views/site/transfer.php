<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Transfer to User';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-transfer">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="user-form">

        <?php $form = ActiveForm::begin(['id' => 'transfer-form']); ?>

        <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => 0.01]) ?>

        <div class="form-group">
            <?= Html::submitButton('Transfer', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
