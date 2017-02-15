<?php

namespace Bf\Writer;


class StdoutWriter implements Writer {
	public function write($byte) {
		echo $byte;
	}
}