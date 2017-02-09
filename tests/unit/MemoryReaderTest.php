<?php

use Bf\Reader\MemoryReader;

class MemoryReaderTest extends TestCase {
	/** @test */
	public function can_read_from_buffer() {
		$reader = new MemoryReader();
		$this->assertEquals(null, $reader->read()); // buffer empty

		$reader->buffer([1, 2]);
		$this->assertEquals(1, $reader->read());
		$this->assertEquals(2, $reader->read());
		$this->assertEquals(null, $reader->read());

		$reader->buffer([3, 4]);
		$this->assertEquals(3, $reader->read());
		$this->assertEquals(4, $reader->read());
		$this->assertEquals(null, $reader->read());
	}
}