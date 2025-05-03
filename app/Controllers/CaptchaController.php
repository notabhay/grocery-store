<?php
namespace App\Controllers;
use App\Core\Session;
use App\Helpers\CaptchaHelper;
use App\Core\BaseController;
use App\Core\Registry;
use Psr\Log\LoggerInterface; 
class CaptchaController extends BaseController
{
    private $session;
    private $captchaHelper;
    private $logger;
    public function __construct(Session $session, CaptchaHelper $captchaHelper)
    {
        $this->session = $session;
        $this->captchaHelper = $captchaHelper;
        if (Registry::has('logger')) {
            $this->logger = Registry::get('logger');
        }
    }
    public function generate()
    {
        try {
            if (!$this->session->isActive()) {
                $this->session->start();
            }
            $text = $this->captchaHelper->generateText();
            $this->captchaHelper->storeText($text);
            $imageData = $this->captchaHelper->generateImageData($text);
            if ($imageData === false) {
                $this->logError('Failed to generate CAPTCHA image data.');
                $this->outputError('Failed to generate CAPTCHA image.');
                return;
            }
            header('Content-Type: image/png');
            header('Cache-Control: no-cache, no-store, must-revalidate'); 
            header('Pragma: no-cache'); 
            header('Expires: 0'); 
            echo $imageData;
        } catch (\Exception $e) {
            $this->logError("Captcha Generation Error: " . $e->getMessage(), ['exception' => $e]);
            $this->outputError('An error occurred while generating the CAPTCHA.');
        }
    }
    private function outputError(string $message): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/plain', true, 500);
        }
        echo "Error: " . htmlspecialchars($message);
    }
    private function logError(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->error($message, $context);
        } else {
            error_log("CaptchaController Error: " . $message . " Context: " . print_r($context, true));
        }
    }
}
