<?php

namespace Bf\Reader;

class MemoryReader implements Reader {
	private $buffer = [];
	private $pointer = 0;

	public function read() {
		if (isset($this->buffer[$this->pointer])) {
			$byte = $this->buffer[$this->pointer];
			$this->pointer++;
			return $byte;
		}
		return null;
	}

	// this is FIFO
	public function buffer($bytes = []) {
		foreach ($bytes as $byte) {
			$this->buffer[] = $byte;
		}
	}
}