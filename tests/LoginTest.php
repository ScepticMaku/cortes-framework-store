<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    protected $baseUrl = 'http://localhost/mini-framework-store/';

    protected function setUp(): void
    {
        // Start session for testing
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function testLoginFormDisplayedWithoutSession()
    {
        // Simulate no session user
        unset($_SESSION['user']);

        // Use file_get_contents or curl to get login.php page
        $response = file_get_contents($this->baseUrl . 'login.php');

        $this->assertStringContainsString('<h1 class="text-center">Login</h1>', $response);
    }

    public function testRedirectIfSessionActive()
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];

        // Use curl to get headers and check for Location header
        $ch = curl_init($this->baseUrl . 'login.php');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $this->assertStringContainsString('Location: my-account.php', $response);
    }

    public function testValidLogin()
    {
        // This test requires a test user in the database with known credentials
        $postData = http_build_query([
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'submit' => 'Login'
        ]);

        $opts = ['http' =>
            [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $postData
            ]
        ];

        $context  = stream_context_create($opts);
        $result = file_get_contents($this->baseUrl . 'login.php', false, $context);

        // Check for redirection header or content indicating success
        // This is a simplified check; in real tests, use curl to check headers
        $this->assertStringNotContainsString('Invalid username or password', $result);
    }

    public function testInvalidLogin()
    {
        $postData = http_build_query([
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword',
            'submit' => 'Login'
        ]);

        $opts = ['http' =>
            [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $postData
            ]
        ];

        $context  = stream_context_create($opts);
        $result = file_get_contents($this->baseUrl . 'login.php', false, $context);

        $this->assertStringContainsString('Invalid username or password', $result);
    }
}
