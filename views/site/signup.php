<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Sign Up';
?>
<div class="form-container">
    <h2>Sign Up</h2>
    <?= Html::beginForm(['site/signup'], 'post') ?>
        <label for="signup-name">Name:</label>
        <?= Html::input('text', 'name', '', ['id' => 'signup-name', 'placeholder' => 'Enter your name', 'required' => true]) ?>

        <label for="signup-email">Email:</label>
        <?= Html::input('email', 'email', '', ['id' => 'signup-email', 'placeholder' => 'Enter your email', 'required' => true]) ?>

        <label for="signup-password">Password:</label>
        <?= Html::input('password', 'password', '', ['id' => 'signup-password', 'placeholder' => 'Create a password', 'required' => true]) ?>

        <?= Html::submitButton('Sign Up', ['class' => 'btn-submit']) ?>
    <?= Html::endForm() ?>
    <p class="toggle-link">Already have an account? <a href="<?= Url::to(['site/login']) ?>">Login</a></p>
</div>
