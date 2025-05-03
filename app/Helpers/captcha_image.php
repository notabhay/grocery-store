<?php
session_start();
header('Content-Type: image/png');
if (!isset($_SESSION['captcha'])) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $captcha_text = '';
    for ($i = 0; $i < 6; $i++) {
        $captcha_text .= $characters[rand(0, strlen($characters) - 1)];
    }
    $_SESSION['captcha'] = $captcha_text;
}
$captcha_text = $_SESSION['captcha'];
$image_width = 200;
$image_height = 50;
$image = imagecreatetruecolor($image_width, $image_height);
$background_color = imagecolorallocate($image, 240, 240, 240);
$text_color = imagecolorallocate($image, 40, 40, 40);
$noise_color = imagecolorallocate($image, 100, 120, 180);
imagefill($image, 0, 0, $background_color);
for ($i = 0; $i < 400; $i++) {
    imagesetpixel($image, rand(0, $image_width), rand(0, $image_height), $noise_color);
}
for ($i = 0; $i < 5; $i++) {
    imageline(
        $image,
        rand(0, $image_width),
        rand(0, $image_height),
        rand(0, $image_width),
        rand(0, $image_height),
        $noise_color
    );
}
for ($i = 0; $i < 3; $i++) {
    imagearc(
        $image,
        rand(0, $image_width),
        rand(0, $image_height),
        rand(10, 30),
        rand(10, 30),
        0,
        360,
        $noise_color
    );
}
$font_size = 5;
$text_width = imagefontwidth($font_size) * strlen($captcha_text);
$text_height = imagefontheight($font_size);
$text_x = ($image_width - $text_width) / 2;
$text_y = ($image_height - $text_height) / 2;
$x = $text_x;
for ($i = 0; $i < strlen($captcha_text); $i++) {
    $char = $captcha_text[$i];
    $y = $text_y + rand(-5, 5);
    $r = 40 + rand(0, 30);
    $g = 40 + rand(0, 30);
    $b = 40 + rand(0, 30);
    $char_text_color = imagecolorallocate($image, $r, $g, $b);
    imagechar($image, $font_size, (int)$x, (int)$y, $char, $char_text_color);
    $x += imagefontwidth($font_size) + rand(-2, 2);
}
imagepng($image);
imagedestroy($image);