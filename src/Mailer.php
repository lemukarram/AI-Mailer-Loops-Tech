<?php

class Mailer {
    private $host;
    private $port;
    private $user;
    private $pass;

    public function __construct($host, $port, $user, $pass) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * Robust SMTP sender using native PHP sockets.
     * Works on shared hosting without any external libraries.
     */
    public function send($to, $subject, $body, $footer = '', $attachment = null) {
        $fullBody = $body . "\n\n" . $footer;
        $htmlBody = nl2br(htmlspecialchars($fullBody));

        $headers = [
            "From: " . $this->user,
            "Reply-To: " . $this->user,
            "To: " . $to,
            "Subject: " . $subject,
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "X-Mailer: PHP/" . phpversion()
        ];

        try {
            return $this->smtpSend($to, $subject, $htmlBody, $headers);
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }

    private function smtpSend($to, $subject, $htmlBody, $headers) {
        // Determine protocol
        $remote = ($this->port == 465) ? "ssl://{$this->host}" : $this->host;
        $socket = fsockopen($remote, $this->port, $errno, $errstr, 15);

        if (!$socket) {
            throw new Exception("Could not connect to SMTP host: $errstr ($errno)");
        }

        $this->getResponse($socket, "220");

        // Say Hello
        fwrite($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        $this->getResponse($socket, "250");

        // TLS if using 587
        if ($this->port == 587) {
            fwrite($socket, "STARTTLS\r\n");
            $this->getResponse($socket, "220");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_ANY_CLIENT);
            fwrite($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
            $this->getResponse($socket, "250");
        }

        // Authenticate
        fwrite($socket, "AUTH LOGIN\r\n");
        $this->getResponse($socket, "334");
        fwrite($socket, base64_encode($this->user) . "\r\n");
        $this->getResponse($socket, "334");
        fwrite($socket, base64_encode($this->pass) . "\r\n");
        $this->getResponse($socket, "235");

        // Set Envelope
        fwrite($socket, "MAIL FROM: <{$this->user}>\r\n");
        $this->getResponse($socket, "250");
        fwrite($socket, "RCPT TO: <{$to}>\r\n");
        $this->getResponse($socket, "250");

        // Send Content
        fwrite($socket, "DATA\r\n");
        $this->getResponse($socket, "354");

        $data = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.\r\n";
        fwrite($socket, $data);
        $this->getResponse($socket, "250");

        // Quit
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    }

    private function getResponse($socket, $expectedCode) {
        $response = "";
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == " ") break;
        }
        if (substr($response, 0, 3) !== $expectedCode) {
            throw new Exception("SMTP Error: Expected $expectedCode but got: " . $response);
        }
    }
}
