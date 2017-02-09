<?php

use Bf\Program;
use Bf\Reader\MemoryReader;
use Bf\Writer\MemoryWriter;

class ProgramTest extends TestCase {
	/** @test */
	public function initial_tape_state_is_empty() {
		$program = new Program();

		$this->assertEquals($this->cleanTape(), $program->tape());
	}

	/** @test */
	public function can_set_tape_state() {
		$program = new Program();
		$this->assertEquals($this->cleanTape(), $program->tape());

		$newTape = $this->cleanTape();
		$newTape[0] = 1;
		$program->tape($newTape);

		$this->assertEquals($newTape, $program->tape());
	}

	/** @test */
	public function initial_pointer_position_is_at_beginning_of_tape() {
		$program = new Program();

		$this->assertEquals(0, $program->pointer());
	}

	/** @test */
	public function can_output() {
		$writer = new MemoryWriter();

		$program = new Program($writer);
		$tape = $this->cleanTape();
		$tape[0] = 1;
		$program->tape($tape);

		$program->execute('.');
		$this->assertEquals([1], $writer->flush());
	}

	/** @test */
	public function can_input() {
		$writer = new MemoryWriter();
		$reader = new MemoryReader();

		$program = new Program($writer, $reader);
		$program->execute('.');
		$this->assertEquals([0], $writer->flush());

		$reader->buffer([1, 2]);
		$program->execute(',');
		$program->execute('.');

		$this->assertEquals([1], $writer->flush());
	}

	/** @test */
	public function can_move_pointer() {
		$program = new Program();

		$program->execute('>');
		$this->assertEquals(1, $program->pointer());

		$program->execute('>');
		$this->assertEquals(2, $program->pointer());

		$program->execute('<');
		$this->assertEquals(1, $program->pointer());
	}

	/** @test */
	public function cannot_move_pointer_past_boundaries() {
		$program = new Program();
		$this->assertEquals(0, $program->pointer());

		$program->execute('<');
		$this->assertEquals(0, $program->pointer());

		$program->pointer(30000);
		$program->execute('>');
		$this->assertEquals(30000, $program->pointer());
	}

	/** @test */
	public function can_add_and_subtract_byte_at_pointer() {
		$writer = new MemoryWriter();
		$program = new Program($writer);
		$program->execute('.');
		$this->assertEquals([0], $writer->flush());

		$program->execute('+');
		$program->execute('.');
		$this->assertEquals([1], $writer->flush());

		$program->execute('+');
		$program->execute('.');
		$this->assertEquals([2], $writer->flush());

		$program->execute('-');
		$program->execute('.');
		$this->assertEquals([1], $writer->flush());
	}

	/** @test */
	public function can_execute_series_of_commands() {
		$writer = new MemoryWriter();
		$program = new Program($writer);

		$program->execute('+>++>+++.<.<.');
		$this->assertEquals([3, 2, 1], $writer->flush());
	}

	/**
	 * @test
	 * @dataProvider unbalancedLoops
	 */
	public function should_check_for_unbalanced_loops($code) {
		$program = new Program();

		try {
			$program->execute(']');
		} catch (Bf\Exception\UnbalancedLoop $e) {
			return;
		}

		$this->fail('Cannot detect unbalanced loop in program.');
	}

	public function unbalancedLoops() {
		return [
			[']'],
			['['],
			['[[]'],
			['[]]'],
			[']][['],
		];
	}

	/**
	 * @test
	 * @group Foo
	 */
	public function can_find_pairs_of_loop_brackets() {
		$program = new Program();

		$this->assertEquals([], $program->findLoopPairs([]));
		$this->assertEquals([[0, 1]], $program->findLoopPairs(str_split('[]')));
		$this->assertEquals([[2, 11], [5, 8]], $program->findLoopPairs(str_split('..[..[--]++]..')));
		$this->assertEquals([[0, 5], [1, 2], [3, 4]], $program->findLoopPairs(str_split('[[][]]')));
	}

	/** @test */
	public function can_skip_loop_on_nonzero_accumulator() {
		$writer = new MemoryWriter();
		$program = new Program($writer);

		$program->execute('[-].');
		$this->assertEquals([0], $writer->flush());
	}

	/** @test */
	public function test_can_loop() {
		$writer = new MemoryWriter();
		$program = new Program($writer);

		$program->execute('++ [ > + < - ] > .');
		$this->assertEquals([2], $writer->flush());
	}

	/** @test */
	public function can_multiply() {
		$writer = new MemoryWriter();
		$program = new Program($writer);

		$program->execute('++++++>+++++++< [ > [ >+ >+ << -] >> [- << + >>] <<< -] >>');
		$this->assertEquals([42], $writer->flush());
	}

	private function cleanTape() {
		return array_fill(0, 30000, 0);
	}
}