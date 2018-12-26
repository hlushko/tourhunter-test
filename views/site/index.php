<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'TourHunter Test App';

$authUserId = \Yii::$app->user->isGuest ? null : \Yii::$app->user->getId()
?>
<div class="site-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'username',
            'balance',

            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['width' => '80'],
                'template' => '{transfer}',
                'buttons' => [
                    'transfer' => function ($url, $model, $key) use ($authUserId) {
                        /** @var \app\models\User $model */
                        if (null === $authUserId || $authUserId === $model->getId()) {
                            return '';
                        }
                        $usernameUrl = $url . '&username=' . urlencode($model->username);
                        return Html::a(
                            '<span class="glyphicon glyphicon-transfer"></span>',
                            $usernameUrl,
                            ['title' => 'Transfer to'],
                        );
                    },
                ],
            ],
        ],
    ]); ?>
</div>
