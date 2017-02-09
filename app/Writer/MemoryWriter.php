<?php

namespace Bf\Writer;

class MemoryWriter implements Writer {
	private $buffer = [];

	public function write($byte) {
		$this->buffer[] = $byte;
	}

	public function flush() {
		$buf = $this->buffer;
		$this->buffer = [];
		return $buf;
	}
}