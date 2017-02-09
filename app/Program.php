<?php

namespace Bf;

use Bf\Exception\UnbalancedLoop;
use Bf\Reader\Reader;
use Bf\Writer\Writer;

class Program {
	const TAPE_SIZE = 30000;

	private $tape;
	private $dataPointer;
	private $executionPointer;
	private $writer;
	private $reader;
	private $loopPairs;

	public function __construct(Writer $writer = null, Reader $reader = null)
	{
		$this->tape = array_fill(0, self::TAPE_SIZE, 0);
		$this->dataPointer = 0;
		$this->writer = $writer;
		$this->reader = $reader;
	}

	public function tape($tape = null) {
		if (!is_null($tape) && is_array($tape)) {
			$this->tape = $tape;
		}

		return $this->tape;
	}

	public function pointer($pointer = null) {
		if (!is_null($pointer) && $pointer >= 0 && $pointer <= self::TAPE_SIZE) {
			$this->dataPointer = $pointer;
		}

		return $this->dataPointer;
	}

	public function execute($programCode = '') {
		$this->executionPointer = 0;

		$commands = str_split($programCode);

		$this->checkLoopBalance($commands);
		$this->loopPairs = $this->findLoopPairs($commands);

		$executionEnd = count($commands);

		while ($this->executionPointer < $executionEnd) {
			$this->executeCommand($commands[$this->executionPointer]);
		}
	}

	// this assumes we have balanced braces
	public function findLoopPairs($commands) {
		$pairs = [];
		$lastTailPosition = count($commands) - 1;
		for ($i = 0; $i < count($commands) && $i < $lastTailPosition; $i++) {
			if ($commands[$i] == '[') {
				for ($j = $lastTailPosition; $j > $i; $j--) {
					if ($commands[$j] == ']') {
						$pairs[] = [$i, $j];
						$lastTailPosition = $j - 1;
						break;
					}
				}
			}
		}

		return $pairs;
	}

	private function executeCommand($command) {
		if ($command == '.') {
			$this->writer->write($this->tape[$this->dataPointer]);
			$this->executionPointer++;
		} else if ($command == ',') {
			$this->tape[$this->dataPointer] = $this->reader->read();
			$this->executionPointer++;
		} else if ($command == '>') {
			$this->dataPointer < self::TAPE_SIZE ? $this->dataPointer++ : false;
			$this->executionPointer++;
		} else if ($command == '<') {
			$this->dataPointer > 0 ? $this->dataPointer-- : false;
			$this->executionPointer++;
		} else if ($command == '+') {
			$this->tape[$this->dataPointer]++;
			$this->executionPointer++;
		} else if ($command == '-') {
			$this->tape[$this->dataPointer]--;
			$this->executionPointer++;
		} else if ($command == '[') {
			if ($this->tape[$this->dataPointer] == 0) {
				$jumpTo = $this->findLoopPair();
				$this->executionPointer = $jumpTo;
			} else {
				$this->executionPointer++;
			}
		} else if ($command == ']') {
			if ($this->tape[$this->dataPointer] == 0) {
				$this->executionPointer++;
			} else {
				$jumpTo = $this->findLoopPair();
				$this->executionPointer = $jumpTo;
			}
		} else { // nop
			$this->executionPointer++;
		}
	}

	private function checkLoopBalance($commands) {
		$balance = 0;
		foreach ($commands as $command) {
			if ($command == ']') {
				$balance--;
			}
			if ($command == '[') {
				$balance++;
			}
			if ($balance < 0) {
				throw new UnbalancedLoop();
			}
		}
		if ($balance != 0) {
			throw new UnbalancedLoop();
		}
	}

	private function findLoopPair() {
		foreach ($this->loopPairs as $pair) {
			if ($pair[0] == $this->executionPointer) {
				return $pair[1];
			}
			if ($pair[1] == $this->executionPointer) {
				return $pair[0];
			}
		}
		throw new UnbalancedLoop();
	}
}