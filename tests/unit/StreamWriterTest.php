<?php

use Bf\Writer\StdoutWriter;

class StreamWriterTest extends TestCase {
	protected function setUp() {
		parent::setUp();

		ob_clean();
	}

	/** @test */
	public function can_write_to_stdout() {
		$writer = new StdoutWriter();

		ob_start();
		$writer->write('a');
		$output = ob_get_clean();

		$this->assertEquals('a', $output);
	}
}