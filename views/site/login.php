<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Login';
?>
<div class="form-container">
    <h2>Login</h2>
    <?= Html::beginForm(['site/login'], 'post') ?>
        <label for="email">Email:</label>
        <?= Html::input('email', 'email', '', ['id' => 'email', 'placeholder' => 'Enter your email', 'required' => true]) ?>

        <label for="password">Password:</label>
        <?= Html::input('password', 'password', '', ['id' => 'password', 'placeholder' => 'Enter your password', 'required' => true]) ?>

        <?= Html::submitButton('Login', ['class' => 'btn-submit']) ?>
    <?= Html::endForm() ?>
    <p class="toggle-link">Don't have an account? <a href="<?= Url::to(['site/signup']) ?>">Sign Up</a></p>
</div>
