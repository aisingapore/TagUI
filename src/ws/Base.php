<?php

/**
 * Copyright (C) 2014, 2015 Textalk
 * Copyright (C) 2015 Patrick McCarren - added payload fragmentation for huge payloads
 *
 * This file is part of Websocket PHP and is free software under the ISC License.
 * License text: https://raw.githubusercontent.com/Textalk/websocket-php/master/COPYING
 */

namespace WebSocket;

class Base {
  protected $socket, $is_connected = false, $is_closing = false, $last_opcode = null,
    $close_status = null, $huge_payload = null;

  protected static $opcodes = array(
    'continuation' => 0,
    'text'         => 1,
    'binary'       => 2,
    'close'        => 8,
    'ping'         => 9,
    'pong'         => 10,
  );

  public function getLastOpcode()  { return $this->last_opcode;  }
  public function getCloseStatus() { return $this->close_status; }
  public function isConnected()    { return $this->is_connected; }

  public function setTimeout($timeout) {
    $this->options['timeout'] = $timeout;

    if ($this->socket && get_resource_type($this->socket) === 'stream') {
      stream_set_timeout($this->socket, $timeout);
    }
  }

  public function setFragmentSize($fragment_size) {
    $this->options['fragment_size'] = $fragment_size;
    return $this;
  }

  public function getFragmentSize() {
    return $this->options['fragment_size'];
  }

  public function send($payload, $opcode = 'text', $masked = true) {
    if (!$this->is_connected) $this->connect(); /// @todo This is a client function, fixme!

    if (!in_array($opcode, array_keys(self::$opcodes))) {
      throw new BadOpcodeException("Bad opcode '$opcode'.  Try 'text' or 'binary'.");
    }

    // record the length of the payload
    $payload_length = strlen($payload);

    $fragment_cursor = 0;
    // while we have data to send
    while ($payload_length > $fragment_cursor) {
      // get a fragment of the payload
      $sub_payload = substr($payload, $fragment_cursor, $this->options['fragment_size']);

      // advance the cursor
      $fragment_cursor += $this->options['fragment_size'];

      // is this the final fragment to send?
      $final = $payload_length <= $fragment_cursor;

      // send the fragment
      $this->send_fragment($final, $sub_payload, $opcode, $masked);

      // all fragments after the first will be marked a continuation
      $opcode = 'continuation';
    }

  }

  protected function send_fragment($final, $payload, $opcode, $masked) {
    // Binary string for header.
    $frame_head_binstr = '';

    // Write FIN, final fragment bit.
    $frame_head_binstr .= (bool) $final ? '1' : '0';

    // RSV 1, 2, & 3 false and unused.
    $frame_head_binstr .= '000';

    // Opcode rest of the byte.
    $frame_head_binstr .= sprintf('%04b', self::$opcodes[$opcode]);

    // Use masking?
    $frame_head_binstr .= $masked ? '1' : '0';

    // 7 bits of payload length...
    $payload_length = strlen($payload);
    if ($payload_length > 65535) {
      $frame_head_binstr .= decbin(127);
      $frame_head_binstr .= sprintf('%064b', $payload_length);
    }
    elseif ($payload_length > 125) {
      $frame_head_binstr .= decbin(126);
      $frame_head_binstr .= sprintf('%016b', $payload_length);
    }
    else {
      $frame_head_binstr .= sprintf('%07b', $payload_length);
    }

    $frame = '';

    // Write frame head to frame.
    foreach (str_split($frame_head_binstr, 8) as $binstr) $frame .= chr(bindec($binstr));

    // Handle masking
    if ($masked) {
      // generate a random mask:
      $mask = '';
      for ($i = 0; $i < 4; $i++) $mask .= chr(rand(0, 255));
      $frame .= $mask;
    }

    // Append payload to frame:
    for ($i = 0; $i < $payload_length; $i++) {
      $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
    }

    $this->write($frame);
  }

  public function receive() {
    if (!$this->is_connected) $this->connect(); /// @todo This is a client function, fixme!

    $this->huge_payload = '';

    $response = null;
    while (is_null($response)) $response = $this->receive_fragment();

    return $response;
  }

  protected function receive_fragment() {

    // Just read the main fragment information first.
    $data = $this->read(2);

    // Is this the final fragment?  // Bit 0 in byte 0
    /// @todo Handle huge payloads with multiple fragments.
    $final = (boolean) (ord($data[0]) & 1 << 7);

    // Should be unused, and must be false…  // Bits 1, 2, & 3
    $rsv1  = (boolean) (ord($data[0]) & 1 << 6);
    $rsv2  = (boolean) (ord($data[0]) & 1 << 5);
    $rsv3  = (boolean) (ord($data[0]) & 1 << 4);

    // Parse opcode
    $opcode_int = ord($data[0]) & 31; // Bits 4-7
    $opcode_ints = array_flip(self::$opcodes);
    if (!array_key_exists($opcode_int, $opcode_ints)) {
      throw new ConnectionException("Bad opcode in websocket frame: $opcode_int");
    }
    $opcode = $opcode_ints[$opcode_int];

    // record the opcode if we are not receiving a continutation fragment
    if ($opcode !== 'continuation') {
      $this->last_opcode = $opcode;
    }

    // Masking?
    $mask = (boolean) (ord($data[1]) >> 7);  // Bit 0 in byte 1

    $payload = '';

    // Payload length
    $payload_length = (integer) ord($data[1]) & 127; // Bits 1-7 in byte 1
    if ($payload_length > 125) {
      if ($payload_length === 126) $data = $this->read(2); // 126: Payload is a 16-bit unsigned int
      else                         $data = $this->read(8); // 127: Payload is a 64-bit unsigned int
      $payload_length = bindec(self::sprintB($data));
    }

    // Get masking key.
    if ($mask) $masking_key = $this->read(4);

    // Get the actual payload, if any (might not be for e.g. close frames.
    if ($payload_length > 0) {
      $data = $this->read($payload_length);

      if ($mask) {
        // Unmask payload.
        for ($i = 0; $i < $payload_length; $i++) $payload .= ($data[$i] ^ $masking_key[$i % 4]);
      }
      else $payload = $data;
    }

    if ($opcode === 'close') {
      // Get the close status.
      if ($payload_length >= 2) {
        $status_bin = $payload[0] . $payload[1];
        $status = bindec(sprintf("%08b%08b", ord($payload[0]), ord($payload[1])));
        $this->close_status = $status;
        $payload = substr($payload, 2);

        if (!$this->is_closing) $this->send($status_bin . 'Close acknowledged: ' . $status, 'close', true); // Respond.
      }

      if ($this->is_closing) $this->is_closing = false; // A close response, all done.

      // And close the socket.
      fclose($this->socket);
      $this->is_connected = false;
    }

    // if this is not the last fragment, then we need to save the payload
    if (!$final) {
      $this->huge_payload .= $payload;
      return null;
    }
    // this is the last fragment, and we are processing a huge_payload
    else if ($this->huge_payload) {
      // sp we need to retreive the whole payload
      $payload = $this->huge_payload .= $payload;
      $this->huge_payload = null;
    }

    return $payload;
  }

  /**
   * Tell the socket to close.
   *
   * @param integer $status  http://tools.ietf.org/html/rfc6455#section-7.4
   * @param string  $message A closing message, max 125 bytes.
   */
  public function close($status = 1000, $message = 'ttfn') {
    $status_binstr = sprintf('%016b', $status);
    $status_str = '';
    foreach (str_split($status_binstr, 8) as $binstr) $status_str .= chr(bindec($binstr));
    $this->send($status_str . $message, 'close', true);

    $this->is_closing = true;
    $response = $this->receive(); // Receiving a close frame will close the socket now.

    return $response;
  }

  protected function write($data) {
    $written = fwrite($this->socket, $data);

    if ($written < strlen($data)) {
      throw new ConnectionException(
        "Could only write $written out of " . strlen($data) . " bytes."
      );
    }
  }

  protected function read($length) {
    $data = '';
    while (strlen($data) < $length) {
      $buffer = fread($this->socket, $length - strlen($data));
      if ($buffer === false) {
        $metadata = stream_get_meta_data($this->socket);
        throw new ConnectionException(
          'Broken frame, read ' . strlen($data) . ' of stated '
          . $length . ' bytes.  Stream state: '
          . json_encode($metadata)
        );
      }
      if ($buffer === '') {
        $metadata = stream_get_meta_data($this->socket);
        throw new ConnectionException(
          'Empty read; connection dead?  Stream state: ' . json_encode($metadata)
        );
      }
      $data .= $buffer;
    }
    return $data;
  }


  /**
   * Helper to convert a binary to a string of '0' and '1'.
   */
  protected static function sprintB($string) {
    $return = '';
    for ($i = 0; $i < strlen($string); $i++) $return .= sprintf("%08b", ord($string[$i]));
    return $return;
  }
}
