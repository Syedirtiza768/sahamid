<?php

namespace SAHamid\BI\Query;

class QueryResult
{
	private $rows;
	private $metadata;
	private $warnings;

	public function __construct(array $rows, array $metadata, array $warnings = array())
	{
		$this->rows = $rows;
		$this->metadata = $metadata;
		$this->warnings = $warnings;
	}

	public function getRows() { return $this->rows; }
	public function getMetadata() { return $this->metadata; }
	public function getWarnings() { return $this->warnings; }

	public function toArray()
	{
		return array('rows' => $this->rows, 'metadata' => $this->metadata, 'warnings' => $this->warnings);
	}
}
