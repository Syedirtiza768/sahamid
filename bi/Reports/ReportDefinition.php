<?php

namespace SAHamid\BI\Reports;

/**
 * A user-facing report definition.
 *
 * Report definitions are intentionally separate from the legacy report
 * implementations. They describe the reporting contract and the compatible
 * source route without changing the source calculation.
 */
class ReportDefinition
{
	private $data;

	public function __construct(array $data)
	{
		$defaults = array(
			'id' => '',
			'title' => '',
			'category' => 'Other',
			'description' => '',
			'legacy_route' => null,
			'bi_route' => null,
			'status' => 'compatibility',
			'source' => '',
			'grain' => '',
			'date_role' => null,
			'date_fields' => array(),
			'filters' => array(),
			'group_by' => array(),
			'aggregations' => array(),
			'visualizations' => array('table'),
			'notes' => array(),
		);
		$this->data = array_merge($defaults, $data);
	}

	public function getId() { return $this->data['id']; }
	public function getTitle() { return $this->data['title']; }
	public function getStatus() { return $this->data['status']; }
	public function getBiRoute() { return $this->data['bi_route']; }

	public function toArray()
	{
		return $this->data;
	}
}
