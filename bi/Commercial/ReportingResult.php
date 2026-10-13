<?php

namespace SAHamid\BI\Commercial;

class ReportingResult
{
	private $facts;
	private $aggregates;
	private $metadata;

	public function __construct(array $facts, array $aggregates, array $metadata)
	{
		$this->facts = $facts;
		$this->aggregates = $aggregates;
		$this->metadata = $metadata;
	}

	public function getFacts() { return $this->facts; }
	public function getAggregates() { return $this->aggregates; }
	public function getMetadata() { return $this->metadata; }
}
