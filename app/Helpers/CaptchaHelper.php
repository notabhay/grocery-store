<?php

namespace App\Helpers;

use App\Core\Session;

class CaptchaHelper
{
    private $session;
    private $sessionKey = 'captcha';
    private $width = 200;
    private $height = 50;
    public function __construct(Session $session, array $options = [])
    {
        $this->session = $session;
        $this->sessionKey = $options['session_key'] ?? $this->sessionKey;
        $this->width = $options['width'] ?? $this->width;
        $this->height = $options['height'] ?? $this->height;
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD library extension is required for CaptchaHelper.');
        }
    }
    public function generateText(int $length = 6): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $captcha_text = '';
        $charLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $captcha_text .= $characters[random_int(0, $charLength - 1)];
        }
        return $captcha_text;
    }
    public function storeText(string $text): void
    {
        $this->session->set($this->sessionKey, strtolower($text));
    }
    public function getText(): ?string
    {
        return $this->session->get($this->sessionKey);
    }
    public function validate(?string $submittedText): bool
    {
        $storedText = $this->getText();
        if ($submittedText === null || $storedText === null) {
            return false;
        }
        return strtolower($submittedText) === $storedText;
    }
    public function generateImageData(string $text)
    {
        $image = imagecreatetruecolor($this->width, $this->height);
        if (!$image) {
            error_log("CaptchaHelper: Failed to create image resource.");
            return false;
        }
        $background_color = imagecolorallocate($image, 240, 240, 240);
        $noise_color = imagecolorallocate($image, 180, 180, 180);
        imagefill($image, 0, 0, $background_color);
        $pixel_count = ($this->width * $this->height) / 5;
        for ($i = 0; $i < $pixel_count; $i++) {
            imagesetpixel($image, random_int(0, $this->width - 1), random_int(0, $this->height - 1), $noise_color);
        }
        for ($i = 0; $i < 5; $i++) {
            imageline(
                $image,
                random_int(0, $this->width),
                random_int(0, $this->height),
                random_int(0, $this->width),
                random_int(0, $this->height),
                $noise_color
            );
        }
        $font_size = 5;
        $text_width = 0;
        $text_height = 0;
        $ttf_font = __DIR__ . '/fonts/arial.ttf';
        $use_ttf = function_exists('imagettfbbox') && function_exists('imagettftext') && file_exists($ttf_font);
        if ($use_ttf) {
            $text_box = @imagettfbbox($font_size * 5, 0, $ttf_font, $text);
            if ($text_box) {
                $text_width = abs($text_box[4] - $text_box[0]);
                $text_height = abs($text_box[5] - $text_box[1]);
            } else {
                error_log("CaptchaHelper: imagettfbbox failed for font: " . $ttf_font);
                $use_ttf = false;
            }
        }
        if ($text_width === 0) {
            $font_size = 5;
            $text_width = imagefontwidth($font_size) * strlen($text);
            $text_height = imagefontheight($font_size);
            $use_ttf = false;
        }
        $start_x = ($this->width - $text_width) / 2;
        $start_y = ($this->height - $text_height) / 2;
        $x = $start_x > 0 ? $start_x : 5;
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            $y = $start_y + random_int(-5, 5);
            $angle = $use_ttf ? random_int(-15, 15) : 0;
            $r = random_int(0, 100);
            $g = random_int(0, 100);
            $b = random_int(0, 100);
            $char_color = imagecolorallocate($image, $r, $g, $b);
            if ($use_ttf) {
                $ttf_y = $y + $text_height * 0.75;
                @imagettftext($image, $font_size * 5, $angle, (int) $x, (int) $ttf_y, $char_color, $ttf_font, $char);
                $char_bbox = @imagettfbbox($font_size * 5, 0, $ttf_font, $char);
                $char_width = $char_bbox ? abs($char_bbox[4] - $char_bbox[0]) : imagefontwidth($font_size) * 2;
            } else {
                imagechar($image, $font_size, (int) $x, (int) $y, $char, $char_color);
                $char_width = imagefontwidth($font_size);
            }
            $x += $char_width + random_int(1, 3);
        }
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        return $imageData;
    }
}