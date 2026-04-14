<?php
require __DIR__ . '/../vendor/autoload.php';
// install dulu composer require gregwar/captcha
use Gregwar\Captcha\CaptchaBuilder;

session_start();

// generate captcha
$builder = new CaptchaBuilder();
$builder->build();

// simpan text ke session
$_SESSION['captcha'] = $builder->getPhrase();

// output image
header('Content-type: image/jpeg');
$builder->output();