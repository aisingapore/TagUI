<?php

/**
 * Copyright (C) 2014, 2015 Textalk
 * Copyright (C) 2015 Ignas Bernotas - added context options and handling
 *
 * This file is part of Websocket PHP and is free software under the ISC License.
 * License text: https://raw.githubusercontent.com/Textalk/websocket-php/master/COPYING
 */

namespace WebSocket;

class Client extends Base {
  protected $socket_uri;

  /**
   * @param string  $uri      A ws/wss-URI
   * @param array   $options
   *   Associative array containing:
   *   - context:      Set the stream context. Default: empty context
   *   - timeout:      Set the socket timeout in seconds.  Default: 5
   *   - headers:      Associative array of headers to set/override.
   */
  public function __construct($uri, $options = array()) {
    $this->options = $options;

    if (!array_key_exists('timeout', $this->options)) $this->options['timeout'] = 5;

    // the fragment size
    if (!array_key_exists('fragment_size', $this->options)) $this->options['fragment_size'] = 4096;

    $this->socket_uri = $uri;
  }

  public function __destruct() {
    if ($this->socket) {
      if (get_resource_type($this->socket) === 'stream') fclose($this->socket);
      $this->socket = null;
    }
  }

  /**
   * Perform WebSocket handshake
   */
  protected function connect() {
    $url_parts = parse_url($this->socket_uri);
    $scheme    = $url_parts['scheme'];
    $host      = $url_parts['host'];
    $user      = isset($url_parts['user']) ? $url_parts['user'] : '';
    $pass      = isset($url_parts['pass']) ? $url_parts['pass'] : '';
    $port      = isset($url_parts['port']) ? $url_parts['port'] : ($scheme === 'wss' ? 443 : 80);
    $path      = isset($url_parts['path']) ? $url_parts['path'] : '/';
    $query     = isset($url_parts['query'])    ? $url_parts['query'] : '';
    $fragment  = isset($url_parts['fragment']) ? $url_parts['fragment'] : '';

    $path_with_query = $path;
    if (!empty($query))    $path_with_query .= '?' . $query;
    if (!empty($fragment)) $path_with_query .= '#' . $fragment;

    if (!in_array($scheme, array('ws', 'wss'))) {
      throw new BadUriException(
        "Url should have scheme ws or wss, not '$scheme' from URI '$this->socket_uri' ."
      );
    }

    $host_uri = ($scheme === 'wss' ? 'ssl' : 'tcp') . '://' . $host;

    // Set the stream context options if they're already set in the config
    if (isset($this->options['context'])) {
      // Suppress the error since we'll catch it below
      if (@get_resource_type($this->options['context']) === 'stream-context') {
        $context = $this->options['context'];
      }
      else {
        throw new \InvalidArgumentException(
          "Stream context in \$options['context'] isn't a valid context"
        );
      }
    }
    else {
      $context = stream_context_create();
    }

    // Open the socket.  @ is there to supress warning that we will catch in check below instead.
    $this->socket = @stream_socket_client(
      $host_uri . ':' . $port,
      $errno,
      $errstr,
      $this->options['timeout'],
      STREAM_CLIENT_CONNECT,
      $context
    );

    if ($this->socket === false) {
      throw new ConnectionException(
        "Could not open socket to \"$host:$port\": $errstr ($errno)."
      );
    }

    // Set timeout on the stream as well.
    stream_set_timeout($this->socket, $this->options['timeout']);

    // Generate the WebSocket key.
    $key = self::generateKey();

    // Default headers (using lowercase for simpler array_merge below).
    $headers = array(
      'host'                  => $host . ":" . $port,
      'user-agent'            => 'websocket-client-php',
      'connection'            => 'Upgrade',
      'upgrade'               => 'websocket',
      'sec-websocket-key'     => $key,
      'sec-websocket-version' => '13',
    );

    // Handle basic authentication.
    if ($user || $pass) {
      $headers['authorization'] = 'Basic ' . base64_encode($user . ':' . $pass) . "\r\n";
    }

    // Deprecated way of adding origin (use headers instead).
    if (isset($this->options['origin'])) $headers['origin'] = $this->options['origin'];

    // Add and override with headers from options.
    if (isset($this->options['headers'])) {
      $headers = array_merge($headers, array_change_key_case($this->options['headers']));
    }

    $header =
      "GET " . $path_with_query . " HTTP/1.1\r\n"
      . implode(
        "\r\n", array_map(
          function($key, $value) { return "$key: $value"; }, array_keys($headers), $headers
        )
      )
      . "\r\n\r\n";

    // Send headers.
    $this->write($header);

    // Get server response header (terminated with double CR+LF).
    $response = stream_get_line($this->socket, 1024, "\r\n\r\n");

    /// @todo Handle version switching

    // Validate response.
    if (!preg_match('#Sec-WebSocket-Accept:\s(.*)$#mUi', $response, $matches)) {
      $address = $scheme . '://' . $host . $path_with_query;
      throw new ConnectionException(
        "Connection to '{$address}' failed: Server sent invalid upgrade response:\n"
        . $response
      );
    }

    $keyAccept = trim($matches[1]);
    $expectedResonse
      = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

    if ($keyAccept !== $expectedResonse) {
      throw new ConnectionException('Server sent bad upgrade response.');
    }

    $this->is_connected = true;
  }

  /**
   * Generate a random string for WebSocket key.
   * @return string Random string
   */
  protected static function generateKey() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"$&/()=[]{}0123456789';
    $key = '';
    $chars_length = strlen($chars);
    for ($i = 0; $i < 16; $i++) $key .= $chars[mt_rand(0, $chars_length-1)];
    return base64_encode($key);
  }
}
