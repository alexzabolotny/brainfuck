<?php

class CommandLineTest extends TestCase {
	/** @test */
	public function run_interpreter_from_command_line_with_program_file_as_argument() {
		$output = $this->runCommand('php ' . __DIR__ . '/../../bin/bf', [__DIR__ . '/../fixtures/simple_out.bf']);
		$this->assertEquals(['2'], $output);
	}

	/** @test */
	public function see_usage() {
		$output = $this->runCommand('php ' . __DIR__ . '/../../bin/bf', []);
		$this->assertEquals(['Usage: bf <program.bf>'], $output);
	}

	private function runCommand($commandPath, $arguments = []) {
		exec($commandPath . ' ' . implode(' ', $arguments), $output, $result);

		if ($result != 0) {
			$this->fail('Command ' . $commandPath . ' failed with code [' . $result . ']. Output: ' . implode(PHP_EOL, $output));
		}

		return $output;
	}
}